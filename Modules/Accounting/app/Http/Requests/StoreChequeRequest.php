<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Morilog\Jalali\Jalalian;
use Illuminate\Validation\Rule;

class StoreChequeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'client_id' => 'nullable|exists:clients,id',
            'bank_id' => 'nullable|exists:accounting_banks,id',
            'type' => 'required|in:received,issued',
            'amount' => 'required|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'cheque_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cheques')->where(function ($query) {
                    return $query;
                }),
            ],
            'sayyad_id' => 'nullable|string|size:16',
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255', // Changed to nullable
            'payee_name' => 'required|string|max:255', // Added back
            'status' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'cheque_number.unique' => 'این شماره چک قبلا در سیستم ثبت شده است.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convert Persian/Arabic numbers and remove commas from amount
        if ($this->has('amount')) {
            $this->merge([
                'amount' => $this->toEnglishDigits($this->amount),
            ]);
        }

        // Convert Jalali dates to Gregorian
        if ($this->has('issue_date')) {
            $this->merge([
                'issue_date' => $this->convertDateToGregorian($this->issue_date),
            ]);
        }
        if ($this->has('due_date')) {
            $this->merge([
                'due_date' => $this->convertDateToGregorian($this->due_date),
            ]);
        }

        // Set default status based on type
        if ($this->type === 'issued' && !$this->has('status')) {
            $this->merge(['status' => 'issued']);
        } elseif ($this->type === 'received' && !$this->has('status')) {
            $this->merge(['status' => 'registered']);
        }
    }

    /**
     * Converts Persian/Arabic digits to English and removes commas.
     *
     * @param string|null $string
     * @return string
     */
    private function toEnglishDigits($string): string
    {
        if ($string === null) {
            return '';
        }
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $string = str_replace($persian, $english, $string);
        $string = str_replace($arabic, $english, $string);
        $string = str_replace(',', '', $string);

        return $string;
    }

    /**
     * Convert Persian date to Gregorian date.
     *
     * @param  string  $date
     * @return string|null
     */
    private function convertDateToGregorian($date)
    {
        try {
            // Standardize separator to '/'
            $date = str_replace('-', '/', $date);
            return Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return 'invalid_date'; // Return a string that will fail date validation
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Assuming authorization is handled by middleware
    }
}
