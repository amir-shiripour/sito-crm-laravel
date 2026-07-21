<!DOCTYPE html>
<html lang="fa" dir="rtl" class="light"> <!-- می‌توانید کلاس dark را بر اساس تنظیمات سیستم اضافه کنید -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>هوش مصنوعی اختصاصی | SmartBot</title>

    <!-- در صورتی که Tailwind را از طریق Vite لود می‌کنید -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- استایل‌های Livewire -->
    @livewireStyles

    <!-- اسکریپت مقداردهی اولیه تم (لایت / دارک / اتوماتیک) -->
    <script>
        const theme = localStorage.getItem('theme') || 'auto';
        if (theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
        }
    </script>

    <!-- فونت‌ها (از فونت دلخواه خود در پروژه استفاده کنید، این فونت‌ها برای اطمینان از زیبایی اضافه شده‌اند) -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'IRANYekanX', 'DM Sans', tahoma, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        /* مخفی کردن اسکرول‌بار مرورگر برای زیبایی بیشتر (اختیاری) */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #d4d4d8;
            border-radius: 4px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #3f3f46;
        }
    </style>
</head>
<body class="bg-slate-50/50 dark:bg-[#0c0c0e] text-zinc-900 dark:text-zinc-100 selection:bg-indigo-500/30 selection:text-white transition-colors duration-300 min-h-screen relative overflow-x-hidden">

<!-- Modern Gradient Mesh / Glassmorphism Background Elements -->
<div class="fixed inset-0 z-0 pointer-events-none overflow-hidden select-none">
    <!-- Top Right Light Blue/Purple Glow -->
    <div class="absolute -top-[20%] -right-[10%] w-[60%] h-[55%] rounded-full bg-gradient-to-br from-indigo-200/50 to-purple-200/40 dark:from-indigo-500/20 dark:to-purple-500/15 blur-[120px] transform rotate-12"></div>
    
    <!-- Left Mid Pink/Rose Glow -->
    <div class="absolute top-[30%] -left-[15%] w-[50%] h-[50%] rounded-full bg-gradient-to-tr from-rose-200/30 to-pink-200/30 dark:from-rose-500/12 dark:to-pink-500/12 blur-[130px]"></div>

    <!-- Bottom Right Indigo/Blue Glow -->
    <div class="absolute -bottom-[15%] right-[10%] w-[55%] h-[50%] rounded-full bg-gradient-to-tl from-blue-200/40 to-indigo-200/30 dark:from-blue-500/15 dark:to-indigo-500/15 blur-[140px]"></div>
    
    <!-- Premium Dotted Grid Overlay -->
    <div class="absolute inset-0 bg-[radial-gradient(#e2e8f0_1.2px,transparent_1.2px)] dark:bg-[radial-gradient(#312e81_1.2px,transparent_1.2px)] [background-size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_80%,transparent_100%)] opacity-80"></div>
</div>

<div class="relative z-10 min-h-screen flex flex-col">
    <!-- Render The Livewire Widget in Full Screen Mode -->
    <livewire:smartbot.widget.chat-widget :isStandalone="true" />
</div>

@php
    $isMarketModuleActive = false;
    try {
        if (class_exists(\App\Models\Module::class)) {
            $isMarketModuleActive = \App\Models\Module::where('slug', 'market')
                ->where('installed', true)
                ->where('active', true)
                ->exists();
        }
    } catch (\Throwable $e) {}
@endphp

@if($isMarketModuleActive)
    @livewire('market::web.cart-manager')
    @livewire('market::web.popup-cart')
    @livewire('market::web.checkout-modal')
    @livewire('market::web.location-modal')
@endif

<!-- اسکریپت‌های Livewire -->
@livewireScripts
</body>
</html>
