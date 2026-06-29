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

        $actionType = $config['action_type'] ?? 'TASK';

        // Check if multiple tasks are defined in the configuration
        $tasksTemplates = $config['tasks'] ?? [];

        if ($actionType !== 'TASK' || empty($tasksTemplates)) {
            if ($actionType === 'FOLLOWUP') {
                $tasksTemplates = [[
                    'title'           => $config['followup_title'] ?? 'پیگیری',
                    'description'     => $config['followup_description'] ?? '',
                    'task_type'       => Task::TYPE_FOLLOW_UP,
                    'priority'        => $config['followup_priority'] ?? Task::PRIORITY_HIGH,
                    'assignee_target' => $config['followup_assignee_target'] ?? 'CURRENT_USER',
                    'assignee_id'     => $config['followup_assignee_id'] ?? null,
                    'status'          => $config['followup_status'] ?? Task::STATUS_TODO,
                    'offset_days'     => $config['followup_offset_days'] ?? 0,
                    'auto_advance'    => true,
                ]];
            } elseif ($actionType === 'SMS') {
                $isAutoAdvance = isset($config['auto_advance']) ? (bool)$config['auto_advance'] : true;
                
                // Resolve pattern parameters
                $rawParams = $config['sms_params'] ?? ($config['params'] ?? []);
                $resolvedParams = [];
                $tokens = $context['tokens'] ?? [];
                foreach ($rawParams as $paramKey) {
                    $resolvedParams[] = $tokens[$paramKey] ?? $paramKey;
                }
                $config['resolved_params'] = $resolvedParams;

                $tasksTemplates = [[
                    'title'         => 'ارسال پیامک سیستمی',
                    'description'   => $this->renderTemplate($config['sms_message'] ?? '', $context, $resolvedParams),
                    'task_type'     => Task::TYPE_SYSTEM,
                    'priority'      => Task::PRIORITY_LOW,
                    'offset_days'   => 0,
                    'auto_advance'  => $isAutoAdvance,
                    '_immediate_done' => $isAutoAdvance,
                    '_action_type'  => 'SMS',
                ]];
            } elseif ($actionType === 'NOTIFICATION') {
                $isAutoAdvance = isset($config['auto_advance']) ? (bool)$config['auto_advance'] : true;
                $tasksTemplates = [[
                    'title'         => 'ارسال اعلان: ' . $this->renderTemplate($config['notification_title'] ?? '', $context),
                    'description'   => $this->renderTemplate($config['notification_message'] ?? '', $context),
                    'task_type'     => Task::TYPE_SYSTEM,
                    'priority'      => Task::PRIORITY_LOW,
                    'offset_days'   => 0,
                    'auto_advance'  => $isAutoAdvance,
                    '_immediate_done' => $isAutoAdvance,
                    '_action_type'  => 'NOTIFICATION',
                ]];
            } else {
                // Backward compatibility fallback for general tasks
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
        }

        foreach ($tasksTemplates as $template) {
            $offsetDays = (int) ($template['offset_days'] ?? 0);
            $dueAt = $offsetDays ? $baseDate->copy()->addDays($offsetDays) : $baseDate;

            $title = $this->renderTemplate($template['title'] ?? $node->name, $context);
            $description = $this->renderTemplate($template['description'] ?? '', $context);

            $assigneeId = null;
            $assigneeMode = $template['assignee_mode'] ?? 'single_user';

            if ($assigneeMode === 'by_roles' && !empty($template['role_id'])) {
                $roleId = $template['role_id'];
                $assignedUsers = $context['assigned_users_by_role'][$roleId] ?? [];
                if (!empty($assignedUsers)) {
                    $assigneeId = $assignedUsers[0]['user_id'];
                } else {
                    $userWithRole = \App\Models\User::whereHas('roles', function ($q) use ($template) {
                        $q->where('id', $template['role_id'])
                          ->orWhere('name', $template['role_id']);
                    })->first();
                    $assigneeId = $template['assignee_id'] ?? $userWithRole?->id ?? null;
                }
            } else {
                $assigneeId = $template['assignee_id'] ?? null;
            }

            // Resolve target-based assignee
            $assigneeTarget = $template['assignee_target'] ?? 'CURRENT_USER';
            if ($assigneeTarget === 'APPOINTMENT_PROVIDER' && isset($context['appointment'])) {
                $assigneeId = $context['appointment']->provider_user_id ?? $assigneeId;
            } elseif ($assigneeTarget === 'SPECIFIC_USER' && !empty($template['assignee_id'])) {
                $assigneeId = $template['assignee_id'];
            } elseif ($assigneeTarget === 'TREATMENT_PLAN_CREATOR' && isset($context['treatment_plan'])) {
                $assigneeId = $context['treatment_plan']->user_id ?? $assigneeId;
            } elseif ($assigneeTarget === 'TREATMENT_PLAN_CLIENT_ASSIGNEE' && isset($context['treatment_plan'])) {
                $assigneeId = $context['treatment_plan']->client_id ?? $assigneeId;
            } elseif (str_starts_with($assigneeTarget, 'TREATMENT_PLAN_ROLE_')) {
                $roleId = (int) str_replace('TREATMENT_PLAN_ROLE_', '', $assigneeTarget);
                $assignedUsers = $context['assigned_users_by_role'][$roleId] ?? [];
                if (!empty($assignedUsers)) {
                    $assigneeId = $assignedUsers[0]['user_id'];
                }
            }

            if (!$assigneeId) {
                $assigneeId = Auth::id();
            }
            $metaData = [
                'workflow_instance_id' => $instance->id,
                'workflow_node_id'     => $node->id,
                'workflow_id'          => $instance->workflow_id,
                'workflow_name'        => $instance->workflow?->name,
                'role_id'              => $template['role_id'] ?? null,
                'auto_advance'         => (bool) ($template['auto_advance'] ?? true),
                'related_target'       => $clientId ? 'client' : 'none',
                'related_client_ids'   => $clientId ? [$clientId] : [],
                'tooth_context'        => $instance->tooth_context ?? null,
                'item_context'         => $instance->item_context ?? null,
                'treatment_plan_id'    => ($instance->related_type === 'TREATMENT_PLAN') ? $instance->related_id : null,
            ];
            
            if (isset($template['_action_type'])) {
                $metaData['_action_type'] = $template['_action_type'];
                $metaData['_node_config'] = $config;
            }

            $task = Task::create([
                'title'        => $title,
                'description'  => $description,
                'task_type'    => $template['task_type'] ?? Task::TYPE_SYSTEM,
                'assignee_id'  => $assigneeId,
                'creator_id'   => $creatorId,
                'status'       => !empty($template['_immediate_done']) ? Task::STATUS_DONE : Task::STATUS_TODO,
                'priority'     => $template['priority'] ?? Task::PRIORITY_MEDIUM,
                'due_at'       => $dueAt,
                'related_type' => $taskRelatedType,
                'related_id'   => $taskRelatedId,
                'meta'         => $metaData
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
