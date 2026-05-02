<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\Entities\MarketSetting;

class MarketSettings extends Component
{
    // ==========================================
    // 1. تب هسته سیستم (سوپر ادمین)
    // ==========================================
    public string $store_type = 'multi'; // single | multi
    public bool $enable_advanced_inventory = false; // ماژول انبارداری پیشرفته (WMS)
    public bool $enable_reports = true; // ماژول گزارشات پیشرفته
    public bool $enable_coupons = true;
    public bool $sequential_discounts = false;
    public bool $enable_wallet = false; // کیف پول کاربری
    public bool $enable_affiliate = false; // سیستم همکاری در فروش
    public string $system_product_prefix = 'SIT';

    // ==========================================
    // 2. تب عمومی و کاتالوگ
    // ==========================================
    public bool $is_market_active = true;
    public string $currency = 'toman';
    public string $currency_position = 'right_space'; // right | right_space | left | left_space
    public bool $hide_out_of_stock = false; // مخفی کردن محصولات ناموجود از کاتالوگ
    public string $selling_location = 'all';
    public array $specific_locations = [];
    public string $business_days = '';

    // ==========================================
    // 3. تب مالیات
    // ==========================================
    public bool $enable_taxes = false;
    public bool $prices_include_tax = false;
    public string $tax_calculation_based_on = 'customer_shipping';
    public string $default_tax_rate = '9'; // نرخ پیش‌فرض ارزش افزوده

    // ==========================================
    // 4. تب فروشندگان (فقط در حالت Multi)
    // ==========================================
    public bool $auto_approve_vendors = false;
    public bool $products_require_approval = true; // آیا محصولات فروشنده قبل از انتشار نیاز به تایید ادمین دارند؟
    public bool $vendor_can_view_customer_info = false; // آیا فروشنده شماره تماس/ایمیل خریدار را در سفارش ببیند؟
    public string $default_commission_rate = '10';
    public int $max_vendor_addresses = 3;

    // ==========================================
    // 5. تب سفارشات
    // ==========================================
    public bool $allow_guest_checkout = false;
    public string $min_order_amount = '0'; // حداقل مبلغ برای ثبت سفارش
    public int $auto_cancel_unpaid_orders_hours = 24;
    public int $return_policy_days = 7;
    public string $invoice_prefix = 'INV-'; // پیش‌وند شماره فاکتورها

    // ==========================================
    // 6. تب مالی و تسویه‌حساب
    // ==========================================
    public string $default_payment_gateway = 'zarinpal';
    public string $min_withdrawal_amount = '500000';
    public string $withdrawal_schedule = 'on_demand'; // on_demand | weekly | monthly (زمان‌بندی تسویه با فروشنده)

