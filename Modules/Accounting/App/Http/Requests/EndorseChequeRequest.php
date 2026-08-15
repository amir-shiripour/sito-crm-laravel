<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EndorseChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'debit_category_id' => ['required', 'exists:accounting_categories,id'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }
}
