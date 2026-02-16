@extends('layouts.guest')

@section('content')
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    @php
        // Helper function to check visibility
        $checkVisibility = function($key, $isRestrictedByDefault = false) use ($property, $visibilitySettings) {
            $allowedRoles = $visibilitySettings[$key] ?? [];

            // If no roles defined:
            // - For restricted items (Owner, Notes): Default to Admin/SuperAdmin only (plus Creator)
            // - For public items (Price, Map): Visible to everyone
            if (empty($allowedRoles)) {
                if ($isRestrictedByDefault) {
                    $allowedRoles = ['super-admin', 'admin'];
                } else {
                    return true; // Public
                }
            }

            // If user is not logged in, they can only see public items
            if (!auth()->check()) {
                return !$isRestrictedByDefault && empty($visibilitySettings[$key] ?? []);
            }

            $user = auth()->user();

            // Creator always sees everything
            if ($user->id == $property->created_by || $user->id == $property->agent_id) {
                return true;
            }

            // Super Admin always sees everything (optional, but good practice)
            if ($user->hasRole('super-admin')) {
                return true;
            }

            // Check if user has any of the allowed roles
            return $user->hasAnyRole($allowedRoles);
        };

        $canViewOwner = $checkVisibility('owner_info', true);
        $canViewNotes = $checkVisibility('confidential_notes', true);
        $canViewPrice = $checkVisibility('price_info', false);
        $canViewMap   = $checkVisibility('map_info', false);

        // Determine the agent to display
        $displayAgent = $property->agent ?? $property->creator;
    @endphp

    <header class="w-full top-0 z-50 transition-all duration-300 backdrop-blur-md border-b border-transparent bg-white/50 dark:bg-gray-950/50 sticky">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="font-bold text-xl tracking-tight">سیستم CRM</a>
            </div>
            <nav class="flex items-center gap-4">
                <a href="{{ route('properties.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">بازگشت به لیست</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Gallery -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-800" id="gallery-container">
                    @php
                        $images = $property->images;
                        $mainImage = $images->first() ? asset('storage/' . $images->first()->path) : ($property->cover_image ? asset('storage/' . $property->cover_image) : null);
                    @endphp

                    @if($mainImage)
                        <div class="relative h-96 group cursor-pointer" onclick="openLightbox()">
                            <img id="main-image" src="{{ $mainImage }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition duration-300 group-hover:scale-105">

                            <!-- Special Badge -->
                            <div class="absolute top-4 right-4 flex flex-col gap-2">
                                @if($property->meta['is_special'] ?? false)
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-bold shadow-md">ویژه</span>
                                @endif
                            </div>

                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition duration-300 flex items-center justify-center">
                                <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition duration-300 drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                            @if($images->count() > 0)
                                <div class="absolute bottom-4 right-4 bg-black/60 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                                    {{ $images->count() }} تصویر
                                </div>
                            @endif
                        </div>

                        @if($images->count() > 1)
                            <div class="relative group/thumbs px-8 py-4">
                                <button onclick="scrollThumbnails('right')" class="absolute right-1 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 p-2 rounded-full shadow-md hover:bg-white dark:hover:bg-gray-800 z-10 hidden md:flex items-center justify-center text-gray-700 dark:text-gray-200 transition opacity-0 group-hover/thumbs:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>

                                <div id="thumbnails-scroll" class="flex gap-3 overflow-x-auto scrollbar-hide snap-x snap-mandatory scroll-smooth">
                                    @foreach($images as $image)
                                        <div class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden cursor-pointer border-2 border-transparent hover:border-indigo-500 transition snap-start"
                                             onclick="changeMainImage('{{ asset('storage/' . $image->path) }}')">
                                            <img src="{{ asset('storage/' . $image->path) }}" alt="" class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                </div>

                                <button onclick="scrollThumbnails('left')" class="absolute left-1 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 p-2 rounded-full shadow-md hover:bg-white dark:hover:bg-gray-800 z-10 hidden md:flex items-center justify-center text-gray-700 dark:text-gray-200 transition opacity-0 group-hover/thumbs:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="h-96 bg-gray-200 flex items-center justify-center text-gray-400 relative">
                            <!-- Special Badge for No Image -->
                            <div class="absolute top-4 right-4 flex flex-col gap-2">
                                @if($property->meta['is_special'] ?? false)
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-bold shadow-md">ویژه</span>
                                @endif
                            </div>
                            بدون تصویر
                        </div>
                    @endif
                </div>

                <!-- General Specifications (New Box) -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-8 border border-gray-100 dark:border-gray-800">
                    <h2 class="text-2xl font-bold mb-6">مشخصات کلی</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-8">
                        @php
                            $specs = collect();

                            // Listing Type (File Type)
                            if($property->listing_type) {
                                $listingLabel = match($property->listing_type) {
                                    'sale' => 'فروش',
                                    'rent' => 'رهن و اجاره',
                                    'presale' => 'پیش‌فروش',
                                    default => $property->listing_type
                                };
                                $specs->push(['name' => 'نوع فایل', 'value' => $listingLabel]);
                            }

                            // Property Type
                            if($property->property_type) {
                                $typeLabel = match($property->property_type) {
                                    'apartment' => 'آپارتمان',
                                    'villa' => 'ویلایی',
                                    'land' => 'زمین',
                                    'commercial' => 'تجاری',
                                    'office' => 'اداری',
                                    default => $property->property_type
                                };
                                $specs->push(['name' => 'نوع ملک', 'value' => $typeLabel]);
                            }

                            // Category
                            if($property->category) {
                                $specs->push(['name' => 'دسته‌بندی', 'value' => $property->category->name]);
                            }

                            // Document Type
                            if($property->document_type) {
                                $docLabel = \Modules\Properties\Entities\Property::DOCUMENT_TYPES[$property->document_type] ?? $property->document_type;
                                $specs->push(['name' => 'نوع سند', 'value' => $docLabel]);
                            }

                            // Area
                            if($property->area) {
                                $specs->push(['name' => 'متراژ', 'value' => $property->area . ' متر']);
                            }

                            // Usage Type
                            if($property->usage_type) {
                                $usageLabel = match($property->usage_type) {
                                    'residential' => 'مسکونی',
                                    'industrial' => 'صنعتی',
                                    'commercial' => 'تجاری',
                                    'agricultural' => 'کشاورزی',
                                    default => $property->usage_type
                                };
                                $specs->push(['name' => 'کاربری', 'value' => $usageLabel]);
                            }

                            // Building ID (if exists)
                            if($property->building_id) {
                                $specs->push(['name' => 'شناسه ساختمان', 'value' => $property->building_id]);
                            }

                            // Delivery Date (Jalali) - For Presale
                            if($property->listing_type === 'presale' && $property->delivery_date) {
                                try {
                                    $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($property->delivery_date)->format('Y/m/d');
                                    $specs->push(['name' => 'تاریخ تحویل', 'value' => $jalaliDate]);
                                } catch (\Exception $e) {}
                            }

                            // Convertible (Updated for all types)
                            if($property->is_convertible) {
                                $convertibleLabel = $property->listing_type == 'rent' ? 'قابل تبدیل' : 'قابل معاوضه';
                                $specs->push(['name' => $convertibleLabel, 'value' => 'بله' . ($property->convertible_with ? " ({$property->convertible_with})" : '')]);
                            }

                            // Bedrooms (if exists as column)
                            if(isset($property->bedrooms)) {
                                 $specs->push(['name' => 'اتاق خواب', 'value' => $property->bedrooms]);
                            }

                            // Bathrooms (if exists as column)
                            if(isset($property->bathrooms)) {
                                 $specs->push(['name' => 'سرویس بهداشتی', 'value' => $property->bathrooms]);
                            }

                            // Dynamic Attributes (Everything except features)
                            $details = $property->attributeValues->filter(function($attr) {
                                // Ensure attribute relation exists
                                if (!$attr->attribute) return false;

                                // Exclude features
                                if ($attr->attribute->section === 'features') return false;

                                // Exclude empty values, but allow "0"
                                return $attr->value !== null && $attr->value !== '';
                            });

                            foreach($details as $detail) {
                                $specs->push(['name' => $detail->attribute->name, 'value' => $detail->value]);
                            }

                            // Custom Details from Meta (New Addition)
                            if (isset($property->meta['details']) && is_array($property->meta['details'])) {
                                foreach ($property->meta['details'] as $key => $value) {
                                    if ($value !== null && $value !== '') {
                                        $specs->push(['name' => $key, 'value' => $value]);
                                    }
                                }
                            }
                        @endphp

                        @foreach($specs as $spec)
                            <div class="flex flex-col">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ $spec['name'] }}</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $spec['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-8 border border-gray-100 dark:border-gray-800">
                    <h2 class="text-2xl font-bold mb-6">توضیحات ملک</h2>
                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $property->description }}
                    </div>

                    @if($property->video)
                        <div class="mt-8 pt-8 border-t border-gray-100 dark:border-gray-800">
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                ویدیو ملک
                            </h3>
                            <div class="rounded-xl overflow-hidden shadow-lg bg-black">
                                <video controls class="w-full aspect-video">
                                    <source src="{{ asset('storage/' . $property->video) }}" type="video/mp4">
                                    مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند.
                                </video>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Confidential Notes -->
                @if($canViewNotes)
                    @if($property->confidential_notes)
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-2xl shadow-lg p-8 border border-yellow-200 dark:border-yellow-800">
                            <div class="flex items-center gap-2 mb-4 text-yellow-800 dark:text-yellow-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <h2 class="text-xl font-bold">یادداشت‌های محرمانه</h2>
                            </div>
                            <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                                {{ $property->confidential_notes }}
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Features -->
                @php
                    $features = $property->attributeValues->filter(function($attr) {
                        return $attr->attribute->section === 'features' && $attr->value == '1';
                    });

                    // Custom Features from Meta
                    $customFeatures = [];
                    if (isset($property->meta['features']) && is_array($property->meta['features'])) {
                        $customFeatures = $property->meta['features'];
                    }
                @endphp
                @if($features->count() > 0 || count($customFeatures) > 0)
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-8 border border-gray-100 dark:border-gray-800">
                        <h2 class="text-2xl font-bold mb-6">امکانات و ویژگی‌ها</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($features as $feature)
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>{{ $feature->attribute->name }}</span>
                                </div>
                            @endforeach

                            @foreach($customFeatures as $customFeature)
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>{{ $customFeature }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Location Map -->
                @if($canViewMap && $property->latitude && $property->longitude)
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-8 border border-gray-100 dark:border-gray-800">
                        <h2 class="text-2xl font-bold mb-6">موقعیت مکانی</h2>
                        <div class="mb-4 text-gray-600 dark:text-gray-300 flex items-start gap-2">
                            <svg class="w-6 h-6 text-indigo-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="text-lg">{{ $property->address }}</p>
                        </div>
                        <div id="map" class="w-full h-80 rounded-xl z-0"></div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Key Info Card -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-800 sticky top-24">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold mb-2">{{ $property->title }}</h1>
                        <p class="text-gray-500 text-sm">کد ملک: {{ $property->code }}</p>
                    </div>

                    @if($canViewPrice)
                        <div class="flex flex-col gap-4 mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                            @if($property->listing_type === 'rent')
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 dark:text-gray-400">رهن:</span>
                                    <span class="text-lg font-bold text-indigo-600">
                                        {{ $property->deposit_price > 0 ? number_format($property->deposit_price) . ' تومان' : 'توافقی' }}
                                    </span>
                                </div>
                                <div class="border-t border-indigo-100 dark:border-indigo-800"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 dark:text-gray-400">اجاره:</span>
                                    <span class="text-lg font-bold text-indigo-600">
                                        {{ $property->rent_price > 0 ? number_format($property->rent_price) . ' تومان' : 'توافقی' }}
                                    </span>
                                </div>
                            @elseif($property->listing_type === 'presale')
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 dark:text-gray-400">پیش‌پرداخت:</span>
                                    <span class="text-lg font-bold text-indigo-600">
                                        {{ $property->advance_price > 0 ? number_format($property->advance_price) . ' تومان' : 'توافقی' }}
                                    </span>
                                </div>
                                <div class="border-t border-indigo-100 dark:border-indigo-800"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 dark:text-gray-400">قیمت کل:</span>
                                    <span class="text-lg font-bold text-indigo-600">
                                        {{ $property->price > 0 ? number_format($property->price) . ' تومان' : 'توافقی' }}
                                    </span>
                                </div>
                            @else
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 dark:text-gray-400">قیمت کل:</span>
                                    <span class="text-2xl font-bold text-indigo-600">
                                        {{ $property->price > 0 ? number_format($property->price) . ' تومان' : 'توافقی' }}
                                    </span>
                                </div>
                            @endif

                            @if($property->is_convertible)
                                <div class="mt-2 pt-2 border-t border-indigo-100 dark:border-indigo-800 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-green-100 text-green-700 text-xs font-bold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                        {{ $property->listing_type == 'rent' ? 'قابل تبدیل' : 'قابل معاوضه' }}
                                    </span>
                                    @if($property->convertible_with)
                                        <p class="text-xs text-gray-500 mt-1">{{ $property->convertible_with }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Status & Date -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        @if($property->status)
                            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                                <p class="text-xs text-gray-500 mb-1">وضعیت</p>
                                <span class="inline-block px-2 py-1 rounded text-xs font-bold text-white" style="background-color: {{ $property->status->color ?? '#6366f1' }}">
                                    {{ $property->status->label }}
                                </span>
                            </div>
                        @endif

                        @if($property->registered_at)
                            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                                <p class="text-xs text-gray-500 mb-1">تاریخ ثبت</p>
                                <p class="font-bold text-sm">
                                    {{ \Morilog\Jalali\Jalalian::fromCarbon($property->registered_at)->format('Y/m/d') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Owner Info -->
                    @if($canViewOwner)
                        @if($property->owner)
                            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                                <h3 class="font-bold text-blue-800 dark:text-blue-200 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    اطلاعات مالک
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">نام:</span>
                                        <span class="font-medium">{{ $property->owner->first_name }} {{ $property->owner->last_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">تلفن:</span>
                                        <a href="tel:{{ $property->owner->phone }}" class="font-medium text-blue-600 hover:underline dir-ltr">{{ $property->owner->phone }}</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xl">
                                {{ substr($displayAgent->name ?? 'A', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">مشاور شما</p>
                                <p class="font-bold">{{ $displayAgent->name ?? 'مشاور املاک' }}</p>
                            </div>
                        </div>
                        <a href="tel:{{ $displayAgent->mobile ?? $displayAgent->phone ?? '' }}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-indigo-200 dark:shadow-none flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            تماس با مشاور
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="fixed inset-0 z-[100] bg-black/90 hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-[101]">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <button onclick="prevImage()" class="absolute left-4 text-white hover:text-gray-300 z-[101] p-2 bg-black/50 rounded-full">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button onclick="nextImage()" class="absolute right-4 text-white hover:text-gray-300 z-[101] p-2 bg-black/50 rounded-full">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        <img id="lightbox-image" src="" class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg shadow-2xl" alt="Full size">
    </div>

    <script>
        // Gallery Logic
        function changeMainImage(src) {
            const mainImage = document.getElementById('main-image');
            mainImage.style.opacity = '0';
            setTimeout(() => {
                mainImage.src = src;
                mainImage.style.opacity = '1';
            }, 200);
        }

        function scrollThumbnails(direction) {
            const container = document.getElementById('thumbnails-scroll');
            const scrollAmount = 200;
            if (direction === 'left') {
                container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        }

        // Lightbox Logic
        const images = [
            @if($property->images->count() > 0)
                @foreach($property->images as $image)
                    "{{ asset('storage/' . $image->path) }}",
                @endforeach
            @elseif($property->cover_image)
                "{{ asset('storage/' . $property->cover_image) }}"
            @endif
        ];

        let currentImageIndex = 0;
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');

        function openLightbox() {
            if (images.length === 0) return;

            // Find current image index based on main image src
            const currentSrc = document.getElementById('main-image').src;
            currentImageIndex = images.findIndex(img => img === currentSrc);
            if (currentImageIndex === -1) currentImageIndex = 0;

            lightboxImage.src = images[currentImageIndex];
            lightbox.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                lightbox.classList.remove('opacity-0');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.add('opacity-0');
            setTimeout(() => {
                lightbox.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            lightboxImage.src = images[currentImageIndex];
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            lightboxImage.src = images[currentImageIndex];
        }

        // Close lightbox on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
        });

        // Map Logic
        document.addEventListener('DOMContentLoaded', function() {
            @if($canViewMap && $property->latitude && $property->longitude)
                if (typeof L !== 'undefined') {
                    const map = L.map('map').setView([{{ $property->latitude }}, {{ $property->longitude }}], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    L.marker([{{ $property->latitude }}, {{ $property->longitude }}]).addTo(map)
                        .bindPopup('{{ $property->title }}')
                        .openPopup();
                } else {
                    console.error('Leaflet is not loaded');
                }
            @endif
        });
    </script>
@endsection
