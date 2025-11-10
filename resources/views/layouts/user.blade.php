{{-- layouts/user.blade.php --}}

@props([
    'title' => config('app.name', 'Laravel'),
])

<!doctype html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>{{ $title }}</title>

    <script>
        (() => {
            const pref = localStorage.getItem('theme') || 'system';
            const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = pref === 'dark' || (pref === 'system' && sysDark);
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100">

<div x-data="dashboardLayout()" x-init="init()" class="min-h-dvh flex">

    {{-- Sidebar --}}
    <aside :class="sidebarCollapsed ? 'w-20' : 'w-72'" class="transition-all duration-200 ease-in-out bg-white/90 dark:bg-gray-800/90 border-l lg:border-l-0 lg:border-r border-gray-200/70 dark:border-gray-700/60 min-h-dvh sticky top-0 z-30">
        @include('user.partials.sidebar')
    </aside>

    {{-- Main column --}}
    <div class="flex-1 min-w-0 flex flex-col">
        {{-- Topbar --}}
        @include('user.partials.topbar')

        {{-- Content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if (View::hasSection('content'))
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>
    </div>

    {{-- Drawer برای موبایل --}}
    <div class="fixed inset-0 bg-black/40 z-40 lg:hidden" x-show="mobileOpen" x-transition.opacity @click="mobileOpen=false"></div>

    {{--<aside class="fixed top-0 bottom-0 right-0 w-72 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 z-50 lg:hidden" x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0">
        <div class="h-16 px-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
            <span class="font-bold">منو</span>
            <button @click="mobileOpen=false" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-3 space-y-1">
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">پیشخوان</a>
            <div x-data="{open:false}">
                <button @click="open=!open" class="w-full flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span>مشتریان</span>
                    <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5"/></svg>
                </button>
                <div x-show="open" x-collapse class="pr-3">
                    <a href="#" class="block rounded-lg px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">لیست مشتریان</a>
                    <a href="#" class="block rounded-lg px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">افزودن مشتری</a>
                </div>
            </div>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">سفارش‌ها</a>
        </div>
    </aside>--}}

</div>

{{-- Alpine helpers (در صورت نبود Alpine در app.js) --}}
<script>
    function dashboardLayout() {
        return {
            mobileOpen: false,
            sidebarCollapsed: false,
            theme: localStorage.getItem('theme') || 'system',
            themeIcon: 'system',

            init() {
                // بازیابی حالت سایدبار
                this.sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === '1';
                // آیکن تم
                this.updateThemeIcon();
                // واکنش به تغییر سیستم
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                    if (this.theme === 'system') this.applyTheme();
                });
            },

            isActive(name) {
                // اگر نام روت فعلی به دسترس نیست، ساده برگردون
                return false;
            },

            toggleSidebar() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed ? '1' : '0');
            },

            isMenuOpen(key) {
                return localStorage.getItem('menu:'+key) === '1';
            },
            toggleMenu(key) {
                const val = this.isMenuOpen(key) ? '0' : '1';
                localStorage.setItem('menu:'+key, val);
            },

            themeTitle() {
                return this.theme === 'dark' ? 'حالت تاریک'
                    : this.theme === 'light' ? 'حالت روشن'
                        : 'همسان با سیستم';
            },
            updateThemeIcon() {
                this.themeIcon = this.theme === 'dark' ? 'dark'
                    : this.theme === 'light' ? 'light'
                        : 'system';
            },
            applyTheme() {
                const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = this.theme === 'dark' || (this.theme === 'system' && sysDark);
                document.documentElement.classList.toggle('dark', isDark);
                this.updateThemeIcon();
            },
            cycleTheme() {
                // ترتیب: system -> dark -> light -> system
                this.theme = this.theme === 'system' ? 'dark' : this.theme === 'dark' ? 'light' : 'system';
                localStorage.setItem('theme', this.theme);
                this.applyTheme();
            },
        }
    }
</script>

</body>
</html>
