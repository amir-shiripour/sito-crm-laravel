<?php

namespace Modules\Workflows\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Morilog\Jalali\Jalalian;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Entities\WorkflowStage;
use Modules\Workflows\Entities\WorkflowAction;
use Modules\Workflows\Entities\WorkflowInstance;
use Modules\Workflows\Entities\WorkflowLog;
use Modules\Workflows\Entities\WorkflowTrigger;
use Modules\Workflows\Entities\WorkflowNode;
use Modules\Workflows\Entities\WorkflowEdge;
use Modules\Workflows\Events\NodeReached;
use Modules\Workflows\Events\WorkflowCompleted;
use Modules\Workflows\Services\ConditionEvaluator;

class WorkflowEngine
{
    public function startWorkflow(Workflow $workflow, ?string $relatedType = null, ?int $relatedId = null, array $payload = []): ?WorkflowInstance
    {
        Log::info("[Workflows] Request to start workflow: '{$workflow->key}' for {$relatedType}:{$relatedId}");

        if (!$workflow->is_active) {
            Log::warning("[Workflows] Workflow inactive: '{$workflow->key}'");
            return null;
        }

        $initialStage = $workflow->stages()->where('is_initial', true)->orderBy('sort_order')->first()
            ?: $workflow->stages()->orderBy('sort_order')->first();

        if (! $initialStage) {
            Log::warning("[Workflows] No initial stage found for workflow: '{$workflow->name}'");
            return null;
        }

        return DB::transaction(function () use ($workflow, $initialStage, $relatedType, $relatedId, $payload) {
            $instance = WorkflowInstance::create([
                'workflow_id'      => $workflow->id,
                'related_type'     => $relatedType ?? 'WORKFLOW_SCHEDULE',
                'related_id'       => $relatedId ?? 0,
                'current_stage_id' => $initialStage->id,
                'status'           => WorkflowInstance::STATUS_ACTIVE,
                'started_at'       => now(),
                'created_by'       => Auth::id(),
                'payload'          => $payload, // Store payload if column exists, or handle it in context
            ]);

            // If payload column doesn't exist in WorkflowInstance, we can't store it directly there.
            // But we can pass it to runStageActions.
            // For now, let's assume we pass it via context building.

            Log::info("[Workflows] Instance created: {$instance->id}. Running initial stage actions.");
            $this->runStageActions($instance, $initialStage, $payload);

            return $instance;
        });
    }

