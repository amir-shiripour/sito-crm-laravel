<?php

namespace Modules\FollowUps\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\FollowUps\Entities\FollowUp;
use Modules\Tasks\Entities\Task;

class FollowUpController extends Controller
{
    protected function validateRequest(Request $request): array
    {
        $statusKeys   = array_keys(config('tasks.statuses', []));
        $priorityKeys = array_keys(config('tasks.priorities', []));

        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'assignee_id'  => ['nullable', 'integer', 'exists:users,id'],
            'status'       => ['nullable', 'string', Rule::in($statusKeys)],
            'priority'     => ['nullable', 'string', Rule::in($priorityKeys)],
            'due_at'       => ['nullable', 'date'],
            'related_type' => ['nullable', 'string', 'max:100'],
            'related_id'   => ['nullable', 'integer'],
        ]);
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

        $followUp = FollowUp::create([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'task_type'    => Task::TYPE_FOLLOW_UP,
            'assignee_id'  => $data['assignee_id'] ?? null,
            'creator_id'   => $user->id,
            'status'       => $data['status'] ?? Task::STATUS_TODO,
            'priority'     => $data['priority'] ?? Task::PRIORITY_MEDIUM,
            'due_at'       => $data['due_at'] ?? null,
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
