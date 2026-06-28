<?php

namespace Modules\Booking\App\Observers;

use Illuminate\Support\Facades\Log;
use Modules\Booking\App\Models\TreatmentPlan;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Services\WorkflowEngine;

class TreatmentPlanObserver
{
    protected WorkflowEngine $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    public function created(TreatmentPlan $treatmentPlan): void
    {
        $this->workflowEngine->start(
            'treatment_plan_created',
            'TREATMENT_PLAN',
            $treatmentPlan->id,
            [
                'treatment_plan' => $treatmentPlan,
                'previous_status' => null,
                'assigned_users' => $treatmentPlan->assigned_users ?? []
            ]
        );

        $this->checkAndTriggerWorkflow($treatmentPlan, null);
        $this->triggerBindings($treatmentPlan, null);
    }

    public function updated(TreatmentPlan $treatmentPlan): void
    {
        if ($treatmentPlan->isDirty('status')) {
            $previousStatus = $treatmentPlan->getOriginal('status');
            $this->checkAndTriggerWorkflow($treatmentPlan, $previousStatus);
            $this->triggerBindings($treatmentPlan, $previousStatus);
        }

        if ($treatmentPlan->isDirty('items')) {
            $oldItems = $treatmentPlan->getOriginal('items') ?? [];
            $newItems = $treatmentPlan->items ?? [];
            $oldCount = count($oldItems);
            $newCount = count($newItems);

            if ($newCount > $oldCount) {
                $this->workflowEngine->start(
                    'treatment_plan_item_added',
                    'TREATMENT_PLAN',
                    $treatmentPlan->id,
                    [
                        'treatment_plan' => $treatmentPlan,
                        'previous_status' => $treatmentPlan->status,
                        'items_count' => $newCount,
                        'assigned_users' => $treatmentPlan->assigned_users ?? []
                    ]
                );
            } elseif ($newCount < $oldCount) {
                $this->workflowEngine->start(
                    'treatment_plan_item_removed',
                    'TREATMENT_PLAN',
                    $treatmentPlan->id,
                    [
                        'treatment_plan' => $treatmentPlan,
                        'previous_status' => $treatmentPlan->status,
                        'items_count' => $newCount,
                        'assigned_users' => $treatmentPlan->assigned_users ?? []
                    ]
                );
            }
        }
    }

    protected function checkAndTriggerWorkflow(TreatmentPlan $treatmentPlan, ?string $previousStatus = null): void
    {
        $status = $treatmentPlan->status;
        Log::info("[Booking] TreatmentPlanObserver triggered for ID {$treatmentPlan->id} with status {$status}, previous status " . ($previousStatus ?? 'null'));

        $workflowKey = 'treatment_plan_' . strtolower($status);

        $teeth = [];
        if (is_array($treatmentPlan->items)) {
            foreach ($treatmentPlan->items as $item) {
                if (!empty($item['tooth'])) {
                    $teeth[] = $item['tooth'];
                } elseif (!empty($item['tooth_id'])) {
                    $teeth[] = $item['tooth_id'];
                } elseif (!empty($item['teeth']) && is_array($item['teeth'])) {
                    foreach ($item['teeth'] as $t) {
                        $teeth[] = $t;
                    }
                }
            }
        }
        $teeth = array_unique($teeth);

        $payload = [
            'treatment_plan' => $treatmentPlan,
            'previous_status' => $previousStatus,
            'assigned_users' => $treatmentPlan->assigned_users ?? []
        ];

        if (empty($teeth)) {
            Log::info("[Booking] Triggering workflows for key '{$workflowKey}' and TreatmentPlan ID {$treatmentPlan->id}");
            $this->workflowEngine->start(
                $workflowKey,
                'TREATMENT_PLAN',
                $treatmentPlan->id,
                $payload
            );
        } else {
            Log::info("[Booking] Triggering workflows for key '{$workflowKey}' for each tooth in TreatmentPlan ID {$treatmentPlan->id}", ['teeth' => $teeth]);
            foreach ($teeth as $tooth) {
                $payload['tooth'] = $tooth;
                $this->workflowEngine->start(
                    $workflowKey,
                    'TREATMENT_PLAN',
                    $treatmentPlan->id,
                    $payload
                );
            }
        }
    }

