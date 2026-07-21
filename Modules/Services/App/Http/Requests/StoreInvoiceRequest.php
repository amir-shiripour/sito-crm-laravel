<?php

namespace Modules\Services\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Services\App\Http\Models\Invoice;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
    }

    public function rules(): array
    {
        $invoiceId = $this->route('invoice')?->id;

        $rules = [
            'invoice_type' => 'required|string|in:invoice,proforma',
            'invoice_number' => [
                'nullable',
                'string',
                'max:255',
                'unique:service_invoices,invoice_number' . ($invoiceId ? ",{$invoiceId}" : ''),
            ],
            'proforma_invoice_number' => [
                'nullable',
                'string',
                'max:255',
                'unique:service_invoices,proforma_invoice_number' . ($invoiceId ? ",{$invoiceId}" : ''),
            ],
            'issue_date' => 'required|string',
            'due_date' => 'nullable|string',
            'customer_id' => 'required|integer|exists:clients,id',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'status_id' => 'nullable|exists:services_statuses,id',
            'notes' => 'nullable|string',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'extra_discount_type' => 'nullable|in:amount,percent',
            'extra_discount_value' => 'nullable|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.custom_service_name' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|integer|min:0',
            'items.*.discount' => 'nullable|integer|min:0',
            'items.*.billing_period' => 'nullable|string|in:monthly,quarterly,semi_annual,annual',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',

            'items.*.custom_fields' => 'nullable|array',
            'items.*.custom_fields.*' => 'nullable',
            'items.*.custom_fields_prices' => 'nullable|array',
            'items.*.custom_fields_prices.*' => 'nullable|numeric',
            'items.*.custom_fields_discounts' => 'nullable|array',
            'items.*.custom_fields_discounts.*' => 'nullable|numeric',

            // قانون اعتبارسنجی برای مالیات فیلدهای سفارشی
            'items.*.custom_fields_taxes' => 'nullable|array',
            'items.*.custom_fields_taxes.*' => 'nullable|numeric|min:0|max:100',

            'payment_mode' => 'nullable|in:cash,installment',
            'payment_method' => 'nullable|in:online,transfer,pos,installment,cod',
            'gateway' => 'nullable|in:zarinpal,zibal,behpardakht',
            'installment_down_payment' => 'nullable|integer|min:0',
            'installment_steps' => 'nullable|integer|min:0|max:60',
            'installment_interest_rate' => 'nullable|numeric|min:0|max:100',
            'installment_option_id' => 'nullable|string',
            'installment_option_title' => 'nullable|string',
            'installment_due_day' => 'nullable|integer|min:1|max:31',
            'installment_start_date' => 'nullable|string',
            'installment_schedule' => 'nullable|string',
        ];

        if ($this->input('payment_mode') === 'installment') {
            $rules['installment_steps'] = 'required|integer|min:1|max:60';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'invoice_number' => 'شماره فاکتور',
            'proforma_invoice_number' => 'شماره پیش فاکتور',
            'customer_id' => 'مشتری',
            'issue_date' => 'تاریخ صدور',
            'due_date' => 'تاریخ سررسید',
        ];
    }
}
