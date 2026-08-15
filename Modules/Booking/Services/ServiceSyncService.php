<?php

namespace Modules\Booking\Services;

use Carbon\Carbon;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingSlotHold;

class ServiceSyncService
{
    public const SETTING_KEY = 'service_sync_groups';

    /**
     * Get all configured sync groups.
     * Array format:
     * [
     *   [
     *     'id' => 'group_1',
     *     'name' => 'هماهنگی ۱',
     *     'provider_user_id' => null|int,
     *     'service_ids' => [3, 7]
     *   ]
     * ]
     */
    public function getGroups(): array
    {
        $raw = BookingSetting::getValue(self::SETTING_KEY, []);
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($raw)) {
            return [];
        }

        $clean = [];
        foreach ($raw as $index => $item) {
            if (!is_array($item)) continue;

            $serviceIds = array_values(array_unique(array_filter(
                array_map('intval', $item['service_ids'] ?? []),
                fn($id) => $id > 0
            )));

            $clean[] = [
                'id' => $item['id'] ?? ('group_' . ($index + 1)),
                'name' => !empty($item['name']) ? trim((string)$item['name']) : ('هماهنگی ' . ($index + 1)),
                'provider_user_id' => !empty($item['provider_user_id']) ? (int)$item['provider_user_id'] : null,
                'service_ids' => $serviceIds,
            ];
        }

        return $clean;
    }

    /**
     * Save groups to database.
     */
    public function saveGroups(array $groups): void
    {
        $clean = [];
        foreach ($groups as $index => $item) {
            if (!is_array($item)) continue;

            $serviceIds = array_values(array_unique(array_filter(
                array_map('intval', $item['service_ids'] ?? []),
                fn($id) => $id > 0
            )));

            // Save groups that have at least one service
            if (empty($serviceIds)) {
                continue;
            }

            $providerUserId = (!empty($item['provider_user_id']) && (int)$item['provider_user_id'] > 0) ? (int)$item['provider_user_id'] : null;

            $clean[] = [
                'id' => !empty($item['id']) ? trim((string)$item['id']) : ('group_' . ($index + 1)),
                'name' => !empty($item['name']) ? trim((string)$item['name']) : ('هماهنگی ' . ($index + 1)),
                'provider_user_id' => $providerUserId,
                'service_ids' => $serviceIds,
            ];
        }

        BookingSetting::setValue(self::SETTING_KEY, json_encode($clean, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Find all sibling service IDs synchronized with $serviceId for a given $providerUserId.
     *
     * @return int[]
     */
    public function getSiblingServiceIds(int $serviceId, ?int $providerUserId = null): array
    {
        $groups = $this->getGroups();
        $siblings = [];

        foreach ($groups as $group) {
            if (!in_array($serviceId, $group['service_ids'], true)) {
                continue;
            }

            // Check provider scope compatibility:
            // If group provider_user_id is null, it applies to all providers.
            // If group provider_user_id matches $providerUserId, it applies to this provider.
            $groupProviderId = $group['provider_user_id'];
            if ($groupProviderId !== null && $providerUserId !== null && (int)$groupProviderId !== (int)$providerUserId) {
                continue;
            }

            foreach ($group['service_ids'] as $sId) {
                if ($sId !== $serviceId) {
                    $siblings[] = $sId;
                }
            }
        }

        return array_values(array_unique($siblings));
    }

    /**
     * Check if a time slot is blocked by any synchronized sibling service.
     */
    public function isSyncBlocked(
        int $serviceId,
        int $providerUserId,
        Carbon $checkStartUtc,
        Carbon $checkEndUtc,
        array $statuses,
        ?int $excludeHoldId = null,
        ?int $excludeAppointmentId = null
    ): bool {
        $siblingServiceIds = $this->getSiblingServiceIds($serviceId, $providerUserId);
        if (empty($siblingServiceIds)) {
            return false;
        }

        // Check if any sibling service has an appointment or hold for the provider in that time range
        $appQuery = Appointment::query()
            ->whereIn('service_id', $siblingServiceIds)
            ->whereIn('status', $statuses)
            ->where('start_at_utc', '<', $checkEndUtc)
            ->where('end_at_utc', '>', $checkStartUtc);

        if ($providerUserId) {
            $appQuery->where('provider_user_id', $providerUserId);
        }

        if ($excludeAppointmentId) {
            $appQuery->where('id', '!=', $excludeAppointmentId);
        }

        if ($appQuery->exists()) {
            return true;
        }

        $holdQuery = BookingSlotHold::query()
            ->whereIn('service_id', $siblingServiceIds)
            ->where('expires_at_utc', '>', now('UTC'))
            ->where('start_at_utc', '<', $checkEndUtc)
            ->where('end_at_utc', '>', $checkStartUtc);

        if ($providerUserId) {
            $holdQuery->where('provider_user_id', $providerUserId);
        }

        if ($excludeHoldId) {
            $holdQuery->where('id', '!=', $excludeHoldId);
        }

        return $holdQuery->exists();
    }
}
