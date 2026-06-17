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
        $statuses = $config['statuses'] ?? [$status];

        $scheduleTz = config('booking.timezones.display_default', 'Asia/Tehran');
        $runAtTime = $config['run_at_time'] ?? null;

        $now = now();
        $nowLocal = $now->copy()->timezone($scheduleTz);

        if (!empty($runAtTime)) {
            // 1. Time-of-day reminder logic
            $query = Appointment::query()
                ->whereIn('status', $statuses)
                ->where('start_at_utc', '>=', $now->toDateTimeString())
                ->where('start_at_utc', '<=', $now->copy()->addDays(45)->toDateTimeString());

            // Apply Service Filters (Include/Exclude)
            $serviceIds = $config['service_ids'] ?? (isset($config['service_id']) ? [$config['service_id']] : []);
            $serviceIds = array_filter(array_map('intval', $serviceIds));
            $serviceOperator = $config['service_operator'] ?? 'IN';
            if (!empty($serviceIds)) {
                if ($serviceOperator === 'IN') {
                    $query->whereIn('service_id', $serviceIds);
                } else {
                    $query->whereNotIn('service_id', $serviceIds);
                }
            }

            // Apply Provider Filters (Include/Exclude)
            $providerIds = $config['provider_ids'] ?? (isset($config['provider_id']) ? [$config['provider_id']] : []);
            $providerIds = array_filter(array_map('intval', $providerIds));
            $providerOperator = $config['provider_operator'] ?? 'IN';
            if (!empty($providerIds)) {
                if ($providerOperator === 'IN') {
                    $query->whereIn('provider_user_id', $providerIds);
                } else {
                    $query->whereNotIn('provider_user_id', $providerIds);
                }
            }

            $appointments = $query->get();

            foreach ($appointments as $appointment) {
                if (!$appointment->start_at_utc) continue;

                $apptLocal = $appointment->start_at_utc->copy()->timezone($scheduleTz);
                $targetTimeLocal = $apptLocal->copy()->addMinutes($offsetMinutes);

                try {
                    [$hours, $minutes] = explode(':', $runAtTime);
                    $scheduledRunTime = $targetTimeLocal->copy()->hour((int)$hours)->minute((int)$minutes)->second(0);
                } catch (\Throwable $e) {
                    continue;
                }

                if ($nowLocal->greaterThanOrEqualTo($scheduledRunTime)) {
                    $exists = $workflow->instances()
                        ->where('related_type', 'APPOINTMENT')
                        ->where('related_id', $appointment->id)
                        ->exists();

                    if (!$exists) {
                        Log::info("[Workflows] Triggering scheduled time reminder '{$workflow->name}' for Appointment #{$appointment->id} at local time {$nowLocal}");
                        $engine->startWorkflow($workflow, 'APPOINTMENT', $appointment->id);
                    }
                }
            }
        } else {
            // 2. Exact minute fallback logic
            $nowMinute = now()->startOfMinute();
            $targetTimeStart = $nowMinute->copy()->subMinutes($offsetMinutes)->subMinutes(1);
            $targetTimeEnd = $nowMinute->copy()->subMinutes($offsetMinutes)->addMinutes(2);

            $query = Appointment::query()
                ->whereIn('status', $statuses)
                ->where('start_at_utc', '>=', $targetTimeStart->utc())
                ->where('start_at_utc', '<', $targetTimeEnd->utc());

            // Service IDs filter
            $serviceIds = $config['service_ids'] ?? (isset($config['service_id']) ? [$config['service_id']] : []);
            $serviceIds = array_filter(array_map('intval', $serviceIds));
            $serviceOperator = $config['service_operator'] ?? 'IN';
            if (!empty($serviceIds)) {
                if ($serviceOperator === 'IN') {
                    $query->whereIn('service_id', $serviceIds);
                } else {
                    $query->whereNotIn('service_id', $serviceIds);
                }
            }

            // Provider IDs filter
            $providerIds = $config['provider_ids'] ?? (isset($config['provider_id']) ? [$config['provider_id']] : []);
            $providerIds = array_filter(array_map('intval', $providerIds));
            $providerOperator = $config['provider_operator'] ?? 'IN';
            if (!empty($providerIds)) {
                if ($providerOperator === 'IN') {
                    $query->whereIn('provider_user_id', $providerIds);
                } else {
                    $query->whereNotIn('provider_user_id', $providerIds);
                }
            }

            $appointments = $query->get();

            foreach ($appointments as $appointment) {
                $exists = $workflow->instances()
                    ->where('related_type', 'APPOINTMENT')
                    ->where('related_id', $appointment->id)
                    ->exists();

                if (!$exists) {
                    Log::info("[Workflows] Triggering exact minute reminder '{$workflow->name}' for Appointment #{$appointment->id}");
                    $engine->startWorkflow($workflow, 'APPOINTMENT', $appointment->id);
                }
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
