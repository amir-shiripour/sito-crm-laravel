<?php

namespace Modules\Accounting\App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\Category;
use Illuminate\Support\Facades\Storage;

class AccountingSettings extends Component
{
    use WithFileUploads;

    // Default Accounts Properties
    public ?int $default_sales_income_category_id = null;
    public ?int $default_receivables_category_id = null;
    public ?int $default_sales_tax_category_id = null;
    public ?int $default_cheques_receivable_category_id = null;
    public ?int $default_cash_fund_id = null;
    public ?int $default_bank_fund_id = null;

    // Lists for dropdowns
    public $incomeCategories = [];
    public $assetCategories = [];
    public $liabilityCategories = [];
    public $fundAccounts = [];

    // All other properties from before...
    public string $currency = 'ریال';
    public string $numbering_mode = 'auto';
    public bool $numbering_include_year = true;
    public string $numbering_prefix = 'P';
    public string $numbering_separator = '-';
    public string $numbering_length = '4';
    public string $proforma_numbering_mode = 'auto';
    public bool $proforma_numbering_include_year = true;
    public string $proforma_numbering_prefix = 'PF';
    public string $proforma_numbering_separator = '-';
    public string $proforma_numbering_length = '4';
    public bool $tax_enabled = false;
    public string $tax_percentage = '0';
    public string $rounding_mode = 'none';
    public string $rounding_amount = '1000';
    public string $seller_name = '';
    public string $economic_number = '';
    public string $registration_number = '';
    public string $national_id = '';
    public string $province_city = '';
    public string $address = '';
    public string $postal_code = '';
    public string $phone_fax = '';
    public array $custom_fields = [];
    public array $units = [];
    public array $expenseCategories = [];
    public bool $allow_negative_balance = false;
    public bool $auto_sync_services = true;
    public bool $auto_sync_booking = true;
    public bool $auto_sync_market = true;
    public string $invoice_template = 'standard';
    public $stamp_signature_image_file;
    public string $stamp_signature_image = '';
    public ?string $stamp_signature_width = '';
    public bool $isSuperAdmin = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = $user && ($user->hasRole('super-admin') || $user->hasRole('superadmin'));

        // Load all categories for dropdowns
        $this->incomeCategories = Category::whereIn('type', ['income', 'revenue'])->get();
        $this->assetCategories = Category::where('type', 'asset')->get();
        $this->liabilityCategories = Category::where('type', 'liability')->get();
        $this->fundAccounts = \Modules\Accounting\App\Models\FundAccount::all();

        $settings = AccountingSetting::all()->pluck('value', 'key')->map(function ($value) {
            if ($value === null) return null;
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        });

        // Load Default Accounts
        $this->default_sales_income_category_id = (int)($settings->get('defaults.sales_income_category_id') ?? null);
        $this->default_receivables_category_id = (int)($settings->get('defaults.receivables_category_id') ?? null);
        $this->default_sales_tax_category_id = (int)($settings->get('defaults.sales_tax_category_id') ?? null);
        $this->default_cheques_receivable_category_id = (int)($settings->get('defaults.cheques_receivable_category_id') ?? null);
        $this->default_cash_fund_id = (int)($settings->get('defaults.cash_fund_id') ?? null);
        $this->default_bank_fund_id = (int)($settings->get('defaults.bank_fund_id') ?? null);

        // Load General & Numbering Settings
        $this->currency = $settings->get('general.currency') ?? 'ریال';
        $this->numbering_mode = $settings->get('numbering.mode') ?? 'auto';
        $this->numbering_include_year = (bool)($settings->get('numbering.include_year') ?? true);
        $this->numbering_prefix = $settings->get('numbering.prefix') ?? 'P';
        $this->numbering_separator = $settings->get('numbering.separator') ?? '-';
        $this->numbering_length = (string)($settings->get('numbering.length') ?? '4');
        
        $this->proforma_numbering_mode = $settings->get('proforma.numbering_mode') ?? 'auto';
        $this->proforma_numbering_include_year = (bool)($settings->get('proforma.numbering_include_year') ?? true);
        $this->proforma_numbering_prefix = $settings->get('proforma.numbering_prefix') ?? 'PF';
        $this->proforma_numbering_separator = $settings->get('proforma.numbering_separator') ?? '-';
        $this->proforma_numbering_length = (string)($settings->get('proforma.numbering_length') ?? '4');

