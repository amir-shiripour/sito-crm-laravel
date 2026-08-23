{{-- resources/views/user/partials/sidebar.blade.php --}}

@php
    // 💡 پیشنهاد: بهتر است این منطق در آینده به یک View Composer یا Blade Component منتقل شود.
    $menuService = app(App\Services\Modules\ModuleMenuService::class);
    $menuData = $menuService->getAllForUser(auth()->user());
    $menuItems = $menuData['items'] ?? [];
    $menuGroups = $menuData['groups'] ?? [];
    $settingsItems = $menuData['settings'] ?? [];

    $customMenuService = app(App\Services\Modules\MenuCustomizationService::class);
    $isCustomEnabled = $customMenuService->isCustomMenuEnabled();

    // Backward compatibility for clients module only in core/default mode
    $clientsItems = [];
    if (!$isCustomEnabled) {
        foreach ($menuItems as $key => $item) {
            if ($item['group'] === 'clients') {
                $clientsItems[] = $item;
                unset($menuItems[$key]);
            }
        }
        foreach ($menuGroups as $key => $group) {
            if ($group['module'] === 'clients') {
                $clientsItems = $group['items'];
                unset($menuGroups[$key]);
            }
        }
    }

    $clientsGroupMeta = $menuData['clients_group_meta'] ?? null;
    $settingsGroupMeta = $menuData['settings_group_meta'] ?? null;

    if (!empty($clientsGroupMeta['hidden']) && !$isCustomEnabled) {
        $clientsItems = [];
    }
    $menuItems = array_values($menuItems);
    $menuGroups = array_values($menuGroups);
@endphp

{{-- Header section of the sidebar --}}
<div class="h-20 px-5 flex items-center justify-between border-b border-gray-100 dark:border-gray-800 shrink-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10">
    <div class="flex items-center gap-3 overflow-hidden">
        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400 group-hover:scale-105 transition-transform duration-300">
                <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2a10 10 0 1 1-7.07 2.93A10 10 0 0 1 12 2Zm0 4a6 6 0 1 0 6 6 6 6 0 0 0-6-6Z" />
                </svg>
            </div>
            <span x-show="!sidebarCollapsed"
                  x-transition:enter="transition ease-out duration-300 delay-100"
                  x-transition:enter-start="opacity-0 translate-x-4"
                  x-transition:enter-end="opacity-100 translate-x-0"
                  class="font-extrabold text-lg truncate text-gray-900 dark:text-white tracking-tight">
                داشبورد
            </span>
        </a>
    </div>

    <button @click="toggleSidebar()"
            class="p-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-all duration-200 hidden lg:flex items-center justify-center"
            :title="sidebarCollapsed ? 'باز کردن منو' : 'جمع کردن منو'">
        <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 transition-transform hover:-translate-x-0.5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M9 6l6 6l-6 6" />
        </svg>
        <svg x-show="sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 transition-transform hover:translate-x-0.5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
        </svg>
    </button>
</div>

{{-- Navigation Links --}}
<nav class="p-4 flex-1 overflow-y-auto space-y-1.5 custom-scrollbar">
    @include('user.partials.sidebar-nav', [
        'menuItems' => $menuItems,
        'menuGroups' => $menuGroups,
        'clientsItems' => $clientsItems,
        'settingsItems' => $settingsItems,
        'clientsGroupMeta' => $clientsGroupMeta,
        'settingsGroupMeta' => $settingsGroupMeta,
        'isCustomEnabled' => $isCustomEnabled,
        'menuBlocks' => $menuData['blocks'] ?? [],
    ])
</nav>

{{-- استایل اختصاصی اسکرول‌بار برای سایدبار --}}
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #374151; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background-color: #d1d5db; }
    .dark .custom-scrollbar:hover::-webkit-scrollbar-thumb { background-color: #4b5563; }
</style>
