<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Accounting\App\Services\CurrencyService;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // ... (Omitted for brevity, no changes here)
    }

    public function rules(): array
    {
        $invoiceId = $this->route('invoice')->id;

        return [
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => ['nullable', 'string', 'max:255', Rule::unique('accounting_invoices')->ignore($invoiceId)],
            'issue_date' => 'required|date_format:Y-m-d',
            'due_date' => 'required|date_format:Y-m-d|after_or_equal:issue_date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,pending_review,unpaid,partially_paid,paid,overdue,cancelled,refunded,bad_debt',
            'notes' => 'nullable|string|max:1000',

            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:1000',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_type' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            // ... (Omitted for brevity, no changes here)
        ];
    }
}
