<header class="fixed w-full top-0 z-50 transition-all duration-300 bg-white/70 dark:bg-gray-950/70 backdrop-blur-xl border-b border-white/60 dark:border-gray-800/60 shadow-[0_4px_30px_rgb(0,0,0,0.03)]">

    {{-- نوار اصلی هدر --}}
    <div class="max-w-5xl mx-auto px-5 h-16 flex items-center justify-between gap-4">

        {{-- دکمه منوی همبرگری (فقط در موبایل) و لوگو --}}
        <div class="flex items-center gap-3 flex-shrink-0">
            <button onclick="toggleMobileMenu()" class="md:hidden p-2 -mr-2 text-gray-600 dark:text-gray-300 bg-white/60 dark:bg-gray-800/60 border border-white/80 dark:border-gray-700/80 hover:bg-white rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>

            <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                @if(isset($appLogo) && $appLogo)
                    <img src="{{ $appLogo }}" alt="{{ $appName ?? 'دکتر فیت' }}" class="h-8 sm:h-10 w-auto object-contain">
                @else
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#3F7D20] flex items-center justify-center text-white shadow-md shadow-[#3F7D20]/20">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                @endif
                <span class="font-extrabold text-lg sm:text-2xl tracking-tight text-gray-900 dark:text-white">{{ $appName ?? 'دکتر فیت' }}</span>
            </a>
        </div>

        {{-- اکشن‌ها --}}
        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
            {{-- انتخاب موقعیت مکانی --}}
            @if(isset($isMarketActive) && $isMarketActive && class_exists(\Modules\Market\Entities\MarketSetting::class) && \Modules\Market\Entities\MarketSetting::getValue('orders.enable_geolocation_ordering', false))
                @php
                    $currentLoc = \Modules\Market\App\Helpers\GeolocationHelper::getClientLocation();
                    $locText = ($currentLoc && !empty($currentLoc['city'])) ? $currentLoc['city'] : 'انتخاب موقعیت';
                @endphp
                <button onclick="Livewire.dispatch('openLocationModal')" class="hidden md:flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gray-100/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 border border-gray-200/50 dark:border-gray-700/50 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-xs font-bold">
                    <svg class="w-4 h-4 text-[#3F7D20]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span id="header-location-text">{{ $locText }}</span>
                </button>
            @endif

            {{-- تغییر تم (فقط در دسکتاپ) --}}
            <button onclick="setAppThemeMode(localStorage.theme === 'dark' ? 'light' : 'dark')" class="hidden md:block p-2 text-gray-500 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 rounded-xl transition-colors" title="تغییر قالب (تاریک/روشن)">
                <svg class="w-5 h-5 hidden dark:block text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                <svg class="w-5 h-5 block dark:hidden text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
            </button>

            @if(request()->is('chat') || request()->is('chat/*'))
                {{-- در صفحه چت: افزودن دکمه‌های سبد خرید و گفتگوی جدید --}}
                @if(isset($isMarketActive) && $isMarketActive)
                    @livewire('market::web.cart-counter')
                @endif

                <button onclick="window.Livewire.dispatch('resetSession')" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#3F7D20]/10 text-[#3F7D20] dark:bg-[#3F7D20]/20 dark:text-[#5cba2f] border border-[#3F7D20]/30 text-xs font-bold hover:bg-[#3F7D20] hover:text-white transition-all shadow-sm" title="گفتگوی جدید">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
                        <path d="M21 3v5h-5" />
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
                        <path d="M3 21v-5h5" />
                    </svg>
                    <span>گفتگوی جدید</span>
                </button>
            @endif

            {{--@if(!request()->is('chat*') && auth()->check() && isset($isMarketActive) && $isMarketActive)
                <div class="hidden md:block">
                    @livewire('market::web.cart-counter')
                </div>
            @endif--}}

            {{--            <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 mx-1 hidden md:block"></div>--}}

            {{-- دکمه حساب کاربری / ورود --}}
            {{--@auth
                <a href="{{ url('/clients/dashboard') }}" class="hidden md:flex items-center gap-2 p-2 sm:px-3 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                    </svg>
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200 hidden sm:block">حساب کاربری</span>
                </a>
            @else
                <a href="{{ url('/clients/dashboard') }}" class="hidden md:flex items-center gap-2 px-4 py-2 bg-[#3F7D20] text-white rounded-xl shadow-[0_8px_15px_-5px_rgba(63,125,32,0.4)] hover:bg-[#2d5a17] transition-all">
                    <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                    </svg>
                    <span class="text-sm font-bold hidden sm:block">ورود / ثبت‌نام</span>
                </a>
            @endauth--}}
        </div>
    </div>
</header>

{{-- ================= نوار کناری موبایل (Mobile Sidebar) ================= --}}
<div id="mobile-menu-overlay" onclick="toggleMobileMenu()" class="fixed inset-0 bg-gray-900/60 dark:bg-black/80 z-[80] opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"></div>

<aside id="mobile-menu-sidebar" class="fixed top-0 right-0 h-full w-4/5 max-w-sm bg-white dark:bg-gray-950 z-[90] transform translate-x-full transition-transform duration-300 ease-in-out md:hidden flex flex-col shadow-2xl border-l border-gray-100 dark:border-gray-800">
    {{-- سربرگ سایدبار --}}
    <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-800">
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            @if(isset($appLogo) && $appLogo)
                <img src="{{ $appLogo }}" alt="{{ $appName ?? 'دکتر فیت' }}" class="h-8 w-auto">
            @endif
            <span class="font-extrabold text-lg text-[#3F7D20]">{{ $appName ?? 'دکتر فیت' }}</span>
        </a>
        <button onclick="toggleMobileMenu()" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    @if(isset($isMarketActive) && $isMarketActive && class_exists(\Modules\Market\Entities\MarketSetting::class) && \Modules\Market\Entities\MarketSetting::getValue('orders.enable_geolocation_ordering', false))
        @php
            $currentLoc = \Modules\Market\App\Helpers\GeolocationHelper::getClientLocation();
            $locText = ($currentLoc && !empty($currentLoc['city'])) ? $currentLoc['city'] : 'انتخاب موقعیت';
        @endphp
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30">
            <button onclick="Livewire.dispatch('openLocationModal')" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 shadow-sm transition-all active:scale-95">
                <svg class="w-4 h-4 text-[#3F7D20]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span id="mobile-location-text">موقعیت شما: {{ $locText }}</span>
            </button>
        </div>
    @endif

    {{-- محتوای سایدبار --}}
    <div class="flex-1 overflow-y-auto p-4 space-y-6">
        <nav class="space-y-2">
            {{--<a href="{{ url('/') }}" class="flex items-center gap-3 px-3 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-xl transition-colors">
                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                    <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                </svg>
                صفحه اصلی
            </a>--}}

            <a href="{{ url('/chat') }}" class="flex items-center gap-3 px-3 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-xl transition-colors">
                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 3c.375 0 .711 .231 .846 .581l1.65 4.29a2.85 2.85 0 0 0 1.632 1.633l4.291 1.65a.906 .906 0 0 1 0 1.692l-4.29 1.65a2.84 2.84 0 0 0 -1.633 1.632l-1.65 4.291a.906 .906 0 0 1 -1.692 0l-1.65 -4.29a2.84 2.84 0 0 0 -1.632 -1.633l-4.291 -1.65a.906 .906 0 0 1 0 -1.692l4.29 -1.65a2.84 2.84 0 0 0 1.633 -1.632l1.65 -4.291a.91 .91 0 0 1 .846 -.581" />
                </svg>
                مشاوره رایگان
            </a>

            {{--@if(auth()->check() && isset($isMarketActive) && $isMarketActive)
                <button onclick="toggleMobileMenu(); window.Livewire.dispatch('showCartPopup')" class="w-full flex items-center gap-3 px-3 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-xl transition-colors text-right">
                    <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M10 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                        <path d="M5.001 8h13.999a2 2 0 0 1 1.977 2.304l-1.255 7.152a3 3 0 0 1 -2.966 2.544h-9.512a3 3 0 0 1 -2.965 -2.544l-1.255 -7.152a2 2 0 0 1 1.977 -2.304" />
                        <path d="M17 10l-2 -6" />
                        <path d="M7 10l2 -6" />
                    </svg>
                    سبد خرید
                </button>
            @endif--}}

            {{--<a href="{{ url('/clients/dashboard') }}" class="flex items-center gap-3 px-3 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-xl transition-colors">
                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                </svg>
                @auth حساب کاربری @else ورود / ثبت‌نام @endauth
            </a>--}}
        </nav>
    </div>

    {{-- پاورقی سایدبار (تغییر تم) --}}
    <div class="p-4 border-t border-gray-100 dark:border-gray-800">
        <button onclick="setAppThemeMode(localStorage.theme === 'dark' ? 'light' : 'dark')" class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-gray-50 dark:bg-gray-900 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors">
            <svg class="w-5 h-5 hidden dark:block text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            <svg class="w-5 h-5 block dark:hidden text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
            <span>تغییر قالب (تاریک/روشن)</span>
        </button>
    </div>
</aside>

@if(!request()->is('chat') && !request()->is('chat/*'))
    {{-- ================= نوار ناوبری پایین (Bottom App Bar) مخصوص موبایل - طراحی بومی و شناور ================= --}}
    <div id="mobile-bottom-nav" class="md:hidden fixed bottom-4 left-4 right-4 z-[60] pointer-events-none pb-safe">
        <nav class="pointer-events-auto bg-white/90 dark:bg-gray-900/90 backdrop-blur-2xl border border-white/60 dark:border-gray-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.5)] rounded-[2rem] overflow-hidden">
            <div class="flex items-center justify-around h-[4.5rem] px-1 relative">

                {{-- دکمه خانه --}}
                @php $isActive = request()->is('/'); @endphp
                <a href="{{ url('/') }}" class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                    @if($isActive)
                        <div class="absolute top-3 w-10 h-10 bg-[#3F7D20] opacity-15 dark:opacity-20 blur-xl rounded-full"></div>
                        <span class="absolute top-0 w-8 h-1 bg-[#3F7D20] rounded-b-full"></span>
                    @endif
                    <div class="relative z-10 flex flex-col items-center justify-center transition-transform duration-300 {{ $isActive ? '-translate-y-0.5' : '' }}">
                        @if($isActive)
                            <svg class="w-6 h-6 mb-1 text-[#3F7D20] dark:text-[#5cba2f] transition-colors duration-300" viewBox="0 0 24 24" fill="currentColor">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12.707 2.293l9 9c.63 .63 .184 1.707 -.707 1.707h-1v6a3 3 0 0 1 -3 3h-1v-7a3 3 0 0 0 -2.824 -2.995l-.176 -.005h-2a3 3 0 0 0 -3 3v7h-1a3 3 0 0 1 -3 -3v-6h-1c-.89 0 -1.337 -1.077 -.707 -1.707l9 -9a1 1 0 0 1 1.414 0m.293 11.707a1 1 0 0 1 1 1v7h-4v-7a1 1 0 0 1 .883 -.993l.117 -.007z" />
                            </svg>
                        @else
                            <svg class="w-6 h-6 mb-1 text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                            </svg>
                        @endif
                        <span class="text-[10px] font-bold {{ $isActive ? 'text-[#3F7D20] dark:text-[#5cba2f]' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200' }} transition-colors duration-300">خانه</span>
                    </div>
                </a>

                {{-- دکمه دستیار --}}
                @php $isChatActive = request()->is('chat') || request()->is('chat/*'); @endphp
                <a href="{{ url('/chat') }}" class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                    @if($isChatActive)
                        <div class="absolute top-3 w-10 h-10 bg-[#3F7D20] opacity-15 dark:opacity-20 blur-xl rounded-full"></div>
                        <span class="absolute top-0 w-8 h-1 bg-[#3F7D20] rounded-b-full"></span>
                    @endif
                    <div class="relative z-10 flex flex-col items-center justify-center transition-transform duration-300 {{ $isChatActive ? '-translate-y-0.5' : '' }}">
                        @if($isChatActive)
                            <svg class="w-6 h-6 mb-1 text-[#3F7D20] dark:text-[#5cba2f] transition-colors duration-300" viewBox="0 0 24 24" fill="currentColor">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 3c.375 0 .711 .231 .846 .581l1.65 4.29a2.85 2.85 0 0 0 1.632 1.633l4.291 1.65a.906 .906 0 0 1 0 1.692l-4.29 1.65a2.84 2.84 0 0 0 -1.633 1.632l-1.65 4.291a.906 .906 0 0 1 -1.692 0l-1.65 -4.29a2.84 2.84 0 0 0 -1.632 -1.633l-4.291 -1.65a.906 .906 0 0 1 0 -1.692l4.29 -1.65a2.84 2.84 0 0 0 1.633 -1.632l1.65 -4.291a.91 .91 0 0 1 .846 -.581" />
                            </svg>
                        @else
                            <svg class="w-6 h-6 mb-1 text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 3c.375 0 .711 .231 .846 .581l1.65 4.29a2.85 2.85 0 0 0 1.632 1.633l4.291 1.65a.906 .906 0 0 1 0 1.692l-4.29 1.65a2.84 2.84 0 0 0 -1.633 1.632l-1.65 4.291a.906 .906 0 0 1 -1.692 0l-1.65 -4.29a2.84 2.84 0 0 0 -1.632 -1.633l-4.291 -1.65a.906 .906 0 0 1 0 -1.692l4.29 -1.65a2.84 2.84 0 0 0 1.633 -1.632l1.65 -4.291a.91 .91 0 0 1 .846 -.581" />
                            </svg>
                        @endif
                        <span class="text-[10px] font-bold {{ $isChatActive ? 'text-[#3F7D20] dark:text-[#5cba2f]' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200' }} transition-colors duration-300">دستیار</span>
                    </div>
                </a>

                {{-- دکمه سبد خرید --}}
                @php $isActive = request()->routeIs('market.public.cart*') || request()->routeIs('checkout*'); @endphp
                @if(auth()->check() && isset($isMarketActive) && $isMarketActive)
                    <div class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                        @livewire('market::web.cart-counter', ['isBottomNav' => true])
                    </div>
                @endif

                {{-- دکمه پروفایل / ورود --}}
                @php $isActive = request()->is('clients/dashboard') || request()->is('clients/dashboard/*') || request()->routeIs('user.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('login'); @endphp
                @auth
                    <a href="{{ url('/clients/dashboard') }}" class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                        @if($isActive)
                            <div class="absolute top-3 w-10 h-10 bg-[#3F7D20] opacity-15 dark:opacity-20 blur-xl rounded-full"></div>
                            <span class="absolute top-0 w-8 h-1 bg-[#3F7D20] rounded-b-full"></span>
                        @endif
                        <div class="relative z-10 flex flex-col items-center justify-center transition-transform duration-300 {{ $isActive ? '-translate-y-0.5' : '' }}">
                            @if($isActive)
                                <svg class="w-6 h-6 mb-1 text-[#3F7D20] dark:text-[#5cba2f] transition-colors duration-300" viewBox="0 0 24 24" fill="currentColor">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                    <path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" />
                                </svg>
                            @else
                                <svg class="w-6 h-6 mb-1 text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                </svg>
                            @endif
                            <span class="text-[10px] font-bold {{ $isActive ? 'text-[#3F7D20] dark:text-[#5cba2f]' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200' }} transition-colors duration-300">پروفایل من</span>
                        </div>
                    </a>
                @else
                    <a href="{{ url('/clients/dashboard') }}" class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                        @if($isActive)
                            <div class="absolute top-3 w-10 h-10 bg-[#3F7D20] opacity-15 dark:opacity-20 blur-xl rounded-full"></div>
                            <span class="absolute top-0 w-8 h-1 bg-[#3F7D20] rounded-b-full"></span>
                        @endif
                        <div class="relative z-10 flex flex-col items-center justify-center transition-transform duration-300 {{ $isActive ? '-translate-y-0.5' : '' }}">
                            @if($isActive)
                                <svg class="w-6 h-6 mb-1 text-[#3F7D20] dark:text-[#5cba2f] transition-colors duration-300" viewBox="0 0 24 24" fill="currentColor">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                    <path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" />
                                </svg>
                            @else
                                <svg class="w-6 h-6 mb-1 text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                </svg>
                            @endif
                            <span class="text-[10px] font-bold {{ $isActive ? 'text-[#3F7D20] dark:text-[#5cba2f]' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200' }} transition-colors duration-300">ورود</span>
                        </div>
                    </a>
                @endauth
            </div>
        </nav>
    </div>
@endif

{{-- اسکریپت کنترل نوار کناری --}}
<script>
    function toggleMobileMenu() {
        const overlay = document.getElementById('mobile-menu-overlay');
        const sidebar = document.getElementById('mobile-menu-sidebar');

        if (sidebar.classList.contains('translate-x-full')) {
            overlay.classList.remove('pointer-events-none');
            overlay.classList.remove('opacity-0');
            sidebar.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden';
        } else {
            overlay.classList.add('opacity-0');
            overlay.classList.add('pointer-events-none');
            sidebar.classList.add('translate-x-full');
            document.body.style.overflow = '';
        }
    }

    window.addEventListener('location-changed', (event) => {
        const city = event.detail.city || 'انتخاب موقعیت';
        const headerText = document.getElementById('header-location-text');
        if (headerText) headerText.textContent = city;
        const mobileText = document.getElementById('mobile-location-text');
        if (mobileText) mobileText.textContent = 'موقعیت شما: ' + city;
    });
</script>

@push('styles')
    <style>
        /* تنظیم فواصل جهت جلوگیری از تداخل با هدر ثابت و نوار شناور موبایل */
        main { padding-top: 5.5rem !important; padding-bottom: 7.5rem !important; }
        @media (min-width: 768px) { main { padding-top: 5.5rem !important; padding-bottom: 0 !important; } }
    </style>
@endpush
