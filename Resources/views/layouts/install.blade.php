<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - نصب</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- فونت فارسی وزیرمتن --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">


    <!-- Scripts -->
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['Resources/css/app.css', 'Resources/js/app.js'])
    @else
        {{-- Fallback در صورت عدم وجود vite build --}}
        <style>
            body {
                font-family: 'Vazirmatn', sans-serif;
            }

            .mt-1 {
                margin-top: 0.25rem;
            }

            .block {
                display: block;
            }

            .w-full {
                width: 100%;
            }

            .px-3 {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .py-2 {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }

            .border {
                border-width: 1px;
            }

            .border-gray-300 {
                border-color: #d1d5db;
            }

            .rounded-md {
                border-radius: 0.375rem;
            }
        </style>
    @endif
</head>

<body class="font-vazirmatn text-gray-900 antialiased bg-gray-100 rtl">
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
    <div>
        <a href="/">
            {{-- لوگوی ساده --}}
            <svg class="w-20 h-20 fill-current text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                 fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                      clip-rule="evenodd" />
            </svg>
        </a>
    </div>

    <div class="w-full sm:max-w-xl mt-6 px-6 py-6 bg-white shadow-md overflow-hidden sm:rounded-lg">
        {{-- محتوای فرم‌های نصب (step1, step2, step3) اینجا قرار می‌گیرد --}}
        @yield('content')
    </div>
</div>
</body>

</html>
