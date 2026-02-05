<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('sms:process-scheduled')->everyMinute();
        $schedule->command('workflows:process')->everyMinute();
        $schedule->command('booking:dispatch-reminders')->everyMinute();

        // دستور تست کرون - بعد از اطمینان از کارکرد، این خط را حذف کنید
        $schedule->command('sms:test-cron')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
