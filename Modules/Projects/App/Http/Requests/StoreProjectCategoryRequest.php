<?php

namespace Modules\Projects\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'icon' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,webp|max:2048',
            'remove_icon' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
