<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CRM هوشمند') }} - لیست املاک</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'IRANYekanX', sans-serif;
        }

        /* افکت گرادینت پس‌زمینه */
        .gradient-bg {
            background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
        }
    </style>

    <script>
        function togglePriceInputs() {
            const listingType = document.getElementById('listing_type').value;
            const saleInputs = document.getElementById('sale_price_inputs');
            const rentInputs = document.getElementById('rent_price_inputs');

            if (listingType === 'rent') {
                saleInputs.classList.add('hidden');
                rentInputs.classList.remove('hidden');
            } else {
                saleInputs.classList.remove('hidden');
                rentInputs.classList.add('hidden');
            }
        }

        function formatNumber(n) {
            // حذف کاراکترهای غیر عددی
            let value = n.replace(/\D/g, "");
            // افزودن کاما
            return value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        document.addEventListener('DOMContentLoaded', function() {
            togglePriceInputs();

            const priceInputs = document.querySelectorAll('.price-input');

            priceInputs.forEach(input => {
                // فرمت‌دهی اولیه هنگام لود صفحه
                if (input.value) {
                    input.value = formatNumber(input.value);
                }

                // فرمت‌دهی هنگام تایپ
                input.addEventListener('input', function() {
                    this.value = formatNumber(this.value);
                });
            });

            // حذف کاماها قبل از ارسال فرم
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    priceInputs.forEach(input => {
                        input.value = input.value.replace(/,/g, '');
                    });
                });
            }
        });
    </script>
</head>

<body
    class="antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col relative overflow-x-hidden"
    x-data="propertyList()">

@php
    $aiSearchEnabled = \Modules\Properties\Entities\PropertySetting::get('ai_property_search', 0);
@endphp

<div class="fixed inset-0 gradient-bg pointer-events-none z-0"></div>
<div
    class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-indigo-500/20 blur-[100px] rounded-full pointer-events-none z-0 dark:bg-indigo-500/10">
</div>

<header
    class="fixed w-full top-0 z-50 transition-all duration-300 backdrop-blur-md border-b border-transparent bg-white/50 dark:bg-gray-950/50">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <span class="font-bold text-xl tracking-tight hidden sm:block">سیستم CRM</span>
            </a>
        </div>

        <nav class="flex items-center gap-4">
            <a href="{{ route('properties.map') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors dark:text-gray-300 dark:hover:text-white">
                نمایش روی نقشه
            </a>
            @auth
                <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-all dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 shadow-lg shadow-gray-200/50 dark:shadow-none">
                    <span>پنل مدیریت</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors dark:text-gray-300 dark:hover:text-white">
                    ورود به سیستم
                </a>
            @endauth
        </nav>
    </div>
</header>

