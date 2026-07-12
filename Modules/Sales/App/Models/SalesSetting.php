<?php

namespace Modules\Sales\App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesSetting extends Model
{
    protected $table = 'client_settings';
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, $default = null)
    {
        return cache()->remember("sales.settings.$key", 3600, function () use ($key, $default) {
            return optional(static::query()->where('key', 'sales.' . $key)->first())->value ?? $default;
        });
    }

    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(['key' => 'sales.' . $key], ['value' => $value]);
        cache()->forget("sales.settings.$key");
    }
}
