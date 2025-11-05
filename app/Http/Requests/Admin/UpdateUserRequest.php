<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        $id = $this->route('user')->id ?? null;

        return [
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($id)],
            'password' => ['nullable','string','min:8','confirmed'],
            'roles'    => ['nullable','array'],
            'roles.*'  => ['string','distinct','exists:roles,name'],
        ];
    }
}
