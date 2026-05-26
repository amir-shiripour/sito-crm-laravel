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
     * @return void
     */
    public function deduct(int $variantId, int $quantity, ?int $vendorProductId = null)
    {
        $isWmsActive = (bool) MarketSetting::getValue('wms.enabled', false);

        if ($isWmsActive) {
            $this->deductFromWms($variantId, $quantity, $vendorProductId);
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
    protected function deductFromWms(int $variantId, int $quantity, ?int $vendorProductId)
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

            // Optionally, you can also create a transaction log here
            // WarehouseTransaction::create([...]);

            $remainingQty -= $qtyToDeduct;
        }

        // Sync legacy stock field in market_vendor_products
        $newStock = app(\Modules\Market\App\Services\WarehouseStockService::class)
            ->getAvailableStock($variantId, $vendorId);
        $vp->update(['stock' => $newStock]);
    }
}
