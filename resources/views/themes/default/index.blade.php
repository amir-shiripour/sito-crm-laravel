@extends('layouts.web')

@section('title', 'پلتفرم جامع مدیریت کسب‌وکار')

@section('content')
    <div class="max-w-7xl mx-auto px-6 w-full flex-grow flex flex-col pt-32 pb-16">
        <div class="w-full flex flex-col gap-32">

            {{-- 1. Advanced Hero Section --}}
            <div class="text-center space-y-8 animate-in fade-in slide-in-from-bottom-8 duration-1000 max-w-4xl mx-auto pt-10">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800/50 text-indigo-600 dark:text-indigo-300 text-xs font-bold mb-4 shadow-sm backdrop-blur-sm">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                    نسخه ۴.۰ با هوش مصنوعی منتشر شد
                </div>

                <h1 class="text-5xl md:text-7xl font-black text-gray-900 dark:text-white leading-tight tracking-tight">
                    هسته‌ی مرکزی برای <br class="hidden md:block" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 relative">
                    رشد تصاعدی
                    <svg class="absolute -bottom-2 w-full h-3 text-indigo-200 dark:text-indigo-900/50 opacity-70" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="3" fill="transparent"/></svg>
                </span>
                    تیم شما
                </h1>

                <p class="text-lg md:text-xl text-gray-500 dark:text-gray-400 leading-relaxed max-w-3xl mx-auto">
                    تنها با یک پلتفرم ابری یکپارچه، فروش خود را چند برابر کنید، ارتباطات مشتریان را متحول سازید و با گزارشات هوشمند، همیشه یک قدم از رقبا جلوتر باشید.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-6">
                    @auth
                        <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}"
                           class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold text-lg shadow-2xl shadow-gray-900/20 dark:shadow-white/10 hover:-translate-y-1 transition-all flex items-center justify-center gap-2 group">
                            ورود به پنل کاربری
                            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold text-lg hover:from-indigo-700 hover:to-purple-700 shadow-xl shadow-indigo-600/30 transition-all hover:-translate-y-1 flex items-center justify-center gap-2 group">
                            شروع رایگان ۱۴ روزه
                            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#"
                           class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 font-bold text-lg hover:border-indigo-500 dark:hover:border-indigo-500 hover:text-indigo-600 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            مشاهده دمو
                        </a>
                    @endauth
                </div>

                <p class="text-sm text-gray-400 dark:text-gray-500 mt-4">بدون نیاز به کارت بانکی • راه‌اندازی در ۳ دقیقه</p>
            </div>

            {{-- 2. Mockup Dashboard (Glassmorphism Effect) --}}
            <div class="relative mx-auto w-full max-w-5xl animate-in fade-in zoom-in-95 duration-1000 delay-300 hidden md:block">
                <div class="absolute inset-0 bg-gradient-to-b from-indigo-500/20 to-purple-500/10 blur-[100px] -z-10 rounded-full"></div>
                <div class="rounded-[2.5rem] border border-white/40 dark:border-gray-700/40 bg-white/40 dark:bg-gray-900/40 backdrop-blur-2xl shadow-2xl shadow-indigo-500/10 p-4 overflow-hidden">
                    <div class="rounded-[2rem] bg-gray-50 dark:bg-gray-950 border border-gray-200/50 dark:border-gray-800 w-full h-[450px] flex flex-col overflow-hidden relative shadow-inner">
                        <!-- Header Mock -->
                        <div class="h-16 border-b border-gray-200 dark:border-gray-800 flex items-center px-6 justify-between bg-white/80 dark:bg-gray-900/80 backdrop-blur">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="flex gap-4">
                                <div class="h-8 w-8 bg-gray-200 dark:bg-gray-800 rounded-full"></div>
                                <div class="h-8 w-32 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl"></div>
                            </div>
                        </div>
                        <!-- Body Mock -->
                        <div class="flex-1 p-8 grid grid-cols-12 gap-8">
                            <!-- Sidebar -->
                            <div class="col-span-3 space-y-4 border-r border-gray-200 dark:border-gray-800 pr-6">
                                <div class="h-10 bg-indigo-500 text-white rounded-xl shadow-md flex items-center px-4 gap-3"><div class="w-4 h-4 bg-white/30 rounded"></div></div>
                                <div class="h-10 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl flex items-center px-4 gap-3"><div class="w-4 h-4 bg-gray-300 dark:bg-gray-700 rounded"></div></div>
                                <div class="h-10 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl flex items-center px-4 gap-3"><div class="w-4 h-4 bg-gray-300 dark:bg-gray-700 rounded"></div></div>
                                <div class="h-10 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl flex items-center px-4 gap-3"><div class="w-4 h-4 bg-gray-300 dark:bg-gray-700 rounded"></div></div>
                            </div>
                            <!-- Main Content -->
                            <div class="col-span-9 space-y-6">
                                <div class="flex justify-between items-end">
                                    <div class="space-y-2">
                                        <div class="h-4 w-24 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                        <div class="h-8 w-48 bg-gray-300 dark:bg-gray-700 rounded"></div>
                                    </div>
                                    <div class="h-10 w-32 bg-indigo-600 rounded-xl"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-6">
                                    <div class="h-28 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 flex flex-col justify-between">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30"></div>
                                        <div class="h-4 w-1/2 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                    </div>
                                    <div class="h-28 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 flex flex-col justify-between">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30"></div>
                                        <div class="h-4 w-2/3 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                    </div>
                                    <div class="h-28 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 flex flex-col justify-between">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30"></div>
                                        <div class="h-4 w-1/3 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                    </div>
                                </div>
                                <div class="h-32 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 w-full relative overflow-hidden flex items-end">
                                    <!-- Fake Area Chart -->
                                    <svg class="w-full h-24 text-indigo-500/20 dark:text-indigo-500/10" preserveAspectRatio="none" viewBox="0 0 100 100" fill="currentColor">
                                        <path d="M0,100 L0,60 Q20,30 40,70 T80,40 L100,20 L100,100 Z"></path>
                                    </svg>
                                    <svg class="absolute w-full h-24 text-indigo-600 dark:text-indigo-500 fill-none" preserveAspectRatio="none" viewBox="0 0 100 100" stroke="currentColor" stroke-width="2">
                                        <path d="M0,60 Q20,30 40,70 T80,40 L100,20"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating Decorative Elements --}}
                <div class="absolute -right-8 top-1/4 bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 flex items-center gap-4 animate-bounce" style="animation-duration: 4s;">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg></div>
                    <div>
                        <div class="text-xs text-gray-500">رشد فروش</div>
                        <div class="font-black text-lg text-gray-900 dark:text-white">+۱۲۴٪</div>
                    </div>
                </div>
            </div>

            {{-- 3. Features Section --}}
            <div>
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">هرآنچه برای مدیریت یک کسب‌وکار مدرن نیاز دارید</h2>
                    <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">به جای استفاده از ده‌ها نرم‌افزار مختلف، تمامی ابزارهای فروش، پشتیبانی و اتوماسیون را در یک سیستم قدرتمند تجربه کنید.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/20 dark:shadow-none hover:-translate-y-2 transition-transform duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 dark:bg-indigo-900/20 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
                        <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-6 dark:bg-indigo-900/50 dark:text-indigo-400">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <h3 class="font-bold text-xl mb-3 text-gray-900 dark:text-white">مدیریت ارتباطات ۳۶۰ درجه</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">سوابق تماس، پیامک‌ها، تیکت‌ها و خریدهای مشتری را در یک تایم‌لاین هوشمند مشاهده کنید و هیچ جزئیاتی را از دست ندهید.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/20 dark:shadow-none hover:-translate-y-2 transition-transform duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
                        <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-6 dark:bg-emerald-900/50 dark:text-emerald-400">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <h3 class="font-bold text-xl mb-3 text-gray-900 dark:text-white">خط لوله فروش (Pipeline)</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">مراحل فروش را در قالب بردهای Kanban مدیریت کنید، وظایف را به اعضای تیم بسپارید و نرخ تبدیل (Conversion Rate) را بالا ببرید.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/20 dark:shadow-none hover:-translate-y-2 transition-transform duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 dark:bg-purple-900/20 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
                        <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center mb-6 dark:bg-purple-900/50 dark:text-purple-400">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <h3 class="font-bold text-xl mb-3 text-gray-900 dark:text-white">اتوماسیون مبتنی بر هوش مصنوعی</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">کارهای تکراری مانند ارسال پیامک خوش‌آمدگویی، یادآوری فاکتور و تگ‌گذاری مشتریان را به ربات‌های هوشمند سیستم بسپارید.</p>
                    </div>
                </div>
            </div>

            {{-- 4. Integrations Section (NEW) --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-[3rem] p-10 md:p-16 border border-gray-200/60 dark:border-gray-800 text-center">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">اتصال آسان به ابزارهای محبوب شما</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-10 max-w-lg mx-auto">پلتفرم ما به راحتی با سرویس‌های پیامکی، درگاه‌های پرداخت، ایمیل مارکتینگ و حسابداری متصل می‌شود.</p>

                <div class="flex flex-wrap justify-center gap-6 items-center">
                    {{-- Mock Logos for Integrations --}}
                    <div class="w-16 h-16 rounded-2xl bg-white dark:bg-gray-800 shadow-md border border-gray-100 dark:border-gray-700 flex items-center justify-center hover:scale-110 transition-transform"><span class="font-black text-blue-500">SMS</span></div>
                    <div class="w-16 h-16 rounded-2xl bg-white dark:bg-gray-800 shadow-md border border-gray-100 dark:border-gray-700 flex items-center justify-center hover:scale-110 transition-transform"><span class="font-black text-yellow-500">Zpal</span></div>
                    <div class="w-16 h-16 rounded-2xl bg-white dark:bg-gray-800 shadow-md border border-gray-100 dark:border-gray-700 flex items-center justify-center hover:scale-110 transition-transform"><svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></div>
                    <div class="w-16 h-16 rounded-2xl bg-white dark:bg-gray-800 shadow-md border border-gray-100 dark:border-gray-700 flex items-center justify-center hover:scale-110 transition-transform"><svg class="w-8 h-8 text-indigo-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg></div>
                    <div class="w-16 h-16 rounded-2xl bg-white dark:bg-gray-800 shadow-md border border-gray-100 dark:border-gray-700 flex items-center justify-center hover:scale-110 transition-transform"><span class="font-black text-rose-500">Mail</span></div>
                </div>
            </div>

            {{-- 5. Final CTA --}}
            <div class="w-full">
                <div class="bg-gradient-to-br from-gray-900 to-indigo-950 rounded-[3rem] p-10 md:p-16 text-center relative overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -translate-y-12 translate-x-12 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 translate-y-12 -translate-x-12 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>

                    <div class="relative z-10 space-y-6">
                        <h2 class="text-3xl md:text-5xl font-black text-white">آماده تحول در کسب‌وکار خود هستید؟</h2>
                        <p class="text-indigo-200 text-lg max-w-2xl mx-auto">همین حالا به جمع هزاران مدیر موفقی بپیوندید که از پلتفرم ما برای توسعه تجارت خود استفاده می‌کنند.</p>
                        <div class="pt-4 flex flex-col sm:flex-row justify-center gap-4">
                            @auth
                                <a href="{{ route('admin.dashboard') }}" class="px-8 py-4 rounded-2xl bg-indigo-500 text-white font-bold text-lg hover:bg-indigo-600 transition-colors shadow-lg shadow-indigo-500/30 border border-indigo-400">
                                    رفتن به داشبورد
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="px-8 py-4 rounded-2xl bg-indigo-500 text-white font-bold text-lg hover:bg-indigo-600 transition-colors shadow-lg shadow-indigo-500/30 border border-indigo-400">
                                    هم‌اکنون شروع کنید
                                </a>
                                <a href="#" class="px-8 py-4 rounded-2xl bg-white/10 text-white font-bold text-lg hover:bg-white/20 transition-colors backdrop-blur-sm border border-white/20">
                                    تماس با مشاوره فروش
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
