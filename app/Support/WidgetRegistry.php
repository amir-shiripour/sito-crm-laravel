<?php

namespace App\Support;

class WidgetRegistry
{
    /**
     * ساختار عناصر:
     * [
     *   'key'        => 'client_calls_quick_create',
     *   'label'      => 'ثبت تماس سریع',
     *   'view'       => 'clientcalls::widgets.quick-call',
     *   'permission' => 'client-calls.create', // اختیاری
     *   'group'      => 'تماس‌ها',            // برای گروه‌بندی در UI
     * ]
     */
    protected static array $widgets = [];

    public static function register(string $key, array $definition): void
    {
        $definition = array_merge([
            'key'        => $key,
            'label'      => $key,
            'view'       => null,
            'permission' => null,
            'group'      => 'سایر',
        ], $definition);

        static::$widgets[$key] = $definition;
    }

    public static function all(): array
    {
        return static::$widgets;
    }

    public static function get(string $key, ?callable $default = null): ?array
    {
        if (isset(static::$widgets[$key])) {
            return static::$widgets[$key];
        }

        return $default ? $default() : null;
    }
}
