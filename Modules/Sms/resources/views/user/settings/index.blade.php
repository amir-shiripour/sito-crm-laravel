@extends('layouts.user')

@section('title', 'تنظیمات پیامک')

@php
    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- هدر صفحه --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </span>
                    تنظیمات پیامک
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    پیکربندی درگاه پیامک برای ارسال اعلان‌ها، کدهای تایید (OTP) و پیامک‌های سیستمی.
                </p>
            </div>

            <div class="flex items-center gap-3">
                 <a href="{{ route('user.sms.logs.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    مشاهده لاگ‌ها
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3 animate-in fade-in slide-in-from-top-2 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-800/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-800/30 text-red-700 dark:text-red-400 text-sm font-medium flex items-start gap-3 animate-in fade-in slide-in-from-top-2 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-800/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <ul class="list-disc list-inside space-y-1 mt-1.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('user.sms.settings.update') }}" class="space-y-8 pb-24">
            @csrf
            @method('PUT')

            {{-- کارت ۱: تنظیمات درگاه --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات درگاه (Gateway)</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">انتخاب سرویس‌دهنده و اطلاعات احراز هویت</p>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">درایور (سرویس‌دهنده)</label>
                            <div class="relative">
                                <select name="driver" class="{{ $selectClass }}">
                                    <option value="null" @selected(optional($setting)->driver === 'null')>Null (فقط لاگ - تستی)</option>
                                    <option value="limosms" @selected(optional($setting)->driver === 'limosms')>Limo SMS (لیمو اس‌ام‌اس)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">شماره ارسال کننده (Sender)</label>
                            <input type="text" name="sender"
                                   value="{{ old('sender', optional($setting)->sender) }}"
                                   class="{{ $inputClass }} dir-ltr text-left"
                                   placeholder="مثلاً: 3000xxxx">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">API Key</label>
                            <input type="text" name="api_key"
                                   value="{{ old('api_key', data_get($setting, 'config.api_key')) }}"
                                   class="{{ $inputClass }} dir-ltr text-left font-mono text-xs"
                                   placeholder="کلید دسترسی به وب‌سرویس">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Base URL (اختیاری)</label>
                            <input type="text" name="base_url"
                                   value="{{ old('base_url', data_get($setting, 'config.base_url')) }}"
                                   class="{{ $inputClass }} dir-ltr text-left font-mono text-xs"
                                   placeholder="https://api.limosms.com/v1/">
                        </div>
                    </div>
                </div>
            </div>

            {{-- کارت ۲: تنظیمات OTP --}}
            @if($clientsModuleInstalled)
                <div class="{{ $cardClass }}">
                    <div class="{{ $headerClass }}">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات ورود دو مرحله‌ای (OTP)</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی ارسال کد تایید برای ورود مشتریان</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-xl p-4 mb-6 flex gap-3 items-start">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div class="text-xs text-amber-800 dark:text-amber-200 leading-relaxed">
                                <span class="font-bold block mb-1">راهنمای پترن (الگو):</span>
                                برای ارسال سریع (Fast/OTP) در سرویس‌هایی مثل لیمو اس‌ام‌اس، باید یک پترن تعریف کنید.
                                <br>
                                مثال: «کد ورود شما: <code>{0}</code>»
                                <br>
                                هنگام ارسال، سیستم به صورت خودکار کد تایید را جایگزین <code>{0}</code> خواهد کرد.
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="{{ $labelClass }}">شناسه پترن (Pattern ID / OtpId)</label>
                                <input type="text" name="client_otp_pattern"
                                       value="{{ old('client_otp_pattern', $clientOtpPattern) }}"
                                       class="{{ $inputClass }} dir-ltr text-left font-mono"
                                       placeholder="مثلاً: 38291">
                                <p class="text-[11px] text-gray-400 mt-2">
                                    شناسه عددی یا کد پترن تعریف شده در پنل پیامک.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- دکمه ذخیره --}}
            <div class="sticky bottom-4 z-40 flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">

                @if($balance)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">اعتبار پنل:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 dir-ltr">{{ number_format((float)($balance['balance'] ?? 0)) }}</span>
                        <span class="text-xs text-gray-400">{{ $balance['currency'] ?? 'ریال' }}</span>
                    </div>
                @else
                    <div></div>
                @endif

                <button type="submit"
                        class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    ذخیره تنظیمات
                </button>
            </div>
        </form>
    </div>
@endsection
