<?php

namespace Modules\Market\App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\Warehouse;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\WarehouseStock;

class MarketSettings extends Component
{
    // ... (بقیه پراپرتی‌ها بدون تغییر)
    public string $stock_deduction_strategy = 'combined';
    public string $store_type = 'multi';
    public string $store_display_type = 'by_vendor';
    public bool $wms_enabled = false;
    public bool $enable_reports = true;
    public bool $enable_coupons = true;
    public bool $sequential_discounts = false;
    public bool $enable_wallet = false;
    public bool $enable_affiliate = false;
    public string $system_product_prefix = 'SIT';
    public bool $is_market_active = true;
    public string $currency = 'toman';
    public string $currency_position = 'right_space';
    public bool $hide_out_of_stock = false;
    public string $selling_location = 'all';
    public array $specific_locations = [];
    public string $business_days = '';
    public string $variant_display_mode = 'grouped';
    public bool $ui_show_category_on_card = true;
    public bool $ui_show_vendor_on_product_page = true;
    public bool $ui_show_stock_warning = true;
    public string $ui_product_card_style = 'modern';
    public bool $enable_taxes = false;
    public bool $prices_include_tax = false;
    public string $tax_calculation_based_on = 'customer_shipping';
    public string $default_tax_rate = '9';
    public bool $auto_approve_vendors = false;
    public bool $products_require_approval = true;
    public bool $vendor_can_view_customer_info = false;
    public bool $vendor_can_create_variants = false;
    public string $default_commission_rate = '10';
    public int $max_vendor_addresses = 3;
    public bool $allow_guest_checkout = false;
    public string $min_order_amount = '0';
    public int $auto_cancel_unpaid_orders_hours = 24;
    public int $return_policy_days = 7;
    public string $invoice_prefix = 'INV-';
    public string $default_payment_gateway = 'zarinpal';
    public string $min_withdrawal_amount = '500000';
    public string $withdrawal_schedule = 'on_demand';


    public function mount()
    {
        $this->stock_deduction_strategy = MarketSetting::getValue('wms.stock_deduction_strategy', 'combined');
        $this->store_type = MarketSetting::getValue('system.store_type', 'multi');
        $this->store_display_type = MarketSetting::getValue('system.store_display_type', 'by_vendor');
        $this->wms_enabled = (bool) MarketSetting::getValue('wms.enabled', false);
        $this->enable_reports = (bool) MarketSetting::getValue('system.enable_reports', true);
        $this->enable_coupons = (bool) MarketSetting::getValue('system.enable_coupons', true);
        $this->sequential_discounts = (bool) MarketSetting::getValue('system.sequential_discounts', false);
        $this->enable_wallet = (bool) MarketSetting::getValue('system.enable_wallet', false);
        $this->enable_affiliate = (bool) MarketSetting::getValue('system.enable_affiliate', false);
        $this->system_product_prefix = MarketSetting::getValue('system.product_prefix', 'SIT');
        $this->is_market_active = (bool) MarketSetting::getValue('general.is_market_active', true);
        $this->currency = MarketSetting::getValue('general.currency', 'toman');
        $this->currency_position = MarketSetting::getValue('general.currency_position', 'right_space');
        $this->hide_out_of_stock = (bool) MarketSetting::getValue('general.hide_out_of_stock', false);
        $this->selling_location = MarketSetting::getValue('general.selling_location', 'all');
        $this->specific_locations = json_decode(MarketSetting::getValue('general.specific_locations', '[]'), true) ?? [];
        $this->business_days = MarketSetting::getValue('general.business_days', '');
        $this->variant_display_mode = MarketSetting::getValue('general.variant_display_mode', 'grouped');
        $this->ui_show_category_on_card = (bool) MarketSetting::getValue('ui.show_category_on_card', true);
        $this->ui_show_vendor_on_product_page = (bool) MarketSetting::getValue('ui.show_vendor_on_product_page', true);
        $this->ui_show_stock_warning = (bool) MarketSetting::getValue('ui.show_stock_warning', true);
        $this->ui_product_card_style = MarketSetting::getValue('ui.product_card_style', 'modern');
        $this->enable_taxes = (bool) MarketSetting::getValue('tax.enable_taxes', false);
        $this->prices_include_tax = (bool) MarketSetting::getValue('tax.prices_include_tax', false);
        $this->tax_calculation_based_on = MarketSetting::getValue('tax.tax_calculation_based_on', 'customer_shipping');
        $this->default_tax_rate = MarketSetting::getValue('tax.default_tax_rate', '9');
        $this->auto_approve_vendors = (bool) MarketSetting::getValue('vendors.auto_approve_vendors', false);
        $this->products_require_approval = (bool) MarketSetting::getValue('vendors.products_require_approval', true);
        $this->vendor_can_view_customer_info = (bool) MarketSetting::getValue('vendors.vendor_can_view_customer_info', false);
        $this->vendor_can_create_variants = (bool) MarketSetting::getValue('vendors.vendor_can_create_variants', false);
        $this->default_commission_rate = MarketSetting::getValue('vendors.default_commission_rate', '10');
        $this->max_vendor_addresses = (int) MarketSetting::getValue('vendors.max_vendor_addresses', 3);
        $this->allow_guest_checkout = (bool) MarketSetting::getValue('orders.allow_guest_checkout', false);
        $this->min_order_amount = MarketSetting::getValue('orders.min_order_amount', '0');
        $this->auto_cancel_unpaid_orders_hours = (int) MarketSetting::getValue('orders.auto_cancel_unpaid_hours', 24);
        $this->return_policy_days = (int) MarketSetting::getValue('orders.return_policy_days', 7);
        $this->invoice_prefix = MarketSetting::getValue('orders.invoice_prefix', 'INV-');
        $this->default_payment_gateway = MarketSetting::getValue('finance.default_gateway', 'zarinpal');
        $this->min_withdrawal_amount = MarketSetting::getValue('finance.min_withdrawal_amount', '500000');
        $this->withdrawal_schedule = MarketSetting::getValue('finance.withdrawal_schedule', 'on_demand');
    }

