<?php

namespace Modules\Workflows\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Workflows\Entities\WorkflowInstance;

class PatientStageAdvanced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WorkflowInstance $instance;
    public array $context;

    public function __construct(WorkflowInstance $instance, array $context = [])
    {
        $this->instance = $instance;
        $this->context = $context;
    }

    public function broadcastOn(): array
    {
        $treatmentPlanId = 0;
        if ($this->instance->related_type === 'TREATMENT_PLAN') {
            $treatmentPlanId = $this->instance->related_id;
        }

        return [
            new PrivateChannel('treatment-plan.' . $treatmentPlanId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'instance_id'     => $this->instance->id,
            'workflow_id'     => $this->instance->workflow_id,
            'workflow_name'   => $this->instance->workflow?->name,
            'current_node_id' => $this->instance->current_node_id,
            'current_node'    => $this->instance->currentNode ? [
                'id'   => $this->instance->currentNode->id,
                'name' => $this->instance->currentNode->name,
                'type' => $this->instance->currentNode->type,
            ] : null,
            'status'          => $this->instance->status,
            'context'         => $this->context,
        ];
    }
}
