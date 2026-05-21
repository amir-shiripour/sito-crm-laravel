<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Morilog\Jalali\Jalalian;
use Illuminate\Validation\Rule;
use Modules\Accounting\App\Models\Cheque; // Import the Cheque model

class UpdateChequeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Directly get the Cheque model instance from the route.
        // This is the most reliable way in Laravel.
        $cheque = $this->route('cheque');

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
                // The ignore rule needs the model instance or its ID.
                // Passing the model instance is often more robust.
                Rule::unique('cheques')->ignore($cheque),
            ],
            'sayyad_id' => 'nullable|string|size:16',
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'payee_name' => 'required|string|max:255', // Re-enabled
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
            'payee_name.required' => 'فیلد نام دریافت کننده الزامی است.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convert amount: remove commas and translate Persian/Arabic numerals
        if ($this->filled('amount')) {
            $this->merge([
                'amount' => $this->convertNumbersToEnglish($this->amount),
            ]);
        }

        // Convert dates from Persian (Jalali) to Gregorian
        if ($this->filled('issue_date')) {
            $this->merge([
                'issue_date' => $this->convertDateToGregorian($this->issue_date),
            ]);
        }

        if ($this->filled('due_date')) {
            $this->merge([
                'due_date' => $this->convertDateToGregorian($this->due_date),
            ]);
        }
    }

    /**
     * Convert Persian/Arabic numbers to English and remove commas.
     *
     * @param  string  $string
     * @return string
     */
    private function convertNumbersToEnglish($string)
    {
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
            $date = str_replace('-', '/', $date);
            return Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return 'invalid_date';
        }
    }
}
