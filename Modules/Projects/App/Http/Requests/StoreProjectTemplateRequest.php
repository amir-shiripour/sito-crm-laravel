<?php

namespace Modules\Projects\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category_id' => 'nullable|exists:projects_categories,id',
            'source_project_id' => 'nullable|exists:projects,id',
            'structure' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'وارد کردن نام الگو الزامی است.',
            'title.max' => 'نام الگو نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۲۰۰۰ کاراکتر باشد.',
            'category_id.exists' => 'دسته‌بندی انتخاب‌شده معتبر نیست.',
        ];
    }
}
