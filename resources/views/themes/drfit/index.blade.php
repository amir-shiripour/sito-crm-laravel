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
        #mobile-bottom-nav {
            display: none !important;
        }
    </style>
    <div dir="rtl" class="antialiased text-gray-800 bg-gray-50">
        <section class="relative flex flex-col items-center justify-center min-h-[100svh] bg-gradient-to-br from-[#f8faf9] via-[#f0f5f1] to-[#e8f0eb] overflow-hidden pt-16 pb-0">

            {{-- المان‌های نوری پس‌زمینه برای جلوه دادن به افکت شیشه‌ای (Glassmorphism) --}}
            <div class="absolute top-[10%] right-[10%] w-[60vw] max-w-[400px] h-[60vw] max-h-[400px] bg-[#3F7D20]/15 rounded-full blur-[80px] pointer-events-none"></div>
            <div class="absolute bottom-[10%] left-[10%] w-[50vw] max-w-[300px] h-[50vw] max-h-[300px] bg-[#c3f25c]/25 rounded-full blur-[70px] pointer-events-none"></div>

            <div class="container relative z-10 px-5 mx-auto max-w-5xl">
                {{-- هیرو گرید دو ستونه (متن و تصویر) --}}
                <div class="flex flex-col-reverse lg:flex-row items-center justify-between gap-10 lg:gap-12 mb-16">

                    {{-- ستون متن هیرو --}}
                    <div class="w-full lg:w-7/12 flex flex-col items-center lg:items-start text-center lg:text-right">

                        {{-- نشانگر بالا (Badge) - مینیمال و ساختاریافته --}}
{{--                        <div class="inline-flex items-center gap-2 px-4 py-1.5 mb-6 bg-[#3F7D20]/10 border border-[#3F7D20]/20 rounded-full">--}}
{{--                            <span class="flex w-2 h-2 rounded-full bg-[#3F7D20]"></span>--}}
{{--                            <span class="text-xs sm:text-sm font-semibold text-[#3F7D20]">دکتر فیت | تیم تخصصی پزشکی و ورزشی</span>--}}
{{--                        </div>--}}

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

            </div>
            {{-- فوتر بسیار مینیمال و یکپارچه با صفحه (بدون باکس مجزا) --}}
            @php
                $globalSettings = [];
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                        $globalSettings = \Modules\Settings\Entities\Setting::pluck('value', 'key')->toArray();
                    }
                } catch (\Throwable $e) {}

                $footerLogo = isset($globalSettings['app_logo']) ? asset($globalSettings['app_logo']) : null;
                $footerAppName = $globalSettings['app_name'] ?? 'دکتر فیت';
            @endphp
            {{--<footer class="relative z-10 w-full pt-20 pb-16 flex flex-col items-center justify-center text-center">

                --}}{{-- لوگو و نام برند --}}{{--
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

                --}}{{-- متن کپی رایت --}}{{--
                <p class="text-[11px] sm:text-xs font-medium text-gray-400 mb-3">
                    تمامی حقوق مادی و معنوی برای <span class="text-gray-500">{{ $appName ?? 'دکتر فیت' }}</span> محفوظ است. &copy; {{ \Carbon\Carbon::now()->year }}
                </p>

                --}}{{-- لینک‌های ضروری (قوانین و حریم خصوصی) --}}{{--
                --}}{{--<div class="flex items-center justify-center gap-3">
                    <a href="#" class="text-[11px] font-medium text-gray-400 hover:text-[#3F7D20] transition-colors">قوانین و مقررات</a>
                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                    <a href="#" class="text-[11px] font-medium text-gray-400 hover:text-[#3F7D20] transition-colors">حریم خصوصی</a>
                </div>--}}{{--

                --}}{{--
                    پدینگ اضافی برای جلوگیری از مخفی شدن فوتر در زیر نوار ناوبری شناور موبایل
                --}}{{--
                <div class="h-6 w-full md:hidden"></div>
            </footer>--}}

        </section>
    </div>

@endsection
