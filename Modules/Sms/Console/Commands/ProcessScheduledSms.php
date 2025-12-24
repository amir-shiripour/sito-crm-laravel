<?php

namespace Modules\Sms\Console\Commands;

use Illuminate\Console\Command;
use Modules\Sms\Entities\SmsMessage;
use Modules\Sms\Services\SmsManager;
use Illuminate\Support\Facades\Log;

class ProcessScheduledSms extends Command
{
    protected $signature = 'sms:process-scheduled {--limit=100}';

    protected $description = 'ارسال پیامک‌های زمان‌بندی شده‌ای که زمانشان رسیده است.';

    public function handle(SmsManager $smsManager): int
    {
        $now = now();

        $messages = SmsMessage::query()
            ->where('type', SmsMessage::TYPE_SCHEDULED)
            ->where('status', SmsMessage::STATUS_PENDING)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->orderBy('scheduled_at')
            ->limit((int) $this->option('limit'))
            ->get();

        Log::debug('[sms:process-scheduled] found messages', [
            'count' => $messages->count(),
            'ids'   => $messages->pluck('id')->all(),
            'now'   => $now->toDateTimeString(),
        ]);

        if ($messages->isEmpty()) {
            $this->info('هیچ پیام زمان‌بندی شده‌ای برای ارسال وجود ندارد.');
            return 0;
        }

        foreach ($messages as $message) {
            $driverName = $message->driver ?: $smsManager->getDefaultDriver();
            $driver     = $smsManager->driver($driverName);

            try {
                if ($message->template_key) {
                    // پیامک پترنی
                    $driver->sendPattern($message, $message->params ?? []);
                } else {
                    // پیامک متنی ساده
                    $driver->sendText($message);
                }

                $this->info("پیام {$message->id} ارسال شد.");
            } catch (\Throwable $e) {
                $message->markAsFailed(
                    $driverName,
                    $e->getMessage(),
                    ['exception' => get_class($e)]
                );

                $this->error("ارسال پیام {$message->id} با خطا مواجه شد: " . $e->getMessage());
            }
        }

        return 0;
    }
}