    public function mount()
    {
        // 1. سیستم
        $this->store_type = MarketSetting::getValue('system.store_type', 'multi');
        $this->enable_advanced_inventory = (bool) MarketSetting::getValue('system.enable_advanced_inventory', false);
        $this->enable_reports = (bool) MarketSetting::getValue('system.enable_reports', true);
        $this->enable_coupons = (bool) MarketSetting::getValue('system.enable_coupons', true);
        $this->sequential_discounts = (bool) MarketSetting::getValue('system.sequential_discounts', false);
        $this->enable_wallet = (bool) MarketSetting::getValue('system.enable_wallet', false);
        $this->enable_affiliate = (bool) MarketSetting::getValue('system.enable_affiliate', false);
        $this->system_product_prefix = MarketSetting::getValue('system.product_prefix', 'SIT');

        // 2. عمومی
        $this->is_market_active = (bool) MarketSetting::getValue('general.is_market_active', true);
        $this->currency = MarketSetting::getValue('general.currency', 'toman');
        $this->currency_position = MarketSetting::getValue('general.currency_position', 'right_space');
        $this->hide_out_of_stock = (bool) MarketSetting::getValue('general.hide_out_of_stock', false);
        $this->selling_location = MarketSetting::getValue('general.selling_location', 'all');
        $this->specific_locations = json_decode(MarketSetting::getValue('general.specific_locations', '[]'), true) ?? [];
        $this->business_days = MarketSetting::getValue('general.business_days', '');

        // 3. مالیات
        $this->enable_taxes = (bool) MarketSetting::getValue('tax.enable_taxes', false);
        $this->prices_include_tax = (bool) MarketSetting::getValue('tax.prices_include_tax', false);
        $this->tax_calculation_based_on = MarketSetting::getValue('tax.tax_calculation_based_on', 'customer_shipping');
        $this->default_tax_rate = MarketSetting::getValue('tax.default_tax_rate', '9');

        // 4. فروشندگان
        $this->auto_approve_vendors = (bool) MarketSetting::getValue('vendors.auto_approve_vendors', false);
        $this->products_require_approval = (bool) MarketSetting::getValue('vendors.products_require_approval', true);
        $this->vendor_can_view_customer_info = (bool) MarketSetting::getValue('vendors.vendor_can_view_customer_info', false);
        $this->default_commission_rate = MarketSetting::getValue('vendors.default_commission_rate', '10');
        $this->max_vendor_addresses = (int) MarketSetting::getValue('vendors.max_vendor_addresses', 3);

        // 5. سفارشات
        $this->allow_guest_checkout = (bool) MarketSetting::getValue('orders.allow_guest_checkout', false);
        $this->min_order_amount = MarketSetting::getValue('orders.min_order_amount', '0');
        $this->auto_cancel_unpaid_orders_hours = (int) MarketSetting::getValue('orders.auto_cancel_unpaid_hours', 24);
        $this->return_policy_days = (int) MarketSetting::getValue('orders.return_policy_days', 7);
        $this->invoice_prefix = MarketSetting::getValue('orders.invoice_prefix', 'INV-');

        // 6. مالی
        $this->default_payment_gateway = MarketSetting::getValue('finance.default_gateway', 'zarinpal');
        $this->min_withdrawal_amount = MarketSetting::getValue('finance.min_withdrawal_amount', '500000');
        $this->withdrawal_schedule = MarketSetting::getValue('finance.withdrawal_schedule', 'on_demand');
    }

    public function save()
    {
        $this->validate([
            'default_tax_rate' => 'numeric|min:0|max:100',
            'default_commission_rate' => 'numeric|min:0|max:100',
            'max_vendor_addresses' => 'integer|min:1',
            'auto_cancel_unpaid_orders_hours' => 'integer|min:1',
            'return_policy_days' => 'integer|min:0',
            'min_order_amount' => 'numeric|min:0',
            'min_withdrawal_amount' => 'numeric|min:0',
            'system_product_prefix' => 'required|string|max:10',
        ]);

        if (auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('admin')) {
            MarketSetting::setValue('system.store_type', $this->store_type);
            MarketSetting::setValue('system.enable_advanced_inventory', $this->enable_advanced_inventory);
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

        MarketSetting::setValue('tax.enable_taxes', $this->enable_taxes);
        MarketSetting::setValue('tax.prices_include_tax', $this->prices_include_tax);
        MarketSetting::setValue('tax.tax_calculation_based_on', $this->tax_calculation_based_on);
        MarketSetting::setValue('tax.default_tax_rate', $this->default_tax_rate);

        MarketSetting::setValue('vendors.auto_approve_vendors', $this->auto_approve_vendors);
        MarketSetting::setValue('vendors.products_require_approval', $this->products_require_approval);
        MarketSetting::setValue('vendors.vendor_can_view_customer_info', $this->vendor_can_view_customer_info);
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

        $this->dispatch('notify', type: 'success', text: 'تنظیمات پیشرفته فروشگاه با موفقیت بروزرسانی شد.');
    }

    public function render()
    {
        return view('market::livewire.admin.market-settings', [
            'locationsList' => ['تهران', 'اصفهان', 'خراسان رضوی', 'فارس', 'آذربایجان شرقی', 'مازندران', 'البرز', 'خوزستان']
        ]);
    }
}
