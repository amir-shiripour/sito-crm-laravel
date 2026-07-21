<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#ffffff">

    @php
        $globalSettings = \Modules\Settings\Entities\Setting::pluck('value', 'key')->toArray();
        $appName = $globalSettings['app_name'] ?? config('app.name', 'CRM هوشمند');
        $appLogo = isset($globalSettings['app_logo']) ? asset($globalSettings['app_logo']) : null;
        $footerText = $globalSettings['footer_text'] ?? 'تمام حقوق محفوظ است.';
        $appTheme = $globalSettings['app_theme'] ?? 'default';

        $isMarketActive = false;
        try {
            if (class_exists(\App\Models\Module::class)) {
                $isMarketActive = \App\Models\Module::where('slug', 'market')
                    ->where('installed', true)
                    ->where('active', true)
                    ->exists();
            }
        } catch (\Throwable $e) {}

        $themeStyles = [
            'default' => [
                'glow' => 'bg-indigo-500/20 dark:bg-indigo-500/10',
                'gradient_bg' => 'radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.12) 0%, transparent 50%), radial-gradient(circle at 100% 0%, rgba(16, 185, 129, 0.08) 0%, transparent 50%)',
            ],
            'booking' => [
                'glow' => 'bg-teal-500/20 dark:bg-teal-500/10',
                'gradient_bg' => 'radial-gradient(circle at 50% 0%, rgba(20, 184, 166, 0.12) 0%, transparent 50%), radial-gradient(circle at 100% 0%, rgba(16, 185, 129, 0.08) 0%, transparent 50%)',
            ],
            'market' => [
                'glow' => 'bg-orange-500/20 dark:bg-orange-500/10',
                'gradient_bg' => 'radial-gradient(circle at 50% 0%, rgba(249, 115, 22, 0.12) 0%, transparent 50%), radial-gradient(circle at 100% 0%, rgba(239, 68, 68, 0.08) 0%, transparent 50%)',
            ],
            'properties' => [
                'glow' => 'bg-blue-500/20 dark:bg-blue-500/10',
                'gradient_bg' => 'radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.12) 0%, transparent 50%), radial-gradient(circle at 100% 0%, rgba(14, 165, 233, 0.08) 0%, transparent 50%)',
            ]
        ];
        $activeStyle = $themeStyles[$appTheme] ?? $themeStyles['default'];
    @endphp

    <title>{{ $appName }} - @yield('title', 'صفحه اصلی')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'IRANYekanX', sans-serif; -webkit-tap-highlight-color: transparent; }
        .theme-dynamic-bg { background: {!! $activeStyle['gradient_bg'] !!}; }
        /* کلاس کمکی برای safe-area در گوشی‌های آیفون و گوشی‌های جدید که Bottom Navigation دارند */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    </style>

    {{-- Dark Mode Initializer --}}
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.querySelector('meta[name="theme-color"]').setAttribute('content', '#030712'); // ست کردن رنگ بالای مرورگر برای موبایل
        } else {
            document.documentElement.classList.remove('dark');
            document.querySelector('meta[name="theme-color"]').setAttribute('content', '#ffffff');
        }

        function setAppThemeMode(mode) {
            if (mode === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
                document.querySelector('meta[name="theme-color"]').setAttribute('content', '#030712');
            } else if (mode === 'light') {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
                document.querySelector('meta[name="theme-color"]').setAttribute('content', '#ffffff');
            } else {
                localStorage.removeItem('theme');
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                    document.querySelector('meta[name="theme-color"]').setAttribute('content', '#030712');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.querySelector('meta[name="theme-color"]').setAttribute('content', '#ffffff');
                }
            }
            updateThemeUI();
        }
    </script>
    @stack('styles')
</head>

<body class="antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col relative overflow-x-hidden transition-colors duration-300">

<div class="fixed inset-0 theme-dynamic-bg pointer-events-none z-0"></div>
<div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] {{ $activeStyle['glow'] }} blur-[100px] rounded-full pointer-events-none z-0"></div>

{{-- Toast / Notification Component --}}
<div
    x-data="{
        items: [],
        notify(e) {
            const id = Date.now() + Math.random();
            const detail = e.detail || e;
            this.items.push({
                id,
                type: detail.type || 'info',
                text: detail.text || detail.message || '',
            });
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
    class="fixed right-3 top-3 z-[99999] w-80 max-w-[90vw] space-y-2" style="z-index: 99999"
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

{{-- Header --}}
@includeFirst(["themes.{$appTheme}.header", 'themes.default.header'], ['appName' => $appName, 'appLogo' => $appLogo])

{{-- Main Content --}}
<main class="flex-grow flex flex-col relative z-10 w-full pb-16 md:pb-0" style="padding-top: 5rem">
    @yield('content')
</main>

{{-- Footer --}}
@includeFirst(["themes.{$appTheme}.footer", 'themes.default.footer'], ['appName' => $appName, 'footerText' => $footerText])

{{-- Global Livewire Components --}}
@if($isMarketActive)
    @livewire('market::web.popup-cart')
    @livewire('market::web.checkout-modal')
    @livewire('market::web.location-modal')
@endif

@php
    $isSmartBotActive = false;
    try {
        if (class_exists(\App\Models\Module::class)) {
            $isSmartBotActive = \App\Models\Module::where('slug', 'smartbot')
                ->where('installed', true)
                ->where('active', true)
                ->exists();
        }
    } catch (\Throwable $e) {}
@endphp

@if($isSmartBotActive && \Modules\SmartBot\App\Models\BotSetting::getValue('is_widget_enabled', true))
    @livewire('smartbot.widget.chat-widget')
@endif

<script>
    function updateThemeUI() {
        const currentTheme = localStorage.theme || 'system';
        document.querySelectorAll('.theme-switcher-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'dark:bg-gray-600', 'shadow-sm', 'text-gray-900', 'dark:text-white');
            btn.classList.add('text-gray-500', 'dark:text-gray-400');
            if(btn.dataset.theme === currentTheme) {
                btn.classList.remove('text-gray-500', 'dark:text-gray-400');
                btn.classList.add('bg-white', 'dark:bg-gray-600', 'shadow-sm', 'text-gray-900', 'dark:text-white');
            }
        });
    }
    document.addEventListener('DOMContentLoaded', updateThemeUI);
</script>

@livewireScripts
@stack('scripts')

{{-- استایل‌های Media Query در فایل Blade مستقیما روی تگ main اعمال شدند تا نیازی به style تگ در پایین نباشد --}}
<style>
    @media (min-width: 768px) { main { padding-top: 8rem !important; } }
</style>
</body>

</html>
