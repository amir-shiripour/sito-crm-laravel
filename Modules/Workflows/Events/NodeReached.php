<?php

namespace Modules\Workflows\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Workflows\Entities\WorkflowInstance;
use Modules\Workflows\Entities\WorkflowNode;

class NodeReached
{
    use SerializesModels;

    public WorkflowInstance $instance;
    public WorkflowNode $node;
    public array $context;

    public function __construct(WorkflowInstance $instance, WorkflowNode $node, array $context = [])
    {
        $this->instance = $instance;
        $this->node = $node;
        $this->context = $context;
    }
}
