@extends('layouts.web')

@section('content')
    <style>
        @keyframes float-subtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .animate-float-subtle {
            animation: float-subtle 4s ease-in-out infinite;
        }
        main {
             padding-bottom: 0 !important;
        }
    </style>
    <div dir="rtl" class="antialiased text-gray-800 bg-gray-50">
        <section class="relative flex flex-col items-center justify-center min-h-[90svh] bg-gradient-to-br from-[#f8faf9] via-[#f0f5f1] to-[#e8f0eb] overflow-hidden pt-36 pb-16">

            {{-- المان‌های نوری پس‌زمینه برای جلوه دادن به افکت شیشه‌ای (Glassmorphism) --}}
            <div class="absolute top-[10%] right-[10%] w-[60vw] max-w-[400px] h-[60vw] max-h-[400px] bg-[#3F7D20]/15 rounded-full blur-[80px] pointer-events-none"></div>
            <div class="absolute bottom-[10%] left-[10%] w-[50vw] max-w-[300px] h-[50vw] max-h-[300px] bg-[#c3f25c]/25 rounded-full blur-[70px] pointer-events-none"></div>

            <div class="container relative z-10 px-5 mx-auto max-w-5xl">
                {{-- هیرو گرید دو ستونه (متن و تصویر) --}}
                <div class="flex flex-col-reverse lg:flex-row items-center justify-between gap-10 lg:gap-12 mb-16">

                    {{-- ستون متن هیرو --}}
                    <div class="w-full lg:w-7/12 flex flex-col items-center lg:items-start text-center lg:text-right">

                        {{-- نشانگر بالا (Badge) - مینیمال و ساختاریافته --}}
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 mb-6 bg-[#3F7D20]/10 border border-[#3F7D20]/20 rounded-full">
                            <span class="flex w-2 h-2 rounded-full bg-[#3F7D20]"></span>
                            <span class="text-xs sm:text-sm font-semibold text-[#3F7D20]">دکتر فیت | تیم تخصصی پزشکی و ورزشی</span>
                        </div>

                        {{-- عنوان اصلی - مستقیم، تمیز و برجسته --}}
                        <h1 class="text-3xl sm:text-5xl lg:text-[2.75rem] xl:text-[3.25rem] font-extrabold text-gray-900 leading-tight mb-6">
                            تغییر فرم بدن با <br class="hidden sm:block" />
                            <span class="text-[#3F7D20]">دکتر فیت</span>
                        </h1>

                        {{-- متن توضیحات - یکدست، خاکستری ملایم و خوانا --}}
                        <p class="max-w-2xl text-base sm:text-lg text-gray-500 mb-8 leading-relaxed px-4 lg:px-0">
                            بدون آزمون و خطا. ما با بررسی دقیق شرایط بیولوژیکی شما، مسیری را طراحی می‌کنیم که هم سلامت شما را تضمین کند و هم شما را به اندام ایده‌آلتان برساند.
                        </p>

                        {{-- دکمه‌های اقدام --}}
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 sm:gap-4 w-full max-w-sm sm:max-w-none">
                            <a href="{{ url('/chat') }}" class="w-full sm:w-auto text-center px-6 py-3.5 text-sm md:text-base font-bold text-white bg-[#3F7D20] rounded-xl shadow-[0_10px_20px_-10px_rgba(63,125,32,0.5)] hover:bg-[#2d5a17] transition-all duration-300">
                                دریافت مشاوره رایگان
                            </a>
                            <button type="button" class="w-full sm:w-auto px-6 py-3.5 text-sm md:text-base font-bold text-gray-700 bg-white/60 backdrop-blur-md border border-white/80 rounded-xl hover:bg-white hover:text-[#3F7D20] transition-all duration-300 flex justify-center items-center gap-2">
                                نحوه کار تیم ما
                                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            </button>
                        </div>
                    </div>

                    {{-- ستون تصویر هیرو --}}
                    <div class="w-full lg:w-5/12 flex justify-center lg:justify-end">
                        <div class="relative w-full max-w-[340px] sm:max-w-[380px] lg:max-w-none">
                            {{-- افکت‌های درخشش و سایه زیر تصویر --}}
                            <div class="absolute -inset-1 bg-gradient-to-r from-[#3F7D20]/30 to-[#c3f25c]/30 rounded-[2.5rem] blur-xl opacity-60"></div>

                            {{-- قاب شیشه‌ای تصویر --}}
                            <div class="relative p-2.5 bg-white/50 backdrop-blur-xl border border-white/80 rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
                                <img src="{{ asset('themes/drfit/drfit-hero-photo.jpg') }}" alt="دکتر فیت - تناسب اندام علمی" class="w-full h-auto object-cover rounded-[2rem] shadow-sm" />
                            </div>

                            {{-- کارت معلق آمار/ویژگی با انیمیشن ملایم --}}
                            <div class="absolute -bottom-4 -right-4 sm:-right-6 bg-white/95 backdrop-blur border border-white/80 shadow-[0_12px_30px_rgba(0,0,0,0.06)] rounded-2xl p-4 flex items-center gap-3 animate-float-subtle">
                                <div class="w-9 h-9 rounded-xl bg-[#3F7D20]/10 flex items-center justify-center text-[#3F7D20] shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4" />
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[10px] text-gray-400 font-semibold">پشتیبانی و برنامه</span>
                                    <span class="block text-xs sm:text-sm font-bold text-gray-800">۱۰۰٪ علمی و شخصی‌سازی شده</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- کارت‌های Glassmorphism با مفاهیم جدید و جذاب --}}
                {{-- گرید: در موبایل زیر هم (1 ستون)، در تبلت به بالا کنار هم (3 ستون) --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 w-full max-w-5xl mx-auto px-2">

                    {{-- کارت ۱: رویکرد علمی --}}
                    <div class="bg-white/40 backdrop-blur-xl border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[2rem] p-6 relative overflow-hidden group hover:bg-white/60 hover:-translate-y-1 transition-all duration-300 text-right">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-[#3F7D20]/10 to-transparent rounded-bl-[4rem] -z-10"></div>
                        <div class="w-10 h-10 mb-4 bg-white/60 border border-white/80 text-[#3F7D20] rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        </div>
                        <span class="block text-base md:text-lg font-bold text-gray-900 mb-2">آنالیز متابولیک</span>
                        <span class="text-xs md:text-sm text-gray-600 leading-relaxed font-medium block">
                    طراحی مسیر تناسب اندام دقیقاً بر اساس بیومارکرها و فرم بدنی شما، نه حدس و گمان.
                </span>
                    </div>

                    {{-- کارت ۲: رویکرد تغذیه --}}
                    <div class="bg-white/40 backdrop-blur-xl border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[2rem] p-6 relative overflow-hidden group hover:bg-white/60 hover:-translate-y-1 transition-all duration-300 text-right">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-[#3F7D20]/10 to-transparent rounded-bl-[4rem] -z-10"></div>
                        <div class="w-10 h-10 mb-4 bg-white/60 border border-white/80 text-[#3F7D20] rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z"></path></svg>
                        </div>
                        <span class="block text-base md:text-lg font-bold text-gray-900 mb-2">تغذیه بدون حذف</span>
                        <span class="text-xs md:text-sm text-gray-600 leading-relaxed font-medium block">
                    آشتی با سفره ایرانی. چربی‌سوزی و عضله‌سازی بدون نیاز به حذف غذاهای مورد علاقه‌تان.
                </span>
                    </div>

                    {{-- کارت ۳: رویکرد پایدار --}}
                    <div class="bg-white/40 backdrop-blur-xl border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[2rem] p-6 relative overflow-hidden group hover:bg-white/60 hover:-translate-y-1 transition-all duration-300 text-right">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-[#3F7D20]/10 to-transparent rounded-bl-[4rem] -z-10"></div>
                        <div class="w-10 h-10 mb-4 bg-white/60 border border-white/80 text-[#3F7D20] rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <span class="block text-base md:text-lg font-bold text-gray-900 mb-2">خداحافظی با اثر یویو</span>
                        <span class="text-xs md:text-sm text-gray-600 leading-relaxed font-medium block">
                    ما فقط وزن کم نمی‌کنیم؛ با اصلاح سبک زندگی، تغییرات شما را برای همیشه تثبیت می‌کنیم.
                </span>
                    </div>

                </div>
            </div>
            {{-- فوتر بسیار مینیمال و یکپارچه با صفحه (بدون باکس مجزا) --}}
            @php
                $globalSettings = \Modules\Settings\Entities\Setting::pluck('value', 'key')->toArray();
                $footerLogo = isset($globalSettings['app_logo']) ? asset($globalSettings['app_logo']) : null;
                $footerAppName = $globalSettings['app_name'] ?? 'دکتر فیت';
            @endphp
            <footer class="relative z-10 w-full pt-20 pb-16 flex flex-col items-center justify-center text-center">

                {{-- لوگو و نام برند --}}
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 mb-5 opacity-80 hover:opacity-100 transition-opacity">
                    @if($footerLogo)
                        <img src="{{ $footerLogo }}" alt="{{ $footerAppName }}" class="h-8 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300">
                    @else
                        <div class="w-8 h-8 rounded-xl bg-gray-200/50 flex items-center justify-center text-gray-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                    @endif
                    <span class="font-extrabold text-lg text-gray-500 tracking-tight">{{ $footerAppName }}</span>
                </a>

                {{-- متن کپی رایت --}}
                <p class="text-[11px] sm:text-xs font-medium text-gray-400 mb-3">
                    تمامی حقوق مادی و معنوی برای <span class="text-gray-500">{{ $appName ?? 'دکتر فیت' }}</span> محفوظ است. &copy; {{ \Carbon\Carbon::now()->year }}
                </p>

                {{-- لینک‌های ضروری (قوانین و حریم خصوصی) --}}
                <div class="flex items-center justify-center gap-3">
                    <a href="#" class="text-[11px] font-medium text-gray-400 hover:text-[#3F7D20] transition-colors">قوانین و مقررات</a>
                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                    <a href="#" class="text-[11px] font-medium text-gray-400 hover:text-[#3F7D20] transition-colors">حریم خصوصی</a>
                </div>

                {{--
                    پدینگ اضافی برای جلوگیری از مخفی شدن فوتر در زیر نوار ناوبری شناور موبایل
                --}}
                <div class="h-6 w-full md:hidden"></div>
            </footer>

        </section>
    </div>

@endsection
