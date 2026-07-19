<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class EntityResolverService
{
    /**
     * Resolve products by IDs.
     */
    public function resolveProducts(array $ids): array
    {
        if (!class_exists('Modules\Market\Entities\MasterProduct')) {
            return [];
        }

        try {
            return \Modules\Market\Entities\MasterProduct::whereIn('id', $ids)
                ->where('status', 'published')
                ->get()
                ->map(function ($product) {
                    $priceInfo = $product->price_info;
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'image' => $product->main_image_url ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500',
                        'price' => $priceInfo['min_price'] ?? 0,
                        'has_stock' => $priceInfo['has_stock'] ?? false,
                        'discount_percent' => $priceInfo['discount_percent'] ?? 0,
                    ];
                })
                ->toArray();
        } catch (\Throwable $e) {
            Log::error('EntityResolverService: Failed to resolve products: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get parameters to add a product to the cart.
     */
    public function getAddToCartParams(int $productId): ?array
    {
        if (!class_exists('Modules\Market\Entities\MasterProduct')) {
            return null;
        }

        try {
            $product = \Modules\Market\Entities\MasterProduct::with(['variants.vendorProducts' => function ($q) {
                $q->where('status', 'published')->where('stock', '>', 0)->orderBy('price', 'asc');
            }])->find($productId);

            if (!$product) {
                return null;
            }

            foreach ($product->variants as $variant) {
                $vp = $variant->vendorProducts->first();
                if ($vp) {
                    return [
                        'variant_id' => $variant->id,
                        'vendor_product_id' => $vp->id,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::error('EntityResolverService: Failed to get add to cart params: ' . $e->getMessage());
        }

        return null;
    }
}
