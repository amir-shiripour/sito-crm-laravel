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

    @vite(['Resources/css/app.css', 'Resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-size: 14px;}
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100">
<div
    x-data="{
        items: [],
        notify(e) {
            const id = Date.now() + Math.random();
            const detail = e.detail || {};
            this.items.push({
                id,
                type: detail.type || 'info',
                text: detail.text || detail.message || '',
            });
            // auto hide
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.items = this.items.filter(i => i.id !== id);
        }
    }"
    x-on:notify.window="notify($event)"
    class="fixed right-3 top-3 z-50 w-80 max-w-[90vw] space-y-2" style="z-index: 999"
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
@livewireScripts
@livewireScriptConfig
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('toast', {
            items: [],
            push(message) {
                const id = Date.now() + Math.random();
                this.items.push({ id, ...message });
                setTimeout(() => this.close(id), message.timeout ?? 3000);
            },
            close(id) {
                this.items = this.items.filter(i => i.id !== id);
            }
        });

        // شنیدن ایونت‌های Livewire (v3) و تبدیل به toast
        window.addEventListener('notify', (e) => {
            const detail = e?.detail || {};
            Alpine.store('toast').push({
                type: detail.type || 'info',
                text: detail.text || '',
                timeout: detail.timeout || 3000
            });
        });
    });
</script>
</body>
</html>