    public function save()
    {
        $this->validate([
            'stock_deduction_strategy' => 'required|in:combined,separated',
            'default_tax_rate' => 'numeric|min:0|max:100',
            'default_commission_rate' => 'numeric|min:0|max:100',
            'max_vendor_addresses' => 'integer|min:1',
            'auto_cancel_unpaid_orders_hours' => 'integer|min:1',
            'return_policy_days' => 'integer|min:0',
            'min_order_amount' => 'numeric|min:0',
            'min_withdrawal_amount' => 'numeric|min:0',
            'system_product_prefix' => 'required|string|max:10',
            'store_display_type' => 'required|in:by_vendor,by_product',
            'variant_display_mode' => 'required|in:grouped,separated',
            'ui_product_card_style' => 'required|in:modern,classic,minimal',
        ]);

        $wasWmsDisabled = !MarketSetting::getValue('wms.enabled', false);

        if (auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('admin')) {
            MarketSetting::setValue('wms.stock_deduction_strategy', $this->stock_deduction_strategy);
            MarketSetting::setValue('system.store_type', $this->store_type);
            MarketSetting::setValue('system.store_display_type', $this->store_display_type);
            MarketSetting::setValue('wms.enabled', $this->wms_enabled);
            MarketSetting::setValue('system.enable_reports', $this->enable_reports);
            MarketSetting::setValue('system.enable_coupons', $this->enable_coupons);
            MarketSetting::setValue('system.sequential_discounts', $this->sequential_discounts);
            MarketSetting::setValue('system.enable_wallet', $this->enable_wallet);
            MarketSetting::setValue('system.enable_affiliate', $this->enable_affiliate);
            MarketSetting::setValue('system.product_prefix', strtoupper($this->system_product_prefix));
        }

        MarketSetting::setValue('general.is_market_active', $this->is_market_active);
        MarketSetting::setValue('general.currency', $this->currency);
        MarketSetting::setValue('general.currency_position', $this->currency_position);
        MarketSetting::setValue('general.hide_out_of_stock', $this->hide_out_of_stock);
        MarketSetting::setValue('general.selling_location', $this->selling_location);
        MarketSetting::setValue('general.specific_locations', json_encode($this->specific_locations));
        MarketSetting::setValue('general.business_days', $this->business_days);
        MarketSetting::setValue('general.variant_display_mode', $this->variant_display_mode);
        MarketSetting::setValue('ui.show_category_on_card', $this->ui_show_category_on_card);
        MarketSetting::setValue('ui.show_vendor_on_product_page', $this->ui_show_vendor_on_product_page);
        MarketSetting::setValue('ui.show_stock_warning', $this->ui_show_stock_warning);
        MarketSetting::setValue('ui.product_card_style', $this->ui_product_card_style);
        MarketSetting::setValue('tax.enable_taxes', $this->enable_taxes);
        MarketSetting::setValue('tax.prices_include_tax', $this->prices_include_tax);
        MarketSetting::setValue('tax.tax_calculation_based_on', $this->tax_calculation_based_on);
        MarketSetting::setValue('tax.default_tax_rate', $this->default_tax_rate);
        MarketSetting::setValue('vendors.auto_approve_vendors', $this->auto_approve_vendors);
        MarketSetting::setValue('vendors.products_require_approval', $this->products_require_approval);
        MarketSetting::setValue('vendors.vendor_can_view_customer_info', $this->vendor_can_view_customer_info);
        MarketSetting::setValue('vendors.vendor_can_create_variants', $this->vendor_can_create_variants);
        MarketSetting::setValue('vendors.default_commission_rate', $this->default_commission_rate);
        MarketSetting::setValue('vendors.max_vendor_addresses', $this->max_vendor_addresses);
        MarketSetting::setValue('orders.allow_guest_checkout', $this->allow_guest_checkout);
        MarketSetting::setValue('orders.min_order_amount', $this->min_order_amount);
        MarketSetting::setValue('orders.auto_cancel_unpaid_hours', $this->auto_cancel_unpaid_orders_hours);
        MarketSetting::setValue('orders.return_policy_days', $this->return_policy_days);
        MarketSetting::setValue('orders.invoice_prefix', $this->invoice_prefix);
        MarketSetting::setValue('finance.default_gateway', $this->default_payment_gateway);
        MarketSetting::setValue('finance.min_withdrawal_amount', $this->min_withdrawal_amount);
        MarketSetting::setValue('finance.withdrawal_schedule', $this->withdrawal_schedule);

        if ($wasWmsDisabled && $this->wms_enabled) {
            $this->onWmsEnabled();
        }

        $this->autoProvisionForSingleVendor();

        $this->dispatch('notify', type: 'success', text: 'تنظیمات پیشرفته فروشگاه با موفقیت بروزرسانی شد.');
    }

