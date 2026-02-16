<?php

if (!function_exists('get_setting')) {
    function get_setting($key, $default = null)
    {
        $setting = \Modules\Settings\Entities\Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
