{{-- user/partials/topbar.blade.php --}}
<div class="h-16 px-3 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center gap-2 overflow-hidden">
        <svg class="w-7 h-7 shrink-0 text-indigo-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2a10 10 0 1 1-7.07 2.93A10 10 0 0 1 12 2Zm0 4a6 6 0 1 0 6 6 6 6 0 0 0-6-6Z"/>
        </svg>
        <span x-show="!sidebarCollapsed" x-transition
              class="font-bold truncate">داشبورد</span>
    </div>

    <button @click="toggleSidebar()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" :title="sidebarCollapsed ? 'باز کردن منو' : 'جمع کردن منو'">
        <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-chevron-right">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" />
        </svg>
        <svg x-show="sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-chevron-left">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 6l-6 6l6 6" />
        </svg>
    </button>
</div>

<nav class="p-3">
    <a href="{{route('user.dashboard')}}" class=" my-1 flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700" :class="isActive('dashboard') ? 'bg-gray-100 dark:bg-gray-700' : ''" :title="sidebarCollapsed ? 'پیشخوان' : null">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0 icon icon-tabler icons-tabler-outline icon-tabler-home"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
        <span x-show="!sidebarCollapsed" x-transition class="truncate">پیشخوان</span>
    </a>

    @php
        $menuService = app(App\Services\Modules\ModuleMenuService::class);
        $menuItems = $menuService->getAllForUser(auth()->user());
    @endphp

    <div x-data="{ open: isMenuOpen('clients') }" class="mt-1">
        <button @click="toggleMenu('clients'); open = !open" class="w-full flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700" :title="sidebarCollapsed ? 'مشتریان' : null">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0 icon icon-tabler icons-tabler-outline icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
            <span x-show="!sidebarCollapsed" class="flex-1 text-start">{{'مدیریت '.config('clients.labels.plural')}}</span>
            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform icon icon-tabler icons-tabler-outline icon-tabler-chevron-down"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 9l6 6l6 -6" /></svg>
        </button>


        <div x-show="open && !sidebarCollapsed" x-collapse class="pr-4 mt-1 space-y-1">
            @foreach($menuItems as $item)
                <a href="{{ $item['route'] ? route($item['route']) : '#' }}" class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs($item['route'] . '*') ? 'active' : '' }}">
                    @php
                        echo $item['icon'];
                    @endphp
                    <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{--@foreach($menuItems as $item)

        <a href="{{ $item['route'] ? route($item['route']) : '#' }}" class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs($item['route'] . '*') ? 'active' : '' }}" :title="sidebarCollapsed ? {{ $item['title'] }} : null">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6a2 2 0 0 1 2-2h3l1-2h4l1 2h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/></svg>
            <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
        </a>
    @endforeach--}}


</nav>
