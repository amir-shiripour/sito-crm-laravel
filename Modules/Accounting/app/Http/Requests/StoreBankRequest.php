<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankRequest extends FormRequest
{
    // Define the allowed colors to prevent arbitrary input
    private $allowedColors = [
        '#333333', '#e1a6ad', '#ffd100', '#6558b1',
        '#00ab84', '#fe0c3b', '#4e91e6'
    ];

    public function authorize(): bool
    {
        return auth()->user()->can('accounting.banks.create');
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'account_number' => $this->account_number ? preg_replace('/[^0-9-]/', '', $this->account_number) : null,
            'card_number' => $this->card_number ? preg_replace('/[^0-9]/', '', $this->card_number) : null,
            'iban' => $this->iban ? preg_replace('/[^A-Za-z0-9]/', '', $this->iban) : null,
            'balance' => $this->balance ? preg_replace('/[^0-9]/', '', $this->balance) : 0,
            'status' => $this->filled('status'),
        ]);
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'balance' => 'nullable|numeric|min:0',
            'account_number' => 'nullable|string|max:50|unique:accounting_banks,account_number',
            'card_number' => 'nullable|string|digits:16|unique:accounting_banks,card_number',
            'iban' => 'nullable|string|size:26|unique:accounting_banks,iban',
            'status' => 'boolean',
            'color' => ['nullable', 'string', Rule::in($this->allowedColors)],
        ];
    }

    public function messages(): array
    {
        return [
            'bank_name.required' => 'وارد کردن نام بانک الزامی است.',
            'account_holder_name.required' => 'وارد کردن نام صاحب حساب الزامی است.',
            'account_number.unique' => 'این شماره حساب قبلا ثبت شده است.',
            'card_number.digits' => 'شماره کارت باید ۱۶ رقم باشد.',
            'card_number.unique' => 'این شماره کارت قبلا ثبت شده است.',
            'iban.size' => 'شماره شبا (IBAN) باید ۲۶ کاراکتر باشد (شامل IR).',
            'iban.unique' => 'این شماره شبا قبلا ثبت شده است.',
            'color.in' => 'رنگ انتخاب شده معتبر نیست.',
        ];
    }
}
