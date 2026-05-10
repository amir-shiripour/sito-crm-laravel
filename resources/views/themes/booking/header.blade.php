<header class="fixed w-full top-0 z-50 transition-all duration-300 bg-white/90 dark:bg-gray-950/90 backdrop-blur-xl border-b border-teal-100 dark:border-teal-900/30 shadow-sm">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">

        {{-- بخش راست: لوگو و منوی پزشکی --}}
        <div class="flex items-center gap-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                @if($appLogo)
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-10 w-auto rounded-xl">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white shadow-lg shadow-teal-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    </div>
                @endif
                <div class="flex flex-col">
{{--                    <span class="font-black text-lg tracking-tight hidden sm:block text-gray-900 dark:text-white">{{ $appName }}</span>--}}
                    <span class="font-black text-lg tracking-tight sm:block text-gray-900 dark:text-white">{{ $appName }}</span>
{{--                    <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 hidden sm:block">کلینیک تخصصی</span>--}}
                    <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 sm:block">کلینیک تخصصی</span>
                </div>
            </a>

            {{--<nav class="hidden lg:flex items-center gap-6 text-sm font-bold text-gray-600 dark:text-gray-300">
                <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">پزشکان ما</a>
                <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">تخصص‌ها و خدمات</a>
                <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">بیمه‌ها</a>
                <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">راهنمای بیماران</a>
            </nav>--}}
        </div>

        {{-- بخش چپ: اکشن‌ها (دسکتاپ) و دکمه موبایل --}}
        <div class="flex items-center gap-3">

            {{-- اکشن‌های دسکتاپ --}}
            <div class="hidden md:flex items-center gap-3">
                {{-- Dark Mode Switcher --}}
                <div class="flex items-center p-1 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <button onclick="setAppThemeMode('light')" data-theme="light" class="theme-switcher-btn p-1.5 rounded-lg transition-all focus:outline-none" title="روشن">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </button>
                    <button onclick="setAppThemeMode('system')" data-theme="system" class="theme-switcher-btn p-1.5 rounded-lg transition-all focus:outline-none" title="سیستم">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </button>
                    <button onclick="setAppThemeMode('dark')" data-theme="dark" class="theme-switcher-btn p-1.5 rounded-lg transition-all focus:outline-none" title="تاریک">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    </button>
                </div>

                <div class="h-6 w-px bg-gray-200 dark:bg-gray-800 mx-1"></div>

                @auth
                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('client.dashboard') }}"
                       class="px-5 py-2.5 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 text-sm font-bold hover:bg-teal-100 dark:hover:bg-teal-900/50 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        پنل کاربری
                    </a>
                @else
                    <a href="{{ route('client.login') }}" class="text-sm font-bold text-gray-600 dark:text-gray-300 hover:text-teal-600 px-3 transition-colors">
                        ورود بیماران
                    </a>
                    {{--<a href="{{ route('booking.public.index') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white text-sm font-bold hover:from-teal-600 hover:to-emerald-600 shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        رزرو نوبت
                    </a>--}}
                    <a href="{{ route('user.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white text-sm font-bold hover:from-teal-600 hover:to-emerald-600 shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        ورود کاربران
                    </a>
                @endauth
            </div>

            {{-- دکمه منوی همبرگری (موبایل) --}}
            <button onclick="toggleMobileMenu()" class="md:hidden p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors focus:outline-none">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>

        </div>
    </div>
</header>

{{-- نوار کناری موبایل (Mobile Sidebar) --}}
<div id="mobile-menu-overlay" class="fixed inset-0 bg-gray-900/50 dark:bg-black/60 backdrop-blur-sm z-[60] hidden opacity-0 transition-opacity duration-300" onclick="toggleMobileMenu()"></div>

<aside id="mobile-menu" class="fixed top-0 right-0 h-full w-72 bg-white dark:bg-gray-950 z-[70] transform translate-x-full transition-transform duration-300 shadow-2xl flex flex-col border-l border-teal-100 dark:border-teal-900/30 overflow-y-auto">
    <div class="h-20 px-6 flex items-center justify-between border-b border-gray-100 dark:border-gray-800/50">
        <span class="font-black text-lg text-gray-900 dark:text-white">{{ $appName }}</span>
        <button onclick="toggleMobileMenu()" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors focus:outline-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <div class="p-6 flex-1 space-y-8">
        {{-- منوی لینک‌ها (کامنت شده) --}}
        {{--<nav class="flex flex-col gap-4 text-sm font-bold text-gray-600 dark:text-gray-300">
            <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">پزشکان ما</a>
            <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">تخصص‌ها و خدمات</a>
            <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">بیمه‌ها</a>
            <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">راهنمای بیماران</a>
        </nav>--}}

        {{-- انتخاب تم --}}
        <div class="space-y-3">
            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">ظاهر سایت</p>
            <div class="flex items-center gap-2 p-1 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <button onclick="setAppThemeMode('light')" data-theme="light" class="theme-switcher-btn flex-1 flex justify-center py-2 rounded-lg transition-all focus:outline-none" title="روشن">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </button>
                <button onclick="setAppThemeMode('system')" data-theme="system" class="theme-switcher-btn flex-1 flex justify-center py-2 rounded-lg transition-all focus:outline-none" title="سیستم">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </button>
                <button onclick="setAppThemeMode('dark')" data-theme="dark" class="theme-switcher-btn flex-1 flex justify-center py-2 rounded-lg transition-all focus:outline-none" title="تاریک">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                </button>
            </div>
        </div>

        {{-- حساب کاربری --}}
        <div class="space-y-3">
            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">حساب کاربری</p>
            <div class="flex flex-col gap-3">
                @auth
                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('client.dashboard') }}"
                       class="w-full justify-center px-5 py-3 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 text-sm font-bold hover:bg-teal-100 dark:hover:bg-teal-900/50 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        پنل کاربری
                    </a>
                @else
                    <a href="{{ route('client.login') }}" class="w-full justify-center px-5 py-3 rounded-xl border border-gray-200 dark:border-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors flex items-center gap-2">
                        ورود بیماران
                    </a>
                    {{--<a href="{{ route('booking.public.index') }}" class="w-full justify-center px-5 py-3 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white text-sm font-bold shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        رزرو نوبت
                    </a>--}}
                    <a href="{{ route('user.dashboard') }}" class="w-full justify-center px-5 py-3 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white text-sm font-bold shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        ورود کاربران
                    </a>
                @endauth
            </div>
        </div>
    </div>
</aside>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const overlay = document.getElementById('mobile-menu-overlay');
        const isClosed = menu.classList.contains('translate-x-full');

        if (isClosed) {
            // نمایش منو
            overlay.classList.remove('hidden');
            // Force reflow for smooth transition
            void overlay.offsetWidth;
            overlay.classList.remove('opacity-0');
            menu.classList.remove('translate-x-full');
        } else {
            // بستن منو
            overlay.classList.add('opacity-0');
            menu.classList.add('translate-x-full');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        }
    }
</script>
