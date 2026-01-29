<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CRM هوشمند') }} - {{ $service->name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'IRANYekanX', sans-serif;
        }

        /* افکت گرادینت پس‌زمینه */
        .gradient-bg {
            background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
        }

        /* Smooth transitions for step content */
        .step-content {
            animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hide scrollbar for step indicators */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body
    class="antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col relative overflow-x-hidden">

<div class="fixed inset-0 gradient-bg pointer-events-none z-0"></div>
<div
    class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-indigo-500/20 blur-[100px] rounded-full pointer-events-none z-0 dark:bg-indigo-500/10">
</div>

<header
    class="fixed w-full top-0 z-50 transition-all duration-300 backdrop-blur-md border-b border-transparent bg-white/50 dark:bg-gray-950/50">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <span class="font-bold text-xl tracking-tight hidden sm:block">سیستم CRM</span>
            </a>
        </div>

        <nav class="flex items-center gap-4">
            @auth
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-all dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 shadow-lg shadow-gray-200/50 dark:shadow-none">
                    <span>پنل مدیریت</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors dark:text-gray-300 dark:hover:text-white">
                    ورود به سیستم
                </a>
            @endauth
        </nav>
    </div>
</header>

<main class="flex-grow flex flex-col pt-32 pb-16 px-6 relative z-10">
    <div class="max-w-5xl mx-auto w-full space-y-8">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-2 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div class="flex items-center gap-4">
                <a href="{{ route('booking.public.index') }}"
                   class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 shadow-sm hover:shadow-md hover:translate-x-1 group">
                    <svg class="w-5 h-5 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-1 tracking-tight">{{ $service->name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">رزرو آنلاین نوبت</p>
                </div>
            </div>
        </div>

        {{-- Service Info Card --}}
        <div
            class="bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-2xl border border-gray-100 dark:border-gray-800 shadow-lg shadow-gray-200/40 dark:shadow-none overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-1000 delay-100">
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center shadow-sm flex-shrink-0 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">هزینه پایه</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($service->base_price) }} <span
                                class="text-base font-normal text-gray-500 dark:text-gray-400">تومان</span>
                        </div>
                    </div>
                    @if($service->description)
                        <div class="hidden md:block max-w-md text-sm text-gray-600 dark:text-gray-400 leading-relaxed border-r-2 border-gray-100 dark:border-gray-800 pr-4 mr-4">
                            {{ $service->description }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 flex items-center gap-3 shadow-sm animate-in fade-in zoom-in duration-300">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Booking Form Card --}}
        <div
            class="bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-2xl border border-gray-100 dark:border-gray-800 shadow-lg shadow-gray-200/40 dark:shadow-none overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-200">

            <div class="p-6 sm:p-8">
                @if(!$settings->global_online_booking_enabled)
                    <div
                        class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border-2 border-rose-200 dark:border-rose-800 p-5 flex items-center gap-3 shadow-md">
                        <svg class="w-6 h-6 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-rose-700 dark:text-rose-300">رزرو آنلاین در حال حاضر غیرفعال است.
                        </p>
                    </div>
                @else
                    <form method="POST" action="{{ route('booking.public.book', $service) }}" class="space-y-8"
                          id="online-booking-form">
                        @csrf
                        <input type="hidden" name="start_at_utc" id="start_at_utc" value="{{ old('start_at_utc') }}">
                        <input type="hidden" name="end_at_utc" id="end_at_utc" value="{{ old('end_at_utc') }}">
                        <input type="hidden" name="date_local" id="date_local" value="{{ old('date_local') }}">

                        {{-- Progress Steps --}}
                        <div class="mb-10" dir="rtl">
                            <div class="flex items-center justify-between w-full relative">

                                {{-- Step 1 --}}
                                <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="1">
                                    <div class="step-circle w-12 h-12 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm group-hover:scale-105">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <span class="step-label mt-3 text-xs font-bold transition-all duration-300">ارائه‌دهنده</span>
                                </div>

                                {{-- Connector 1-2 --}}
                                <div class="flex-1 h-1 mx-2 sm:mx-4 rounded-full bg-gray-100 dark:bg-gray-800 relative overflow-hidden step-connector" data-from="1" data-to="2">
                                    <div class="absolute top-0 right-0 h-full bg-emerald-500 transition-all duration-500 w-0"></div>
                                </div>

                                {{-- Step 2 --}}
                                <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="2">
                                    <div class="step-circle w-12 h-12 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm group-hover:scale-105">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="step-label mt-3 text-xs font-bold transition-all duration-300">تاریخ</span>
                                </div>

                                {{-- Connector 2-3 --}}
                                <div class="flex-1 h-1 mx-2 sm:mx-4 rounded-full bg-gray-100 dark:bg-gray-800 relative overflow-hidden step-connector" data-from="2" data-to="3">
                                    <div class="absolute top-0 right-0 h-full bg-emerald-500 transition-all duration-500 w-0"></div>
                                </div>

                                {{-- Step 3 --}}
                                <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="3">
                                    <div class="step-circle w-12 h-12 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm group-hover:scale-105">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="step-label mt-3 text-xs font-bold transition-all duration-300">زمان</span>
                                </div>

                                {{-- Connector 3-4 --}}
                                <div class="flex-1 h-1 mx-2 sm:mx-4 rounded-full bg-gray-100 dark:bg-gray-800 relative overflow-hidden step-connector" data-from="3" data-to="4">
                                    <div class="absolute top-0 right-0 h-full bg-emerald-500 transition-all duration-500 w-0"></div>
                                </div>

                                {{-- Step 4 --}}
                                <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="4">
                                    <div class="step-circle w-12 h-12 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm group-hover:scale-105">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="step-label mt-3 text-xs font-bold transition-all duration-300">فرم</span>
                                </div>

                                {{-- Connector 4-5 --}}
                                <div class="flex-1 h-1 mx-2 sm:mx-4 rounded-full bg-gray-100 dark:bg-gray-800 relative overflow-hidden step-connector" data-from="4" data-to="5">
                                    <div class="absolute top-0 right-0 h-full bg-emerald-500 transition-all duration-500 w-0"></div>
                                </div>

                                {{-- Step 5 --}}
                                <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="5">
                                    <div class="step-circle w-12 h-12 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm group-hover:scale-105">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <span class="step-label mt-3 text-xs font-bold transition-all duration-300">اطلاعات</span>
                                </div>
                            </div>
                        </div>

                        {{-- Step 1: Provider Selection --}}
                        <div class="step-content" data-step="1">
                            <div
                                class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 mb-4">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0 text-indigo-600 dark:text-indigo-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">مرحله ۱: انتخاب
                                            ارائه‌دهنده</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">لطفاً ارائه‌دهنده مورد نظر خود
                                            را انتخاب کنید</p>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">ارائه‌دهنده
                                        <span class="text-rose-500">*</span></label>
                                    <select name="provider_user_id"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:shadow-md font-medium cursor-pointer">
                                        <option value="">-- انتخاب کنید --</option>
                                        @foreach($service->serviceProviders->where('is_active', true) as $sp)
                                            <option value="{{ $sp->provider_user_id }}" @selected(old('provider_user_id')==$sp->
                                    provider_user_id)>
                                                {{ optional($sp->provider)->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('provider_user_id')
                                    <div
                                        class="mt-4 text-sm text-rose-600 dark:text-rose-400 flex items-center gap-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl p-4">
                                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-medium">{{ $message }}</span>
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Date Selection --}}
                        <div class="step-content hidden" data-step="2">
                            <div
                                class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="flex items-center gap-4 mb-6">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0 text-indigo-600 dark:text-indigo-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">مرحله ۲: انتخاب
                                            تاریخ</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">تاریخ مورد نظر خود را از تقویم
                                            انتخاب کنید</p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div
                                        class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-xl p-3 shadow-sm border border-gray-200 dark:border-gray-700 mb-3">
                                        <button type="button" id="calendar-prev"
                                                class="flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:border-indigo-400 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-200 font-medium text-sm shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 5l7 7-7 7" />
                                            </svg>
                                            قبل
                                        </button>
                                        <div class="text-base font-bold text-gray-900 dark:text-white px-4"
                                             id="calendar-label">
                                        </div>
                                        <button type="button" id="calendar-next"
                                                class="flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:border-indigo-400 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-200 font-medium text-sm shadow-sm hover:shadow-md">
                                            بعد
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
                                        <div class="grid grid-cols-7 gap-2 mb-2" dir="rtl" id="calendar-grid"></div>
                                        <div class="text-xs text-rose-600 dark:text-rose-400 text-center font-medium"
                                             id="calendar-error">
                                        </div>
                                    </div>
                                    @error('date_local')
                                    <div
                                        class="text-sm text-rose-600 dark:text-rose-400 flex items-center gap-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg p-3">
                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Time Slot Selection --}}
                        <div class="step-content hidden" data-step="3">
                            <div
                                class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="flex items-center gap-4 mb-6">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0 text-indigo-600 dark:text-indigo-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">مرحله ۳: انتخاب
                                            زمان</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">زمان مورد نظر خود را انتخاب کنید
                                        </p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">اسلات‌های
                                            موجود <span class="text-rose-500">*</span></label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2"
                                             id="slots-container"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-4 text-center py-4 px-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
                                             id="slots-empty">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="font-medium text-sm">ابتدا تاریخ را انتخاب کنید</p>
                                        </div>
                                        @error('start_at_utc')
                                        <div
                                            class="mt-3 text-sm text-rose-600 dark:text-rose-400 flex items-center gap-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg p-3">
                                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    <div
                                        class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-xl p-4 shadow-sm mt-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="text-sm font-semibold text-indigo-900 dark:text-indigo-300">زمان
                                                انتخاب شده:</div>
                                        </div>
                                        <div class="text-base font-bold text-indigo-700 dark:text-indigo-400"
                                             id="slot-selected">
                                            اسلاتی انتخاب نشده است.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 4: Form Fields --}}
                        @php
                            $engine = app(\Modules\Booking\Services\BookingEngine::class);
                            $isOnlineEnabled = false;
                            if ($service->serviceProviders->where('is_active', true)->count() > 0) {
                            $firstProvider = $service->serviceProviders->where('is_active', true)->first();
                            $isOnlineEnabled = $engine->isOnlineBookingEnabled($service->id, $firstProvider->provider_user_id);
                            }
                        @endphp
                        @if($service->appointmentForm && is_array($service->appointmentForm->schema_json) &&
                        $isOnlineEnabled)
                            @php
                                $onlineFields = array_filter($service->appointmentForm->schema_json['fields'] ?? [],
                                function($field) {
                                return !empty($field['name']) && !empty($field['collect_from_online']);
                                });
                            @endphp
                            @if(count($onlineFields) > 0)
                                <div class="step-content hidden" data-step="4">
                                    <div
                                        class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
                                        <div class="flex items-center gap-4 mb-6">
                                            <div
                                                class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0 text-indigo-600 dark:text-indigo-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">مرحله ۴: تکمیل فرم
                                                </h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">لطفاً اطلاعات مورد نیاز را وارد
                                                    کنید</p>
                                            </div>
                                        </div>
                                        <div class="space-y-4">
                                            @foreach($onlineFields as $field)
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                        {{ $field['label'] ?? $field['name'] }}
                                                        @if(!empty($field['required'])) <span class="text-rose-500">*</span> @endif
                                                    </label>
                                                    @if(($field['type'] ?? 'text') === 'textarea')
                                                        <textarea name="form_data[{{ $field['name'] }}]" rows="4"
                                                                  class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 resize-none shadow-sm hover:shadow-md"
                                                                  placeholder="{{ $field['placeholder'] ?? '' }}">{{ old('form_data.'.$field['name']) }}</textarea>
                                                    @elseif(($field['type'] ?? 'text') === 'select')
                                                        <select name="form_data[{{ $field['name'] }}]"
                                                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:shadow-md font-medium cursor-pointer">
                                                            <option value="">انتخاب کنید...</option>
                                                            @foreach($field['options'] ?? [] as $opt)
                                                                <option value="{{ $opt }}" @selected(old('form_data.'.$field['name'])==$opt)>
                                                                    {{ $opt }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <input type="{{ $field['type'] ?? 'text' }}" name="form_data[{{ $field['name'] }}]"
                                                               value="{{ old('form_data.'.$field['name']) }}"
                                                               placeholder="{{ $field['placeholder'] ?? '' }}"
                                                               class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:shadow-md">
                                                    @endif
                                                    @error('form_data.'.$field['name'])
                                                    <div
                                                        class="mt-3 text-sm text-rose-600 dark:text-rose-400 flex items-center gap-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg p-3">
                                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                                             viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- Step 5: Client Information --}}
                        @php
                            $clientMode = \Modules\Clients\Entities\ClientSetting::getValue('auth.mode', 'password');
                            $client = auth('client')->user();
                        @endphp
                        <div class="step-content hidden" data-step="5">
                            <div
                                class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="flex items-center gap-4 mb-6">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0 text-indigo-600 dark:text-indigo-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">مرحله ۵: اطلاعات
                                            مشتری</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">لطفاً اطلاعات تماس خود را وارد
                                            کنید</p>
                                    </div>
                                </div>
                                @if($client)
                                    <div
                                        class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-5 flex items-center gap-4 shadow-sm">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0 text-emerald-600 dark:text-emerald-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-base font-bold text-emerald-900 dark:text-emerald-300 mb-1">رزرو
                                                برای: <span class="font-extrabold">{{ $client->full_name }}</span></div>
                                            <div class="text-sm text-emerald-700 dark:text-emerald-400">
                                                {{ $client->phone ?? 'بدون شماره' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label
                                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">نام
                                                    و
                                                    نام خانوادگی <span class="text-rose-500">*</span></label>
                                                <input type="text" name="full_name" value="{{ old('full_name') }}"
                                                       autocomplete="name"
                                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:shadow-md"
                                                       placeholder="نام و نام خانوادگی خود را وارد کنید">
                                                @error('full_name')
                                                <div
                                                    class="mt-2 text-sm text-rose-600 dark:text-rose-400 flex items-center gap-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg p-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span class="font-medium">{{ $message }}</span>
                                                </div>
                                                @enderror
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">شماره
                                                    تماس <span class="text-rose-500">*</span></label>
                                                <input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:shadow-md"
                                                       placeholder="09123456789">
                                                @error('phone')
                                                <div
                                                    class="mt-2 text-sm text-rose-600 dark:text-rose-400 flex items-center gap-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg p-2">
                                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span class="font-medium">{{ $message }}</span>
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                        @if($clientMode === 'password')
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">رمز
                                                    عبور
                                                    <span class="text-rose-500">*</span></label>
                                                <input type="password" name="password" autocomplete="new-password"
                                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:shadow-md"
                                                       placeholder="رمز عبور خود را وارد کنید">
                                                @error('password')
                                                <div
                                                    class="mt-2 text-sm text-rose-600 dark:text-rose-400 flex items-center gap-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg p-2">
                                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span class="font-medium">{{ $message }}</span>
                                                </div>
                                                @enderror
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div
                            class="flex items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <button type="button" id="prev-step-btn"
                                    class="hidden px-6 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-200 font-semibold shadow-sm hover:shadow-md">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                قبلی
                            </span>
                            </button>
                            <div class="flex-1"></div>
                            <button type="button" id="next-step-btn"
                                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold hover:from-indigo-700 hover:to-indigo-800 focus:ring-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <span class="flex items-center gap-2">
                                بعدی
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                            </span>
                            </button>
                            <button type="submit" id="submit-btn"
                                    class="hidden px-8 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-bold hover:from-emerald-700 hover:to-emerald-800 focus:ring-2 focus:ring-emerald-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7" />
                                </svg>
                                ثبت نوبت
                            </span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</main>

<footer class="border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 py-8 z-10">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} تمام حقوق محفوظ است.
        </p>
        <div class="flex items-center gap-6 text-sm text-gray-500">
            <a href="#" class="hover:text-indigo-600 transition-colors">قوانین و مقررات</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">حریم خصوصی</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">تماس با ما</a>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('online-booking-form');
        if (!form) return;

        const providerSelect = form.querySelector('select[name="provider_user_id"]');
        const slotsContainer = document.getElementById('slots-container');
        const slotsEmpty = document.getElementById('slots-empty');
        const startInput = document.getElementById('start_at_utc');
        const endInput = document.getElementById('end_at_utc');
        const slotSelected = document.getElementById('slot-selected');
        const dateInput = document.getElementById('date_local');
        const calendarGrid = document.getElementById('calendar-grid');
        const calendarLabel = document.getElementById('calendar-label');
        const calendarPrev = document.getElementById('calendar-prev');
        const calendarNext = document.getElementById('calendar-next');
        const calendarError = document.getElementById('calendar-error');
        const nextStepBtn = document.getElementById('next-step-btn');
        const prevStepBtn = document.getElementById('prev-step-btn');
        const submitBtn = document.getElementById('submit-btn');

        const weekDays = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
        const persianMonths = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];

        let calendarYear = {{ $currentJalali['year'] ?? 'new Date().getFullYear()' }};
        let calendarMonth = {{ $currentJalali['month'] ?? 'new Date().getMonth() + 1' }};

        let calendarDays = [];

        // Step Management
        let currentStep = 1;
        const totalSteps = 5;

        function showStep(step) {
            currentStep = step; // Update currentStep
            document.querySelectorAll('.step-content').forEach(el => {
                el.classList.add('hidden');
                el.style.opacity = '0';
            });

            // Update Stepper UI
            document.querySelectorAll('.step-item').forEach((el) => {
                const stepNum = parseInt(el.getAttribute('data-step'));
                const circle = el.querySelector('.step-circle');
                const label = el.querySelector('.step-label');

                // Reset classes
                circle.className = 'step-circle w-12 h-12 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm group-hover:scale-105';
                label.className = 'step-label mt-3 text-xs font-bold transition-all duration-300';

                if (stepNum < step) {
                    // Completed
                    circle.classList.add('border-emerald-500', 'bg-emerald-500', 'text-white');
                    label.classList.add('text-emerald-600', 'dark:text-emerald-400');
                    circle.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`;
                } else if (stepNum === step) {
                    // Active
                    circle.classList.add('border-indigo-600', 'bg-indigo-600', 'text-white', 'ring-4', 'ring-indigo-100', 'dark:ring-indigo-900/30');
                    label.classList.add('text-indigo-600', 'dark:text-indigo-400');
                    restoreIcon(el, stepNum);
                } else {
                    // Pending
                    circle.classList.add('border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-400', 'dark:text-gray-500');
                    label.classList.add('text-gray-400', 'dark:text-gray-500');
                    restoreIcon(el, stepNum);
                }
            });

            // Update Connectors
            document.querySelectorAll('.step-connector').forEach((el) => {
                const from = parseInt(el.getAttribute('data-from'));
                const bar = el.querySelector('div');
                if (from < step) {
                    bar.style.width = '100%';
                } else {
                    bar.style.width = '0%';
                }
            });

            const stepContent = document.querySelector(`.step-content[data-step="${step}"]`);
            if (stepContent) {
                stepContent.classList.remove('hidden');
                setTimeout(() => {
                    stepContent.style.opacity = '1';
                }, 10);
            }
            updateButtons();
            // Scroll to top of form
            form.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function restoreIcon(el, stepNum) {
            const circle = el.querySelector('.step-circle');
            if(!circle) return;

            let iconSvg = '';
            switch(stepNum) {
                case 1: iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />'; break;
                case 2: iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />'; break;
                case 3: iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'; break;
                case 4: iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'; break;
                case 5: iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />'; break;
            }
            circle.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">${iconSvg}</svg>`;
        }

        function updateButtons() {
            const totalSteps = document.querySelectorAll('.step-content').length;
            if (currentStep === 1) {
                prevStepBtn.classList.add('hidden');
            } else {
                prevStepBtn.classList.remove('hidden');
            }
            if (currentStep === totalSteps) {
                nextStepBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                nextStepBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        function validateStep(step) {
            if (step === 1) return providerSelect?.value;
            if (step === 2) return dateInput?.value;
            if (step === 3) return startInput?.value && endInput?.value;
            return true;
        }

        nextStepBtn?.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });

        prevStepBtn?.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        // Calendar Functions
        const clearSlots = (message = 'ابتدا تاریخ را انتخاب کنید') => {
            if (slotsContainer) slotsContainer.innerHTML = '';
            if (slotsEmpty) {
                slotsEmpty.innerHTML = message.includes('در حال') ?
                    `<div class="flex items-center justify-center gap-2"><svg class="animate-spin w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><p class="font-medium">${message}</p></div>` :
                    `<svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><p class="font-medium">${message}</p>`;
                slotsEmpty.classList.remove('hidden');
            }
            if (startInput) startInput.value = '';
            if (endInput) endInput.value = '';
            if (slotSelected) slotSelected.textContent = 'اسلاتی انتخاب نشده است.';
        };

        const renderCalendar = () => {
            if (!calendarGrid) return;
            calendarGrid.innerHTML = '';

            const monthName = persianMonths[calendarMonth - 1] || 'نامشخص';
            if (calendarLabel) calendarLabel.textContent = `${monthName} ${calendarYear}`;

            weekDays.forEach((w) => {
                const div = document.createElement('div');
                div.className =
                    'text-center text-xs font-semibold text-gray-600 dark:text-gray-400 py-2';
                div.textContent = w;
                calendarGrid.appendChild(div);
            });

            if (!calendarDays.length) return;
            const first = calendarDays[0].local_date;
            const firstDate = new Date(first + 'T00:00:00');
            const persianWeekdayIndex = (firstDate.getDay() + 1) % 7;

            for (let i = 0; i < persianWeekdayIndex; i++) {
                const empty = document.createElement('div');
                empty.className = 'h-12';
                calendarGrid.appendChild(empty);
            }

            calendarDays.forEach((day) => {
                const cell = document.createElement('button');
                cell.type = 'button';
                const isSelected = dateInput?.value === day.local_date;
                const isDisabled = day.is_closed || !day.has_available_slots;
                cell.className =
                    'w-full h-12 border rounded-xl p-2 text-center text-sm font-semibold transition-all duration-300 transform hover:scale-105';
                if (isSelected) {
                    cell.classList.add('border-indigo-600', 'bg-gradient-to-br', 'from-indigo-600',
                        'to-indigo-700', 'text-white', 'shadow-xl', 'scale-105', 'ring-4',
                        'ring-indigo-200', 'dark:ring-indigo-900/50');
                } else if (isDisabled) {
                    cell.classList.add('border-gray-200', 'dark:border-gray-700', 'bg-gray-100',
                        'dark:bg-gray-800', 'text-gray-400', 'cursor-not-allowed', 'opacity-50');
                } else {
                    cell.classList.add('border-gray-300', 'dark:border-gray-600', 'bg-white',
                        'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300',
                        'hover:bg-indigo-50', 'dark:hover:bg-indigo-900/30',
                        'hover:border-indigo-400', 'dark:hover:border-indigo-500', 'hover:shadow-lg'
                    );
                }
                cell.disabled = isDisabled;
                const num = new Date(day.local_date + 'T00:00:00').toLocaleDateString(
                    'fa-IR-u-ca-persian', {
                        day: 'numeric'
                    });
                cell.innerHTML = `<div class="font-bold">${num}</div>`;
                if (!isDisabled) {
                    cell.addEventListener('click', () => {
                        if (dateInput) dateInput.value = day.local_date;
                        fetchSlots();
                        renderCalendar();
                    });
                }
                calendarGrid.appendChild(cell);
            });
        };

        const fetchCalendar = async () => {
            if (!providerSelect?.value) return;
            if (calendarError) calendarError.textContent = '';
            if (calendarGrid) calendarGrid.style.opacity = '0.5';
            const params = new URLSearchParams({
                provider_user_id: providerSelect.value,
                year: calendarYear,
                month: calendarMonth,
            });
            try {
                const res = await fetch(`{{ route('booking.public.calendar', $service) }}?` + params
                    .toString(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();
                calendarDays = json.data || [];
                renderCalendar();
                if (calendarGrid) calendarGrid.style.opacity = '1';
            } catch (e) {
                if (calendarError) calendarError.textContent = 'خطا در دریافت تقویم.';
                if (calendarGrid) calendarGrid.style.opacity = '1';
            }
        };

        const renderSlots = (slots) => {
            if (!slotsContainer) return;
            slotsContainer.innerHTML = '';
            if (!slots.length) {
                if (slotsEmpty) {
                    slotsEmpty.innerHTML =
                        '<svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg><p class="font-medium">اسلات خالی برای این روز یافت نشد</p>';
                    slotsEmpty.classList.remove('hidden');
                }
                return;
            }
            if (slotsEmpty) slotsEmpty.classList.add('hidden');
            slots.forEach((slot) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className =
                    'px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold text-sm hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all duration-300 transform hover:scale-105 hover:shadow-md';
                const timeLabel = slot.start_time ? slot.start_time : slot.start_at_view.split(' ')[1]
                    .substring(0, 5);
                btn.textContent = timeLabel;

                btn.addEventListener('click', () => {
                    if (startInput) startInput.value = slot.start_at_utc;
                    if (endInput) endInput.value = slot.end_at_utc;

                    const sTime = slot.start_time || slot.start_at_view.split(' ')[1].substring(
                        0, 5);
                    const eTime = slot.end_time || slot.end_at_view.split(' ')[1].substring(0,
                        5);

                    if (slotSelected) slotSelected.textContent = `از ${sTime} تا ${eTime}`;
                    slotsContainer.querySelectorAll('button').forEach((b) => {
                        b.classList.remove('border-indigo-600', 'bg-gradient-to-br',
                            'from-indigo-600', 'to-indigo-700',
                            'text-white', 'shadow-xl', 'scale-105', 'ring-4',
                            'ring-indigo-200', 'dark:ring-indigo-900/50');
                        b.classList.add('border-gray-300', 'dark:border-gray-600',
                            'bg-white', 'dark:bg-gray-800', 'text-gray-700',
                            'dark:text-gray-300');
                    });
                    btn.classList.remove('border-gray-300', 'dark:border-gray-600', 'bg-white',
                        'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300');
                    btn.classList.add('border-indigo-600', 'bg-gradient-to-br',
                        'from-indigo-600', 'to-indigo-700', 'text-white',
                        'shadow-xl', 'scale-105', 'ring-4', 'ring-indigo-200',
                        'dark:ring-indigo-900/50');
                });
                slotsContainer.appendChild(btn);
            });
        };

        const fetchSlots = async () => {
            if (!providerSelect?.value || !dateInput?.value) {
                clearSlots();
                return;
            }

            clearSlots('در حال دریافت اسلات‌ها...');
            if (slotsContainer) slotsContainer.style.opacity = '0.5';
            const params = new URLSearchParams({
                provider_user_id: providerSelect.value,
                date_local: dateInput.value,
            });
            try {
                const res = await fetch(`{{ route('booking.public.slots', $service) }}?` + params
                    .toString(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();
                renderSlots(json.data || []);
                if (slotsContainer) slotsContainer.style.opacity = '1';
            } catch (e) {
                clearSlots('خطا در دریافت اسلات‌ها.');
                if (slotsContainer) slotsContainer.style.opacity = '1';
            }
        };

        providerSelect?.addEventListener('change', () => {
            if (dateInput) dateInput.value = '';
            clearSlots();
            fetchCalendar();
        });

        calendarPrev?.addEventListener('click', () => {
            calendarMonth -= 1;
            if (calendarMonth < 1) {
                calendarMonth = 12;
                calendarYear -= 1;
            }
            fetchCalendar();
        });

        calendarNext?.addEventListener('click', () => {
            calendarMonth += 1;
            if (calendarMonth > 12) {
                calendarMonth = 1;
                calendarYear += 1;
            }
            fetchCalendar();
        });

        // Initialize
        showStep(1);
        fetchCalendar();
        if (dateInput?.value) {
            fetchSlots();
        }
    });
</script>

</body>

</html>
