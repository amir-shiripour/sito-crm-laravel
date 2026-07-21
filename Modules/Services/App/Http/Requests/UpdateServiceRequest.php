<?php

namespace Modules\Services\App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateServiceRequest extends StoreServiceRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('services.edit');
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $serviceId = $this->route('service') ? $this->route('service')->id : null;
        $rules['code'] = ['nullable', 'string', 'max:50', Rule::unique('services', 'code')->ignore($serviceId)];
        unset($rules['override_renewal_prices']);
        unset($rules['override_renewal_prices.*']);

        return $rules;
    }
}
