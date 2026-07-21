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

        $isMarketActive = true;
        $hideOutOfStock = false;
        $currency = 'toman';
        $currencyPosition = 'right_space';
        $variantMode = 'grouped';

        if (class_exists('Modules\Market\Entities\MarketSetting')) {
            $isMarketActive = (bool) \Modules\Market\Entities\MarketSetting::getValue('general.is_market_active', true);
            $hideOutOfStock = (bool) \Modules\Market\Entities\MarketSetting::getValue('general.hide_out_of_stock', false);
            $currency = \Modules\Market\Entities\MarketSetting::getValue('general.currency', 'toman');
            $currencyPosition = \Modules\Market\Entities\MarketSetting::getValue('general.currency_position', 'right_space');
            $variantMode = \Modules\Market\Entities\MarketSetting::getValue('general.variant_display_mode', 'grouped');
        }

        if (!$isMarketActive) {
            return [];
        }

        try {
            if ($variantMode === 'separated' && class_exists('Modules\Market\Entities\ProductVariant')) {
                $query = \Modules\Market\Entities\ProductVariant::with([
                    'masterProduct',
                    'vendorProducts' => function ($q) {
                        $q->where('status', 'published')->orderBy('price', 'asc');
                    }
                ])
                ->whereIn('master_product_id', $ids)
                ->whereHas('masterProduct', function ($q) {
                    $q->where('status', 'active');
                });

                if ($hideOutOfStock) {
                    $query->whereHas('vendorProducts', function ($q) {
                        $q->where('status', 'published')->where('stock', '>', 0);
                    });
                } else {
                    $query->whereHas('vendorProducts', function ($q) {
                        $q->where('status', 'published');
                    });
                }

                return $query->get()
                    ->map(function ($variant) use ($currency, $currencyPosition) {
                        $masterProduct = $variant->masterProduct;

                        $minPrice = 0;
                        $originalPrice = 0;
                        $hasStock = false;
                        $vendorProductId = null;

                        $bestVp = $variant->vendorProducts->where('stock', '>', 0)->first();
                        if (!$bestVp) {
                            $bestVp = $variant->vendorProducts->first();
                        }

                        if ($bestVp) {
                            $minPrice = $bestVp->discount_price > 0 ? $bestVp->discount_price : $bestVp->price;
                            $originalPrice = $bestVp->price;
                            $hasStock = $bestVp->stock > 0;
                            $vendorProductId = $bestVp->id;
                        }

                        $discountPercent = 0;
                        if ($originalPrice > 0 && $minPrice > 0 && $originalPrice > $minPrice) {
                            $discountPercent = (int) round((($originalPrice - $minPrice) / $originalPrice) * 100);
                        }

                        $title = $masterProduct->title;

                        $image = !empty($masterProduct->main_image_url) ? $masterProduct->main_image_url : null;

                        return [
                            'id' => $variant->id,
                            'title' => $title,
                            'image' => $image,
                            'price' => $minPrice,
                            'has_stock' => $hasStock,
                            'discount_percent' => $discountPercent,
                            'variant_id' => $variant->id,
                            'vendor_product_id' => $vendorProductId,
                            'slug' => $masterProduct->slug,
                            'has_variations' => false,
                            'variant_name' => $variant->name,
                            'formatted_price' => $this->formatPrice((float)$minPrice, $currency, $currencyPosition),
                            'formatted_original_price' => $this->formatPrice((float)$originalPrice, $currency, $currencyPosition),
                        ];
                    })
                    ->toArray();
            }

            return \Modules\Market\Entities\MasterProduct::with(['variants.vendorProducts' => function ($q) {
                    $q->where('status', 'published')->where('stock', '>', 0)->orderBy('price', 'asc');
                }])
                ->whereIn('id', $ids)
                ->where('status', 'active')
                ->get()
                ->map(function ($product) use ($currency, $currencyPosition) {
                    $priceInfo = $product->price_info;
                    
                    $variantId = null;
                    $vendorProductId = null;
                    foreach ($product->variants as $variant) {
                        $vp = $variant->vendorProducts->first();
                        if ($vp) {
                            $variantId = $variant->id;
                            $vendorProductId = $vp->id;
                            break;
                        }
                    }

                    $minPrice = $priceInfo['min_price'] ?? 0;
                    $originalPrice = $priceInfo['original_price'] ?? 0;
                    $hasStock = $priceInfo['has_stock'] ?? false;
                    $discountPercent = $priceInfo['discount_percent'] ?? 0;

                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'image' => !empty($product->main_image_url) ? $product->main_image_url : null,
                        'price' => $minPrice,
                        'has_stock' => $hasStock,
                        'discount_percent' => $discountPercent,
                        'variant_id' => $variantId,
                        'vendor_product_id' => $vendorProductId,
                        'slug' => $product->slug,
                        'has_variations' => ($priceInfo['active_variants_count'] > 1 || $product->variants->count() > 1),
                        'variant_name' => '',
                        'formatted_price' => $this->formatPrice((float)$minPrice, $currency, $currencyPosition),
                        'formatted_original_price' => $this->formatPrice((float)$originalPrice, $currency, $currencyPosition),
                    ];
                })
                ->filter(function ($p) use ($hideOutOfStock) {
                    if ($hideOutOfStock) {
                        return $p['has_stock'];
                    }
                    return true;
                })
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            Log::error('EntityResolverService: Failed to resolve products: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format price using store settings.
     */
    public function formatPrice(float $amount, string $currency, string $position): string
    {
        $formattedAmount = number_format($amount);
        $currencyLabel = $currency === 'toman' ? 'تومان' : ($currency === 'rial' ? 'ریال' : $currency);

        switch ($position) {
            case 'left':
                return $currencyLabel . $formattedAmount;
            case 'left_space':
                return $currencyLabel . ' ' . $formattedAmount;
            case 'right':
                return $formattedAmount . $currencyLabel;
            case 'right_space':
            default:
                return $formattedAmount . ' ' . $currencyLabel;
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
