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
            
            $varName = $nodeExpr;
            if (str_contains($nodeExpr, '=')) {
                $varName = trim(explode('=', $nodeExpr, 2)[0]);
            }
            
            $resolvedValue = $evaluator->resolveValue($varName, $context);
            if ($resolvedValue === null) {
                Log::info("[Workflows] Node ID {$currentNode->id} condition variable '{$varName}' is missing. Pausing workflow for user input.");
                return;
            }

            $nodeResult = $evaluator->evaluate($nodeExpr, $context);

            Log::info("[Workflows] Node ID {$currentNode->id} condition expression '{$nodeExpr}' evaluated to " . ($nodeResult ? 'TRUE' : 'FALSE'));

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
        }

        switch ($action->action_type) {
            case WorkflowAction::TYPE_CREATE_TASK:
            case WorkflowAction::TYPE_CREATE_FOLLOWUP:
                $isTask = $action->action_type === WorkflowAction::TYPE_CREATE_TASK;
                $module = $isTask ? ($deps['tasks'] ?? null) : ($deps['followups'] ?? null);
                $class = $isTask ? 'Modules\\Tasks\\Entities\\Task' : 'Modules\\FollowUps\\Entities\\FollowUp';

                if ($this->isModuleEnabled($module) && class_exists($class)) {
                    $Model = $class;

                    $dueAt = $this->calcOffsetDate($config['offset_days'] ?? 0, $baseDate);
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
                Log::info('[Workflows] notification', [
                    'instance_id' => $instance->id,
                    'message'     => $message,
                ]);
                $result = ['status' => 'logged'];
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
}
