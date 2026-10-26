@extends('layouts.web')

@section('title', 'سامانه هوشمند اعزام تکنسین، عیب‌یابی و خدمات فنی در محل')

@section('content')
{{-- کانتینر اصلی صفحه با ساختار اپلیکیشن‌محور، فواصل استاندارد و پدینگ هماهنگ در موبایل و دسکتاپ --}}
<div class="max-w-7xl mx-auto w-full flex-grow flex flex-col pt-6 sm:pt-10 lg:pt-12 pb-12 sm:pb-14 md:pb-16 px-4 sm:px-6 lg:px-8 font-sans space-y-12 sm:space-y-20 lg:space-y-28">

    {{-- ============================================================== --}}
    {{-- ۱. بخش هیرو (Hero Section) با طراحی اپلیکیشن و اینترفیس زنده --}}
    {{-- ============================================================== --}}
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center pt-2 lg:pt-6">
        
        {{-- ستون راست: متن، تیتر، بج اعتماد و انتخاب سریع خدمت --}}
        <div class="lg:col-span-7 space-y-6 text-right">
            
            {{-- بج اعتماد اجتماعی رسمی (Social Proof Badge) بدون ایموجی --}}
            <div class="inline-flex items-center gap-3 p-1.5 pr-3.5 pl-3 rounded-full bg-slate-50 dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800 shadow-sm font-sans">
                <div class="flex items-center gap-1 text-amber-500 font-extrabold text-xs font-sans">
                    <svg class="w-4 h-4 fill-amber-400 text-amber-400" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span>۴.۹</span>
                </div>
                <div class="h-3 w-px bg-slate-200 dark:bg-slate-800"></div>
                <div class="flex -space-x-1.5 space-x-reverse">
                    <span class="inline-block w-5 h-5 rounded-full bg-indigo-600 text-white text-[9px] font-bold text-center leading-5 ring-2 ring-white dark:ring-slate-900 font-sans">م</span>
                    <span class="inline-block w-5 h-5 rounded-full bg-cyan-600 text-white text-[9px] font-bold text-center leading-5 ring-2 ring-white dark:ring-slate-900 font-sans">س</span>
                    <span class="inline-block w-5 h-5 rounded-full bg-emerald-600 text-white text-[9px] font-bold text-center leading-5 ring-2 ring-white dark:ring-slate-900 font-sans">ع</span>
                </div>
                <span class="text-xs text-slate-600 dark:text-slate-300 font-medium font-sans">
                    بیش از {{ number_format(max($completedOrdersCount ?? 0, 10000)) }} سفارش موفق در مازندران
                </span>
            </div>

            {{-- تیتر اصلی با سایز بهینه‌شده در موبایل (یک درجه بزرگتر) و دسکتاپ --}}
            <h1 class="text-2xl sm:text-2xl lg:text-3xl xl:text-[2.1rem] font-black text-slate-900 dark:text-white leading-[1.35] sm:leading-[1.35] tracking-tight font-sans">
                اعزام نزدیک‌ترین تکنسین متخصص به محل شما؛ <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-indigo-700 to-cyan-600 dark:from-indigo-400 dark:to-cyan-400">
                    با نرخ مصوب اتحادیه و ضمانت کتبی ۹۰ روزه
                </span>
            </h1>

            {{-- متن توضیحی روان و ساختاریافته --}}
            <p class="text-xs sm:text-sm md:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl font-sans">
                سامانه هوشمند عیب‌یابی و تعمیرات پکیج شوفاژ، کولر گازی، چیلر و تاسیسات مرکزی در استان مازندران. امنیت مالی شما با سیستم پرداخت امن امانی (Escrow) تضمین شده و هزینه تنها پس از تست کامل کارکرد آزاد می‌شود.
            </p>

            {{-- نوار ابزار انتخاب سریع خدمت در هیرو (App-feel Quick Selector) --}}
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl sm:rounded-3xl border border-slate-200 dark:border-slate-800 shadow-lg shadow-indigo-950/5 space-y-3 font-sans">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    
                    {{-- انتخاب نوع دستگاه --}}
                    <div class="sm:col-span-5 space-y-1.5">
                        <label for="hero-service-type" class="block text-xs font-bold text-slate-600 dark:text-slate-300 pr-1 font-sans">دستگاه یا سیستم مورد نظر:</label>
                        <select id="hero-service-type" class="w-full h-12 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3 text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            <option value="package">پکیج شوفاژ دیواری و زمینی</option>
                            <option value="split_ac">کولر گازی و اسپلیت</option>
                            <option value="chiller">چیلر ساختمانی و تجاری</option>
                            <option value="boiler_room">موتورخانه و تاسیسات مرکزی</option>
                            <option value="cold_storage">سردخانه صنعتی و تجاری</option>
                            <option value="ducted_split">داکت اسپلیت اینورتر</option>
                        </select>
                    </div>

                    {{-- انتخاب شهر یا منطقه --}}
                    <div class="sm:col-span-4 space-y-1.5">
                        <label for="hero-city" class="block text-xs font-bold text-slate-600 dark:text-slate-300 pr-1 font-sans">محدوده اعزام در مازندران:</label>
                        <select id="hero-city" class="w-full h-12 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3 text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            <option value="babol">بابل و حومه</option>
                            <option value="sari">ساری و میاندورود</option>
                            <option value="amol">آمل و دشت‌سر</option>
                            <option value="qaemshahr">قائم‌شهر و سوادکوه</option>
                            <option value="babolsar">بابلسر و فریدونکنار</option>
                        </select>
                    </div>

                    {{-- دکمه استعلام و اعزام --}}
                    <div class="sm:col-span-3">
                        <button onclick="window.location.href='{{ route('fieldservice.customer') }}?type=' + document.getElementById('hero-service-type').value + '&city=' + document.getElementById('hero-city').value"
                                class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 font-sans cursor-pointer">
                            <span>استعلام و اعزام</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </button>
                    </div>

                </div>

                {{-- خط تکمیلی اطلاعات زیر فرم --}}
                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-[11px] text-slate-500 dark:text-slate-400 font-sans">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ $activeTechCount > 0 ? $activeTechCount . ' تکنسین آنلاین و آماده‌به‌خدمت' : 'اعزام فوری زیر ۳۰ دقیقه' }}
                    </span>
                    <a href="{{ route('fieldservice.technician') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        تکنسین هستید؟ ورود به پرتال همکاران ←
                    </a>
                </div>
            </div>

            {{-- چیپس‌های دسترسی سریع در موبایل (Mobile Horizontal Snap Chips) --}}
            <div class="sm:hidden pt-1">
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 mb-2 font-sans">دسترسی سریع به خدمات پرتقاضا:</p>
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs font-bold font-sans">
                    <a href="{{ route('fieldservice.customer') }}?type=package" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 shrink-0 border border-slate-200 dark:border-slate-700">پکیج شوفاژ</a>
                    <a href="{{ route('fieldservice.customer') }}?type=split_ac" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 shrink-0 border border-slate-200 dark:border-slate-700">کولر گازی</a>
                    <a href="{{ route('fieldservice.customer') }}?type=chiller" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 shrink-0 border border-slate-200 dark:border-slate-700">چیلر ساختمانی</a>
                    <a href="{{ route('fieldservice.customer') }}?type=boiler_room" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 shrink-0 border border-slate-200 dark:border-slate-700">موتورخانه</a>
                </div>
            </div>

        </div>

        {{-- ستون چپ: کارت شبیه‌ساز واقعی اپلیکیشن موبایل (Real App UI Simulation) --}}
        <div class="lg:col-span-5">
            <div class="relative max-w-md mx-auto">
                {{-- افکت نورانی ملایم در پشت موکاپ --}}
                <div class="absolute -inset-2 bg-gradient-to-tr from-indigo-500/20 via-cyan-500/10 to-indigo-600/20 rounded-[2.5rem] blur-xl pointer-events-none"></div>

                {{-- کادر اصلی گوشی / اپلیکیشن پیاده‌سازی شده --}}
                <div class="relative bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-[2rem] p-5 shadow-2xl space-y-4 font-sans">
                    
                    {{-- هدر وضعیت اپلیکیشن --}}
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-sans">سفارش فعال #۲۸۱۴</span>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white font-sans">اعزام زنده به میدان فردوس، بابل</h4>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 font-sans">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            تکنسین در مسیر
                        </span>
                    </div>

                    {{-- خط زمان وضعیت خدمت (Live Stepper) --}}
                    <div class="bg-slate-50 dark:bg-slate-950/60 rounded-xl p-3 space-y-2 border border-slate-100 dark:border-slate-800/60 font-sans">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">تخمین زمان رسیدن به محل:</span>
                            <span class="font-extrabold text-indigo-600 dark:text-indigo-400 font-sans">۱۴ دقیقه</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-500 to-cyan-500 h-1.5 rounded-full w-2/3"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>ثبت درخواست: ۱۰:۱۵</span>
                            <span>پذیرش تکنسین: ۱۰:۱۸</span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">حضور در محل: ۱۰:۳۲</span>
                        </div>
                    </div>

                    {{-- کارت مشخصات تکنسین تخصیص‌داده‌شده --}}
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-800 text-white font-bold text-sm flex items-center justify-center font-sans shadow-md shadow-indigo-600/20">
                                ق
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h5 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white font-sans">مهندس علی قاسمی</h5>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 font-sans">
                                        تکنسین ارشد
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-sans">شماره مهارت: ۹۸/۳۰۲ • عدم سوءپیشینه ✓</p>
                            </div>
                        </div>
                        <div class="text-left font-sans">
                            <div class="flex items-center gap-1 text-amber-500 text-xs font-bold font-sans">
                                <span>★</span>
                                <span>۴.۹۵</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-sans">۱۹۳ سفارش</span>
                        </div>
                    </div>

                    {{-- ریزفاکتور امن امانی (Escrow Held Preview) --}}
                    <div class="p-3 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 space-y-1.5 text-xs font-sans">
                        <div class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                            <span>تعرفه کارشناسی و ایاب‌وذهاب:</span>
                            <span class="font-bold font-sans">{{ number_format($baseTravelFee ?? 60000) }} تومان</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px]">
                            <span>وضعیت پرداخت وجه:</span>
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold">قفل در حساب امانی تا رضایت مشتری</span>
                        </div>
                    </div>

                    {{-- دکمه پیگیری سفارش --}}
                    <a href="{{ route('fieldservice.customer') }}#lookup"
                       class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white font-bold text-xs text-center flex items-center justify-center gap-2 transition-all font-sans cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>پیگیری آنلاین سفارشات با کد پیگیری</span>
                    </a>

                </div>
            </div>
        </div>

    </section>

    {{-- ============================================================== --}}
    {{-- ۲. نوار ارزش‌های متمایز نصاب‌باشی (تضمین‌های چهارگانه بدون ایموجی) --}}
    {{-- ============================================================== --}}
    <section class="space-y-8 sm:space-y-10">
        <div class="text-center space-y-2">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white font-sans">
                چرا خانواده‌ها و مدیران مجتمع، <span class="text-indigo-600 dark:text-indigo-400">نصاب‌باشی</span> را انتخاب می‌کنند؟
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-sans">
                چهار ستون استاندارد در ارائه خدمات فنی در محل
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- کارت ۱: اعزام راداری --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm hover:border-indigo-500/50 transition-all flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans">اعزام راداری زیر ۳۰ دقیقه</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                        موقعیت‌یابی نزدیک‌ترین تکنسین مجاز در محدوده با حداقل زمان انتظار.
                    </p>
                </div>
            </div>

            {{-- کارت ۲: سیستم پرداخت امن امانی --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm hover:border-indigo-500/50 transition-all flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans">پرداخت امن امانی (Escrow)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                        آزادسازی وجه فاکتور تنها پس از تست کامل دستگاه و رضایت ۱۰۰٪ شما.
                    </p>
                </div>
            </div>

            {{-- کارت ۳: تایید صلاحیت تکنسین‌ها --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm hover:border-indigo-500/50 transition-all flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans">تکنسین‌های تاییدصلاحیت‌شده</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                        بررسی گواهی عدم سوءپیشینه، مدارک فنی‌وحرفه‌ای و تایید هویت حضوری.
                    </p>
                </div>
            </div>

            {{-- کارت ۴: ضمانت ۹۰ روزه کتبی --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm hover:border-indigo-500/50 transition-all flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans">ضمانت کتبی ۹۰ روزه</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                        صدور فاکتور دیجیتال و پوشش گارانتی رایگان مجدد در صورت عیب تکراری.
                    </p>
                </div>
            </div>

        </div>
    </section>

    {{-- ============================================================== --}}
    {{-- ۳. هر کاربر، یک تجربه منحصر به فرد (۳ پرتال اختصاصی و تفکیک‌شده) --}}
    {{-- ============================================================== --}}
    <section id="roles" class="space-y-10 sm:space-y-12">
        
        <div class="text-center max-w-2xl mx-auto space-y-2.5">
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider font-sans">تفکیک نقش‌ها و خدمات متمرکز</span>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-sans">
                هر کاربر، <span class="text-indigo-600 dark:text-indigo-400">یک تجربه منحصر به فرد</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-sans">
                پلتفرم اختصاصی نصاب‌باشی مسیر مستقلی برای نیاز هر گروه از مخاطبان فراهم کرده است
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- کارت ۱: پرتال مشتریان (ثبت درخواست و پیگیری) --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-sm hover:shadow-xl hover:border-indigo-500/50 transition-all flex flex-col justify-between text-right space-y-6">
                
                <div class="space-y-5">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-black text-slate-900 dark:text-white font-sans">مشتریان و متقاضیان</h3>
                            <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-full">درخواست خدمت</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">تعمیرات و عیب‌یابی فوری تجهیزات سرمایشی و گرمایشی در محل مسکونی یا اداری.</p>
                    </div>

                    <div class="space-y-2 pt-1 font-sans">
                        <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-4 h-4 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>ثبت آنلاین سفارش در کمتر از ۲ دقیقه بدون نیاز به تماس</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-4 h-4 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>ردیابی موقعیت زنده تکنسین روی نقشه تا رسیدن به نشانی</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-4 h-4 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>پرداخت امن امانی با امکان اعتراض و بازگشت وجه</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('fieldservice.customer') }}"
                       class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm text-center flex items-center justify-center gap-2 shadow-md shadow-indigo-600/25 transition-all font-sans cursor-pointer">
                        <span>ورود به پرتال مشتریان (ثبت سفارش)</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                </div>

            </div>

            {{-- کارت ۲: پرتال متخصصان و تکنسین‌ها (همکاران فنی) --}}
            <div class="bg-white dark:bg-slate-900 border-2 border-indigo-500/80 dark:border-indigo-500 rounded-3xl p-6 sm:p-7 shadow-lg shadow-indigo-600/10 hover:shadow-2xl transition-all flex flex-col justify-between text-right space-y-6 relative">
                
                {{-- بج ویژه ناوگان تکنسین‌ها --}}
                <div class="absolute -top-3 left-6 px-3 py-0.5 rounded-full bg-indigo-600 text-white text-[10px] font-black tracking-wide font-sans shadow-md">
                    پیوستن به ناوگان نصاب‌باشی
                </div>

                <div class="space-y-5">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-black text-slate-900 dark:text-white font-sans">متخصصان و تکنسین‌ها</h3>
                            <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-full">ناوگان همکار</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">پلتفرم اختصاصی دریافت مستمر ماموریت‌های فنی، ثبت اجرت و تسویه حساب آنی.</p>
                    </div>

                    <div class="space-y-2 pt-1 font-sans">
                        <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>دریافت هوشمند ماموریت‌های نزدیک به محل سکونت شما</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>تسویه حساب سریع در کیف پول اعتباری با کارمزد منصفانه</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>دسترسی به شبکه قطعات یدکی اصل با تخفیف ویژه همکار</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('fieldservice.technician') }}"
                       class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm text-center flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 transition-all font-sans cursor-pointer">
                        <span>ورود به پرتال تکنسین‌ها</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                </div>

            </div>

            {{-- کارت ۳: پرتال مجتمع‌ها، شرکت‌ها و همکاران B2B --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-sm hover:shadow-xl hover:border-indigo-500/50 transition-all flex flex-col justify-between text-right space-y-6">
                
                <div class="space-y-5">
                    <div class="w-14 h-14 rounded-2xl bg-cyan-50 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-black text-slate-900 dark:text-white font-sans">مجتمع‌ها و سازمان‌ها</h3>
                            <span class="text-[10px] font-bold text-cyan-700 dark:text-cyan-300 bg-cyan-50 dark:bg-cyan-950/60 px-2 py-0.5 rounded-full">قرارداد B2B</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">راهکارهای یکپارچه نگهداری، صدور فاکتور رسمی و تامین قطعات برای ساختمان‌ها.</p>
                    </div>

                    <div class="space-y-2 pt-1 font-sans">
                        <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-4 h-4 rounded-full bg-cyan-100 dark:bg-cyan-950 text-cyan-600 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>قراردادهای دوره‌ای بازدید و نگهداری پیشگیرانه موتورخانه و چیلر</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-4 h-4 rounded-full bg-cyan-100 dark:bg-cyan-950 text-cyan-600 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>پنل اختصاصی ویژه فروشگاه‌ها و تامین‌کنندگان معتبر قطعات</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-4 h-4 rounded-full bg-cyan-100 dark:bg-cyan-950 text-cyan-600 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>صدور صورت‌حساب تجمیعی رسمی همراه با شناسه ملی و گزارش فنی</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('fieldservice.b2b') }}"
                       class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs sm:text-sm text-center flex items-center justify-center gap-2 transition-all font-sans cursor-pointer">
                        <span>مشاهده خدمات B2B و همکاران</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                </div>

            </div>

        </div>

    </section>

    {{-- ============================================================== --}}
    {{-- ۴. تجهیزات و خدمات تخصصی تحت پوشش (#services) --}}
    {{-- ============================================================== --}}
    <section id="services" class="space-y-10 sm:space-y-12">
        
        <div class="text-center max-w-2xl mx-auto space-y-2.5">
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider font-sans">تخصص جامع در سیستم‌های حرارتی و برودتی</span>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-sans">
                دستگاه و خدمات مورد نیاز خود را انتخاب کنید
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-sans">
                تمامی خدمات با فاکتور رسمی، نرخ مصوب اتحادیه و ضمانت کتبی ارائه می‌شوند
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- ۱. پکیج شوفاژ دیواری --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 dark:bg-orange-500/15 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-sans">پکیج شوفاژ دیواری و زمینی</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                            عیب‌یابی تخصصی ارورهای دیجیتال، رفع افت فشار مدار، اسیدشویی مبدل اصلی و ثانویه، تعمیر یا تعویض پمپ و برد (بوتان، ایران‌رادیاتور، لورچ و برندهای خارجی).
                        </p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-sans">
                    <span class="text-emerald-700 dark:text-emerald-400 font-bold">ایاب‌وذهاب: {{ number_format($baseTravelFee ?? 60000) }} تومان</span>
                    <a href="{{ route('fieldservice.customer') }}?type=package" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        ثبت سفارش پکیج ←
                    </a>
                </div>
            </div>

            {{-- ۲. کولر گازی و اسپلیت --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-cyan-50 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-sans">کولر گازی و اسپلیت دیواری</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                            نصب و جابجایی اصولی، وکیوم تخصصی مدار، نشت‌یابی و شارژ کامل گاز مبرد R22 و R410a با گیج دیجیتال، شستشوی سیکل و سرویس فصلی کمپرسور اینورتر.
                        </p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-sans">
                    <span class="text-emerald-700 dark:text-emerald-400 font-bold">ایاب‌وذهاب: {{ number_format($baseTravelFee ?? 60000) }} تومان</span>
                    <a href="{{ route('fieldservice.customer') }}?type=split_ac" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        ثبت سفارش کولر گازی ←
                    </a>
                </div>
            </div>

            {{-- ۳. چیلر تراکمی و جذبی --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-sans">چیلر تراکمی و جذبی ساختمانی</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                            سرویس دوره‌ای کمپرسورهای اسکرو و اسکرال، اسیدشویی و رسوب‌زدایی کندانسور، شارژ روغن برودتی و تنظیم ترموستات آنتی‌فریز مجتمع‌ها.
                        </p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-sans">
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">اعزام مهندس ناظر</span>
                    <a href="{{ route('fieldservice.customer') }}?type=chiller" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        ثبت کارشناسی چیلر ←
                    </a>
                </div>
            </div>

            {{-- ۴. موتورخانه و تاسیسات مرکزی --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-sans">موتورخانه و تاسیسات مرکزی</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                            تنظیم نسبت سوخت و هوای مشعل گازی، کوپلینگ و تعویض واسطه و پروانه پمپ‌های سیرکولاتور، تعویض پره دیگ چدنی و آب‌بندی لوله‌کشی.
                        </p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-sans">
                    <span class="text-emerald-700 dark:text-emerald-400 font-bold">ایاب‌وذهاب: {{ number_format($baseTravelFee ?? 60000) }} تومان</span>
                    <a href="{{ route('fieldservice.customer') }}?type=boiler_room" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        ثبت سفارش موتورخانه ←
                    </a>
                </div>
            </div>

            {{-- ۵. سردخانه صنعتی --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-sans">سردخانه صنعتی و تجاری</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                            عیب‌یابی سردخانه‌های زیر صفر و بالای صفر، تنظیم دیفراست و هیتر اواپراتور، کنترل شیر انبساط ترموستاتیک دانفوس و تابلو برق قدرت.
                        </p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-sans">
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">اورژانسی ۲۴/۷</span>
                    <a href="{{ route('fieldservice.customer') }}?type=cold_storage" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        ثبت سرویس سردخانه ←
                    </a>
                </div>
            </div>

            {{-- ۶. داکت اسپلیت اینورتر --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-sans">داکت اسپلیت اینورتر و کانالی</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                            شستشوی فیلتر و کویل اواپراتور توکار، هواگیری کویل آبگرم گرمایشی، تعویض موتور فن بلوور داخلی و شارژ تکمیلی متراژ بالا با گیج دیجیتال.
                        </p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-sans">
                    <span class="text-emerald-700 dark:text-emerald-400 font-bold">ایاب‌وذهاب: {{ number_format($baseTravelFee ?? 60000) }} تومان</span>
                    <a href="{{ route('fieldservice.customer') }}?type=ducted_split" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        ثبت سفارش داکت اسپلیت ←
                    </a>
                </div>
            </div>

        </div>

    </section>

    {{-- ============================================================== --}}
    {{-- ۵. سیستم پرداخت امن امانی (Escrow Held) و گارانتی کتبی --}}
    {{-- طراحی متقارن، پرکنتراست و بدون فضای خالی ناموزون --}}
    {{-- ============================================================== --}}
    <section id="guarantee" class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 sm:p-10 lg:p-12 shadow-2xl border border-indigo-900/50 relative overflow-hidden">
        
        {{-- افکت نور ملایم پس‌زمینه کارت --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-cyan-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 w-full space-y-8 sm:space-y-10">
            
            {{-- هدر متقارن با چیدمان وسط و خوانایی بالا --}}
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-sans shadow-sm">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    امنیت مالی صددرصدی و ضمانت کیفیت خدمات
                </span>

                <h2 class="text-xl sm:text-2xl lg:text-3xl font-black leading-snug font-sans text-white">
                    سیستم پرداخت امن امانی (Escrow Held) چگونه کار می‌کند؟
                </h2>

                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl mx-auto font-sans">
                    در نصاب‌باشی هیچ وجهی مستقیماً به تکنسین پرداخت نمی‌شود؛ مبلغ در حساب امانی امن سامانه قفل شده و تنها پس از تست کامل عملکرد دستگاه و رضایت ۱۰۰٪ شما آزاد می‌گردد.
                </p>
            </div>

            {{-- دیاگرام ۴ مرحله‌ای پرداخت امانی با کارت‌های متقارن و پرکنتراست --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                
                {{-- مرحله ۱ --}}
                <div class="p-5 rounded-2xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-indigo-500/60 transition-all space-y-3 relative shadow-lg group">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-xl bg-indigo-500/20 border border-indigo-400/40 text-indigo-300 font-black text-xs flex items-center justify-center font-sans shadow-inner">۱</span>
                        <span class="text-[10px] font-bold text-slate-400 font-sans">گام اول</span>
                    </div>
                    <h4 class="font-bold text-sm text-white font-sans">ثبت و کارشناسی</h4>
                    <p class="text-xs text-slate-300 leading-relaxed font-sans">تکنسین دستگاه را عیب‌یابی و فاکتور بر اساس نرخ مصوب را ثبت می‌کند.</p>
                </div>

                {{-- مرحله ۲ --}}
                <div class="p-5 rounded-2xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-indigo-500/60 transition-all space-y-3 relative shadow-lg group">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-xl bg-indigo-500/20 border border-indigo-400/40 text-indigo-300 font-black text-xs flex items-center justify-center font-sans shadow-inner">۲</span>
                        <span class="text-[10px] font-bold text-slate-400 font-sans">گام دوم</span>
                    </div>
                    <h4 class="font-bold text-sm text-white font-sans">قفل وجه در امانت</h4>
                    <p class="text-xs text-slate-300 leading-relaxed font-sans">مبلغ پرداختی شما نزد سامانه به امانت می‌ماند و در دسترس تکنسین نیست.</p>
                </div>

                {{-- مرحله ۳ --}}
                <div class="p-5 rounded-2xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-indigo-500/60 transition-all space-y-3 relative shadow-lg group">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-xl bg-indigo-500/20 border border-indigo-400/40 text-indigo-300 font-black text-xs flex items-center justify-center font-sans shadow-inner">۳</span>
                        <span class="text-[10px] font-bold text-slate-400 font-sans">گام سوم</span>
                    </div>
                    <h4 class="font-bold text-sm text-white font-sans">تست کارکرد و تایید</h4>
                    <p class="text-xs text-slate-300 leading-relaxed font-sans">دستگاه در حضور شما تست شده و پس از رضایت کامل، تایید نهایی را ثبت می‌کنید.</p>
                </div>

                {{-- مرحله ۴ --}}
                <div class="p-5 rounded-2xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-indigo-500/60 transition-all space-y-3 relative shadow-lg group">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-xl bg-indigo-500/20 border border-indigo-400/40 text-indigo-300 font-black text-xs flex items-center justify-center font-sans shadow-inner">۴</span>
                        <span class="text-[10px] font-bold text-slate-400 font-sans">گام نهایی</span>
                    </div>
                    <h4 class="font-bold text-sm text-white font-sans">تسویه و گارانتی</h4>
                    <p class="text-xs text-slate-300 leading-relaxed font-sans">تسویه با تکنسین انجام و گارانتی کتبی ۹۰ روزه بلافاصله فعال می‌گردد.</p>
                </div>

            </div>

            {{-- اکشن پایین به صورت متقارن و در مرکز --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                <a href="{{ route('fieldservice.customer') }}"
                   class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm text-center shadow-lg shadow-indigo-600/30 transition-all font-sans cursor-pointer flex items-center justify-center gap-2">
                    <span>درخواست اعزام تکنسین با ضمانت امانی</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <span class="text-xs text-slate-300 flex items-center gap-2 font-sans">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    پشتیبانی و پیگیری ۲۴ ساعته در سراسر مازندران
                </span>
            </div>

        </div>

    </section>

    {{-- ============================================================== --}}
    {{-- ۶. تجربه کاربران و نظرات واقعی (Social Proof) --}}
    {{-- ============================================================== --}}
    <section class="space-y-10 sm:space-y-12">
        
        <div class="text-center max-w-2xl mx-auto space-y-2.5">
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider font-sans">دیدگاه مشتریان و همکاران</span>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-sans">
                تجربه کاربران با <span class="text-indigo-600 dark:text-indigo-400">نصاب‌باشی</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-sans">
                روایت‌های ثبت‌شده از مشتریان خانگی، مدیران مجتمع‌های ساختمانی و تکنسین‌ها
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- نظر ۱ --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4 text-right flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center gap-1 text-amber-500 text-xs font-bold">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                        «پکیج منزلمان در بابل با خطای افت بار متوقف شد. از طریق سامانه ثبت سفارش کردم؛ تکنسین ظرف ۲۰ دقیقه با قطعه اصلی حاضر شد و بعد از تست آبگرم، پرداخت را انجام دادم.»
                    </p>
                </div>
                <div class="flex items-center gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold text-xs flex items-center justify-center font-sans">
                        م‌ه
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white font-sans">مریم هدایتی</h4>
                        <span class="text-[10px] text-slate-400 font-sans">مشتری خانگی پکیج - بابل</span>
                    </div>
                </div>
            </div>

            {{-- نظر ۲ --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4 text-right flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center gap-1 text-amber-500 text-xs font-bold">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                        «برای نگهداری دوره‌ای چیلر و موتورخانه مجتمع مسکونی قرارداد بستیم. فاکتور رسمی شفاف و عدم دریافت وجه تا قبل از تایید هیئت‌مدیره، خیال ساکنین را آسوده کرد.»
                    </p>
                </div>
                <div class="flex items-center gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-cyan-50 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 font-bold text-xs flex items-center justify-center font-sans">
                        ع‌ن
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white font-sans">مهندس علی نوری</h4>
                        <span class="text-[10px] text-slate-400 font-sans">مدیر مجتمع نیلوفر - ساری</span>
                    </div>
                </div>
            </div>

            {{-- نظر ۳ --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4 text-right flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center gap-1 text-amber-500 text-xs font-bold">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                        «به عنوان تکنسین اسپلیت، ارجاع مستقیم کار در محدوده اطراف منزل و تسویه روزانه در کیف پول باعث شده بدون واسطه و با آرامش خاطر روزانه به امور فنی برسم.»
                    </p>
                </div>
                <div class="flex items-center gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold text-xs flex items-center justify-center font-sans">
                        ر‌ص
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white font-sans">رضا صادقی</h4>
                        <span class="text-[10px] text-slate-400 font-sans">تکنسین برتر اسپلیت - آمل</span>
                    </div>
                </div>
            </div>

        </div>

    </section>

    {{-- ============================================================== --}}
    {{-- ۷. دانشنامه فنی و آموزش نگهداری تاسیسات (#articles) --}}
    {{-- ============================================================== --}}
    <section id="articles" class="space-y-10 sm:space-y-12">
        
        <div class="text-center max-w-2xl mx-auto space-y-2.5">
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider font-sans">محتوای تخصصی و آموزشی</span>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-sans">
                دانشنامه فنی و نگهداری تاسیسات
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-sans">
                راهنماهای گام‌به‌گام و نکات عیب‌یابی برای ارتقای راندمان دستگاه‌ها
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            {{-- مقاله ۱ --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-2.5 text-right">
                    <span class="text-[10px] font-bold text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-950/60 px-2 py-0.5 rounded-full font-sans">پکیج شوفاژ</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans leading-snug">
                        ۵ نشانه افت فشار مدار گرمایش و روش اصولی تنظیم بار
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                        بررسی دلایل نوسان فشار مانومتر و زمان مناسب باز کردن شیر پرکن زیر دستگاه.
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] font-sans">
                    <span class="text-slate-400">۵ دقیقه مطالعه</span>
                    <a href="{{ route('fieldservice.customer') }}?type=package" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        مطالعه راهنما ←
                    </a>
                </div>
            </div>

            {{-- مقاله ۲ --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-2.5 text-right">
                    <span class="text-[10px] font-bold text-cyan-600 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-950/60 px-2 py-0.5 rounded-full font-sans">کولر گازی</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans leading-snug">
                        سرویس فصلی اسپلیت و راهکارهای کاهش ۳۰٪ مصرف برق
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                        اصول شستشوی فیلترها، تست نشت گاز مبرد و نحوه تنظیم دمای آسایش ۲۴ درجه.
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] font-sans">
                    <span class="text-slate-400">۴ دقیقه مطالعه</span>
                    <a href="{{ route('fieldservice.customer') }}?type=split_ac" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        مطالعه راهنما ←
                    </a>
                </div>
            </div>

            {{-- مقاله ۳ --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-2.5 text-right">
                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-2 py-0.5 rounded-full font-sans">موتورخانه مرکزی</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans leading-snug">
                        چک‌لیست راه‌اندازی و نگهداری پیشگیرانه موتورخانه
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                        نکات تنظیم مشعل، کوپلینگ پمپ‌های سیرکوله و کنترل رسوب‌زدایی دیگ چدنی.
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] font-sans">
                    <span class="text-slate-400">۶ دقیقه مطالعه</span>
                    <a href="{{ route('fieldservice.customer') }}?type=boiler_room" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        مطالعه راهنما ←
                    </a>
                </div>
            </div>

            {{-- مقاله ۴ --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-2.5 text-right">
                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full font-sans">ضمانت و امنیت</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans leading-snug">
                        راهنمای اعتبارسنجی تکنسین مجاز و کارت شناسایی
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                        چگونه از تایید گواهی مهارت و فعال بودن پوشش گارانتی کتبی اطمینان حاصل کنیم؟
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] font-sans">
                    <span class="text-slate-400">۳ دقیقه مطالعه</span>
                    <a href="{{ route('fieldservice.customer') }}#lookup" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        مطالعه راهنما ←
                    </a>
                </div>
            </div>

        </div>

    </section>

    {{-- ============================================================== --}}
    {{-- ۸. سوالات متداول (FAQ Accordion) با عملکرد کامپکت --}}
    {{-- ============================================================== --}}
    <section class="max-w-3xl mx-auto w-full space-y-8 sm:space-y-10" x-data="{ activeFaq: null }">
        <div class="text-center space-y-2">
            <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white font-sans">
                سوالات متداول
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-sans">
                پاسخ به پرتکرارترین پرسش‌های کاربران نصاب‌باشی
            </p>
        </div>

        <div class="space-y-2.5 font-sans">
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden">
                <button @click="activeFaq = (activeFaq === 1 ? null : 1)" class="w-full p-4 text-right font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center justify-between gap-3 focus:outline-none font-sans cursor-pointer">
                    <span>هزینه ایاب‌وذهاب و کارشناسی چگونه محاسبه می‌شود؟</span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold text-base transition-transform" :class="{ 'rotate-180': activeFaq === 1 }">↓</span>
                </button>
                <div x-show="activeFaq === 1" x-collapse class="px-4 pb-4 text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans border-t border-slate-100 dark:border-slate-800 pt-3">
                    تعرفه ایاب‌وذهاب و کارشناسی تکنسین در کلیه مناطق حوزه شهری مازندران با نرخ مصوب تنها {{ number_format($baseTravelFee ?? 60000) }} تومان می‌باشد. سایر دستمزدها نیز پیش از اقدام توسط تکنسین طبق جدول نرخ مصوب اتحادیه اعلام می‌گردد.
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden">
                <button @click="activeFaq = (activeFaq === 2 ? null : 2)" class="w-full p-4 text-right font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center justify-between gap-3 focus:outline-none font-sans cursor-pointer">
                    <span>مکانیزم پرداخت امن امانی (Escrow) چگونه عمل می‌کند؟</span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold text-base transition-transform" :class="{ 'rotate-180': activeFaq === 2 }">↓</span>
                </button>
                <div x-show="activeFaq === 2" x-collapse class="px-4 pb-4 text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans border-t border-slate-100 dark:border-slate-800 pt-3">
                    مبلغ پرداختی شما مستقیماً به تکنسین واریز نمی‌شود، بلکه در حساب امانی نگهداری می‌شود و فقط پس از رضایت و تایید نهایی شما تسویه می‌گردد.
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden">
                <button @click="activeFaq = (activeFaq === 3 ? null : 3)" class="w-full p-4 text-right font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center justify-between gap-3 focus:outline-none font-sans cursor-pointer">
                    <span>مدت زمان و شرایط ضمانت کتبی خدمات چقدر است؟</span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold text-base transition-transform" :class="{ 'rotate-180': activeFaq === 3 }">↓</span>
                </button>
                <div x-show="activeFaq === 3" x-collapse class="px-4 pb-4 text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-sans border-t border-slate-100 dark:border-slate-800 pt-3">
                    کلیه قطعات تعویضی و اجرت خدمات فنی به مدت ۳ ماه (۹۰ روز) تحت گارانتی رسمی قرار دارند و در صورت بروز مجدد عیب، بدون هزینه برطرف می‌شوند.
                </div>
            </div>

        </div>
    </section>

</div>

{{-- ============================================================== --}}
{{-- نوار شناور پایین صفحه در موبایل (App-Native Floating Bottom Bar) --}}
{{-- طراحی فلوتینگ با زاویه‌های گرد، سایه عمیق و اولویت قطعی سفارش --}}
{{-- ============================================================== --}}
<div class="fixed bottom-3 inset-x-3 sm:inset-x-6 z-40 max-w-lg mx-auto bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200/90 dark:border-slate-800 rounded-2xl p-2.5 shadow-2xl flex items-center justify-between gap-2.5 md:hidden font-sans">
    
    @if(!empty($supportPhone))
    <a href="tel:{{ $supportPhone }}" 
       class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700 active:scale-95 transition-transform" 
       title="تماس مستقیم پشتیبانی">
        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
        </svg>
    </a>
    @endif

    <a href="{{ route('fieldservice.customer') }}"
       class="flex-1 h-11 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 transition-all font-sans cursor-pointer">
        <svg class="w-4 h-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span>درخواست فوری تکنسین (اعزام زیر ۳۰ دقیقه)</span>
    </a>

</div>
@endsection
