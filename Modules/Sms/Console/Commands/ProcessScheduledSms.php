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

        // تفکیک پیام‌های پترنی و متنی برای ارسال بهینه
        $patternMessages = $messages->filter(fn($m) => !empty($m->template_key));
        $textMessages    = $messages->filter(fn($m) => empty($m->template_key));

        // ۱) ارسال پیام‌های پترنی (به صورت تکی)
        foreach ($patternMessages as $message) {
            $driverName = $message->driver ?: $smsManager->getDefaultDriver();
            $driver     = $smsManager->driver($driverName);

            try {
                $driver->sendPattern($message, $message->params ?? []);
                $this->info("پیام پترن {$message->id} با موفقیت ارسال شد.");
            } catch (\Throwable $e) {
                $message->markAsFailed($driverName, $e->getMessage(), ['exception' => get_class($e)]);
                $this->error("خطا در ارسال پیام پترن {$message->id}: " . $e->getMessage());
            }
        }

        // ۲) ارسال پیام‌های متنی ساده به تفکیک درایور
        $byDriver = $textMessages->groupBy(fn($m) => $m->driver ?: $smsManager->getDefaultDriver());

        foreach ($byDriver as $driverName => $group) {
            $driver = $smsManager->driver($driverName);

            if ($group->count() === 1) {
                $msg = $group->first();
                try {
                    $driver->sendText($msg);
                    $this->info("پیام متنی {$msg->id} ارسال شد.");
                } catch (\Throwable $e) {
                    $msg->markAsFailed($driverName, $e->getMessage(), ['exception' => get_class($e)]);
                    $this->error("خطا در ارسال پیام {$msg->id}: " . $e->getMessage());
                }
                continue;
            }

            // ارسال گروهی پیام‌های همزمان
            $models = $group->all();
            $phones = $group->pluck('to')->all();
            $bodies = $group->pluck('message')->all();
            $uniqueBodies = array_unique($bodies);

            try {
                if (count($uniqueBodies) === 1) {
                    // متن یکسان برای تمام گیرندگان -> ارسال گروهی
                    $smsManager->sendBulkText($models, $uniqueBodies[0], $phones, $driverName);
                    $this->info("تعداد {$group->count()} پیام متنی هم‌متن به صورت گروهی ارسال شد.");
                } else {
                    // متن متفاوت برای هر گیرنده -> ارسال متناظر
                    $smsManager->sendPeerToPeer($models, $phones, $bodies, $driverName);
                    $this->info("تعداد {$group->count()} پیام متنی متناظر به صورت گروهی ارسال شد.");
                }
            } catch (\Throwable $e) {
                foreach ($models as $m) {
                    $m->markAsFailed($driverName, $e->getMessage(), ['exception' => get_class($e)]);
                }
                $this->error("خطا در ارسال گروهی پیام‌های زمان‌بندی شده: " . $e->getMessage());
            }
        }

        return 0;
    }
}
