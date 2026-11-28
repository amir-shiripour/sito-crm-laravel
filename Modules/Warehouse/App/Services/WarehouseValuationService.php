<?php

namespace Modules\Warehouse\App\Services;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseItemBatch;
use Modules\Warehouse\Entities\WarehouseItemStock;
use Illuminate\Support\Facades\DB;

class WarehouseValuationService
{
    /**
     * Add a new stock batch to an item and update warehouse item stock.
     */
    public function addBatch(
        WarehouseItem $item,
        int $warehouseId,
        float $quantity,
        float $unitPurchasePrice,
        ?string $batchCode = null,
        ?string $purchaseDate = null,
        ?string $expiryDate = null,
        ?string $supplier = null,
        ?string $notes = null,
        ?int $purchaseReceiptId = null
    ): WarehouseItemBatch {
        return DB::transaction(function () use (
            $item,
            $warehouseId,
            $quantity,
            $unitPurchasePrice,
            $batchCode,
            $purchaseDate,
            $expiryDate,
            $supplier,
            $notes,
            $purchaseReceiptId
        ) {
            $batch = WarehouseItemBatch::create([
                'item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'purchase_receipt_id' => $purchaseReceiptId,
                'batch_code' => $batchCode ?: ('BATCH-' . date('Ymd') . '-' . rand(100, 999)),
                'purchase_date' => $purchaseDate ?: now()->toDateString(),
                'unit_purchase_price' => $unitPurchasePrice,
                'initial_quantity' => $quantity,
                'remaining_quantity' => $quantity,
                'expiry_date' => $expiryDate,
                'supplier' => $supplier,
                'notes' => $notes,
            ]);

            // Update or create aggregated stock
            $stock = WarehouseItemStock::firstOrNew([
                'warehouse_id' => $warehouseId,
                'item_id' => $item->id,
            ]);
            $stock->quantity = (float)$stock->quantity + $quantity;
            $stock->save();

            // Update item last purchase price
            if ($unitPurchasePrice > 0) {
                $item->updateQuietly([
                    'last_purchase_price' => $unitPurchasePrice,
                ]);
            }

            return $batch;
        });
    }

    /**
     * Reverse an unconsumed batch when a purchase receipt is modified or cancelled.
     */
    public function removeUnconsumedBatch(WarehouseItemBatch $batch): void
    {
        DB::transaction(function () use ($batch) {
            $lockedBatch = WarehouseItemBatch::where('id', $batch->id)->lockForUpdate()->first();
            if (!$lockedBatch) {
                return;
            }

            if ((float)$lockedBatch->remaining_quantity < (float)$lockedBatch->initial_quantity) {
                throw new \RuntimeException('امکان تغییر یا ابطال این رسید وجود ندارد زیرا بخشی از موجودی پارت آن مصرف یا خارج شده است.');
            }

            $stock = WarehouseItemStock::where('warehouse_id', $lockedBatch->warehouse_id)
                ->where('item_id', $lockedBatch->item_id)
                ->lockForUpdate()
                ->first();

            if ($stock) {
                $stock->quantity = max(0, (float)$stock->quantity - (float)$lockedBatch->initial_quantity);
                $stock->save();
            }

            $itemId = $lockedBatch->item_id;
            $lockedBatch->delete();

            $item = WarehouseItem::find($itemId);
            if ($item) {
                $latestPrice = WarehouseItemBatch::where('item_id', $itemId)
                    ->where('unit_purchase_price', '>', 0)
                    ->orderByDesc('purchase_date')
                    ->orderByDesc('id')
                    ->value('unit_purchase_price');

                $item->updateQuietly([
                    'last_purchase_price' => $latestPrice,
                ]);
            }
        });
    }

