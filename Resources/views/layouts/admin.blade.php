{{-- layouts/admin.blade.php --}}
<x-app-layout>
    <x-slot name="header"></x-slot>


    <div x-data="{ sidebarOpen:false, dark: document.documentElement.classList.contains('dark') }" x-init="$watch('dark', v => document.documentElement.classList.toggle('dark', v))" class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="flex">
            {{-- Sidebar (desktop) --}}
            <aside class="hidden lg:block w-72 bg-white dark:bg-gray-800 border-l lg:border-l-0 lg:border-r border-gray-200/70 dark:border-gray-700/60 min-h-screen sticky top-0">
                @include('admin.partials.sidebar')
            </aside>


            {{-- Drawer mask (mobile) --}}
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="sidebarOpen=false"></div>


            {{-- Sidebar (mobile) --}}
            <aside x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="-translate-x-full opacity-0" class="fixed z-50 top-0 bottom-0 start-0 w-72 bg-white dark:bg-gray-800 border-r border-gray-200/70 dark:border-gray-700/60 p-0 lg:hidden">
                @include('admin.partials.sidebar')
            </aside>


            {{-- Main column --}}
            <div class="flex-1 flex flex-col min-h-screen">
                {{-- Topbar --}}
                @include('admin.partials.topbar')


                {{-- Page content --}}
                <main class="p-4 sm:p-6 lg:p-8">
                    <div class="mx-auto max-w-7xl">
                        @include('admin.partials.flash')


                        @if (View::hasSection('content'))
                            @yield('content')
                        @else
                            {{ $slot ?? '' }}
                        @endif
                    </div>
                </main>


                {{-- Footer optional --}}
                <footer class="mt-auto py-6 text-center text-xs text-gray-500 dark:text-gray-400">
                    <span>© {{ date('Y') }} CRM Admin</span>
                </footer>
            </div>
        </div>
    </div>
</x-app-layout>
