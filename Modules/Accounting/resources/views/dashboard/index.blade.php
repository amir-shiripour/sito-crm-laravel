@php use Modules\Accounting\App\Services\CurrencyService; @endphp
@extends('layouts.user')

@section('title', 'داشبورد جامع حسابداری')

@php
    $faNum = function($str) {
        if (is_null($str)) return '۰';
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
    $currencyLabel = CurrencyService::getBaseCurrency();
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header Section --}}
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 bg-white dark:bg-gray-800/80 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm backdrop-blur-xl">
            <div class="flex items-center gap-4">
            <span
                class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-700 text-white shadow-xl shadow-indigo-500/25 shrink-0 ring-4 ring-indigo-50 dark:ring-indigo-950/40">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </span>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        مرکز فرماندهی و داشبورد حسابداری
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">تحلیل لحظه‌ای خزانه‌داری، تراز مالی، اسناد
                        و سیستم یکپارچه حسابداری</p>
                </div>
            </div>

            {{-- Top Action Shortcuts (Replaced Issue Invoice with fast action hubs) --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.accounting.reports.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all border border-indigo-200/60 dark:border-indigo-500/20 shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 012-2h2a2 2 0 012-2z"/>
                    </svg>
                    گزارشات و تراز مالی
                </a>

                <a href="{{ route('admin.accounting.documents.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all border border-emerald-200/60 dark:border-emerald-500/20 shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    دفتر اسناد
                </a>

                <a href="{{ route('admin.accounting.fund-accounts.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400 text-xs font-bold hover:bg-sky-100 dark:hover:bg-sky-500/20 transition-all border border-sky-200/60 dark:border-sky-500/20 shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    خزانه‌داری
                </a>
            </div>
        </div>

        {{-- Financial KPI Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            {{-- Cash & Bank --}}
            <div
                class="relative overflow-hidden rounded-3xl border p-5 flex items-center gap-4 bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl group hover:border-sky-200 dark:hover:border-sky-500/30 transition-all duration-300">
                <div
                    class="flex items-center justify-center w-13 h-13 rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wide block mb-1 truncate">موجودی نقد و بانک</span>
                    <div class="flex items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight">{{ number_format($totalCashAndBank) }}</span>
                        <span class="text-xs font-medium text-gray-400">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>

            {{-- Receivables --}}
            <div
                class="relative overflow-hidden rounded-3xl border p-5 flex items-center gap-4 bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl group hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-all duration-300">
                <div
                    class="flex items-center justify-center w-13 h-13 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wide block mb-1 truncate">مطالبات (حساب‌های دریافتنی)</span>
                    <div class="flex items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight">{{ number_format($totalReceivables) }}</span>
                        <span class="text-xs font-medium text-gray-400">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>

            {{-- Payables --}}
            <div
                class="relative overflow-hidden rounded-3xl border p-5 flex items-center gap-4 bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl group hover:border-rose-200 dark:hover:border-rose-500/30 transition-all duration-300">
                <div
                    class="flex items-center justify-center w-13 h-13 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wide block mb-1 truncate">بدهی‌ها (حساب‌های پرداختنی)</span>
                    <div class="flex items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight">{{ number_format($totalPayables) }}</span>
                        <span class="text-xs font-medium text-gray-400">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>

            {{-- Pending Cheques --}}
            <div
                class="relative overflow-hidden rounded-3xl border p-5 flex items-center gap-4 bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl group hover:border-amber-200 dark:hover:border-amber-500/30 transition-all duration-300">
                <div
                    class="flex items-center justify-center w-13 h-13 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wide block mb-1 truncate">چک‌های در جریان وصول</span>
                    <div class="flex items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight">{{ number_format($totalPendingCheques) }}</span>
                        <span class="text-xs font-medium text-gray-400">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION: Accounting Module Capabilities Showcase Grid (7 Capabilities) --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-600 animate-pulse"></span>
                        قابلیت‌ها و بخش‌های ماژول حسابداری
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">دسترسی مستقیم و مدیریت کامل به تمام
                        ابزارهای حسابداری و خزانه‌داری</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

                {{-- 1. Treasury & Fund Accounts --}}
                <div
                    class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 backdrop-blur-xl flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 dark:bg-sky-500/10 text-sky-700 dark:text-sky-400 border border-sky-200/50 dark:border-sky-500/20">
                            {{ $faNum($fundCount) }} حساب فعال
                        </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                            خزانه‌داری و مدیریت حساب‌ها
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            مدیریت حساب‌های بانکی، صندوق‌ها، تنخواه‌گردان و جابجایی بین‌حسابی با کارت‌خوان و شماره شبای
                            رسمی.
                        </p>
                    </div>

                    <div
                        class="pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/40 flex items-center justify-between text-xs font-bold">
                        <a href="{{ route('admin.accounting.fund-accounts.index') }}"
                           class="text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">
                            مدیریت حساب‌ها
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- 2. Cheque Management System --}}
                <div
                    class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 backdrop-blur-xl flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-500/20">
                            {{ $faNum($pendingChequesCount) }} در جریان
                        </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                            سامانه مدیریت چک و اسناد
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            ثبت چک‌های دریافتی/پرداختی، اعلام وصول، پاس شدن، برگشت، ظهرنویسی و هشدار سررسید.
                        </p>
                    </div>

                    <div
                        class="pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/40 flex items-center justify-between text-xs font-bold">
                        <a href="{{ route('admin.accounting.cheques.index') }}"
                           class="text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                            لیست چک‌ها
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.accounting.cheques.create') }}"
                           class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">+ ثبت چک</a>
                    </div>
                </div>

                {{-- 3. Financial Reports & Analytics --}}
                <div
                    class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 backdrop-blur-xl flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 012-2h2a2 2 0 012-2z"/>
                                </svg>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-500/20">
                            گزارش‌گیری دقیق
                        </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            گزارشات مالی و ترازنامه
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            صورت سود و زیان، ترازنامه استاندار، تراز آزمایشی ۲ و ۴ ستونی و گردش دفاتر معین.
                        </p>
                    </div>

                    <div
                        class="pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/40 flex items-center justify-between text-xs font-bold">
                        <a href="{{ route('admin.accounting.reports.index') }}"
                           class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                            مرکز گزارشات
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- 4. Accounting Vouchers & Documents --}}
                <div
                    class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 backdrop-blur-xl flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-500/20">
                            {{ $faNum($documentsCount) }} سند ثبت شده
                        </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                            اسناد و دفاتر حسابداری
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            ثبت سند دستی و خودکار، تراز دوبل بدهکار/بستانکار، بایگانی اسناد و صدور رسید.
                        </p>
                    </div>
                    <div
                        class="pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/40 flex items-center justify-between text-xs font-bold">
                        <a href="{{ route('admin.accounting.documents.index') }}"
                           class="text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                            دفتر اسناد
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.accounting.documents.create') }}"
                           class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">+ سند جدید</a>
                    </div>
                </div>

                {{-- 5. Chart of Accounts & Categories --}}
                <div
                    class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 backdrop-blur-xl flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border border-purple-200/50 dark:border-purple-500/20">
                            {{ $faNum($categoriesCount) }} سرفصل
                        </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                            کدینگ و درخت سرفصل‌ها
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            ساختار درخت حساب‌ها (کل، معین، تفصیلی)، دارایی‌ها، بدهی‌ها، درآمدها و هزینه‌ها.
                        </p>
                    </div>
                    <div
                        class="pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/40 flex items-center justify-between text-xs font-bold">
                        <a href="{{ route('admin.accounting.categories.index') }}"
                           class="text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                            مدیریت کدینگ
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- 6. Expense Management --}}
                <div
                    class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 backdrop-blur-xl flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/50 dark:border-rose-500/20">
                            مدیریت مخارج
                        </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">
                            مدیریت هزینه‌ها
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            ثبت هزینه‌های جاری و اداری، تخصیص حساب پرداختی، ثبت فاکتور هزینه و گزارش‌های تفکیکی.
                        </p>
                    </div>
                    <div
                        class="pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/40 flex items-center justify-between text-xs font-bold">
                        <a href="{{ route('admin.accounting.expenses.index') }}"
                           class="text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-1">
                            لیست هزینه‌ها
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.accounting.expenses.create') }}"
                           class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">+ ثبت هزینه</a>
                    </div>
                </div>

                {{-- 7. System Settings --}}
                <div
                    class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 backdrop-blur-xl flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-gray-500/10 text-gray-600 dark:text-gray-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300">
                            پیکربندی
                        </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                            تنظیمات حسابداری
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            تنظیمات سال مالی، فرمت شماره‌گذاری خودکار، سرفصل‌های پیش‌فرض و پارامترهای سیستم.
                        </p>
                    </div>

                    <div
                        class="pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/40 flex items-center justify-between text-xs font-bold">
                        <a href="{{ route('admin.accounting.settings.edit') }}"
                           class="text-gray-700 dark:text-gray-300 hover:underline flex items-center gap-1">
                            تنظیمات سیستم
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        {{-- SECTION: Treasury Fund Accounts Status Overview --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 backdrop-blur-xl space-y-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                    وضعیت موجودی حساب‌های خزانه‌داری (بانک‌ها و صندوق‌ها)
                </h2>
                <a href="{{ route('admin.accounting.fund-accounts.index') }}"
                   class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline">مدیریت حساب‌ها</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($fundAccounts as $account)
                    <div
                        class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/30 flex items-center justify-between gap-3 hover:border-sky-200 dark:hover:border-sky-500/20 transition-all">
                        <div class="flex items-center gap-3">
                        <span
                            class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs shrink-0">
                            @if($account->type === 'bank')
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            @endif
                        </span>
                            <div>
                                <p class="font-bold text-sm text-gray-900 dark:text-white">{{ $account->name }}</p>
                                <p class="text-[11px] text-gray-400">
                                    {{ $account->bank_name ? $account->bank_name : ($account->type === 'bank' ? 'حساب بانکی' : 'صندوق') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-left">
                        <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums">
                            {{ number_format(CurrencyService::convertForDisplay($account->calculated_balance ?? 0)) }}
                        </span>
                            <span class="text-[11px] text-gray-400 block">{{ $currencyLabel }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-6 text-sm text-gray-400">
                        هیچ حساب خزانه‌داری یا صندوقی تعریف نشده است.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- SECTION: Dual Column Lists (Upcoming Cheques & Recent Documents) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- 1. Upcoming Cheques (7 days) --}}
            <div
                class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        چک‌های با سررسید نزدیک (۷ روز آینده)
                    </h2>
                    <a href="{{ route('admin.accounting.cheques.index') }}"
                       class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">مشاهده همه</a>
                </div>

                <div class="space-y-3">
                    @forelse($upcomingCheques as $cheque)
                        <a href="{{ route('admin.accounting.cheques.show', $cheque) }}"
                           class="flex justify-between items-center p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 hover:bg-amber-50/50 dark:hover:bg-amber-500/10 border border-transparent hover:border-amber-200 dark:hover:border-amber-500/20 transition-all duration-200 group">
                            <div class="space-y-1">
                                <p class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                    چک شماره: <span class="font-mono">{{ $cheque->cheque_number }}</span>
                                </p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold {{ $cheque->type === 'incoming' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' }}">
                                    {{ $cheque->type === 'incoming' ? 'دریافتی' : 'پرداختی' }}
                                </span>
                                    <span>• {{ $cheque->payee_name }}</span>
                                </div>
                            </div>
                            <div class="text-left space-y-1">
                                <p class="font-bold text-sm text-gray-900 dark:text-white tabular-nums">{{ number_format(CurrencyService::convertForDisplay($cheque->amount)) }}
                                    <span class="text-xs font-normal text-gray-400">{{ $currencyLabel }}</span></p>
                                <p class="text-xs font-medium text-gray-400">{{ jdate($cheque->due_date)->format('l, d F') }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-10">
                            <p class="text-sm font-medium text-gray-400">هیچ چک با سررسید نزدیک یافت نشد.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- 2. Recent Accounting Documents --}}
            <div
                class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        آخرین اسناد حسابداری ثبت‌شده
                    </h2>
                    <a href="{{ route('admin.accounting.documents.index') }}"
                       class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">مشاهده دفتر
                        اسناد</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentDocuments as $doc)
                        <a href="{{ route('admin.accounting.documents.show', $doc) }}"
                           class="flex justify-between items-center p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 hover:bg-emerald-50/50 dark:hover:bg-emerald-500/10 border border-transparent hover:border-emerald-200 dark:hover:border-emerald-500/20 transition-all duration-200 group">
                            <div class="space-y-1">
                                <p class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                    سند شماره: <span class="font-mono">#{{ $doc->document_number }}</span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                    {{ $doc->description ?: 'بدون توضیحات' }}
                                </p>
                            </div>
                            <div class="text-left space-y-1">
                                <p class="font-bold text-sm text-emerald-600 dark:text-emerald-400 tabular-nums">
                                    {{ number_format(CurrencyService::convertForDisplay($doc->transactions->sum('debit'))) }}
                                    <span
                                        class="text-xs font-normal text-gray-400">{{ $currencyLabel }}</span></p>
                                <p class="text-xs font-medium text-gray-400">{{ jdate($doc->document_date)->format('Y/m/d') }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-10">
                            <p class="text-sm font-medium text-gray-400">هیچ سندی هنوز ثبت نشده است.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
