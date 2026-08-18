@extends('layouts.web')

@php
    $isProviderFlow = ($flow ?? 'SERVICE_FIRST') === 'PROVIDER_FIRST';

    // استخراج لیست‌های یکتا برای فیلترها در صورت حالت انتخاب ارائه‌دهنده
    $allSpecialties = collect();
    $allProvinces = collect();
    $locationsData = [];
    $providersList = [];

    if ($isProviderFlow && isset($items) && $items->count() > 0) {
        foreach ($items as $item) {
            $profile = $item->profile ?? null;
            $specs = $profile?->specialties_list ?? [];
            if (is_array($specs)) {
                foreach ($specs as $s) {
                    if (!empty(trim($s))) {
                        $allSpecialties->push(trim($s));
                    }
                }
            }
            if (!empty($profile?->province)) {
                $pName = trim($profile->province);
                $allProvinces->push($pName);
                if (!isset($locationsData[$pName])) {
                    $locationsData[$pName] = [];
                }
                if (!empty($profile?->city)) {
                    $cName = trim($profile->city);
                    if (!in_array($cName, $locationsData[$pName], true)) {
                        $locationsData[$pName][] = $cName;
                    }
                }
            }

            $providersList[] = [
                'id' => $item->id,
                'name' => $item->name ?? $item->full_name ?? config('booking.labels.provider'),
                'specialties' => $profile?->specialties_list ?? [],
                'specialty_text' => $profile?->specialty_text ?: ($profile?->specialty ?? ''),
                'clinic' => $profile?->clinic_name ?? '',
                'province' => $profile?->province ?? '',
                'city' => $profile?->city ?? '',
                'rating' => (float) ($profile?->effective_rating ?? 0),
                'reviews_count' => (int) ($profile?->effective_reviews_count ?? 0),
                'satisfaction_rate' => (int) ($profile?->effective_satisfaction_rate ?? 0),
                'bookings_count' => (int) ($profile?->effective_successful_bookings_count ?? 0),
                'endorsements_count' => (int) ($profile?->effective_endorsements_count ?? 0),
                'min_price' => (float) ($item->min_price ?? 0),
                'link' => Route::has('booking.public.provider') ? route('booking.public.provider', $item->id) : '#',
            ];
        }
    }

    $uniqueSpecialties = $allSpecialties->unique()->values();
    $uniqueProvinces = $allProvinces->unique()->values();
@endphp

@section('title', $isProviderFlow ? 'انتخاب ' . config('booking.labels.provider') : 'لیست خدمات رزرو آنلاین')

