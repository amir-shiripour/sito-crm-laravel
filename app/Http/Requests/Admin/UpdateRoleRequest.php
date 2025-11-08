<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        $role = $this->route('role');
        return [
            'display_name' => ['required','string','max:100'],
            'name'         => ['nullable','string','max:100','alpha_dash', Rule::unique('roles','name')->ignore($role?->id)],
            'permissions'  => ['array'],
            'permissions.*'=> ['string'],
        ];
    }
}
