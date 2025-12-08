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
use Modules\Clients\Entities\ClientStatus;
use Morilog\Jalali\Jalalian;


class TaskController extends Controller
{
    protected function normalizeRequest(Request $request): void
    {
        // 1) تبدیل تاریخ شمسی (due_at_view) به فیلد due_at میلادی
        if (!$request->filled('due_at') && $request->filled('due_at_view')) {
            $jalali = $request->input('due_at_view');

            try {
                if (class_exists(Jalalian::class)) {
                    // فرض فرمت 1403/09/18
                    $carbon = Jalalian::fromFormat('Y/m/d', $jalali)->toCarbon()->startOfDay();
                } else {
                    // اگر پکیج جلالی نداری، موقتاً همین رو استفاده کن
                    $carbon = Carbon::parse($jalali);
                }

                $request->merge([
                    'due_at' => $carbon->toDateString(),
                ]);
            } catch (\Throwable $e) {
                // اگر تبدیل موفق نشد، تاریخ رو خالی می‌ذاریم
                $request->merge([
                    'due_at' => null,
                ]);
            }
        }

        // 2) استخراج assignee_id از multi-select جدید (assignee_user_ids[])
        $assigneeIds = $request->input('assignee_user_ids', []);

        if (!is_array($assigneeIds)) {
            $assigneeIds = array_filter([$assigneeIds]);
        }

        $assigneeId = collect($assigneeIds)->filter()->first();

        // بک‌کامپتیبل: اگر کسی هنوز assignee_id کلاسیک رو فرستاده بود
        if (!$assigneeId && $request->filled('assignee_id')) {
            $assigneeId = $request->input('assignee_id');
        }

        $request->merge([
            'assignee_id' => $assigneeId,
        ]);

        // 3) استخراج related_type / related_id بر اساس related_target + multi-select ها
        $relatedType = null;
        $relatedId   = null;

        $target = $request->input('related_target');

        if ($target === 'user') {
            $userIds = $request->input('related_user_ids', $request->input('related_user_id'));

            if (!is_array($userIds)) {
                $userIds = array_filter([$userIds]);
            }

            $relatedId = collect($userIds)->filter()->first();
            if ($relatedId) {
                $relatedType = User::class;
            }
        } elseif ($target === 'client') {
            $clientIds = $request->input('related_client_ids', $request->input('related_client_id'));

            if (!is_array($clientIds)) {
                $clientIds = array_filter([$clientIds]);
            }

            $relatedId = collect($clientIds)->filter()->first();
            if ($relatedId) {
                $relatedType = Client::class;
            }
        }

        // اگر "هیچکدام" بود یا چیزی انتخاب نشد، ارتباط رو null می‌کنیم
        if ($target === 'none' || !$target) {
            $relatedType = null;
            $relatedId   = null;
        }

        $request->merge([
            'related_type' => $relatedType,
            'related_id'   => $relatedId,
        ]);
    }

    protected function validateRequest(Request $request, ?Task $task = null): array
    {
        $types      = array_keys(Task::typeOptions());
        $statuses   = array_keys(Task::statusOptions());
        $priorities = array_keys(Task::priorityOptions());

        return $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'task_type'   => ['required', Rule::in($types)],
            'status'      => ['required', Rule::in($statuses)],
            'priority'    => ['required', Rule::in($priorities)],
            'due_at'      => ['nullable', 'date'],

            // 🔹 حالت انتخاب مسئول
            'assignee_mode' => ['nullable', 'in:single_user,by_roles'],

            // 🔹 مسئول‌ها (چند کاربر)
            'assignee_user_ids'   => ['nullable', 'array'],
            'assignee_user_ids.*' => ['integer', 'exists:users,id'],

            // 🔹 مسئول‌ها بر اساس نقش
            'assignee_role_ids'   => ['nullable', 'array'],
            'assignee_role_ids.*' => ['integer', 'exists:roles,id'],

            // 🔹 موجودیت مرتبط
            'related_target' => ['nullable', 'in:none,user,client'],

            // 🔹 نقش‌های کاربران مرتبط (برای فیلتر پویا)
            'related_user_role_ids'   => ['nullable', 'array'],
            'related_user_role_ids.*' => ['integer', 'exists:roles,id'],

            // 🔹 خود کاربران مرتبط (multi-select جدید)
            'related_user_ids'   => ['nullable', 'array'],
            'related_user_ids.*' => ['integer', 'exists:users,id'],

            // 🔹 وضعیت‌های مشتری (برای فیلتر پویا)
            'related_client_status_ids'   => ['nullable', 'array'],
            'related_client_status_ids.*' => ['integer', 'exists:client_statuses,id'],

            // 🔹 خود مشتریان مرتبط (multi-select جدید)
            'related_client_ids'   => ['nullable', 'array'],
            'related_client_ids.*' => ['integer', 'exists:clients,id'],
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
        $clients        = \Modules\Clients\Entities\Client::select('id', 'full_name', 'phone', 'status_id')->get();
        $clientStatuses = \Modules\Clients\Entities\ClientStatus::active()->get();

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

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $this->validateRequest($request, $task);

        $user       = auth()->user();
        $creatorId  = $task->creator_id ?? ($user ? $user->id : null);
        $assigneeId = $task->assignee_id ?? $creatorId;

        // 🔹 تعیین مسئول بر اساس حالت و دسترسی
        $canAssign = $user && (
                $user->can('tasks.assign')
                || $user->can('tasks.manage')
                || $user->hasRole('super-admin')
            );

        $assigneeMode = $data['assignee_mode'] ?? 'single_user';
        $assigneeUserIds = collect($data['assignee_user_ids'] ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($assigneeMode === 'single_user') {
            // اگر دسترسی دارد و حداقل یک کاربر انتخاب شده
            if ($canAssign && $assigneeUserIds->isNotEmpty()) {
                $assigneeId = (int) $assigneeUserIds->first();
            }
            // اگر دسترسی ندارد، همان قبلی باقی می‌ماند (یا خودش)
        } else {
            // حالت by_roles → فعلاً همان مسئول قبلی/پیش‌فرض را نگه می‌داریم
            // اگر خواستی می‌تونی اینجا بعداً منطق خاص برای نقش‌ها اضافه کنی
        }

        // 🔹 تعیین موجودیت مرتبط اصلی (برای همین Task)
        $relatedType = null;
        $relatedId   = null;
        $relatedTarget = $data['related_target'] ?? 'none';

        if ($relatedTarget === 'user') {
            $relatedUserIds = collect($data['related_user_ids'] ?? [])
                ->filter()
                ->unique()
                ->values();

            if ($relatedUserIds->isNotEmpty()) {
                $relatedType = \App\Models\User::class;
                $relatedId   = (int) $relatedUserIds->first();
            }
        } elseif ($relatedTarget === 'client') {
            $relatedClientIds = collect($data['related_client_ids'] ?? [])
                ->filter()
                ->unique()
                ->values();

            if ($relatedClientIds->isNotEmpty()) {
                $relatedType = Client::class;
                $relatedId   = (int) $relatedClientIds->first();
            }
        }

        // 🔹 خود Task را آپدیت می‌کنیم
        $task->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'task_type'   => $data['task_type'],
            'status'      => $data['status'],
            'priority'    => $data['priority'],
            'due_at'      => $data['due_at'] ?? null,
            'assignee_id' => $assigneeId,
            'creator_id'  => $creatorId,
            'related_type' => $relatedType,
            'related_id'   => $relatedId,
        ]);

        return redirect()
            ->route('user.tasks.show', $task)
            ->with('success', 'وظیفه با موفقیت به‌روزرسانی شد.');
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
