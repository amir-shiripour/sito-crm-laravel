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
use Modules\Booking\Services\AppointmentService;
use App\Models\User;
use Modules\Clients\Entities\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service)
    {
    }

    public function index(Request $request)
    {
        $settings = BookingSetting::current();
        $user = $request->user();

        $appointmentsQuery = Appointment::query()
            ->with(['service', 'provider', 'client'])
            ->orderByDesc('start_at_utc');

        if ($this->userIsProvider($user, $settings) && ! $this->isAdminUser($user)) {
            $appointmentsQuery->where('provider_user_id', $user->id);
        }

        $appointments = $appointmentsQuery->paginate(25);

        return view('booking::user.appointments.index', compact('appointments'));
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

        return view('booking::user.appointments.create', compact('settings', 'flow', 'fixedProvider'));
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

            // از UI اسلات
            'start_at_utc'      => ['nullable', 'date'],
            'end_at_utc'        => ['nullable', 'date'],
            'date_local'        => ['nullable', 'date_format:Y-m-d'],
            'start_time_local'  => ['nullable', 'date_format:H:i'],
            'end_time_local'    => ['nullable', 'date_format:H:i'],

            'notes'             => ['nullable', 'string'],

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
            fn ($v) => $v > 0
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
                ->whereHas('roles', fn ($q) => $q->whereIn('id', $roleIds))
                ->exists();

            if (!$isValidProvider) {
                $providerUser = User::query()->whereKey($data['provider_user_id'])->first();
                $providerRoleIds = $providerUser
                    ? $providerUser->roles()->pluck('id')->map(fn ($v) => (int) $v)->all()
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
        $usesCustomSchedule = (bool) $service->custom_schedule_enabled;

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

        try {
            $this->service->createAppointmentByOperator(
                (int) $service->id,
                (int) $data['provider_user_id'],
                (int) $client->id,
                $startUtc->toIso8601String(),
                $endUtc->toIso8601String(),
                createdByUserId: $request->user()->id,
                notes: $data['notes'] ?? null,
                appointmentFormResponse: $formJson
            );
        } catch (\InvalidArgumentException|\RuntimeException $e) {
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

        return redirect()
            ->route('user.booking.appointments.index')
            ->with('success', 'نوبت با موفقیت ثبت شد.');
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
            fn ($v) => $v > 0
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

        if (! $this->isAdminUser($authUser) && !empty($roleIds)) {
            $providersQuery->whereHas('roles', fn ($r) => $r->whereIn('id', $roleIds));
        }

        if ($serviceId) {
            // فقط ارائه‌دهنده‌هایی که این سرویس برایشان فعال است
            $providersQuery->whereIn('id', function ($sub) use ($serviceId) {
                $sub->from('booking_service_providers')
                    ->select('provider_user_id')
                    ->where('service_id', $serviceId)
                    ->where('is_active', 1);
            });
        }

        if ($q !== '') {
            $providersQuery->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $providers = $providersQuery->orderBy('name')->limit(50)->get(['id','name']);

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
            fn ($v) => $v > 0
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

        $userRoleIds = $user->roles()->pluck('id')->map(fn ($v) => (int) $v)->all();
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
            ->get(['id','name']);

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

        $start = Carbon::create($year, $month, 1, 0, 0, 0, $scheduleTz)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

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

            $availableSlots = $slotsByDay[$localDate] ?? [];
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
        $q = trim((string)$request->query('q',''));

        $clientsQ = Client::query()->visibleForUser($user);

        if ($q !== '') {
            $clientsQ->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $clients = $clientsQ->orderByDesc('id')->limit(30)->get(['id','full_name','phone','email']);

        return response()->json(['data' => $clients]);
    }
}
