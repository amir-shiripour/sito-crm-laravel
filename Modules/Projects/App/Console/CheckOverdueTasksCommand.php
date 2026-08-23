<?php

namespace Modules\Projects\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Projects\App\Http\Controllers\ProjectsTaskSseController;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectChecklistItem;
use Modules\Projects\App\Http\Models\ProjectStatus;
use Modules\Projects\App\Http\Models\ProjectTask;

class CheckOverdueTasksCommand extends Command
{
    protected $signature = 'projects:check-overdue-tasks';
    protected $description = 'Check and update overdue projects, task groups, and work items to delayed status (تعویق)';

    public function handle(): int
    {
        $projectCount = 0;
        $taskCount = 0;
        $itemCount = 0;
        $delayedProjectStatus = ProjectStatus::delayedFor('project');
        if ($delayedProjectStatus) {
            $allProjectStatuses = ProjectStatus::where('type', 'project')->get();
            $ignoredProjectStatusIds = $allProjectStatuses
                ->filter(fn($s) => $s->isCompleted() || $s->isCanceled() || $s->isDelayed())
                ->pluck('id');

            $overdueProjects = Project::query()
                ->where('is_template', false)
                ->whereNotNull('end_date')
                ->whereDate('end_date', '<', now()->toDateString())
                ->where(function ($q) use ($ignoredProjectStatusIds) {
                    $q->whereNull('status_id')
                        ->orWhereNotIn('status_id', $ignoredProjectStatusIds);
                })
                ->get();

            foreach ($overdueProjects as $project) {
                try {
                    if ($project->isCanceled() || $project->isCompleted() || $project->isDelayed()) {
                        continue;
                    }

                    $project->update([
                        'status_id' => $delayedProjectStatus->id,
                    ]);

                    ProjectActivity::log(
                        projectId: $project->id,
                        action: 'project.overdue',
                        subject: "وضعیت پروژه به دلیل گذشتن از تاریخ پایان پروژه به «{$delayedProjectStatus->name}» تغییر یافت."
                    );

                    $projectCount++;
                } catch (\Throwable $e) {
                    Log::error('[Projects] CheckOverdueTasks error for project ' . $project->id, [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $delayedTaskStatus = ProjectStatus::delayedFor('task');
        if ($delayedTaskStatus) {
            $allTaskStatuses = ProjectStatus::where('type', 'task')->get();
            $ignoredTaskStatusIds = $allTaskStatuses
                ->filter(fn($s) => $s->isCompleted() || $s->isCanceled() || $s->isDelayed())
                ->pluck('id');

            $overdueTasks = ProjectTask::query()
                ->with(['project', 'status'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->where(function ($q) use ($ignoredTaskStatusIds) {
                    $q->whereNull('status_id')
                        ->orWhereNotIn('status_id', $ignoredTaskStatusIds);
                })
                ->get();

            foreach ($overdueTasks as $task) {
                try {
                    if ($task->isCanceled() || $task->project?->isCanceled() || $task->status?->isCompleted() || $task->status?->isDelayed()) {
                        continue;
                    }

                    // If all active checklist items are completed, sync to completed and don't mark as delayed
                    $activeCount = $task->activeChecklistCount();
                    if ($activeCount > 0 && $task->completedChecklistCount() >= $activeCount) {
                        $task->syncStatusFromChecklist();
                        continue;
                    }

                    $task->update([
                        'status_id' => $delayedTaskStatus->id,
                    ]);

                    ProjectActivity::log(
                        projectId: $task->project_id,
                        action: 'task.overdue',
                        subject: "وضعیت گروه «{$task->title}» به دلیل گذشتن از تاریخ سررسید به «{$delayedTaskStatus->name}» تغییر یافت.",
                        taskId: $task->id
                    );

                    $task->project?->refreshProgress();

                    ProjectsTaskSseController::broadcastEvent($task->project_id, 'task_updated', [
                        'task_id' => $task->id,
                        'status_id' => $delayedTaskStatus->id,
                        'task_status' => [
                            'id' => $delayedTaskStatus->id,
                            'name' => $delayedTaskStatus->name,
                            'color' => $delayedTaskStatus->color,
                            'is_completed' => false,
                        ],
                        'task_progress' => $task->checklistProgress(),
                        'checklist_done' => $task->completedChecklistCount(),
                        'checklist_total' => $task->totalChecklistCount(),
                        'project_progress' => $task->project?->progress ?? 0,
                        'action' => 'status_updated',
                        'triggered_by_user_id' => null,
                    ]);

                    $taskCount++;
                } catch (\Throwable $e) {
                    Log::error('[Projects] CheckOverdueTasks error for task ' . $task->id, [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $delayedChecklistStatus = ProjectStatus::delayedFor('checklist');
        if ($delayedChecklistStatus) {
            $allChecklistStatuses = ProjectStatus::where('type', 'checklist')->get();
            $ignoredChecklistStatusIds = $allChecklistStatuses
                ->filter(fn($s) => $s->isCompleted() || $s->isCanceled() || $s->isDelayed())
                ->pluck('id');

            $overdueItems = ProjectChecklistItem::query()
                ->with(['task.project', 'status'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->where('is_done', false)
                ->where(function ($q) use ($ignoredChecklistStatusIds) {
                    $q->whereNull('status_id')
                        ->orWhereNotIn('status_id', $ignoredChecklistStatusIds);
                })
                ->get();

            foreach ($overdueItems as $item) {
                try {
                    if ($item->isCanceled() || $item->task?->isCanceled() || $item->task?->project?->isCanceled() || $item->isCompleted() || $item->isDelayed()) {
                        continue;
                    }

                    $item->update([
                        'status_id' => $delayedChecklistStatus->id,
                    ]);

                    if ($item->task?->project_id) {
                        ProjectActivity::log(
                            projectId: $item->task->project_id,
                            action: 'task.overdue',
                            subject: "وضعیت کار «{$item->title}» در گروه «{$item->task->title}» به دلیل گذشتن از تاریخ سررسید به «{$delayedChecklistStatus->name}» تغییر یافت.",
                            taskId: $item->task_id
                        );

                        ProjectsTaskSseController::broadcastEvent($item->task->project_id, 'checklist_updated', [
                            'task_id' => $item->task_id,
                            'item_id' => $item->id,
                            'action' => 'overdue',
                            'triggered_by_user_id' => null,
                        ]);
                    }

                    $itemCount++;
                } catch (\Throwable $e) {
                    Log::error('[Projects] CheckOverdueTasks error for checklist item ' . $item->id, [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Overdue check completed: {$projectCount} project(s), {$taskCount} group(s), and {$itemCount} work item(s) updated to delayed status.");
        return self::SUCCESS;
    }
}
