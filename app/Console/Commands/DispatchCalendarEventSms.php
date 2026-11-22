<?php

namespace App\Console\Commands;

use App\Services\CalendarEventSmsService;
use Illuminate\Console\Command;

class DispatchCalendarEventSms extends Command
{
    protected $signature = 'calendar:dispatch-sms';

    protected $description = 'ارسال پیامک‌های زمان‌بندی‌شده رویدادهای یک‌باره و تکرارپذیر تقویم';

    public function handle(CalendarEventSmsService $smsService): int
    {
        $count = $smsService->dispatchDueReminders();
        $this->info("تعداد {$count} پیامک رویداد تقویم پردازش و ارسال شد.");

        return self::SUCCESS;
    }
}
