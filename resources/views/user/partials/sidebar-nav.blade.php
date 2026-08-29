{{-- resources/views/user/partials/sidebar-nav.blade.php --}}

@php
    // استایل‌های مشترک لینک‌ها با کنتراست دقیق در هر دو تم لایت و دارک
    $linkBaseClass = "group flex items-center justify-between rounded-xl px-3 py-2.5 font-medium transition-all duration-200 relative overflow-hidden text-sm";

    $linkInactiveClass = "text-gray-600 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 hover:text-gray-900 dark:hover:text-white";

    $linkActiveClass = "bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-200 font-bold border border-indigo-100 dark:border-indigo-800/60 shadow-xs dark:shadow-indigo-950/40";

    $iconWrapperClass = "w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-all duration-300";

    $iconInactiveWrapper = "bg-gray-100/80 dark:bg-gray-800/90 text-gray-500 dark:text-gray-300 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/60 group-hover:text-indigo-600 dark:group-hover:text-indigo-200";

    $iconActiveWrapper = "bg-indigo-600 dark:bg-indigo-500 text-white shadow-sm shadow-indigo-500/30 dark:shadow-indigo-950/50";

    $groupSettingsByModule = function($items) {
        $menuService = app(\App\Services\Modules\ModuleMenuService::class);
        $grouped = collect($items)->groupBy(function($item) use ($menuService) {
            $module = $item['module'] ?? 'core';
            if ($module === 'core' || $module === 'settings' || $module === 'admin') {
                return 'تنظیمات عمومی و سیستم';
            }
            if ($module === 'clients') {
                return 'تنظیمات ' . config('clients.labels.plural', 'مشتریان');
            }
            $moduleName = $item['module_name'] ?? '';
            if (empty($moduleName) || $moduleName === $module) {
                $moduleName = $menuService->resolveModuleGroupTitle($module, $module);
            }
            return 'تنظیمات ' . $moduleName;
        });

        return $grouped->sortBy(function($val, $key) {
            return $key === 'تنظیمات عمومی و سیستم' ? '000_' . $key : '100_' . $key;
        });
    };

    // تشخیص گروه فعال در زمان لود اولیه برای حالت دو مرحله‌ای
    $activeDrilldownOnInit = 'null';
    $activeDrilldownTitleOnInit = "''";
    if (!empty($isTwoStepEnabled)) {
        if (!empty($isCustomEnabled) && !empty($menuBlocks)) {
            foreach ($menuBlocks as $block) {
                if ($block['type'] === 'group') {
                    $gMod = $block['module'];
                    $isAct = request()->routeIs('user.' . $gMod . '.*')
                        || request()->routeIs($gMod . '.*')
                        || ($gMod === 'clients' && (request()->routeIs('user.clients.*') || request()->routeIs('user.settings.clients.*')))
                        || collect($block['items'])->contains(fn($i) => !empty($i['route']) && request()->routeIs($i['route'] . '*'));
                    if ($isAct) {
                        $activeDrilldownOnInit = "'" . $gMod . "'";
                        $activeDrilldownTitleOnInit = "'" . addslashes($block['title']) . "'";
                        break;
                    }
                } elseif ($block['type'] === 'settings') {
                    $isAct = request()->routeIs('settings.*') || request()->routeIs('user.settings.*') || request()->routeIs('user.settings.clients.*');
                    if ($isAct) {
                        $activeDrilldownOnInit = "'settings'";
                        $activeDrilldownTitleOnInit = "'" . addslashes($block['title'] ?? 'تنظیمات') . "'";
                        break;
                    }
                }
            }
        } else {
            if (request()->routeIs('user.clients.*') || request()->routeIs('clients.*') || request()->routeIs('user.settings.clients.*')) {
                $activeDrilldownOnInit = "'clients'";
                $activeDrilldownTitleOnInit = "'" . addslashes($clientsGroupMeta['title'] ?? ('مدیریت '.config('clients.labels.plural', 'مشتریان'))) . "'";
            } else {
                foreach ($menuGroups as $group) {
                    $gMod = $group['module'];
                    $isAct = request()->routeIs('user.' . $gMod . '.*')
                        || request()->routeIs($gMod . '.*')
                        || collect($group['items'])->contains(fn($i) => !empty($i['route']) && request()->routeIs($i['route'] . '*'));
                    if ($isAct) {
                        $activeDrilldownOnInit = "'" . $gMod . "'";
                        $activeDrilldownTitleOnInit = "'" . addslashes($group['title'] ?? $group['module_name'] ?? $gMod) . "'";
                        break;
                    }
                }
                if ($activeDrilldownOnInit === 'null' && (request()->routeIs('settings.*') || request()->routeIs('user.settings.*') || request()->routeIs('user.settings.clients.*'))) {
                    $activeDrilldownOnInit = "'settings'";
                    $activeDrilldownTitleOnInit = "'" . addslashes($settingsGroupMeta['title'] ?? 'تنظیمات سیستم') . "'";
                }
            }
        }
    }
@endphp

