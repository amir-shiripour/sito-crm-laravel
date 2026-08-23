<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingSlotHold;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Booking\Entities\BookingForm;
use Modules\Booking\Entities\BookingPayment; // اضافه شده برای استفاده در show
use Modules\Booking\Services\AppointmentService;
use App\Models\User;
use Modules\Clients\Entities\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\CalendarUtils;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service) {}

    public function index(Request $request)
    {
        $settings = BookingSetting::current();
        $user = $request->user();

        $query = Appointment::query()
            ->with(['service', 'provider', 'client']);

        // Permission Scope
        if (! $this->isAdminUser($user) && ! $user->can('booking.appointments.view.all')) {
            if ($user->can('booking.appointments.view.own') || $this->userIsProvider($user, $settings)) {
                $query->where(function ($q) use ($user) {
                    $q->where('provider_user_id', $user->id)
                      ->orWhere('created_by_user_id', $user->id);
                });
            } else {
                $query->where('created_by_user_id', $user->id);
            }
        }

        // --- Filtering ---

        // 1. Search
        if ($q = $request->input('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('id', $q)
                    ->orWhereHas('client', function ($c) use ($q) {
                        $c->where('full_name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhere('national_code', 'like', "%{$q}%");
                    });
            });
        }

        // 2. Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // 3. Service
        if ($serviceId = $request->input('service_id')) {
            $query->where('service_id', $serviceId);
        }

        // 4. Provider
        if ($providerId = $request->input('provider_user_id')) {
            $query->where('provider_user_id', $providerId);
        }

        // 5. Date Range
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        if ($dateFrom = $request->input('date_from')) {
            // Jalali Datepicker usually sends YYYY/MM/DD
            $localDate = $this->convertJalaliDateToLocal($dateFrom, $scheduleTz);
            if ($localDate) {
                $query->where('start_at_utc', '>=', $localDate->startOfDay()->timezone('UTC'));
            }
        }
        if ($dateTo = $request->input('date_to')) {
            $localDate = $this->convertJalaliDateToLocal($dateTo, $scheduleTz);
            if ($localDate) {
                $query->where('start_at_utc', '<=', $localDate->endOfDay()->timezone('UTC'));
            }
        }

        // --- Stats ---
        $statsQuery = $query->clone();
        $totalCount = $statsQuery->count();
        $statusCounts = $statsQuery->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // --- Sorting ---
        $sort = $request->input('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('start_at_utc');
        } elseif ($sort === 'created_desc') {
            $query->orderByDesc('created_at');
        } else {
            // Default: Newest (Start Time) DESC
            $query->orderByDesc('start_at_utc');
        }

        $appointments = $query->paginate(25)->withQueryString();

        // Data for Filters
        $services = BookingService::query()->orderBy('name')->get(['id', 'name']);

        $providers = [];
        if ($this->isAdminUser($user)) {
            // Filter providers based on allowed roles in settings
            $roleIds = array_values(array_filter(
                array_map('intval', (array) ($settings->allowed_roles ?? [])),
                fn($v) => $v > 0
            ));

            $providersQuery = User::query();

            if (!empty($roleIds)) {
                $providersQuery->whereHas('roles', fn($r) => $r->whereIn('id', $roleIds));
            } else {
                // Fallback: if no roles defined, show users who are attached to services
                $providersQuery->whereIn('id', function($q) {
                    $q->select('provider_user_id')->from('booking_service_providers');
                });
            }

            $providers = $providersQuery->orderBy('name')->get(['id', 'name']);
        }

        return view('booking::user.appointments.index', compact(
            'appointments',
            'services',
            'providers',
            'totalCount',
            'statusCounts'
        ));
    }

    public function schedule(Request $request)
    {
        return view('booking::user.schedule.index');
    }

    public function create()
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentCreateAccess(request(), $settings);

        $shouldLog = (bool) config('app.debug') || (bool) config('booking.debug_logs', false);

        // مرحله اول از تنظیمات
        $flow = $settings->operator_appointment_flow ?: 'PROVIDER_FIRST';

        $user = Auth::user();
        $fixedProvider = null;

        if ($this->userIsProvider($user, $settings) && ! $this->isAdminUser($user)) {
            $flow = 'PROVIDER_FIRST';
            $fixedProvider = $user;
        }

        if ($shouldLog) {
            Log::info('[Booking][Appointments][Create] create view context', [
                'user_id' => $user?->id,
                'is_admin' => $this->isAdminUser($user),
                'is_provider' => $this->userIsProvider($user, $settings),
                'operator_flow_setting' => $settings->operator_appointment_flow,
                'final_flow' => $flow,
                'fixed_provider_id' => $fixedProvider?->id,
                'allowed_roles_raw' => $settings->allowed_roles,
            ]);
        }

        $isQueueEnabled = BookingSetting::isQueueEnabled() && class_exists(\Modules\Booking\Entities\BookingWaitlist::class);

        return view('booking::user.appointments.create', compact('settings', 'flow', 'fixedProvider', 'isQueueEnabled'));
    }

    public function store(Request $request)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentCreateAccess($request, $settings);

        $shouldLog = (bool) config('app.debug') || (bool) config('booking.debug_logs', false);

        $data = $request->validate([
            'service_id'        => ['required', 'integer', 'exists:booking_services,id'],
            'provider_user_id'  => ['required', 'integer', 'exists:users,id'],
            'client_id'         => ['required', 'integer', 'exists:clients,id'],
            'waitlist_id'       => ['nullable', 'integer'],

            // از UI اسلات
            'start_at_utc'      => ['nullable', 'date'],
            'end_at_utc'        => ['nullable', 'date'],
            'date_local'        => ['nullable', 'date_format:Y-m-d'],
            'start_time_local'  => ['nullable', 'date_format:H:i'],
            'end_time_local'    => ['nullable', 'date_format:H:i'],

            'notes'             => ['nullable', 'string'],
            'status'            => ['required', Rule::in([
                Appointment::STATUS_DRAFT,
                Appointment::STATUS_PENDING,
                Appointment::STATUS_PENDING_PAYMENT,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CANCELED_BY_ADMIN,
                Appointment::STATUS_CANCELED_BY_CLIENT,
                Appointment::STATUS_NO_SHOW,
                Appointment::STATUS_DONE,
                Appointment::STATUS_RESCHEDULED,
            ])],

            // اگر فرم داشت
            'appointment_form_response_json' => ['nullable', 'string'],
        ]);

        $authUser = $request->user();

        if ($shouldLog) {
            Log::info('[Booking][Appointments][Store] incoming', [
                'auth_user_id' => $authUser?->id,
                'service_id' => $data['service_id'] ?? null,
                'provider_user_id' => $data['provider_user_id'] ?? null,
                'client_id' => $data['client_id'] ?? null,
                'allowed_roles_raw' => $settings->allowed_roles,
            ]);
        }

        // provider باید جزو allowed_roles باشد
        $roleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn($v) => $v > 0
        ));

        if ($shouldLog) {
            Log::info('[Booking][Appointments][Store] provider role filter snapshot', [
                'roleIds' => $roleIds,
                'roleIds_empty' => empty($roleIds),
            ]);
        }

        if (!empty($roleIds)) {
            $isValidProvider = User::query()
                ->whereKey($data['provider_user_id'])
                ->whereHas('roles', fn($q) => $q->whereIn('id', $roleIds))
                ->exists();

            if (!$isValidProvider) {
                $providerUser = User::query()->whereKey($data['provider_user_id'])->first();
                $providerRoleIds = $providerUser
                    ? $providerUser->roles()->pluck('id')->map(fn($v) => (int) $v)->all()
                    : null;

                Log::warning('[Booking][Appointments][Store] provider rejected by allowed_roles', [
                    'provider_user_id' => (int) $data['provider_user_id'],
                    'provider_role_ids' => $providerRoleIds,
                    'allowed_role_ids' => $roleIds,
                    'allowed_roles_raw' => $settings->allowed_roles,
                ]);

                return back()
                    ->withErrors(['provider_user_id' => 'ارائه‌دهنده انتخاب‌شده مجاز نیست.'])
                    ->withInput();
            }
        }

        if ($this->userIsProvider($authUser, $settings) && ! $this->isAdminUser($authUser)) {
            if ((int) $data['provider_user_id'] !== (int) $authUser->id) {
                Log::warning('[Booking][Appointments][Store] provider tried to create for another provider', [
                    'auth_user_id' => (int) $authUser->id,
                    'requested_provider_user_id' => (int) $data['provider_user_id'],
                ]);

                return back()
                    ->withErrors(['provider_user_id' => 'ارائه‌دهنده انتخاب‌شده معتبر نیست.'])
                    ->withInput();
            }
        }

        // client باید قابل مشاهده باشد
        $client = Client::query()->whereKey($data['client_id'])->firstOrFail();
        if (!$client->isVisibleFor($request->user())) {
            Log::warning('[Booking][Appointments][Store] client not visible for user', [
                'auth_user_id' => $authUser?->id,
                'client_id' => (int) $data['client_id'],
            ]);
            abort(403, 'شما به این مشتری دسترسی ندارید.');
        }

        // سرویس و رابطه provider-service باید معتبر و فعال باشد
        $service = BookingService::query()->whereKey($data['service_id'])->firstOrFail();

        $sp = BookingServiceProvider::query()
            ->where('service_id', $service->id)
            ->where('provider_user_id', (int)$data['provider_user_id'])
            ->first();

        if (!$sp || !$sp->is_active) {
            Log::warning('[Booking][Appointments][Store] service-provider relation invalid/inactive', [
                'service_id' => (int) $service->id,
                'provider_user_id' => (int) $data['provider_user_id'],
                'sp_exists' => (bool) $sp,
                'sp_active' => $sp ? (bool) $sp->is_active : null,
            ]);

            return back()
                ->withErrors(['service_id' => 'این سرویس برای این ارائه‌دهنده فعال نیست.'])
                ->withInput();
        }

        // زمان‌ها
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $allowManualTimeOverride = (bool) ($settings->allow_manual_time_override ?? false);
        $hasManualTimeInputs = !empty($data['date_local']) && !empty($data['start_time_local']) && !empty($data['end_time_local']);

        $usesCustomSchedule = (bool) $service->custom_schedule_enabled
            || ($allowManualTimeOverride && $hasManualTimeInputs && (empty($data['start_at_utc']) || empty($data['end_at_utc'])));

        if ($usesCustomSchedule) {
            if (empty($data['date_local']) || empty($data['start_time_local']) || empty($data['end_time_local'])) {
                return back()
                    ->withErrors(['start_time_local' => 'لطفاً تاریخ و ساعت شروع/پایان را کامل وارد کنید.'])
                    ->withInput();
            }

            $startLocal = Carbon::createFromFormat('Y-m-d H:i', "{$data['date_local']} {$data['start_time_local']}", $scheduleTz);
            $endLocal   = Carbon::createFromFormat('Y-m-d H:i', "{$data['date_local']} {$data['end_time_local']}", $scheduleTz);

            $startUtc = $startLocal->copy()->timezone('UTC');
            $endUtc   = $endLocal->copy()->timezone('UTC');
        } else {
            if (empty($data['start_at_utc']) || empty($data['end_at_utc'])) {
                return back()
                    ->withErrors(['start_at_utc' => 'لطفاً یک اسلات زمانی را انتخاب کنید.'])
                    ->withInput();
            }

            $startUtc = Carbon::parse($data['start_at_utc'], 'UTC');
            $endUtc   = Carbon::parse($data['end_at_utc'], 'UTC');
        }

        if ($endUtc->lte($startUtc)) {
            Log::warning('[Booking][Appointments][Store] invalid slot time range', [
                'start_at_utc' => $startUtc->toIso8601String(),
                'end_at_utc' => $endUtc->toIso8601String(),
            ]);
            return back()->withErrors(['start_at_utc' => 'بازه زمانی اسلات نامعتبر است.'])->withInput();
        }

        $formJson = null;
        if (!empty($data['appointment_form_response_json'])) {
            $decoded = json_decode($data['appointment_form_response_json'], true);
            $formJson = is_array($decoded) ? $decoded : null;

            if ($shouldLog && $formJson === null) {
                Log::warning('[Booking][Appointments][Store] invalid appointment_form_response_json', [
                    'raw' => $data['appointment_form_response_json'],
                ]);
            }
        }

        if ($shouldLog) {
            Log::info('[Booking][Appointments][Store] Attempting to create appointment', [
                'service_id' => $service->id,
                'provider_id' => $data['provider_user_id'],
                'client_id' => $client->id,
                'start_utc' => $startUtc->toIso8601String(),
                'end_utc' => $endUtc->toIso8601String(),
            ]);
        }

        try {
            $this->service->createAppointmentByOperator(
                (int) $service->id,
                (int) $data['provider_user_id'],
                (int) $client->id,
                $startUtc->toIso8601String(),
                $endUtc->toIso8601String(),
                createdByUserId: $request->user()->id,
                notes: $data['notes'] ?? null,
                appointmentFormResponse: $formJson,
                status: $data['status']
            );
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            Log::error('[Booking][Appointments][Store] Failed to create appointment', [
                'message' => $e->getMessage(),
                'start_utc' => $startUtc->toIso8601String(),
                'end_utc' => $endUtc->toIso8601String(),
                'trace' => $e->getTraceAsString(),
            ]);

            $message = match ($e->getMessage()) {
                'Slot capacity is full.' => 'ظرفیت این بازه زمانی تکمیل است.',
                'Day capacity is full.' => 'ظرفیت روز تکمیل است.',
                'This day is closed.' => 'این روز بسته است.',
                'Slot is outside work windows.' => 'این بازه خارج از ساعات کاری است.',
                'Slot overlaps with break.' => 'این بازه با زمان استراحت تداخل دارد.',
                'Slot crosses day boundary.' => 'بازه انتخابی باید داخل همان روز باشد.',
                default => 'امکان ثبت نوبت در این بازه وجود ندارد.',
            };

            return back()
                ->withErrors(['start_at_utc' => $message])
                ->withInput();
        }

        if ($shouldLog) {
            Log::info('[Booking][Appointments][Store] appointment created', [
                'service_id' => (int) $service->id,
                'provider_user_id' => (int) $data['provider_user_id'],
                'client_id' => (int) $client->id,
                'start_at_utc' => $startUtc->toIso8601String(),
                'end_at_utc' => $endUtc->toIso8601String(),
                'created_by_user_id' => (int) $request->user()->id,
            ]);
        }

        if (!empty($data['waitlist_id']) && class_exists(\Modules\Booking\Entities\BookingWaitlist::class)) {
            $waitlist = \Modules\Booking\Entities\BookingWaitlist::find($data['waitlist_id']);
            if ($waitlist && $waitlist->status !== \Modules\Booking\Entities\BookingWaitlist::STATUS_CONVERTED) {
                $waitlist->update([
                    'status' => \Modules\Booking\Entities\BookingWaitlist::STATUS_CONVERTED,
                    'converted_at' => now(),
                ]);
            }
        }

        return redirect()
            ->route('user.booking.appointments.index')
            ->with('success', 'نوبت با موفقیت ثبت شد.');
    }

    public function show(Request $request, Appointment $appointment)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentViewAccess($request->user(), $appointment, $settings);

        $appointment->load(['service.appointmentForm', 'payments']);

        // --- پردازش لاجیک View به Controllers منتقل شد (طبق اصول معماری MVC) ---
        $tz = config('booking.timezones.display_default', 'Asia/Tehran');
        $startLocal = $appointment->start_at_utc?->copy()->timezone($tz);
        $endLocal = $appointment->end_at_utc?->copy()->timezone($tz);
        $entryLocal = $appointment->entry_at_utc?->copy()->timezone($tz);
        $exitLocal = $appointment->exit_at_utc?->copy()->timezone($tz);

        $dateJalali = $startLocal
            ? \Morilog\Jalali\Jalalian::fromDateTime($startLocal)->format('Y/m/d')
            : '—';
        $startTime = $startLocal ? $startLocal->format('H:i') : '—';
        $endTime = $endLocal ? $endLocal->format('H:i') : '—';

        $statusMap = [
            Appointment::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
            Appointment::STATUS_PENDING_PAYMENT => ['label' => 'در انتظار پرداخت', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200'],
            Appointment::STATUS_CONFIRMED => ['label' => 'تایید شده', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'],
            Appointment::STATUS_CANCELED_BY_ADMIN => ['label' => 'لغو شده (ادمین)', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
            Appointment::STATUS_CANCELED_BY_CLIENT => ['label' => 'لغو شده (مشتری)', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
            Appointment::STATUS_NO_SHOW => ['label' => 'عدم حضور', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
            Appointment::STATUS_DONE => ['label' => 'انجام شده', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'],
            Appointment::STATUS_RESCHEDULED => ['label' => 'جابجا شده', 'class' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-200'],
        ];
        $statusMeta = $statusMap[$appointment->status] ?? ['label' => $appointment->status, 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'];

        $entryValue = $entryLocal ? $entryLocal->format('H:i') : '—';
        $exitValue = $exitLocal ? $exitLocal->format('H:i') : '—';

        $rawFormResponses = $appointment->appointment_form_response_json ?? [];
        $formResponses = [];
        $legacyResponses = [];

        $hasToothForm = false;

        if (!empty($rawFormResponses) && $appointment->service && $appointment->service->appointmentForm) {
            $form = $appointment->service->appointmentForm;
            $formSchema = $form->schema_json;
            $fieldMeta = [];

            if (isset($formSchema['fields']) && is_array($formSchema['fields'])) {
                foreach ($formSchema['fields'] as $field) {
                    if (isset($field['name'])) {
                        $fType = $field['type'] ?? 'text';
                        if ($fType === 'tooth_number') {
                            $hasToothForm = true;
                        }
                        $fieldMeta[$field['name']] = [
                            'label' => $field['label'] ?? $field['name'],
                            'type' => $fType,
                        ];
                    }
                }
            }

            if ($form->form_type === 'TOOTH_NUMBER') {
                $hasToothForm = true;
            }

            // Collect user IDs for select-user-by-role fields
            $roleUserIds = [];
            foreach ($rawFormResponses as $key => $value) {
                if (isset($fieldMeta[$key]) && ($fieldMeta[$key]['type'] ?? '') === 'select-user-by-role') {
                    if (is_array($value)) {
                        foreach ($value as $v) {
                            if (is_numeric($v)) $roleUserIds[] = (int) $v;
                        }
                    } elseif (is_numeric($value)) {
                        $roleUserIds[] = (int) $value;
                    }
                }
            }

            $userNamesMap = [];
            if (!empty($roleUserIds)) {
                $userNamesMap = User::whereIn('id', array_unique($roleUserIds))->pluck('name', 'id')->toArray();
            }

            $legacyToothKeys = ['UR', 'UL', 'DR', 'LR', 'DL', 'LL'];

            foreach ($rawFormResponses as $key => $value) {
                $isToothKey = ($key === 'tooth_numbers' || str_contains(strtolower((string)$key), 'tooth'));
                $isLegacyToothKey = in_array($key, $legacyToothKeys, true);

                // If key is not explicitly defined in form schema and it's a tooth key/legacy tooth key, only include if service has a tooth form
                if (!isset($fieldMeta[$key])) {
                    if (($isToothKey || $isLegacyToothKey) && !$hasToothForm) {
                        continue;
                    }
                }

                $type = $fieldMeta[$key]['type'] ?? (
                    ($hasToothForm && ($isToothKey || $form->form_type === 'TOOTH_NUMBER'))
                        ? 'tooth_number'
                        : null
                );
                $label = $fieldMeta[$key]['label'] ?? ($key === 'tooth_numbers' ? 'نقشه دندانی' : $key);

                if ($type !== null || isset($fieldMeta[$key])) {
                    $displayValue = $value;

                    if ($type === 'select-user-by-role') {
                        if (is_array($value)) {
                            $names = array_map(fn($id) => $userNamesMap[$id] ?? "کاربر #{$id}", $value);
                            $displayValue = implode('، ', $names);
                        } elseif (is_numeric($value)) {
                            $displayValue = $userNamesMap[$value] ?? "کاربر #{$value}";
                        }
                    } elseif ($type === 'tooth_number') {
                        $displayValue = $value;
                    } elseif (is_array($value)) {
                        $displayValue = implode('، ', $value);
                    }

                    $formResponses[] = [
                        'label' => $label,
                        'value' => $displayValue,
                        'type' => $type ?? 'text',
                    ];
                } else {
                    $label = $key;
                    if ($key === 'UR') $label = 'شماره دندان (UR)';
                    elseif ($key === 'UL') $label = 'شماره دندان (UL)';
                    elseif ($key === 'DR' || $key === 'LR') $label = 'شماره دندان (LR)';
                    elseif ($key === 'DL' || $key === 'LL') $label = 'شماره دندان (LL)';

                    $legacyResponses[] = [
                        'label' => $label,
                        'value' => $value,
                        'type' => 'text',
                    ];
                }
            }
        } else if (!empty($rawFormResponses)) {
            // Fallback if form is not available — skip dental chart and legacy tooth numbers since service has no tooth form attached
            $legacyToothKeys = ['UR', 'UL', 'DR', 'LR', 'DL', 'LL'];
            foreach ($rawFormResponses as $key => $value) {
                $isTooth = $key === 'tooth_numbers' || str_contains(strtolower((string)$key), 'tooth');
                $isLegacyToothKey = in_array($key, $legacyToothKeys, true);

                if ($isTooth || $isLegacyToothKey) {
                    continue;
                }

                $legacyResponses[] = [
                    'label' => $key,
                    'value' => $value,
                    'type' => 'text',
                ];
            }
        }

        $appointment->load(['service', 'service.appointmentForm', 'payments', 'client']);
        $payments = $appointment->payments ?? collect();

        $servicePaymentMode = $appointment->service->payment_mode ?? BookingService::PAYMENT_MODE_NONE;
        $servicePaymentAmountType = $appointment->service->payment_amount_type ?? null;
        $servicePaymentAmountValue = (float) ($appointment->service->payment_amount_value ?? 0);
        $serviceBasePrice = (float) ($appointment->service->base_price ?? 0);

        $rawSuggestedAmount = match($servicePaymentAmountType) {
            BookingService::PAYMENT_AMOUNT_DEPOSIT  => $servicePaymentAmountValue,
            BookingService::PAYMENT_AMOUNT_FIXED    => $servicePaymentAmountValue,
            default                                 => $serviceBasePrice,
        };

        $settingsMap = \Illuminate\Support\Facades\Schema::hasTable('settings')
            ? \Modules\Settings\Entities\Setting::query()->pluck('value', 'key')->toArray()
            : [];

        // سیستم واحد پول اصلی را از settings#payment بررسی می‌کند (اگر تومان است، ورودی کاربر به ریال تبدیل می‌شود)
        $systemCurrency = strtolower($settingsMap['payment_currency'] ?? $settings->currency_unit ?? 'toman');
        $currencyUnit = ($systemCurrency === 'toman' || $systemCurrency === 'irt') ? 'IRT' : 'IRR';
        $currencyLabel = ($currencyUnit === 'IRT') ? 'تومان' : 'ریال';

        $suggestedAmount = $rawSuggestedAmount;

        $hasPaidPayment = $payments->contains('status', BookingPayment::STATUS_PAID);

        $paymentStatusMap = [
            BookingPayment::STATUS_PENDING   => ['label' => 'در انتظار پرداخت', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200'],
            BookingPayment::STATUS_PAID      => ['label' => 'پرداخت شده',       'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'],
            BookingPayment::STATUS_FAILED    => ['label' => 'ناموفق',           'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
            BookingPayment::STATUS_REFUNDED  => ['label' => 'برگشت داده شده',   'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'],
            BookingPayment::STATUS_CANCELLED => ['label' => 'لغو شده',          'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
        ];

        $servicePaymentModeLabel = match($servicePaymentMode) {
            BookingService::PAYMENT_MODE_OPTIONAL => 'اختیاری',
            BookingService::PAYMENT_MODE_REQUIRED => 'الزامی',
            default                                => 'بدون پرداخت',
        };

        $settingsMap = \Illuminate\Support\Facades\Schema::hasTable('settings')
            ? \Modules\Settings\Entities\Setting::query()->pluck('value', 'key')->toArray()
            : [];

        $posDevices = is_string($settingsMap['pos_devices'] ?? null) ? (json_decode($settingsMap['pos_devices'], true) ?: []) : [];
        $bankAccounts = is_string($settingsMap['bank_transfer_accounts'] ?? null) ? (json_decode($settingsMap['bank_transfer_accounts'], true) ?: []) : [];
        $installmentTypes = is_string($settingsMap['installment_types'] ?? null) ? (json_decode($settingsMap['installment_types'], true) ?: []) : [];

        $onlineGateways = [];
        if (($settingsMap['zarinpal_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'zarinpal', 'label' => 'درگاه زرین‌پال'];
        }
        if (($settingsMap['zibal_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'zibal', 'label' => 'درگاه زیبال'];
        }
        if (($settingsMap['behpardakht_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'behpardakht', 'label' => 'درگاه بهپرداخت ملت'];
        }

        // Build available payment methods based on section statuses in /settings#payment
        $availablePaymentMethods = [];

        if (!empty($onlineGateways)) {
            $availablePaymentMethods['online'] = 'درگاه پرداخت آنلاین';
        }

        if (($settingsMap['pos_status'] ?? '') === 'active' || (!isset($settingsMap['pos_status']) && !empty($posDevices))) {
            $availablePaymentMethods['pos'] = 'دستگاه کارتخوان (POS)';
        }

        if (($settingsMap['bank_transfer_status'] ?? '') === 'active' || (!isset($settingsMap['bank_transfer_status']) && !empty($bankAccounts))) {
            $availablePaymentMethods['transfer'] = 'انتقال بانکی / کارت به کارت (شبا)';
        }

        if (($settingsMap['cod_status'] ?? '') === 'active') {
            $availablePaymentMethods['cod'] = 'پرداخت در محل (نقد)';
        }

        if (($settingsMap['installment_status'] ?? '') === 'active' || (!isset($settingsMap['installment_status']) && !empty($installmentTypes))) {
            $availablePaymentMethods['installment'] = 'پرداخت قسطی / چک';
        }

        // Manual option is always available for admin
        $availablePaymentMethods['manual'] = 'ثبت دستی (ادمین)';

        $paymentSubItems = [
            'pos' => array_values(array_map(fn($d) => [
                'id' => $d['id'] ?? ($d['name'] ?? ''),
                'label' => ($d['name'] ?? 'کارتخوان') . (!empty($d['account_number']) ? ' (حساب: ' . $d['account_number'] . ')' : '')
            ], $posDevices)),
            'transfer' => array_values(array_map(fn($a) => [
                'id' => $a['id'] ?? ($a['bank_name'] ?? ''),
                'label' => ($a['bank_name'] ?? 'بانک') . (!empty($a['owner_name']) ? ' - ' . $a['owner_name'] : '') . (!empty($a['card_number']) ? ' (' . $a['card_number'] . ')' : (!empty($a['iban']) ? ' (' . $a['iban'] . ')' : ''))
            ], $bankAccounts)),
            'installment' => array_values(array_map(fn($i) => [
                'id' => $i['id'] ?? ($i['title'] ?? ''),
                'label' => ($i['title'] ?? 'طرح اقساطی') . (!empty($i['default_tier_config']['max_months']) ? ' (' . $i['default_tier_config']['max_months'] . ' ماهه)' : '')
            ], $installmentTypes)),
            'online' => $onlineGateways,
        ];

        return view('booking::user.appointments.show', compact(
            'appointment', 'settings', 'dateJalali', 'startTime', 'endTime',
            'statusMeta', 'entryValue', 'exitValue', 'formResponses', 'legacyResponses',
            'payments', 'paymentStatusMap', 'servicePaymentMode', 'servicePaymentModeLabel',
            'suggestedAmount', 'hasPaidPayment', 'availablePaymentMethods', 'paymentSubItems',
            'currencyUnit', 'currencyLabel'
        ));
    }

    public function edit(Request $request, Appointment $appointment)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentEditAccess($request->user(), $appointment, $settings);

        $user = $request->user();
        $appointment->load(['payments', 'service']);

        $services = BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $providersQuery = User::query();
        $roleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn($v) => $v > 0
        ));

        if (! $this->isAdminUser($user) && !empty($roleIds)) {
            $providersQuery->whereHas('roles', fn($r) => $r->whereIn('id', $roleIds));
        }

        $providers = $providersQuery->orderBy('name')->get(['id', 'name']);

        $clients = Client::query()
            ->visibleForUser($user)
            ->orderBy('full_name')
            ->limit(200)
            ->get(['id', 'full_name']);

        $payments = $appointment->payments ?? collect();
        $servicePaymentMode = $appointment->service->payment_mode ?? BookingService::PAYMENT_MODE_NONE;
        $hasPaidPayment = $payments->contains('status', BookingPayment::STATUS_PAID);

        $paymentStatusMap = [
            BookingPayment::STATUS_PENDING   => ['label' => 'در انتظار پرداخت', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200'],
            BookingPayment::STATUS_PAID      => ['label' => 'پرداخت شده',       'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'],
            BookingPayment::STATUS_FAILED    => ['label' => 'ناموفق',           'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
            BookingPayment::STATUS_REFUNDED  => ['label' => 'برگشت داده شده',   'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'],
            BookingPayment::STATUS_CANCELLED => ['label' => 'لغو شده',          'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
        ];

        return view('booking::user.appointments.edit', compact(
            'appointment', 'settings', 'services', 'providers', 'clients',
            'payments', 'paymentStatusMap', 'servicePaymentMode', 'hasPaidPayment'
        ));
    }

    /**
     * ثبت پرداخت جدید توسط ادمین
     */
    public function storePayment(Request $request, Appointment $appointment)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentEditAccess($request->user(), $appointment, $settings);

        if ($request->has('amount')) {
            $cleaned = preg_replace('/[^\d.]/', '', (string) $request->input('amount'));
            $request->merge(['amount' => $cleaned]);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in([
                BookingPayment::STATUS_PENDING,
                BookingPayment::STATUS_PAID,
                BookingPayment::STATUS_FAILED,
                BookingPayment::STATUS_REFUNDED,
                BookingPayment::STATUS_CANCELLED,
            ])],
            'gateway_ref' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'sub_item_label' => ['nullable', 'string', 'max:255'],
            'paid_at_jalali' => ['nullable', 'string', 'max:20'],
        ]);

        $settingsMap = \Illuminate\Support\Facades\Schema::hasTable('settings')
            ? \Modules\Settings\Entities\Setting::query()->pluck('value', 'key')->toArray()
            : [];

        $systemCurrency = strtolower($settingsMap['payment_currency'] ?? $settings->currency_unit ?? 'toman');
        $currencyUnit = ($systemCurrency === 'toman' || $systemCurrency === 'irt') ? 'IRT' : 'IRR';
        
        $inputAmount = (float) $data['amount'];
        $amountInRials = ($currencyUnit === 'IRT') ? ($inputAmount * 10) : $inputAmount;

        $paidAt = null;
        if (!empty($data['paid_at_jalali'])) {
            $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
            $localDate = $this->convertJalaliDateToLocal($data['paid_at_jalali'], $scheduleTz);
            if ($localDate) {
                $paidAt = $localDate;
            }
        }
        if (!$paidAt && $data['status'] === BookingPayment::STATUS_PAID) {
            $paidAt = now();
        }

        $notes = $data['notes'] ?? '';
        if (!empty($data['sub_item_label'])) {
            $notes = $notes ? ($data['sub_item_label'] . ' | ' . $notes) : $data['sub_item_label'];
        }

        $payment = BookingPayment::create([
            'appointment_id' => $appointment->id,
            'client_id' => $appointment->client_id,
            'type' => $data['type'] ?? 'manual',
            'amount' => $amountInRials,
            'currency_unit' => 'IRR',
            'status' => $data['status'],
            'gateway_ref' => $data['gateway_ref'] ?? null,
            'notes' => $notes ?: null,
            'paid_at' => $paidAt,
        ]);

        if ($data['status'] === BookingPayment::STATUS_PAID) {
            $this->service->markPaymentPaid($payment->id, $payment->gateway_ref);
        }

        $this->service->triggerWorkflow('payment_manual_recorded', $appointment->fresh());

        return redirect()
            ->route('user.booking.appointments.show', $appointment)
            ->with('success', 'پرداخت جدید با موفقیت ثبت شد.');
    }

    /**
     * ویرایش پرداخت توسط ادمین
     */
    public function updatePayment(Request $request, Appointment $appointment, BookingPayment $payment)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentEditAccess($request->user(), $appointment, $settings);

        if ((int) $payment->appointment_id !== (int) $appointment->id) {
            abort(403, 'پرداخت متعلق به این نوبت نیست.');
        }

        if ($request->has('amount')) {
            $cleaned = preg_replace('/[^\d.]/', '', (string) $request->input('amount'));
            $request->merge(['amount' => $cleaned]);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in([
                BookingPayment::STATUS_PENDING,
                BookingPayment::STATUS_PAID,
                BookingPayment::STATUS_FAILED,
                BookingPayment::STATUS_REFUNDED,
                BookingPayment::STATUS_CANCELLED,
            ])],
            'gateway_ref' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'sub_item_label' => ['nullable', 'string', 'max:255'],
            'paid_at_jalali' => ['nullable', 'string', 'max:20'],
        ]);

        $wasPaidBefore = ($payment->status === BookingPayment::STATUS_PAID);

        $settingsMap = \Illuminate\Support\Facades\Schema::hasTable('settings')
            ? \Modules\Settings\Entities\Setting::query()->pluck('value', 'key')->toArray()
            : [];

        $systemCurrency = strtolower($settingsMap['payment_currency'] ?? $settings->currency_unit ?? 'toman');
        $currencyUnit = ($systemCurrency === 'toman' || $systemCurrency === 'irt') ? 'IRT' : 'IRR';

        $inputAmount = (float) $data['amount'];
        $amountInRials = ($currencyUnit === 'IRT') ? ($inputAmount * 10) : $inputAmount;

        $paidAt = $payment->paid_at;
        if (!empty($data['paid_at_jalali'])) {
            $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
            $localDate = $this->convertJalaliDateToLocal($data['paid_at_jalali'], $scheduleTz);
            if ($localDate) {
                $paidAt = $localDate;
            }
        }
        if (!$paidAt && $data['status'] === BookingPayment::STATUS_PAID) {
            $paidAt = now();
        }

        $notes = $data['notes'] ?? '';
        if (!empty($data['sub_item_label'])) {
            $notes = $notes ? ($data['sub_item_label'] . ' | ' . $notes) : $data['sub_item_label'];
        }

        $payment->update([
            'type' => $data['type'] ?? $payment->type,
            'amount' => $amountInRials,
            'currency_unit' => 'IRR',
            'status' => $data['status'],
            'gateway_ref' => $data['gateway_ref'] ?? null,
            'notes' => $notes ?: null,
            'paid_at' => $paidAt,
        ]);

        if ($data['status'] === BookingPayment::STATUS_PAID && !$wasPaidBefore) {
            $this->service->markPaymentPaid($payment->id, $payment->gateway_ref);
        }

        $this->service->triggerWorkflow('payment_manual_updated', $appointment->fresh());

        return redirect()
            ->route('user.booking.appointments.show', $appointment)
            ->with('success', 'اطلاعات پرداخت با موفقیت بروزرسانی شد.');
    }

    /**
     * لغو پرداخت توسط ادمین
     */
    public function destroyPayment(Request $request, Appointment $appointment, BookingPayment $payment)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentEditAccess($request->user(), $appointment, $settings);

        if ((int) $payment->appointment_id !== (int) $appointment->id) {
            abort(403, 'پرداخت متعلق به این نوبت نیست.');
        }

        $payment->update([
            'status' => BookingPayment::STATUS_CANCELLED,
        ]);

        $this->service->triggerWorkflow('payment_manual_cancelled', $appointment->fresh());

        return redirect()
            ->route('user.booking.appointments.show', $appointment)
            ->with('success', 'پرداخت با موفقیت لغو شد.');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentEditAccess($request->user(), $appointment, $settings);

        $data = $request->validate([
            'service_id'        => ['required', 'integer', 'exists:booking_services,id'],
            'provider_user_id'  => ['required', 'integer', 'exists:users,id'],
            'client_id'         => ['required', 'integer', 'exists:clients,id'],
            'status'            => ['required', Rule::in([
                Appointment::STATUS_DRAFT,
                Appointment::STATUS_PENDING,
                Appointment::STATUS_PENDING_PAYMENT,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CANCELED_BY_ADMIN,
                Appointment::STATUS_CANCELED_BY_CLIENT,
                Appointment::STATUS_NO_SHOW,
                Appointment::STATUS_DONE,
                Appointment::STATUS_RESCHEDULED,
            ])],
            'date_local'        => ['required', 'string'],
            'start_time_local'  => ['required', 'string'],
            'end_time_local'    => ['required', 'string'],
            'notes'             => ['nullable', 'string'],
            'entry_time_local'  => ['nullable', 'string'],
            'exit_time_local'   => ['nullable', 'string'],
            'cancel_reason'     => ['nullable', 'string', 'max:500'],
            'appointment_form_response_json' => ['nullable', 'string'],
        ]);

        $authUser = $request->user();

        $client = Client::query()->whereKey($data['client_id'])->firstOrFail();
        if (!$client->isVisibleFor($authUser)) {
            abort(403, 'شما به این مشتری دسترسی ندارید.');
        }

        $service = BookingService::query()->whereKey($data['service_id'])->firstOrFail();
        $sp = BookingServiceProvider::query()
            ->where('service_id', $service->id)
            ->where('provider_user_id', (int) $data['provider_user_id'])
            ->first();

        if (!$sp || !$sp->is_active) {
            return back()
                ->withErrors(['service_id' => 'این سرویس برای این ارائه‌دهنده فعال نیست.'])
                ->withInput();
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $localDate = $this->convertJalaliDateToLocal($data['date_local'] ?? null, $scheduleTz);
        if (!$localDate) {
            return back()
                ->withErrors(['date_local' => 'تاریخ وارد شده معتبر نیست.'])
                ->withInput();
        }

        $startLocal = $this->combineLocalDateAndTime($localDate, $data['start_time_local'] ?? null);
        $endLocal = $this->combineLocalDateAndTime($localDate, $data['end_time_local'] ?? null);

        if (!$startLocal || !$endLocal) {
            return back()
                ->withErrors(['start_time_local' => 'زمان شروع/پایان معتبر نیست.'])
                ->withInput();
        }

        if ($endLocal->lte($startLocal)) {
            return back()
                ->withErrors(['end_time_local' => 'زمان پایان باید بعد از زمان شروع باشد.'])
                ->withInput();
        }

        $startUtc = $startLocal->copy()->timezone('UTC');
        $endUtc = $endLocal->copy()->timezone('UTC');

        // وضعیت‌هایی که نوبت را غیرفعال می‌کنند نیازی به validation برنامه زمانی ندارند
        // (لغو، عدم حضور، انجام شده، جابجا شده) - نباید بررسی تداخل با استراحت مانع این تغییرات شود
        $terminalStatuses = [
            Appointment::STATUS_CANCELED_BY_ADMIN,
            Appointment::STATUS_CANCELED_BY_CLIENT,
            Appointment::STATUS_NO_SHOW,
            Appointment::STATUS_DONE,
            Appointment::STATUS_RESCHEDULED,
        ];

        $skipSlotValidation = in_array($data['status'], $terminalStatuses);

        if (!$skipSlotValidation) {
            try {
                $this->service->validateSlotAvailableForUpdate(
                    (int) $service->id,
                    (int) $data['provider_user_id'],
                    $localDate->toDateString(),
                    $startUtc,
                    $endUtc,
                    $appointment->id
                );
            } catch (\InvalidArgumentException | \RuntimeException $e) {
                $message = match ($e->getMessage()) {
                    'Slot capacity is full.' => 'ظرفیت این بازه زمانی تکمیل است.',
                    'Day capacity is full.' => 'ظرفیت روز تکمیل است.',
                    'This day is closed.' => 'این روز بسته است.',
                    'Slot is outside work windows.' => 'این بازه خارج از ساعات کاری است.',
                    'Slot overlaps with break.' => 'این بازه با زمان استراحت تداخل دارد.',
                    'Slot crosses day boundary.' => 'بازه انتخابی باید داخل همان روز باشد.',
                    default => 'امکان ثبت نوبت در این بازه وجود ندارد.',
                };

                return back()
                    ->withErrors(['start_time_local' => $message])
                    ->withInput();
            }
        }

        $previousStatus = $appointment->status;

        $appointment->service_id = (int) $service->id;
        $appointment->provider_user_id = (int) $data['provider_user_id'];
        $appointment->client_id = (int) $client->id;
        $appointment->status = $data['status'];

        if (in_array($data['status'], [Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT])) {
            $appointment->cancel_reason = !empty($data['cancel_reason']) 
                ? $data['cancel_reason'] 
                : ($appointment->cancel_reason ?: ($data['status'] === Appointment::STATUS_CANCELED_BY_ADMIN ? 'لغو توسط ادمین' : 'لغو توسط مشتری'));
        } elseif ($previousStatus !== $data['status']) {
            $appointment->cancel_reason = null;
        }

        $appointment->start_at_utc = $startUtc;
        $appointment->end_at_utc = $endUtc;
        $appointment->notes = $data['notes'] ?? null;

        if ($settings->allow_appointment_entry_exit_times) {
            $entryLocal = $this->combineLocalDateAndTime($localDate, $data['entry_time_local'] ?? null);
            $exitLocal = $this->combineLocalDateAndTime($localDate, $data['exit_time_local'] ?? null);
            $entryUtc = $entryLocal?->copy()->timezone('UTC');
            $exitUtc = $exitLocal?->copy()->timezone('UTC');

            if ($entryUtc && $exitUtc && $exitUtc->lte($entryUtc)) {
                return back()
                    ->withErrors(['exit_time_local' => 'زمان خروج باید بعد از زمان ورود باشد.'])
                    ->withInput();
            }

            $appointment->entry_at_utc = $entryUtc;
            $appointment->exit_at_utc = $exitUtc;
        }

        $formJson = null;
        if (!empty($data['appointment_form_response_json'])) {
            $decoded = json_decode($data['appointment_form_response_json'], true);
            $formJson = is_array($decoded) ? $decoded : null;
        }
        $appointment->appointment_form_response_json = $formJson;

        $appointment->save();

        // Trigger workflows
        if ($previousStatus !== $appointment->status) {
            $this->service->triggerStatusWorkflows($appointment, $previousStatus);
        }

        return redirect()
            ->route('user.booking.appointments.show', $appointment)
            ->with('success', 'نوبت با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentEditAccess($request->user(), $appointment, $settings);

        $appointment->delete();

        return redirect()
            ->back()
            ->with('success', 'نوبت با موفقیت حذف شد.');
    }

    // ------------------------------------------------------------
    // Wizard JSON endpoints
    // ------------------------------------------------------------

    public function wizardProviders(Request $request)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentCreateAccess($request, $settings);

        $shouldLog = (bool) config('app.debug') || (bool) config('booking.debug_logs', false);

        $roleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn($v) => $v > 0
        ));

        $authUser = $request->user();
        if ($this->userIsProvider($authUser, $settings) && ! $this->isAdminUser($authUser)) {
            if ($shouldLog) {
                Log::info('[Booking][WizardProviders] provider-self mode', [
                    'auth_user_id' => $authUser?->id,
                    'allowed_role_ids' => $roleIds,
                    'allowed_roles_raw' => $settings->allowed_roles,
                ]);
            }

            return response()->json([
                'data' => $authUser ? [['id' => $authUser->id, 'name' => $authUser->name]] : [],
            ]);
        }

        $q = trim((string)$request->query('q', ''));
        $serviceId = (int) $request->query('service_id', 0);

        $providersQuery = User::query();

        // 1. First filter by allowed roles (if any)
        if (! $this->isAdminUser($authUser) && !empty($roleIds)) {
            $providersQuery->whereHas('roles', fn($r) => $r->whereIn('id', $roleIds));
        }

        // 2. Then filter by active service providers
        $providersQuery->whereIn('id', function ($sub) use ($serviceId) {
            $sub->from('booking_service_providers')
                ->select('provider_user_id')
                ->where('is_active', 1);

            if ($serviceId) {
                $sub->where('service_id', $serviceId);
            }
        });

        if ($q !== '') {
            $providersQuery->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $providers = $providersQuery->orderBy('name')->limit(50)->get(['id', 'name']);

        if ($shouldLog) {
            Log::info('[Booking][WizardProviders] result', [
                'q' => $q,
                'service_id' => $serviceId,
                'allowed_role_ids' => $roleIds,
                'allowed_roles_raw' => $settings->allowed_roles,
                'count' => $providers->count(),
            ]);
        }

        return response()->json(['data' => $providers]);
    }

    protected function isAdminUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        return $user->can('booking.manage') || $user->can('booking.appointments.manage');
    }

    protected function userIsProvider(?User $user, BookingSetting $settings): bool
    {
        if (! $user) {
            return false;
        }

        $shouldLog = (bool) config('app.debug') || (bool) config('booking.debug_logs', false);

        $providerRoleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn($v) => $v > 0
        ));

        if (empty($providerRoleIds)) {
            if ($shouldLog) {
                Log::info('[Booking][ProviderCheck] allowed_roles empty => not provider', [
                    'user_id' => $user->id,
                    'allowed_roles_raw' => $settings->allowed_roles,
                ]);
            }
            return false;
        }

        $userRoleIds = $user->roles()->pluck('id')->map(fn($v) => (int) $v)->all();
        $intersect = array_values(array_intersect($providerRoleIds, $userRoleIds));
        $isProvider = count($intersect) > 0;

        if ($shouldLog) {
            Log::info('[Booking][ProviderCheck] userIsProvider check', [
                'user_id' => $user->id,
                'user_role_ids' => $userRoleIds,
                'allowed_role_ids' => $providerRoleIds,
                'allowed_roles_raw' => $settings->allowed_roles,
                'intersect' => $intersect,
                'is_provider' => $isProvider,
            ]);
        }

        return $isProvider;
    }

    protected function ensureAppointmentCreateAccess(Request $request, BookingSetting $settings): void
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $allowed =
            $user->can('booking.appointments.create')
            || $this->isAdminUser($user)
            || $this->userIsProvider($user, $settings);

        if ($allowed) {
            return;
        }

        $shouldLog = (bool) config('app.debug') || (bool) config('booking.debug_logs', false);
        if ($shouldLog) {
            Log::warning('[Booking][Access] appointment create access denied', [
                'user_id' => $user->id,
                'can_create' => $user->can('booking.appointments.create'),
                'is_admin' => $this->isAdminUser($user),
                'is_provider' => $this->userIsProvider($user, $settings),
                'allowed_roles_raw' => $settings->allowed_roles,
            ]);
        }

        abort(403);
    }

    protected function ensureAppointmentViewAccess(?User $user, Appointment $appointment, BookingSetting $settings): void
    {
        if (! $user) {
            abort(403);
        }

        if (! $this->isAdminUser($user) && ! $user->can('booking.appointments.view') && ! $this->userIsProvider($user, $settings)) {
            abort(403);
        }

        if ($this->userIsProvider($user, $settings) && ! $this->isAdminUser($user)) {
            if ((int) $appointment->provider_user_id !== (int) $user->id) {
                abort(403, 'شما به این نوبت دسترسی ندارید.');
            }
        }
    }

    protected function ensureAppointmentEditAccess(?User $user, Appointment $appointment, BookingSetting $settings): void
    {
        if (! $user) {
            abort(403);
        }

        if (! $this->isAdminUser($user) && ! $user->can('booking.appointments.edit')) {
            abort(403);
        }

        if ($this->userIsProvider($user, $settings) && ! $this->isAdminUser($user)) {
            if ((int) $appointment->provider_user_id !== (int) $user->id) {
                abort(403, 'شما به این نوبت دسترسی ندارید.');
            }
        }
    }

    protected function convertJalaliDateToLocal(?string $value, string $tz): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            $datePieces = preg_split('/[^\d]+/', trim($value));
            if (count($datePieces) < 3) {
                return null;
            }

            [$jy, $jm, $jd] = array_map('intval', array_slice($datePieces, 0, 3));
            [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);

            return Carbon::create($gy, $gm, $gd, 0, 0, 0, $tz);
        } catch (\Throwable $e) {
            if (function_exists('logger')) {
                logger()->warning('Failed to convert Jalali date', [
                    'value' => $value,
                    'error' => $e->getMessage(),
                ]);
            }

            return null;
        }
    }

    protected function combineLocalDateAndTime(?Carbon $date, ?string $time): ?Carbon
    {
        if (!$date || empty($time)) {
            return null;
        }

        $timePieces = preg_split('/[^\d]+/', trim($time));
        if (count($timePieces) < 2) {
            return null;
        }

        $hour = min(max((int) $timePieces[0], 0), 23);
        $minute = min(max((int) $timePieces[1], 0), 59);

        return $date->copy()->setTime($hour, $minute, 0);
    }

    // بقیه متدها بدون تغییر
    public function wizardServices(Request $request)
    {
        $settings = BookingSetting::current();
        $this->ensureAppointmentCreateAccess($request, $settings);

        $providerId = (int) $request->query('provider_id', 0);
        $categoryId = $request->query('category_id');
        $q = trim((string)$request->query('q', ''));

        if (!$providerId) {
            return response()->json(['data' => []]);
        }

        $serviceIds = BookingServiceProvider::query()
            ->where('provider_user_id', $providerId)
            ->where('is_active', 1)
            ->pluck('service_id')
            ->all();

        $servicesQ = BookingService::query()
            ->leftJoin('booking_categories as bc', 'booking_services.category_id', '=', 'bc.id')
            ->whereIn('booking_services.id', $serviceIds)
            ->where('booking_services.status', BookingService::STATUS_ACTIVE);

        if ($categoryId !== null && $categoryId !== '') {
            $servicesQ->where('booking_services.category_id', (int)$categoryId);
        }

        if ($q !== '') {
            $servicesQ->where('booking_services.name', 'like', "%{$q}%");
        }

        $services = $servicesQ
            ->orderBy('booking_services.name')
            ->limit(100)
            ->get([
                'booking_services.id',
                'booking_services.name',
                'booking_services.category_id',
                'booking_services.appointment_form_id',
                'booking_services.custom_schedule_enabled',
                'booking_services.payment_mode',
                'bc.name as category_name',
            ]);

        return response()->json(['data' => $services]);
    }

    public function wizardCategories(Request $request)
    {
        $this->ensureAppointmentCreateAccess($request, BookingSetting::current());
        $providerId = (int) $request->query('provider_id', 0);
        if (!$providerId) {
            return response()->json(['data' => []]);
        }

        $serviceIds = BookingServiceProvider::query()
            ->where('provider_user_id', $providerId)
            ->where('is_active', 1)
            ->pluck('service_id')
            ->all();

        $rows = \Modules\Booking\Entities\BookingCategory::query()
            ->whereIn('id', BookingService::query()->whereIn('id', $serviceIds)->pluck('category_id')->filter()->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $rows]);
    }

    public function wizardAllServices(Request $request)
    {
        $this->ensureAppointmentCreateAccess($request, BookingSetting::current());
        $user = $request->user();
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');

        $servicesQ = BookingService::query()
            ->leftJoin('booking_categories as bc', 'booking_services.category_id', '=', 'bc.id')
            ->where('booking_services.status', BookingService::STATUS_ACTIVE);

        if ($categoryId !== null && $categoryId !== '') {
            $servicesQ->where('booking_services.category_id', (int) $categoryId);
        }

        if (! $this->isAdminUser($user)) {
            $servicesQ->whereExists(function ($sub) {
                $sub->from('booking_service_providers')
                    ->selectRaw('1')
                    ->whereColumn('booking_service_providers.service_id', 'booking_services.id')
                    ->where('booking_service_providers.is_active', 1);
            });
        }

        if ($q !== '') {
            $servicesQ->where('booking_services.name', 'like', "%{$q}%");
        }

        $services = $servicesQ
            ->orderBy('booking_services.name')
            ->limit(100)
            ->get([
                'booking_services.id',
                'booking_services.name',
                'booking_services.category_id',
                'booking_services.appointment_form_id',
                'booking_services.custom_schedule_enabled',
                'booking_services.payment_mode',
                'bc.name as category_name',
            ]);

        return response()->json(['data' => $services]);
    }

    public function wizardCalendar(Request $request)
    {
        $this->ensureAppointmentCreateAccess($request, BookingSetting::current());
        $serviceId  = (int) $request->query('service_id', 0);
        $providerId = (int) $request->query('provider_id', 0);
        $year       = (int) $request->query('year', 0);
        $month      = (int) $request->query('month', 0);

        if (!$serviceId || !$providerId || !$year || !$month) {
            return response()->json(['data' => []]);
        }

        $engine     = app(\Modules\Booking\Services\BookingEngine::class);
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        $isGregorian = $year >= 1700;

        if ($isGregorian) {
            $start = Carbon::create($year, $month, 1, 0, 0, 0, $scheduleTz)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } else { // Jalali
            $start = $this->parseFlexibleLocalDate(sprintf('%04d-%02d-01', $year, $month), $scheduleTz);
            if (!$start) {
                return response()->json(['data' => []]);
            }

            $end = null;
            for ($d = 1; $d <= 31; $d++) {
                $tmp = $this->parseFlexibleLocalDate(sprintf('%04d-%02d-%02d', $year, $month, $d), $scheduleTz);
                if (!$tmp) {
                    break;
                }
                $end = $tmp;
            }
            if (!$end) {
                return response()->json(['data' => []]);
            }
        }

        $allSlots = $engine->generateSlots(
            $serviceId,
            $providerId,
            $start->toDateString(),
            $end->toDateString(),
            viewerTimezone: config('booking.timezones.display_default', $scheduleTz)
        );

        $slotsByDay = [];
        foreach ($allSlots as $s) {
            $slotsByDay[$s['local_date']][] = $s;
        }

        $statuses = (array) config('booking.capacity_consuming_statuses', []);

        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $localDate = $d->toDateString();

            $policy = $engine->resolveDayPolicy($serviceId, $providerId, $d);
            $hasWork = !$policy['is_closed'] && !empty($policy['work_windows']);

            $dayStartUtc = $d->copy()->startOfDay()->timezone('UTC');
            $dayEndUtc   = $d->copy()->addDay()->startOfDay()->timezone('UTC');

            $booked = Appointment::query()
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $providerId)
                ->whereIn('status', $statuses)
                ->where('start_at_utc', '>=', $dayStartUtc)
                ->where('start_at_utc', '<', $dayEndUtc)
                ->count();

            $held = \Modules\Booking\Entities\BookingSlotHold::query()
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $providerId)
                ->where('expires_at_utc', '>', now('UTC'))
                ->where('start_at_utc', '>=', $dayStartUtc)
                ->where('start_at_utc', '<', $dayEndUtc)
                ->count();

            $capDay = $policy['capacity_per_day'];
            $remainingDayCap = null;
            if ($capDay !== null) {
                $remainingDayCap = max(0, (int)$capDay - (int)$booked - (int)$held);
            }

            $allDaySlots = $slotsByDay[$localDate] ?? [];
            $availableSlots = array_filter($allDaySlots, function($s) {
                $remCap = $s['remaining_capacity'] ?? null;
                $isBlocked = !empty($s['sync_blocked']);
                return !$isBlocked && ($remCap === null || (int)$remCap > 0);
            });
            $availableSlotsCount = count($availableSlots);

            $isClosed = !$hasWork;
            $hasAvailableSlots = (!$isClosed) && ($availableSlotsCount > 0);
            $status = $isClosed ? 'CLOSED' : ($hasAvailableSlots ? 'AVAILABLE' : 'FULL');

            $days[] = [
                'local_date' => $localDate,
                'status' => $status,
                'is_closed' => (bool) $isClosed,
                'has_available_slots' => (bool) $hasAvailableSlots,
                'available_slots_count' => $availableSlotsCount,

                'capacity_per_day' => $capDay,
                'booked' => (int) $booked,
                'held' => (int) $held,
                'remaining_day_capacity' => $remainingDayCap,

                'capacity_per_slot' => (int) ($policy['capacity_per_slot'] ?? 1),
                'slot_duration_minutes' => (int) ($policy['slot_duration_minutes'] ?? 30),
            ];
        }

        return response()->json([
            'data' => $days,
            'meta' => ['year' => $year, 'month' => $month],
        ]);
    }

    public function wizardClients(Request $request)
    {
        $this->ensureAppointmentCreateAccess($request, BookingSetting::current());
        $user = $request->user();
        $q = trim((string)$request->query('q', ''));

        $clientsQ = Client::query()->visibleForUser($user);

        if ($q !== '') {
            $clientsQ->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('national_code', 'like', "%{$q}%")
                    ->orWhere('case_number', 'like', "%{$q}%");
            });
            $clients = $clientsQ->orderByDesc('id')->limit(50)->get(['id', 'full_name', 'phone', 'email', 'national_code', 'case_number']);
        } else {
            $clients = $clientsQ->orderByDesc('id')->limit(3)->get(['id', 'full_name', 'phone', 'email', 'national_code', 'case_number']);
        }

        return response()->json(['data' => $clients]);
    }

    public function wizardWaitlist(Request $request)
    {
        $this->ensureAppointmentCreateAccess($request, BookingSetting::current());
        if (!BookingSetting::isQueueEnabled() || !class_exists(\Modules\Booking\Entities\BookingWaitlist::class)) {
            return response()->json(['data' => []]);
        }

        $user = $request->user();
        $entries = \Modules\Booking\Entities\BookingWaitlist::query()
            ->whereIn('status', [
                \Modules\Booking\Entities\BookingWaitlist::STATUS_WAITING,
                \Modules\Booking\Entities\BookingWaitlist::STATUS_NOTIFIED,
                \Modules\Booking\Entities\BookingWaitlist::STATUS_IN_PROGRESS
            ])
            ->whereHas('client', function ($cq) use ($user) {
                $cq->visibleForUser($user);
            })
            ->with(['client', 'service', 'provider'])
            ->get()
            ->sortBy(fn($item) => $item->queue_rank ?? $item->position ?? 0)
            ->values()
            ->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'client_id' => (int) $item->client_id,
                    'client_name' => $item->client?->full_name ?? 'بدون نام',
                    'client_phone' => $item->client?->phone,
                    'client_national_code' => $item->client?->national_code,
                    'client_case_number' => $item->client?->case_number,
                    'client_email' => $item->client?->email,
                    'service_id' => $item->service_id ? (int) $item->service_id : null,
                    'service_name' => $item->service?->name,
                    'provider_user_id' => $item->provider_user_id ? (int) $item->provider_user_id : null,
                    'provider_name' => $item->provider?->name,
                    'preferred_date' => $item->preferred_date ? \Morilog\Jalali\Jalalian::fromDateTime($item->preferred_date)->format('Y/m/d') : null,
                    'queue_rank' => $item->queue_rank ?? $item->position ?? 1,
                    'notes' => $item->notes,
                    'status' => $item->status,
                ];
            });

        return response()->json(['data' => $entries]);
    }

    public function wizardForm(Request $request)
    {
        $this->ensureAppointmentCreateAccess($request, BookingSetting::current());
        $formId = (int) $request->query('form_id', 0);

        if (! $formId) {
            return response()->json(['data' => null]);
        }

        $form = BookingForm::query()
            ->whereKey($formId)
            ->where('status', BookingForm::STATUS_ACTIVE)
            ->first();

        if (! $form) {
            return response()->json(['data' => null]);
        }

        $schema = $form->schema_json ?? [];
        if (isset($schema['fields']) && is_array($schema['fields'])) {
            foreach ($schema['fields'] as &$field) {
                if (($field['type'] ?? '') === 'select-user-by-role') {
                    $roleName = $field['role'] ?? null;
                    $usersQ = User::query();
                    if ($roleName) {
                        $usersQ->whereHas('roles', fn($r) => $r->where('name', $roleName));
                    }
                    $field['user_options'] = $usersQ->orderBy('name')->get(['id', 'name']);
                }
            }
        }

        return response()->json([
            'data' => [
                'id' => $form->id,
                'name' => $form->name,
                'form_type' => $form->form_type,
                'schema_json' => $schema,
            ],
        ]);
    }

    public function wizardHistory(Request $request)
    {
        $this->ensureAppointmentCreateAccess($request, BookingSetting::current());
        $serviceId = (int) $request->query('service_id', 0);
        $providerId = (int) $request->query('provider_id', 0);
        $dateLocal = $request->query('date_local');

        if (!$serviceId || !$providerId || !$dateLocal) {
            return response()->json(['data' => [], 'stats' => [], 'suggested_time' => null]);
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        // Use the same parsing logic as calendar to be consistent
        $localDate = $this->parseFlexibleLocalDate($dateLocal, $scheduleTz);

        if (!$localDate) {
            return response()->json(['data' => [], 'stats' => [], 'suggested_time' => null]);
        }

        // Ensure we cover the full day in local time
        $startUtc = $localDate->copy()->startOfDay()->timezone('UTC');
        $endUtc = $localDate->copy()->endOfDay()->timezone('UTC');

        $user = $request->user();
        $settings = BookingSetting::current();

        $query = Appointment::query()
            ->with(['client'])
            ->where('service_id', $serviceId)
            ->where('provider_user_id', $providerId)
            ->where('start_at_utc', '>=', $startUtc)
            ->where('start_at_utc', '<=', $endUtc);

        // Permission Scope
        if (! $this->isAdminUser($user) && ! $user->can('booking.appointments.view.all')) {
            if ($user->can('booking.appointments.view.own') || $this->userIsProvider($user, $settings)) {
                $query->where(function ($q) use ($user) {
                    $q->where('provider_user_id', $user->id)
                      ->orWhere('created_by_user_id', $user->id);
                });
            } else {
                $query->where('created_by_user_id', $user->id);
            }
        }

        $appointments = $query->orderBy('start_at_utc')->get();

        $statusLabels = [
            Appointment::STATUS_DRAFT => 'پیش‌نویس',
            Appointment::STATUS_PENDING => 'در انتظار تایید',
            Appointment::STATUS_PENDING_PAYMENT => 'در انتظار پرداخت',
            Appointment::STATUS_CONFIRMED => 'قطعی شده',
            Appointment::STATUS_CANCELED_BY_ADMIN => 'لغو توسط ادمین',
            Appointment::STATUS_CANCELED_BY_CLIENT => 'لغو توسط مشتری',
            Appointment::STATUS_NO_SHOW => 'عدم مراجعه',
            Appointment::STATUS_DONE => 'انجام شده',
            Appointment::STATUS_RESCHEDULED => 'جابجا شده',
        ];

        $data = $appointments->map(function ($app) use ($scheduleTz, $statusLabels) {
            $start = $app->start_at_utc->timezone($scheduleTz);
            $end = $app->end_at_utc->timezone($scheduleTz);

            return [
                'id' => $app->id,
                'client_name' => $app->client->full_name ?? 'ناشناس',
                'status' => $app->status,
                'status_label' => $statusLabels[$app->status] ?? $app->status,
                'start_time' => $start->format('H:i'),
                'end_time' => $end->format('H:i'),
                'duration_minutes' => $start->diffInMinutes($end),
            ];
        });

        // Calculate Stats
        $stats = [
            'total' => $appointments->count(),
            'confirmed' => $appointments->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'pending' => $appointments->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT])->count(),
            'done' => $appointments->where('status', Appointment::STATUS_DONE)->count(),
            'canceled' => $appointments->whereIn('status', [Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT, Appointment::STATUS_NO_SHOW])->count(),
        ];

        // Calculate Suggested Time (First Available Slot)
        $suggestedTime = null;
        try {
            $engine = app(\Modules\Booking\Services\BookingEngine::class);
            $slots = $engine->generateSlots(
                $serviceId,
                $providerId,
                $localDate->toDateString(),
                $localDate->toDateString(),
                viewerTimezone: config('booking.timezones.display_default', $scheduleTz)
            );

            foreach ($slots as $slot) {
                // Check if slot has capacity
                $cap = $slot['remaining_capacity'] ?? 1;
                if ($cap > 0) {
                    $suggestedTime = Carbon::parse($slot['start_at_view'])->format('H:i');
                    break;
                }
            }
        } catch (\Throwable $e) {
            // Ignore errors in suggestion calculation
        }

        return response()->json([
            'data' => $data,
            'stats' => $stats,
            'suggested_time' => $suggestedTime
        ]);
    }

    protected function parseFlexibleLocalDate(string $value, string $tz): ?Carbon
    {
        $value = trim($value);

        $datePieces = preg_split('/[^\d]+/', $value);
        if (count($datePieces) < 3) {
            return null;
        }

        [$y, $m, $d] = array_map('intval', array_slice($datePieces, 0, 3));

        if ($m < 1 || $m > 12 || $d < 1 || $d > 31) {
            return null;
        }

        try {
            if ($y >= 1700) {
                return Carbon::create($y, $m, $d, 0, 0, 0, $tz);
            }

            [$gy, $gm, $gd] = CalendarUtils::toGregorian($y, $m, $d);
            return Carbon::create($gy, $gm, $gd, 0, 0, 0, $tz);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
