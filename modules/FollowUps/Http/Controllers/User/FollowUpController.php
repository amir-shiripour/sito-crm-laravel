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
            'due_at_view'  => ['nullable', 'string'], // تاریخ شمسی از فرم‌های کلاینت
            'related_type' => ['nullable', 'string', 'max:100'],
            'related_id'   => ['nullable', 'integer'],
        ]);
    }

    /**
     * تبدیل تاریخ شمسی (مثلاً 1403/09/15) به Carbon میلادی.
     */
    private function convertJalaliDate(?string $jalali): ?Carbon
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

            return Carbon::createFromDate($gy, $gm, $gd)->startOfDay();
        } catch (\Throwable $e) {
            if (function_exists('logger')) {
                logger()->warning('Failed to convert FollowUp Jalali due_at_view', [
                    'value' => $jalali,
                    'error' => $e->getMessage(),
                ]);
            }
            return null;
        }
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
            ->with(['assignee', 'creator'])
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

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($relatedType = $request->get('related_type')) {
            $query->where('related_type', $relatedType);
        }

        if ($relatedId = $request->get('related_id')) {
            $query->where('related_id', $relatedId);
        }


        $perPage   = config('tasks.default_items_per_page', 15);
        $followups = $query->paginate($perPage)->withQueryString();

        return view('followups::user.followups.index', compact('followups'));
    }

    public function create()
    {
        $user = Auth::user();

        if (! $user->can('followups.create')) {
            abort(403);
        }

        $statuses   = array_keys(config('tasks.statuses', []));
        $priorities = array_keys(config('tasks.priorities', []));

        return view('followups::user.followups.create', compact('statuses', 'priorities'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('followups.create')) {
            abort(403);
        }

        $data = $this->validateRequest($request);

        // تاریخ سررسید: اولویت با due_at_view (شمسی)، بعد due_at میلادی
        $dueAt = $this->convertJalaliDate($data['due_at_view'] ?? null)
            ?? (! empty($data['due_at']) ? Carbon::parse($data['due_at']) : null);

        $followUp = FollowUp::create([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'task_type'    => Task::TYPE_FOLLOW_UP,
            'assignee_id'  => $data['assignee_id'] ?? $user->id,
            'creator_id'   => $user->id,
            'status'       => $data['status'] ?? Task::STATUS_TODO,
            'priority'     => $data['priority'] ?? Task::PRIORITY_MEDIUM,
            'due_at'       => $dueAt,
            'related_type' => $data['related_type'] ?? null,
            'related_id'   => $data['related_id'] ?? null,
        ]);

        return redirect()
            ->route('user.followups.show', $followUp)
            ->with('status', 'پیگیری با موفقیت ایجاد شد.');
    }


    public function show(FollowUp $followUp)
    {
        $this->authorizeView($followUp);

        $followUp->load(['assignee', 'creator']);

        return view('followups::user.followups.show', compact('followUp'));
    }

    public function edit(FollowUp $followUp)
    {
        $this->authorizeEdit($followUp);

        $statuses   = array_keys(config('tasks.statuses', []));
        $priorities = array_keys(config('tasks.priorities', []));

        return view('followups::user.followups.edit', compact('followUp', 'statuses', 'priorities'));
    }

    public function update(Request $request, FollowUp $followUp)
    {
        $this->authorizeEdit($followUp);

        $data = $this->validateRequest($request);

        $followUp->fill([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'assignee_id'  => $data['assignee_id'] ?? $followUp->assignee_id,
            'status'       => $data['status'] ?? $followUp->status,
            'priority'     => $data['priority'] ?? $followUp->priority,
            'due_at'       => $data['due_at'] ?? $followUp->due_at,
            'related_type' => $data['related_type'] ?? $followUp->related_type,
            'related_id'   => $data['related_id'] ?? $followUp->related_id,
        ]);

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