@section('content')
    <div class="max-w-7xl mx-auto pt-32 px-6 w-full space-y-10"
         @if($isProviderFlow)
             x-data="providerFilterManager(@js($providersList), @js($locationsData))"
         @endif
    >

        {{-- Header Section --}}
        <div class="text-center space-y-4 mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl shadow-indigo-500/30 mb-4 rotate-3 hover:rotate-0 transition-transform duration-300">
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
                    انتخاب <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">{{ config('booking.labels.providers') }}</span>
                @else
                    رزرو آنلاین <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">سرویس‌ها</span>
                @endif
            </h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                @if($isProviderFlow)
                    {{ config('booking.labels.provider') }} مورد نظر خود را از لیست زیر انتخاب کرده و پس از انتخاب سرویس، در کوتاه‌ترین زمان نوبت بگیرید.
                @else
                    سرویس مورد نظر خود را از لیست زیر انتخاب کنید و در کوتاه‌ترین زمان نوبت خود را قطعی نمایید.
                @endif
            </p>
        </div>

        {{-- FILTER & SORT BAR (مخصوص بخش انتخاب ارائه‌دهندگان) --}}
        @if($isProviderFlow && isset($items) && $items->count() > 0)
            <div class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl shadow-gray-200/20 dark:shadow-none p-5 sm:p-6 space-y-4 animate-in fade-in slide-in-from-bottom-6 duration-700">
                {{-- Search & Main Filters Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                    {{-- Search Input --}}
                    <div class="lg:col-span-4 relative">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text"
                               x-model="search"
                               placeholder="جستجوی نام، تخصص، کلینیک یا شهر..."
                               class="w-full rounded-2xl pr-10 pl-9 py-2.5 bg-gray-50/70 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700/80 text-xs sm:text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <button type="button"
                                x-show="search"
                                @click="search = ''"
                                class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Specialty Filter --}}
                    <div class="lg:col-span-3 relative">
                        <select x-model="selectedSpecialty"
                                class="w-full rounded-2xl px-3.5 py-2.5 bg-gray-50/70 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700/80 text-xs sm:text-sm text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <option value="">همه تخصص‌ها</option>
                            @foreach($uniqueSpecialties as $spec)
                                <option value="{{ $spec }}">{{ $spec }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Province Filter --}}
                    <div class="lg:col-span-2.5 lg:col-span-3 relative">
                        <select x-model="selectedProvince"
                                class="w-full rounded-2xl px-3.5 py-2.5 bg-gray-50/70 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700/80 text-xs sm:text-sm text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <option value="">همه استان‌ها</option>
                            @foreach($uniqueProvinces as $prov)
                                <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- City Filter --}}
                    <div class="lg:col-span-2 relative">
                        <select x-model="selectedCity"
                                :disabled="!selectedProvince || cities.length === 0"
                                class="w-full rounded-2xl px-3.5 py-2.5 bg-gray-50/70 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700/80 text-xs sm:text-sm text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">همه شهرها</option>
                            <template x-for="c in cities" :key="c">
                                <option :value="c" x-text="c"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Sort Options & Active Filters Bar --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-800/80 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                    {{-- Sort Buttons --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="font-bold text-gray-500 dark:text-gray-400 ml-1">مرتب‌سازی:</span>
                        
                        <button type="button"
                                @click="sortBy = 'default'"
                                class="px-3 py-1.5 rounded-xl font-bold transition-all select-none"
                                :class="sortBy === 'default' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'">
                            پیش‌فرض
                        </button>

                        <button type="button"
                                @click="sortBy = 'popular'"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-bold transition-all select-none"
                                :class="sortBy === 'popular' ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'">
                            <span>⭐</span>
                            <span>محبوب‌ترین (بالاترین امتیاز)</span>
                        </button>

                        <button type="button"
                                @click="sortBy = 'bookings'"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-bold transition-all select-none"
                                :class="sortBy === 'bookings' ? 'bg-sky-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'">
                            <span>🔵✓</span>
                            <span>بیشترین نوبت موفق</span>
                        </button>

                        <button type="button"
                                @click="sortBy = 'satisfaction'"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-bold transition-all select-none"
                                :class="sortBy === 'satisfaction' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'">
                            <span>👍</span>
                            <span>بیشترین رضایت</span>
                        </button>
                    </div>

                    {{-- Counter & Reset Button --}}
                    <div class="flex items-center justify-between md:justify-end gap-3">
                        <div class="text-gray-500 dark:text-gray-400 font-medium">
                            <span>نمایش </span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm" x-text="toFa(filteredItems.length)"></span>
                            <span> از </span>
                            <span class="font-bold text-gray-800 dark:text-gray-200" x-text="toFa(rawItems.length)"></span>
                            <span> {{ config('booking.labels.provider') }}</span>
                        </div>

                        <button type="button"
                                x-show="hasActiveFilters"
                                @click="resetFilters()"
                                class="inline-flex items-center gap-1 text-rose-600 dark:text-rose-400 hover:text-rose-700 font-bold transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>پاک کردن فیلترها</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Items Grid --}}
        @if(isset($items) && $items->count() > 0)
            @if($isProviderFlow)
                {{-- PROVIDER FLOW GRID (کنترل شده توسط Alpine با فیلتر و مرتب‌سازی لحظه‌ای) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-100"
                     x-show="filteredItems.length > 0">
                    <template x-for="item in filteredItems" :key="item.id">
                        <a :href="item.link"
                           class="group flex flex-col bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-[2rem] border border-gray-100 dark:border-gray-800 shadow-xl shadow-gray-200/20 dark:shadow-none hover:border-indigo-500/50 hover:-translate-y-2 transition-all duration-300 overflow-hidden h-full">

                            <div class="p-8 flex-1 flex flex-col">
                                {{-- Item Header --}}
                                <div class="flex items-start justify-between gap-4 mb-6">
                                    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-500 transition-colors duration-300 text-indigo-600 dark:text-indigo-400 overflow-hidden relative shadow-2xs">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>

                                    {{-- Micro-Stats Badges --}}
                                    <div class="flex flex-col items-end gap-1.5" x-show="item.rating > 0 || item.bookings_count > 0">
                                        <div x-show="item.rating > 0" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/50 text-amber-900 dark:text-amber-200 text-xs font-extrabold shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-400 shrink-0" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span x-text="toFa(item.rating)"></span>
                                            <span x-show="item.reviews_count > 0" class="text-[10px] text-gray-500 dark:text-gray-400 font-normal" x-text="'(' + toFa(item.reviews_count) + ')'"></span>
                                        </div>

                                        <div x-show="item.bookings_count > 0" class="inline-flex items-center gap-1 text-[11px] font-semibold text-sky-600 dark:text-sky-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                            <span x-text="toFa(item.bookings_count) + ' نوبت موفق'"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200 mb-1" x-text="item.name">
                                    </h3>

                                    {{-- Specialty list / tags --}}
                                    <div class="flex flex-wrap items-center gap-1.5 my-2" x-show="item.specialties && item.specialties.length > 0">
                                        <template x-for="(spec, sIdx) in item.specialties" :key="sIdx">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/60" x-text="spec"></span>
                                        </template>
                                    </div>

                                    {{-- Fallback single specialty text --}}
                                    <div class="text-indigo-600 dark:text-indigo-400 text-sm font-semibold mb-1"
                                         x-show="(!item.specialties || item.specialties.length === 0) && item.specialty_text"
                                         x-text="item.specialty_text">
                                    </div>

                                    {{-- Clinic & Location --}}
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        <span x-show="item.clinic" class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            <span x-text="item.clinic"></span>
                                        </span>
                                        <span x-show="item.clinic && (item.province || item.city)" class="text-gray-300 dark:text-gray-700">•</span>
                                        <span x-show="item.province || item.city" class="inline-flex items-center gap-1 text-rose-600/90 dark:text-rose-400/90 font-medium">
                                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span x-text="[item.province, item.city].filter(Boolean).join('، ')"></span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Action Area --}}
                                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm text-gray-500 font-medium">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span>انتخاب و ثبت نوبت</span>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5 transform transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>

                {{-- Filter Empty State --}}
                <div x-show="filteredItems.length === 0"
                     class="max-w-2xl mx-auto bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-2xl p-12 text-center animate-in fade-in zoom-in duration-500"
                     style="display: none;">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 mb-4 shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        هیچ ارائه‌دهنده‌ای با این مشخصات یافت نشد
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">
                        لطفاً عبارت جستجو را تغییر دهید یا فیلترهای اعمال‌شده را پاک کنید.
                    </p>
                    <button type="button"
                            @click="resetFilters()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>پاک کردن همه فیلترها</span>
                    </button>
                </div>
            @else
                {{-- SERVICE FLOW GRID (جریان پیش‌فرض انتخاب سرویس‌ها) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-100">
                    @foreach($items as $item)
                        @php
                            $desc = $item->description ?? null;
                            $link = route('booking.public.service', $item->id);
                        @endphp

                        <a href="{{ $link }}"
                           class="group flex flex-col bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-[2rem] border border-gray-100 dark:border-gray-800 shadow-xl shadow-gray-200/20 dark:shadow-none hover:border-indigo-500/50 hover:-translate-y-2 transition-all duration-300 overflow-hidden h-full">

                            <div class="p-8 flex-1 flex flex-col">
                                {{-- Item Header --}}
                                <div class="flex items-start justify-between gap-4 mb-6">
                                    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-500 transition-colors duration-300 text-indigo-600 dark:text-indigo-400 overflow-hidden relative">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200 mb-1">
                                        {{ $item->name }}
                                    </h3>

                                    @if($desc)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-3 mt-3">
                                            {{ $desc }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Action Area --}}
                                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span>انتخاب زمان دلخواه</span>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5 transform transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="max-w-2xl mx-auto bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-2xl p-16 text-center animate-in fade-in zoom-in duration-500">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-50 dark:bg-gray-800 mb-6 shadow-inner">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-3">
                    @if($isProviderFlow) هیچ {{ config('booking.labels.provider') }} یافت نشد @else هیچ سرویسی یافت نشد @endif
                </h3>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    در حال حاضر هیچ @if($isProviderFlow) {{ config('booking.labels.provider') }} @else خدماتی @endif برای رزرو آنلاین در سیستم فعال نیست. لطفاً بعداً مراجعه کنید.
                </p>
            </div>
        @endif
    </div>
@endsection

@if($isProviderFlow)
    @push('scripts')
        <script>
            function providerFilterManager(initialItems, locationsData) {
                return {
                    rawItems: initialItems || [],
                    provincesData: locationsData || {},
                    search: '',
                    selectedSpecialty: '',
                    selectedProvince: '',
                    selectedCity: '',
                    sortBy: 'default',
                    cities: [],

                    init() {
                        const params = new URLSearchParams(window.location.search);
                        if (params.get('search')) this.search = params.get('search');
                        if (params.get('specialty')) this.selectedSpecialty = params.get('specialty');
                        if (params.get('province')) {
                            this.selectedProvince = params.get('province');
                            this.updateCities();
                        }
                        if (params.get('city')) this.selectedCity = params.get('city');
                        if (params.get('sort')) this.sortBy = params.get('sort');

                        this.$watch('selectedProvince', () => {
                            this.updateCities();
                        });
                    },

                    updateCities() {
                        this.cities = (this.selectedProvince && this.provincesData[this.selectedProvince])
                            ? this.provincesData[this.selectedProvince]
                            : [];
                        if (this.selectedCity && !this.cities.includes(this.selectedCity)) {
                            this.selectedCity = '';
                        }
                    },

                    toFa(num) {
                        if (num === null || num === undefined) return '';
                        return String(num).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
                    },

                    get hasActiveFilters() {
                        return this.search !== '' ||
                               this.selectedSpecialty !== '' ||
                               this.selectedProvince !== '' ||
                               this.selectedCity !== '' ||
                               this.sortBy !== 'default';
                    },

                    resetFilters() {
                        this.search = '';
                        this.selectedSpecialty = '';
                        this.selectedProvince = '';
                        this.selectedCity = '';
                        this.sortBy = 'default';
                        this.cities = [];
                    },

                    get filteredItems() {
                        let list = [...this.rawItems];

                        // 1. جستجوی متنی
                        if (this.search.trim() !== '') {
                            const q = this.search.trim().toLowerCase();
                            list = list.filter(item => {
                                const name = (item.name || '').toLowerCase();
                                const specText = (item.specialty_text || '').toLowerCase();
                                const clinic = (item.clinic || '').toLowerCase();
                                const prov = (item.province || '').toLowerCase();
                                const city = (item.city || '').toLowerCase();
                                const specs = (item.specialties || []).map(s => String(s).toLowerCase()).join(' ');

                                return name.includes(q) ||
                                       specText.includes(q) ||
                                       specs.includes(q) ||
                                       clinic.includes(q) ||
                                       prov.includes(q) ||
                                       city.includes(q);
                            });
                        }

                        // 2. فیلتر تخصص
                        if (this.selectedSpecialty) {
                            list = list.filter(item => {
                                if (Array.isArray(item.specialties) && item.specialties.includes(this.selectedSpecialty)) {
                                    return true;
                                }
                                if (item.specialty_text && item.specialty_text.includes(this.selectedSpecialty)) {
                                    return true;
                                }
                                return false;
                            });
                        }

                        // 3. فیلتر استان
                        if (this.selectedProvince) {
                            list = list.filter(item => item.province === this.selectedProvince);
                        }

                        // 4. فیلتر شهر
                        if (this.selectedCity) {
                            list = list.filter(item => item.city === this.selectedCity);
                        }

                        // 5. مرتب‌سازی
                        if (this.sortBy === 'popular') {
                            list.sort((a, b) => {
                                if (b.rating !== a.rating) return b.rating - a.rating;
                                return b.reviews_count - a.reviews_count;
                            });
                        } else if (this.sortBy === 'bookings') {
                            list.sort((a, b) => b.bookings_count - a.bookings_count);
                        } else if (this.sortBy === 'satisfaction') {
                            list.sort((a, b) => b.satisfaction_rate - a.satisfaction_rate);
                        }

                        return list;
                    }
                };
            }
        </script>
    @endpush
@endif

