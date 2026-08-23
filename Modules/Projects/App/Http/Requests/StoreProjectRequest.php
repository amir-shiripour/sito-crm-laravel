<?php

namespace Modules\Projects\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Projects\App\Http\Models\ProjectSetting;
use Modules\Projects\App\Traits\HandlesJalaliDate;

class StoreProjectRequest extends FormRequest
{
    use HandlesJalaliDate;

    public function rules(): array
    {
        $requireClient = ProjectSetting::getBool('projects_require_client', false);
        $requireDates = ProjectSetting::getBool('projects_require_dates', false);

        return [
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:projects_categories,id',
            'client_id' => $requireClient ? 'required|integer|exists:clients,id' : 'nullable|integer',
            'status_id' => 'nullable|exists:projects_statuses,id',
            'start_date' => $requireDates ? 'required|string' : 'nullable|string',
            'end_date' => $requireDates ? 'required|string' : 'nullable|string',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required_with:members|integer|exists:users,id',
            'members.*.role' => 'required_with:members|string|max:50',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('start_date') && $this->filled('end_date')) {
                $startG = $this->convertJalaliDate($this->start_date);
                $endG = $this->convertJalaliDate($this->end_date);

                if ($startG && $endG && $startG > $endG) {
                    $validator->errors()->add('start_date', 'تاریخ شروع پروژه نمی‌تواند دیرتر از تاریخ سررسید باشد.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'start_date.required' => 'وارد کردن تاریخ شروع پروژه الزامی است.',
            'end_date.required' => 'وارد کردن تاریخ پایان / سررسید پروژه الزامی است.',
            'title.required' => 'وارد کردن عنوان پروژه الزامی است.',
            'category_id.required' => 'انتخاب دسته‌بندی پروژه الزامی است.',
            'client_id.required' => 'انتخاب مشتری الزامی است.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