    public function start($workflow, ?string $relatedType = null, ?int $relatedId = null, array $payload = []): ?WorkflowInstance
    {
        if ($workflow instanceof Workflow) {
            return $this->startNodeWorkflow($workflow, $relatedType, $relatedId, $payload);
        }

        $workflowKey = $workflow;

        // 1. Try to find workflow by key (Legacy/Direct trigger)
        $workflowModel = Workflow::where('key', $workflowKey)->first();
        if ($workflowModel) {
            if ($workflowModel->nodes()->exists()) {
                return $this->startNodeWorkflow($workflowModel, $relatedType, $relatedId, $payload);
            }
            return $this->startWorkflow($workflowModel, $relatedType, $relatedId, $payload);
        }

        // 2. Try to find workflows by EVENT trigger
        // We look for workflows that have a trigger of type EVENT with config->event_key matching the workflowKey
        $workflows = Workflow::where('is_active', true)
            ->whereHas('triggers', function ($q) use ($workflowKey) {
                $q->where('type', WorkflowTrigger::TYPE_EVENT)
                  ->where(function ($query) use ($workflowKey) {
                      $query->whereJsonContains('config->event_key', $workflowKey)
                            ->orWhere('config->event_key', $workflowKey);
                  });
            })
            ->with(['triggers' => function ($q) {
                $q->where('type', WorkflowTrigger::TYPE_EVENT);
            }])
            ->get();

        if ($workflows->isEmpty()) {
            Log::warning("[Workflows] No workflow found for key/event: '{$workflowKey}'");
            return null;
        }

        $lastInstance = null;
        foreach ($workflows as $wf) {
            $trigger = $wf->triggers->first(function ($t) use ($workflowKey) {
                $eventKey = $t->config['event_key'] ?? null;
                if (is_array($eventKey)) {
                    return in_array($workflowKey, $eventKey);
                }
                return $eventKey === $workflowKey;
            });

            if ($trigger) {
                $config = $trigger->config;

                // 1. Service Filter
                if ($relatedType === 'APPOINTMENT') {
                    $appt = \Modules\Booking\Entities\Appointment::find($relatedId);
                    if ($appt) {
                        $serviceIds = $config['service_ids'] ?? (isset($config['service_id']) ? [$config['service_id']] : []);
                        $serviceIds = array_filter(array_map('intval', $serviceIds));
                        $serviceOperator = $config['service_operator'] ?? 'IN';

                        if (!empty($serviceIds)) {
                            $inArray = in_array((int)$appt->service_id, $serviceIds, true);
                            if (($serviceOperator === 'IN' && !$inArray) || ($serviceOperator === 'NOT_IN' && $inArray)) {
                                Log::info("[Workflows] Skipping workflow {$wf->name} (ID: {$wf->id}) due to service filter mismatch (Operator: {$serviceOperator}).");
                                continue;
                            }
                        }

                        $providerIds = $config['provider_ids'] ?? (isset($config['provider_id']) ? [$config['provider_id']] : []);
                        $providerIds = array_filter(array_map('intval', $providerIds));
                        $providerOperator = $config['provider_operator'] ?? 'IN';

                        if (!empty($providerIds)) {
                            $inArray = in_array((int)$appt->provider_user_id, $providerIds, true);
                            if (($providerOperator === 'IN' && !$inArray) || ($providerOperator === 'NOT_IN' && $inArray)) {
                                Log::info("[Workflows] Skipping workflow {$wf->name} (ID: {$wf->id}) due to provider filter mismatch (Operator: {$providerOperator}).");
                                continue;
                            }
                        }
                    }
                }

                // 2. Treatment Plan Filter
                if ($relatedType === 'TREATMENT_PLAN') {
                    $plan = \Modules\Booking\App\Models\TreatmentPlan::find($relatedId);
                    if ($plan) {
                        // Previous status filter
                        $prevStatuses = $config['tp_prev_statuses'] ?? [];
                        $prevStatuses = array_filter(array_map('strval', $prevStatuses));
                        if (!empty($prevStatuses)) {
                            $prevStatus = $payload['previous_status'] ?? null;
                            if (!in_array((string)$prevStatus, $prevStatuses, true)) {
                                Log::info("[Workflows] Skipping workflow {$wf->name} (ID: {$wf->id}) due to treatment plan previous status filter mismatch.");
                                continue;
                            }
                        }

                        // Amount filter
                        $minAmount = isset($config['tp_min_amount']) && $config['tp_min_amount'] !== '' ? (float)$config['tp_min_amount'] : null;
                        $maxAmount = isset($config['tp_max_amount']) && $config['tp_max_amount'] !== '' ? (float)$config['tp_max_amount'] : null;

                        if ($minAmount !== null && $plan->final_payable < $minAmount) {
                            Log::info("[Workflows] Skipping workflow {$wf->name} (ID: {$wf->id}) because amount {$plan->final_payable} is less than min {$minAmount}.");
                            continue;
                        }
                        if ($maxAmount !== null && $plan->final_payable > $maxAmount) {
                            Log::info("[Workflows] Skipping workflow {$wf->name} (ID: {$wf->id}) because amount {$plan->final_payable} is greater than max {$maxAmount}.");
                            continue;
                        }
                    }
                }

                // 3. Client Filter
                if ($relatedType === 'CLIENT') {
                    $client = \Modules\Clients\Entities\Client::find($relatedId);
                    if ($client) {
                        // 3.1 Current Status Filter
                        $currentStatuses = $config['client_current_statuses'] ?? [];
                        $currentStatuses = array_filter(array_map('strval', $currentStatuses));
                        if (!empty($currentStatuses)) {
                            $currentStatus = $payload['current_status'] ?? ($client->status?->key ?? null);
                            $currentStatus = $currentStatus !== null ? (string)$currentStatus : '';
                            $currentOperator = $config['client_current_status_operator'] ?? 'IN';
                            $inArray = in_array($currentStatus, $currentStatuses, true);
                            if (($currentOperator === 'IN' && !$inArray) || ($currentOperator === 'NOT_IN' && $inArray)) {
                                Log::info("[Workflows] Skipping workflow {$wf->name} (ID: {$wf->id}) due to client current status filter mismatch (Operator: {$currentOperator}).");
                                continue;
                            }
                        }

                        // 3.2 Previous Status Filter
                        $prevStatuses = $config['client_prev_statuses'] ?? [];
                        $prevStatuses = array_filter(array_map('strval', $prevStatuses));
                        if (!empty($prevStatuses)) {
                            $prevStatus = $payload['previous_status'] ?? null;
                            $prevStatus = $prevStatus !== null ? (string)$prevStatus : null;
                            $prevOperator = $config['client_prev_status_operator'] ?? 'IN';
                            
                            if ($prevStatus === null) {
                                if ($prevOperator === 'IN') {
                                    Log::info("[Workflows] Skipping workflow {$wf->name} (ID: {$wf->id}) due to client previous status filter mismatch (Operator: IN but previous status is null).");
                                    continue;
                                }
                            } else {
                                $inArray = in_array($prevStatus, $prevStatuses, true);
                                if (($prevOperator === 'IN' && !$inArray) || ($prevOperator === 'NOT_IN' && $inArray)) {
                                    Log::info("[Workflows] Skipping workflow {$wf->name} (ID: {$wf->id}) due to client previous status filter mismatch (Operator: {$prevOperator}).");
                                    continue;
                                }
                            }
                        }
                    }
                }

                // 4. Call Filter
                if ($relatedType === 'CLIENT_CALL' && class_exists(\Modules\ClientCalls\Entities\ClientCall::class)) {
                    $call = \Modules\ClientCalls\Entities\ClientCall::find($relatedId);
                    if ($call) {
                        // Direction filter
                        $directions = $config['call_directions'] ?? [];
                        if (!empty($directions) && !in_array($call->direction, $directions, true)) {
                            Log::info("[Workflows] Skipping workflow {$wf->name} due to call direction filter mismatch.");
                            continue;
                        }
                        
                        // Status filter
                        $callStatuses = $config['call_statuses'] ?? [];
                        $callStatuses = array_filter(array_map('strval', $callStatuses));
                        if (!empty($callStatuses)) {
                            $callStatus = (string)$call->status;
                            $callOperator = $config['call_status_operator'] ?? 'IN';
                            $inArray = in_array($callStatus, $callStatuses, true);
                            if (($callOperator === 'IN' && !$inArray) || ($callOperator === 'NOT_IN' && $inArray)) {
                                Log::info("[Workflows] Skipping workflow {$wf->name} due to call status filter mismatch.");
                                continue;
                            }
                        }
                    }
                }

                // 5. Task / FollowUp Filter
                if (($relatedType === 'TASK' || $relatedType === 'FOLLOW_UP') && class_exists(\Modules\Tasks\Entities\Task::class)) {
                    $task = \Modules\Tasks\Entities\Task::find($relatedId);
                    if ($task) {
                        $prefix = $relatedType === 'FOLLOW_UP' ? 'followup' : 'task';
                        
                        // Status filter
                        $taskStatuses = $config["{$prefix}_statuses"] ?? [];
                        $taskStatuses = array_filter(array_map('strval', $taskStatuses));
                        if (!empty($taskStatuses)) {
                            $taskStatus = (string)$task->status;
                            $taskOperator = $config["{$prefix}_status_operator"] ?? 'IN';
                            $inArray = in_array($taskStatus, $taskStatuses, true);
                            if (($taskOperator === 'IN' && !$inArray) || ($taskOperator === 'NOT_IN' && $inArray)) {
                                Log::info("[Workflows] Skipping workflow {$wf->name} due to {$relatedType} status filter mismatch.");
                                continue;
                            }
                        }

                        // Priority filter
                        $taskPriorities = $config["{$prefix}_priorities"] ?? [];
                        $taskPriorities = array_filter(array_map('strval', $taskPriorities));
                        if (!empty($taskPriorities)) {
                            $taskPriority = (string)$task->priority;
                            $taskOperator = $config["{$prefix}_priority_operator"] ?? 'IN';
                            $inArray = in_array($taskPriority, $taskPriorities, true);
                            if (($taskOperator === 'IN' && !$inArray) || ($taskOperator === 'NOT_IN' && $inArray)) {
                                Log::info("[Workflows] Skipping workflow {$wf->name} due to {$relatedType} priority filter mismatch.");
                                continue;
                            }
                        }
                    }
                }
            }

            if ($wf->nodes()->exists()) {
                $lastInstance = $this->startNodeWorkflow($wf, $relatedType, $relatedId, $payload);
            } else {
                $lastInstance = $this->startWorkflow($wf, $relatedType, $relatedId, $payload);
            }
        }

        return $lastInstance;
    }

