{{-- layouts/admin.blade.php --}}
<x-app-layout>
    <x-slot name="header"></x-slot>

    <div x-data="{
            sidebarOpen: false,
            dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
         }"
         x-init="
            $watch('dark', val => {
                localStorage.setItem('theme', val ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', val);
            });
            document.documentElement.classList.toggle('dark', dark);
         "
         class="min-h-screen bg-[#f8fafc] dark:bg-[#070a13] text-slate-900 dark:text-slate-100 transition-colors duration-500 font-iranYekan">

        <div class="flex">
            {{-- Sidebar (desktop) --}}
            <aside class="hidden lg:block w-72 bg-white/80 dark:bg-slate-900/50 backdrop-blur-xl border-l lg:border-l-0 lg:border-r border-slate-200 dark:border-slate-800/60 min-h-screen sticky top-0 z-40">
                @include('admin.partials.sidebar')
            </aside>

            {{-- Drawer mask (mobile) --}}
            <div x-show="sidebarOpen"
                 x-transition.opacity
                 class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
                 @click="sidebarOpen=false"></div>

            {{-- Sidebar (mobile) --}}
            <aside x-show="sidebarOpen"
                   x-transition:enter="transition ease-out duration-300"
                   x-transition:enter-start="-translate-x-full opacity-0"
                   x-transition:enter-end="translate-x-0 opacity-100"
                   class="fixed z-50 top-0 bottom-0 start-0 w-72 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 p-0 lg:hidden shadow-2xl">
                @include('admin.partials.sidebar')
            </aside>

            {{-- Main column --}}
            <div class="flex-1 flex flex-col min-h-screen">
                {{-- Topbar --}}
                @include('admin.partials.topbar')

                {{-- Page content --}}
                <main class="p-4 sm:p-6 lg:p-10">
                    <div class="mx-auto max-w-7xl">
                        @include('admin.partials.flash')

                        <div class="relative">
                            @if (View::hasSection('content'))
                                @yield('content')
                            @else
                                {{ $slot ?? '' }}
                            @endif
                        </div>
                    </div>
                </main>

                {{-- Footer --}}
                <footer class="mt-auto py-8 text-center">
                    <div class="inline-flex items-center px-4 py-2 bg-white/50 dark:bg-slate-900/30 backdrop-blur-sm rounded-2xl border border-slate-200/50 dark:border-slate-800/50">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 tracking-tight italic">
                            © {{ date('Y') }} <span class="text-indigo-600 dark:text-indigo-400 font-black">SITO CRM</span> • Core Version System
                        </span>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</x-app-layout>
