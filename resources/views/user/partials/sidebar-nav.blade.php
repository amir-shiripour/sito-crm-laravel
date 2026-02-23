{{-- resources/views/user/partials/sidebar-nav.blade.php --}}

@php
    // استایل‌های مشترک لینک‌ها
    $linkBaseClass = "group flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition-all duration-200 relative overflow-hidden";

    $linkInactiveClass = "text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100";

    $linkActiveClass = "bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 font-bold";

    $iconClass = "w-5 h-5 shrink-0 transition-transform duration-300 group-hover:scale-110";
@endphp

{{-- لینک پیشخوان --}}
<a href="{{ route('user.dashboard') }}"
   class="{{ $linkBaseClass }} {{ request()->routeIs('user.dashboard') ? $linkActiveClass : $linkInactiveClass }}"
   :title="sidebarCollapsed ? 'داشبورد' : null">

    {{-- نوار رنگی کنار لینک فعال (برای راست‌چین) --}}
    @if(request()->routeIs('user.dashboard'))
        <span class="absolute right-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-indigo-600 dark:bg-indigo-500 rounded-l-full"></span>
    @endif

    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $iconClass }}">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
        <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
        <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
    </svg>
    <span x-show="!sidebarCollapsed" x-transition class="truncate">پیشخوان</span>
</a>

