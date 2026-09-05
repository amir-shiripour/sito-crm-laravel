<?php

namespace Modules\Booking\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;

class ClinicMonitoringService
{
    /**
     * Get the configured schedule timezone.
     */
    public function getScheduleTimezone(): string
    {
        return config('booking.timezones.schedule', 'Asia/Tehran');
    }

    /**
     * Check if user has administrative booking access.
     */
    public function isAdminUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        return $user->can('booking.manage')
            || $user->can('booking.appointments.manage')
            || $user->can('booking.admin');
    }

    /**
     * Check if user is a configured provider.
     */
    public function userIsProvider(?User $user, ?BookingSetting $settings = null): bool
    {
        if (! $user) {
            return false;
        }

        $settings = $settings ?? BookingSetting::current();
        $providerRoleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn ($v) => $v > 0
        ));

        if (empty($providerRoleIds)) {
            return $user->hasRole('doctor');
        }

        $userRoleIds = $user->roles()->pluck('id')->map(fn ($v) => (int) $v)->all();

        return count(array_intersect($providerRoleIds, $userRoleIds)) > 0 || $user->hasRole('doctor');
    }

    /**
     * Determine if user has permission to view all appointments across the clinic.
     */
    public function canViewAllAppointments(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return true;
        }

        // If user has view.own, they can only view own appointments
        if ($user->hasPermissionTo('booking.appointments.view.own') && ! $user->hasDirectPermission('booking.appointments.view.all')) {
            return false;
        }

        return $user->can('booking.appointments.view.all')
            || $user->can('booking.manage')
            || $user->can('booking.appointments.manage');
    }

    /**
     * Build base scoped query for appointments on a specific local date.
     */
    public function buildBaseQuery(?string $date = null, ?User $user = null): Builder
    {
        $tz = $this->getScheduleTimezone();
        if ($date) {
            $clean = str_replace(
                ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                trim($date)
            );
            if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $clean, $m)) {
                $y = (int) $m[1];
                if ($y >= 1300 && $y <= 1500) {
                    $g = \Morilog\Jalali\CalendarUtils::toGregorian($y, (int) $m[2], (int) $m[3]);
                    $localDate = Carbon::createFromDate($g[0], $g[1], $g[2], $tz)->startOfDay();
                } else {
                    $localDate = Carbon::parse($clean, $tz)->startOfDay();
                }
            } else {
                $localDate = Carbon::parse($date, $tz)->startOfDay();
            }
        } else {
            $localDate = Carbon::today($tz);
        }

        $startOfDayUtc = $localDate->copy()->startOfDay()->timezone('UTC');
        $endOfDayUtc = $localDate->copy()->endOfDay()->timezone('UTC');

        $query = Appointment::query()
            ->whereBetween('start_at_utc', [$startOfDayUtc, $endOfDayUtc]);

        $this->applyPermissionScope($query, $user ?? Auth::user());

        return $query;
    }

    /**
     * Apply RBAC permission scoping: admin / view.all vs doctor / view.own.
     */
    public function applyPermissionScope(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query;
        }

        if (! $this->canViewAllAppointments($user)) {
            if ($user->can('booking.appointments.view.own') || $this->userIsProvider($user) || $user->hasRole('doctor')) {
                $query->where('provider_user_id', $user->id);
            } else {
                $query->where('created_by_user_id', $user->id);
            }
        }

        return $query;
    }

    /**
     * Apply filter criteria (provider, service, status).
     */
    public function applyFilters(
        Builder $query,
        mixed $providerId = null,
        mixed $serviceId = null,
        ?string $status = null
    ): Builder {
        if (! empty($providerId) && $providerId !== 'all') {
            $query->where('provider_user_id', (int) $providerId);
        }

        if (! empty($serviceId) && $serviceId !== 'all') {
            $query->where('service_id', (int) $serviceId);
        }

        if (! empty($status) && $status !== 'all' && $status !== '') {
            $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Resolve active in-visit patient:
     * Priority 1: entry_at_utc IS NOT NULL AND exit_at_utc IS NULL and not terminal, ordered by entry_at_utc ASC.
     * Priority 2: In-window fallback: status == CONFIRMED AND start_at_utc <= now('UTC') AND end_at_utc >= now('UTC') ordered by start_at_utc ASC.
     */
    public function resolveActivePatient(Builder $baseQuery): ?Appointment
    {
        // 1. Physically checked-in patient currently in consultation (earliest entry prioritized)
        $entered = (clone $baseQuery)
            ->whereNotNull('entry_at_utc')
            ->whereNull('exit_at_utc')
            ->whereNotIn('status', [
                Appointment::STATUS_DONE,
                Appointment::STATUS_CANCELED_BY_ADMIN,
                Appointment::STATUS_CANCELED_BY_CLIENT,
                Appointment::STATUS_NO_SHOW,
            ])
            ->orderBy('entry_at_utc', 'asc')
            ->with(['service', 'client', 'provider'])
            ->first();

        if ($entered) {
            return $entered;
        }

        // 2. Currently within scheduled window fallback
        $nowUtc = now('UTC');
        $inWindow = (clone $baseQuery)
            ->where('status', Appointment::STATUS_CONFIRMED)
            ->where('start_at_utc', '<=', $nowUtc)
            ->where('end_at_utc', '>=', $nowUtc)
            ->whereNull('exit_at_utc')
            ->orderBy('start_at_utc', 'asc')
            ->with(['service', 'client', 'provider'])
            ->first();

        return $inWindow;
    }

    /**
     * Resolve next patient waiting in queue:
     * - Statuses: CONFIRMED, PENDING, PENDING_PAYMENT
     * - Exclude current active patient if provided
     * - Order: arrived patients (entry_at_utc IS NOT NULL) first, then scheduled start_at_utc ASC, tie-break id ASC.
     */
    public function resolveNextInQueue(Builder $baseQuery, ?int $excludeAppointmentId = null): ?Appointment
    {
        return (clone $baseQuery)
            ->when($excludeAppointmentId, fn ($q) => $q->where('id', '!=', $excludeAppointmentId))
            ->whereIn('status', [
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_PENDING,
                Appointment::STATUS_PENDING_PAYMENT,
            ])
            ->whereNull('exit_at_utc')
            ->orderByRaw('CASE WHEN entry_at_utc IS NOT NULL THEN 0 ELSE 1 END ASC')
            ->orderBy('start_at_utc', 'asc')
            ->orderBy('id', 'asc')
            ->with(['service', 'client', 'provider'])
            ->first();
    }

    /**
     * Aggregate status counts breakdown for the filtered day stream.
     */
    public function getStatusCounts(Builder $baseQuery): array
    {
        $raw = (clone $baseQuery)
            ->selectRaw("
                COUNT(id) as total,
                SUM(CASE WHEN entry_at_utc IS NOT NULL AND status NOT IN ('CANCELED_BY_ADMIN', 'CANCELED_BY_CLIENT', 'NO_SHOW') THEN 1 ELSE 0 END) as attended,
                SUM(CASE WHEN status IN ('PENDING', 'PENDING_PAYMENT') THEN 1 ELSE 0 END) as waiting,
                SUM(CASE WHEN status = 'CONFIRMED' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status IN ('CANCELED_BY_ADMIN', 'CANCELED_BY_CLIENT') THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'NO_SHOW' THEN 1 ELSE 0 END) as noshow,
                SUM(CASE WHEN status = 'DONE' THEN 1 ELSE 0 END) as done
            ")
            ->first();

        return [
            'total' => (int) ($raw->total ?? 0),
            'attended' => (int) ($raw->attended ?? 0),
            'waiting' => (int) ($raw->waiting ?? 0),
            'confirmed' => (int) ($raw->confirmed ?? 0),
            'cancelled' => (int) ($raw->cancelled ?? 0),
            'noshow' => (int) ($raw->noshow ?? 0),
            'done' => (int) ($raw->done ?? 0),
        ];
    }

    /**
     * Get accessible providers list based on current user permissions.
     */
    public function getAccessibleProviders(?User $user = null): Collection
    {
        $user = $user ?? Auth::user();
        $settings = BookingSetting::current();

        $query = User::query()->orderBy('name');

        if ($user && ! $this->canViewAllAppointments($user)) {
            $query->where('id', $user->id);

            return $query->get(['id', 'name', 'email']);
        }

        $allowedRoleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn ($v) => $v > 0
        ));

        if (! empty($allowedRoleIds)) {
            $query->whereHas('roles', fn ($q) => $q->whereIn('id', $allowedRoleIds));
        } else {
            $query->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'doctor'))
                    ->orWhereIn('id', function ($sub) {
                        $sub->select('provider_user_id')->from('booking_service_providers')->where('is_active', true);
                    });
            });
        }

        return $query->get(['id', 'name', 'email']);
    }

    /**
     * Get active services list.
     */
    public function getActiveServices(): Collection
    {
        return BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'base_price']);
    }

    /**
     * Get live status summary for each provider on the given date stream.
     */
    public function getProvidersLiveSummary(Builder $baseQuery, Collection $providers): Collection
    {
        $appointments = (clone $baseQuery)
            ->with(['service:id,name,color', 'client:id,full_name,phone,national_code,case_number', 'provider:id,name'])
            ->get();

        $grouped = $appointments->groupBy('provider_user_id');
        $nowUtc = now('UTC');

        return $providers->map(function ($provider) use ($grouped, $nowUtc) {
            $providerApts = $grouped->get($provider->id, collect());

            $totalCount = $providerApts->count();
            $doneCount = $providerApts->where('status', Appointment::STATUS_DONE)->count();
            $attendedCount = $providerApts->filter(fn ($a) => ! empty($a->entry_at_utc) && empty($a->exit_at_utc) && ! in_array($a->status, [
                Appointment::STATUS_DONE,
                Appointment::STATUS_CANCELED_BY_ADMIN,
                Appointment::STATUS_CANCELED_BY_CLIENT,
                Appointment::STATUS_NO_SHOW,
            ]))->count();

            // 1. Physically checked-in patient currently in consultation (earliest entry prioritized)
            $activePatient = $providerApts->first(fn ($a) => ! empty($a->entry_at_utc) && empty($a->exit_at_utc) && ! in_array($a->status, [
                Appointment::STATUS_DONE,
                Appointment::STATUS_CANCELED_BY_ADMIN,
                Appointment::STATUS_CANCELED_BY_CLIENT,
                Appointment::STATUS_NO_SHOW,
            ]));

            // 2. In-window fallback
            if (! $activePatient) {
                $activePatient = $providerApts->first(fn ($a) => $a->status === Appointment::STATUS_CONFIRMED && $a->start_at_utc <= $nowUtc && $a->end_at_utc >= $nowUtc && empty($a->exit_at_utc));
            }

            // Resolve next in queue for this provider
            $nextPatient = $providerApts->filter(function ($a) use ($activePatient) {
                if ($activePatient && $a->id === $activePatient->id) {
                    return false;
                }

                return in_array($a->status, [
                    Appointment::STATUS_CONFIRMED,
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_PENDING_PAYMENT,
                ]) && empty($a->exit_at_utc);
            })->sortBy([
                fn ($a) => ! empty($a->entry_at_utc) ? 0 : 1,
                fn ($a) => $a->start_at_utc?->timestamp ?? 0,
                fn ($a) => $a->id,
            ])->first();

            return [
                'provider' => $provider,
                'total' => $totalCount,
                'done' => $doneCount,
                'attended' => $attendedCount,
                'active_patient' => $activePatient,
                'next_patient' => $nextPatient,
                'has_appointments' => $totalCount > 0,
            ];
        });
    }

    /**
     * Get list of patients currently waiting in the clinic lobby (checked-in, not yet exited, not currently in consultation).
     */
    public function getLobbyWaitingPatients(Builder $baseQuery, ?int $providerId = null): Collection
    {
        $query = (clone $baseQuery)
            ->whereNotNull('entry_at_utc')
            ->whereNull('exit_at_utc')
            ->whereNotIn('status', [
                Appointment::STATUS_DONE,
                Appointment::STATUS_CANCELED_BY_ADMIN,
                Appointment::STATUS_CANCELED_BY_CLIENT,
                Appointment::STATUS_NO_SHOW,
            ])
            ->with(['service:id,name,color', 'client:id,full_name,phone,national_code,case_number', 'provider:id,name'])
            ->orderBy('entry_at_utc', 'asc');

        if (! empty($providerId)) {
            $query->where('provider_user_id', $providerId);
        }

        return $query->get();
    }

    /**
     * Retrieve filter preferences stored in session.
     */
    public function getSessionFilters(): array
    {
        return [
            'provider_id' => session('clinic_monitoring_provider_id') ?? session('monitoring_filter_provider'),
            'service_id' => session('clinic_monitoring_service_id') ?? session('monitoring_filter_service'),
            'status' => session('clinic_monitoring_status_filter') ?? session('monitoring_filter_status', ''),
            'date' => session('clinic_monitoring_date') ?? session('monitoring_filter_date'),
        ];
    }

    /**
     * Store filter preferences in session across compatible keys.
     */
    public function putSessionFilter(string $key, mixed $value): void
    {
        match ($key) {
            'provider_id' => session([
                'clinic_monitoring_provider_id' => $value,
                'monitoring_filter_provider' => $value,
            ]),
            'service_id' => session([
                'clinic_monitoring_service_id' => $value,
                'monitoring_filter_service' => $value,
            ]),
            'status' => session([
                'clinic_monitoring_status_filter' => $value,
                'monitoring_filter_status' => $value,
            ]),
            'date' => session([
                'clinic_monitoring_date' => $value,
                'monitoring_filter_date' => $value,
            ]),
            default => null,
        };
    }

    /**
     * Clear all monitoring filter preferences from session.
     */
    public function clearSessionFilters(): void
    {
        session()->forget([
            'clinic_monitoring_provider_id',
            'monitoring_filter_provider',
            'clinic_monitoring_service_id',
            'monitoring_filter_service',
            'clinic_monitoring_status_filter',
            'monitoring_filter_status',
            'clinic_monitoring_date',
            'monitoring_filter_date',
            'clinic_monitoring_date_jalali',
        ]);
    }
}
