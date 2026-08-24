<?php

namespace App\Services\Modules;

use App\Models\Module;
use Illuminate\Support\Str;

class ModuleMenuService
{
    /**
     * Get all menu items for a given user.
     */
    public function getAllForUser($user): array
    {
        $items = [];
        $moduleGroups = [];
        $settingsItems = [];

        // Core menu
        $coreMenuPath = resource_path('menu.php');
        if (file_exists($coreMenuPath)) {
            try {
                $coreMenu = include $coreMenuPath;
                if (is_array($coreMenu)) {
                    $coreItems = [];
                    $coreSettings = [];

                    foreach ($coreMenu as $m) {
                        if (!empty($m['permission'])) {
                            if (!$user->can($m['permission'])) {
                                continue;
                            }
                        }

                        if (isset($m['show'])) {
                            $show = is_callable($m['show']) ? $m['show']() : (bool) $m['show'];
                            if (!$show) {
                                continue;
                            }
                        }

                        $m['module'] = 'core';
                        $m['module_name'] = 'سیستم';

                        $isSettings = $this->isSettingsItem($m);

                        if ($isSettings) {
                            $coreSettings[] = $m;
                        } else {
                            $coreItems[] = $m;
                        }
                    }

                    if (count($coreItems) > 1) {
                        $moduleGroups[] = [
                            'module' => 'admin',
                            'module_name' => __('menu.system_management'),
                            'items' => $coreItems,
                        ];
                    } elseif (count($coreItems) === 1) {
                        $items[] = $coreItems[0];
                    }

                    $settingsItems = array_merge($settingsItems, $coreSettings);
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to load core menu: " . $e->getMessage());
            }
        }

        $modules = Module::where('installed', true)->where('active', true)->get();

        foreach ($modules as $module) {
            $moduleName = $module->name;
            $menuPath = base_path("Modules/{$moduleName}/resources/menu.php");

            if (file_exists($menuPath)) {
                try {
                    $menu = include $menuPath;
                    if (is_array($menu)) {
                        $moduleItems = [];
                        $moduleSettings = [];

                        foreach ($menu as $m) {
                            if (!empty($m['permission'])) {
                                if (!$user->can($m['permission'])) {
                                    continue;
                                }
                            }

                            if (isset($m['show'])) {
                                $show = is_callable($m['show']) ? $m['show']() : (bool) $m['show'];
                                if (!$show) {
                                    continue;
                                }
                            }

                            $m['module'] = $module->slug;
                            $m['module_name'] = $module->name;

                            $isSettings = $this->isSettingsItem($m);

                            if ($isSettings) {
                                $moduleSettings[] = $m;
                            } else {
                                $moduleItems[] = $m;
                            }
                        }

                        if (count($moduleItems) > 1) {
                            $resolvedTitle = $this->resolveModuleGroupTitle($module->slug, $module->name);
                            $moduleGroups[] = [
                                'module' => $module->slug,
                                'module_name' => $resolvedTitle,
                                'items' => $moduleItems,
                            ];
                        } elseif (count($moduleItems) === 1) {
                            $items[] = $moduleItems[0];
                        }

                        $settingsItems = array_merge($settingsItems, $moduleSettings);
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Failed to load menu for module {$moduleName}: " . $e->getMessage());
                    continue;
                }
            }
        }

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

        $result = [
            'items' => $items,
            'groups' => $moduleGroups,
            'settings' => $settingsItems,
        ];

        try {
            return app(MenuCustomizationService::class)->applyOverrides($result, $user);
        } catch (\Throwable $e) {
            \Log::warning("MenuCustomizationService failed: " . $e->getMessage());
            return $result;
        }
    }

    /**
     * Get raw menu without applying customizations.
     */
    public function getRawForUser($user): array
    {
        $items = [];
        $moduleGroups = [];
        $settingsItems = [];

        $coreMenuPath = resource_path('menu.php');
        if (file_exists($coreMenuPath)) {
            try {
                $coreMenu = include $coreMenuPath;
                if (is_array($coreMenu)) {
                    $coreItems = [];
                    $coreSettings = [];

                    foreach ($coreMenu as $m) {
                        $m['module'] = 'core';
                        $m['module_name'] = 'سیستم';
                        $isSettings = $this->isSettingsItem($m);
                        if ($isSettings) {
                            $coreSettings[] = $m;
                        } else {
                            $coreItems[] = $m;
                        }
                    }

                    if (count($coreItems) > 1) {
                        $moduleGroups[] = [
                            'module' => 'admin',
                            'module_name' => __('menu.system_management'),
                            'items' => $coreItems,
                        ];
                    } elseif (count($coreItems) === 1) {
                        $items[] = $coreItems[0];
                    }
                    $settingsItems = array_merge($settingsItems, $coreSettings);
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to load core raw menu: " . $e->getMessage());
            }
        }

        $modules = Module::where('installed', true)->where('active', true)->get();

        foreach ($modules as $module) {
            $moduleName = $module->name;
            $menuPath = base_path("Modules/{$moduleName}/resources/menu.php");

            if (file_exists($menuPath)) {
                try {
                    $menu = include $menuPath;
                    if (is_array($menu)) {
                        $moduleItems = [];
                        $moduleSettings = [];

                        foreach ($menu as $m) {
                            $m['module'] = $module->slug;
                            $m['module_name'] = $module->name;
                            $isSettings = $this->isSettingsItem($m);
                            if ($isSettings) {
                                $moduleSettings[] = $m;
                            } else {
                                $moduleItems[] = $m;
                            }
                        }

                        if (count($moduleItems) > 1) {
                            $resolvedTitle = $this->resolveModuleGroupTitle($module->slug, $module->name);
                            $moduleGroups[] = [
                                'module' => $module->slug,
                                'module_name' => $resolvedTitle,
                                'items' => $moduleItems,
                            ];
                        } elseif (count($moduleItems) === 1) {
                            $items[] = $moduleItems[0];
                        }
                        $settingsItems = array_merge($settingsItems, $moduleSettings);
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Failed to load raw menu for module {$moduleName}: " . $e->getMessage());
                    continue;
                }
            }
        }

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
     * Resolve module group title from module translation files.
     */
    public function resolveModuleGroupTitle(string $moduleSlug, string $moduleName): string
    {
        if ($moduleSlug === 'admin') {
            return __('menu.system_management');
        }
        if ($moduleSlug === 'clients') {
            return 'مدیریت ' . config('clients.labels.plural', 'مشتریان');
        }
        if ($moduleSlug === 'settings') {
            return 'تنظیمات';
        }
        if ($moduleSlug === 'dashboard') {
            return 'پیشخوان';
        }

        $transKey = "{$moduleSlug}::menu.group_title";
        $translated = trans($transKey);
        if ($translated !== $transKey && !empty($translated)) {
            return $translated;
        }

        $knownTitles = [
            'tasks' => 'وظایف',
            'reminders' => 'یادآوری‌ها',
            'notifications' => 'اعلان‌ها',
            'sms' => 'پیامک',
            'booking' => 'نوبت‌دهی',
            'workflows' => 'مدیریت گردش کار',
            'smartbot' => 'دستیار هوشمند',
            'properties' => 'املاک',
            'accounting' => 'حسابداری',
            'sales' => 'فروش',
            'market' => 'مدیریت فروشگاه',
            'contractforge' => 'قراردادها',
            'contentforge' => 'تولید محتوا',
            'services' => 'سرویس‌ها و خدمات',
            'wallet' => 'کیف پول',
            'followups' => 'پیگیری‌ها',
            'clientcalls' => 'تماس‌ها',
            'projects' => 'پروژه‌ها',
            'single' => 'آیتم‌های عمومی و مستقل',
        ];
        if (isset($knownTitles[$moduleSlug])) {
            return $knownTitles[$moduleSlug];
        }

        return ucfirst($moduleSlug);
    }

    /**
     * Check if a menu item is a settings item.
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
