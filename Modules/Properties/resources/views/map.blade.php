<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CRM هوشمند') }} - نقشه املاک</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body {
            font-family: 'IRANYekanX', sans-serif;
        }
        #map {
            height: calc(100vh - 80px); /* Adjust based on header height */
            width: 100%;
            z-index: 0;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(229, 231, 235, 0.5);
        }
        .leaflet-popup-content {
            margin: 0;
            width: 300px !important;
        }
        .leaflet-popup-tip {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        /* Custom Scrollbar for Sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 20px;
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

        document.addEventListener('DOMContentLoaded', function() {
            togglePriceInputs();
        });
    </script>
</head>

<body class="antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col relative overflow-hidden">

<header class="fixed w-full top-0 z-50 transition-all duration-300 backdrop-blur-md border-b border-transparent bg-white/90 dark:bg-gray-950/90 shadow-sm h-20">
    <div class="max-w-7xl mx-auto px-6 h-full flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <span class="font-bold text-xl tracking-tight hidden sm:block">سیستم CRM</span>
            </a>
        </div>

        <div class="flex-1 mx-4 hidden md:block">
             <form action="{{ route('properties.map') }}" method="GET" class="flex gap-2">
                 <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجو در نقشه..." class="w-full max-w-md rounded-xl border-gray-200 bg-gray-50 px-4 py-2 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                 <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors">جستجو</button>

                 @if(request()->anyFilled(['search', 'listing_type', 'property_type', 'min_price', 'max_price', 'category_id', 'building_id']))
                    <a href="{{ route('properties.map') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-bold hover:bg-gray-300 transition-colors dark:bg-gray-700 dark:text-gray-300">پاک کردن</a>
                 @endif
             </form>
        </div>

        <nav class="flex items-center gap-4">
            <a href="{{ route('properties.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors dark:text-gray-300 dark:hover:text-white">
                نمایش لیستی
            </a>
            @auth
                <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-all dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 shadow-lg shadow-gray-200/50 dark:shadow-none">
                    <span>پنل مدیریت</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors dark:text-gray-300 dark:hover:text-white">
                    ورود به سیستم
                </a>
            @endauth
        </nav>
    </div>
</header>

<main class="flex-grow pt-20 relative z-0 flex">
    <!-- Sidebar Filters (Desktop) -->
    <div class="hidden md:block w-80 bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 h-[calc(100vh-80px)] overflow-y-auto custom-scrollbar z-10 shadow-lg">
        <div class="p-5 space-y-6">
            <h2 class="text-lg font-bold flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                فیلترهای پیشرفته
            </h2>

            <form action="{{ route('properties.map') }}" method="GET" class="space-y-5">
                <!-- Search (Mobile/Sidebar) -->
                <div class="md:hidden">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">جستجو</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                </div>

                <!-- Listing Type -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع معامله</label>
                    <select name="listing_type" id="listing_type" onchange="togglePriceInputs()" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">همه موارد</option>
                        <option value="sale" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>فروش</option>
                        <option value="rent" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>رهن و اجاره</option>
                        <option value="presale" {{ request('listing_type') == 'presale' ? 'selected' : '' }}>پیش‌فروش</option>
                    </select>
                </div>

                <!-- Property Type -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع ملک</label>
                    <select name="property_type" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">همه موارد</option>
                        <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>آپارتمان</option>
                        <option value="villa" {{ request('property_type') == 'villa' ? 'selected' : '' }}>ویلا</option>
                        <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>زمین</option>
                        <option value="office" {{ request('property_type') == 'office' ? 'selected' : '' }}>اداری</option>
                        <option value="store" {{ request('property_type') == 'store' ? 'selected' : '' }}>مغازه</option>
                    </select>
                </div>

                <!-- Document Type -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع سند</label>
                    <select name="document_type" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">همه موارد</option>
                        @foreach(\Modules\Properties\Entities\Property::DOCUMENT_TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('document_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category (New) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">دسته‌بندی</label>
                    <select name="category_id" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @if(isset($categories))
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Building (New) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ساختمان</label>
                    <select name="building_id" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">همه ساختمان‌ها</option>
                        @if(isset($buildings))
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>{{ $building->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Price Range -->
                <div id="sale_price_inputs" class="{{ request('listing_type') == 'rent' ? 'hidden' : '' }}">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">محدوده قیمت (تومان)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="حداقل" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="حداکثر" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>

                <div id="rent_price_inputs" class="{{ request('listing_type') == 'rent' ? '' : 'hidden' }} space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">محدوده رهن</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_deposit_price" value="{{ request('min_deposit_price') }}" placeholder="حداقل" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <input type="number" name="max_deposit_price" value="{{ request('max_deposit_price') }}" placeholder="حداکثر" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">محدوده اجاره</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_rent_price" value="{{ request('min_rent_price') }}" placeholder="حداقل" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <input type="number" name="max_rent_price" value="{{ request('max_rent_price') }}" placeholder="حداکثر" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        </div>
                    </div>
                </div>

                <!-- Area Range -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">متراژ (متر مربع)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_area" value="{{ request('min_area') }}" placeholder="حداقل" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <input type="number" name="max_area" value="{{ request('max_area') }}" placeholder="حداکثر" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>

                <!-- Dynamic Attributes -->
                @foreach($filterableAttributes as $attribute)
                    @if($attribute->is_range_filter && $attribute->type === 'number')
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $attribute->name }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="min_attr_{{ $attribute->id }}" value="{{ request('min_attr_' . $attribute->id) }}" placeholder="حداقل" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <input type="number" name="max_attr_{{ $attribute->id }}" value="{{ request('max_attr_' . $attribute->id) }}" placeholder="حداکثر" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $attribute->name }}</label>
                            @if($attribute->type === 'select')
                                <select name="attr_{{ $attribute->id }}" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">همه</option>
                                    @if(is_array($attribute->options))
                                        @foreach($attribute->options as $option)
                                            <option value="{{ $option }}" {{ request('attr_' . $attribute->id) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            @else
                                <input type="text" name="attr_{{ $attribute->id }}" value="{{ request('attr_' . $attribute->id) }}" placeholder="{{ $attribute->name }}..." class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            @endif
                        </div>
                    @endif
                @endforeach

                <!-- Features -->
                @if($features->count() > 0)
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-3">امکانات رفاهی</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($features as $feature)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="features[]" value="{{ $feature->id }}" {{ in_array($feature->id, request('features', [])) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $feature->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Special & Show All -->
                <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="special" value="1" {{ request('special') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-amber-500 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">فقط آگهی‌های ویژه</span>
                    </label>

                    @auth
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">نمایش همه املاک</span>
                        </label>
                    @endauth
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                    اعمال فیلتر
                </button>
            </form>
        </div>
    </div>

    <div class="flex-1 relative">
        <div id="map"></div>

        <!-- Filter Toggle Button (Mobile) -->
        <button onclick="document.getElementById('mobile-filters').classList.remove('translate-x-full')" class="md:hidden fixed bottom-6 right-6 z-[1000] bg-indigo-600 text-white p-4 rounded-full shadow-lg hover:bg-indigo-700 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
        </button>
    </div>

    <!-- Mobile Filters Sidebar -->
    <div id="mobile-filters" class="fixed inset-0 z-[2000] transform translate-x-full transition-transform duration-300 md:hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('mobile-filters').classList.add('translate-x-full')"></div>
        <div class="absolute right-0 top-0 bottom-0 w-80 bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto custom-scrollbar">
            <div class="p-5">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold">فیلترها</h2>
                    <button onclick="document.getElementById('mobile-filters').classList.add('translate-x-full')" class="text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <!-- Copy of the form for mobile -->
                <form action="{{ route('properties.map') }}" method="GET" class="space-y-5">
                    <!-- Same fields as desktop sidebar -->
                    <!-- Listing Type -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع معامله</label>
                        <select name="listing_type" onchange="togglePriceInputs()" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">همه موارد</option>
                            <option value="sale" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>فروش</option>
                            <option value="rent" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>رهن و اجاره</option>
                            <option value="presale" {{ request('listing_type') == 'presale' ? 'selected' : '' }}>پیش‌فروش</option>
                        </select>
                    </div>
                    <!-- Property Type -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع ملک</label>
                        <select name="property_type" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">همه موارد</option>
                            <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>آپارتمان</option>
                            <option value="villa" {{ request('property_type') == 'villa' ? 'selected' : '' }}>ویلا</option>
                            <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>زمین</option>
                            <option value="office" {{ request('property_type') == 'office' ? 'selected' : '' }}>اداری</option>
                            <option value="store" {{ request('property_type') == 'store' ? 'selected' : '' }}>مغازه</option>
                        </select>
                    </div>

                    <!-- Category (New Mobile) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">دسته‌بندی</label>
                        <select name="category_id" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">همه دسته‌بندی‌ها</option>
                            @if(isset($categories))
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Building (New Mobile) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ساختمان</label>
                        <select name="building_id" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">همه ساختمان‌ها</option>
                            @if(isset($buildings))
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>{{ $building->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                        اعمال فیلتر
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map
        const map = L.map('map').setView([35.6892, 51.3890], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const markers = [];
        const bounds = L.latLngBounds();

        // Office Location Marker
        @if(isset($officeLocation['lat']) && isset($officeLocation['lng']) && $officeLocation['lat'] && $officeLocation['lng'])
            (function() {
                const lat = {{ $officeLocation['lat'] }};
                const lng = {{ $officeLocation['lng'] }};
                const title = "{{ $officeLocation['title'] }}";

                // Custom Icon for Office
                const officeIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                const marker = L.marker([lat, lng], {icon: officeIcon}).addTo(map);
                marker.bindPopup(`<div style="text-align:center; font-weight:bold;">${title}</div>`);

                markers.push(marker);
                bounds.extend([lat, lng]);
            })();
        @endif

        @foreach($properties as $property)
            @if($property->latitude && $property->longitude)
                (function() {
                    const lat = {{ $property->latitude }};
                    const lng = {{ $property->longitude }};
                    const title = "{{ $property->title }}";
                    const image = "{{ $property->cover_image ? asset('storage/' . $property->cover_image) : '' }}";
                    const price = "{{ $property->listing_type == 'rent' ? 'رهن: ' . number_format($property->deposit_price) : number_format($property->price) . ' تومان' }}";
                    const link = "{{ route('properties.show', $property->slug) }}";
                    const type = "{{ match($property->listing_type) { 'sale' => 'فروش', 'rent' => 'اجاره', 'presale' => 'پیش‌فروش', default => '' } }}";
                    const area = "{{ $property->area ? number_format($property->area) . ' متر' : '' }}";
                    const code = "{{ $property->code }}";

                    const popupContent = `
                        <div class="property-popup group relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
                            <div class="relative h-40 overflow-hidden">
                                ${image ? `<img src="${image}" alt="${title}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">` : '<div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></div>'}
                                <div class="absolute top-3 right-3">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white/90 text-gray-800 shadow-sm backdrop-blur-sm">
                                        ${type}
                                    </span>
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-2 line-clamp-1 group-hover:text-indigo-600 transition-colors" title="${title}">${title}</h3>

                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-indigo-600 dark:text-indigo-400 font-bold text-sm">${price}</span>
                                    ${area ? `<span class="text-[10px] text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 px-2 py-0.5 rounded-md">${area}</span>` : ''}
                                </div>

                                <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700/50">
                                    <span class="text-[10px] text-gray-400 font-mono">Code: ${code}</span>
                                    <a href="${link}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                        مشاهده
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;

                    const marker = L.marker([lat, lng]).addTo(map);
                    marker.bindPopup(popupContent);

                    markers.push(marker);
                    bounds.extend([lat, lng]);
                })();
            @endif
        @endforeach

        if (markers.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    });
</script>

</body>
</html>
