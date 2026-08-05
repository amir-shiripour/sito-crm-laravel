<?php

namespace Modules\Booking\Services;

use Modules\Booking\Entities\BookingAuditLog;

class AuditLogger
{
    public function log(
        string $action,
        string $entityType,
        ?int $entityId,
        int|array|null $userId = null,
        ?array $before = null,
        ?array $after = null,
        ?array $meta = null
    ): void {
        if (is_array($userId)) {
            $meta = $after;
            $after = $before;
            $before = $userId;
            $userId = null;
        }

        BookingAuditLog::query()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before' => $before,
            'after' => $after,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }
}
