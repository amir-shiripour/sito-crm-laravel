<!DOCTYPE html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

    <script>
        (() => {
            const storedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = storedTheme ?? (prefersDark ? 'dark' : 'light');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @stack('styles')
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 transition-colors duration-150 dark:bg-slate-950 dark:text-slate-100">
<x-banner />

<div class="min-h-screen flex flex-col">
    @livewire('navigation-menu')

    @if (isset($header))
        <header class="border-b border-slate-200/70 bg-white/70 backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/60">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 sm:flex-row sm:items-center sm:justify-between sm:py-8 lg:px-6">
                {{ $header }}
            </div>
        </header>
    @endif

    <main class="flex-1">
        <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </div>
    </main>

    @php($policyUrl = Route::has('policy.show') ? route('policy.show') : '#')
    @php($termsUrl = Route::has('terms.show') ? route('terms.show') : '#')
    <footer class="mt-auto border-t border-slate-200/70 bg-white/70 px-4 py-6 text-xs text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-400 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ date('Y') }} {{ config('app.name', 'CRM Suite') }}</span>
            <div class="flex items-center gap-4">
                <a href="{{ $policyUrl }}" class="hover:text-indigo-500">حریم خصوصی</a>
                <a href="{{ $termsUrl }}" class="hover:text-indigo-500">قوانین استفاده</a>
            </div>
        </div>
    </footer>
</div>

@stack('modals')

@livewireScripts
@stack('scripts')

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const getTheme = () => (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        const setTheme = (next) => {
            document.documentElement.classList.toggle('dark', next === 'dark');
            localStorage.setItem('theme', next);
        };

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const next = getTheme() === 'dark' ? 'light' : 'dark';
                setTheme(next);
            });
        });
    });
</script>
</body>
</html>
