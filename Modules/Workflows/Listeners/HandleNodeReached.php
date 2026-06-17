<?php

namespace Modules\Workflows\Listeners;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Workflows\Events\NodeReached;
use Modules\Workflows\Entities\WorkflowNode;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Entities\WorkflowInstance;
use Modules\Workflows\Services\WorkflowEngine;
use Modules\Tasks\Entities\Task;

class HandleNodeReached
{
    protected WorkflowEngine $engine;

    public function __construct(WorkflowEngine $engine)
    {
        $this->engine = $engine;
    }

    public function handle(NodeReached $event): void
    {
        $instance = $event->instance;
        $node = $event->node;
        $context = $event->context;

        Log::info("[Workflows] HandleNodeReached triggered for node ID {$node->id} ({$node->type})");

        if ($node->type === WorkflowNode::TYPE_ACTION) {
            $this->handleActionNode($instance, $node, $context);
        } elseif ($node->type === WorkflowNode::TYPE_SUB_WORKFLOW) {
            $this->handleSubWorkflowNode($instance, $node, $context);
        }
    }

    protected function resolveClientId(WorkflowInstance $instance): ?int
    {
        if ($instance->related_type === 'TREATMENT_PLAN') {
            if (class_exists(\Modules\Booking\App\Models\TreatmentPlan::class)) {
                $plan = \Modules\Booking\App\Models\TreatmentPlan::find($instance->related_id);
                return $plan?->client_id;
            }
        } elseif ($instance->related_type === 'APPOINTMENT') {
            if (class_exists(\Modules\Booking\Entities\Appointment::class)) {
                $appt = \Modules\Booking\Entities\Appointment::find($instance->related_id);
                return $appt?->client_id;
            }
        }
        return null;
    }

    protected function handleActionNode(WorkflowInstance $instance, WorkflowNode $node, array $context): void
    {
        $config = $node->config ?? [];
        Log::info("[Workflows] Processing ACTION node ID {$node->id} for instance {$instance->id}", ['config' => $config]);

        if (!class_exists(Task::class)) {
            Log::warning("[Workflows] Task class does not exist. Skipping task creation.");
            return;
        }

        $baseDate = $instance->started_at ?? now();
        $clientId = $this->resolveClientId($instance);
        $taskRelatedType = $clientId ? 'CLIENT' : $instance->related_type;
        $taskRelatedId = $clientId ?: $instance->related_id;
        $creatorId = $instance->created_by ?: Auth::id();

        // Check if multiple tasks are defined in the configuration
        $tasksTemplates = $config['tasks'] ?? [];

        if (empty($tasksTemplates)) {
            // Backward compatibility fallback
            $tasksTemplates = [[
                'title'         => $config['title'] ?? $node->name,
                'description'   => $config['description'] ?? '',
                'task_type'     => $config['task_type'] ?? Task::TYPE_GENERAL,
                'priority'      => $config['priority'] ?? Task::PRIORITY_MEDIUM,
                'assignee_mode' => !empty($config['role_id']) ? 'by_roles' : 'single_user',
                'role_id'       => $config['role_id'] ?? null,
                'assignee_id'   => $config['assignee_id'] ?? null,
                'offset_days'   => $config['offset_days'] ?? 0,
                'auto_advance'  => $config['auto_advance'] ?? true,
            ]];
        }

        foreach ($tasksTemplates as $template) {
            $offsetDays = (int) ($template['offset_days'] ?? 0);
            $dueAt = $offsetDays ? $baseDate->copy()->addDays($offsetDays) : $baseDate;

            $title = $this->renderTemplate($template['title'] ?? $node->name, $context);
            $description = $this->renderTemplate($template['description'] ?? '', $context);

            $assigneeId = null;
            $assigneeMode = $template['assignee_mode'] ?? 'single_user';

            if ($assigneeMode === 'by_roles' && !empty($template['role_id'])) {
                $userWithRole = \App\Models\User::whereHas('roles', function ($q) use ($template) {
                    $q->where('id', $template['role_id'])
                      ->orWhere('name', $template['role_id']);
                })->first();
                $assigneeId = $template['assignee_id'] ?? $userWithRole?->id ?? null;
            } else {
                $assigneeId = $template['assignee_id'] ?? null;
            }

            if (!$assigneeId) {
                $assigneeId = Auth::id();
            }

            $task = Task::create([
                'title'        => $title,
                'description'  => $description,
                'task_type'    => Task::TYPE_SYSTEM, // Workflow engine task is always SYSTEM type in DB
                'assignee_id'  => $assigneeId,
                'creator_id'   => $creatorId,
                'status'       => Task::STATUS_TODO,
                'priority'     => $template['priority'] ?? Task::PRIORITY_MEDIUM,
                'due_at'       => $dueAt,
                'related_type' => $taskRelatedType,
                'related_id'   => $taskRelatedId,
                'meta'         => [
                    'workflow_instance_id' => $instance->id,
                    'workflow_node_id'     => $node->id,
                    'role_id'              => $template['role_id'] ?? null,
                    'auto_advance'         => (bool) ($template['auto_advance'] ?? true),
                    'related_target'       => $clientId ? 'client' : 'none',
                    'related_client_ids'   => $clientId ? [$clientId] : [],
                ]
            ]);

            Log::info("[Workflows] Created task ID {$task->id} for action node ID {$node->id} linked to client ID {$taskRelatedId}");
        }
    }

    protected function handleSubWorkflowNode(WorkflowInstance $instance, WorkflowNode $node, array $context): void
    {
        $config = $node->config ?? [];
        $childWorkflowId = $config['child_workflow_id'] ?? null;

        if (!$childWorkflowId) {
            Log::warning("[Workflows] Sub-workflow node ID {$node->id} has no child_workflow_id configured.");
            return;
        }

        $childWorkflow = Workflow::find($childWorkflowId);
        if (!$childWorkflow) {
            Log::error("[Workflows] Child workflow ID {$childWorkflowId} not found.");
            return;
        }

        Log::info("[Workflows] Spawning child workflow ID {$childWorkflowId} from parent instance {$instance->id}");

        // Spawn child workflow instance and associate parent instance ID during creation
        $childInstance = $this->engine->startNodeWorkflow(
            $childWorkflow,
            $instance->related_type,
            $instance->related_id,
            $context['tokens'] ?? [],
            $instance->id
        );

        if ($childInstance) {
            Log::info("[Workflows] Spawned child instance ID {$childInstance->id} with parent_instance_id {$instance->id}");
        }
    }

    protected function renderTemplate(string $template, array $context): string
    {
        $tokens = $context['tokens'] ?? [];
        foreach ($tokens as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }
        return $template;
    }
}
