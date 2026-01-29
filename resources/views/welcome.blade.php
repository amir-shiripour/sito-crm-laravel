<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CRM هوشمند') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'IRANYekanX', sans-serif;
        }

        /* افکت گرادینت پس‌زمینه */
        .gradient-bg {
            background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
        }
    </style>
</head>

<body
    class="antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col relative overflow-x-hidden">

<div class="fixed inset-0 gradient-bg pointer-events-none z-0"></div>
<div
    class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-indigo-500/20 blur-[100px] rounded-full pointer-events-none z-0 dark:bg-indigo-500/10">
</div>

<header
    class="fixed w-full top-0 z-50 transition-all duration-300 backdrop-blur-md border-b border-transparent bg-white/50 dark:bg-gray-950/50">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <span class="font-bold text-xl tracking-tight hidden sm:block">سیستم CRM</span>
        </div>

        <nav class="flex items-center gap-4">
            @auth
                <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-all dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 shadow-lg shadow-gray-200/50 dark:shadow-none">
                    <span>پنل مدیریت</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors dark:text-gray-300 dark:hover:text-white">
                    ورود به سیستم
                </a>
                {{--
                <a href="{{ route('register') }}"
                class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 shadow-lg
                shadow-indigo-600/30 hover:shadow-indigo-600/40 transition-all transform hover:-translate-y-0.5">
                ثبت نام رایگان
                </a>
                --}}
            @endauth
        </nav>
    </div>
</header>

<main class="flex-grow flex flex-col justify-center pt-32 pb-16 px-6 relative z-10">
    <div class="max-w-5xl mx-auto text-center space-y-8">

        <div class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-medium mb-4 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                نسخه جدید منتشر شد
            </div>

            <h1 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white leading-tight">
                مدیریت هوشمند <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-emerald-500">مشتریان</span><br
                    class="hidden md:block" />
                برای رشد کسب‌وکار شما
            </h1>

            <p class="text-lg md:text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                یک پلتفرم یکپارچه برای مدیریت ارتباط با مشتریان، پیگیری فروش و تحلیل عملکرد تیم شما. ساده، سریع و
                قدرتمند.
            </p>
        </div>

        <div
            class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4 animate-in fade-in slide-in-from-bottom-4 duration-1000 delay-200">
            <a href="{{ route('booking.public.index') }}"
               class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-bold text-lg hover:from-indigo-700 hover:to-indigo-800 shadow-xl shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2 group">
                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                رزرو آنلاین نوبت
            </a>
            @auth
                <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}"
                   class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-gray-700 font-bold text-lg border-2 border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all dark:bg-gray-900 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:border-indigo-500 dark:hover:text-indigo-400 flex items-center justify-center gap-2">
                    ورود به داشبورد
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-gray-700 font-bold text-lg border-2 border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all dark:bg-gray-900 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:border-indigo-500 dark:hover:text-indigo-400 flex items-center justify-center gap-2">
                    ورود به سیستم
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            @endauth
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-16 text-right">
            <div
                class="p-6 rounded-2xl bg-white border border-gray-100 shadow-lg shadow-gray-200/40 dark:bg-gray-900 dark:border-gray-800 dark:shadow-none hover:border-indigo-500/30 transition-colors group">
                <div
                    class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform dark:bg-indigo-900/30 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">مدیریت مشتریان</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">پرونده کامل مشتریان خود را ایجاد
                    کنید و تمام تعاملات را در یک نگاه ببینید.</p>
            </div>

            <div
                class="p-6 rounded-2xl bg-white border border-gray-100 shadow-lg shadow-gray-200/40 dark:bg-gray-900 dark:border-gray-800 dark:shadow-none hover:border-emerald-500/30 transition-colors group">
                <div
                    class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform dark:bg-emerald-900/30 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">فرم‌ساز داینامیک</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">بدون نیاز به کدنویسی، فرم‌های
                    ورود اطلاعات سفارشی برای کسب‌وکار خود بسازید.</p>
            </div>

            <div
                class="p-6 rounded-2xl bg-white border border-gray-100 shadow-lg shadow-gray-200/40 dark:bg-gray-900 dark:border-gray-800 dark:shadow-none hover:border-blue-500/30 transition-colors group">
                <div
                    class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">سرعت و امنیت</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">توسعه یافته با جدیدترین
                    تکنولوژی‌ها برای تضمین سرعت بالا و امنیت داده‌ها.</p>
            </div>
        </div>

    </div>
</main>

<footer class="border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 py-8 z-10">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} تمام حقوق محفوظ است. توسعه داده شده با ❤️
        </p>
        <div class="flex items-center gap-6 text-sm text-gray-500">
            <a href="#" class="hover:text-indigo-600 transition-colors">قوانین و مقررات</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">حریم خصوصی</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">تماس با ما</a>
        </div>
    </div>
</footer>

</body>

</html>
