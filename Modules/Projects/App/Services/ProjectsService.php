<?php

namespace Modules\Projects\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectChecklistItem;
use Modules\Projects\App\Http\Models\ProjectPhase;
use Modules\Projects\App\Http\Models\ProjectSetting;
use Modules\Projects\App\Http\Models\ProjectStatus;
use Modules\Projects\App\Http\Models\ProjectTask;
use Modules\Projects\App\Traits\HandlesJalaliDate;

class ProjectsService
{
    use HandlesJalaliDate;

    public function create(array $data, int $createdBy): Project
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $members = $data['members'] ?? [];
            unset($data['members']);

            $data['created_by'] = $createdBy;

            if (isset($data['start_date'])) {
                $data['start_date'] = $this->convertJalaliDate($data['start_date']);
            }
            if (isset($data['end_date'])) {
                $data['end_date'] = $this->convertJalaliDate($data['end_date']);
            }

            $project = Project::create($data);

            $this->syncMembers($project, $members);

            $autoAssign = ProjectSetting::getBool('projects_auto_assign_creator', true);
            $creatorRole = ProjectSetting::get('projects_default_creator_role', 'manager');

            // Ensure the creator is registered if auto-assign is enabled and not explicitly assigned
            if ($autoAssign && !$project->members()->where('user_id', $createdBy)->exists()) {
                $project->members()->create([
                    'user_id' => $createdBy,
                    'role' => $creatorRole,
                ]);
            }