<main class="flex-grow flex flex-col pt-32 pb-16 px-6 relative z-10">
    <div class="max-w-7xl mx-auto w-full space-y-8">

        {{-- Header Section --}}
        <div class="text-center space-y-4 mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div
                class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg mb-4 shadow-indigo-500/30">
                {{-- House Icon --}}
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">لیست <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-emerald-500">املاک</span></h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                ملک مورد نظر خود را جستجو کنید و جزئیات آن را مشاهده نمایید
            </p>
        </div>

        {{-- AI Search Trigger --}}
        @if($aiSearchEnabled)
            <div class="bg-purple-50 dark:bg-gray-800/50 rounded-2xl border border-purple-200 dark:border-purple-700 shadow-sm p-5 flex items-center justify-between animate-in fade-in slide-in-from-bottom-5 duration-700 delay-50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                         <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-purple-900 dark:text-purple-200">جستجوی هوشمند</h3>
                        <p class="text-sm text-purple-700 dark:text-purple-300">ملک مورد نظر خود را به زبان ساده توصیف کنید.</p>
                    </div>
                </div>
                <button @click="showAiModal = true"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-bold hover:bg-purple-700 hover:shadow-lg hover:shadow-purple-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    شروع کنید
                </button>
            </div>
        @endif

        {{-- Advanced Search Box --}}
        <div class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl p-6 mb-12 animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
            <form action="{{ route('properties.index') }}" method="GET" class="space-y-6">
                {{-- Top Row: Search & Main Filters --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search Input --}}
                    <div class="md:col-span-4 lg:col-span-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">جستجو</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="عنوان، کد، آدرس، توضیحات..."
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 pl-10 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Listing Type --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع معامله</label>
                        <select name="listing_type" id="listing_type" onchange="togglePriceInputs()" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            <option value="">همه موارد</option>
                            <option value="sale" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>فروش</option>
                            <option value="rent" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>رهن و اجاره</option>
                            <option value="presale" {{ request('listing_type') == 'presale' ? 'selected' : '' }}>پیش‌فروش</option>
                        </select>
                    </div>

                    {{-- Property Type --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع ملک</label>
                        <select name="property_type" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            <option value="">همه موارد</option>
                            <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>آپارتمان</option>
                            <option value="villa" {{ request('property_type') == 'villa' ? 'selected' : '' }}>ویلا</option>
                            <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>زمین</option>
                            <option value="office" {{ request('property_type') == 'office' ? 'selected' : '' }}>اداری</option>
                            <option value="store" {{ request('property_type') == 'store' ? 'selected' : '' }}>مغازه</option>
                        </select>
                    </div>

                    {{-- Document Type --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع سند</label>
                        <select name="document_type" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            <option value="">همه موارد</option>
                            @foreach(\Modules\Properties\Entities\Property::DOCUMENT_TYPES as $key => $label)
                                <option value="{{ $key }}" {{ request('document_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Second Row: Category & Building (Public) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    {{-- Category --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">دسته‌بندی</label>
                        <select name="category_id" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            <option value="">همه دسته‌بندی‌ها</option>
                            @if(isset($categories))
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Building --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ساختمان</label>
                        <select name="building_id" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            <option value="">همه ساختمان‌ها</option>
                            @if(isset($buildings))
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>{{ $building->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Dynamic Attributes & Features Filter --}}
                @php
                    $hasAttributes = isset($filterableAttributes) && $filterableAttributes->count() > 0;
                    $hasFeatures = isset($features) && $features->count() > 0;
                @endphp

                @if($hasAttributes || $hasFeatures)
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        {{-- Attributes --}}
                        @if($hasAttributes)
                            @foreach($filterableAttributes as $attribute)
                                @if($attribute->is_range_filter && $attribute->type === 'number')
                                    <div class="md:col-span-2 grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداقل {{ $attribute->name }}</label>
                                            <input type="number" name="min_attr_{{ $attribute->id }}" value="{{ request('min_attr_' . $attribute->id) }}" placeholder="حداقل..."
                                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداکثر {{ $attribute->name }}</label>
                                            <input type="number" name="max_attr_{{ $attribute->id }}" value="{{ request('max_attr_' . $attribute->id) }}" placeholder="حداکثر..."
                                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                                        </div>
                                    </div>
                                @else
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $attribute->name }}</label>
                                        @if($attribute->type === 'select')
                                            <select name="attr_{{ $attribute->id }}" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                                                <option value="">همه</option>
                                                @if(is_array($attribute->options))
                                                    @foreach($attribute->options as $option)
                                                        <option value="{{ $option }}" {{ request('attr_' . $attribute->id) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @else
                                            <input type="text" name="attr_{{ $attribute->id }}" value="{{ request('attr_' . $attribute->id) }}" placeholder="{{ $attribute->name }}..."
                                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        {{-- Features Checkboxes --}}
                        @if($hasFeatures)
                            <div class="md:col-span-4 mt-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-3">امکانات رفاهی</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                    @foreach($features as $feature)
                                        <label class="flex items-center gap-2 p-2.5 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                            <input type="checkbox" name="features[]" value="{{ $feature->id }}"
                                                   {{ in_array($feature->id, request('features', [])) ? 'checked' : '' }}
                                                   class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feature->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Bottom Row: Price Range --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    {{-- Price Range (Sale) --}}
                    <div id="sale_price_inputs" class="lg:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-4 {{ request('listing_type') == 'rent' ? 'hidden' : '' }}">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداقل قیمت (تومان)</label>
                            <input type="text" name="min_price" value="{{ request('min_price') }}" placeholder="مثلا: 100000000"
                                   class="price-input w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداکثر قیمت (تومان)</label>
                            <input type="text" name="max_price" value="{{ request('max_price') }}" placeholder="مثلا: 5000000000"
                                   class="price-input w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                        </div>
                    </div>

                    {{-- Price Range (Rent) --}}
                    <div id="rent_price_inputs" class="lg:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-4 {{ request('listing_type') == 'rent' ? '' : 'hidden' }}">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداقل رهن</label>
                                <input type="text" name="min_deposit_price" value="{{ request('min_deposit_price') }}" placeholder="تومان"
                                       class="price-input w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداکثر رهن</label>
                                <input type="text" name="max_deposit_price" value="{{ request('max_deposit_price') }}" placeholder="تومان"
                                       class="price-input w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداقل اجاره</label>
                                <input type="text" name="min_rent_price" value="{{ request('min_rent_price') }}" placeholder="تومان"
                                       class="price-input w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداکثر اجاره</label>
                                <input type="text" name="max_rent_price" value="{{ request('max_rent_price') }}" placeholder="تومان"
                                       class="price-input w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-between items-center gap-3 pt-2">
                    <div class="flex items-center gap-4">
                        {{-- Special Filter Toggle --}}
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="special" value="1" {{ request('special') ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 dark:peer-focus:ring-amber-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-500"></div>
                            <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-amber-600 transition-colors">فقط آگهی‌های ویژه</span>
                        </label>

                        {{-- Show All Filter Toggle (For All Logged-in Users) --}}
                        @auth
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="checkbox" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">نمایش همه املاک</span>
                            </label>
                        @endauth
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('properties.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
                            پاک کردن فیلترها
                        </a>
                        <button type="submit" class="px-8 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/50 transition-all transform active:scale-95 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            جستجو و فیلتر
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Properties Grid --}}
        @if($properties->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-100">
                @foreach($properties as $property)
                    @php
                        $isMyProperty = auth()->check() && ($property->created_by === auth()->id() || $property->agent_id === auth()->id());
                        $cardClass = $isMyProperty
                            ? 'bg-indigo-50 dark:bg-indigo-900/20 border-2 border-indigo-500 dark:border-indigo-400 shadow-xl shadow-indigo-200/50 dark:shadow-indigo-900/30 ring-2 ring-indigo-200 dark:ring-indigo-800 ring-offset-2 dark:ring-offset-gray-900'
                            : 'bg-white dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 shadow-lg shadow-gray-200/40 dark:shadow-none';
                    @endphp
                    <a href="{{ route('properties.show', $property->slug) }}"
                       class="group relative flex flex-col backdrop-blur-sm rounded-2xl border shadow-lg hover:border-indigo-500/30 transition-all duration-300 overflow-hidden hover:-translate-y-1 h-full {{ $cardClass }}">

                        {{-- Image Section --}}
                        <div class="relative h-56 w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                            @if($property->cover_image)
                                <img src="{{ asset('storage/' . $property->cover_image) }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                            @endif

                            {{-- My Property Badge --}}
                            @if($isMyProperty)
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">
                                    <span class="px-4 py-1 rounded-b-xl text-xs font-bold bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        ملک من
                                    </span>
                                </div>
                            @endif

                            {{-- Special Badge --}}
                            @if(isset($property->meta['is_special']) && $property->meta['is_special'])
                                <div class="absolute top-4 left-4 z-10">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-500 text-white shadow-lg shadow-amber-500/30 flex items-center gap-1 animate-pulse">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                        ویژه
                                    </span>
                                </div>
                            @endif

                            {{-- Badges --}}
                            <div class="absolute top-4 right-4 flex flex-col gap-2">
                                @if($property->status)
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold text-white shadow-lg"
                                          style="background-color: {{ $property->status->color }}; box-shadow: 0 4px 6px -1px {{ $property->status->color }}40;">
                                        {{ $property->status->label ?? $property->status->name }}
                                    </span>
                                @endif
                                @if($property->listing_type)
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                                        {{ match($property->listing_type) {
                                            'sale' => 'فروش',
                                            'rent' => 'اجاره',
                                            'presale' => 'پیش‌فروش',
                                            default => $property->listing_type
                                        } }}
                                    </span>
                                @endif
                                @if($property->property_type)
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-white/90 dark:bg-gray-900/90 text-gray-700 dark:text-gray-300 backdrop-blur-sm shadow-sm">
                                        {{ match($property->property_type) {
                                            'apartment' => 'آپارتمان',
                                            'villa' => 'ویلا',
                                            'land' => 'زمین',
                                            'office' => 'اداری',
                                            'store' => 'مغازه',
                                            default => $property->property_type
                                        } }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-5 flex-1 flex flex-col space-y-4">
                            {{-- Title & Address --}}
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200 line-clamp-1 mb-1">
                                    {{ $property->title }}
                                </h3>
                                @if($property->address)
                                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-xs">
                                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <span class="line-clamp-1">{{ $property->address }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Features Grid --}}
                            @if($showFeatures)
                                <div class="grid grid-cols-3 gap-2 py-3 border-y border-gray-100 dark:border-gray-800">
                                    @if($property->area)
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <span class="text-xs text-gray-400 mb-1">متراژ</span>
                                            <div class="flex items-center gap-1 text-sm font-bold text-gray-700 dark:text-gray-300">
                                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                                </svg>
                                                <span>{{ number_format($property->area) }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex flex-col items-center justify-center text-center border-r border-gray-100 dark:border-gray-800 col-span-2">
                                        <span class="text-xs text-gray-400 mb-1">کد ملک</span>
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 font-mono">{{ $property->code ?? '-' }}</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Price Section --}}
                            <div class="flex flex-col gap-1">
                                @if($property->listing_type == 'rent')
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">رهن:</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            @if($property->deposit_price == 0)
                                                توافقی
                                            @else
                                                {{ number_format($property->deposit_price) }} <span class="text-xs font-normal text-gray-500">تومان</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">اجاره:</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            @if($property->rent_price == 0)
                                                توافقی
                                            @else
                                                {{ number_format($property->rent_price) }} <span class="text-xs font-normal text-gray-500">تومان</span>
                                            @endif
                                        </span>
                                    </div>
                                @else
                                    <div class="flex justify-between items-center mt-auto">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">قیمت کل:</span>
                                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                            @if($property->price == 0)
                                                توافقی
                                            @else
                                                {{ number_format($property->price) }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">تومان</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Action Button --}}
                            <div class="mt-auto pt-4">
                                <div
                                    class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm text-center group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 flex items-center justify-center gap-2 group-hover:shadow-lg group-hover:shadow-indigo-600/30">
                                    <span>مشاهده جزئیات</span>
                                    <svg class="w-4 h-4 transform transition-transform duration-300 group-hover:-translate-x-1" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12 flex justify-center">
                {{ $properties->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div
                class="bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-2xl border border-gray-100 dark:border-gray-800 shadow-lg p-12 text-center animate-in fade-in zoom-in duration-500">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">ملکی یافت نشد</h3>
                <p class="text-gray-500 dark:text-gray-400">با فیلترهای انتخاب شده هیچ ملکی پیدا نشد.</p>
                <a href="{{ route('properties.index') }}" class="inline-block mt-4 text-indigo-600 hover:underline">پاک کردن فیلترها</a>
            </div>
        @endif
    </div>
</main>

<footer class="border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 py-8 z-10">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} تمام حقوق محفوظ است.
        </p>
        <div class="flex items-center gap-6 text-sm text-gray-500">
            <a href="#" class="hover:text-indigo-600 transition-colors">قوانین و مقررات</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">حریم خصوصی</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">تماس با ما</a>
        </div>
    </div>
</footer>

{{-- AI Search Modal --}}
@if($aiSearchEnabled)
<div x-show="showAiModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100] overflow-y-auto"
     style="display: none;">

    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showAiModal = false"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="showAiModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">

            <div class="bg-purple-50/50 dark:bg-purple-900/20 px-6 py-4 border-b border-purple-100 dark:border-purple-800 flex items-center justify-between">
                <h3 class="text-lg font-bold text-purple-900 dark:text-purple-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    جستجوی هوشمند ملک
                </h3>
                <button @click="showAiModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="px-6 py-6 space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    توضیح دهید چه ملکی مد نظرتان است. هوش مصنوعی بهترین گزینه‌ها را برای شما پیدا می‌کند.
                </p>
                <textarea x-model="aiQuery" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 resize-none" placeholder="مثلاً: یک آپارتمان دو خوابه در سعادت آباد با قیمت حدود ۵ میلیارد تومان..."></textarea>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900/30 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-700">
                <button type="button" @click="performAiSearch" :disabled="isAiSearching || aiQuery.length < 3"
                        class="inline-flex w-full justify-center rounded-xl border border-transparent bg-purple-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 sm:ml-3 sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="!isAiSearching">جستجو کن</span>
                    <span x-show="isAiSearching" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        در حال تحلیل...
                    </span>
                </button>
                <button type="button" @click="showAiModal = false"
                        class="mt-3 inline-flex w-full justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    انصراف
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    function propertyList() {
        return {
            showAiModal: false,
            aiQuery: '',
            isAiSearching: false,
            async performAiSearch() {
                if (this.aiQuery.length < 3) return;
                this.isAiSearching = true;
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ auth()->check() ? route("user.properties.ai.search") : "" }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ query: this.aiQuery })
                    });
                    const result = await response.json();
                    if (response.ok && result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        // You might want to add a notification system here
                        alert(result.error || 'خطا در جستجو.');
                        this.isAiSearching = false;
                    }
                } catch (error) {
                    console.error('AI Search Error:', error);
                    alert('خطا در ارتباط با سرور.');
                    this.isAiSearching = false;
                }
            }
        }
    }
</script>

</body>

</html>
