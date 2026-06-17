<header class="fixed w-full top-0 z-50 transition-all duration-300 bg-white/80 dark:bg-gray-950/80 backdrop-blur-lg border-b border-gray-200/50 dark:border-gray-800/50">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">

        {{-- بخش راست: لوگو و منو --}}
        <div class="flex items-center gap-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                @if($appLogo)
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-10 w-auto rounded-xl shadow-sm bg-white border border-gray-100 dark:border-gray-800">
                @else
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                @endif
                <span class="font-bold text-xl tracking-tight sm:block text-gray-900 dark:text-white">{{ $appName }}</span>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600 dark:text-gray-300">
                <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">محصولات</a>
                <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">راهکارها</a>
                <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">تعرفه‌ها</a>
                <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">وبلاگ</a>
            </nav>
        </div>

        {{-- بخش چپ: اکشن‌ها (دسکتاپ) و دکمه موبایل --}}
        <div class="flex items-center gap-4">

            {{-- اکشن‌های دسکتاپ --}}
            <div class="hidden md:flex items-center gap-4">

                @if(isset($isMarketActive) && $isMarketActive)
                    @livewire('market::web.cart-counter')
                @endif

                {{-- Dark/Light Mode Switcher Component --}}
                <div class="flex items-center p-1 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700/50">
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

                @auth
                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}"
                       class="px-5 py-2.5 rounded-xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm font-bold hover:bg-gray-800 dark:hover:bg-gray-100 transition-all shadow-sm">
                        داشبورد
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                        ورود
                    </a>
                    <a href="#" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all">
                        ثبت نام رایگان
                    </a>
                @endauth
            </div>

            {{-- دکمه منوی همبرگری (موبایل) --}}
            <div class="md:hidden flex items-center gap-2">
                @if(isset($isMarketActive) && $isMarketActive)
                    @livewire('market::web.cart-counter')
                @endif
                <button onclick="toggleMobileMenu()" class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>

        </div>
    </div>
</header>

{{-- نوار کناری موبایل (Mobile Sidebar) --}}
<div id="mobile-menu-overlay" class="fixed inset-0 bg-gray-900/50 dark:bg-black/60 backdrop-blur-sm z-[60] hidden opacity-0 transition-opacity duration-300" onclick="toggleMobileMenu()"></div>

<aside id="mobile-menu" class="fixed top-0 right-0 h-full w-72 bg-white dark:bg-gray-950 z-[70] transform translate-x-full transition-transform duration-300 shadow-2xl flex flex-col border-l border-gray-200/50 dark:border-gray-800/50 overflow-y-auto">
    <div class="h-20 px-6 flex items-center justify-between border-b border-gray-100 dark:border-gray-800/50">
        <span class="font-bold text-lg text-gray-900 dark:text-white">{{ $appName }}</span>
        <button onclick="toggleMobileMenu()" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors focus:outline-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <div class="p-6 flex-1 space-y-8">
        {{-- لینک‌های منو --}}
        <nav class="flex flex-col gap-4 text-sm font-medium text-gray-600 dark:text-gray-300">
            <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">محصولات</a>
            <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">راهکارها</a>
            <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">تعرفه‌ها</a>
            <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">وبلاگ</a>
        </nav>

        {{-- انتخاب تم --}}
        <div class="space-y-3">
            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">ظاهر سایت</p>
            <div class="flex items-center gap-2 p-1 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700/50">
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

        {{-- حساب کاربری و اکشن‌ها --}}
        <div class="space-y-3">
            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">حساب کاربری</p>
            <div class="flex flex-col gap-3">
                @auth
                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}"
                       class="w-full justify-center px-5 py-3 rounded-xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm font-bold hover:bg-gray-800 dark:hover:bg-gray-100 transition-all shadow-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        داشبورد
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full justify-center px-5 py-3 rounded-xl border border-gray-200 dark:border-gray-800 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors flex items-center gap-2">
                        ورود به حساب
                    </a>
                    <a href="#" class="w-full justify-center px-5 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all flex items-center gap-2">
                        ثبت نام رایگان
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
            overlay.classList.remove('hidden');
            void overlay.offsetWidth;
            overlay.classList.remove('opacity-0');
            menu.classList.remove('translate-x-full');
        } else {
            overlay.classList.add('opacity-0');
            menu.classList.add('translate-x-full');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        }
    }
</script>
