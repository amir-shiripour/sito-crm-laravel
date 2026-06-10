<?php

namespace Modules\Market\App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\Warehouse;
use Modules\Market\Entities\WarehouseStock;
use Modules\Market\Entities\WarehouseTransaction;

class WarehouseStockService
{
    /**
     * Checks if the Warehouse Management System (WMS) is active.
     *
     * @return bool
     */
    public function isWmsActive(): bool
    {
        return (bool) MarketSetting::getValue('wms.enabled', false);
    }

    /**
     * Get the stock deduction strategy.
     *
     * @return string
     */
    public function getStockDeductionStrategy(): string
    {
        return MarketSetting::getValue('wms.stock_deduction_strategy', 'combined');
    }

    /**
     * Get the store architecture type.
     *
     * @return string
     */
    public function getStoreType(): string
    {
        return MarketSetting::getValue('system.store_type', 'multi');
    }

    /**
     * Get the available stock for a product variant based on current settings.
     *
     * @param int $variantId
     * @param int|null $vendorId
     * @return int
     */
    public function getAvailableStock(int $variantId, int $vendorId = null): int
    {
        if (!$this->isWmsActive()) {
            // WMS is not active, use legacy stock field.
            if ($vendorId) {
                $product = VendorProduct::where('vendor_id', $vendorId)->where('product_variant_id', $variantId)->first();
                return $product ? $product->stock : 0;
            }
            $variant = ProductVariant::find($variantId);
            return $variant ? $variant->stock : 0;
        }

        // WMS is active
        $query = WarehouseStock::query()
            ->where('product_variant_id', $variantId)
            ->join('market_warehouses', 'market_warehouse_stocks.warehouse_id', '=', 'market_warehouses.id')
            ->where('market_warehouses.is_active', true);

        if ($vendorId) {
            // Check vendor's own warehouses
            $query->where('market_warehouses.vendor_id', $vendorId);
        } else {
            // Check central warehouse
            $query->whereNull('market_warehouses.vendor_id');
        }

        $stockField = 'physical_stock';
        if ($this->getStockDeductionStrategy() === 'separated') {
            $stockField = 'online_stock';
        }

        return $query->sum(DB::raw("{$stockField} - reserved_stock"));
    }

