<?php

namespace Modules\Booking\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Entities\Appointment;

class BookingDispatchReminders extends Command
{
    protected $signature = 'booking:dispatch-reminders {--limit=200 : Max reminders to process}';
    protected $description = 'Dispatch due reminders for appointments (SMS via Sms module, and mark reminders as sent).';

    public function handle(): int
    {
        if (!config('booking.integrations.reminders.enabled', true)) {
            $this->warn('Booking reminders integration is disabled.');
            return self::SUCCESS;
        }

        if (!class_exists('Modules\\Reminders\\Entities\\Reminder')) {
            $this->warn('Reminders module is not available/enabled.');
            return self::SUCCESS;
        }

        $Reminder = \Modules\Reminders\Entities\Reminder::class;

        $limit = (int) $this->option('limit') ?: 200;
        $now = now();

        $reminders = $Reminder::query()
            ->where('related_type', 'APPOINTMENT')
            ->where('status', $Reminder::STATUS_OPEN)
            ->where('is_sent', false)
            ->where('remind_at', '<=', $now)
            ->orderBy('remind_at')
            ->limit($limit)
            ->get();

        if ($reminders->isEmpty()) {
            $this->info('No due reminders.');
            return self::SUCCESS;
        }

        $smsAvailable = class_exists('Modules\\Sms\\Services\\SmsManager');

        $SmsManager = $smsAvailable ? app(\Modules\Sms\Services\SmsManager::class) : null;
        $AppointmentService = app(\Modules\Booking\Services\AppointmentService::class);

        $sent = 0;
        foreach ($reminders as $rem) {
            try {
                $appointment = Appointment::query()->find($rem->related_id);

                // For SMS reminders, we try to send if Sms module exists.
                if ($rem->channel === $Reminder::CHANNEL_SMS) {
                    if (!$smsAvailable) {
                        $this->warn('SMS reminder skipped (Sms module not available). Reminder #' . $rem->id);
                        continue;
                    }

                    // If related appointment exists, try to find phone number from client record.
                    $to = $appointment?->client?->phone;

                    if (!$to) {
                        $this->warn('SMS reminder skipped (no client phone). Reminder #' . $rem->id);
                        continue;
                    }

                    $SmsManager->sendText($to, $rem->message ?? 'یادآوری نوبت', [
                        'type' => \Modules\Sms\Entities\SmsMessage::TYPE_SYSTEM,
                        'related_type' => 'APPOINTMENT',
                        'related_id' => $appointment?->id,
                    ]);
                } elseif ($rem->channel === 'WORKFLOW' && $appointment) {
                    $AppointmentService->triggerWorkflow('appointment_reminder', $appointment);
                }

                // Mark sent for all channels (IN_APP doesn't need external dispatch)
                $rem->is_sent = true;
                $rem->sent_at = now();
                $rem->save();

                $sent++;
            } catch (\Throwable $e) {
                Log::error('[Booking] dispatch reminder failed', [
                    'reminder_id' => $rem->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Processed reminders: {$sent} / {$reminders->count()}");
        return self::SUCCESS;
    }
}
