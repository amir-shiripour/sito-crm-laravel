@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    $groups = $groups ?? config('navigation.admin.sidebar', []);
@endphp

<div class="flex h-full flex-col">
    <div class="flex h-16 items-center justify-between border-b border-slate-200/70 px-4 dark:border-slate-700/60">
        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/') }}" class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-content-center rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 text-base font-bold text-white">
                {{ mb_substr(config('app.name', 'CRM'), 0, 2) }}
            </div>
            <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ config('app.name', 'CRM Suite') }}</div>
        </a>
        <button data-sidebar-close class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200/80 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden">
            <span class="sr-only">بستن منو</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-6">
        <div class="space-y-8">
            @foreach ($groups as $group)
                @php($items = Arr::get($group, 'items', []))
                @if (empty($items))
                    @continue
                @endif
                <div class="space-y-3">
                    @if ($group['heading'] ?? null)
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500/80 dark:text-slate-400/70">
                            {{ $group['heading'] }}
                        </p>
                    @endif
                    <ul class="space-y-1">
                        @foreach ($items as $item)
                            @php
                                if (!empty($item['can']) && !Auth::user()?->can($item['can'])) {
                                    continue;
                                }
                                if (!empty($item['route']) && !Route::has($item['route'])) {
                                    continue;
                                }
                                $patterns = $item['active'] ?? [$item['route'] ?? null];
                                $patterns = array_filter((array) $patterns);
                                $isActive = false;
                                foreach ($patterns as $pattern) {
                                    if (request()->routeIs($pattern)) {
                                        $isActive = true;
                                        break;
                                    }
                                }
                                $baseClasses = 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition';
                                $activeClasses = $isActive ? 'bg-gradient-to-l from-indigo-500 to-sky-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/70';
                                $target = !empty($item['route']) ? route($item['route']) : ($item['url'] ?? '#');
                            @endphp
                            <li>
                                <a href="{{ $target }}" class="{{ $baseClasses }} {{ $activeClasses }}">
                                    @if (!empty($item['icon']))
                                        <x-icon :name="$item['icon']" class="h-5 w-5 opacity-80" />
                                    @endif
                                    <span class="font-medium">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </nav>

    <div class="border-t border-slate-200/70 px-4 py-5 text-[11px] text-slate-500 dark:border-slate-700/60 dark:text-slate-400">
        <div>ورود با: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ Auth::user()?->name }}</span></div>
        <div class="mt-1">{{ now()->format('Y/m/d H:i') }}</div>
    </div>
</div>
