<?php

namespace App\Services\Modules;

use App\Models\Module;
use Illuminate\Support\Str;

class ModuleMenuService
{
    /**
     * بازگرداندن آیتم‌های منو برای یک کاربر مشخص (فقط از ماژول‌های installed+active)
     * هر ماژول می‌تواند فایل resources/menu.php داشته باشد که آرایه‌ای از آیتم‌ها برمی‌گرداند.
     *
     * ساختار بازگشتی:
     * [
     *   'items' => [...], // آیتم‌های تکی (ماژول‌هایی که یک آیتم غیر تنظیمات دارند)
     *   'groups' => [...], // گروه‌های ماژول‌ها (ماژول‌هایی که بیشتر از یک آیتم غیر تنظیمات دارند)
     *   'settings' => [...], // تمام آیتم‌های تنظیمات از همه ماژول‌ها
     * ]
     */
    public function getAllForUser($user): array
    {
        $items = [];
        $moduleGroups = [];
        $settingsItems = [];

        // لود کردن منوی هسته (core menu) از resources/menu.php
        $coreMenuPath = resource_path('menu.php');
        if (file_exists($coreMenuPath)) {
            try {
                $coreMenu = include $coreMenuPath;
                if (is_array($coreMenu)) {
                    $coreItems = [];
                    $coreSettings = [];

                    foreach ($coreMenu as $m) {
                        // اگر permission تعریف شده باشد و کاربر دسترسی ندارد، رد کن
                        if (!empty($m['permission'])) {
                            if (! $user->can($m['permission'])) {
                                continue;
                            }
                        }

                        // اضافه کردن اطلاعات ماژول به آیتم
                        $m['module'] = 'core';
                        $m['module_name'] = 'سیستم';

                        // تشخیص اینکه آیا آیتم تنظیمات است یا نه
                        $isSettings = $this->isSettingsItem($m);

                        if ($isSettings) {
                            $coreSettings[] = $m;
                        } else {
                            $coreItems[] = $m;
                        }
                    }

                    // اگر هسته بیشتر از یک آیتم غیر تنظیمات دارد، گروه بساز
                    if (count($coreItems) > 1) {
                        $moduleGroups[] = [
                            'module' => 'admin',
                            'module_name' => 'مدیریت سیستم',
                            'items' => $coreItems,
                        ];
                    } elseif (count($coreItems) === 1) {
                        // اگر فقط یک آیتم دارد، به لیست آیتم‌های تکی اضافه کن
                        $items[] = $coreItems[0];
                    }

                    // تمام تنظیمات را به لیست تنظیمات اضافه کن
                    $settingsItems = array_merge($settingsItems, $coreSettings);
                }
            } catch (\Throwable $e) {
                // لاگ کن و ادامه بده
                \Log::warning("Failed to load core menu: " . $e->getMessage());
            }
        }

        $modules = Module::where('installed', true)->where('active', true)->get();

        foreach ($modules as $module) {
            // مسیر فایل menu در ماژول
            $moduleName = Str::studly($module->slug);
            $menuPath = base_path("Modules/{$moduleName}/resources/menu.php");

            \Log::debug("Checking menu for module: {$moduleName} (slug: {$module->slug})", [
                'menu_path' => $menuPath,
                'file_exists' => file_exists($menuPath)
            ]);

            if (file_exists($menuPath)) {
                try {
                    $menu = include $menuPath;
                    \Log::debug("Menu loaded for module {$moduleName}", [
                        'menu_count' => is_array($menu) ? count($menu) : 0
                    ]);

                    if (is_array($menu)) {
                        $moduleItems = [];
                        $moduleSettings = [];

                        foreach ($menu as $m) {
                            // اگر permission تعریف شده باشد و کاربر دسترسی ندارد، رد کن
                            if (!empty($m['permission'])) {
                                $hasPermission = $user->can($m['permission']);
                                \Log::debug("Checking permission for menu item", [
                                    'module' => $moduleName,
                                    'permission' => $m['permission'],
                                    'user_id' => $user->id,
                                    'has_permission' => $hasPermission,
                                    'title' => $m['title'] ?? 'N/A'
                                ]);

                                if (! $hasPermission) {
                                    continue;
                                }
                            }

                            // اضافه کردن اطلاعات ماژول به آیتم
                            $m['module'] = $module->slug;
                            $m['module_name'] = $module->name;

                            // تشخیص اینکه آیا آیتم تنظیمات است یا نه
                            $isSettings = $this->isSettingsItem($m);

                            if ($isSettings) {
                                $moduleSettings[] = $m;
                            } else {
                                $moduleItems[] = $m;
                            }
                        }

                        // اگر ماژول بیشتر از یک آیتم غیر تنظیمات دارد، گروه بساز
                        \Log::debug("Module {$moduleName} menu items count", [
                            'module_items_count' => count($moduleItems),
                            'module_settings_count' => count($moduleSettings)
                        ]);

                        if (count($moduleItems) > 1) {
                            $moduleGroups[] = [
                                'module' => $module->slug,
                                'module_name' => $module->name,
                                'items' => $moduleItems,
                            ];
                            \Log::debug("Module {$moduleName} added as group");
                        } elseif (count($moduleItems) === 1) {
                            // اگر فقط یک آیتم دارد، به لیست آیتم‌های تکی اضافه کن
                            $items[] = $moduleItems[0];
                            \Log::debug("Module {$moduleName} added as single item", [
                                'item_title' => $moduleItems[0]['title'] ?? 'N/A',
                                'item_route' => $moduleItems[0]['route'] ?? 'N/A'
                            ]);
                        }

                        // تمام تنظیمات را به لیست تنظیمات اضافه کن
                        $settingsItems = array_merge($settingsItems, $moduleSettings);
                    }
                } catch (\Throwable $e) {
                    // لاگ کن و به ماژول بعدی برو
                    \Log::warning("Failed to load menu for module {$moduleName}: " . $e->getMessage());
                    continue;
                }
            }
        }

        // مرتب‌سازی بر اساس position
        usort($items, function ($a, $b) {
            return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
        });

        foreach ($moduleGroups as &$group) {
            usort($group['items'], function ($a, $b) {
                return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
            });
        }

        usort($settingsItems, function ($a, $b) {
            return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
        });

        return [
            'items' => $items,
            'groups' => $moduleGroups,
            'settings' => $settingsItems,
        ];
    }

    /**
     * تشخیص اینکه آیا یک آیتم منو، آیتم تنظیمات است یا نه
     */
    protected function isSettingsItem(array $item): bool
    {
        $group = $item['group'] ?? '';

        // اگر group به -settings ختم شود یا شامل settings باشد
        if (str_ends_with($group, '-settings') || str_contains(strtolower($group), 'settings')) {
            return true;
        }

        // اگر title شامل "تنظیمات" باشد
        $title = $item['title'] ?? '';
        if (str_contains($title, 'تنظیمات')) {
            return true;
        }

        // اگر route شامل "settings" باشد
        $route = $item['route'] ?? '';
        if (str_contains(strtolower($route), 'settings')) {
            return true;
        }

        return false;
    }
}