    /**
     * Discharge / consume quantity from warehouse using FIFO order (oldest batches first).
     */
    public function dischargeStockFifo(WarehouseItem $item, int $warehouseId, float $qtyToDischarge): array
    {
        return DB::transaction(function () use ($item, $warehouseId, $qtyToDischarge) {
            $batches = WarehouseItemBatch::where('item_id', $item->id)
                ->where('warehouse_id', $warehouseId)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('purchase_date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $discharged = [];
            $remainingToDischarge = $qtyToDischarge;

            foreach ($batches as $batch) {
                if ($remainingToDischarge <= 0) {
                    break;
                }

                $availableInBatch = (float)$batch->remaining_quantity;
                $take = min($availableInBatch, $remainingToDischarge);

                $batch->remaining_quantity = $availableInBatch - $take;
                $batch->save();

                $discharged[] = [
                    'batch_id' => $batch->id,
                    'batch_code' => $batch->batch_code,
                    'unit_purchase_price' => (float)$batch->unit_purchase_price,
                    'quantity_taken' => $take,
                    'cost_basis' => $take * (float)$batch->unit_purchase_price,
                ];

                $remainingToDischarge -= $take;
            }

            // Update aggregated stock
            $stock = WarehouseItemStock::where('warehouse_id', $warehouseId)
                ->where('item_id', $item->id)
                ->first();

            if ($stock) {
                $stock->quantity = max(0, (float)$stock->quantity - ($qtyToDischarge - $remainingToDischarge));
                $stock->save();
            }

            return [
                'discharged_batches' => $discharged,
                'total_discharged' => $qtyToDischarge - $remainingToDischarge,
                'unfulfilled' => $remainingToDischarge,
            ];
        });
    }

    /**
     * Restore previously deducted FIFO batches when an issue/consumption document is cancelled.
     */
    public function reverseIssuedBatches(array $batchDeductions, int $warehouseId, int $itemId): void
    {
        DB::transaction(function () use ($batchDeductions, $warehouseId, $itemId) {
            $totalRestored = 0.0;

            foreach ($batchDeductions as $deduction) {
                $batchId = (int)($deduction['batch_id'] ?? 0);
                $qty = (float)($deduction['quantity_deducted'] ?? 0);
                if ($batchId <= 0 || $qty <= 0) {
                    continue;
                }

                $batch = WarehouseItemBatch::where('id', $batchId)->lockForUpdate()->first();
                if ($batch) {
                    $newRem = min((float)$batch->initial_quantity, (float)$batch->remaining_quantity + $qty);
                    $restoredDelta = max(0.0, $newRem - (float)$batch->remaining_quantity);
                    $batch->remaining_quantity = $newRem;
                    $batch->save();
                    $totalRestored += $restoredDelta;
                }
            }

            if ($totalRestored > 0) {
                $stock = WarehouseItemStock::where('warehouse_id', $warehouseId)
                    ->where('item_id', $itemId)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->quantity = (float)$stock->quantity + $totalRestored;
                    $stock->save();
                } else {
                    WarehouseItemStock::create([
                        'warehouse_id' => $warehouseId,
                        'item_id' => $itemId,
                        'quantity' => $totalRestored,
                    ]);
                }
            }
        });
    }

