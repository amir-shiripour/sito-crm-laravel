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
    protected function validateRequest(Request $request, ?Task $task = null): array
    {
        $types      = array_keys(Task::typeOptions());
        $statuses   = array_keys(Task::statusOptions());
        $priorities = array_keys(Task::priorityOptions());

        return $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            // نوع وظیفه (GENERAL / FOLLOW_UP / SYSTEM)
            'task_type'   => ['required', Rule::in($types)],

            'status'      => ['required', Rule::in($statuses)],
            'priority'    => ['required', Rule::in($priorities)],

            // تاریخ میلادی مستقیم (اگر جایی از API/فرم دیگر بیاید)
            'due_at'      => ['nullable', 'date'],

            // تاریخ شمسی از فرم create/edit (مثلاً 1403/09/15)
            'due_at_view' => ['nullable', 'string'],
            'due_time'    => ['nullable', 'string'],

            // 🔹 حالت انتخاب مسئول
            'assignee_mode' => ['nullable', 'in:single_user,by_roles'],

            // 🔹 مسئول‌ها (چند کاربر مشخص)
            'assignee_user_ids'   => ['nullable', 'array'],
            'assignee_user_ids.*' => ['integer', 'exists:users,id'],

            // 🔹 مسئول‌ها بر اساس نقش
            // value ممکن است '__all__' باشد، پس integer نیست
            'assignee_role_ids'   => ['nullable', 'array'],
            'assignee_role_ids.*' => ['string'],  // '__all__' یا id عددی

            // 🔹 موجودیت مرتبط
            'related_target' => ['nullable', 'in:none,user,client'],

            // 🔹 نقش‌های کاربران مرتبط (برای فیلتر پویا + ذخیره در meta)
            'related_user_role_ids'   => ['nullable', 'array'],
            'related_user_role_ids.*' => ['string'], // ممکن است '__all__' باشد

            // 🔹 خود کاربران مرتبط (multi-select)
            'related_user_ids'   => ['nullable', 'array'],
            'related_user_ids.*' => ['integer', 'exists:users,id'],

            // 🔹 وضعیت‌های مشتری (برای فیلتر پویا)
            'related_client_status_ids'   => ['nullable', 'array'],
            'related_client_status_ids.*' => ['string'], // ممکن است '__all__' باشد

            // 🔹 خود مشتریان مرتبط (multi-select)
            'related_client_ids'   => ['nullable', 'array'],
            'related_client_ids.*' => ['integer', 'exists:clients,id'],
        ]);
    }


    /**
     * تبدیل تاریخ شمسی (مثلاً 1403/09/15 یا 1403-09-15) به Carbon میلادی.
     */
    /**
     * تبدیل تاریخ شمسی (مثلاً 1403/09/15 یا 1403-09-15) + ساعت اختیاری (HH:MM) به Carbon میلادی.
     */
    private function convertJalaliDate(?string $jalali, ?string $time = null): ?Carbon
    {
        if (empty($jalali)) {
            return null;
        }

        try {
            $parts = preg_split('/[^\d]+/', trim($jalali));
            if (count($parts) < 3) {
                return null;
            }

            [$jy, $jm, $jd] = array_map('intval', array_slice($parts, 0, 3));
            [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);

            // 🔹 ساعت/دقیقه پیش‌فرض: 00:00
            $hour = 0;
            $minute = 0;

            if (!empty($time)) {
                $timeParts = preg_split('/[^\d]+/', trim($time));
                if (count($timeParts) >= 2) {
                    $h = (int) $timeParts[0];
                    $m = (int) $timeParts[1];

                    if ($h >= 0 && $h <= 23) {
                        $hour = $h;
                    }
                    if ($m >= 0 && $m <= 59) {
                        $minute = $m;
                    }
                }
            }

            return Carbon::create($gy, $gm, $gd, $hour, $minute, 0);
        } catch (\Throwable $e) {
            if (function_exists('logger')) {
                logger()->warning('Failed to convert Jalali due_at_view', [
                    'value' => $jalali,
                    'time'  => $time,
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
    private function resolveAssigneeIds(array $data, Request $request, string $taskType, User $currentUser): array
    {
        $canAssign = $currentUser->can('tasks.assign')
            || $currentUser->can('tasks.manage')
            || $currentUser->hasRole('super-admin');

        // 🔹 پیگیری + نداشتن دسترسی → همیشه خود کاربر فعلی مسئول است
        if ($taskType === Task::TYPE_FOLLOW_UP && ! $canAssign) {
            return [$currentUser->id];
        }

        $assigneeMode = $data['assignee_mode'] ?? $request->input('assignee_mode', 'single_user');
        $assigneeIds  = [];

        // 🔹 حالت انتخاب چند کاربر مشخص
        if ($assigneeMode === 'single_user') {
            $ids = $data['assignee_user_ids'] ?? $request->input('assignee_user_ids', []);
            $ids = array_filter(array_map('intval', (array) $ids));

            if (! empty($ids) && $canAssign) {
                $assigneeIds = array_values(array_unique($ids));
            }

            // اگر دسترسی assign ندارد یا چیزی انتخاب نشده، fallback:
            if (empty($assigneeIds)) {
                $assigneeIds = [$currentUser->id];
            }
        }

        // 🔹 حالت انتخاب بر اساس نقش‌ها
        if ($assigneeMode === 'by_roles') {
            $roleIds = $data['assignee_role_ids'] ?? $request->input('assignee_role_ids', []);
            $roleIds = array_map('strval', (array) $roleIds);

            // اگر "همه نقش‌ها" انتخاب شده باشد
            if (in_array('__all__', $roleIds, true)) {
                $roleIds = Role::pluck('id')->map(fn ($id) => (string) $id)->all();
            } else {
                $roleIds = array_values(array_unique(array_filter($roleIds)));
            }

            if (! empty($roleIds) && $canAssign) {
                $assigneeIds = User::query()
                    ->whereHas('roles', function ($q) use ($roleIds) {
                        $q->whereIn('id', $roleIds);
                    })
                    ->pluck('id')
                    ->unique()
                    ->values()
                    ->all();
            }

            // اگر نتیجه‌ای نشد، fallback به خود کاربر
            if (empty($assigneeIds)) {
                $assigneeIds = [$currentUser->id];
            }
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
    /**
     * نوع و لیست شناسه‌های موجودیت مرتبط را بر اساس فرم تعیین می‌کند.
     *
     * خروجی:
     *  [$relatedType, $relatedIds]
     *  - $relatedType: یکی از Task::RELATED_TYPE_USER / Task::RELATED_TYPE_CLIENT / null
     *  - $relatedIds : آرایه‌ای از id ها (ممکن است خالی باشد)
     */
    private function resolveRelatedEntities(Request $request): array
    {
        $target = $request->input('related_target', 'none');

        if ($target === 'client') {
            $ids = (array) $request->input('related_client_ids', []);
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
            return [Task::RELATED_TYPE_CLIENT, $ids];
        }

        if ($target === 'user') {
            $ids = (array) $request->input('related_user_ids', []);
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
            return [Task::RELATED_TYPE_USER, $ids];
        }

        return [null, []];
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

        if (! $user->can('tasks.edit') && ! $user->can('tasks.manage')) {
            abort(403);
        }

        // در صورت نیاز می‌توان محدودیت‌های بیشتری برای ویرایش اعمال کرد.
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Task::query()
            ->with(['assignee', 'creator']);

        // مرتب‌سازی
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'due_asc':
                $query->orderByRaw('due_at IS NULL, due_at ASC')->orderByDesc('created_at');
                break;
            case 'due_desc':
                $query->orderByRaw('due_at IS NULL, due_at DESC')->orderByDesc('created_at');
                break;
            case 'newest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        // دسترسی‌ها
        if ($user->can('tasks.view.all')) {
            // همه وظایف
        } elseif ($user->can('tasks.view.assigned')) {
            $query->where('assignee_id', $user->id);
        } elseif ($user->can('tasks.view.own')) {
            $query->where('creator_id', $user->id);
        } else {
            abort(403);
        }

        // جستجو در عنوان/توضیحات
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // فیلتر وضعیت
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // فیلتر اولویت
        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        // فیلتر نوع موجودیت مرتبط (مثلاً client)
        if ($relatedType = $request->get('related_type')) {
            $query->where('related_type', $relatedType);
        }

        // فیلتر شناسه موجودیت مرتبط (مثلاً id کلاینت)
        if ($relatedId = $request->get('related_id')) {
            $query->where('related_id', $relatedId);
        }

        // فیلتر کاربر مسئول
        if ($user->can('tasks.view.all') && $assigneeId = $request->get('assignee_id')) {
            $query->where('assignee_id', $assigneeId);
        }

        $perPage = config('tasks.default_items_per_page', 15);
        $tasks   = $query->paginate($perPage)->withQueryString();

        // برای نمایش labelها در جدول
        $statuses   = Task::statusOptions();
        $priorities = Task::priorityOptions();
        $types      = Task::typeOptions();

        $users = [];
        if ($user->can('tasks.view.all')) {
            $users = \App\Models\User::select('id', 'name', 'email')->get();
        }

        return view('tasks::user.tasks.index', compact(
            'tasks',
            'statuses',
            'priorities',
            'types',
            'users'
        ));
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

        // ۱) اعتبارسنجی
        $data = $this->validateRequest($request);

        // نوع وظیفه (عمومی / پیگیری / سیستمی)
        $taskType = $data['task_type'] ?? Task::TYPE_GENERAL;

        // ۲) تبدیل تاریخ سررسید:
        //    اولویت با due_at_view (شمسی) است، اگر نبود از due_at (میلادی) استفاده می‌کنیم.
        $dueAt = $this->convertJalaliDate(
            $request->input('due_at_view'),
            $request->input('due_time')
        )
            ?? (! empty($data['due_at']) ? Carbon::parse($data['due_at']) : null);


        // ۳) لیست کاربران مسئول (بر اساس حالت و دسترسی)
        $assigneeIds = $this->resolveAssigneeIds($data, $request, $taskType, $user);

        // ۴) نوع و لیست موجودیت‌های مرتبط (user/client)
        [$relatedType, $relatedIds] = $this->resolveRelatedEntities($request);

        // ۵) meta برای نگه‌داشتن تنظیمات فرم (برای توسعه‌پذیری)
        $meta = [
            'assignee_mode'             => $request->input('assignee_mode', 'single_user'),
            'assignee_role_ids'         => array_values((array) $request->input('assignee_role_ids', [])),

            'related_target'            => $request->input('related_target', 'none'),
            'related_user_role_ids'     => array_values((array) $request->input('related_user_role_ids', [])),
            'related_user_ids'          => array_values((array) $request->input('related_user_ids', [])),
            'related_client_status_ids' => array_values((array) $request->input('related_client_status_ids', [])),
            'related_client_ids'        => array_values((array) $request->input('related_client_ids', [])),
        ];

        // ۶) ساخت وظایف
        $createdTasks = [];

        // اگر موجودیت مرتبط وجود دارد → برای هر related_id و هر مسئول یک Task بساز
        if (! empty($relatedType) && ! empty($relatedIds)) {
            foreach ($relatedIds as $rid) {
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
                        'related_id'   => $rid,
                        'meta'         => $meta,
                    ]);
                }
            }
        } else {
            // در غیر اینصورت، فقط بر اساس مسئول‌ها (بدون موجودیت مرتبط)
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
                    'related_type' => null,
                    'related_id'   => null,
                    'meta'         => $meta,
                ]);
            }
        }

        // هوک created در مدل Task خودش Reminder می‌سازد (برای هر Task)

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

        $meta = $task->meta ?? [];

        // برای نمایش label وضعیت‌ها/اولویت‌ها/نوع
        $types      = Task::typeOptions();
        $statuses   = Task::statusOptions();
        $priorities = Task::priorityOptions();

        // موجودیت مرتبط
        $relatedTarget = $meta['related_target'] ?? null;

        // اگر در meta نبود، از خود task حدس بزن
        if (! $relatedTarget) {
            if ($task->related_type === Task::RELATED_TYPE_USER) {
                $relatedTarget = 'user';
            } elseif ($task->related_type === Task::RELATED_TYPE_CLIENT) {
                $relatedTarget = 'client';
            } else {
                $relatedTarget = 'none';
            }
        }

        $relatedUser = null;
        $relatedClient = null;

        if ($task->related_type === Task::RELATED_TYPE_USER && $task->related_id) {
            $relatedUser = User::find($task->related_id);
        }

        if ($task->related_type === Task::RELATED_TYPE_CLIENT && $task->related_id) {
            $relatedClient = Client::find($task->related_id);
        }

        // برای نمایش roleها در بخش meta
        $allRoles = Role::select('id', 'name')->get();
        $clientStatuses = ClientStatus::active()->get();

        // Load workflow context for SYSTEM tasks safely without hard dependencies
        $workflowInstance = null;
        $treatmentPlan = null;
        $toothContext = $meta['tooth_context'] ?? null;
        $itemContext = $meta['item_context'] ?? null;
        $treatmentPlanId = $meta['treatment_plan_id'] ?? null;

        if ($task->task_type === Task::TYPE_SYSTEM && !empty($meta['workflow_instance_id'])) {
            if (class_exists(\Modules\Workflows\Entities\WorkflowInstance::class)) {
                $workflowInstance = \Modules\Workflows\Entities\WorkflowInstance::with('workflow')
                    ->find($meta['workflow_instance_id']);
            }
            if ($treatmentPlanId && class_exists(\Modules\Booking\App\Models\TreatmentPlan::class)) {
                $treatmentPlan = \Modules\Booking\App\Models\TreatmentPlan::with(['client', 'user'])
                    ->find($treatmentPlanId);
            }
        }

        if (is_string($itemContext)) {
            $itemContext = json_decode($itemContext, true);
        }

        return view('tasks::user.tasks.show', compact(
            'task',
            'types',
            'statuses',
            'priorities',
            'meta',
            'relatedTarget',
            'relatedUser',
            'relatedClient',
            'allRoles',
            'clientStatuses',
            'workflowInstance',
            'treatmentPlan',
            'toothContext',
            'itemContext'
        ));
    }

    public function edit(Task $task)
    {
        $this->authorizeEdit($task);

        // لیست گزینه‌ها از روی مدل Task
        $statuses   = Task::statusOptions();
        $priorities = Task::priorityOptions();
        $types      = Task::typeOptions();

        // کاربران همراه با نقش‌ها (برای فیلتر در multi-select)
        $users = User::query()
            ->select('id', 'name', 'email')
            ->with('roles:id,name') // برای userOptions در view
            ->get();

        // نقش‌ها
        $roles = Role::query()
            ->select('id', 'name')
            ->get();

        // کلاینت‌ها + وضعیتشان
        $clients = Client::query()
            ->select('id', 'full_name', 'phone', 'status_id')
            ->get();

        // وضعیت‌های فعال مشتری
        $clientStatuses = ClientStatus::active()->get();

        // برای view، بهتر است canAssign را هم پاس بدهیم (هرچند خودش هم می‌تواند محاسبه کند)
        $currentUser = auth()->user();
        $canAssign = $currentUser && (
                $currentUser->can('tasks.assign')
                || $currentUser->can('tasks.manage')
                || $currentUser->hasRole('super-admin')
            );

        return view('tasks::user.tasks.edit', compact(
            'task',
            'statuses',
            'priorities',
            'types',
            'users',
            'roles',
            'clients',
            'clientStatuses',
            'canAssign'
        ));
    }

    public function update(Request $request, Task $task)
    {
        // مجوز ویرایش
        $this->authorizeEdit($task);

        // ۱) اعتبارسنجی بر اساس validateRequest (که الآن due_at_view و بقیه رو هم پوشش می‌دهد)
        $data = $this->validateRequest($request, $task);

        $user = auth()->user();

        // ۲) نوع وظیفه (اگر در فرم تغییر کرده باشد - برای تسک‌های سیستمی تغییر داده نمی‌شود)
        $taskType = $task->task_type === Task::TYPE_SYSTEM ? Task::TYPE_SYSTEM : ($data['task_type'] ?? $task->task_type ?? Task::TYPE_GENERAL);

        // ۳) تبدیل تاریخ سررسید:
        //    اولویت با due_at_view (شمسی) است، اگر نبود از due_at (میلادی) استفاده می‌کنیم،
        //    در غیر این صورت مقدار فعلی Task حفظ می‌شود.
        $dueAt = $this->convertJalaliDate(
            $request->input('due_at_view'),
            $request->input('due_time')
        )
            ?? (! empty($data['due_at'])
                ? Carbon::parse($data['due_at'])
                : $task->due_at);


        // ۴) تعیین creator (تغییرش معمولاً منطقی نیست؛ اگر خالی بود، فعلی را می‌گذاریم کاربر جاری)
        $creatorId = $task->creator_id ?: ($user ? $user->id : null);

        // ۵) تعیین مسئول:
        //    از همان منطق resolveAssigneeIds استفاده می‌کنیم و سپس اولین id را روی این Task ست می‌کنیم.
        $assigneeIds = $this->resolveAssigneeIds($data, $request, $taskType, $user);

        $assigneeId = $task->assignee_id; // پیش‌فرض: مسئول فعلی همین Task

        if (! empty($assigneeIds)) {
            // در edit فقط همین Task را ویرایش می‌کنیم → اولین مسئول انتخاب‌شده
            $assigneeId = (int) $assigneeIds[0];
        } elseif (! $assigneeId && $user) {
            // اگر قبلاً مسئول نداشت، حداقل کاربر جاری را مسئول کنیم
            $assigneeId = $user->id;
        }

        // ۶) نوع و لیست موجودیت‌های مرتبط بر اساس فرم
        [$relatedType, $relatedIds] = $this->resolveRelatedEntities($request);

        $finalRelatedType = $task->related_type;
        $finalRelatedId   = $task->related_id;

        if (! empty($relatedType) && ! empty($relatedIds)) {
            // در edit فقط یک موجودیت را روی این Task نگه می‌داریم → اولین id
            $finalRelatedType = $relatedType;
            $finalRelatedId   = (int) $relatedIds[0];
        } elseif ($request->input('related_target', 'none') === 'none') {
            // اگر کاربر «هیچکدام» را انتخاب کرده، ارتباط را پاک می‌کنیم
            $finalRelatedType = null;
            $finalRelatedId   = null;
        }
        // در غیر این صورت، اگر فرم چیزی نفرستاده، مقدار قبلی دست نخورده می‌ماند.

        // ۷) meta جدید از روی ورودی فرم (برای نگهداری تنظیمات پویا)
        $newMeta = [
            'assignee_mode'             => $request->input('assignee_mode', 'single_user'),
            'assignee_role_ids'         => array_values((array) $request->input('assignee_role_ids', [])),

            'related_target'            => $request->input('related_target', 'none'),
            'related_user_role_ids'     => array_values((array) $request->input('related_user_role_ids', [])),
            'related_user_ids'          => array_values((array) $request->input('related_user_ids', [])),
            'related_client_status_ids' => array_values((array) $request->input('related_client_status_ids', [])),
            'related_client_ids'        => array_values((array) $request->input('related_client_ids', [])),
        ];

        // ادغام متادیتای قبلی برای جلوگیری از حذف اطلاعات سیستمی/گردش‌کار
        $meta = array_merge($task->meta ?? [], $newMeta);

        // ۸) خود Task را آپدیت می‌کنیم
        $task->update([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'task_type'    => $taskType,
            'status'       => $data['status'],
            'priority'     => $data['priority'],
            'due_at'       => $dueAt,
            'assignee_id'  => $assigneeId,
            'creator_id'   => $creatorId,
            'related_type' => $finalRelatedType,
            'related_id'   => $finalRelatedId,
            'meta'         => $meta,
        ]);

        return redirect()
            ->route('user.tasks.show', $task)
            ->with('status', 'وظیفه با موفقیت به‌روزرسانی شد.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:tasks,id'],
            'action' => ['required', 'string', 'in:status,delete'],
            'status' => ['required_if:action,status', 'nullable', 'string', 'in:TODO,IN_PROGRESS,DONE,CANCELED'],
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');
        $user = Auth::user();

        // فیلتر وظایف بر اساس دسترسی مشاهده
        $tasksQuery = Task::whereIn('id', $ids);
        
        if (!$user->can('tasks.view.all')) {
            if ($user->can('tasks.view.assigned')) {
                $tasksQuery->where('assignee_id', $user->id);
            } elseif ($user->can('tasks.view.own')) {
                $tasksQuery->where('creator_id', $user->id);
            } else {
                abort(403);
            }
        }

        $tasks = $tasksQuery->get();
        $count = 0;

        if ($action === 'status') {
            $status = $request->input('status');
            foreach ($tasks as $task) {
                if ($user->can('tasks.edit') || $user->can('tasks.manage')) {
                    $task->update(['status' => $status]);
                    $count++;
                }
            }
            return redirect()->back()->with('status', "وضعیت {$count} وظیفه با موفقیت تغییر کرد.");
        } elseif ($action === 'delete') {
            if (!$user->can('tasks.delete')) {
                abort(403);
            }
            foreach ($tasks as $task) {
                $task->delete();
                $count++;
            }
            return redirect()->back()->with('status', "تعداد {$count} وظیفه با موفقیت حذف شد.");
        }

        return redirect()->back();
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
