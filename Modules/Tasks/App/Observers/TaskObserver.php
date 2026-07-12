<?php

namespace Modules\Tasks\App\Observers;

use Modules\Tasks\Entities\Task;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
            $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);
            
            $isFollowup = $task->task_type === Task::TYPE_FOLLOW_UP;
            $eventKey = $isFollowup ? 'followup_created' : 'task_created';
            $relatedType = $isFollowup ? 'FOLLOW_UP' : 'TASK';

            // Resolve client if related
            $client = null;
            if ($task->related_type === 'CLIENT' && class_exists(\Modules\Clients\Entities\Client::class)) {
                $client = \Modules\Clients\Entities\Client::find($task->related_id);
            }

            $engine->start(
                $eventKey,
                $relatedType,
                $task->id,
                [
                    'task' => $task,
                    'client' => $client,
                    'previous_status' => null,
                    'current_status' => $task->status,
                ]
            );
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
            $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);
            
            $isFollowup = $task->task_type === Task::TYPE_FOLLOW_UP;
            $relatedType = $isFollowup ? 'FOLLOW_UP' : 'TASK';

            // Resolve client if related
            $client = null;
            if ($task->related_type === 'CLIENT' && class_exists(\Modules\Clients\Entities\Client::class)) {
                $client = \Modules\Clients\Entities\Client::find($task->related_id);
            }

            if ($task->isDirty('status')) {
                $eventKey = $isFollowup ? 'followup_status_changed' : 'task_status_changed';
                $payload = [
                    'task' => $task,
                    'client' => $client,
                    'previous_status' => $task->getOriginal('status'),
                    'current_status' => $task->status,
                ];

                $engine->start(
                    $eventKey,
                    $relatedType,
                    $task->id,
                    $payload
                );
            } else {
                $eventKey = $isFollowup ? 'followup_updated' : 'task_updated';
                $engine->start(
                    $eventKey,
                    $relatedType,
                    $task->id,
                    [
                        'task' => $task,
                        'client' => $client,
                        'previous_status' => null,
                        'current_status' => $task->status,
                    ]
                );
            }
        }
    }
}
