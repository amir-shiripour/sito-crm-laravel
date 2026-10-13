@extends('layouts.user')

@php
    $title = 'پروفایل میزبانی';
    $cardClass = "bg-white dark:bg-gray-800/90 backdrop-blur-md rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden transition-all duration-300";
    $badgeClass = "inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold font-sans";

    // آماده‌سازی داده‌های اقامتگاه‌ها برای فیلتر و جستجوی آنی در Alpine.js
    $propertiesJson = $properties->map(function ($p) {
        $filterCategory = 'other';
        if ($p->approval_status === 'approved' && $p->publication_status === 'published') {
            $filterCategory = 'approved';
        } elseif ($p->approval_status === 'pending_review') {
            $filterCategory = 'pending_review';
        } elseif ($p->approval_status === 'rejected') {
            $filterCategory = 'rejected';
        }

        return [
            'id' => $p->id,
            'title' => $p->title ?? '',
            'code' => (string) ($p->code ?? $p->id),
            'address' => $p->address ?? '',
            'category_name' => $p->category->name ?? 'اقامتگاه روزانه',
            'filter_category' => $filterCategory,
        ];
    })->values();
@endphp

@section('content')
<div class="space-y-6 pb-12 font-sans"
     x-data="hostDashboardManager({
         properties: {{ Js::from($propertiesJson) }}
     })">

    {{-- ۱. بنر پرمیوم سربرگ و اطلاعات میزبان (هماهنگ با صفحه ویرایش پروفایل میزبانی) --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 text-white p-6 sm:p-8 shadow-xl shadow-indigo-950/20">
        {{-- افکت‌های نوری ملایم --}}
        <div class="absolute -top-24 -left-24 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                {{-- آواتار میزبان --}}
                <div class="relative flex-shrink-0">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 text-white flex items-center justify-center font-black text-2xl overflow-hidden shadow-2xl">
                        @if($host->avatar)
                            <img src="{{ asset('storage/' . $host->avatar) }}" alt="{{ $host->display_name }}" class="w-full h-full object-cover">
                        @else
                            <span class="font-sans font-black">{{ mb_substr($host->display_name, 0, 1) }}</span>
                        @endif
                    </div>

                    @if($host->status === 'active')
                        <span class="absolute -bottom-1 -left-1 w-6 h-6 rounded-full bg-emerald-500 border-2 border-indigo-900 flex items-center justify-center text-white" title="میزبان فعال و تأیید شده">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                        </span>
                    @elseif($host->status === 'pending')
                        <span class="absolute -bottom-1 -left-1 w-6 h-6 rounded-full bg-amber-500 border-2 border-indigo-900 flex items-center justify-center text-white" title="در انتظار تأیید مدیریت">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                    @else
                        <span class="absolute -bottom-1 -left-1 w-6 h-6 rounded-full bg-red-500 border-2 border-indigo-900 flex items-center justify-center text-white" title="حساب غیرفعال">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </span>
                    @endif
                </div>

                {{-- مشخصات و بج‌های وضعیت --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-white font-sans">{{ $host->display_name }}</h1>

                        @if($host->status === 'active')
                            <span class="px-3 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold font-sans">
                                میزبان فعال
                            </span>
                        @elseif($host->status === 'pending')
                            <span class="px-3 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/30 text-xs font-bold font-sans">
                                در انتظار تأیید مدیریت
                            </span>
                        @else
                            <span class="px-3 py-0.5 rounded-full bg-red-500/20 text-red-300 border border-red-400/30 text-xs font-bold font-sans">
                                حساب تعلیق / غیرفعال
                            </span>
                        @endif

                        @if($host->kyc_status === 'approved')
                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-200 border border-indigo-400/20 text-xs font-bold font-sans flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                هویت احراز شده
                            </span>
                        @elseif($host->kyc_status === 'pending')
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500/30 text-amber-200 border border-amber-400/20 text-xs font-bold font-sans">
                                مدارک در حال بررسی
                            </span>
                        @elseif($host->kyc_status === 'rejected')
                            <span class="px-2.5 py-0.5 rounded-full bg-red-500/30 text-red-200 border border-red-400/20 text-xs font-bold font-sans">
                                مدارک نیازمند اصلاح
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-x-4 gap-y-1.5 text-xs text-indigo-200/90 font-sans flex-wrap">
                        <span>شماره تماس: <strong class="dir-ltr inline-block text-white">{{ $host->phone }}</strong></span>
                        <span class="text-indigo-400/60">•</span>
                        <span>تاریخ عضویت: <strong class="text-white">{{ \Morilog\Jalali\Jalalian::fromCarbon($host->created_at)->format('Y/m/d') }}</strong></span>
                        <span class="text-indigo-400/60">•</span>
                        <span class="text-amber-300">نرخ کارمزد: <strong>{{ $host->effective_commission_rate }}٪</strong></span>
                        <span class="text-indigo-400/60">•</span>
                        <span>
                            حساب تسویه:
                            @if($host->shaba_number)
                                <strong class="text-emerald-300">
                                    {{ $host->bank_name ?: 'شبای بانکی' }} (<span class="dir-ltr inline-block">IR{{ substr(preg_replace('/^IR/i', '', $host->shaba_number), 0, 4) }}...{{ substr($host->shaba_number, -4) }}</span>)
                                </strong>
                            @else
                                <a href="{{ route('user.properties.hosts.profile') }}" class="text-amber-300 font-bold underline hover:text-amber-200">
                                    شماره شبا ثبت نشده (تکمیل کنید)
                                </a>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- دکمه‌های عملیاتی سریع --}}
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('user.properties.hosts.bookings.index') }}"
                   class="px-4 py-2.5 rounded-2xl bg-white/20 hover:bg-white/25 backdrop-blur-md border border-white/25 text-white text-xs font-black transition flex items-center gap-2 font-sans shadow-md shadow-black/10">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    مدیریت رزروها و تقویم
                </a>

                <a href="{{ route('user.properties.hosts.profile') }}"
                   class="px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/15 backdrop-blur-md border border-white/15 text-white text-xs font-bold transition flex items-center gap-2 font-sans">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    ویرایش اطلاعات و مدارک
                </a>

                @if($canCreateProperty)
                    <a href="{{ route('user.properties.create') }}?type=daily_rental"
                       class="px-5 py-2.5 rounded-2xl bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-xs transition flex items-center gap-2 shadow-lg shadow-amber-400/20 font-sans">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        ثبت اقامتگاه جدید
                    </a>
                @else
                    <button type="button"
                            @click="showRestrictionModal = true"
                            class="px-5 py-2.5 rounded-2xl bg-white/15 hover:bg-white/20 border border-white/20 text-white font-bold text-xs transition flex items-center gap-2 font-sans">
                        <svg class="w-4 h-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        ثبت اقامتگاه جدید
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ۲. ویجت گام‌به‌گام وضعیت تکمیل پروفایل (فقط زمانی که ۱۰۰٪ تکمیل و تأیید نشده باشد نمایش داده می‌شود) --}}
    @if($showOnboardingStepper)
        <div class="{{ $cardClass }}">
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">وضعیت تکمیل پروفایل و مجوز میزبانی</h2>
                            <span class="px-2.5 py-0.5 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-700/50">
                                {{ $onboardingProgress }}٪ تکمیل شده
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            @if($host->kyc_status === 'rejected' || $host->status === 'suspended')
                                مدارک یا حساب میزبانی شما نیازمند بررسی و اصلاح است؛ لطفاً توضیحات زیر را مطالعه فرمایید.
                            @elseif(!$isKycFieldsComplete || !$isBankComplete)
                                برای تسریع در تأیید حساب و فعال‌سازی تسویه‌حساب منظم، گام‌های ناقص زیر را تکمیل نمایید.
                            @else
                                مدارک شما ثبت شده و در صف بررسی و تأیید نهایی توسط کارشناسان قرار دارد.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 self-end sm:self-auto">
                    <div class="hidden md:block w-36 bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ $onboardingProgress }}%"></div>
                    </div>
                    <a href="{{ route('user.properties.hosts.profile') }}"
                       class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-md shadow-indigo-500/20 whitespace-nowrap">
                        تکمیل مدارک و شبا
                    </a>
                </div>
            </div>

            <div class="p-5 sm:p-6 bg-gray-50/50 dark:bg-gray-900/30 space-y-5">
                {{-- هشدار رد شدن مدارک یا تعلیق حساب --}}
                @if($host->kyc_status === 'rejected' || $host->status === 'suspended')
                    <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <div>
                                <h4 class="text-xs font-bold text-red-900 dark:text-red-200">
                                    {{ $host->status === 'suspended' ? 'حساب میزبانی شما تعلیق شده است' : 'مدارک احراز هویت شما نیازمند اصلاح است' }}
                                </h4>
                                @if($host->kyc_rejection_reason)
                                    <p class="text-xs text-red-700 dark:text-red-300 mt-1 leading-relaxed">
                                        <strong>توضیحات کارشناس:</strong> {{ $host->kyc_rejection_reason }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('user.properties.hosts.profile') }}" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition flex-shrink-0 text-center">
                            اصلاح و ارسال مجدد مدارک
                        </a>
                    </div>
                @endif

                {{-- ۴ گام آمادگی میزبانی --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- گام ۱ --}}
                    <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border {{ $isBasicComplete ? 'border-emerald-200 dark:border-emerald-800/50' : 'border-amber-200 dark:border-amber-800/50' }} flex flex-col justify-between gap-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-400">گام اول</span>
                            @if($isBasicComplete)
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    تکمیل شده
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 text-[11px] font-bold">نیازمند تکمیل</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">اطلاعات پایه میزبانی</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">نام نمایشی و شماره تماس هماهنگی</p>
                        </div>
                    </div>

                    {{-- گام ۲ --}}
                    <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border {{ $host->kyc_status === 'approved' ? 'border-emerald-200 dark:border-emerald-800/50' : ($host->kyc_status === 'rejected' ? 'border-red-200 dark:border-red-800/50' : 'border-indigo-200 dark:border-indigo-800/50') }} flex flex-col justify-between gap-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-400">گام دوم</span>
                            @if($host->kyc_status === 'approved')
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    احراز شده
                                </span>
                            @elseif($host->kyc_status === 'rejected')
                                <span class="px-2.5 py-0.5 rounded-full bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 text-[11px] font-bold">رد شده</span>
                            @elseif($isKycFieldsComplete)
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 text-[11px] font-bold">در انتظار بررسی</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold">
                                    {{ $completedKycFieldsCount }} از {{ $totalKycFieldsCount }} مدرک
                                </span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">مدارک احراز هویت</h3>
                            @if(count($missingKycFieldLabels) > 0)
                                <p class="text-[11px] text-red-600 dark:text-red-400 mt-1 line-clamp-1" title="{{ implode('، ', $missingKycFieldLabels) }}">
                                    ناقص: {{ implode('، ', array_slice($missingKycFieldLabels, 0, 2)) }}{{ count($missingKycFieldLabels) > 2 ? ' و...' : '' }}
                                </p>
                            @else
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $totalKycFieldsCount > 0 ? 'مدارک هویتی بارگذاری شده است' : 'بررسی هویت میزبان' }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- گام ۳ --}}
                    <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border {{ $isBankComplete ? 'border-emerald-200 dark:border-emerald-800/50' : 'border-amber-200 dark:border-amber-800/50' }} flex flex-col justify-between gap-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-400">گام سوم</span>
                            @if($isBankComplete)
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    ثبت شده
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 text-[11px] font-bold">ثبت نشده</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">اطلاعات بانکی تسویه</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                {{ $isBankComplete ? ($host->bank_name ?: 'شماره شبا ثبت شده است') : 'ثبت شماره شبا جهت واریز درآمد' }}
                            </p>
                        </div>
                    </div>

                    {{-- گام ۴ --}}
                    <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border {{ $host->status === 'active' ? 'border-emerald-200 dark:border-emerald-800/50' : 'border-gray-200 dark:border-gray-700' }} flex flex-col justify-between gap-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-400">گام چهارم</span>
                            @if($host->status === 'active')
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    فعال
                                </span>
                            @elseif($host->status === 'pending')
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 text-[11px] font-bold">در انتظار تأیید</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 text-[11px] font-bold">غیرفعال</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">مجوز انتشار اقامتگاه</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                @if($host->status === 'active')
                                    مجاز به ثبت و انتشار اقامتگاه
                                @elseif($pendingCanCreate)
                                    مجاز به ثبت پیش‌نویس تا زمان تأیید
                                @else
                                    فعال‌سازی پس از تأیید مدیریت
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ۳. کارت‌های آمار تعاملی (Bento Grid KPI Cards) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        {{-- کل اقامتگاه‌ها --}}
        <button type="button"
                @click="activeFilter = 'all'"
                :class="activeFilter === 'all' ? 'ring-2 ring-indigo-600 dark:ring-indigo-400 border-indigo-500' : 'hover:border-indigo-300 dark:hover:border-indigo-700'"
                class="{{ $cardClass }} text-right p-5 bg-gradient-to-br from-white to-gray-50/50 dark:from-gray-800 dark:to-gray-800/60 relative">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-300">کل اقامتگاه‌ها</span>
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-gray-900 dark:text-white font-sans">{{ $stats['total'] }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 font-sans">واحد ثبت‌شده</span>
            </div>
        </button>

        {{-- اقامتگاه‌های منتشر شده --}}
        <button type="button"
                @click="activeFilter = 'approved'"
                :class="activeFilter === 'approved' ? 'ring-2 ring-emerald-600 dark:ring-emerald-400 border-emerald-500' : 'hover:border-emerald-300 dark:hover:border-emerald-700'"
                class="{{ $cardClass }} text-right p-5 bg-gradient-to-br from-white to-emerald-50/20 dark:from-gray-800 dark:to-gray-800/60 border-emerald-100 dark:border-emerald-900/30 relative">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">منتشر و آماده رزرو</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-sans">{{ $stats['approved'] }}</span>
                <span class="text-xs text-emerald-600/80 dark:text-emerald-400/80 font-sans">فعال در سایت</span>
            </div>
        </button>

        {{-- در انتظار بررسی --}}
        <button type="button"
                @click="activeFilter = 'pending_review'"
                :class="activeFilter === 'pending_review' ? 'ring-2 ring-amber-500 dark:ring-amber-400 border-amber-500' : 'hover:border-amber-300 dark:hover:border-amber-700'"
                class="{{ $cardClass }} text-right p-5 bg-gradient-to-br from-white to-amber-50/20 dark:from-gray-800 dark:to-gray-800/60 border-amber-100 dark:border-amber-900/30 relative">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-400">در انتظار تأیید</span>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-amber-600 dark:text-amber-400 font-sans">{{ $stats['pending'] }}</span>
                <span class="text-xs text-amber-600/80 dark:text-amber-400/80 font-sans">در صف بازبینی</span>
            </div>
        </button>

        {{-- رد شده / نیاز به اصلاح --}}
        <button type="button"
                @click="activeFilter = 'rejected'"
                :class="activeFilter === 'rejected' ? 'ring-2 ring-red-600 dark:ring-red-400 border-red-500' : 'hover:border-red-300 dark:hover:border-red-700'"
                class="{{ $cardClass }} text-right p-5 bg-gradient-to-br from-white to-red-50/20 dark:from-gray-800 dark:to-gray-800/60 border-red-100 dark:border-red-900/30 relative">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-red-700 dark:text-red-400">نیازمند اصلاح</span>
                <div class="w-10 h-10 rounded-2xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-red-600 dark:text-red-400 font-sans">{{ $stats['rejected'] }}</span>
                <span class="text-xs text-red-600/80 dark:text-red-400/80 font-sans">رد شده</span>
            </div>
        </button>
    </div>

    {{-- ۴. بخش اصلی و یکپارچه: مدیریت اقامتگاه‌ها و ویلاها --}}
    <div class="{{ $cardClass }}">
        {{-- هدر و نوار ابزار یکپارچه کارت اقامتگاه‌ها --}}
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/60 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div>
                    <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        اقامتگاه‌ها و ویلاهای شما
                        <span class="px-2.5 py-0.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold"
                              x-text="filteredCount + ' مورد'"></span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مدیریت تقویم، نرخ‌های اجاره روزانه و وضعیت انتشار اقامتگاه‌ها</p>
                </div>

                {{-- تب‌های فیلتر سریع --}}
                @if($properties->isNotEmpty())
                    <div class="flex items-center gap-1.5 flex-wrap sm:mr-2">
                        <button type="button"
                                @click="activeFilter = 'all'"
                                :class="activeFilter === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200/70 dark:hover:bg-gray-700'"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors">
                            همه ({{ $stats['total'] }})
                        </button>
                        <button type="button"
                                @click="activeFilter = 'approved'"
                                :class="activeFilter === 'approved' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200/70 dark:hover:bg-gray-700'"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors">
                            منتشر شده ({{ $stats['approved'] }})
                        </button>
                        <button type="button"
                                @click="activeFilter = 'pending_review'"
                                :class="activeFilter === 'pending_review' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200/70 dark:hover:bg-gray-700'"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors">
                            در انتظار تأیید ({{ $stats['pending'] }})
                        </button>
                        @if($stats['rejected'] > 0)
                            <button type="button"
                                    @click="activeFilter = 'rejected'"
                                    :class="activeFilter === 'rejected' ? 'bg-red-600 text-white' : 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-300 hover:bg-red-100'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors">
                                نیازمند اصلاح ({{ $stats['rejected'] }})
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- جستجو + سوئیچ نمایش (کارت‌ها / جدول) --}}
            @if($properties->isNotEmpty())
                <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                    <div class="relative flex-1 sm:w-64">
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="جستجوی نام، کد ملک یا آدرس..."
                               class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 pr-9 pl-7 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition font-sans">
                        <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <button type="button"
                                x-show="searchQuery.length > 0"
                                @click="searchQuery = ''"
                                class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="bg-gray-100 dark:bg-gray-900/60 p-1 rounded-2xl flex items-center gap-1 border border-gray-200/60 dark:border-gray-700/60">
                        <button type="button"
                                @click="viewMode = 'cards'"
                                :class="viewMode === 'cards' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                                class="px-2.5 py-1.5 rounded-xl transition-colors text-xs font-bold flex items-center gap-1.5"
                                title="نمایش کارتی">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                            <span class="hidden sm:inline">کارت‌ها</span>
                        </button>
                        <button type="button"
                                @click="viewMode = 'table'"
                                :class="viewMode === 'table' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                                class="px-2.5 py-1.5 rounded-xl transition-colors text-xs font-bold flex items-center gap-1.5"
                                title="نمایش جدولی">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            <span class="hidden sm:inline">جدول</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        @if($properties->isEmpty())
            {{-- حالت خالی (بدون اقامتگاه) --}}
            <div class="py-16 px-4 text-center space-y-4">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <div class="space-y-1.5 max-w-md mx-auto">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">هنوز اقامتگاهی ثبت نکرده‌اید!</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                        اولین ویلا، سوئیت یا اقامتگاه بوم‌گردی خود را همراه با تصاویر و نرخ‌های تقویم ثبت کنید تا پس از تأیید کارشناسان در سایت منتشر شود.
                    </p>
                </div>
                <div class="pt-1">
                    @if($canCreateProperty)
                        <a href="{{ route('user.properties.create') }}?type=daily_rental"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors">
                            <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            ثبت اولین اقامتگاه
                        </a>
                    @else
                        <button type="button"
                                @click="showRestrictionModal = true"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold border border-gray-200 dark:border-gray-600 transition-colors">
                            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            ثبت اولین اقامتگاه (نیازمند تأیید حساب)
                        </button>
                    @endif
                </div>
            </div>
        @else
            {{-- پیام عدم یافتن نتیجه در فیلتر یا جستجو --}}
            <div x-show="filteredCount === 0" x-cloak class="py-12 px-4 text-center space-y-3">
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">هیچ اقامتگاهی با فیلتر یا عبارت جستجوشده مطابقت ندارد.</p>
                <button type="button"
                        @click="activeFilter = 'all'; searchQuery = ''"
                        class="px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-100 transition-colors">
                    نمایش همه اقامتگاه‌ها
                </button>
            </div>

            {{-- الف) حالت نمایش کارتی ۴ ستونه متناسب (Visual Cards - 4 Columns) --}}
            <div x-show="viewMode === 'cards' && filteredCount > 0" class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($properties as $property)
                    @php
                        $cfg = $property->rentalConfig;
                        $hasPendingRevision = $property->pendingRevision !== null;
                        $latestRev = $property->latestRevision;
                    @endphp
                    <div x-show="matchesProperty({{ $property->id }})"
                         class="rounded-2xl border border-gray-200/80 dark:border-gray-700/80 hover:border-indigo-300 dark:hover:border-indigo-500/50 bg-white dark:bg-gray-800/80 overflow-hidden transition-colors duration-200 flex flex-col justify-between">
                        <div>
                            {{-- تصویر کاور و بج‌های وضعیت (بدون زوم در هاور) --}}
                            <div class="relative h-44 bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                @if($property->cover_image)
                                    <img src="{{ asset('storage/' . $property->cover_image) }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                        <svg class="w-9 h-9 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <span class="text-[11px] mt-1">بدون تصویر کاور</span>
                                    </div>
                                @endif

                                {{-- وضعیت تأیید بالای تصویر --}}
                                <div class="absolute top-2.5 right-2.5 flex flex-wrap items-center gap-1">
                                    @if($property->approval_status === 'approved' && $property->publication_status === 'published')
                                        <span class="px-2.5 py-1 rounded-lg bg-emerald-600/90 backdrop-blur-md text-white text-[11px] font-bold font-sans">
                                            منتشر شده
                                        </span>
                                    @elseif($property->approval_status === 'pending_review')
                                        <span class="px-2.5 py-1 rounded-lg bg-amber-500/90 backdrop-blur-md text-white text-[11px] font-bold font-sans">
                                            در انتظار بررسی
                                        </span>
                                    @elseif($property->approval_status === 'rejected')
                                        <span class="px-2.5 py-1 rounded-lg bg-red-600/90 backdrop-blur-md text-white text-[11px] font-bold font-sans">
                                            نیازمند اصلاح
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg bg-gray-800/85 backdrop-blur-md text-white text-[11px] font-bold font-sans">
                                            پیش‌نویس
                                        </span>
                                    @endif

                                    @if($hasPendingRevision)
                                        <span class="px-2 py-1 rounded-lg bg-indigo-600/90 backdrop-blur-md text-white text-[10px] font-bold font-sans" title="تغییرات اخیر شما در صف بررسی مدیریت است">
                                            ویرایش در صف
                                        </span>
                                    @endif
                                </div>

                                {{-- کد ملک و دسته‌بندی پایین تصویر --}}
                                <div class="absolute bottom-2.5 inset-x-2.5 flex items-center justify-between gap-2">
                                    <span class="px-2 py-0.5 rounded-lg bg-black/65 backdrop-blur-md text-white text-[11px] font-bold font-sans">
                                        کد: {{ $property->code ?? $property->id }}
                                    </span>
                                    @if($property->category)
                                        <span class="px-2 py-0.5 rounded-lg bg-black/65 backdrop-blur-md text-indigo-200 text-[11px] font-bold font-sans truncate max-w-[120px]">
                                            {{ $property->category->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- محتوای کارت --}}
                            <div class="p-4 space-y-3">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate" title="{{ $property->title }}">
                                        {{ $property->title }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate font-sans">
                                        {{ $property->address ?: 'آدرس ثبت نشده است' }}
                                    </p>
                                </div>

                                {{-- نمایش دلیل رد اقامتگاه یا آخرین ویرایش --}}
                                @if($property->approval_status === 'rejected' && $property->rejection_reason)
                                    <div class="p-2.5 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 text-[11px] text-red-800 dark:text-red-300 space-y-0.5">
                                        <div class="font-bold text-red-700 dark:text-red-300">علت رد اقامتگاه:</div>
                                        <p class="leading-relaxed line-clamp-2" title="{{ $property->rejection_reason }}">{{ $property->rejection_reason }}</p>
                                    </div>
                                @elseif($latestRev && $latestRev->status === 'rejected' && $latestRev->rejection_reason)
                                    <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 text-[11px] text-amber-800 dark:text-amber-300 space-y-0.5">
                                        <div class="font-bold">علت رد آخرین ویرایش:</div>
                                        <p class="leading-relaxed line-clamp-2" title="{{ $latestRev->rejection_reason }}">{{ $latestRev->rejection_reason }}</p>
                                    </div>
                                @endif

                                {{-- ظرفیت و حداقل اقامت --}}
                                <div class="flex items-center justify-between gap-2 text-xs text-gray-600 dark:text-gray-300 font-sans py-2 border-y border-gray-100 dark:border-gray-700/60">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        <span><strong>{{ $cfg->base_guests ?? 2 }} تا {{ $cfg->max_guests ?? 4 }}</strong> نفر</span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <span>حداقل <strong>{{ $cfg->min_stay_nights ?? 1 }}</strong> شب</span>
                                    </span>
                                </div>

                                {{-- نرخ‌های شبانه اقامتگاه --}}
                                <div class="space-y-1.5 pt-0.5 font-sans">
                                    @if($cfg && $cfg->price_per_night > 0)
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">نرخ وسط هفته:</span>
                                            <div>
                                                <strong class="text-sm font-black text-gray-900 dark:text-white">{{ number_format($cfg->price_per_night) }}</strong>
                                                <span class="text-[11px] text-gray-400">تومان</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">نرخ آخر هفته:</span>
                                            <div>
                                                <strong class="text-sm font-black text-indigo-600 dark:text-indigo-400">
                                                    {{ number_format($cfg->price_weekend > 0 ? $cfg->price_weekend : $cfg->price_per_night) }}
                                                </strong>
                                                <span class="text-[11px] text-gray-400">تومان</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between py-1 text-xs">
                                            <span class="text-gray-400">نرخ هر شب:</span>
                                            <a href="{{ route('user.properties.rental.config', $property) }}" class="text-amber-600 dark:text-amber-400 font-bold hover:underline">
                                                تنظیم نشده (ثبت نرخ)
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- عملیات کارت --}}
                        <div class="p-3 bg-gray-50/70 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/60 grid grid-cols-3 gap-1.5">
                            <a href="{{ route('user.properties.rental.calendar', $property) }}"
                               class="py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 dark:bg-teal-950/40 dark:hover:bg-teal-900/50 dark:text-teal-300 text-xs font-bold text-center transition-colors flex items-center justify-center gap-1 font-sans"
                               title="مدیریت تقویم روزانه و قیمت‌ها">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                تقویم
                            </a>

                            <a href="{{ route('user.properties.rental.config', $property) }}"
                               class="py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/50 dark:text-indigo-300 text-xs font-bold text-center transition-colors flex items-center justify-center gap-1 font-sans"
                               title="تنظیمات نرخ‌ها، ظرفیت و قوانین اقامتگاه">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                                مشخصات
                            </a>

                            <a href="{{ route('user.properties.edit', $property) }}"
                               class="py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 text-xs font-bold text-center transition-colors flex items-center justify-center gap-1 font-sans"
                               title="ویرایش مشخصات و تصاویر اقامتگاه">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                ویرایش
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ب) حالت نمایش جدولی فشرده (Table View) --}}
            <div x-show="viewMode === 'table' && filteredCount > 0" x-cloak class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40 text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4">عنوان اقامتگاه</th>
                            <th class="px-6 py-4">ظرفیت و حداقل اقامت</th>
                            <th class="px-6 py-4">نرخ وسط هفته / آخر هفته</th>
                            <th class="px-6 py-4">وضعیت انتشار</th>
                            <th class="px-6 py-4 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach($properties as $property)
                            @php
                                $cfg = $property->rentalConfig;
                                $hasPendingRevision = $property->pendingRevision !== null;
                            @endphp
                            <tr x-show="matchesProperty({{ $property->id }})" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0 border border-gray-200 dark:border-gray-600 shadow-sm">
                                            @if($property->cover_image)
                                                <img src="{{ asset('storage/' . $property->cover_image) }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <svg class="w-6 h-6 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $property->title }}</span>
                                            <span class="text-xs text-gray-400 font-sans mt-0.5">
                                                کد: {{ $property->code ?? $property->id }}
                                                @if($property->category)
                                                    • {{ $property->category->name }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-xs font-sans text-gray-600 dark:text-gray-300">
                                    <div>ظرفیت: <strong>{{ $cfg->base_guests ?? 2 }} تا {{ $cfg->max_guests ?? 4 }}</strong> نفر</div>
                                    <div class="text-gray-400 mt-0.5">حداقل اقامت: <strong>{{ $cfg->min_stay_nights ?? 1 }}</strong> شب</div>
                                </td>

                                <td class="px-6 py-4 font-sans">
                                    @if($cfg && $cfg->price_per_night > 0)
                                        <div class="text-xs font-bold text-gray-900 dark:text-white">
                                            {{ number_format($cfg->price_per_night) }} <span class="text-[11px] font-normal text-gray-400">تومان (وسط هفته)</span>
                                        </div>
                                        <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">
                                            {{ number_format($cfg->price_weekend > 0 ? $cfg->price_weekend : $cfg->price_per_night) }} <span class="text-[11px] font-normal text-gray-400">تومان (آخر هفته)</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-bold">تنظیم نشده</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if($property->approval_status === 'approved' && $property->publication_status === 'published')
                                            <span class="{{ $badgeClass }} bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">تأیید و منتشر شده</span>
                                        @elseif($property->approval_status === 'pending_review')
                                            <span class="{{ $badgeClass }} bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">در انتظار بررسی</span>
                                        @elseif($property->approval_status === 'rejected')
                                            <span class="{{ $badgeClass }} bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border border-red-200 dark:border-red-800/40" title="{{ $property->rejection_reason }}">نیازمند اصلاح</span>
                                        @else
                                            <span class="{{ $badgeClass }} bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">پیش‌نویس</span>
                                        @endif

                                        @if($hasPendingRevision)
                                            <span class="{{ $badgeClass }} bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/40">ویرایش در صف بررسی</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('user.properties.rental.calendar', $property) }}" class="p-2.5 rounded-xl bg-teal-50 text-teal-600 hover:bg-teal-100 dark:bg-teal-950/40 dark:text-teal-300 dark:hover:bg-teal-900/50 transition" title="مدیریت تقویم و قیمت‌ها">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </a>

                                        <a href="{{ route('user.properties.rental.config', $property) }}" class="p-2.5 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-300 dark:hover:bg-indigo-900/50 transition" title="نرخ‌ها و قوانین اقامتگاه">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                                        </a>

                                        <a href="{{ route('user.properties.edit', $property) }}" class="p-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition" title="ویرایش اطلاعات کلی">
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

    {{-- ۵. بخش پویای «نکات کلیدی میزبانی موفق» (قابل مدیریت از تنظیمات املاک) --}}
    @if($hostTipsEnabled && !empty($hostTips))
        <div class="{{ $cardClass }} p-6 space-y-5">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                        {{ $hostTipsTitle ?: 'نکات کلیدی میزبانی موفق' }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">راهنمای افزایش رزرو، رضایت مسافران و تسویه‌حساب سریع‌تر</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($hostTips as $idx => $tip)
                    @if(!empty($tip['title']) || !empty($tip['text']))
                        @php
                            $tipText = str_replace(
                                ['{commission}', ':commission'],
                                $host->effective_commission_rate . '٪',
                                $tip['text'] ?? ''
                            );
                        @endphp
                        <div class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            @if(!empty($tip['title']))
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-xs font-black flex items-center justify-center flex-shrink-0">
                                        {{ $idx + 1 }}
                                    </span>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">
                                        {{ $tip['title'] }}
                                    </h4>
                                </div>
                            @endif
                            @if(!empty($tipText))
                                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                    {{ $tipText }}
                                </p>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- مودال راهنمای محدودیت ثبت اقامتگاه --}}
    <div x-show="showRestrictionModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.away="showRestrictionModal = false"
             class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-6 border border-gray-200 dark:border-gray-700 shadow-2xl space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">ثبت اقامتگاه نیازمند تأیید حساب است</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">حساب میزبانی شما در حال بررسی توسط مدیریت است</p>
                </div>
            </div>

            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                بر اساس تنظیمات پلتفرم، امکان ثبت اقامتگاه جدید پس از بررسی مدارک هویتی و فعال‌سازی حساب میزبانی شما باز خواهد شد. در صورتی که مدارک احراز هویت یا شماره شبای خود را هنوز کامل نکرده‌اید، از بخش پروفایل اقدام نمایید.
            </p>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button"
                        @click="showRestrictionModal = false"
                        class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold transition">
                    متوجه شدم
                </button>
                <a href="{{ route('user.properties.hosts.profile') }}"
                   class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                    بررسی و تکمیل مدارک
                </a>
            </div>
        </div>
    </div>

</div>

<script>
    function hostDashboardManager(config) {
        return {
            properties: config.properties || [],
            activeFilter: 'all',
            searchQuery: '',
            viewMode: 'cards',
            showRestrictionModal: false,

            get filteredCount() {
                return this.properties.filter(p => this.matchesItem(p)).length;
            },

            matchesProperty(id) {
                const item = this.properties.find(p => Number(p.id) === Number(id));
                if (!item) return true;
                return this.matchesItem(item);
            },

            matchesItem(item) {
                if (this.activeFilter !== 'all' && item.filter_category !== this.activeFilter) {
                    return false;
                }
                const q = this.searchQuery.trim().toLowerCase();
                if (!q) return true;

                return (item.title && item.title.toLowerCase().includes(q)) ||
                       (item.code && item.code.toLowerCase().includes(q)) ||
                       (item.address && item.address.toLowerCase().includes(q)) ||
                       (item.category_name && item.category_name.toLowerCase().includes(q));
            }
        };
    }
</script>
@endsection
