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
    @livewireStyles
    <style>
        body { font-size: 14px;}
        /* Jalali Datepicker Z-Index Fix */
        jdp-container { z-index: 9999 !important; }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100">
<div
    x-data="{
        items: [],
        notify(e) {
            const id = Date.now() + Math.random();
            const detail = e.detail || e; // Support both event detail and direct object
            this.items.push({
                id,
                type: detail.type || 'info',
                text: detail.text || detail.message || '',
            });
            // auto hide
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) {
            this.items = this.items.filter(i => i.id !== id);
        }
    }"
    x-on:notify.window="notify($event)"
    x-init="
        @if(session()->has('success'))
            notify({ type: 'success', text: '{{ session('success') }}' });
        @endif
        @if(session()->has('error'))
            notify({ type: 'error', text: '{{ session('error') }}' });
        @endif
        @if(session()->has('warning'))
            notify({ type: 'warning', text: '{{ session('warning') }}' });
        @endif
        @if(session()->has('info'))
            notify({ type: 'info', text: '{{ session('info') }}' });
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                notify({ type: 'error', text: '{{ $error }}' });
            @endforeach
        @endif
    "
    class="fixed right-3 top-3 z-50 w-80 max-w-[90vw] space-y-2" style="z-index: 9999"
>
    <template x-for="item in items" :key="item.id">
        <div
            x-show="true"
            x-transition
            class="rounded-2xl px-4 py-3 text-sm shadow-lg border backdrop-blur bg-white/90 dark:bg-gray-900/90"
            :class="{
                'border-emerald-200 text-emerald-800 dark:border-emerald-500/60 dark:text-emerald-200': item.type === 'success',
                'border-red-200 text-red-800 dark:border-red-500/60 dark:text-red-200': item.type === 'error',
                'border-blue-200 text-blue-800 dark:border-blue-500/60 dark:text-blue-200': item.type === 'info',
                'border-amber-200 text-amber-800 dark:border-amber-500/60 dark:text-amber-200': item.type === 'warning',
            }"
        >
            <div class="flex items-start justify-between gap-3">
                <p class="leading-relaxed" x-text="item.text"></p>
                <button
                    type="button"
                    class="text-xs opacity-60 hover:opacity-100"
                    @click="remove(item.id)"
                >
                    ✕
                </button>
            </div>
        </div>
    </template>
</div>
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

</div>


{{-- Alpine helpers (در صورت نبود Alpine در app.js) --}}
<script>
    function dashboardLayout() {
        return {
            mobileOpen: false,
            sidebarCollapsed: false, // پیش‌فرض باز باشد
            theme: localStorage.getItem('theme') || 'system',
            themeIcon: 'system',

            init() {
                // بازیابی حالت سایدبار (اگر قبلاً ذخیره شده باشد)
                // اگر ذخیره نشده باشد (null)، پیش‌فرض false (باز) می‌ماند
                const storedState = localStorage.getItem('sidebarCollapsed');
                if (storedState !== null) {
                    this.sidebarCollapsed = storedState === '1';
                }

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
                // اگر سایدبار بسته است، هیچ منویی باز نیست
                if (this.sidebarCollapsed) return false;
                return localStorage.getItem('menu:'+key) === '1';
            },
            toggleMenu(key) {
                // اگر سایدبار بسته است، این تابع کار نمی‌کند
                if (this.sidebarCollapsed) return;
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
@livewireScripts
@livewireScriptConfig
</body>
</html>