    // ... (بقیه متدها بدون تغییر باقی می‌مانند)
    public function incrementStock(int $warehouseId, int $variantId, int $quantity, int $vendorProductId = null, $reference = null, int $userId = null, string $description = 'Stock increment'): WarehouseStock
    {
        return DB::transaction(function () use ($warehouseId, $variantId, $quantity, $vendorProductId, $reference, $userId, $description) {
            $stock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'product_variant_id' => $variantId,
                    'vendor_product_id' => $vendorProductId,
                ],
                ['physical_stock' => 0, 'online_stock' => 0, 'reserved_stock' => 0]
            );

            $stock->increment('physical_stock', $quantity);

            WarehouseTransaction::create([
                'warehouse_id' => $warehouseId,
                'product_variant_id' => $variantId,
                'vendor_product_id' => $vendorProductId,
                'type' => 'in',
                'quantity' => $quantity,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->id : null,
                'description' => $description,
                'user_id' => $userId,
            ]);

            return $stock;
        });
    }

    public function decrementStock(int $warehouseId, int $variantId, int $quantity, int $vendorProductId = null, $reference = null, int $userId = null, string $description = 'Stock decrement'): WarehouseStock
    {
        return DB::transaction(function () use ($warehouseId, $variantId, $quantity, $vendorProductId, $reference, $userId, $description) {
            $stock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'product_variant_id' => $variantId,
                    'vendor_product_id' => $vendorProductId,
                ]
            );

            if ($stock->physical_stock < $quantity) {
                throw new \Exception('Not enough physical stock to decrement.');
            }

            $stock->decrement('physical_stock', $quantity);

            WarehouseTransaction::create([
                'warehouse_id' => $warehouseId,
                'product_variant_id' => $variantId,
                'vendor_product_id' => $vendorProductId,
                'type' => 'out',
                'quantity' => $quantity,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->id : null,
                'description' => $description,
                'user_id' => $userId,
            ]);

            return $stock;
        });
    }

    public function reserveStock(int $warehouseId, int $variantId, int $quantity, int $vendorProductId = null, $reference = null): WarehouseStock
    {
        return DB::transaction(function () use ($warehouseId, $variantId, $quantity, $vendorProductId, $reference) {
            $stock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'product_variant_id' => $variantId,
                    'vendor_product_id' => $vendorProductId,
                ]
            );

            $stockField = $this->getStockDeductionStrategy() === 'separated' ? 'online_stock' : 'physical_stock';

            if (($stock->{$stockField} - $stock->reserved_stock) < $quantity) {
                throw new \Exception('Not enough available stock to reserve.');
            }

            $stock->increment('reserved_stock', $quantity);

            WarehouseTransaction::create([
                'warehouse_id' => $warehouseId,
                'product_variant_id' => $variantId,
                'vendor_product_id' => $vendorProductId,
                'type' => 'reserve_add',
                'quantity' => $quantity,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->id : null,
                'description' => 'Stock reserved',
            ]);

            return $stock;
        });
    }

    public function releaseReservedStock(int $warehouseId, int $variantId, int $quantity, int $vendorProductId = null, $reference = null): WarehouseStock
    {
        return DB::transaction(function () use ($warehouseId, $variantId, $quantity, $vendorProductId, $reference) {
            $stock = WarehouseStock::where('warehouse_id', $warehouseId)
                ->where('product_variant_id', $variantId)
                ->when($vendorProductId, function ($q) use ($vendorProductId) {
                    $q->where('vendor_product_id', $vendorProductId);
                }, function ($q) {
                    $q->whereNull('vendor_product_id');
                })->first();

            if (!$stock || $stock->reserved_stock < $quantity) {
                throw new \Exception('Not enough reserved stock to release.');
            }

            $stock->decrement('reserved_stock', $quantity);

            WarehouseTransaction::create([
                'warehouse_id' => $warehouseId,
                'product_variant_id' => $variantId,
                'vendor_product_id' => $vendorProductId,
                'type' => 'reserve_release',
                'quantity' => $quantity,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->id : null,
                'description' => 'Reserved stock released',
            ]);

            return $stock;
        });
    }

    public function finalizeShipment(int $warehouseId, int $variantId, int $quantity, int $vendorProductId = null, $reference = null, int $userId = null): WarehouseStock
    {
        return DB::transaction(function () use ($warehouseId, $variantId, $quantity, $vendorProductId, $reference, $userId) {
            $stock = WarehouseStock::where('warehouse_id', $warehouseId)
                ->where('product_variant_id', $variantId)
                ->when($vendorProductId, function ($q) use ($vendorProductId) {
                    $q->where('vendor_product_id', $vendorProductId);
                }, function ($q) {
                    $q->whereNull('vendor_product_id');
                })->lockForUpdate()->first();

            if (!$stock || $stock->reserved_stock < $quantity || $stock->physical_stock < $quantity) {
                throw new \Exception('Not enough stock to finalize shipment.');
            }

            $stock->decrement('reserved_stock', $quantity);
            $stock->decrement('physical_stock', $quantity);

            WarehouseTransaction::create([
                'warehouse_id' => $warehouseId,
                'product_variant_id' => $variantId,
                'vendor_product_id' => $vendorProductId,
                'type' => 'out',
                'quantity' => $quantity,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->id : null,
                'description' => 'Shipped from reserved stock',
                'user_id' => $userId,
            ]);

            return $stock;
        });
    }

    /**
     * Transfer stock between warehouses (transactional and audited).
     *
     * @param \Modules\Market\Entities\WarehouseTransfer $transfer
     * @param int $userId
     * @return void
     */
    public function transferStock(\Modules\Market\Entities\WarehouseTransfer $transfer, int $userId): void
    {
        DB::transaction(function () use ($transfer, $userId) {
            // 1. Fetch source stock
            $sourceStock = WarehouseStock::where('warehouse_id', $transfer->source_warehouse_id)
                ->where('product_variant_id', $transfer->product_variant_id)
                ->first();

            if (!$sourceStock) {
                throw new \Exception('موجودی کالا در انبار مبدا یافت نشد.');
            }

            $qty = $transfer->quantity;
            if ($sourceStock->physical_stock < $qty) {
                throw new \Exception('موجودی فیزیکی کافی در انبار مبدا وجود ندارد.');
            }
            if ($sourceStock->online_stock < $qty) {
                throw new \Exception('موجودی آنلاین کافی در انبار مبدا وجود ندارد.');
            }

            // 2. Deduct from source warehouse
            $sourceStock->decrement('physical_stock', $qty);
            $sourceStock->decrement('online_stock', $qty);

            // 3. Find or create destination vendor product & stock
            $destVendorId = $transfer->destinationWarehouse->vendor_id;
            $destVendorIdForProduct = $destVendorId ?? (Vendor::first()?->id);
            
            $destVendorProduct = VendorProduct::firstOrCreate(
                ['vendor_id' => $destVendorIdForProduct, 'product_variant_id' => $transfer->product_variant_id],
                ['status' => 'draft', 'price' => 0]
            );

            $destStock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $transfer->destination_warehouse_id,
                    'product_variant_id' => $transfer->product_variant_id,
                    'vendor_product_id' => $destVendorProduct->id,
                ],
                ['physical_stock' => 0, 'online_stock' => 0, 'reserved_stock' => 0]
            );

            // 4. Increment at destination warehouse
            $destStock->increment('physical_stock', $qty);
            $destStock->increment('online_stock', $qty);

            // 5. Log WarehouseTransactions for source (out)
            WarehouseTransaction::create([
                'warehouse_id' => $transfer->source_warehouse_id,
                'product_variant_id' => $transfer->product_variant_id,
                'vendor_product_id' => $transfer->vendor_product_id,
                'type' => 'out',
                'quantity' => -$qty,
                'description' => "انتقال کالا به انبار [{$transfer->destinationWarehouse->name}] (درخواست شماره #{$transfer->id})",
                'user_id' => $userId,
            ]);

            // 6. Log WarehouseTransactions for destination (in)
            $sourceVendorName = $transfer->sourceWarehouse->vendor->store_name ?? 'سیستم مرکزی';
            WarehouseTransaction::create([
                'warehouse_id' => $transfer->destination_warehouse_id,
                'product_variant_id' => $transfer->product_variant_id,
                'vendor_product_id' => $destVendorProduct->id,
                'type' => 'in',
                'quantity' => $qty,
                'description' => "انتقال کالا از انبار [{$transfer->sourceWarehouse->name}] - فروشنده [{$sourceVendorName}] (درخواست شماره #{$transfer->id})",
                'user_id' => $userId,
            ]);

            // 7. Sync legacy stocks
            $sourceVendorId = $transfer->sourceWarehouse->vendor_id;
            if ($sourceVendorId) {
                $sourceStockNew = $this->getAvailableStock($transfer->product_variant_id, $sourceVendorId);
                if ($transfer->vendorProduct) {
                    $transfer->vendorProduct->update(['stock' => $sourceStockNew]);
                }
            }
            $destStockNew = $this->getAvailableStock($transfer->product_variant_id, $destVendorId);
            $destVendorProduct->update(['stock' => $destStockNew]);
        });
    }
}