    /**
     * Transfer quantity between warehouses in FIFO order while preserving unit purchase price & batch origin.
     */
    public function transferStockFifo(
        WarehouseItem $item,
        int $sourceWarehouseId,
        int $destWarehouseId,
        float $qtyToTransfer,
        int $transferRequestId,
        ?string $transferNumber = null,
        ?string $notes = null
    ): array {
        if ($sourceWarehouseId === $destWarehouseId) {
            throw new \InvalidArgumentException('انبار مبدأ و انبار مقصد نمی‌توانند یکسان باشند.');
        }
        if ($qtyToTransfer <= 0) {
            throw new \InvalidArgumentException('تعداد انتقال باید بیشتر از صفر باشد.');
        }

        return DB::transaction(function () use (
            $item,
            $sourceWarehouseId,
            $destWarehouseId,
            $qtyToTransfer,
            $transferRequestId,
            $transferNumber,
            $notes
        ) {
            $sourceBatches = WarehouseItemBatch::where('item_id', $item->id)
                ->where('warehouse_id', $sourceWarehouseId)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('purchase_date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $availableTotal = (float)$sourceBatches->sum('remaining_quantity');
            if ($availableTotal < $qtyToTransfer) {
                $availFormatted = number_format($availableTotal);
                throw new \RuntimeException("موجودی کالای «{$item->name}» در انبار مبدأ کافی نیست (موجودی قابل برداشت: {$availFormatted} {$item->unit}).");
            }

            $remainingToMove = $qtyToTransfer;
            $movedBatches = [];
            $totalCost = 0.0;

            foreach ($sourceBatches as $srcBatch) {
                if ($remainingToMove <= 0) {
                    break;
                }

                $availInBatch = (float)$srcBatch->remaining_quantity;
                $take = min($availInBatch, $remainingToMove);

                $srcBatch->remaining_quantity = $availInBatch - $take;
                $srcBatch->save();

                $destBatch = WarehouseItemBatch::create([
                    'item_id' => $item->id,
                    'warehouse_id' => $destWarehouseId,
                    'purchase_receipt_id' => $srcBatch->purchase_receipt_id,
                    'transfer_request_id' => $transferRequestId,
                    'source_batch_id' => $srcBatch->id,
                    'batch_code' => $srcBatch->batch_code,
                    'purchase_date' => $srcBatch->purchase_date,
                    'unit_purchase_price' => $srcBatch->unit_purchase_price,
                    'initial_quantity' => $take,
                    'remaining_quantity' => $take,
                    'expiry_date' => $srcBatch->expiry_date,
                    'supplier' => $srcBatch->supplier,
                    'notes' => $notes ?: ('انتقال با حواله ' . ($transferNumber ?: "#{$transferRequestId}")),
                ]);

                $costSlice = round($take * (float)$srcBatch->unit_purchase_price, 2);
                $totalCost += $costSlice;

                $movedBatches[] = [
                    'source_batch_id' => $srcBatch->id,
                    'dest_batch_id' => $destBatch->id,
                    'quantity' => $take,
                    'unit_purchase_price' => (float)$srcBatch->unit_purchase_price,
                    'cost_value' => $costSlice,
                ];

                $remainingToMove -= $take;
            }

            // Decrement source warehouse aggregated stock
            $srcStock = WarehouseItemStock::firstOrNew([
                'warehouse_id' => $sourceWarehouseId,
                'item_id' => $item->id,
            ]);
            $srcStock->quantity = max(0, (float)$srcStock->quantity - $qtyToTransfer);
            $srcStock->save();

            // Increment destination warehouse aggregated stock
            $destStock = WarehouseItemStock::firstOrNew([
                'warehouse_id' => $destWarehouseId,
                'item_id' => $item->id,
            ]);
            $destStock->quantity = (float)$destStock->quantity + $qtyToTransfer;
            $destStock->save();

            return [
                'moved_batches' => $movedBatches,
                'total_transferred' => $qtyToTransfer,
                'total_cost_value' => $totalCost,
            ];
        });
    }

    /**
     * Reverse all batches transferred by a transfer request (only if none were consumed in destination).
     */
    public function reverseTransferredBatches(int $transferRequestId): void
    {
        DB::transaction(function () use ($transferRequestId) {
            $transferredBatches = WarehouseItemBatch::where('transfer_request_id', $transferRequestId)
                ->lockForUpdate()
                ->get();

            if ($transferredBatches->isEmpty()) {
                return;
            }

            foreach ($transferredBatches as $destBatch) {
                if ((float)$destBatch->remaining_quantity < (float)$destBatch->initial_quantity) {
                    throw new \RuntimeException('امکان ابطال این حواله وجود ندارد زیرا بخشی از موجودی منتقل‌شده در انبار مقصد مصرف شده است.');
                }
            }

            foreach ($transferredBatches as $destBatch) {
                $qty = (float)$destBatch->initial_quantity;
                $destWarehouseId = (int)$destBatch->warehouse_id;
                $itemId = (int)$destBatch->item_id;
                $sourceBatchId = $destBatch->source_batch_id;

                if ($sourceBatchId) {
                    $srcBatch = WarehouseItemBatch::where('id', $sourceBatchId)->lockForUpdate()->first();
                    if ($srcBatch) {
                        $srcBatch->remaining_quantity = (float)$srcBatch->remaining_quantity + $qty;
                        $srcBatch->save();

                        $srcStock = WarehouseItemStock::firstOrNew([
                            'warehouse_id' => $srcBatch->warehouse_id,
                            'item_id' => $itemId,
                        ]);
                        $srcStock->quantity = (float)$srcStock->quantity + $qty;
                        $srcStock->save();
                    }
                }

                $destStock = WarehouseItemStock::where('warehouse_id', $destWarehouseId)
                    ->where('item_id', $itemId)
                    ->lockForUpdate()
                    ->first();

                if ($destStock) {
                    $destStock->quantity = max(0, (float)$destStock->quantity - $qty);
                    $destStock->save();
                }

                $destBatch->delete();
            }
        });
    }

    /**
     * Get aggregate valuation & alerts metrics for dashboard and reports.
     */
    public function getValuationMetrics(?int $warehouseId = null): array
    {
        $itemsQuery = WarehouseItem::where('is_active', true);

        $batchesQuery = WarehouseItemBatch::where('remaining_quantity', '>', 0);
        if ($warehouseId) {
            $batchesQuery->where('warehouse_id', $warehouseId);
        }

        $batches = $batchesQuery->with('item')->get();

        $totalCostValue = 0.0;
        $totalMarketValue = 0.0;
        $totalUnits = 0.0;

        foreach ($batches as $b) {
            $rem = (float)$b->remaining_quantity;
            $unitBuy = (float)$b->unit_purchase_price;
            $unitMarket = (float)($b->item->current_price ?? $unitBuy);

            $totalUnits += $rem;
            $totalCostValue += ($rem * $unitBuy);
            $totalMarketValue += ($rem * $unitMarket);
        }

        $unrealizedProfit = $totalMarketValue - $totalCostValue;

        // Alerts
        $allItems = $itemsQuery->with('activeBatches')->get();
        $criticalCount = 0;
        $reorderCount = 0;

        foreach ($allItems as $item) {
            if ($item->is_below_min_stock) {
                $criticalCount++;
            } elseif ($item->is_below_reorder_point) {
                $reorderCount++;
            }
        }

        return [
            'total_items_count' => $itemsQuery->count(),
            'total_units_in_stock' => $totalUnits,
            'total_cost_value' => $totalCostValue,
            'total_market_value' => $totalMarketValue,
            'unrealized_profit' => $unrealizedProfit,
            'profit_margin_percent' => $totalCostValue > 0 ? round(($unrealizedProfit / $totalCostValue) * 100, 1) : 0,
            'critical_stock_count' => $criticalCount,
            'reorder_stock_count' => $reorderCount,
        ];
    }

    /**
     * Aggregate live item counts, total units, and cost value per warehouse ID in a single SQL query.
     */
    public function getWarehousesStockSummary(iterable $warehouseIds): array
    {
        $ids = collect($warehouseIds)->filter()->map(fn($id) => (int)$id)->unique()->values()->all();
        if (empty($ids)) {
            return [];
        }

        $rows = WarehouseItemBatch::whereIn('warehouse_id', $ids)
            ->where('remaining_quantity', '>', 0)
            ->selectRaw('warehouse_id, COUNT(DISTINCT item_id) as distinct_items, SUM(remaining_quantity) as total_units, SUM(remaining_quantity * unit_purchase_price) as total_cost_value')
            ->groupBy('warehouse_id')
            ->get();

        $summary = [];
        foreach ($ids as $id) {
            $summary[$id] = [
                'distinct_items' => 0,
                'total_units' => 0.0,
                'total_cost_value' => 0.0,
            ];
        }

        foreach ($rows as $row) {
            $summary[(int)$row->warehouse_id] = [
                'distinct_items' => (int)$row->distinct_items,
                'total_units' => (float)$row->total_units,
                'total_cost_value' => (float)$row->total_cost_value,
            ];
        }

        return $summary;
    }

    /**
     * Get detailed live stock & expiry metrics for a specific warehouse or all warehouses combined.
     */
    public function getWarehouseSnapshotMetrics(?int $warehouseId = null): array
    {
        $base = $this->getValuationMetrics($warehouseId);

        $inStockItemsCount = WarehouseItemBatch::where('remaining_quantity', '>', 0)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->distinct('item_id')
            ->count('item_id');

        $today = now()->toDateString();
        $inNinetyDays = now()->addDays(90)->toDateString();

        $expiredCount = WarehouseItemBatch::where('remaining_quantity', '>', 0)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today)
            ->count();

        $expiringSoonCount = WarehouseItemBatch::where('remaining_quantity', '>', 0)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', $inNinetyDays)
            ->count();

        return array_merge($base, [
            'in_stock_items_count' => $inStockItemsCount,
            'expired_batches_count' => $expiredCount,
            'expiring_soon_batches_count' => $expiringSoonCount,
            'expiry_alerts_count' => $expiredCount + $expiringSoonCount,
        ]);
    }

