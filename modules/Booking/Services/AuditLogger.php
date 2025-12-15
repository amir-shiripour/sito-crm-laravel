<?php

namespace Modules\Booking\Services;

use Modules\Booking\Entities\BookingAuditLog;

class AuditLogger
{
    public function log(
        string $action,
        string $entityType,
        ?int $entityId,
        ?int $userId = null,
        ?array $before = null,
        ?array $after = null,
        ?array $meta = null
    ): void {
        BookingAuditLog::query()->create([
            'user_id' => $userId,
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
