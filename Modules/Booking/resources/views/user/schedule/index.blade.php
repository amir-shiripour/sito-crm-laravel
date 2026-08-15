@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-gray-100 flex items-center gap-2.5">
                    <span class="w-3 h-8 bg-indigo-600 rounded-full inline-block"></span>
                    مدیریت برنامه زمانی (تقویم نوبت‌ها)
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    بررسی چیدمان روزانه نوبت‌ها، زمان‌های خالی، و برنامه کاری {{ config('booking.labels.providers') }} و سرویس‌ها
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('user.booking.appointments.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-900 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    نمای لیستی نوبت‌ها
                </a>
            </div>
        </div>

        @includeIf('partials.jalali-date-picker')

        {{-- Livewire Schedule Manager Component --}}
        <livewire:booking.user.schedule-manager />
    </div>
@endsection
