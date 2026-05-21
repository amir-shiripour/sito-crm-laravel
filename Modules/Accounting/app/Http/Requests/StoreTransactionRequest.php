<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Accounting\App\Models\Bank;
use Illuminate\Validation\Validator;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('accounting.transactions.create');
    }

    protected function prepareForValidation()
    {
        $amount = $this->input('amount');

        if ($amount) {
            // Convert Persian and Arabic numbers to English
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

            $amount = str_replace($persian, $english, $amount);
            $amount = str_replace($arabic, $english, $amount);

            // Remove commas
            $amount = str_replace(',', '', $amount);
        }

        $this->merge([
            'amount' => $amount,
            'type' => 'transfer', // Ensure type is always transfer
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:transfer',
            'from_bank_id' => 'required|exists:accounting_banks,id',
            'to_bank_id' => 'required|exists:accounting_banks,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'انتخاب نوع تراکنش الزامی است.',
            'type.in' => 'نوع تراکنش معتبر نمی باشد.',
            'from_bank_id.required' => 'انتخاب حساب مبدا الزامی است.',
            'from_bank_id.exists' => 'حساب مبدا انتخاب شده معتبر نمی باشد.',
            'to_bank_id.required' => 'انتخاب حساب مقصد الزامی است.',
            'to_bank_id.exists' => 'حساب مقصد انتخاب شده معتبر نمی باشد.',
            'amount.required' => 'وارد کردن مبلغ تراکنش الزامی است.',
            'amount.numeric' => 'مبلغ تراکنش باید عددی باشد.',
            'amount.min' => 'مبلغ تراکنش باید حداقل ۱ باشد.',
            'description.required' => 'وارد کردن توضیحات الزامی می باشد.',
            'description.string' => 'توضیحات باید متن باشد.',
            'description.max' => 'توضیحات نمی تواند بیشتر از ۵۰۰ کاراکتر باشد.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $amount = $this->input('amount');
            $fromBankId = $this->input('from_bank_id');
            $toBankId = $this->input('to_bank_id');

            // Check if from_bank_id and to_bank_id are the same for transfer
            if ($fromBankId && $toBankId && $fromBankId == $toBankId) {
                $validator->errors()->add('to_bank_id', 'حساب مبدا و مقصد نمی توانند یکسان باشند.');
            }

            // Check for sufficient funds
            if ($fromBankId && is_numeric($amount)) {
                $fromBank = Bank::find($fromBankId);
                // Cast amount to float to compare with float balance correctly
                if (!$fromBank || (float)$fromBank->getRawOriginal('current_balance') < (float)$amount) {
                    $validator->errors()->add('amount', 'موجودی حساب مبدا برای این تراکنش کافی نمی باشد.');
                }
            }
        });
    }
}
