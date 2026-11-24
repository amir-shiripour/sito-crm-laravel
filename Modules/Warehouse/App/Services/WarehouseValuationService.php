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
        ?string $notes = null
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
            $notes
        ) {
            $batch = WarehouseItemBatch::create([
                'item_id' => $item->id,
                'warehouse_id' => $warehouseId,
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
}
