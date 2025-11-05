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
            'name'         => ['required','string','max:100', Rule::unique('roles','name')->where('guard_name','web')->ignore($role?->id)],
            'permissions'  => ['nullable','array'],
            'permissions.*'=> ['string','distinct','exists:permissions,name'],
        ];
    }
}
