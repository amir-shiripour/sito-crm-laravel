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
        Log::info("[Workflows] Starting process command...");
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

        Log::info("[Workflows] Found " . $workflows->count() . " active reminder workflows.");

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
        // Use 2 minutes window to ensure we don't miss anything due to seconds mismatch
        // We check [Target - 1min, Target + 1min]

        $targetTimeStart = $now->copy()->subMinutes($offsetMinutes)->subMinutes(1);
        $targetTimeEnd = $now->copy()->subMinutes($offsetMinutes)->addMinutes(2);


        Log::debug("[Workflows] Checking reminders for workflow: {$workflow->name}", [
            'offset' => $offsetMinutes,
            'status' => $status,
            'now' => $now->toIso8601String(),
            'target_start_range' => $targetTimeStart->toIso8601String(), // UTC conversion happens in query
            'target_end_range' => $targetTimeEnd->toIso8601String(),
        ]);


        // Note: Laravel automatically converts Carbon dates to UTC for database queries if configured correctly.
        // However, to be absolutely sure, we can force UTC conversion here if 'start_at_utc' is stored as UTC.
        // Assuming 'start_at_utc' is a datetime column.

        $appointments = Appointment::query()
            ->where('status', $status)
            ->where('start_at_utc', '>=', $targetTimeStart->utc()) // Force UTC for comparison
            ->where('start_at_utc', '<', $targetTimeEnd->utc())   // Force UTC for comparison
            ->get();

        if ($appointments->isNotEmpty()) {
            Log::info("[Workflows] Found " . $appointments->count() . " appointments for reminder workflow: {$workflow->name}");
        } else {
             // Log::debug("[Workflows] No appointments found in range.");
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
                Log::debug("[Workflows] Reminder already sent for Appointment #{$appointment->id}");
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
