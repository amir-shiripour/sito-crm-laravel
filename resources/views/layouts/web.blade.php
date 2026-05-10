<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $globalSettings = \Modules\Settings\Entities\Setting::pluck('value', 'key')->toArray();
        $appName = $globalSettings['app_name'] ?? config('app.name', 'CRM هوشمند');
        $appLogo = isset($globalSettings['app_logo']) ? asset($globalSettings['app_logo']) : null;
        $footerText = $globalSettings['footer_text'] ?? 'تمام حقوق محفوظ است.';
        $appTheme = $globalSettings['app_theme'] ?? 'default';

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
        body { font-family: 'IRANYekanX', sans-serif; }
        .theme-dynamic-bg { background: {!! $activeStyle['gradient_bg'] !!}; }
    </style>

    {{-- Dark Mode Initializer (اجرا قبل از رندر صفحه برای جلوگیری از پرش نور) --}}
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // تابع تغییر تم و ذخیره در LocalStorage
        function setAppThemeMode(mode) {
            if (mode === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            } else if (mode === 'light') {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                localStorage.removeItem('theme');
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
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

{{--
    [Architecture Note]:
    لود کردن داینامیک هدر. اگر فایل themes/market/header.blade.php وجود نداشت،
    به طور خودکار themes/default/header.blade.php لود می‌شود!
--}}
@includeFirst(["themes.{$appTheme}.header", 'themes.default.header'], ['appName' => $appName, 'appLogo' => $appLogo])

<main class="flex-grow flex flex-col relative z-10 w-full" style="padding: 9rem 0">
    @yield('content')
</main>

{{-- لود کردن داینامیک فوتر --}}
@includeFirst(["themes.{$appTheme}.footer", 'themes.default.footer'], ['appName' => $appName, 'footerText' => $footerText])

{{-- اسکریپت به‌روزرسانی UI دکمه‌های تغییر تم --}}
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
@stack('scripts')
@livewireScripts
</body>

</html>
