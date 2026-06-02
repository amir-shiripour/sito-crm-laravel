<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MarketSetting extends Model
{
    protected $table = 'market_settings';
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'checkout' => [
            'sync_require_approval' => false,
            'auto_fill_from_client' => true,
            'default_form_key' => null,
            'allow_product_override' => true,
            'allow_category_override' => true,
        ],
        'wms' => [
            'stock_deduction_strategy' => 'combined',
            'enabled' => false,
        ],
        'system' => [
            'store_type' => 'single',
            'store_display_type' => 'by_product',
            'enable_reports' => true,
            'enable_coupons' => true,
            'sequential_discounts' => false,
            'enable_wallet' => false,
            'enable_affiliate' => false,
            'product_prefix' => 'SIT',
        ],
        'general' => [
            'is_market_active' => true,
            'currency' => 'toman',
            'currency_position' => 'right_space',
            'hide_out_of_stock' => false,
            'selling_location' => 'all',
            'specific_locations' => '[]',
            'business_days' => '',
            'variant_display_mode' => 'grouped',
        ],
        'ui' => [
            'show_category_on_card' => true,
            'show_vendor_on_product_page' => true,
            'show_stock_warning' => true,
            'product_card_style' => 'modern',
        ],
        'tax' => [
            'enable_taxes' => false,
            'prices_include_tax' => false,
            'tax_calculation_based_on' => 'customer_shipping',
            'default_tax_rate' => '9',
        ],
        'vendors' => [
            'auto_approve_vendors' => false,
            'products_require_approval' => true,
            'vendor_can_view_customer_info' => false,
            'vendor_can_create_variants' => false,
            'default_commission_rate' => '10',
            'max_vendor_addresses' => 3,
        ],
        'orders' => [
            'allow_guest_checkout' => false,
            'min_order_amount' => '0',
            'auto_cancel_unpaid_hours' => 24,
            'return_policy_days' => 7,
            'invoice_prefix' => 'INV-',
        ],
        'finance' => [
            'default_gateway' => 'zarinpal',
            'min_withdrawal_amount' => '500000',
            'withdrawal_schedule' => 'on_demand',
        ],
        'map' => [
            'provider' => 'neshan',
            'api_key' => '',
        ],
    ];

    public static function getValue(string $key, $default = null)
    {
        return Cache::rememberForever("market_setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if ($setting) {
                return $setting->value;
            }

            $keys = explode('.', $key);
            $value = self::DEFAULTS;
            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return $default;
                }
                $value = $value[$k];
            }
            return $value;
        });
    }

    public static function setValue(string $key, $value)
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("market_setting_{$key}");
        return $setting;
    }
}
