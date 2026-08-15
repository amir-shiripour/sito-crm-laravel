<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Assuming permissions are handled elsewhere or temporarily disabled for development
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'title' => ['required', 'string', 'max:255', Rule::unique('accounting_categories')->ignore($categoryId)],
            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],
            'status' => 'nullable|boolean',
            'is_treasury_related' => 'nullable|boolean',
        ];
    }

    /**
     * Get the custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'وارد کردن عنوان دسته بندی الزامی است.',
            'title.string' => 'عنوان دسته بندی باید یک متن معتبر باشد.',
            'title.max' => 'عنوان دسته بندی نمی تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'title.unique' => 'این عنوان دسته بندی قبلا ثبت شده است.',
            'type.required' => 'انتخاب نوع ماهیت الزامی است.',
            'type.in' => 'نوع ماهیت انتخاب شده معتبر نمی باشد.',
            'status.boolean' => 'مقدار وضعیت معتبر نمی باشد.',
            'is_treasury_related.boolean' => 'مقدار فیلد "مرتبط با خزانه‌داری" معتبر نمی باشد.',
        ];
    }
}