    /**
     * Build a full dual-level (clinic-wide & central warehouse) reorder alert report with budget estimates.
     */
    public function getReorderAlertsReport(array $filters = []): array
    {
        $search = trim((string)($filters['search'] ?? ''));
        $categoryId = $filters['category_id'] ?? null;
        $subcategoryId = $filters['subcategory_id'] ?? null;
        $scope = (string)($filters['scope'] ?? 'alerts_only');

        $mainWarehouse = Warehouse::inventory()->main()->active()->first();
        $activeInventoryWarehouseIds = Warehouse::inventory()->active()->pluck('id')->map(fn($id) => (int)$id)->all();
        $today = now()->toDateString();
        $inNinetyDays = now()->addDays(90)->toDateString();

        $items = WarehouseItem::with([
            'category',
            'subcategory',
            'creator',
            'activeBatches' => function ($q) use ($activeInventoryWarehouseIds) {
                if (!empty($activeInventoryWarehouseIds)) {
                    $q->whereIn('warehouse_id', $activeInventoryWarehouseIds);
                } else {
                    $q->whereRaw('1 = 0');
                }
                $q->with('warehouse')->orderBy('purchase_date')->orderBy('id');
            },
        ])
            ->active()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('system_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('item_type', 'like', "%{$search}%")
                        ->orWhere('model_spec', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($subcategoryId, fn($q) => $q->where('subcategory_id', $subcategoryId))
            ->orderBy('name')
            ->get();

        $summary = [
            'critical_count' => 0,
            'reorder_count' => 0,
            'central_low_count' => 0,
            'adequate_count' => 0,
            'unconfigured_count' => 0,
            'expiring_count' => 0,
            'alerts_only_count' => 0,
            'all_count' => $items->count(),
            'total_deficit_units' => 0.0,
            'total_estimated_budget' => 0.0,
            'filtered_deficit_units' => 0.0,
            'filtered_estimated_budget' => 0.0,
        ];

        $priorityMap = [
            'critical' => 1,
            'reorder' => 2,
            'central_low' => 3,
            'adequate' => 4,
            'unconfigured' => 5,
        ];

        $rows = [];
        foreach ($items as $item) {
            $batches = $item->activeBatches;
            $clinicStock = (float)$batches->sum('remaining_quantity');
            $centralStock = $mainWarehouse
                ? (float)$batches->where('warehouse_id', $mainWarehouse->id)->sum('remaining_quantity')
                : 0.0;
            $subWarehousesStock = max(0.0, $clinicStock - $centralStock);

            $minStock = (float)($item->min_stock ?? 0);
            $reorderPoint = (float)($item->reorder_point ?? 0);
            $threshold = $reorderPoint > 0 ? $reorderPoint : $minStock;
            $hasThreshold = $threshold > 0;

            $clinicDeficit = $hasThreshold ? max(0.0, $threshold - $clinicStock) : 0.0;
            $centralDeficit = ($mainWarehouse && $hasThreshold) ? max(0.0, $threshold - $centralStock) : 0.0;
            $suggestedOrderQty = $clinicDeficit > 0 ? $clinicDeficit : $centralDeficit;

            $expiredQty = 0.0;
            $expiringSoonQty = 0.0;
            $nearestExpiry = null;
            $totalCostValue = 0.0;

            foreach ($batches as $batch) {
                $rem = (float)$batch->remaining_quantity;
                $totalCostValue += ($rem * (float)$batch->unit_purchase_price);

                if ($batch->expiry_date) {
                    $expDateStr = $batch->expiry_date->toDateString();
                    if ($nearestExpiry === null || $batch->expiry_date->lt($nearestExpiry)) {
                        $nearestExpiry = $batch->expiry_date;
                    }
                    if ($expDateStr < $today) {
                        $expiredQty += $rem;
                    } elseif ($expDateStr <= $inNinetyDays) {
                        $expiringSoonQty += $rem;
                    }
                }
            }

            $hasExpiryAlert = ($expiredQty > 0 || $expiringSoonQty > 0);
            $avgCost = $clinicStock > 0 ? ($totalCostValue / $clinicStock) : 0.0;
            $referenceUnitPrice = (float)($item->last_purchase_price ?? 0) > 0
                ? (float)$item->last_purchase_price
                : ((float)($item->current_price ?? 0) > 0 ? (float)$item->current_price : $avgCost);
            $estimatedOrderCost = round($suggestedOrderQty * $referenceUnitPrice, 0);

            if (!$hasThreshold) {
                $alertStatus = 'unconfigured';
                $summary['unconfigured_count']++;
            } elseif ($clinicStock <= 0 || ($minStock > 0 && $clinicStock <= $minStock)) {
                $alertStatus = 'critical';
                $summary['critical_count']++;
            } elseif ($reorderPoint > 0 && $clinicStock <= $reorderPoint) {
                $alertStatus = 'reorder';
                $summary['reorder_count']++;
            } elseif ($mainWarehouse && $centralStock <= $threshold) {
                $alertStatus = 'central_low';
                $summary['central_low_count']++;
            } else {
                $alertStatus = 'adequate';
                $summary['adequate_count']++;
            }

            if ($hasExpiryAlert) {
                $summary['expiring_count']++;
            }

            $isActionable = in_array($alertStatus, ['critical', 'reorder', 'central_low'], true);
            if ($isActionable) {
                $summary['alerts_only_count']++;
                $summary['total_deficit_units'] += $suggestedOrderQty;
                $summary['total_estimated_budget'] += $estimatedOrderCost;
            }

            $include = match ($scope) {
                'critical' => $alertStatus === 'critical',
                'reorder' => $alertStatus === 'reorder',
                'central_low' => $alertStatus === 'central_low',
                'expiring' => $hasExpiryAlert,
                'adequate' => $alertStatus === 'adequate',
                'unconfigured' => $alertStatus === 'unconfigured',
                'all' => true,
                default => $isActionable, // alerts_only
            };

            if (!$include) {
                continue;
            }

            $summary['filtered_deficit_units'] += $suggestedOrderQty;
            $summary['filtered_estimated_budget'] += $estimatedOrderCost;

            $rows[] = [
                'item' => $item,
                'clinic_stock' => $clinicStock,
                'central_stock' => $centralStock,
                'sub_warehouses_stock' => $subWarehousesStock,
                'min_stock' => $minStock,
                'reorder_point' => $reorderPoint,
                'threshold' => $threshold,
                'has_threshold' => $hasThreshold,
                'clinic_deficit' => $clinicDeficit,
                'central_deficit' => $centralDeficit,
                'suggested_order_qty' => $suggestedOrderQty,
                'reference_unit_price' => $referenceUnitPrice,
                'estimated_order_cost' => $estimatedOrderCost,
                'expired_qty' => $expiredQty,
                'expiring_soon_qty' => $expiringSoonQty,
                'has_expiry_alert' => $hasExpiryAlert,
                'nearest_expiry' => $nearestExpiry,
                'alert_status' => $alertStatus,
                'is_actionable' => $isActionable,
                'priority' => $priorityMap[$alertStatus] ?? 9,
            ];
        }

        usort($rows, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] <=> $b['priority'];
            }
            if ($a['suggested_order_qty'] !== $b['suggested_order_qty']) {
                return $b['suggested_order_qty'] <=> $a['suggested_order_qty'];
            }
            $expA = $a['expired_qty'] + $a['expiring_soon_qty'];
            $expB = $b['expired_qty'] + $b['expiring_soon_qty'];
            if ($expA !== $expB) {
                return $expB <=> $expA;
            }
            return strcmp((string)$a['item']->name, (string)$b['item']->name);
        });

        return [
            'main_warehouse' => $mainWarehouse,
            'summary' => $summary,
            'rows' => collect($rows),
        ];
    }
}