    public function triggerBindings(TreatmentPlan $plan, ?string $previousStatus): void
    {
        $bindings = \Modules\Booking\App\Models\TreatmentPlanWorkflowBinding::where('treatment_plan_id', $plan->id)
            ->where('is_active', true)
            ->where('auto_trigger', true)
            ->with('workflow')
            ->get();

        foreach ($bindings as $binding) {
            // Check trigger statuses (if set)
            $triggerStatuses = $binding->trigger_statuses;
            if (!empty($triggerStatuses)) {
                if (!in_array($plan->status, $triggerStatuses)) {
                    continue;
                }
            }

            // Check previous status (if set)
            if (!empty($binding->previous_status)) {
                if ($binding->previous_status !== $previousStatus) {
                    continue;
                }
            }

            // Check minimum amount (if set)
            if (!empty($binding->min_amount)) {
                if ($plan->total < $binding->min_amount) {
                    continue;
                }
            }

            if ($binding->scope === 'plan') {
                $this->workflowEngine->start(
                    $binding->workflow,
                    'TREATMENT_PLAN',
                    $plan->id,
                    [
                        'treatment_plan' => $plan,
                        'previous_status' => $previousStatus,
                        'binding_id' => $binding->id,
                        'scope' => 'plan',
                        'assigned_users' => $plan->assigned_users ?? [],
                    ]
                );
            } elseif ($binding->scope === 'item') {
                $items = is_array($plan->items) ? $plan->items : [];
                $item = null;
                foreach ($items as $it) {
                    if (($it['item_uuid'] ?? '') === $binding->item_key) {
                        $item = $it;
                        break;
                    }
                }
                if ($item) {
                    $this->workflowEngine->start(
                        $binding->workflow,
                        'TREATMENT_PLAN',
                        $plan->id,
                        [
                            'treatment_plan' => $plan,
                            'previous_status' => $previousStatus,
                            'binding_id' => $binding->id,
                            'scope' => 'item',
                            'item_context' => $item,
                            'assigned_users' => $plan->assigned_users ?? [],
                        ]
                    );
                }
            } elseif ($binding->scope === 'tooth') {
                $items = is_array($plan->items) ? $plan->items : [];
                $bindingTeeth = explode(',', $binding->tooth);

                if (in_array('all', $bindingTeeth)) {
                    $triggeredTeeth = [];
                    foreach ($items as $it) {
                        if (empty($binding->item_key) || ($it['item_uuid'] ?? '') === $binding->item_key) {
                            $itemTeeth = [];
                            if (!empty($it['tooth'])) {
                                $itemTeeth[] = $it['tooth'];
                            } elseif (!empty($it['tooth_id'])) {
                                $itemTeeth[] = $it['tooth_id'];
                            } elseif (!empty($it['teeth']) && is_array($it['teeth'])) {
                                $itemTeeth = $it['teeth'];
                            }

                            foreach ($itemTeeth as $toothNum) {
                                if ($toothNum && !in_array($toothNum, $triggeredTeeth)) {
                                    $triggeredTeeth[] = $toothNum;
                                    $this->workflowEngine->start(
                                        $binding->workflow,
                                        'TREATMENT_PLAN',
                                        $plan->id,
                                        [
                                            'treatment_plan' => $plan,
                                            'previous_status' => $previousStatus,
                                            'binding_id' => $binding->id,
                                            'scope' => 'tooth',
                                            'tooth_context' => $toothNum,
                                            'item_context' => $it,
                                            'assigned_users' => $plan->assigned_users ?? [],
                                        ]
                                    );
                                }
                            }
                        }
                    }
                } else {
                    foreach ($bindingTeeth as $tNum) {
                        $tNum = trim($tNum);
                        if (empty($tNum)) continue;

                        $item = null;
                        foreach ($items as $it) {
                            $itemTeeth = [];
                            if (!empty($it['tooth'])) {
                                $itemTeeth[] = $it['tooth'];
                            } elseif (!empty($it['tooth_id'])) {
                                $itemTeeth[] = $it['tooth_id'];
                            } elseif (!empty($it['teeth']) && is_array($it['teeth'])) {
                                $itemTeeth = $it['teeth'];
                            }

                            if (in_array($tNum, $itemTeeth)) {
                                if (empty($binding->item_key) || ($it['item_uuid'] ?? '') === $binding->item_key) {
                                    $item = $it;
                                    break;
                                }
                            }
                        }
                        if ($item) {
                            $this->workflowEngine->start(
                                $binding->workflow,
                                'TREATMENT_PLAN',
                                $plan->id,
                                [
                                    'treatment_plan' => $plan,
                                    'previous_status' => $previousStatus,
                                    'binding_id' => $binding->id,
                                    'scope' => 'tooth',
                                    'tooth_context' => $tNum,
                                    'item_context' => $item,
                                    'assigned_users' => $plan->assigned_users ?? [],
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
