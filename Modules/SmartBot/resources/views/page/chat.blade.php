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
<body class="bg-white dark:bg-[#151515] text-zinc-900 dark:text-zinc-100 selection:bg-indigo-500/30 selection:text-white transition-colors duration-300">

<!-- Render The Livewire Widget in Full Screen Mode -->
<livewire:smartbot.widget.chat-widget :isStandalone="true" />

<!-- اسکریپت‌های Livewire -->
@livewireScripts
</body>
</html>
