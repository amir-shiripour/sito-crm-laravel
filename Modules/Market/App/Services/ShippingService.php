<?php

namespace Modules\Market\App\Services;

use Modules\Market\Entities\ShippingZone;
use Modules\Market\Entities\ShippingMethod;
use Modules\Market\Entities\ShippingRate;
use Modules\Market\Entities\ShippingRule;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\Brand;
use Modules\Market\Entities\Category;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    /**
     * Calculate shipping cost for a given method, destination, and items.
     *
     * @param int $methodId
     * @param string|null $province
     * @param string|null $city
     * @param array $cartItems
     * @param float $grandTotal
     * @return float
     */
    public function calculateShippingCost(int $methodId, ?string $province, ?string $city, array $cartItems, float $grandTotal = 0): ?float
    {
        $method = ShippingMethod::findOrFail($methodId);
        if (!$method->is_active) {
            return null;
        }

        // 1. Calculate physical and volumetric weight
        $weights = $this->calculateCartWeights($cartItems);
        $chargeableWeight = max($weights['physical'], $weights['volumetric']);

        // 2. Determine shipping zone
        $zone = $this->findMatchingZone($province, $city);
        if (!$zone) {
            // If no matching zone, fallback to a flat default
            $cost = $this->getFallbackFlatRate($method, $chargeableWeight, $grandTotal);
            if ($cost === null) return null;
        }

        // 3. Calculate rate using driver
        $cost = null;
        switch ($method->driver) {
            case 'post_api':
                $cost = $this->calculatePostApiRate($method, $province, $city, $chargeableWeight, $grandTotal);
                break;
            case 'tipax_api':
                $cost = $this->calculateTipaxApiRate($method, $province, $city, $chargeableWeight, $grandTotal);
                break;
            case 'flat_rate':
            case 'weight_based':
            default:
                if ($zone) {
                    $cost = $this->calculateDatabaseRate($method->id, $zone->id, $chargeableWeight, $grandTotal);
                }
                break;
        }

        if ($cost === null) {
            return null;
        }

        // 4. Apply Shipping Rules (Discounts / Free Shipping)
        $cost = $this->applyShippingRules($cost, $cartItems, $grandTotal);

        return max(0, $cost);
    }

    /**
     * Calculate total physical and volumetric weights of cart items.
     *
     * @param array $cartItems
     * @return array
     */
    public function calculateCartWeights(array $cartItems): array
    {
        $totalPhysical = 0; // grams
        $totalVolumetric = 0; // grams (dim weight)

        foreach ($cartItems as $item) {
            $qty = $item['quantity'] ?? 1;
            $variantId = $item['variant_id'] ?? ($item['id'] ?? null);
            if (!$variantId) {
                continue;
            }

            $variant = ProductVariant::with('masterProduct')->find($variantId);
            if (!$variant || !$variant->masterProduct) {
                continue;
            }

            $product = $variant->masterProduct;

            // Physical weight (fallback to 0)
            $itemWeight = (float) ($product->weight ?? 0); // grams
            $totalPhysical += ($itemWeight * $qty);

            // Volumetric weight: (L * W * H) / 5000 (usually in cm, outputs kg equivalent, so we multiply by 1000 for grams)
            $l = (float) ($product->length ?? 0);
            $w = (float) ($product->width ?? 0);
            $h = (float) ($product->height ?? 0);

            if ($l > 0 && $w > 0 && $h > 0) {
                $volWeightKg = ($l * $w * $h) / 5000;
                $volWeightGrams = $volWeightKg * 1000;
                $totalVolumetric += ($volWeightGrams * $qty);
            }
        }

        return [
            'physical' => $totalPhysical,
            'volumetric' => $totalVolumetric
        ];
    }

    /**
     * Find the best shipping zone matching destination.
     *
     * @param string|null $province
     * @param string|null $city
     * @return ShippingZone|null
     */
    public function findMatchingZone(?string $province, ?string $city): ?ShippingZone
    {
        if (empty($province)) {
            return null;
        }

        $zones = ShippingZone::where('is_active', true)->get();

        // 1. Try to find zone matching both city and province
        if (!empty($city)) {
            foreach ($zones as $zone) {
                $states = $zone->states ?? [];
                $cities = $zone->cities ?? [];
                if (in_array($province, $states) && in_array($city, $cities)) {
                    return $zone;
                }
            }
        }

        // 2. Try to find zone matching province
        foreach ($zones as $zone) {
            $states = $zone->states ?? [];
            if (in_array($province, $states)) {
                return $zone;
            }
        }

        // 3. Try to find a global zone (states is empty/null)
        foreach ($zones as $zone) {
            if (empty($zone->states)) {
                return $zone;
            }
        }

        return null;
    }

    /**
     * Calculate rate from the database shipping rates table.
     *
     * @param int $methodId
     * @param int $zoneId
     * @param float $weightGrams
     * @param float $grandTotal
     * @return float
     */
    protected function calculateDatabaseRate(int $methodId, int $zoneId, float $weightGrams, float $grandTotal): ?float
    {
        $rate = ShippingRate::where('shipping_method_id', $methodId)
            ->where('shipping_zone_id', $zoneId)
            ->where('min_weight', '<=', $weightGrams)
            ->where('max_weight', '>=', $weightGrams)
            ->where('min_order_price', '<=', $grandTotal)
            ->orderBy('min_order_price', 'desc')
            ->first();

        if (!$rate) {
            // Fallback to general zone rate or base cost
            $rate = ShippingRate::where('shipping_method_id', $methodId)
                ->where('shipping_zone_id', $zoneId)
                ->orderBy('min_weight', 'asc')
                ->first();
                
            if ($rate && $grandTotal < $rate->min_order_price) {
                return null;
            }
        }

        if (!$rate) {
            return null;
        }

        $cost = (float) $rate->cost;
        
        // If there's a per kg extra cost
        if ($rate->per_kg_cost > 0 && $weightGrams > $rate->min_weight) {
            $extraWeightKg = ceil(($weightGrams - $rate->min_weight) / 1000);
            $cost += ($extraWeightKg * (float) $rate->per_kg_cost);
        }

        return $cost;
    }

    /**
     * Fallback flat rate method if zonation is incomplete.
     */
    protected function getFallbackFlatRate(ShippingMethod $method, float $weightGrams, float $grandTotal): ?float
    {
        $rate = ShippingRate::where('shipping_method_id', $method->id)
            ->orderBy('cost', 'asc')
            ->first();

        if ($rate && $grandTotal < $rate->min_order_price) {
            return null;
        }

        return $rate ? (float) $rate->cost : null;
    }

    /**
     * Call mock/live API for Iran Post.
     */
    protected function calculatePostApiRate(ShippingMethod $method, ?string $province, ?string $city, float $weightGrams, float $grandTotal): float
    {
        try {
            $settings = $method->settings ?? [];
            $apiKey = $settings['api_key'] ?? null;

            if (!$apiKey) {
                // No API Key, fallback to DB static rates
                return $this->fallbackDatabaseRate($method->id, $province, $city, $weightGrams, $grandTotal);
            }

            // Simulate API request to Iran Post
            // Real implementation would use Http::post or curl.
            // Let's implement a realistic weight-based API pricing simulation:
            $basePrice = 35000; // Toman
            if ($province !== 'تهران') {
                $basePrice = 55000; // out of province
            }
            $weightKg = ceil($weightGrams / 1000);
            $apiCost = $basePrice + ($weightKg * 8000);

            return $apiCost;

        } catch (\Exception $e) {
            Log::error('Iran Post API Error: ' . $e->getMessage() . '. Falling back to DB rates.');
            return $this->fallbackDatabaseRate($method->id, $province, $city, $weightGrams, $grandTotal);
        }
    }

    /**
     * Call mock/live API for Tipax.
     */
    protected function calculateTipaxApiRate(ShippingMethod $method, ?string $province, ?string $city, float $weightGrams, float $grandTotal): float
    {
        try {
            $settings = $method->settings ?? [];
            $apiKey = $settings['api_key'] ?? null;

            if (!$apiKey) {
                return $this->fallbackDatabaseRate($method->id, $province, $city, $weightGrams, $grandTotal);
            }

            // Simulate Tipax pricing (Tipax is usually heavier and costlier)
            $basePrice = 75000; // Toman
            if ($province !== 'تهران') {
                $basePrice = 110000;
            }
            $weightKg = ceil($weightGrams / 1000);
            $apiCost = $basePrice + ($weightKg * 12000);

            return $apiCost;

        } catch (\Exception $e) {
            Log::error('Tipax API Error: ' . $e->getMessage() . '. Falling back to DB rates.');
            return $this->fallbackDatabaseRate($method->id, $province, $city, $weightGrams, $grandTotal);
        }
    }

    /**
     * Fallback lookup helper.
     */
    protected function fallbackDatabaseRate(int $methodId, ?string $province, ?string $city, float $weightGrams, float $grandTotal): float
    {
        $zone = $this->findMatchingZone($province, $city);
        if (!$zone) {
            return 0;
        }
        return $this->calculateDatabaseRate($methodId, $zone->id, $weightGrams, $grandTotal);
    }

    /**
     * Apply active discount rules and free shipping exceptions.
     */
    protected function applyShippingRules(float $cost, array $cartItems, float $grandTotal): float
    {
        $rules = ShippingRule::where('is_active', true)->get();

        foreach ($rules as $rule) {
            // Check grand total condition
            if ($rule->min_grand_total > 0 && $grandTotal < $rule->min_grand_total) {
                continue;
            }

            // Check product/brand/category conditions
            $conditions = $rule->conditions ?? [];
            $match = false;

            $brandIds = $conditions['brand_ids'] ?? [];
            $categoryIds = $conditions['category_ids'] ?? [];
            $displayCategoryIds = $conditions['display_category_ids'] ?? [];
            $productIds = $conditions['product_ids'] ?? [];
            $variantIds = $conditions['variant_ids'] ?? [];

            // If no item conditions specified, the rule applies globally
            if (empty($brandIds) && empty($categoryIds) && empty($displayCategoryIds) && empty($productIds) && empty($variantIds)) {
                $match = true;
            } else {
                // Check if any cart item matches the conditions
                foreach ($cartItems as $item) {
                    $variantId = $item['variant_id'] ?? ($item['id'] ?? null);
                    if (!$variantId) {
                        continue;
                    }

                    if (in_array($variantId, $variantIds)) {
                        $match = true;
                        break;
                    }

                    $variant = ProductVariant::with('masterProduct')->find($variantId);
                    if (!$variant || !$variant->masterProduct) {
                        continue;
                    }

                    if (in_array($variant->master_product_id, $productIds)) {
                        $match = true;
                        break;
                    }

                    if (in_array($variant->masterProduct->brand_id, $brandIds)) {
                        $match = true;
                        break;
                    }

                    if (in_array($variant->masterProduct->category_id, $categoryIds)) {
                        $match = true;
                        break;
                    }

                    if (!empty($displayCategoryIds)) {
                        $prodDisplayCatIds = $variant->masterProduct->displayCategories()->pluck('market_display_categories.id')->toArray();
                        if (array_intersect($prodDisplayCatIds, $displayCategoryIds)) {
                            $match = true;
                            break;
                        }
                    }
                }
            }

            if ($match) {
                if ($rule->action_type === 'free_shipping') {
                    return 0;
                } elseif ($rule->action_type === 'percentage_discount') {
                    $discount = $cost * ((float) $rule->action_value / 100);
                    $cost -= $discount;
                } elseif ($rule->action_type === 'fixed_discount') {
                    $cost -= (float) $rule->action_value;
                }
            }
        }

        return max(0, $cost);
    }
}