    public function startNodeWorkflow(Workflow $workflow, $subjectType, $subjectId, array $payload = [], ?int $parentInstanceId = null): ?WorkflowInstance
    {
        Log::info("[Workflows] Starting node-based workflow: '{$workflow->key}' for {$subjectType}:{$subjectId}");

        if (!$workflow->is_active) {
            Log::warning("[Workflows] Workflow inactive: '{$workflow->key}'");
            return null;
        }

        $startNode = $workflow->nodes()->where('type', WorkflowNode::TYPE_START)->first();

        if (!$startNode) {
            Log::warning("[Workflows] No START node found for workflow: '{$workflow->name}'");
            return null;
        }

        return DB::transaction(function () use ($workflow, $startNode, $subjectType, $subjectId, $payload, $parentInstanceId) {
            $instance = WorkflowInstance::create([
                'workflow_id'        => $workflow->id,
                'parent_instance_id' => $parentInstanceId,
                'related_type'       => $subjectType,
                'related_id'         => $subjectId,
                'current_node_id'    => $startNode->id,
                'status'             => WorkflowInstance::STATUS_ACTIVE,
                'started_at'         => now(),
                'created_by'         => Auth::id(),
                'binding_id'         => $payload['binding_id'] ?? null,
                'tooth_context'      => $payload['tooth_context'] ?? null,
                'item_context'       => $payload['item_context'] ?? null,
            ]);

            Log::info("[Workflows] Node-based Instance created: {$instance->id}. Current node: START ({$startNode->id})");

            WorkflowLog::create([
                'instance_id' => $instance->id,
                'from_node_id' => null,
                'to_node_id' => $startNode->id,
                'transition_type' => 'START',
                'run_at' => now(),
                'user_id' => Auth::id()
            ]);

            $context = $this->buildContextData($instance, $payload);

            event(new NodeReached($instance, $startNode, $context));

            $this->advance($instance, $context);

            return $instance;
        });
    }

    public function advance(WorkflowInstance $instance, array $context = []): void
    {
        if ($instance->status !== WorkflowInstance::STATUS_ACTIVE) {
            Log::warning("[Workflows] Cannot advance inactive workflow instance {$instance->id}");
            return;
        }

        $currentNode = $instance->currentNode;
        if (!$currentNode) {
            Log::warning("[Workflows] Workflow instance {$instance->id} has no current node.");
            return;
        }

        Log::info("[Workflows] Advancing instance {$instance->id} from node ID {$currentNode->id} ({$currentNode->name}, Type: {$currentNode->type})");

        if (empty($context)) {
            $context = $this->buildContextData($instance);
        }

        $edges = WorkflowEdge::where('source_node_id', $currentNode->id)->get();
        if ($edges->isEmpty()) {
            Log::info("[Workflows] Node ID {$currentNode->id} has no outgoing edges.");
            if ($currentNode->type === WorkflowNode::TYPE_END) {
                $this->completeWorkflow($instance, $context);
            }
            return;
        }

        $evaluator = new ConditionEvaluator();
        $matchedEdge = null;

        if ($currentNode->type === WorkflowNode::TYPE_CONDITION) {
            $nodeExpr = $currentNode->config['condition_expression'] ?? null;
            
            $varName = $nodeExpr ?: 'condition_result';
            if ($nodeExpr && str_contains($nodeExpr, '=')) {
                $varName = trim(explode('=', $nodeExpr, 2)[0]);
            }
            
            $resolvedValue = $evaluator->resolveValue($varName, $context);
            if ($resolvedValue === null) {
                Log::info("[Workflows] Node ID {$currentNode->id} condition variable '{$varName}' is missing. Pausing workflow for user input.");
                return;
            }

            if (empty($nodeExpr)) {
                $nodeResult = in_array($resolvedValue, [true, 'true', 1, '1'], true);
            } else {
                $nodeResult = $evaluator->evaluate($nodeExpr, $context);
            }

            Log::info("[Workflows] Node ID {$currentNode->id} condition expression '" . ($nodeExpr ?: 'NULL') . "' evaluated to " . ($nodeResult ? 'TRUE' : 'FALSE'));

            // Clean transient choice from context to prevent state pollution in chained condition nodes
            if (isset($context['tokens'][$varName])) {
                unset($context['tokens'][$varName]);
            }
            if (isset($context['tokens']['condition_result'])) {
                unset($context['tokens']['condition_result']);
            }
            if (isset($context[$varName])) {
                unset($context[$varName]);
            }
            if (isset($context['condition_result'])) {
                unset($context['condition_result']);
            }

            foreach ($edges as $edge) {
                $edgeCond = trim($edge->condition);
                // Map common Persian/English yes/no values to boolean
                $isYesEdge = in_array(strtolower($edgeCond), ['بله', 'yes', 'true', '1']);
                $isNoEdge = in_array(strtolower($edgeCond), ['خیر', 'no', 'false', '0']);

                if (($nodeResult && $isYesEdge) || (!$nodeResult && $isNoEdge)) {
                    $matchedEdge = $edge;
                    break;
                }
            }
        } else {
            foreach ($edges as $edge) {
                if ($evaluator->evaluate($edge->condition, $context)) {
                    $matchedEdge = $edge;
                    break;
                }
            }
        }

        if (!$matchedEdge) {
            Log::warning("[Workflows] No matching transition edge found from node ID {$currentNode->id} for instance {$instance->id}");
            return;
        }

        $targetNode = WorkflowNode::find($matchedEdge->target_node_id);
        if (!$targetNode) {
            Log::error("[Workflows] Target node ID {$matchedEdge->target_node_id} not found for edge ID {$matchedEdge->id}");
            return;
        }

        DB::transaction(function () use ($instance, $currentNode, $targetNode, $context) {
            WorkflowLog::create([
                'instance_id' => $instance->id,
                'from_node_id' => $currentNode->id,
                'to_node_id' => $targetNode->id,
                'transition_type' => 'ADVANCE',
                'run_at' => now(),
                'user_id' => Auth::id()
            ]);

            $instance->current_node_id = $targetNode->id;
            $instance->save();
            $instance->unsetRelation('currentNode');

            Log::info("[Workflows] Instance {$instance->id} transitioned to node ID {$targetNode->id} ({$targetNode->name})");

            event(new NodeReached($instance, $targetNode, $context));
            event(new \Modules\Workflows\Events\PatientStageAdvanced($instance, $context));

            if ($targetNode->type === WorkflowNode::TYPE_END) {
                $this->completeWorkflow($instance, $context);
            }
        });

        // Chained auto-advance for CONDITION or START nodes
        if ($targetNode->type === WorkflowNode::TYPE_CONDITION || $targetNode->type === WorkflowNode::TYPE_START) {
            Log::info("[Workflows] Chaining auto-advance for node ID {$targetNode->id} (Type: {$targetNode->type})");
            $this->advance($instance, $context);
        }
    }

    protected function completeWorkflow(WorkflowInstance $instance, array $context): void
    {
        $instance->status = WorkflowInstance::STATUS_COMPLETED;
        $instance->completed_at = now();
        $instance->save();

        Log::info("[Workflows] Workflow instance {$instance->id} reached END node. Status set to COMPLETED.");

        event(new WorkflowCompleted($instance, $context));
        event(new \Modules\Workflows\Events\PatientStageAdvanced($instance, $context));
    }

