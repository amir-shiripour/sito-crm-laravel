<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AccountingSetting extends Model
{
    protected $table = 'accounting_settings';
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = self::query()->where('key', $key)->first();
        // هنگام خواندن، نیازی به تغییر نیست چون جیسون به درستی خوانده می شود
        return $setting ? json_decode($setting->value, true) : $default;
    }

    public static function setValues(array $settings): void
    {
        foreach ($settings as $key => $value) {
            // ۴. استفاده از فلگ JSON_UNESCAPED_UNICODE برای ذخیره صحیح حروف فارسی
            $encodedValue = json_encode($value, JSON_UNESCAPED_UNICODE);

            self::updateOrCreate(
                ['key' => $key],
                ['value' => $encodedValue]
            );
        }

        Cache::forget('accounting_settings');
    }
}
