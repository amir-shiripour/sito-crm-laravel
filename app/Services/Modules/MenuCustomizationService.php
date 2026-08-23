<?php

namespace App\Services\Modules;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\Settings\Entities\MenuCustomGroup;
use Modules\Settings\Entities\MenuCustomization;

class MenuCustomizationService
{
    /**
     * Cache key prefix for menu customizations.
     */
    protected const CACHE_PREFIX = 'menu_customizations_';

    /**
     * Check if custom menu system is activated globally in system settings.
     */
    public function isCustomMenuEnabled(): bool
    {
        return Cache::remember('custom_menu_system_enabled', 3600, function () {
            if (!Schema::hasTable('settings')) {
                return false;
            }
            $setting = \Modules\Settings\Entities\Setting::where('key', 'custom_menu_enabled')->first();
            return $setting ? (bool) $setting->value : false;
        });
    }

    /**
     * Apply overrides on raw menu data for a specific user.
     *
     * @param array $menuData Array with keys: 'items', 'groups', 'settings'
     * @param User|null $user
     * @return array
     */
    public function applyOverrides(array $menuData, ?User $user): array
    {
        // اگر سیستم منوی سفارشی فعال نشده باشد، منوی پیش‌فرض دست‌نخورده برمی‌گردد
        if (!$this->isCustomMenuEnabled()) {
            return $menuData;
        }

        if (!Schema::hasTable('menu_customizations') || !Schema::hasTable('menu_custom_groups')) {
            return $menuData;
        }

        // Get effective overrides and custom groups
        $overrides = $this->getEffectiveOverrides($user);
        $customGroups = $this->getEffectiveCustomGroups($user);

        // If no customizations exist, return original structure unmodified
        if ($overrides->isEmpty() && $customGroups->isEmpty()) {
            return $menuData;
        }

        // Extract all individual items from groups, standalone items, and settings
        $allItems = [];

        // 1. Standalone items
        foreach ($menuData['items'] as $item) {
            $item['menu_key'] = $this->generateMenuKey($item);
            $allItems[] = $item;
        }

        // 2. Items inside module groups
        foreach ($menuData['groups'] as $group) {
            $groupKey = $group['module'];
            $groupTitle = $group['module_name'] ?? $groupKey;
            foreach ($group['items'] as $item) {
                $item['group'] = $groupKey;
                $item['group_title'] = $groupTitle;
                $item['menu_key'] = $this->generateMenuKey($item);
                $allItems[] = $item;
            }
        }

        // 3. Settings items
        foreach ($menuData['settings'] as $item) {
            $item['is_settings_category'] = true;
            $item['menu_key'] = $this->generateMenuKey($item);
            $allItems[] = $item;
        }

        // Map overrides by menu_key
        $overrideMap = [];
        foreach ($overrides as $customization) {
            $overrideMap[$customization->menu_key] = $customization->overrides;
        }

        // Group overrides map (for group title, icon, position, hidden)
        $groupOverrides = [];
        foreach ($overrideMap as $key => $ov) {
            if (str_starts_with($key, 'group:')) {
                $groupOverrides[substr($key, 6)] = $ov;
            }
        }

        // Apply item overrides
        $processedItems = [];
        foreach ($allItems as $item) {
            $key = $item['menu_key'];

            if (isset($overrideMap[$key])) {
                $ov = $overrideMap[$key];

                // Check hidden / visibility
                if (!empty($ov['hidden'])) {
                    continue;
                }

                if (isset($ov['visibility_type'])) {
                    if ($ov['visibility_type'] === 'roles' && !empty($ov['allowed_roles']) && $user) {
                        $userRoles = $user->roles->pluck('name')->toArray();
                        $hasRole = false;
                        foreach ($ov['allowed_roles'] as $r) {
                            if (in_array($r, $userRoles, true)) {
                                $hasRole = true;
                                break;
                            }
                        }
                        if (!$hasRole) {
                            continue;
                        }
                    } elseif ($ov['visibility_type'] === 'users' && !empty($ov['allowed_users']) && $user) {
                        if (!in_array($user->id, array_map('intval', $ov['allowed_users']), true)) {
                            continue;
                        }
                    }
                }

                // Apply custom properties
                if (!empty($ov['title'])) {
                    $item['title'] = $ov['title'];
                }
                if (isset($ov['icon']) && trim($ov['icon']) !== '') {
                    $item['icon'] = $ov['icon'];
                }
                if (isset($ov['position']) && is_numeric($ov['position'])) {
                    $item['position'] = (int) $ov['position'];
                }
                if (!empty($ov['group'])) {
                    $item['group'] = $ov['group'];
                }
            }

            $processedItems[] = $item;
        }

        // Reconstruct groups and items
        $finalItems = [];
        $finalGroupMap = [];
        $finalSettings = [];

        // Register custom groups in map
        foreach ($customGroups as $cg) {
            if (!$cg->is_active) {
                continue;
            }
            $finalGroupMap[$cg->group_key] = [
                'module' => $cg->group_key,
                'module_name' => $cg->title,
                'icon' => $cg->icon,
                'position' => $cg->position ?? 99,
                'items' => [],
                'is_custom' => true,
            ];
        }

        // Categorize items
        foreach ($processedItems as $item) {
            $isSettings = ($item['is_settings_category'] ?? false) ||
                str_ends_with($item['group'] ?? '', '-settings') ||
                str_contains(strtolower($item['group'] ?? ''), 'settings');

            if ($isSettings) {
                $finalSettings[] = $item;
                continue;
            }

            $groupKey = $item['group'] ?? 'general';

            if ($groupKey === 'dashboard') {
                // Dashboard item remains single at top
                $finalItems[] = $item;
            } elseif ($groupKey === 'single' || empty($groupKey)) {
                $finalItems[] = $item;
            } else {
                if (!isset($finalGroupMap[$groupKey])) {
                    $defaultGroupPositions = [
                        'clients' => 10,
                        'admin' => 30,
                        'sales' => 40,
                        'accounting' => 50,
                        'properties' => 60,
                        'booking' => 70,
                        'tasks' => 80,
                        'workflows' => 90,
                        'sms' => 100,
                        'smartbot' => 110,
                        'settings' => 900,
                    ];
                    $title = $item['group_title'] ?? ucfirst($groupKey);
                    $finalGroupMap[$groupKey] = [
                        'module' => $groupKey,
                        'module_name' => $title,
                        'position' => $defaultGroupPositions[$groupKey] ?? 99,
                        'items' => [],
                        'is_custom' => false,
                    ];
                }
                $finalGroupMap[$groupKey]['items'][] = $item;
            }
        }

        // Process group overrides (e.g. title, icon, position, hidden)
        $finalGroups = [];
        $clientsGroupMeta = [
            'title' => 'مدیریت ' . config('clients.labels.plural', 'مشتریان'),
            'icon' => null,
            'hidden' => false,
            'position' => 10,
        ];
        $settingsGroupMeta = [
            'title' => 'تنظیمات',
            'icon' => null,
            'hidden' => false,
            'position' => 900,
        ];
        $singleGroupMeta = [
            'title' => 'آیتم‌های عمومی و مستقل',
            'icon' => null,
            'hidden' => false,
            'position' => 20,
        ];

        // Apply clients, settings & single group overrides
        if (isset($groupOverrides['clients'])) {
            $cov = $groupOverrides['clients'];
            if (!empty($cov['title'])) $clientsGroupMeta['title'] = $cov['title'];
            if (!empty($cov['icon'])) $clientsGroupMeta['icon'] = $cov['icon'];
            if (!empty($cov['hidden'])) $clientsGroupMeta['hidden'] = true;
            if (isset($cov['position'])) $clientsGroupMeta['position'] = (int) $cov['position'];
        }

        if (isset($groupOverrides['settings'])) {
            $sov = $groupOverrides['settings'];
            if (!empty($sov['title'])) $settingsGroupMeta['title'] = $sov['title'];
            if (!empty($sov['icon'])) $settingsGroupMeta['icon'] = $sov['icon'];
            if (!empty($sov['hidden'])) $settingsGroupMeta['hidden'] = true;
            if (isset($sov['position'])) $settingsGroupMeta['position'] = (int) $sov['position'];
        }

        if (isset($groupOverrides['single'])) {
            $siov = $groupOverrides['single'];
            if (!empty($siov['title'])) $singleGroupMeta['title'] = $siov['title'];
            if (!empty($siov['icon'])) $singleGroupMeta['icon'] = $siov['icon'];
            if (!empty($siov['hidden'])) $singleGroupMeta['hidden'] = true;
            if (isset($siov['position'])) $singleGroupMeta['position'] = (int) $siov['position'];
        }

        foreach ($finalGroupMap as $gKey => $gData) {
            // Apply group overrides if any
            if (isset($groupOverrides[$gKey])) {
                $gov = $groupOverrides[$gKey];
                if (!empty($gov['hidden'])) {
                    continue;
                }
                if (!empty($gov['title'])) {
                    $gData['module_name'] = $gov['title'];
                    $gData['title'] = $gov['title'];
                }
                if (!empty($gov['icon'])) {
                    $gData['icon'] = $gov['icon'];
                }
                if (isset($gov['position']) && is_numeric($gov['position'])) {
                    $gData['position'] = (int) $gov['position'];
                }
            } else {
                $gData['title'] = $gData['module_name'] ?? ucfirst($gKey);
            }

            if (empty($gData['items'])) {
                continue;
            }

            // Sort items within group by position
            usort($gData['items'], function ($a, $b) {
                return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
            });

            // Keep group structure intact as defined by the user
            $finalGroups[] = $gData;
        }

        // Sort single items
        if ($singleGroupMeta['hidden']) {
            $finalItems = [];
        } else {
            usort($finalItems, function ($a, $b) {
                return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
            });
        }

        // Sort groups by group position
        usort($finalGroups, function ($a, $b) {
            return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
        });

        // Sort settings items
        usort($finalSettings, function ($a, $b) {
            return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
        });

        // If settings group is hidden
        if ($settingsGroupMeta['hidden']) {
            $finalSettings = [];
        }

        // Build unified ordered blocks for accurate sidebar rendering
        $menuBlocks = [];

        // 1. Dashboard block
        $dashOverride = $groupOverrides['dashboard'] ?? [];
        if (empty($dashOverride['hidden'])) {
            $dashItem = null;
            foreach ($finalItems as $k => $fItm) {
                if (($fItm['route'] ?? '') === 'user.dashboard' || ($fItm['group'] ?? '') === 'dashboard') {
                    $dashItem = $fItm;
                    unset($finalItems[$k]);
                    break;
                }
            }
            if (!$dashItem) {
                $dashSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>';
                $dashItem = [
                    'menu_key' => 'core:user.dashboard',
                    'title' => $dashOverride['title'] ?? 'پیشخوان',
                    'icon' => $dashOverride['icon'] ?? $dashSvg,
                    'route' => 'user.dashboard',
                    'position' => $dashOverride['position'] ?? 1,
                ];
            }
            $menuBlocks[] = [
                'type' => 'dashboard',
                'key' => 'dashboard',
                'position' => isset($dashOverride['position']) ? (int) $dashOverride['position'] : ($dashItem['position'] ?? 1),
                'item' => $dashItem,
            ];
        }

        // 2. Module & Custom groups
        foreach ($finalGroups as $fg) {
            $menuBlocks[] = [
                'type' => 'group',
                'key' => $fg['module'],
                'module' => $fg['module'],
                'title' => $fg['title'] ?? $fg['module_name'],
                'icon' => $fg['icon'] ?? null,
                'position' => (int) ($fg['position'] ?? 99),
                'items' => $fg['items'],
                'is_custom' => !empty($fg['is_custom']),
            ];
        }

        // 3. Single items placed strictly at the single group's position
        $singleBasePos = (int) ($singleGroupMeta['position'] ?? 20);
        foreach (array_values($finalItems) as $idx => $fi) {
            $itemSubPos = isset($fi['position']) ? ((int) $fi['position']) / 1000 : ($idx + 1) * 0.001;
            $menuBlocks[] = [
                'type' => 'single',
                'key' => $fi['menu_key'] ?? ('single_' . ($fi['route'] ?? 'item')),
                'position' => $singleBasePos + $itemSubPos,
                'item' => $fi,
            ];
        }

        // 4. Settings group
        if (!empty($finalSettings) && empty($settingsGroupMeta['hidden'])) {
            $menuBlocks[] = [
                'type' => 'settings',
                'key' => 'settings',
                'title' => $settingsGroupMeta['title'] ?? 'تنظیمات',
                'icon' => $settingsGroupMeta['icon'] ?? null,
                'position' => (int) ($settingsGroupMeta['position'] ?? 900),
                'items' => $finalSettings,
            ];
        }

        // Sort all blocks strictly by position
        usort($menuBlocks, function ($a, $b) {
            return ($a['position'] <=> $b['position']);
        });

        return [
            'items' => array_values($finalItems),
            'groups' => array_values($finalGroups),
            'settings' => array_values($finalSettings),
            'clients_group_meta' => $clientsGroupMeta,
            'settings_group_meta' => $settingsGroupMeta,
            'blocks' => array_values($menuBlocks),
        ];
    }