<div x-data="{
    twoStepActive: {{ !empty($isTwoStepEnabled) ? 'true' : 'false' }},
    activeDrilldown: {{ $activeDrilldownOnInit }},
    activeDrilldownTitle: {{ $activeDrilldownTitleOnInit }},
    setDrilldown(key, title) {
        this.activeDrilldown = key;
        this.activeDrilldownTitle = title || '';
    },
    clearDrilldown() {
        this.activeDrilldown = null;
        this.activeDrilldownTitle = '';
    },
    shouldShowGroup(key) {
        if (!this.twoStepActive) return true;
        if (sidebarFilter && sidebarFilter.trim().length > 0) return true;
        if (!this.activeDrilldown) return true;
        return this.activeDrilldown === key;
    },
    isStep1() {
        if (!this.twoStepActive) return true;
        if (sidebarFilter && sidebarFilter.trim().length > 0) return true;
        return !this.activeDrilldown;
    }
}" class="space-y-1.5">

    {{-- هدر مرحله دوم: دکمه بازگشت به منوی اصلی --}}
    @if(!empty($isTwoStepEnabled))
        <div x-show="!isStep1() && !sidebarCollapsed"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mb-2 p-2 rounded-xl bg-gradient-to-r from-indigo-50/90 via-indigo-50/50 to-transparent dark:from-indigo-950/70 dark:via-indigo-950/40 dark:to-transparent border border-indigo-100/90 dark:border-indigo-800/70 flex items-center justify-between shadow-xs">
            <button type="button" @click="clearDrilldown()"
                    class="flex items-center gap-2 text-xs font-bold text-indigo-700 dark:text-indigo-200 hover:text-indigo-900 dark:hover:text-white transition-colors group">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 flex items-center justify-center group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800 transition-colors">
                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M9 6l6 6l-6 6" />
                    </svg>
                </span>
                <span>بازگشت به منوی اصلی</span>
            </button>
            <span class="text-[11px] font-bold text-gray-700 dark:text-gray-200 truncate max-w-[120px] bg-white/90 dark:bg-gray-800/90 px-2.5 py-0.5 rounded-md border border-indigo-100/80 dark:border-indigo-800/60 shadow-xs" x-text="activeDrilldownTitle"></span>
        </div>
    @endif

    @if(!empty($isCustomEnabled) && !empty($menuBlocks))
        {{-- ======================================================== --}}
        {{-- حالت ۱: منوی سفارشی (Custom Mode)                         --}}
        {{-- ======================================================== --}}
        @foreach($menuBlocks as $block)
            @if($block['type'] === 'dashboard')
                @php
                    $dashItem = $block['item'];
                    $isDashActive = request()->routeIs('user.dashboard');
                    $dashTitle = $dashItem['title'] ?? 'پیشخوان';
                @endphp
                <div x-data="{
                        tooltipOpen: false,
                        tooltipTop: 0,
                        openTooltip(el) {
                            if (!sidebarCollapsed) return;
                            const rect = el.getBoundingClientRect();
                            this.tooltipTop = Math.max(8, rect.top + (rect.height / 2) - 16);
                            this.tooltipOpen = true;
                        },
                        closeTooltip() {
                            this.tooltipOpen = false;
                        }
                     }"
                     @mouseenter="openTooltip($el)"
                     @mouseleave="closeTooltip()"
                     x-show="isStep1() && (!sidebarFilter || '{{ addslashes($dashTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()))"
                     class="relative group/navitem">
                    <a href="{{ route('user.dashboard') }}"
                       class="{{ $linkBaseClass }} {{ $isDashActive ? $linkActiveClass : $linkInactiveClass }}">
                        
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="{{ $iconWrapperClass }} {{ $isDashActive ? $iconActiveWrapper : $iconInactiveWrapper }} [&>svg]:w-4 [&>svg]:h-4">
                                {!! $dashItem['icon'] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>' !!}
                            </span>
                            <span x-show="!sidebarCollapsed" x-transition class="truncate font-semibold">{{ $dashTitle }}</span>
                        </div>

                        @if($isDashActive)
                            <span x-show="!sidebarCollapsed" class="w-1.5 h-4 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                        @endif
                    </a>

                    {{-- Floating Tooltip when collapsed --}}
                    <div x-show="sidebarCollapsed && tooltipOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                         :style="'top: ' + tooltipTop + 'px; right: 76px;'"
                         class="fixed z-50 px-3 py-1.5 rounded-xl bg-gray-900/95 dark:bg-gray-800/95 backdrop-blur-md text-white dark:text-gray-100 text-xs font-semibold shadow-xl border border-gray-700/60 pointer-events-none whitespace-nowrap flex items-center gap-1.5">
                        <span>{{ $dashTitle }}</span>
                    </div>
                </div>

            @elseif($block['type'] === 'single')
                @php
                    $item = $block['item'];
                    try {
                        $routeExists = !empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']);
                        $routeUrl = $routeExists ? route($item['route']) : '#';
                    } catch (\Exception $e) {
                        $routeExists = false;
                        $routeUrl = '#';
                    }
                    $isActive = $routeExists && request()->routeIs($item['route'] . '*');
                    $itemTitle = $item['title'] ?? '';
                @endphp
                <div x-data="{
                        tooltipOpen: false,
                        tooltipTop: 0,
                        openTooltip(el) {
                            if (!sidebarCollapsed) return;
                            const rect = el.getBoundingClientRect();
                            this.tooltipTop = Math.max(8, rect.top + (rect.height / 2) - 16);
                            this.tooltipOpen = true;
                        },
                        closeTooltip() {
                            this.tooltipOpen = false;
                        }
                     }"
                     @mouseenter="openTooltip($el)"
                     @mouseleave="closeTooltip()"
                     x-show="isStep1() && (!sidebarFilter || '{{ addslashes($itemTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()))"
                     class="relative group/navitem">
                    <a href="{{ $routeUrl }}"
                       class="{{ $linkBaseClass }} {{ $isActive ? $linkActiveClass : $linkInactiveClass }}">
                        
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="{{ $iconWrapperClass }} {{ $isActive ? $iconActiveWrapper : $iconInactiveWrapper }} [&>svg]:w-4 [&>svg]:h-4">
                                {!! $item['icon'] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 8v4l3 3" /></svg>' !!}
                            </span>
                            <span x-show="!sidebarCollapsed" x-transition class="truncate font-semibold">{{ $itemTitle }}</span>
                        </div>

                        @if($isActive)
                            <span x-show="!sidebarCollapsed" class="w-1.5 h-4 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                        @endif
                    </a>

                    {{-- Floating Tooltip when collapsed --}}
                    <div x-show="sidebarCollapsed && tooltipOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                         :style="'top: ' + tooltipTop + 'px; right: 76px;'"
                         class="fixed z-50 px-3 py-1.5 rounded-xl bg-gray-900/95 dark:bg-gray-800/95 backdrop-blur-md text-white dark:text-gray-100 text-xs font-semibold shadow-xl border border-gray-700/60 pointer-events-none whitespace-nowrap flex items-center gap-1.5">
                        <span>{{ $itemTitle }}</span>
                    </div>
                </div>

            @elseif($block['type'] === 'group')
                @php
                    $validGroupItems = collect($block['items'])->filter(fn($i) => empty($i['route']) || \Illuminate\Support\Facades\Route::has($i['route']))->values()->all();
                    $groupModule = $block['module'];
                    $isGroupActive = request()->routeIs('user.' . $groupModule . '.*')
                        || request()->routeIs($groupModule . '.*')
                        || ($groupModule === 'clients' && (request()->routeIs('user.clients.*') || request()->routeIs('user.settings.clients.*')))
                        || collect($block['items'])->contains(fn($i) => !empty($i['route']) && request()->routeIs($i['route'] . '*'));
                    $groupTitle = $block['title'];
                    $firstItem = $block['items'][0] ?? null;
                    $itemsJson = json_encode(array_column($validGroupItems, 'title'));
                @endphp
                @if(count($validGroupItems) > 0)
                <div x-data="{
                        flyoutOpen: false,
                        flyoutTop: 0,
                        subItems: {{ $itemsJson }},
                        matches() {
                            if (!sidebarFilter) return true;
                            const q = sidebarFilter.toLowerCase();
                            if ('{{ addslashes($groupTitle) }}'.toLowerCase().includes(q)) return true;
                            return this.subItems.some(title => title && title.toLowerCase().includes(q));
                        },
                        isOpen() {
                            return isMenuOpen('{{ $block['module'] }}', {{ $isGroupActive ? 'true' : 'false' }});
                        },
                        isSubmenuOpen() {
                            if (!twoStepActive) {
                                return this.isOpen();
                            }
                            if (sidebarFilter && sidebarFilter.trim().length > 0) return true;
                            return activeDrilldown === '{{ $block['module'] }}';
                        },
                        openFlyout(el) {
                            if (!sidebarCollapsed) return;
                            const rect = el.getBoundingClientRect();
                            const windowHeight = window.innerHeight;
                            const flyoutHeight = Math.min(420, (this.subItems.length * 44) + 60);
                            if (rect.top + flyoutHeight > windowHeight - 16) {
                                this.flyoutTop = Math.max(8, windowHeight - flyoutHeight - 16);
                            } else {
                                this.flyoutTop = Math.max(8, rect.top);
                            }
                            this.flyoutOpen = true;
                        },
                        closeFlyout() {
                            this.flyoutOpen = false;
                        }
                     }"
                     x-show="shouldShowGroup('{{ $block['module'] }}') && matches()"
                     class="relative mt-1 group/groupblock"
                     @mouseenter="openFlyout($el)"
                     @mouseleave="closeFlyout()">

                    {{-- Header Button (نمایش در مرحله اول یا هنگام فیلتر) --}}
                    <button x-show="isStep1()"
                            @click="if(!sidebarCollapsed) { if (twoStepActive && !sidebarFilter) { setDrilldown('{{ $block['module'] }}', '{{ addslashes($groupTitle) }}'); } else { toggleMenu('{{ $block['module'] }}', {{ $isGroupActive ? 'true' : 'false' }}); } } else { toggleSidebar(); }"
                            @if($isGroupActive)
                                class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-bold transition-all duration-200 group border bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-200 border-indigo-100/90 dark:border-indigo-800/60 shadow-xs"
                            @else
                                class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 group border"
                                :class="isOpen() ? 'bg-gray-100/90 dark:bg-gray-800/90 text-gray-900 dark:text-white font-semibold border-gray-200/70 dark:border-gray-700/80 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 hover:text-gray-900 dark:hover:text-white border-transparent'"
                            @endif>

                        <div class="flex items-center gap-2.5 min-w-0">
                            @if($isGroupActive)
                                <span class="{{ $iconWrapperClass }} {{ $iconActiveWrapper }} [&>svg]:w-4 [&>svg]:h-4">
                                    @if(!empty($block['icon']))
                                        {!! $block['icon'] !!}
                                    @elseif($firstItem && isset($firstItem['icon']))
                                        {!! $firstItem['icon'] !!}
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 19a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l2 2h10a2 2 0 0 1 2 2v11a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /></svg>
                                    @endif
                                </span>
                            @else
                                <span class="{{ $iconWrapperClass }} [&>svg]:w-4 [&>svg]:h-4"
                                      :class="isOpen() ? 'bg-gray-200/90 dark:bg-gray-700 text-gray-800 dark:text-gray-100' : 'bg-gray-100/80 dark:bg-gray-800/90 text-gray-500 dark:text-gray-300 group-hover:bg-gray-200/80 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                    @if(!empty($block['icon']))
                                        {!! $block['icon'] !!}
                                    @elseif($firstItem && isset($firstItem['icon']))
                                        {!! $firstItem['icon'] !!}
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 19a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l2 2h10a2 2 0 0 1 2 2v11a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /></svg>
                                    @endif
                                </span>
                            @endif
                            <span x-show="!sidebarCollapsed" class="truncate font-semibold text-start text-xs sm:text-sm">{{ $groupTitle }}</span>
                        </div>

                        <div x-show="!sidebarCollapsed" class="flex items-center gap-1.5 shrink-0">
                            @if($isGroupActive)
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60">
                                    {{ count($validGroupItems) }}
                                </span>
                            @else
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full transition-colors border border-transparent"
                                      :class="isOpen() ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 dark:border-gray-600/50' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 dark:border-gray-700/60 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                    {{ count($validGroupItems) }}
                                </span>
                            @endif

                            {{-- آیکون هدایت یا باز شدن --}}
                            <template x-if="twoStepActive && !sidebarFilter">
                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-400 -rotate-90 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                                </svg>
                            </template>
                            <template x-if="!twoStepActive || sidebarFilter">
                                <svg :class="isOpen() ? 'rotate-90 {{ $isGroupActive ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300' }}' : '-rotate-90 text-gray-400 dark:text-gray-400'"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="w-3.5 h-3.5 transition-transform duration-200">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                                </svg>
                            </template>
                        </div>
                    </button>

                    {{-- Submenu (Expanded View / Step 2 View) --}}
                    <div x-show="isSubmenuOpen() && !sidebarCollapsed"
                         :class="(twoStepActive && !sidebarFilter) ? 'space-y-1 mt-1' : 'mt-1.5 mr-7 space-y-1 relative pr-3.5 before:absolute before:right-0 before:top-1.5 before:bottom-1.5 before:w-0.5 before:bg-indigo-200/90 dark:before:bg-indigo-800/80'"
                         class="transition-all">
                        @foreach($block['items'] as $item)
                            @if(empty($item['route']) || \Illuminate\Support\Facades\Route::has($item['route']))
                            @php
                                $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*');
                                $subItemTitle = $item['title'] ?? '';
                            @endphp
                            <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                               x-show="!sidebarFilter || '{{ addslashes($subItemTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()) || '{{ addslashes($groupTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase())"
                               class="flex items-center justify-between px-3 py-2 text-sm rounded-xl font-medium transition-all duration-200 relative group/child
                               {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/70 dark:border-indigo-800/50 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-gray-800/70' }}">
                                
                                <div class="flex items-center gap-2.5 truncate">
                                    <span class="w-1.5 h-1.5 rounded-full transition-colors {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-4 ring-indigo-100 dark:ring-indigo-950' : 'bg-gray-300 dark:bg-gray-600 group-hover/child:bg-indigo-400 dark:group-hover/child:bg-indigo-300' }}"></span>
                                    <span class="truncate">{{ $subItemTitle }}</span>
                                </div>

                                @if($isItemActive)
                                    <span class="w-1 h-3.5 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                                @endif
                            </a>
                            @endif
                        @endforeach
                    </div>

                    {{-- Floating Flyout Panel (When Sidebar is Collapsed) --}}
                    <div x-show="sidebarCollapsed && flyoutOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                         :style="'top: ' + flyoutTop + 'px; right: 76px;'"
                         class="fixed z-50 w-64 rounded-2xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-2xl border border-gray-200/90 dark:border-gray-700/90 p-2.5 before:absolute before:-right-4 before:top-0 before:bottom-0 before:w-4 before:content-['']">
                    
                    <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700/70 mb-1 flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900 dark:text-white truncate">{{ $groupTitle }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $isGroupActive ? 'bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60' : 'bg-gray-100 dark:bg-gray-700/90 text-gray-600 dark:text-gray-300 border border-transparent dark:border-gray-600/50' }}">
                            {{ count($validGroupItems) }}
                        </span>
                    </div>

                    <div class="space-y-1 max-h-72 overflow-y-auto custom-scrollbar">
                        @foreach($validGroupItems as $item)
                            @php $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*'); @endphp
                            <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                               class="flex items-center justify-between px-3 py-2 text-sm rounded-xl font-medium transition-colors
                               {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/50 dark:border-indigo-800/40' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/70 hover:text-gray-900 dark:hover:text-white' }}">
                                <div class="flex items-center gap-2.5 truncate">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                    <span class="truncate">{{ $item['title'] ?? '' }}</span>
                                </div>
                                @if($isItemActive)
                                    <span class="w-1 h-3.5 bg-indigo-600 dark:bg-indigo-400 rounded-full shrink-0"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

            @elseif($block['type'] === 'settings')
                @php
                    $isSettingsActive = request()->routeIs('settings.*') || request()->routeIs('user.settings.*') || request()->routeIs('user.settings.clients.*');
                    $settingsTitle = $block['title'] ?? 'تنظیمات';
                    $settingsIcon = $block['icon'] ?? null;
                    $settingsValidItems = $block['items'] ?? [];
                    $settingsJson = json_encode(array_column($settingsValidItems, 'title'));
                @endphp
                <div x-data="{
                        flyoutOpen: false,
                        flyoutTop: 0,
                        subItems: {{ $settingsJson }},
                        matches() {
                            if (!sidebarFilter) return true;
                            const q = sidebarFilter.toLowerCase();
                            if ('{{ addslashes($settingsTitle) }}'.toLowerCase().includes(q)) return true;
                            return this.subItems.some(title => title && title.toLowerCase().includes(q));
                        },
                        isOpen() {
                            return isMenuOpen('settings', {{ $isSettingsActive ? 'true' : 'false' }});
                        },
                        isSubmenuOpen() {
                            if (!twoStepActive) {
                                return this.isOpen();
                            }
                            if (sidebarFilter && sidebarFilter.trim().length > 0) return true;
                            return activeDrilldown === 'settings';
                        },
                        openFlyout(el) {
                            if (!sidebarCollapsed) return;
                            const rect = el.getBoundingClientRect();
                            const windowHeight = window.innerHeight;
                            const flyoutHeight = 360;
                            if (rect.top + flyoutHeight > windowHeight - 16) {
                                this.flyoutTop = Math.max(8, windowHeight - flyoutHeight - 16);
                            } else {
                                this.flyoutTop = Math.max(8, rect.top);
                            }
                            this.flyoutOpen = true;
                        },
                        closeFlyout() {
                            this.flyoutOpen = false;
                        }
                     }"
                     x-show="shouldShowGroup('settings') && matches()"
                     class="relative mt-2 pb-4 group/settingsblock"
                     @mouseenter="openFlyout($el)"
                     @mouseleave="closeFlyout()">

                    {{-- Header Button (نمایش در مرحله اول) --}}
                    <button x-show="isStep1()"
                            @click="if(!sidebarCollapsed) { if (twoStepActive && !sidebarFilter) { setDrilldown('settings', '{{ addslashes($settingsTitle) }}'); } else { toggleMenu('settings', {{ $isSettingsActive ? 'true' : 'false' }}); } } else { toggleSidebar(); }"
                            @if($isSettingsActive)
                                class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-bold transition-all duration-200 group border bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-200 border-indigo-100/90 dark:border-indigo-800/60 shadow-xs"
                            @else
                                class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 group border"
                                :class="isOpen() ? 'bg-gray-100/90 dark:bg-gray-800/90 text-gray-900 dark:text-white font-semibold border-gray-200/70 dark:border-gray-700/80 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 hover:text-gray-900 dark:hover:text-white border-transparent'"
                            @endif>
                        
                        <div class="flex items-center gap-2.5 min-w-0">
                            @if($isSettingsActive)
                                <span class="{{ $iconWrapperClass }} {{ $iconActiveWrapper }} [&>svg]:w-4 [&>svg]:h-4">
                                    @if(!empty($settingsIcon))
                                        {!! $settingsIcon !!}
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                            <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                        </svg>
                                    @endif
                                </span>
                            @else
                                <span class="{{ $iconWrapperClass }} [&>svg]:w-4 [&>svg]:h-4"
                                      :class="isOpen() ? 'bg-gray-200/90 dark:bg-gray-700 text-gray-800 dark:text-gray-100' : 'bg-gray-100/80 dark:bg-gray-800/90 text-gray-500 dark:text-gray-300 group-hover:bg-gray-200/80 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                    @if(!empty($settingsIcon))
                                        {!! $settingsIcon !!}
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                            <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                        </svg>
                                    @endif
                                </span>
                            @endif
                            <span x-show="!sidebarCollapsed" class="truncate font-semibold text-start text-xs sm:text-sm">{{ $settingsTitle }}</span>
                        </div>

                        <div x-show="!sidebarCollapsed" class="flex items-center gap-1.5 shrink-0">
                            @if($isSettingsActive)
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60">
                                    {{ count($settingsValidItems) }}
                                </span>
                            @else
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full transition-colors border border-transparent"
                                      :class="isOpen() ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 dark:border-gray-600/50' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 dark:border-gray-700/60 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                    {{ count($settingsValidItems) }}
                                </span>
                            @endif

                            {{-- آیکون هدایت یا باز شدن --}}
                            <template x-if="twoStepActive && !sidebarFilter">
                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-400 -rotate-90 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                                </svg>
                            </template>
                            <template x-if="!twoStepActive || sidebarFilter">
                                <svg :class="isOpen() ? 'rotate-90 {{ $isSettingsActive ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300' }}' : '-rotate-90 text-gray-400 dark:text-gray-400'"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="w-3.5 h-3.5 transition-transform duration-200">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                                </svg>
                            </template>
                        </div>
                    </button>

                    @php
                        $customGroupedSettings = $groupSettingsByModule($block['items']);
                    @endphp

                    <div x-show="isSubmenuOpen() && !sidebarCollapsed"
                         :class="(twoStepActive && !sidebarFilter) ? 'space-y-3 mt-1' : 'mt-1.5 mr-7 space-y-2 relative pr-3.5 before:absolute before:right-0 before:top-1.5 before:bottom-1.5 before:w-0.5 before:bg-indigo-200/90 dark:before:bg-indigo-800/80'"
                         class="transition-all">
                        @foreach($customGroupedSettings as $categoryTitle => $catItems)
                            @php
                                $validCatItems = collect($catItems)->filter(fn($i) => empty($i['route']) || \Illuminate\Support\Facades\Route::has($i['route']))->values();
                                $catTitlesJson = json_encode($validCatItems->pluck('title')->toArray());
                                $isCatActive = $validCatItems->contains(fn($i) => !empty($i['route']) && request()->routeIs($i['route'] . '*'));
                            @endphp
                            @if($validCatItems->isNotEmpty())
                            <div x-data="{
                                    catTitles: {{ $catTitlesJson }},
                                    hasCatMatches() {
                                        if (!sidebarFilter) return true;
                                        const q = sidebarFilter.toLowerCase();
                                        if ('{{ addslashes($categoryTitle) }}'.toLowerCase().includes(q)) return true;
                                        if ('{{ addslashes($settingsTitle) }}'.toLowerCase().includes(q)) return true;
                                        return this.catTitles.some(t => t && t.toLowerCase().includes(q));
                                    }
                                 }"
                                 x-show="hasCatMatches()"
                                 class="space-y-1">

                                {{-- Category Header in Drilldown Step 2 --}}
                                <div x-show="twoStepActive && !sidebarFilter"
                                     class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-gradient-to-r from-gray-100/90 via-gray-100/40 to-transparent dark:from-gray-800/90 dark:via-gray-800/50 dark:to-transparent border border-gray-200/70 dark:border-gray-700/70 mb-1 mt-2.5 first:mt-0">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isCatActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900/60' : 'bg-indigo-400 dark:bg-indigo-500' }}"></span>
                                        <span class="text-xs font-bold {{ $isCatActive ? 'text-indigo-700 dark:text-indigo-200' : 'text-gray-800 dark:text-gray-200' }} truncate">
                                            {{ $categoryTitle }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md {{ $isCatActive ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/80 dark:text-indigo-200' : 'bg-white/90 dark:bg-gray-700/90 text-gray-600 dark:text-gray-300 border border-gray-200/60 dark:border-gray-600/60' }}">
                                        {{ count($validCatItems) }}
                                    </span>
                                </div>

                                {{-- Category Header in Default Mode or Filter Mode --}}
                                <div x-show="!twoStepActive || sidebarFilter"
                                     class="flex items-center gap-2 pt-3 pb-1 pr-1 pl-1 text-[11px] font-bold text-indigo-700 dark:text-indigo-300 select-none first:pt-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900/60"></span>
                                    <span class="whitespace-nowrap tracking-wide">{{ $categoryTitle }}</span>
                                    <span class="h-px flex-1 bg-indigo-200/90 dark:bg-indigo-800/80"></span>
                                    <span class="text-[9px] font-bold text-indigo-700 dark:text-indigo-200 px-1.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/80 border border-indigo-200/70 dark:border-indigo-800/70">
                                        {{ count($validCatItems) }}
                                    </span>
                                </div>

                                {{-- Submenu Items inside this Category --}}
                                <div class="space-y-0.5">
                                    @foreach($validCatItems as $item)
                                        @php
                                            $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*');
                                            $subItemTitle = $item['title'] ?? '';
                                        @endphp
                                        <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                                           x-show="!sidebarFilter || '{{ addslashes($subItemTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()) || '{{ addslashes($categoryTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()) || '{{ addslashes($settingsTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase())"
                                           class="flex items-center justify-between px-3 py-2 text-sm rounded-xl font-medium transition-all duration-200 relative group/child
                                           {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/70 dark:border-indigo-800/50 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-gray-800/70' }}">
                                            
                                            <div class="flex items-center gap-2.5 truncate">
                                                <span class="w-1.5 h-1.5 rounded-full transition-colors {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-4 ring-indigo-100 dark:ring-indigo-950' : 'bg-gray-300 dark:bg-gray-600 group-hover/child:bg-indigo-400 dark:group-hover/child:bg-indigo-300' }}"></span>
                                                <span class="truncate">{{ $subItemTitle }}</span>
                                            </div>

                                            @if($isItemActive)
                                                <span class="w-1 h-3.5 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Floating Flyout Panel (When Sidebar is Collapsed) --}}
                    <div x-show="sidebarCollapsed && flyoutOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                         :style="'top: ' + flyoutTop + 'px; right: 76px;'"
                         class="fixed z-50 w-72 rounded-2xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-2xl border border-gray-200/90 dark:border-gray-700/90 p-2.5 before:absolute before:-right-4 before:top-0 before:bottom-0 before:w-4 before:content-['']">
                        
                        <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700/70 mb-2 flex items-center justify-between">
                            <span class="font-bold text-xs text-gray-900 dark:text-white truncate">{{ $settingsTitle }}</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $isSettingsActive ? 'bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60' : 'bg-gray-100 dark:bg-gray-700/90 text-gray-600 dark:text-gray-300 border border-transparent dark:border-gray-600/50' }}">
                                {{ count($settingsValidItems) }}
                            </span>
                        </div>

                        <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
                            @foreach($customGroupedSettings as $catTitle => $catList)
                                @php
                                    $validCatFlyoutItems = collect($catList)->filter(fn($i) => empty($i['route']) || \Illuminate\Support\Facades\Route::has($i['route']))->values();
                                @endphp
                                @if($validCatFlyoutItems->isNotEmpty())
                                    <div class="space-y-1">
                                        <div class="px-2 py-0.5 text-[10px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50/80 dark:bg-indigo-950/60 rounded-md border border-indigo-100/60 dark:border-indigo-800/50">
                                            {{ $catTitle }}
                                        </div>
                                        @foreach($validCatFlyoutItems as $item)
                                            @php $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*'); @endphp
                                            <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                                               class="flex items-center justify-between px-2.5 py-1.5 text-xs rounded-xl font-medium transition-colors
                                               {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/50 dark:border-indigo-800/40' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/70 hover:text-gray-900 dark:hover:text-white' }}">
                                                <div class="flex items-center gap-2 truncate">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                                    <span class="truncate">{{ $item['title'] ?? '' }}</span>
                                                </div>
                                                @if($isItemActive)
                                                    <span class="w-1 h-3 bg-indigo-600 dark:bg-indigo-400 rounded-full shrink-0"></span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

    @else
        {{-- ======================================================== --}}
        {{-- حالت ۲: منوی استاندارد سیستم (Default Mode)              --}}
        {{-- ======================================================== --}}

        {{-- پیشخوان --}}
        <div x-data="{
                tooltipOpen: false,
                tooltipTop: 0,
                openTooltip(el) {
                    if (!sidebarCollapsed) return;
                    const rect = el.getBoundingClientRect();
                    this.tooltipTop = Math.max(8, rect.top + (rect.height / 2) - 16);
                    this.tooltipOpen = true;
                },
                closeTooltip() {
                    this.tooltipOpen = false;
                }
             }"
             @mouseenter="openTooltip($el)"
             @mouseleave="closeTooltip()"
             x-show="isStep1() && (!sidebarFilter || 'پیشخوان'.toLowerCase().includes(sidebarFilter.toLowerCase()))"
             class="relative group/navitem">
            <a href="{{ route('user.dashboard') }}"
               class="{{ $linkBaseClass }} {{ request()->routeIs('user.dashboard') ? $linkActiveClass : $linkInactiveClass }}">
                
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="{{ $iconWrapperClass }} {{ request()->routeIs('user.dashboard') ? $iconActiveWrapper : $iconInactiveWrapper }} [&>svg]:w-4 [&>svg]:h-4">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                            <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                        </svg>
                    </span>
                    <span x-show="!sidebarCollapsed" x-transition class="truncate font-semibold">پیشخوان</span>
                </div>

                @if(request()->routeIs('user.dashboard'))
                    <span x-show="!sidebarCollapsed" class="w-1.5 h-4 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                @endif
            </a>

            {{-- Floating Tooltip when collapsed --}}
            <div x-show="sidebarCollapsed && tooltipOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                 :style="'top: ' + tooltipTop + 'px; right: 76px;'"
                 class="fixed z-50 px-3 py-1.5 rounded-xl bg-gray-900/95 dark:bg-gray-800/95 backdrop-blur-md text-white dark:text-gray-100 text-xs font-semibold shadow-xl border border-gray-700/60 pointer-events-none whitespace-nowrap flex items-center gap-1.5">
                <span>پیشخوان</span>
            </div>
        </div>

        {{-- گروه اختصاصی مشتریان --}}
        @if(count($clientsItems) > 0)
            @php
                $isClientsActive = request()->routeIs('user.clients.*')
                    || request()->routeIs('clients.*')
                    || request()->routeIs('user.settings.clients.*');
                $clientsTitle = $clientsGroupMeta['title'] ?? ('مدیریت '.config('clients.labels.plural', 'مشتریان'));
                $clientsIcon = $clientsGroupMeta['icon'] ?? null;
                $clientsItemsJson = json_encode(array_column($clientsItems, 'title'));
            @endphp
            <div x-data="{
                    flyoutOpen: false,
                    flyoutTop: 0,
                    subItems: {{ $clientsItemsJson }},
                    matches() {
                        if (!sidebarFilter) return true;
                        const q = sidebarFilter.toLowerCase();
                        if ('{{ addslashes($clientsTitle) }}'.toLowerCase().includes(q)) return true;
                        return this.subItems.some(title => title && title.toLowerCase().includes(q));
                    },
                    isOpen() {
                        return isMenuOpen('clients', {{ $isClientsActive ? 'true' : 'false' }});
                    },
                    isSubmenuOpen() {
                        if (!twoStepActive) {
                            return this.isOpen();
                        }
                        if (sidebarFilter && sidebarFilter.trim().length > 0) return true;
                        return activeDrilldown === 'clients';
                    },
                    openFlyout(el) {
                        if (!sidebarCollapsed) return;
                        const rect = el.getBoundingClientRect();
                        const windowHeight = window.innerHeight;
                        const flyoutHeight = Math.min(420, (this.subItems.length * 44) + 60);
                        if (rect.top + flyoutHeight > windowHeight - 16) {
                            this.flyoutTop = Math.max(8, windowHeight - flyoutHeight - 16);
                        } else {
                            this.flyoutTop = Math.max(8, rect.top);
                        }
                        this.flyoutOpen = true;
                    },
                    closeFlyout() {
                        this.flyoutOpen = false;
                    }
                 }"
                 x-show="shouldShowGroup('clients') && matches()"
                 class="relative mt-1 group/clientsblock"
                 @mouseenter="openFlyout($el)"
                 @mouseleave="closeFlyout()">

                {{-- Header Button (نمایش در مرحله اول) --}}
                <button x-show="isStep1()"
                        @click="if(!sidebarCollapsed) { if (twoStepActive && !sidebarFilter) { setDrilldown('clients', '{{ addslashes($clientsTitle) }}'); } else { toggleMenu('clients', {{ $isClientsActive ? 'true' : 'false' }}); } } else { toggleSidebar(); }"
                        @if($isClientsActive)
                            class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-bold transition-all duration-200 group border bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-200 border-indigo-100/90 dark:border-indigo-800/60 shadow-xs"
                        @else
                            class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 group border"
                            :class="isOpen() ? 'bg-gray-100/90 dark:bg-gray-800/90 text-gray-900 dark:text-white font-semibold border-gray-200/70 dark:border-gray-700/80 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 hover:text-gray-900 dark:hover:text-white border-transparent'"
                        @endif>

                    <div class="flex items-center gap-2.5 min-w-0">
                        @if($isClientsActive)
                            <span class="{{ $iconWrapperClass }} {{ $iconActiveWrapper }} [&>svg]:w-4 [&>svg]:h-4">
                                @if(!empty($clientsIcon))
                                    {!! $clientsIcon !!}
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                        <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                        <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                                    </svg>
                                @endif
                            </span>
                        @else
                            <span class="{{ $iconWrapperClass }} [&>svg]:w-4 [&>svg]:h-4"
                                  :class="isOpen() ? 'bg-gray-200/90 dark:bg-gray-700 text-gray-800 dark:text-gray-100' : 'bg-gray-100/80 dark:bg-gray-800/90 text-gray-500 dark:text-gray-300 group-hover:bg-gray-200/80 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                @if(!empty($clientsIcon))
                                    {!! $clientsIcon !!}
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                        <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                        <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                                    </svg>
                                @endif
                            </span>
                        @endif
                        <span x-show="!sidebarCollapsed" class="truncate font-semibold text-start text-xs sm:text-sm">{{ $clientsTitle }}</span>
                    </div>

                    <div x-show="!sidebarCollapsed" class="flex items-center gap-1.5 shrink-0">
                        @if($isClientsActive)
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60">
                                {{ count($clientsItems) }}
                            </span>
                        @else
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full transition-colors border border-transparent"
                                  :class="isOpen() ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 dark:border-gray-600/50' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 dark:border-gray-700/60 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                {{ count($clientsItems) }}
                            </span>
                        @endif

                        {{-- آیکون هدایت یا باز شدن --}}
                        <template x-if="twoStepActive && !sidebarFilter">
                            <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-400 -rotate-90 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                            </svg>
                        </template>
                        <template x-if="!twoStepActive || sidebarFilter">
                            <svg :class="isOpen() ? 'rotate-90 {{ $isClientsActive ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300' }}' : '-rotate-90 text-gray-400 dark:text-gray-400'"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="w-3.5 h-3.5 transition-transform duration-200">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                            </svg>
                        </template>
                    </div>
                </button>

                {{-- Submenu (Expanded View / Step 2 View) --}}
                <div x-show="isSubmenuOpen() && !sidebarCollapsed"
                     :class="(twoStepActive && !sidebarFilter) ? 'space-y-1 mt-1' : 'mt-1.5 mr-7 space-y-1 relative pr-3.5 before:absolute before:right-0 before:top-1.5 before:bottom-1.5 before:w-0.5 before:bg-indigo-200/90 dark:before:bg-indigo-800/80'"
                     class="transition-all">
                    @foreach($clientsItems as $item)
                        @php
                            $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*');
                            $subItemTitle = $item['title'] ?? '';
                        @endphp
                        <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                           x-show="!sidebarFilter || '{{ addslashes($subItemTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()) || '{{ addslashes($clientsTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase())"
                           class="flex items-center justify-between px-3 py-2 text-sm rounded-xl font-medium transition-all duration-200 relative group/child
                           {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/70 dark:border-indigo-800/50 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-gray-800/70' }}">
                            
                            <div class="flex items-center gap-2.5 truncate">
                                <span class="w-1.5 h-1.5 rounded-full transition-colors {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-4 ring-indigo-100 dark:ring-indigo-950' : 'bg-gray-300 dark:bg-gray-600 group-hover/child:bg-indigo-400 dark:group-hover/child:bg-indigo-300' }}"></span>
                                <span class="truncate">{{ $subItemTitle }}</span>
                            </div>

                            @if($isItemActive)
                                <span class="w-1 h-3.5 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                            @endif
                        </a>
                    @endforeach
                </div>

                {{-- Floating Flyout Panel (When Sidebar is Collapsed) --}}
                <div x-show="sidebarCollapsed && flyoutOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                     :style="'top: ' + flyoutTop + 'px; right: 76px;'"
                     class="fixed z-50 w-64 rounded-2xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-2xl border border-gray-200/90 dark:border-gray-700/90 p-2.5 before:absolute before:-right-4 before:top-0 before:bottom-0 before:w-4 before:content-['']">
                    
                    <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700/70 mb-1 flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900 dark:text-white truncate">{{ $clientsTitle }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $isClientsActive ? 'bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60' : 'bg-gray-100 dark:bg-gray-700/90 text-gray-600 dark:text-gray-300 border border-transparent dark:border-gray-600/50' }}">
                            {{ count($clientsItems) }}
                        </span>
                    </div>

                    <div class="space-y-1 max-h-72 overflow-y-auto custom-scrollbar">
                        @foreach($clientsItems as $item)
                            @php $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*'); @endphp
                            <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                               class="flex items-center justify-between px-3 py-2 text-sm rounded-xl font-medium transition-colors
                               {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/50 dark:border-indigo-800/40' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/70 hover:text-gray-900 dark:hover:text-white' }}">
                                <div class="flex items-center gap-2.5 truncate">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                    <span class="truncate">{{ $item['title'] ?? '' }}</span>
                                </div>
                                @if($isItemActive)
                                    <span class="w-1 h-3.5 bg-indigo-600 dark:bg-indigo-400 rounded-full shrink-0"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- آیتم‌های تکی (Standalone Items) --}}
        @foreach($menuItems as $item)
            @php
                try {
                    $routeExists = !empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']);
                    $routeUrl = $routeExists ? route($item['route']) : '#';
                } catch (\Exception $e) {
                    $routeExists = false;
                    $routeUrl = '#';
                }
                $isActive = $routeExists && request()->routeIs($item['route'] . '*');
                $itemTitle = $item['title'] ?? '';
            @endphp
            <div x-data="{
                    tooltipOpen: false,
                    tooltipTop: 0,
                    openTooltip(el) {
                        if (!sidebarCollapsed) return;
                        const rect = el.getBoundingClientRect();
                        this.tooltipTop = Math.max(8, rect.top + (rect.height / 2) - 16);
                        this.tooltipOpen = true;
                    },
                    closeTooltip() {
                        this.tooltipOpen = false;
                    }
                 }"
                 @mouseenter="openTooltip($el)"
                 @mouseleave="closeTooltip()"
                 x-show="isStep1() && (!sidebarFilter || '{{ addslashes($itemTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()))"
                 class="relative group/navitem">
                <a href="{{ $routeUrl }}"
                   class="{{ $linkBaseClass }} {{ $isActive ? $linkActiveClass : $linkInactiveClass }}">
                    
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="{{ $iconWrapperClass }} {{ $isActive ? $iconActiveWrapper : $iconInactiveWrapper }} [&>svg]:w-4 [&>svg]:h-4">
                            {!! $item['icon'] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 8v4l3 3" /></svg>' !!}
                        </span>
                        <span x-show="!sidebarCollapsed" x-transition class="truncate font-semibold">{{ $itemTitle }}</span>
                    </div>

                    @if($isActive)
                        <span x-show="!sidebarCollapsed" class="w-1.5 h-4 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                    @endif
                </a>

                {{-- Floating Tooltip when collapsed --}}
                <div x-show="sidebarCollapsed && tooltipOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                     :style="'top: ' + tooltipTop + 'px; right: 76px;'"
                     class="fixed z-50 px-3 py-1.5 rounded-xl bg-gray-900/95 dark:bg-gray-800/95 backdrop-blur-md text-white dark:text-gray-100 text-xs font-semibold shadow-xl border border-gray-700/60 pointer-events-none whitespace-nowrap flex items-center gap-1.5">
                    <span>{{ $itemTitle }}</span>
                </div>
            </div>
        @endforeach

        {{-- گروه‌های ماژول‌ها (Module Groups) --}}
        @foreach($menuGroups as $group)
            @php
                $validGroupItems = collect($group['items'])->filter(fn($i) => empty($i['route']) || \Illuminate\Support\Facades\Route::has($i['route']))->values()->all();
                $groupModule = $group['module'];
                $isGroupActive = request()->routeIs('user.' . $groupModule . '.*')
                    || request()->routeIs($groupModule . '.*')
                    || collect($group['items'])->contains(fn($i) => !empty($i['route']) && request()->routeIs($i['route'] . '*'));
                $groupTitle = $group['title'] ?? $group['module_name'] ?? $groupModule;
                $firstItem = $group['items'][0] ?? null;
                $itemsJson = json_encode(array_column($validGroupItems, 'title'));
            @endphp
            @if(count($validGroupItems) > 0)
            <div x-data="{
                    flyoutOpen: false,
                    flyoutTop: 0,
                    subItems: {{ $itemsJson }},
                    matches() {
                        if (!sidebarFilter) return true;
                        const q = sidebarFilter.toLowerCase();
                        if ('{{ addslashes($groupTitle) }}'.toLowerCase().includes(q)) return true;
                        return this.subItems.some(title => title && title.toLowerCase().includes(q));
                    },
                    isOpen() {
                        return isMenuOpen('{{ $groupModule }}', {{ $isGroupActive ? 'true' : 'false' }});
                    },
                    isSubmenuOpen() {
                        if (!twoStepActive) {
                            return this.isOpen();
                        }
                        if (sidebarFilter && sidebarFilter.trim().length > 0) return true;
                        return activeDrilldown === '{{ $groupModule }}';
                    },
                    openFlyout(el) {
                        if (!sidebarCollapsed) return;
                        const rect = el.getBoundingClientRect();
                        const windowHeight = window.innerHeight;
                        const flyoutHeight = Math.min(420, (this.subItems.length * 44) + 60);
                        if (rect.top + flyoutHeight > windowHeight - 16) {
                            this.flyoutTop = Math.max(8, windowHeight - flyoutHeight - 16);
                        } else {
                            this.flyoutTop = Math.max(8, rect.top);
                        }
                        this.flyoutOpen = true;
                    },
                    closeFlyout() {
                        this.flyoutOpen = false;
                    }
                 }"
                 x-show="shouldShowGroup('{{ $groupModule }}') && matches()"
                 class="relative mt-1 group/groupblock"
                 @mouseenter="openFlyout($el)"
                 @mouseleave="closeFlyout()">

                {{-- Header Button (نمایش در مرحله اول) --}}
                <button x-show="isStep1()"
                        @click="if(!sidebarCollapsed) { if (twoStepActive && !sidebarFilter) { setDrilldown('{{ $groupModule }}', '{{ addslashes($groupTitle) }}'); } else { toggleMenu('{{ $groupModule }}', {{ $isGroupActive ? 'true' : 'false' }}); } } else { toggleSidebar(); }"
                        @if($isGroupActive)
                            class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-bold transition-all duration-200 group border bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-200 border-indigo-100/90 dark:border-indigo-800/60 shadow-xs"
                        @else
                            class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 group border"
                            :class="isOpen() ? 'bg-gray-100/90 dark:bg-gray-800/90 text-gray-900 dark:text-white font-semibold border-gray-200/70 dark:border-gray-700/80 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 hover:text-gray-900 dark:hover:text-white border-transparent'"
                        @endif>

                    <div class="flex items-center gap-2.5 min-w-0">
                        @if($isGroupActive)
                            <span class="{{ $iconWrapperClass }} {{ $iconActiveWrapper }} [&>svg]:w-4 [&>svg]:h-4">
                                @if(!empty($group['icon']))
                                    {!! $group['icon'] !!}
                                @elseif($firstItem && isset($firstItem['icon']))
                                    {!! $firstItem['icon'] !!}
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 19a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l2 2h10a2 2 0 0 1 2 2v11a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /></svg>
                                @endif
                            </span>
                        @else
                            <span class="{{ $iconWrapperClass }} [&>svg]:w-4 [&>svg]:h-4"
                                  :class="isOpen() ? 'bg-gray-200/90 dark:bg-gray-700 text-gray-800 dark:text-gray-100' : 'bg-gray-100/80 dark:bg-gray-800/90 text-gray-500 dark:text-gray-300 group-hover:bg-gray-200/80 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                @if(!empty($group['icon']))
                                    {!! $group['icon'] !!}
                                @elseif($firstItem && isset($firstItem['icon']))
                                    {!! $firstItem['icon'] !!}
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 19a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l2 2h10a2 2 0 0 1 2 2v11a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /></svg>
                                @endif
                            </span>
                        @endif
                        <span x-show="!sidebarCollapsed" class="truncate font-semibold text-start text-xs sm:text-sm">{{ $groupTitle }}</span>
                    </div>

                    <div x-show="!sidebarCollapsed" class="flex items-center gap-1.5 shrink-0">
                        @if($isGroupActive)
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60">
                                {{ count($validGroupItems) }}
                            </span>
                        @else
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full transition-colors border border-transparent"
                                  :class="isOpen() ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 dark:border-gray-600/50' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 dark:border-gray-700/60 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                {{ count($validGroupItems) }}
                            </span>
                        @endif

                        {{-- آیکون هدایت یا باز شدن --}}
                        <template x-if="twoStepActive && !sidebarFilter">
                            <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-400 -rotate-90 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                            </svg>
                        </template>
                        <template x-if="!twoStepActive || sidebarFilter">
                            <svg :class="isOpen() ? 'rotate-90 {{ $isGroupActive ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300' }}' : '-rotate-90 text-gray-400 dark:text-gray-400'"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="w-3.5 h-3.5 transition-transform duration-200">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                            </svg>
                        </template>
                    </div>
                </button>

                {{-- Submenu (Expanded View / Step 2 View) --}}
                <div x-show="isSubmenuOpen() && !sidebarCollapsed"
                     :class="(twoStepActive && !sidebarFilter) ? 'space-y-1 mt-1' : 'mt-1.5 mr-7 space-y-1 relative pr-3.5 before:absolute before:right-0 before:top-1.5 before:bottom-1.5 before:w-0.5 before:bg-indigo-200/90 dark:before:bg-indigo-800/80'"
                     class="transition-all">
                    @foreach($group['items'] as $item)
                        @if(empty($item['route']) || \Illuminate\Support\Facades\Route::has($item['route']))
                        @php
                            $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*');
                            $subItemTitle = $item['title'] ?? '';
                        @endphp
                        <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                           x-show="!sidebarFilter || '{{ addslashes($subItemTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()) || '{{ addslashes($groupTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase())"
                           class="flex items-center justify-between px-3 py-2 text-sm rounded-xl font-medium transition-all duration-200 relative group/child
                           {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/70 dark:border-indigo-800/50 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-gray-800/70' }}">
                            
                            <div class="flex items-center gap-2.5 truncate">
                                <span class="w-1.5 h-1.5 rounded-full transition-colors {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-4 ring-indigo-100 dark:ring-indigo-950' : 'bg-gray-300 dark:bg-gray-600 group-hover/child:bg-indigo-400 dark:group-hover/child:bg-indigo-300' }}"></span>
                                <span class="truncate">{{ $subItemTitle }}</span>
                            </div>

                            @if($isItemActive)
                                <span class="w-1 h-3.5 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                            @endif
                        </a>
                        @endif
                    @endforeach
                </div>

                {{-- Floating Flyout Panel (When Sidebar is Collapsed) --}}
                <div x-show="sidebarCollapsed && flyoutOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                     :style="'top: ' + flyoutTop + 'px; right: 76px;'"
                     class="fixed z-50 w-64 rounded-2xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-2xl border border-gray-200/90 dark:border-gray-700/90 p-2.5 before:absolute before:-right-4 before:top-0 before:bottom-0 before:w-4 before:content-['']">
                    
                    <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700/70 mb-1 flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900 dark:text-white truncate">{{ $groupTitle }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $isGroupActive ? 'bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60' : 'bg-gray-100 dark:bg-gray-700/90 text-gray-600 dark:text-gray-300 border border-transparent dark:border-gray-600/50' }}">
                            {{ count($validGroupItems) }}
                        </span>
                    </div>

                    <div class="space-y-1 max-h-72 overflow-y-auto custom-scrollbar">
                        @foreach($validGroupItems as $item)
                            @php $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*'); @endphp
                            <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                               class="flex items-center justify-between px-3 py-2 text-sm rounded-xl font-medium transition-colors
                               {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/50 dark:border-indigo-800/40' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/70 hover:text-gray-900 dark:hover:text-white' }}">
                                <div class="flex items-center gap-2.5 truncate">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                    <span class="truncate">{{ $item['title'] ?? '' }}</span>
                                </div>
                                @if($isItemActive)
                                    <span class="w-1 h-3.5 bg-indigo-600 dark:bg-indigo-400 rounded-full shrink-0"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        {{-- گروه تنظیمات سیستم (Settings Group) --}}
        @if(count($settingsItems) > 0)
            @php
                $isSettingsActive = request()->routeIs('settings.*') || request()->routeIs('user.settings.*') || request()->routeIs('user.settings.clients.*');
                $settingsTitle = $settingsGroupMeta['title'] ?? 'تنظیمات سیستم';
                $settingsIcon = $settingsGroupMeta['icon'] ?? null;
                $settingsValidItems = $settingsItems;
                $settingsJson = json_encode(array_column($settingsValidItems, 'title'));
            @endphp
            <div x-data="{
                    flyoutOpen: false,
                    flyoutTop: 0,
                    subItems: {{ $settingsJson }},
                    matches() {
                        if (!sidebarFilter) return true;
                        const q = sidebarFilter.toLowerCase();
                        if ('{{ addslashes($settingsTitle) }}'.toLowerCase().includes(q)) return true;
                        return this.subItems.some(title => title && title.toLowerCase().includes(q));
                    },
                    isOpen() {
                        return isMenuOpen('settings', {{ $isSettingsActive ? 'true' : 'false' }});
                    },
                    isSubmenuOpen() {
                        if (!twoStepActive) {
                            return this.isOpen();
                        }
                        if (sidebarFilter && sidebarFilter.trim().length > 0) return true;
                        return activeDrilldown === 'settings';
                    },
                    openFlyout(el) {
                        if (!sidebarCollapsed) return;
                        const rect = el.getBoundingClientRect();
                        const windowHeight = window.innerHeight;
                        const flyoutHeight = 360;
                        if (rect.top + flyoutHeight > windowHeight - 16) {
                            this.flyoutTop = Math.max(8, windowHeight - flyoutHeight - 16);
                        } else {
                            this.flyoutTop = Math.max(8, rect.top);
                        }
                        this.flyoutOpen = true;
                    },
                    closeFlyout() {
                        this.flyoutOpen = false;
                    }
                 }"
                 x-show="shouldShowGroup('settings') && matches()"
                 class="relative mt-2 pb-4 group/settingsblock"
                 @mouseenter="openFlyout($el)"
                 @mouseleave="closeFlyout()">

                {{-- Header Button (نمایش در مرحله اول) --}}
                <button x-show="isStep1()"
                        @click="if(!sidebarCollapsed) { if (twoStepActive && !sidebarFilter) { setDrilldown('settings', '{{ addslashes($settingsTitle) }}'); } else { toggleMenu('settings', {{ $isSettingsActive ? 'true' : 'false' }}); } } else { toggleSidebar(); }"
                        @if($isSettingsActive)
                            class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-bold transition-all duration-200 group border bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-200 border-indigo-100/90 dark:border-indigo-800/60 shadow-xs"
                        @else
                            class="w-full flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 group border"
                            :class="isOpen() ? 'bg-gray-100/90 dark:bg-gray-800/90 text-gray-900 dark:text-white font-semibold border-gray-200/70 dark:border-gray-700/80 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 hover:text-gray-900 dark:hover:text-white border-transparent'"
                        @endif>
                    
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if($isSettingsActive)
                            <span class="{{ $iconWrapperClass }} {{ $iconActiveWrapper }} [&>svg]:w-4 [&>svg]:h-4">
                                @if(!empty($settingsIcon))
                                    {!! $settingsIcon !!}
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                    </svg>
                                @endif
                            </span>
                        @else
                            <span class="{{ $iconWrapperClass }} [&>svg]:w-4 [&>svg]:h-4"
                                  :class="isOpen() ? 'bg-gray-200/90 dark:bg-gray-700 text-gray-800 dark:text-gray-100' : 'bg-gray-100/80 dark:bg-gray-800/90 text-gray-500 dark:text-gray-300 group-hover:bg-gray-200/80 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                @if(!empty($settingsIcon))
                                    {!! $settingsIcon !!}
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                    </svg>
                                @endif
                            </span>
                        @endif
                        <span x-show="!sidebarCollapsed" class="truncate font-semibold text-start text-xs sm:text-sm">{{ $settingsTitle }}</span>
                    </div>

                    <div x-show="!sidebarCollapsed" class="flex items-center gap-1.5 shrink-0">
                        @if($isSettingsActive)
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60">
                                {{ count($settingsItems) }}
                            </span>
                        @else
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full transition-colors border border-transparent"
                                  :class="isOpen() ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 dark:border-gray-600/50' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 dark:border-gray-700/60 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 group-hover:text-gray-800 dark:group-hover:text-white'">
                                {{ count($settingsItems) }}
                            </span>
                        @endif

                        {{-- آیکون هدایت یا باز شدن --}}
                        <template x-if="twoStepActive && !sidebarFilter">
                            <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-400 -rotate-90 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                            </svg>
                        </template>
                        <template x-if="!twoStepActive || sidebarFilter">
                            <svg :class="isOpen() ? 'rotate-90 {{ $isSettingsActive ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300' }}' : '-rotate-90 text-gray-400 dark:text-gray-400'"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="w-3.5 h-3.5 transition-transform duration-200">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 6l-6 6l6 6" />
                            </svg>
                        </template>
                    </div>
                </button>

                @php
                    $defaultGroupedSettings = $groupSettingsByModule($settingsItems);
                @endphp

                <div x-show="isSubmenuOpen() && !sidebarCollapsed"
                     :class="(twoStepActive && !sidebarFilter) ? 'space-y-3 mt-1' : 'mt-1.5 mr-7 space-y-2 relative pr-3.5 before:absolute before:right-0 before:top-1.5 before:bottom-1.5 before:w-0.5 before:bg-indigo-200/90 dark:before:bg-indigo-800/80'"
                     class="transition-all">
                    @foreach($defaultGroupedSettings as $categoryTitle => $catItems)
                        @php
                            $validCatItems = collect($catItems)->filter(fn($i) => empty($i['route']) || \Illuminate\Support\Facades\Route::has($i['route']))->values();
                            $catTitlesJson = json_encode($validCatItems->pluck('title')->toArray());
                            $isCatActive = $validCatItems->contains(fn($i) => !empty($i['route']) && request()->routeIs($i['route'] . '*'));
                        @endphp
                        @if($validCatItems->isNotEmpty())
                        <div x-data="{
                                catTitles: {{ $catTitlesJson }},
                                hasCatMatches() {
                                    if (!sidebarFilter) return true;
                                    const q = sidebarFilter.toLowerCase();
                                    if ('{{ addslashes($categoryTitle) }}'.toLowerCase().includes(q)) return true;
                                    if ('{{ addslashes($settingsTitle) }}'.toLowerCase().includes(q)) return true;
                                    return this.catTitles.some(t => t && t.toLowerCase().includes(q));
                                }
                             }"
                             x-show="hasCatMatches()"
                             class="space-y-1">

                            {{-- Category Header in Drilldown Step 2 --}}
                            <div x-show="twoStepActive && !sidebarFilter"
                                 class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-gradient-to-r from-gray-100/90 via-gray-100/40 to-transparent dark:from-gray-800/90 dark:via-gray-800/50 dark:to-transparent border border-gray-200/70 dark:border-gray-700/70 mb-1 mt-2.5 first:mt-0">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isCatActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900/60' : 'bg-indigo-400 dark:bg-indigo-500' }}"></span>
                                    <span class="text-xs font-bold {{ $isCatActive ? 'text-indigo-700 dark:text-indigo-200' : 'text-gray-800 dark:text-gray-200' }} truncate">
                                        {{ $categoryTitle }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md {{ $isCatActive ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/80 dark:text-indigo-200' : 'bg-white/90 dark:bg-gray-700/90 text-gray-600 dark:text-gray-300 border border-gray-200/60 dark:border-gray-600/60' }}">
                                    {{ count($validCatItems) }}
                                </span>
                            </div>

                            {{-- Category Header in Default Mode or Filter Mode --}}
                            <div x-show="!twoStepActive || sidebarFilter"
                                 class="flex items-center gap-2 pt-3 pb-1 pr-1 pl-1 text-[11px] font-bold text-indigo-700 dark:text-indigo-300 select-none first:pt-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900/60"></span>
                                <span class="whitespace-nowrap tracking-wide">{{ $categoryTitle }}</span>
                                <span class="h-px flex-1 bg-indigo-200/90 dark:bg-indigo-800/80"></span>
                                <span class="text-[9px] font-bold text-indigo-700 dark:text-indigo-200 px-1.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/80 border border-indigo-200/70 dark:border-indigo-800/70">
                                    {{ count($validCatItems) }}
                                </span>
                            </div>

                            {{-- Submenu Items inside this Category --}}
                            <div class="space-y-0.5">
                                @foreach($validCatItems as $item)
                                    @php
                                        $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*');
                                        $subItemTitle = $item['title'] ?? '';
                                    @endphp
                                    <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                                       x-show="!sidebarFilter || '{{ addslashes($subItemTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()) || '{{ addslashes($categoryTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase()) || '{{ addslashes($settingsTitle) }}'.toLowerCase().includes(sidebarFilter.toLowerCase())"
                                       class="flex items-center justify-between px-3 py-2 text-sm rounded-xl font-medium transition-all duration-200 relative group/child
                                       {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/70 dark:border-indigo-800/50 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-gray-800/70' }}">
                                        
                                        <div class="flex items-center gap-2.5 truncate">
                                            <span class="w-1.5 h-1.5 rounded-full transition-colors {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-4 ring-indigo-100 dark:ring-indigo-950' : 'bg-gray-300 dark:bg-gray-600 group-hover/child:bg-indigo-400 dark:group-hover/child:bg-indigo-300' }}"></span>
                                            <span class="truncate">{{ $subItemTitle }}</span>
                                        </div>

                                        @if($isItemActive)
                                            <span class="w-1 h-3.5 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                {{-- Floating Flyout Panel (When Sidebar is Collapsed) --}}
                <div x-show="sidebarCollapsed && flyoutOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                     :style="'top: ' + flyoutTop + 'px; right: 76px;'"
                     class="fixed z-50 w-72 rounded-2xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-2xl border border-gray-200/90 dark:border-gray-700/90 p-2.5 before:absolute before:-right-4 before:top-0 before:bottom-0 before:w-4 before:content-['']">
                    
                    <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700/70 mb-2 flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900 dark:text-white truncate">{{ $settingsTitle }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $isSettingsActive ? 'bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/60' : 'bg-gray-100 dark:bg-gray-700/90 text-gray-600 dark:text-gray-300 border border-transparent dark:border-gray-600/50' }}">
                            {{ count($settingsItems) }}
                        </span>
                    </div>

                    <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
                        @foreach($defaultGroupedSettings as $catTitle => $catList)
                            @php
                                $validCatFlyoutItems = collect($catList)->filter(fn($i) => empty($i['route']) || \Illuminate\Support\Facades\Route::has($i['route']))->values();
                            @endphp
                            @if($validCatFlyoutItems->isNotEmpty())
                                <div class="space-y-1">
                                    <div class="px-2 py-0.5 text-[10px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50/80 dark:bg-indigo-950/60 rounded-md border border-indigo-100/60 dark:border-indigo-800/50">
                                        {{ $catTitle }}
                                    </div>
                                    @foreach($validCatFlyoutItems as $item)
                                        @php $isItemActive = !empty($item['route']) && request()->routeIs($item['route'] . '*'); @endphp
                                        <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}"
                                           class="flex items-center justify-between px-2.5 py-1.5 text-xs rounded-xl font-medium transition-colors
                                           {{ $isItemActive ? 'text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/60 font-bold border border-indigo-100/50 dark:border-indigo-800/40' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/70 hover:text-gray-900 dark:hover:text-white' }}">
                                            <div class="flex items-center gap-2 truncate">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $isItemActive ? 'bg-indigo-600 dark:bg-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                                <span class="truncate">{{ $item['title'] ?? '' }}</span>
                                            </div>
                                            @if($isItemActive)
                                                <span class="w-1 h-3 bg-indigo-600 dark:bg-indigo-400 rounded-full shrink-0"></span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

</div>
