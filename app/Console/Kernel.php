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
        // ==========================================
        // دستورات سنتی و هاردکد قبلی (جهت رفرنس حفظ شده است)
        // ==========================================
        // $schedule->command('inspire')->hourly();
        // $schedule->command('sms:process-scheduled')->everyMinute();
        // $schedule->command('workflows:process')->everyMinute();
        // $schedule->command('workflows:process')->everyFiveMinutes();
        // $schedule->command('booking:dispatch-reminders')->everyMinute();
        // $schedule->command('booking:handle-payment-timeouts')->everyMinute();
        // $schedule->command('sms:test-cron')->everyMinute();

        // ==========================================
        // موتور پویای مرکز مدیریت کرون و زمان‌بندی ماژولار
        // ==========================================
        try {
            \App\Services\Cron\CronService::seedDefaultTasks();
            \App\Services\Cron\CronService::registerDynamicSchedule($schedule);
        } catch (\Throwable $e) {
            // در صورت بروز هرگونه خطای دیتابیس یا مایگریشن، کرنل متوقف نمی‌شود
            \Illuminate\Support\Facades\Log::warning('Cron Kernel schedule registration notice: ' . $e->getMessage());
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
