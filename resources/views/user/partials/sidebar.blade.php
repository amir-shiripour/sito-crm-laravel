{{-- user/partials/sidebar.blade.php --}}
<div
    class="h-16 px-3 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
    <div class="flex items-center gap-2 overflow-hidden">
        <svg class="w-7 h-7 shrink-0 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="currentColor"
             aria-hidden="true">
            <path d="M12 2a10 10 0 1 1-7.07 2.93A10 10 0 0 1 12 2Zm0 4a6 6 0 1 0 6 6 6 6 0 0 0-6-6Z" />
        </svg>
        <span x-show="!sidebarCollapsed" x-transition
              class="font-bold truncate text-gray-900 dark:text-gray-100">داشبورد</span>
    </div>

    <button @click="toggleSidebar()"
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors"
            :title="sidebarCollapsed ? 'باز کردن منو' : 'جمع کردن منو'">
        <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M9 6l6 6l-6 6" />
        </svg>
        <svg x-show="sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M15 6l-6 6l6 6" />
        </svg>
    </button>
</div>

<nav class="p-3 bg-white dark:bg-gray-800">
    {{-- لینک داشبورد --}}
    <a href="{{route('user.dashboard')}}"
       class="my-1 flex items-center gap-3 rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('user.dashboard') ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}"
       :title="sidebarCollapsed ? 'داشبورد' : null">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="w-5 h-5 shrink-0">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
            <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
        </svg>
        <span x-show="!sidebarCollapsed" x-transition class="truncate">داشبورد</span>
    </a>

    @php
        $menuService = app(App\Services\Modules\ModuleMenuService::class);
        $menuData = $menuService->getAllForUser(auth()->user());
        $menuItems = $menuData['items'] ?? [];
        $menuGroups = $menuData['groups'] ?? [];
        $settingsItems = $menuData['settings'] ?? [];

        // برای سازگاری با کد قدیمی، آیتم‌های clients را جدا می‌کنیم
        $clientsItems = [];
        $clientsSettingsItems = [];
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
        foreach ($settingsItems as $key => $item) {
        if ($item['group'] === 'clients-settings') {
        $clientsSettingsItems[] = $item;
        }
        }
        $menuItems = array_values($menuItems);
        $menuGroups = array_values($menuGroups);
    @endphp

    {{-- نمایش آیتم‌های تکی (ماژول‌هایی که یک آیتم غیر تنظیمات دارند) --}}
    @foreach($menuItems as $item)
        @php
            try {
            $routeExists = $item['route'] && \Illuminate\Support\Facades\Route::has($item['route']);
            $routeUrl = $routeExists ? route($item['route']) : '#';
            } catch (\Exception $e) {
            $routeExists = false;
            $routeUrl = '#';
            }
        @endphp
        <a href="{{ $routeUrl }}"
           class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2 transition-colors {{ $routeExists && request()->routeIs($item['route'] . '*') ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }} ">
            @php
                echo $item['icon'];
            @endphp
            <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
        </a>
    @endforeach

    {{-- نمایش گروه‌های ماژول‌ها (ماژول‌هایی که بیشتر از یک آیتم غیر تنظیمات دارند) --}}
    @foreach($menuGroups as $group)
        <div x-data="{ open: isMenuOpen('{{ $group['module'] }}') }" class="mt-1">
            <button @click="toggleMenu('{{ $group['module'] }}'); open = !open"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
                    :title="sidebarCollapsed ? '{{ $group['module_name'] }}' : null">
                @php
                    // استفاده از آیکون اولین آیتم گروه به عنوان آیکون گروه
                    $firstItem = $group['items'][0] ?? null;
                    if ($firstItem && isset($firstItem['icon'])) {
                    echo $firstItem['icon'];
                    } else {
                    echo '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="w-5 h-5 shrink-0">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path
                            d="M5 19a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l2 2h10a2 2 0 0 1 2 2v11a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
                    </svg>';
                    }
                @endphp
                <span x-show="!sidebarCollapsed" class="flex-1 text-start">{{ $group['module_name'] }}</span>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform shrink-0">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M6 9l6 6l6 -6" />
                </svg>
            </button>

            <div x-show="open && !sidebarCollapsed" x-collapse class="pr-4 mt-1 space-y-1">
                @foreach($group['items'] as $item)
                    <a href="{{ $item['route'] ? route($item['route']) : '#' }}"
                       class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2 transition-colors {{ request()->routeIs($item['route'] . '*') ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }} ">
                        @php
                            echo $item['icon'];
                        @endphp
                        <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- گروه مشتریان (سازگاری با کد قدیمی) --}}
    @if(count($clientsItems) > 0)
        <div x-data="{ open: isMenuOpen('clients') }" class="mt-1">
            <button @click="toggleMenu('clients'); open = !open"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
                    :title="sidebarCollapsed ? 'مشتریان' : null">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="w-5 h-5 shrink-0">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                </svg>
                <span x-show="!sidebarCollapsed"
                      class="flex-1 text-start">{{'مدیریت '.config('clients.labels.plural')}}</span>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform shrink-0">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M6 9l6 6l6 -6" />
                </svg>
            </button>

            <div x-show="open && !sidebarCollapsed" x-collapse class="pr-4 mt-1 space-y-1">
                @foreach($clientsItems as $item)
                    <a href="{{ $item['route'] ? route($item['route']) : '#' }}"
                       class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2 transition-colors {{ request()->routeIs($item['route'] . '*') ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }} ">
                        @php
                            echo $item['icon'];
                        @endphp
                        <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- گروه تنظیمات - نمایش تمام آیتم‌های تنظیمات از همه ماژول‌ها --}}
    @if(count($settingsItems) > 0)
        <div x-data="{ open: isMenuOpen('settings') }" class="mt-1">
            <button @click="toggleMenu('settings'); open = !open"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
                    :title="sidebarCollapsed ? 'تنظیمات' : null">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="w-5 h-5 shrink-0">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                </svg>
                <span x-show="!sidebarCollapsed" class="flex-1 text-start">تنظیمات</span>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform shrink-0">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M6 9l6 6l6 -6" />
                </svg>
            </button>

            <div x-show="open && !sidebarCollapsed" x-collapse class="pr-4 mt-1 space-y-1">
                @foreach($settingsItems as $item)
                    <a href="{{ $item['route'] ? route($item['route']) : '#' }}"
                       class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2 transition-colors {{ request()->routeIs($item['route'] . '*') ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }} ">
                        @php
                            echo $item['icon'];
                        @endphp
                        <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</nav>
