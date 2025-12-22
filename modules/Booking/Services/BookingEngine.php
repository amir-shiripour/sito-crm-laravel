<?php

namespace Modules\Booking\Services;

use Carbon\Carbon;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Booking\Entities\BookingAvailabilityRule;
use Modules\Booking\Entities\BookingAvailabilityException;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingSlotHold;

class BookingEngine
{
    /**
     * Resolve the service-provider pivot row (service_id + provider_user_id).
     */
    public function getServiceProvider(int $serviceId, int $providerUserId): ?BookingServiceProvider
    {
        return BookingServiceProvider::query()
            ->where('service_id', $serviceId)
            ->where('provider_user_id', $providerUserId)
            ->first();
    }

    /**
     * Resolve day policy by merging layers:
     * Global -> Service -> Provider(User) -> Provider(Pivot) -> Exceptions (Global/Service/Provider(User)/Provider(Pivot))
     *
     * Input local date is in schedule timezone.
     *
     * Output:
     * [
     *   'is_closed' => bool,
     *   'work_windows' => array<array{start:string,end:string}>,
     *   'breaks' => array<array{start_local:string,end_local:string}>,
     *   'slot_duration_minutes' => int,
     *   'capacity_per_slot' => int, // 0 => unlimited
     *   'capacity_per_day' => ?int, // null => unlimited
     * ]
     */
    public function resolveDayPolicy(int $serviceId, int $providerUserId, Carbon $localDate): array
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $localDate = $localDate->copy()->timezone($scheduleTz)->startOfDay();

        $weekday = ((int)$localDate->dayOfWeek + 1) % 7; // 0=Sat .. 6=Fri

        $settings = BookingSetting::current();

        // Base defaults
        $policy = [
            'is_closed' => true,
            'work_windows' => [],
            'breaks' => [],
            'slot_duration_minutes' => (int)($settings->default_slot_duration_minutes ?? config('booking.defaults.slot_duration_minutes', 30)),
            'capacity_per_slot' => (int)($settings->default_capacity_per_slot ?? config('booking.defaults.capacity_per_slot', 1)),
            'capacity_per_day' => $settings->default_capacity_per_day ?? config('booking.defaults.capacity_per_day', null),
        ];

        $svc = BookingService::query()->find($serviceId);
        if (!$svc) {
            $policy['is_closed'] = true;
            $policy['work_windows'] = [];
            return $policy;
        }

        if ($svc->status !== BookingService::STATUS_ACTIVE) {
            $policy['is_closed'] = true;
            $policy['work_windows'] = [];
            return $policy;
        }

        $sp = $this->getServiceProvider($serviceId, $providerUserId);

        if (!$sp || !$sp->is_active) {
            $policy['is_closed'] = true;
            $policy['work_windows'] = [];
            return $policy;
        }

        if ($sp->effectiveStatus() !== BookingService::STATUS_ACTIVE) {
            $policy['is_closed'] = true;
            $policy['work_windows'] = [];
            return $policy;
        }

        // --------------------
        // Rules: GLOBAL -> SERVICE -> PROVIDER(USER) -> PROVIDER(PIVOT)
        // --------------------

