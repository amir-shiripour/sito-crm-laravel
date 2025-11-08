<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required','string','max:100'], // فارسی
            'name'         => ['nullable','string','max:100','alpha_dash','unique:roles,name'],
            'permissions'  => ['array'],
            'permissions.*'=> ['string'],
        ];
    }
}