            return $project->load(['category', 'status', 'client', 'members.user']);
        });
    }

    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $members = $data['members'] ?? null;
            unset($data['members']);

            if (array_key_exists('start_date', $data)) {
                $data['start_date'] = $this->convertJalaliDate($data['start_date']);
            }
            if (array_key_exists('end_date', $data)) {
                $data['end_date'] = $this->convertJalaliDate($data['end_date']);
            }

            $project->update($data);

            if (!is_null($members)) {
                $this->syncMembers($project, $members);
            }

            return $project->fresh(['category', 'status', 'client', 'members.user']);
        });
    }


    public function syncMembers(Project $project, array $members): void
    {
        $userIds = collect($members)->pluck('user_id')->filter()->unique()->toArray();

        $project->members()->whereNotIn('user_id', $userIds)->delete();

        foreach ($members as $m) {
            if (empty($m['user_id'])) {
                continue;
            }

            $project->members()->updateOrCreate(
                ['user_id' => $m['user_id']],
                ['role' => $m['role'] ?? 'viewer']
            );
        }
    }

    public function dashboardStats(Project $project): array
    {
        $project->loadMissing([
            'status',
            'tasks.status',
            'tasks.assignee',
            'tasks.checklistItems.status',
            'members'
        ]);

        $tasks = $project->tasks;
        $taskStatuses = ProjectStatus::forType('task')->get();

        $taskStatusCounts = $tasks->groupBy(fn($t) => $t->status?->name ?? 'بدون وضعیت')
            ->map(fn($g) => $g->count());

        $totalTasks = $tasks->count();
        $activeTasks = $tasks->filter(fn($t) => !$t->isCanceled())->count();
        $completedTasks = $tasks->filter(fn($t) => $t->isCompleted())->count();
        $overdueTasks = $tasks->filter(fn($t) => $t->isDelayed() || $t->isOverdue())->count();
        $canceledTasks = $tasks->filter(fn($t) => $t->isCanceled())->count();

        $allChecklist = $tasks->flatMap->checklistItems;
        $totalChecklist = $allChecklist->count();
        $activeChecklist = $allChecklist->filter(fn($i) => !$i->isCanceled())->count();
        $doneChecklist = $allChecklist->filter(fn($i) => $i->is_done || $i->isCompleted())->count();
        $overdueChecklist = $allChecklist->filter(fn($i) => $i->isDelayed() || $i->isOverdue())->count();
        $canceledChecklist = $allChecklist->filter(fn($i) => $i->isCanceled())->count();
        $checklistProgress = $activeChecklist > 0 ? (int) round(($doneChecklist / $activeChecklist) * 100) : ($totalChecklist > 0 ? 100 : 0);

        $projectStartDate = $project->start_date ? $project->start_date->copy()->startOfDay() : ($project->created_at ? $project->created_at->copy()->startOfDay() : now()->startOfDay());

        $ganttTasks = $tasks->map(function($t) use ($projectStartDate) {
            $taskStart = $t->created_at ? $t->created_at->copy()->startOfDay() : $projectStartDate->copy();
            if ($taskStart->lt($projectStartDate)) {
                $taskStart = $projectStartDate->copy();
            }

            $startDayOffset = (int) $projectStartDate->diffInDays($taskStart, false);
            if ($startDayOffset < 0) $startDayOffset = 0;

            if ($t->due_date) {
                $endDayOffset = (int) $projectStartDate->diffInDays($t->due_date->copy()->startOfDay(), false);
                if ($endDayOffset <= $startDayOffset) {
                    $endDayOffset = $startDayOffset + 1;
                }
            } else {
                $endDayOffset = $startDayOffset + 2;
            }

            $startDateStr = function_exists('jdate') ? jdate($taskStart)->format('Y/m/d') : (class_exists(\Morilog\Jalali\Jalalian::class) ? \Morilog\Jalali\Jalalian::fromCarbon($taskStart)->format('Y/m/d') : $taskStart->format('Y/m/d'));
            $dueDateStr = $t->due_date ? (function_exists('jdate') ? jdate($t->due_date)->format('Y/m/d') : (class_exists(\Morilog\Jalali\Jalalian::class) ? \Morilog\Jalali\Jalalian::fromCarbon($t->due_date)->format('Y/m/d') : $t->due_date->format('Y/m/d'))) : 'تعیین نشده';
            $progress = $t->status?->isCompleted() ? 100 : $t->checklistProgress();

            return [
                'id' => $t->id,
                'title' => $t->title,
                'status_id' => $t->status_id,
                'status_name' => $t->status?->name ?? 'بدون وضعیت',
                'status_color' => $t->status?->color ?? '#6366f1',
                'assignee_name' => $t->assignee?->name ?? 'تخصیص‌نیافته',
                'start_day' => $startDayOffset,
                'end_day' => $endDayOffset,
                'start_date_str' => $startDateStr,
                'due_date_str' => $dueDateStr,
                'progress' => $progress,
            ];
        })->values();

        $kanbanColumns = $taskStatuses->map(function($st) use ($tasks) {
            $matchingTasks = $tasks->where('status_id', $st->id);
            $count = $matchingTasks->count();
            $totalChecklist = $matchingTasks->sum(fn($t) => $t->totalChecklistCount());
            $doneChecklist = $matchingTasks->sum(fn($t) => $t->completedChecklistCount());

            return [
                'id' => $st->id,
                'name' => $st->name,
                'color' => $st->color ?? '#6366f1',
                'count' => $count,
                'total_checklist' => $totalChecklist,
                'done_checklist' => $doneChecklist,
            ];
        })->values();

        $phasesStats = [];
        $project->loadMissing('phases');
        foreach ($project->phases as $ph) {
            $pTasks = $tasks->where('phase_id', $ph->id);
            $pTotal = $pTasks->count();
            $pActive = $pTasks->filter(fn($t) => !$t->isCanceled())->count();
            $pCompleted = $pTasks->filter(fn($t) => $t->isCompleted())->count();
            $pPct = $pActive > 0 ? (int) round(($pCompleted / $pActive) * 100) : ($pTotal > 0 ? 100 : 0);
            $phasesStats[$ph->id] = [
                'phase_id' => $ph->id,
                'total' => $pTotal,
                'active' => $pActive,
                'completed' => $pCompleted,
                'progress' => $pPct,
            ];
        }

        $genTasks = $tasks->whereNull('phase_id');
        $genTotal = $genTasks->count();
        $genActive = $genTasks->filter(fn($t) => !$t->isCanceled())->count();
        $genCompleted = $genTasks->filter(fn($t) => $t->isCompleted())->count();
        $genPct = $genActive > 0 ? (int) round(($genCompleted / $genActive) * 100) : ($genTotal > 0 ? 100 : 0);
        $phasesStats[0] = [
            'phase_id' => 0,
            'total' => $genTotal,
            'active' => $genActive,
            'completed' => $genCompleted,
            'progress' => $genPct,
        ];

        $taskCompletedStatus = ProjectStatus::completedFor('task');
        $taskDelayedStatus = ProjectStatus::delayedFor('task');
        $taskCanceledStatus = ProjectStatus::canceledFor('task');

        $checklistCompletedStatus = ProjectStatus::completedFor('checklist') ?? $taskCompletedStatus;
        $checklistDelayedStatus = ProjectStatus::delayedFor('checklist') ?? $taskDelayedStatus;
        $checklistCanceledStatus = ProjectStatus::canceledFor('checklist') ?? $taskCanceledStatus;

        $projectCompletedStatus = ProjectStatus::completedFor('project');
        $projectDelayedStatus = ProjectStatus::delayedFor('project');
        $projectCanceledStatus = ProjectStatus::canceledFor('project');

        return [
            'progress' => (int) $project->calculateProgress(),
            'total_tasks' => $totalTasks,
            'active_tasks' => $activeTasks,
            'completed_tasks' => $completedTasks,
            'overdue_tasks' => $overdueTasks,
            'canceled_tasks' => $canceledTasks,
            'task_status_breakdown' => $taskStatusCounts,
            'checklist_total' => $totalChecklist,
            'checklist_active' => $activeChecklist,
            'checklist_done' => $doneChecklist,
            'checklist_overdue' => $overdueChecklist,
            'checklist_canceled' => $canceledChecklist,
            'checklist_progress' => $checklistProgress,
            'phases_stats' => $phasesStats,
            'members_count' => $project->members()->count(),
            'documents_count' => $project->documents()->count(),
            'messages_count' => $project->messages()->count(),
            'days_remaining' => $project->daysRemaining(),
            'is_overdue' => (bool)$project->isOverdue(),
            'is_completed' => (bool)$project->isCompleted(),
            'is_canceled' => (bool)$project->isCanceled(),
            'project_status' => $project->status ? [
                'id' => $project->status->id,
                'name' => $project->status->name,
                'color' => $project->status->color ?? '#6366f1',
                'is_completed' => (bool)$project->isCompleted(),
                'is_canceled' => (bool)$project->isCanceled(),
                'is_delayed' => (bool)($project->isDelayed() || $project->isOverdue()),
            ] : null,
            'status_colors' => [
                'task_completed' => $taskCompletedStatus?->color ?? '#10b981',
                'task_delayed' => $taskDelayedStatus?->color ?? '#f97316',
                'task_canceled' => $taskCanceledStatus?->color ?? '#ef4444',
                'checklist_completed' => $checklistCompletedStatus?->color ?? '#10b981',
                'checklist_delayed' => $checklistDelayedStatus?->color ?? '#f97316',
                'checklist_canceled' => $checklistCanceledStatus?->color ?? '#ef4444',
                'project_completed' => $projectCompletedStatus?->color ?? '#10b981',
                'project_delayed' => $projectDelayedStatus?->color ?? '#f97316',
                'project_canceled' => $projectCanceledStatus?->color ?? '#ef4444',
            ],
            'gantt_tasks' => $ganttTasks,
            'kanban_columns' => $kanbanColumns,
        ];
    }

    public function duplicate(Project $source, array $options, int $userId): Project
    {
        return DB::transaction(function () use ($source, $options, $userId) {
            $source->loadMissing(['phases.tasks.checklistItems', 'tasks.checklistItems', 'members']);

            $isTemplate = !empty($options['is_template']);
            $title = $options['title'] ?? ($isTemplate ? "الگوی: {$source->title}" : "کپی از {$source->title}");
            $code = Project::generateNextCode();
            $defaultStatus = ProjectStatus::defaultFor('project')?->id;
            $defaultTaskStatus = ProjectStatus::defaultFor('task')?->id;

            $newProject = Project::create([
                'title' => $title,
                'code' => $code,
                'description' => $options['description'] ?? $source->description,
                'category_id' => $options['category_id'] ?? $source->category_id,
                'client_id' => $isTemplate ? null : ($options['client_id'] ?? $source->client_id),
                'status_id' => $defaultStatus,
                'start_date' => isset($options['start_date']) ? $this->convertJalaliDate($options['start_date']) : null,
                'end_date' => isset($options['end_date']) ? $this->convertJalaliDate($options['end_date']) : null,
                'created_by' => $userId,
                'progress' => 0,
                'is_template' => $isTemplate,
                'meta' => $source->meta,
            ]);

            // Copy Members (if requested)
            if (!empty($options['copy_members'])) {
                foreach ($source->members as $member) {
                    $newProject->members()->create([
                        'user_id' => $member->user_id,
                        'role' => $member->role,
                    ]);
                }
            }

            // Always ensure creator is registered
            if (!$newProject->members()->where('user_id', $userId)->exists()) {
                $creatorRole = ProjectSetting::get('projects_default_creator_role', 'manager');
                $newProject->members()->create([
                    'user_id' => $userId,
                    'role' => $creatorRole,
                ]);
            }

            // Copy Phases (if requested)
            $phaseMap = [];
            if (!empty($options['copy_phases'])) {
                foreach ($source->phases as $phase) {
                    $newPhase = $newProject->phases()->create([
                        'name' => $phase->name,
                        'description' => $phase->description,
                        'color' => $phase->color,
                        'sort_order' => $phase->sort_order,
                    ]);
                    $phaseMap[$phase->id] = $newPhase->id;
                }
            }

            // Copy Tasks (if requested)
            if (!empty($options['copy_tasks'])) {
                foreach ($source->tasks as $task) {
                    $newPhaseId = ($task->phase_id && isset($phaseMap[$task->phase_id])) ? $phaseMap[$task->phase_id] : null;

                    $newTask = $newProject->tasks()->create([
                        'phase_id' => $newPhaseId,
                        'status_id' => $defaultTaskStatus,
                        'group_name' => $task->group_name,
                        'title' => $task->title,
                        'description' => $task->description,
                        'due_date' => null,
                        'assigned_to' => !empty($options['copy_members']) ? $task->assigned_to : null,
                        'created_by' => $userId,
                        'sort_order' => $task->sort_order,
                        'completed_at' => null,
                    ]);

                    // Copy Checklists (if requested)
                    if (!empty($options['copy_checklist'])) {
                        foreach ($task->checklistItems as $item) {
                            $newTask->checklistItems()->create([
                                'title' => $item->title,
                                'is_done' => false,
                                'sort_order' => $item->sort_order,
                            ]);
                        }
                    }
                }
            }

            // Activity log
            $actionDesc = $isTemplate 
                ? "ساخت الگوی آماده «{$newProject->title}» از روی «{$source->title}»"
                : "تکثیر و ساخت پروژه «{$newProject->title}» از روی «{$source->title}»";

            ProjectActivity::log(
                projectId: $newProject->id,
                action: 'project.created',
                subject: $actionDesc,
                userId: $userId
            );

            return $newProject;
        });
    }
}
