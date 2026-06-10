<?php

namespace Modules\Market\App\Services;

use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\WarehouseStock;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Deduct stock for a given variant.
     *
     * @param int $variantId
     * @param int $quantity
     * @param int|null $vendorProductId
     * @param float|null $unitPrice
     * @return void
     */
    public function deduct(int $variantId, int $quantity, ?int $vendorProductId = null, ?float $unitPrice = null)
    {
        $isWmsActive = (bool) MarketSetting::getValue('wms.enabled', false);

        if ($isWmsActive) {
            $this->deductFromWms($variantId, $quantity, $vendorProductId, $unitPrice);
        } else {
            $this->deductFromLegacy($variantId, $quantity, $vendorProductId);
        }
    }

    /**
     * Deduct stock from the legacy stock system (market_vendor_products.stock).
     */
    protected function deductFromLegacy(int $variantId, int $quantity, ?int $vendorProductId)
    {
        if (!$vendorProductId) {
            // This case should ideally not happen in a real checkout flow
            $vp = VendorProduct::where('product_variant_id', $variantId)
                ->where('stock', '>=', $quantity)
                ->orderBy('price', 'asc')
                ->firstOrFail();
        } else {
            $vp = VendorProduct::findOrFail($vendorProductId);
        }

        if ($vp->stock < $quantity) {
            throw new \Exception("Not enough stock for product variant #{$variantId}.");
        }

        $vp->decrement('stock', $quantity);
    }

    /**
     * Deduct stock from the Warehouse Management System (WMS).
     */
    protected function deductFromWms(int $variantId, int $quantity, ?int $vendorProductId, ?float $unitPrice = null)
    {
        $vp = VendorProduct::findOrFail($vendorProductId);
        $vendorId = $vp->vendor_id;

        $strategy = MarketSetting::getValue('wms.stock_deduction_strategy', 'combined');
        $stockField = $strategy === 'separated' ? 'online_stock' : 'physical_stock';

        // Get all active warehouses for the vendor that have the variant in stock
        $stocks = WarehouseStock::where('product_variant_id', $variantId)
            ->where($stockField, '>', 0)
            ->whereHas('warehouse', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)->where('is_active', true);
            })
            ->orderBy('created_at', 'asc') // FIFO-like logic on warehouse level
            ->get();

        $totalAvailableStock = $stocks->sum($stockField);

        if ($totalAvailableStock < $quantity) {
            throw new \Exception("Not enough WMS stock for product variant #{$variantId}.");
        }

        $remainingQty = $quantity;

        foreach ($stocks as $stock) {
            if ($remainingQty <= 0) {
                break;
            }

            $qtyToDeduct = min($stock->{$stockField}, $remainingQty);

            $stock->decrement($stockField, $qtyToDeduct);
            
            // If strategy is combined, also decrement online_stock to keep it in sync
            if ($strategy !== 'separated' && $stock->online_stock >= $qtyToDeduct) {
                $stock->decrement('online_stock', $qtyToDeduct);
            }

            // Create an automatic transaction log for checkout order
            \Modules\Market\Entities\WarehouseTransaction::create([
                'warehouse_id' => $stock->warehouse_id,
                'product_variant_id' => $variantId,
                'vendor_product_id' => $vendorProductId,
                'type' => 'out',
                'quantity' => $qtyToDeduct,
                'unit_price' => $unitPrice,
                'description' => 'کاهش خودکار موجودی از طریق سفارش آنلاین (فروش)',
                'user_id' => auth()->id(),
            ]);

            $remainingQty -= $qtyToDeduct;
        }

        // Sync legacy stock field in market_vendor_products
        $newStock = app(\Modules\Market\App\Services\WarehouseStockService::class)
            ->getAvailableStock($variantId, $vendorId);
        $vp->update(['stock' => $newStock]);
    }

    /**
     * Restore stock for a failed or canceled order.
     *
     * @param \Modules\Market\App\Models\Order $order
     * @return void
     */
    public function releaseReservation(\Modules\Market\App\Models\Order $order)
    {
        $isWmsActive = (bool) MarketSetting::getValue('wms.enabled', false);

        foreach ($order->items as $item) {
            $vp = VendorProduct::find($item->vendor_product_id);
            if (!$vp) continue;

            $variantId = $vp->product_variant_id;
            $quantity = $item->quantity;

            if ($isWmsActive) {
                $this->restoreToWms($variantId, $quantity, $vp);
            } else {
                $this->restoreToLegacy($vp, $quantity);
            }
        }
    }

    /**
     * Restore stock to legacy system.
     */
    protected function restoreToLegacy(VendorProduct $vp, int $quantity)
    {
        $vp->increment('stock', $quantity);
    }

    /**
     * Restore stock to WMS system.
     */
    protected function restoreToWms(int $variantId, int $quantity, VendorProduct $vp)
    {
        $vendorId = $vp->vendor_id;
        $strategy = MarketSetting::getValue('wms.stock_deduction_strategy', 'combined');
        $stockField = $strategy === 'separated' ? 'online_stock' : 'physical_stock';

        $warehouseStock = WarehouseStock::where('product_variant_id', $variantId)
            ->whereHas('warehouse', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)->where('is_active', true);
            })
            ->first();

        if ($warehouseStock) {
            $warehouseStock->increment($stockField, $quantity);

            if ($strategy !== 'separated') {
                $warehouseStock->increment('online_stock', $quantity);
            }
        }

        // Sync legacy stock field in market_vendor_products
        $newStock = app(\Modules\Market\App\Services\WarehouseStockService::class)
            ->getAvailableStock($variantId, $vendorId);
        $vp->update(['stock' => $newStock]);
    }
}