    public function goBack(WorkflowInstance $instance): void
    {
        if ($instance->status !== WorkflowInstance::STATUS_ACTIVE) {
            Log::warning("[Workflows] Cannot go back on an inactive workflow instance {$instance->id}");
            return;
        }

        $currentNode = $instance->currentNode;
        if (!$currentNode) {
            return;
        }

        // Find the last transition to this node
        $lastLog = WorkflowLog::where('instance_id', $instance->id)
            ->where('to_node_id', $currentNode->id)
            ->where('transition_type', 'ADVANCE')
            ->orderByDesc('id')
            ->first();

        if (!$lastLog || !$lastLog->from_node_id) {
            Log::warning("[Workflows] No valid previous node found for instance {$instance->id}");
            return;
        }

        DB::transaction(function () use ($instance, $currentNode, $lastLog) {
            // Cancel tasks created at current node
            $tasksToCancel = \Modules\Tasks\Entities\Task::where('meta->workflow_instance_id', $instance->id)
                ->where('meta->workflow_node_id', $currentNode->id)
                ->whereIn('status', [\Modules\Tasks\Entities\Task::STATUS_TODO, \Modules\Tasks\Entities\Task::STATUS_IN_PROGRESS])
                ->get();
            
            foreach ($tasksToCancel as $taskToCancel) {
                $taskToCancel->update([
                    'status' => \Modules\Tasks\Entities\Task::STATUS_CANCELED,
                    'completed_at' => now()
                ]);
            }

            WorkflowLog::create([
                'instance_id' => $instance->id,
                'from_node_id' => $currentNode->id,
                'to_node_id' => $lastLog->from_node_id,
                'transition_type' => 'BACK',
                'run_at' => now(),
                'user_id' => Auth::id()
            ]);

            $instance->current_node_id = $lastLog->from_node_id;
            $instance->save();
            $instance->unsetRelation('currentNode');

            Log::info("[Workflows] Instance {$instance->id} reverted to node ID {$lastLog->from_node_id}");
        });
    }

    public function moveToStage(WorkflowInstance $instance, WorkflowStage $stage): void
    {
        DB::transaction(function () use ($instance, $stage) {
            $instance->current_stage_id = $stage->id;
            $instance->save();

            $this->runStageActions($instance, $stage);
        });
    }

    protected function runStageActions(WorkflowInstance $instance, WorkflowStage $stage, array $payload = []): void
    {
        $actions = $stage->actions()->orderBy('sort_order')->get();
        Log::info("[Workflows] Found " . $actions->count() . " actions for stage: {$stage->name}");

        $context = $this->buildContextData($instance, $payload);

        foreach ($actions as $action) {
            try {
                $result = $this->runAction($instance, $stage, $action, $context);

                WorkflowLog::create([
                    'instance_id' => $instance->id,
                    'stage_id'    => $stage->id,
                    'action_type' => $action->action_type,
                    'data'        => ['config' => $action->config, 'result' => $result],
                    'run_at'      => now(),
                    'user_id'     => Auth::id(),
                ]);
            } catch (\Throwable $e) {
                Log::error('[Workflows] action failed', [
                    'instance_id' => $instance->id,
                    'stage_id'    => $stage->id,
                    'action_id'   => $action->id,
                    'error'       => $e->getMessage(),
                    'trace'       => $e->getTraceAsString(),
                ]);
            }
        }
    }

