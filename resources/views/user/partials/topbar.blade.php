{{-- user/partials/topbar.blade.php --}}
<header class="sticky top-0 z-30 bg-white/80 dark:bg-gray-800/80 backdrop-blur border-b border-gray-200/70 dark:border-gray-700/60">
    <div class="h-16 px-4 sm:px-6 lg:px-8 flex items-center gap-3 justify-between">
        <div class="flex items-center gap-2">
            <button class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700" @click="toggleSidebar()" aria-label="menu">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>
            <div class="hidden sm:block text-sm font-semibold text-gray-900 dark:text-gray-100">
                {{ $title ?? 'حساب کاربری' }}
            </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            <button @click="toggleDark()" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700" title="حالت تیره">
                <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM12 18a6 6 0 100-12 6 6 0 000 12z"/></svg>
                <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M21.752 15.002A9.718 9.718 0 0112.75 22C7.089 22 2.5 17.411 2.5 11.75c0-3.62 1.83-6.8 4.598-8.697a.75.75 0 01.998 1.12A8.218 8.218 0 004 11.75 8.25 8.25 0 0012.25 20a8.218 8.218 0 006.578-3.095.75.75 0 011.12.997l-.196.1z"/></svg>
            </button>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700" aria-haspopup="true" aria-expanded="false">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <img class="h-9 w-9 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    @else
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Str::words(Auth::user()->name, 1, '') }}</span>
                    @endif
                </button>
                <div x-cloak x-show="open" x-transition @click.outside="open = false" class="absolute end-0 mt-2 w-56 rounded-xl bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700"></div>
                    <div class="py-2">
                        <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">پروفایل</a>
                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <a href="{{ route('api-tokens.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">توکن‌های API</a>
                        @endif
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700"></div>
                    <form method="POST" action="{{ route('logout') }}" class="py-2">
                        @csrf
                        <button type="submit" class="w-full text-start px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">خروج</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
