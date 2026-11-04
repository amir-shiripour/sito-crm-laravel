@extends('layouts.user')

@php
    $title = 'مشخصات و قیمت‌گذاری اقامتگاه روزانه';
    $cardClass = "bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs overflow-hidden";
    $labelClass = "block text-xs font-bold text-slate-700 dark:text-slate-200 mb-2 font-sans";
    $inputClass = "w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 placeholder-slate-400 shadow-2xs transition-all focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 focus:outline-hidden dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-indigo-400 dark:focus:ring-indigo-400/20 font-sans";
    $checkboxClass = "w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-600 cursor-pointer";
    $currencyLabel = $currency === 'toman' ? 'تومان' : 'ریال';
    $isAdmin = auth()->user()->hasRole(['super-admin', 'admin']) || auth()->user()->can('properties.manage');
    $isWizard = $isWizard ?? (request('wizard') == '1' || request('from') === 'create');
@endphp

@section('content')
<div class="w-full px-3 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6 font-sans" x-data="rentalConfigForm()">

    {{-- هدر صفحه و ناوبری --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white flex items-center gap-2 sm:gap-2.5 font-sans">
                <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-center shadow-2xs shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                </span>
                <span class="truncate">{{ $isWizard ? 'ایجاد اقامتگاه (مرحله ۲): تنظیمات و نرخ‌ها' : 'تنظیمات اقامتگاه: ' . $property->title }}</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-sans">تعیین نرخ‌های شبانه، ظرفیت مهمانان، زمان‌بندی و شرایط اقامت</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            @if(!$isWizard)
                <a href="{{ route('user.properties.rental.calendar', $property) }}" class="flex-1 sm:flex-initial px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 text-xs font-bold border border-teal-200/80 dark:border-teal-800/50 hover:bg-teal-100 dark:hover:bg-teal-900/60 transition flex items-center justify-center gap-1.5 font-sans whitespace-nowrap shadow-2xs">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span>تقویم و نرخ روزها</span>
                </a>
                <a href="{{ route('user.properties.edit', $property) }}" class="flex-1 sm:flex-initial px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-300 text-xs font-bold border border-indigo-200/80 dark:border-indigo-800/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition flex items-center justify-center gap-1.5 font-sans whitespace-nowrap shadow-2xs">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    <span>امکانات و مشخصات</span>
                </a>
            @endif
            <a href="{{ route('user.properties.index') }}" class="px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-700/60 transition font-sans shadow-2xs whitespace-nowrap">
                بازگشت
            </a>
        </div>
    </div>

    {{-- نوار وضعیت مراحل ایجاد اقامتگاه (فقط در حالت ثبت اولیه / ویزارد) --}}
    @if($isWizard)
        <div class="rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/50 p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-xs sm:text-sm shadow-xs font-sans shrink-0">
                    ۲
                </span>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100">
                        مرحله ۲ از ۴: ظرفیت مهمانان، نرخ‌های شبانه و قوانین ورود
                    </h4>
                    <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5 font-sans">
                        مشخصات اولیه ثبت شد. پس از تکمیل این صفحه، وارد مرحله اطلاعات تکمیلی و مشخصات اتاق‌ها می‌شوید.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-bold self-start sm:self-auto font-sans bg-white dark:bg-slate-800 px-3 py-1.5 rounded-xl border border-indigo-200/60 dark:border-indigo-800/60 shadow-2xs">
                <span>مرحله بعد: اطلاعات تکمیلی</span>
                <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </div>
        </div>
    @endif

    {{-- وضعیت بررسی ادمین و دکمه مشاهده تغییرات --}}
    @if($isAdmin)
        <div class="{{ $cardClass }} p-3.5 sm:p-4 bg-amber-50/40 dark:bg-slate-800/90 border-amber-200/80 dark:border-amber-900/40">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3.5 sm:gap-4">
                <div class="flex items-start sm:items-center gap-3">
                    <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-500/15 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </span>
                    <div class="space-y-0.5">
                        <span class="text-xs font-bold text-amber-900 dark:text-amber-300 block">
                            وضعیت تأیید و انتشار در پلتفرم
                        </span>
                        <div class="flex items-center gap-2 flex-wrap text-xs text-slate-600 dark:text-slate-300 font-sans">
                            <span>وضعیت:</span>
                            <strong class="text-slate-900 dark:text-white">
                                {{ match($property->approval_status) { 'approved' => 'تأیید شده و منتشر', 'pending_review' => 'در انتظار بررسی مدیریت', 'rejected' => 'رد شده', default => $property->approval_status } }}
                            </strong>
                            @if($property->approval_status === 'rejected' && $property->rejection_reason)
                                <span class="text-[11px] text-rose-600 dark:text-rose-400 font-sans">
                                    (علت رد: {{ $property->rejection_reason }})
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full md:w-auto justify-start md:justify-end">
                    @if($property->pendingRevision()->exists())
                        <button type="button"
                                @click="$dispatch('open-revision-diff', { propertyId: {{ $property->id }}, reviewUrl: '{{ route('user.properties.rental.review-status', $property) }}', isPending: true })"
                                class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>بررسی تغییرات (Diff)</span>
                        </button>
                    @elseif($property->latestRevision()->exists())
                        <button type="button"
                                @click="$dispatch('open-revision-diff', { propertyId: {{ $property->id }}, reviewUrl: '{{ route('user.properties.rental.review-status', $property) }}', isPending: false })"
                                class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700/80 dark:hover:bg-slate-700 dark:text-slate-200 text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 border border-slate-200 dark:border-slate-600 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <span>گزارش آخرین تغییرات</span>
                        </button>
                    @endif

                    <form action="{{ route('user.properties.rental.review-status', $property) }}" method="POST" class="m-0 flex-1 sm:flex-initial">
                        @csrf
                        @if($property->approval_status !== 'approved')
                            <input type="hidden" name="approval_status" value="approved">
                            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>تأیید مستقیم</span>
                            </button>
                        @else
                            <input type="hidden" name="approval_status" value="rejected">
                            <input type="hidden" name="rejection_reason" value="نیاز به بازنگری تصاویر یا اطلاعات">
                            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                <span>لغو تأیید</span>
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        @include('properties::user.partials.revision-diff-modal')
    @else
        @if($property->approval_status === 'pending_review' || $property->pendingRevision()->exists())
            <div class="{{ $cardClass }} p-3.5 sm:p-4 bg-amber-50/40 dark:bg-slate-800/90 border-amber-200/80 dark:border-amber-900/40 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200">
                            آخرین تغییرات شما در انتظار تأیید مدیریت است
                        </h4>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5 font-sans">
                            تا پیش از تأیید کارشناس، نسخه معتبر قبلی نمایش داده می‌شود.
                        </p>
                    </div>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-300/60 dark:border-amber-700/50 shrink-0">
                    در انتظار بررسی
                </span>
            </div>
        @endif
    @endif

    {{-- فرم تنظیمات اقامتگاه --}}
    <form action="{{ route('user.properties.rental.config.update', $property) }}" method="POST" class="space-y-4 sm:space-y-6" @submit="isSubmitting = true">
        @csrf
        @method('PUT')
        @if($isWizard)
            <input type="hidden" name="wizard" value="1">
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">

            {{-- ستون اصلی: نرخ‌های پایه، تاریخ‌های خاص و مقررات (۸ ستون) --}}
            <div class="lg:col-span-8 space-y-4 sm:space-y-5">

                {{-- کارت ۱: نرخ‌های پایه شبانه --}}
                <div class="{{ $cardClass }} p-4 sm:p-5.5 space-y-4 sm:space-y-5">
                    <div class="flex items-center gap-2.5 sm:gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3 sm:pb-3.5">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-700/70 dark:text-slate-200 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">نرخ‌های پایه شبانه</h2>
                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-sans">تعیین نرخ مبنا، آخر هفته، ایام پیک و شرایط اقامت (مبالغ به {{ $currencyLabel }})</p>
                        </div>
                    </div>

                    <div class="space-y-4 sm:space-y-4.5">
                        {{-- ۱. هزینه شب‌های عادی / پایه (پیش‌فرض) --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 font-sans">هزینه شب‌های عادی / پایه (پیش‌فرض) <span class="text-red-500">*</span></label>
                                <span class="text-[10px] text-emerald-700 dark:text-emerald-300 font-bold bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/60 dark:border-emerald-800/40 px-2 py-0.5 rounded-md font-sans">نرخ مبنا</span>
                            </div>
                            <div class="relative">
                                <input type="text" name="price_per_night" x-model="pricePerNight" @input="formatPrice('pricePerNight')" required class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-500 dark:text-slate-400 font-sans pointer-events-none">{{ $currencyLabel }}</span>
                            </div>
                        </div>

                        {{-- ۲. نرخ آخر هفته و روزهای ویژه هفتگی --}}
                        <div class="p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/60 dark:bg-slate-900/40 space-y-3">
                            <div class="flex items-center justify-between gap-2 mb-2.5">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 font-sans">نرخ آخر هفته و روزهای انتخابی</label>
                                <span class="text-[11px] text-indigo-700 dark:text-indigo-300 font-bold font-sans bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200/60 dark:border-indigo-800/40 px-2 py-0.5 rounded-md">روزهای فعال</span>
                            </div>
                            
                            {{-- سلکتور چندانتخابی روزهای هفته (فشرده در موبایل: ۴ ستون و ۷ ستون در دسکتاپ) --}}
                            <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 sm:gap-2">
                                @foreach($weekDays as $dayKey => $dayName)
                                    <button type="button" 
                                            @click="toggleWeekendDay('{{ $dayKey }}')" 
                                            class="py-2 px-1 text-[11px] sm:text-xs font-bold rounded-lg sm:rounded-xl border transition-all flex items-center justify-center gap-1 font-sans select-none"
                                            :class="isWeekendSelected('{{ $dayKey }}') 
                                                ? 'bg-indigo-600 border-indigo-600 text-white shadow-2xs dark:bg-indigo-500/25 dark:text-indigo-300 dark:border-indigo-500/50' 
                                                : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-600 hover:border-slate-400 dark:hover:border-slate-500'">
                                        <span>{{ $dayName }}</span>
                                        <span class="w-1.5 h-1.5 rounded-full" :class="isWeekendSelected('{{ $dayKey }}') ? 'bg-white dark:bg-indigo-400' : 'bg-transparent'"></span>
                                    </button>
                                @endforeach
                            </div>
                            
                            {{-- اینپوت‌های پنهان برای ارسال روزهای انتخاب‌شده --}}
                            <template x-for="day in selectedWeekendDays" :key="day">
                                <input type="hidden" name="weekend_days[]" :value="day">
                            </template>
                            
                            {{-- فیلد قیمت برای این روزها --}}
                            <div class="relative pt-1 sm:pt-1.5">
                                <input type="text" name="price_weekend" x-model="priceWeekend" @input="formatPrice('priceWeekend')" placeholder="مبلغ برای روزهای انتخاب شده آخر هفته" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-500 dark:text-slate-400 font-sans pointer-events-none">{{ $currencyLabel }}</span>
                            </div>
                        </div>

                        {{-- ۳. نرخ ایام پیک و تعطیلات رسمی --}}
                        <div class="p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/60 dark:bg-slate-900/40 space-y-2.5">
                            <div class="flex items-center justify-between gap-2 mb-2.5">
                                <div class="flex items-center gap-2">
                                    <label class="block text-xs font-bold text-slate-900 dark:text-slate-100 font-sans">ایام پیک و تعطیلات رسمی</label>
                                    <button type="button" 
                                            @click="showHolidayModal = true" 
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-bold text-amber-800 bg-amber-100/80 hover:bg-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:hover:bg-amber-500/25 border border-amber-300/60 dark:border-amber-500/30 transition-all font-sans cursor-pointer group shadow-2xs" 
                                            title="مشاهده منطق و الگوریتم محاسبه ایام پیک">
                                        <svg class="w-3.5 h-3.5 text-amber-700 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>راهنمای الگوریتم</span>
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <input type="text" name="price_holiday" x-model="priceHoliday" @input="formatPrice('priceHoliday')" placeholder="مبلغ برای تعطیلات و پیک" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-500 dark:text-slate-400 font-sans pointer-events-none">{{ $currencyLabel }}</span>
                            </div>
                        </div>

                        {{-- ۴. تخفیف لحظه آخری و قیمت ویژه --}}
                        <div class="p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/60 dark:bg-slate-900/40 space-y-3.5">
                            <div class="flex items-center justify-between gap-3 mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full shrink-0"
                                          :class="hasDiscount ? 'bg-emerald-600 dark:bg-emerald-400' : 'bg-slate-400 dark:bg-slate-600'"></span>
                                    <label class="block text-xs font-bold text-slate-900 dark:text-slate-100 font-sans cursor-pointer">
                                        تخفیف لحظه آخری (پیشنهاد ویژه)
                                    </label>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="has_discount" value="1" x-model="hasDiscount" {{ old('has_discount', $rentalConfig->has_discount ?? false) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-10 h-5.5 bg-slate-300 peer-focus:outline-hidden rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-600"></div>
                                    <span class="ms-2.5 text-xs font-bold font-sans"
                                          :class="hasDiscount ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400'"
                                          x-text="hasDiscount ? 'فعال' : 'غیرفعال'">{{ old('has_discount', $rentalConfig->has_discount ?? false) ? 'فعال' : 'غیرفعال' }}</span>
                                </label>
                            </div>

                            <div x-show="hasDiscount" x-cloak x-transition class="space-y-3.5 pt-3.5 border-t border-slate-200 dark:border-slate-700/80">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="{{ $labelClass }}">نوع محاسبه تخفیف</label>
                                        <div class="grid grid-cols-2 gap-1.5 bg-slate-200/70 dark:bg-slate-950 p-1 rounded-xl border border-slate-300/80 dark:border-slate-700/80">
                                            <button type="button" 
                                                    @click="onDiscountTypeChange('percent')"
                                                    :class="discountType === 'percent' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
                                                    class="py-1.5 text-xs rounded-lg transition-all flex items-center justify-center gap-1 font-sans">
                                                <span>درصدی</span>
                                                <span class="text-[11px] font-sans opacity-70">(٪)</span>
                                            </button>
                                            <button type="button" 
                                                    @click="onDiscountTypeChange('fixed')"
                                                    :class="discountType === 'fixed' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
                                                    class="py-1.5 text-xs rounded-lg transition-all flex items-center justify-center gap-1 font-sans">
                                                <span>مبلغ ثابت</span>
                                                <span class="text-[11px] font-sans opacity-70">({{ $currencyLabel }})</span>
                                            </button>
                                        </div>
                                        <input type="hidden" name="discount_type" :value="discountType">
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            <span x-show="discountType === 'percent'">درصد تخفیف (٪)</span>
                                            <span x-show="discountType === 'fixed'">مبلغ کسر از هر شب ({{ $currencyLabel }})</span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" 
                                                   name="discount_value" 
                                                   x-model="discountValue" 
                                                   @input="onDiscountValueInput()" 
                                                   :placeholder="discountType === 'percent' ? 'مثلاً ۲۰' : 'مثلاً ۲۰۰,۰۰۰'" 
                                                   class="{{ $inputClass }} font-sans"
                                                   :class="discountType === 'percent' ? 'text-center' : 'text-left dir-ltr pl-14'">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-500 dark:text-slate-400 font-sans pointer-events-none" 
                                                  x-text="discountType === 'percent' ? '٪' : '{{ $currencyLabel }}'"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- پیش‌نمایش قیمت‌ها با احتساب تخفیف --}}
                                <div x-show="getRawPrice(discountValue) > 0" class="p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-800 dark:text-slate-200">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            پیش‌نمایش قیمت‌ها پس از اعمال تخفیف:
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 text-xs">
                                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between">
                                            <span class="text-slate-500 dark:text-slate-400 text-[11px]">نرخ عادی:</span>
                                            <div class="mt-1 flex items-baseline gap-1.5 flex-wrap">
                                                <span class="text-slate-400 line-through text-[11px]" x-text="pricePerNight ? pricePerNight : '---'"></span>
                                                <span class="font-bold text-emerald-600 dark:text-emerald-400 font-sans text-xs sm:text-sm" x-text="pricePerNight ? formatNumber(getDiscountedPrice(pricePerNight)) + ' {{ $currencyLabel }}' : '---'"></span>
                                            </div>
                                        </div>
                                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between">
                                            <span class="text-slate-500 dark:text-slate-400 text-[11px]">آخر هفته:</span>
                                            <div class="mt-1 flex items-baseline gap-1.5 flex-wrap">
                                                <span class="text-slate-400 line-through text-[11px]" x-text="priceWeekend ? priceWeekend : (pricePerNight ? pricePerNight : '---')"></span>
                                                <span class="font-bold text-emerald-600 dark:text-emerald-400 font-sans text-xs sm:text-sm" x-text="(priceWeekend || pricePerNight) ? formatNumber(getDiscountedPrice(priceWeekend || pricePerNight)) + ' {{ $currencyLabel }}' : '---'"></span>
                                            </div>
                                        </div>
                                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between">
                                            <span class="text-slate-500 dark:text-slate-400 text-[11px]">ایام پیک:</span>
                                            <div class="mt-1 flex items-baseline gap-1.5 flex-wrap">
                                                <span class="text-slate-400 line-through text-[11px]" x-text="priceHoliday ? priceHoliday : (priceWeekend ? priceWeekend : (pricePerNight ? pricePerNight : '---'))"></span>
                                                <span class="font-bold text-emerald-600 dark:text-emerald-400 font-sans text-xs sm:text-sm" x-text="(priceHoliday || priceWeekend || pricePerNight) ? formatNumber(getDiscountedPrice(priceHoliday || priceWeekend || pricePerNight)) + ' {{ $currencyLabel }}' : '---'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ۵. سایر مبالغ و حداقل مدت اقامت --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                            <div>
                                <label class="{{ $labelClass }}">هزینه نفر اضافه (هر شب)</label>
                                <div class="relative">
                                    <input type="text" name="extra_guest_fee" x-model="extraGuestFee" @input="formatPrice('extraGuestFee')" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-500 dark:text-slate-400 font-sans pointer-events-none">{{ $currencyLabel }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">هزینه نظافت (اختیاری)</label>
                                <div class="relative">
                                    <input type="text" name="cleaning_fee" x-model="cleaningFee" @input="formatPrice('cleaningFee')" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-500 dark:text-slate-400 font-sans pointer-events-none">{{ $currencyLabel }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">حداقل مدت اقامت</label>
                                <div class="relative">
                                    <input type="number" name="min_stay_nights" value="{{ old('min_stay_nights', $rentalConfig->min_stay_nights ?? 1) }}" min="1" max="30" class="{{ $inputClass }} text-center font-sans">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-500 dark:text-slate-400 font-sans pointer-events-none">شب</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- کارت ۲: قیمت‌گذاری ویژه تاریخ‌های خاص (تکی و بازه‌ای) --}}
                <div class="{{ $cardClass }} p-4 sm:p-5.5 space-y-4 sm:space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3 sm:pb-3.5">
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-700/70 dark:text-slate-200 flex items-center justify-center font-bold shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">قیمت‌گذاری ویژه تاریخ‌های خاص</h2>
                                    <span class="text-[10px] text-purple-700 dark:text-purple-300 font-bold bg-purple-50 dark:bg-purple-950/50 border border-purple-200/60 dark:border-purple-800/40 px-2 py-0.5 rounded-md font-sans">بالاترین اولویت</span>
                                </div>
                            </div>
                        </div>

                        <button type="button" 
                                @click="addSpecialPrice()"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 sm:py-2 text-xs font-bold rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/50 border border-purple-200/80 dark:border-purple-800/40 transition shadow-2xs font-sans self-start sm:self-center">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            افزودن تاریخ خاص
                        </button>
                    </div>

                    {{-- ردیف‌های تاریخ‌های خاص --}}
                    <div class="space-y-2.5 sm:space-y-3">
                        <template x-for="(item, index) in specialPrices" :key="index">
                            <div class="p-3 sm:p-4 rounded-xl bg-slate-50/60 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700/80 space-y-2.5 sm:space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="w-5 h-5 sm:w-6 sm:h-6 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[11px] font-bold font-sans shrink-0" x-text="index + 1"></span>
                                        <input type="text" 
                                               :name="`special_prices[${index}][title]`" 
                                               x-model="item.title" 
                                               placeholder="عنوان مناسبت (اختیاری، مثلاً: شب یلدا)" 
                                               class="{{ $inputClass }} py-1.5 text-xs font-sans">
                                    </div>

                                    {{-- سوئیچ تاریخ تکی / بازه --}}
                                    <div class="flex items-center bg-slate-200 dark:bg-slate-800 p-0.5 rounded-lg text-xs font-sans shrink-0 border border-slate-300/60 dark:border-slate-700/60">
                                        <button type="button" 
                                                @click="item.type = 'single'"
                                                :class="item.type === 'single' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'"
                                                class="px-2 py-1 rounded-md transition-all text-[11px]">
                                            تکی
                                        </button>
                                        <button type="button" 
                                                @click="item.type = 'range'"
                                                :class="item.type === 'range' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'"
                                                class="px-2 py-1 rounded-md transition-all text-[11px]">
                                            بازه
                                        </button>
                                        <input type="hidden" :name="`special_prices[${index}][type]`" :value="item.type">
                                    </div>

                                    <button type="button" 
                                            @click="removeSpecialPrice(index)" 
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition shrink-0" 
                                            title="حذف این تاریخ">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>

                                {{-- فیلدهای تاریخ و قیمت --}}
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 sm:gap-3 items-center">
                                    <div :class="item.type === 'range' ? 'sm:col-span-4' : 'sm:col-span-6'">
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1 font-sans">
                                            <span x-text="item.type === 'range' ? 'تاریخ شروع' : 'تاریخ مورد نظر'"></span>
                                        </label>
                                        <input type="text" 
                                               :name="`special_prices[${index}][start_date]`" 
                                               x-model="item.start_date" 
                                               data-jdp-only-date 
                                               placeholder="1405/01/01" 
                                               class="{{ $inputClass }} text-center dir-ltr font-sans text-xs" 
                                               required>
                                    </div>

                                    <div x-show="item.type === 'range'" class="sm:col-span-4">
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1 font-sans">تاریخ پایان</label>
                                        <input type="text" 
                                               :name="`special_prices[${index}][end_date]`" 
                                               x-model="item.end_date" 
                                               data-jdp-only-date 
                                               placeholder="1405/01/05" 
                                               class="{{ $inputClass }} text-center dir-ltr font-sans text-xs">
                                    </div>

                                    <div :class="item.type === 'range' ? 'sm:col-span-4' : 'sm:col-span-6'">
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1 font-sans">نرخ هر شب ({{ $currencyLabel }})</label>
                                        <div class="relative">
                                            <input type="text" 
                                                   :name="`special_prices[${index}][price]`" 
                                                   x-model="item.price" 
                                                   @input="formatSpecialPrice(index)" 
                                                   placeholder="مبلغ هر شب" 
                                                   class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans text-xs" 
                                                   required>
                                            <span class="absolute inset-y-0 left-2.5 flex items-center text-[10px] text-slate-500 dark:text-slate-400 font-sans pointer-events-none">{{ $currencyLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="specialPrices.length === 0" class="text-center py-5 px-3 flex flex-col items-center justify-center text-slate-400 border border-dashed border-slate-200 dark:border-slate-700/80 rounded-xl">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2 font-sans">هنوز قیمت‌گذاری ویژه‌ای برای تاریخ‌های خاص ثبت نشده است.</p>
                            <button type="button" 
                                    @click="addSpecialPrice()" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 transition font-sans">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                افزودن تاریخ خاص
                            </button>
                        </div>
                    </div>
                </div>

                {{-- کارت ۳: مقررات و قوانین ورود به اقامتگاه --}}
                <div class="{{ $cardClass }} p-4 sm:p-5.5 space-y-4 sm:space-y-5">
                    <div class="flex items-center gap-2.5 sm:gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3 sm:pb-3.5">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-700/70 dark:text-slate-200 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">مقررات و قوانین ورود به اقامتگاه</h2>
                        </div>
                    </div>

                    {{-- ۱. قوانین پیش‌فرض سامانه --}}
                    <div class="space-y-2.5">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 block">قوانین و استانداردهای عمومی</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-2.5">
                            @foreach($defaultRules as $rKey => $rTitle)
                                <label class="flex items-center gap-2.5 p-2.5 sm:p-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-white dark:hover:bg-slate-800 cursor-pointer transition select-none">
                                    <input type="checkbox" name="house_rules[]" value="{{ $rKey }}" {{ in_array($rKey, $selectedDefaultRules ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300 font-sans">{{ $rTitle }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- ۲. قوانین اختصاصی اقامتگاه --}}
                    <div class="pt-3.5 border-t border-slate-100 dark:border-slate-700/60 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                قوانین اختصاصی اقامتگاه
                            </h3>
                            <span class="text-[11px] text-indigo-700 dark:text-indigo-300 font-bold font-sans bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200/60 dark:border-indigo-800/40 px-2 py-0.5 rounded-md" x-text="`${customRules.length} قانون اختصاصی`"></span>
                        </div>

                        {{-- فیلد افزودن قانون سفارشی جدید --}}
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input type="text" 
                                       x-model="newRuleText" 
                                       @keydown.enter.prevent="addCustomRule()"
                                       placeholder="متن قانون اختصاصی اقامتگاه..." 
                                       class="{{ $inputClass }} text-xs py-2 pl-3 font-sans">
                            </div>
                            <button type="button" 
                                    @click="addCustomRule()"
                                    class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1 shadow-2xs whitespace-nowrap shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                <span>افزودن</span>
                            </button>
                        </div>

                        {{-- لیست قوانین سفارشی افزوده شده --}}
                        <div class="space-y-2" x-show="customRules.length > 0">
                            <template x-for="(rule, index) in customRules" :key="index">
                                <div class="flex items-center justify-between gap-2.5 p-2.5 sm:p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40 hover:bg-white dark:hover:bg-slate-800 transition">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <span class="w-5 h-5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[11px] font-bold shrink-0 font-sans" x-text="index + 1"></span>
                                        <input type="hidden" name="custom_rules[]" :value="rule">
                                        <span class="text-xs text-slate-800 dark:text-slate-200 font-sans break-words leading-relaxed" x-text="rule"></span>
                                    </div>
                                    <button type="button" 
                                            @click="removeCustomRule(index)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition shrink-0"
                                            title="حذف این قانون">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- پیام در صورت نبود قانون سفارشی --}}
                        <div x-show="customRules.length === 0" class="text-center py-2.5 px-3 rounded-xl border border-dashed border-slate-200 dark:border-slate-700/80 text-[11px] text-slate-400 dark:text-slate-500 font-sans">
                            قانون اختصاصی ثبت نشده است. در صورت تمایل از کادر بالا اضافه نمایید.
                        </div>
                    </div>
                </div>

            </div>

            {{-- ستون کناری: ظرفیت مهمان، ورود/خروج و ثبت (۴ ستون) --}}
            <div class="lg:col-span-4 space-y-4 sm:space-y-5">

                {{-- کارت ۴: ظرفیت پذیرش مهمان --}}
                <div class="{{ $cardClass }} p-4 sm:p-5 space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-700/70 dark:text-slate-200 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">ظرفیت پذیرش مهمان</h2>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                        <div>
                            <label class="{{ $labelClass }}">ظرفیت استاندارد <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="base_guests" value="{{ old('base_guests', $rentalConfig->base_guests ?? 2) }}" min="1" max="50" required class="{{ $inputClass }} text-center font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-500 dark:text-slate-400 font-sans pointer-events-none">نفر</span>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">حداکثر با اضافه <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="max_guests" value="{{ old('max_guests', $rentalConfig->max_guests ?? 4) }}" min="1" max="100" required class="{{ $inputClass }} text-center font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-slate-500 dark:text-slate-400 font-sans pointer-events-none">نفر</span>
                            </div>
                        </div>
                    </div>

                    @if($isWizard)
                        <div class="p-3 rounded-xl bg-indigo-50/60 border border-indigo-100 dark:bg-indigo-950/20 dark:border-indigo-900/40 flex items-center gap-2 text-xs text-indigo-900 dark:text-indigo-200 font-sans">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>مشخصات اتاق‌ها، تخت‌ها و امکانات در گام‌های بعدی تکمیل می‌شوند.</span>
                        </div>
                    @endif
                </div>

                {{-- کارت ۵: زمان‌بندی ورود و خروج و رزرو فوری --}}
                <div class="{{ $cardClass }} p-4 sm:p-5 space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-700/70 dark:text-slate-200 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">ساعت ورود و خروج</h2>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                            <div>
                                <label class="{{ $labelClass }}">ساعت ورود</label>
                                <div class="relative">
                                    <input type="text" 
                                           name="check_in_time" 
                                           data-jdp-only-time
                                           placeholder="14:00"
                                           value="{{ old('check_in_time', substr($rentalConfig->check_in_time ?? '14:00', 0, 5)) }}" 
                                           class="{{ $inputClass }} text-center dir-ltr font-sans cursor-pointer pl-8">
                                    <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 pointer-events-none">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">ساعت خروج</label>
                                <div class="relative">
                                    <input type="text" 
                                           name="check_out_time" 
                                           data-jdp-only-time
                                           placeholder="12:00"
                                           value="{{ old('check_out_time', substr($rentalConfig->check_out_time ?? '12:00', 0, 5)) }}" 
                                           class="{{ $inputClass }} text-center dir-ltr font-sans cursor-pointer pl-8">
                                    <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 pointer-events-none">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40 cursor-pointer">
                                <input type="checkbox" name="instant_booking" value="1" {{ ($rentalConfig->instant_booking ?? false) ? 'checked' : '' }} class="{{ $checkboxClass }} mt-0.5">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">رزرو قطعی آنی</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">ثبت مستقیم رزرو بدون نیاز به تأیید میزبان</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- کارت ۶: عملیات ذخیره سازی و ادامه فرآیند (چسبان در دسکتاپ) --}}
                <div class="{{ $cardClass }} p-4 sm:p-5 space-y-2.5 lg:sticky lg:top-6 z-20">
                    @if($isWizard)
                        <button type="submit" class="w-full py-2.5 sm:py-3 px-5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm shadow-xs transition flex items-center justify-center gap-2">
                            <span>ذخیره و ادامه (اطلاعات تکمیلی)</span>
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-sans block text-center">
                            مرحله بعد: مشخصات اتاق‌ها و ابعاد
                        </span>
                    @else
                        <button type="submit" class="w-full py-2.5 sm:py-3 px-5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm shadow-xs transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>ذخیره تنظیمات اقامتگاه</span>
                        </button>

                        <div class="pt-1 flex flex-col gap-1.5">
                            <a href="{{ route('user.properties.rental.calendar', $property) }}" class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span>مدیریت تقویم و نرخ روزها</span>
                            </a>
                            <a href="{{ route('user.properties.edit', $property) }}" class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                <span>ویرایش امکانات و مشخصات</span>
                            </a>
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </form>

    {{-- مودال راهنمای الگوریتم هوشمند محاسبه ایام پیک و تعطیلات --}}
    <div x-show="showHolidayModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs transition-opacity"
             @click="showHolidayModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-right shadow-xl transition-all w-full max-w-lg border border-slate-200 dark:border-slate-700 font-sans p-4 sm:p-6 space-y-4"
                 @click.away="showHolidayModal = false"
                 @keydown.escape.window="showHolidayModal = false">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/40 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                الگوریتم محاسبه ایام پیک و تعطیلات
                            </h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                استعلام تقویم، تشخیص شب‌های تعطیل و اولویت‌بندی نرخ‌ها
                            </p>
                        </div>
                    </div>

                    <button type="button" 
                            @click="showHolidayModal = false" 
                            class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Items List --}}
                <div class="space-y-2.5 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/80 flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <div class="space-y-0.5">
                            <strong class="text-slate-900 dark:text-white font-bold block text-xs">بانک اطلاعاتی تقویم ملی</strong>
                            <p class="text-slate-600 dark:text-slate-300 leading-relaxed font-sans text-[11px]">روزهای تعطیل رسمی تقویم شمسی کشور مستقیماً بر اساس «بانک اطلاعاتی مناسبت‌ها و تعطیلات ملی» ثبت‌شده در سامانه استعلام و محاسبه می‌شوند.</p>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/80 flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <div class="space-y-0.5">
                            <strong class="text-slate-900 dark:text-white font-bold block text-xs">محاسبه هوشمند شب تعطیل (Eve of Holiday)</strong>
                            <p class="text-slate-600 dark:text-slate-300 leading-relaxed font-sans text-[11px]">با توجه به زمان حرکت و اقامت مسافران در عصر و شب قبل از تعطیلی، «شبِ قبل از هر تعطیلی رسمی تقویم» به عنوان شب پیک شناسایی شده و این نرخ اعمال می‌شود.</p>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/80 flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <div class="space-y-0.5">
                            <strong class="text-slate-900 dark:text-white font-bold block text-xs">وضعیت روزهای جمعه</strong>
                            <p class="text-slate-600 dark:text-slate-300 leading-relaxed font-sans text-[11px]">جمعه‌شب به صورت پیش‌فرض عادی است (زیرا شنبه روز کاری است)؛ اما در صورت تعطیل رسمی بودن روز «شنبه»، جمعه‌شب به صورت خودکار به عنوان شب پیک محاسبه می‌شود.</p>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/80 flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <div class="space-y-0.5">
                            <strong class="text-slate-900 dark:text-white font-bold block text-xs">روزهای آخر هفته</strong>
                            <p class="text-slate-600 dark:text-slate-300 leading-relaxed font-sans text-[11px]">چهارشنبه و پنج‌شنبه (یا روزهای انتخابی شما در تنظیمات) تحت پوشش «نرخ آخر هفته» هستند، مگر اینکه با تعطیلات پیوسته شوند که نرخ پیک اولویت پیدا می‌کند.</p>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/80 flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <div class="space-y-0.5">
                            <strong class="text-slate-900 dark:text-white font-bold block text-xs">اولویت خودکار</strong>
                            <p class="text-slate-600 dark:text-slate-300 leading-relaxed font-sans text-[11px]">این نرخ برای تمامی شب‌های مشمول تعطیلات رسمی به صورت خودکار در فاکتور و تقویم رزرو مسافر جایگزین نرخ‌های پایه می‌شود.</p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="pt-1 flex justify-end">
                    <button type="button" 
                            @click="showHolidayModal = false" 
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-bold transition font-sans">
                        متوجه شدم
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function rentalConfigForm() {
        return {
            showHolidayModal: false,
            isSubmitting: false,
            pricePerNight: '{{ old('price_per_night', $rentalConfig->price_per_night ? number_format($rentalConfig->price_per_night) : '') }}',
            priceWeekend: '{{ old('price_weekend', $rentalConfig->price_weekend ? number_format($rentalConfig->price_weekend) : '') }}',
            priceHoliday: '{{ old('price_holiday', $rentalConfig->price_holiday ? number_format($rentalConfig->price_holiday) : '') }}',
            hasDiscount: {{ old('has_discount', $rentalConfig->has_discount ?? false) ? 'true' : 'false' }},
            discountType: '{{ old('discount_type', $rentalConfig->discount_type ?? 'percent') }}',
            discountValue: '{{ old('discount_value', $rentalConfig->discount_value ? ($rentalConfig->discount_type === 'fixed' ? number_format($rentalConfig->discount_value) : $rentalConfig->discount_value) : ($rentalConfig->discount_percent ?? '')) }}',
            extraGuestFee: '{{ old('extra_guest_fee', $rentalConfig->extra_guest_fee ? number_format($rentalConfig->extra_guest_fee) : '') }}',
            cleaningFee: '{{ old('cleaning_fee', $rentalConfig->cleaning_fee ? number_format($rentalConfig->cleaning_fee) : '') }}',
            selectedWeekendDays: @json(old('weekend_days', $rentalConfig->weekend_days ?? ['4', '5'])),
            specialPrices: @json(old('special_prices', $specialPrices ?? [])),
            customRules: @json(old('custom_rules', $existingCustomRules ?? [])),
            newRuleText: '',

            init() {
                this.$nextTick(() => {
                    this.initDatePicker();
                });
            },

            initDatePicker() {
                if (window.jalaliDatepicker) {
                    try {
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp-only-date]',
                            minDate: 'attr',
                            date: true,
                            time: false,
                            autoHide: true,
                            hideAfterChange: true
                        });
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp-only-time]',
                            time: true,
                            date: false,
                            hasSecond: false,
                            autoHide: true
                        });
                    } catch (e) {
                        console.warn('JalaliDatepicker init in config warning:', e);
                    }
                }
            },

            addCustomRule() {
                let text = (this.newRuleText || '').trim();
                if (text !== '') {
                    if (!this.customRules.includes(text)) {
                        this.customRules.push(text);
                    }
                    this.newRuleText = '';
                }
            },

            removeCustomRule(index) {
                this.customRules.splice(index, 1);
            },

            toggleWeekendDay(dayKey) {
                dayKey = String(dayKey);
                if (this.selectedWeekendDays.includes(dayKey)) {
                    this.selectedWeekendDays = this.selectedWeekendDays.filter(d => d !== dayKey);
                } else {
                    this.selectedWeekendDays.push(dayKey);
                }
            },

            isWeekendSelected(dayKey) {
                return this.selectedWeekendDays.includes(String(dayKey));
            },

            addSpecialPrice() {
                this.specialPrices.push({
                    id: null,
                    title: '',
                    type: 'single',
                    start_date: '',
                    end_date: '',
                    price: ''
                });
                this.$nextTick(() => {
                    this.initDatePicker();
                });
            },

            removeSpecialPrice(index) {
                this.specialPrices.splice(index, 1);
            },

            formatSpecialPrice(index) {
                let val = String(this.specialPrices[index].price).replace(/,/g, '').replace(/[^\d]/g, '');
                if (val !== '') {
                    this.specialPrices[index].price = parseInt(val).toLocaleString('en-US');
                } else {
                    this.specialPrices[index].price = '';
                }
            },

            formatPrice(field) {
                let val = String(this[field]).replace(/,/g, '').replace(/[^\d]/g, '');
                if (val !== '') {
                    this[field] = parseInt(val).toLocaleString('en-US');
                } else {
                    this[field] = '';
                }
            },

            getRawPrice(val) {
                if (!val) return 0;
                let n = parseInt(String(val).replace(/,/g, '').replace(/[^\d]/g, ''), 10);
                return isNaN(n) ? 0 : n;
            },

            getDiscountAmount(price) {
                let p = this.getRawPrice(price);
                if (p <= 0 || !this.hasDiscount) return 0;
                let val = this.getRawPrice(this.discountValue);
                if (val <= 0) return 0;

                if (this.discountType === 'percent') {
                    let pct = Math.min(99, Math.max(1, val));
                    return Math.round((p * pct) / 100);
                } else {
                    return Math.min(p, val);
                }
            },

            getDiscountedPrice(price) {
                let p = this.getRawPrice(price);
                if (p <= 0) return 0;
                let discount = this.getDiscountAmount(p);
                return Math.max(0, p - discount);
            },

            formatNumber(n) {
                return Number(n).toLocaleString('en-US');
            },

            onDiscountTypeChange(newType) {
                this.discountType = newType;
                this.discountValue = '';
            },

            onDiscountValueInput() {
                if (this.discountType === 'fixed') {
                    this.formatPrice('discountValue');
                } else {
                    let val = String(this.discountValue).replace(/[^\d]/g, '');
                    if (val !== '') {
                        let num = parseInt(val, 10);
                        if (num > 99) num = 99;
                        this.discountValue = num > 0 ? num : '';
                    } else {
                        this.discountValue = '';
                    }
                }
            }
        }
    }
</script>
@endsection
