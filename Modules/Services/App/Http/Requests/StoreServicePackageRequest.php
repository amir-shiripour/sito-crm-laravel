<?php

namespace Modules\Services\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
            'discount_type' => 'nullable|string|in:amount,percent',
            'discount_value' => 'nullable|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.custom_service_name' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|integer|min:0',
            'items.*.discount_type' => 'nullable|string|in:amount,percent',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'items.*.billing_period' => 'nullable|string',
            'items.*.custom_fields' => 'nullable',
            'items.*.custom_fields_prices' => 'nullable',
            'items.*.custom_fields_quantities' => 'nullable',
        ];
    }
}
