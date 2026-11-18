@extends('layouts.web')

@section('title', 'پرتال جذب و ثبت‌نام تکنسین‌های فنی | شروع همکاری')

@section('content')
<div class="max-w-4xl mx-auto w-full flex-grow flex flex-col pt-4 sm:pt-8 pb-20 sm:pb-16 px-4 sm:px-6 font-sans space-y-12 sm:space-y-14"
     x-data="{
        heroPhone: '',
        calcSpecialty: 'package',
        calcRate: 750000,
        calcDays: 5,
        calcOrders: 3,
        techSharePct: {{ $technicianPayoutShare ?? 85 }},
        get monthlyIncome() {
            const dailyNet = this.calcOrders * this.calcRate * (this.techSharePct / 100);
            return Math.round(dailyNet * this.calcDays * 4.2);
        },
        formatMoney(amount) {
            return new Intl.NumberFormat('fa-IR').format(amount);
        },
        startRegistration() {
            let phone = this.heroPhone ? this.heroPhone.trim() : '';
            let url = '{{ route('fieldservice.technician') }}';
            if (phone.length >= 10) {
                url += '?phone=' + encodeURIComponent(phone);
            }
            window.location.href = url;
        }
     }">

    {{-- ============================================================== --}}
    {{-- ۱. بخش هیرو: متمرکز، فلت، بدون ماک‌آپ اضافی و با ریتم عمودی استاندارد --}}
    {{-- ============================================================== --}}
    <section class="text-right space-y-5 pt-2 sm:pt-4">

        {{-- برچسب ساده وضعیت --}}
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold font-sans">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>پذیرش فعال تکنسین و استادکار در سراسر مازندران</span>
        </div>

        {{-- تیتر اصلی --}}
        <div class="space-y-2">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-white leading-snug tracking-tight font-sans">
                درآمد روزانه، تسویه حساب مستقیم و بدون سفته
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-sans max-w-2xl">
                سفارش‌های نصب و عیب‌یابی را در محدوده دلخواه خود دریافت کنید. {{ $technicianPayoutShare ?? 85 }}٪ از مبلغ کل فاکتور مستقیماً متعلق به شماست و در کمتر از ۱ ساعت پس از اتمام کار به شبای شما واریز می‌شود.
            </p>
        </div>

        {{-- کارت فرم ثبت‌نام سریع با شماره موبایل (طراحی فلت، تمیز و اپلیکیشن‌محور) --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3">
            <label for="technician-phone-input" class="block text-xs font-bold text-slate-700 dark:text-slate-300 font-sans">
                برای شروع همکاری، شماره همراه خود را وارد کنید:
            </label>

            <form action="{{ route('fieldservice.technician') }}" method="GET" @submit.prevent="startRegistration()" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <div class="relative flex-1">
                    <input id="technician-phone-input"
                           type="tel"
                           name="phone"
                           x-model="heroPhone"
                           placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                           dir="ltr"
                           maxlength="11"
                           class="w-full h-12 px-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 font-sans transition">
                </div>

                <button type="submit"
                        class="h-12 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] text-white text-xs sm:text-sm font-bold transition font-sans cursor-pointer flex items-center justify-center gap-2 shrink-0">
                    <span>ثبت‌نام آنلاین تکنسین</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </button>
            </form>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-[11px] text-slate-500 dark:text-slate-400 font-sans">
                <span>ثبت‌نام رایگان • تایید هویت آنلاین در کمتر از ۲ دقیقه</span>
                <a href="{{ route('fieldservice.technician') }}"
                   class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                    قبلاً ثبت‌نام کرده‌اید؟ ورود همکاران ←
                </a>
            </div>
        </div>

        {{-- ۴ اصل کلیدی همکاری (فلت و بدون تزیینات فانتزی) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-right space-y-0.5">
                <span class="block text-slate-900 dark:text-white font-bold text-xs font-sans">تسویه روزانه</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-sans">واریز مستقیم به شماره شبا</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-right space-y-0.5">
                <span class="block text-slate-900 dark:text-white font-bold text-xs font-sans">بدون سفته</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-sans">عدم دریافت وجه یا ضمانت مالی</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-right space-y-0.5">
                <span class="block text-slate-900 dark:text-white font-bold text-xs font-sans">ساعات دلخواه</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-sans">فعالیت تمام‌وقت یا پاره‌وقت</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-right space-y-0.5">
                <span class="block text-slate-900 dark:text-white font-bold text-xs font-sans">پشتیبانی فنی</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-sans">تامین قطعات و مشاوره عیب‌یابی</span>
            </div>
        </div>

    </section>

    {{-- ============================================================== --}}
    {{-- ۲. محاسبه‌گر سریع و فلت درآمد تکنسین                        --}}
    {{-- ============================================================== --}}
    <section id="calculator" class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-6 font-sans">

        <div class="space-y-1 text-right">
            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white font-sans">
                محاسبه‌گر تخمینی درآمد ماهانه
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-sans">
                تخصص، تعداد روزهای کاری و سفارش‌های روزانه خود را انتخاب کنید:
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">

            {{-- فیلدهای ورودی اسلایدرها --}}
            <div class="md:col-span-7 space-y-5">

                {{-- انتخاب تخصص --}}
                <div class="space-y-2">
                    <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 font-sans">تخصص اصلی شما:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                        <button type="button"
                                @click="calcSpecialty = 'package'; calcRate = 750000;"
                                :class="calcSpecialty === 'package' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
                                class="p-2.5 rounded-xl border text-xs font-bold transition text-center font-sans cursor-pointer">
                            پکیج گرمایشی
                        </button>
                        <button type="button"
                                @click="calcSpecialty = 'split_ac'; calcRate = 950000;"
                                :class="calcSpecialty === 'split_ac' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
                                class="p-2.5 rounded-xl border text-xs font-bold transition text-center font-sans cursor-pointer">
                            کولر گازی
                        </button>
                        <button type="button"
                                @click="calcSpecialty = 'chiller'; calcRate = 1600000;"
                                :class="calcSpecialty === 'chiller' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
                                class="p-2.5 rounded-xl border text-xs font-bold transition text-center font-sans cursor-pointer">
                            چیلر و موتورخانه
                        </button>
                        <button type="button"
                                @click="calcSpecialty = 'electric'; calcRate = 600000;"
                                :class="calcSpecialty === 'electric' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
                                class="p-2.5 rounded-xl border text-xs font-bold transition text-center font-sans cursor-pointer">
                            برق و تاسیسات
                        </button>
                    </div>
                </div>

                {{-- اسلایدر روزهای کاری --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs font-bold font-sans">
                        <span class="text-slate-700 dark:text-slate-300">روزهای فعالیت در هفته:</span>
                        <span class="text-indigo-600 dark:text-indigo-400" x-text="calcDays + ' روز'"></span>
                    </div>
                    <input type="range" min="2" max="7" step="1" x-model.number="calcDays"
                           class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                </div>

                {{-- اسلایدر سفارش در روز --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs font-bold font-sans">
                        <span class="text-slate-700 dark:text-slate-300">تعداد سفارش در روز:</span>
                        <span class="text-indigo-600 dark:text-indigo-400" x-text="calcOrders + ' سفارش'"></span>
                    </div>
                    <input type="range" min="1" max="6" step="1" x-model.number="calcOrders"
                           class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                </div>

            </div>

            {{-- نمایش نتیجه محاسبه --}}
            <div class="md:col-span-5 p-5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center space-y-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 font-sans block">
                    درآمد تخمینی ماهانه خالص شما:
                </span>
                <div class="flex items-baseline justify-center gap-1.5">
                    <span class="text-3xl font-black text-slate-900 dark:text-white font-sans" x-text="formatMoney(monthlyIncome)"></span>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 font-sans">تومان</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-sans leading-relaxed">
                    محاسبه شده بر مبنای سهم {{ $technicianPayoutShare ?? 85 }}٪ تکنسین. هزینه قطعات یدکی به این مبلغ اضافه می‌شود.
                </p>
                <a href="{{ route('fieldservice.technician') }}"
                   class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition font-sans flex items-center justify-center">
                    شروع همکاری با این درآمد
                </a>
            </div>

        </div>

    </section>

    {{-- ============================================================== --}}
    {{-- ۳. مراحل ۴ گانه عضویت (ساده، سریع و بدون ابهام)               --}}
    {{-- ============================================================== --}}
    <section id="steps" class="space-y-5 font-sans">
        <div class="text-right space-y-1">
            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white font-sans">
                مراحل شروع همکاری
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-sans">
                از ثبت‌نام تا دریافت اولین سفارش، تمام مراحل آنلاین انجام می‌شود:
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
                <span class="inline-block w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs leading-6 text-center font-sans">۱</span>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white font-sans">ثبت شماره همراه</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                    شماره موبایل خود را وارد کرده و کد تایید پیامکی را ثبت می‌کنید.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
                <span class="inline-block w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs leading-6 text-center font-sans">۲</span>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white font-sans">تعیین تخصص‌ها</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                    مهارت‌های فنی و شهر محل فعالیت خود را انتخاب می‌نمایید.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
                <span class="inline-block w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs leading-6 text-center font-sans">۳</span>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white font-sans">امضای آنلاین قرارداد</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                    قرارداد همکاری هوشمند را آنلاین و بدون نیاز به مراجعه حضوری امضا می‌کنید.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
                <span class="inline-block w-6 h-6 rounded-lg bg-emerald-500 text-white font-bold text-xs leading-6 text-center font-sans">۴</span>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white font-sans">دریافت سفارش و تسویه</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                    در پرتال آنلاین می‌شوید، سفارش‌های اطراف را قبول کرده و روزانه تسویه می‌شوید.
                </p>
            </div>
        </div>
    </section>

    {{-- ============================================================== --}}
    {{-- ۴. تخصص‌ها و مدارک مورد نیاز (خلاصه و یکپارچه در یک بخش)      --}}
    {{-- ============================================================== --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4 font-sans">

        {{-- تخصص‌های فعال --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white font-sans">تخصص‌های دارای تقاضای فوری</h3>
            <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300 font-sans">
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    <span>تعمیر، نصب و سرویس انواع پکیج شوفاژ دیواری و زمینی</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    <span>نصب، لوله‌کشی و شارژ گاز کولر گازی، اسپلیت و داکت اسپلیت</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    <span>تعمیرات و نگهداری تخصصی چیلر، فن‌کویل و موتورخانه مرکزی</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    <span>سردخانه‌های صنعتی، تجاری و سردخانه‌های متحرک</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    <span>سیم‌کشی، تابلو برق و عیب‌یابی برق تاسیسات ساختمان</span>
                </li>
            </ul>
        </div>

        {{-- مدارک و شرایط اولیه --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white font-sans">مدارک و شرایط پذیرش</h3>
            <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300 font-sans">
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>کارت ملی یا شناسنامه معتبر</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>ابزارآلات کار متناسب با حوزه تخصصی</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>مدرک فنی‌حرفه‌ای یا حداقل ۲ سال سابقه تجربی کارگاهی</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>گواهی عدم سوء پیشینه یا تعهدنامه رسمی</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>وسیله نقلیه (خودرو یا موتور) جهت ایاب‌وذهاب</span>
                </li>
            </ul>
        </div>

    </section>

    {{-- ============================================================== --}}
    {{-- ۵. پاسخ به سوالات ضروری همکاران (FAQ فلت)                     --}}
    {{-- ============================================================== --}}
    <section id="faq" class="space-y-4 font-sans" x-data="{ activeFaq: null }">
        <div class="text-right space-y-1">
            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white font-sans">
                سوالات متداول
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-sans">
                پاسخ‌های شفاف به سوالات پرتکرار استادکاران:
            </p>
        </div>

        <div class="space-y-2">

            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                <button type="button" @click="activeFaq = (activeFaq === 1 ? null : 1)"
                        class="w-full p-4 text-right flex items-center justify-between gap-4 font-bold text-xs sm:text-sm text-slate-900 dark:text-white font-sans cursor-pointer">
                    <span>تسویه حساب چگونه و چه زمانی انجام می‌شود؟</span>
                    <span class="text-slate-400 font-bold" x-text="activeFaq === 1 ? '−' : '+'">+</span>
                </button>
                <div x-show="activeFaq === 1" x-collapse class="px-4 pb-4 text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans border-t border-slate-100 dark:border-slate-800 pt-3">
                    پس از اعلام پایان ماموریت و ثبت فاکتور توسط شما در پرتال، سهم ۸۵٪ دستمزد به کیف پولتان افزوده شده و همان روز به صورت پایا به شماره شبای بانکی شما واریز می‌گردد.
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                <button type="button" @click="activeFaq = (activeFaq === 2 ? null : 2)"
                        class="w-full p-4 text-right flex items-center justify-between gap-4 font-bold text-xs sm:text-sm text-slate-900 dark:text-white font-sans cursor-pointer">
                    <span>آیا برای شروع همکاری نیاز به ارائه سفته یا ضمانت مالی است؟</span>
                    <span class="text-slate-400 font-bold" x-text="activeFaq === 2 ? '−' : '+'">+</span>
                </button>
                <div x-show="activeFaq === 2" x-collapse class="px-4 pb-4 text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans border-t border-slate-100 dark:border-slate-800 pt-3">
                    خیر، هیچ‌گونه وجه نقد یا سفته ضمانتی برای ثبت‌نام و پذیرش دریافت نمی‌شود. احراز هویت اولیه و امضای آنلاین قرارداد همکاری هوشمند ملاک شروع کار است.
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                <button type="button" @click="activeFaq = (activeFaq === 3 ? null : 3)"
                        class="w-full p-4 text-right flex items-center justify-between gap-4 font-bold text-xs sm:text-sm text-slate-900 dark:text-white font-sans cursor-pointer">
                    <span>آیا امکان فعالیت پاره‌وقت وجود دارد؟</span>
                    <span class="text-slate-400 font-bold" x-text="activeFaq === 3 ? '−' : '+'">+</span>
                </button>
                <div x-show="activeFaq === 3" x-collapse class="px-4 pb-4 text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans border-t border-slate-100 dark:border-slate-800 pt-3">
                    بله، هیچ ساعت کاری اجباری وجود ندارد. هر زمان که وضعیت خود را در پرتال روی «آماده به خدمت» بگذارید سفارش‌های اطراف را دریافت می‌کنید.
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                <button type="button" @click="activeFaq = (activeFaq === 4 ? null : 4)"
                        class="w-full p-4 text-right flex items-center justify-between gap-4 font-bold text-xs sm:text-sm text-slate-900 dark:text-white font-sans cursor-pointer">
                    <span>هزینه قطعات یدکی چگونه محاسبه و دریافت می‌شود؟</span>
                    <span class="text-slate-400 font-bold" x-text="activeFaq === 4 ? '−' : '+'">+</span>
                </button>
                <div x-show="activeFaq === 4" x-collapse class="px-4 pb-4 text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans border-t border-slate-100 dark:border-slate-800 pt-3">
                    هزینه قطعات تعویضی به طور کامل و جداگانه در فاکتور مشتری ثبت می‌شود و سامانه هیچ کمیسیونی از هزینه قطعه کسر نمی‌کند.
                </div>
            </div>

        </div>
    </section>

    {{-- ============================================================== --}}
    {{-- ۶. کال‌تو‌اکشن نهایی شروع همکاری                                --}}
    {{-- ============================================================== --}}
    <section class="p-6 sm:p-8 rounded-2xl bg-slate-900 dark:bg-slate-950 text-white text-center space-y-4 font-sans">
        <div class="space-y-1.5 max-w-xl mx-auto">
            <h3 class="text-lg sm:text-xl font-black font-sans">
                شروع فعالیت در ناوگان فنی
            </h3>
            <p class="text-xs text-slate-300 font-sans leading-relaxed">
                شماره همراه خود را در بالای صفحه وارد کنید یا با واحد جذب همکاران تماس بگیرید.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 pt-1">
            <a href="{{ route('fieldservice.technician') }}"
               class="h-11 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition font-sans flex items-center justify-center">
                ثبت‌نام آنلاین تکنسین
            </a>
            @if(!empty($supportPhone))
            <a href="tel:{{ $supportPhone }}"
               class="h-11 px-5 rounded-xl border border-slate-700 text-slate-300 hover:text-white text-xs font-bold transition font-sans flex items-center justify-center gap-2">
                <span>تماس با امور تکنسین‌ها: {{ $supportPhone }}</span>
            </a>
            @endif
        </div>
    </section>

    {{-- ============================================================== --}}
    {{-- ۷. نوار چسبان پایین مخصوص موبایل (Native App Action Bar)      --}}
    {{-- ============================================================== --}}
    <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 p-3 pb-safe font-sans">
        <div class="max-w-md mx-auto flex items-center gap-2">
            <a href="{{ route('fieldservice.technician') }}"
               class="flex-1 h-12 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition font-sans">
                <span>ثبت‌نام آنلاین تکنسین</span>
            </a>
            <a href="{{ route('fieldservice.technician') }}"
               class="h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center justify-center transition font-sans">
                <span>ورود</span>
            </a>
        </div>
    </div>

</div>
@endsection
