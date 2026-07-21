<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Models;

use Illuminate\Database\Eloquent\Model;

final class BotSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, $default = null): mixed
    {
        try {
            $setting = self::where('key', $key)->first();
            if ($setting) {
                return $setting->value;
            }
        } catch (\Throwable $e) {
            // Table might not exist yet
        }

        return config("smartbot.{$key}", $default);
    }

    public static function setValue(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}
