<?php

namespace Modules\Booking\Services;

use Illuminate\Support\Facades\Log;
use Modules\Booking\Entities\BookingLaboratoryOrder;
use Modules\Booking\Entities\BookingLaboratoryStage;
use Nwidart\Modules\Facades\Module;

class LaboratoryIntegrationService
{
    /**
     * Check if Tasks module is ready
     */
    public function isTaskModuleReady(): bool
    {
        return class_exists('Modules\\Tasks\\Entities\\Task') &&
               Module::has('Tasks') && Module::isEnabled('Tasks');
    }

    /**
     * Check if FollowUps module is ready
     */
    public function isFollowUpModuleReady(): bool
    {
        return class_exists('Modules\\FollowUps\\Entities\\FollowUp') &&
               Module::has('FollowUps') && Module::isEnabled('FollowUps');
    }

    /**
     * Check if Reminders module is ready
     */
    public function isReminderModuleReady(): bool
    {
        return class_exists('Modules\\Reminders\\Entities\\Reminder') &&
               Module::has('Reminders') && Module::isEnabled('Reminders');
    }

    /**
     * Check if Workflows module is ready
     */
    public function isWorkflowModuleReady(): bool
    {
        return class_exists('Modules\\Workflows\\Entities\\Workflow') &&
               Module::has('Workflows') && Module::isEnabled('Workflows');
    }

    /**
     * Triggered when a new laboratory order is created
     */
    public function onOrderCreated(BookingLaboratoryOrder $order): void
    {
        try {
            // 1. Create Reminders for upcoming stages
            if ($this->isReminderModuleReady()) {
                $Reminder = 'Modules\\Reminders\\Entities\\Reminder';
                $userId = $order->doctor_id ?: auth()->id();

                foreach ($order->stages as $stage) {
                    if ($stage->due_at && $stage->due_at->isFuture()) {
                        $Reminder::query()->create([
                            'user_id'      => $userId,
                            'related_type' => 'CLIENT',
                            'related_id'   => $order->client_id,
                            'remind_at'    => $stage->due_at->copy()->setTime(9, 0, 0), // Remind at 9:00 AM on due day
                            'channel'      => 'IN_APP',
                            'message'      => "موعد {$stage->stage_title} سفارش پروتز بیمار {$order->patient_name} ({$order->lab_partner_name})",
                            'status'       => 'OPEN',
                            'is_sent'      => false,
                        ]);
                    }
                }
            }

            // 2. In-house digital lab task creation
            if ($order->lab_type === BookingLaboratoryOrder::LAB_TYPE_IN_HOUSE && $this->isTaskModuleReady()) {
                $firstStage = $order->stages()->orderBy('sort_order')->first();
                if ($firstStage) {
                    $Task = 'Modules\\Tasks\\Entities\\Task';
                    $assignee = $order->technician_id ?: ($order->doctor_id ?: auth()->id());

                    $Task::query()->create([
                        'title'        => "طراحی اسکن لابراتوار مطب: {$order->patient_name}",
                        'description'  => "سفارش دیجیتال شماره {$order->order_number} جهت طراحی و بررسی در مرحله {$firstStage->stage_title}",
                        'task_type'    => $Task::TYPE_SYSTEM,
                        'assignee_id'  => $assignee,
                        'creator_id'   => auth()->id(),
                        'status'       => $Task::STATUS_TODO,
                        'priority'     => $Task::PRIORITY_HIGH,
                        'due_at'       => $firstStage->due_at,
                        'related_type' => 'CLIENT',
                        'related_id'   => $order->client_id,
                        'meta'         => [
                            'source'   => 'booking_laboratory',
                            'order_id' => $order->id,
                            'stage_id' => $firstStage->id,
                        ],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[Booking][LaboratoryIntegration] onOrderCreated warning: ' . $e->getMessage());
        }
    }

    /**
     * Triggered when a stage is completed
     */
    public function onStageCompleted(BookingLaboratoryStage $stage, ?string $note = null): void
    {
        try {
            $order = $stage->order;

            // Mark any linked FollowUp or Task as DONE
            if ($this->isFollowUpModuleReady()) {
                $FollowUp = 'Modules\\FollowUps\\Entities\\FollowUp';
                $FollowUp::query()
                    ->where('related_id', $order->client_id)
                    ->whereJsonContains('meta->stage_id', $stage->id)
                    ->update([
                        'status'       => 'DONE',
                        'completed_at' => now(),
                    ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[Booking][LaboratoryIntegration] onStageCompleted warning: ' . $e->getMessage());
        }
    }

    /**
     * Triggered when the final work is received from laboratory
     */
    public function onOrderReceived(BookingLaboratoryOrder $order): void
    {
        try {
            // Create a high-priority follow-up to schedule delivery appointment with the patient
            if ($this->isFollowUpModuleReady() && $order->client_id) {
                $FollowUp = 'Modules\\FollowUps\\Entities\\FollowUp';
                $FollowUp::query()->create([
                    'title'        => "رزرو نوبت تحویل پروتز: {$order->patient_name}",
                    'description'  => "کار لابراتوار ({$order->lab_partner_name} - {$order->category_label}) در تاریخ " . now()->format('Y/m/d') . " به مطب تحویل شد. جهت تحویل نوبت تنظیم شود.",
                    'task_type'    => 'FOLLOW_UP',
                    'assignee_id'  => $order->doctor_id ?: auth()->id(),
                    'creator_id'   => auth()->id(),
                    'status'       => 'TODO',
                    'priority'     => 'HIGH',
                    'due_at'       => now()->addDay(),
                    'related_type' => 'CLIENT',
                    'related_id'   => $order->client_id,
                    'meta'         => [
                        'source'   => 'booking_laboratory_received',
                        'order_id' => $order->id,
                    ],
                ]);
            }

            // Create reminder for doctor
            if ($this->isReminderModuleReady() && $order->doctor_id) {
                $Reminder = 'Modules\\Reminders\\Entities\\Reminder';
                $Reminder::query()->create([
                    'user_id'      => $order->doctor_id,
                    'related_type' => 'CLIENT',
                    'related_id'   => $order->client_id,
                    'remind_at'    => now(),
                    'channel'      => 'IN_APP',
                    'message'      => "پروتز بیمار {$order->patient_name} از لابراتوار تحویل مطب گردید.",
                    'status'       => 'OPEN',
                    'is_sent'      => false,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[Booking][LaboratoryIntegration] onOrderReceived warning: ' . $e->getMessage());
        }
    }
}
