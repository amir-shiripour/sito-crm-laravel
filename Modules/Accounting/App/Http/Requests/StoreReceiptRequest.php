<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $mergeData = [];

        if ($this->has('document_date')) {
            try {
                $dateString = explode(' ', $this->document_date)[0];
                $mergeData['document_date'] = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $dateString)->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        if ($this->has('cheque.due_date')) {
            try {
                $dateString = explode(' ', $this->input('cheque.due_date'))[0];
                $cheque = $this->input('cheque');
                $cheque['due_date'] = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $dateString)->toCarbon()->format('Y-m-d');
                $mergeData['cheque'] = $cheque;
            } catch (\Exception $e) {}
        }

        if ($this->has('amount')) {
            $mergeData['amount'] = str_replace(',', '', $this->amount);
        }

        if ($this->has('cheque.amount')) {
            $cheque = $mergeData['cheque'] ?? $this->input('cheque');
            $cheque['amount'] = str_replace(',', '', $cheque['amount']);
            $mergeData['cheque'] = $cheque;
        }

        $this->merge($mergeData);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'invoice_id' => 'nullable|exists:accounting_invoices,id',
            'payment_method' => 'required|in:cash,cheque',
            'description' => 'required|string|max:1000',
        ];

        if ($this->input('payment_method') === 'cash') {
            $rules = array_merge($rules, [
                'amount' => 'required|string',
                'document_date' => 'required|string',
                'fund_account_id' => 'required|exists:accounting_fund_accounts,id',
                'category_id' => 'nullable|required_without:invoice_id|exists:accounting_categories,id',
            ]);
        }

        if ($this->input('payment_method') === 'cheque') {
            $rules = array_merge($rules, [
                'cheque_id' => 'required|exists:accounting_cheques,id',
                'document_date' => 'required|string',
                'category_id' => 'nullable|required_without:invoice_id|exists:accounting_categories,id',
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'انتخاب روش پرداخت الزامی است.',
            'amount.required' => 'وارد کردن مبلغ الزامی است.',
            'document_date.required' => 'وارد کردن تاریخ الزامی است.',
            'fund_account_id.required' => 'انتخاب حساب واریزی الزامی است.',
            'category_id.required_without' => 'انتخاب طرف حساب برای رسیدهای عمومی الزامی است.',
            'cheque_id.required' => 'انتخاب چک دریافتی الزامی است.',
            'cheque_id.exists' => 'چک انتخاب شده معتبر نیست.',
        ];
    }
}
