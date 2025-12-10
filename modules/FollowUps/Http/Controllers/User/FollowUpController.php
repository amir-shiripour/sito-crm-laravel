<?php

namespace Modules\FollowUps\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\FollowUps\Entities\FollowUp;
use Modules\Tasks\Entities\Task;
use Carbon\Carbon;
use Morilog\Jalali\CalendarUtils;
use App\Models\User;
use Modules\Clients\Entities\Client;


class FollowUpController extends Controller
{
    protected function validateRequest(Request $request): array
    {
        $statusKeys   = array_keys(Task::statusOptions());
        $priorityKeys = array_keys(Task::priorityOptions());

        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'assignee_id'  => ['nullable', 'integer', 'exists:users,id'],
            'status'       => ['nullable', 'string', Rule::in($statusKeys)],
            'priority'     => ['nullable', 'string', Rule::in($priorityKeys)],
            'due_at'       => ['nullable', 'date'],
            'due_at_view'  => ['nullable', 'string'],
            'due_time'    => ['nullable', 'string'],
            'related_type' => ['nullable', 'string', 'max:100'],
            'related_id'   => ['nullable', 'integer'],
        ]);
    }

    private function normalizeJalaliDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $latin   = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];

        return str_replace($persian, $latin, $value);
    }
    /**
     * تبدیل تاریخ شمسی (مثلاً 1403/09/15) + ساعت اختیاری (HH:MM) به Carbon میلادی.
     */
    private function convertJalaliDate(?string $jalali, ?string $time = null): ?Carbon
    {
        if (empty($jalali)) {
            return null;
        }

        try {
            // 👈 ارقام را انگلیسی کن
            $jalali = $this->normalizeJalaliDigits(trim($jalali));

            $parts = preg_split('/[^\d]+/', $jalali);
            if (count($parts) < 3) {
                return null;
            }

            [$jy, $jm, $jd] = array_map('intval', array_slice($parts, 0, 3));
            [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);

            [$hour, $minute] = $this->parseTimeString($time);

            return Carbon::create($gy, $gm, $gd, $hour, $minute, 0);
        } catch (\Throwable $e) {
            if (function_exists('logger')) {
                logger()->warning('Failed to convert FollowUp Jalali due_at_view', [
                    'value' => $jalali,
                    'time'  => $time,
                    'error' => $e->getMessage(),
                ]);
            }
            return null;
        }
    }

    private function parseTimeString(?string $time): array
    {
        $hour = 0;
        $minute = 0;

        if ($time !== null) {
            $time = $this->normalizeJalaliDigits(trim($time));
            if ($time !== '') {
                $parts = preg_split('/[^\d]+/', $time);
                if (count($parts) >= 2) {
                    $h = (int) $parts[0];
                    $m = (int) $parts[1];

                    if ($h >= 0 && $h <= 23) {
                        $hour = $h;
                    }
                    if ($m >= 0 && $m <= 59) {
                        $minute = $m;
                    }
                }
            }
        }

        return [$hour, $minute];
    }

    protected function authorizeView(FollowUp $followUp): void
    {
        $user = Auth::user();

        if ($user->can('followups.view.all')) {
            return;
        }

        if ($user->can('followups.view.assigned') && $followUp->assignee_id === $user->id) {
            return;
        }

        if ($user->can('followups.view.own') && $followUp->creator_id === $user->id) {
            return;
        }

        abort(403);
    }

    protected function authorizeEdit(FollowUp $followUp): void
    {
        $user = Auth::user();

        if (! $user->can('followups.edit')) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = FollowUp::query()
            ->with(['assignee', 'creator', 'client'])
            ->orderByDesc('due_at')
            ->orderByDesc('created_at');

        if ($user->can('followups.view.all')) {
            // همه پیگیری‌ها
        } elseif ($user->can('followups.view.assigned')) {
            $query->where('assignee_id', $user->id);
        } elseif ($user->can('followups.view.own')) {
            $query->where('creator_id', $user->id);
        } else {
            abort(403);
        }

        // فیلتر وضعیت
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // فیلتر اولویت
        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        // فیلتر جستجو در عنوان/توضیحات
        if ($q = $request->get('q')) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $perPage   = config('tasks.default_items_per_page', 15);
        $followups = $query->paginate($perPage)->withQueryString();

        $statuses   = Task::statusOptions();
        $priorities = Task::priorityOptions();
        $types      = Task::typeOptions();

        return view('followups::user.followups.index', compact(
            'followups',
            'statuses',
            'priorities',
            'types'
        ));
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('followups.create')) {
            abort(403);
        }

        $statuses   = Task::statusOptions();
        $priorities = Task::priorityOptions();

        // لیست کاربران برای انتخاب مسئول
        $users = \App\Models\User::select('id', 'name', 'email')->get();

        // لیست مشتری‌ها برای انتخاب موجودیت مرتبط
        $clients = \Modules\Clients\Entities\Client::select('id', 'full_name', 'phone')->get();

        // مجوز انتخاب مسئول
        $canAssign = $user->can('followups.manage') || $user->hasRole('super-admin');

        // اگر از صفحه مشتری آمده باشیم
        $relatedType = $request->get('related_type');
        $relatedId   = $request->get('related_id');

        $relatedClient = null;
        if ($relatedType === Task::RELATED_TYPE_CLIENT && $relatedId) {
            $relatedClient = \Modules\Clients\Entities\Client::find($relatedId);
        }

        return view('followups::user.followups.create', compact(
            'statuses',
            'priorities',
            'users',
            'clients',
            'canAssign',
            'relatedType',
            'relatedId',
            'relatedClient'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('followups.create')) {
            abort(403);
        }

        $statusKeys   = array_keys(Task::statusOptions());
        $priorityKeys = array_keys(Task::priorityOptions());

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'assignee_id'  => ['nullable', 'integer', 'exists:users,id'],
            'status'       => ['nullable', 'string', Rule::in($statusKeys)],
            'priority'     => ['nullable', 'string', Rule::in($priorityKeys)],
            'due_at'       => ['nullable', 'date'],
            'due_at_view'  => ['nullable', 'string'],
            'due_time'     => ['nullable', 'string'],
            'related_type' => ['nullable', 'string', 'max:100'],
            'related_id'   => ['nullable', 'integer'],
        ]);

        // تبدیل تاریخ شمسی به میلادی
        $dueAt = $this->convertJalaliDate($data['due_at_view'] ?? null, $request->input('due_time'))
            ?? (! empty($data['due_at']) ? Carbon::parse($data['due_at']) : null);


        // منطق تعیین مسئول
        $canAssign = $user->can('tasks.assign')
            || $user->can('tasks.manage')
            || $user->hasRole('super-admin')
            || $user->can('followups.manage');

        $assigneeId = $data['assignee_id'] ?? null;

        if (! $canAssign || empty($assigneeId)) {
            $assigneeId = $user->id;
        }

        $followUp = FollowUp::create([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'task_type'    => Task::TYPE_FOLLOW_UP,
            'assignee_id'  => $assigneeId,
            'creator_id'   => $user->id,
            'status'       => $data['status'] ?? Task::STATUS_TODO,
            'priority'     => $data['priority'] ?? Task::PRIORITY_MEDIUM,
            'due_at'       => $dueAt,
            'related_type' => $data['related_type'] ?? null,
            'related_id'   => $data['related_id'] ?? null,
        ]);

        // هوک created در مدل Task (پدر FollowUp) خودش Reminder می‌سازد
        return redirect()
            ->route('user.followups.show', $followUp)
            ->with('status', 'پیگیری با موفقیت ایجاد شد.');
    }

    public function show(FollowUp $followUp)
    {
        $this->authorizeView($followUp);

        $followUp->load(['assignee', 'creator', 'client']);

        $statuses   = Task::statusOptions();
        $priorities = Task::priorityOptions();
        $types      = Task::typeOptions();

        return view('followups::user.followups.show', compact(
            'followUp',
            'statuses',
            'priorities',
            'types'
        ));
    }

    public function quickStore(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('followups.create')) {
            abort(403);
        }

        $statusKeys   = array_keys(Task::statusOptions());
        $priorityKeys = array_keys(Task::priorityOptions());

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'assignee_id'  => ['nullable', 'integer', 'exists:users,id'],
            'status'       => ['nullable', 'string', Rule::in($statusKeys)],
            'priority'     => ['nullable', 'string', Rule::in($priorityKeys)],
            'due_at_view'  => ['nullable', 'string'],
            'due_time'     => ['nullable', 'string'],
            'client_id'    => ['required', 'integer', 'exists:clients,id'],
        ]);

        // تبدیل تاریخ شمسی
        $dueAt = $this->convertJalaliDate($data['due_at_view'] ?? null, $request->input('due_time'));

        // منطق تعیین مسئول مثل store
        $canAssign = $user->can('tasks.assign')
            || $user->can('tasks.manage')
            || $user->hasRole('super-admin')
            || $user->can('followups.manage');

        $assigneeId = $data['assignee_id'] ?? null;

        if (! $canAssign || empty($assigneeId)) {
            $assigneeId = $user->id;
        }

        $followUp = FollowUp::create([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'task_type'    => Task::TYPE_FOLLOW_UP,
            'assignee_id'  => $assigneeId,
            'creator_id'   => $user->id,
            'status'       => $data['status'] ?? Task::STATUS_TODO,
            'priority'     => $data['priority'] ?? Task::PRIORITY_MEDIUM,
            'due_at'       => $dueAt,
            'related_type' => Task::RELATED_TYPE_CLIENT,
            'related_id'   => $data['client_id'],
        ]);

        // مدل Task خودش Reminder می‌سازد (autoCreateReminderIfPossible)

        if ($request->expectsJson()) {
            return response()->json([
                'message'   => 'پیگیری با موفقیت ثبت شد.',
                'followup_id' => $followUp->id,
            ], 201);
        }

        return back()->with('status', 'پیگیری با موفقیت ثبت شد.');
    }

    public function edit(FollowUp $followUp)
    {
        $this->authorizeEdit($followUp);

        $user = Auth::user();

        // همان optionها مثل Task
        $statuses   = Task::statusOptions();
        $priorities = Task::priorityOptions();

        // لیست کاربران برای انتخاب مسئول
        $users = \App\Models\User::select('id', 'name', 'email')->get();

        // لیست مشتری‌ها برای موجودیت مرتبط
        $clients = \Modules\Clients\Entities\Client::select('id', 'full_name', 'phone')->get();

        // مجوز انتخاب/تغییر مسئول
        $canAssign = $user->can('followups.manage') || $user->hasRole('super-admin');

        // اگر این پیگیری به یک Client وصل باشد
        $relatedClient = null;
        if ($followUp->related_type === Task::RELATED_TYPE_CLIENT && $followUp->related_id) {
            $relatedClient = \Modules\Clients\Entities\Client::find($followUp->related_id);
        }

        return view('followups::user.followups.edit', compact(
            'followUp',
            'statuses',
            'priorities',
            'users',
            'clients',
            'canAssign',
            'relatedClient'
        ));
    }

    public function update(Request $request, FollowUp $followUp)
    {
        $this->authorizeEdit($followUp);

        $data = $this->validateRequest($request);

        $user = Auth::user();

        // مجوز انتخاب/تغییر مسئول
        $canAssign = $user->can('followups.manage') || $user->hasRole('super-admin');

        // اگر مجوز مدیریت دارد و چیزی انتخاب شده، از همان استفاده کن
        // اگر نه، همان مسئول قبلی یا خود کاربر فعلی
        $assigneeId = $canAssign && !empty($data['assignee_id'])
            ? (int) $data['assignee_id']
            : ($followUp->assignee_id ?: $user->id);

        // تبدیل تاریخ شمسی (due_at_view) به میلادی
        $dueAt = $this->convertJalaliDate(
            $request->input('due_at_view'),
            $request->input('due_time')
        )
            ?? (!empty($data['due_at']) ? Carbon::parse($data['due_at']) : $followUp->due_at);


        $followUp->fill([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'assignee_id' => $assigneeId,
            'status'      => $data['status'] ?? $followUp->status,
            'priority'    => $data['priority'] ?? $followUp->priority,
            'due_at'      => $dueAt,
        ]);

        // موجودیت مرتبط: همیشه CLIENT (طبق خواسته‌ات)
        $followUp->related_type = Task::RELATED_TYPE_CLIENT;
        $followUp->related_id   = $data['related_id'] ?? $followUp->related_id;

        // اگر پیگیری به وضعیت Done/Cancelled رفت و completed_at نداشت، ست کن
        if (in_array($followUp->status, [Task::STATUS_DONE, Task::STATUS_CANCELED], true) && ! $followUp->completed_at) {
            $followUp->completed_at = now();
        }

        $followUp->save();

        return redirect()
            ->route('user.followups.show', $followUp)
            ->with('status', 'پیگیری با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(FollowUp $followUp)
    {
        $this->authorizeEdit($followUp);

        $followUp->delete();

        return redirect()
            ->route('user.followups.index')
            ->with('status', 'پیگیری حذف شد.');
    }
}
