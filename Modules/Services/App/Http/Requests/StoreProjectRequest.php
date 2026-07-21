<?php

namespace Modules\Services\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('project')) {
            $project = $this->route('project');
            return !$project->isReadonly()
                && $this->user()->can('services.projects.manage');
        }

        return $this->user()->can('services.projects.create');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'service_id' => 'nullable|exists:services,id',
            'customer_id' => 'required|exists:users,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'status_id' => 'nullable|exists:services_statuses,id',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|integer|min:0',
            'priority' => 'required|in:low,medium,high,urgent',
            'progress' => 'nullable|integer|min:0|max:100',
        ];
    }
}
