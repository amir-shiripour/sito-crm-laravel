<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'IRANYekanX', sans-serif; }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-950 dark:text-gray-100">
@if (View::hasSection('content'))
    @yield('content')
@else
    {{ $slot ?? '' }}
@endif
</body>
</html>
