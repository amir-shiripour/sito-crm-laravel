@extends('layouts.web')

@section('title', 'سامانه هوشمند اعزام تکنسین و خدمات فنی در محل')

@section('content')
<div class="max-w-7xl mx-auto w-full flex-grow flex flex-col pt-8 pb-16 px-4 sm:px-6 lg:px-8 font-sans">

    {{-- ۱. بخش هیرو (Hero Section) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center my-8 lg:my-16">
        
        {{-- ستون متن هیرو --}}
        <div class="lg:col-span-7 space-y-6 text-right">
            
            {{-- بج رادار و تکنسین‌های فعال --}}
            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs sm:text-sm font-bold dark:bg-indigo-500/15 dark:border-indigo-500/30 dark:text-indigo-300 font-sans shadow-sm">
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span>رادار اعزام فعال:</span>
                <span class="text-emerald-700 dark:text-emerald-400 font-extrabold font-sans">
                    {{ $activeTechCount > 0 ? $activeTechCount . ' تکنسین آماده‌به‌خدمت در منطقه' : 'اعزام سریع تکنسین در کمتر از ۳۰ دقیقه' }}
                </span>
            </div>

            {{-- عنوان اصلی --}}
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 dark:text-white leading-[1.3] tracking-tight font-sans">
                تعمیرات تخصصی در محل شما، <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-indigo-700 to-cyan-600 dark:from-indigo-400 dark:to-cyan-400">
                    با نرخ مصوب و ضمانت کتبی ۹۰ روزه
                </span>
            </h1>

            {{-- متن توضیحی --}}
            <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl font-sans">
                پلتفرم هوشمند اعزام نزدیک‌ترین تکنسین متخصص به محل شما در سراسر استان مازندران. عیب‌یابی انواع پکیج شوفاژ، کولر گازی، چیلر، موتورخانه و تاسیسات با سیستم پرداخت امن امانی (Escrow) و قطعات یدکی اورجینال.
            </p>

            {{-- دکمه‌های اکشن سریع --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-3">
                <a href="{{ route('fieldservice.customer') }}"
                   class="px-6 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm sm:text-base text-center shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 font-sans">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>ثبت آنلاین درخواست اعزام (کمتر از ۱ دقیقه)</span>
                </a>

                <a href="{{ route('fieldservice.customer') }}#lookup"
                   class="px-6 py-3.5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-200 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold text-sm sm:text-base text-center transition-all flex items-center justify-center gap-2 font-sans">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>پیگیری وضعیت سفارش</span>
                </a>
            </div>

            {{-- نشان‌های اطمینان زیر دکمه‌ها --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs text-slate-600 dark:text-slate-300 font-sans">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">✓</span>
                    <span>تعرفه مصوب {{ number_format(($baseTravelFee ?? 60000) / 10) }} تومانی</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">🛡️</span>
                    <span>گارانتی کتبی ۹۰ روزه</span>
                </div>
                <div class="flex items-center gap-2 col-span-2 sm:col-span-1">
                    <span class="w-6 h-6 rounded-lg bg-cyan-50 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold">🔒</span>
                    <span>پرداخت امن امانی</span>
                </div>
            </div>

        </div>

        {{-- ستون موکاپ رادار و وضعیت اعزام زنده --}}
        <div class="lg:col-span-5">
            <div class="relative">
                {{-- افکت هاله نئونی پشت کارت --}}
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-cyan-500 rounded-3xl blur-2xl opacity-25 dark:opacity-20"></div>

                <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-xl space-y-5">
                    
                    {{-- هدر کارت رادار --}}
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white font-sans">سیستم رادار هوشمند اعزام</h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 font-sans">شعاع اولیه جستجو: ۳ کیلومتر در بابل و مازندران</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            فعال و برخط
                        </span>
                    </div>

                    {{-- نمایش شبیه‌ساز فرآیند رادار --}}
                    <div class="bg-gray-50 dark:bg-gray-950/70 border border-gray-100 dark:border-gray-800/80 rounded-2xl p-4 space-y-3 font-sans">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400">تکنسین‌های حاضر در محدوده:</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 font-sans">{{ $activeTechCount > 0 ? $activeTechCount : 2 }} تکنسین مجاز</span>
                        </div>
                        
                        {{-- انیمیشن خط پیشرفت رادار --}}
                        <div class="w-full bg-gray-200 dark:bg-gray-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-500 via-indigo-600 to-cyan-500 h-2 rounded-full animate-pulse w-3/4"></div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                            <span>پوشش شهری بابل، آمل، ساری و قائم‌شهر</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">میانگین اعزام: ۱۸ دقیقه</span>
                        </div>
                    </div>

                    {{-- کارت نمونه یک تکنسین برتر --}}
                    <div class="p-4 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 bg-indigo-50/40 dark:bg-indigo-500/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-indigo-600 text-white font-bold text-sm flex items-center justify-center font-sans shadow-md shadow-indigo-600/20">
                                نق
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white font-sans">مهندس قاسمی</h4>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 dark:bg-amber-950 dark:text-amber-300 text-amber-800">
                                        تکنسین مستر
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 font-sans">متخصص ارشد پکیج، چیلر و موتورخانه</p>
                            </div>
                        </div>
                        <div class="text-left font-sans">
                            <div class="flex items-center gap-1 text-amber-500 text-xs font-bold font-sans">
                                <span>★</span>
                                <span>۴.۹۵</span>
                            </div>
                            <span class="text-[10px] text-gray-400 font-sans">۱۳۸ سفارش موفق</span>
                        </div>
                    </div>

                    {{-- کال تو اکشن سریع داخل کارت --}}
                    <a href="{{ route('fieldservice.customer') }}"
                       class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm text-center flex items-center justify-center gap-2 shadow-md shadow-indigo-600/30 transition-all font-sans">
                        <span>اعزام تکنسین با همین مشخصات</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>

                </div>
            </div>
        </div>

    </div>

    {{-- ۲. بخش تجهیزات و خدمات تحت پوشش (#services) --}}
    <div id="services" class="py-12 border-t border-gray-100 dark:border-gray-800/80">
        
        <div class="text-center max-w-3xl mx-auto mb-12 space-y-3">
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider font-sans">پوشش جامع سیستم‌های سرمایشی و گرمایشی</span>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white font-sans">
                دستگاه و خدمات مورد نیاز خود را انتخاب کنید
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 font-sans">
                کلیه قطعات مصرفی تعویض‌شده دارای فاکتور رسمی و ضمانت کتبی سه ماهه هستند.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- ۱. پکیج شوفاژ دیواری --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl">
                        🔥
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white font-sans">پکیج شوفاژ دیواری و زمینی</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                        عیب‌یابی تخصصی ارورهای دیجیتال، رفع افت فشار مدار، اسیدشویی مبدل اصلی و ثانویه، تعمیر یا تعویض پمپ و برد الکترونیکی (بوتان، ایران‌رادیاتور، لورچ و برندهای خارجی).
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-sans">ایاب‌وذهاب: {{ number_format(($baseTravelFee ?? 60000) / 10) }} تومان</span>
                    <a href="{{ route('fieldservice.customer') }}?type=package" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline font-sans">
                        ثبت سفارش پکیج ←
                    </a>
                </div>
            </div>

            {{-- ۲. کولر گازی و اسپلیت --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl">
                        ❄️
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white font-sans">کولر گازی، اسپلیت و ایستاده</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                        نصب و جابجایی اصولی، وکیوم تخصصی مدار، نشت‌یابی و شارژ کامل گاز مبرد R22 و R410a با گیج دیجیتال، شستشوی سیکل و تعویض خازن و کمپرسور اینورتر.
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-sans">ایاب‌وذهاب: {{ number_format(($baseTravelFee ?? 60000) / 10) }} تومان</span>
                    <a href="{{ route('fieldservice.customer') }}?type=split_ac" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline font-sans">
                        ثبت سفارش کولر گازی ←
                    </a>
                </div>
            </div>

            {{-- ۳. چیلر تراکمی و جذبی --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl">
                        🏢
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white font-sans">چیلر تراکمی و جذبی ساختمانی</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                        سرویس دوره‌ای کمپرسورهای اسکرو و اسکرال، اسیدشویی و رسوب‌زدایی کندانسور، شارژ روغن برودتی، رفع روغن‌ریزی و تنظیم ترموستات آنتی‌فریز مجتمع‌های مسکونی و اداری.
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 font-sans">اعزام مهندس ناظر</span>
                    <a href="{{ route('fieldservice.customer') }}?type=chiller" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline font-sans">
                        ثبت کارشناسی چیلر ←
                    </a>
                </div>
            </div>

            {{-- ۴. موتورخانه و تاسیسات مرکزی --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl">
                        ⚙️
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white font-sans">موتورخانه و تاسیسات مرکزی</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                        تنظیم نسبت سوخت و هوای مشعل گازی، کوپلینگ و تعویض واسطه و پروانه پمپ‌های سیرکولاتور گراندفوس، تعویض پره دیگ چدنی و آب‌بندی لوله‌کشی فولادی.
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-sans">ایاب‌وذهاب: {{ number_format(($baseTravelFee ?? 60000) / 10) }} تومان</span>
                    <a href="{{ route('fieldservice.customer') }}?type=boiler_room" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline font-sans">
                        ثبت سفارش موتورخانه ←
                    </a>
                </div>
            </div>

            {{-- ۵. سردخانه صنعتی --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center text-2xl">
                        🧊
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white font-sans">سردخانه صنعتی و تجاری</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                        عیب‌یابی سردخانه‌های زیر صفر نگهداری پروتئینی و بالای صفر میوه، تنظیم دیفراست و هیترهای اواپراتور، کنترل شیر انبساط ترموستاتیک (دانفوس/کستل) و تابلو برق قدرت.
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 font-sans">اورژانسی ۲۴/۷</span>
                    <a href="{{ route('fieldservice.customer') }}?type=cold_storage" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline font-sans">
                        ثبت سرویس سردخانه ←
                    </a>
                </div>
            </div>

            {{-- ۶. داکت اسپلیت اینورتر --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl">
                        💨
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white font-sans">داکت اسپلیت اینورتر و کانالی</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                        شستشوی فیلتر و کویل اواپراتور توکار، هواگیری کویل آبگرم گرمایشی، تعویض موتور فن بلوور داخلی و شارژ تکمیلی متراژ بالا با بالاترین راندمان هوادهی.
                    </p>
                </div>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-sans">ایاب‌وذهاب: {{ number_format(($baseTravelFee ?? 60000) / 10) }} تومان</span>
                    <a href="{{ route('fieldservice.customer') }}?type=ducted_split" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline font-sans">
                        ثبت سفارش داکت اسپلیت ←
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- ۳. مراحل کارکرد نصاب‌باشی (#how-it-works) --}}
    <div id="how-it-works" class="py-16 border-t border-gray-100 dark:border-gray-800/80">
        
        <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider font-sans">ساده، سریع و بدون اتلاف وقت</span>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white font-sans">
                چرخه اعزام و خدمت در ۴ گام
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 font-sans">
                از لحظه ثبت درخواست تا اتمام گارانتی ۹۰ روزه همراه شما هستیم.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
            
            {{-- گام ۱ --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative text-right space-y-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center font-sans shadow-md shadow-indigo-600/30">
                    ۱
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white font-sans">ثبت آسان درخواست</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                    نوع دستگاه، مدل، شرح خلاصه عیب و آدرس خود را در فرم سریع مشتری وارد می‌کنید.
                </p>
            </div>

            {{-- گام ۲ --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative text-right space-y-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center font-sans shadow-md shadow-indigo-600/30">
                    ۲
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white font-sans">رادار و اعزام سریع</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                    رادار سیستم نزدیک‌ترین تکنسین واجد صلاحیت و تاییدشده را پیدا کرده و اعزام می‌کند.
                </p>
            </div>

            {{-- گام ۳ --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative text-right space-y-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center font-sans shadow-md shadow-indigo-600/30">
                    ۳
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white font-sans">حضور و عیب‌یابی</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                    تکنسین با کارت معتبر در محل حاضر شده و پس از اعلام هزینه مصوب، اقدام به رفع عیب می‌نماید.
                </p>
            </div>

            {{-- گام ۴ --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative text-right space-y-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center font-sans shadow-md shadow-indigo-600/30">
                    ۴
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white font-sans">فاکتور و ضمانت ۹۰ روزه</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                    فاکتور دیجیتال صادر شده، پرداخت در حساب امن امانی قفل شده و گارانتی سه ماهه فعال می‌شود.
                </p>
            </div>

        </div>
    </div>

    {{-- ۴. سیستم پرداخت امن امانی و گارانتی (#guarantee) --}}
    <div id="guarantee" class="my-12 p-8 sm:p-12 rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-950 text-white relative overflow-hidden">
        
        <div class="relative z-10 max-w-3xl space-y-5">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-sans">
                🛡️ نهایت امنیت و آرامش خاطر
            </span>
            <h2 class="text-2xl sm:text-4xl font-black leading-tight font-sans">
                سیستم پرداخت امن امانی (Escrow Held) چگونه از شما محافظت می‌کند؟
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-sans">
                در نصاب‌باشی هیچ پرداختی مستقیماً به حساب شخصی تکنسین واریز نمی‌شود. وجه فاکتور پس از پرداخت آنلاین، در حساب امانی پلتفرم قفل می‌گردد و تنها زمانی به تکنسین تسویه خواهد شد که شما از عملکرد بی‌نقص دستگاه و کیفیت خدمت رضایت کامل داشته باشید.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 text-xs font-sans">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 border border-white/10">
                    <span class="text-emerald-400 font-bold text-base">✓</span>
                    <span>ضمانت کتبی ۹۰ روزه قطعات و اجرت</span>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 border border-white/10">
                    <span class="text-emerald-400 font-bold text-base">✓</span>
                    <span>حق استرداد وجه در صورت عدم رضایت</span>
                </div>
            </div>

            <div class="pt-4">
                <a href="{{ route('fieldservice.customer') }}"
                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-xl shadow-indigo-600/30 transition-all font-sans">
                    درخواست اعزام تکنسین با ضمانت امانی
                </a>
            </div>
        </div>
    </div>

    {{-- ۵. بنر جذب تکنسین و فروشگاه‌های همکار --}}
    <div class="my-8 p-8 rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-right">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 font-sans">
                    فرصت همکاری
                </span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white font-sans">تکنسین تاسیسات یا صاحب فروشگاه قطعات هستید؟</h3>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed font-sans">
                با عضویت در پلتفرم نصاب‌باشی، روزانه سفارش‌های تعمیرات حوزه سکونت خود را دریافت کنید، درآمد تضمین‌شده داشته باشید و از تسویه حساب آنی در کیف پول اختصاصی بهره‌مند شوید.
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('register', ['role' => 'technician']) }}"
               class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-600/20 transition-all font-sans">
                ثبت‌نام تکنسین متخصص
            </a>
            <a href="{{ route('login') }}"
               class="px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs sm:text-sm font-bold transition-all font-sans">
                ورود همکاران
            </a>
        </div>
    </div>

</div>
@endsection
