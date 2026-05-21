<?php

namespace Modules\Accounting\App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidIban implements Rule
{
    public function passes($attribute, $value): bool
    {
        // Must be 26 characters long, start with 'IR' (case-insensitive), followed by 24 digits.
        return preg_match('/^ir[0-9]{24}$/i', $value) === 1;
    }

    public function message(): string
    {
        return 'فرمت شماره شبا (IBAN) صحیح نیست. باید با IR شروع شده و ۲۴ رقم داشته باشد.';
    }
}
