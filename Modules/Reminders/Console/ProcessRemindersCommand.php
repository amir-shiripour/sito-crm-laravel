<?php

namespace Modules\Reminders\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Reminders\Entities\Reminder;

class ProcessRemindersCommand extends Command
{
    protected $signature = 'reminders:process';
    protected $description = 'Process pending reminders (Workflow triggers, etc.)';

    public function handle(): void
    {
        $this->info('Processing pending reminders...');

        // 1. Process WORKFLOW reminders
        $workflowReminders = Reminder::query()
            ->where('channel', 'WORKFLOW')
            ->where('status', Reminder::STATUS_OPEN)
            ->where('is_sent', false)
            ->where('remind_at', '<=', now())
            ->limit(50) // Process in batches
            ->get();

        foreach ($workflowReminders as $reminder) {
            $this->processWorkflowReminder($reminder);
        }

        $this->info('Done.');
    }

    protected function processWorkflowReminder(Reminder $reminder): void
    {
        $workflowKey = $reminder->message; // We stored the workflow key in the message field
        $relatedType = $reminder->related_type;
        $relatedId   = $reminder->related_id;

        if (!$workflowKey || !$relatedType || !$relatedId) {
            $reminder->status = Reminder::STATUS_CANCELED;
            $reminder->save();
            return;
        }

        $this->info("Triggering workflow '{$workflowKey}' for {$relatedType}:{$relatedId}");

        try {
            // Trigger Workflow
            if ($relatedType === 'APPOINTMENT' && class_exists('Modules\\Booking\\Services\\AppointmentService')) {
                // Use AppointmentService to trigger, as it might have specific logic
                // But AppointmentService::triggerWorkflow takes a key and an Appointment object.
                // So we need to fetch the appointment first.

                $appt = \Modules\Booking\Entities\Appointment::find($relatedId);
                if ($appt) {
                    app(\Modules\Booking\Services\AppointmentService::class)->triggerWorkflow($workflowKey, $appt);
                } else {
                    Log::warning("[Reminders] Appointment not found for reminder {$reminder->id}");
                }
            } else {
                // Generic workflow trigger (if we support other types in future)
                if (class_exists('Modules\\Workflows\\Services\\WorkflowEngine')) {
                    app(\Modules\Workflows\Services\WorkflowEngine::class)->start($workflowKey, $relatedType, $relatedId);
                }
            }

            // Mark as sent
            $reminder->is_sent = true;
            $reminder->sent_at = now();
            $reminder->status  = Reminder::STATUS_DONE;
            $reminder->save();

        } catch (\Throwable $e) {
            Log::error("[Reminders] Failed to process workflow reminder {$reminder->id}: " . $e->getMessage());
        }
    }
}
