<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class AccountingSetting extends Model
{
    protected $table = 'accounting_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'json', // Automatically cast to/from JSON
    ];

    /**
     * Get a setting value by key, supporting dot notation.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $primaryKey = array_shift($keys);

        $setting = self::find($primaryKey);

        if (!$setting) {
            return $default;
        }

        // If there's no dot notation, return the whole value.
        if (empty($keys)) {
            return $setting->value;
        }

        // Use Arr::get for dot notation access on the JSON value.
        return Arr::get($setting->value, implode('.', $keys), $default);
    }


    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        return self::get($key, $default);
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @return Model
     */
    public static function setValue(string $key, $value): Model
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
