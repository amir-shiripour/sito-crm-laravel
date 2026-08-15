<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Morilog\Jalali\Jalalian;

class StoreChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->issue_date) {
            try {
                // Strip any time part or extra whitespace (e.g. "1403/05/01 00:00:00" → "1403/05/01")
                $issueDate = trim(explode(' ', $this->issue_date)[0]);
                $issueDate = preg_replace('/[^0-9\/]/', '', $issueDate);
                $this->merge(['issue_date' => Jalalian::fromFormat('Y/m/d', $issueDate)->toCarbon()->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Leave as-is; validation will catch the bad format
            }
        }
        if ($this->due_date) {
            try {
                $dueDate = trim(explode(' ', $this->due_date)[0]);
                $dueDate = preg_replace('/[^0-9\/]/', '', $dueDate);
                $this->merge(['due_date' => Jalalian::fromFormat('Y/m/d', $dueDate)->toCarbon()->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Leave as-is; validation will catch the bad format
            }
        }
        if ($this->amount) {
            $this->merge(['amount' => preg_replace('/[^0-9]/', '', $this->amount)]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['receivable', 'payable'])],
            'client_id' => ['required', 'exists:clients,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payee_name' => ['required', 'string', 'max:255'],
            'cheque_number' => ['required', 'string', 'max:255', Rule::unique('accounting_cheques', 'cheque_number')],
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:issue_date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'انتخاب مشتری مرتبط الزامی است.',
            'payee_name.required' => 'نام صادرکننده/در وجه الزامی است.',
            'cheque_number.required' => 'شماره چک/صیادی الزامی است.',
            'cheque_number.unique' => 'این شماره چک قبلاً در سیستم ثبت شده است.',
        ];
    }
}
