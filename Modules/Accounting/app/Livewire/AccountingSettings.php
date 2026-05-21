<?php

namespace Modules\Accounting\App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\Category;
use Illuminate\Support\Facades\Module;
use Illuminate\Support\Facades\Storage;

class AccountingSettings extends Component
{
    use WithFileUploads;

    // All other properties from before...
    public string $numbering_mode = 'auto';
    public bool $numbering_include_year = true;
    public string $numbering_prefix = 'P';
    public string $numbering_separator = '-';
    public string $numbering_length = '4';
    public bool $tax_enabled = false;
    public string $tax_percentage = '0';

    // Rounding settings
    public string $rounding_mode = 'none'; // none, up, down
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

    // New property for negative balance
    public bool $allow_negative_balance = false;

    // Invoice appearance properties
    public string $invoice_template = 'standard';
    public $stamp_signature_image_file; // For file upload
    public string $stamp_signature_image = ''; // For storing path
    public ?string $stamp_signature_width = ''; // For storing width

    public function mount(): void
    {
        // Decode JSON values after fetching from DB
        $settings = AccountingSetting::all()->pluck('value', 'key')->map(function ($value) {
            return json_decode($value, true);
        });

        // شماره گذاری
        $this->numbering_mode = $settings->get('numbering.mode') ?? 'auto';
        $this->numbering_include_year = (bool) ($settings->get('numbering.include_year') ?? true);
        $this->numbering_prefix = $settings->get('numbering.prefix') ?? 'P';
        $this->numbering_separator = $settings->get('numbering.separator') ?? '-';
        $this->numbering_length = $settings->get('numbering.length') ?? '4';

        // مالیات و رند کردن
        $this->tax_enabled = (bool) ($settings->get('tax.enabled') ?? false);
        $this->tax_percentage = $settings->get('tax.percentage') ?? '0';
        $this->rounding_mode = $settings->get('tax.rounding_mode') ?? 'none';
        $this->rounding_amount = $settings->get('tax.rounding_amount') ?? '1000';

        // ظاهر (اطلاعات فروشنده)
        $this->seller_name = $settings->get('appearance.seller_name') ?? '';
        $this->economic_number = $settings->get('appearance.economic_number') ?? '';
        $this->registration_number = $settings->get('appearance.registration_number') ?? '';
        $this->national_id = $settings->get('appearance.national_id') ?? '';
        $this->province_city = $settings->get('appearance.province_city') ?? '';
        $this->address = $settings->get('appearance.address') ?? '';
        $this->postal_code = $settings->get('appearance.postal_code') ?? '';
        $this->phone_fax = $settings->get('appearance.phone_fax') ?? '';
        $this->custom_fields = $settings->get('appearance.custom_fields') ?? [];

        // قالب فاکتور
        $this->invoice_template = $settings->get('appearance.invoice_template') ?? 'standard';
        $this->stamp_signature_image = $settings->get('appearance.stamp_signature_image') ?? '';
        $this->stamp_signature_width = $settings->get('appearance.stamp_signature_width') ?? '';

        // واحدها
        $this->units = $settings->get('units.list') ?? ['عدد', 'کیلوگرم', 'متر', 'ساعت'];

        // بانکی
        $this->allow_negative_balance = (bool) ($settings->get('banking.allow_negative_balance') ?? false);

        // Load expense categories from the database
        $this->loadExpenseCategories();
    }

