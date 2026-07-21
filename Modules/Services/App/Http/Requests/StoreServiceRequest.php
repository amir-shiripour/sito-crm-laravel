<?php

namespace Modules\Services\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('services.create');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:services,code',
            'category_id' => 'nullable|exists:service_categories,id',
            'template_id' => 'nullable|exists:service_templates,id',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:services_statuses,id',
            'base_price' => 'nullable|integer|min:0',
            'setup_fee' => 'nullable|integer|min:0',

            // فیلدهای فروش واحدی
            'has_unit_pricing' => 'nullable|boolean',
            'unit_name' => 'nullable|string|max:100',
            'unit_price' => 'nullable|integer|min:0',

            'billing_type' => 'nullable|in:one_time,recurring',
            'recurring_period' => 'nullable|in:monthly,quarterly,semi_annual,annual',
            'custom_period_days' => 'nullable|integer|min:1',
            'renewal_reminder_days' => 'nullable|integer|min:0',
            'auto_renewal' => 'nullable|boolean',

            'renewal_prices' => 'nullable|array',
            'renewal_prices.*' => 'nullable|numeric|min:0',

            // Custom fields validation
            'custom_fields' => 'nullable|array',
            'custom_fields.*.id' => 'nullable|integer',
            'custom_fields.*.label' => 'required|string|max:255',
            'custom_fields.*.type' => 'required|in:text,textarea,number,date,datetime,select,multiselect,checkbox,radio,email,url,phone,file',
            'custom_fields.*.options_text' => 'nullable|string',
            'custom_fields.*.is_required' => 'nullable|boolean',
            'custom_fields.*.show_in_invoice' => 'nullable|boolean',
            'custom_fields.*.has_pricing' => 'nullable|boolean',
            'custom_fields.*.pricing_type' => 'nullable|in:fixed,percentage',
            'custom_fields.*.pricing_amount' => 'nullable|numeric|min:0',
            'custom_fields.*.sort_order' => 'nullable|integer|min:0',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('billing_type')) {
            $this->merge(['billing_type' => 'one_time']);
        }
    }
}
