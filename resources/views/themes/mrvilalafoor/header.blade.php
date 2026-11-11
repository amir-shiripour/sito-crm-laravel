@php
    $globalSettings = [];
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $globalSettings = \Modules\Settings\Entities\Setting::pluck('value', 'key')->toArray();
        }
    } catch (\Throwable $e) {}

    $appName = $globalSettings['app_name'] ?? 'مستر ویلا لفور';
    $appLogo = isset($globalSettings['app_logo']) ? asset($globalSettings['app_logo']) : null;
    $isHostRegistered = false;
    $rentalMultiHostEnabled = false;
    $rentalModeEnabled = true;

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('property_settings')) {
            $rentalMultiHostEnabled = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_multi_host_enabled', 0);
            $rentalModeEnabled = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_mode_enabled', 1);
        }
    } catch (\Throwable $e) {}

    if (auth()->check()) {
        try {
            $isHostRegistered = \Modules\Properties\Entities\PropertyHost::where('user_id', auth()->id())->exists();
        } catch (\Throwable $e) {}
    }
@endphp

<!-- ============================================================= -->
<!-- MR. VILA LAFOOR ULTRA-MINIMAL HEADER (76px, Pure Breathing Room) -->
<!-- ============================================================= -->
<header class="hidden md:block sticky top-0 z-40 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md border-b border-[#ECECEE] dark:border-zinc-800 transition-colors duration-200">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-8 h-[76px] flex items-center justify-between">
        
        {{-- Logo & Brand --}}
        <div class="flex items-center gap-6">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                @if($appLogo)
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" class="w-9 h-9 object-contain rounded-xl">
                @else
                    <div class="w-9 h-9 rounded-xl bg-[#007A5E] dark:bg-emerald-600 flex items-center justify-center text-white shadow-sm group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                @endif
                <div class="flex items-center">
                    <span class="text-base sm:text-lg font-black tracking-tight text-[#18181B] dark:text-zinc-100 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors">
                        {{ $appName }}
                    </span>
                </div>
            </a>

            {{-- Desktop Navigation Links --}}
            <nav class="hidden md:flex items-center gap-1 mr-4">
                <a href="{{ route('properties.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold {{ request()->routeIs('properties.*') ? 'text-[#007A5E] dark:text-emerald-400 bg-[#ECFDF5]/70 dark:bg-emerald-950/30' : 'text-zinc-700 dark:text-zinc-300 hover:text-[#007A5E] dark:hover:text-emerald-400 hover:bg-[#F7F8F9] dark:hover:bg-zinc-900' }} transition-colors">
                    اقامتگاه‌ها
                </a>
            </nav>
        </div>

        {{-- Actions: Multi-Host Marketplace CTA & Auth & Dark Mode --}}
        <div class="flex items-center gap-2.5 sm:gap-3">

            {{-- Multi-Host Marketplace Call to Action (ثبت اقامتگاه / میزبان شوید) --}}
            @if($rentalMultiHostEnabled)
                <a href="{{ $isHostRegistered ? route('user.properties.hosts.dashboard') : url('/register') }}" 
                   class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-[#007A5E]/30 dark:border-emerald-500/30 bg-[#ECFDF5] dark:bg-emerald-950/40 hover:bg-[#007A5E]/10 dark:hover:bg-emerald-900/40 text-xs font-bold text-[#007A5E] dark:text-emerald-300 transition-colors">
                    <svg class="w-4 h-4 text-[#007A5E] dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>{{ $isHostRegistered ? 'داشبورد میزبانی' : 'میزبان شوید' }}</span>
                </a>
            @endif

            {{-- Dark Mode Toggle --}}
            <button type="button" 
                    onclick="toggleDarkMode()" 
                    title="تغییر حالت شب و روز"
                    class="w-9 h-9 rounded-xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-900 text-zinc-600 dark:text-zinc-300 flex items-center justify-center hover:border-zinc-400 dark:hover:border-zinc-700 transition-colors">
                <svg id="theme-icon-sun" class="w-4 h-4 hidden dark:block text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg id="theme-icon-moon" class="w-4 h-4 block dark:hidden text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>

            {{-- User Auth / Profile (پشتیبانی کامل از کاربران سیستم، ادمین و کلاینت‌ها) --}}
            @if(auth()->check())
                @php
                    $currentUser = auth()->user();
                    $isSuperAdmin = ($currentUser->is_super_admin ?? false) || (method_exists($currentUser, 'hasRole') && ($currentUser->hasRole('super-admin') || $currentUser->hasRole('Super Admin')));
                    $userDashboardUrl = $isSuperAdmin ? url('/admin') : url('/user');
                    $userDashboardTitle = $isSuperAdmin ? 'پنل مدیریت کل' : 'پنل کاربری';
                @endphp
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" 
                            @click="open = !open" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-300 dark:hover:border-zinc-700 transition-colors">
                        <div class="w-7 h-7 rounded-lg bg-[#007A5E]/10 dark:bg-emerald-950/60 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                            {{ mb_substr($currentUser->name ?? 'ک', 0, 1) }}
                        </div>
                        <span class="text-xs font-bold text-[#18181B] dark:text-zinc-200 hidden sm:inline max-w-[110px] truncate">
                            {{ $currentUser->name }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-zinc-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Menu for Users / Staff / Admin --}}
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 mt-2 w-52 rounded-2xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-xl py-2 z-50 text-right">
                        
                        <div class="px-4 py-2 border-b border-[#ECECEE] dark:border-zinc-800">
                            <div class="text-xs font-bold text-[#18181B] dark:text-zinc-100">{{ $currentUser->name }}</div>
                            <div class="text-[11px] text-zinc-400 truncate mt-0.5">{{ $currentUser->email ?? $currentUser->phone }}</div>
                        </div>

                        <a href="{{ $userDashboardUrl }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800 transition-colors font-medium">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>{{ $userDashboardTitle }}</span>
                        </a>

                        @if($rentalMultiHostEnabled)
                            <a href="{{ $isHostRegistered ? route('user.properties.hosts.dashboard') : url('/register') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-[#007A5E] dark:text-emerald-400 hover:bg-[#ECFDF5] dark:hover:bg-emerald-950/30 transition-colors font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>{{ $isHostRegistered ? 'داشبورد میزبانان' : 'ثبت درخواست میزبانی' }}</span>
                            </a>
                        @endif

                        <div class="border-t border-[#ECECEE] dark:border-zinc-800 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors text-right cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>خروج از حساب</span>
                            </button>
                        </form>
                    </div>
                </div>
            @elseif(auth('client')->check())
                @php
                    $currentClient = auth('client')->user();
                    $clientDisplayName = trim(($currentClient->first_name ?? '') . ' ' . ($currentClient->last_name ?? '')) ?: ($currentClient->full_name ?? ($currentClient->name ?? 'مسافر گرامی'));
                @endphp
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" 
                            @click="open = !open" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-300 dark:hover:border-zinc-700 transition-colors">
                        <div class="w-7 h-7 rounded-lg bg-[#007A5E]/10 dark:bg-emerald-950/60 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                            {{ mb_substr($clientDisplayName, 0, 1) }}
                        </div>
                        <span class="text-xs font-bold text-[#18181B] dark:text-zinc-200 hidden sm:inline max-w-[110px] truncate">
                            {{ $clientDisplayName }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-zinc-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Menu for Client --}}
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 mt-2 w-52 rounded-2xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-xl py-2 z-50 text-right">
                        
                        <div class="px-4 py-2 border-b border-[#ECECEE] dark:border-zinc-800">
                            <div class="text-xs font-bold text-[#18181B] dark:text-zinc-100">{{ $clientDisplayName }}</div>
                            <div class="text-[11px] text-zinc-400 truncate mt-0.5">{{ $currentClient->phone ?? $currentClient->email }}</div>
                        </div>

                        <a href="{{ url('/clients/dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800 transition-colors font-medium">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>داشبورد من</span>
                        </a>

                        @if(Route::has('client.rental-bookings.index'))
                            <a href="{{ route('client.rental-bookings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800 transition-colors font-medium">
                                <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>رزروها و سفرهای من</span>
                            </a>
                        @endif

                        <div class="border-t border-[#ECECEE] dark:border-zinc-800 my-1"></div>

                        <form method="POST" action="{{ route('client.logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors text-right cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>خروج از حساب</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ url('/clients/dashboard') }}" 
                   class="px-4 py-2 rounded-xl border border-[#ECECEE] dark:border-zinc-800 hover:border-[#18181B] dark:hover:border-zinc-600 bg-white dark:bg-zinc-900 text-xs font-bold text-[#18181B] dark:text-zinc-100 transition-colors shadow-sm inline-flex items-center">
                    ورود / ثبت‌نام
                </a>
            @endif

        </div>

    </div>
</header>

<script>
    function toggleDarkMode() {
        const isDark = document.documentElement.classList.contains('dark');
        if (isDark) {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
        }
    }
</script>
