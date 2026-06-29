<?php

namespace Modules\ContractForge\App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractSetting extends Model
{
    protected $table = 'contract_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if ($setting) {
            $val = $setting->value;
            // check if json
            $decoded = json_decode($val, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $val;
        }
        return $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value): void
    {
        $val = is_array($value) ? json_encode($value) : (string) $value;
        static::updateOrCreate(['key' => $key], ['value' => $val]);
    }
}
