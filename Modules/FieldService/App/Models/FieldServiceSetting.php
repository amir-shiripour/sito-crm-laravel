<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldServiceSetting extends Model
{
    use HasFactory;

    protected $table = 'field_service_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting || $setting->value === null) {
            return $default;
        }

        $val = $setting->value;
        $decoded = json_decode($val, true);
        if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
            return $decoded;
        }

        return $val;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        $storeValue = is_array($value) || is_object($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storeValue,
                'group' => $group,
            ]
        );
    }
}
