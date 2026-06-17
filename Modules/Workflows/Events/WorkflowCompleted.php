<?php

namespace Modules\Workflows\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Workflows\Entities\WorkflowInstance;

class WorkflowCompleted
{
    use SerializesModels;

    public WorkflowInstance $instance;
    public array $context;

    public function __construct(WorkflowInstance $instance, array $context = [])
    {
        $this->instance = $instance;
        $this->context = $context;
    }
}
