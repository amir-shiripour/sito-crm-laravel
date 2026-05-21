@extends('layouts.user')

@php
    $title = __('پروفایل کاربری');
    $user = auth()->user();
    $userRolesDisplay = $user->roles->pluck('display_name')->toArray();
@endphp

@section('content')
<div class="mx-auto max-w-full space-y-6" x-data="{ activeTab: 'profile' }">
    {{-- هدر پروفایل --}}
    <div class="relative bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700 p-6 sm:p-8">
        <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                {{-- آواتار --}}
                @if(Laravel\Jetstream\Jetstream::managesProfilePhotos() && $user->profile_photo_path)
                    <img class="h-16 w-16 shrink-0 rounded-full object-cover ring-4 ring-white dark:ring-gray-800" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
                @else
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300 text-2xl font-bold ring-4 ring-white dark:ring-gray-800">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                @endif

                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $user->name }}
                    </h1>

                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs sm:text-sm">
                        {{-- ایمیل --}}
                        <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-mono dir-ltr">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>{{ $user->email }}</span>
                        </div>

                        {{-- نقش --}}
                        @if(count($userRolesDisplay))
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-medium bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-current/40"></span>
                                {{ implode('، ', $userRolesDisplay) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- دکمه‌ها / تب‌ها (بخش تب هدر) --}}
            <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto mt-2 xl:mt-0">
                <nav class="flex w-full sm:w-auto space-x-3 space-x-reverse overflow-x-auto pb-2 sm:pb-0 hide-scrollbar" aria-label="Tabs">
                    {{-- تب اطلاعات کاربری --}}
                    <button @click="activeTab = 'profile'"
                            :class="activeTab === 'profile'
                                ? 'bg-white border-gray-200 text-indigo-600 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-indigo-300'
                                : 'bg-transparent border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/60'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-medium transition-all whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        اطلاعات کاربری
                    </button>

                    {{-- تب امنیت --}}
                    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                        <button @click="activeTab = 'security'"
                                :class="activeTab === 'security'
                                    ? 'bg-white border-gray-200 text-indigo-600 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-indigo-300'
                                    : 'bg-transparent border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/60'"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-medium transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            امنیت
                        </button>
                    @endif

                    {{-- تب نشست‌ها --}}
                    <button @click="activeTab = 'sessions'"
                            :class="activeTab === 'sessions'
                                ? 'bg-white border-gray-200 text-indigo-600 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-indigo-300'
                                : 'bg-transparent border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/60'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-medium transition-all whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        نشست‌ها
                    </button>
                </nav>
            </div>
        </div>
    </div>

    {{-- محتوای تب‌ها --}}
    <div class="p-6 sm:p-8 bg-white dark:bg-gray-800">

        {{-- تب: اطلاعات کاربری --}}
        <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
            @endif
        </div>

        {{-- تب: امنیت (تغییر رمز عبور و احراز هویت دو مرحله‌ای) --}}
        <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="space-y-10">
                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                    <div>
                        @livewire('profile.update-password-form')
                    </div>
                @endif

                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    <x-section-border />
                    <div>
                        @livewire('profile.two-factor-authentication-form')
                    </div>
                @endif
            </div>
        </div>

        {{-- تب: نشست‌ها و حذف حساب --}}
        <div x-show="activeTab === 'sessions'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="space-y-10">
                <div>
                    @livewire('profile.logout-other-browser-sessions-form')
                </div>

                @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                    <x-section-border />
                    <div>
                        @livewire('profile.delete-user-form')
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>

<style>
    /* مخفی کردن اسکرول‌بار در حالت موبایل برای تب‌ها */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection
