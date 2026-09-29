@extends('layouts.user')

@php
    $title = 'میزکار میزبانی اقامتگاه';
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $badgeClass = "inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold font-sans";
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

    {{-- بنر و هدر میزبان --}}
    <div class="{{ $cardClass }} p-6 bg-gradient-to-r from-indigo-50/70 via-white to-purple-50/50 dark:from-gray-800 dark:via-gray-800 dark:to-indigo-950/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl overflow-hidden border border-indigo-200 dark:border-indigo-800/50 shadow-sm flex-shrink-0">
                    @if($host->avatar)
                        <img src="{{ asset('storage/' . $host->avatar) }}" class="w-full h-full object-cover">
                    @else
                        {{ mb_substr($host->display_name, 0, 1) }}
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $host->display_name }}</h1>
                        @if($host->status === 'active')
                            <span class="{{ $badgeClass }} bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                                میزبان فعال
                            </span>
                        @elseif($host->status === 'pending')
                            <span class="{{ $badgeClass }} bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">
                                در انتظار تایید مدیریت
                            </span>
                        @else
                            <span class="{{ $badgeClass }} bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800/50">
                                معلق شده
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">شماره تماس: {{ $host->phone }} • تاریخ عضویت: {{ \Morilog\Jalali\Jalalian::fromCarbon($host->created_at)->format('Y/m/d') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('user.properties.hosts.profile') }}" class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    اطلاعات مالی و کاربری
                </a>

                <a href="{{ route('user.properties.create') }}?type=daily_rental" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/25 transition transform active:scale-95 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    ثبت اقامتگاه جدید
                </a>
            </div>
        </div>
    </div>

    {{-- کارت‌های آمار --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="{{ $cardClass }} p-4">
            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1">کل اقامتگاه‌ها</span>
            <span class="text-2xl font-bold text-gray-900 dark:text-white font-sans">{{ $stats['total'] }}</span>
        </div>

        <div class="{{ $cardClass }} p-4">
            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1">منتشر و فعال</span>
            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-sans">{{ $stats['approved'] }}</span>
        </div>

        <div class="{{ $cardClass }} p-4">
            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1">در انتظار بررسی</span>
            <span class="text-2xl font-bold text-amber-600 dark:text-amber-400 font-sans">{{ $stats['pending'] }}</span>
        </div>

        <div class="{{ $cardClass }} p-4">
            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1">رد شده / نیاز به اصلاح</span>
            <span class="text-2xl font-bold text-red-600 dark:text-red-400 font-sans">{{ $stats['rejected'] }}</span>
        </div>
    </div>

    {{-- لیست اقامتگاه‌ها --}}
    <div class="{{ $cardClass }}">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                اقامتگاه‌های من
            </h2>
            <span class="text-xs text-gray-500 dark:text-gray-400 font-sans">تعداد: {{ $properties->count() }} اقامتگاه</span>
        </div>

        @if($properties->isEmpty())
            <div class="py-16 text-center">
                <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700/50 rounded-2xl flex items-center justify-center text-gray-400 dark:text-gray-500 mb-3">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">هنوز اقامتگاهی ثبت نکرده‌اید</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">برای شروع و دریافت رزرو، اولین اقامتگاه یا ویلای خود را اضافه کنید.</p>
                <a href="{{ route('user.properties.create') }}?type=daily_rental" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-md hover:bg-indigo-700 transition">
                    افزودن اولین اقامتگاه
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40 text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3.5">عنوان اقامتگاه</th>
                            <th class="px-6 py-3.5">نوع ملک</th>
                            <th class="px-6 py-3.5">قیمت شبانه</th>
                            <th class="px-6 py-3.5">وضعیت بررسی</th>
                            <th class="px-6 py-3.5 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($properties as $property)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0 border border-gray-200 dark:border-gray-600">
                                            @if($property->cover_image)
                                                <img src="{{ asset('storage/' . $property->cover_image) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $property->title }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 font-sans mt-0.5">کد: {{ $property->code }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">
                                        {{ match($property->property_type) { 'villa' => 'ویلا و باغچه', 'apartment' => 'آپارتمان و خانه', 'office' => 'سوئیت تجاری', default => $property->property_type } }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    @if($property->rentalConfig && $property->rentalConfig->price_per_night > 0)
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400 font-sans">
                                            {{ number_format($property->rentalConfig->price_per_night) }} تومان / شب
                                        </span>
                                    @else
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-sans">قیمت تنظیم نشده</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if($property->approval_status === 'approved')
                                        <span class="{{ $badgeClass }} bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">تأیید شده</span>
                                    @elseif($property->approval_status === 'pending_review')
                                        <span class="{{ $badgeClass }} bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">در انتظار بررسی</span>
                                    @else
                                        <span class="{{ $badgeClass }} bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800/40" title="{{ $property->rejection_reason }}">رد شده</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- دکمه تقویم --}}
                                        <a href="{{ route('user.properties.rental.calendar', $property) }}" class="p-2 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 dark:bg-teal-900/20 dark:text-teal-300 dark:hover:bg-teal-900/40 transition" title="مدیریت تقویم و قیمت‌ها">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </a>

                                        {{-- دکمه تنظیمات اقامتگاه --}}
                                        <a href="{{ route('user.properties.rental.config', $property) }}" class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/40 transition" title="تنظیم ظرفیت و امکانات">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                                        </a>

                                        {{-- دکمه ویرایش عمومی --}}
                                        <a href="{{ route('user.properties.edit', $property) }}" class="p-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition" title="ویرایش اطلاعات کلی">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
