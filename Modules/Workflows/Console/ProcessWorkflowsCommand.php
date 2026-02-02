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
        // Use 2 minutes window to be safe against slight delays
        $targetTimeStart = $now->copy()->subMinutes($offsetMinutes);
        $targetTimeEnd = $targetTimeStart->copy()->addMinutes(2);

        $appointments = Appointment::query()
            ->where('status', $status)
            ->where('start_at_utc', '>=', $targetTimeStart)
            ->where('start_at_utc', '<', $targetTimeEnd)
            ->get();

        foreach ($appointments as $appointment) {
            $exists = $workflow->instances()
                ->where('related_type', 'APPOINTMENT')
                ->where('related_id', $appointment->id)
                ->exists();

            if (!$exists) {
                Log::info("[Workflows] Triggering reminder workflow '{$workflow->name}' for Appointment #{$appointment->id}");
                $engine->startWorkflow($workflow, 'APPOINTMENT', $appointment->id);
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
