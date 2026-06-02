<?php

namespace Modules\Market\App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Modules\Market\App\Models\CheckoutForm;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\Warehouse;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\WarehouseStock;

class MarketSettings extends Component
{
    // ... (All other properties remain unchanged)
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
    public bool $enable_geolocation_ordering = false;
    public string $default_payment_gateway = 'zarinpal';
    public string $min_withdrawal_amount = '500000';
    public string $withdrawal_schedule = 'on_demand';

    public string $map_provider = 'neshan';
    public string $map_api_key = '';

    // Checkout settings
    public bool $checkout_sync_require_approval = false;
    public bool $checkout_auto_fill_from_client = true;
    public ?string $checkout_default_form_key = null;
    public bool $checkout_allow_product_override = true;
    public bool $checkout_allow_category_override = true;


    public function mount()
    {
        $this->stock_deduction_strategy = MarketSetting::getValue('wms.stock_deduction_strategy');
        $this->store_type = MarketSetting::getValue('system.store_type');
        $this->store_display_type = MarketSetting::getValue('system.store_display_type');
        $this->wms_enabled = (bool) MarketSetting::getValue('wms.enabled');
        $this->enable_reports = (bool) MarketSetting::getValue('system.enable_reports');
        $this->enable_coupons = (bool) MarketSetting::getValue('system.enable_coupons');
        $this->sequential_discounts = (bool) MarketSetting::getValue('system.sequential_discounts');
        $this->enable_wallet = (bool) MarketSetting::getValue('system.enable_wallet');
        $this->enable_affiliate = (bool) MarketSetting::getValue('system.enable_affiliate');
        $this->system_product_prefix = MarketSetting::getValue('system.product_prefix');
        $this->is_market_active = (bool) MarketSetting::getValue('general.is_market_active');
        $this->currency = MarketSetting::getValue('general.currency');
        $this->currency_position = MarketSetting::getValue('general.currency_position');
        $this->hide_out_of_stock = (bool) MarketSetting::getValue('general.hide_out_of_stock');
        $this->selling_location = MarketSetting::getValue('general.selling_location');
        $this->specific_locations = json_decode(MarketSetting::getValue('general.specific_locations', '[]'), true) ?? [];
        $this->business_days = MarketSetting::getValue('general.business_days');
        $this->variant_display_mode = MarketSetting::getValue('general.variant_display_mode');
        $this->ui_show_category_on_card = (bool) MarketSetting::getValue('ui.show_category_on_card');
        $this->ui_show_vendor_on_product_page = (bool) MarketSetting::getValue('ui.show_vendor_on_product_page');
        $this->ui_show_stock_warning = (bool) MarketSetting::getValue('ui.show_stock_warning');
        $this->ui_product_card_style = MarketSetting::getValue('ui.product_card_style');
        $this->enable_taxes = (bool) MarketSetting::getValue('tax.enable_taxes');
        $this->prices_include_tax = (bool) MarketSetting::getValue('tax.prices_include_tax');
        $this->tax_calculation_based_on = MarketSetting::getValue('tax.tax_calculation_based_on');
        $this->default_tax_rate = MarketSetting::getValue('tax.default_tax_rate');
        $this->auto_approve_vendors = (bool) MarketSetting::getValue('vendors.auto_approve_vendors');
        $this->products_require_approval = (bool) MarketSetting::getValue('vendors.products_require_approval');
        $this->vendor_can_view_customer_info = (bool) MarketSetting::getValue('vendors.vendor_can_view_customer_info');
        $this->vendor_can_create_variants = (bool) MarketSetting::getValue('vendors.vendor_can_create_variants');
        $this->default_commission_rate = MarketSetting::getValue('vendors.default_commission_rate');
        $this->max_vendor_addresses = (int) MarketSetting::getValue('vendors.max_vendor_addresses');
        $this->allow_guest_checkout = (bool) MarketSetting::getValue('orders.allow_guest_checkout');
        $this->min_order_amount = MarketSetting::getValue('orders.min_order_amount');
        $this->auto_cancel_unpaid_orders_hours = (int) MarketSetting::getValue('orders.auto_cancel_unpaid_hours');
        $this->return_policy_days = (int) MarketSetting::getValue('orders.return_policy_days');
        $this->invoice_prefix = MarketSetting::getValue('orders.invoice_prefix');
        $this->enable_geolocation_ordering = (bool) MarketSetting::getValue('orders.enable_geolocation_ordering', false);
        $this->default_payment_gateway = MarketSetting::getValue('finance.default_gateway');
        $this->min_withdrawal_amount = MarketSetting::getValue('finance.min_withdrawal_amount');
        $this->withdrawal_schedule = MarketSetting::getValue('finance.withdrawal_schedule');

        // Checkout settings
        $this->checkout_sync_require_approval = (bool) MarketSetting::getValue('checkout.sync_require_approval');
        $this->checkout_auto_fill_from_client = (bool) MarketSetting::getValue('checkout.auto_fill_from_client');
        $this->checkout_default_form_key = MarketSetting::getValue('checkout.default_form_key');
        $this->checkout_allow_product_override = (bool) MarketSetting::getValue('checkout.allow_product_override');
        $this->checkout_allow_category_override = (bool) MarketSetting::getValue('checkout.allow_category_override');

        // Map settings
        $this->map_provider = MarketSetting::getValue('map.provider', 'neshan');
        $this->map_api_key = MarketSetting::getValue('map.api_key', '');
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
            'map_provider' => 'required|in:neshan,map_ir',
            'map_api_key' => 'nullable|string',
        ]);

        $wasWmsDisabled = !MarketSetting::getValue('wms.enabled');

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
        MarketSetting::setValue('orders.enable_geolocation_ordering', $this->enable_geolocation_ordering);
        MarketSetting::setValue('finance.default_gateway', $this->default_payment_gateway);
        MarketSetting::setValue('finance.min_withdrawal_amount', $this->min_withdrawal_amount);
        MarketSetting::setValue('finance.withdrawal_schedule', $this->withdrawal_schedule);

        // Checkout settings
        MarketSetting::setValue('checkout.sync_require_approval', $this->checkout_sync_require_approval);
        MarketSetting::setValue('checkout.auto_fill_from_client', $this->checkout_auto_fill_from_client);
        MarketSetting::setValue('checkout.default_form_key', $this->checkout_default_form_key);
        MarketSetting::setValue('checkout.allow_product_override', $this->checkout_allow_product_override);
        MarketSetting::setValue('checkout.allow_category_override', $this->checkout_allow_category_override);

        // Map settings
        MarketSetting::setValue('map.provider', $this->map_provider);
        MarketSetting::setValue('map.api_key', $this->map_api_key);

        if ($wasWmsDisabled && $this->wms_enabled) {
            $this->onWmsEnabled();
        }

        $this->autoProvisionForSingleVendor();

        $this->dispatch('notify', type: 'success', text: 'تنظیمات پیشرفته فروشگاه با موفقیت بروزرسانی شد.');
    }

    protected function onWmsEnabled()
    {
        DB::transaction(function () {
            WarehouseStock::query()->update([
                'online_stock' => 0,
                'physical_stock' => 0,
                'reserved_stock' => 0,
            ]);

            $warehouseCache = [];

            VendorProduct::query()
                ->where('stock', '>', 0)
                ->with('vendor.user')
                ->chunkById(200, function ($vendorProducts) use (&$warehouseCache) {
                    foreach ($vendorProducts as $vendorProduct) {
                        $vendorId = $vendorProduct->vendor_id;

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

                        WarehouseStock::updateOrCreate(
                            [
                                'warehouse_id' => $targetWarehouse->id,
                                'product_variant_id' => $vendorProduct->product_variant_id,
                                'vendor_product_id' => $vendorProduct->id,
                            ],
                            [
                                'online_stock' => $vendorProduct->getRawOriginal('stock'),
                                'physical_stock' => $vendorProduct->getRawOriginal('stock'),
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
        $checkoutForms = CheckoutForm::all();

        return view('market::livewire.admin.market-settings', [
            'locationsList' => ['تهران', 'اصفهان', 'خراسان رضوی', 'فارس', 'آذربایجان شرقی', 'مازندران', 'البرز', 'خوزستان'],
            'checkoutForms' => $checkoutForms,
        ]);
    }
}
