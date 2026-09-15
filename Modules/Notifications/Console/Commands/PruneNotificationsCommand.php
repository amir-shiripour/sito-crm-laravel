<?php

namespace Modules\Notifications\Console\Commands;

use Illuminate\Console\Command;
use Modules\Notifications\Entities\Notification;

class PruneNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:prune 
                            {--days= : Number of days to keep read notifications (defaults to config)} 
                            {--dry-run : Simulate pruning without deleting records}';

    /**
     * The console command description.
     */
    protected $description = 'Prune old read database notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('notifications.pruning.days', 60));
        $dryRun = (bool) $this->option('dry-run');

        $cutoffDate = now()->subDays($days);

        $query = Notification::whereNotNull('read_at')
            ->where('created_at', '<', $cutoffDate);

        $count = $query->count();

        if ($dryRun) {
            $this->info("[Dry Run] Found {$count} read notifications older than {$days} days ({$cutoffDate->toDateTimeString()}) eligible for pruning.");
            return 0;
        }

        $deleted = $query->delete();
        $this->info("Successfully pruned {$deleted} old read notifications.");

        return 0;
    }
}
