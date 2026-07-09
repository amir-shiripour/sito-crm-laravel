<?php

namespace Modules\ContentForge\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContentSetting extends Model
{
    protected $table = 'content_settings';
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'general' => [
            'posts_per_page'    => 12,
            'default_theme_key' => 'content',
            'enable_comments'   => 'true',
            'enable_tags'       => 'true',
            'reading_time_wpm'  => 200,
        ],
        'seo' => [
            'auto_generate_description' => 'true',
            'description_length'        => 160,
            'auto_schema_markup'        => 'true',
        ],
        'short_link' => [
            'enabled'     => 'true',
            'prefix'      => 's',
            'code_length' => 6,
        ],
        'editor' => [
            'default_editor' => 'tiptap',
            'enable_ai'      => 'false',
        ]
    ];

    public static function getValue(string $key, $default = null)
    {
        try {
            return Cache::rememberForever("content_setting_{$key}", function () use ($key, $default) {
                $setting = self::where('key', $key)->first();
                if ($setting) {
                    return $setting->value;
                }

                $keys = explode('.', $key);
                $value = self::DEFAULTS;
                foreach ($keys as $k) {
                    if (!isset($value[$k])) {
                        return $default;
                    }
                    $value = $value[$k];
                }
                return $value;
            });
        } catch (\Throwable $e) {
            $keys = explode('.', $key);
            $value = self::DEFAULTS;
            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return $default;
                }
                $value = $value[$k];
            }
            return $value;
        }
    }

    public static function setValue(string $key, $value)
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("content_setting_{$key}");
        return $setting;
    }
}
