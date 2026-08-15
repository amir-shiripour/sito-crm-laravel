<?php

namespace Modules\Accounting\App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\App\Services\CurrencyService;

class MoneyCast implements CastsAttributes
{
    /**
     * Get the display currency unit from config.
     *
     * @return string 'toman' or 'rial'
     */
    protected function getDisplayUnit(): string
    {
        return strtolower(config('settings.currency.unit', 'rial'));
    }

    /**
     * Cast the stored value to the display format. (READ from DB)
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value The value from the database (always in RIAL)
     * @param array $attributes
     * @return string
     */
    public function get($model, $key, $value, $attributes): string
    {
        if (is_null($value)) {
            return '0';
        }

        return CurrencyService::formatWithSuffix($value);
    }

    /**
     * Prepare the given value for storage. (WRITE to DB)
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value The value from user input
     * @param array $attributes
     * @return int
     */
    public function set($model, $key, $value, $attributes): int
    {
        return (int)CurrencyService::convertToBaseRial($value);
    }
}
