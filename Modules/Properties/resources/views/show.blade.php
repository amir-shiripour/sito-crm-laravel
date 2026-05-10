@extends('layouts.web')

@section('title', $property->title ?? 'جزئیات ملک')

@push('styles')
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* استایل‌های یکپارچه و مدرن برای پاپ‌آپ نقشه */
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
        .leaflet-popup-close-button:hover { color: #4f46e5 !important; background: #e0e7ff !important; }
        .dark .leaflet-popup-close-button:hover { color: #818cf8 !important; background: rgba(79, 70, 229, 0.2) !important; }
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
        $mapService = \Modules\Properties\Entities\PropertySetting::get('map_service', 'leaflet');
        $mapIrApiKey = \Modules\Properties\Entities\PropertySetting::get('map_ir_api_key', '');
    @endphp

    <div class="max-w-7xl mx-auto px-6 py-12 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Main Content Area --}}
            <div class="lg:col-span-2 space-y-8 animate-in fade-in slide-in-from-bottom-8 duration-700">

                {{-- Breadcrumbs / Header --}}
                <div class="flex items-center gap-3 text-sm font-bold text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('properties.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">املاک</a>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    <span class="text-gray-900 dark:text-white line-clamp-1">{{ $property->title }}</span>
                </div>

                {{-- Gallery --}}
                <div class="bg-white dark:bg-gray-900/80 backdrop-blur-sm rounded-[2rem] shadow-xl overflow-hidden border border-gray-100 dark:border-gray-800" id="gallery-container">
                    @php
                        $images = $property->images;
                        $mainImage = null;
                        if ($canViewCover) {
                            $mainImage = $images->first() ? asset('storage/' . $images->first()->path) : ($property->cover_image ? asset('storage/' . $property->cover_image) : null);
                        }
                    @endphp

                    @if($mainImage)
                        <div class="relative h-[28rem] group cursor-pointer overflow-hidden" onclick="openLightbox()">
                            <img id="main-image" src="{{ $mainImage }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">

                            <div class="absolute top-6 right-6 flex flex-col gap-2 z-10">
                                @if($property->meta['is_special'] ?? false)
                                    <span class="bg-amber-500 text-white px-4 py-1.5 rounded-xl text-sm font-black shadow-lg shadow-amber-500/30 flex items-center gap-1 animate-pulse">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg> ویژه
                                    </span>
                                @endif
                                @if($property->status)
                                    <span class="px-4 py-1.5 rounded-xl text-sm font-black text-white shadow-lg" style="background-color: {{ $property->status->color }};">{{ $property->status->label }}</span>
                                @endif
                            </div>

                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white scale-90 group-hover:scale-100 transition-transform duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                </div>
                            </div>

                            @if($canViewGallery && $images->count() > 0)
                                <div class="absolute bottom-6 right-6 bg-gray-900/60 text-white px-4 py-1.5 rounded-xl text-sm font-bold backdrop-blur-md border border-white/10 shadow-lg">
                                    <span class="text-indigo-300">{{ $images->count() }}</span> تصویر
                                </div>
                            @endif
                        </div>

                        @if($canViewGallery && $images->count() > 1)
                            <div class="relative group/thumbs px-8 py-6 bg-gray-50/50 dark:bg-gray-900/50">
                                <button onclick="scrollThumbnails('right')" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 p-2.5 rounded-xl shadow-lg hover:bg-gray-50 text-gray-700 dark:text-gray-200 transition-all opacity-0 group-hover/thumbs:opacity-100 z-10 hidden md:flex items-center border border-gray-100 dark:border-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>

                                <div id="thumbnails-scroll" class="flex gap-4 overflow-x-auto scrollbar-hide snap-x snap-mandatory scroll-smooth px-2 pb-2">
                                    @foreach($images as $image)
                                        <div class="flex-shrink-0 w-28 h-28 rounded-xl overflow-hidden cursor-pointer border-2 border-transparent hover:border-indigo-500 hover:shadow-lg hover:-translate-y-1 transition-all snap-start"
                                             onclick="changeMainImage('{{ asset('storage/' . $image->path) }}')">
                                            <img src="{{ asset('storage/' . $image->path) }}" alt="" class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                </div>

                                <button onclick="scrollThumbnails('left')" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 p-2.5 rounded-xl shadow-lg hover:bg-gray-50 text-gray-700 dark:text-gray-200 transition-all opacity-0 group-hover/thumbs:opacity-100 z-10 hidden md:flex items-center border border-gray-100 dark:border-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="h-96 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 relative rounded-t-[2rem]">
                            <div class="absolute top-4 right-4 flex flex-col gap-2">
                                @if($property->meta['is_special'] ?? false)
                                    <span class="bg-amber-500 text-white px-3 py-1 rounded-xl text-sm font-bold shadow-md">ویژه</span>
                                @endif
                            </div>
                            @if(!$canViewCover)
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    <span class="font-bold">تصویر محدود شده است</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    <span class="font-bold">بدون تصویر</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- General Specifications --}}
                <div class="bg-white dark:bg-gray-900/80 backdrop-blur-sm rounded-[2rem] shadow-xl p-8 border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        </div>
                        <h2 class="text-2xl font-black">مشخصات کلیدی</h2>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-6 gap-x-8">
                        @php
                            $specs = collect();
                            if($property->listing_type) $specs->push(['name' => 'نوع فایل', 'value' => match($property->listing_type) {'sale' => 'فروش', 'rent' => 'رهن و اجاره', 'presale' => 'پیش‌فروش', default => $property->listing_type}]);
                            if($property->property_type) $specs->push(['name' => 'نوع ملک', 'value' => match($property->property_type) {'apartment' => 'آپارتمان', 'villa' => 'ویلایی', 'land' => 'زمین', 'commercial' => 'تجاری', 'office' => 'اداری', default => $property->property_type}]);
                            if($property->category) $specs->push(['name' => 'دسته‌بندی', 'value' => $property->category->name]);
                            if($property->document_type) $specs->push(['name' => 'نوع سند', 'value' => \Modules\Properties\Entities\Property::DOCUMENT_TYPES[$property->document_type] ?? $property->document_type]);
                            if($property->area) $specs->push(['name' => 'متراژ', 'value' => $property->area . ' متر']);
                            if($property->usage_type) $specs->push(['name' => 'کاربری', 'value' => match($property->usage_type) {'residential' => 'مسکونی', 'industrial' => 'صنعتی', 'commercial' => 'تجاری', 'agricultural' => 'کشاورزی / زراعی', 'garden' => 'باغ', 'outsideTheTissue' => 'خارج از بافت', default => $property->usage_type}]);
                            if($property->building) $specs->push(['name' => 'ساختمان مجتمع', 'value' => $property->building->name]);
                            if($property->listing_type === 'presale' && $property->delivery_date) { try { $specs->push(['name' => 'تاریخ تحویل', 'value' => \Morilog\Jalali\Jalalian::fromCarbon($property->delivery_date)->format('Y/m/d')]); } catch (\Exception $e) {} }
                            if($property->is_convertible) $specs->push(['name' => $property->listing_type == 'rent' ? 'قابل تبدیل' : 'قابل معاوضه', 'value' => 'بله' . ($property->convertible_with ? " ({$property->convertible_with})" : '')]);
                            if(isset($property->bedrooms)) $specs->push(['name' => 'تعداد خواب', 'value' => $property->bedrooms]);
                            if(isset($property->bathrooms)) $specs->push(['name' => 'سرویس بهداشتی', 'value' => $property->bathrooms]);

                            $details = $property->attributeValues->filter(function($attr) {
                                if (!$attr->attribute) return false;
                                if ($attr->attribute->section === 'features') return false;
                                return $attr->value !== null && $attr->value !== '';
                            });
                            foreach($details as $detail) $specs->push(['name' => $detail->attribute->name, 'value' => $detail->value]);
                            if (isset($property->meta['details']) && is_array($property->meta['details'])) {
                                foreach ($property->meta['details'] as $key => $value) if ($value !== null && $value !== '') $specs->push(['name' => $key, 'value' => $value]);
                            }
                        @endphp

                        @foreach($specs as $spec)
                            <div class="flex flex-col gap-1.5 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <span class="text-sm font-bold text-gray-400 dark:text-gray-500">{{ $spec['name'] }}</span>
                                <span class="font-black text-gray-900 dark:text-gray-100 text-lg">{{ $spec['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Description & Video --}}
                <div class="bg-white dark:bg-gray-900/80 backdrop-blur-sm rounded-[2rem] shadow-xl p-8 border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                        </div>
                        <h2 class="text-2xl font-black">توضیحات تکمیلی</h2>
                    </div>

                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-loose text-justify font-medium text-base whitespace-pre-line">
                        {{ $property->description }}
                    </div>

                    @if($property->video)
                        <div class="mt-10 pt-8 border-t border-gray-100 dark:border-gray-800">
                            <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-gray-900 dark:text-white">
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                </div>
                                تور ویدئویی ملک
                            </h3>
                            <div class="rounded-2xl overflow-hidden shadow-2xl bg-black border border-gray-800">
                                <video controls class="w-full aspect-video outline-none">
                                    <source src="{{ asset('storage/' . $property->video) }}" type="video/mp4">
                                </video>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Confidential Notes --}}
                @if($canViewNotes && $property->confidential_notes)
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-[2rem] shadow-xl p-8 border-2 border-amber-200 dark:border-amber-800/50">
                        <div class="flex items-center gap-3 mb-4 text-amber-800 dark:text-amber-200">
                            <div class="w-10 h-10 rounded-xl bg-amber-200 dark:bg-amber-800/50 flex items-center justify-center text-amber-800 dark:text-amber-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <h2 class="text-xl font-black">یادداشت‌های محرمانه سیستم</h2>
                        </div>
                        <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line font-medium text-sm">
                            {{ $property->confidential_notes }}
                        </div>
                    </div>
                @endif

                {{-- Features & Amenities --}}
                @php
                    $features = $property->attributeValues->filter(function($attr) { return $attr->attribute->section === 'features' && $attr->value == '1'; });
                    $customFeatures = isset($property->meta['features']) && is_array($property->meta['features']) ? $property->meta['features'] : [];
                @endphp
                @if($features->count() > 0 || count($customFeatures) > 0)
                    <div class="bg-white dark:bg-gray-900/80 backdrop-blur-sm rounded-[2rem] shadow-xl p-8 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <h2 class="text-2xl font-black">امکانات رفاهی</h2>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($features as $feature)
                                <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800/50 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="font-bold text-sm">{{ $feature->attribute->name }}</span>
                                </div>
                            @endforeach
                            @foreach($customFeatures as $customFeature)
                                <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800/50 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="font-bold text-sm">{{ $customFeature }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Location Map --}}
                @if($canViewMap && $property->latitude && $property->longitude)
                    <div class="bg-white dark:bg-gray-900/80 backdrop-blur-sm rounded-[2rem] shadow-xl p-8 border border-gray-100 dark:border-gray-800">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <h2 class="text-2xl font-black">موقعیت روی نقشه</h2>
                            </div>
                            <div class="text-sm font-bold text-gray-500 bg-gray-50 dark:bg-gray-800 px-4 py-2 rounded-xl">{{ $property->address }}</div>
                        </div>
                        <div id="map" class="w-full h-[400px] rounded-2xl z-0 border-2 border-gray-100 dark:border-gray-700 shadow-inner"></div>
                    </div>
                @endif
            </div>

            {{-- Sidebar (Sticky) --}}
            <div class="space-y-8 lg:sticky top-28 h-fit animate-in fade-in slide-in-from-right-8 duration-700 delay-200">

                {{-- Main Pricing & Action Card --}}
                <div class="bg-white dark:bg-gray-900/90 backdrop-blur-md rounded-[2rem] shadow-2xl p-8 border-t-4 border-indigo-500">
                    <div class="mb-6">
                        <p class="text-gray-400 font-bold text-sm flex items-center gap-1.5 mb-2">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                            کد ملک: {{ $property->code }}
                        </p>
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white leading-tight">{{ $property->title }}</h1>
                    </div>

                    @if($canViewPrice)
                        <div class="bg-gradient-to-br from-gray-50 to-indigo-50/50 dark:from-gray-800 dark:to-indigo-900/20 rounded-2xl p-6 mb-8 border border-gray-100 dark:border-gray-700/50">
                            @if($property->listing_type === 'rent')
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">مبلغ رهن:</span>
                                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $property->deposit_price > 0 ? number_format($property->deposit_price) . ' تومان' : 'توافقی' }}</span>
                                </div>
                                <div class="border-t border-gray-200/60 dark:border-gray-700/60 mb-4"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">مبلغ اجاره:</span>
                                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $property->rent_price > 0 ? number_format($property->rent_price) . ' تومان' : 'توافقی' }}</span>
                                </div>
                            @elseif($property->listing_type === 'presale')
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">پیش‌پرداخت:</span>
                                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $property->advance_price > 0 ? number_format($property->advance_price) . ' تومان' : 'توافقی' }}</span>
                                </div>
                                <div class="border-t border-gray-200/60 dark:border-gray-700/60 mb-4"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">قیمت کل:</span>
                                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $property->price > 0 ? number_format($property->price) . ' تومان' : 'توافقی' }}</span>
                                </div>
                            @else
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">قیمت نهایی:</span>
                                    <div class="text-right">
                                        <div class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $property->price > 0 ? number_format($property->price) : 'توافقی' }}</div>
                                        @if($property->price > 0)
                                            <div class="text-xs text-gray-400 font-bold mt-1">تومان</div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($property->is_convertible)
                                <div class="mt-6 pt-4 border-t border-gray-200/60 dark:border-gray-700/60 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-black shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                        {{ $property->listing_type == 'rent' ? 'مبلغ قابل تبدیل است' : 'قابل معاوضه با سایر املاک' }}
                                    </span>
                                    @if($property->convertible_with)
                                        <p class="text-xs font-bold text-gray-500 mt-2 bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-100 dark:border-gray-700">{{ $property->convertible_with }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl p-6 mb-8 text-center border border-indigo-100 dark:border-indigo-800/50">
                            <svg class="w-12 h-12 text-indigo-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <p class="font-black text-indigo-700 dark:text-indigo-400">قیمت فقط برای کاربران تایید شده</p>
                            <p class="text-xs text-indigo-500 mt-2 font-bold">برای مشاهده قیمت لطفاً وارد سیستم شوید</p>
                        </div>
                    @endif

                    <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-indigo-500/30">
                                {{ mb_substr($displayAgent->name ?? 'م', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 mb-1">مشاور اختصاصی این فایل</p>
                                <p class="font-black text-lg text-gray-900 dark:text-white">{{ $displayAgent->name ?? 'مشاور املاک' }}</p>
                            </div>
                        </div>

                        <a href="tel:{{ $displayAgent->mobile ?? $displayAgent->phone ?? '' }}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-4 rounded-xl transition-all shadow-xl shadow-indigo-600/30 hover:-translate-y-1 hover:shadow-indigo-600/50 flex items-center justify-center gap-3">
                            <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            تماس فوری با مشاور
                        </a>

                        <div class="mt-4 flex gap-2">
                            <button class="flex-1 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold py-3 rounded-xl transition-colors border border-gray-200 dark:border-gray-700 text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                نشان کردن
                            </button>
                            <button class="flex-1 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold py-3 rounded-xl transition-colors border border-gray-200 dark:border-gray-700 text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                                اشتراک
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Status Meta Box --}}
                <div class="grid grid-cols-2 gap-4">
                    @if($property->status)
                        <div class="p-4 bg-white dark:bg-gray-900 rounded-[1.5rem] shadow-lg border border-gray-100 dark:border-gray-800 text-center flex flex-col items-center justify-center">
                            <span class="w-2 h-2 rounded-full mb-2" style="background-color: {{ $property->status->color ?? '#6366f1' }}"></span>
                            <p class="text-xs font-bold text-gray-400 mb-1">وضعیت فایل</p>
                            <p class="font-black text-sm" style="color: {{ $property->status->color ?? '#6366f1' }}">{{ $property->status->label }}</p>
                        </div>
                    @endif
                    @if($property->registered_at)
                        <div class="p-4 bg-white dark:bg-gray-900 rounded-[1.5rem] shadow-lg border border-gray-100 dark:border-gray-800 text-center flex flex-col items-center justify-center">
                            <svg class="w-4 h-4 text-gray-300 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <p class="text-xs font-bold text-gray-400 mb-1">تاریخ ثبت</p>
                            <p class="font-black text-sm text-gray-700 dark:text-gray-200 dir-ltr">{{ \Morilog\Jalali\Jalalian::fromCarbon($property->registered_at)->format('Y/m/d') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Owner Info -->
                @if($canViewOwner && $property->owner)
                    <div class="p-6 bg-white dark:bg-gray-900 rounded-[1.5rem] shadow-lg border border-gray-100 dark:border-gray-800">
                        <h3 class="font-black text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                            اطلاعات مالک
                        </h3>
                        <div class="space-y-3 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700 text-sm">
                            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                                <span class="font-bold text-gray-500">نام مالک:</span>
                                <span class="font-black text-gray-900 dark:text-white">{{ $property->owner->first_name }} {{ $property->owner->last_name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-500">تلفن تماس:</span>
                                <a href="tel:{{ $property->owner->phone }}" class="font-black text-blue-600 hover:text-blue-700 dir-ltr flex items-center gap-1">
                                    {{ $property->owner->phone }}
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="fixed inset-0 z-[100] bg-black/95 hidden flex items-center justify-center opacity-0 transition-opacity duration-300 backdrop-blur-sm">
        <button onclick="closeLightbox()" class="absolute top-6 right-6 text-white/50 hover:text-white z-[101] bg-white/10 hover:bg-white/20 p-2 rounded-2xl backdrop-blur transition-all">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <button onclick="prevImage()" class="absolute left-6 text-white/50 hover:text-white z-[101] p-4 bg-white/10 hover:bg-white/20 rounded-2xl backdrop-blur transition-all hidden md:block">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <button onclick="nextImage()" class="absolute right-6 top-1/2 -translate-y-1/2 text-white/50 hover:text-white z-[101] p-4 bg-white/10 hover:bg-white/20 rounded-2xl backdrop-blur transition-all hidden md:block">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
        <img id="lightbox-image" src="" class="max-h-[90vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl" alt="Full size">
    </div>
@endsection

@push('scripts')
    <script>
        function loadScript(src, type, callback) {
            const existing = (type === 'js') ? document.querySelector(`script[src="${src}"]`) : document.querySelector(`link[href="${src}"]`);
            if (existing) { if (callback) callback(); return; }
            let tag;
            if (type === 'js') {
                tag = document.createElement('script'); tag.src = src; tag.onload = callback;
                tag.onerror = () => console.error(`Failed to load script: ${src}`);
            } else {
                tag = document.createElement('link'); tag.href = src; tag.rel = 'stylesheet';
            }
            document.head.appendChild(tag);
        }

        const images = [
            @if($canViewGallery && $images->count() > 0)
                @foreach($images as $image) "{{ asset('storage/' . $image->path) }}", @endforeach
                @elseif($canViewCover && $property->cover_image)
                "{{ asset('storage/' . $property->cover_image) }}"
            @endif
        ];
        let currentImageIndex = 0;

        function changeMainImage(src) {
            const main = document.getElementById('main-image');
            main.style.opacity = '0';
            setTimeout(() => { main.src = src; main.style.opacity = '1'; }, 200);
        }

        function scrollThumbnails(direction) {
            const container = document.getElementById('thumbnails-scroll');
            container.scrollBy({ left: direction === 'left' ? -250 : 250, behavior: 'smooth' });
        }

        function openLightbox() {
            if (images.length === 0) return;
            const currentSrc = document.getElementById('main-image').src;
            currentImageIndex = images.indexOf(currentSrc);
            if (currentImageIndex === -1) currentImageIndex = 0;
            document.getElementById('lightbox-image').src = images[currentImageIndex];
            const lb = document.getElementById('lightbox');
            lb.classList.remove('hidden');
            setTimeout(() => lb.classList.remove('opacity-0'), 10);
            document.body.style.overflow = 'hidden';

            // مخفی کردن هدرها (حل مشکل روی هم افتادگی z-index)
            document.querySelectorAll('header').forEach(header => {
                header.style.opacity = '0';
                header.style.zIndex = '0';
                header.style.pointerEvents = 'none';
            });
        }

        function closeLightbox() {
            const lb = document.getElementById('lightbox');
            lb.classList.add('opacity-0');
            setTimeout(() => {
                lb.classList.add('hidden');
                document.body.style.overflow = 'auto';

                // بازگرداندن هدرها
                document.querySelectorAll('header').forEach(header => {
                    header.style.opacity = '1';
                    header.style.zIndex = '50';
                    header.style.pointerEvents = 'auto';
                });
            }, 300);
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            document.getElementById('lightbox-image').src = images[currentImageIndex];
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            document.getElementById('lightbox-image').src = images[currentImageIndex];
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
        });

        document.addEventListener('DOMContentLoaded', function() {
            @if($canViewMap && $property->latitude && $property->longitude)
            const mapService = '{{ $mapService }}';
            const mapIrApiKey = '{{ $mapIrApiKey }}';
            const lat = {{ $property->latitude }};
            const lng = {{ $property->longitude }};
            const title = '{{ addslashes($property->title) }}';
            const baseUrl = '{{ asset("modules/properties/dist") }}';

            const popupHtml = `
                    <div class="p-4 pt-6 text-right flex flex-col gap-1 border-t-4 border-indigo-500 rounded-t-xl bg-white dark:bg-gray-800" dir="rtl">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm line-clamp-2 leading-relaxed pr-2">${title}</h3>
                        <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 mt-1 pr-2">
                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>موقعیت مکانی ملک</span>
                        </div>
                    </div>
                `;

            const customMarkerIcon = function() {
                return L.icon({
                    iconUrl: baseUrl + '/assets/images/marker-icon.png',
                    iconRetinaUrl: baseUrl + '/assets/images/marker-icon.png',
                    shadowUrl: baseUrl + '/assets/images/marker-shadow.png',
                    iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
                });
            };

            if (mapService === 'map_ir') {
                if (!mapIrApiKey) {
                    document.getElementById('map').innerHTML = `<div class="flex items-center justify-center h-full bg-gray-100 dark:bg-gray-800 text-gray-500 font-bold text-sm p-4 text-center rounded-2xl">برای نمایش نقشه، لطفاً کلید API سرویس Map.ir را در تنظیمات وارد کنید.</div>`;
                    return;
                }
                loadScript(baseUrl + '/css/mapp.min.css', 'css'); loadScript(baseUrl + '/css/fa/style.css', 'css');
                const setupMapIr = () => {
                    if (typeof Mapp === 'undefined') return;
                    const mapp = new Mapp({ element: '#map', presets: { latlng: { lat: lat, lng: lng }, zoom: 15 }, apiKey: mapIrApiKey });
                    mapp.addLayers();
                    L.marker([lat, lng], {icon: customMarkerIcon()}).addTo(mapp.map).bindPopup(popupHtml).openPopup();
                };
                const loadMapp = () => loadScript(baseUrl + '/js/mapp.min.js', 'js', () => setTimeout(setupMapIr, 0));
                const loadEnv = () => loadScript(baseUrl + '/js/mapp.env.js', 'js', loadMapp);
                if (typeof jQuery === 'undefined' || !jQuery.fn) loadScript(baseUrl + '/js/jquery-3.2.1.min.js', 'js', loadEnv); else loadEnv();
            } else {
                if (typeof L !== 'undefined') {
                    const map = L.map('map').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    L.marker([lat, lng], {icon: customMarkerIcon()}).addTo(map).bindPopup(popupHtml).openPopup();
                }
            }
            @endif
        });
    </script>
@endpush
