<?php

namespace Modules\Booking\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Entities\BookingLaboratoryOrder;
use Modules\Booking\Entities\BookingLaboratoryStage;
use Modules\Booking\Entities\BookingSetting;
use Morilog\Jalali\Jalalian;
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
               class_exists('Modules\\Workflows\\Services\\WorkflowEngine') &&
               Module::has('Workflows') && Module::isEnabled('Workflows');
    }

    /**
     * Resolve target user IDs based on integration configuration
     *
     * @param array $config
     * @param int|null $fallbackUserId
     * @return array<int>
     */
    public function resolveTargetUserIds(array $config, ?int $fallbackUserId = null): array
    {
        $assignType = $config['assign_type'] ?? 'role';

        if ($assignType === 'user' && !empty($config['target_user_id'])) {
            return [(int) $config['target_user_id']];
        }

        if ($assignType === 'role' && !empty($config['target_role'])) {
            $roleName = trim($config['target_role']);
            $userIds = User::role($roleName)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            if (!empty($userIds)) {
                return $userIds;
            }
        }

        if ($fallbackUserId) {
            return [$fallbackUserId];
        }

        $authId = auth()->id();
        return $authId ? [$authId] : [];
    }

    /**
     * Triggered when a new laboratory order is created
     */
    public function onOrderCreated(BookingLaboratoryOrder $order): void
    {
        try {
            $cfg = BookingSetting::getLaboratoryIntegrationSettings();

            // 1. Create Reminders for upcoming stages
            if ($this->isReminderModuleReady() && ($cfg['reminders']['enabled'] ?? true)) {
                $Reminder = 'Modules\\Reminders\\Entities\\Reminder';
                $targetUserIds = $this->resolveTargetUserIds($cfg['reminders'], $order->doctor_id);

                foreach ($order->stages as $stage) {
                    if ($stage->due_at && $stage->due_at->isFuture()) {
                        foreach ($targetUserIds as $uId) {
                            $Reminder::query()->create([
                                'user_id'      => $uId,
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
            }

            // 2. In-house digital lab task creation
            if ($order->lab_type === BookingLaboratoryOrder::LAB_TYPE_IN_HOUSE && $this->isTaskModuleReady() && ($cfg['tasks']['enabled'] ?? true)) {
                $firstStage = $order->stages()->orderBy('sort_order')->first();
                if ($firstStage) {
                    $Task = 'Modules\\Tasks\\Entities\\Task';
                    $targetUserIds = $this->resolveTargetUserIds($cfg['tasks'], $order->technician_id ?: ($order->doctor_id ?: auth()->id()));
                    $assignee = $targetUserIds[0] ?? auth()->id();

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

            // 3. Dispatch Workflows Trigger
            if ($this->isWorkflowModuleReady() && $order->client_id) {
                app(\Modules\Workflows\Services\WorkflowEngine::class)->start(
                    'laboratory_order_created',
                    'CLIENT',
                    $order->client_id,
                    [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'patient_name' => $order->patient_name,
                        'lab_partner'  => $order->lab_partner_name,
                        'category'     => $order->category_label,
                        'units_count'  => $order->units_count,
                        'teeth'        => $order->teeth_numbers,
                    ]
                );
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
            if ($this->isFollowUpModuleReady() && $order) {
                $FollowUp = 'Modules\\FollowUps\\Entities\\FollowUp';
                $FollowUp::query()
                    ->where('related_id', $order->client_id)
                    ->whereJsonContains('meta->stage_id', $stage->id)
                    ->update([
                        'status'       => 'DONE',
                        'completed_at' => now(),
                    ]);
            }

            // Dispatch Workflows Trigger
            if ($this->isWorkflowModuleReady() && $order && $order->client_id) {
                app(\Modules\Workflows\Services\WorkflowEngine::class)->start(
                    'laboratory_stage_completed',
                    'CLIENT',
                    $order->client_id,
                    [
                        'order_id'     => $order->id,
                        'stage_id'     => $stage->id,
                        'stage_title'  => $stage->stage_title,
                        'patient_name' => $order->patient_name,
                        'order_number' => $order->order_number,
                        'note'         => $note,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('[Booking][LaboratoryIntegration] onStageCompleted warning: ' . $e->getMessage());
        }
    }

    /**
     * Triggered when a stage is overdue
     */
    public function onStageOverdue(BookingLaboratoryStage $stage): void
    {
        try {
            $order = $stage->order;
            if ($this->isWorkflowModuleReady() && $order && $order->client_id) {
                app(\Modules\Workflows\Services\WorkflowEngine::class)->start(
                    'laboratory_stage_overdue',
                    'CLIENT',
                    $order->client_id,
                    [
                        'order_id'     => $order->id,
                        'stage_id'     => $stage->id,
                        'stage_title'  => $stage->stage_title,
                        'due_at'       => $stage->due_at?->toDateString(),
                        'due_jalali'   => $stage->due_at_jalali,
                        'patient_name' => $order->patient_name,
                        'order_number' => $order->order_number,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('[Booking][LaboratoryIntegration] onStageOverdue warning: ' . $e->getMessage());
        }
    }

    /**
     * Triggered when the final work is received from laboratory
     */
    public function onOrderReceived(BookingLaboratoryOrder $order): void
    {
        try {
            $cfg = BookingSetting::getLaboratoryIntegrationSettings();

            // 1. Create a high-priority follow-up to schedule delivery appointment with the patient
            if ($this->isFollowUpModuleReady() && $order->client_id && ($cfg['followups']['enabled'] ?? true)) {
                $FollowUp = 'Modules\\FollowUps\\Entities\\FollowUp';
                $targetUserIds = $this->resolveTargetUserIds($cfg['followups'], $order->doctor_id ?: auth()->id());
                $assignee = $targetUserIds[0] ?? auth()->id();

                $jalaliDate = Jalalian::now()->format('Y/m/d');

                $FollowUp::query()->create([
                    'title'        => "رزرو نوبت تحویل پروتز: {$order->patient_name}",
                    'description'  => "کار لابراتوار ({$order->lab_partner_name} - {$order->category_label}) در تاریخ {$jalaliDate} به مطب تحویل شد. جهت تحویل نوبت تنظیم شود.",
                    'task_type'    => 'FOLLOW_UP',
                    'assignee_id'  => $assignee,
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

            // 2. Create reminder for target users
            if ($this->isReminderModuleReady() && ($cfg['reminders']['enabled'] ?? true)) {
                $Reminder = 'Modules\\Reminders\\Entities\\Reminder';
                $targetUserIds = $this->resolveTargetUserIds($cfg['reminders'], $order->doctor_id);

                foreach ($targetUserIds as $uId) {
                    $Reminder::query()->create([
                        'user_id'      => $uId,
                        'related_type' => 'CLIENT',
                        'related_id'   => $order->client_id,
                        'remind_at'    => now(),
                        'channel'      => 'IN_APP',
                        'message'      => "پروتز بیمار {$order->patient_name} از لابراتوار تحویل مطب گردید.",
                        'status'       => 'OPEN',
                        'is_sent'      => false,
                    ]);
                }
            }

            // 3. Dispatch Workflows Trigger
            if ($this->isWorkflowModuleReady() && $order->client_id) {
                app(\Modules\Workflows\Services\WorkflowEngine::class)->start(
                    'laboratory_order_received',
                    'CLIENT',
                    $order->client_id,
                    [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'patient_name' => $order->patient_name,
                        'lab_partner'  => $order->lab_partner_name,
                        'category'     => $order->category_label,
                        'teeth'        => $order->teeth_numbers,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('[Booking][LaboratoryIntegration] onOrderReceived warning: ' . $e->getMessage());
        }
    }
}
