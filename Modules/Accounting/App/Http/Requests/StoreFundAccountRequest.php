<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFundAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Permissions will be handled later
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'account_number' => $this->account_number ? preg_replace('/[^0-9-]/', '', $this->account_number) : null,
            'card_number' => $this->card_number ? preg_replace('/[^0-9]/', '', $this->card_number) : null,
            'iban' => $this->iban ? preg_replace('/[^A-Za-z0-9]/', '', $this->iban) : null,
            'status' => $this->filled('status'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,cash,gateway',
            'category_id' => 'required|exists:accounting_categories,id',
            'core_gateway_id' => 'nullable|integer',
            'bank_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'card_number' => 'nullable|string|digits:16',
            'iban' => 'nullable|string|max:26', // Can add specific IBAN validation rule if needed
            'currency' => 'nullable|string|max:10',
            'status' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام حساب الزامی است.',
            'type.required' => 'انتخاب نوع حساب الزامی است.',
            'type.in' => 'نوع حساب انتخاب شده معتبر نیست.',
            'category_id.required' => 'انتخاب سرفصل حسابداری الزامی است.',
            'category_id.exists' => 'سرفصل حسابداری انتخاب شده معتبر نیست.',
            'card_number.digits' => 'شماره کارت باید دقیقاً ۱۶ رقم باشد.',
        ];
    }
}
