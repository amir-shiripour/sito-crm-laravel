<?php

namespace Modules\Accounting\App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidIranianCardNumber implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (!preg_match('/^[0-9]{16}$/', $value)) {
            return false;
        }

        $sum = 0;
        $alternate = false;
        for ($i = strlen($value) - 1; $i >= 0; $i--) {
            $digit = (int) $value[$i];
            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }
            $sum += $digit;
            $alternate = !$alternate;
        }

        return ($sum % 10 === 0);
    }

    public function message(): string
    {
        return 'شماره کارت بانکی وارد شده معتبر نیست.';
    }
}
