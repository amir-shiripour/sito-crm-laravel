<?php

namespace Modules\Clients\Entities;

use Illuminate\Database\Eloquent\Model;

class ClientSetting extends Model
{
    protected $table = 'client_settings';
    protected $fillable = ['key','value'];

    public static function getValue(string $key, $default = null)
    {
        return cache()->remember("clients.settings.$key", 3600, function () use ($key, $default) {
            return optional(static::query()->where('key',$key)->first())->value ?? $default;
        });
    }

    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(['key'=>$key], ['value'=>$value]);
        cache()->forget("clients.settings.$key");
    }
}
