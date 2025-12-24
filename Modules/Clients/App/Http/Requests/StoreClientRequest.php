<?php
namespace Modules\Clients\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->can('clients.create');
    }

    public function rules()
    {
        return [
            'name'=>'required|string|max:255',
            'email'=>'nullable|email|unique:clients,email',
            'phone'=>'nullable|string',
            'notes'=>'nullable|string',
        ];
    }
}
