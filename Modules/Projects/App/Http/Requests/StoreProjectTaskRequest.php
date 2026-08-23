<?php

namespace Modules\Projects\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'phase_id' => 'nullable|integer|exists:projects_phases,id',
            'group_name' => 'nullable|string|max:120',
            'description' => 'nullable|string',
            'due_date' => 'nullable|string',
            'status_id' => 'nullable|exists:projects_statuses,id',
            'assigned_to' => 'required|integer|exists:users,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'checklist' => 'nullable|array',
            'checklist.*.id' => 'nullable|integer',
            'checklist.*.title' => 'required_with:checklist|string|max:255',
            'checklist.*.is_done' => 'nullable|boolean',
            'checklist.*.due_date' => 'nullable|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان گروه',
            'assigned_to' => 'مدیر گروه',
            'phase_id' => 'فاز پروژه',
            'due_date' => 'تاریخ سررسید',
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.required' => 'انتخاب مدیر گروه الزامی است.',
            'title.required' => 'وارد کردن عنوان گروه الزامی است.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
