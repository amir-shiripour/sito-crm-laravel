@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    $normalize = function (array $links): array {
        $normalized = [];
        foreach ($links as $link) {
            if (!empty($link['can']) && !Auth::user()?->can($link['can'])) {
                continue;
            }
            if (!empty($link['route']) && !Route::has($link['route'])) {
                continue;
            }
            $normalized[] = $link;
        }
        return $normalized;
    };

    $primaryLinks = $normalize(config('navigation.user.primary', []));
    $secondaryLinks = $normalize(config('navigation.user.secondary', []));
    $brandUrl = Route::has('dashboard') ? route('dashboard') : url('/');
@endphp

<nav class="border-b border-slate-200/70 bg-white/80 backdrop-blur dark:border-slate-700/60 dark:bg-slate-950/70">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:h-20 sm:px-6 lg:px-8">
        <div class="flex flex-1 items-center gap-6">
            <a href="{{ $brandUrl }}" class="flex items-center gap-2 text-slate-900 transition hover:text-indigo-600 dark:text-slate-100">
                <x-application-mark class="h-8 w-auto" />
                <span class="hidden text-sm font-semibold sm:block">{{ config('app.name', 'CRM Suite') }}</span>
            </a>

            @if ($primaryLinks)
                <div class="hidden items-center gap-1 md:flex">
                    @foreach ($primaryLinks as $link)
                        @php
                            $href = $link['url'] ?? (!empty($link['route']) ? route($link['route']) : '#');
                            $patterns = Arr::wrap($link['active'] ?? ($link['route'] ?? null));
                            $isActive = false;
                            foreach ($patterns as $pattern) {
                                if ($pattern && request()->routeIs($pattern)) {
                                    $isActive = true;
                                    break;
                                }
                            }
                        @endphp
                        <a href="{{ $href }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition {{ $isActive ? 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 dark:bg-indigo-500/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800/70' }}">
                            @if (!empty($link['icon']))
                                <x-icon :name="$link['icon']" class="h-4 w-4" />
                            @endif
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <button data-theme-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800">
                <span class="sr-only">تغییر تم</span>
                <svg class="h-5 w-5 dark:hidden" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.25a.75.75 0 01.75.75V5a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM6.364 4.636a.75.75 0 011.06 0l1.275 1.275a.75.75 0 11-1.06 1.06L6.364 5.697a.75.75 0 010-1.061zM4.5 11.25a.75.75 0 010-1.5H6a.75.75 0 010 1.5H4.5zm10.5 9a.75.75 0 01.75-.75H18a.75.75 0 010 1.5h-2.25a.75.75 0 01-.75-.75zM17.636 4.636a.75.75 0 010 1.06l-1.275 1.276a.75.75 0 11-1.06-1.061l1.275-1.275a.75.75 0 011.06 0zM5.64 18.364a.75.75 0 011.061 0l1.275 1.275a.75.75 0 11-1.06 1.06L5.64 19.424a.75.75 0 010-1.06z" /><path d="M12 18.75a6.75 6.75 0 100-13.5 6.75 6.75 0 000 13.5z" /></svg>
                <svg class="hidden h-5 w-5 dark:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M21.752 15.002a9 9 0 01-12.75-12.75 9.001 9.001 0 1012.75 12.75z" /></svg>
            </button>

            <a href="{{ Route::has('notifications.index') ? route('notifications.index') : $brandUrl }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800">
                <span class="sr-only">اعلان‌ها</span>
                <span class="absolute -top-1 -start-1 h-2 w-2 rounded-full bg-rose-500"></span>
                <x-icon name="bell" class="h-5 w-5" />
            </a>

            @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                <div class="relative hidden sm:block">
                    <x-dropdown align="right" width="60">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200/70 px-3 py-2 text-xs font-medium text-slate-600 transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:text-slate-300 dark:hover:border-indigo-400 dark:hover:text-indigo-200">
                                <span>{{ Auth::user()->currentTeam->name }}</span>
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="w-60">
                                <div class="block px-4 py-2 text-xs text-slate-400">{{ __('Manage Team') }}</div>

                                <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                    {{ __('Team Settings') }}
                                </x-dropdown-link>

                                @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                    <x-dropdown-link href="{{ route('teams.create') }}">
                                        {{ __('Create New Team') }}
                                    </x-dropdown-link>
                                @endcan

                                @if (Auth::user()->allTeams()->count() > 1)
                                    <div class="border-t border-slate-200/70"></div>
                                    <div class="block px-4 py-2 text-xs text-slate-400">{{ __('Switch Teams') }}</div>
                                    @foreach (Auth::user()->allTeams() as $team)
                                        <x-switchable-team :team="$team" />
                                    @endforeach
                                @endif
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
            @endif

            <div class="relative hidden sm:block">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <button class="flex items-center gap-2 rounded-xl border border-slate-200/70 bg-white/70 px-2 py-1.5 text-sm font-medium text-slate-600 transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-indigo-400 dark:hover:text-indigo-200">
                                <img class="h-8 w-8 rounded-xl object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                <span class="hidden lg:block">{{ Auth::user()->name }}</span>
                            </button>
                        @else
                            <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200/70 px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:text-slate-200 dark:hover:border-indigo-400 dark:hover:text-indigo-200">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                        @endif
                    </x-slot>

                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs text-slate-400">{{ __('Manage Account') }}</div>

                        <x-dropdown-link href="{{ route('profile.show') }}">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                {{ __('API Tokens') }}
                            </x-dropdown-link>
                        @endif

                        <div class="border-t border-slate-200/70"></div>

                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf

                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button data-mobile-menu-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800 sm:hidden">
                <span class="sr-only">فهرست</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </div>

    <div id="app-mobile-menu" class="hidden border-t border-slate-200/70 bg-white/95 px-4 py-4 dark:border-slate-700/60 dark:bg-slate-950/90 sm:hidden">
        <div class="space-y-2">
            @foreach ($primaryLinks as $link)
                @php
                    $href = $link['url'] ?? (!empty($link['route']) ? route($link['route']) : '#');
                    $patterns = Arr::wrap($link['active'] ?? ($link['route'] ?? null));
                    $isActive = false;
                    foreach ($patterns as $pattern) {
                        if ($pattern && request()->routeIs($pattern)) {
                            $isActive = true;
                            break;
                        }
                    }
                @endphp
                <a href="{{ $href }}" class="flex flex-col rounded-xl border px-3 py-2 text-sm transition {{ $isActive ? 'border-indigo-500 bg-indigo-500/10 text-indigo-600 dark:border-indigo-400 dark:bg-indigo-500/20 dark:text-indigo-200' : 'border-transparent text-slate-600 hover:border-slate-200 hover:bg-slate-100 dark:text-slate-200 dark:hover:border-slate-700 dark:hover:bg-slate-800/70' }}">
                    <span class="font-medium">{{ $link['label'] }}</span>
                    @if (!empty($link['description']))
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $link['description'] }}</span>
                    @endif
                </a>
            @endforeach

            @foreach ($secondaryLinks as $link)
                @php
                    $href = $link['url'] ?? (!empty($link['route']) ? route($link['route']) : '#');
                @endphp
                <a href="{{ $href }}" class="flex flex-col rounded-xl border border-slate-200/70 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800/70">
                    <span class="font-medium">{{ $link['label'] }}</span>
                    @if (!empty($link['description']))
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $link['description'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="mt-5 space-y-4 border-t border-slate-200/70 pt-4 dark:border-slate-700/60">
            <div class="flex items-center gap-3">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <img class="h-12 w-12 rounded-xl object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                @endif
                <div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('profile.show') }}" class="flex items-center justify-between rounded-xl border border-slate-200/70 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800/70">
                    <span>{{ __('Profile') }}</span>
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5L15.75 12 8.25 19.5" /></svg>
                </a>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <a href="{{ route('api-tokens.index') }}" class="flex items-center justify-between rounded-xl border border-slate-200/70 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800/70">
                        <span>{{ __('API Tokens') }}</span>
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5L15.75 12 8.25 19.5" /></svg>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-between rounded-xl border border-rose-200/60 px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-500 hover:text-white dark:border-rose-400/70 dark:text-rose-300 dark:hover:bg-rose-500/80 dark:hover:text-white">
                        <span>{{ __('Log Out') }}</span>
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 9l3 3-3 3m3-3H3" /></svg>
                    </button>
                </form>

                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="pt-3">
                        <div class="text-xs font-semibold text-slate-400">{{ __('Manage Team') }}</div>
                        <a href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" class="mt-2 flex items-center justify-between rounded-xl border border-slate-200/70 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800/70">
                            <span>{{ __('Team Settings') }}</span>
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5L15.75 12 8.25 19.5" /></svg>
                        </a>
                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <a href="{{ route('teams.create') }}" class="mt-2 flex items-center justify-between rounded-xl border border-slate-200/70 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800/70">
                                <span>{{ __('Create New Team') }}</span>
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            </a>
                        @endcan
                        @if (Auth::user()->allTeams()->count() > 1)
                            <div class="mt-3 text-xs font-semibold text-slate-400">{{ __('Switch Teams') }}</div>
                            <div class="mt-2 space-y-1">
                                @foreach (Auth::user()->allTeams() as $team)
                                    <x-switchable-team :team="$team" />
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menu = document.getElementById('app-mobile-menu');
            const toggleButtons = document.querySelectorAll('[data-mobile-menu-toggle]');
            toggleButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    menu?.classList.toggle('hidden');
                });
            });
        });
    </script>
@endpush
