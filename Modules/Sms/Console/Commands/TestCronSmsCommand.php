<?php

namespace Modules\Sms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Sms\Services\SmsManager;

class TestCronSmsCommand extends Command
{
    protected $signature = 'sms:test-cron';
    protected $description = 'Test cron job execution by sending an SMS';

    public function handle(SmsManager $smsManager): void
    {
        $phone = '09119035272';
        $message = 'تست کرون جاب سرور: ' . now()->format('H:i:s');

        Log::info("[TestCronSms] Executing test command. Sending SMS to $phone");

        try {
            $result = $smsManager->sendText($phone, $message);
            Log::info("[TestCronSms] SMS sent successfully. ID: " . ($result->id ?? 'unknown'));
            $this->info("SMS sent to $phone");
        } catch (\Exception $e) {
            Log::error("[TestCronSms] Failed to send SMS: " . $e->getMessage());
            $this->error("Failed to send SMS: " . $e->getMessage());
        }
    }
}
