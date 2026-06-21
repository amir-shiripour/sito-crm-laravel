<?php

namespace Modules\Booking\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\App\Models\TreatmentPlan;
use Modules\Clients\Entities\Client;

class CureController extends Controller
{

    private function getInstallmentTypes(BookingSetting $settings = null): array
    {
        $installmentTypes = [];

        // 1. Try via BookingSetting model first (if it has the property)
        if ($settings && !empty($settings->installment_types)) {
            $decoded = json_decode($settings->installment_types, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // 2. Direct query from 'settings' table (reliable method)
        try {
            $settingRow = DB::table('settings')
                ->where('key', 'installment_types')
                ->first();

            if ($settingRow && !empty($settingRow->value)) {
                $decoded = json_decode($settingRow->value, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                } else {
                    \Log::error('Installment Types JSON Decode Error', [
                        'error' => json_last_error_msg(),
                        'raw_value' => $settingRow->value
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load installment_types setting', [
                'error' => $e->getMessage()
            ]);
        }

        return $installmentTypes;
    }

    /**
     * Show the treatment plan builder (create new).
     */
    public function index(Request $request)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        $settings = BookingSetting::current();
        $installmentTypes = $this->getInstallmentTypes($settings);

        $services = BookingService::with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($s) {
                $cp = $s->custom_prices ?? [];
                if (!isset($cp['tabs']) && !isset($cp->tabs)) {
                    $cp = ['tabs' => []];
                }
                return [
                    'id'            => $s->id,
                    'name'          => $s->name,
                    'base_price'    => (float) $s->base_price,
                    'category_id'   => $s->category_id,
                    'category_name' => $s->category?->name,
                    'custom_prices' => $cp,
                ];
            });

        $categories = BookingCategory::orderBy('name')->get();

        $clients = Client::orderBy('full_name')
            ->get()
            ->map(fn($c) => [
                'id'        => $c->id,
                'full_name' => $c->full_name ?? '',
                'phone'     => $c->phone ?? '',
                'email'     => $c->email ?? '',
            ]);

        return view('booking::user.cure.index', [
            'servicesJs'       => $services,
            'planJs'           => null,
            'isReadOnly'       => false,
            'categories'       => $categories,
            'clients'          => $clients,
            'settings'         => $settings,
            'installmentTypes' => $installmentTypes,
        ]);
    }

    /**
     * List all treatment plans.
     */
    public function list(Request $request)
    {
        abort_unless(
            auth()->user()->canAny([
                'booking.cure.view',
                'booking.cure.view.all',
                'booking.cure.view.own',
                'booking.cure.manage',
            ]),
            403
        );

        $settings = BookingSetting::current();
        $cureAllowEditConfirmed = (bool) ($settings->cure_allow_edit_confirmed ?? false);

        $user  = auth()->user();
        $query = TreatmentPlan::with('client', 'creator');

        // Scope to own plans if user only has view.own
        if (
            ! $user->can('booking.cure.view.all') &&
            ! $user->can('booking.cure.manage') &&
            $user->can('booking.cure.view.own')
        ) {
            $query->where('user_id', $user->id);
        }

        // Search
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhereHas('client', fn($q2) => $q2->where('full_name', 'like', "%{$search}%"));
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sorting
        switch ($request->input('sort', 'newest')) {
            case 'oldest':
                $query->oldest();
                break;
            case 'total_desc':
                $query->orderByDesc('total');
                break;
            default:
                $query->latest();
                break;
        }

        $plans = $query->paginate(15)->withQueryString();

        // Stats
        $statsQuery = TreatmentPlan::query();
        if (
            ! $user->can('booking.cure.view.all') &&
            ! $user->can('booking.cure.manage') &&
            $user->can('booking.cure.view.own')
        ) {
            $statsQuery->where('user_id', $user->id);
        }

        $totalCount   = $statsQuery->count();
        $statusCounts = $statsQuery->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $totalAmount  = $statsQuery->sum('total');

        return view('booking::user.cure.cure-list', compact(
            'plans',
            'settings',
            'cureAllowEditConfirmed',
            'totalCount',
            'statusCounts',
            'totalAmount'
        ));
    }

    /**
     * Store a new treatment plan.
     */
    public function store(Request $request)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        $data = $request->validate([
            'client_id'            => ['required', 'integer', 'exists:clients,id'],
            'patient_name'         => ['nullable', 'string', 'max:255'],
            'status'               => ['required', 'in:draft,confirmed'],
            'notes'                => ['nullable', 'string'],
            'discount_amount'      => ['nullable', 'numeric', 'min:0'],
            'discount_type'        => ['nullable', 'in:amount,percent'],
            'subtotal'             => ['nullable', 'numeric', 'min:0'],
            'discount_value'       => ['nullable', 'numeric', 'min:0'],
            'total'                => ['nullable', 'numeric', 'min:0'],
            'installment_option_id' => ['nullable', 'string'],
            'installment_option_title' => ['nullable', 'string'],
            'installment_down_payment' => ['nullable', 'numeric', 'min:0'],
            'installment_monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'installment_fee_value' => ['nullable', 'numeric', 'min:0'],
            'installment_months'   => ['nullable', 'integer', 'min:0'],
            'installment_count'    => ['nullable', 'integer', 'min:0'],
            'final_payable'        => ['nullable', 'numeric', 'min:0'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.service_id'   => ['required', 'integer'],
            'items.*.service_name' => ['required', 'string'],
            'items.*.teeth'        => ['nullable', 'array'],
            'items.*.brands'       => ['nullable', 'array'],
            'items.*.price'        => ['required', 'numeric', 'min:0'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.subtotal'     => ['nullable', 'numeric', 'min:0'],
            'items.*.warranty'     => ['nullable', 'string', 'max:255'],
            'items.*.category_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Downgrade to draft if user cannot confirm
        if (
            $data['status'] === 'confirmed' &&
            ! auth()->user()->can('booking.cure.confirm') &&
            ! auth()->user()->can('booking.cure.manage')
        ) {
            $data['status'] = 'draft';
        }

        $setting  = BookingSetting::current();
        $currency = $setting->currency_unit ?? 'IRT';

        $plan = TreatmentPlan::create([
            'user_id'        => auth()->id(),
            'client_id'      => $data['client_id'],
            'patient_name'   => $data['patient_name'] ?? null,
            'status'         => $data['status'],
            'notes'          => $data['notes'] ?? null,
            'currency'       => $currency,
            'discount_amount'=> $data['discount_amount'] ?? 0,
            'discount_type'  => $data['discount_type'] ?? 'amount',
            'discount_value' => $data['discount_value'] ?? 0,
            'subtotal'       => $data['subtotal'] ?? 0,
            'total'          => $data['total'] ?? 0,
            'installment_option_id' => $data['installment_option_id'] ?? null,
            'installment_option_title' => $data['installment_option_title'] ?? null,
            'installment_down_payment' => $data['installment_down_payment'] ?? 0,
            'installment_monthly_amount' => $data['installment_monthly_amount'] ?? 0,
            'installment_fee_value' => $data['installment_fee_value'] ?? 0,
            'installment_months'   => $data['installment_months'] ?? 0,
            'installment_count'    => $data['installment_count'] ?? 0,
            'items'          => $data['items'],
        ]);

        $redirect = $data['status'] === 'confirmed'
            ? route('user.booking.cure.list')
            : null;

        return response()->json([
            'success'  => true,
            'message'  => $data['status'] === 'confirmed' ? 'طرح درمان تأیید شد.' : 'پیش‌نویس ذخیره شد.',
            'id'       => $plan->id,
            'redirect' => $redirect,
        ]);
    }

    /**
     * Show a treatment plan (read-only).
     */
    public function show(Request $request, TreatmentPlan $cure)
    {
        abort_unless(
            auth()->user()->canAny([
                'booking.cure.view',
                'booking.cure.view.all',
                'booking.cure.view.own',
                'booking.cure.manage',
            ]),
            403
        );

        $user = auth()->user();
        if (
            ! $user->can('booking.cure.view.all') &&
            ! $user->can('booking.cure.manage')
        ) {
            abort_unless($cure->user_id === $user->id, 403);
        }

        $settings = BookingSetting::current();
        $installmentTypes = $this->getInstallmentTypes($settings);

        $services = BookingService::with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($s) {
                $cp = $s->custom_prices ?? [];
                if (!isset($cp['tabs']) && !isset($cp->tabs)) {
                    $cp = ['tabs' => []];
                }
                return [
                    'id'            => $s->id,
                    'name'          => $s->name,
                    'base_price'    => (float) $s->base_price,
                    'category_id'   => $s->category_id,
                    'category_name' => $s->category?->name,
                    'custom_prices' => $cp,
                ];
            });

        $categories = BookingCategory::orderBy('name')->get();

        $clients = Client::orderBy('full_name')
            ->get()
            ->map(fn($c) => [
                'id'        => $c->id,
                'full_name' => $c->full_name ?? '',
                'phone'     => $c->phone ?? '',
                'email'     => $c->email ?? '',
            ]);

        $workflowInstances = $this->getWorkflowInstancesData($cure);

        $planJs = [
            'id'              => $cure->id,
            'client'          => $cure->client ? [
                'id'        => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
            ] : null,
            'patient_name'    => $cure->patient_name,
            'status'          => $cure->status,
            'notes'           => $cure->notes,
            'discount_amount' => $cure->discount_amount,
            'discount_type'   => $cure->discount_type,
            'items'           => $cure->items ?? [],
            'workflows'       => $workflowInstances,
            'installment_option_id'      => $cure->installment_option_id,
            'installment_option_title'   => $cure->installment_option_title,
            'installment_down_payment'   => $cure->installment_down_payment,
            'installment_monthly_amount' => $cure->installment_monthly_amount,
            'installment_fee_value'      => $cure->installment_fee_value,
            'installment_months'         => $cure->installment_months,
            'installment_count'          => $cure->installment_count,
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($planJs);
        }

        return view('booking::user.cure.index', [
            'servicesJs' => $services,
            'planJs'     => $planJs,
            'isReadOnly' => true,
            'categories' => $categories,
            'clients'    => $clients,
            'settings'         => $settings,
            'installmentTypes' => $installmentTypes,
        ]);
    }

    /**
     * Show and manage workflows for the treatment plan.
     */
    public function workflows(Request $request, TreatmentPlan $cure)
    {
        abort_unless(
            auth()->user()->canAny([
                'booking.cure.view',
                'booking.cure.view.all',
                'booking.cure.view.own',
                'booking.cure.manage',
            ]),
            403
        );

        $user = auth()->user();
        if (
            ! $user->can('booking.cure.view.all') &&
            ! $user->can('booking.cure.manage')
        ) {
            abort_unless($cure->user_id === $user->id, 403);
        }

        $workflowInstances = $this->getWorkflowInstancesData($cure);

        $planJs = [
            'id'              => $cure->id,
            'client'          => $cure->client ? [
                'id'        => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
            ] : null,
            'patient_name'    => $cure->patient_name,
            'status'          => $cure->status,
            'notes'           => $cure->notes,
            'workflows'       => $workflowInstances,
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($planJs);
        }

        return view('booking::user.cure.workflows', [
            'cure'   => $cure,
            'planJs' => $planJs,
        ]);
    }

    /**
     * Show the edit form for a treatment plan.
     */
    public function edit(TreatmentPlan $cure)
    {
        $user     = auth()->user();
        $settings = BookingSetting::current();
        $cureAllowEditConfirmed = (bool) ($settings->cure_allow_edit_confirmed ?? false);

        abort_unless(
            $user->can('booking.cure.edit') || $user->can('booking.cure.manage'),
            403
        );

        if ($cure->status === 'confirmed') {
            abort_unless(
                $cureAllowEditConfirmed && (
                    $user->can('booking.cure.edit.confirmed') ||
                    $user->can('booking.cure.manage')
                ),
                403
            );
        }

        $installmentTypes = $this->getInstallmentTypes($settings);

        $services = BookingService::with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($s) {
                $cp = $s->custom_prices ?? [];
                if (!isset($cp['tabs']) && !isset($cp->tabs)) {
                    $cp = ['tabs' => []];
                }
                return [
                    'id'            => $s->id,
                    'name'          => $s->name,
                    'base_price'    => (float) $s->base_price,
                    'category_id'   => $s->category_id,
                    'category_name' => $s->category?->name,
                    'custom_prices' => $cp,
                ];
            });

        $categories = BookingCategory::orderBy('name')->get();

        $clients = Client::orderBy('full_name')
            ->get()
            ->map(fn($c) => [
                'id'        => $c->id,
                'full_name' => $c->full_name ?? '',
                'phone'     => $c->phone ?? '',
                'email'     => $c->email ?? '',
            ]);

        $planJs = [
            'id'              => $cure->id,
            'client'          => $cure->client ? [
                'id'        => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
            ] : null,
            'patient_name'    => $cure->patient_name,
            'status'          => $cure->status,
            'notes'           => $cure->notes,
            'discount_amount' => $cure->discount_amount,
            'discount_type'   => $cure->discount_type,
            'items'           => $cure->items ?? [],
            'installment_option_id'      => $cure->installment_option_id,
            'installment_option_title'   => $cure->installment_option_title,
            'installment_down_payment'   => $cure->installment_down_payment,
            'installment_monthly_amount' => $cure->installment_monthly_amount,
            'installment_fee_value'      => $cure->installment_fee_value,
            'installment_months'         => $cure->installment_months,
            'installment_count'          => $cure->installment_count,
        ];

        return view('booking::user.cure.index', [
            'servicesJs'       => $services,
            'planJs'           => $planJs,
            'isReadOnly'       => false,
            'categories'       => $categories,
            'clients'          => $clients,
            'settings'         => $settings,
            'installmentTypes' => $installmentTypes,
        ]);
    }

    /**
     * Update an existing treatment plan.
     */
    public function update(Request $request, TreatmentPlan $cure)
    {
        $user    = auth()->user();
        $setting = BookingSetting::current();
        $cureAllowEditConfirmed = (bool) ($setting->cure_allow_edit_confirmed ?? false);

        abort_unless(
            $user->can('booking.cure.edit') || $user->can('booking.cure.manage'),
            403
        );

        if ($cure->status === 'confirmed') {
            abort_unless(
                $cureAllowEditConfirmed && (
                    $user->can('booking.cure.edit.confirmed') ||
                    $user->can('booking.cure.manage')
                ),
                403
            );
        }

        $data = $request->validate([
            'client_id'            => ['required', 'integer', 'exists:clients,id'],
            'patient_name'         => ['nullable', 'string', 'max:255'],
            'status'               => ['required', 'in:draft,confirmed'],
            'notes'                => ['nullable', 'string'],
            'discount_amount'      => ['nullable', 'numeric', 'min:0'],
            'discount_type'        => ['nullable', 'in:amount,percent'],
            'subtotal'             => ['nullable', 'numeric', 'min:0'],
            'discount_value'       => ['nullable', 'numeric', 'min:0'],
            'total'                => ['nullable', 'numeric', 'min:0'],
            'installment_option_id' => ['nullable', 'string'],
            'installment_option_title' => ['nullable', 'string'],
            'installment_down_payment' => ['nullable', 'numeric', 'min:0'],
            'installment_monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'installment_fee_value' => ['nullable', 'numeric', 'min:0'],
            'installment_months'   => ['nullable', 'integer', 'min:0'],
            'installment_count'    => ['nullable', 'integer', 'min:0'],
            'final_payable'        => ['nullable', 'numeric', 'min:0'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.service_id'   => ['required', 'integer'],
            'items.*.service_name' => ['required', 'string'],
            'items.*.teeth'        => ['nullable', 'array'],
            'items.*.brands'       => ['nullable', 'array'],
            'items.*.price'        => ['required', 'numeric', 'min:0'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.subtotal'     => ['nullable', 'numeric', 'min:0'],
            'items.*.warranty'      => ['nullable', 'string', 'max:255'],
            'items.*.category_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Keep current status if user cannot confirm
        if (
            $data['status'] === 'confirmed' &&
            ! $user->can('booking.cure.confirm') &&
            ! $user->can('booking.cure.manage')
        ) {
            $data['status'] = $cure->status;
        }

        $cure->update([
            'client_id'      => $data['client_id'],
            'patient_name'   => $data['patient_name'] ?? null,
            'status'         => $data['status'],
            'notes'          => $data['notes'] ?? null,
            'discount_amount'=> $data['discount_amount'] ?? 0,
            'discount_type'  => $data['discount_type'] ?? 'amount',
            'discount_value' => $data['discount_value'] ?? 0,
            'subtotal'       => $data['subtotal'] ?? 0,
            'total'          => $data['total'] ?? 0,
            'installment_option_id' => $data['installment_option_id'] ?? null,
            'installment_option_title' => $data['installment_option_title'] ?? null,
            'installment_down_payment' => $data['installment_down_payment'] ?? 0,
            'installment_monthly_amount' => $data['installment_monthly_amount'] ?? 0,
            'installment_fee_value' => $data['installment_fee_value'] ?? 0,
            'installment_months'   => $data['installment_months'] ?? 0,
            'installment_count'    => $data['installment_count'] ?? 0,
            'items'          => $data['items'],
        ]);

        $redirect = $data['status'] === 'confirmed'
            ? route('user.booking.cure.list')
            : null;

        return response()->json([
            'success'  => true,
            'message'  => $data['status'] === 'confirmed' ? 'طرح درمان تأیید شد.' : 'پیش‌نویس ذخیره شد.',
            'id'       => $cure->id,
            'redirect' => $redirect,
        ]);
    }

    /**
     * Delete a treatment plan.
     */
    public function destroy(TreatmentPlan $cure)
    {
        abort_unless(
            auth()->user()->can('booking.cure.delete') || auth()->user()->can('booking.cure.manage'),
            403
        );

        $cure->delete();

        return redirect()->route('user.booking.cure.list')
            ->with('success', 'طرح درمان حذف شد.');
    }

    /**
     * Get treatment plans for a specific client.
     */
    public function clientPlans(Request $request, $clientId)
    {
        $plans = TreatmentPlan::where('client_id', $clientId)
            ->orderBy('id', 'desc')
            ->get();

        $toothTreatments = [];
        foreach ($plans as $plan) {
            $items = $plan->items ?? [];
            foreach ($items as $item) {
                $serviceName = $item['service_name'] ?? '';
                $teeth = $item['teeth'] ?? [];
                $planStatus = $plan->status === 'confirmed' ? 'تایید شده' : 'پیش‌نویس';
                foreach ($teeth as $toothId) {
                    $toothTreatments[$toothId][] = [
                        'plan_id' => $plan->id,
                        'service_name' => $serviceName,
                        'status' => $plan->status,
                        'status_label' => $planStatus,
                        'created_at' => $plan->created_at ? $plan->created_at->format('Y-m-d') : null,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'tooth_treatments' => $toothTreatments,
        ]);
    }

    protected function getWorkflowInstancesData(TreatmentPlan $cure)
    {
        return \Modules\Workflows\Entities\WorkflowInstance::where('related_type', 'TREATMENT_PLAN')
            ->where('related_id', $cure->id)
            ->with(['workflow.nodes', 'workflow.edges', 'currentNode'])
            ->latest()
            ->get()
            ->map(function ($inst) {
                $tasks = class_exists(\Modules\Tasks\Entities\Task::class)
                    ? \Modules\Tasks\Entities\Task::where('meta->workflow_instance_id', $inst->id)
                        ->select(['id', 'title', 'status', 'meta'])
                        ->get()
                        ->map(fn($t) => [
                            'id' => $t->id,
                            'title' => $t->title,
                            'status' => $t->status,
                            'node_id' => $t->meta['workflow_node_id'] ?? null,
                            'status_label' => match ($t->status) {
                                'TODO' => 'در صف انجام',
                                'IN_PROGRESS' => 'در حال انجام',
                                'DONE' => 'انجام شده',
                                'CANCELED' => 'لغو شده',
                                default => $t->status,
                            }
                        ])
                    : collect();

                return [
                    'id' => $inst->id,
                    'workflow_name' => $inst->workflow?->name,
                    'current_node_id' => $inst->current_node_id,
                    'current_node_name' => $inst->currentNode?->name,
                    'current_node_type' => $inst->currentNode?->type,
                    'status' => $inst->status,
                    'started_at' => $inst->started_at?->format('Y-m-d H:i:s'),
                    'completed_at' => $inst->completed_at?->format('Y-m-d H:i:s'),
                    'nodes' => $inst->workflow?->nodes->map(fn($n) => [
                        'id' => $n->id,
                        'name' => $n->name,
                        'type' => $n->type,
                        'config' => $n->config,
                    ]),
                    'edges' => $inst->workflow?->edges->map(fn($e) => [
                        'source_id' => $e->source_node_id,
                        'target_id' => $e->target_node_id,
                        'condition' => $e->condition,
                    ]),
                    'tasks' => $tasks,
                    'logs' => \Modules\Workflows\Entities\WorkflowLog::where('instance_id', $inst->id)
                        ->with('user:id,name,email')
                        ->orderByDesc('id')
                        ->get()
                        ->map(fn($l) => [
                            'id' => $l->id,
                            'transition_type' => $l->transition_type,
                            'run_at' => $l->run_at?->format('Y-m-d H:i:s'),
                            'user_name' => $l->user ? $l->user->name : 'سیستم',
                            'from_node_id' => $l->from_node_id,
                            'to_node_id' => $l->to_node_id,
                        ]),
                ];
            });
    }
}