    protected function loadExpenseCategories(): void
    {
        $this->expenseCategories = Category::where('type', 'expense')
            ->orderBy('is_system', 'desc') // Show system categories first
            ->orderBy('title')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'is_system' => (bool)$category->is_system,
                ];
            })->toArray();
    }

    public function addCategory(): void
    {
        $this->expenseCategories[] = ['id' => null, 'title' => '', 'is_system' => false];
    }

    public function removeCategory(int $index): void
    {
        $category = $this->expenseCategories[$index];

        // Prevent deletion of system categories
        if ($category['is_system']) {
            $this->dispatch('notify', ['type' => 'error', 'text' => 'امکان حذف دسته‌بندی سیستمی وجود ندارد.']);
            return;
        }

        if (!empty($category['id'])) {
            Category::find($category['id'])->delete();
        }

        unset($this->expenseCategories[$index]);
        $this->expenseCategories = array_values($this->expenseCategories);
    }

    public function removeStampImage(): void
    {
        if ($this->stamp_signature_image) {
            Storage::disk('public')->delete($this->stamp_signature_image);
            $this->stamp_signature_image = '';

            // Immediately save the removal to DB
            AccountingSetting::setValues([
                'appearance.stamp_signature_image' => ''
            ]);

            $this->dispatch('notify', ['type' => 'success', 'text' => 'تصویر مهر و امضا حذف شد.']);
        }
    }

    public function saveSettings(): void
    {
        $this->validate([
            'expenseCategories.*.title' => 'required|string|max:255',
            'units.*' => 'required|string|max:255',
            'tax_percentage' => 'numeric|min:0|max:100',
            'rounding_mode' => 'required|in:none,up,down',
            'rounding_amount' => 'required_unless:rounding_mode,none|numeric|min:1',
            'numbering_prefix' => 'nullable|string|max:10',
            'numbering_separator' => 'nullable|string|max:3',
            'numbering_length' => 'required|integer|min:1|max:10',
            'allow_negative_balance' => 'boolean',
            'invoice_template' => 'required|in:standard,official',
            'stamp_signature_image_file' => 'nullable|image|max:2048', // 2MB Max
            'stamp_signature_width' => 'nullable|integer|min:50|max:500',
        ]);

        if ($this->stamp_signature_image_file) {
            // Delete old if exists
            if ($this->stamp_signature_image) {
                Storage::disk('public')->delete($this->stamp_signature_image);
            }
            $this->stamp_signature_image = $this->stamp_signature_image_file->store('invoice_assets', 'public');
            // Reset file input
            $this->stamp_signature_image_file = null;
        }

        // 1. Save general settings
        $settingsToSave = [
            'numbering.mode' => $this->numbering_mode,
            'numbering.include_year' => $this->numbering_include_year,
            'numbering.prefix' => $this->numbering_prefix,
            'numbering.separator' => $this->numbering_separator,
            'numbering.length' => $this->numbering_length,
            'tax.enabled' => $this->tax_enabled,
            'tax.percentage' => $this->tax_percentage,
            'tax.rounding_mode' => $this->rounding_mode,
            'tax.rounding_amount' => $this->rounding_amount,
            'appearance.seller_name' => $this->seller_name,
            'appearance.economic_number' => $this->economic_number,
            'appearance.registration_number' => $this->registration_number,
            'appearance.national_id' => $this->national_id,
            'appearance.province_city' => $this->province_city,
            'appearance.address' => $this->address,
            'appearance.postal_code' => $this->postal_code,
            'appearance.phone_fax' => $this->phone_fax,
            'appearance.custom_fields' => array_values(array_filter($this->custom_fields, fn($field) => !empty($field['key']))),
            'appearance.invoice_template' => $this->invoice_template,
            'appearance.stamp_signature_image' => $this->stamp_signature_image,
            'appearance.stamp_signature_width' => $this->stamp_signature_width,
            'units.list' => array_values(array_filter($this->units)),
            'banking.allow_negative_balance' => $this->allow_negative_balance,
        ];
        AccountingSetting::setValues($settingsToSave);

        // 2. Save expense categories
        foreach ($this->expenseCategories as $categoryData) {
            if (empty($categoryData['title']) || $categoryData['is_system']) {
                continue;
            }
            Category::updateOrCreate(
                ['id' => $categoryData['id']],
                ['title' => $categoryData['title'], 'type' => 'expense', 'status' => 1, 'is_system' => false]
            );
        }

        $this->loadExpenseCategories(); // Reload to reflect changes
        $this->dispatch('notify', ['type' => 'success', 'text' => 'تنظیمات با موفقیت ذخیره شد.']);
    }

    // Other methods like addUnit, removeUnit, etc.
    public function addUnit(): void { $this->units[] = ''; }
    public function removeUnit(int $index): void { unset($this->units[$index]); $this->units = array_values($this->units); }
    public function addCustomField(): void { $this->custom_fields[] = ['key' => '', 'value' => '']; }
    public function removeCustomField(int $index): void { unset($this->custom_fields[$index]); $this->custom_fields = array_values($this->custom_fields); }


    /**
     * Render the component.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('accounting::livewire.accounting-settings')
            ->layout('layouts.user');
    }
}