    protected function runAction(WorkflowInstance $instance, WorkflowStage $stage, WorkflowAction $action, array $context): array
    {
        $config = $action->config ?? [];
        $result = ['status' => 'skipped'];
        $deps = config('workflows.dependencies', []);

        Log::info("[Workflows] Running action ID: {$action->id} Type: {$action->action_type}", ['config' => $config]);

        // Base date for offsets: appointment start time if available, otherwise workflow start time
        $baseDate = $context['appointment']->start_at_utc ?? $instance->started_at ?? now();

        // Resolve target user/assignee for Tasks/FollowUps
        $targetUserId = Auth::id();
        $assigneeTarget = $config['assignee_target'] ?? 'CURRENT_USER';

        if ($assigneeTarget === 'APPOINTMENT_PROVIDER' && isset($context['appointment'])) {
            $targetUserId = $context['appointment']->provider_user_id ?? $targetUserId;
        } elseif ($assigneeTarget === 'SPECIFIC_USER' && !empty($config['assignee_id'])) {
            $targetUserId = $config['assignee_id'];
        } elseif ($assigneeTarget === 'TREATMENT_PLAN_CREATOR' && isset($context['treatment_plan'])) {
            $targetUserId = $context['treatment_plan']->user_id ?? $targetUserId;
        } elseif ($assigneeTarget === 'TREATMENT_PLAN_CLIENT_ASSIGNEE' && isset($context['treatment_plan'])) {
            $targetUserId = $context['treatment_plan']->client_id ?? $targetUserId;
        } elseif (str_starts_with($assigneeTarget, 'TREATMENT_PLAN_ROLE_')) {
            $roleId = (int) str_replace('TREATMENT_PLAN_ROLE_', '', $assigneeTarget);
            $assignedUsers = $context['assigned_users_by_role'][$roleId] ?? [];
            if (!empty($assignedUsers)) {
                $targetUserId = $assignedUsers[0]['user_id'];
            }
        } elseif ($assigneeTarget === 'CLIENT_CREATOR' && isset($context['client'])) {
            $targetUserId = $context['client']->created_by ?? $targetUserId;
        } elseif ($assigneeTarget === 'CLIENT_ASSIGNED_USER' && isset($context['client'])) {
            $assignedUser = $context['client']->users()->first();
            $targetUserId = $assignedUser?->id ?? $targetUserId;
        } elseif ($assigneeTarget === 'CALL_CREATOR' && isset($context['call'])) {
            $targetUserId = $context['call']->user_id ?? $targetUserId;
        } elseif (($assigneeTarget === 'TASK_CREATOR' || $assigneeTarget === 'FOLLOWUP_CREATOR') && (isset($context['task']) || isset($context['followup']))) {
            $taskObj = $context['task'] ?? $context['followup'];
            $targetUserId = $taskObj->creator_id ?? $targetUserId;
        } elseif (($assigneeTarget === 'TASK_ASSIGNEE' || $assigneeTarget === 'FOLLOWUP_ASSIGNEE') && (isset($context['task']) || isset($context['followup']))) {
            $taskObj = $context['task'] ?? $context['followup'];
            $targetUserId = $taskObj->assignee_id ?? $targetUserId;
        }

        switch ($action->action_type) {
            case WorkflowAction::TYPE_CREATE_TASK:
            case WorkflowAction::TYPE_CREATE_FOLLOWUP:
                $isTask = $action->action_type === WorkflowAction::TYPE_CREATE_TASK;
                $module = $isTask ? ($deps['tasks'] ?? null) : ($deps['followups'] ?? null);
                $class = $isTask ? 'Modules\\Tasks\\Entities\\Task' : 'Modules\\FollowUps\\Entities\\FollowUp';

                if ($this->isModuleEnabled($module) && class_exists($class)) {
                    $Model = $class;

                    $dueAt = $this->calcTaskDueAt($targetUserId, (int)($config['offset_days'] ?? 0), $baseDate);
                    $title = $this->renderTemplate($config['title'] ?? $stage->name, $context);
                    $description = $this->renderTemplate($config['description'] ?? '', $context);

                    $clientId = $this->resolveClientId($instance);
                    $taskRelatedType = $clientId ? 'CLIENT' : $instance->related_type;
                    $taskRelatedId = $clientId ?: $instance->related_id;

                    try {
                        $task = $Model::create([
                            'title'        => $title,
                            'description'  => $description,
                            'task_type'    => $isTask ? ($config['task_type'] ?? 'GENERAL') : 'FOLLOW_UP',
                            'assignee_id'  => $targetUserId,
                            'creator_id'   => $instance->created_by ?: Auth::id(),
                            'status'       => $config['status'] ?? 'TODO',
                            'priority'     => $config['priority'] ?? ($isTask ? 'MEDIUM' : 'HIGH'),
                            'due_at'       => $dueAt,
                            'related_type' => $taskRelatedType,
                            'related_id'   => $taskRelatedId,
                            'meta'         => [
                                'workflow_instance_id' => $instance->id,
                                'auto_advance'         => (bool) ($config['auto_advance'] ?? true),
                                'related_target'       => $clientId ? 'client' : 'none',
                                'related_client_ids'   => $clientId ? [$clientId] : [],
                            ]
                        ]);
                        Log::info("[Workflows] Task created successfully. ID: {$task->id} linked to client ID {$taskRelatedId}");
                        $result = ['status' => 'created', 'task_id' => $task->id];
                    } catch (\Exception $e) {
                        Log::error("[Workflows] Task creation threw exception: " . $e->getMessage());
                        throw $e;
                    }
                } else {
                    Log::warning("[Workflows] Module $module not enabled or class $class not found.");
                }
                break;

            case WorkflowAction::TYPE_SEND_NOTIFICATION:
                $message = $this->renderTemplate($config['message'] ?? ('اجرای مرحله '.$stage->name), $context);
                Log::info('[Workflows] notification template rendered', [
                    'instance_id' => $instance->id,
                    'message'     => $message,
                ]);

                $notifTarget = $config['notification_target'] ?? 'CURRENT_USER';
                $notifUserId = Auth::id() ?: ($instance->created_by ?? null);

                if ($notifTarget === 'APPOINTMENT_PROVIDER' && isset($context['appointment'])) {
                    $notifUserId = $context['appointment']->provider_user_id ?? $notifUserId;
                } elseif ($notifTarget === 'SPECIFIC_USER' && !empty($config['notification_target_user_id'])) {
                    $notifUserId = $config['notification_target_user_id'];
                } elseif ($notifTarget === 'TREATMENT_PLAN_CREATOR' && isset($context['treatment_plan'])) {
                    $notifUserId = $context['treatment_plan']->user_id ?? $notifUserId;
                } elseif ($notifTarget === 'TREATMENT_PLAN_CLIENT_ASSIGNEE' && isset($context['treatment_plan'])) {
                    $notifUserId = $context['treatment_plan']->client_id ?? $notifUserId;
                } elseif (str_starts_with($notifTarget, 'TREATMENT_PLAN_ROLE_')) {
                    $roleId = (int) str_replace('TREATMENT_PLAN_ROLE_', '', $notifTarget);
                    $assignedUsers = $context['assigned_users_by_role'][$roleId] ?? [];
                    if (!empty($assignedUsers)) {
                        $notifUserId = $assignedUsers[0]['user_id'];
                    }
                } elseif ($notifTarget === 'CLIENT_CREATOR' && isset($context['client'])) {
                    $notifUserId = $context['client']->created_by ?? $notifUserId;
                } elseif ($notifTarget === 'CLIENT_ASSIGNED_USER' && isset($context['client'])) {
                    $assignedUser = $context['client']->users()->first();
                    $notifUserId = $assignedUser?->id ?? $notifUserId;
                } elseif ($notifTarget === 'CALL_CREATOR' && isset($context['call'])) {
                    $notifUserId = $context['call']->user_id ?? $notifUserId;
                } elseif (($notifTarget === 'TASK_CREATOR' || $notifTarget === 'FOLLOWUP_CREATOR') && (isset($context['task']) || isset($context['followup']))) {
                    $taskObj = $context['task'] ?? $context['followup'];
                    $notifUserId = $taskObj->creator_id ?? $notifUserId;
                } elseif (($notifTarget === 'TASK_ASSIGNEE' || $notifTarget === 'FOLLOWUP_ASSIGNEE') && (isset($context['task']) || isset($context['followup']))) {
                    $taskObj = $context['task'] ?? $context['followup'];
                    $notifUserId = $taskObj->assignee_id ?? $notifUserId;
                }

                $recipient = $notifUserId ? \App\Models\User::find($notifUserId) : null;

                if ($recipient && class_exists(\Modules\Workflows\Notifications\SystemNotification::class)) {
                    $title = 'گردش کار: ' . ($instance->workflow->name ?? 'اعلان سیستم');
                    $recipient->notify(new \Modules\Workflows\Notifications\SystemNotification($title, $message));
                    Log::info("[Workflows] Sent system notification to User ID {$recipient->id}");
                    $result = ['status' => 'sent', 'user_id' => $recipient->id];
                } else {
                    Log::warning("[Workflows] Recipient not found or SystemNotification class does not exist. Target: {$notifTarget}, Resolved User ID: {$notifUserId}");
                    $result = ['status' => 'logged_only'];
                }
                break;

            case WorkflowAction::TYPE_SEND_SMS:
                if ($this->isModuleEnabled($deps['sms'] ?? null) && class_exists('Modules\\Sms\\Services\\SmsManager')) {
                    $to = $this->resolveTargetPhone($config, $context);
                    Log::info("[Workflows] Sending SMS to: " . ($to ?? 'NULL'));

                    if ($to) {
                        $Sms = app(\Modules\Sms\Services\SmsManager::class);
                        $params = $this->resolveSmsParams($config['params'] ?? [], $context);
                        $message = $this->renderTemplate($config['message'] ?? '', $context, $params);

                        $options = [
                            'type' => \Modules\Sms\Entities\SmsMessage::TYPE_SYSTEM,
                            'related_type' => $instance->related_type,
                            'related_id' => $instance->related_id,
                            'meta' => [
                                'workflow_id' => $instance->workflow_id,
                                'workflow_name' => $instance->workflow?->name,
                                'workflow_instance_id' => $instance->id,
                            ],
                        ];

                        if (!empty($config['offset_minutes'])) {
                            $options['scheduled_at'] = $this->calcOffsetMinutes((int) $config['offset_minutes'], $baseDate);
                        }

                        if (!empty($config['pattern_key'])) {
                            Log::info("[Workflows] Sending Pattern SMS: {$config['pattern_key']}");
                            $sms = $Sms->sendPattern($to, $config['pattern_key'], $params, $options);
                            $result = ['status' => 'pattern_sent', 'sms_id' => $sms->id, 'to' => $to];
                        } else {
                            Log::info("[Workflows] Sending Text SMS: {$message}");
                            $sms = $Sms->sendText($to, $message, $options);
                            $result = ['status' => 'text_sent', 'sms_id' => $sms->id, 'to' => $to];
                        }
                    } else {
                        Log::warning("[Workflows] SMS skipped: No phone number found.");
                        $result = ['status' => 'skipped', 'reason' => 'missing_phone'];
                    }
                } else {
                    Log::warning("[Workflows] SMS module not enabled or manager not found.");
                }
                break;
        }

        return $result;
    }

