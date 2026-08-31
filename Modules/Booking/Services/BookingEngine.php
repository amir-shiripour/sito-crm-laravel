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
     *   'rule_source' => string, // GLOBAL, SERVICE, SERVICE_PROVIDER, EXCEPTION
     * ]
     */
    public function resolveDayPolicy(?int $serviceId, int $providerUserId, Carbon $localDate): array
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $localDate = $localDate->copy()->timezone($scheduleTz)->startOfDay();
        $weekday = ((int)$localDate->dayOfWeek + 1) % 7; // 0=Sat .. 6=Fri
        $localDateStr = $localDate->toDateString();

        $settings = BookingSetting::current();

        $svc = $serviceId ? BookingService::query()->find($serviceId) : null;
        if ($serviceId !== null && (!$svc || $svc->status !== BookingService::STATUS_ACTIVE)) {
            \Illuminate\Support\Facades\Log::warning('[BookingEngine::resolveDayPolicy] Service inactive or not found', [
                'serviceId' => $serviceId,
                'status' => $svc?->status ?? 'NOT_FOUND',
            ]);
            return [
                'is_closed' => true,
                'work_windows' => [],
                'inherited_work_windows' => [],
                'breaks' => [],
                'slot_duration_minutes' => (int)($settings->default_slot_duration_minutes ?? config('booking.defaults.slot_duration_minutes', 30)),
                'capacity_per_slot' => (int)($settings->default_capacity_per_slot ?? config('booking.defaults.capacity_per_slot', 1)),
                'capacity_per_day' => $settings->default_capacity_per_day ?? config('booking.defaults.capacity_per_day', null),
                'rule_source' => 'SERVICE',
            ];
        }

        $sp = $serviceId ? $this->getServiceProvider($serviceId, $providerUserId) : null;

        if ($serviceId !== null && (!$sp || !$sp->is_active || $sp->effectiveStatus() !== BookingService::STATUS_ACTIVE)) {
            \Illuminate\Support\Facades\Log::warning('[BookingEngine::resolveDayPolicy] Provider-Service pivot link inactive or missing', [
                'serviceId' => $serviceId,
                'providerUserId' => $providerUserId,
                'sp_exists' => (bool)$sp,
                'is_active' => $sp?->is_active ?? false,
                'effective_status' => $sp?->effectiveStatus() ?? 'NONE',
            ]);
            return [
                'is_closed' => true,
                'work_windows' => [],
                'inherited_work_windows' => [],
                'breaks' => [],
                'slot_duration_minutes' => (int)($settings->default_slot_duration_minutes ?? config('booking.defaults.slot_duration_minutes', 30)),
                'capacity_per_slot' => (int)($settings->default_capacity_per_slot ?? config('booking.defaults.capacity_per_slot', 1)),
                'capacity_per_day' => $settings->default_capacity_per_day ?? config('booking.defaults.capacity_per_day', null),
                'rule_source' => 'SERVICE_PROVIDER',
            ];
        }

        // --------------------
        // Check scope presence for Provider, Service, Global
        // If a scope has configured rules in DB, but no rule/exception for today,
        // then that scope is NOT active today (is_closed = true).
        // --------------------
        $providerHasAnyRules = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $providerUserId)
            ->exists() || BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $providerUserId)
            ->exists();

        $serviceHasAnyRules = $serviceId ? (BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE)
            ->where('scope_id', $serviceId)
            ->exists() || BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE)
            ->where('scope_id', $serviceId)
            ->exists()) : false;

        $globalHasAnyRules = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_GLOBAL)
            ->exists() || BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_GLOBAL)
            ->exists();

        $layers = [];

        // 1. GLOBAL Layer
        $globalEx = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_GLOBAL)
            ->whereDate('local_date', $localDateStr)
            ->first();
        $globalRule = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_GLOBAL)
            ->where('weekday', $weekday)
            ->first();
        $globalCfg = $this->extractLayerConfig($globalEx, $globalRule, 'GLOBAL');
        if ($globalHasAnyRules && !$globalCfg) {
            $globalCfg = ['is_closed' => true, 'work_windows' => null, 'breaks' => [], 'slot_duration_minutes' => null, 'capacity_per_slot' => null, 'capacity_per_day' => null, 'source_label' => 'GLOBAL'];
        }
        $layers['GLOBAL'] = $globalCfg;

        // 2. PROVIDER USER Layer
        $providerUserEx = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $providerUserId)
            ->whereDate('local_date', $localDateStr)
            ->first();
        $providerUserRule = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $providerUserId)
            ->where('weekday', $weekday)
            ->first();
        $providerCfg = $this->extractLayerConfig($providerUserEx, $providerUserRule, 'SERVICE_PROVIDER');
        if ($providerHasAnyRules && !$providerCfg) {
            $providerCfg = ['is_closed' => true, 'work_windows' => null, 'breaks' => [], 'slot_duration_minutes' => null, 'capacity_per_slot' => null, 'capacity_per_day' => null, 'source_label' => 'SERVICE_PROVIDER'];
        }
        $layers['PROVIDER_USER'] = $providerCfg;

        // 3. SERVICE Layer
        if ($serviceId !== null && $svc && ($serviceHasAnyRules || $svc->custom_schedule_enabled || $svc->capacity_per_slot !== null || $svc->capacity_per_day !== null)) {
            $serviceEx = BookingAvailabilityException::query()
                ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE)
                ->where('scope_id', $serviceId)
                ->whereDate('local_date', $localDateStr)
                ->first();
            $serviceRule = BookingAvailabilityRule::query()
                ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE)
                ->where('scope_id', $serviceId)
                ->where('weekday', $weekday)
                ->first();
            $serviceCfg = $this->extractLayerConfig($serviceEx, $serviceRule, 'SERVICE');
            if (!$serviceCfg && ($svc->capacity_per_slot !== null || $svc->capacity_per_day !== null)) {
                $serviceCfg = [
                    'is_closed' => false,
                    'work_windows' => null,
                    'breaks' => [],
                    'slot_duration_minutes' => null,
                    'capacity_per_slot' => $svc->capacity_per_slot,
                    'capacity_per_day' => $svc->capacity_per_day,
                    'source_label' => 'SERVICE',
                ];
            } elseif ($serviceCfg) {
                if (($serviceCfg['capacity_per_slot'] ?? null) === null && $svc->capacity_per_slot !== null) {
                    $serviceCfg['capacity_per_slot'] = (int)$svc->capacity_per_slot;
                }
                if (($serviceCfg['capacity_per_day'] ?? null) === null && $svc->capacity_per_day !== null) {
                    $serviceCfg['capacity_per_day'] = (int)$svc->capacity_per_day;
                }
            } elseif ($serviceHasAnyRules && !$serviceCfg) {
                $serviceCfg = ['is_closed' => true, 'work_windows' => null, 'breaks' => [], 'slot_duration_minutes' => null, 'capacity_per_slot' => null, 'capacity_per_day' => null, 'source_label' => 'SERVICE'];
            }
            $layers['SERVICE'] = $serviceCfg;
        }

        // 4. PROVIDER PIVOT Layer (Pivot ID)
        if ($sp) {
            $providerPivotEx = BookingAvailabilityException::query()
                ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE_PROVIDER)
                ->where('scope_id', (int)$sp->id)
                ->whereDate('local_date', $localDateStr)
                ->first();
            $providerPivotRule = BookingAvailabilityRule::query()
                ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER)
                ->where('scope_id', (int)$sp->id)
                ->where('weekday', $weekday)
                ->first();
            if ($providerPivotEx || $providerPivotRule) {
                $layers['PROVIDER_PIVOT'] = $this->extractLayerConfig($providerPivotEx, $providerPivotRule, 'SERVICE_PROVIDER');
            }
        }

        // --------------------
        // Combine Layers via Intersection of Windows, Union of Breaks, Closures, and Minimum Capacity Constraints
        // --------------------
        $isClosed = false;
        $ruleSource = 'GLOBAL';
        $effectiveWindows = null;
        $allBreaks = [];
        $windowDefinedByAnyLayer = false;

        $slotDuration = null;
        $slotCapacities = [];
        $dayCapacities = [];
        $bufferBefore = null;
        $bufferAfter = null;

        if ($svc && $svc->buffer_before_minutes !== null) {
            $bufferBefore = (int)$svc->buffer_before_minutes;
        }
        if ($svc && $svc->buffer_after_minutes !== null) {
            $bufferAfter = (int)$svc->buffer_after_minutes;
        }

        foreach ($layers as $cfg) {
            if (!$cfg) {
                continue;
            }

            if ($cfg['is_closed']) {
                $isClosed = true;
                $ruleSource = $cfg['source_label'];
                break;
            }

            if ($cfg['work_windows'] !== null) {
                $windowDefinedByAnyLayer = true;
                if ($effectiveWindows === null) {
                    $effectiveWindows = $cfg['work_windows'];
                } else {
                    $effectiveWindows = $this->intersectWorkWindows($effectiveWindows, $cfg['work_windows']);
                }
                $ruleSource = $cfg['source_label'];
            }

            if (!empty($cfg['breaks'])) {
                $allBreaks = array_merge($allBreaks, $cfg['breaks']);
            }

            if (!empty($cfg['slot_duration_minutes'])) {
                $slotDuration = (int)$cfg['slot_duration_minutes'];
            }

            if (isset($cfg['capacity_per_slot']) && $cfg['capacity_per_slot'] !== null && (int)$cfg['capacity_per_slot'] > 0) {
                $slotCapacities[] = (int)$cfg['capacity_per_slot'];
            }

            if (isset($cfg['capacity_per_day']) && $cfg['capacity_per_day'] !== null && (int)$cfg['capacity_per_day'] > 0) {
                $dayCapacities[] = (int)$cfg['capacity_per_day'];
            }

            if (isset($cfg['buffer_before_minutes']) && $cfg['buffer_before_minutes'] !== null) {
                $bufferBefore = (int)$cfg['buffer_before_minutes'];
            }

            if (isset($cfg['buffer_after_minutes']) && $cfg['buffer_after_minutes'] !== null) {
                $bufferAfter = (int)$cfg['buffer_after_minutes'];
            }
        }

        // If work windows were defined by layers but intersection yielded no overlapping times, close the day
        if ($windowDefinedByAnyLayer && empty($effectiveWindows)) {
            $isClosed = true;
        }

        // Capacity per slot is the MINIMUM limitation among configured active layers
        $capacityPerSlot = !empty($slotCapacities)
            ? min($slotCapacities)
            : (int)($settings->default_capacity_per_slot ?? config('booking.defaults.capacity_per_slot', 1));

        $capacityPerDay = !empty($dayCapacities)
            ? min($dayCapacities)
            : ($settings->default_capacity_per_day ?? config('booking.defaults.capacity_per_day', null));

        $slotDuration = $slotDuration ?? (int)($settings->default_slot_duration_minutes ?? config('booking.defaults.slot_duration_minutes', 30));
        $bufferBefore = max(0, (int)($bufferBefore ?? $settings->default_buffer_before_minutes ?? 0));
        $bufferAfter = max(0, (int)($bufferAfter ?? $settings->default_buffer_after_minutes ?? 0));

        $finalWindows = ($isClosed || $effectiveWindows === null) ? [] : $this->normalizeWorkWindows($effectiveWindows);

        if ($isClosed || empty($finalWindows)) {
            \Illuminate\Support\Facades\Log::info('[BookingEngine::resolveDayPolicy] Day is closed or no work windows', [
                'serviceId' => $serviceId,
                'providerUserId' => $providerUserId,
                'date' => $localDateStr,
                'weekday' => $weekday,
                'is_closed' => $isClosed,
                'rule_source' => $ruleSource,
                'work_windows' => $finalWindows,
            ]);
        }

        return [
            'is_closed' => $isClosed,
            'work_windows' => $finalWindows,
            'inherited_work_windows' => $finalWindows,
            'breaks' => array_values(array_filter($allBreaks, fn($b) => (!empty($b['start_local']) && !empty($b['end_local'])) || (!empty($b['start']) && !empty($b['end'])))),
            'slot_duration_minutes' => max(5, (int)$slotDuration),
            'capacity_per_slot' => max(0, (int)$capacityPerSlot),
            'capacity_per_day' => $capacityPerDay !== null ? ((int)$capacityPerDay <= 0 ? null : (int)$capacityPerDay) : null,
            'buffer_before_minutes' => $bufferBefore,
            'buffer_after_minutes' => $bufferAfter,
            'custom_schedule_enabled' => (bool)($svc->custom_schedule_enabled ?? false),
            'rule_source' => $ruleSource,
        ];
    }

    protected function extractLayerConfig(
        ?BookingAvailabilityException $ex,
        ?BookingAvailabilityRule $rule,
        string $scopeLabel
    ): ?array {
        if ($ex) {
            $windows = null;
            if ($ex->override_work_windows_json !== null) {
                $windows = $this->normalizeWorkWindows($this->ensureArray($ex->override_work_windows_json));
            }

            return [
                'is_closed' => (bool)$ex->is_closed,
                'work_windows' => $windows,
                'breaks' => $ex->override_breaks_json !== null ? $this->ensureArray($ex->override_breaks_json) : [],
                'slot_duration_minutes' => null,
                'capacity_per_slot' => $ex->override_capacity_per_slot,
                'capacity_per_day' => $ex->override_capacity_per_day,
                'buffer_before_minutes' => $ex->override_buffer_before_minutes,
                'buffer_after_minutes' => $ex->override_buffer_after_minutes,
                'source_label' => 'EXCEPTION_' . $scopeLabel,
            ];
        }

        if ($rule) {
            $windows = null;
            if ($rule->work_start_local && $rule->work_end_local) {
                $windows = [[
                    'start' => substr((string)$rule->work_start_local, 0, 5),
                    'end' => substr((string)$rule->work_end_local, 0, 5),
                ]];
            }

            return [
                'is_closed' => (bool)$rule->is_closed,
                'work_windows' => $windows,
                'breaks' => $rule->breaks_json !== null ? $this->ensureArray($rule->breaks_json) : [],
                'slot_duration_minutes' => $rule->slot_duration_minutes,
                'capacity_per_slot' => $rule->capacity_per_slot,
                'capacity_per_day' => $rule->capacity_per_day,
                'buffer_before_minutes' => $rule->buffer_before_minutes,
                'buffer_after_minutes' => $rule->buffer_after_minutes,
                'source_label' => $scopeLabel,
            ];
        }

        return null;
    }

    protected function intersectWorkWindows(array $windows1, array $windows2): array
    {
        if (empty($windows1) || empty($windows2)) {
            return [];
        }

        $out = [];
        foreach ($windows1 as $w1) {
            $s1 = substr(trim($w1['start'] ?? ''), 0, 5);
            $e1 = substr(trim($w1['end'] ?? ''), 0, 5);
            if (!$s1 || !$e1) continue;

            foreach ($windows2 as $w2) {
                $s2 = substr(trim($w2['start'] ?? ''), 0, 5);
                $e2 = substr(trim($w2['end'] ?? ''), 0, 5);
                if (!$s2 || !$e2) continue;

                $start = max($s1, $s2);
                $end   = min($e1, $e2);

                if ($start < $end) {
                    $out[] = ['start' => $start, 'end' => $end];
                }
            }
        }

        return array_values($out);
    }

    protected function applyRule(array $policy, ?BookingAvailabilityRule $rule, string $scopeLabel = 'GLOBAL'): array
    {
        if (!$rule) {
            return $policy;
        }

        if ($rule->is_closed) {
            $policy['is_closed'] = true;
            $policy['work_windows'] = [];
            $policy['breaks'] = [];
            $policy['rule_source'] = $scopeLabel;
            if ($scopeLabel === 'SERVICE') {
                $policy['service_is_closed'] = true;
            }
            return $policy;
        }

        // If service level explicitly closed this weekday, lower provider levels cannot re-open it
        if (!empty($policy['service_is_closed']) && $scopeLabel !== 'SERVICE') {
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

        // Work window override if provided
        if ($rule->work_start_local && $rule->work_end_local) {
            $policy['is_closed'] = false;
            $policy['work_windows'] = [[
                'start' => substr((string)$rule->work_start_local, 0, 5),
                'end' => substr((string)$rule->work_end_local, 0, 5),
            ]];
            $policy['rule_source'] = $scopeLabel;
        } elseif ($policy['is_closed'] && !empty($policy['inherited_work_windows'])) {
            // Rule explicitly opened day (is_closed = false), restore inherited windows
            $policy['is_closed'] = false;
            $policy['work_windows'] = $policy['inherited_work_windows'];
            $policy['rule_source'] = $scopeLabel;
        }

        if (!empty($policy['work_windows'])) {
            $policy['inherited_work_windows'] = $policy['work_windows'];
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

    protected function applyException(array $policy, ?BookingAvailabilityException $ex, string $scopeLabel = 'EXCEPTION'): array
    {
        if (!$ex) {
            return $policy;
        }

        if ($ex->is_closed) {
            $policy['is_closed'] = true;
            $policy['work_windows'] = [];
            $policy['breaks'] = [];
            $policy['rule_source'] = $scopeLabel;
            if ($ex->scope_type === BookingAvailabilityException::SCOPE_SERVICE) {
                $policy['service_is_closed'] = true;
            }
            return $policy;
        }

        // If service level explicitly closed this date, non-service exceptions cannot re-open it
        if (!empty($policy['service_is_closed']) && $ex->scope_type !== BookingAvailabilityException::SCOPE_SERVICE) {
            if ($ex->override_capacity_per_slot !== null) {
                $policy['capacity_per_slot'] = $ex->override_capacity_per_slot;
            }
            if ($ex->override_capacity_per_day !== null) {
                $policy['capacity_per_day'] = $ex->override_capacity_per_day;
            }
            return $policy;
        }

        if ($ex->override_work_windows_json !== null) {
            $windowsRaw = $this->ensureArray($ex->override_work_windows_json) ?: [];
            $policy['work_windows'] = $this->normalizeWorkWindows($windowsRaw);

            // اگر Exception پنجره کاری داد ولی خالی بود => بسته
            $policy['is_closed'] = empty($policy['work_windows']);
            $policy['rule_source'] = $scopeLabel;
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
        ?string $viewerTimezone = null,
        bool    $includePast = false
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
                \Illuminate\Support\Facades\Log::warning('[BookingEngine::generateSlots] Day skipped because closed or work_windows empty', [
                    'date' => $date->toDateString(),
                    'serviceId' => $serviceId,
                    'providerUserId' => $providerUserId,
                    'is_closed' => $policy['is_closed'],
                    'work_windows' => $policy['work_windows'],
                    'rule_source' => $policy['rule_source'],
                ]);
                continue;
            }

            // Day range in UTC for aggregation
            $dayStartUtc = $date->copy()->startOfDay()->timezone('UTC');
            $dayEndUtc   = $date->copy()->addDay()->startOfDay()->timezone('UTC');

            $statuses = (array)config('booking.capacity_consuming_statuses', []);

            $dayAppointments = Appointment::query()
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $providerUserId)
                ->whereIn('status', $statuses)
                ->where('start_at_utc', '<', $dayEndUtc)
                ->where('end_at_utc', '>', $dayStartUtc)
                ->get(['start_at_utc', 'end_at_utc']);

            $dayHolds = BookingSlotHold::query()
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $providerUserId)
                ->where('expires_at_utc', '>', now('UTC'))
                ->where('start_at_utc', '<', $dayEndUtc)
                ->where('end_at_utc', '>', $dayStartUtc)
                ->get(['start_at_utc', 'end_at_utc']);

            $dailyBooked = $dayAppointments->count();
            $dailyHeld   = $dayHolds->count();

            // Fetch sibling service appointments and holds for the day
            $syncService = app(\Modules\Booking\Services\ServiceSyncService::class);
            $siblingServiceIds = $syncService->getSiblingServiceIds($serviceId, $providerUserId);

            $siblingAppointments = collect();
            $siblingHolds = collect();
            if (!empty($siblingServiceIds)) {
                $siblingAppointments = Appointment::query()
                    ->whereIn('service_id', $siblingServiceIds)
                    ->where('provider_user_id', $providerUserId)
                    ->whereIn('status', $statuses)
                    ->where('start_at_utc', '<', $dayEndUtc)
                    ->where('end_at_utc', '>', $dayStartUtc)
                    ->get(['start_at_utc', 'end_at_utc']);

                $siblingHolds = BookingSlotHold::query()
                    ->whereIn('service_id', $siblingServiceIds)
                    ->where('provider_user_id', $providerUserId)
                    ->where('expires_at_utc', '>', now('UTC'))
                    ->where('start_at_utc', '<', $dayEndUtc)
                    ->where('end_at_utc', '>', $dayStartUtc)
                    ->get(['start_at_utc', 'end_at_utc']);
            }

            $slotDuration    = (int)$policy['slot_duration_minutes'];
            $capacityPerSlot = (int)($policy['capacity_per_slot'] ?? 0); // 0 => unlimited
            $bufBefore       = (int)($policy['buffer_before_minutes'] ?? 0);
            $bufAfter        = (int)($policy['buffer_after_minutes'] ?? 0);

            $capacityPerDay = $policy['capacity_per_day']; // null => unlimited
            $dailyRemaining = $capacityPerDay !== null
                ? max(0, (int)$capacityPerDay - $dailyBooked - $dailyHeld)
                : null;

            $stepMinutes = max(5, $slotDuration + $bufAfter);

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
                    $ovBreak = $this->getOverlappingBreak($slotStartLocal, $slotEndLocal, $policy['breaks'] ?? []);
                    if ($ovBreak) {
                        if ($ovBreak['end']->gt($cursor)) {
                            $cursor = $ovBreak['end']->copy();
                        } else {
                            $cursor->addMinutes($stepMinutes);
                        }
                        continue;
                    }

                    $slotStartUtc = $slotStartLocal->copy()->timezone('UTC');
                    $slotEndUtc   = $slotEndLocal->copy()->timezone('UTC');

                    // Skip past slots unless includePast is requested
                    if (!$includePast && $slotStartUtc->lt(now('UTC'))) {
                        $cursor->addMinutes($stepMinutes);
                        continue;
                    }

                    $reqStartWithBuf = $slotStartUtc->copy()->subMinutes($bufBefore);
                    $reqEndWithBuf   = $slotEndUtc->copy()->addMinutes($bufAfter);

                    $booked = $dayAppointments->filter(fn($a) => $a->start_at_utc < $reqEndWithBuf && $a->end_at_utc > $reqStartWithBuf)->count();
                    $held   = $dayHolds->filter(fn($h) => $h->start_at_utc < $reqEndWithBuf && $h->end_at_utc > $reqStartWithBuf)->count();

                    // Check if sibling services occupied this slot
                    $siblingBookedCount = $siblingAppointments->filter(fn($a) => $a->start_at_utc < $reqEndWithBuf && $a->end_at_utc > $reqStartWithBuf)->count();
                    $siblingHeldCount   = $siblingHolds->filter(fn($h) => $h->start_at_utc < $reqEndWithBuf && $h->end_at_utc > $reqStartWithBuf)->count();
                    $isSyncBlocked = ($siblingBookedCount + $siblingHeldCount) > 0;

                    // slot capacity
                    if ($isSyncBlocked) {
                        $slotRemaining = 0;
                    } elseif ($capacityPerSlot <= 0) {
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

                    if ($includePast || $slotRemaining === null || $slotRemaining > 0 || $isSyncBlocked) {
                        $slotsOut[] = [
                            'local_date' => $date->toDateString(),
                            'start_at_utc' => $slotStartUtc->toIso8601String(),
                            'end_at_utc' => $slotEndUtc->toIso8601String(),
                            'start_at_view' => $slotStartUtc->copy()->timezone($viewerTz)->toIso8601String(),
                            'end_at_view' => $slotEndUtc->copy()->timezone($viewerTz)->toIso8601String(),
                            'start_time' => $slotStartUtc->copy()->timezone($viewerTz)->format('H:i'),
                            'end_time' => $slotEndUtc->copy()->timezone($viewerTz)->format('H:i'),
                            'remaining_capacity' => $slotRemaining, // null => unlimited
                            'capacity_per_slot' => $capacityPerSlot,
                            'capacity_per_day_remaining' => $dailyRemaining,
                            'buffer_before_minutes' => $bufBefore,
                            'buffer_after_minutes' => $bufAfter,
                            'sync_blocked' => $isSyncBlocked,
                        ];
                    }

                    $cursor->addMinutes($stepMinutes);
                }
            }
        }

        usort($slotsOut, fn($a, $b) => strcmp($a['start_at_utc'], $b['start_at_utc']));

        \Illuminate\Support\Facades\Log::info('[BookingEngine::generateSlots] Diagnostic Result', [
            'serviceId' => $serviceId,
            'providerUserId' => $providerUserId,
            'fromLocalDate' => $fromLocalDate,
            'toLocalDate' => $toLocalDate,
            'includePast' => $includePast,
            'slots_count' => count($slotsOut),
            'slots' => array_map(fn($s) => $s['start_time'] . ' - ' . $s['end_time'] . ' (Cap: ' . var_export($s['remaining_capacity'], true) . ')', $slotsOut),
        ]);

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

    public function isInBreak(Carbon $slotStartLocal, Carbon $slotEndLocal, array $breaks): bool
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

    public function getOverlappingBreak(Carbon $slotStartLocal, Carbon $slotEndLocal, array $breaks): ?array
    {
        foreach ($breaks as $b) {
            $s = $b['start_local'] ?? $b['start'] ?? null;
            $e = $b['end_local'] ?? $b['end'] ?? null;
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

            if ($slotStartLocal->lt($bEnd) && $slotEndLocal->gt($bStart)) {
                return ['break' => $b, 'start' => $bStart, 'end' => $bEnd];
            }
        }

        return null;
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
