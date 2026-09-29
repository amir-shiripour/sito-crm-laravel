@extends('layouts.user')

@php
    $title = 'مدیریت میزبانان اقامتگاه';
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $badgeClass = "inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold font-sans";
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </span>
                مدیریت میزبانان اقامتگاه
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-10">بررسی درخواست‌های میزبانی، احراز هویت و مدیریت فعال‌سازی</p>
        </div>
    </div>

    {{-- جدول میزبان‌ها --}}
    <div class="{{ $cardClass }}">
        @if($hosts->isEmpty())
            <div class="py-16 text-center">
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">هیچ میزبان ثبت‌شده‌ای یافت نشد.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40 text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3.5">میزبان / کاربر</th>
                            <th class="px-6 py-3.5">اطلاعات تماس</th>
                            <th class="px-6 py-3.5">تعداد اقامتگاه</th>
                            <th class="px-6 py-3.5">وضعیت</th>
                            <th class="px-6 py-3.5">احراز هویت</th>
                            <th class="px-6 py-3.5 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($hosts as $host)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                            @if($host->avatar)
                                                <img src="{{ asset('storage/' . $host->avatar) }}" class="w-full h-full object-cover rounded-xl">
                                            @else
                                                {{ mb_substr($host->display_name, 0, 1) }}
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $host->display_name }}</span>
                                            <span class="text-xs text-gray-400">کاربر: {{ optional($host->user)->name ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-xs font-sans text-gray-700 dark:text-gray-300">
                                    <div>{{ $host->phone }}</div>
                                    @if($host->shaba_number)
                                        <div class="text-[11px] text-gray-400 mt-0.5">IR{{ $host->shaba_number }}</div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 font-sans text-gray-900 dark:text-white font-bold">
                                    {{ $host->properties_count }} اقامتگاه
                                </td>

                                <td class="px-6 py-4">
                                    @if($host->status === 'active')
                                        <span class="{{ $badgeClass }} bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">فعال</span>
                                    @elseif($host->status === 'pending')
                                        <span class="{{ $badgeClass }} bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">در انتظار تایید</span>
                                    @else
                                        <span class="{{ $badgeClass }} bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800/40">معلق</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if($host->kyc_status === 'approved')
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">احراز شده</span>
                                    @elseif($host->kyc_status === 'pending')
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-bold">مدارک ارسال شده</span>
                                    @else
                                        <span class="text-xs text-gray-400">ثبت نشده</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($host->status !== 'active')
                                            <form action="{{ route('user.properties.hosts.admin.approve', $host) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 text-xs font-bold transition">
                                                    تأیید میزبان
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('user.properties.hosts.admin.reject', $host) }}" method="POST" onsubmit="return confirm('آیا از تعلیق این میزبان اطمینان دارید؟');">
                                                @csrf
                                                <input type="hidden" name="reason" value="تعلیق توسط مدیریت">
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-300 text-xs font-bold transition">
                                                    تعلیق حساب
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $hosts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
