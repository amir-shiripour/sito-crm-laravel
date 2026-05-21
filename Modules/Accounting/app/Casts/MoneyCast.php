<?php

namespace Modules\Accounting\App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCast implements CastsAttributes
{
    /**
     * Get the display currency unit from config.
     *
     * @return string 'toman' or 'rial'
     */
    protected function getDisplayUnit(): string
    {
        // Assuming the global setting is stored in 'settings.currency.unit'
        // You can change this to your actual config path e.g., 'config.app.currency'
        return strtolower(config('settings.currency.unit', 'rial'));
    }

    /**
     * Cast the stored value to the display format. (READ from DB)
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value  The value from the database (always in RIAL)
     * @param  array  $attributes
     * @return string
     */
    public function get($model, $key, $value, $attributes): string
    {
        if (is_null($value)) {
            return '0';
        }

        $displayValue = $value;
        $unit = $this->getDisplayUnit();
        $unitLabel = 'ریال';

        if ($unit === 'toman') {
            $displayValue = $value / 10;
            $unitLabel = 'تومان';
        }

        return number_format($displayValue) . ' ' . $unitLabel;
    }

    /**
     * Prepare the given value for storage. (WRITE to DB)
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value  The value from user input
     * @param  array  $attributes
     * @return int
     */
    public function set($model, $key, $value, $attributes): int
    {
        // Remove any formatting like commas
        $value = preg_replace('/[^0-9.]/', '', $value);

        if ($this->getDisplayUnit() === 'toman') {
            $value *= 10; // Convert Toman to Rial for storage
        }

        return (int) $value;
    }
}
