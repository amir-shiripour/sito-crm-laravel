<?php

if (!function_exists('get_setting')) {
    function get_setting($key, $default = null)
    {
        $setting = \Modules\Settings\Entities\Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}

if (!function_exists('is_panel_indexing_blocked')) {
    function is_panel_indexing_blocked(): bool
    {
        $val = get_setting('prevent_panel_indexing', false);
        return filter_var($val, FILTER_VALIDATE_BOOLEAN) || $val === '1' || $val === 1;
    }
}

if (!function_exists('is_public_indexing_blocked')) {
    function is_public_indexing_blocked(): bool
    {
        $val = get_setting('prevent_public_indexing', false);
        return filter_var($val, FILTER_VALIDATE_BOOLEAN) || $val === '1' || $val === 1;
    }
}
