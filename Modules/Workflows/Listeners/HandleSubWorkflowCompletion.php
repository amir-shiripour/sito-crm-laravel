<?php

namespace Modules\Workflows\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Workflows\Events\WorkflowCompleted;
use Modules\Workflows\Entities\WorkflowInstance;
use Modules\Workflows\Services\WorkflowEngine;

class HandleSubWorkflowCompletion
{
    protected WorkflowEngine $engine;

    public function __construct(WorkflowEngine $engine)
    {
        $this->engine = $engine;
    }

    public function handle(WorkflowCompleted $event): void
    {
        $instance = $event->instance;
        $context = $event->context;

        if ($instance->parent_instance_id) {
            $parentInstance = WorkflowInstance::find($instance->parent_instance_id);

            if ($parentInstance && $parentInstance->status === WorkflowInstance::STATUS_ACTIVE) {
                Log::info("[Workflows] Child workflow instance {$instance->id} completed. Resuming parent instance {$parentInstance->id}");
                
                // Wake up parent workflow and advance to the next node
                $this->engine->advance($parentInstance, $context);
            }
        }
    }
}
