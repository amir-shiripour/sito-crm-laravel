<?php

namespace Modules\Projects\App\Observers;

use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectStatus;

class ProjectObserver
{
    public function created(Project $project): void
    {
        ProjectActivity::log(
            projectId: $project->id,
            action: 'project.created',
            subject: $project->title,
            newValue: [
                'title' => $project->title,
                'code' => $project->code,
                'status' => $project->status?->name,
            ]
        );
    }

    public function updated(Project $project): void
    {
        if ($project->isDirty('status_id')) {
            $oldStatusId = $project->getOriginal('status_id');
            $newStatusId = $project->status_id;

            $oldStatus = $oldStatusId ? ProjectStatus::find($oldStatusId)?->name : 'بدون وضعیت';
            $newStatus = $newStatusId ? ProjectStatus::find($newStatusId)?->name : 'بدون وضعیت';

            $action = 'project.status_changed';
            if ($project->status?->isCanceled() || str_contains($newStatus, 'لغو') || str_contains(strtolower($newStatus), 'cancel')) {
                $action = 'project.canceled';
            }

            ProjectActivity::log(
                projectId: $project->id,
                action: $action,
                subject: "تغییر وضعیت پروژه به «{$newStatus}»",
                oldValue: ['status' => $oldStatus],
                newValue: ['status' => $newStatus]
            );
        } elseif ($project->isDirty(['title', 'category_id', 'client_id', 'start_date', 'end_date', 'description'])) {
            $changes = [];
            foreach (['title', 'description', 'start_date', 'end_date'] as $field) {
                if ($project->isDirty($field)) {
                    $changes[$field] = [
                        'old' => $project->getOriginal($field),
                        'new' => $project->getAttribute($field),
                    ];
                }
            }

            ProjectActivity::log(
                projectId: $project->id,
                action: 'project.updated',
                subject: "ویرایش مشخصات پروژه «{$project->title}»",
                newValue: $changes
            );
        }
    }
}
