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

    private function applyRoundingSettings(BookingSetting $settings): BookingSetting
    {
        $rows = DB::table('settings')
            ->whereIn('key', ['installment_rounding_mode', 'installment_rounding_factor'])
            ->pluck('value', 'key');

        // ── Rounding Mode ──
        $rawMode = $rows['installment_rounding_mode'] ?? null;
        $mode = 'none';
        if ($rawMode !== null) {
            $decoded = json_decode($rawMode, true);
            $modeStr = is_string($decoded) ? $decoded : strval($rawMode);
            $mode = strtolower(trim($modeStr));
        }
        if (!in_array($mode, ['none', 'up', 'down'], true)) {
            $mode = 'none';
        }

        // ── Rounding Factor ──
        $rawFactor = $rows['installment_rounding_factor'] ?? null;
        $factor = 1000;
        if ($rawFactor !== null) {
            $decoded = json_decode($rawFactor, true);
            $factor = is_numeric($decoded)
                ? (int)$decoded
                : (is_numeric($rawFactor) ? (int)$rawFactor : 1000);
        }

        $settings->installment_rounding_mode = $mode;
        $settings->installment_rounding_factor = $factor;

        return $settings;
    }

    private function planValidationRules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'patient_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'in:amount,percent'],
            'assigned_users' => ['nullable', 'array'],
            'assigned_users.*.role_id' => ['required', 'integer'],
            'assigned_users.*.role_name' => ['required', 'string'],
            'assigned_users.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'assigned_users.*.user_name' => ['required', 'string'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'tax_value' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'final_payable' => ['nullable', 'numeric', 'min:0'],

            // اطلاعات کلی طرح اقساطی
            'installment_option_id' => ['nullable', 'string'],
            'installment_option_title' => ['nullable', 'string'],
            'installment_down_payment' => ['nullable', 'numeric', 'min:0'],
            'installment_monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'installment_fee_value' => ['nullable', 'numeric', 'min:0'],
            'installment_months' => ['nullable', 'integer', 'min:0'],
            'installment_count' => ['nullable', 'integer', 'min:0'],

            // جزییات تکمیلی اقساط
            'installment_due_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'installment_start_date' => ['nullable', 'string', 'max:20'],
            'installment_interval_months' => ['nullable', 'integer', 'min:0'],
            'installment_down_payment_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installment_fee_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installment_cash_now' => ['nullable', 'numeric', 'min:0'],
            'installment_uncovered_total' => ['nullable', 'numeric', 'min:0'],

            // ── installment_breakdown: کلیدهای camelCase که واقعاً از فرانت ارسال می‌شوند ──
            'installment_breakdown' => ['nullable', 'array'],
            'installment_breakdown.covered' => ['nullable', 'array'],
            'installment_breakdown.covered.*.brandName' => ['nullable', 'string', 'max:255'],
            'installment_breakdown.covered.*.price' => ['nullable', 'numeric', 'min:0'],
            'installment_breakdown.covered.*.downPayment' => ['nullable', 'numeric', 'min:0'],
            'installment_breakdown.covered.*.feePercent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installment_breakdown.covered.*.feeValue' => ['nullable', 'numeric', 'min:0'],
            'installment_breakdown.uncovered' => ['nullable', 'array'],
            'installment_breakdown.uncovered.*.brandName' => ['nullable', 'string', 'max:255'],
            'installment_breakdown.uncovered.*.price' => ['nullable', 'numeric', 'min:0'],

            // نسخه قدیمی snake_case هم برای سازگاری عقب‌رو نگه داشته می‌شود
            'installment_breakdown.covered.*.brand_name' => ['nullable', 'string', 'max:255'],
            'installment_breakdown.uncovered.*.brand_name' => ['nullable', 'string', 'max:255'],
            'installment_breakdown.brand_detail' => ['nullable', 'array'],
            'installment_breakdown.brand_detail.*.brand_name' => ['nullable', 'string', 'max:255'],
            'installment_breakdown.brand_detail.*.price' => ['nullable', 'numeric', 'min:0'],
            'installment_breakdown.brand_detail.*.quantity' => ['nullable', 'integer', 'min:0'],
            'installment_breakdown.brand_detail.*.down_payment_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installment_breakdown.brand_detail.*.fee_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installment_breakdown.brand_detail.*.interval_months' => ['nullable', 'integer', 'min:0'],
            'installment_breakdown.brand_detail.*.installments' => ['nullable', 'integer', 'min:0'],

            'generated_cheques' => ['nullable', 'array'],
            'generated_cheques.*.number' => ['nullable'],
            'generated_cheques.*.total' => ['nullable'],
            'generated_cheques.*.amount' => ['nullable', 'numeric', 'min:0'],
            'generated_cheques.*.date' => ['nullable', 'string', 'max:20'],
            'generated_cheques.*.display_date' => ['nullable', 'string', 'max:100'],
            'generated_cheques.*.bankName' => ['nullable', 'string', 'max:255'],
            'generated_cheques.*.chequeNumber' => ['nullable', 'string', 'max:255'],
            'generated_cheques.*.isManual' => ['nullable', 'boolean'],

            // آیتم‌های طرح درمان
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_uuid' => ['nullable', 'string'],
            'items.*.service_id' => ['required', 'integer'],
            'items.*.service_name' => ['required', 'string'],
            'items.*.teeth' => ['nullable', 'array'],
            'items.*.brands' => ['nullable', 'array'],
            'items.*.brand_selections' => ['nullable', 'array'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.base_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.subtotal' => ['nullable', 'numeric', 'min:0'],
            'items.*.discounted_subtotal' => ['nullable', 'numeric', 'min:0'],
            'items.*.warranty' => ['nullable', 'string', 'max:255'],
            'items.*.category_name' => ['nullable', 'string', 'max:255'],
            'items.*.has_installment_brands' => ['nullable', 'boolean'],
            'items.*.installment_info' => ['nullable', 'array'],
            'items.*.installment_info.total_payable' => ['nullable', 'numeric', 'min:0'],
            'items.*.installment_info.down_payment' => ['nullable', 'numeric', 'min:0'],
            'items.*.installment_info.monthly' => ['nullable', 'numeric', 'min:0'],
            'items.*.installment_info.count' => ['nullable', 'integer', 'min:0'],
        ];
    }


    private function buildPlanData(array $data): array
    {
        return [
            'patient_name' => $data['patient_name'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'discount_type' => $data['discount_type'] ?? 'amount',
            'discount_value' => $data['discount_value'] ?? 0,
            'tax_value' => $data['tax_value'] ?? 0,
            'subtotal' => $data['subtotal'] ?? 0,
            'total' => $data['total'] ?? 0,
            'final_payable' => $data['final_payable'] ?? ($data['total'] ?? 0),

            'installment_option_id' => $data['installment_option_id'] ?? null,
            'installment_option_title' => $data['installment_option_title'] ?? null,
            'installment_down_payment' => $data['installment_down_payment'] ?? 0,
            'installment_monthly_amount' => $data['installment_monthly_amount'] ?? 0,
            'installment_fee_value' => $data['installment_fee_value'] ?? 0,
            'installment_months' => $data['installment_months'] ?? 0,
            'installment_count' => $data['installment_count'] ?? 0,

            'installment_due_day' => $data['installment_due_day'] ?? null,
            'installment_start_date' => $data['installment_start_date'] ?? null,
            'installment_interval_months' => $data['installment_interval_months'] ?? null,
            'installment_down_payment_percent' => $data['installment_down_payment_percent'] ?? null,
            'installment_fee_percent' => $data['installment_fee_percent'] ?? null,
            'installment_cash_now' => $data['installment_cash_now'] ?? null,
            'installment_uncovered_total' => $data['installment_uncovered_total'] ?? null,
            'installment_breakdown' => $data['installment_breakdown'] ?? null,
            'generated_cheques' => $data['generated_cheques'] ?? null,

            'items' => $data['items'],
            'assigned_users' => $data['assigned_users'] ?? null,
        ];
    }

    public function index(Request $request)
    {
        abort_unless(
            auth()->user()->can('booking.cure.create') || auth()->user()->can('booking.cure.manage'),
            403
        );

        $settings = BookingSetting::current();
        $settings = $this->applyRoundingSettings($settings);
        $installmentTypes = $this->getInstallmentTypes($settings);

        $allowedCategories = $settings->cure_allowed_categories ?? [];
        $servicesQuery = BookingService::with(['category', 'categories'])->orderBy('name');
        if (!empty($allowedCategories)) {
            $servicesQuery->where(function ($q) use ($allowedCategories) {
                $q->whereIn('category_id', $allowedCategories)
                  ->orWhereHas('categories', function ($q2) use ($allowedCategories) {
                      $q2->whereIn('booking_categories.id', $allowedCategories);
                  });
            });
        }

        $services = $servicesQuery->get()
            ->map(function ($s) {
                $cp = $s->custom_prices ?? [];
                if (!isset($cp['tabs']) && !isset($cp->tabs)) {
                    $cp = ['tabs' => []];
                }
                
                $categoryIds = $s->categories->pluck('id')->toArray();
                if ($s->category_id && !in_array((int)$s->category_id, $categoryIds)) {
                    $categoryIds[] = (int)$s->category_id;
                }

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'base_price' => (float)$s->base_price,
                    'category_id' => $s->category_id,
                    'category_ids' => $categoryIds,
                    'category_name' => $s->category?->name,
                    'custom_prices' => $cp,
                ];
            });

        $categoriesQuery = BookingCategory::orderBy('name');
        if (!empty($allowedCategories)) {
            $categoriesQuery->whereIn('id', $allowedCategories);
        }
        $categories = $categoriesQuery->get();

        $clients = [];

        $assignableRolesWithUsers = $this->getAssignableRolesWithUsers($settings);

        return view('booking::user.cure.index', [
            'servicesJs' => $services,
            'planJs' => null,
            'isReadOnly' => false,
            'categories' => $categories,
            'clients' => $clients,
            'settings' => $settings,
            'installmentTypes' => $installmentTypes,
            'assignableRolesWithUsers' => $assignableRolesWithUsers,
            'cureStatuses' => $settings->cure_statuses ?? [],
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
                'booking.cure.view.assigned',
                'booking.cure.manage',
            ]),
            403
        );

        $settings = BookingSetting::current();
        $cureAllowEditConfirmed = (bool)($settings->cure_allow_edit_confirmed ?? false);

        $user = auth()->user();
        $query = TreatmentPlan::with('client', 'creator');

        // Scope to own/assigned plans
        if (!$user->hasRole('super-admin') && !$user->can('booking.cure.view.all') && !$user->can('booking.cure.manage')) {
            $query->where(function ($q) use ($user) {
                $hasOwn = $user->can('booking.cure.view.own') || $user->can('booking.cure.view');
                $hasAssigned = $user->can('booking.cure.view.assigned');

                if ($hasOwn && $hasAssigned) {
                    $q->where('user_id', $user->id)
                      ->orWhereJsonContains('assigned_users', ['user_id' => $user->id]);
                } elseif ($hasOwn) {
                    $q->where('user_id', $user->id);
                } elseif ($hasAssigned) {
                    $q->whereJsonContains('assigned_users', ['user_id' => $user->id]);
                } else {
                    $q->whereRaw('1 = 0');
                }
            });
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
        if (!$user->hasRole('super-admin') && !$user->can('booking.cure.view.all') && !$user->can('booking.cure.manage')) {
            $statsQuery->where(function ($q) use ($user) {
                $hasOwn = $user->can('booking.cure.view.own') || $user->can('booking.cure.view');
                $hasAssigned = $user->can('booking.cure.view.assigned');

                if ($hasOwn && $hasAssigned) {
                    $q->where('user_id', $user->id)
                      ->orWhereJsonContains('assigned_users', ['user_id' => $user->id]);
                } elseif ($hasOwn) {
                    $q->where('user_id', $user->id);
                } elseif ($hasAssigned) {
                    $q->whereJsonContains('assigned_users', ['user_id' => $user->id]);
                } else {
                    $q->whereRaw('1 = 0');
                }
            });
        }

        $totalCount = $statsQuery->count();
        $statusCounts = $statsQuery->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $totalAmount = $statsQuery->sum('total');

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

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $this->planValidationRules());

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $data = $validator->validated();

        $setting = BookingSetting::current();
        $targetStatus = $data['status'] ?? $setting->cure_default_status ?? 'draft';

        $statuses = $setting->cure_statuses ?? [];
        $targetStatusData = null;
        foreach ($statuses as $st) {
            if ($st['id'] === $targetStatus) {
                $targetStatusData = $st;
                break;
            }
        }

        if ($targetStatusData) {
            $allowedRoles = $targetStatusData['allowed_roles'] ?? [];
            if (!empty($allowedRoles)) {
                $userRoleIds = auth()->user()->roles->pluck('id')->toArray();
                if (empty(array_intersect($userRoleIds, $allowedRoles))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'شما دسترسی لازم برای ثبت طرح در وضعیت "' . $targetStatusData['name'] . '" را ندارید.'
                    ], 403);
                }
            }
        }

        $currency = $setting->currency_unit ?? 'IRT';

        $planData = $this->buildPlanData($data);
        $planData['user_id'] = auth()->id();
        $planData['client_id'] = $data['client_id'];
        $planData['currency'] = $currency;

        try {
            $plan = TreatmentPlan::create($planData);

            // Save initial workflow bindings sent during creation
            $bindings = $request->input('workflow_bindings', []);
            if (is_array($bindings) && !empty($bindings)) {
                foreach ($bindings as $bData) {
                    $plan->workflowBindings()->create([
                        'workflow_id' => $bData['workflow_id'],
                        'scope' => $bData['scope'],
                        'item_key' => $bData['item_key'] ?? null,
                        'tooth' => $bData['tooth'] ?? null,
                        'trigger_statuses' => $bData['trigger_statuses'] ?? null,
                        'previous_status' => $bData['previous_status'] ?? null,
                        'min_amount' => $bData['min_amount'] ?? null,
                        'auto_trigger' => $bData['auto_trigger'] ?? true,
                        'is_active' => $bData['is_active'] ?? true,
                    ]);
                }
            }

            // Manually trigger bindings since they weren't in the database during Eloquent's created event
            try {
                app(\Modules\Booking\App\Observers\TreatmentPlanObserver::class)->triggerBindings($plan, null);
            } catch (\Exception $e) {
                \Log::error("[CureController] Failed to manually trigger bindings: " . $e->getMessage());
            }
            
            // Create initial snapshot of the plan state upon creation
            \Modules\Booking\App\Models\TreatmentPlanSnapshot::create([
                'treatment_plan_id' => $plan->id,
                'status_from' => null,
                'status_to' => $targetStatus,
                'data' => $this->mapPlanToJs($plan),
                'changed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'طرح درمان با موفقیت ذخیره شد.',
                'id' => $plan->id,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
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
                'booking.cure.view.assigned',
                'booking.cure.manage',
            ]),
            403
        );

        $user = auth()->user();
        if (
            !$user->hasRole('super-admin') &&
            !$user->can('booking.cure.view.all') &&
            !$user->can('booking.cure.manage')
        ) {
            $isCreator = ($cure->user_id === $user->id);
            $isAssigned = false;
            $assignedUsers = $cure->assigned_users ?? [];
            if (is_array($assignedUsers)) {
                foreach ($assignedUsers as $au) {
                    if (isset($au['user_id']) && (int)$au['user_id'] === $user->id) {
                        $isAssigned = true;
                        break;
                    }
                }
            }
            if ($isCreator && $user->canAny(['booking.cure.view.own', 'booking.cure.view'])) {
                // Allowed
            } elseif ($isAssigned && $user->can('booking.cure.view.assigned')) {
                // Allowed
            } else {
                abort(403);
            }
        }

        $settings = BookingSetting::current();
        $settings = $this->applyRoundingSettings($settings);
        $installmentTypes = $this->getInstallmentTypes($settings);

        $allowedCategories = $settings->cure_allowed_categories ?? [];
        $servicesQuery = BookingService::with('category')->orderBy('name');
        if (!empty($allowedCategories)) {
            $servicesQuery->where(function ($q) use ($allowedCategories) {
                $q->whereIn('category_id', $allowedCategories)
                  ->orWhereHas('categories', function ($q2) use ($allowedCategories) {
                      $q2->whereIn('booking_categories.id', $allowedCategories);
                  });
            });
        }

        $services = $servicesQuery->get()
            ->map(function ($s) {
                $cp = $s->custom_prices ?? [];
                if (!isset($cp['tabs']) && !isset($cp->tabs)) {
                    $cp = ['tabs' => []];
                }
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'base_price' => (float)$s->base_price,
                    'category_id' => $s->category_id,
                    'category_name' => $s->category?->name,
                    'custom_prices' => $cp,
                ];
            });

        $categoriesQuery = BookingCategory::orderBy('name');
        if (!empty($allowedCategories)) {
            $categoriesQuery->whereIn('id', $allowedCategories);
        }
        $categories = $categoriesQuery->get();

        $clients = [];
        if ($cure->client) {
            $clients = [[
                'id' => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
                'phone' => $cure->client->phone ?? '',
                'email' => $cure->client->email ?? '',
                'national_code' => $cure->client->national_code ?? '',
                'case_number' => $cure->client->case_number ?? '',
            ]];
        }

        $workflowInstances = $this->getWorkflowInstancesData($cure);

        $planJs = $this->mapPlanToJs($cure);
        $assignableRolesWithUsers = $this->getAssignableRolesWithUsers($settings);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($planJs);
        }

        return view('booking::user.cure.index', [
            'servicesJs' => $services,
            'planJs' => $planJs,
            'isReadOnly' => true,
            'categories' => $categories,
            'clients' => $clients,
            'settings' => $settings,
            'installmentTypes' => $installmentTypes,
            'assignableRolesWithUsers' => $assignableRolesWithUsers,
            'cureStatuses' => $settings->cure_statuses ?? [],
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
                'booking.cure.view.assigned',
                'booking.cure.manage',
            ]),
            403
        );

        $user = auth()->user();
        if (
            !$user->hasRole('super-admin') &&
            !$user->can('booking.cure.view.all') &&
            !$user->can('booking.cure.manage')
        ) {
            $isCreator = ($cure->user_id === $user->id);
            $isAssigned = false;
            $assignedUsers = $cure->assigned_users ?? [];
            if (is_array($assignedUsers)) {
                foreach ($assignedUsers as $au) {
                    if (isset($au['user_id']) && (int)$au['user_id'] === $user->id) {
                        $isAssigned = true;
                        break;
                    }
                }
            }
            if ($isCreator && $user->canAny(['booking.cure.view.own', 'booking.cure.view'])) {
                // Allowed
            } elseif ($isAssigned && $user->can('booking.cure.view.assigned')) {
                // Allowed
            } else {
                abort(403);
            }
        }

        $workflowInstances = $this->getWorkflowInstancesData($cure);

        $planJs = [
            'id' => $cure->id,
            'client' => $cure->client ? [
                'id' => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
            ] : null,
            'patient_name' => $cure->patient_name,
            'status' => $cure->status,
            'notes' => $cure->notes,
            'workflows' => $workflowInstances,
            'tooth_numbering_system' => BookingSetting::current()->cure['tooth_numbering_system'] ?? 'palmer',
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($planJs);
        }

        return view('booking::user.cure.workflows', [
            'cure' => $cure,
            'planJs' => $planJs,
        ]);
    }

    /**
     * Show a snapshot as a full read-only treatment plan page.
     */
    public function showSnapshot(Request $request, TreatmentPlan $cure, \Modules\Booking\App\Models\TreatmentPlanSnapshot $snapshot)
    {
        abort_unless(
            auth()->user()->canAny([
                'booking.cure.view',
                'booking.cure.view.all',
                'booking.cure.view.own',
                'booking.cure.view.assigned',
                'booking.cure.manage',
            ]),
            403
        );

        $user = auth()->user();
        if (
            !$user->hasRole('super-admin') &&
            !$user->can('booking.cure.view.all') &&
            !$user->can('booking.cure.manage')
        ) {
            $isCreator = ($cure->user_id === $user->id);
            $isAssigned = false;
            $assignedUsers = $cure->assigned_users ?? [];
            if (is_array($assignedUsers)) {
                foreach ($assignedUsers as $au) {
                    if (isset($au['user_id']) && (int)$au['user_id'] === $user->id) {
                        $isAssigned = true;
                        break;
                    }
                }
            }
            if ($isCreator && $user->canAny(['booking.cure.view.own', 'booking.cure.view'])) {
                // Allowed
            } elseif ($isAssigned && $user->can('booking.cure.view.assigned')) {
                // Allowed
            } else {
                abort(403);
            }
        }

        abort_unless($snapshot->treatment_plan_id === $cure->id, 404);

        $settings = BookingSetting::current();
        $settings = $this->applyRoundingSettings($settings);

        $installmentTypes = $this->getInstallmentTypes($settings);

        $allowedCategories = $settings->cure_allowed_categories ?? [];
        $servicesQuery = BookingService::with('category')->orderBy('name');
        if (!empty($allowedCategories)) {
            $servicesQuery->where(function ($q) use ($allowedCategories) {
                $q->whereIn('category_id', $allowedCategories)
                  ->orWhereHas('categories', function ($q2) use ($allowedCategories) {
                      $q2->whereIn('booking_categories.id', $allowedCategories);
                  });
            });
        }

        $services = $servicesQuery->get()
            ->map(function ($s) {
                $cp = $s->custom_prices ?? [];
                if (!isset($cp['tabs']) && !isset($cp->tabs)) {
                    $cp = ['tabs' => []];
                }
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'base_price' => (float)$s->base_price,
                    'category_id' => $s->category_id,
                    'category_name' => $s->category?->name,
                    'custom_prices' => $cp,
                ];
            });

        $categoriesQuery = BookingCategory::orderBy('name');
        if (!empty($allowedCategories)) {
            $categoriesQuery->whereIn('id', $allowedCategories);
        }
        $categories = $categoriesQuery->get();

        $clients = [];
        if ($cure->client) {
            $clients = [[
                'id' => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
                'phone' => $cure->client->phone ?? '',
                'email' => $cure->client->email ?? '',
                'national_code' => $cure->client->national_code ?? '',
                'case_number' => $cure->client->case_number ?? '',
            ]];
        }

        $assignableRolesWithUsers = $this->getAssignableRolesWithUsers($settings);

        // Parse snapshot data (it should be exactly the output of mapPlanToJs)
        $planJs = is_string($snapshot->data) ? json_decode($snapshot->data, true) : $snapshot->data;

        // Ensure the snapshots list in the view contains ALL snapshots of the plan,
        // so that the user can navigate the full history even when viewing an old snapshot!
        $snapshots = $cure->snapshots()->with('user')->get()->map(fn($s) => [
            'id' => $s->id,
            'status_from' => $s->status_from,
            'status_from_label' => $this->getStatusLabel($s->status_from, $settings),
            'status_to' => $s->status_to,
            'status_to_label' => $this->getStatusLabel($s->status_to, $settings),
            'changed_by_name' => $s->user?->name ?? 'سیستم',
            'created_at' => $s->created_at->format('Y-m-d H:i:s'),
            'display_date' => \Morilog\Jalali\Jalalian::fromDateTime($s->created_at)->format('Y/m/d H:i'),
            'items' => is_string($s->data['items'] ?? null) ? json_decode($s->data['items'], true) : ($s->data['items'] ?? []),
            'total' => $s->data['total'] ?? 0,
            'notes' => $s->data['notes'] ?? '',
            'discount_amount' => $s->data['discount_amount'] ?? 0,
            'discount_type' => $s->data['discount_type'] ?? 'fixed',
            'installment_option_title' => $s->data['installment_option_title'] ?? null,
            'installment_down_payment' => $s->data['installment_down_payment'] ?? 0,
            'installment_monthly_amount' => $s->data['installment_monthly_amount'] ?? 0,
            'installment_fee_value' => $s->data['installment_fee_value'] ?? 0,
            'installment_months' => $s->data['installment_months'] ?? 0,
        ]);
        
        if (is_array($planJs)) {
            $planJs['snapshots'] = $snapshots;
        }

        return view('booking::user.cure.index', [
            'servicesJs' => $services,
            'planJs' => $planJs,
            'isReadOnly' => true,
            'isSnapshotView' => true,
            'snapshotLabel' => $this->getStatusLabel($snapshot->status_to, $settings),
            'categories' => $categories,
            'clients' => $clients,
            'settings' => $settings,
            'installmentTypes' => $installmentTypes,
            'assignableRolesWithUsers' => $assignableRolesWithUsers,
            'cureStatuses' => $settings->cure_statuses ?? [],
        ]);
    }

    /**
     * Show the edit form for a treatment plan.
     */
    public function edit(TreatmentPlan $cure)
    {
        $user = auth()->user();
        $settings = BookingSetting::current();
        $settings = $this->applyRoundingSettings($settings);
        $cureAllowEditConfirmed = (bool)($settings->cure_allow_edit_confirmed ?? false);

        abort_unless(
            $user->can('booking.cure.edit') ||
            $user->can('booking.cure.edit.own') ||
            $user->can('booking.cure.edit.assigned') ||
            $user->can('booking.cure.manage'),
            403
        );

        if (
            !$user->hasRole('super-admin') &&
            !$user->can('booking.cure.edit') &&
            !$user->can('booking.cure.manage')
        ) {
            $isCreator = ($cure->user_id === $user->id);
            $isAssigned = false;
            $assignedUsers = $cure->assigned_users ?? [];
            if (is_array($assignedUsers)) {
                foreach ($assignedUsers as $au) {
                    if (isset($au['user_id']) && (int)$au['user_id'] === $user->id) {
                        $isAssigned = true;
                        break;
                    }
                }
            }
            if ($isCreator && $user->can('booking.cure.edit.own')) {
                // Allowed
            } elseif ($isAssigned && $user->can('booking.cure.edit.assigned')) {
                // Allowed
            } else {
                abort(403);
            }
        }

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

        $allowedCategories = $settings->cure_allowed_categories ?? [];
        $servicesQuery = BookingService::with('category')->orderBy('name');
        if (!empty($allowedCategories)) {
            $servicesQuery->where(function ($q) use ($allowedCategories) {
                $q->whereIn('category_id', $allowedCategories)
                  ->orWhereHas('categories', function ($q2) use ($allowedCategories) {
                      $q2->whereIn('booking_categories.id', $allowedCategories);
                  });
            });
        }

        $services = $servicesQuery->get()
            ->map(function ($s) {
                $cp = $s->custom_prices ?? [];
                if (!isset($cp['tabs']) && !isset($cp->tabs)) {
                    $cp = ['tabs' => []];
                }
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'base_price' => (float)$s->base_price,
                    'category_id' => $s->category_id,
                    'category_name' => $s->category?->name,
                    'custom_prices' => $cp,
                ];
            });

        $categoriesQuery = BookingCategory::orderBy('name');
        if (!empty($allowedCategories)) {
            $categoriesQuery->whereIn('id', $allowedCategories);
        }
        $categories = $categoriesQuery->get();

        $clients = [];
        if ($cure->client) {
            $clients = [[
                'id' => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
                'phone' => $cure->client->phone ?? '',
                'email' => $cure->client->email ?? '',
                'national_code' => $cure->client->national_code ?? '',
                'case_number' => $cure->client->case_number ?? '',
            ]];
        }

        $planJs = $this->mapPlanToJs($cure);
        $assignableRolesWithUsers = $this->getAssignableRolesWithUsers($settings);

        return view('booking::user.cure.index', [
            'servicesJs' => $services,
            'planJs' => $planJs,
            'isReadOnly' => false,
            'categories' => $categories,
            'clients' => $clients,
            'settings' => $settings,
            'installmentTypes' => $installmentTypes,
            'assignableRolesWithUsers' => $assignableRolesWithUsers,
            'cureStatuses' => $settings->cure_statuses ?? [],
        ]);
    }

    /**
     * Update an existing treatment plan.
     */
    public function update(Request $request, TreatmentPlan $cure)
    {
        $user = auth()->user();
        $setting = BookingSetting::current();
        $cureAllowEditConfirmed = (bool)($setting->cure_allow_edit_confirmed ?? false);

        abort_unless(
            $user->can('booking.cure.edit') ||
            $user->can('booking.cure.edit.own') ||
            $user->can('booking.cure.edit.assigned') ||
            $user->can('booking.cure.manage'),
            403
        );

        if (
            !$user->hasRole('super-admin') &&
            !$user->can('booking.cure.edit') &&
            !$user->can('booking.cure.manage')
        ) {
            $isCreator = ($cure->user_id === $user->id);
            $isAssigned = false;
            $assignedUsers = $cure->assigned_users ?? [];
            if (is_array($assignedUsers)) {
                foreach ($assignedUsers as $au) {
                    if (isset($au['user_id']) && (int)$au['user_id'] === $user->id) {
                        $isAssigned = true;
                        break;
                    }
                }
            }
            if ($isCreator && $user->can('booking.cure.edit.own')) {
                // Allowed
            } elseif ($isAssigned && $user->can('booking.cure.edit.assigned')) {
                // Allowed
            } else {
                abort(403);
            }
        }

        if ($cure->status === 'confirmed') {
            abort_unless(
                $cureAllowEditConfirmed && (
                    $user->can('booking.cure.edit.confirmed') ||
                    $user->can('booking.cure.manage')
                ),
                403
            );
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $this->planValidationRules());

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $data = $validator->validated();

        $targetStatus = $data['status'] ?? $cure->status;
        $oldStatus = $cure->status;
        $statusChanged = ($oldStatus !== $targetStatus);

        if (!$cure->canTransitionTo($targetStatus, $user)) {
            $statusName = $targetStatus;
            foreach (($setting->cure_statuses ?? []) as $st) {
                if ($st['id'] === $targetStatus) {
                    $statusName = $st['name'];
                    break;
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی کافی برای ثبت/تغییر وضعیت طرح درمان به "' . $statusName . '" را ندارید.'
            ], 403);
        }

        $planData = $this->buildPlanData($data);
        $planData['client_id'] = $data['client_id'];

        try {
            $cure->update($planData);
            
            // Capture the snapshot AFTER updating the DB so it captures the actual post-save state.
            $freshCure = $cure->fresh();
            $snapshotData = $this->mapPlanToJs($freshCure);

            \Modules\Booking\App\Models\TreatmentPlanSnapshot::create([
                'treatment_plan_id' => $cure->id,
                'status_from' => $oldStatus,
                'status_to' => $targetStatus,
                'data' => $snapshotData,
                'changed_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'طرح درمان با موفقیت بروزرسانی شد.',
                'id' => $cure->id,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a treatment plan.
     */
    public function destroy(TreatmentPlan $cure)
    {
        $user = auth()->user();
        abort_unless(
            $user->can('booking.cure.delete') ||
            $user->can('booking.cure.delete.own') ||
            $user->can('booking.cure.delete.assigned') ||
            $user->can('booking.cure.manage'),
            403
        );

        if (
            !$user->hasRole('super-admin') &&
            !$user->can('booking.cure.delete') &&
            !$user->can('booking.cure.manage')
        ) {
            $isCreator = ($cure->user_id === $user->id);
            $isAssigned = false;
            $assignedUsers = $cure->assigned_users ?? [];
            if (is_array($assignedUsers)) {
                foreach ($assignedUsers as $au) {
                    if (isset($au['user_id']) && (int)$au['user_id'] === $user->id) {
                        $isAssigned = true;
                        break;
                    }
                }
            }
            if ($isCreator && $user->can('booking.cure.delete.own')) {
                // Allowed
            } elseif ($isAssigned && $user->can('booking.cure.delete.assigned')) {
                // Allowed
            } else {
                abort(403);
            }
        }

        $cure->delete();

        return redirect()->route('user.booking.cure.list')
            ->with('success', 'طرح درمان حذف شد.');
    }

    private function mapPlanToJs(TreatmentPlan $cure): array
    {
        $items = $cure->items;
        if (is_string($items)) {
            $items = json_decode($items, true);
        }
        if (!is_array($items)) $items = [];

        $generatedCheques = $cure->generated_cheques;
        if (is_string($generatedCheques)) {
            $generatedCheques = json_decode($generatedCheques, true);
        }
        if (!is_array($generatedCheques)) $generatedCheques = [];

        $installmentBreakdown = $cure->installment_breakdown;
        if (is_string($installmentBreakdown)) {
            $installmentBreakdown = json_decode($installmentBreakdown, true);
        }

        $settings = BookingSetting::current();
        $snapshots = $cure->snapshots()->with('user')->get()->map(fn($s) => [
            'id' => $s->id,
            'status_from' => $s->status_from,
            'status_from_label' => $this->getStatusLabel($s->status_from, $settings),
            'status_to' => $s->status_to,
            'status_to_label' => $this->getStatusLabel($s->status_to, $settings),
            'changed_by_name' => $s->user?->name ?? 'سیستم',
            'created_at' => $s->created_at->format('Y-m-d H:i:s'),
            'display_date' => \Morilog\Jalali\Jalalian::fromDateTime($s->created_at)->format('Y/m/d H:i'),
            'items' => is_string($s->data['items'] ?? null) ? json_decode($s->data['items'], true) : ($s->data['items'] ?? []),
            'total' => $s->data['total'] ?? 0,
            'notes' => $s->data['notes'] ?? '',
            'discount_amount' => $s->data['discount_amount'] ?? 0,
            'discount_type' => $s->data['discount_type'] ?? 'fixed',
            'installment_option_title' => $s->data['installment_option_title'] ?? null,
            'installment_down_payment' => $s->data['installment_down_payment'] ?? 0,
            'installment_monthly_amount' => $s->data['installment_monthly_amount'] ?? 0,
            'installment_fee_value' => $s->data['installment_fee_value'] ?? 0,
            'installment_months' => $s->data['installment_months'] ?? 0,
        ]);

        return [
            'id' => $cure->id,
            'client' => $cure->client ? [
                'id' => $cure->client->id,
                'full_name' => $cure->client->full_name ?? '',
                'phone' => $cure->client->phone ?? '',
                'email' => $cure->client->email ?? '',
                'national_code' => $cure->client->national_code ?? '',
                'case_number' => $cure->client->case_number ?? '',
            ] : null,
            'patient_name' => $cure->patient_name,
            'status' => $cure->status,
            'notes' => $cure->notes,
            'discount_amount' => $cure->discount_amount,
            'discount_type' => $cure->discount_type,
            'items' => $items,

            'installment_option_id' => $cure->installment_option_id,
            'installment_option_title' => $cure->installment_option_title,
            'installment_down_payment' => $cure->installment_down_payment,
            'installment_monthly_amount' => $cure->installment_monthly_amount,
            'installment_fee_value' => $cure->installment_fee_value,
            'installment_months' => $cure->installment_months,
            'installment_count' => $cure->installment_count,

            'installment_due_day' => $cure->installment_due_day,
            'installment_start_date' => $cure->installment_start_date,
            'installment_interval_months' => $cure->installment_interval_months,
            'installment_down_payment_percent' => $cure->installment_down_payment_percent,
            'installment_fee_percent' => $cure->installment_fee_percent,
            'installment_cash_now' => $cure->installment_cash_now,
            'installment_uncovered_total' => $cure->installment_uncovered_total,
            'installment_breakdown' => $installmentBreakdown,
            'generated_cheques' => $generatedCheques,
            'assigned_users' => $cure->assigned_users ?? [],
            'snapshots' => $snapshots,
        ];
    }

    private function getAssignableRolesWithUsers(BookingSetting $settings): array
    {
        $assignableRoleIds = $settings->cure_assignable_roles ?? [];
        $assignableRolesWithUsers = [];
        if (!empty($assignableRoleIds)) {
            $roles = \Spatie\Permission\Models\Role::whereIn('id', $assignableRoleIds)->get();
            foreach ($roles as $role) {
                $users = \App\Models\User::role($role->name)->get()->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name ?? $u->full_name ?? $u->username ?? 'کاربر ' . $u->id,
                ]);
                $assignableRolesWithUsers[] = [
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                    'role_label' => match ($role->name) {
                        'admin' => 'مدیر',
                        'doctor' => 'پزشک',
                        'assistant' => 'دستیار',
                        default => $role->name,
                    },
                    'users' => $users,
                ];
            }
        }
        return $assignableRolesWithUsers;
    }

    private function getStatusLabel(?string $status, BookingSetting $setting): string
    {
        if (!$status) return '-';
        $statuses = $setting->cure_statuses ?? [];
        foreach ($statuses as $st) {
            if ($st['id'] === $status) {
                return $st['name'];
            }
        }
        return match ($status) {
            'draft'     => 'پیش‌نویس',
            'confirmed' => 'تأیید شده',
            default     => ucfirst($status),
        };
    }

    public function changeStatus(Request $request, TreatmentPlan $cure)
    {
        $user = auth()->user();

        abort_unless(
            $user->can('booking.cure.edit') ||
            $user->can('booking.cure.edit.own') ||
            $user->can('booking.cure.edit.assigned') ||
            $user->can('booking.cure.manage'),
            403
        );

        if (
            !$user->hasRole('super-admin') &&
            !$user->can('booking.cure.edit') &&
            !$user->can('booking.cure.manage')
        ) {
            $isCreator = ($cure->user_id === $user->id);
            $isAssigned = false;
            $assignedUsers = $cure->assigned_users ?? [];
            if (is_array($assignedUsers)) {
                foreach ($assignedUsers as $au) {
                    if (isset($au['user_id']) && (int)$au['user_id'] === $user->id) {
                        $isAssigned = true;
                        break;
                    }
                }
            }
            if ($isCreator && $user->can('booking.cure.edit.own')) {
                // Allowed
            } elseif ($isAssigned && $user->can('booking.cure.edit.assigned')) {
                // Allowed
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'شما دسترسی ویرایش این طرح درمان را ندارید.'
                ], 403);
            }
        }
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        $newStatus = $request->input('status');

        if ($cure->status === $newStatus) {
            return response()->json([
                'success' => true,
                'message' => 'وضعیت تغییری نکرده است.',
            ]);
        }

        if (!$cure->canTransitionTo($newStatus, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'انتقال به این وضعیت مجاز نیست یا دسترسی کافی ندارید.',
            ], 403);
        }

        $oldStatus = $cure->status;
        
        $cure->update(['status' => $newStatus]);
        
        $freshCure = $cure->fresh();
        $snapshotData = $this->mapPlanToJs($freshCure);

        // Create snapshot of the post-save state
        \Modules\Booking\App\Models\TreatmentPlanSnapshot::create([
            'treatment_plan_id' => $cure->id,
            'status_from' => $oldStatus,
            'status_to' => $newStatus,
            'data' => $snapshotData,
            'changed_by' => $user->id,
        ]);

        $cure->status = $newStatus;
        $cure->save();

        return response()->json([
            'success' => true,
            'message' => 'وضعیت طرح درمان با موفقیت تغییر کرد.',
            'status' => $newStatus,
            'status_label' => $cure->status_label,
            'status_color' => $cure->status_color,
        ]);
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
                    'tooth_context' => $inst->tooth_context,
                    'item_context' => $inst->item_context,
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
