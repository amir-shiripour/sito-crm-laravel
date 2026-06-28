@extends('layouts.web')

@section('title', 'پلتفرم جامع اتوماسیون کسب‌وکار')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --font-vazir: "Vazirmatn", sans-serif;
        }
        .font-vazir { font-family: var(--font-vazir); }

        /* سایه دکمه اصلی */
        .shadow-btn-primary {
            box-shadow: inset 0px 4px 4px 0px rgba(255,255,255,0.35), 0 10px 25px -5px rgba(0, 132, 255, 0.25);
        }

        /* انیمیشن‌های ورود */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-down { animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-slide-up-delay-1 { animation: slideUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards; opacity: 0; }
        .animate-slide-up-delay-2 { animation: slideUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards; opacity: 0; }

        /* انیمیشن‌های شناور (تطبیق یافته با محورهای X برای حالت RTL) */
        @keyframes float-1 {
            0%, 100% { transform: translateY(0) translateX(0) scale(1) rotate(0deg); }
            50% { transform: translateY(-8px) translateX(-2px) scale(1) rotate(0deg); }
        }
        @keyframes float-2 {
            0%, 100% { transform: translateY(0) translateX(0) scale(1) rotate(0deg); }
            50% { transform: translateY(8px) translateX(2px) scale(1) rotate(0deg); }
        }
        @keyframes float-3 {
            0%, 100% { transform: translateY(0) translateX(0) scale(1) rotate(0deg); }
            50% { transform: translateY(-10px) translateX(1px) scale(1) rotate(0deg); }
        }
        .animate-float-1 { animation: float-1 5.0s ease-in-out infinite; }
        .animate-float-2 { animation: float-2 5.5s ease-in-out infinite; }
        .animate-float-3 { animation: float-3 4.8s ease-in-out infinite; }

        /* انیمیشن پرش بج‌ها */
        @keyframes spring-in {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-spring-badge-1 { animation: spring-in 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.6s forwards; opacity: 0; }
        .animate-spring-badge-2 { animation: spring-in 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.8s forwards; opacity: 0; }
        .animate-spring-badge-3 { animation: spring-in 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 1.0s forwards; opacity: 0; }
    </style>
@endpush

@section('content')
    <div dir="rtl" class="relative w-full min-h-screen bg-white overflow-hidden text-[#171717] font-vazir">

        {{-- هاله‌های نورانی پس‌زمینه --}}
        <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-[#60B1FF]/20 rounded-full blur-[120px] pointer-events-none z-0"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[600px] h-[600px] bg-[#319AFF]/15 rounded-full blur-[100px] pointer-events-none z-0"></div>

        {{-- 1. منوی شیشه‌ای شناور --}}
        <nav x-data="{ mobileMenuOpen: false }" class="fixed top-[30px] left-0 right-0 z-50 flex justify-center px-4 pointer-events-none animate-slide-down">
            <div class="w-full max-w-[1280px] bg-white/60 backdrop-blur-[40px] border border-white/70 shadow-sm h-12 rounded-[16px] pointer-events-auto transition-all duration-300 flex items-center justify-between px-6 py-2 relative">

                {{-- لوگوی برند --}}
                <a href="#" class="font-vazir font-extrabold text-[20px] tracking-tight text-black flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#0084FF]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
                    دستیار هوشمند
                </a>

                {{-- لینک‌های دسکتاپ --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="#" class="text-[14px] font-medium text-black/60 hover:text-black transition-colors">خانه</a>
                    <a href="#" class="text-[14px] font-medium text-black/60 hover:text-black transition-colors">امکانات پلتفرم</a>
                    <a href="#" class="text-[14px] font-medium text-black/60 hover:text-black transition-colors">مشتریان ما</a>
                    <a href="#" class="text-[14px] font-medium text-black/60 hover:text-black transition-colors">تعرفه‌ها</a>
                </div>

                {{-- دکمه اقدام / منوی موبایل --}}
                <div class="flex items-center gap-4">
                    <a href="#" class="hidden sm:flex group h-9 px-5 rounded-[12px] bg-black/5 hover:bg-black/10 border border-black/10 text-[14px] font-semibold items-center gap-2 text-black transition-all hover:shadow-md">
                        ورود به پنل
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    </a>

                    {{-- دکمه منوی موبایل --}}
                    <button @click="mobileMenuOpen = true" class="md:hidden p-2 text-black/60 hover:text-black">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            {{-- کشوی منوی موبایل (Alpine.js) --}}
            <div x-show="mobileMenuOpen"
                 style="display: none;"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-full"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-full"
                 class="fixed top-0 right-0 bottom-0 w-[260px] bg-white/95 backdrop-blur-[40px] border-l border-black/10 z-[100] pointer-events-auto flex flex-col p-6 shadow-2xl">

                <div class="flex justify-end mb-8">
                    <button @click="mobileMenuOpen = false" class="p-2 text-black/60 hover:text-black rounded-full hover:bg-black/5">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex flex-col gap-6">
                    <a href="#" class="text-[16px] font-medium text-black/80 hover:text-black">خانه</a>
                    <a href="#" class="text-[16px] font-medium text-black/80 hover:text-black">امکانات پلتفرم</a>
                    <a href="#" class="text-[16px] font-medium text-black/80 hover:text-black">مشتریان ما</a>
                    <a href="#" class="text-[16px] font-medium text-black/80 hover:text-black">تعرفه‌ها</a>
                    <hr class="border-black/10">
                    <a href="#" class="flex justify-center group h-10 rounded-[12px] bg-[#0084FF] text-white text-[15px] font-semibold items-center gap-2 transition-all shadow-md">
                        ورود به پنل
                    </a>
                </div>
            </div>

            {{-- پس‌زمینه تیره موبایل --}}
            <div x-show="mobileMenuOpen"
                 @click="mobileMenuOpen = false"
                 style="display: none;"
                 x-transition.opacity
                 class="fixed inset-0 bg-black/20 z-[90] pointer-events-auto md:hidden"></div>
        </nav>

        {{-- محتوای اصلی بدنه --}}
        <main class="w-full max-w-[1280px] mx-auto px-6 sm:px-12 lg:px-20 pt-[120px] md:pt-[160px] pb-24 relative z-10">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12">

                {{-- 2. ستون سمت راست: متون، تاییدیه‌های اجتماعی و دکمه‌ها --}}
                <div class="lg:col-span-5 flex flex-col justify-center items-start text-right max-w-[620px] lg:pl-6 animate-slide-up-delay-1">

                    {{-- نشان تاییدیه کاربران --}}
                    <div class="px-3 py-1.5 rounded-full bg-black/5 border border-black/5 flex items-center gap-3 w-fit shadow-xs hover:bg-black/10 transition-colors group cursor-default">
                        <div class="flex -space-x-2 -space-x-reverse select-none">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=80&h=80&fit=crop&crop=face" alt="User 1" class="w-6 h-6 rounded-full border-[1.5px] border-white object-cover group-hover:scale-110 transition-transform duration-300 z-[4]">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop&crop=face" alt="User 2" class="w-6 h-6 rounded-full border-[1.5px] border-white object-cover group-hover:scale-110 transition-transform duration-300 delay-75 z-[3]">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&h=80&fit=crop&crop=face" alt="User 3" class="w-6 h-6 rounded-full border-[1.5px] border-white object-cover group-hover:scale-110 transition-transform duration-300 delay-100 z-[2]">
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=80&h=80&fit=crop&crop=face" alt="User 4" class="w-6 h-6 rounded-full border-[1.5px] border-white object-cover group-hover:scale-110 transition-transform duration-300 delay-150 z-[1]">
                        </div>
                        <span class="text-[12px] text-black/80 font-vazir">مورد اعتماد بیش از <strong class="text-[#171717] font-semibold">۱۰,۰۰۰ کسب‌وکار</strong></span>
                    </div>

                    {{-- عنوان اصلی --}}
                    <h1 class="font-vazir font-black text-[36px] sm:text-[44px] lg:text-[56px] leading-[1.2] tracking-tight mt-6 select-none text-black">
                        اتوماسیون جامع <br/> مدیریت کسب‌وکار
                    </h1>

                    {{-- پاراگراف توضیحات --}}
                    <p class="font-vazir text-[16px] sm:text-[18px] text-black/60 leading-relaxed mt-5 max-w-[480px]">
                        با کمک هوش مصنوعی کارهای تکراری را خودکار کنید، ارتباط با مشتریان را بهبود ببخشید و بهره‌وری تیم خود را چندین برابر افزایش دهید.
                    </p>

                    {{-- دکمه‌های اقدام --}}
                    <div class="mt-8 flex flex-wrap items-center gap-6">

                        {{-- دکمه اصلی --}}
                        <button class="group pr-6 pl-2 py-2 rounded-[16px] flex items-center gap-4 text-sm font-bold transition-all w-fit shadow-btn-primary bg-[#0084FF] hover:bg-[#0074E0] text-white hover:scale-[1.02] active:scale-[0.98]">
                            شروع رایگان
                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-[#0084FF] transition-transform group-hover:-translate-x-1">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </div>
                        </button>

                        {{-- لینک مشاهده دمو --}}
                        <a href="#" class="flex items-center gap-2 group cursor-pointer">
                            <div class="w-9 h-9 rounded-full bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center border border-blue-100 transition-colors">
                                <svg class="w-4 h-4 text-[#0084FF] mr-0.5 transform rotate-180" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                            </div>
                            <span class="font-vazir font-bold text-[14px] text-[#0084FF] group-hover:text-[#0074E0] transition-colors">مشاهده دمو</span>
                        </a>
                    </div>
                </div>

                {{-- 3. ستون سمت چپ: ویدیوی ربات و کارت‌های شناور --}}
                <div class="lg:col-span-7 relative w-full flex items-center justify-center lg:justify-start py-10 pointer-events-none animate-slide-up-delay-2 mt-10 lg:mt-0">

                    {{-- افکت‌های دایره‌ای تزئینی --}}
                    <div class="absolute top-[30%] right-[20%] w-[420px] h-[420px] bg-sky-400/15 rounded-full blur-[110px] -z-10 animate-pulse duration-[7000ms]"></div>

                    {{-- حلقه‌های متمرکز --}}
                    <div class="absolute w-[620px] h-[620px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-[52%] -z-10 opacity-35">
                        <svg viewBox="0 0 620 620" class="w-full h-full" fill="none" stroke="url(#ring-gradient)" stroke-dasharray="12 12">
                            <defs>
                                <linearGradient id="ring-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#60B1FF" />
                                    <stop offset="100%" stop-color="#319AFF" />
                                </linearGradient>
                            </defs>
                            <circle cx="310" cy="310" r="200" stroke-width="1" />
                            <circle cx="310" cy="310" r="260" stroke-width="1.5" />
                            <circle cx="310" cy="310" r="310" stroke-width="0.5" />
                        </svg>
                    </div>

                    {{-- ویدیوی مرکزی ربات --}}
                    <div class="relative w-full max-w-[600px] z-10">
                        <video
                            autoPlay
                            loop
                            muted
                            playsInline
                            class="w-full h-auto rounded-[24px] select-none block"
                            style="filter: brightness(1.02) contrast(1.04);"
                        >
                            <source src="https://strvid.nyc3.cdn.digitaloceanspaces.com/motionsite/hero_robo_video.mp4" type="video/mp4">
                        </video>

                        {{-- بج‌های شناور متحرک --}}

                        {{-- بج ۱: ارسال فاکتور (بالا سمت چپ تصویر در حالت RTL) --}}
                        <div class="absolute top-[18%] -left-4 sm:-left-10 md:-left-14 z-20 animate-spring-badge-1">
                            <div class="animate-float-1 group bg-gradient-to-bl from-white/75 to-white/45 border border-white/70 ring-1 ring-black/5 backdrop-blur-[20px] shadow-[inset_0_2.5px_4px_rgba(255,255,255,0.8),_0_12px_32px_-4px_rgba(0,132,255,0.12)] px-5 py-3 rounded-[20px] flex items-center gap-3 pointer-events-auto hover:scale-[1.05] hover:rotate-[1deg] transition-all duration-300 cursor-pointer">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#0084FF] to-[#0066CC] flex items-center justify-center shadow-[0_4px_12px_rgba(0,132,255,0.3)] shrink-0">
                                    <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/><path d="m15 5 3 3"/></svg>
                                </div>
                                <div class="flex flex-col text-right leading-tight">
                                    <span class="font-vazir font-black text-[13px] text-neutral-900 tracking-tight">ارسال پیش‌فاکتور</span>
                                    <span class="font-vazir font-semibold text-[10px] text-neutral-500 mt-0.5">برای مشتریان جدید</span>
                                </div>
                            </div>
                        </div>

                        {{-- بج ۲: خلاصه سازی (مرکز سمت راست تصویر در حالت RTL) --}}
                        <div class="absolute top-[48%] -right-6 sm:-right-12 md:-right-16 z-20 animate-spring-badge-2">
                            <div class="animate-float-2 group bg-gradient-to-bl from-white/75 to-white/45 border border-white/70 ring-1 ring-black/5 backdrop-blur-[20px] shadow-[inset_0_2.5px_4px_rgba(255,255,255,0.8),_0_12px_32px_-4px_rgba(16,185,129,0.12)] px-5 py-3 rounded-[20px] flex items-center gap-3 pointer-events-auto hover:scale-[1.05] hover:-rotate-[1deg] transition-all duration-300 cursor-pointer">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#10B981] to-[#059669] flex items-center justify-center shadow-[0_4px_12px_rgba(16,185,129,0.3)] shrink-0">
                                    <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                </div>
                                <div class="flex flex-col text-right leading-tight">
                                    <span class="font-vazir font-black text-[13px] text-neutral-900 tracking-tight">گزارش‌گیری</span>
                                    <span class="font-vazir font-semibold text-[10px] text-neutral-500 mt-0.5">عملکرد فروش ماهانه</span>
                                </div>
                            </div>
                        </div>

                        {{-- بج ۳: ایجاد وظیفه (پایین سمت چپ تصویر در حالت RTL) --}}
                        <div class="absolute bottom-[18%] -left-4 sm:-left-8 md:-left-12 z-20 animate-spring-badge-3">
                            <div class="animate-float-3 group bg-gradient-to-bl from-white/75 to-white/45 border border-white/70 ring-1 ring-black/5 backdrop-blur-[20px] shadow-[inset_0_2.5px_4px_rgba(255,255,255,0.8),_0_12px_32px_-4px_rgba(147,51,234,0.12)] px-5 py-3 rounded-[20px] flex items-center gap-3 pointer-events-auto hover:scale-[1.05] hover:rotate-[1.5deg] transition-all duration-300 cursor-pointer">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#9333EA] to-[#7E22CE] flex items-center justify-center shadow-[0_4px_12px_rgba(147,51,234,0.3)] shrink-0">
                                    <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </div>
                                <div class="flex flex-col text-right leading-tight">
                                    <span class="font-vazir font-black text-[13px] text-neutral-900 tracking-tight">تخصیص وظیفه</span>
                                    <span class="font-vazir font-semibold text-[10px] text-neutral-500 mt-0.5">به تیم پشتیبانی</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>

    </div>
@endsection
