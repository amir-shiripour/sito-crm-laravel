<?php
namespace Modules\Clients\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Clients\Entities\Client;

class UpdateClientRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->can('clients.edit');
    }

    public function rules()
    {
        $clientId = $this->route('client')->id;

        return [
            'name'=>'required|string|max:255',
            'email'=>"nullable|email|unique:clients,email,{$clientId}",
            'phone'=>'nullable|string',
            'notes'=>'nullable|string',
        ];
    }
}
