<!DOCTYPE html>
<html dir="rtl" lang="fa" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('panel.title', config('app.name', 'Laravel'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('panel.head')
    @livewireStyles
</head>
<body class="font-sans antialiased">
<x-banner />
@php($panelSidebar = trim(View::yieldContent('panel.sidebar')))
@php($panelTopbar = trim(View::yieldContent('panel.topbar')))
@php($panelFlash = trim(View::yieldContent('panel.flash')))
@php($panelFooter = trim(View::yieldContent('panel.footer')))

<div x-data="panelLayout()" x-init="init()" class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="flex">
        @if ($panelSidebar !== '')
            <aside class="hidden lg:block w-72 bg-white dark:bg-gray-800 border-l lg:border-l-0 lg:border-r border-gray-200/70 dark:border-gray-700/60 min-h-screen sticky top-0">
                {!! $panelSidebar !!}
            </aside>

            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="closeSidebar()"></div>

            <aside x-show="sidebarOpen"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="-translate-x-full opacity-0"
                   x-transition:enter-end="translate-x-0 opacity-100"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="translate-x-0 opacity-100"
                   x-transition:leave-end="-translate-x-full opacity-0"
                   class="fixed z-50 top-0 bottom-0 start-0 w-72 bg-white dark:bg-gray-800 border-r border-gray-200/70 dark:border-gray-700/60 p-0 lg:hidden">
                {!! $panelSidebar !!}
            </aside>
        @endif

        <div class="flex-1 flex flex-col min-h-screen">
            {!! $panelTopbar !!}

            <main class="p-4 sm:p-6 lg:p-8">
                <div class="mx-auto max-w-7xl">
                    @if ($panelFlash !== '')
                        {!! $panelFlash !!}
                    @endif

                    @yield('panel.content')
                </div>
            </main>

            @if ($panelFooter !== '')
                <footer class="mt-auto py-6 text-center text-xs text-gray-500 dark:text-gray-400">
                    {!! $panelFooter !!}
                </footer>
            @endif
        </div>
    </div>
</div>

@stack('modals')
@livewireScripts
@stack('panel.scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('panelLayout', () => ({
            sidebarOpen: false,
            dark: localStorage.getItem('panel:dark') === '1' || document.documentElement.classList.contains('dark'),
            init() {
                this.$watch('dark', value => {
                    document.documentElement.classList.toggle('dark', value);
                    localStorage.setItem('panel:dark', value ? '1' : '0');
                });
                document.documentElement.classList.toggle('dark', this.dark);
            },
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
            },
            closeSidebar() {
                this.sidebarOpen = false;
            },
            toggleDark() {
                this.dark = !this.dark;
            }
        }));
    });
</script>
</body>
</html>
