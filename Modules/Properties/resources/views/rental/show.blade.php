@extends('layouts.web')

@section('title', $property->title . ' - اجاره ویلا و اقامتگاه')

@push('styles')
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* استایل‌های اختصاصی پاپ‌آپ نقشه */
        .leaflet-popup-content-wrapper {
            border-radius: 1rem !important; padding: 0 !important; overflow: hidden !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #f3f4f6 !important; background-color: #ffffff !important;
        }
        .dark .leaflet-popup-content-wrapper { background-color: #1f2937 !important; border-color: #374151 !important; }
        .leaflet-popup-tip { background-color: #ffffff !important; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important; }
        .dark .leaflet-popup-tip { background-color: #1f2937 !important; }
        .leaflet-popup-content { margin: 0 !important; width: auto !important; min-width: 240px !important; font-family: inherit !important; }
        .leaflet-popup-close-button {
            color: #9ca3af !important; top: 10px !important; right: 10px !important; width: 24px !important; height: 24px !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
            background: #f3f4f6 !important; border-radius: 50% !important; text-decoration: none !important; font-size: 16px !important;
            transition: all 0.2s ease !important; z-index: 10 !important;
        }
        .dark .leaflet-popup-close-button { background: #374151 !important; color: #d1d5db !important; }
        .leaflet-container a.leaflet-popup-close-button { padding: 0 !important; }
    </style>
@endpush

@section('content')
    @php
        $checkVisibility = function($key, $isRestrictedByDefault = false) use ($property, $visibilitySettings) {
            $allowedRoles = $visibilitySettings[$key] ?? [];
            if (empty($allowedRoles)) {
                if ($isRestrictedByDefault) return ['super-admin', 'admin'];
                else return true;
            }
            if (!auth()->check()) return in_array('guest', $allowedRoles);

            $user = auth()->user();
            if ($user->id == $property->created_by || $user->id == $property->agent_id) return true;
            if ($user->hasRole('super-admin')) return true;
            return $user->hasAnyRole($allowedRoles);
        };

        $canViewOwner = $checkVisibility('owner_info', true);
        $canViewNotes = $checkVisibility('confidential_notes', true);
        $canViewPrice = $checkVisibility('price_info', false);
        $canViewMap   = $checkVisibility('map_info', false);
        $canViewCover = $checkVisibility('cover_image', false);
        $canViewGallery = $checkVisibility('gallery_images', false);

        $displayAgent = $property->agent ?? $property->creator;
        $host = $property->host;
        $config = $property->rentalConfig;
        $currencyLabel = $currency === 'toman' ? 'تومان' : 'ریال';
        $images = $property->images;

        $mapService = \Modules\Properties\Entities\PropertySetting::get('map_service', 'leaflet');
        $mapIrApiKey = \Modules\Properties\Entities\PropertySetting::get('map_ir_api_key', '');
        $showBookmarkButton = (bool) \Modules\Properties\Entities\PropertySetting::get('show_bookmark_button', 1);

        // فرمت استاندارد ساعت ورود و خروج بدون ثانیه (HH:mm)
        $formatTimeOnly = function($time, $default = '14:00') {
            if (empty($time)) return $default;
            if (preg_match('/^(\d{1,2}:\d{2})/', trim($time), $m)) {
                return $m[1];
            }
            return $time;
        };
        $checkInTimeFormatted = $formatTimeOnly($config?->check_in_time, '14:00');
        $checkOutTimeFormatted = $formatTimeOnly($config?->check_out_time, '12:00');

        // استخراج و هماهنگ‌سازی امکانات رفاهی و تفریحی (Features)
        $features = $property->attributeValues->filter(function($attr) {
            return $attr->attribute && $attr->attribute->section === 'features' && ($attr->value == '1' || $attr->value === true);
        });

        $rawCustomFeatures = isset($property->meta['features']) && is_array($property->meta['features']) ? $property->meta['features'] : [];
        $customFeatures = [];
        foreach($rawCustomFeatures as $cItem) {
            if (is_array($cItem)) {
                $val = $cItem['value'] ?? $cItem['name'] ?? reset($cItem);
            } else {
                $val = $cItem;
            }
            if (!empty($val) && is_string($val)) {
                $customFeatures[] = trim($val);
            }
        }

        // استخراج و ساختاردهی اطلاعات تکمیلی و مشخصات فنی (Details & Specs)
        $detailSpecs = collect();

        if ($property->property_type) {
            $detailSpecs->push([
                'name' => 'نوع اقامتگاه',
                'value' => match($property->property_type) {
                    'apartment' => 'آپارتمان',
                    'villa' => 'ویلایی',
                    'land' => 'زمین / باغ',
                    'commercial' => 'تجاری',
                    'office' => 'اداری',
                    default => $property->property_type
                }
            ]);
        }

        if ($property->category) {
            $detailSpecs->push(['name' => 'دسته‌بندی', 'value' => $property->category->name]);
        }

        if ($property->usage_type) {
            $detailSpecs->push([
                'name' => 'کاربری',
                'value' => match($property->usage_type) {
                    'residential' => 'مسکونی',
                    'industrial' => 'صنعتی',
                    'commercial' => 'تجاری',
                    'agricultural' => 'کشاورزی / زراعی',
                    'garden' => 'باغ',
                    'outsideTheTissue' => 'خارج از بافت',
                    default => $property->usage_type
                }
            ]);
        }

        if ($property->document_type) {
            $detailSpecs->push([
                'name' => 'نوع سند / مدرک',
                'value' => \Modules\Properties\Entities\Property::DOCUMENT_TYPES[$property->document_type] ?? $property->document_type
            ]);
        }

        // فیلدهای بخش اطلاعات تکمیلی در سیستم (section !== 'features')
        $detailsAttributes = $property->attributeValues->filter(function($attr) {
            if (!$attr->attribute) return false;
            if ($attr->attribute->section === 'features') return false;
            return $attr->value !== null && $attr->value !== '';
        });

        foreach ($detailsAttributes as $detail) {
            $val = $detail->value;
            if ($detail->attribute->type === 'checkbox') {
                $val = ($val == '1' || $val === true) ? 'دارد' : 'ندارد';
            }
            $detailSpecs->push([
                'name' => $detail->attribute->name,
                'value' => $val
            ]);
        }

        // ویژگی‌های سفارشی بخش اطلاعات تکمیلی (meta['details'])
        if (isset($property->meta['details']) && is_array($property->meta['details'])) {
            foreach ($property->meta['details'] as $key => $value) {
                if (is_array($value)) {
                    $name = $value['key'] ?? $value['name'] ?? (is_string($key) ? $key : null);
                    $val = $value['value'] ?? null;
                    if ($name && $val !== null && $val !== '') {
                        $detailSpecs->push(['name' => $name, 'value' => $val]);
                    }
                } elseif ($value !== null && $value !== '') {
                    $detailSpecs->push(['name' => $key, 'value' => $value]);
                }
            }
        }

        $detailSpecs = $detailSpecs->unique('name');

        // قوانین اقامتگاه
        $houseRules = $config ? ($config->house_rules ?? []) : [];
        $customRules = $houseRules['custom_rules'] ?? [];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-10 w-full font-sans" 
         x-data="rentalBookingApp(@js($calendarData['flatDates'] ?? []), @js($config), '{{ $currencyLabel }}', '{{ addslashes($property->title) }}', '{{ $property->code }}')">

        {{-- نوار ناوبری و عنوان --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-gray-500 dark:text-gray-400">
                <a href="{{ route('properties.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">املاک</a>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold">اقامتگاه‌های روزانه</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                <span class="text-gray-900 dark:text-white line-clamp-1 font-bold">{{ $property->title }}</span>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold border border-indigo-200/80 dark:border-indigo-800/50 flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                    کد اقامتگاه: {{ $property->code }}
                </span>
                @if($config && $config->instant_booking)
                    <span class="px-3 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-xs font-bold border border-amber-200 dark:border-amber-800/50 flex items-center gap-1 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                        رزرو آنی
                    </span>
                @endif
            </div>
        </div>

        {{-- عنوان اصلی و لوکیشن خلاصه --}}
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white leading-tight mb-2">
                {{ $property->title }}
            </h1>
            <p class="text-xs sm:text-sm font-bold text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>{{ $property->address ?? 'منطقه اقامتی' }}</span>
                @if($property->category)
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600 mx-1"></span>
                    <span class="text-indigo-600 dark:text-indigo-400">{{ $property->category->name }}</span>
                @endif
            </p>
        </div>

        {{-- گالری تصاویر مدرن موزاییکی (Hero Mosaic Gallery) --}}
        <div class="mb-10">
            @php
                $coverUrl = ($canViewCover && $property->cover_image) ? asset('storage/' . $property->cover_image) : null;
                $galleryItems = ($canViewGallery && $images) ? $images : collect();

                if ($coverUrl) {
                    // کاور همیشه در سمت راست قرار می‌گیرد
                    $heroImage = $coverUrl;
                    // تصاویر گالری در سمت چپ
                    $sideImages = $galleryItems->take(4);
                    // لیست کامل برای لایت‌باکس: ابتدا کاور، سپس بقیه تصاویر گالری بدون تکرار
                    $allGalleryUrls = collect([$coverUrl])
                        ->merge($galleryItems->map(fn($img) => asset('storage/' . $img->path)))
                        ->unique()
                        ->values();
                } elseif ($galleryItems->isNotEmpty()) {
                    // در صورت نبود کاور اختصاصی، اولین تصویر گالری به عنوان هیرو در سمت راست قرار می‌گیرد
                    $heroImage = asset('storage/' . $galleryItems->first()->path);
                    $sideImages = $galleryItems->slice(1, 4)->values();
                    $allGalleryUrls = $galleryItems->map(fn($img) => asset('storage/' . $img->path))->values();
                } else {
                    $heroImage = null;
                    $sideImages = collect();
                    $allGalleryUrls = collect();
                }
            @endphp

            @if($heroImage && $sideImages->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2.5 rounded-3xl overflow-hidden shadow-xl border border-gray-100 dark:border-gray-800 bg-gray-100 dark:bg-gray-900 relative group">
                    {{-- تصویر اصلی / کاور شاخص در سمت راست (در دسکتاپ نیمی از فضا: ۲ ستون از ۴ ستون) --}}
                    <div class="md:col-span-2 h-72 sm:h-96 md:h-[450px] relative overflow-hidden cursor-pointer" onclick="openLightbox(0)">
                        <img src="{{ $heroImage }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105">
                        <div class="absolute inset-0 bg-black/10 hover:bg-transparent transition-colors"></div>
                    </div>

                    {{-- شبکه تصاویر گالری در سمت چپ بر اساس تعداد تصاویر --}}
                    @if($sideImages->count() === 1)
                        {{-- تک تصویر جانبی تمام‌ارتفاع --}}
                        <div class="hidden md:block md:col-span-2 h-[450px] relative overflow-hidden cursor-pointer" onclick="openLightbox(1)">
                            <img src="{{ asset('storage/' . $sideImages->first()->path) }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105">
                            <div class="absolute inset-0 bg-black/10 hover:bg-transparent transition-colors"></div>
                        </div>
                    @elseif($sideImages->count() === 2)
                        {{-- دو تصویر جانبی عمودی روی هم --}}
                        <div class="hidden md:grid md:col-span-2 grid-cols-1 gap-2.5 h-[450px]">
                            @foreach($sideImages as $sIdx => $img)
                                <div class="relative overflow-hidden cursor-pointer h-[220px]" onclick="openLightbox({{ $sIdx + 1 }})">
                                    <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105">
                                    <div class="absolute inset-0 bg-black/10 hover:bg-transparent transition-colors"></div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($sideImages->count() === 3)
                        {{-- سه تصویر: یک بزرگ + دو کوچک روی هم --}}
                        <div class="hidden md:grid md:col-span-2 grid-cols-2 gap-2.5 h-[450px]">
                            <div class="relative overflow-hidden cursor-pointer h-[450px]" onclick="openLightbox(1)">
                                <img src="{{ asset('storage/' . $sideImages[0]->path) }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105">
                                <div class="absolute inset-0 bg-black/10 hover:bg-transparent transition-colors"></div>
                            </div>
                            <div class="grid grid-cols-1 gap-2.5 h-[450px]">
                                <div class="relative overflow-hidden cursor-pointer h-[220px]" onclick="openLightbox(2)">
                                    <img src="{{ asset('storage/' . $sideImages[1]->path) }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105">
                                    <div class="absolute inset-0 bg-black/10 hover:bg-transparent transition-colors"></div>
                                </div>
                                <div class="relative overflow-hidden cursor-pointer h-[220px]" onclick="openLightbox(3)">
                                    <img src="{{ asset('storage/' . $sideImages[2]->path) }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105">
                                    <div class="absolute inset-0 bg-black/10 hover:bg-transparent transition-colors"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- چهار تصویر به صورت شبکه ۲x۲ --}}
                        <div class="hidden md:grid md:col-span-2 grid-cols-2 gap-2.5 h-[450px]">
                            @foreach($sideImages as $sIdx => $img)
                                <div class="relative overflow-hidden cursor-pointer h-[220px]" onclick="openLightbox({{ $sIdx + 1 }})">
                                    <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105">
                                    <div class="absolute inset-0 bg-black/10 hover:bg-transparent transition-colors"></div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- دکمه مشاهده همه تصاویر --}}
                    @if($allGalleryUrls->count() > 1)
                        <button onclick="openLightbox(0)" 
                                class="absolute bottom-4 left-4 sm:bottom-6 sm:left-6 px-4 py-2.5 rounded-2xl bg-white/95 dark:bg-gray-900/95 text-gray-900 dark:text-white text-xs sm:text-sm font-black shadow-lg backdrop-blur-md border border-gray-200/80 dark:border-gray-700 hover:scale-105 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>مشاهده همه {{ $allGalleryUrls->count() }} تصویر</span>
                        </button>
                    @endif
                </div>
            @elseif($heroImage)
                <div class="h-80 sm:h-96 md:h-[450px] rounded-3xl overflow-hidden shadow-xl border border-gray-100 dark:border-gray-800 relative cursor-pointer" onclick="openLightbox(0)">
                    <img src="{{ $heroImage }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="h-64 sm:h-80 rounded-3xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-16 h-16 opacity-40 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    <span class="text-sm font-bold">بدون تصویر</span>
                </div>
            @endif
        </div>

        {{-- نوار خلاصه ظرفیت و ویژگی‌های کلیدی اقامتگاه (Highlights Bar) --}}
        <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-200/80 dark:border-gray-800 mb-10">
            <div class="grid grid-cols-2 sm:grid-cols-5 text-center">
                {{-- ۱. ظرفیت پایه و حداکثر (سمت راست - بدون بوردر راست) --}}
                <div class="flex flex-col items-center justify-center gap-1.5 py-3 sm:py-1 px-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="text-xs text-gray-400 font-bold">ظرفیت مهمان</span>
                    <span class="text-sm font-black text-gray-900 dark:text-white">
                        {{ $config ? $config->base_guests : 2 }} نفر پایه
                        @if($config && $config->max_guests > $config->base_guests)
                            <span class="text-[11px] text-gray-500 font-bold block">(تا {{ $config->max_guests }} نفر)</span>
                        @endif
                    </span>
                </div>

                {{-- ۲. خواب --}}
                <div class="flex flex-col items-center justify-center gap-1.5 py-3 sm:py-1 px-3 border-r border-gray-100 dark:border-gray-800">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <span class="text-xs text-gray-400 font-bold">تعداد خواب</span>
                    <span class="text-sm font-black text-gray-900 dark:text-white">
                        @if($property->bedrooms !== null && $property->bedrooms !== '')
                            {{ $property->bedrooms == 0 ? 'سوئیت (بدون خواب)' : $property->bedrooms . ' اتاق خواب' }}
                        @else
                            —
                        @endif
                    </span>
                </div>

                {{-- ۳. سرویس بهداشتی --}}
                <div class="flex flex-col items-center justify-center gap-1.5 py-3 sm:py-1 px-3 border-t sm:border-t-0 sm:border-r border-gray-100 dark:border-gray-800">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h18M3 13a2 2 0 00-2 2v1a4 4 0 004 4h14a4 4 0 004-4v-1a2 2 0 00-2-2M3 13V7a3 3 0 013-3h1m3 0a2 2 0 012 2v7M5 21v1m14-1v1"/></svg>
                    </div>
                    <span class="text-xs text-gray-400 font-bold">سرویس بهداشتی</span>
                    <span class="text-sm font-black text-gray-900 dark:text-white">
                        @if($property->bathrooms !== null && $property->bathrooms !== '')
                            {{ $property->bathrooms }} سرویس و حمام
                        @else
                            —
                        @endif
                    </span>
                </div>

                {{-- ۴. متراژ اقامتگاه --}}
                <div class="flex flex-col items-center justify-center gap-1.5 py-3 sm:py-1 px-3 border-t sm:border-t-0 border-r border-gray-100 dark:border-gray-800">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </div>
                    <span class="text-xs text-gray-400 font-bold">متراژ اقامتگاه</span>
                    <span class="text-sm font-black text-gray-900 dark:text-white">
                        {{ $property->area_formatted ? ($property->area_formatted . ' متر مربع') : '—' }}
                    </span>
                </div>

                {{-- ۵. حداقل مدت اقامت --}}
                <div class="flex flex-col items-center justify-center gap-1.5 py-3 sm:py-1 px-3 col-span-2 sm:col-span-1 border-t sm:border-t-0 sm:border-r border-gray-100 dark:border-gray-800">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </div>
                    <span class="text-xs text-gray-400 font-bold">حداقل رزرو</span>
                    <span class="text-sm font-black text-gray-900 dark:text-white">{{ $config ? ($config->min_stay_nights ?? 1) : 1 }} شب اقامت</span>
                </div>
            </div>
        </div>

        {{-- چیدمان اصلی: دو ستونه (ستون محتوا + سایدبار چسبان رزرو) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            {{-- ستون محتوا (Main Column) --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- کارت میزبان و هویت (Host Identity Card) --}}
                <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-3xl p-6 sm:p-7 shadow-sm border border-gray-200/80 dark:border-gray-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                        <div class="flex items-center gap-4">
                            @if($host && $host->avatar)
                                <img src="{{ asset('storage/' . $host->avatar) }}" alt="{{ $host->display_name }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-indigo-100 dark:border-indigo-900/50 shadow-sm">
                            @else
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-2xl shadow-md shadow-indigo-500/20">
                                    {{ mb_substr($host ? $host->display_name : ($displayAgent->name ?? 'م'), 0, 1) }}
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-black text-gray-900 dark:text-white">
                                        {{ $host ? $host->display_name : ($displayAgent->name ?? 'میزبان اقامتگاه') }}
                                    </h3>
                                    @if($host && $host->kyc_status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-[11px] font-bold border border-emerald-200/80 dark:border-emerald-800/40">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            میزبان تایید هویت شده
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">میزبان مستقل در پلتفرم اقامتگاهی</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5">
                            @php
                                $hostPhone = $host && $host->phone ? $host->phone : ($displayAgent->mobile ?? $displayAgent->phone ?? null);
                            @endphp
                            @if($hostPhone)
                                <a href="tel:{{ $hostPhone }}" class="px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition text-xs font-bold flex items-center gap-2 border border-indigo-200/60 dark:border-indigo-800/50">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    تماس با میزبان
                                </a>
                            @endif
                        </div>
                    </div>

                    @if($host && $host->about)
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                            {{ $host->about }}
                        </div>
                    @endif
                </div>

                {{-- توضیحات اقامتگاه و ویدئو تور --}}
                <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-3xl p-6 sm:p-7 shadow-sm border border-gray-200/80 dark:border-gray-800 space-y-5">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        درباره این اقامتگاه
                    </h2>

                    <div class="text-sm text-gray-700 dark:text-gray-300 leading-loose text-justify whitespace-pre-line font-medium">
                        {{ $property->description ?: 'توضیحات تکمیلی برای این اقامتگاه ثبت نشده است.' }}
                    </div>

                    @if($property->video)
                        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                            <h3 class="text-sm font-black text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                تور ویدئویی اقامتگاه
                            </h3>
                            <div class="rounded-2xl overflow-hidden shadow-lg bg-black aspect-video">
                                <video controls class="w-full h-full">
                                    <source src="{{ asset('storage/' . $property->video) }}" type="video/mp4">
                                </video>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- اطلاعات تکمیلی و مشخصات اقامتگاه (Supplementary Details & Specs) --}}
                @if($detailSpecs->count() > 0)
                    <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-3xl p-6 sm:p-7 shadow-sm border border-gray-200/80 dark:border-gray-800">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            اطلاعات تکمیلی و مشخصات اقامتگاه
                        </h2>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                            @foreach($detailSpecs as $spec)
                                <div class="flex flex-col gap-1 p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/60">
                                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400">{{ $spec['name'] }}</span>
                                    <span class="text-sm font-black text-gray-900 dark:text-gray-100">{{ $spec['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- امکانات و تجهیزات اقامتگاه (Amenities & Facilities) --}}
                @if($features->count() > 0 || count($customFeatures) > 0)
                    <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-3xl p-6 sm:p-7 shadow-sm border border-gray-200/80 dark:border-gray-800">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            امکانات و تجهیزات اقامتگاه
                        </h2>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                            @foreach($features as $feature)
                                <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-gray-50/70 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/60 text-gray-800 dark:text-gray-200">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-xs font-bold">{{ $feature->attribute->name }}</span>
                                </div>
                            @endforeach

                            @foreach($customFeatures as $customFeature)
                                <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-gray-50/70 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/60 text-gray-800 dark:text-gray-200">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-xs font-bold">{{ $customFeature }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- قوانین، مقررات و ساعت تحویل و تخلیه (House Rules) --}}
                <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-3xl p-6 sm:p-7 shadow-sm border border-gray-200/80 dark:border-gray-800 space-y-6">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        قوانین و مقررات ورود به اقامتگاه
                    </h2>

                    {{-- ساعات ورود و خروج --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-center gap-3.5 p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40">
                            <span class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <span class="block text-xs font-bold text-gray-500 dark:text-gray-400">ساعت تحویل (ورود)</span>
                                <span class="text-sm font-black text-gray-900 dark:text-white">از ساعت {{ $checkInTimeFormatted }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3.5 p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/20 border border-indigo-200/60 dark:border-indigo-900/40">
                            <span class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </span>
                            <div>
                                <span class="block text-xs font-bold text-gray-500 dark:text-gray-400">ساعت تخلیه (خروج)</span>
                                <span class="text-sm font-black text-gray-900 dark:text-white">تا ساعت {{ $checkOutTimeFormatted }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- لیست قوانین شاخص --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-bold">
                        <div class="flex items-center gap-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 rounded-full {{ !empty($houseRules['pets_allowed']) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            <span>ورود حیوان خانگی:</span>
                            <span class="{{ !empty($houseRules['pets_allowed']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ !empty($houseRules['pets_allowed']) ? 'مجاز است' : 'ممنوع است' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 rounded-full {{ !empty($houseRules['smoking_allowed']) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            <span>استعمال دخانیات:</span>
                            <span class="{{ !empty($houseRules['smoking_allowed']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ !empty($houseRules['smoking_allowed']) ? 'مجاز است' : 'در فضای داخلی ممنوع است' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 rounded-full {{ !empty($houseRules['parties_allowed']) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            <span>برگزاری جشن و مهمانی:</span>
                            <span class="{{ !empty($houseRules['parties_allowed']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ !empty($houseRules['parties_allowed']) ? 'مجاز است' : 'ممنوع است' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>مدارک مورد نیاز:</span>
                            <span class="text-gray-800 dark:text-gray-200">ارائه کارت ملی هوشمند الزامی است</span>
                        </div>
                    </div>

                    {{-- قوانین سفارشی ثبت‌شده --}}
                    @if(!empty($customRules) && is_array($customRules))
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                            <span class="block text-xs font-black text-gray-800 dark:text-gray-200 mb-2.5">سایر قوانین خاص میزبان:</span>
                            <ul class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400 list-disc list-inside">
                                @foreach($customRules as $cRule)
                                    <li>{{ $cRule }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- تقویم تعاملی دسترسی و نرخ‌های روزانه (Interactive Calendar) --}}
                <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-3xl p-5 sm:p-7 shadow-sm border border-gray-200/80 dark:border-gray-800 space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                تقویم دسترسی و نرخ‌های روزانه
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">جهت انتخاب بازه اقامت، روی تاریخ ورود و سپس تاریخ خروج کلیک نمایید.</p>
                        </div>

                        {{-- راهنمای رنگ‌ها (حداقلی و هماهنگ) --}}
                        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                <span>عادی</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>آخر هفته</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                <span>پیک و تعطیلات</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-600"></span>
                                <span>مسدود</span>
                            </div>
                        </div>
                    </div>

                    {{-- نوار وضعیت بازه انتخابی --}}
                    <div x-cloak x-show="checkIn" 
                         class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 rounded-xl bg-indigo-50/90 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/60 text-xs transition-all">
                        <div class="flex flex-wrap items-center gap-2 font-bold text-indigo-950 dark:text-indigo-200">
                            <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                            <span>ورود: <strong class="text-indigo-600 dark:text-indigo-400 font-black" x-text="checkIn"></strong></span>
                            <template x-if="checkOut">
                                <span class="flex items-center gap-2">
                                    <span class="text-indigo-400">←</span>
                                    <span>خروج: <strong class="text-indigo-600 dark:text-indigo-400 font-black" x-text="checkOut"></strong></span>
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-600 text-white text-[11px] font-black mr-1" x-text="nightsCount + ' شب اقامت'"></span>
                                </span>
                            </template>
                            <template x-if="!checkOut">
                                <span class="text-[11px] text-indigo-500 dark:text-indigo-400 font-normal mr-2">«لطفاً تاریخ خروج را انتخاب نمایید»</span>
                            </template>
                        </div>
                        <button type="button" 
                                @click="clearDates()" 
                                class="text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold text-[11px] flex items-center gap-1 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>لغو انتخاب</span>
                        </button>
                    </div>

                    {{-- ماه‌های تقویم --}}
                    <div class="space-y-6">
                        @foreach($calendarData['months'] as $m)
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between pb-1 border-b border-gray-100 dark:border-gray-800/60">
                                    <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                        <span>{{ $m['month_name'] }}</span>
                                        <span class="text-gray-400 font-bold">{{ $m['year'] }}</span>
                                    </h3>
                                    <span class="text-[11px] text-gray-400 font-bold">مبالغ به {{ $currencyLabel }}</span>
                                </div>

                                {{-- گرید تقویم با اسکرول افقی در موبایل --}}
                                <div class="overflow-x-auto -mx-1 px-1 scrollbar-thin">
                                    <div class="min-w-[480px] md:min-w-full space-y-1">
                                        {{-- سرستون روزها --}}
                                        <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-bold py-1 text-gray-400 dark:text-gray-500">
                                            <span>ش</span>
                                            <span>۱ش</span>
                                            <span>۲ش</span>
                                            <span>۳ش</span>
                                            <span>۴ش</span>
                                            <span class="text-amber-500">۵ش</span>
                                            <span class="text-amber-500">ج</span>
                                        </div>

                                        {{-- روزها --}}
                                        <div class="grid grid-cols-7 gap-1">
                                            @for($i = 0; $i < $m['first_day_of_week']; $i++)
                                                <div class="h-13 sm:h-14 rounded-lg bg-transparent"></div>
                                            @endfor

                                            @foreach($m['days'] as $d)
                                                @php
                                                    $isBlocked = $d['is_blocked'];
                                                    $isPast = $d['is_past'];
                                                    $priceType = $d['price_type'];
                                                @endphp
                                                <div @click="!{{ $isBlocked ? 'true' : 'false' }} && !{{ $isPast ? 'true' : 'false' }} ? selectCalendarDate('{{ $d['jalali_date'] }}') : null"
                                                     class="h-13 sm:h-14 p-1 rounded-lg border transition-all flex flex-col items-center justify-center text-center relative select-none
                                                     {{ $isBlocked 
                                                        ? 'bg-gray-100/60 dark:bg-gray-800/25 border-gray-200/50 dark:border-gray-800/40 text-gray-400 dark:text-gray-500 cursor-not-allowed opacity-60' 
                                                        : ($isPast 
                                                            ? 'bg-transparent border-transparent text-gray-300 dark:text-gray-700 opacity-25 cursor-not-allowed' 
                                                            : ($priceType === 'custom'
                                                                ? 'bg-purple-50/50 dark:bg-purple-950/20 border-purple-200/60 dark:border-purple-900/30 text-gray-900 dark:text-white hover:border-purple-400 cursor-pointer'
                                                                : ($priceType === 'holiday'
                                                                    ? 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-200/60 dark:border-rose-900/30 text-gray-900 dark:text-white hover:border-rose-400 cursor-pointer'
                                                                    : ($priceType === 'weekend'
                                                                        ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200/60 dark:border-amber-900/30 text-gray-900 dark:text-white hover:border-amber-400 cursor-pointer'
                                                                        : 'bg-white dark:bg-gray-800/40 border-gray-200/70 dark:border-gray-700/40 text-gray-900 dark:text-white hover:border-indigo-400 dark:hover:border-indigo-500 cursor-pointer')))) }}"
                                                     :class="getDayClasses('{{ $d['jalali_date'] }}')">

                                                    {{-- شماره روز (وسط‌چین) --}}
                                                    <span class="text-xs sm:text-sm font-black leading-none text-center transition-colors"
                                                          :class="{
                                                              'text-white': isStartDate('{{ $d['jalali_date'] }}') || isEndDate('{{ $d['jalali_date'] }}'),
                                                              'text-indigo-950 dark:text-indigo-100': isInRange('{{ $d['jalali_date'] }}'),
                                                              'line-through text-gray-400 dark:text-gray-600': {{ $isBlocked ? 'true' : 'false' }}
                                                          }">
                                                        {{ $d['day'] }}
                                                    </span>

                                                    {{-- نرخ روزانه یا برچسب (وسط‌چین) --}}
                                                    <div class="mt-0.5 leading-none">
                                                        <template x-if="isStartDate('{{ $d['jalali_date'] }}')">
                                                            <span class="text-[9px] text-white/90 font-bold block">ورود</span>
                                                        </template>
                                                        <template x-if="isEndDate('{{ $d['jalali_date'] }}')">
                                                            <span class="text-[9px] text-white/90 font-bold block">خروج</span>
                                                        </template>

                                                        @if($d['price'] > 0 && !$isBlocked)
                                                            <span x-show="!isStartDate('{{ $d['jalali_date'] }}') && !isEndDate('{{ $d['jalali_date'] }}')"
                                                                  class="text-[10px] sm:text-[11px] font-bold leading-none tracking-tight block transition-colors"
                                                                  :class="{
                                                                      'text-indigo-700 dark:text-indigo-300 font-black': isInRange('{{ $d['jalali_date'] }}'),
                                                                      '{{ $priceType === 'holiday' ? 'text-rose-600 dark:text-rose-400 font-black' : ($priceType === 'weekend' ? 'text-amber-600 dark:text-amber-400 font-black' : ($priceType === 'custom' ? 'text-purple-600 dark:text-purple-400 font-black' : 'text-indigo-600 dark:text-indigo-400 font-black')) }}': !isInRange('{{ $d['jalali_date'] }}')
                                                                  }">
                                                                {{ number_format($d['price']) }}
                                                            </span>
                                                        @elseif($isBlocked)
                                                            <span x-show="!isStartDate('{{ $d['jalali_date'] }}') && !isEndDate('{{ $d['jalali_date'] }}')"
                                                                  class="text-[9px] text-gray-400 dark:text-gray-500 font-medium block">مسدود</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- موقعیت روی نقشه --}}
                @if($canViewMap && $property->latitude && $property->longitude)
                    <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-3xl p-6 sm:p-7 shadow-sm border border-gray-200/80 dark:border-gray-800">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    موقعیت جغرافیایی و دسترسی
                                </h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">محدوده تقریبی اقامتگاه جهت حفظ حریم شخصی</p>
                            </div>
                            <span class="text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3.5 py-1.5 rounded-xl self-start sm:self-auto">
                                {{ $property->address }}
                            </span>
                        </div>
                        <div id="map" class="w-full h-[350px] rounded-2xl z-0 border border-gray-200 dark:border-gray-700 shadow-inner"></div>
                    </div>
                @endif
            </div>

            {{-- سایدبار چسبان رزرواسیون و محاسبه‌گر زنده (Sticky Booking Widget) --}}
            <div class="space-y-6 lg:sticky top-24 h-fit">

                {{-- کارت ویجت رزرو --}}
                <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-3xl p-6 sm:p-7 shadow-xl border-t-4 border-indigo-500 border-x border-b border-gray-200/80 dark:border-gray-800">
                    
                    {{-- هدر قیمت پایه --}}
                    <div class="mb-5 pb-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-baseline justify-between">
                            <div>
                                <span class="text-2xl sm:text-3xl font-black text-indigo-600 dark:text-indigo-400" x-text="formatNumber(basePrice)">
                                    {{ number_format($config ? $config->price_per_night : 0) }}
                                </span>
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 mr-1">{{ $currencyLabel }} / هر شب (پایه)</span>
                            </div>
                        </div>
                    </div>

                    {{-- فرم انتخاب تاریخ و مهمانان --}}
                    <div class="space-y-4 mb-6">
                        {{-- انتخاب تاریخ ورود و خروج --}}
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ ورود</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="rental_checkin"
                                           x-model="checkIn"
                                           data-jdp 
                                           data-jdp-only-date 
                                           @input="checkIn = normalizeDate($el.value); calculateInvoice()"
                                           @change="checkIn = normalizeDate($el.value); calculateInvoice()"
                                           @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                           placeholder="۱۴۰۵/۰۱/۰۱" 
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 text-xs font-bold text-center text-gray-900 dark:text-white cursor-pointer focus:ring-2 focus:ring-indigo-500/20">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ خروج</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="rental_checkout"
                                           x-model="checkOut"
                                           data-jdp 
                                           data-jdp-only-date 
                                           @input="checkOut = normalizeDate($el.value); calculateInvoice()"
                                           @change="checkOut = normalizeDate($el.value); calculateInvoice()"
                                           @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                           placeholder="۱۴۰۵/۰۱/۰۵" 
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 text-xs font-bold text-center text-gray-900 dark:text-white cursor-pointer focus:ring-2 focus:ring-indigo-500/20">
                                </div>
                            </div>
                        </div>

                        {{-- انتخابگر تعداد مهمانان --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تعداد مهمانان</label>
                            <div class="flex items-center justify-between p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                    <span x-text="guests"></span> نفر
                                    <span class="text-[10px] text-gray-400" x-show="guests > baseGuests">
                                        (<span x-text="guests - baseGuests"></span> نفر اضافه)
                                    </span>
                                </span>

                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            @click="decreaseGuests()" 
                                            :disabled="guests <= 1"
                                            class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 flex items-center justify-center font-bold hover:bg-gray-100 disabled:opacity-40 transition">
                                        -
                                    </button>
                                    <button type="button" 
                                            @click="increaseGuests()" 
                                            :disabled="guests >= maxGuests"
                                            class="w-7 h-7 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 flex items-center justify-center font-bold hover:bg-gray-100 disabled:opacity-40 transition">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- هشدار روزهای مسدود در بازه انتخابی --}}
                    <div x-show="hasBlockedDays" x-cloak class="p-3 mb-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/40 text-xs text-red-700 dark:text-red-300 font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>برخی از شب‌های انتخابی شما مسدود یا رزرو شده‌اند. لطفاً تاریخ‌های دیگری انتخاب کنید.</span>
                    </div>

                    {{-- پیش‌فاکتور شفاف محاسباتی زنده (Live Breakdown) --}}
                    <div x-show="nightsCount > 0 && !hasBlockedDays" x-cloak class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 space-y-2.5 mb-6 text-xs">
                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-300 font-bold">
                            <span>اقامت به مدت <span x-text="nightsCount"></span> شب:</span>
                            <span class="font-black text-gray-900 dark:text-white" x-text="formatNumber(nightsTotal) + ' ' + currencyLabel"></span>
                        </div>

                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-300 font-bold" x-show="extraGuestsFeeTotal > 0">
                            <span>هزینه نفرات اضافه (<span x-text="guests - baseGuests"></span> نفر):</span>
                            <span class="font-black text-gray-900 dark:text-white" x-text="formatNumber(extraGuestsFeeTotal) + ' ' + currencyLabel"></span>
                        </div>

                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-300 font-bold" x-show="cleaningFee > 0">
                            <span>هزینه خدمات و نظافت:</span>
                            <span class="font-black text-gray-900 dark:text-white" x-text="formatNumber(cleaningFee) + ' ' + currencyLabel"></span>
                        </div>

                        <div class="border-t border-indigo-200/60 dark:border-indigo-800/60 pt-2.5 flex justify-between items-center text-sm font-black">
                            <span class="text-indigo-900 dark:text-indigo-200">مبلغ کل قابل پرداخت:</span>
                            <span class="text-base text-indigo-600 dark:text-indigo-400" x-text="formatNumber(grandTotal) + ' ' + currencyLabel"></span>
                        </div>
                    </div>

                    {{-- دکمه‌های اقدام رزرو و تماس --}}
                    <div class="space-y-3">
                        <button type="button" 
                                @click="submitBookingInquiry()" 
                                :disabled="hasBlockedDays"
                                class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm transition-all shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 flex items-center justify-center gap-2 disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>ثبت درخواست رزرو اقامتگاه</span>
                        </button>

                        @if($hostPhone)
                            <a href="tel:{{ $hostPhone }}" class="w-full py-3 px-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold text-xs transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>تماس مستقیم با میزبان ({{ $hostPhone }})</span>
                            </a>
                        @endif
                    </div>

                    {{-- ضمانت‌های پلتفرم --}}
                    <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-800 space-y-2 text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>ضمانت تطابق مشخصات و تمیزی اقامتگاه</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>پشتیبانی شبانه‌روزی تا انتهای سفر</span>
                        </div>
                    </div>
                </div>

                {{-- کارت اشتراک‌گذاری --}}
                <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-2xl p-4 shadow-sm border border-gray-200/80 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-gray-600 dark:text-gray-300">
                    <span>اشتراک‌گذاری اقامتگاه:</span>
                    <div class="flex items-center gap-2">
                        <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($property->title) }}" target="_blank" class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-500 flex items-center justify-center hover:bg-blue-500 hover:text-white transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.24-5.54 3.65-.52.36-.99.53-1.4.52-.46-.01-1.34-.26-2-.48-.8-.27-1.44-.42-1.39-.89.03-.25.38-.51 1.07-.78 4.2-1.82 7-3.03 8.4-3.61 3.99-1.66 4.82-1.95 5.36-1.96.12 0 .38.03.55.17.14.12.18.28.2.43-.02.07-.02.15-.02.22z"/></svg>
                        </a>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($property->title . ' ' . url()->current()) }}" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500 flex items-center justify-center hover:bg-emerald-500 hover:text-white transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.01 2.01C6.48 2.01 2 6.48 2 12.01c0 2.17.69 4.19 1.86 5.83L2.5 21.5l3.86-1.27c1.55.93 3.37 1.48 5.65 1.48 5.53 0 10-4.47 10-10s-4.47-10-10-10zm5.66 14.16c-.23.65-1.34 1.25-1.85 1.32-.47.06-.93.07-3.41-.92-3.12-1.25-5.11-4.38-5.27-4.59-.16-.21-1.3-1.72-1.3-3.29 0-1.57.82-2.34 1.11-2.65.29-.31.64-.39.85-.39.21 0 .42.01.6.01.19.01.44-.07.69.53.25.6 1.03 2.51 1.12 2.69.09.18.15.39.03.62-.12.23-.26.39-.42.56-.16.18-.34.39-.49.53-.16.16-.33.34-.14.66.19.32.85 1.4 1.82 2.27.97.87 1.79 1.14 2.11 1.27.32.13.51.11.7-.1.19-.22.82-.95 1.04-1.27.22-.32.44-.27.75-.16.31.11 1.99.98 2.33 1.15.34.17.57.25.65.39.09.14.09.81-.14 1.46z"/></svg>
                        </a>
                        <button onclick="copyToClipboard('{{ url()->current() }}')" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 flex items-center justify-center hover:bg-gray-200 transition" title="کپی لینک">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- نوار شناور رزرواسیون در موبایل (Mobile Floating Booking Bar) --}}
        <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 p-3.5 px-5 shadow-2xl flex items-center justify-between">
            <div>
                <span class="text-xs text-gray-400 block font-bold">شروع قیمت هر شب از:</span>
                <span class="text-lg font-black text-indigo-600 dark:text-indigo-400" x-text="formatNumber(basePrice) + ' ' + currencyLabel"></span>
            </div>
            <button type="button" 
                    @click="document.getElementById('rental_checkin').scrollIntoView({ behavior: 'smooth' }); document.getElementById('rental_checkin').focus();"
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-black text-xs shadow-md shadow-indigo-600/30">
                بررسی و ثبت رزرو
            </button>
        </div>

    </div>

    {{-- مدال لایت‌باکس بزرگ‌نمایی تصاویر --}}
    <div id="lightbox" class="fixed inset-0 z-[100] bg-black/95 hidden flex items-center justify-center opacity-0 transition-opacity duration-300 backdrop-blur-sm">
        <button onclick="closeLightbox()" class="absolute top-6 right-6 text-white/50 hover:text-white z-[101] bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl backdrop-blur transition-all">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <button onclick="prevImage()" class="absolute left-6 top-1/2 -translate-y-1/2 text-white/50 hover:text-white z-[101] p-4 bg-white/10 hover:bg-white/20 rounded-2xl backdrop-blur transition-all hidden md:block">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <button onclick="nextImage()" class="absolute right-6 top-1/2 -translate-y-1/2 text-white/50 hover:text-white z-[101] p-4 bg-white/10 hover:bg-white/20 rounded-2xl backdrop-blur transition-all hidden md:block">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
        <img id="lightbox-image" src="" class="max-h-[90vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl" alt="تصویر اقامتگاه">
    </div>

    @includeIf('partials.jalali-date-picker')
@endsection

@push('scripts')
    <script>
        // اپلیکیشن آلپاین محاسبه‌گر زنده رزرواسیون
        function rentalBookingApp(flatDates, config, currencyLabel, propertyTitle, propertyCode) {
            return {
                flatDates: flatDates || {},
                config: config || {},
                currencyLabel: currencyLabel,
                propertyTitle: propertyTitle,
                propertyCode: propertyCode,

                basePrice: config ? Number(config.price_per_night || 0) : 0,
                baseGuests: config ? Number(config.base_guests || 2) : 2,
                maxGuests: config ? Number(config.max_guests || 4) : 4,
                extraGuestFee: config ? Number(config.extra_guest_fee || 0) : 0,
                cleaningFee: config ? Number(config.cleaning_fee || 0) : 0,
                minStayNights: config ? Number(config.min_stay_nights || 1) : 1,

                checkIn: '',
                checkOut: '',
                guests: config ? Number(config.base_guests || 2) : 2,

                nightsCount: 0,
                nightsTotal: 0,
                extraGuestsFeeTotal: 0,
                grandTotal: 0,
                hasBlockedDays: false,

                init() {
                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            try {
                                window.jalaliDatepicker.startWatch({
                                    selector: '[data-jdp-only-date]',
                                    date: true,
                                    time: false,
                                    autoHide: true,
                                    hideAfterChange: true
                                });
                            } catch (e) {}
                        }

                        // شنود رویداد تغییر تاریخ‌ها از ورودی‌های دیت‌پیکر هسته
                        const inEl = document.getElementById('rental_checkin');
                        const outEl = document.getElementById('rental_checkout');
                        if (inEl) {
                            const handlerIn = (e) => {
                                this.checkIn = this.normalizeDate(e.target.value);
                                this.calculateInvoice();
                            };
                            inEl.addEventListener('change', handlerIn);
                            inEl.addEventListener('input', handlerIn);
                        }
                        if (outEl) {
                            const handlerOut = (e) => {
                                this.checkOut = this.normalizeDate(e.target.value);
                                this.calculateInvoice();
                            };
                            outEl.addEventListener('change', handlerOut);
                            outEl.addEventListener('input', handlerOut);
                        }
                    });
                },

                // استانداردسازی تاریخ جلالی به فرمت YYYY/MM/DD با اعداد انگلیسی
                normalizeDate(d) {
                    if (!d) return '';
                    let clean = String(d).trim().replace(/[۰-۹]/g, w => '۰۱۲۳۴۵۶۷۸۹'.indexOf(w));
                    let parts = clean.split('/');
                    if (parts.length === 3) {
                        let y = parts[0];
                        let m = parts[1].padStart(2, '0');
                        let day = parts[2].padStart(2, '0');
                        return `${y}/${m}/${day}`;
                    }
                    return clean;
                },

                isStartDate(dateStr) {
                    if (!this.checkIn) return false;
                    return this.normalizeDate(this.checkIn) === this.normalizeDate(dateStr);
                },

                isEndDate(dateStr) {
                    if (!this.checkOut) return false;
                    return this.normalizeDate(this.checkOut) === this.normalizeDate(dateStr);
                },

                isInRange(dateStr) {
                    if (!this.checkIn || !this.checkOut) return false;
                    let cIn = this.normalizeDate(this.checkIn);
                    let cOut = this.normalizeDate(this.checkOut);
                    let curr = this.normalizeDate(dateStr);
                    return curr > cIn && curr < cOut;
                },

                getDayClasses(dateStr) {
                    if (this.isStartDate(dateStr)) {
                        return '!bg-indigo-600 !border-indigo-600 !text-white shadow-md z-10 ' + (this.checkOut ? '!rounded-r-xl !rounded-l-none' : '!rounded-xl');
                    }
                    if (this.isEndDate(dateStr)) {
                        return '!bg-indigo-600 !border-indigo-600 !text-white shadow-md z-10 !rounded-l-xl !rounded-r-none';
                    }
                    if (this.isInRange(dateStr)) {
                        return '!bg-indigo-50 dark:!bg-indigo-950/40 !border-y !border-x-0 !border-indigo-200 dark:!border-indigo-800/60 !rounded-none';
                    }
                    return '';
                },

                clearDates() {
                    this.checkIn = '';
                    this.checkOut = '';
                    this.calculateInvoice();
                },

                increaseGuests() {
                    if (this.guests < this.maxGuests) {
                        this.guests++;
                        this.calculateInvoice();
                    }
                },

                decreaseGuests() {
                    if (this.guests > 1) {
                        this.guests--;
                        this.calculateInvoice();
                    }
                },

                selectCalendarDate(dateStr) {
                    let normalized = this.normalizeDate(dateStr);
                    let currentIn = this.normalizeDate(this.checkIn);
                    let currentOut = this.normalizeDate(this.checkOut);

                    if (!currentIn || (currentIn && currentOut)) {
                        this.checkIn = normalized;
                        this.checkOut = '';
                    } else if (currentIn && !currentOut) {
                        if (normalized > currentIn) {
                            this.checkOut = normalized;
                        } else if (normalized < currentIn) {
                            this.checkIn = normalized;
                            this.checkOut = '';
                        } else {
                            this.checkIn = '';
                            this.checkOut = '';
                        }
                    }
                    this.calculateInvoice();
                },

                calculateInvoice() {
                    this.hasBlockedDays = false;
                    this.nightsCount = 0;
                    this.nightsTotal = 0;
                    this.extraGuestsFeeTotal = 0;
                    this.grandTotal = 0;

                    let cIn = this.normalizeDate(this.checkIn);
                    let cOut = this.normalizeDate(this.checkOut);

                    if (!cIn || !cOut) {
                        return;
                    }

                    if (cOut <= cIn) {
                        return;
                    }

                    // جستجو در دیکشنری تاریخ‌ها
                    let checkInDay = this.flatDates[cIn];
                    let checkOutDay = this.flatDates[cOut];

                    if (!checkInDay || !checkOutDay) {
                        return;
                    }

                    let startCarbon = new Date(checkInDay.carbon_date);
                    let endCarbon = new Date(checkOutDay.carbon_date);
                    let timeDiff = endCarbon.getTime() - startCarbon.getTime();
                    let daysDiff = Math.round(timeDiff / (1000 * 3600 * 24));

                    if (daysDiff <= 0) return;

                    this.nightsCount = daysDiff;
                    let totalNightsPrice = 0;
                    let blocked = false;

                    let curr = new Date(startCarbon);
                    for (let i = 0; i < daysDiff; i++) {
                        let y = curr.getFullYear();
                        let m = String(curr.getMonth() + 1).padStart(2, '0');
                        let d = String(curr.getDate()).padStart(2, '0');
                        let isoKey = `${y}-${m}-${d}`;

                        let dayData = this.flatDates[isoKey];
                        if (dayData) {
                            if (dayData.is_blocked) {
                                blocked = true;
                            }
                            totalNightsPrice += Number(dayData.price || this.basePrice);
                        } else {
                            totalNightsPrice += this.basePrice;
                        }
                        curr.setDate(curr.getDate() + 1);
                    }

                    this.hasBlockedDays = blocked;
                    this.nightsTotal = totalNightsPrice;

                    // محاسبه نفرات اضافه
                    let extraGuests = Math.max(0, this.guests - this.baseGuests);
                    this.extraGuestsFeeTotal = extraGuests * this.extraGuestFee * this.nightsCount;

                    this.grandTotal = this.nightsTotal + this.extraGuestsFeeTotal + this.cleaningFee;
                },

                submitBookingInquiry() {
                    if (this.hasBlockedDays) {
                        alert('برخی از شب‌های انتخابی شما مسدود است. لطفاً تاریخ دیگری انتخاب کنید.');
                        return;
                    }

                    let msg = `سلام، مایل به رزرو اقامتگاه «${this.propertyTitle}» (کد فایل: ${this.propertyCode}) هستم.`;
                    if (this.checkIn && this.checkOut) {
                        msg += `\nاز تاریخ ${this.checkIn} تا ${this.checkOut} (${this.nightsCount} شب)`;
                    }
                    msg += `\nتعداد مهمانان: ${this.guests} نفر`;
                    if (this.grandTotal > 0) {
                        msg += `\nبرآورد مبلغ: ${this.formatNumber(this.grandTotal)} ${this.currencyLabel}`;
                    }

                    // هدایت به تماس یا واتساپ میزبان
                    @php
                        $waPhone = preg_replace('/[^0-9]/', '', (string)$hostPhone);
                        if (str_starts_with($waPhone, '0')) {
                            $waPhone = '98' . substr($waPhone, 1);
                        }
                    @endphp
                    let waUrl = `https://api.whatsapp.com/send?phone={{ $waPhone }}&text=${encodeURIComponent(msg)}`;
                    window.open(waUrl, '_blank');
                },

                formatNumber(val) {
                    if (!val && val !== 0) return '۰';
                    return Number(val).toLocaleString('fa-IR');
                }
            }
        }

        // اسلایدر و لایت باکس تصاویر
        const galleryImages = [
            @if(isset($allGalleryUrls))
                @foreach($allGalleryUrls as $url)
                    '{{ $url }}',
                @endforeach
            @endif
        ];
        let currentImageIndex = 0;

        function openLightbox(index = 0) {
            if (galleryImages.length === 0) return;
            currentImageIndex = index;
            document.getElementById('lightbox-image').src = galleryImages[currentImageIndex];
            const lb = document.getElementById('lightbox');
            lb.classList.remove('hidden');
            setTimeout(() => { lb.classList.remove('opacity-0'); }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const lb = document.getElementById('lightbox');
            lb.classList.add('opacity-0');
            setTimeout(() => { lb.classList.add('hidden'); }, 300);
            document.body.style.overflow = 'auto';
        }

        function nextImage() {
            if (galleryImages.length <= 1) return;
            currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
            document.getElementById('lightbox-image').src = galleryImages[currentImageIndex];
        }

        function prevImage() {
            if (galleryImages.length <= 1) return;
            currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
            document.getElementById('lightbox-image').src = galleryImages[currentImageIndex];
        }

        document.addEventListener('keydown', (e) => {
            if (document.getElementById('lightbox').classList.contains('hidden')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
        });

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('لینک اقامتگاه با موفقیت کپی شد.');
            }).catch(() => {});
        }

        // نقشه Leaflet
        @if($canViewMap && $property->latitude && $property->longitude)
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof L !== 'undefined') {
                    const lat = {{ $property->latitude }};
                    const lng = {{ $property->longitude }};
                    const map = L.map('map').setView([lat, lng], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    // محدوده یا دایره امن اقامتگاه
                    L.circle([lat, lng], {
                        color: '#4f46e5',
                        fillColor: '#6366f1',
                        fillOpacity: 0.25,
                        radius: 300
                    }).addTo(map).bindPopup('<b>محدوده تقریبی اقامتگاه</b>');
                }
            });
        @endif
    </script>
@endpush
