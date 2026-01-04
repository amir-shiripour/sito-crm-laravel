{{-- admin/partials/topbar.blade.php --}}
<header
    class="sticky top-0 z-30 bg-white/80 dark:bg-gray-800/80 backdrop-blur border-b border-gray-200/70 dark:border-gray-700/60">
    <div class="h-16 px-4 sm:px-6 lg:px-8 flex items-center gap-3 justify-between">
        <div class="flex items-center gap-2">
            <button
                class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700"
                @click="sidebarOpen=true" aria-label="menu">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <div class="hidden sm:block text-sm font-semibold text-gray-900 dark:text-gray-100">
                {{ $title ?? 'پنل مدیریت' }}
            </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="hidden md:flex items-center">
                <form action="#" class="relative">
                    <input type="text" placeholder="جستجو..."
                           class="peer ps-10 pe-3 py-2 w-64 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                    <svg
                        class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 peer-focus:text-indigo-500"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z"/>
                    </svg>
                </form>
            </div>
            <button @click="dark=!dark"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700"
                    title="Dark Mode">
                <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                     class="w-5 h-5">
                    <path
                        d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM12 18a6 6 0 100-12 6 6 0 000 12z"/>
                </svg>
                <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                     class="w-5 h-5">
                    <path
                        d="M21.752 15.002A9.718 9.718 0 0112.75 22C7.089 22 2.5 17.411 2.5 11.75c0-3.62 1.83-6.8 4.598-8.697a.75.75 0 01.998 1.12A8.218 8.218 0 004 11.75 8.25 8.25 0 0012.25 20a8.218 8.218 0 006.578-3.095.75.75 0 011.12.997l-.196.1z"/>
                </svg>
            </button>
            <button
                class="relative inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700"
                title="Notifications">
                <span class="absolute -top-1 -end-1 w-2 h-2 rounded-full bg-red-500"></span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path
                        d="M14.857 17.082a23.848 23.848 0 01-5.714 0A1.916 1.916 0 017.25 15.2V11a4.75 4.75 0 119.5 0v4.2c0 .947-.673 1.76-1.893 1.882zM9.75 19.5a2.25 2.25 0 004.5 0h-4.5z"/>
                </svg>
            </button>
            <div class="shrink-0">
                @auth
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        {{--<div class="h-10 w-10 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700">
                            <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover" />
                        </div>--}}
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <button
                                        class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                        <img class="h-9 w-9 rounded-full object-cover"
                                             src="{{ Auth::user()->profile_photo_url }}"
                                             alt="{{ Auth::user()->name }}"/>
                                    </button>
                                @else
                                    <span class="inline-flex rounded-md">
                                    <button type="button"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                        {{ Auth::user()->name }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                        </svg>
                                    </button>
                                </span>
                                @endif
                            </x-slot>

                            <x-slot name="content">
                                <!-- Account Management -->
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('Manage Account') }}
                                </div>

                                <x-dropdown-link href="{{ route('profile.show') }}">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                    <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                        {{ __('API Tokens') }}
                                    </x-dropdown-link>
                                @endif

                                <div class="border-t border-gray-200"></div>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}" x-data>
                                    @csrf

                                    <x-dropdown-link href="{{ route('logout') }}"
                                                     @click.prevent="$root.submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    @else
                        <div
                            class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</header>
