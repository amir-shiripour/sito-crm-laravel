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

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service)
    {
    }

    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->with(['service', 'provider', 'client'])
            ->orderByDesc('start_at_utc')
            ->paginate(25);

        return view('booking::user.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $settings = BookingSetting::current();

        // مرحله اول از تنظیمات
        $flow = $settings->operator_appointment_flow ?: 'PROVIDER_FIRST';

        $user = Auth::user();
        $fixedProvider = null;

        if ($this->userIsProvider($user, $settings) && ! $this->isAdminUser($user)) {
            $flow = 'PROVIDER_FIRST';
            $fixedProvider = $user;
        }

        return view('booking::user.appointments.create', compact('settings', 'flow', 'fixedProvider'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id'        => ['required', 'integer', 'exists:booking_services,id'],
            'provider_user_id'  => ['required', 'integer', 'exists:users,id'],
            'client_id'         => ['required', 'integer', 'exists:clients,id'],

            // از UI اسلات
            'start_at_utc'      => ['required', 'date'],
            'end_at_utc'        => ['required', 'date'],

            'notes'             => ['nullable', 'string'],

            // اگر فرم داشت
            'appointment_form_response_json' => ['nullable', 'string'],
        ]);

        $settings = BookingSetting::current();
        $authUser = $request->user();

        // provider باید جزو allowed_roles باشد
        $roleIds = (array) ($settings->allowed_roles ?? []);
        if (!empty($roleIds)) {
            $isValidProvider = User::query()
                ->whereKey($data['provider_user_id'])
                ->whereHas('roles', fn ($q) => $q->whereIn('id', $roleIds))
                ->exists();

            if (!$isValidProvider) {
                return back()
                    ->withErrors(['provider_user_id' => 'ارائه‌دهنده انتخاب‌شده مجاز نیست.'])
                    ->withInput();
            }
        }

        if ($this->userIsProvider($authUser, $settings) && ! $this->isAdminUser($authUser)) {
            if ((int) $data['provider_user_id'] !== (int) $authUser->id) {
                return back()
                    ->withErrors(['provider_user_id' => 'ارائه‌دهنده انتخاب‌شده معتبر نیست.'])
                    ->withInput();
            }
        }

        // client باید قابل مشاهده باشد
        $client = Client::query()->whereKey($data['client_id'])->firstOrFail();
        if (!$client->isVisibleFor($request->user())) {
            abort(403, 'شما به این مشتری دسترسی ندارید.');
        }

        // سرویس و رابطه provider-service باید معتبر و فعال باشد
        $service = BookingService::query()->whereKey($data['service_id'])->firstOrFail();

        $sp = BookingServiceProvider::query()
            ->where('service_id', $service->id)
            ->where('provider_user_id', (int)$data['provider_user_id'])
            ->first();

        if (!$sp || !$sp->is_active) {
            return back()
                ->withErrors(['service_id' => 'این سرویس برای این ارائه‌دهنده فعال نیست.'])
                ->withInput();
        }

        // زمان‌ها
        $startUtc = Carbon::parse($data['start_at_utc'], 'UTC');
        $endUtc   = Carbon::parse($data['end_at_utc'], 'UTC');

        if ($endUtc->lte($startUtc)) {
            return back()->withErrors(['start_at_utc' => 'بازه زمانی اسلات نامعتبر است.'])->withInput();
        }

        $formJson = null;
        if (!empty($data['appointment_form_response_json'])) {
            $decoded = json_decode($data['appointment_form_response_json'], true);
            $formJson = is_array($decoded) ? $decoded : null;
        }

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
        $roleIds  = (array) ($settings->allowed_roles ?? []);

        $authUser = $request->user();
        if ($this->userIsProvider($authUser, $settings) && ! $this->isAdminUser($authUser)) {
            return response()->json([
                'data' => $authUser ? [['id' => $authUser->id, 'name' => $authUser->name]] : [],
            ]);
        }

        $q = trim((string)$request->query('q', ''));
        $serviceId = (int) $request->query('service_id', 0);

        $providersQuery = User::query();

        if (!empty($roleIds)) {
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

        $providerRoleIds = array_map('intval', (array) ($settings->allowed_roles ?? []));
        if (empty($providerRoleIds)) {
            return false;
        }

        $userRoleIds = $user->roles()->pluck('id')->map(fn ($v) => (int) $v)->all();

        return count(array_intersect($providerRoleIds, $userRoleIds)) > 0;
    }

    public function wizardServices(Request $request)
    {
        $settings = BookingSetting::current();

        $providerId = (int) $request->query('provider_id', 0);
        $categoryId = $request->query('category_id');
        $q = trim((string)$request->query('q', ''));

        if (!$providerId) {
            return response()->json(['data' => []]);
        }

        // سرویس‌هایی که برای این provider فعال هستند (public + own)
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
            $servicesQ->where('category_id', (int)$categoryId);
        }

        if ($q !== '') {
            $servicesQ->where('name', 'like', "%{$q}%");
        }

        $services = $servicesQ
            ->orderBy('booking_services.name')
            ->limit(100)
            ->get([
                'booking_services.id',
                'booking_services.name',
                'booking_services.category_id',
                'booking_services.appointment_form_id',
                'bc.name as category_name',
            ]);

        return response()->json(['data' => $services]);
    }

    public function wizardCategories(Request $request)
    {
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
        $q = trim((string) $request->query('q', ''));

        $servicesQ = BookingService::query()
            ->leftJoin('booking_categories as bc', 'booking_services.category_id', '=', 'bc.id')
            ->where('booking_services.status', BookingService::STATUS_ACTIVE)
            ->whereExists(function ($sub) {
                $sub->from('booking_service_providers')
                    ->selectRaw('1')
                    ->whereColumn('booking_service_providers.service_id', 'booking_services.id')
                    ->where('booking_service_providers.is_active', 1);
            });

        if ($q !== '') {
            $servicesQ->where('name', 'like', "%{$q}%");
        }

        $services = $servicesQ
            ->orderBy('booking_services.name')
            ->limit(100)
            ->get([
                'booking_services.id',
                'booking_services.name',
                'booking_services.category_id',
                'booking_services.appointment_form_id',
                'bc.name as category_name',
            ]);

        return response()->json(['data' => $services]);
    }

    public function wizardCalendar(Request $request)
    {
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

        // 1) همه اسلات‌های قابل رزرو ماه (remaining_capacity > 0) را یک‌جا می‌گیریم
        $allSlots = $engine->generateSlots(
            $serviceId,
            $providerId,
            $start->toDateString(),
            $end->toDateString(),
            viewerTimezone: config('booking.timezones.display_default', $scheduleTz)
        );

        // Group by local_date
        $slotsByDay = [];
        foreach ($allSlots as $s) {
            $slotsByDay[$s['local_date']][] = $s;
        }

        $statuses = (array) config('booking.capacity_consuming_statuses', []);

        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $localDate = $d->toDateString();

            // Policy واقعی همین روز
            $policy = $engine->resolveDayPolicy($serviceId, $providerId, $d);

            $hasWork = !$policy['is_closed'] && !empty($policy['work_windows']);

            // Booked/Held روز (برای ظرفیت روزانه)
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

            // اسلات‌های قابل رزرو برای این روز
            $availableSlots = $slotsByDay[$localDate] ?? [];
            $availableSlotsCount = count($availableSlots);

            // وضعیت روز:
            // - CLOSED: تعطیل یا بدون بازه کاری
            // - FULL: بازه دارد ولی هیچ اسلات قابل رزرو نیست (پر / یا ظرفیت صفر)
            // - AVAILABLE: اسلات دارد
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

                // برای UI اگر لازم شد:
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
