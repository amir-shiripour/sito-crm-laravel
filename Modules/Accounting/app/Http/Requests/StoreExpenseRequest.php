<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Accounting\App\Models\Category;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('accounting.expenses.create');
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'amount' => $this->amount ? str_replace(',', '', $this->amount) : null,
            'document_date' => $this->document_date ? \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $this->document_date)->toCarbon()->toDateString() : null,
        ]);
    }

    public function rules(): array
    {
        $clientsCategory = Category::where('title', 'مشتریان')->where('is_system', true)->first();
        $clientsCategoryId = $clientsCategory ? $clientsCategory->id : null;

        return [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'document_date' => 'required|date',
            'category_id' => 'required|exists:accounting_categories,id',
            'bank_id' => 'required|exists:accounting_banks,id',
            'client_id' => [
                'nullable',
                Rule::requiredIf($this->category_id == $clientsCategoryId),
                'exists:clients,id'
            ],
            'reference_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'وارد کردن شرح هزینه الزامی است.',
            'amount.required' => 'وارد کردن مبلغ الزامی است.',
            'amount.numeric' => 'مبلغ باید یک عدد معتبر باشد.',
            'document_date.required' => 'وارد کردن تاریخ الزامی است.',
            'category_id.required' => 'انتخاب دسته‌بندی الزامی است.',
            'bank_id.required' => 'انتخاب حساب پرداختی الزامی است.',
            'client_id.required' => 'برای دسته‌بندی "مشتریان"، انتخاب مشتری الزامی است.',
        ];
    }
}