    /**
     * Hook for when WMS is enabled.
     * This method performs a one-time data migration from the legacy stock system
     * to the new Warehouse Management System (WMS). It ensures that the existing
     * 'stock' values from 'market_vendor_products' are correctly transferred to the
     * 'online_stock' and 'physical_stock' fields in the corresponding default
     * warehouse for each product variant, making the legacy data the source of truth.
     */
    protected function onWmsEnabled()
    {
        DB::transaction(function () {
            // Step 1: CRITICAL - Reset all WMS stocks to zero before migration.
            // This prevents additive errors from leftover data in the warehouse table,
            // ensuring the legacy stock is the absolute source of truth.
            WarehouseStock::query()->update([
                'online_stock' => 0,
                'physical_stock' => 0,
                'reserved_stock' => 0,
            ]);

            // Step 2: Prepare a cache for default warehouses to minimize database queries.
            $warehouseCache = [];

            // Step 3: Iterate through all vendor products with legacy stock, in chunks, to sync them.
            VendorProduct::query()
                ->where('stock', '>', 0)
                ->with('vendor.user') // Eager load vendor and user for efficiency
                ->chunkById(200, function ($vendorProducts) use (&$warehouseCache) {
                    foreach ($vendorProducts as $vendorProduct) {
                        $vendorId = $vendorProduct->vendor_id;

                        // Step 3.1: Find or create the default warehouse for the vendor/system.
                        if (!isset($warehouseCache[$vendorId ?? 'system'])) {
                            if ($vendorId) {
                                $vendor = $vendorProduct->vendor;
                                $warehouseCache[$vendorId] = Warehouse::firstOrCreate(
                                    ['vendor_id' => $vendorId],
                                    [
                                        'name' => 'انبار اصلی ' . ($vendor->store_name ?: $vendor->user->name),
                                        'code' => 'WH-' . strtoupper(substr($vendor->slug, 0, 10)),
                                        'is_active' => true,
                                    ]
                                );
                            } else {
                                $warehouseCache['system'] = Warehouse::firstOrCreate(
                                    ['vendor_id' => null],
                                    [
                                        'name' => 'انبار مرکزی سیستم',
                                        'code' => 'WH-MAIN',
                                        'is_active' => true,
                                    ]
                                );
                            }
                        }

                        $targetWarehouse = $warehouseCache[$vendorId ?? 'system'];

                        if (!$targetWarehouse) {
                            continue;
                        }

                        // Step 3.2: Sync the legacy stock to the WMS using updateOrCreate.
                        // Since we reset the table, this will correctly set the stock values.
                        WarehouseStock::updateOrCreate(
                            [
                                'warehouse_id' => $targetWarehouse->id,
                                'product_variant_id' => $vendorProduct->product_variant_id,
                                // Adding vendor_product_id to the query key for more precise matching
                                'vendor_product_id' => $vendorProduct->id,
                            ],
                            [
                                'online_stock' => $vendorProduct->stock,
                                'physical_stock' => $vendorProduct->stock,
                                'reserved_stock' => 0,
                            ]
                        );
                    }
                });
        });
    }


    protected function autoProvisionForSingleVendor()
    {
        if ($this->store_type === 'single') {
            $adminUser = User::role(['super-admin', 'admin'])->first();

            if ($adminUser) {
                $vendorExists = Vendor::where('user_id', $adminUser->id)->exists();

                if (!$vendorExists) {
                    Vendor::create([
                        'user_id' => $adminUser->id,
                        'store_name' => 'فروشگاه اصلی',
                        'slug' => 'main-store',
                        'status' => 'active',
                        'kyc_status' => 'approved',
                    ]);
                }
            }
        }
    }

    public function render()
    {
        return view('market::livewire.admin.market-settings', [
            'locationsList' => ['تهران', 'اصفهان', 'خراسان رضوی', 'فارس', 'آذربایجان شرقی', 'مازندران', 'البرز', 'خوزستان']
        ]);
    }
}
