@extends('layouts.user')

@php
    $title = 'مشخصات و قیمت‌گذاری اقامتگاه روزانه';
    $cardClass = "bg-white dark:bg-gray-800/90 backdrop-blur-md rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden transition-all duration-300";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-2xl border-gray-200 bg-gray-50/70 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-800 font-sans placeholder-gray-400 dark:placeholder-gray-500";
    $checkboxClass = "w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer";
    $currencyLabel = $currency === 'toman' ? 'تومان' : 'ریال';
    $isAdmin = auth()->user()->hasRole(['super-admin', 'admin']) || auth()->user()->can('properties.manage');
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-8" x-data="rentalConfigForm()">

    {{-- هدر صفحه و ناوبری --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 flex items-center justify-center shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                </span>
                تنظیمات اقامتگاه: {{ $property->title }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-13">ظرفیت مهمانان، قیمت‌های شبانه، شرایط اقامت، امکانات و قوانین ورود</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('user.properties.rental.calendar', $property) }}" class="px-4 py-2.5 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300 text-xs font-bold border border-indigo-200 dark:border-indigo-800/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                تقویم و مسدودسازی روزها
            </a>
            <a href="{{ route('user.properties.index') }}" class="px-4 py-2.5 rounded-2xl border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                بازگشت به لیست املاک
            </a>
        </div>
    </div>

    {{-- وضعیت بررسی ادمین و دکمه مشاهده تغییرات --}}
    @if($isAdmin)
        <div class="{{ $cardClass }} p-5 bg-gradient-to-r from-amber-50/60 to-white dark:from-gray-800 dark:to-gray-800/80 border-amber-200/80 dark:border-amber-900/40">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-amber-800 dark:text-amber-300 block flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        وضعیت تأیید و انتشار در پلتفرم
                    </span>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs text-gray-600 dark:text-gray-300 font-sans">
                            وضعیت فعلی:
                            <strong class="text-gray-900 dark:text-white mr-1">
                                {{ match($property->approval_status) { 'approved' => 'تأیید شده و منتشر', 'pending_review' => 'در انتظار بررسی مدیریت', 'rejected' => 'رد شده', default => $property->approval_status } }}
                            </strong>
                        </span>

                        @if($property->approval_status === 'rejected' && $property->rejection_reason)
                            <span class="text-[11px] text-rose-600 dark:text-rose-400 font-sans">
                                (علت رد: {{ $property->rejection_reason }})
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    {{-- دکمه مشاهده جزئیات تغییرات و مقایسه (در صورتی که درخواست ویرایش در انتظار باشد) --}}
                    @if($property->pendingRevision()->exists())
                        <button type="button"
                                @click="$dispatch('open-revision-diff', { propertyId: {{ $property->id }}, reviewUrl: '{{ route('user.properties.rental.review-status', $property) }}' })"
                                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5 animate-pulse">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            بررسی و مقایسه تغییرات (Diff)
                        </button>
                    @endif

                    <form action="{{ route('user.properties.rental.review-status', $property) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        @if($property->approval_status !== 'approved')
                            <input type="hidden" name="approval_status" value="approved">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                تأیید مستقیم
                            </button>
                        @else
                            <input type="hidden" name="approval_status" value="rejected">
                            <input type="hidden" name="rejection_reason" value="نیاز به بازنگری تصاویر یا اطلاعات">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                لغو تأیید
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        {{-- ورود مودال مقایسه تغییرات --}}
        @include('properties::user.partials.revision-diff-modal')
    @endif

    {{-- فرم تنظیمات اقامتگاه --}}
    <form action="{{ route('user.properties.rental.config.update', $property) }}" method="POST" class="space-y-8" @submit="isSubmitting = true">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- ستون اصلی: نرخ‌های پایه، تاریخ‌های خاص و مقررات (۸ ستون) --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- کارت ۱: نرخ‌های پایه شبانه --}}
                <div class="{{ $cardClass }} p-6 sm:p-7 space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">نرخ‌های پایه شبانه</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تعیین قیمت شب‌های عادی، روزهای آخر هفته، ایام پیک، هزینه مهمان اضافه و نظافت (مبالغ به {{ $currencyLabel }})</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        {{-- ۱. هزینه شب‌های عادی / پایه (پیش‌فرض) --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="{{ $labelClass }} mb-0">هزینه شب‌های عادی / پایه (پیش‌فرض) <span class="text-red-500">*</span></label>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md font-sans">نرخ مبنا</span>
                            </div>
                            <div class="relative">
                                <input type="text" name="price_per_night" x-model="pricePerNight" @input="formatPrice('pricePerNight')" required class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-sans">این مبلغ نرخ مرجع و پیش‌فرض اقامتگاه است که روی تمامی شب‌های سال اعمال می‌شود مگر با نرخ‌های دیگر بازنویسی شود.</p>
                        </div>

                        {{-- ۲. نرخ آخر هفته و روزهای ویژه هفتگی --}}
                        <div class="p-5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="space-y-0.5">
                                    <label class="{{ $labelClass }} mb-0">نرخ آخر هفته و روزهای ویژه هفتگی</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-sans">روزهای مدنظر برای نرخ آخر هفته را انتخاب و قیمت آن را مشخص کنید (پیش‌فرض: چهارشنبه و پنج‌شنبه).</p>
                                </div>
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold font-sans self-start sm:self-auto bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-1 rounded-lg">انتخاب روزهای فعال</span>
                            </div>
                            
                            {{-- سلکتور چندانتخابی روزهای هفته --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2">
                                @foreach($weekDays as $dayKey => $dayName)
                                    <button type="button" 
                                            @click="toggleWeekendDay('{{ $dayKey }}')" 
                                            class="py-2.5 px-2 text-xs font-bold rounded-xl border transition-all flex items-center justify-center gap-1.5 font-sans select-none"
                                            :class="isWeekendSelected('{{ $dayKey }}') 
                                                ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm shadow-indigo-500/20 dark:bg-indigo-500/25 dark:text-indigo-300 dark:border-indigo-500/40' 
                                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
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
                            <div class="relative pt-1">
                                <input type="text" name="price_weekend" x-model="priceWeekend" @input="formatPrice('priceWeekend')" placeholder="مبلغ برای روزهای انتخاب شده آخر هفته" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                            </div>
                        </div>

                        {{-- ۳. نرخ ایام پیک و تعطیلات رسمی --}}
                        <div class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="{{ $labelClass }} mb-0 text-amber-900 dark:text-amber-200">ایام پیک و تعطیلات رسمی</label>
                                    <p class="text-xs text-amber-700/80 dark:text-amber-400/80 font-sans mt-0.5">نرخ مخصوص تعطیلات رسمی کشور و شب‌های پیش از تعطیلی</p>
                                </div>
                                <span class="text-xs text-amber-700 dark:text-amber-300 font-bold bg-amber-100 dark:bg-amber-900/40 px-2.5 py-1 rounded-lg font-sans">تقویم ملی</span>
                            </div>
                            <div class="relative">
                                <input type="text" name="price_holiday" x-model="priceHoliday" @input="formatPrice('priceHoliday')" placeholder="مبلغ برای تعطیلات و پیک" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                            </div>

                            {{-- باکس تشریح الگوریتم هوشمند محاسبه ایام پیک --}}
                            <div class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-amber-200/80 dark:border-amber-800/40 space-y-2.5 text-xs">
                                <div class="flex items-center gap-2 font-bold text-amber-800 dark:text-amber-300">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>الگوریتم هوشمند محاسبه ایام پیک و تعطیلات:</span>
                                </div>
                                <ul class="space-y-1.5 text-gray-600 dark:text-gray-300 pr-5 list-disc text-xs leading-relaxed font-sans">
                                    <li><strong>بانک اطلاعاتی تقویم ملی:</strong> روزهای تعطیل رسمی تقویم شمسی کشور مستقیماً بر اساس «بانک اطلاعاتی مناسبت‌ها و تعطیلات ملی» ثبت‌شده در تنظیمات سیستم استعلام و محاسبه می‌شوند.</li>
                                    <li><strong>محاسبه هوشمند شب تعطیل (Eve of Holiday):</strong> از آنجا که مسافران عصر/شب قبل از روز تعطیل حرکت و اقامت می‌کنند، «شبِ قبل از هر تعطیلی رسمی تقویم» به عنوان شب پیک شناسایی شده و این نرخ روی آن اعمال می‌شود.</li>
                                    <li><strong>وضعیت روزهای جمعه:</strong> جمعه شب به صورت پیش‌فرض مشمول نرخ پایه و عادی است (زیرا شنبه روز کاری است و مسافران جمعه عصر بازمی‌گردند)؛ اما چنانچه روز «شنبه» تعطیل رسمی باشد، جمعه شب به عنوان «شبِ قبل از تعطیل رسمی» به صورت هوشمند مشمول نرخ ایام پیک خواهد شد.</li>
                                    <li><strong>روزهای آخر هفته:</strong> چهارشنبه و پنج‌شنبه (یا روزهای انتخابی شما در بالا) تحت پوشش «نرخ آخر هفته» هستند، مگر اینکه با تعطیلات رسمی تقویمی پیوسته شوند که نرخ پیک اولویت پیدا می‌کند.</li>
                                    <li><strong>اولویت خودکار:</strong> این نرخ برای تمامی شب‌های مشمول تعطیلات رسمی تقویم به صورت خودکار در فاکتور و تقویم رزرو مسافر جایگزین نرخ‌های پایه می‌شود.</li>
                                </ul>
                            </div>
                        </div>

                        {{-- ۴. سایر مبالغ و حداقل مدت اقامت --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <label class="{{ $labelClass }}">هزینه نفر اضافه (هر شب)</label>
                                <div class="relative">
                                    <input type="text" name="extra_guest_fee" x-model="extraGuestFee" @input="formatPrice('extraGuestFee')" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">هزینه نظافت (اختیاری)</label>
                                <div class="relative">
                                    <input type="text" name="cleaning_fee" x-model="cleaningFee" @input="formatPrice('cleaningFee')" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">{{ $currencyLabel }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">حداقل مدت اقامت</label>
                                <div class="relative">
                                    <input type="number" name="min_stay_nights" value="{{ old('min_stay_nights', $rentalConfig->min_stay_nights ?? 1) }}" min="1" max="30" class="{{ $inputClass }} text-center font-sans">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">شب</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- کارت ۲: قیمت‌گذاری ویژه تاریخ‌های خاص (تکی و بازه‌ای) --}}
                <div class="{{ $cardClass }} p-6 sm:p-7 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">قیمت‌گذاری ویژه تاریخ‌های خاص (تکی و بازه‌ای)</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تعیین نرخ اختصاصی برای مناسبت‌ها یا بازه‌های زمانی دلخواه (دارای بالاترین اولویت نسبت به سایر نرخ‌ها).</p>
                            </div>
                        </div>

                        <button type="button" 
                                @click="addSpecialPrice()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/50 border border-purple-200/80 dark:border-purple-800/40 transition shadow-sm font-sans self-start sm:self-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            افزودن تاریخ خاص
                        </button>
                    </div>

                    {{-- ردیف‌های تاریخ‌های خاص --}}
                    <div class="space-y-3">
                        <template x-for="(item, index) in specialPrices" :key="index">
                            <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 hover:border-purple-200 dark:hover:border-purple-800/50 transition-all space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-bold font-sans flex-shrink-0" x-text="index + 1"></span>
                                        <input type="text" 
                                               :name="`special_prices[${index}][title]`" 
                                               x-model="item.title" 
                                               placeholder="عنوان یا مناسبت (اختیاری، مثلاً: شب یلدا، کنسرت)" 
                                               class="{{ $inputClass }} py-1.5 text-xs font-sans">
                                    </div>

                                    {{-- سوئیچ تاریخ تکی / بازه --}}
                                    <div class="flex items-center bg-gray-200/80 dark:bg-gray-800 p-0.5 rounded-xl text-xs font-sans flex-shrink-0">
                                        <button type="button" 
                                                @click="item.type = 'single'"
                                                :class="item.type === 'single' ? 'bg-white dark:bg-gray-700 text-purple-600 dark:text-purple-300 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                                class="px-2.5 py-1 rounded-lg transition-all text-[11px]">
                                            تاریخ تکی
                                        </button>
                                        <button type="button" 
                                                @click="item.type = 'range'"
                                                :class="item.type === 'range' ? 'bg-white dark:bg-gray-700 text-purple-600 dark:text-purple-300 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                                class="px-2.5 py-1 rounded-lg transition-all text-[11px]">
                                            بازه چند روزه
                                        </button>
                                        <input type="hidden" :name="`special_prices[${index}][type]`" :value="item.type">
                                    </div>

                                    <button type="button" 
                                            @click="removeSpecialPrice(index)" 
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition flex-shrink-0" 
                                            title="حذف این تاریخ">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>

                                {{-- فیلدهای تاریخ و قیمت --}}
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                    <div :class="item.type === 'range' ? 'sm:col-span-4' : 'sm:col-span-6'">
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1 font-sans">
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
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1 font-sans">تاریخ پایان</label>
                                        <input type="text" 
                                               :name="`special_prices[${index}][end_date]`" 
                                               x-model="item.end_date" 
                                               data-jdp-only-date 
                                               placeholder="1405/01/05" 
                                               class="{{ $inputClass }} text-center dir-ltr font-sans text-xs">
                                    </div>

                                    <div :class="item.type === 'range' ? 'sm:col-span-4' : 'sm:col-span-6'">
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1 font-sans">نرخ هر شب ({{ $currencyLabel }})</label>
                                        <div class="relative">
                                            <input type="text" 
                                                   :name="`special_prices[${index}][price]`" 
                                                   x-model="item.price" 
                                                   @input="formatSpecialPrice(index)" 
                                                   placeholder="مبلغ هر شب" 
                                                   class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans text-xs" 
                                                   required>
                                            <span class="absolute inset-y-0 left-2.5 flex items-center text-[10px] text-gray-400 font-sans">{{ $currencyLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="specialPrices.length === 0" class="text-center py-8 px-4 flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-700/80 rounded-2xl">
                            <svg class="w-10 h-10 mb-2.5 opacity-25 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 font-sans">هنوز هیچ قیمت‌گذاری ویژه‌ای برای تاریخ‌های خاص ثبت نشده است.</p>
                            <button type="button" 
                                    @click="addSpecialPrice()" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 dark:text-purple-300 transition-colors font-sans">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                افزودن اولین تاریخ خاص
                            </button>
                        </div>
                    </div>
                </div>

                {{-- کارت ۳: مقررات و قوانین ورود به اقامتگاه --}}
                <div class="{{ $cardClass }} p-6 sm:p-7 space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="w-10 h-10 rounded-2xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">مقررات و قوانین ورود به اقامتگاه</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">شرایط و محدودیت‌های ورود مهمان که قبل از رزرو باید پذیرفته شود.</p>
                        </div>
                    </div>

                    {{-- ۱. قوانین پیش‌فرض سامانه --}}
                    <div class="space-y-3">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">قوانین و استانداردهای عمومی</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @foreach($defaultRules as $rKey => $rTitle)
                                <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-gray-100 dark:border-gray-700/70 bg-gray-50/60 dark:bg-gray-900/30 hover:bg-white dark:hover:bg-gray-800/80 cursor-pointer transition select-none">
                                    <input type="checkbox" name="house_rules[]" value="{{ $rKey }}" {{ in_array($rKey, $selectedDefaultRules ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 font-sans">{{ $rTitle }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- ۲. قوانین اختصاصی و سفارشی اقامتگاه --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    قوانین اختصاصی و سفارشی اقامتگاه
                                </h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">هرگونه شرط، محدودیت یا قانون خاص ملک خود را به صورت دلخواه اضافه کنید.</p>
                            </div>
                            <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold font-sans self-start sm:self-auto bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-1 rounded-lg" x-text="`${customRules.length} قانون اختصاصی`"></span>
                        </div>

                        {{-- فیلد افزودن قانون سفارشی جدید --}}
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input type="text" 
                                       x-model="newRuleText" 
                                       @keydown.enter.prevent="addCustomRule()"
                                       placeholder="متن قانون سفارشی را بنویسید (مثلاً: استفاده از استخر بعد از ساعت ۲۳ ممنوع است)..." 
                                       class="{{ $inputClass }} text-xs py-2.5 pl-4 font-sans">
                            </div>
                            <button type="button" 
                                    @click="addCustomRule()"
                                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-indigo-500/20 whitespace-nowrap active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                افزودن قانون
                            </button>
                        </div>

                        {{-- لیست قوانین سفارشی افزوده شده --}}
                        <div class="space-y-2.5" x-show="customRules.length > 0">
                            <template x-for="(rule, index) in customRules" :key="index">
                                <div class="flex items-center justify-between gap-3 p-3 rounded-2xl border border-gray-200/80 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 hover:bg-white dark:hover:bg-gray-800 transition">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <span class="w-5 h-5 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-[11px] font-bold flex-shrink-0 font-sans" x-text="index + 1"></span>
                                        <input type="hidden" name="custom_rules[]" :value="rule">
                                        <span class="text-xs text-gray-800 dark:text-gray-200 font-sans break-words leading-relaxed" x-text="rule"></span>
                                    </div>
                                    <button type="button" 
                                            @click="removeCustomRule(index)"
                                            class="p-1.5 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition flex-shrink-0"
                                            title="حذف این قانون">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- پیام راهنما در صورت نبود قانون سفارشی --}}
                        <div x-show="customRules.length === 0" class="text-center py-3.5 px-4 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700/80 text-[11px] text-gray-400 dark:text-gray-500 font-sans">
                            هنوز قانون سفارشی ثبت نشده است. می‌توانید قوانین اختصاصی اقامتگاه را از کادر بالا اضافه نمایید.
                        </div>
                    </div>
                </div>

            </div>

            {{-- ستون کناری: ظرفیت مهمان، ورود/خروج و ثبت (۴ ستون) --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- کارت ۴: ظرفیت پذیرش مهمان --}}
                <div class="{{ $cardClass }} p-6 space-y-5">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">ظرفیت پذیرش مهمان</h2>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">تعداد مجاز مسافران</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">ظرفیت استاندارد <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="base_guests" value="{{ old('base_guests', $rentalConfig->base_guests ?? 2) }}" min="1" max="50" required class="{{ $inputClass }} text-center font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">نفر</span>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">حداکثر با اضافه <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="max_guests" value="{{ old('max_guests', $rentalConfig->max_guests ?? 4) }}" min="1" max="100" required class="{{ $inputClass }} text-center font-sans">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-gray-400 font-sans">نفر</span>
                            </div>
                        </div>
                    </div>

                    {{-- راهنمای اتصال به تب‌های امکانات و مشخصات --}}
                    <div class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-100 dark:bg-indigo-950/20 dark:border-indigo-900/40 space-y-2.5">
                        <div class="flex items-start gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-indigo-950 dark:text-indigo-200">اتاق‌ها، تخت‌ها و امکانات</p>
                                <p class="text-[11px] text-indigo-700 dark:text-indigo-400 leading-relaxed font-sans">تعداد اتاق، تخت‌ها، حمام، سرویس بهداشتی و کلیه امکانات در تب‌های «اطلاعات تکمیلی» و «امکانات» ویرایش ملک مدیریت می‌شوند.</p>
                            </div>
                        </div>
                        <a href="{{ route('user.properties.edit', $property) }}" target="_blank" class="w-full py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm shadow-indigo-500/20 transition flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            ویرایش امکانات و مشخصات
                        </a>
                    </div>
                </div>

                {{-- کارت ۵: زمان‌بندی ورود و خروج و رزرو فوری --}}
                <div class="{{ $cardClass }} p-6 space-y-5">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">ساعت ورود و خروج</h2>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">ساعت تحویل کلید به مهمان</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="{{ $labelClass }}">ساعت ورود</label>
                                <div class="relative">
                                    <input type="text" 
                                           name="check_in_time" 
                                           data-jdp-only-time
                                           placeholder="14:00"
                                           value="{{ old('check_in_time', substr($rentalConfig->check_in_time ?? '14:00', 0, 5)) }}" 
                                           class="{{ $inputClass }} text-center dir-ltr font-sans cursor-pointer pl-9">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
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
                                           class="{{ $inputClass }} text-center dir-ltr font-sans cursor-pointer pl-9">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 cursor-pointer">
                                <input type="checkbox" name="instant_booking" value="1" {{ ($rentalConfig->instant_booking ?? false) ? 'checked' : '' }} class="{{ $checkboxClass }} mt-0.5">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">رزرو قطعی آنی (Instant)</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">مهمان می‌تواند بدون نیاز به تایید تلفنی مستقیم رزرو را ثبت کند.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- کارت ۶: عملیات ذخیره سازی و پیوند تقویم (چسبان در اسکرول) --}}
                <div class="{{ $cardClass }} p-5 space-y-4 sticky top-6 z-20 shadow-md backdrop-blur-md">
                    <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        ذخیره تنظیمات اقامتگاه
                    </button>

                    <a href="{{ route('user.properties.rental.calendar', $property) }}" class="w-full py-3 px-4 rounded-2xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700/60 transition flex items-center justify-center gap-2 text-center">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        مدیریت تقویم و قیمت روزها
                    </a>
                </div>

            </div>

        </div>

    </form>
</div>

<script>
    function rentalConfigForm() {
        return {
            isSubmitting: false,
            pricePerNight: '{{ old('price_per_night', $rentalConfig->price_per_night ? number_format($rentalConfig->price_per_night) : '') }}',
            priceWeekend: '{{ old('price_weekend', $rentalConfig->price_weekend ? number_format($rentalConfig->price_weekend) : '') }}',
            priceHoliday: '{{ old('price_holiday', $rentalConfig->price_holiday ? number_format($rentalConfig->price_holiday) : '') }}',
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
            }
        }
    }
</script>
@endsection
