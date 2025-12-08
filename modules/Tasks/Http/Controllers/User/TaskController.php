<?php

namespace Modules\Tasks\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Tasks\Entities\Task;
use Carbon\Carbon;
use Morilog\Jalali\CalendarUtils;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Modules\Clients\Entities\Client;


class TaskController extends Controller
{
    protected function validateRequest(Request $request): array
    {
        // کلیدهای مجاز را از خود مدل Task می‌گیریم تا با برچسب‌های فارسی هم‌خوان باشد
        $statusKeys   = array_keys(Task::statusOptions());
        $priorityKeys = array_keys(Task::priorityOptions());
        $typeKeys     = array_keys(Task::typeOptions());

        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],

            'task_type'    => ['nullable', 'string', Rule::in($typeKeys)],

            // مسئول
            'assignee_id'       => ['nullable', 'integer', 'exists:users,id'],
            'assignee_mode'     => ['nullable', 'string', 'in:single_user,by_roles'],
            'assignee_role_ids' => ['nullable', 'array'],
            'assignee_role_ids.*' => ['integer', 'exists:roles,id'],

            // وضعیت / اولویت
            'status'    => ['nullable', 'string', Rule::in($statusKeys)],
            'priority'  => ['nullable', 'string', Rule::in($priorityKeys)],

            // تاریخ سررسید (میلادی؛ با Jalali Datepicker مقداردهی می‌شود)
            'due_at'    => ['nullable', 'date'],
            'due_at_view'  => ['nullable', 'string'], // 👈 اضافه شد

            // فیلدهای خام related_type/related_id اگر از جایی دیگر فرم خام بیاد
            'related_type' => ['nullable', 'string', 'max:100'],
            'related_id'   => ['nullable', 'integer'],

            // موجودیت مرتبط سطح بالا
            'related_target' => ['nullable', 'string', 'in:none,user,client'],

            // موجودیت مرتبط: کاربران
            'related_user_role_ids'   => ['nullable', 'array'],
            'related_user_role_ids.*' => ['integer', 'exists:roles,id'],
            'related_user_id'         => ['nullable', 'integer', 'exists:users,id'],

