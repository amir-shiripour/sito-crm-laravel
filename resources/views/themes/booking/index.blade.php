@extends('layouts.web')

@php
    $isProviderFlow = (isset($flow) && $flow === 'PROVIDER_FIRST');
@endphp

@section('title', 'سامانه نوبت‌دهی و مشاوره')

@section('content')
    <div class="max-w-7xl mx-auto w-full flex-grow flex flex-col pt-8 pb-16">

        {{-- 1. Hero Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-24 px-6 md:px-0">
            <div class="space-y-8 animate-in fade-in slide-in-from-right-8 duration-1000">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-50 border border-teal-100 text-teal-700 text-sm font-bold dark:bg-teal-900/30 dark:border-teal-800 dark:text-teal-300">
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-teal-500"></span>
                </span>
                    سیستم نوبت‌دهی هوشمند ۲۴/۷
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white leading-tight">
                    سلامتی شما، <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-500 to-emerald-500">اولویت اصلی ماست</span>
                </h1>

                <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed max-w-lg">
                    بدون اتلاف وقت در صف‌های طولانی، نوبت پزشک متخصص یا خدمات درمانی خود را به صورت آنلاین و در کمتر از یک دقیقه رزرو کنید.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <a href="{{ route('booking.public.index') }}" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-bold text-lg text-center hover:from-teal-600 hover:to-teal-700 shadow-xl shadow-teal-500/30 transition-all hover:-translate-y-1 flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        رزرو آنلاین نوبت
                    </a>
                    <a href="#services" class="px-8 py-4 rounded-2xl bg-white text-gray-700 font-bold text-lg text-center border-2 border-gray-200 hover:border-teal-500 hover:text-teal-600 transition-all dark:bg-gray-900 dark:border-gray-800 dark:text-gray-300 dark:hover:border-teal-500 flex items-center justify-center gap-2">
                        {{ $isProviderFlow ? 'مشاهده پزشکان' : 'مشاهده خدمات' }}
                    </a>
                </div>

                <div class="flex items-center gap-4 pt-4 text-sm text-gray-500 dark:text-gray-400 font-medium">
                    <div class="flex -space-x-3 space-x-reverse">
                        <img class="w-10 h-10 rounded-full border-2 border-white dark:border-gray-900" src="https://i.pravatar.cc/100?img=1" alt="User">
                        <img class="w-10 h-10 rounded-full border-2 border-white dark:border-gray-900" src="https://i.pravatar.cc/100?img=2" alt="User">
                        <img class="w-10 h-10 rounded-full border-2 border-white dark:border-gray-900" src="https://i.pravatar.cc/100?img=3" alt="User">
                        <div class="w-10 h-10 rounded-full border-2 border-white dark:border-gray-900 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs font-bold">+۵K</div>
                    </div>
                    بیمارانی که به ما اعتماد کرده‌اند
                </div>
            </div>

            {{-- Hero Visual --}}
            <div class="relative animate-in fade-in zoom-in-95 duration-1000 delay-200 hidden lg:block">
                <div class="absolute inset-0 bg-gradient-to-tr from-teal-200 to-emerald-200 dark:from-teal-900/40 dark:to-emerald-900/40 rounded-full blur-3xl -z-10"></div>
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-4 rounded-[2rem] shadow-2xl relative">
                    <div class="bg-gray-50 dark:bg-gray-950 rounded-[1.5rem] p-6 border border-gray-100 dark:border-gray-800 space-y-4">
                        {{-- Fake Calendar UI --}}
                        <div class="flex justify-between items-center mb-6">
                            <div class="font-bold text-lg dark:text-white">اردیبهشت ۱۴۰۳</div>
                            <div class="flex gap-2">
                                <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 shadow flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></div>
                                <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-600 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold text-gray-400 mb-2">
                            <div>ش</div><div>ی</div><div>د</div><div>س</div><div>چ</div><div>پ</div><div>ج</div>
                        </div>
                        <div class="grid grid-cols-7 gap-2">
                            @for($i=1; $i<=14; $i++)
                                <div class="aspect-square rounded-xl flex items-center justify-center text-sm font-bold {{ $i==8 ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/40 transform scale-110' : 'bg-white dark:bg-gray-800 dark:text-gray-300' }}">{{ $i }}</div>
                            @endfor
                        </div>
                        <div class="mt-4 p-4 rounded-xl bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-800/50 flex gap-4 items-center">
                            <div class="w-12 h-12 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-teal-600 font-bold">۱۷:۳۰</div>
                            <div>
                                <div class="font-bold text-sm text-teal-900 dark:text-teal-100">دکتر محمدی - قلب و عروق</div>
                                <div class="text-xs text-teal-600 dark:text-teal-400">زمان انتخاب شده</div>
                            </div>
                        </div>
                    </div>
                    {{-- Floating Badge --}}
                    <div class="absolute -right-6 top-12 bg-white dark:bg-gray-800 p-3 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 flex items-center gap-3 animate-bounce" style="animation-duration: 3s;">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                        <div>
                            <div class="text-xs text-gray-500">نوبت موفق</div>
                            <div class="font-bold text-sm">تایید فوری</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Real Services / Providers Grid --}}
        <div id="services" class="py-16 px-6 md:px-0">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ $isProviderFlow ? 'پزشکان و متخصصین' : 'خدمات و تخصص‌ها' }}
                </h2>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ $isProviderFlow ? 'پزشک مورد نظر خود را برای رزرو انتخاب کنید' : 'سرویس مورد نظر خود را برای رزرو انتخاب کنید' }}
                </p>
            </div>

            @if(isset($bookingItems) && $bookingItems->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($bookingItems as $item)
                        @php
                            $name = $isProviderFlow ? ($item->name ?? $item->full_name ?? 'پزشک') : $item->name;
                            $desc = $isProviderFlow ? ($item->bio ?? $item->description ?? 'مشاهده خدمات و دریافت نوبت') : $item->description;
                            $priceLabel = $isProviderFlow ? 'شروع قیمت از' : '';
                            $price = $isProviderFlow ? ($item->min_price ?? 0) : ($item->final_price ?? $item->base_price);

                            $link = $isProviderFlow
                                ? (Route::has('booking.public.provider') ? route('booking.public.provider', $item->id) : '#')
                                : route('booking.public.service', $item->id);
                        @endphp

                        <a href="{{ $link }}" class="group flex flex-col bg-white dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-lg shadow-gray-200/20 dark:shadow-none hover:-translate-y-2 hover:border-teal-500/50 transition-all duration-300 overflow-hidden h-full">
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-start justify-between gap-4 mb-4">
                                    <div class="w-14 h-14 rounded-2xl bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 flex items-center justify-center group-hover:bg-teal-500 group-hover:text-white transition-colors duration-300 overflow-hidden shrink-0">
                                        @if($isProviderFlow && !empty($item->avatar))
                                            <img src="{{ $item->avatar }}" alt="{{ $name }}" class="w-full h-full object-cover">
                                        @elseif($isProviderFlow)
                                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        @else
                                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                        @endif
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-900 px-3 py-1.5 rounded-lg flex flex-col items-center gap-0.5">
                                        @if($priceLabel) <span class="text-[9px] font-bold text-gray-500 dark:text-gray-400">{{ $priceLabel }}</span> @endif
                                        <div class="text-xs font-bold text-gray-600 dark:text-gray-300 flex items-center gap-1">
                                            {{ number_format($price) }} <span class="text-[10px] font-normal">تومان</span>
                                        </div>
                                        @if(isset($bookingSettings) && $bookingSettings->tax_enabled)
{{--                                            <span class="text-[9px] font-normal text-teal-600/80 dark:text-teal-500">(با مالیات)</span>--}}
                                            <span class="text-[9px] font-normal text-teal-600/80 dark:text-teal-500"></span>
                                        @endif
                                    </div>
                                </div>

                                <h3 class="font-bold text-xl text-gray-900 dark:text-white mb-2">{{ $name }}</h3>
                                @if($desc)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed flex-1">{{ $desc }}</p>
                                @endif

                                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-teal-600 dark:text-teal-400 font-bold text-sm">
                                    <span>ثبت نوبت</span>
                                    <svg class="w-5 h-5 transform group-hover:-translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                        @if($isProviderFlow) هیچ پزشکی یافت نشد @else سرویسی برای رزرو یافت نشد @endif
                    </h3>
                    <p class="text-sm text-gray-500">
                        در حال حاضر هیچ @if($isProviderFlow) پزشکی @else خدماتی @endif برای رزرو آنلاین در سیستم فعال نیست.
                    </p>
                </div>
            @endif
        </div>

        {{-- 3. How it Works --}}
        <div class="py-16 mt-8 mx-6 md:mx-0 bg-teal-50 dark:bg-gray-900/50 rounded-[3rem] px-8 border border-teal-100 dark:border-gray-800">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">چگونه نوبت بگیریم؟</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                <!-- Connector Line -->
                <div class="hidden md:block absolute top-1/2 left-[15%] right-[15%] h-0.5 bg-teal-200 dark:bg-teal-900 -translate-y-1/2 z-0"></div>

                <div class="relative z-10 flex flex-col items-center text-center space-y-4">
                    <div class="w-20 h-20 rounded-full bg-white dark:bg-gray-800 border-4 border-teal-100 dark:border-teal-900 flex items-center justify-center shadow-lg text-teal-600 dark:text-teal-400 hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="font-bold text-xl dark:text-white">۱. جستجو و انتخاب</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">سرویس یا نام پزشک مورد نظر خود را جستجو کنید.</p>
                </div>

                <div class="relative z-10 flex flex-col items-center text-center space-y-4">
                    <div class="w-20 h-20 rounded-full bg-teal-500 border-4 border-teal-100 dark:border-teal-900 flex items-center justify-center shadow-lg shadow-teal-500/40 text-white hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="font-bold text-xl dark:text-white">۲. رزرو زمان</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">از بین زمان‌های خالی، مناسب‌ترین وقت را انتخاب کنید.</p>
                </div>

                <div class="relative z-10 flex flex-col items-center text-center space-y-4">
                    <div class="w-20 h-20 rounded-full bg-white dark:bg-gray-800 border-4 border-teal-100 dark:border-teal-900 flex items-center justify-center shadow-lg text-teal-600 dark:text-teal-400 hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="font-bold text-xl dark:text-white">۳. تایید و مراجعه</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">درگاه پرداخت را تکمیل کرده و در زمان مقرر مراجعه نمایید.</p>
                </div>
            </div>
        </div>

    </div>
@endsection
