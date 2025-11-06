{{-- user/partials/sidebar.blade.php --}}
<div class="h-full flex flex-col">
    <div class="h-16 px-5 flex items-center border-b border-gray-200/70 dark:border-gray-700/60">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white grid place-content-center font-bold">{{ \Illuminate\Support\Str::substr(config('app.name', 'CRM'), 0, 1) }}</div>
            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ config('app.name', 'CRM') }}</div>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4">
        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-widest text-gray-500/80 dark:text-gray-400/70">پیشخوان</p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 opacity-80"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5V21a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 21v-10.5z"/></svg>
                        <span>داشبورد</span>
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-widest text-gray-500/80 dark:text-gray-400/70">حساب کاربری</p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('profile.show') }}"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('profile.show') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 opacity-80"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0v.75H4.5v-.75z"/></svg>
                        <span>پروفایل</span>
                    </a>
                </li>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <li>
                        <a href="{{ route('api-tokens.index') }}"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('api-tokens.index') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 opacity-80"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25V12l1.5 1.5m6.75-1.5a8.25 8.25 0 11-16.5 0 8.25 8.25 0 0116.5 0z"/></svg>
                            <span>توکن‌های API</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-widest text-gray-500/80 dark:text-gray-400/70">پشتیبانی</p>
            <ul class="space-y-1">
                <li>
                    <a href="#"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition text-gray-400 dark:text-gray-500 cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 opacity-80"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>به‌زودی</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="px-4 py-3 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-200/70 dark:border-gray-700/60">
        {{ config('app.name', 'CRM') }}
    </div>
</div>
