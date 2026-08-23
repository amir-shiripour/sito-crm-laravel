<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectChecklistItem;
use Modules\Projects\App\Http\Models\ProjectTask;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectStatus;
use Modules\Projects\App\Services\ProjectsService;
use Modules\Projects\App\Traits\HandlesJalaliDate;

class ProjectsChecklistController extends Controller
{
    use HandlesJalaliDate;

    public function __construct(private ProjectsService $projectsSvc)
    {
    }

    /**
     * Get a single checklist item with comments and assignee
     */
    public function show(Project $project, ProjectTask $task, ProjectChecklistItem $item)
    {
        $this->authorize('view', $project);

        $item->load(['assignees', 'assignee', 'status', 'creator', 'comments.user']);
        $data = $item->toArray();
        $assignedIds = $item->assignees->pluck('id')->toArray();
        if (empty($assignedIds) && $item->assigned_to) {
            $assignedIds = [(int)$item->assigned_to];
        }
        $data['assigned_to'] = $assignedIds;
        $data['assignees'] = $item->assignees->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'is_done' => (bool)$u->pivot->is_done,
                'completed_at' => $u->pivot->completed_at,
            ];
        });
        $data['due_date_jalali'] = $item->due_date && function_exists('jdate')
            ? jdate($item->due_date)->format('Y/m/d')
            : ($item->due_date ? $item->due_date->format('Y/m/d') : '');
        $data['task_due_date_jalali'] = $task->due_date && function_exists('jdate')
            ? jdate($task->due_date)->format('Y/m/d')
            : ($task->due_date ? $task->due_date->format('Y/m/d') : '');
        $data['project_due_date_jalali'] = $project->end_date && function_exists('jdate')
            ? jdate($project->end_date)->format('Y/m/d')
            : ($project->end_date ? $project->end_date->format('Y/m/d') : '');

        return response()->json($data);
    }

    public function toggle(Project $project, ProjectTask $task, ProjectChecklistItem $item)
    {
        $this->authorize('changeTaskStatus', $project);

        if ($task->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این گروه لغو شده است و امکان تغییر کارهای آن وجود ندارد.',
            ], 422);
        }

        if ($item->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این کار لغو شده است و امکان تغییر وضعیت آن وجود ندارد.',
            ], 422);
        }

        $userId = auth()->id();
        $item->load('assignees');
        $assignees = $item->assignees;
        $isAssignee = $assignees->contains('id', $userId);

        if ($assignees->count() > 1 && $isAssignee) {
            // Multiple assignees and current user is one of the assignees
            $myPivot = $assignees->firstWhere('id', $userId)->pivot;
            $myNewDoneState = !$myPivot->is_done;

            $item->assignees()->updateExistingPivot($userId, [
                'is_done' => $myNewDoneState,
                'completed_at' => $myNewDoneState ? now() : null,
            ]);

            $freshAssignees = $item->assignees()->get();
            $allDone = $freshAssignees->every(fn($u) => (bool)$u->pivot->is_done);
            $anyDone = $freshAssignees->contains(fn($u) => (bool)$u->pivot->is_done);

            if ($allDone) {
                $completedStatus = ProjectStatus::completedFor('checklist');
                $item->update([
                    'is_done' => true,
                    'status_id' => $completedStatus?->id,
                    'completed_at' => now(),
                ]);
            } else {
                $item->update([
                    'is_done' => false,
                    'completed_at' => null,
                ]);

                $isPastDue = $item->due_date && $item->due_date->isPast() && !$item->due_date->isToday();
                if ($isPastDue) {
                    $delayedStatus = ProjectStatus::delayedFor('checklist');
                    $item->update(['status_id' => $delayedStatus?->id]);
                } elseif ($item->status?->isCompleted()) {
                    $inProgressStatus = $anyDone
                        ? ProjectStatus::inProgressFor('checklist')
                        : (ProjectStatus::defaultFor('checklist') ?? ProjectStatus::queuedFor('checklist'));
                    $item->update(['status_id' => $inProgressStatus?->id]);
                } elseif (!$item->status || $item->status->isQueued()) {
                    if ($anyDone) {
                        $inProgressStatus = ProjectStatus::inProgressFor('checklist');
                        if ($inProgressStatus) {
                            $item->update(['status_id' => $inProgressStatus->id]);
                        }
                    }
                }
            }
        } else {
            // Single assignee, 0 assignees, or manager/superadmin direct action
            $newDoneState = !$item->is_done;
            $statusId = null;

            if ($newDoneState) {
                $completedStatus = ProjectStatus::completedFor('checklist');
                $statusId = $completedStatus?->id;
            } else {
                $isPastDue = $item->due_date && $item->due_date->isPast() && !$item->due_date->isToday();
                if ($isPastDue) {
                    $delayedStatus = ProjectStatus::delayedFor('checklist');
                    $statusId = $delayedStatus?->id;
                } else {
                    $inProgressStatus = ProjectStatus::inProgressFor('checklist');
                    $queuedStatus = ProjectStatus::queuedFor('checklist');
                    $statusId = $item->status?->isInProgress() ? ($inProgressStatus?->id ?? $queuedStatus?->id) : ($queuedStatus?->id ?? ProjectStatus::defaultFor('checklist')?->id);
                }
            }

            if ($assignees->isNotEmpty()) {
                foreach ($assignees as $assignee) {
                    $item->assignees()->updateExistingPivot($assignee->id, [
                        'is_done' => $newDoneState,
                        'completed_at' => $newDoneState ? now() : null,
                    ]);
                }
            }

            $item->update([
                'is_done' => $newDoneState,
                'status_id' => $statusId,
                'completed_at' => $newDoneState ? now() : null,
            ]);
        }

        $task->syncStatusFromChecklist();

        $freshItem = $item->fresh(['status', 'assignees', 'assignee']);
        $freshTask = $task->fresh(['status', 'assignee']);

        ProjectsTaskSseController::broadcastEvent($project->id, 'checklist_item_toggled', [
            'task_id' => $task->id,
            'item_id' => $freshItem->id,
            'item' => [
                'id' => $freshItem->id,
                'title' => $freshItem->title,
                'is_done' => (bool)$freshItem->is_done,
                'is_canceled' => $freshItem->isCanceled(),
                'is_overdue' => $freshItem->isOverdue(),
                'status_id' => $freshItem->status_id,
                'assignees_completed_count' => $freshItem->assigneesCompletedCount(),
                'assignees_total_count' => $freshItem->assigneesTotalCount(),
                'assignees' => $freshItem->assignees->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'is_done' => (bool)$u->pivot->is_done,
                ])->values()->all(),
                'status' => $freshItem->status ? [
                    'id' => $freshItem->status->id,
                    'name' => $freshItem->status->name,
                    'color' => $freshItem->status->color,
                ] : null,
            ],
            'task' => [
                'id' => $freshTask->id,
                'status_id' => $freshTask->status_id,
                'completed_at' => $freshTask->completed_at,
                'status' => $freshTask->status ? [
                    'id' => $freshTask->status->id,
                    'name' => $freshTask->status->name,
                    'color' => $freshTask->status->color,
                    'is_completed' => $freshTask->status->isCompleted(),
                ] : null,
            ],
            'task_progress' => $task->checklistProgress(),
            'checklist_done' => $task->completedChecklistCount(),
            'checklist_total' => $task->activeChecklistCount(),
            'triggered_by_user_id' => $userId,
        ]);

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $freshItem->id,
                'title' => $freshItem->title,
                'is_done' => (bool)$freshItem->is_done,
                'is_canceled' => $freshItem->isCanceled(),
                'is_overdue' => $freshItem->isOverdue(),
                'status_id' => $freshItem->status_id,
                'is_completed_by_me' => $freshItem->isCompletedBy($userId),
                'assignees_completed_count' => $freshItem->assigneesCompletedCount(),
                'assignees_total_count' => $freshItem->assigneesTotalCount(),
                'assignees' => $freshItem->assignees->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'is_done' => (bool)$u->pivot->is_done,
                ]),
                'status' => $freshItem->status ? [
                    'id' => $freshItem->status->id,
                    'name' => $freshItem->status->name,
                    'color' => $freshItem->status->color,
                ] : null,
            ],
            'task' => $freshTask,
            'task_progress' => $task->checklistProgress(),
            'checklist_done' => $task->completedChecklistCount(),
            'checklist_total' => $task->activeChecklistCount(),
            'dashboard_stats' => $this->projectsSvc->dashboardStats($project->fresh()),
        ]);
    }

    public function cancel(Project $project, ProjectTask $task, ProjectChecklistItem $item)
    {
        $this->authorize('cancelTasks', $project);

        if ($task->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این گروه لغو شده است و امکان تغییر وضعیت کارهای آن وجود ندارد.',
            ], 422);
        }

        if ($item->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این کار قبلاً لغو شده است.',
            ], 422);
        }

        $canceledStatus = ProjectStatus::canceledFor('checklist');
        if (!$canceledStatus) {
            return response()->json([
                'success' => false,
                'message' => 'وضعیت «لغو شده» برای کارها در سیستم یافت نشد.',
            ], 422);
        }

        $item->update([
            'status_id' => $canceledStatus->id,
            'is_done' => false,
            'completed_at' => null,
        ]);

        ProjectActivity::log(
            projectId: $project->id,
            action: 'task.canceled',
            subject: "لغو کار «{$item->title}» در گروه «{$task->title}»",
            taskId: $task->id,
            userId: auth()->id()
        );

        $task->syncStatusFromChecklist();

        ProjectsTaskSseController::broadcastEvent($project->id, 'checklist_updated', [
            'task_id' => $task->id,
            'item_id' => $item->id,
            'action' => 'canceled',
            'triggered_by_user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'کار با موفقیت لغو شد.',
            'item' => $item->fresh(['status', 'assignee']),
            'task' => $task->fresh(['status', 'assignee']),
            'task_progress' => $task->checklistProgress(),
            'checklist_done' => $task->completedChecklistCount(),
            'checklist_total' => $task->activeChecklistCount(),
            'dashboard_stats' => $this->projectsSvc->dashboardStats($project->fresh()),
        ]);
    }

    public function updateStatus(Request $request, Project $project, ProjectTask $task, ProjectChecklistItem $item)
    {
        $this->authorize('changeTaskStatus', $project);

        if ($task->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این گروه لغو شده است و امکان تغییر وضعیت آن وجود ندارد.',
            ], 422);
        }

        if ($item->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این کار لغو شده است و امکان تغییر وضعیت آن وجود ندارد.',
            ], 422);
        }

        $request->validate([
            'status_id' => 'required|exists:projects_statuses,id',
        ]);

        $status = ProjectStatus::find($request->status_id);
        $isDone = $status?->isCompleted() ?? $item->is_done;

        $item->update([
            'status_id' => $request->status_id,
            'is_done' => $isDone,
            'completed_at' => $isDone ? ($item->completed_at ?? now()) : null,
        ]);

        $task->syncStatusFromChecklist();

        $freshItem = $item->fresh(['status', 'assignees', 'assignee']);
        $freshTask = $task->fresh(['status', 'assignee']);

        ProjectsTaskSseController::broadcastEvent($project->id, 'checklist_item_toggled', [
            'task_id' => $task->id,
            'item_id' => $freshItem->id,
            'item' => [
                'id' => $freshItem->id,
                'title' => $freshItem->title,
                'is_done' => (bool)$freshItem->is_done,
                'is_canceled' => $freshItem->isCanceled(),
                'is_overdue' => $freshItem->isOverdue(),
                'status_id' => $freshItem->status_id,
                'assignees_completed_count' => $freshItem->assigneesCompletedCount(),
                'assignees_total_count' => $freshItem->assigneesTotalCount(),
                'assignees' => $freshItem->assignees->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'is_done' => (bool)$u->pivot->is_done,
                ])->values()->all(),
                'status' => $freshItem->status ? [
                    'id' => $freshItem->status->id,
                    'name' => $freshItem->status->name,
                    'color' => $freshItem->status->color,
                ] : null,
            ],
            'task' => [
                'id' => $freshTask->id,
                'status_id' => $freshTask->status_id,
                'completed_at' => $freshTask->completed_at,
                'status' => $freshTask->status ? [
                    'id' => $freshTask->status->id,
                    'name' => $freshTask->status->name,
                    'color' => $freshTask->status->color,
                    'is_completed' => $freshTask->status->isCompleted(),
                ] : null,
            ],
            'task_progress' => $task->checklistProgress(),
            'checklist_done' => $task->completedChecklistCount(),
            'checklist_total' => $task->activeChecklistCount(),
            'triggered_by_user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'item' => $freshItem,
            'task' => $freshTask,
            'task_progress' => $task->checklistProgress(),
            'checklist_done' => $task->completedChecklistCount(),
            'checklist_total' => $task->totalChecklistCount(),
            'dashboard_stats' => $this->projectsSvc->dashboardStats($project->fresh()),
        ]);
    }

    public function store(Request $request, Project $project, ProjectTask $task)
    {
        $this->authorize('manageTasks', $project);

        if ($task->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این گروه لغو شده است و امکان افزودن کار به آن وجود ندارد.',
            ], 422);
        }

        $rawAssignedTo = $request->input('assigned_to');
        if (is_numeric($rawAssignedTo)) {
            $request->merge(['assigned_to' => [(int)$rawAssignedTo]]);
        } elseif (is_string($rawAssignedTo)) {
            $decoded = json_decode($rawAssignedTo, true);
            if (is_array($decoded)) {
                $request->merge(['assigned_to' => $decoded]);
            } else {
                $request->merge(['assigned_to' => array_filter(array_map('intval', explode(',', $rawAssignedTo)))]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|string',
            'is_done' => 'nullable|boolean',
            'status_id' => 'nullable|exists:projects_statuses,id',
            'assigned_to' => 'required|array|min:1',
            'assigned_to.*' => 'integer|exists:users,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ], [
            'assigned_to.required' => 'انتخاب حداقل یک مسئول کار الزامی است.',
            'assigned_to.min' => 'انتخاب حداقل یک مسئول کار الزامی است.',
            'title.required' => 'وارد کردن عنوان کار الزامی است.',
        ]);

        $inProgressStatus = ProjectStatus::inProgressFor('checklist')
            ?? ProjectStatus::defaultFor('checklist');
        $statusId = $inProgressStatus?->id;
        $isDone = false;

        $dueDate = $this->convertJalaliDate($validated['due_date'] ?? null);

        if (!empty($dueDate)) {
            if ($task->due_date && strtotime($dueDate) > strtotime($task->due_date->format('Y-m-d'))) {
                $taskDueDateJalali = function_exists('jdate') ? jdate($task->due_date)->format('Y/m/d') : $task->due_date->format('Y/m/d');
                return response()->json([
                    'success' => false,
                    'message' => "تاریخ سررسید کار نمی‌تواند پس از تاریخ سررسید گروه ({$taskDueDateJalali}) باشد.",
                    'errors' => ['due_date' => ["تاریخ سررسید کار نمی‌تواند پس از تاریخ سررسید گروه ({$taskDueDateJalali}) باشد."]]
                ], 422);
            }

            if ($project->end_date && strtotime($dueDate) > strtotime($project->end_date->format('Y-m-d'))) {
                $projectEndDateJalali = function_exists('jdate') ? jdate($project->end_date)->format('Y/m/d') : $project->end_date->format('Y/m/d');
                return response()->json([
                    'success' => false,
                    'message' => "تاریخ سررسید کار نمی‌تواند پس از تاریخ پایان پروژه ({$projectEndDateJalali}) باشد.",
                    'errors' => ['due_date' => ["تاریخ سررسید کار نمی‌تواند پس از تاریخ پایان پروژه ({$projectEndDateJalali}) باشد."]]
                ], 422);
            }
        }

        $sortOrder = (int)$task->checklistItems()->max('sort_order') + 1;
        $firstAssignee = !empty($validated['assigned_to']) ? (int)$validated['assigned_to'][0] : null;

        $item = $task->checklistItems()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'assigned_to' => $firstAssignee,
            'created_by' => auth()->id(),
            'priority' => $validated['priority'] ?? 'normal',
            'status_id' => $statusId,
            'is_done' => $isDone,
            'due_date' => $dueDate,
            'sort_order' => $sortOrder,
            'completed_at' => $isDone ? now() : null,
        ]);

        $item->syncAssignees($validated['assigned_to']);

        ProjectActivity::log(
            projectId: $project->id,
            action: 'task.created',
            subject: "افزودن کار «{$item->title}» به گروه «{$task->title}»",
            taskId: $task->id,
            userId: auth()->id()
        );

        $task->syncStatusFromChecklist();

        ProjectsTaskSseController::broadcastEvent($project->id, 'checklist_updated', [
            'task_id' => $task->id,
            'item_id' => $item->id,
            'action' => 'created',
            'triggered_by_user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'item' => $item->load(['status', 'assignees', 'assignee']),
            'task' => $task->fresh(['status', 'assignee']),
            'task_progress' => $task->checklistProgress(),
            'checklist_done' => $task->completedChecklistCount(),
            'checklist_total' => $task->activeChecklistCount(),
            'dashboard_stats' => $this->projectsSvc->dashboardStats($project->fresh()),
        ], 201);
    }

    public function update(Request $request, Project $project, ProjectTask $task, ProjectChecklistItem $item)
    {
        $this->authorize('manageTasks', $project);

        if ($item->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این کار لغو شده است و امکان ویرایش آن وجود ندارد.',
            ], 422);
        }

        $rawAssignedTo = $request->input('assigned_to');
        if (is_numeric($rawAssignedTo)) {
            $request->merge(['assigned_to' => [(int)$rawAssignedTo]]);
        } elseif (is_string($rawAssignedTo)) {
            $decoded = json_decode($rawAssignedTo, true);
            if (is_array($decoded)) {
                $request->merge(['assigned_to' => $decoded]);
            } else {
                $request->merge(['assigned_to' => array_filter(array_map('intval', explode(',', $rawAssignedTo)))]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|string',
            'is_done' => 'nullable|boolean',
            'assigned_to' => 'required|array|min:1',
            'assigned_to.*' => 'integer|exists:users,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'status_id' => 'nullable|exists:projects_statuses,id',
        ], [
            'assigned_to.required' => 'انتخاب حداقل یک مسئول کار الزامی است.',
            'assigned_to.min' => 'انتخاب حداقل یک مسئول کار الزامی است.',
            'title.required' => 'وارد کردن عنوان کار الزامی است.',
        ]);

        $statusId = $validated['status_id'] ?? $item->status_id;
        if (!empty($validated['status_id'])) {
            $statusObj = ProjectStatus::find($statusId);
            $isDone = $statusObj?->isCompleted() ?? false;
        } elseif (isset($validated['is_done'])) {
            $isDone = (bool)$validated['is_done'];
            if ($isDone) {
                $statusId = ProjectStatus::completedFor('checklist')?->id ?? $statusId;
            } else {
                if ($item->isOverdue()) {
                    $statusId = ProjectStatus::delayedFor('checklist')?->id ?? $statusId;
                } else {
                    $statusId = ProjectStatus::defaultFor('checklist')?->id ?? ProjectStatus::queuedFor('checklist')?->id;
                }
            }
        } else {
            $isDone = $item->is_done;
        }

        $dueDate = array_key_exists('due_date', $validated)
            ? $this->convertJalaliDate($validated['due_date'])
            : $item->due_date?->format('Y-m-d');

        if (!empty($dueDate)) {
            if ($task->due_date && strtotime($dueDate) > strtotime($task->due_date->format('Y-m-d'))) {
                $taskDueDateJalali = function_exists('jdate') ? jdate($task->due_date)->format('Y/m/d') : $task->due_date->format('Y/m/d');
                return response()->json([
                    'success' => false,
                    'message' => "تاریخ سررسید کار نمی‌تواند پس از تاریخ سررسید گروه ({$taskDueDateJalali}) باشد.",
                    'errors' => ['due_date' => ["تاریخ سررسید کار نمی‌تواند پس از تاریخ سررسید گروه ({$taskDueDateJalali}) باشد."]]
                ], 422);
            }

            if ($project->end_date && strtotime($dueDate) > strtotime($project->end_date->format('Y-m-d'))) {
                $projectEndDateJalali = function_exists('jdate') ? jdate($project->end_date)->format('Y/m/d') : $project->end_date->format('Y/m/d');
                return response()->json([
                    'success' => false,
                    'message' => "تاریخ سررسید کار نمی‌تواند پس از تاریخ پایان پروژه ({$projectEndDateJalali}) باشد.",
                    'errors' => ['due_date' => ["تاریخ سررسید کار نمی‌تواند پس از تاریخ پایان پروژه ({$projectEndDateJalali}) باشد."]]
                ], 422);
            }
        }

        $firstAssignee = !empty($validated['assigned_to']) ? (int)$validated['assigned_to'][0] : null;

        $item->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? $item->description,
            'assigned_to' => $firstAssignee,
            'priority' => $validated['priority'] ?? $item->priority,
            'status_id' => $statusId,
            'is_done' => $isDone,
            'due_date' => $dueDate,
            'completed_at' => $isDone ? ($item->completed_at ?? now()) : null,
        ]);

        $item->syncAssignees($validated['assigned_to']);

        $task->syncStatusFromChecklist();

        ProjectsTaskSseController::broadcastEvent($project->id, 'checklist_updated', [
            'task_id' => $task->id,
            'item_id' => $item->id,
            'action' => 'updated',
            'triggered_by_user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'item' => $item->fresh(['status', 'assignees', 'assignee']),
            'task' => $task->fresh(['status', 'assignee']),
            'task_progress' => $task->checklistProgress(),
            'checklist_done' => $task->completedChecklistCount(),
            'checklist_total' => $task->activeChecklistCount(),
            'dashboard_stats' => $this->projectsSvc->dashboardStats($project->fresh()),
        ]);
    }

    public function destroy(Project $project, ProjectTask $task, ProjectChecklistItem $item)
    {
        $this->authorize('deleteTasks', $project);

        if ($task->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این گروه لغو شده است و امکان حذف کارهای آن وجود ندارد.',
            ], 422);
        }

        $itemId = $item->id;
        $title = $item->title;
        $item->delete();

        ProjectActivity::log(
            projectId: $project->id,
            action: 'task.deleted',
            subject: "حذف کار «{$title}» از گروه «{$task->title}»",
            taskId: $task->id,
            userId: auth()->id()
        );

        $task->syncStatusFromChecklist();

        ProjectsTaskSseController::broadcastEvent($project->id, 'checklist_updated', [
            'task_id' => $task->id,
            'item_id' => $itemId,
            'action' => 'deleted',
            'triggered_by_user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->fresh(['status', 'assignee']),
            'task_progress' => $task->checklistProgress(),
            'checklist_done' => $task->completedChecklistCount(),
            'checklist_total' => $task->totalChecklistCount(),
            'dashboard_stats' => $this->projectsSvc->dashboardStats($project->fresh()),
        ]);
    }

    public function reorder(Request $request, Project $project, ProjectTask $task)
    {
        $this->authorize('manageTasks', $project);

        $request->validate(['ids' => 'required|array']);

        foreach ($request->ids as $position => $id) {
            ProjectChecklistItem::where('id', $id)
                ->where('task_id', $task->id)
                ->update(['sort_order' => $position]);
        }

        ProjectsTaskSseController::broadcastEvent($project->id, 'checklist_updated', [
            'task_id' => $task->id,
            'action' => 'reordered',
            'triggered_by_user_id' => auth()->id(),
        ]);

        return response()->json(['ok' => true]);
    }
}
