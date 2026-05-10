@extends('layouts.web')

@php
    $isProviderFlow = ($flow ?? 'SERVICE_FIRST') === 'PROVIDER_FIRST';
@endphp

@section('title', $isProviderFlow ? 'انتخاب پزشک' : 'لیست خدمات رزرو آنلاین')

@section('content')
    <div class="max-w-7xl mx-auto px-6 w-full space-y-12">

        {{-- Header Section --}}
        <div class="text-center space-y-4 mb-12 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl shadow-indigo-500/30 mb-6 rotate-3 hover:rotate-0 transition-transform duration-300">
                @if($isProviderFlow)
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                @else
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                @endif
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight">
                @if($isProviderFlow)
                    انتخاب <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">پزشک/متخصص</span>
                @else
                    رزرو آنلاین <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">سرویس‌ها</span>
                @endif
            </h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                @if($isProviderFlow)
                    متخصص مورد نظر خود را از لیست زیر انتخاب کرده و پس از انتخاب سرویس، در کوتاه‌ترین زمان نوبت بگیرید.
                @else
                    سرویس مورد نظر خود را از لیست زیر انتخاب کنید و در کوتاه‌ترین زمان نوبت خود را قطعی نمایید.
                @endif
            </p>
        </div>

        {{-- Items Grid --}}
        @if(isset($items) && $items->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-100">
                @foreach($items as $item)
                    @php
                        // منطق تعیین نام و مشخصات براساس جریان سرویس یا پزشک
                        $name = $isProviderFlow ? ($item->name ?? $item->full_name ?? 'پزشک') : $item->name;
                        $desc = $isProviderFlow ? ($item->bio ?? $item->description ?? 'مشاهده خدمات و دریافت نوبت') : $item->description;
                        $priceLabel = $isProviderFlow ? 'شروع قیمت از' : '';
                        $price = $isProviderFlow ? ($item->min_price ?? 0) : ($item->final_price ?? $item->base_price);

                        // اطمینان از وجود روت provider برای جلوگیری از خطای برنامه
                        $link = $isProviderFlow
                            ? (Route::has('booking.public.provider') ? route('booking.public.provider', $item->id) : '#')
                            : route('booking.public.service', $item->id);
                    @endphp

                    <a href="{{ $link }}"
                       class="group flex flex-col bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-[2rem] border border-gray-100 dark:border-gray-800 shadow-xl shadow-gray-200/20 dark:shadow-none hover:border-indigo-500/50 hover:-translate-y-2 transition-all duration-300 overflow-hidden h-full">

                        <div class="p-8 flex-1 flex flex-col">
                            {{-- Item Header --}}
                            <div class="flex items-start justify-between gap-4 mb-6">
                                <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-500 transition-colors duration-300 text-indigo-600 dark:text-indigo-400 overflow-hidden">
                                    @if($isProviderFlow && !empty($item->avatar))
                                        <img src="{{ $item->avatar }}" alt="{{ $name }}" class="w-full h-full object-cover">
                                    @elseif($isProviderFlow)
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    @else
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 px-3 py-1.5 rounded-xl text-sm font-bold flex flex-col items-center gap-0.5 border border-emerald-100 dark:border-emerald-800/50">
                                    @if($priceLabel) <span class="text-[9px] font-normal opacity-80">{{ $priceLabel }}</span> @endif
                                    <div class="flex items-center gap-1">
                                        {{ number_format($price) }} <span class="text-[10px] font-normal">تومان</span>
                                    </div>
                                    @if($settings->tax_enabled)
                                        <span class="text-[9px] font-normal opacity-80 text-emerald-600/80 dark:text-emerald-500"></span>
{{--                                        <span class="text-[9px] font-normal opacity-80 text-emerald-600/80 dark:text-emerald-500">(با مالیات)</span>--}}
                                    @endif
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200 mb-3">
                                    {{ $name }}
                                </h3>
                                @if($desc)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-3">
                                        {{ $desc }}
                                    </p>
                                @endif
                            </div>

                            {{-- Action Area --}}
                            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>
                                        @if($isProviderFlow) انتخاب و ثبت نوبت @else انتخاب زمان دلخواه @endif
                                    </span>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5 transform transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="max-w-2xl mx-auto bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-2xl p-16 text-center animate-in fade-in zoom-in duration-500">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-50 dark:bg-gray-800 mb-6 shadow-inner">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-3">
                    @if($isProviderFlow) هیچ پزشکی یافت نشد @else هیچ سرویسی یافت نشد @endif
                </h3>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    در حال حاضر هیچ @if($isProviderFlow) پزشکی @else خدماتی @endif برای رزرو آنلاین در سیستم فعال نیست. لطفاً بعداً مراجعه کنید.
                </p>
            </div>
        @endif
    </div>
@endsection
