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
        $this->checkAndTriggerWorkflow($treatmentPlan);
    }

    public function updated(TreatmentPlan $treatmentPlan): void
    {
        if ($treatmentPlan->isDirty('status')) {
            $this->checkAndTriggerWorkflow($treatmentPlan);
        }
    }

    protected function checkAndTriggerWorkflow(TreatmentPlan $treatmentPlan): void
    {
        $status = $treatmentPlan->status;
        Log::info("[Booking] TreatmentPlanObserver triggered for ID {$treatmentPlan->id} with status {$status}");

        $workflowKey = 'treatment_plan_' . strtolower($status);

        $teeth = [];
        if (is_array($treatmentPlan->items)) {
            foreach ($treatmentPlan->items as $item) {
                if (!empty($item['tooth'])) {
                    $teeth[] = $item['tooth'];
                } elseif (!empty($item['tooth_id'])) {
                    $teeth[] = $item['tooth_id'];
                }
            }
        }
        $teeth = array_unique($teeth);

        if (empty($teeth)) {
            Log::info("[Booking] Triggering workflows for key '{$workflowKey}' and TreatmentPlan ID {$treatmentPlan->id}");
            $this->workflowEngine->start(
                $workflowKey,
                'TREATMENT_PLAN',
                $treatmentPlan->id,
                ['treatment_plan' => $treatmentPlan]
            );
        } else {
            Log::info("[Booking] Triggering workflows for key '{$workflowKey}' for each tooth in TreatmentPlan ID {$treatmentPlan->id}", ['teeth' => $teeth]);
            foreach ($teeth as $tooth) {
                $this->workflowEngine->start(
                    $workflowKey,
                    'TREATMENT_PLAN',
                    $treatmentPlan->id,
                    [
                        'tooth' => $tooth,
                        'treatment_plan' => $treatmentPlan
                    ]
                );
            }
        }
    }
}
