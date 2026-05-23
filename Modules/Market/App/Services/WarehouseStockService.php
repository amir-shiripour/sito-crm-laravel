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

        if ($this->getStoreType() === 'multi' && $vendorId) {
            // Multi-vendor: check vendor's own warehouses
            $query->where('market_warehouses.vendor_id', $vendorId);
        } else {
            // Single-vendor or no vendor specified: check central warehouse
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
}
