<?php

namespace Modules\Market\App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\ProductVariant;
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
     * Get the available stock for a product variant.
     * Switches between legacy stock and WMS based on settings.
     *
     * @param int $variantId
     * @param int|null $vendorProductId
     * @return int
     */
    public function getAvailableStock(int $variantId, int $vendorProductId = null): int
    {
        if (!$this->isWmsActive()) {
            // WMS is not active, use legacy stock field.
            if ($vendorProductId) {
                $product = VendorProduct::find($vendorProductId);
                return $product ? $product->stock : 0;
            }
            $variant = ProductVariant::find($variantId);
            return $variant ? $variant->stock : 0;
        }

        // WMS is active, calculate stock from warehouses.
        $query = WarehouseStock::query()
            ->where('product_variant_id', $variantId)
            ->join('market_warehouses', 'market_warehouse_stocks.warehouse_id', '=', 'market_warehouses.id')
            ->where('market_warehouses.is_active', true);

        if ($vendorProductId) {
            $query->where('vendor_product_id', $vendorProductId);
        } else {
            $query->whereNull('vendor_product_id');
        }

        return $query->sum(DB::raw('physical_stock - reserved_stock'));
    }

    /**
     * Increment stock for a specific warehouse.
     *
     * @param int $warehouseId
     * @param int $variantId
     * @param int $quantity
     * @param int|null $vendorProductId
     * @param Model|null $reference
     * @param int|null $userId
     * @param string|null $description
     * @return WarehouseStock
     */
    public function incrementStock(int $warehouseId, int $variantId, int $quantity, int $vendorProductId = null, $reference = null, int $userId = null, string $description = 'Stock increment'): WarehouseStock
    {
        return DB::transaction(function () use ($warehouseId, $variantId, $quantity, $vendorProductId, $reference, $userId, $description) {
            $stock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'product_variant_id' => $variantId,
                    'vendor_product_id' => $vendorProductId,
                ],
                ['physical_stock' => 0, 'reserved_stock' => 0]
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

    /**
     * Decrement stock from a specific warehouse.
     *
     * @param int $warehouseId
     * @param int $variantId
     * @param int $quantity
     * @param int|null $vendorProductId
     * @param Model|null $reference
     * @param int|null $userId
     * @param string|null $description
     * @return WarehouseStock
     */
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

    /**
     * Reserve stock for an order or other purposes.
     *
     * @param int $warehouseId
     * @param int $variantId
     * @param int $quantity
     * @param int|null $vendorProductId
     * @param Model|null $reference
     * @return WarehouseStock
     */
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

            if (($stock->physical_stock - $stock->reserved_stock) < $quantity) {
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

    /**
     * Release reserved stock.
     *
     * @param int $warehouseId
     * @param int $variantId
     * @param int $quantity
     * @param int|null $vendorProductId
     * @param Model|null $reference
     * @return WarehouseStock
     */
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

    /**
     * Finalize a shipment by converting reserved stock to a physical stock decrement.
     *
     * @param int $warehouseId
     * @param int $variantId
     * @param int $quantity
     * @param int|null $vendorProductId
     * @param Model|null $reference
     * @param int|null $userId
     * @return WarehouseStock
     */
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