            // موجودیت مرتبط: مشتریان
            'related_client_status_ids'   => ['nullable', 'array'],
            'related_client_status_ids.*' => ['integer', 'exists:client_statuses,id'],
            'related_client_id'           => ['nullable', 'integer', 'exists:clients,id'],
        ]);
    }
    /**
     * تبدیل تاریخ شمسی (مثلاً 1403/09/15 یا 1403-09-15) به Carbon میلادی.
     */
    private function convertJalaliDate(?string $jalali): ?Carbon
    {
        if (empty($jalali)) {
            return null;
        }

        try {
            // هر چیزی غیر عدد جداکننده فرض می‌شود (/, -, space, ...)
            $parts = preg_split('/[^\d]+/', trim($jalali));
            if (count($parts) < 3) {
                return null;
            }

            [$jy, $jm, $jd] = array_map('intval', array_slice($parts, 0, 3));

            [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);

            // فقط تاریخ (بدون زمان)
            return Carbon::createFromDate($gy, $gm, $gd)->startOfDay();
        } catch (\Throwable $e) {
            if (function_exists('logger')) {
                logger()->warning('Failed to convert Jalali due_at_view', [
                    'value' => $jalali,
                    'error' => $e->getMessage(),
                ]);
            }
            return null;
        }
    }

    /**
     * تعیین لیست کاربران مسئول بر اساس:
     * - نوع وظیفه (عمومی / پیگیری)
     * - دسترسی کاربر فعلی
     * - حالت انتخاب مسئول (تک‌کاربر / بر اساس نقش‌ها)
     */
    private function resolveAssigneeIds(array $data, Request $request, string $taskType, \App\Models\User $currentUser): array
    {
        $canAssign = $currentUser->can('tasks.assign')
            || $currentUser->can('tasks.manage')
            || $currentUser->hasRole('super-admin');

        // پیگیری + نداشتن دسترسی → همیشه خود کاربر فعلی
        if ($taskType === Task::TYPE_FOLLOW_UP && ! $canAssign) {
            return [$currentUser->id];
        }

        $assigneeMode    = $request->input('assignee_mode', 'single_user');
        $assigneeRoleIds = (array) $request->input('assignee_role_ids', []);
        $assigneeIds     = [];

        // حالت بر اساس نقش‌ها
        if ($assigneeMode === 'by_roles') {
            // اگر "همه نقش‌ها" انتخاب شده باشد (value="__all__")
            if (in_array('__all__', $assigneeRoleIds, true)) {
                $assigneeRoleIds = Role::pluck('id')->all();
            }

            if (! empty($assigneeRoleIds)) {
                $assigneeIds = User::query()
                    ->whereHas('roles', function ($q) use ($assigneeRoleIds) {
                        $q->whereIn('id', $assigneeRoleIds);
                    })
                    ->pluck('id')
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        // اگر بر اساس نقش‌ها چیزی درنیومد، از assignee_id استفاده کن
        if (empty($assigneeIds) && ! empty($data['assignee_id'])) {
            $assigneeIds = [(int) $data['assignee_id']];
        }

        // اگر هنوز خالی بود، حداقل خود کاربر فعلی را مسئول کن
        if (empty($assigneeIds)) {
            $assigneeIds = [$currentUser->id];
        }

        return $assigneeIds;
    }

    /**
     * تعیین لیست id مشتریان هدف بر اساس:
     * - انتخاب مستقیم یک مشتری
     * - یا انتخاب چند وضعیت مشتری
     */
    private function resolveClientIds(Request $request): array
    {
        $relatedTarget = $request->input('related_target', 'none');

        if ($relatedTarget !== 'client') {
            return [];
        }

        $clientId         = $request->input('related_client_id');
        $statusIds        = (array) $request->input('related_client_status_ids', []);
        $selectedClientIds = [];

        // ۱) اگر یک مشتری مشخص انتخاب شده باشد
        if (! empty($clientId)) {
            return [(int) $clientId];
        }

        // ۲) اگر وضعیت‌ها انتخاب شده باشند
        if (! empty($statusIds)) {
            // اگر گزینه "همه وضعیت‌ها" انتخاب شده باشد
            if (in_array('__all__', $statusIds, true)) {
                $selectedClientIds = Client::pluck('id')->all();
            } else {
                $selectedClientIds = Client::query()
                    ->whereIn('status_id', $statusIds)
                    ->pluck('id')
                    ->all();
            }
        }

        return $selectedClientIds;
    }


    protected function authorizeView(Task $task): void
    {
        $user = Auth::user();

        if ($user->can('tasks.view.all')) {
            return;
        }

        if ($user->can('tasks.view.assigned') && $task->assignee_id === $user->id) {
            return;
        }

        if ($user->can('tasks.view.own') && $task->creator_id === $user->id) {
            return;
        }

        abort(403);
    }

    protected function authorizeEdit(Task $task): void
    {
        $user = Auth::user();

        if (! $user->can('tasks.edit')) {
            abort(403);
        }

        // در صورت نیاز می‌توان محدودیت‌های بیشتری برای ویرایش اعمال کرد.
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Task::query()
            ->with(['assignee', 'creator'])
            ->orderByDesc('due_at')
            ->orderByDesc('created_at');

        if ($user->can('tasks.view.all')) {
            // همه وظایف
        } elseif ($user->can('tasks.view.assigned')) {
            $query->where('assignee_id', $user->id);
        } elseif ($user->can('tasks.view.own')) {
            $query->where('creator_id', $user->id);
        } else {
            abort(403);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('task_type')) {
            $query->where('task_type', $type);
        }

        if ($relatedType = $request->get('related_type')) {
            $query->where('related_type', $relatedType);
        }

        $perPage = config('tasks.default_items_per_page', 15);
        $tasks   = $query->paginate($perPage)->withQueryString();

        return view('tasks::user.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $user = Auth::user();

        if (! $user->can('tasks.create')) {
            abort(403);
        }

        // از خود Task برای برچسب‌های فارسی استفاده می‌کنیم
        $statuses   = Task::statusOptions();
        $priorities = Task::priorityOptions();
        $types      = Task::typeOptions();

        $users      = \App\Models\User::select('id', 'name', 'email')->get();
        $roles      = \Spatie\Permission\Models\Role::select('id', 'name')->get();

        // ماژول کلاینت
        $clients        = \Modules\Clients\Entities\Client::select('id', 'full_name', 'phone')->get();
        $clientStatuses = \Modules\Clients\Entities\ClientStatus::all();

        return view('tasks::user.tasks.create', compact(
            'statuses',
            'priorities',
            'types',
            'users',
            'roles',
            'clients',
            'clientStatuses'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('tasks.create')) {
            abort(403);
        }

        $data = $this->validateRequest($request);

        // نوع وظیفه (عمومی / پیگیری / سیستمی)
        $taskType = $data['task_type'] ?? Task::TYPE_GENERAL;

        // تاریخ سررسید از فیلد شمسی (due_at_view) → میلادی
        $dueAt = $this->convertJalaliDate($data['due_at_view'] ?? null)
            ?? (! empty($data['due_at']) ? Carbon::parse($data['due_at']) : null);

        // ۱) لیست کاربران مسئول (بر اساس نقش‌ها یا تک‌کاربر)
        $assigneeIds = $this->resolveAssigneeIds($data, $request, $taskType, $user);

        // ۲) لیست مشتریان هدف (بر اساس وضعیت یا یک مشتری مشخص)
        $clientIds = $this->resolveClientIds($request);

        // ۳) موجودیت مرتبط دیگر (مثلاً User) – فعلاً فقط یک‌تایی
        $relatedType = null;
        $relatedId   = null;

        $relatedTarget = $request->input('related_target', 'none');

        if ($relatedTarget === 'user' && $request->filled('related_user_id')) {
            $relatedType = Task::RELATED_TYPE_USER;
            $relatedId   = (int) $request->input('related_user_id');
        }

        // ۴) ساخت وظایف
        $createdTasks = [];

        // اگر مشتری‌ها مشخص شده‌اند → برای هر مشتری و هر مسئول یک وظیفه بساز
        if (! empty($clientIds)) {
            foreach ($clientIds as $cid) {
                foreach ($assigneeIds as $aid) {
                    $createdTasks[] = Task::create([
                        'title'        => $data['title'],
                        'description'  => $data['description'] ?? null,
                        'task_type'    => $taskType,
                        'assignee_id'  => $aid,
                        'creator_id'   => $user->id,
                        'status'       => $data['status'] ?? Task::STATUS_TODO,
                        'priority'     => $data['priority'] ?? Task::PRIORITY_MEDIUM,
                        'due_at'       => $dueAt,
                        'related_type' => Task::RELATED_TYPE_CLIENT,
                        'related_id'   => $cid,
                    ]);
                }
            }
        } else {
            // در غیر اینصورت، فقط بر اساس مسئول‌ها (بدون مشتری یا با related_type دیگر)
            foreach ($assigneeIds as $aid) {
                $createdTasks[] = Task::create([
                    'title'        => $data['title'],
                    'description'  => $data['description'] ?? null,
                    'task_type'    => $taskType,
                    'assignee_id'  => $aid,
                    'creator_id'   => $user->id,
                    'status'       => $data['status'] ?? Task::STATUS_TODO,
                    'priority'     => $data['priority'] ?? Task::PRIORITY_MEDIUM,
                    'due_at'       => $dueAt,
                    'related_type' => $relatedType,
                    'related_id'   => $relatedId,
                ]);
            }
        }

        // hook created در مدل Task خودش Reminder می‌سازد (برای هر Task)
        // یک وظیفه‌ی مرجع برای redirect
        $primaryTask = $createdTasks[0] ?? null;

        if (! $primaryTask) {
            return redirect()
                ->route('user.tasks.index')
                ->with('status', 'هیچ وظیفه‌ای ساخته نشد.');
        }

        return redirect()
            ->route('user.tasks.show', $primaryTask)
            ->with('status', 'وظایف با موفقیت ایجاد شدند.');
    }


    public function show(Task $task)
    {
        $this->authorizeView($task);

        $task->load(['assignee', 'creator']);

        return view('tasks::user.tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorizeEdit($task);

        $statuses   = Task::statusOptions();
        $priorities = Task::priorityOptions();
        $types      = Task::typeOptions();

        return view('tasks::user.tasks.edit', compact('task', 'statuses', 'priorities', 'types'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeEdit($task);

        $data = $this->validateRequest($request);
        $dueAt = $this->convertJalaliDate($data['due_at_view'] ?? null)
            ?? (!empty($data['due_at']) ? Carbon::parse($data['due_at']) : $task->due_at);
        $task->fill([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'task_type'    => $data['task_type'] ?? $task->task_type,
            'assignee_id'  => $data['assignee_id'] ?? $task->assignee_id,
            'status'       => $data['status'] ?? $task->status,
            'priority'     => $data['priority'] ?? $task->priority,
            'due_at'       => $dueAt,
            // در ویرایش ساده، related_type / related_id را دست نمی‌زنیم تا
            // لاجیک پیچیده مرتبط را بعداً جداگانه پیاده کنیم
        ]);

        if (in_array($task->status, [Task::STATUS_DONE, Task::STATUS_CANCELED], true) && ! $task->completed_at) {
            $task->completed_at = now();
        }

        $task->save();

        return redirect()
            ->route('user.tasks.show', $task)
            ->with('status', 'وظیفه با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Task $task)
    {
        $this->authorizeEdit($task);

        $task->delete();

        return redirect()
            ->route('user.tasks.index')
            ->with('status', 'وظیفه حذف شد.');
    }
}
