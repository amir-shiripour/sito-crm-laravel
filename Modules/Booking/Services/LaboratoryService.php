<?php

namespace Modules\Booking\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
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
            $oldVariant = $order->variant_key;
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
                $oldVariant !== $order->variant_key ||
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
     * Generate sequential stages based on category, variant, and settings
     */
    public function generateStagesForOrder(BookingLaboratoryOrder $order, ?array $customStages = null): void
    {
        $stagesConfig = [];

        if (!empty($customStages)) {
            $stagesConfig = $customStages;
        } else {
            // First check dynamic stages defined in settings (including variant support)
            $stagesConfig = BookingSetting::getLaboratoryCategoryStages(
                $order->lab_type,
                $order->category_type,
                $order->variant_key
            );

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

        $this->createStagesFromConfig(
            $order,
            $stagesConfig,
            $order->sent_at ? $order->sent_at->copy() : now(),
            null,
            0,
            0,
            null,
            null
        );
    }

    /**
     * Materialize an array of stage configs into BookingLaboratoryStage records.
     */
    protected function createStagesFromConfig(
        BookingLaboratoryOrder $order,
        array $stagesConfig,
        Carbon $startCursor,
        ?BookingLaboratoryStage $parentStage = null,
        int $layerDepth = 0,
        int $revisionRound = 0,
        ?string $branchKey = null,
        ?string $branchLabel = null,
        ?int $insertAfterSortOrder = null
    ): Collection {
        $currentCursor = $startCursor->copy();
        $created = collect();
        $count = count($stagesConfig);
        if ($count === 0) {
            return $created;
        }

        if ($insertAfterSortOrder !== null) {
            BookingLaboratoryStage::where('order_id', $order->id)
                ->where('sort_order', '>', $insertAfterSortOrder)
                ->increment('sort_order', $count);
            $sortOrder = $insertAfterSortOrder + 1;
        } else {
            $maxSort = (int) ($order->stages()->max('sort_order') ?? 0);
            $sortOrder = $maxSort + 1;
        }

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
            $stageType = $cfg['stage_type'] ?? BookingLaboratoryStage::TYPE_STANDARD;
            if (!in_array($stageType, [
                BookingLaboratoryStage::TYPE_STANDARD,
                BookingLaboratoryStage::TYPE_SELECTION,
                BookingLaboratoryStage::TYPE_DECISION,
            ], true)) {
                $stageType = BookingLaboratoryStage::TYPE_STANDARD;
            }

            $snapshot = $this->buildStageConfigSnapshot($cfg);

            $stage = BookingLaboratoryStage::create([
                'order_id'         => $order->id,
                'parent_stage_id'  => $parentStage?->id,
                'layer_depth'      => $layerDepth,
                'revision_round'   => $revisionRound,
                'branch_key'       => $branchKey,
                'branch_label'     => $branchLabel,
                'stage_key'        => $cfg['key'] ?? $cfg['stage_key'] ?? "stage_{$sortOrder}",
                'stage_title'      => $cfg['title'] ?? $cfg['stage_title'] ?? "مرحله {$sortOrder}",
                'stage_type'       => $stageType,
                'offset_value'     => $offsetVal,
                'offset_unit'      => $offsetUnit,
                'sort_order'       => $sortOrder,
                'due_at'           => $currentCursor,
                'is_completed'     => false,
                'is_receive_stage' => $isReceive,
                'config_snapshot'  => !empty($snapshot) ? $snapshot : null,
                'status'           => $currentCursor->isPast() ? BookingLaboratoryStage::STATUS_OVERDUE : BookingLaboratoryStage::STATUS_PENDING,
            ]);

            $created->push($stage);
            $finalDueAt = $currentCursor;
            $sortOrder++;
        }

        // If inserted in the middle of an existing chain, cascade-recalculate subsequent pending stages' due_at
        if ($insertAfterSortOrder !== null && $finalDueAt) {
            $lastInsertedSort = $insertAfterSortOrder + $count;
            $subsequentPending = BookingLaboratoryStage::where('order_id', $order->id)
                ->where('sort_order', '>', $lastInsertedSort)
                ->where('is_completed', false)
                ->orderBy('sort_order')
                ->get();

            $tailCursor = $finalDueAt->copy();
            foreach ($subsequentPending as $subStage) {
                $tailCursor = ($subStage->offset_unit === 'hours')
                    ? $tailCursor->copy()->addHours((int) $subStage->offset_value)
                    : $tailCursor->copy()->addDays((int) $subStage->offset_value);

                $subStage->update([
                    'due_at' => $tailCursor,
                    'status' => $subStage->isOverdue() ? BookingLaboratoryStage::STATUS_OVERDUE : BookingLaboratoryStage::STATUS_PENDING,
                ]);
            }
        }

        $maxDueAt = $order->stages()->max('due_at');
        if ($maxDueAt) {
            $order->update(['expected_delivery_at' => Carbon::parse($maxDueAt)]);
        } elseif ($finalDueAt) {
            $order->update(['expected_delivery_at' => $finalDueAt]);
        }

        return $created;
    }

    /**
     * Extract clean configuration snapshot for selection/decision/optional stages.
     */
    protected function buildStageConfigSnapshot(array $cfg): array
    {
        $stageType = $cfg['stage_type'] ?? BookingLaboratoryStage::TYPE_STANDARD;
        $isOptional = !empty($cfg['is_optional']);

        if ($stageType === BookingLaboratoryStage::TYPE_STANDARD) {
            return $isOptional ? ['stage_type' => $stageType, 'is_optional' => true] : [];
        }

        $snapshot = [
            'stage_type'         => $stageType,
            'is_optional'        => $isOptional,
            'options_source'     => $cfg['options_source'] ?? 'custom',
            'sync_order_partner' => !empty($cfg['sync_order_partner']),
        ];

        if ($stageType === BookingLaboratoryStage::TYPE_SELECTION) {
            $snapshot['options'] = is_array($cfg['options'] ?? null) ? array_values($cfg['options']) : [];
        } elseif ($stageType === BookingLaboratoryStage::TYPE_DECISION) {
            $snapshot['outcomes'] = is_array($cfg['outcomes'] ?? null) ? array_values($cfg['outcomes']) : [];
        }

        return $snapshot;
    }

    /**
     * Sync active (non-received, non-delivered, non-canceled) orders with updated laboratory stage settings.
     * Preserves all completed stages and daily logs.
     */
    public function syncActiveOrdersWithSettings(): array
    {
        $updatedOrders = 0;
        $addedStages = 0;

        $activeOrders = BookingLaboratoryOrder::with('stages')
            ->whereNotIn('status', [
                BookingLaboratoryOrder::STATUS_RECEIVED,
                BookingLaboratoryOrder::STATUS_DELIVERED,
                BookingLaboratoryOrder::STATUS_CANCELED,
            ])
            ->get();

        foreach ($activeOrders as $order) {
            $stagesConfig = BookingSetting::getLaboratoryCategoryStages(
                $order->lab_type,
                $order->category_type,
                $order->variant_key
            );
            if (empty($stagesConfig)) {
                continue;
            }

            DB::transaction(function () use ($order, $stagesConfig, &$updatedOrders, &$addedStages) {
                $existingStages = $order->stages->whereNull('parent_stage_id')->sortBy('sort_order')->values();
                $matchedIds = [];
                $cursor = $order->sent_at ? $order->sent_at->copy() : now();
                $finalDueAt = null;
                $precedingPendingShifted = false;
                $orderChanged = false;
                $sortOrder = 1;

                // Pass 1: Pre-match by stage_key, then by stage_title, so inserting a new stage
                // in the middle never steals a later stage (especially completed ones) by sort_order.
                $matchedByIndex = [];
                foreach ($stagesConfig as $idx => $cfg) {
                    $sortOrderNum = $idx + 1;
                    $key = $cfg['key'] ?? $cfg['stage_key'] ?? "stage_{$sortOrderNum}";
                    $match = $existingStages->first(function ($st) use ($key, $matchedIds) {
                        return !in_array($st->id, $matchedIds, true) && $st->stage_key === $key;
                    });
                    if ($match) {
                        $matchedByIndex[$idx] = $match;
                        $matchedIds[] = $match->id;
                    }
                }
                foreach ($stagesConfig as $idx => $cfg) {
                    if (isset($matchedByIndex[$idx])) {
                        continue;
                    }
                    $sortOrderNum = $idx + 1;
                    $title = $cfg['title'] ?? $cfg['stage_title'] ?? "مرحله {$sortOrderNum}";
                    $match = $existingStages->first(function ($st) use ($title, $matchedIds) {
                        return !in_array($st->id, $matchedIds, true) && $st->stage_title === $title;
                    });
                    if ($match) {
                        $matchedByIndex[$idx] = $match;
                        $matchedIds[] = $match->id;
                    }
                }
                // Pass 2: Fallback to unreserved pending stage at the same sort_order
                foreach ($stagesConfig as $idx => $cfg) {
                    if (isset($matchedByIndex[$idx])) {
                        continue;
                    }
                    $sortOrderNum = $idx + 1;
                    $match = $existingStages->first(function ($st) use ($sortOrderNum, $matchedIds) {
                        return !in_array($st->id, $matchedIds, true)
                            && !$st->is_completed
                            && (int) $st->sort_order === $sortOrderNum;
                    });
                    if ($match) {
                        $matchedByIndex[$idx] = $match;
                        $matchedIds[] = $match->id;
                    }
                }

                foreach ($stagesConfig as $idx => $cfg) {
                    $key = $cfg['key'] ?? $cfg['stage_key'] ?? "stage_{$sortOrder}";
                    $title = $cfg['title'] ?? $cfg['stage_title'] ?? "مرحله {$sortOrder}";
                    $offsetVal = (int) ($cfg['offset'] ?? $cfg['offset_value'] ?? 1);
                    $offsetUnit = $cfg['unit'] ?? $cfg['offset_unit'] ?? 'days';
                    $isReceive = !empty($cfg['is_receive']) || !empty($cfg['is_receive_stage']);

                    $existing = $matchedByIndex[$idx] ?? null;

                    if ($existing) {

                        if ($existing->is_completed) {
                            // Never alter completed stages; anchor cursor to completion/due time
                            if ((int) $existing->sort_order !== $sortOrder) {
                                $existing->update(['sort_order' => $sortOrder]);
                            }
                            $cursor = $existing->completed_at
                                ? $existing->completed_at->copy()
                                : ($existing->due_at ? $existing->due_at->copy() : $cursor);
                            $finalDueAt = $existing->due_at;
                            $sortOrder++;
                            continue;
                        }

                        $stageType = $cfg['stage_type'] ?? BookingLaboratoryStage::TYPE_STANDARD;
                        $snapshot = $this->buildStageConfigSnapshot($cfg);

                        // Pending stage: check if its own config changed
                        $ownConfigChanged = (
                            $existing->stage_title !== $title ||
                            ($existing->stage_type ?: BookingLaboratoryStage::TYPE_STANDARD) !== $stageType ||
                            (int) $existing->offset_value !== $offsetVal ||
                            $existing->offset_unit !== $offsetUnit ||
                            (bool) $existing->is_receive_stage !== $isReceive ||
                            $existing->config_snapshot != (!empty($snapshot) ? $snapshot : null)
                        );

                        if ($ownConfigChanged || $precedingPendingShifted) {
                            $cursor = $offsetUnit === 'hours'
                                ? $cursor->copy()->addHours($offsetVal)
                                : $cursor->copy()->addDays($offsetVal);

                            $existing->update([
                                'stage_key'        => $key,
                                'stage_title'      => $title,
                                'stage_type'       => $stageType,
                                'offset_value'     => $offsetVal,
                                'offset_unit'      => $offsetUnit,
                                'sort_order'       => $sortOrder,
                                'due_at'           => $cursor,
                                'is_receive_stage' => $isReceive,
                                'config_snapshot'  => !empty($snapshot) ? $snapshot : null,
                                'status'           => $cursor->isPast()
                                    ? BookingLaboratoryStage::STATUS_OVERDUE
                                    : BookingLaboratoryStage::STATUS_PENDING,
                            ]);

                            $precedingPendingShifted = true;
                            $orderChanged = true;
                            $finalDueAt = $cursor;
                        } else {
                            // Config for this pending stage didn't change and no earlier pending stage shifted
                            if ((int) $existing->sort_order !== $sortOrder) {
                                $existing->update(['sort_order' => $sortOrder]);
                                $orderChanged = true;
                            }
                            $cursor = $existing->due_at ? $existing->due_at->copy() : $cursor;
                            $finalDueAt = $existing->due_at;
                        }
                    } else {
                        // Newly added stage in settings
                        $stageType = $cfg['stage_type'] ?? BookingLaboratoryStage::TYPE_STANDARD;
                        $snapshot = $this->buildStageConfigSnapshot($cfg);
                        $cursor = $offsetUnit === 'hours'
                            ? $cursor->copy()->addHours($offsetVal)
                            : $cursor->copy()->addDays($offsetVal);

                        BookingLaboratoryStage::create([
                            'order_id'         => $order->id,
                            'layer_depth'      => 0,
                            'revision_round'   => 0,
                            'stage_key'        => $key,
                            'stage_title'      => $title,
                            'stage_type'       => $stageType,
                            'offset_value'     => $offsetVal,
                            'offset_unit'      => $offsetUnit,
                            'sort_order'       => $sortOrder,
                            'due_at'           => $cursor,
                            'is_completed'     => false,
                            'is_receive_stage' => $isReceive,
                            'config_snapshot'  => !empty($snapshot) ? $snapshot : null,
                            'status'           => $cursor->isPast()
                                ? BookingLaboratoryStage::STATUS_OVERDUE
                                : BookingLaboratoryStage::STATUS_PENDING,
                        ]);

                        $precedingPendingShifted = true;
                        $orderChanged = true;
                        $addedStages++;
                        $finalDueAt = $cursor;
                    }

                    $sortOrder++;
                }

                // Safely remove unmatched pending stages without losing any daily logs
                foreach ($existingStages as $orphan) {
                    if (!in_array($orphan->id, $matchedIds, true) && !$orphan->is_completed) {
                        BookingLaboratoryDailyLog::where('stage_id', $orphan->id)->update(['stage_id' => null]);
                        $orphan->delete();
                        $orderChanged = true;
                    }
                }

                if ($orderChanged) {
                    if ($finalDueAt) {
                        $order->update(['expected_delivery_at' => $finalDueAt]);
                    }

                    BookingLaboratoryDailyLog::create([
                        'order_id'         => $order->id,
                        'stage_id'         => null,
                        'log_date'         => now()->toDateString(),
                        'needs_followup'   => false,
                        'followup_result'  => 'همگام‌سازی خودکار مراحل سفارش با تنظیمات جدید لابراتوار',
                        'operator_user_id' => auth()->id(),
                    ]);

                    $updatedOrders++;
                }
            });
        }

        return [
            'updated_orders' => $updatedOrders,
            'added_stages'   => $addedStages,
        ];
    }

    /**
     * Mark a stage as completed (supports standard, selection, and multi-layer decision/loop stages)
     */
    public function completeStage(
        BookingLaboratoryStage $stage,
        ?string $note = null,
        ?int $userId = null,
        array $selectionData = []
    ): void {
        DB::transaction(function () use ($stage, $note, $userId, $selectionData) {
            $order = $stage->order;
            $action = trim((string) ($selectionData['action'] ?? 'complete'));
            $optionKey = trim((string) ($selectionData['option_key'] ?? ''));
            $subOptionKey = trim((string) ($selectionData['sub_option_key'] ?? ''));
            $resolvedUserId = $userId ?: auth()->id();

            // Optional stage skipped ("عدم نیاز")
            if ($action === 'skip') {
                $stage->markSkipped($note, $resolvedUserId);

                $logMessage = "رد کردن مرحله اختیاری (عدم نیاز): {$stage->stage_title}" . ($note ? " - {$note}" : '');
                BookingLaboratoryDailyLog::create([
                    'order_id'         => $order->id,
                    'stage_id'         => $stage->id,
                    'log_date'         => now()->toDateString(),
                    'needs_followup'   => false,
                    'followup_result'  => $logMessage,
                    'operator_user_id' => $resolvedUserId,
                ]);

                $this->integrationService->onStageCompleted($stage, $note);

                if ($stage->is_receive_stage && !$order->stages()->where('is_completed', false)->exists()) {
                    $order->update([
                        'status'      => BookingLaboratoryOrder::STATUS_RECEIVED,
                        'received_at' => now(),
                    ]);
                    $this->integrationService->onOrderReceived($order);
                }

                return;
            }

            if ($stage->isSelectionStage()) {
                $options = $stage->getAvailableOptions();
                $chosen = collect($options)->firstWhere('key', $optionKey);
                if (!empty($options) && !$chosen) {
                    throw new \InvalidArgumentException('لطفاً یکی از گزینه‌های این مرحله را انتخاب کنید.');
                }

                $chosenLabel = $chosen['label'] ?? $optionKey;
                $stage->update([
                    'selected_option_key'   => $chosen['key'] ?? ($optionKey ?: null),
                    'selected_option_label' => $chosenLabel ?: null,
                    'selected_meta'         => $chosen ?: null,
                ]);
                $stage->markCompleted($note, $resolvedUserId);

                $cfg = is_array($stage->config_snapshot) ? $stage->config_snapshot : [];
                if (!empty($cfg['sync_order_partner']) && $order && $chosenLabel !== '') {
                    $orderUpdates = ['lab_partner_name' => $chosenLabel];
                    $order->update($orderUpdates);
                }

                $logMessage = "تکمیل مرحله «{$stage->stage_title}»" . ($chosenLabel ? " (انتخاب: {$chosenLabel})" : '');
                if ($note) {
                    $logMessage .= " - {$note}";
                }

                BookingLaboratoryDailyLog::create([
                    'order_id'         => $order->id,
                    'stage_id'         => $stage->id,
                    'log_date'         => now()->toDateString(),
                    'needs_followup'   => false,
                    'followup_result'  => $logMessage,
                    'operator_user_id' => $resolvedUserId,
                ]);

                $this->integrationService->onStageCompleted($stage, $note);

                if ($stage->is_receive_stage) {
                    $this->integrationService->onOrderReceived($order);
                }

                return;
            }

            if ($stage->isDecisionStage()) {
                $outcomes = $stage->getAvailableOutcomes();
                $chosen = collect($outcomes)->firstWhere('key', $optionKey);
                if (!empty($outcomes) && !$chosen) {
                    throw new \InvalidArgumentException('لطفاً یکی از تصمیم‌های این مرحله را انتخاب کنید.');
                }

                $chosenLabel = $chosen['label'] ?? $optionKey;
                $effect = $chosen['effect'] ?? 'continue';
                $now = now();

                if ($effect === 'skip') {
                    $stage->update([
                        'selected_option_key'   => $chosen['key'] ?? ($optionKey ?: null),
                        'selected_option_label' => $chosenLabel ?: null,
                        'selected_meta'         => ['effect' => 'skip'],
                    ]);
                    $stage->markSkipped($note, $resolvedUserId);

                    $logMessage = "تصمیم مرحله «{$stage->stage_title}»: {$chosenLabel} (رد شد / عدم نیاز)" . ($note ? " - {$note}" : '');
                    BookingLaboratoryDailyLog::create([
                        'order_id'         => $order->id,
                        'stage_id'         => $stage->id,
                        'log_date'         => $now->toDateString(),
                        'needs_followup'   => false,
                        'followup_result'  => $logMessage,
                        'operator_user_id' => $resolvedUserId,
                    ]);

                    $this->integrationService->onStageCompleted($stage, $note);
                    return;
                }

                $delayMinutes = $stage->calculateDelayMinutes($now);
                $wasLate = $delayMinutes > 0;

                $stage->update([
                    'selected_option_key'   => $chosen['key'] ?? ($optionKey ?: null),
                    'selected_option_label' => $chosenLabel ?: null,
                    'selected_meta'         => ['effect' => $effect, 'target_stage_key' => $chosen['target_stage_key'] ?? null],
                    'is_completed'          => true,
                    'is_skipped'            => false,
                    'was_completed_late'    => $wasLate,
                    'delay_minutes'         => $delayMinutes,
                    'completed_at'          => $now,
                    'completed_by_user_id'  => $resolvedUserId,
                    'status'                => BookingLaboratoryStage::STATUS_COMPLETED,
                    'notes'                 => $note ?: $stage->notes,
                ]);

                $logMessage = "تصمیم مرحله «{$stage->stage_title}»: {$chosenLabel}";
                if ($note) {
                    $logMessage .= " - {$note}";
                }

                BookingLaboratoryDailyLog::create([
                    'order_id'         => $order->id,
                    'stage_id'         => $stage->id,
                    'log_date'         => $now->toDateString(),
                    'needs_followup'   => $effect !== 'finish',
                    'followup_result'  => $logMessage,
                    'operator_user_id' => $resolvedUserId,
                ]);

                $this->integrationService->onStageCompleted($stage, $note);

                if ($effect === 'finish') {
                    $order->update([
                        'status'              => BookingLaboratoryOrder::STATUS_RECEIVED,
                        'received_at'         => $now,
                        'active_branch_label' => $chosenLabel ?: $order->active_branch_label,
                    ]);
                    $this->integrationService->onOrderReceived($order);
                    return;
                }

                if ($effect === 'spawn_stages') {
                    $childConfigs = is_array($chosen['stages'] ?? null) ? $chosen['stages'] : [];
                    if (!empty($childConfigs)) {
                        $round = $stage->revision_round > 0 ? $stage->revision_round : 1;
                        $branchLabel = $chosenLabel;
                        if ($round > 1 && $stage->layer_depth >= 1) {
                            $branchLabel = "{$chosenLabel} (دور {$round})";
                        }

                        $spawned = $this->createStagesFromConfig(
                            $order,
                            $childConfigs,
                            $now,
                            $stage,
                            $stage->layer_depth + 1,
                            $round,
                            $chosen['key'] ?? null,
                            $branchLabel,
                            (int) $stage->sort_order
                        );

                        $order->update([
                            'status'              => BookingLaboratoryOrder::STATUS_IN_PROGRESS,
                            'received_at'         => null,
                            'active_branch_label' => $branchLabel,
                        ]);

                        $this->integrationService->onBranchStagesSpawned($order, $spawned);

                        // Optional fast-forward into immediate nested decision (e.g. اصلاحیه -> دریافت اسکن دوم)
                        if ($subOptionKey !== '' && $spawned->count() === 1 && $spawned->first()->isDecisionStage()) {
                            $this->completeStage($spawned->first(), null, $resolvedUserId, [
                                'option_key' => $subOptionKey,
                            ]);
                        }
                    }
                    return;
                }

                if ($effect === 'loop_to_stage') {
                    $targetKey = trim((string) ($chosen['target_stage_key'] ?? ''));
                    $targetTemplate = $this->resolveLoopTargetTemplate($order, $stage, $targetKey);
                    if ($targetTemplate) {
                        $nextRound = ((int) ($order->stages()->max('revision_round') ?? 0)) + 1;
                        $branchLabel = ($chosenLabel ?: 'اصلاحیه مجدد') . " (دور {$nextRound})";

                        $spawned = $this->createStagesFromConfig(
                            $order,
                            [$targetTemplate],
                            $now,
                            $stage,
                            $stage->layer_depth + 1,
                            $nextRound,
                            $chosen['key'] ?? 'revision_loop',
                            $branchLabel,
                            (int) $stage->sort_order
                        );

                        $order->update([
                            'status'              => BookingLaboratoryOrder::STATUS_IN_PROGRESS,
                            'received_at'         => null,
                            'active_branch_label' => $branchLabel,
                        ]);

                        $this->integrationService->onBranchStagesSpawned($order, $spawned);

                        if ($subOptionKey !== '' && $spawned->count() === 1 && $spawned->first()->isDecisionStage()) {
                            $this->completeStage($spawned->first(), null, $resolvedUserId, [
                                'option_key' => $subOptionKey,
                            ]);
                        }
                    }
                    return;
                }

                return;
            }

            // Standard stage completion
            $stage->markCompleted($note, $resolvedUserId);

            // Log follow-up in daily logs
            BookingLaboratoryDailyLog::create([
                'order_id'         => $order->id,
                'stage_id'         => $stage->id,
                'log_date'         => now()->toDateString(),
                'needs_followup'   => false,
                'followup_result'  => $note ?: "تکمیل مرحله: {$stage->stage_title}",
                'operator_user_id' => $resolvedUserId,
            ]);

            // Notify integration service
            $this->integrationService->onStageCompleted($stage, $note);

            if ($stage->is_receive_stage) {
                $this->integrationService->onOrderReceived($order);
            }
        });
    }

    /**
     * Resolve target stage template for a loop_to_stage decision outcome.
     */
    protected function resolveLoopTargetTemplate(
        BookingLaboratoryOrder $order,
        BookingLaboratoryStage $currentStage,
        string $targetStageKey
    ): ?array {
        if ($targetStageKey === '') {
            return null;
        }

        // 1. Try current settings definition first (supports live updates to branch templates)
        $fromSettings = BookingSetting::findLaboratoryStageTemplateByKey(
            $order->lab_type,
            $order->category_type,
            $order->variant_key,
            $targetStageKey
        );
        if ($fromSettings) {
            return $fromSettings;
        }

        // 2. Fallback: search snapshots on earlier stages in the same order
        $earlierStages = $order->stages()->where('id', '<=', $currentStage->id)->get();
        foreach ($earlierStages as $st) {
            if ($st->stage_key === $targetStageKey) {
                return array_merge($st->config_snapshot ?? [], [
                    'key'        => $st->stage_key,
                    'title'      => $st->stage_title,
                    'stage_type' => $st->stage_type,
                    'offset'     => $st->offset_value,
                    'unit'       => $st->offset_unit,
                    'is_receive' => $st->is_receive_stage,
                ]);
            }
            $snapshot = $st->config_snapshot ?? [];
            if (!empty($snapshot['outcomes'])) {
                $found = $this->findStageInOutcomes($snapshot['outcomes'], $targetStageKey);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    protected function findStageInOutcomes(array $outcomes, string $targetStageKey): ?array
    {
        foreach ($outcomes as $outcome) {
            foreach ($outcome['stages'] ?? [] as $childCfg) {
                $key = $childCfg['key'] ?? $childCfg['stage_key'] ?? '';
                if ($key === $targetStageKey) {
                    return $childCfg;
                }
                if (!empty($childCfg['outcomes'])) {
                    $found = $this->findStageInOutcomes($childCfg['outcomes'], $targetStageKey);
                    if ($found) {
                        return $found;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Revert a completed stage back to pending and cascade-reset/cleanup all subsequent stages and spawned branches
     */
    public function revertStage(BookingLaboratoryStage $stage, ?string $note = null, ?int $userId = null): void
    {
        DB::transaction(function () use ($stage, $note, $userId) {
            $order = $stage->order()->with('stages')->first();
            $allStages = $order
                ? $order->stages->sortBy([['sort_order', 'asc'], ['id', 'asc']])->values()
                : collect([$stage]);

            $subsequentStages = $allStages->filter(function ($s) use ($stage) {
                if ((int) $s->sort_order > (int) $stage->sort_order) {
                    return true;
                }
                return (int) $s->sort_order === (int) $stage->sort_order && (int) $s->id > (int) $stage->id;
            })->values();

            $cascadeOriginIds = array_merge([$stage->id], $subsequentStages->pluck('id')->all());
            $toDeleteIds = [];
            $toRevertStages = [];

            foreach ($subsequentStages as $sub) {
                if ($sub->parent_stage_id && in_array((int) $sub->parent_stage_id, $cascadeOriginIds, true)) {
                    $toDeleteIds[] = $sub->id;
                } else {
                    $toRevertStages[] = $sub;
                }
            }

            foreach ($this->collectDescendantStageIds($stage) as $descId) {
                if (!in_array($descId, $toDeleteIds, true)) {
                    $toDeleteIds[] = $descId;
                }
            }

            if (!empty($toDeleteIds)) {
                BookingLaboratoryDailyLog::whereIn('stage_id', $toDeleteIds)->update(['stage_id' => null]);
                BookingLaboratoryStage::whereIn('id', $toDeleteIds)->delete();
            }

            foreach ($toRevertStages as $revStage) {
                $revStage->update([
                    'selected_option_key'   => null,
                    'selected_option_label' => null,
                    'selected_meta'         => null,
                ]);
                $revStage->markPending();
            }

            $stage->update([
                'selected_option_key'   => null,
                'selected_option_label' => null,
                'selected_meta'         => null,
            ]);
            $stage->markPending($note);

            if ($order) {
                $order->refresh();
                $remainingStages = $order->stages()->get();

                $lastDueAt = $remainingStages->max('due_at');
                $latestBranchStage = $remainingStages
                    ->whereNotNull('branch_label')
                    ->sortByDesc('sort_order')
                    ->first();

                $partnerSyncTouched = !empty(($stage->config_snapshot ?? [])['sync_order_partner'])
                    || collect($toRevertStages)->contains(fn($s) => !empty(($s->config_snapshot ?? [])['sync_order_partner']));

                $orderUpdates = [
                    'expected_delivery_at' => $lastDueAt,
                    'active_branch_label'  => $latestBranchStage?->branch_label,
                ];

                if ($partnerSyncTouched) {
                    $lastCompletedPartnerStage = $remainingStages
                        ->where('is_completed', true)
                        ->filter(fn($s) => !empty(($s->config_snapshot ?? [])['sync_order_partner']) && !empty($s->selected_option_label))
                        ->sortByDesc('sort_order')
                        ->first();

                    if ($lastCompletedPartnerStage) {
                        $orderUpdates['lab_partner_name'] = $lastCompletedPartnerStage->selected_option_label;
                    } else {
                        $typeInfo = collect(BookingSetting::getLaboratoryTypesList())->firstWhere('id', $order->lab_type);
                        $orderUpdates['lab_partner_name'] = $typeInfo['default_partner'] ?? null;
                    }
                }

                $order->update($orderUpdates);
            }

            $reason = trim((string) $note);
            $cascadeInfo = '';
            $revertedLaterCount = collect($toRevertStages)->where('is_completed', true)->count();
            $deletedSpawnedCount = count($toDeleteIds);
            if ($revertedLaterCount > 0 || $deletedSpawnedCount > 0) {
                $cascadeInfo = " (بازگشت {$revertedLaterCount} مرحله بعدی و پاکسازی {$deletedSpawnedCount} زیرمرحله شاخه)";
            }

            $logText = "بازگشت مرحله «{$stage->stage_title}» به حالت در انتظار{$cascadeInfo}" . ($reason !== '' ? " (علت: {$reason})" : '');

            BookingLaboratoryDailyLog::create([
                'order_id'         => $stage->order_id,
                'stage_id'         => $stage->id,
                'log_date'         => now()->toDateString(),
                'needs_followup'   => true,
                'followup_result'  => $logText,
                'operator_user_id' => $userId ?: auth()->id(),
            ]);
        });
    }

    /**
     * Recursively collect all descendant stage IDs spawned from a parent stage.
     */
    protected function collectDescendantStageIds(BookingLaboratoryStage $stage): array
    {
        $ids = [];
        $children = $stage->childStages()->get();
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->collectDescendantStageIds($child));
        }
        return $ids;
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
     * Postpone / extend due date of a stage and adjust order expected delivery
     */
    public function postponeStage(
        BookingLaboratoryStage $stage,
        int|Carbon $daysOrDate,
        ?string $note = null,
        ?int $userId = null
    ): void {
        DB::transaction(function () use ($stage, $daysOrDate, $note, $userId) {
            $oldDue = $stage->due_at ? $stage->due_at->copy() : now();
            
            if ($daysOrDate instanceof Carbon) {
                $newDue = $daysOrDate;
                $diffDays = $oldDue->diffInDays($newDue, false);
            } else {
                $diffDays = (int) $daysOrDate;
                $newDue = $oldDue->copy()->addDays($diffDays);
            }

            $stage->update([
                'due_at' => $newDue,
                'status' => $newDue->isPast() ? BookingLaboratoryStage::STATUS_OVERDUE : BookingLaboratoryStage::STATUS_PENDING,
            ]);

            $order = $stage->order;

            // Shift subsequent incomplete stages by the same diff if diff > 0
            if ($diffDays > 0) {
                $subsequentStages = $order->stages()
                    ->where('id', '!=', $stage->id)
                    ->where('sort_order', '>', $stage->sort_order)
                    ->where('is_completed', false)
                    ->get();

                foreach ($subsequentStages as $sStage) {
                    $sNewDue = $sStage->due_at ? $sStage->due_at->copy()->addDays($diffDays) : now()->addDays($diffDays);
                    $sStage->update([
                        'due_at' => $sNewDue,
                        'status' => $sNewDue->isPast() ? BookingLaboratoryStage::STATUS_OVERDUE : BookingLaboratoryStage::STATUS_PENDING,
                    ]);
                }
            }

            $lastStageDue = $order->stages()->orderByDesc('due_at')->value('due_at');
            if ($lastStageDue) {
                $order->update(['expected_delivery_at' => $lastStageDue]);
            }

            $logNote = $note ?: "تمدید موعد پیگیری مرحله {$stage->stage_title} به مدت {$diffDays} روز";

            BookingLaboratoryDailyLog::create([
                'order_id'         => $order->id,
                'stage_id'         => $stage->id,
                'log_date'         => now()->toDateString(),
                'needs_followup'   => true,
                'followup_result'  => $logNote,
                'operator_user_id' => $userId ?: auth()->id(),
            ]);
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
