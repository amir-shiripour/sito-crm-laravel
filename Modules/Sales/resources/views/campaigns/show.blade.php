@extends('layouts.user')
@php($title = 'جزئیات کمپین')
@section('content')
    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.sales.campaigns.index') }}" class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">کمپین {{ $campaign->name ?? '' }}</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مشاهده جزئیات و آمار کمپین</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('user.sales.campaigns.edit', $campaign) }}" class="px-5 py-2.5 bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-95">ویرایش</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" dir="rtl">
            <div class="md:col-span-2 space-y-6">
                <!-- Description & Goal -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700/50 pb-2">شرح کمپین و اهداف</h3>
                    <div class="space-y-4">
                        <div>
                            <span class="text-xs font-bold text-gray-400 block mb-1">هدف کمپین:</span>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                @switch($campaign->goal)
                                    @case('lead_generation') تولید سرنخ (Lead Generation) @break
                                    @case('conversion') افزایش نرخ تبدیل (Conversion) @break
                                    @case('retention') حفظ مشتریان فعلی (Retention) @break
                                    @case('upsell') بیش‌فروشی (Upsell) @break
                                    @case('awareness') آگاهی‌بخشی برند (Awareness) @break
                                    @default {{ $campaign->goal ?: 'تعریف نشده' }}
                                @endswitch
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-400 block mb-1">مخاطبان هدف:</span>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                {{ is_array($campaign->target_audience) ? implode(', ', $campaign->target_audience) : ($campaign->target_audience ?? 'تعریف نشده') }}
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-400 block mb-1">توضیحات تکمیلی:</span>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                {!! nl2br(e($campaign->description ?? 'هیچ توضیحی ثبت نشده است.')) !!}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Financial Performance -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700/50 pb-2">عملکرد مالی کمپین</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <span class="text-xs font-semibold text-gray-400 block">بودجه مصوب:</span>
                            <span class="text-base font-black text-indigo-600 dark:text-indigo-400 block mt-1">
                                {{ $campaign->budget ? number_format((float)$campaign->budget) . ' ریال' : 'صفر یا ثبت نشده' }}
                            </span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <span class="text-xs font-semibold text-gray-400 block">هزینه واقعی انجام شده:</span>
                            <span class="text-base font-black text-rose-600 dark:text-rose-400 block mt-1">
                                {{ $campaign->actual_cost ? number_format((float)$campaign->actual_cost) . ' ریال' : 'صفر یا ثبت نشده' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <!-- Metadata Sidebar -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700/50 pb-2">مشخصات کمپین</h3>
                    <ul class="space-y-4">
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">نوع کانال:</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                @switch($campaign->type)
                                    @case('sms') <span class="bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400 px-2 py-0.5 rounded-md">پیامکی</span> @break
                                    @case('email') <span class="bg-purple-50 text-purple-600 dark:bg-purple-950/20 dark:text-purple-400 px-2 py-0.5 rounded-md">ایمیلی</span> @break
                                    @case('call') <span class="bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20 dark:text-indigo-400 px-2 py-0.5 rounded-md">تلفنی</span> @break
                                    @case('social') <span class="bg-pink-50 text-pink-600 dark:bg-pink-950/20 dark:text-pink-400 px-2 py-0.5 rounded-md">شبکه‌های اجتماعی</span> @break
                                    @default <span class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400 px-2 py-0.5 rounded-md">{{ $campaign->type }}</span>
                                @endswitch
                            </span>
                        </li>
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">وضعیت فعلی:</span>
                            <span>
                                @switch($campaign->status)
                                    @case('active') <span class="bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400 px-2.5 py-0.5 rounded-md font-bold text-xs">فعال</span> @break
                                    @case('draft') <span class="bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 px-2.5 py-0.5 rounded-md font-bold text-xs">پیش‌نویس</span> @break
                                    @case('paused') <span class="bg-amber-50 text-amber-600 dark:bg-amber-950/20 dark:text-amber-400 px-2.5 py-0.5 rounded-md font-bold text-xs">متوقف‌شده</span> @break
                                    @case('completed') <span class="bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400 px-2.5 py-0.5 rounded-md font-bold text-xs">تکمیل‌نشده</span> @break
                                    @case('cancelled') <span class="bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400 px-2.5 py-0.5 rounded-md font-bold text-xs">لغوشده</span> @break
                                    @default <span class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400 px-2.5 py-0.5 rounded-md font-bold text-xs">تعریف نشده</span>
                                @endswitch
                            </span>
                        </li>
                        <li class="flex flex-col gap-1 border-t border-gray-100 dark:border-gray-700/50 pt-3">
                            <span class="text-xs text-gray-400">بازه زمان اجرا:</span>
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 mt-1 dir-ltr text-right">
                                {{ $campaign->start_date ? \Morilog\Jalali\Jalalian::fromCarbon($campaign->start_date)->format('Y/m/d') : 'نامشخص' }}
                                الی
                                {{ $campaign->end_date ? \Morilog\Jalali\Jalalian::fromCarbon($campaign->end_date)->format('Y/m/d') : 'نامشخص' }}
                            </span>
                        </li>
                        <li class="flex justify-between items-center text-sm border-t border-gray-100 dark:border-gray-700/50 pt-3">
                            <span class="text-gray-400">کارشناس مسئول:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">
                                {{ $campaign->assignee ? $campaign->assignee->name : 'تخصیص نیافته' }}
                            </span>
                        </li>
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">ایجاد کننده:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">
                                {{ $campaign->creator ? $campaign->creator->name : 'سیستم' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Campaign Contacts Manager --}}
        <div class="mt-6">
            @livewire('sales::campaign-contact-manager', ['campaign' => $campaign])
        </div>
    </div>
@endsection
