{{-- user/partials/sidebar.blade.php --}}
{{-- This file now only contains the navigation content. --}}
@php
    $menuService = app(App\Services\Modules\ModuleMenuService::class);
    $menuData = $menuService->getAllForUser(auth()->user());
    $menuItems = $menuData['items'] ?? [];
    $menuGroups = $menuData['groups'] ?? [];
    $settingsItems = $menuData['settings'] ?? [];

    // Backward compatibility for clients module
    $clientsItems = [];
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
    $menuItems = array_values($menuItems);
    $menuGroups = array_values($menuGroups);
@endphp

{{-- Header section of the sidebar --}}
<div class="h-16 px-3 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 shrink-0">
    <div class="flex items-center gap-2 overflow-hidden">
        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2">
            <svg class="w-7 h-7 shrink-0 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2a10 10 0 1 1-7.07 2.93A10 10 0 0 1 12 2Zm0 4a6 6 0 1 0 6 6 6 6 0 0 0-6-6Z" />
            </svg>
            <span x-show="!sidebarCollapsed" x-transition class="font-bold truncate text-gray-900 dark:text-gray-100">داشبورد</span>
        </a>
    </div>

    <button @click="toggleSidebar()"
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors hidden lg:block"
            :title="sidebarCollapsed ? 'باز کردن منو' : 'جمع کردن منو'">
        <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M9 6l6 6l-6 6" />
        </svg>
        <svg x-show="sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
        </svg>
    </button>
</div>

{{-- Navigation Links --}}
<nav class="p-3 flex-1 overflow-y-auto">
    @include('user.partials.sidebar-nav', [
        'menuItems' => $menuItems,
        'menuGroups' => $menuGroups,
        'clientsItems' => $clientsItems,
        'settingsItems' => $settingsItems,
    ])
</nav>
