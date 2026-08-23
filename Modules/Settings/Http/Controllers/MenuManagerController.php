<?php

namespace Modules\Settings\Http\Controllers;

use App\Models\User;
use App\Services\Modules\MenuCustomizationService;
use App\Services\Modules\ModuleMenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Settings\Entities\MenuCustomGroup;
use Modules\Settings\Entities\MenuCustomization;
use Modules\Settings\Entities\Setting;
use Spatie\Permission\Models\Role;

class MenuManagerController extends Controller
{
    protected MenuCustomizationService $customizationService;
    protected ModuleMenuService $moduleMenuService;

    public function __construct(
        MenuCustomizationService $customizationService,
        ModuleMenuService $moduleMenuService
    ) {
        $this->customizationService = $customizationService;
        $this->moduleMenuService = $moduleMenuService;
    }

    /**
     * Get all menu items and existing customizations for UI editor.
     */
    public function getItems(Request $request): JsonResponse
    {
        $scope = $request->query('scope', 'global');
        $scopeId = $request->query('scope_id');

        if ($scope === 'global') {
            $scopeId = null;
        }

        // Get raw menu structure without modifications
        $rawMenu = $this->moduleMenuService->getRawForUser(Auth::user());

        // Extract flattened items from raw menu
        $extractedItems = [];
        $detectedGroups = [];

        // 0. Dashboard Item
        $dashboardSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>';
        $extractedItems[] = [
            'menu_key' => 'core:user.dashboard',
            'default_title' => 'پیشخوان',
            'title' => 'پیشخوان',
            'default_icon' => $dashboardSvg,
            'icon' => $dashboardSvg,
            'route' => 'user.dashboard',
            'permission' => null,
            'default_group' => 'dashboard',
            'group' => 'dashboard',
            'group_title' => 'پیشخوان',
            'default_position' => 1,
            'position' => 1,
            'module' => 'core',
            'module_name' => 'سیستم',
            'is_settings' => false,
            'hidden' => false,
            'visibility_type' => 'all',
            'allowed_roles' => [],
            'allowed_users' => [],
            'is_customized' => false,
        ];

        // 1. Single items
        foreach ($rawMenu['items'] as $item) {
            $key = $this->customizationService->generateMenuKey($item);
            $itemGroup = $item['group'] ?? 'single';
            $extractedItems[] = [
                'menu_key' => $key,
                'default_title' => $item['title'] ?? '',
                'title' => $item['title'] ?? '',
                'default_icon' => $item['icon'] ?? '',
                'icon' => $item['icon'] ?? '',
                'route' => $item['route'] ?? '',
                'permission' => $item['permission'] ?? null,
                'default_group' => $itemGroup,
                'group' => $itemGroup,
                'group_title' => $this->resolveGroupTitle($itemGroup, $item['module'] ?? 'core'),
                'default_position' => $item['position'] ?? 99,
                'position' => $item['position'] ?? 99,
                'module' => $item['module'] ?? 'core',
                'module_name' => $item['module_name'] ?? 'سیستم',
                'is_settings' => false,
                'hidden' => false,
                'visibility_type' => 'all',
                'allowed_roles' => [],
                'allowed_users' => [],
                'is_customized' => false,
            ];
        }

        // 2. Group items
        foreach ($rawMenu['groups'] as $group) {
            $gModule = $group['module'];
            $gTitle = $this->resolveGroupTitle($gModule, $gModule);

            foreach ($group['items'] as $item) {
                $key = $this->customizationService->generateMenuKey($item);
                $extractedItems[] = [
                    'menu_key' => $key,
                    'default_title' => $item['title'] ?? '',
                    'title' => $item['title'] ?? '',
                    'default_icon' => $item['icon'] ?? '',
                    'icon' => $item['icon'] ?? '',
                    'route' => $item['route'] ?? '',
                    'permission' => $item['permission'] ?? null,
                    'default_group' => $gModule,
                    'group' => $gModule,
                    'group_title' => $gTitle,
                    'default_position' => $item['position'] ?? 99,
                    'position' => $item['position'] ?? 99,
                    'module' => $item['module'] ?? $gModule,
                    'module_name' => $item['module_name'] ?? $gTitle,
                    'is_settings' => false,
                    'hidden' => false,
                    'visibility_type' => 'all',
                    'allowed_roles' => [],
                    'allowed_users' => [],
                    'is_customized' => false,
                ];
            }
        }

        // 3. Settings items
        foreach ($rawMenu['settings'] as $item) {
            $key = $this->customizationService->generateMenuKey($item);
            $extractedItems[] = [
                'menu_key' => $key,
                'default_title' => $item['title'] ?? '',
                'title' => $item['title'] ?? '',
                'default_icon' => $item['icon'] ?? '',
                'icon' => $item['icon'] ?? '',
                'route' => $item['route'] ?? '',
                'permission' => $item['permission'] ?? null,
                'default_group' => 'settings',
                'group' => 'settings',
                'group_title' => 'تنظیمات',
                'default_position' => $item['position'] ?? 99,
                'position' => $item['position'] ?? 99,
                'module' => $item['module'] ?? 'core',
                'module_name' => $item['module_name'] ?? 'سیستم',
                'is_settings' => true,
                'hidden' => false,
                'visibility_type' => 'all',
                'allowed_roles' => [],
                'allowed_users' => [],
                'is_customized' => false,
            ];
        }

        // Fetch customizations for the requested scope
        $customizations = MenuCustomization::where('scope', $scope)
            ->when($scopeId, fn($q) => $q->where('scope_id', $scopeId))
            ->get()
            ->keyBy('menu_key');

        // Apply overrides to editor items
        foreach ($extractedItems as &$item) {
            $key = $item['menu_key'];
            if (isset($customizations[$key])) {
                $ov = $customizations[$key]->overrides ?? [];
                $item['is_customized'] = true;

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
                $item['hidden'] = !empty($ov['hidden']);
                $item['visibility_type'] = $ov['visibility_type'] ?? 'all';
                $item['allowed_roles'] = $ov['allowed_roles'] ?? [];
                $item['allowed_users'] = $ov['allowed_users'] ?? [];
            }
        }
        unset($item);

        // Fetch custom groups from database
        $customGroups = MenuCustomGroup::where('scope', $scope)
            ->when($scopeId, fn($q) => $q->where('scope_id', $scopeId))
            ->orderBy('position', 'asc')
            ->get();

        // Standard system groups definition with dynamic localized titles from Modules/*/lang
        $defaultGroupList = [
            [
                'key' => 'dashboard',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('dashboard', 'Core'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /></svg>',
                'position' => 1,
                'is_custom' => false,
                'is_collapsible' => false,
            ],
            [
                'key' => 'clients',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('clients', 'Clients'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>',
                'position' => 10,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'single',
                'title' => 'آیتم‌های عمومی و مستقل',
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 8v4l3 3" /></svg>',
                'position' => 20,
                'is_custom' => false,
                'is_collapsible' => false,
            ],
            [
                'key' => 'admin',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('admin', 'Admin'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>',
                'position' => 30,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'sales',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('sales', 'Sales'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M12 4v16" /><path d="M12 12h8" /></svg>',
                'position' => 40,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'accounting',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('accounting', 'Accounting'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
                'position' => 50,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'properties',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('properties', 'Properties'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>',
                'position' => 60,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'booking',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('booking', 'Booking'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="5" width="16" height="16" rx="2" /><line x1="16" y1="3" x2="16" y2="7" /><line x1="8" y1="3" x2="8" y2="7" /><line x1="4" y1="11" x2="20" y2="11" /><circle cx="12" cy="16" r="2" /></svg>',
                'position' => 70,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'tasks',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('tasks', 'Tasks'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3l8 -8" /><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9" /></svg>',
                'position' => 80,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'workflows',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('workflows', 'Workflows'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="6" height="6" rx="1" /><rect x="15" y="15" width="6" height="6" rx="1" /><path d="M6 9v3a3 3 0 0 0 3 3h6" /></svg>',
                'position' => 90,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'sms',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('sms', 'Sms'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9h8" /><path d="M8 13h6" /><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z" /></svg>',
                'position' => 100,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'smartbot',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('smartbot', 'SmartBot'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="8" width="16" height="12" rx="2" /><path d="M9 4v4" /><path d="M15 4v4" /></svg>',
                'position' => 110,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'contractforge',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('contractforge', 'ContractForge'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 15l2 2l4 -4" /></svg>',
                'position' => 120,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'market',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('market', 'Market'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6" cy="19" r="2" /><circle cx="17" cy="19" r="2" /><path d="M17 17h-11v-14h-2" /><path d="M6 5l14 1l-1 7h-13" /></svg>',
                'position' => 130,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'services',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('services', 'Services'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21a9 9 0 0 1 0 -18c4.97 0 9 3.582 9 8c0 1.06 -.474 2.078 -1.318 2.828c-.844 .75 -1.989 1.172 -3.182 1.172h-2.5a2 2 0 0 0 -1 3.75a1.3 1.3 0 0 1 -.818 2.25" /></svg>',
                'position' => 140,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
            [
                'key' => 'settings',
                'title' => $this->moduleMenuService->resolveModuleGroupTitle('settings', 'Settings'),
                'icon' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>',
                'position' => 900,
                'is_custom' => false,
                'is_collapsible' => true,
            ],
        ];

        // Format group list and apply group customizations
        $allGroups = [];
        foreach ($defaultGroupList as $dg) {
            $gKey = $dg['key'];
            $customKey = "group:{$gKey}";

            $gTitle = $dg['title'];
            $gIcon = $dg['icon'];
            $gPosition = $dg['position'];
            $gHidden = false;
            $isCustomized = false;

            if (isset($customizations[$customKey])) {
                $gov = $customizations[$customKey]->overrides ?? [];
                $isCustomized = true;
                if (!empty($gov['title'])) $gTitle = $gov['title'];
                if (!empty($gov['icon'])) $gIcon = $gov['icon'];
                if (isset($gov['position']) && is_numeric($gov['position'])) $gPosition = (int) $gov['position'];
                if (!empty($gov['hidden'])) $gHidden = true;
            }

            $allGroups[] = [
                'key' => $gKey,
                'title' => $gTitle,
                'default_title' => $dg['title'],
                'icon' => $gIcon,
                'default_icon' => $dg['icon'],
                'position' => $gPosition,
                'is_custom' => false,
                'is_collapsible' => $dg['is_collapsible'],
                'hidden' => $gHidden,
                'is_customized' => $isCustomized,
            ];
        }

        // Add custom created groups
        foreach ($customGroups as $cg) {
            $customKey = "group:{$cg->group_key}";
            $gTitle = $cg->title;
            $gIcon = $cg->icon ?: '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 19a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l2 2h10a2 2 0 0 1 2 2v11a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /></svg>';
            $gPosition = $cg->position ?: 99;
            $gHidden = false;
            $isCustomized = true;

            if (isset($customizations[$customKey])) {
                $gov = $customizations[$customKey]->overrides ?? [];
                if (!empty($gov['title'])) $gTitle = $gov['title'];
                if (!empty($gov['icon'])) $gIcon = $gov['icon'];
                if (isset($gov['position']) && is_numeric($gov['position'])) $gPosition = (int) $gov['position'];
                if (!empty($gov['hidden'])) $gHidden = true;
            }

            $allGroups[] = [
                'id' => $cg->id,
                'key' => $cg->group_key,
                'title' => $gTitle,
                'default_title' => $cg->title,
                'icon' => $gIcon,
                'default_icon' => $cg->icon,
                'position' => $gPosition,
                'is_custom' => true,
                'is_collapsible' => true,
                'hidden' => $gHidden,
                'is_customized' => $isCustomized,
            ];
        }

        // Sort groups by position
        usort($allGroups, fn($a, $b) => ($a['position'] ?? 999) <=> ($b['position'] ?? 999));

        // Fetch available roles and users for selection
        $roles = class_exists(Role::class) ? Role::select('id', 'name')->get() : [];
        $users = User::select('id', 'name', 'email')->limit(100)->get();

        $isCustomMenuEnabled = $this->customizationService->isCustomMenuEnabled();

        return response()->json([
            'success' => true,
            'is_custom_menu_enabled' => $isCustomMenuEnabled,
            'items' => $extractedItems,
            'groups' => $allGroups,
            'custom_groups' => $customGroups,
            'roles' => $roles,
            'users' => $users,
            'scope' => $scope,
            'scope_id' => $scopeId,
        ]);
    }

    /**
     * Resolve group title with localization fallbacks.
     */
    protected function resolveGroupTitle(string $groupKey, string $module): string
    {
        return $this->moduleMenuService->resolveModuleGroupTitle($groupKey, $module);
    }

    /**
     * Save menu customizations (items and groups).
     */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'scope' => 'required|string|in:global,role,user',
            'scope_id' => 'nullable|integer',
            'items' => 'nullable|array',
            'groups' => 'nullable|array',
        ]);

        $scope = $request->input('scope', 'global');
        $scopeId = $scope === 'global' ? null : $request->input('scope_id');
        $items = $request->input('items', []);
        $groups = $request->input('groups', []);

        DB::beginTransaction();
        try {
            // Save item customizations
            foreach ($items as $itemData) {
                $menuKey = $itemData['menu_key'];

                $overrides = [
                    'title' => $itemData['title'] ?? null,
                    'icon' => $itemData['icon'] ?? null,
                    'position' => isset($itemData['position']) ? (int) $itemData['position'] : 99,
                    'group' => $itemData['group'] ?? null,
                    'hidden' => !empty($itemData['hidden']),
                    'visibility_type' => $itemData['visibility_type'] ?? 'all',
                    'allowed_roles' => $itemData['allowed_roles'] ?? [],
                    'allowed_users' => $itemData['allowed_users'] ?? [],
                ];

                MenuCustomization::updateOrCreate(
                    [
                        'scope' => $scope,
                        'scope_id' => $scopeId,
                        'menu_key' => $menuKey,
                    ],
                    [
                        'type' => 'item',
                        'overrides' => $overrides,
                        'created_by' => Auth::id(),
                    ]
                );
            }

            // Save group customizations
            foreach ($groups as $groupData) {
                $gKey = $groupData['key'] ?? '';
                if (empty($gKey)) continue;

                $menuKey = "group:{$gKey}";
                $overrides = [
                    'title' => $groupData['title'] ?? null,
                    'icon' => $groupData['icon'] ?? null,
                    'position' => isset($groupData['position']) ? (int) $groupData['position'] : 99,
                    'hidden' => !empty($groupData['hidden']),
                ];

                MenuCustomization::updateOrCreate(
                    [
                        'scope' => $scope,
                        'scope_id' => $scopeId,
                        'menu_key' => $menuKey,
                    ],
                    [
                        'type' => 'group',
                        'overrides' => $overrides,
                        'created_by' => Auth::id(),
                    ]
                );

                // If it's a custom DB group, also sync position/title/icon
                if (!empty($groupData['is_custom']) && !empty($groupData['id'])) {
                    MenuCustomGroup::where('id', $groupData['id'])->update([
                        'title' => $groupData['title'],
                        'icon' => $groupData['icon'] ?? null,
                        'position' => (int) ($groupData['position'] ?? 99),
                    ]);
                }
            }

            DB::commit();

            // Clear cache
            $this->customizationService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'تنظیمات و چیدمان منوها با موفقیت ذخیره شد.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error saving menu customizations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در ذخیره‌سازی: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset customizations to factory defaults.
     */
    public function reset(Request $request): JsonResponse
    {
        $scope = $request->input('scope', 'global');
        $scopeId = $scope === 'global' ? null : $request->input('scope_id');
        $menuKey = $request->input('menu_key');

        try {
            $query = MenuCustomization::where('scope', $scope)
                ->when($scopeId, fn($q) => $q->where('scope_id', $scopeId));

            if (!empty($menuKey)) {
                $query->where('menu_key', $menuKey)->delete();
                $message = 'آیتم یا گروه مورد نظر به حالت پیش‌فرض بازگشت.';
            } else {
                $query->delete();
                $message = 'تمامی شخصی‌سازی‌های این بخش به حالت پیش‌فرض سیستم بازگردانده شد.';
            }

            $this->customizationService->clearCache();

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error resetting menu customizations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در بازنشانی: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create or update a custom menu group.
     */
    public function saveGroup(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'nullable|integer',
            'group_key' => 'required|string|max:60|regex:/^[a-zA-Z0-9_\-]+$/',
            'title' => 'required|string|max:100',
            'icon' => 'nullable|string',
            'position' => 'nullable|integer',
            'scope' => 'nullable|string|in:global,role,user',
            'scope_id' => 'nullable|integer',
        ]);

        $scope = $request->input('scope', 'global');
        $scopeId = $scope === 'global' ? null : $request->input('scope_id');

        try {
            $group = MenuCustomGroup::updateOrCreate(
                [
                    'id' => $request->input('id'),
                ],
                [
                    'group_key' => $request->input('group_key'),
                    'title' => $request->input('title'),
                    'icon' => $request->input('icon'),
                    'position' => (int) ($request->input('position') ?? 99),
                    'scope' => $scope,
                    'scope_id' => $scopeId,
                    'is_active' => true,
                ]
            );

            $this->customizationService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'گروه منو با موفقیت ذخیره شد.',
                'group' => $group,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ذخیره گروه: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a custom menu group.
     */
    public function deleteGroup(Request $request, MenuCustomGroup $group): JsonResponse
    {
        try {
            $group->delete();
            $this->customizationService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'گروه سفارشی حذف شد.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف گروه: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active state of custom menu system (Switch between default core menu and custom menu).
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $enabled = (bool) $request->input('enabled');

        try {
            Setting::updateOrCreate(
                ['key' => 'custom_menu_enabled'],
                ['value' => $enabled ? '1' : '0']
            );

            // Clear cache
            \Illuminate\Support\Facades\Cache::forget('custom_menu_system_enabled');
            $this->customizationService->clearCache();

            $statusText = $enabled
                ? 'سیستم منوی سفارشی‌سازی شده فعال شد.'
                : 'منوی سیستم به حالت پیش‌فرض و استاندارد هسته تغییر یافت (تمامی شخصی‌سازی‌های شما در پایگاه‌داده محفوظ است).';

            return response()->json([
                'success' => true,
                'is_custom_menu_enabled' => $enabled,
                'message' => $statusText,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تغییر وضعیت سیستم منو: ' . $e->getMessage(),
            ], 500);
        }
    }
}
