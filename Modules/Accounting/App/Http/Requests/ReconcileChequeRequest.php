<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Morilog\Jalali\Jalalian;
use Modules\Accounting\App\Models\Bank;

class ReconcileChequeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bank_id' => 'required|exists:accounting_banks,id',
            'status' => 'required|in:passed,returned',
            'reconciliation_date' => 'required|date_format:Y-m-d',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->filled('reconciliation_date')) {
            $this->convertDateToGregorian('reconciliation_date');
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cheque = $this->route('cheque');
            $status = $this->input('status');
            $bankId = $this->input('bank_id');

            if ($cheque && $status === 'passed' && $cheque->type === 'issued' && $bankId) {
                $bank = Bank::find($bankId);
                if ($bank && $bank->balance < $cheque->amount) {
                    $validator->errors()->add('bank_id', 'موجودی حساب برای پرداخت این چک کافی نمی‌باشد. موجودی فعلی: ' . number_format($bank->balance) . ' تومان.');
                }
            }
        });
    }

    /**
     * Converts a Jalali date string to a Gregorian date string.
     *
     * @param string $field The name of the input field.
     * @return void
     */
    private function convertDateToGregorian(string $field): void
    {
        $date = $this->input($field);
        // Check if the date is already in Gregorian format (Y-m-d)
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                // Standardize Persian/Arabic digits to English
                $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                $arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                $englishDate = str_replace($persianDigits, $englishDigits, $date);
                $englishDate = str_replace($arabicDigits, $englishDigits, $englishDate);

                // Standardize separator to '/'
                $englishDate = str_replace('-', '/', $englishDate);

                // Convert to Gregorian
                $gregorianDate = Jalalian::fromFormat('Y/m/d', $englishDate)->toCarbon();
                $this->merge([$field => $gregorianDate->format('Y-m-d')]);
            } catch (\Exception $e) {
                // If conversion fails, set an invalid date to trigger validation error
                $this->merge([$field => 'invalid_date']);
            }
        }
    }


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Ensure the cheque is not already reconciled
        if ($this->route('cheque') && $this->route('cheque')->isReconciled()) {
            return false;
        }
        return true;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'reconciliation_date.date_format' => 'فرمت تاریخ نامعتبر است.',
            'authorize' => 'این چک قبلا وصول شده و امکان تغییر وضعیت آن وجود ندارد.',
        ];
    }
}
