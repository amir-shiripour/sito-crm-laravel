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
     *   'module'     => 'ClientCalls',         // نام ماژول اختیاری جهت بررسی فعال بودن
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
            'module'     => null,
        ], $definition);

        static::$widgets[$key] = $definition;
    }

    public static function all(): array
    {
        return array_filter(static::$widgets, function ($widget) {
            if (!empty($widget['module'])) {
                return static::isModuleActive($widget['module']);
            }
            return true;
        });
    }

    public static function isModuleActive(string $moduleName): bool
    {
        // ۱. بررسی از طریق Nwidart\Modules
        if (class_exists(\Nwidart\Modules\Facades\Module::class)) {
            try {
                if (!\Nwidart\Modules\Facades\Module::has($moduleName) || !\Nwidart\Modules\Facades\Module::isEnabled($moduleName)) {
                    return false;
                }
            } catch (\Throwable $e) {
                return false;
            }
        }

        // ۲. بررسی از طریق جدول دیتابیس modules
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('modules')) {
                $dbModule = \App\Models\Module::where('name', $moduleName)
                    ->orWhere('slug', strtolower($moduleName))
                    ->first();
                if ($dbModule && (!$dbModule->installed || !$dbModule->active)) {
                    return false;
                }
            }
        } catch (\Throwable $e) {
            // در صورت هرگونه خطا، مانع اجرای سیستم نمی‌شود
            return false;
        }

        return true;
    }

    public static function get(string $key, ?callable $default = null): ?array
    {
        if (isset(static::$widgets[$key])) {
            $widget = static::$widgets[$key];
            if (!empty($widget['module']) && !static::isModuleActive($widget['module'])) {
                return $default ? $default() : null;
            }
            return $widget;
        }

        return $default ? $default() : null;
    }
}
