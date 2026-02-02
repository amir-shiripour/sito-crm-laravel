<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PropertySetting extends Model
{
    protected $table = 'property_settings';
    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        return Cache::rememberForever("property_setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set($key, $value)
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("property_setting_{$key}");
        return $setting;
    }
}
