<?php

namespace Modules\Booking\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Booking\App\Models\TreatmentPlan;
use Modules\Booking\App\Models\TreatmentPlanWorkflowBinding;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Services\WorkflowEngine;

class TreatmentPlanWorkflowController extends Controller
{
    protected WorkflowEngine $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    public function index(TreatmentPlan $plan)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        $bindings = $plan->workflowBindings()
            ->with(['workflow', 'instances.currentNode'])
            ->get();

        return response()->json([
            'success' => true,
            'bindings' => $bindings
        ]);
    }

    public function store(Request $request, TreatmentPlan $plan)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        $validator = Validator::make($request->all(), [
            'workflow_id' => ['required', 'exists:workflows,id'],
            'scope' => ['required', 'in:plan,item,tooth'],
            'item_key' => ['nullable', 'string'],
            'tooth' => ['nullable', 'string'],
            'trigger_statuses' => ['nullable', 'array'],
            'previous_status' => ['nullable', 'string'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'auto_trigger' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات وارد شده نامعتبر است.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['treatment_plan_id'] = $plan->id;
        $data['auto_trigger'] = $request->input('auto_trigger', true);
        $data['is_active'] = $request->input('is_active', true);

        $binding = TreatmentPlanWorkflowBinding::create($data);

        return response()->json([
            'success' => true,
            'message' => 'اتصال گردش‌کار با موفقیت ثبت شد.',
            'binding' => $binding
        ]);
    }

    public function update(Request $request, TreatmentPlan $plan, TreatmentPlanWorkflowBinding $binding)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        if ($binding->treatment_plan_id !== $plan->id) {
            return response()->json(['success' => false, 'message' => 'اتصال نامعتبر است.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'workflow_id' => ['required', 'exists:workflows,id'],
            'scope' => ['required', 'in:plan,item,tooth'],
            'item_key' => ['nullable', 'string'],
            'tooth' => ['nullable', 'string'],
            'trigger_statuses' => ['nullable', 'array'],
            'previous_status' => ['nullable', 'string'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'auto_trigger' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات وارد شده نامعتبر است.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['auto_trigger'] = $request->input('auto_trigger', true);
        $data['is_active'] = $request->input('is_active', true);

        $binding->update($data);

        return response()->json([
            'success' => true,
            'message' => 'اتصال گردش‌کار با موفقیت به‌روزرسانی شد.',
            'binding' => $binding
        ]);
    }

    public function destroy(TreatmentPlan $plan, TreatmentPlanWorkflowBinding $binding)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        if ($binding->treatment_plan_id !== $plan->id) {
            return response()->json(['success' => false, 'message' => 'اتصال نامعتبر است.'], 404);
        }

        $binding->delete();

        return response()->json([
            'success' => true,
            'message' => 'اتصال گردش‌کار با موفقیت حذف شد.'
        ]);
    }

    public function trigger(TreatmentPlan $plan, TreatmentPlanWorkflowBinding $binding)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        if ($binding->treatment_plan_id !== $plan->id) {
            return response()->json(['success' => false, 'message' => 'اتصال نامعتبر است.'], 404);
        }

        $items = is_array($plan->items) ? $plan->items : [];

        if ($binding->scope === 'plan') {
            $this->workflowEngine->start(
                $binding->workflow,
                'TREATMENT_PLAN',
                $plan->id,
                [
                    'treatment_plan' => $plan,
                    'previous_status' => null,
                    'binding_id' => $binding->id,
                    'scope' => 'plan',
                    'assigned_users' => $plan->assigned_users ?? [],
                ]
            );
        } elseif ($binding->scope === 'item') {
            $item = null;
            foreach ($items as $it) {
                if (($it['item_uuid'] ?? '') === $binding->item_key) {
                    $item = $it;
                    break;
                }
            }
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'آیتم مورد نظر یافت نشد.'], 404);
            }
            $this->workflowEngine->start(
                $binding->workflow,
                'TREATMENT_PLAN',
                $plan->id,
                [
                    'treatment_plan' => $plan,
                    'previous_status' => null,
                    'binding_id' => $binding->id,
                    'scope' => 'item',
                    'item_context' => $item,
                    'assigned_users' => $plan->assigned_users ?? [],
                ]
            );
        } elseif ($binding->scope === 'tooth') {
            $bindingTeeth = explode(',', $binding->tooth);
            if (in_array('all', $bindingTeeth)) {
                $triggered = 0;
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
                                        'previous_status' => null,
                                        'binding_id' => $binding->id,
                                        'scope' => 'tooth',
                                        'tooth_context' => $toothNum,
                                        'item_context' => $it,
                                        'assigned_users' => $plan->assigned_users ?? [],
                                    ]
                                );
                                $triggered++;
                            }
                        }
                    }
                }
                if ($triggered === 0) {
                    return response()->json(['success' => false, 'message' => 'هیچ دندانی برای اجرا یافت نشد.'], 404);
                }
            } else {
                $triggered = 0;
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
                                'previous_status' => null,
                                'binding_id' => $binding->id,
                                'scope' => 'tooth',
                                'tooth_context' => $tNum,
                                'item_context' => $item,
                                'assigned_users' => $plan->assigned_users ?? [],
                            ]
                        );
                        $triggered++;
                    }
                }
                if ($triggered === 0) {
                    return response()->json(['success' => false, 'message' => 'دندان یا آیتم مورد نظر یافت نشد.'], 404);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'گردش‌کار با موفقیت به صورت دستی راه‌اندازی شد.'
        ]);
    }
}