    protected function isModuleEnabled(?string $moduleName): bool
    {
        if (!$moduleName) return false;
        if (class_exists('Nwidart\\Modules\\Facades\\Module')) {
            return \Nwidart\Modules\Facades\Module::has($moduleName) && \Nwidart\Modules\Facades\Module::isEnabled($moduleName);
        }
        return true;
    }

    protected function calcOffsetDate(int $days, $base)
    {
        $base = $base ? \Illuminate\Support\Carbon::parse($base) : now();
        return $days ? $base->copy()->addDays($days) : $base;
    }

    protected function calcOffsetMinutes(int $minutes, $base)
    {
        $base = $base ? \Illuminate\Support\Carbon::parse($base) : now();
        return $minutes ? $base->copy()->addMinutes($minutes) : $base;
    }

    public function buildContextData(WorkflowInstance $instance, array $payload = []): array
    {
        $data = ['tokens' => []];

        // Merge payload into tokens
        if (!empty($payload)) {
            $data['tokens'] = array_merge($data['tokens'], $payload);
        }

        if ($instance->related_type === 'APPOINTMENT' && class_exists('Modules\\Booking\\Entities\\Appointment')) {
            $appt = \Modules\Booking\Entities\Appointment::query()
                ->with(['client', 'service', 'provider'])
                ->find($instance->related_id);

            if ($appt) {
                $scheduleTz = config('booking.timezones.display_default', 'Asia/Tehran');
                $dateJalali = $appt->start_at_utc ? Jalalian::fromDateTime($appt->start_at_utc->copy()->timezone($scheduleTz)) : null;

                // Check if route exists before using it
                $paymentLink = Route::has('booking.payment.show')
                    ? route('booking.payment.show', ['id' => $appt->id])
                    : '#';

                $data['appointment'] = $appt;
                $data['tokens'] = array_merge($data['tokens'], [
                    'client_name' => $appt->client?->full_name,
                    'client_phone' => $appt->client?->phone,
                    'service_name' => $appt->service?->name,
                    'provider_name' => $appt->provider?->name,
                    'appointment_date_jalali' => $dateJalali?->format('Y/m/d'),
                    'appointment_time_jalali' => $dateJalali?->format('H:i'),
                    'appointment_datetime_jalali' => $dateJalali?->format('Y/m/d H:i'),
                    'payment_link' => $paymentLink,
                ]);
            }
        }

        // Handle STATEMENT context
        if ($instance->related_type === 'STATEMENT' && class_exists('Modules\\Booking\\Entities\\BookingStatement')) {
            $statement = \Modules\Booking\Entities\BookingStatement::with(['provider', 'user'])->find($instance->related_id);

            if ($statement) {
                $data['statement'] = $statement;

                // Convert dates to Jalali for tokens
                $startDateJalali = Jalalian::fromCarbon(\Carbon\Carbon::parse($statement->start_date))->format('Y/m/d');
                $endDateJalali = Jalalian::fromCarbon(\Carbon\Carbon::parse($statement->end_date))->format('Y/m/d');

                $data['tokens'] = array_merge($data['tokens'], [
                    'statement_id' => $statement->id,
                    'provider_name' => $statement->provider?->name,
                    'provider_phone' => $statement->provider?->mobile ?? $statement->provider?->phone,
                    'start_date' => $startDateJalali,
                    'end_date' => $endDateJalali,
                    'status' => $statement->status,
                    'first_appointment_time' => $statement->first_appointment_time,
                    'last_appointment_time' => $statement->last_appointment_time,
                    'notes' => $statement->notes,
                ]);
            }
        }

        // Handle TREATMENT_PLAN context
        if ($instance->related_type === 'TREATMENT_PLAN' && class_exists('Modules\\Booking\\App\\Models\\TreatmentPlan')) {
            $plan = \Modules\Booking\App\Models\TreatmentPlan::with(['client', 'patient', 'user'])->find($instance->related_id);
            if ($plan) {
                $data['treatment_plan'] = $plan;

                $assignedUsers = $plan->assigned_users ?? [];
                $assignedUserModels = [];
                $assignedByRole = [];
                
                $cureAssignableRoles = \Modules\Booking\Entities\BookingSetting::current()?->cure_assignable_roles ?? [];
                
                foreach ($assignedUsers as $assignment) {
                    $userId = $assignment['user_id'] ?? null;
                    $roleId = $assignment['role_id'] ?? null;
                    if (!$userId) continue;
                    
                    $userModel = \App\Models\User::find($userId);
                    if (!$userModel) continue;
                    
                    $assignedUserModels[$roleId][] = $userModel;
                    $assignedByRole[$roleId][] = [
                        'user_id' => $userId,
                        'name' => $userModel->name,
                        'phone' => $userModel->phone ?? $userModel->mobile,
                        'email' => $userModel->email,
                    ];
                }
                
                $data['assigned_users_by_role'] = $assignedByRole;
                $data['assigned_user_models'] = $assignedUserModels;

                $toothContext = $payload['tooth_context'] ?? $instance->tooth_context ?? null;
                $itemContext = $payload['item_context'] ?? $instance->item_context ?? null;
                if (is_string($itemContext)) {
                    $itemContext = json_decode($itemContext, true);
                }

                $data['tokens'] = array_merge([
                    'plan_id' => $plan->id,
                    'patient_name' => $plan->patient_name ?? $plan->client?->full_name,
                    'status' => $plan->status,
                    'status_label' => $plan->status_label,
                    'notes' => $plan->notes,
                    'total' => $plan->total,
                    'final_payable' => $plan->final_payable,
                    'currency' => $plan->currency ?? 'تومان',
                    'client_phone' => $plan->client?->phone ?? $plan->client?->mobile,
                    'creator_name' => $plan->user?->name,
                    'creator_phone' => $plan->user?->phone ?? $plan->user?->mobile,
                    'tooth' => $toothContext,
                    'item_service_name' => $itemContext['service_name'] ?? null,
                    'item_price' => $itemContext['price'] ?? null,
                    'item_tooth' => $itemContext['tooth'] ?? $itemContext['tooth_id'] ?? null,
                ], $this->buildRoleTokens($assignedByRole, $cureAssignableRoles), $data['tokens']);
            }
        }

        // Handle CLIENT_CALL context
        if ($instance->related_type === 'CLIENT_CALL' && class_exists(\Modules\ClientCalls\Entities\ClientCall::class)) {
            $call = \Modules\ClientCalls\Entities\ClientCall::with(['client', 'user'])->find($instance->related_id);
            if ($call) {
                $data['call'] = $call;
                if ($call->client) {
                    $data['client'] = $call->client;
                }
                
                $callTokens = [
                    'call_id' => $call->id,
                    'call_date' => $call->call_date?->format('Y-m-d') ?? '',
                    'call_time' => $call->call_time?->format('H:i') ?? '',
                    'call_reason' => $call->reason ?? '',
                    'call_result' => $call->result ?? '',
                    'call_status' => $call->status ?? '',
                    'call_direction' => $call->direction === 'inbound' ? 'ورودی' : 'خروجی',
                    'call_duration' => $call->duration_seconds ?? 0,
                    'call_notes' => $call->notes ?? '',
                    'call_next_action' => $call->next_action ?? '',
                    'call_next_action_date' => $call->next_action_date?->format('Y-m-d') ?? '',
                    'call_phone' => $call->contact_phone ?? '',
                ];
                
                $data['tokens'] = array_merge($data['tokens'], $callTokens);
            }
        }

        // Handle TASK / FOLLOW_UP context
        if (($instance->related_type === 'TASK' || $instance->related_type === 'FOLLOW_UP') && class_exists(\Modules\Tasks\Entities\Task::class)) {
            $task = \Modules\Tasks\Entities\Task::find($instance->related_id);
            if ($task) {
                $taskTypeKey = $instance->related_type === 'FOLLOW_UP' ? 'followup' : 'task';
                $data[$taskTypeKey] = $task;
                
                if ($task->related_type === 'CLIENT' && class_exists(\Modules\Clients\Entities\Client::class)) {
                    $client = \Modules\Clients\Entities\Client::with(['creator', 'status'])->find($task->related_id);
                    if ($client) {
                        $data['client'] = $client;
                    }
                }
                
                $assigneeName = $task->assignee_id ? (\App\Models\User::find($task->assignee_id)?->name ?? '') : '';
                $assigneePhone = $task->assignee_id ? (\App\Models\User::find($task->assignee_id)?->phone ?? '') : '';
                
                $taskTokens = [
                    "{$taskTypeKey}_id" => $task->id,
                    "{$taskTypeKey}_title" => $task->title ?? '',
                    "{$taskTypeKey}_description" => $task->description ?? '',
                    "{$taskTypeKey}_type" => $task->task_type ?? '',
                    "{$taskTypeKey}_status" => $task->status ?? '',
                    "{$taskTypeKey}_priority" => $task->priority ?? '',
                    "{$taskTypeKey}_due_at" => $task->due_at?->format('Y-m-d H:i') ?? '',
                    "{$taskTypeKey}_completed_at" => $task->completed_at?->format('Y-m-d H:i') ?? '',
                    "{$taskTypeKey}_assignee_name" => $assigneeName,
                    "{$taskTypeKey}_assignee_phone" => $assigneePhone,
                    "{$taskTypeKey}_creator_name" => $task->creator_id ? (\App\Models\User::find($task->creator_id)?->name ?? '') : '',
                ];
                
                $data['tokens'] = array_merge($data['tokens'], $taskTokens);
            }
        }

        // Handle CLIENT context
        if (isset($data['client']) || ($instance->related_type === 'CLIENT' && class_exists('Modules\\Clients\\Entities\\Client'))) {
            $client = $data['client'] ?? \Modules\Clients\Entities\Client::with(['creator', 'status'])->find($instance->related_id);
            if ($client) {
                $data['client'] = $client;
                
                $clientTokens = [
                    'client_id' => $client->id,
                    'client_name' => $client->full_name,
                    'client_username' => $client->username,
                    'client_phone' => $client->phone,
                    'client_email' => $client->email,
                    'client_national_code' => $client->national_code,
                    'client_case_number' => $client->case_number,
                    'client_notes' => $client->notes,
                    'client_status' => $client->status?->label ?? $client->status?->key,
                    'client_created_at_jalali' => $client->created_at ? \Morilog\Jalali\Jalalian::fromCarbon($client->created_at)->format('Y/m/d H:i') : null,
                    'client_creator_name' => $client->creator?->name,
                ];

                // Expose custom fields from Form Builder schema
                if (class_exists('Modules\\Clients\\Entities\\ClientForm')) {
                    $form = \Modules\Clients\Entities\ClientForm::default();
                    if ($form) {
                        $fields = $form->schema['fields'] ?? [];
                        foreach ($fields as $field) {
                            $fieldId = $field['id'] ?? null;
                            if ($fieldId && !\Modules\Clients\Entities\ClientForm::isSystemFieldId($fieldId)) {
                                $metaVal = $client->meta[$fieldId] ?? null;
                                if (is_array($metaVal)) {
                                    $metaVal = implode('، ', $metaVal);
                                }
                                $clientTokens["client_custom_{$fieldId}"] = $metaVal;
                            }
                        }
                    }
                }

                $data['tokens'] = array_merge($clientTokens, $data['tokens']);
            }
        }

        return $data;
    }

