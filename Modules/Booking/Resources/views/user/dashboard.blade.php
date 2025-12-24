@extends('layouts.user')

@section('content')
    <div class="space-y-5">
        {{-- Header --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">داشبورد نوبت‌دهی</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">۳۰ روز اخیر</p>
            </div>
            <div class="flex flex-wrap gap-2 justify-end">
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200"
                   href="{{ route('user.booking.services.index') }}">
                    سرویس‌ها
                </a>
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 transition-all duration-200"
                   href="{{ route('user.booking.appointments.index') }}">
                    نوبت‌ها
                </a>
                @if(auth()->user()?->can('booking.forms.view'))
                    <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-medium hover:bg-sky-700 hover:shadow-lg hover:shadow-sky-500/30 transition-all duration-200"
                       href="{{ route('user.booking.forms.index') }}">
                        فرم‌ها
                    </a>
                @endif
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-800 text-white text-sm font-medium hover:bg-gray-900 hover:shadow-lg hover:shadow-gray-700/30 transition-all duration-200"
                   href="{{ route('user.booking.settings.edit') }}">
                    تنظیمات
                </a>
            </div>
        </div>

        {{-- KPI cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div
                class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm space-y-1">
                <div class="text-sm text-gray-500 dark:text-gray-400">کل نوبت‌ها</div>
                <div class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ $total }}</div>
            </div>
            <div
                class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm space-y-1">
                <div class="text-sm text-gray-500 dark:text-gray-400">تایید شده</div>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $confirmed }}</div>
            </div>
            <div
                class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm space-y-1">
                <div class="text-sm text-gray-500 dark:text-gray-400">لغو شده</div>
                <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $canceled }}</div>
            </div>
            <div
                class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm space-y-1">
                <div class="text-sm text-gray-500 dark:text-gray-400">عدم حضور</div>
                <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">{{ $noShow }}</div>
            </div>
            <div
                class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm space-y-1">
                <div class="text-sm text-gray-500 dark:text-gray-400">درآمد پرداخت‌شده</div>
                <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ number_format($revenue) }}
                </div>
            </div>
        </div>
    </div>
@endsection
