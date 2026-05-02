<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MarketSetting extends Model
{
    protected $table = 'market_settings';
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, $default = null)
    {
        return Cache::rememberForever("market_setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function setValue(string $key, $value)
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("market_setting_{$key}");
        return $setting;
    }
}