        $globalRule = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_GLOBAL)
            ->where('weekday', $weekday)
            ->first();

        $policy = $this->applyRule($policy, $globalRule);

        $serviceRule = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE)
            ->where('scope_id', $serviceId)
            ->where('weekday', $weekday)
            ->first();

        $policy = $this->applyRule($policy, $serviceRule);

        // Provider rule stored by UI: scope_id = provider_user_id
        $providerUserRule = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $providerUserId)
            ->where('weekday', $weekday)
            ->first();

        $policy = $this->applyRule($policy, $providerUserRule);

        // Optional more-specific layer: scope_id = booking_service_providers.id (pivot)
        $providerPivotRule = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', (int)$sp->id)
            ->where('weekday', $weekday)
            ->first();

        $policy = $this->applyRule($policy, $providerPivotRule);

        // --------------------
        // Exceptions: GLOBAL -> SERVICE -> PROVIDER(USER) -> PROVIDER(PIVOT)  (last wins)
        // --------------------

        $localDateStr = $localDate->toDateString();

        $globalEx = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_GLOBAL)
            ->whereDate('local_date', $localDateStr)
            ->first();

        $policy = $this->applyException($policy, $globalEx);

        $serviceEx = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE)
            ->where('scope_id', $serviceId)
            ->whereDate('local_date', $localDateStr)
            ->first();

        $policy = $this->applyException($policy, $serviceEx);

        // Provider exception stored by UI: scope_id = provider_user_id
        $providerUserEx = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $providerUserId)
            ->whereDate('local_date', $localDateStr)
            ->first();

        $policy = $this->applyException($policy, $providerUserEx);

        // Optional more-specific: scope_id = booking_service_providers.id (pivot)
        $providerPivotEx = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', (int)$sp->id)
            ->whereDate('local_date', $localDateStr)
            ->first();

        $policy = $this->applyException($policy, $providerPivotEx);

        // --------------------
        // Normalize
        // --------------------

        $policy['slot_duration_minutes'] = max(5, (int)($policy['slot_duration_minutes'] ?? 30));

        // capacity_per_slot: 0 or null => unlimited
        $capSlot = $policy['capacity_per_slot'];
        $policy['capacity_per_slot'] = max(0, (int)($capSlot ?? 0));

        // capacity_per_day: null or <=0 => unlimited (keep null)
        $capDay = $policy['capacity_per_day'];
        if ($capDay === null) {
            $policy['capacity_per_day'] = null;
        } else {
            $capDay = (int)$capDay;
            $policy['capacity_per_day'] = $capDay <= 0 ? null : $capDay;
        }

        // Normalize windows and breaks (accept start/end OR start_local/end_local)
        $policy['work_windows'] = $this->normalizeWorkWindows($policy['work_windows'] ?? []);
        $policy['breaks'] = array_values(array_filter($policy['breaks'] ?? [], fn($b) => !empty($b['start_local']) && !empty($b['end_local'])));

        return $policy;
    }

    protected function applyRule(array $policy, ?BookingAvailabilityRule $rule): array
    {
        if (!$rule) {
            return $policy;
        }

        if ($rule->is_closed) {
            $policy['is_closed'] = true;
            $policy['work_windows'] = [];
            $policy['breaks'] = [];
            return $policy;
        }

        // Work window override if provided
        if ($rule->work_start_local && $rule->work_end_local) {
            $policy['is_closed'] = false;
            $policy['work_windows'] = [[
                'start' => substr((string)$rule->work_start_local, 0, 5),
                'end' => substr((string)$rule->work_end_local, 0, 5),
            ]];
        }

        // Breaks override only if not null (to allow [] as "no breaks")
        if ($rule->breaks_json !== null) {
            $breaks = $this->ensureArray($rule->breaks_json);
            $policy['breaks'] = $breaks ?: [];
        }

        if ($rule->slot_duration_minutes !== null) {
            $policy['slot_duration_minutes'] = (int)$rule->slot_duration_minutes;
        }

        if ($rule->capacity_per_slot !== null) {
            $policy['capacity_per_slot'] = $rule->capacity_per_slot;
        }

        if ($rule->capacity_per_day !== null) {
            $policy['capacity_per_day'] = $rule->capacity_per_day;
        }

        return $policy;
    }

    protected function applyException(array $policy, ?BookingAvailabilityException $ex): array
    {
        if (!$ex) {
            return $policy;
        }

        if ($ex->is_closed) {
            $policy['is_closed'] = true;
            $policy['work_windows'] = [];
            $policy['breaks'] = [];
            return $policy;
        }

        if ($ex->override_work_windows_json !== null) {
            $windowsRaw = $this->ensureArray($ex->override_work_windows_json) ?: [];
            $policy['work_windows'] = $this->normalizeWorkWindows($windowsRaw);

            // اگر Exception پنجره کاری داد ولی خالی بود => بسته
            $policy['is_closed'] = empty($policy['work_windows']);
        }

        if ($ex->override_breaks_json !== null) {
            $breaks = $this->ensureArray($ex->override_breaks_json);
            $policy['breaks'] = $breaks ?: [];
        }

        if ($ex->override_capacity_per_slot !== null) {
            $policy['capacity_per_slot'] = $ex->override_capacity_per_slot;
        }

        if ($ex->override_capacity_per_day !== null) {
            $policy['capacity_per_day'] = $ex->override_capacity_per_day;
        }

        return $policy;
    }

    /**
     * Generate free slots with remaining capacity.
     *
     * @param string $fromLocalDate YYYY-MM-DD in schedule timezone
     * @param string $toLocalDate   YYYY-MM-DD in schedule timezone
     */
    public function generateSlots(
        int     $serviceId,
        int     $providerUserId,
        string  $fromLocalDate,
        string  $toLocalDate,
        ?string $viewerTimezone = null
    ): array
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $viewerTz = $viewerTimezone ?: config('booking.timezones.display_default', $scheduleTz);

        $from = Carbon::createFromFormat('Y-m-d', $fromLocalDate, $scheduleTz)->startOfDay();
        $to   = Carbon::createFromFormat('Y-m-d', $toLocalDate, $scheduleTz)->startOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        $slotsOut = [];

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $policy = $this->resolveDayPolicy($serviceId, $providerUserId, $date);

            if ($policy['is_closed'] || empty($policy['work_windows'])) {
                continue;
            }

            // Day range in UTC for aggregation
            $dayStartUtc = $date->copy()->startOfDay()->timezone('UTC');
            $dayEndUtc   = $date->copy()->addDay()->startOfDay()->timezone('UTC');

            $statuses = (array)config('booking.capacity_consuming_statuses', []);

            // appointments by slot start
            $apptCounts = Appointment::query()
                ->selectRaw('start_at_utc, COUNT(*) as cnt')
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $providerUserId)
                ->whereIn('status', $statuses)
                ->where('start_at_utc', '>=', $dayStartUtc)
                ->where('start_at_utc', '<', $dayEndUtc)
                ->groupBy('start_at_utc')
                ->pluck('cnt', 'start_at_utc');

            // holds by slot start
            $holdCounts = BookingSlotHold::query()
                ->selectRaw('start_at_utc, COUNT(*) as cnt')
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $providerUserId)
                ->where('expires_at_utc', '>', now('UTC'))
                ->where('start_at_utc', '>=', $dayStartUtc)
                ->where('start_at_utc', '<', $dayEndUtc)
                ->groupBy('start_at_utc')
                ->pluck('cnt', 'start_at_utc');

            $dailyBooked = (int)($apptCounts->sum() ?? 0);
            $dailyHeld   = (int)($holdCounts->sum() ?? 0);

            $slotDuration    = (int)$policy['slot_duration_minutes'];
            $capacityPerSlot = (int)($policy['capacity_per_slot'] ?? 0); // 0 => unlimited

            $capacityPerDay = $policy['capacity_per_day']; // null => unlimited
            $dailyRemaining = $capacityPerDay !== null
                ? max(0, (int)$capacityPerDay - $dailyBooked - $dailyHeld)
                : null;

            foreach ($policy['work_windows'] as $win) {
                $winStart = $this->makeLocalDateTime($date, $win['start'], $scheduleTz);
                $winEnd   = $this->makeLocalDateTime($date, $win['end'], $scheduleTz);

                if (!$winStart || !$winEnd || $winEnd->lte($winStart)) {
                    continue;
                }

                $cursor = $winStart->copy();
                while ($cursor->copy()->addMinutes($slotDuration)->lte($winEnd)) {
                    $slotStartLocal = $cursor->copy();
                    $slotEndLocal   = $cursor->copy()->addMinutes($slotDuration);

                    if ($this->isInBreak($slotStartLocal, $slotEndLocal, $policy['breaks'] ?? [])) {
                        $cursor->addMinutes($slotDuration);
                        continue;
                    }

                    $slotStartUtc = $slotStartLocal->copy()->timezone('UTC');
                    $slotEndUtc   = $slotEndLocal->copy()->timezone('UTC');

                    // Skip past slots
                    if ($slotStartUtc->lt(now('UTC'))) {
                        $cursor->addMinutes($slotDuration);
                        continue;
                    }

                    // NOTE: pluck keys for timestamps usually come as "Y-m-d H:i:s"
                    $key = $slotStartUtc->format('Y-m-d H:i:s');

                    $booked = (int)($apptCounts[$key] ?? 0);
                    $held   = (int)($holdCounts[$key] ?? 0);

                    // slot capacity
                    if ($capacityPerSlot <= 0) {
                        $slotRemaining = null; // unlimited per slot
                    } else {
                        $slotRemaining = max(0, $capacityPerSlot - $booked - $held);
                    }

                    // day capacity cap (if any)
                    if ($dailyRemaining !== null) {
                        if ($slotRemaining === null) {
                            $slotRemaining = $dailyRemaining;
                        } else {
                            $slotRemaining = min($slotRemaining, $dailyRemaining);
                        }
                    }

                    if ($slotRemaining === null || $slotRemaining > 0) {
                        $slotsOut[] = [
                            'local_date' => $date->toDateString(),
                            'start_at_utc' => $slotStartUtc->toIso8601String(),
                            'end_at_utc' => $slotEndUtc->toIso8601String(),
                            'start_at_view' => $slotStartUtc->copy()->timezone($viewerTz)->toIso8601String(),
                            'end_at_view' => $slotEndUtc->copy()->timezone($viewerTz)->toIso8601String(),
                            'remaining_capacity' => $slotRemaining, // null => unlimited
                            'capacity_per_slot' => $capacityPerSlot,
                            'capacity_per_day_remaining' => $dailyRemaining,
                        ];
                    }

                    $cursor->addMinutes($slotDuration);
                }
            }
        }

        usort($slotsOut, fn($a, $b) => strcmp($a['start_at_utc'], $b['start_at_utc']));
        return $slotsOut;
    }

    protected function makeLocalDateTime(Carbon $localDate, string $time, string $tz): ?Carbon
    {
        $time = trim($time);
        if (!preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            return null;
        }

        [$h, $m] = array_map('intval', explode(':', $time));
        return $localDate->copy()->timezone($tz)->setTime($h, $m, 0);
    }

    protected function isInBreak(Carbon $slotStartLocal, Carbon $slotEndLocal, array $breaks): bool
    {
        foreach ($breaks as $b) {
            $s = $b['start_local'] ?? null;
            $e = $b['end_local'] ?? null;
            if (!$s || !$e) continue;

            $bStart = $this->makeLocalDateTime(
                $slotStartLocal->copy()->startOfDay(),
                $s,
                $slotStartLocal->getTimezone()->getName()
            );
            $bEnd = $this->makeLocalDateTime(
                $slotStartLocal->copy()->startOfDay(),
                $e,
                $slotStartLocal->getTimezone()->getName()
            );
            if (!$bStart || !$bEnd) continue;

            // overlap
            if ($slotStartLocal->lt($bEnd) && $slotEndLocal->gt($bStart)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve online booking availability for a given service+provider.
     */
    public function isOnlineBookingEnabled(int $serviceId, int $providerUserId): bool
    {
        $settings = BookingSetting::current();
        if (!$settings->global_online_booking_enabled) {
            return false;
        }

        $svc = BookingService::query()->find($serviceId);
        if (!$svc) return false;

        $sp = $this->getServiceProvider($serviceId, $providerUserId);
        if (!$sp || !$sp->is_active) return false;

        $mode = $sp->effectiveOnlineBookingMode();

        return match ($mode) {
            BookingService::ONLINE_MODE_FORCE_ON => true,
            BookingService::ONLINE_MODE_FORCE_OFF => false,
            default => true, // INHERIT => from global, which is enabled here
        };
    }

    // ------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------

    /**
     * Ensure JSON-ish field is an array.
     */
    protected function ensureArray($value): array
    {
        if ($value === null) return [];
        if (is_array($value)) return $value;

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Normalize work windows to array of ['start' => 'HH:MM', 'end' => 'HH:MM']
     * Accepts either start/end or start_local/end_local.
     */
    protected function normalizeWorkWindows(array $windows): array
    {
        $out = [];
        foreach ($windows as $w) {
            if (!is_array($w)) continue;

            $start = $w['start'] ?? $w['start_local'] ?? null;
            $end   = $w['end'] ?? $w['end_local'] ?? null;

            $start = is_string($start) ? substr(trim($start), 0, 5) : null;
            $end   = is_string($end) ? substr(trim($end), 0, 5) : null;

            if (!$start || !$end) continue;

            $out[] = ['start' => $start, 'end' => $end];
        }

        return array_values($out);
    }
}
