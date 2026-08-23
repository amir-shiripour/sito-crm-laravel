<?php

namespace Modules\Projects\App\Observers;

use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectStatus;
use Modules\Projects\App\Http\Models\ProjectTask;

class ProjectTaskObserver
{
    public function created(ProjectTask $task): void
    {
        ProjectActivity::log(
            projectId: $task->project_id,
            action: 'task.created',
            subject: "افزودن کار «{$task->title}»",
            taskId: $task->id,
            newValue: [
                'title' => $task->title,
                'status' => $task->status?->name,
                'assignee' => $task->assignee?->name,
            ]
        );
    }

    public function updated(ProjectTask $task): void
    {
        if ($task->isDirty('status_id')) {
            $oldStatusId = $task->getOriginal('status_id');
            $newStatusId = $task->status_id;

            $oldStatus = $oldStatusId ? ProjectStatus::find($oldStatusId)?->name : 'بدون وضعیت';
            $newStatus = $newStatusId ? ProjectStatus::find($newStatusId)?->name : 'بدون وضعیت';

            $action = 'task.status_changed';
            if ($task->status?->isCompleted()) {
                $action = 'task.completed';
            }

            ProjectActivity::log(
                projectId: $task->project_id,
                action: $action,
                subject: "تغییر وضعیت کار «{$task->title}» به «{$newStatus}»",
                taskId: $task->id,
                oldValue: ['status' => $oldStatus],
                newValue: ['status' => $newStatus]
            );
        } elseif ($task->isDirty(['title', 'due_date', 'assigned_to', 'description'])) {
            $changes = [];
            foreach (['title', 'due_date', 'description'] as $field) {
                if ($task->isDirty($field)) {
                    $changes[$field] = [
                        'old' => $task->getOriginal($field),
                        'new' => $task->getAttribute($field),
                    ];
                }
            }

            ProjectActivity::log(
                projectId: $task->project_id,
                action: 'task.updated',
                subject: "ویرایش اطلاعات کار «{$task->title}»",
                taskId: $task->id,
                newValue: $changes
            );
        }
    }

    public function deleted(ProjectTask $task): void
    {
        ProjectActivity::log(
            projectId: $task->project_id,
            action: 'task.deleted',
            subject: "حذف کار «{$task->title}»",
            taskId: $task->id
        );
    }
}