{{-- گروه مشتریان --}}
@if(count($clientsItems) > 0)
    @php
        $isClientsActive = request()->routeIs('clients.*');
    @endphp
    <div x-data="{ open: isMenuOpen('clients') || {{ $isClientsActive ? 'true' : 'false' }} }" class="mt-1">
        <button @click="toggleMenu('clients'); open = !open"
                class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 font-medium transition-all duration-200 group
                {{ $isClientsActive ? 'bg-gray-50 dark:bg-gray-800/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100' }}"
                :title="sidebarCollapsed ? 'مشتریان' : null">

            <div class="flex items-center gap-3 overflow-hidden">
                <span class="{{ $iconClass }} {{ $isClientsActive ? 'text-indigo-600 dark:text-indigo-400' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                        <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                    </svg>
                </span>
                <span x-show="!sidebarCollapsed" class="truncate font-semibold text-start">{{'مدیریت '.config('clients.labels.plural', 'مشتریان')}}</span>
            </div>

            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90 text-indigo-500' : '-rotate-90'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform duration-300 shrink-0 opacity-50 group-hover:opacity-100">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M15 6l-6 6l6 6" />
            </svg>
        </button>

        <div x-show="open && !sidebarCollapsed" x-collapse class="mt-1 space-y-1 relative before:absolute before:right-5 before:top-2 before:bottom-2 before:w-px before:bg-gray-200 dark:before:bg-gray-700">
            @foreach($clientsItems as $item)
                @php $isItemActive = request()->routeIs($item['route'] . '*'); @endphp
                <a href="{{ $item['route'] ? route($item['route']) : '#' }}"
                   class="flex items-center pr-10 pl-3 py-2 text-sm rounded-xl font-medium transition-all duration-200 relative group/child
                   {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/30' }}">

                    <span class="absolute right-[18px] top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full transition-colors {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-4 ring-indigo-50 dark:ring-indigo-900/30' : 'bg-gray-300 dark:bg-gray-600 group-hover/child:bg-gray-400 dark:group-hover/child:bg-gray-500' }}"></span>
                    <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
{{-- دیوایدر --}}
<div class="h-px bg-gray-100 dark:bg-gray-800 my-2 mx-2"></div>

{{-- نمایش آیتم‌های تکی --}}
@foreach($menuItems as $item)
    @php
        try {
            $routeExists = $item['route'] && \Illuminate\Support\Facades\Route::has($item['route']);
            $routeUrl = $routeExists ? route($item['route']) : '#';
        } catch (\Exception $e) {
            $routeExists = false;
            $routeUrl = '#';
        }
        $isActive = $routeExists && request()->routeIs($item['route'] . '*');
    @endphp

    <a href="{{ $routeUrl }}"
       class="{{ $linkBaseClass }} {{ $isActive ? $linkActiveClass : $linkInactiveClass }}"
       :title="sidebarCollapsed ? '{{ htmlspecialchars($item['title'], ENT_QUOTES) }}' : null">

        @if($isActive)
            <span class="absolute right-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-indigo-600 dark:bg-indigo-500 rounded-l-full"></span>
        @endif

        <span class="{{ $iconClass }} [&>svg]:w-5 [&>svg]:h-5">
            {!! $item['icon'] !!}
        </span>
        <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
    </a>
@endforeach

{{-- نمایش گروه‌های ماژول‌ها --}}
@foreach($menuGroups as $group)
    @php
        $isGroupActive = request()->routeIs($group['module'] . '.*');
        $groupTitle = $group['module'] === 'admin' ? __('menu.system_management') : __($group['module'] . '::menu.group_title');
    @endphp

    <div x-data="{ open: isMenuOpen('{{ $group['module'] }}') || {{ $isGroupActive ? 'true' : 'false' }} }" class="mt-1">
        <button @click="toggleMenu('{{ $group['module'] }}'); open = !open"
                class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 font-medium transition-all duration-200 group
                {{ $isGroupActive ? 'bg-gray-50 dark:bg-gray-800/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100' }}"
                :title="sidebarCollapsed ? '{{ htmlspecialchars($groupTitle, ENT_QUOTES) }}' : null">

            <div class="flex items-center gap-3 overflow-hidden">
                <span class="{{ $iconClass }} [&>svg]:w-5 [&>svg]:h-5 {{ $isGroupActive ? 'text-indigo-600 dark:text-indigo-400' : '' }}">
                    @php
                        $firstItem = $group['items'][0] ?? null;
                        if ($firstItem && isset($firstItem['icon'])) {
                            echo $firstItem['icon'];
                        } else {
                            echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 19a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l2 2h10a2 2 0 0 1 2 2v11a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /></svg>';
                        }
                    @endphp
                </span>
                <span x-show="!sidebarCollapsed" class="truncate font-semibold text-start">
                    {{ $groupTitle }}
                </span>
            </div>

            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90 text-indigo-500' : '-rotate-90'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform duration-300 shrink-0 opacity-50 group-hover:opacity-100">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M15 6l-6 6l6 6" />
            </svg>
        </button>

        <div x-show="open && !sidebarCollapsed" x-collapse class="mt-1 space-y-1 relative before:absolute before:right-5 before:top-2 before:bottom-2 before:w-px before:bg-gray-200 dark:before:bg-gray-700">
            @foreach($group['items'] as $item)
                @php
                    $isItemActive = request()->routeIs($item['route'] . '*');
                @endphp
                <a href="{{ $item['route'] ? route($item['route']) : '#' }}"
                   class="flex items-center pr-10 pl-3 py-2 text-sm rounded-xl font-medium transition-all duration-200 relative group/child
                   {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/30' }}">

                    {{-- نقطه اتصال به خط عمودی --}}
                    <span class="absolute right-[18px] top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full transition-colors {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-4 ring-indigo-50 dark:ring-indigo-900/30' : 'bg-gray-300 dark:bg-gray-600 group-hover/child:bg-gray-400 dark:group-hover/child:bg-gray-500' }}"></span>

                    <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endforeach

{{-- دیوایدر تنظیمات و مشتریان --}}
@if(count($clientsItems) > 0 || count($settingsItems) > 0)
    <div class="h-px bg-gray-100 dark:bg-gray-800 my-4 mx-2"></div>
@endif



{{-- گروه تنظیمات کل --}}
@if(count($settingsItems) > 0)
    @php
        $isSettingsActive = request()->routeIs('settings.*') || request()->routeIs('user.settings.*');
    @endphp
    <div x-data="{ open: isMenuOpen('settings') || {{ $isSettingsActive ? 'true' : 'false' }} }" class="mt-1 pb-4">
        <button @click="toggleMenu('settings'); open = !open"
                class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 font-medium transition-all duration-200 group
                {{ $isSettingsActive ? 'bg-gray-50 dark:bg-gray-800/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100' }}"
                :title="sidebarCollapsed ? 'تنظیمات' : null">

            <div class="flex items-center gap-3 overflow-hidden">
                <span class="{{ $iconClass }} {{ $isSettingsActive ? 'text-indigo-600 dark:text-indigo-400' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                    </svg>
                </span>
                <span x-show="!sidebarCollapsed" class="truncate font-semibold text-start">تنظیمات</span>
            </div>

            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90 text-indigo-500' : '-rotate-90'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform duration-300 shrink-0 opacity-50 group-hover:opacity-100">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M15 6l-6 6l6 6" />
            </svg>
        </button>

        <div x-show="open && !sidebarCollapsed" x-collapse class="mt-1 space-y-1 relative before:absolute before:right-5 before:top-2 before:bottom-2 before:w-px before:bg-gray-200 dark:before:bg-gray-700">
            @foreach($settingsItems as $item)
                @php $isItemActive = request()->routeIs($item['route'] . '*'); @endphp
                <a href="{{ $item['route'] ? route($item['route']) : '#' }}"
                   class="flex items-center pr-10 pl-3 py-2 text-sm rounded-xl font-medium transition-all duration-200 relative group/child
                   {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/30' }}">

                    <span class="absolute right-[18px] top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full transition-colors {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-4 ring-indigo-50 dark:ring-indigo-900/30' : 'bg-gray-300 dark:bg-gray-600 group-hover/child:bg-gray-400 dark:group-hover/child:bg-gray-500' }}"></span>
                    <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['title'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