        $this->tax_enabled = (bool)($settings->get('tax.enabled') ?? false);
        $this->tax_percentage = (string)($settings->get('tax.percentage') ?? '0');
        $this->seller_name = $settings->get('seller.name') ?? '';
        $this->economic_number = $settings->get('seller.economic_number') ?? '';
        $this->registration_number = $settings->get('seller.registration_number') ?? '';
        $this->national_id = $settings->get('seller.national_id') ?? '';
        $this->province_city = $settings->get('seller.province_city') ?? '';
        $this->address = $settings->get('seller.address') ?? '';
        $this->postal_code = $settings->get('seller.postal_code') ?? '';
        $this->phone_fax = $settings->get('seller.phone_fax') ?? '';
        $this->allow_negative_balance = (bool)($settings->get('banking.allow_negative_balance') ?? false);
        $this->auto_sync_services = (bool)($settings->get('integration.auto_sync_services') ?? true);
        $this->auto_sync_booking = (bool)($settings->get('integration.auto_sync_booking') ?? true);
        $this->auto_sync_market = (bool)($settings->get('integration.auto_sync_market') ?? true);
        $this->check_cheque_due_dates = (bool)($settings->get('general.check_cheque_due_dates') ?? true);
    }

    public function saveSettings(): void
    {
        $this->validate([
            'default_sales_income_category_id' => 'nullable|exists:accounting_categories,id',
            'default_receivables_category_id' => 'nullable|exists:accounting_categories,id',
            'default_sales_tax_category_id' => 'nullable|exists:accounting_categories,id',
            'default_cheques_receivable_category_id' => 'nullable|exists:accounting_categories,id',
            'default_cash_fund_id' => 'nullable|exists:accounting_fund_accounts,id',
            'default_bank_fund_id' => 'nullable|exists:accounting_fund_accounts,id',
            'currency' => 'required|string|max:50',
            'seller_name' => 'nullable|string|max:255',
        ]);

        // Save Default Accounts settings
        AccountingSetting::setValue('defaults.sales_income_category_id', $this->default_sales_income_category_id);
        AccountingSetting::setValue('defaults.receivables_category_id', $this->default_receivables_category_id);
        AccountingSetting::setValue('defaults.sales_tax_category_id', $this->default_sales_tax_category_id);
        AccountingSetting::setValue('defaults.cheques_receivable_category_id', $this->default_cheques_receivable_category_id);
        AccountingSetting::setValue('defaults.cash_fund_id', $this->default_cash_fund_id);
        AccountingSetting::setValue('defaults.bank_fund_id', $this->default_bank_fund_id);

        // Save General & Seller settings
        if ($this->isSuperAdmin) {
            AccountingSetting::setValue('general.currency', $this->currency);
        }
        AccountingSetting::setValue('numbering.mode', $this->numbering_mode);
        AccountingSetting::setValue('numbering.prefix', $this->numbering_prefix);
        AccountingSetting::setValue('proforma.numbering_prefix', $this->proforma_numbering_prefix);
        AccountingSetting::setValue('tax.enabled', $this->tax_enabled);
        AccountingSetting::setValue('tax.percentage', $this->tax_percentage);
        AccountingSetting::setValue('seller.name', $this->seller_name);
        AccountingSetting::setValue('seller.economic_number', $this->economic_number);
        AccountingSetting::setValue('seller.address', $this->address);
        AccountingSetting::setValue('banking.allow_negative_balance', $this->allow_negative_balance);
        AccountingSetting::setValue('general.check_cheque_due_dates', $this->check_cheque_due_dates);

        // Save Cross-Module Integration settings
        AccountingSetting::setValue('integration.auto_sync_services', $this->auto_sync_services);
        AccountingSetting::setValue('integration.auto_sync_booking', $this->auto_sync_booking);
        AccountingSetting::setValue('integration.auto_sync_market', $this->auto_sync_market);

        $this->dispatch('notify', ['type' => 'success', 'text' => 'تنظیمات با موفقیت ذخیره شد.']);
    }

    public function render()
    {
        return view('accounting::livewire.accounting-settings')
            ->layout('layouts.user');
    }

    // ... (rest of the existing methods)
}