    protected function buildRoleTokens(array $assignedByRole, array $cureAssignableRoles): array
    {
        $tokens = [];
        $roles = \Spatie\Permission\Models\Role::whereIn('id', $cureAssignableRoles)->get();
        
        foreach ($roles as $role) {
            $users = $assignedByRole[$role->id] ?? [];
            $first = $users[0] ?? null;
            
            $roleSlug = preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/u', '_', $role->name);
            $roleSlug = trim(preg_replace('/_+/', '_', $roleSlug), '_');
            if (empty($roleSlug)) {
                $roleSlug = 'role_' . $role->id;
            }
            
            $tokens["plan_role_{$roleSlug}_name"] = $first['name'] ?? '';
            $tokens["plan_role_{$roleSlug}_phone"] = $first['phone'] ?? '';
            $tokens["plan_role_{$roleSlug}_all_names"] = implode('، ', array_column($users, 'name'));
        }
        
        return $tokens;
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
        } elseif ($instance->related_type === 'CLIENT') {
            return $instance->related_id;
        } elseif ($instance->related_type === 'CLIENT_CALL') {
            if (class_exists(\Modules\ClientCalls\Entities\ClientCall::class)) {
                $call = \Modules\ClientCalls\Entities\ClientCall::find($instance->related_id);
                return $call?->client_id;
            }
        } elseif ($instance->related_type === 'TASK' || $instance->related_type === 'FOLLOW_UP') {
            if (class_exists(\Modules\Tasks\Entities\Task::class)) {
                $task = \Modules\Tasks\Entities\Task::find($instance->related_id);
                if ($task && $task->related_type === 'CLIENT') {
                    return $task->related_id;
                }
            }
        }
        return null;
    }

    protected function resolveTargetPhone(array $config, array $context): ?string
    {
        $target = $config['target'] ?? 'APPOINTMENT_CLIENT';

        if ($target === 'APPOINTMENT_CLIENT') {
            return $context['tokens']['client_phone'] ?? null;
        }

        if ($target === 'APPOINTMENT_PROVIDER') {
            // Assuming provider has a phone number in User model or profile
            $provider = $context['appointment']->provider ?? null;
            return $provider?->phone ?? $provider?->mobile ?? null;
        }

        // New target for Statement Provider
        if ($target === 'STATEMENT_PROVIDER') {
            return $context['tokens']['provider_phone'] ?? null;
        }

        if ($target === 'SPECIFIC_USER') {
            $userId = $config['target_user_id'] ?? null;
            if ($userId) {
                $user = User::find($userId);
                return $user?->phone ?? $user?->mobile ?? null;
            }
        }

        if ($target === 'CUSTOM_PHONE') {
            return $config['phone'] ?? null;
        }

        if ($target === 'TREATMENT_PLAN_CLIENT') {
            return $context['tokens']['client_phone'] ?? null;
        }

        if ($target === 'TREATMENT_PLAN_CREATOR') {
            return $context['tokens']['creator_phone'] ?? null;
        }

        if (str_starts_with($target, 'TREATMENT_PLAN_ROLE_')) {
            $roleId = (int) str_replace('TREATMENT_PLAN_ROLE_', '', $target);
            $assignedUsers = $context['assigned_users_by_role'][$roleId] ?? [];
            return $assignedUsers[0]['phone'] ?? null;
        }

        if ($target === 'CLIENT') {
            return $context['tokens']['client_phone'] ?? null;
        }

        if ($target === 'CLIENT_CREATOR') {
            $creator = $context['client']->creator ?? null;
            return $creator?->phone ?? $creator?->mobile ?? null;
        }

        if ($target === 'CALL_CREATOR' && isset($context['call'])) {
            $user = $context['call']->user ?? null;
            return $user?->phone ?? $user?->mobile ?? null;
        }

        if (($target === 'TASK_CREATOR' || $target === 'FOLLOWUP_CREATOR') && (isset($context['task']) || isset($context['followup']))) {
            $taskObj = $context['task'] ?? $context['followup'];
            $user = \App\Models\User::find($taskObj->creator_id);
            return $user?->phone ?? $user?->mobile ?? null;
        }

        if (($target === 'TASK_ASSIGNEE' || $target === 'FOLLOWUP_ASSIGNEE') && (isset($context['task']) || isset($context['followup']))) {
            $taskObj = $context['task'] ?? $context['followup'];
            $user = \App\Models\User::find($taskObj->assignee_id);
            return $user?->phone ?? $user?->mobile ?? null;
        }

        return null;
    }

    protected function resolveSmsParams(array $paramKeys, array $context): array
    {
        $tokens = $context['tokens'] ?? [];
        $params = [];
        foreach ($paramKeys as $key) {
            // If key is a token name, use its value. If it's a raw string (not in tokens), use it as is?
            // For now, we assume keys are token names from the dropdown.
            $params[] = $tokens[$key] ?? $key;
        }
        return $params;
    }

    protected function renderTemplate(string $template, array $context, array $indexedParams = []): string
    {
        $tokens = $context['tokens'] ?? [];

        // Replace indexed placeholders {0}, {1}, ...
        if (!empty($indexedParams)) {
            foreach ($indexedParams as $idx => $value) {
                $template = str_replace('{' . $idx . '}', (string) $value, $template);
            }
        }

        // Replace named placeholders like {client_name}
        foreach ($tokens as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return $template;
    }

    public function calcTaskDueAt(?int $assigneeId, int $offsetDays, $baseDate): \Illuminate\Support\Carbon
    {
        $base = $baseDate ? \Illuminate\Support\Carbon::parse($baseDate) : now();

        $bookingEnabled = $this->isModuleEnabled('Booking')
            && class_exists('\Modules\Booking\Entities\BookingAvailabilityRule')
            && class_exists('\Modules\Booking\Entities\BookingAvailabilityException');

        if ($bookingEnabled && $assigneeId) {
            // Check if provider has schedule rules
            $hasSchedule = \Modules\Booking\Entities\BookingAvailabilityRule::query()
                ->where('scope_type', \Modules\Booking\Entities\BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER)
                ->where('scope_id', $assigneeId)
                ->exists();

            if ($hasSchedule) {
                $current = $base->copy();

                if ($offsetDays <= 0) {
                    // Check if open on base date
                    if ($this->isProviderOpenOnDate($assigneeId, $current)) {
                        return $current;
                    }
                    // Otherwise search for the first open day
                    $safetyCounter = 0;
                    while ($safetyCounter < 365) {
                        $current->addDay();
                        $safetyCounter++;
                        if ($this->isProviderOpenOnDate($assigneeId, $current)) {
                            return $current;
                        }
                    }
                    return $base->copy();
                }

                $workDaysRemaining = $offsetDays;
                $safetyCounter = 0;

                while ($workDaysRemaining > 0 && $safetyCounter < 365) {
                    $current->addDay();
                    $safetyCounter++;

                    // Check if open on this day
                    $isOpen = $this->isProviderOpenOnDate($assigneeId, $current);
                    if ($isOpen) {
                        $workDaysRemaining--;
                    }
                }

                return $current;
            }
        }

        return $offsetDays <= 0 ? $base : $base->copy()->addDays($offsetDays);
    }

    /**
     * Check if a provider is open on a specific date based on rules and exceptions.
     */
    protected function isProviderOpenOnDate(int $providerUserId, \Illuminate\Support\Carbon $date): bool
    {
        $localDateStr = $date->toDateString();

        // 1. Check Exception
        $exception = \Modules\Booking\Entities\BookingAvailabilityException::query()
            ->where('scope_type', \Modules\Booking\Entities\BookingAvailabilityException::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $providerUserId)
            ->whereDate('local_date', $localDateStr)
            ->first();

        if ($exception) {
            return !$exception->is_closed;
        }

        // 2. Check Rule
        $weekday = ((int)$date->dayOfWeek + 1) % 7; // 0=Sat .. 6=Fri
        $rule = \Modules\Booking\Entities\BookingAvailabilityRule::query()
            ->where('scope_type', \Modules\Booking\Entities\BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $providerUserId)
            ->where('weekday', $weekday)
            ->first();

        if ($rule) {
            return !$rule->is_closed;
        }

        return false;
    }
}
