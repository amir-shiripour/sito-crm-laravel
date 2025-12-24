<?php

namespace Modules\Booking\Console\Commands;

use Illuminate\Console\Command;
use Modules\Booking\Entities\BookingSlotHold;

class BookingCleanupHolds extends Command
{
    protected $signature = 'booking:cleanup-holds {--dry-run : Do not delete, only report}';
    protected $description = 'Cleanup expired booking slot holds (prevents ghost capacity usage).';

    public function handle(): int
    {
        $now = now(config('booking.timezones.storage', 'UTC'));

        $query = BookingSlotHold::query()->where('expires_at_utc', '<=', $now);

        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Expired holds: {$count} (dry-run)");
            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Deleted expired holds: {$deleted}");

        return self::SUCCESS;
    }
}
