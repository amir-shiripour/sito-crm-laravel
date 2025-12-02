{{-- clients::layouts.client --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'پنل '.config('clients.labels.plural', 'مشتریان') }} | {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css','resources/js/app.js'])

    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased selection:bg-indigo-500 selection:text-white">
<div class="min-h-screen flex flex-col">

    {{-- هدر سایت --}}
    <header class="sticky top-0 z-40 w-full border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md transition-colors">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">

                {{-- بخش لوگو و عنوان --}}
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/20">
                        {{-- آیکون کاربر --}}
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="flex flex-col justify-center">
                        <h1 class="text-sm font-bold text-gray-900 dark:text-white">
                            پنل {{ config('clients.labels.plural', 'مشتریان') }}
                        </h1>
                        <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                                {{ config('app.name') }}
                            </span>
                    </div>
                </div>

                {{-- بخش پروفایل و خروج --}}
                @auth('client')
                    <div class="flex items-center gap-4">
                        {{-- نام کاربر (فقط دسکتاپ) --}}
                        <div class="hidden md:flex flex-col items-end">
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ auth('client')->user()->full_name }}
                                </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 dir-ltr font-mono">
                                    {{ auth('client')->user()->username }}
                                </span>
                        </div>

                        {{-- دیوایدر عمودی --}}
                        <div class="hidden md:block h-8 w-px bg-gray-200 dark:bg-gray-700"></div>

                        {{-- دکمه خروج --}}
                        <form method="POST" action="{{ route('client.logout') }}">
                            @csrf
                            <button type="submit"
                                    class="group flex items-center gap-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-medium text-red-600 transition-all hover:bg-red-100 hover:text-red-700 dark:bg-red-900/10 dark:text-red-400 dark:hover:bg-red-900/20"
                                    title="خروج از حساب">
                                <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="hidden sm:inline">خروج</span>
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    {{-- محتوای اصلی --}}
    <main class="flex-1 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    {{-- فوتر --}}
    <footer class="mt-auto border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 py-6">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. تمامی حقوق محفوظ است.
            </p>
        </div>
    </footer>
</div>
</body>
</html>
