@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $quickActions = [];
    foreach (config('navigation.admin.quick_actions', []) as $action) {
        if (!empty($action['can']) && !Auth::user()?->can($action['can'])) {
            continue;
        }
        if (!empty($action['route']) && !Route::has($action['route'])) {
            continue;
        }
        $quickActions[] = $action;
    }
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/80 backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/70">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:h-20 sm:px-6 lg:px-10">
        <div class="flex items-center gap-3">
            <button data-sidebar-open class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden">
                <span class="sr-only">باز کردن منو</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $title ?? 'پنل مدیریت' }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">مدیریت و کنترل یکپارچه کسب‌وکار</span>
            </div>
        </div>

        <div class="flex flex-1 items-center justify-end gap-3">
            <form action="{{ route('admin.dashboard') }}" method="GET" class="relative hidden lg:block">
                <label class="sr-only" for="admin-global-search">جستجو</label>
                <input id="admin-global-search" name="q" type="search" placeholder="جستجوی سریع..." class="w-72 rounded-2xl border border-slate-200/70 bg-white/70 py-2.5 ps-10 pe-4 text-sm text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-100" />
                <svg class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-3.873-3.873m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.877 11.877z" /></svg>
            </form>

            @if ($quickActions)
                <div class="hidden items-center gap-2 xl:flex">
                    @foreach ($quickActions as $action)
                        @php($href = !empty($action['route']) ? route($action['route']) : ($action['url'] ?? '#'))
                        <a href="{{ $href }}" class="flex items-center gap-2 rounded-xl border border-indigo-100/60 bg-indigo-500/10 px-3 py-2 text-xs font-medium text-indigo-600 transition hover:border-indigo-500 hover:bg-indigo-500 hover:text-white dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-200 dark:hover:border-indigo-400 dark:hover:bg-indigo-400/90 dark:hover:text-white">
                            @if (!empty($action['icon']))
                                <x-icon :name="$action['icon']" class="h-4 w-4" />
                            @endif
                            <span>{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <button data-theme-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800">
                <span class="sr-only">تغییر تم</span>
                <svg class="h-5 w-5 dark:hidden" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.25a.75.75 0 01.75.75V5a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM6.364 4.636a.75.75 0 011.06 0l1.275 1.275a.75.75 0 11-1.06 1.06L6.364 5.697a.75.75 0 010-1.061zM4.5 11.25a.75.75 0 010-1.5H6a.75.75 0 010 1.5H4.5zm10.5 9a.75.75 0 01.75-.75H18a.75.75 0 010 1.5h-2.25a.75.75 0 01-.75-.75zM17.636 4.636a.75.75 0 010 1.06l-1.275 1.276a.75.75 0 11-1.06-1.061l1.275-1.275a.75.75 0 011.06 0zM5.64 18.364a.75.75 0 011.061 0l1.275 1.275a.75.75 0 11-1.06 1.06L5.64 19.424a.75.75 0 010-1.06z" /><path d="M12 18.75a6.75 6.75 0 100-13.5 6.75 6.75 0 000 13.5z" /></svg>
                <svg class="hidden h-5 w-5 dark:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M21.752 15.002a9 9 0 01-12.75-12.75 9.001 9.001 0 1012.75 12.75z" /></svg>
            </button>

            <a href="{{ Route::has('notifications.index') ? route('notifications.index') : route('admin.dashboard') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800">
                <span class="sr-only">اعلان‌ها</span>
                <span class="absolute -top-1 -start-1 h-2 w-2 rounded-full bg-rose-500"></span>
                <x-icon name="bell" class="h-5 w-5" />
            </a>

            <div class="relative">
                <button class="flex h-10 items-center gap-3 rounded-xl border border-slate-200/70 bg-white/70 px-2 ps-2 pe-3 text-sm font-medium text-slate-600 shadow-sm transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-indigo-400 dark:hover:text-indigo-200" type="button">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <img class="h-8 w-8 rounded-xl object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    @else
                        <span class="grid h-8 w-8 place-content-center rounded-xl bg-indigo-500/20 text-sm font-semibold text-indigo-600 dark:bg-indigo-500/30 dark:text-indigo-200">
                            {{ Str::of(Auth::user()?->name)->substr(0, 2)->upper() }}
                        </span>
                    @endif
                    <span class="hidden sm:block">{{ Auth::user()?->name }}</span>
                </button>
            </div>
        </div>
    </div>
</header>
