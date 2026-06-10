<header class="fixed w-full top-0 z-50 transition-all duration-300 bg-white dark:bg-gray-950 border-b border-gray-200/50 dark:border-gray-800 shadow-sm">

    {{-- نوار اصلی هدر --}}
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-4 md:gap-8">

        {{-- دکمه منوی همبرگری (فقط در موبایل) و لوگو --}}
        <div class="flex items-center gap-3 flex-shrink-0">
            <button onclick="toggleMobileMenu()" class="md:hidden p-2 -mr-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>

            <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80">
                @if($appLogo)
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-8 sm:h-10 w-auto object-contain">
                @else
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl {{ $t['bg'] ?? 'bg-orange-600' }} flex items-center justify-center text-white">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    </div>
                @endif
                <span class="font-black text-xl sm:text-2xl tracking-tight hidden lg:block {{ $t['text'] ?? 'text-orange-600' }} {{ $t['text_dark'] ?? 'dark:text-orange-500' }}">{{ $appName }}</span>
            </a>
        </div>

        {{-- نوار جستجو (فقط دسکتاپ) --}}
        <div class="hidden md:flex flex-1 max-w-2xl relative group">
            <input type="text" placeholder="جستجو در بین کالاها..." class="w-full bg-gray-100 dark:bg-gray-900 border-transparent {{ $t['focus_border'] ?? 'focus:border-orange-500' }} focus:bg-white dark:focus:bg-gray-800 rounded-xl py-3 px-4 pl-12 text-sm transition-all text-gray-900 dark:text-white">
            <button class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 {{ $t['hover_text'] ?? 'hover:text-orange-500' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </button>
        </div>

        {{-- اکشن‌ها --}}
        <div class="flex items-center gap-1 sm:gap-4 flex-shrink-0">
            @if(isset($isMarketActive) && $isMarketActive && class_exists(\Modules\Market\Entities\MarketSetting::class) && \Modules\Market\Entities\MarketSetting::getValue('orders.enable_geolocation_ordering', false))
                @php
                    $currentLoc = \Modules\Market\App\Helpers\GeolocationHelper::getClientLocation();
                    $locText = ($currentLoc && !empty($currentLoc['city'])) ? $currentLoc['city'] : 'انتخاب موقعیت';
                @endphp
                <button onclick="Livewire.dispatch('openLocationModal')" class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700/50 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-xs font-bold">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>{{ $locText }}</span>
                </button>
            @endif

            {{-- Dark Mode Switcher (مخفی در موبایل) --}}
            <button onclick="setAppThemeMode(localStorage.theme === 'dark' ? 'light' : 'dark')" class="hidden sm:block p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                <svg class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
            </button>

            @if(isset($isMarketActive) && $isMarketActive)
                @livewire('market::web.cart-counter')
            @endif

            <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 mx-1 hidden sm:block"></div>

            {{-- دکمه حساب کاربری --}}
            @auth
                <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}" class="flex items-center gap-2 p-2 sm:px-3 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200 hidden sm:block">حساب کاربری</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex items-center gap-2 px-3 py-2 sm:px-4 sm:py-2 {{ $t['bg_light'] ?? 'bg-orange-50' }} {{ $t['bg_light_dark'] ?? 'dark:bg-orange-900/30' }} {{ $t['text'] ?? 'text-orange-600' }} {{ $t['text_dark'] ?? 'dark:text-orange-400' }} rounded-xl {{ $t['hover_bg_light'] ?? 'hover:bg-orange-100' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                    <span class="text-sm font-bold hidden sm:block">ورود / ثبت‌نام</span>
                </a>
            @endauth
        </div>
    </div>

    {{-- نوار دسته‌بندی‌ها (Mega Menu Bar - فقط دسکتاپ) --}}
    <div class="border-t border-gray-100 dark:border-gray-800 hidden md:block bg-gray-50/50 dark:bg-gray-900/50">
        <div class="max-w-[1440px] mx-auto px-6 h-12 flex items-center gap-6 text-sm font-medium text-gray-600 dark:text-gray-300">
            <a href="#" class="flex items-center gap-1 font-bold text-gray-900 dark:text-white {{ $t['hover_text'] ?? 'hover:text-orange-600' }} transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                دسته‌بندی کالاها
            </a>
            <span class="w-px h-4 bg-gray-300 dark:bg-gray-700"></span>
            <a href="#" class="{{ $t['hover_text'] ?? 'hover:text-orange-600' }} transition-colors flex items-center gap-1"><svg class="w-4 h-4 {{ $t['text'] ?? 'text-orange-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /></svg> پرفروش‌ترین‌ها</a>
            <a href="#" class="{{ $t['hover_text'] ?? 'hover:text-orange-600' }} transition-colors">تخفیف‌ها و پیشنهادها</a>
            <a href="#" class="{{ $t['hover_text'] ?? 'hover:text-orange-600' }} transition-colors">سوالی دارید؟</a>
        </div>
    </div>

    {{-- ================= نوار کناری موبایل (Mobile Sidebar) ================= --}}
    <div id="mobile-menu-overlay" onclick="toggleMobileMenu()" class="fixed inset-0 bg-gray-900/60 dark:bg-black/80 z-[60] opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"></div>

    <aside id="mobile-menu-sidebar" class="fixed top-0 right-0 h-full w-4/5 max-w-sm bg-white dark:bg-gray-950 z-[70] transform translate-x-full transition-transform duration-300 ease-in-out md:hidden flex flex-col shadow-2xl border-l border-gray-100 dark:border-gray-800">
        {{-- سربرگ سایدبار --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-800">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                @if($appLogo)
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-8 w-auto">
                @endif
                <span class="font-black text-lg {{ $t['text'] ?? 'text-orange-600' }}">{{ $appName }}</span>
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
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>موقعیت شما: {{ $locText }}</span>
                </button>
            </div>
        @endif

        {{-- محتوای سایدبار --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-6">
            {{-- جستجو در موبایل --}}
            <div class="relative">
                <input type="text" placeholder="جستجو..." class="w-full bg-gray-100 dark:bg-gray-900 border-transparent {{ $t['focus_border'] ?? 'focus:border-orange-500' }} rounded-xl py-3 px-4 pl-10 text-sm text-gray-900 dark:text-white">
                <button class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </button>
            </div>

            {{-- لینک‌های دسته‌بندی --}}
            <nav class="space-y-2">
                <p class="text-xs font-bold text-gray-400 dark:text-gray-500 mb-3 px-2">دسته‌بندی‌ها و لینک‌ها</p>
                <a href="{{ Route::has('market.public.category') ? route('market.public.category') : '#' }}" class="flex items-center gap-3 px-3 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-xl">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    دسته‌بندی کالاها
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-xl">
                    <svg class="w-5 h-5 {{ $t['text'] ?? 'text-orange-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /></svg>
                    پرفروش‌ترین‌ها
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-xl">
                    تخفیف‌ها و پیشنهادها
                </a>
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

</header>

{{-- ================= نوار ناوبری پایین (Bottom App Bar) مخصوص موبایل - طراحی بومی و شناور ================= --}}
<div class="md:hidden fixed bottom-4 left-4 right-4 z-[60] pointer-events-none pb-safe">
    <nav class="pointer-events-auto bg-white/90 dark:bg-gray-900/90 backdrop-blur-2xl border border-white/60 dark:border-gray-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.5)] rounded-[2rem] overflow-hidden">
        <div class="flex items-center justify-around h-[4.5rem] px-1 relative">

            {{-- دکمه خانه --}}
            @php $isActive = request()->is('/'); @endphp
            <a href="{{ url('/') }}" class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                @if($isActive)
                    {{-- هاله نوری (Glow) جذاب پشت آیکون --}}
                    <div class="absolute top-3 w-10 h-10 {{ $t['bg'] ?? 'bg-orange-600' }} opacity-15 dark:opacity-20 blur-xl rounded-full"></div>
                    <span class="absolute top-0 w-8 h-1 {{ $t['bg'] ?? 'bg-orange-600' }} rounded-b-full"></span>
                @endif
                <div class="relative z-10 flex flex-col items-center justify-center transition-transform duration-300 {{ $isActive ? '-translate-y-0.5' : '' }}">
                    <svg class="w-6 h-6 mb-1 {{ $isActive ? ($t['text'] ?? 'text-orange-600') . ' ' . ($t['text_dark'] ?? 'dark:text-orange-400') : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300' }} transition-colors duration-300" fill="{{ $isActive ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $isActive ? '0' : '1.5' }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-[10px] font-bold {{ $isActive ? ($t['text'] ?? 'text-orange-600') . ' ' . ($t['text_dark'] ?? 'dark:text-orange-400') : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200' }} transition-colors duration-300">خانه</span>
                </div>
            </a>

            {{-- دکمه دسته‌بندی --}}
            @php $isActive = request()->routeIs('market.public.category*'); @endphp
            <a href="{{ Route::has('market.public.category') ? route('market.public.category') : '#' }}" class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                @if($isActive)
                    <div class="absolute top-3 w-10 h-10 {{ $t['bg'] ?? 'bg-orange-600' }} opacity-15 dark:opacity-20 blur-xl rounded-full"></div>
                    <span class="absolute top-0 w-8 h-1 {{ $t['bg'] ?? 'bg-orange-600' }} rounded-b-full"></span>
                @endif
                <div class="relative z-10 flex flex-col items-center justify-center transition-transform duration-300 {{ $isActive ? '-translate-y-0.5' : '' }}">
                    <svg class="w-6 h-6 mb-1 {{ $isActive ? ($t['text'] ?? 'text-orange-600') . ' ' . ($t['text_dark'] ?? 'dark:text-orange-400') : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300' }} transition-colors duration-300" fill="{{ $isActive ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $isActive ? '0' : '1.5' }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="text-[10px] font-bold {{ $isActive ? ($t['text'] ?? 'text-orange-600') . ' ' . ($t['text_dark'] ?? 'dark:text-orange-400') : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200' }} transition-colors duration-300">دسته‌بندی</span>
                </div>
            </a>

            {{-- دکمه سبد خرید --}}
            @php $isActive = request()->routeIs('market.public.cart*') || request()->routeIs('checkout*'); @endphp
            <div class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                 @if(isset($isMarketActive) && $isMarketActive)
                     @livewire('market::web.cart-counter', ['isBottomNav' => true])
                 @endif
            </div>

            {{-- دکمه پروفایل / ورود --}}
            @php $isActive = request()->routeIs('user.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('login'); @endphp
            @auth
                <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}" class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                    @if($isActive)
                        <div class="absolute top-3 w-10 h-10 {{ $t['bg'] ?? 'bg-orange-600' }} opacity-15 dark:opacity-20 blur-xl rounded-full"></div>
                        <span class="absolute top-0 w-8 h-1 {{ $t['bg'] ?? 'bg-orange-600' }} rounded-b-full"></span>
                    @endif
                    <div class="relative z-10 flex flex-col items-center justify-center transition-transform duration-300 {{ $isActive ? '-translate-y-0.5' : '' }}">
                        <svg class="w-6 h-6 mb-1 {{ $isActive ? ($t['text'] ?? 'text-orange-600') . ' ' . ($t['text_dark'] ?? 'dark:text-orange-400') : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300' }} transition-colors duration-300" fill="{{ $isActive ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $isActive ? '0' : '1.5' }}">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-[10px] font-bold {{ $isActive ? ($t['text'] ?? 'text-orange-600') . ' ' . ($t['text_dark'] ?? 'dark:text-orange-400') : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200' }} transition-colors duration-300">پروفایل من</span>
                    </div>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex-1 flex flex-col items-center justify-center h-full relative group transition-all duration-300 active:scale-90">
                    @if($isActive)
                        <div class="absolute top-3 w-10 h-10 {{ $t['bg'] ?? 'bg-orange-600' }} opacity-15 dark:opacity-20 blur-xl rounded-full"></div>
                        <span class="absolute top-0 w-8 h-1 {{ $t['bg'] ?? 'bg-orange-600' }} rounded-b-full"></span>
                    @endif
                    <div class="relative z-10 flex flex-col items-center justify-center transition-transform duration-300 {{ $isActive ? '-translate-y-0.5' : '' }}">
                        <svg class="w-6 h-6 mb-1 {{ $isActive ? ($t['text'] ?? 'text-orange-600') . ' ' . ($t['text_dark'] ?? 'dark:text-orange-400') : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300' }} transition-colors duration-300" fill="{{ $isActive ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $isActive ? '0' : '1.5' }}">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span class="text-[10px] font-bold {{ $isActive ? ($t['text'] ?? 'text-orange-600') . ' ' . ($t['text_dark'] ?? 'dark:text-orange-400') : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200' }} transition-colors duration-300">ورود</span>
                    </div>
                </a>
            @endauth
        </div>
    </nav>
</div>

{{-- اسکریپت کنترل نوار کناری --}}
<script>
    function toggleMobileMenu() {
        const overlay = document.getElementById('mobile-menu-overlay');
        const sidebar = document.getElementById('mobile-menu-sidebar');

        // اگر سایدبار بسته است، باز شود
        if (sidebar.classList.contains('translate-x-full')) {
            overlay.classList.remove('pointer-events-none');
            overlay.classList.remove('opacity-0');
            sidebar.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden'; // جلوگیری از اسکرول صفحه زیرین
        } else {
            // بستن سایدبار
            overlay.classList.add('opacity-0');
            overlay.classList.add('pointer-events-none');
            sidebar.classList.add('translate-x-full');
            document.body.style.overflow = '';
        }
    }
</script>

{{--
    تنظیم فواصل پایین صفحات برای عدم تداخل با نوار اپلیکیشنی موبایل
--}}
@push('styles')
    <style>
        /* در دسکتاپ به اندازه 8rem فضا نیاز داریم */
        /* در موبایل علاوه بر 5.5rem فضای هدر، حدود 7.5rem فضا در پایین برای نوار شناور اختصاص داده شده است */
        main { padding-top: 5.5rem !important; padding-bottom: 7.5rem !important; }
        @media (min-width: 768px) { main { padding-top: 8rem !important; padding-bottom: 0 !important; } }
    </style>
@endpush
