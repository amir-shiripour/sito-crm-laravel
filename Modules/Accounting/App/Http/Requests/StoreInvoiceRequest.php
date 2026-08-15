<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Morilog\Jalali\Jalalian;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->issue_date) {
            $issueDate = explode(' ', $this->issue_date)[0];
            $this->merge(['issue_date' => Jalalian::fromFormat('Y/m/d', $issueDate)->toCarbon()->format('Y-m-d')]);
        }
        if ($this->due_date) {
            $dueDate = explode(' ', $this->due_date)[0];
            $this->merge(['due_date' => Jalalian::fromFormat('Y/m/d', $dueDate)->toCarbon()->format('Y-m-d')]);
        }
        if ($this->items && is_string($this->items)) {
            $this->merge(['items' => json_decode($this->items, true)]);
        }
        $this->merge(['status' => 'draft']);
    }

    public function rules(): array
    {
        return [
            'invoice_number' => 'nullable|string|max:255|unique:accounting_invoices,invoice_number',
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date_format:Y-m-d',
            'due_date' => 'required|date_format:Y-m-d|after_or_equal:issue_date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.unit_type' => 'nullable|string|max:255',
        ];
    }
}
