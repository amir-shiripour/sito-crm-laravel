<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRM شما</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'IRANYekanX', sans-serif; }
    </style>
</head>
<body class="antialiased font-sans">
<div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
    <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-red-500 selection:text-white">
        <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
            <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                <div class="flex lg-justify-center lg:col-start-2">
                    <!-- لوگوی شما اینجا -->
                </div>
                <nav class="flex flex-1 justify-end">
                    @auth
                        {{-- لینک داشبورد به پنل ادمین ما --}}
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                        >
                            داشبورد
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                        >
                            ورود
                        </a>

                        <!-- لینک ثبت‌نام حذف شد -->

                    @endauth
                </nav>
            </header>

            <main class="mt-6">
                <div class="flex flex-col items-center justify-center">
                    <h1 class="text-4xl font-bold text-gray-800 dark:text-white">
                        به CRM خوش آمدید
                    </h1>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        راهکار جامع مدیریت ارتباط با مشتریان.
                    </p>

                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="mt-8 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
                            ورود به پنل مدیریت
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="mt-8 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
                            ورود به پنل مدیریت
                        </a>
                    @endauth

                </div>
            </main>

            <footer class="py-16 text-center text-sm text-black/50 dark:text-white/50">
                <!-- متن فوتر شما -->
            </footer>
        </div>
    </div>
</div>
</body>
</html>

