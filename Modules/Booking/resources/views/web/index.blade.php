<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CRM هوشمند') }} - رزرو آنلاین</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;300;400;500;700;900&display=swap"
          rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'IRANYekanX', 'Vazirmatn', sans-serif;
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
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <span class="font-bold text-xl tracking-tight hidden sm:block">سیستم CRM</span>
            </a>
        </div>

        <nav class="flex items-center gap-4">
            @auth
                <a href="{{ route('admin.dashboard') }}"
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
            @endauth
        </nav>
    </div>
</header>

<main class="flex-grow flex flex-col pt-32 pb-16 px-6 relative z-10">
    <div class="max-w-5xl mx-auto w-full space-y-8">

        {{-- Header Section --}}
        <div class="text-center space-y-4 mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div
                class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg mb-4 shadow-indigo-500/30">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">رزرو آنلاین <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-emerald-500">نوبت</span></h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                سرویس مورد نظر خود را انتخاب کنید و در کوتاه‌ترین زمان نوبت خود را رزرو کنید
            </p>
        </div>

        {{-- Services Grid --}}
        @if($services->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-100">
                @foreach($services as $srv)
                    <a href="{{ route('booking.public.service', $srv) }}"
                       class="group relative flex flex-col bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-2xl border border-gray-100 dark:border-gray-800 shadow-lg shadow-gray-200/40 dark:shadow-none hover:border-indigo-500/30 transition-all duration-300 overflow-hidden hover:-translate-y-1 h-full">

                        <div class="p-6 flex-1 flex flex-col space-y-4">
                            {{-- Service Header --}}
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <h3
                                        class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200 truncate">
                                        {{ $srv->name }}
                                    </h3>
                                    @if($srv->description)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2 leading-relaxed">
                                            {{ $srv->description }}
                                        </p>
                                    @endif
                                </div>
                                <div
                                    class="flex-shrink-0 w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-500 transition-all duration-300 text-indigo-600 dark:text-indigo-400 shadow-sm group-hover:shadow-indigo-500/30">
                                    {{-- Service Icon --}}
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Spacer --}}
                            <div class="flex-1"></div>

                            {{-- Price & Details --}}
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center flex-shrink-0 text-emerald-600 dark:text-emerald-400">
                                            <svg class="w-5 h-5" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">هزینه پایه</span>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($srv->base_price) }}</span>
                                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">تومان</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Button --}}
                            <div class="mt-2">
                                <div
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-center group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 flex items-center justify-center gap-2 group-hover:shadow-lg group-hover:shadow-indigo-600/30">
                                    <span>رزرو نوبت</span>
                                    {{-- Left Arrow for RTL --}}
                                    <svg class="w-4 h-4 transform transition-transform duration-300 group-hover:-translate-x-1" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div
                class="bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-2xl border border-gray-100 dark:border-gray-800 shadow-lg p-12 text-center animate-in fade-in zoom-in duration-500">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">سرویسی یافت نشد</h3>
                <p class="text-gray-500 dark:text-gray-400">در حال حاضر هیچ سرویسی برای رزرو آنلاین در دسترس نیست.</p>
            </div>
        @endif
    </div>
</main>

<footer class="border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 py-8 z-10">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} تمام حقوق محفوظ است.
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
