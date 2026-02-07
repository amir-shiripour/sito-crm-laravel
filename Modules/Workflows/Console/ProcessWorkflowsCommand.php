<?php

namespace Modules\Workflows\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Entities\Appointment;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Entities\WorkflowTrigger;
use Modules\Workflows\Services\WorkflowEngine;

class ProcessWorkflowsCommand extends Command
{
    protected $signature = 'workflows:process';
    protected $description = 'Process scheduled workflows and appointment reminders';

    public function handle(WorkflowEngine $engine): void
    {
        // Log::info("[Workflows] Starting process command..."); // Uncomment for verbose logging
        $this->processScheduledWorkflows($engine);
        $this->processAppointmentReminders($engine);
    }

    protected function processScheduledWorkflows(WorkflowEngine $engine): void
    {
        $workflows = Workflow::where('is_active', true)
            ->whereHas('triggers', function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_SCHEDULE);
            })
            ->with(['triggers' => function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_SCHEDULE);
            }])
            ->get();

        foreach ($workflows as $workflow) {
            foreach ($workflow->triggers as $trigger) {
                if ($this->shouldRunSchedule($trigger)) {
                    Log::info("[Workflows] Running scheduled workflow: {$workflow->name} (ID: {$workflow->id})");
                    $engine->startWorkflow($workflow, 'WORKFLOW_SCHEDULE', 0);
                }
            }
        }
    }

    protected function processAppointmentReminders(WorkflowEngine $engine): void
    {
        $workflows = Workflow::where('is_active', true)
            ->whereHas('triggers', function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_APPOINTMENT_REMINDER);
            })
            ->with(['triggers' => function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_APPOINTMENT_REMINDER);
            }])
            ->get();

        // Log::info("[Workflows] Found " . $workflows->count() . " active reminder workflows.");

        foreach ($workflows as $workflow) {
            foreach ($workflow->triggers as $trigger) {
                $this->checkAndTriggerReminders($engine, $workflow, $trigger);
            }
        }
    }

    protected function checkAndTriggerReminders(WorkflowEngine $engine, Workflow $workflow, WorkflowTrigger $trigger): void
    {
        $config = $trigger->config;
        $offsetMinutes = (int)($config['offset_minutes'] ?? 0);
        $status = $config['status'] ?? Appointment::STATUS_CONFIRMED;

        // Align to start of minute
        $now = now()->startOfMinute();

        // Calculate target window
        // Target time is: Appointment Start Time + Offset
        // Example: Reminder 1 hour BEFORE (-60 min). Appt at 10:00. Target check time is 09:00.
        // Logic: We want to find appointments where (Start Time + Offset) is NOW.
        // So: Start Time = NOW - Offset.

        // Example: Offset = -60 (1 hour before). Now = 09:00.
        // Target Start Time = 09:00 - (-60) = 10:00.
        // So we look for appointments starting at 10:00.

        $targetTimeStart = $now->copy()->subMinutes($offsetMinutes);
        $targetTimeEnd = $targetTimeStart->copy()->addMinutes(1); // Check 1 minute window

        /*
        Log::debug("[Workflows] Checking reminders for workflow: {$workflow->name}", [
            'offset' => $offsetMinutes,
            'status' => $status,
            'now' => $now->toIso8601String(),
            'target_start_range' => $targetTimeStart->toIso8601String(),
            'target_end_range' => $targetTimeEnd->toIso8601String(),
        ]);
        */

        $appointments = Appointment::query()
            ->where('status', $status)
            ->where('start_at_utc', '>=', $targetTimeStart)
            ->where('start_at_utc', '<', $targetTimeEnd)
            ->get();

        if ($appointments->isNotEmpty()) {
            Log::info("[Workflows] Found " . $appointments->count() . " appointments for reminder workflow: {$workflow->name}");
        }

        foreach ($appointments as $appointment) {
            $exists = $workflow->instances()
                ->where('related_type', 'APPOINTMENT')
                ->where('related_id', $appointment->id)
                ->exists();

            if (!$exists) {
                Log::info("[Workflows] Triggering reminder workflow '{$workflow->name}' for Appointment #{$appointment->id}");
                $engine->startWorkflow($workflow, 'APPOINTMENT', $appointment->id);
            } else {
                // Log::debug("[Workflows] Reminder already sent for Appointment #{$appointment->id}");
            }
        }
    }

    protected function shouldRunSchedule(WorkflowTrigger $trigger): bool
    {
        $config = $trigger->config;
        $cronExpression = $config['cron'] ?? null;

        if (!$cronExpression) {
            return false;
        }

        if (class_exists(\Cron\CronExpression::class)) {
            $cron = new \Cron\CronExpression($cronExpression);
            return $cron->isDue();
        }

        return false;
    }
}
