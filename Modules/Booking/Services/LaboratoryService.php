<?php

namespace Modules\Booking\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Entities\BookingLaboratoryOrder;
use Modules\Booking\Entities\BookingLaboratoryStage;
use Modules\Booking\Entities\BookingLaboratoryDailyLog;
use Modules\Booking\Entities\BookingSetting;
use Morilog\Jalali\Jalalian;

class LaboratoryService
{
    public function __construct(
        protected LaboratoryIntegrationService $integrationService
    ) {}

    /**
     * Generate sequential unique tracking code (e.g. LAB-1403-0001)
     */
    public function generateOrderNumber(): string
    {
        $year = Jalalian::now()->getYear();
        $prefix = "LAB-{$year}-";

        $lastOrder = BookingLaboratoryOrder::withTrashed()
            ->where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $nextSeq = 1;
        if ($lastOrder && preg_match('/-(\d+)$/', $lastOrder->order_number, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new laboratory order with auto-calculated stages
     */
    public function createOrder(array $data, ?array $customStages = null): BookingLaboratoryOrder
    {
        return DB::transaction(function () use ($data, $customStages) {
            if (empty($data['order_number'])) {
                $data['order_number'] = $this->generateOrderNumber();
            }

            if (empty($data['created_by_user_id'])) {
                $data['created_by_user_id'] = auth()->id();
            }

            // Ensure sent_at is Carbon instance
            $sentAt = isset($data['sent_at']) ? Carbon::parse($data['sent_at']) : Carbon::now();
            $data['sent_at'] = $sentAt;

            $order = BookingLaboratoryOrder::create($data);

            $this->generateStagesForOrder($order, $customStages);

            // Trigger cross-module integration
            $this->integrationService->onOrderCreated($order);

            return $order->fresh(['stages', 'client', 'doctor']);
        });
    }

    /**
     * Update an existing laboratory order and adjust stages if necessary
     */
    public function updateOrder(BookingLaboratoryOrder $order, array $data, bool $recalculateStages = false): BookingLaboratoryOrder
    {
        return DB::transaction(function () use ($order, $data, $recalculateStages) {
            $oldSentAt = $order->sent_at ? $order->sent_at->copy() : null;
            $oldCategory = $order->category_type;
            $oldPhase = $order->full_jaw_phase;
            $oldPmma = (bool) $order->has_pmma;
            $oldLabType = $order->lab_type;

            if (isset($data['sent_at'])) {
                $data['sent_at'] = Carbon::parse($data['sent_at']);
            }

            $order->update($data);

            // Determine if timeline / stages structure needs adjustment
            $structureChanged = $recalculateStages ||
                $oldCategory !== $order->category_type ||
                $oldPhase !== $order->full_jaw_phase ||
                $oldPmma !== (bool) $order->has_pmma ||
                $oldLabType !== $order->lab_type;

            $dateChanged = $oldSentAt && $order->sent_at && !$oldSentAt->isSameDay($order->sent_at);

            if ($order->status !== BookingLaboratoryOrder::STATUS_RECEIVED) {
                $hasCompletedStages = $order->stages()->where('is_completed', true)->exists();

                if ($structureChanged && !$hasCompletedStages) {
                    // Clean rebuild of stages
                    $order->stages()->delete();
                    $this->generateStagesForOrder($order);
                } elseif ($dateChanged) {
                    // Shift existing non-completed stages due_at
                    $diffDays = $oldSentAt->diffInDays($order->sent_at, false);
                    if ($diffDays !== 0) {
                        foreach ($order->stages()->where('is_completed', false)->get() as $stage) {
                            $newDueAt = $stage->due_at->copy()->addDays($diffDays);
                            $stage->update([
                                'due_at' => $newDueAt,
                                'status' => $newDueAt->isPast() ? BookingLaboratoryStage::STATUS_OVERDUE : BookingLaboratoryStage::STATUS_PENDING,
                            ]);
                        }

                        $lastStageDue = $order->stages()->orderByDesc('due_at')->value('due_at');
                        if ($lastStageDue) {
                            $order->update(['expected_delivery_at' => $lastStageDue]);
                        }
                    }
                }
            }

            return $order->fresh(['stages', 'client', 'doctor']);
        });
    }

    /**
     * Generate sequential stages based on category and settings
     */
    public function generateStagesForOrder(BookingLaboratoryOrder $order, ?array $customStages = null): void
    {
        $stagesConfig = [];

        if (!empty($customStages)) {
            $stagesConfig = $customStages;
        } else {
            // First check dynamic stages defined in settings
            $stagesConfig = BookingSetting::getLaboratoryCategoryStages($order->lab_type, $order->category_type);

            // Backward compatibility fallbacks if dynamic lookup returned empty
            if (empty($stagesConfig)) {
                $settings = BookingSetting::current();
                $labSettings = $settings->laboratory_settings;

                if ($order->lab_type === BookingLaboratoryOrder::LAB_TYPE_IN_HOUSE) {
                    $stagesConfig = $labSettings['in_house_digital']['stages'] ?? [];
                } else {
                    $cat = $order->category_type;
                    if ($cat === BookingLaboratoryOrder::CATEGORY_FULL_JAW) {
                        $phase = $order->full_jaw_phase ?: BookingLaboratoryOrder::FULL_JAW_BASE_RIM;
                        $stagesConfig = $labSettings['external_labs']['full_jaw']['phases'][$phase]['stages'] ?? [];
                    } elseif ($cat === BookingLaboratoryOrder::CATEGORY_UNITS_7_11 && $order->has_pmma) {
                        $stagesConfig = $labSettings['external_labs']['units_7_11_pmma']['stages'] ?? [];
                    } elseif (isset($labSettings['external_labs'][$cat]['stages'])) {
                        $stagesConfig = $labSettings['external_labs'][$cat]['stages'];
                    } else {
                        $stagesConfig = [
                            ['key' => 'followup_1', 'title' => 'پیگیری اول', 'offset' => 7, 'unit' => 'days', 'is_receive' => false],
                            ['key' => 'receive_work', 'title' => 'دریافت کار', 'offset' => 5, 'unit' => 'days', 'is_receive' => true],
                        ];
                    }
                }
            }
        }

        // Calculate chronological dates step by step
        $currentCursor = $order->sent_at->copy();
        $sortOrder = 1;
        $finalDueAt = null;

        foreach ($stagesConfig as $cfg) {
            $offsetVal = (int) ($cfg['offset'] ?? $cfg['offset_value'] ?? 1);
            $offsetUnit = $cfg['unit'] ?? $cfg['offset_unit'] ?? 'days';

            if ($offsetUnit === 'hours') {
                $currentCursor = $currentCursor->copy()->addHours($offsetVal);
            } else {
                $currentCursor = $currentCursor->copy()->addDays($offsetVal);
            }

            $isReceive = !empty($cfg['is_receive']) || !empty($cfg['is_receive_stage']);

            BookingLaboratoryStage::create([
                'order_id'         => $order->id,
                'stage_key'        => $cfg['key'] ?? $cfg['stage_key'] ?? "stage_{$sortOrder}",
                'stage_title'      => $cfg['title'] ?? $cfg['stage_title'] ?? "مرحله {$sortOrder}",
                'offset_value'     => $offsetVal,
                'offset_unit'      => $offsetUnit,
                'sort_order'       => $sortOrder,
                'due_at'           => $currentCursor,
                'is_completed'     => false,
                'is_receive_stage' => $isReceive,
                'status'           => $currentCursor->isPast() ? BookingLaboratoryStage::STATUS_OVERDUE : BookingLaboratoryStage::STATUS_PENDING,
            ]);

            $finalDueAt = $currentCursor;
            $sortOrder++;
        }

        if ($finalDueAt) {
            $order->update(['expected_delivery_at' => $finalDueAt]);
        }
    }

    /**
     * Mark a stage as completed
     */
    public function completeStage(BookingLaboratoryStage $stage, ?string $note = null, ?int $userId = null): void
    {
        DB::transaction(function () use ($stage, $note, $userId) {
            $stage->markCompleted($note, $userId);

            $order = $stage->order;

            // Log follow-up in daily logs
            BookingLaboratoryDailyLog::create([
                'order_id'         => $order->id,
                'stage_id'         => $stage->id,
                'log_date'         => now()->toDateString(),
                'needs_followup'   => false,
                'followup_result'  => $note ?: "تکمیل مرحله: {$stage->stage_title}",
                'operator_user_id' => $userId ?: auth()->id(),
            ]);

            // Notify integration service
            $this->integrationService->onStageCompleted($stage, $note);

            if ($stage->is_receive_stage) {
                $this->integrationService->onOrderReceived($order);
            }
        });
    }

    /**
     * Mark entire order as received (turns green!)
     */
    public function markOrderReceived(BookingLaboratoryOrder $order, ?string $note = null, ?int $userId = null): void
    {
        DB::transaction(function () use ($order, $note, $userId) {
            // Mark all receive stages as completed
            foreach ($order->stages as $stage) {
                if ($stage->is_receive_stage && !$stage->is_completed) {
                    $stage->markCompleted($note, $userId);
                }
            }

            $order->update([
                'status'      => BookingLaboratoryOrder::STATUS_RECEIVED,
                'received_at' => now(),
            ]);

            BookingLaboratoryDailyLog::create([
                'order_id'         => $order->id,
                'stage_id'         => $order->stages()->where('is_receive_stage', true)->value('id'),
                'log_date'         => now()->toDateString(),
                'needs_followup'   => false,
                'followup_result'  => $note ?: 'دریافت کار از لابراتوار و ثبت در مطب',
                'operator_user_id' => $userId ?: auth()->id(),
            ]);

            $this->integrationService->onOrderReceived($order);
        });
    }

    /**
     * Log a follow-up action for the daily board
     */
    public function logDailyFollowup(
        BookingLaboratoryOrder $order,
        ?BookingLaboratoryStage $stage,
        bool $needsFollowup,
        ?string $result,
        ?int $operatorId = null
    ): BookingLaboratoryDailyLog {
        return BookingLaboratoryDailyLog::create([
            'order_id'         => $order->id,
            'stage_id'         => $stage?->id,
            'log_date'         => now()->toDateString(),
            'needs_followup'   => $needsFollowup,
            'followup_result'  => $result,
            'operator_user_id' => $operatorId ?: auth()->id(),
        ]);
    }
}
