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
        $this->processBookingPaymentReminders($engine);
        $this->processInvoiceReminders($engine);
        $this->processOrderRenewalReminders($engine);
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

    protected function processInvoiceReminders(WorkflowEngine $engine): void
    {
        $workflows = Workflow::where('is_active', true)
            ->whereHas('triggers', function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_INVOICE_REMINDER);
            })
            ->with(['triggers' => function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_INVOICE_REMINDER);
            }])
            ->get();

        Log::info("[Workflows] Found " . $workflows->count() . " active invoice reminder workflows.");

        foreach ($workflows as $workflow) {
            foreach ($workflow->triggers as $trigger) {
                $this->checkAndTriggerInvoiceReminders($engine, $workflow, $trigger);
            }
        }
    }

    protected function checkAndTriggerInvoiceReminders(WorkflowEngine $engine, Workflow $workflow, WorkflowTrigger $trigger): void
    {
        if (!class_exists('Modules\\Services\\App\\Http\\Models\\Invoice')) {
            return;
        }

        $config = $trigger->config;
        $offsetDays = (int)($config['offset_days'] ?? 0);
        $runAtTime = $config['run_at_time'] ?? '08:00';
        $scheduleTz = config('booking.timezones.display_default', 'Asia/Tehran');

        $nowLocal = now()->timezone($scheduleTz);
        
        try {
            [$hours, $minutes] = explode(':', $runAtTime);
            $scheduledRunTime = $nowLocal->copy()->hour((int)$hours)->minute((int)$minutes)->second(0);
        } catch (\Throwable $e) {
            return; // invalid time format
        }

        // We only want to trigger this once per day, at the specified time.
        // If current time hasn't reached the scheduled time, do not run.
        if ($nowLocal->lessThan($scheduledRunTime)) {
            return;
        }

        $targetDate = $nowLocal->copy()->subDays($offsetDays)->format('Y-m-d');

        if (!class_exists(\Modules\Services\App\Http\Models\Invoice::class) || !\Illuminate\Support\Facades\Schema::hasTable('services_invoices')) {
            return;
        }

        $query = \Modules\Services\App\Http\Models\Invoice::whereDate('due_date', '<=', $targetDate)
            ->whereRaw('paid_amount < total');

        $invoiceStatuses = $config['invoice_statuses'] ?? [];
        $invoiceStatuses = array_filter(array_map('strval', $invoiceStatuses));
        
        $paymentStatuses = $config['payment_statuses'] ?? [];
        $paymentStatuses = array_filter(array_map('strval', $paymentStatuses));

        $invoices = $query->get();

        if (!empty($invoiceStatuses) || !empty($paymentStatuses)) {
            $invoices = $invoices->filter(function ($invoice) use ($invoiceStatuses, $paymentStatuses) {
                $statusName = (string)$invoice->status?->name;
                $invoiceMatch = empty($invoiceStatuses) || in_array($statusName, $invoiceStatuses, true);
                $paymentMatch = empty($paymentStatuses) || in_array($statusName, $paymentStatuses, true);
                return $invoiceMatch && $paymentMatch;
            });
        }

        foreach ($invoices as $invoice) {
            // Ensure this specific workflow hasn't already been triggered for this invoice
            $exists = $workflow->instances()
                ->where('related_type', 'INVOICE')
                ->where('related_id', $invoice->id)
                ->exists();

            if (!$exists) {
                Log::info("[Workflows] Triggering invoice reminder '{$workflow->name}' for Invoice #{$invoice->id} (Target Date: {$targetDate})");
                $engine->startWorkflow($workflow, 'INVOICE', $invoice->id);
            }
        }
    }

    protected function processOrderRenewalReminders(WorkflowEngine $engine): void
    {
        $workflows = Workflow::where('is_active', true)
            ->whereHas('triggers', function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_ORDER_RENEWAL_REMINDER);
            })
            ->with(['triggers' => function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_ORDER_RENEWAL_REMINDER);
            }])
            ->get();

        if ($workflows->count() > 0) {
            Log::info("[Workflows] Found " . $workflows->count() . " active order renewal reminder workflows.");
        }

        foreach ($workflows as $workflow) {
            foreach ($workflow->triggers as $trigger) {
                $this->checkAndTriggerOrderRenewalReminders($engine, $workflow, $trigger);
            }
        }
    }

    protected function checkAndTriggerOrderRenewalReminders(WorkflowEngine $engine, Workflow $workflow, WorkflowTrigger $trigger): void
    {
        if (!class_exists('Modules\\Services\\App\\Http\\Models\\Order')) {
            return;
        }

        $config = $trigger->config;
        $offsetDays = (int)($config['offset_days'] ?? 0);
        $runAtTime = $config['run_at_time'] ?? '08:00';
        $scheduleTz = config('booking.timezones.display_default', 'Asia/Tehran');

        $nowLocal = now()->timezone($scheduleTz);
        
        try {
            [$hours, $minutes] = explode(':', $runAtTime);
            $scheduledRunTime = $nowLocal->copy()->hour((int)$hours)->minute((int)$minutes)->second(0);
        } catch (\Throwable $e) {
            return; // invalid time format
        }

        if ($nowLocal->lessThan($scheduledRunTime)) {
            return;
        }

        $targetDate = $nowLocal->copy()->subDays($offsetDays)->format('Y-m-d');

        if (!class_exists(\Modules\Services\App\Http\Models\Order::class) || !\Illuminate\Support\Facades\Schema::hasTable('services_orders')) {
            return;
        }

        $query = \Modules\Services\App\Http\Models\Order::whereDate('renewal_date', $targetDate);

        $orderStatuses = $config['order_statuses'] ?? [];
        $orderStatuses = array_filter(array_map('strval', $orderStatuses));

        $orders = $query->get();

        if (!empty($orderStatuses)) {
            $orders = $orders->filter(function ($order) use ($orderStatuses) {
                $statusName = (string)$order->status?->name;
                return in_array($statusName, $orderStatuses, true);
            });
        }

        foreach ($orders as $order) {
            $exists = $workflow->instances()
                ->where('related_type', 'ORDER')
                ->where('related_id', $order->id)
                ->whereDate('created_at', $nowLocal->toDateString())
                ->exists();

            if (!$exists) {
                Log::info("[Workflows] Triggering order renewal reminder '{$workflow->name}' for Order #{$order->id} (Target Date: {$targetDate})");
                $engine->startWorkflow($workflow, 'ORDER', $order->id);
            }
        }
    }

    protected function processBookingPaymentReminders(WorkflowEngine $engine): void
    {
        if (!class_exists(\Modules\Booking\Entities\BookingPayment::class) || !\Illuminate\Support\Facades\Schema::hasTable('booking_payments')) {
            return;
        }

        $workflows = Workflow::where('is_active', true)
            ->whereHas('triggers', function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_BOOKING_PAYMENT_REMINDER);
            })
            ->with(['triggers' => function ($query) {
                $query->where('type', WorkflowTrigger::TYPE_BOOKING_PAYMENT_REMINDER);
            }])
            ->get();

        Log::info("[Workflows] Found " . $workflows->count() . " active booking payment reminder workflows.");

        foreach ($workflows as $workflow) {
            foreach ($workflow->triggers as $trigger) {
                $this->checkAndTriggerBookingPaymentReminders($engine, $workflow, $trigger);
            }
        }
    }

    protected function checkAndTriggerBookingPaymentReminders(WorkflowEngine $engine, Workflow $workflow, WorkflowTrigger $trigger): void
    {
        $config = $trigger->config ?? [];
        $offsetMinutes = (int)($config['offset_minutes'] ?? 0);
        $statuses = $config['statuses'] ?? (isset($config['status']) ? [$config['status']] : ['PAID', 'PENDING']);
        $statuses = array_filter(array_map('strval', $statuses));

        $nowMinute = now()->startOfMinute();
        $targetTimeStart = $nowMinute->copy()->subMinutes($offsetMinutes)->subMinutes(1);
        $targetTimeEnd = $nowMinute->copy()->subMinutes($offsetMinutes)->addMinutes(2);

        $query = \Modules\Booking\Entities\BookingPayment::query()
            ->with(['appointment'])
            ->whereHas('appointment')
            ->where(function($q) use ($targetTimeStart, $targetTimeEnd) {
                $q->whereBetween('created_at', [$targetTimeStart, $targetTimeEnd])
                  ->orWhereBetween('updated_at', [$targetTimeStart, $targetTimeEnd]);
            });

        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        $payments = $query->get();

        foreach ($payments as $payment) {
            $appointment = $payment->appointment;
            if (!$appointment) continue;

            // Apply Service Filters
            $serviceIds = $config['service_ids'] ?? (isset($config['service_id']) ? [$config['service_id']] : []);
            $serviceIds = array_filter(array_map('intval', $serviceIds));
            $serviceOperator = $config['service_operator'] ?? 'IN';
            if (!empty($serviceIds)) {
                if ($serviceOperator === 'IN' && !in_array((int)$appointment->service_id, $serviceIds, true)) {
                    continue;
                }
                if ($serviceOperator === 'NOT_IN' && in_array((int)$appointment->service_id, $serviceIds, true)) {
                    continue;
                }
            }

            // Apply Provider Filters
            $providerIds = $config['provider_ids'] ?? (isset($config['provider_id']) ? [$config['provider_id']] : []);
            $providerIds = array_filter(array_map('intval', $providerIds));
            $providerOperator = $config['provider_operator'] ?? 'IN';
            if (!empty($providerIds)) {
                if ($providerOperator === 'IN' && !in_array((int)$appointment->provider_user_id, $providerIds, true)) {
                    continue;
                }
                if ($providerOperator === 'NOT_IN' && in_array((int)$appointment->provider_user_id, $providerIds, true)) {
                    continue;
                }
            }

            // Check if this workflow instance already ran for this appointment
            $exists = $workflow->instances()
                ->where('related_type', 'APPOINTMENT')
                ->where('related_id', $appointment->id)
                ->exists();

            if (!$exists) {
                Log::info("[Workflows] Triggering booking payment reminder '{$workflow->name}' for Appointment #{$appointment->id} / Payment #{$payment->id}");
                $engine->startWorkflow($workflow, 'APPOINTMENT', $appointment->id, [
                    'appointment_payment_id' => $payment->id,
                    'payment_id' => $payment->id,
                    'payment_amount' => $payment->amount,
                    'payment_status' => $payment->status,
                ]);
            }
        }
    }
}
