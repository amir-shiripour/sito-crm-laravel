<x-app-layout>
    @php($title = $title ?? 'پنل مدیریت')

    @php
        $sidebarGroups = config('navigation.admin.sidebar', []);
    @endphp

    <div class="min-h-screen bg-slate-100/80 transition-colors duration-150 dark:bg-slate-950">
        <div class="relative mx-auto flex min-h-screen w-full max-w-[1600px]">
            <div id="admin-sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/40 backdrop-blur-sm lg:hidden"></div>

            <aside id="admin-sidebar" class="fixed start-0 top-0 z-40 flex h-full w-72 -translate-x-full transform flex-col border-l border-slate-200/70 bg-white/90 backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/80 lg:relative lg:translate-x-0 lg:border-r lg:border-l-0">
                @include('admin.partials.sidebar', ['groups' => $sidebarGroups])
            </aside>

            <div class="flex min-h-screen flex-1 flex-col lg:ps-72">
                @include('admin.partials.topbar', ['title' => $title])

                <main class="flex-1 px-4 pb-10 pt-6 sm:px-6 lg:px-10">
                    <div class="mx-auto w-full max-w-7xl">
                        @include('admin.partials.flash')

                        @if (View::hasSection('content'))
                            @yield('content')
                        @else
                            {{ $slot ?? '' }}
                        @endif
                    </div>
                </main>

                <footer class="px-4 py-6 text-xs text-slate-500 dark:text-slate-400 sm:px-6 lg:px-10">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <span>© {{ date('Y') }} {{ config('app.name', 'CRM Suite') }}</span>
                        <span class="text-slate-400">نسخه {{ app()->version() }}</span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sidebar = document.getElementById('admin-sidebar');
                const backdrop = document.getElementById('admin-sidebar-backdrop');
                const openButtons = document.querySelectorAll('[data-sidebar-open]');
                const closeButtons = document.querySelectorAll('[data-sidebar-close]');

                const open = () => {
                    sidebar.classList.add('translate-x-0');
                    sidebar.classList.remove('-translate-x-full');
                    backdrop?.classList.remove('hidden');
                };

                const close = () => {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                    backdrop?.classList.add('hidden');
                };

                sidebar.classList.add('transition-transform', 'duration-200', 'ease-out');

                const sync = () => {
                    if (window.innerWidth >= 1024) {
                        sidebar.classList.add('translate-x-0');
                        sidebar.classList.remove('-translate-x-full');
                        backdrop?.classList.add('hidden');
                    } else {
                        close();
                    }
                };

                openButtons.forEach((button) => button.addEventListener('click', open));
                closeButtons.forEach((button) => button.addEventListener('click', close));
                backdrop?.addEventListener('click', close);

                sync();
                window.addEventListener('resize', sync);
            });
        </script>
    @endpush
</x-app-layout>