    /**
     * Generate a unique deterministic key for an item.
     */
    public function generateMenuKey(array $item): string
    {
        $module = $item['module'] ?? 'core';
        $route = $item['route'] ?? '';
        $title = $item['title'] ?? '';

        if (!empty($route)) {
            return "{$module}:{$route}";
        }

        return "{$module}:" . md5($title . ($item['group'] ?? ''));
    }

    /**
     * Get effective overrides for a user with hierarchical priority: User > Roles > Global.
     */
    public function getEffectiveOverrides(?User $user)
    {
        $cacheKey = self::CACHE_PREFIX . 'overrides_' . ($user ? "u{$user->id}" : 'guest');

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            // 1. Fetch Global customizations
            $globals = MenuCustomization::where('scope', 'global')->get();

            // 2. Fetch Role customizations if user exists
            $roleOverrides = collect();
            if ($user && method_exists($user, 'roles')) {
                $roleIds = $user->roles->pluck('id')->toArray();
                if (!empty($roleIds)) {
                    $roleOverrides = MenuCustomization::where('scope', 'role')
                        ->whereIn('scope_id', $roleIds)
                        ->get();
                }
            }

            // 3. Fetch User-specific customizations
            $userOverrides = collect();
            if ($user) {
                $userOverrides = MenuCustomization::where('scope', 'user')
                    ->where('scope_id', $user->id)
                    ->get();
            }

            // Merge with precedence: User overrides Role, Role overrides Global
            $merged = collect();

            // Add globals first
            foreach ($globals as $item) {
                $merged->put($item->menu_key, $item);
            }

            // Overwrite with roles
            foreach ($roleOverrides as $item) {
                $merged->put($item->menu_key, $item);
            }

            // Overwrite with user-specific
            foreach ($userOverrides as $item) {
                $merged->put($item->menu_key, $item);
            }

            return $merged->values();
        });
    }

    /**
     * Get effective custom groups.
     */
    public function getEffectiveCustomGroups(?User $user)
    {
        $cacheKey = self::CACHE_PREFIX . 'groups_' . ($user ? "u{$user->id}" : 'guest');

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            return MenuCustomGroup::where('is_active', true)
                ->where(function ($q) use ($user) {
                    $q->where('scope', 'global');
                    if ($user) {
                        $roleIds = method_exists($user, 'roles') ? $user->roles->pluck('id')->toArray() : [];
                        if (!empty($roleIds)) {
                            $q->orWhere(function ($rq) use ($roleIds) {
                                $rq->where('scope', 'role')->whereIn('scope_id', $roleIds);
                            });
                        }
                        $q->orWhere(function ($uq) use ($user) {
                            $uq->where('scope', 'user')->where('scope_id', $user->id);
                        });
                    }
                })
                ->orderBy('position', 'asc')
                ->get();
        });
    }

    /**
     * Clear all menu customization caches.
     */
    public function clearCache(): void
    {
        Cache::flush(); // Or selectively if tag-supported
    }
}
