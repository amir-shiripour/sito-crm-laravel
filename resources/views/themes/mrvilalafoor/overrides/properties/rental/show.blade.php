@extends('layouts.web')

@section('title', $property->title . ' - اجاره ویلا و اقامتگاه در لفور')

@push('styles')
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* پاپ‌آپ نقشه Leaflet */
        .leaflet-popup-content-wrapper {
            border-radius: 1.25rem !important; padding: 0 !important; overflow: hidden !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #ECECEE !important; background-color: #ffffff !important;
        }
        .dark .leaflet-popup-content-wrapper { background-color: #18181B !important; border-color: #27272a !important; }
        .leaflet-popup-tip { background-color: #ffffff !important; }
        .dark .leaflet-popup-tip { background-color: #18181B !important; }
        .leaflet-popup-content { margin: 0 !important; font-family: 'IRANYekanX', sans-serif !important; }
    </style>
    @if(!empty($property->latitude) && !empty($property->longitude))
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endif
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

        $canViewPrice = $checkVisibility('price_info', false);
        $canViewMapPermission = $checkVisibility('map_info', false);
        $canViewCover = $checkVisibility('cover_image', false);
        $canViewGallery = $checkVisibility('gallery_images', false);

        $rSettings = $rentalDisplaySettings ?? [
            'show_host_box' => \Modules\Properties\Entities\PropertySetting::get('rental_show_host_box', 'all'),
            'host_show_avatar' => (bool) \Modules\Properties\Entities\PropertySetting::get('rental_host_show_avatar', 1),
            'host_show_badge' => (bool) \Modules\Properties\Entities\PropertySetting::get('rental_host_show_badge', 1),
            'host_show_about' => (bool) \Modules\Properties\Entities\PropertySetting::get('rental_host_show_about', 1),
            'host_show_phone' => \Modules\Properties\Entities\PropertySetting::get('rental_host_show_phone', 'confirmed_booking'),
            'show_address' => \Modules\Properties\Entities\PropertySetting::get('rental_show_address', 'all'),
            'show_map' => \Modules\Properties\Entities\PropertySetting::get('rental_show_map', 'all'),
            'map_display_type' => \Modules\Properties\Entities\PropertySetting::get('rental_map_display_type', 'circle_then_exact'),
            'map_circle_radius' => (int) \Modules\Properties\Entities\PropertySetting::get('rental_map_circle_radius', 300),
            'show_direct_call_btn' => \Modules\Properties\Entities\PropertySetting::get('rental_show_direct_call_btn', 'all'),
        ];

        $isConfirmedOrPrivileged = isset($hasConfirmedBooking) ? (bool) $hasConfirmedBooking : false;
        if (!isset($hasConfirmedBooking) && auth()->check()) {
            $isConfirmedOrPrivileged = app(\Modules\Properties\Services\RentalPublicService::class)->hasConfirmedBooking($property, auth()->user());
        }

        $evalRule = function($settingVal) use ($isConfirmedOrPrivileged) {
            if ($settingVal === 'none') return false;
            if ($settingVal === 'confirmed_booking') return $isConfirmedOrPrivileged;
            return true;
        };

        $canShowHostBox = $evalRule($rSettings['show_host_box'] ?? 'all');
        $canShowHostAvatar = !empty($rSettings['host_show_avatar']);
        $canShowHostBadge = !empty($rSettings['host_show_badge']);
        $canShowHostAbout = !empty($rSettings['host_show_about']);
        $canShowHostPhoneInBox = $evalRule($rSettings['host_show_phone'] ?? 'confirmed_booking');

        $canShowAddress = $evalRule($rSettings['show_address'] ?? 'all');
        $canShowMap = $canViewMapPermission && $evalRule($rSettings['show_map'] ?? 'all');
        $canShowDirectCall = $evalRule($rSettings['show_direct_call_btn'] ?? 'all');

        $mapDisplaySetting = $rSettings['map_display_type'] ?? 'circle_then_exact';
        $circleRadius = max(50, (int) ($rSettings['map_circle_radius'] ?? 300));
        $isMapExact = ($mapDisplaySetting === 'exact') ? true : (($mapDisplaySetting === 'circle') ? false : $isConfirmedOrPrivileged);

        $displayAgent = $property->agent ?? $property->creator;
        $host = $property->host;
        $hostPhone = $host && $host->phone ? $host->phone : ($displayAgent->mobile ?? $displayAgent->phone ?? null);
        $config = $property->rentalConfig;
        $currencyLabel = $currency === 'toman' ? 'تومان' : 'ریال';
        $images = $property->images;

        $formatTimeOnly = function($time, $default = '14:00') {
            if (empty($time)) return $default;
            if (preg_match('/^(\d{1,2}:\d{2})/', trim($time), $m)) return $m[1];
            return $time;
        };
        $checkInTimeFormatted = !empty($config?->check_in_time) ? $formatTimeOnly($config->check_in_time) : null;
        $checkOutTimeFormatted = !empty($config?->check_out_time) ? $formatTimeOnly($config->check_out_time) : null;

        // محاسبات قیمت پایه و تخفیف روزانه
        $baseNightPrice = (float) ($config?->price_per_night ?: ($property->rent_price ?: ($property->price ?: 0)));
        $hasDiscount = !empty($config?->has_discount) && (float)($config?->discount_value ?? 0) > 0;
        $discountAmount = $hasDiscount ? $config->calculateDiscountAmount($baseNightPrice) : 0;
        $payablePrice = max(0, $baseNightPrice - $discountAmount);
        $discountBadge = null;
        if ($hasDiscount) {
            if ($config->discount_type === 'percent') {
                $discountBadge = ((int) $config->discount_value) . '٪ تخفیف لحظه آخری';
            } else {
                $discountBadge = number_format($config->discount_value) . ' ' . $currencyLabel . ' تخفیف';
            }
        }
        $isInstant = !empty($config?->instant_booking);

        $features = $property->attributeValues ? $property->attributeValues->filter(function($attr) {
            return $attr->attribute && $attr->attribute->section === 'features' && ($attr->value == '1' || $attr->value === true);
        }) : collect();

        // استخراج و تفکیک مشخصات واقعی اقامتگاه از مقادیر ویژگی‌ها بدون دیتای فیک
        $attrMap = [];
        if ($property->relationLoaded('attributeValues') || $property->attributeValues) {
            foreach ($property->attributeValues as $av) {
                if ($av->attribute) {
                    $attrMap[trim($av->attribute->name)] = $av->value;
                }
            }
        }
        $bedroomsCount = $property->bedrooms ?: (!empty($attrMap['تعداد اتاق خواب']) ? (int)$attrMap['تعداد اتاق خواب'] : null);
        $doubleBeds = !empty($attrMap['تعداد تخت دو نفره']) ? (int)$attrMap['تعداد تخت دو نفره'] : null;
        $singleBeds = !empty($attrMap['تعداد تخت یک نفره']) ? (int)$attrMap['تعداد تخت یک نفره'] : null;
        $bathroomsCount = $property->bathrooms ?: (!empty($attrMap['تعداد حمام']) ? (int)$attrMap['تعداد حمام'] : (!empty($attrMap['تعداد سرویس بهداشتی']) ? (int)$attrMap['تعداد سرویس بهداشتی'] : null));
        $areaValue = $property->area_formatted ?? ($property->area ? $property->area . ' متر' : (!empty($attrMap['متراژ اقامتگاه (زیربنا)']) ? $attrMap['متراژ اقامتگاه (زیربنا)'] . ' متر' : (!empty($attrMap['متراژ']) ? $attrMap['متراژ'] . ' متر' : null)));

        // استخراج و تفکیک دقیق قوانین اقامتگاه
        $allHouseRules = $config?->house_rules ?? [];
        $ruleKeys = is_array($allHouseRules) ? $allHouseRules : [];
        $smokingAllowed = !in_array('no_smoking', $ruleKeys);
        $petsAllowed = !in_array('no_pets', $ruleKeys);
        $partiesAllowed = !in_array('no_party', $ruleKeys);
        $idRequired = in_array('national_card_required', $ruleKeys);
        $quietHours = in_array('quiet_hours', $ruleKeys);
        $coupleRules = in_array('couple_rules', $ruleKeys);

        $customRules = [];
        $defaultRuleKeys = ['no_smoking', 'no_pets', 'no_party', 'national_card_required', 'quiet_hours', 'couple_rules'];
        foreach ($ruleKeys as $r) {
            if (!in_array($r, $defaultRuleKeys) && is_string($r) && trim($r) !== '') {
                $customRules[] = trim($r);
            }
        }

        $currentClient = auth('client')->user();
        $clientPayload = [
            'is_logged_in' => (bool)$currentClient,
            'name' => $currentClient->full_name ?? ($currentClient->name ?? ''),
            'phone' => $currentClient->phone ?? '',
            'national_code' => $currentClient->national_code ?? '',
        ];

        // گردآوری تصاویر برای گالری
        $allGalleryUrls = [];
        if (!empty($property->cover_image)) {
            $allGalleryUrls[] = str_starts_with($property->cover_image, 'http')
                ? $property->cover_image
                : asset('storage/' . $property->cover_image);
        }
        if ($images && $images->count() > 0) {
            foreach($images as $img) {
                $imgPath = $img->path ?? $img->image_path;
                if ($imgPath) {
                    $allGalleryUrls[] = str_starts_with($imgPath, 'http') ? $imgPath : asset('storage/' . $imgPath);
                }
            }
        }
        if (empty($allGalleryUrls)) {
            if (!empty($property->primary_image_url) && !str_contains($property->primary_image_url, 'placeholder')) {
                $allGalleryUrls[] = $property->primary_image_url;
            } else {
                $allGalleryUrls[] = asset('images/property-placeholder.svg');
            }
        }

        $checkoutUrlBase = route('properties.checkout', $property->slug ?: $property->code);
        $similarList = $similarProperties ?? collect([]);
    @endphp

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-5 w-full font-sans pb-28 lg:pb-12" 
         x-data="rentalBookingApp(@js($calendarData['flatDates'] ?? []), @js($config), '{{ $currencyLabel }}', '{{ addslashes($property->title) }}', '{{ $property->code }}', @js($clientPayload), '{{ $checkoutUrlBase }}', {{ $hasDiscount ? 'true' : 'false' }}, '{{ $config?->discount_type ?? 'percent' }}', {{ (float)($config?->discount_value ?? 0) }})">

        {{-- ۱. بخش گالری و تصاویر شاخص (در موبایل تمام‌عرض با اسلایدر، در دسکتاپ موزاییکی شکیل) --}}
        <div class="relative -mx-4 sm:mx-0 mb-6 sm:rounded-3xl overflow-hidden bg-gray-100 dark:bg-zinc-900 border-b sm:border border-gray-200/80 dark:border-zinc-800 shadow-sm"
             x-data="{ activeIndex: 0, total: {{ count($allGalleryUrls) }} }">
            
            {{-- نمایش موبایل (اسلایدر سوایپ با شمارنده عکس) --}}
            <div class="block sm:hidden relative aspect-[16/11] w-full overflow-hidden"
                 x-data="{ touchStartX: 0, touchEndX: 0 }"
                 @touchstart.passive="touchStartX = $event.changedTouches[0].screenX"
                 @touchend.passive="touchEndX = $event.changedTouches[0].screenX; 
                    if (touchStartX - touchEndX > 40) { 
                        activeIndex = (activeIndex < total - 1 ? activeIndex + 1 : 0); 
                    } else if (touchEndX - touchStartX > 40) { 
                        activeIndex = (activeIndex > 0 ? activeIndex - 1 : total - 1); 
                    }">
                <template x-for="(img, idx) in @js($allGalleryUrls)" :key="idx">
                    <img :src="img" 
                         x-show="activeIndex === idx" 
                         @click="openLightbox(idx)" 
                         alt="{{ $property->title }}" 
                         class="w-full h-full object-cover select-none cursor-pointer transition-opacity duration-300">
                </template>

                {{-- دکمه‌های ناوبری موبایل روی تصویر --}}
                <div class="absolute inset-x-3 top-3 flex items-center justify-between z-10">
                    <a href="{{ route('properties.index') }}" class="w-9 h-9 rounded-full bg-black/40 backdrop-blur-md text-white flex items-center justify-center text-sm shadow">
                        <svg class="w-5 h-5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <button type="button" onclick="shareProperty()" class="w-9 h-9 rounded-full bg-black/40 backdrop-blur-md text-white flex items-center justify-center text-sm shadow">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    </button>
                </div>

                {{-- شماره تصویر در پایین عکس موبایل --}}
                <div class="absolute bottom-3 left-3 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-white text-xs font-bold font-sans flex items-center gap-1.5 z-10">
                    <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span x-text="formatNumber(activeIndex + 1) + ' / ' + formatNumber(total)"></span>
                </div>

                {{-- دکمه‌های قبلی / بعدی لمسی (فلش راست = بعدی، فلش چپ = قبلی) --}}
                <button type="button" 
                        @click="activeIndex = (activeIndex < total - 1 ? activeIndex + 1 : 0)" 
                        class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition shadow z-10" 
                        title="تصویر بعدی" 
                        aria-label="تصویر بعدی">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <button type="button" 
                        @click="activeIndex = (activeIndex > 0 ? activeIndex - 1 : total - 1)" 
                        class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition shadow z-10" 
                        title="تصویر قبلی" 
                        aria-label="تصویر قبلی">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
            </div>

            {{-- نمایش دسکتاپ (موزاییک ۵ عکسی شبیه جاباما / جاجیگا) --}}
            <div class="hidden sm:grid grid-cols-4 gap-2 p-2">
                <div class="col-span-2 row-span-2 aspect-[4/3] rounded-2xl overflow-hidden cursor-pointer relative group" onclick="openLightbox(0)">
                    <img src="{{ $allGalleryUrls[0] }}" 
                         alt="{{ $property->title }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors"></div>
                </div>

                @for($i = 1; $i < min(5, count($allGalleryUrls)); $i++)
                    <div class="aspect-[4/3] rounded-2xl overflow-hidden cursor-pointer relative group" onclick="openLightbox({{ $i }})">
                        <img src="{{ $allGalleryUrls[$i] }}" 
                             alt="{{ $property->title }} - تصویر {{ $i + 1 }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors"></div>

                        @if($i === 4 && count($allGalleryUrls) > 5)
                            <div class="absolute inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center text-white text-xs font-black">
                                + {{ count($allGalleryUrls) - 5 }} تصویر دیگر
                            </div>
                        @endif
                    </div>
                @endfor
            </div>

            {{-- دکمه مشاهده همه تصاویر دسکتاپ --}}
            <button type="button" 
                    onclick="openLightbox(0)" 
                    class="hidden sm:flex absolute bottom-5 left-5 px-4 py-2.5 rounded-2xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-gray-200 dark:border-zinc-700 hover:bg-white dark:hover:bg-zinc-800 text-xs font-bold text-gray-900 dark:text-white transition-all shadow-md items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>مشاهده همه {{ count($allGalleryUrls) }} تصویر</span>
            </button>
        </div>

        {{-- ساختار اصلی: محتوای جزئیات در راست، ویجت رزرو در چپ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- ستون راست: محتوا و سکشن‌های اقامتگاه به سبک نمونه ضمیمه --}}
            <div class="lg:col-span-7 xl:col-span-8 space-y-7">

                {{-- ۲. عنوان اقامتگاه، کد، موقعیت و امتیاز --}}
                <div class="space-y-2 border-b border-gray-100 dark:border-zinc-800 pb-5">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($hasDiscount)
                            <span class="px-2.5 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50 text-xs font-bold">
                                🔥 {{ $discountBadge }}
                            </span>
                        @endif
                        @if($isInstant)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold border border-indigo-200 dark:border-indigo-800/40">
                                ⚡ رزرو آنی
                            </span>
                        @endif
                    </div>

                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-gray-900 dark:text-white tracking-tight leading-snug">
                        {{ $property->title }}
                    </h1>

                    <div class="flex items-center gap-2.5 text-xs text-gray-500 dark:text-gray-400 flex-wrap pt-1">
                        @if(!empty($property->rating))
                            <span class="inline-flex items-center gap-1 font-bold text-gray-900 dark:text-gray-200">
                                <span class="text-amber-500 text-sm">★</span>
                                <span>{{ number_format($property->rating, 1) }}</span>
                                @if(!empty($property->reviews_count))
                                    <span class="text-gray-400 font-normal">({{ $property->reviews_count }} نظر مسافران)</span>
                                @endif
                            </span>
                            <span class="text-gray-300 dark:text-gray-700">•</span>
                        @endif
                        <span>کد اقامتگاه: <strong class="text-gray-700 dark:text-gray-300 font-sans">{{ $property->code }}</strong></span>
                        @if(!empty($property->province) || !empty($property->city))
                            <span class="text-gray-300 dark:text-gray-700">•</span>
                            <span class="flex items-center gap-1 text-gray-700 dark:text-gray-300">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span>{{ implode('، ', array_filter([$property->province, $property->city])) }}</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- ۳. نوار خلاصه میزبان (اطلاعات واقعی میزبان) --}}
                @if($canShowHostBox && $host)
                    <div class="flex items-center justify-between gap-4 py-2 border-b border-gray-100 dark:border-zinc-800 pb-5">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($canShowHostAvatar && $host->avatar)
                                <img src="{{ asset('storage/' . $host->avatar) }}" alt="{{ $host->display_name }}" class="w-12 h-12 rounded-full object-cover border border-gray-200 dark:border-zinc-700 shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 font-black text-sm flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-800">
                                    {{ mb_substr($host->display_name ?: 'م', 0, 1) }}
                                </div>
                            @endif
                            <div class="truncate space-y-0.5">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                    میزبان: {{ $host->display_name ?: ($host->user?->name ?: 'میزبان اقامتگاه') }}
                                </h3>
                                @php
                                    $hostMeta = [];
                                    if (!empty($host->created_at)) {
                                        $hostMeta[] = 'عضویت از سال ' . verta($host->created_at)->format('Y');
                                    }
                                    if (!empty($host->response_rate)) {
                                        $hostMeta[] = 'نرخ پاسخگویی ' . $host->response_rate . '٪';
                                    }
                                    if (!empty($host->is_superhost)) {
                                        $hostMeta[] = 'میزبان برگزیده';
                                    }
                                @endphp
                                @if(!empty($hostMeta))
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                        {{ implode(' • ', $hostMeta) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if($canShowDirectCall && $hostPhone)
                            <a href="tel:{{ $hostPhone }}" class="px-3.5 py-2 rounded-xl bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 dark:hover:bg-zinc-700 text-xs font-bold text-gray-800 dark:text-gray-200 transition shrink-0 border border-gray-200 dark:border-zinc-700">
                                تماس مستقیم
                            </a>
                        @endif
                    </div>
                @endif

                {{-- ۴. مشخصه‌های اصلی اقامتگاه (تنها کارت‌های دارای دیتای واقعی نمایش داده می‌شوند) --}}
                @php
                    $mainSpecs = [];
                    if (!empty($config?->base_guests) || !empty($config?->max_guests) || !empty($property->max_guests)) {
                        $capText = (!empty($config?->base_guests) && !empty($config?->max_guests) && $config->base_guests != $config->max_guests)
                            ? "{$config->base_guests} تا {$config->max_guests} نفر"
                            : "تا " . ($config?->max_guests ?? $property->max_guests ?? $config?->base_guests) . " نفر";
                        $mainSpecs[] = [
                            'label' => 'ظرفیت',
                            'value' => $capText,
                            'iconBg' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400',
                            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />'
                        ];
                    }
                    if (!empty($bedroomsCount) && $bedroomsCount > 0) {
                        $mainSpecs[] = [
                            'label' => 'تعداد اتاق',
                            'value' => "{$bedroomsCount} اتاق خواب",
                            'iconBg' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400',
                            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />'
                        ];
                    }
                    if (!empty($property->property_type_name) || !empty($property->propertyType?->name)) {
                        $mainSpecs[] = [
                            'label' => 'نوع اقامتگاه',
                            'value' => $property->property_type_name ?? $property->propertyType?->name,
                            'iconBg' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400',
                            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />'
                        ];
                    }
                    if (!empty($areaValue)) {
                        $mainSpecs[] = [
                            'label' => 'متراژ زیربنا',
                            'value' => $areaValue,
                            'iconBg' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400',
                            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />'
                        ];
                    }
                @endphp

                @if(!empty($mainSpecs))
                    <div class="grid grid-cols-2 sm:grid-cols-{{ min(count($mainSpecs), 4) }} gap-2.5 py-1">
                        @foreach($mainSpecs as $spec)
                            <div class="p-3.5 rounded-2xl bg-gray-50/70 dark:bg-zinc-900/60 border border-gray-200/60 dark:border-zinc-800/60 text-center space-y-1">
                                <div class="w-7 h-7 mx-auto rounded-xl {{ $spec['iconBg'] }} flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $spec['svg'] !!}</svg>
                                </div>
                                <span class="text-[11px] text-gray-400 block font-medium">{{ $spec['label'] }}</span>
                                <strong class="text-xs sm:text-sm font-black text-gray-900 dark:text-white block">{{ $spec['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ۵. بنر رزرو آنی با رنگ بنفش/ایندیگو جذاب (دقیقاً مثل نمونه ضمیمه) --}}
                @if($isInstant)
                    <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-indigo-500/5 dark:from-indigo-950/40 dark:via-purple-950/40 dark:to-zinc-900 border border-indigo-200/70 dark:border-indigo-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-indigo-600/30">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-black text-indigo-950 dark:text-indigo-200">
                                    رزرو آنی و قطعی (بدون نیاز به تأیید میزبان)
                                </h4>
                                <p class="text-[11px] text-indigo-800/80 dark:text-indigo-300/80 mt-0.5">
                                    با انتخاب تاریخ در تقویم، اقامت شما بلافاصله در سامانه ثبت و قطعی خواهد شد.
                                </p>
                            </div>
                        </div>

                        <button type="button" 
                                @click="document.getElementById('calendar-section')?.scrollIntoView({ behavior: 'smooth' })"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-sm shrink-0 self-start sm:self-center">
                            انتخاب تاریخ و رزرو
                        </button>
                    </div>
                @endif

                {{-- ۶. مشخصات کلی اقامتگاه (کاملاً داینامیک و تنها در صورت وجود اطلاعات) --}}
                @php
                    $generalSpecs = [];
                    if (!empty($property->property_type_name) || !empty($areaValue)) {
                        $parts = [];
                        if (!empty($property->property_type_name)) $parts[] = $property->property_type_name;
                        if (!empty($areaValue)) $parts[] = 'با متراژ ' . $areaValue . ' زیربنا';
                        if (!empty($property->city)) $parts[] = 'در محدوده ' . $property->city;
                        $generalSpecs[] = [
                            'icon' => '🏡',
                            'title' => 'درباره فضا و ساختمان',
                            'desc' => implode(' ', $parts)
                        ];
                    }
                    if (!empty($config?->base_guests) || !empty($config?->max_guests)) {
                        $guestDesc = 'ظرفیت استاندارد ' . ($config->base_guests ?? $config->max_guests) . ' نفر';
                        if (!empty($config->max_guests) && $config->max_guests > ($config->base_guests ?? 1)) {
                            $guestDesc .= ' (امکان پذیرش تا ' . $config->max_guests . ' نفر مهمان با هزینه نفر اضافه)';
                        }
                        $generalSpecs[] = [
                            'icon' => '👥',
                            'title' => 'ظرفیت مسافران',
                            'desc' => $guestDesc
                        ];
                    }
                    if (!empty($checkInTimeFormatted) || !empty($checkOutTimeFormatted)) {
                        $timeDesc = [];
                        if (!empty($checkInTimeFormatted)) {
                            $timeDesc[] = 'تحویل اقامتگاه از ساعت ' . $checkInTimeFormatted;
                        }
                        if (!empty($checkOutTimeFormatted)) {
                            $timeDesc[] = 'تخلیه اقامتگاه تا ساعت ' . $checkOutTimeFormatted;
                        }
                        $generalSpecs[] = [
                            'icon' => '🕒',
                            'title' => 'ساعت ورود و خروج',
                            'desc' => implode(' • ', $timeDesc)
                        ];
                    }
                    if (!empty($bedroomsCount) || !empty($bathroomsCount) || !empty($doubleBeds) || !empty($singleBeds)) {
                        $roomDesc = [];
                        if (!empty($bedroomsCount)) $roomDesc[] = "دارای {$bedroomsCount} اتاق خواب";
                        if (!empty($doubleBeds)) $roomDesc[] = "{$doubleBeds} تخت دو نفره";
                        if (!empty($singleBeds)) $roomDesc[] = "{$singleBeds} تخت یک نفره";
                        if (!empty($bathroomsCount)) $roomDesc[] = "{$bathroomsCount} سرویس بهداشتی و حمام";
                        $generalSpecs[] = [
                            'icon' => '🛏️',
                            'title' => 'فضای خواب و سرویس بهداشتی',
                            'desc' => implode('، ', $roomDesc)
                        ];
                    }
                @endphp

                @if(!empty($generalSpecs))
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-zinc-800">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                            مشخصات کلی اقامتگاه
                        </h3>

                        <div class="space-y-3.5 text-xs">
                            @foreach($generalSpecs as $gSpec)
                                <div class="flex items-start gap-3">
                                    <span class="w-5 h-5 text-gray-400 mt-0.5 shrink-0 text-base select-none">{{ $gSpec['icon'] }}</span>
                                    <div>
                                        <strong class="text-gray-900 dark:text-white block font-bold">{{ $gSpec['title'] }}</strong>
                                        <span class="text-gray-500 dark:text-gray-400 mt-0.5 block leading-relaxed">
                                            {{ $gSpec['desc'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ۷. فضای خواب و اتاق‌ها (فقط در صورت وجود اطلاعات واقعی اتاق یا تخت‌ها نمایش داده می‌شود) --}}
                @php
                    $roomCards = [];
                    if (!empty($bedroomsCount) && $bedroomsCount > 0) {
                        for ($r = 1; $r <= min($bedroomsCount, 6); $r++) {
                            $rDesc = [];
                            if ($r === 1 && !empty($doubleBeds)) {
                                $rDesc[] = $doubleBeds . ' تخت ۲ نفره';
                            } elseif ($r > 1 && !empty($singleBeds)) {
                                $rDesc[] = ceil($singleBeds / max(1, $bedroomsCount - 1)) . ' تخت ۱ نفره';
                            } else {
                                $rDesc[] = 'سرویس خواب استاندارد و تمیز';
                            }
                            $roomCards[] = [
                                'title' => 'اتاق خواب ' . $r,
                                'desc' => implode(' • ', $rDesc),
                                'icon' => '🚪'
                            ];
                        }
                    } elseif (!empty($doubleBeds) || !empty($singleBeds)) {
                        if (!empty($doubleBeds)) {
                            $roomCards[] = [
                                'title' => 'تخت دو نفره',
                                'desc' => $doubleBeds . ' عدد تخت ۲ نفره استاندارد',
                                'icon' => '🛏️'
                            ];
                        }
                        if (!empty($singleBeds)) {
                            $roomCards[] = [
                                'title' => 'تخت یک نفره',
                                'desc' => $singleBeds . ' عدد تخت ۱ نفره استاندارد',
                                'icon' => '🛋️'
                            ];
                        }
                    }
                @endphp

                @if(!empty($roomCards))
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-zinc-800">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                            فضای خواب و تخت‌ها
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-{{ min(count($roomCards), 3) }} gap-3">
                            @foreach($roomCards as $rc)
                                <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-900/60 border border-gray-200/60 dark:border-zinc-800/60 space-y-2">
                                    <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                        <span class="text-base select-none">{{ $rc['icon'] }}</span>
                                        <span class="text-xs font-black text-gray-900 dark:text-white">{{ $rc['title'] }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                        {{ $rc['desc'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ۸. درباره این اقامتگاه (تنها در صورت وجود توضیحات ثبت شده نمایش داده می‌شود) --}}
                @if(!empty(trim($property->description ?? '')))
                    <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-zinc-800" x-data="{ expanded: false }">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                            درباره این اقامتگاه
                        </h3>
                        <div class="text-xs sm:text-sm leading-relaxed text-gray-600 dark:text-gray-300 relative transition-all"
                             :class="expanded ? '' : 'line-clamp-4'">
                            {!! nl2br(e($property->description)) !!}
                        </div>
                        @if(mb_strlen($property->description) > 200)
                            <button type="button" 
                                    @click="expanded = !expanded" 
                                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1 pt-1">
                                <span x-text="expanded ? '− بستن توضیحات' : '+ مشاهده توضیحات کامل'"></span>
                            </button>
                        @endif
                    </div>
                @endif

                {{-- ۹. امکانات رفاهی و اقامتی (تنها در صورت ثبت امکانات برای اقامتگاه نمایش داده می‌شود) --}}
                @if($features->isNotEmpty())
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-zinc-800" x-data="{ showAmenitiesModal: false }">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                                امکانات اقامتگاه
                            </h3>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($features->take(9) as $feat)
                                <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-gray-50/70 dark:bg-zinc-900/60 border border-gray-200/60 dark:border-zinc-800/60 text-xs font-bold text-gray-800 dark:text-gray-200">
                                    <span class="text-base select-none">{{ $feat->attribute->icon ?: '✨' }}</span>
                                    <span class="truncate">{{ $feat->attribute->name }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if($features->count() > 9)
                            <div class="pt-2">
                                <button type="button" 
                                        @click="showAmenitiesModal = true" 
                                        class="w-full py-3 rounded-2xl border border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800 text-xs font-bold text-gray-900 dark:text-white transition shadow-2xs">
                                    مشاهده همه امکانات اقامتگاه ({{ $features->count() }} مورد)
                                </button>
                            </div>
                        @endif

                        {{-- مودال همه امکانات --}}
                        <div x-show="showAmenitiesModal" 
                             x-transition 
                             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                            <div @click.away="showAmenitiesModal = false" 
                                 class="w-full max-w-lg bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-2xl border border-gray-200 dark:border-zinc-800 max-h-[85vh] overflow-y-auto space-y-4">
                                <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
                                    <h4 class="text-base font-black text-gray-900 dark:text-white">امکانات کامل اقامتگاه ({{ $features->count() }} مورد)</h4>
                                    <button type="button" @click="showAmenitiesModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl font-bold">✕</button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                                    @foreach($features as $feat)
                                        <div class="flex items-center gap-2.5 p-3 rounded-xl bg-gray-50 dark:bg-zinc-800 text-xs font-bold text-gray-800 dark:text-gray-200">
                                            <span class="text-base select-none">{{ $feat->attribute->icon ?: '✨' }}</span>
                                            <span>{{ $feat->attribute->name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ۱۰. تقویم رزرو و قیمت‌ها (شبیه نمونه ضمیمه‌شده) --}}
                <div id="calendar-section" class="p-5 sm:p-7 rounded-3xl bg-white dark:bg-zinc-900 border border-gray-200/80 dark:border-zinc-800 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
                        <div>
                            <h3 class="text-base sm:text-xl font-black text-gray-900 dark:text-white">
                                تقویم رزرو و قیمت‌ها
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                تاریخ ورود و خروج خود را با لمس روزها انتخاب نمایید.
                            </p>
                        </div>
                        <button type="button" 
                                @click="clearDates()" 
                                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 transition">
                            پاک کردن تاریخ‌ها
                        </button>
                    </div>

                    @if(isset($calendarData['months']) && count($calendarData['months']) > 0)
                        {{-- الف) نسخه موبایل: نمایش تک‌ماه با سوئیچر ورق زدن ماه (دقیقاً مشابه نمونه تصویر ارسالی) --}}
                        <div class="block md:hidden">
                            {{-- نوار ناوبری ماه در موبایل --}}
                            <div class="flex items-center justify-between mb-5">
                                {{-- دکمه ماه قبل (سمت راست در RTL) --}}
                                <button type="button" 
                                        @click="prevMonthMobile()" 
                                        :disabled="mobileMonthIndex <= 0"
                                        :class="mobileMonthIndex <= 0 ? 'opacity-25 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-800 dark:text-gray-200'"
                                        class="w-10 h-10 rounded-full flex items-center justify-center border border-gray-200 dark:border-zinc-700 transition font-bold"
                                        title="ماه قبل">
                                    <svg class="w-5 h-5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                                </button>

                                {{-- کپسول عنوان ماه --}}
                                @foreach($calendarData['months'] as $mIdx => $m)
                                    <div x-show="mobileMonthIndex === {{ $mIdx }}" 
                                         class="px-7 py-2 rounded-full bg-gray-100 dark:bg-zinc-800 text-sm font-black text-gray-900 dark:text-white font-sans shadow-2xs">
                                        {{ $m['month_title'] ?? (($m['month_name'] ?? '') . ' ' . ($m['year'] ?? '')) }}
                                    </div>
                                @endforeach

                                {{-- دکمه ماه بعد (سمت چپ در RTL) --}}
                                <button type="button" 
                                        @click="nextMonthMobile()" 
                                        :disabled="mobileMonthIndex >= {{ count($calendarData['months']) - 1 }}"
                                        :class="mobileMonthIndex >= {{ count($calendarData['months']) - 1 }} ? 'opacity-25 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-800 dark:text-gray-200'"
                                        class="w-10 h-10 rounded-full flex items-center justify-center border border-gray-200 dark:border-zinc-700 transition font-bold"
                                        title="ماه بعد">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                                </button>
                            </div>

                            {{-- شبکه‌های ماه‌ها در موبایل --}}
                            @foreach($calendarData['months'] as $mIdx => $m)
                                <div x-show="mobileMonthIndex === {{ $mIdx }}" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                                    {{-- هدر روزهای هفته --}}
                                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-gray-400 dark:text-gray-500 py-2.5 border-b border-gray-200 dark:border-zinc-800 mb-4">
                                        <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span class="text-rose-500">ج</span>
                                    </div>

                                    {{-- سلول‌های روزهای ماه --}}
                                    <div class="grid grid-cols-7 gap-1.5 text-center text-xs">
                                        @for($e = 0; $e < ($m['empty_cells_start'] ?? ($m['first_day_of_week'] ?? 0)); $e++)
                                            <div class="h-16"></div>
                                        @endfor

                                        @foreach($m['days'] as $day)
                                            @php
                                                $isPast = !empty($day['is_past']);
                                                $isBlocked = !empty($day['is_blocked']);
                                                $iso = $day['carbon_date'];
                                                $dayNum = $day['day_num'] ?? ($day['day'] ?? '');
                                                $priceVal = !empty($day['price']) ? (float) $day['price'] : 0;
                                                $priceThousands = $priceVal > 0 ? number_format(round($priceVal / 1000)) : '';
                                                $isHoliday = !empty($day['is_holiday']);
                                                $isFriday = ($day['day_of_week'] ?? 0) == 6;
                                                $isHolidayOrFriday = $isHoliday || $isFriday;
                                                $isWeekend = !empty($day['is_weekend']);
                                            @endphp

                                            @if($isPast)
                                                <div class="h-16 flex items-center justify-center text-sm font-sans {{ $isHolidayOrFriday ? 'text-rose-300 dark:text-rose-900/60' : 'text-gray-300 dark:text-zinc-600' }}">
                                                    {{ $dayNum }}
                                                </div>
                                            @else
                                                <button type="button" 
                                                        @if(!$isBlocked) @click="selectCalendarDate('{{ $iso }}')" @endif
                                                        :class="getDayClasses('{{ $iso }}', false, {{ $isBlocked ? 'true' : 'false' }})"
                                                        class="relative rounded-2xl border border-dashed border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 hover:border-emerald-500 transition-all flex flex-col items-center justify-center py-1 px-0.5 min-h-[64px] font-sans shadow-2xs {{ $isBlocked ? 'opacity-35 cursor-not-allowed bg-gray-50 dark:bg-zinc-800/40' : 'cursor-pointer' }}">
                                                    
                                                    @if(!$isBlocked)
                                                        <span :class="(checkIn === '{{ $iso }}' || checkOut === '{{ $iso }}') ? 'hidden' : ''" 
                                                              class="absolute top-0 right-0 w-2.5 h-2.5 bg-amber-400 rounded-bl-md"></span>
                                                    @endif

                                                    <span class="text-xs sm:text-sm font-black font-sans {{ $isHolidayOrFriday ? 'text-rose-600 dark:text-rose-400' : 'text-gray-800 dark:text-gray-100' }}"
                                                          :class="(checkIn === '{{ $iso }}' || checkOut === '{{ $iso }}') ? '!text-white' : ''">
                                                        {{ $dayNum }}
                                                    </span>

                                                    @if($isBlocked)
                                                        <span class="text-[9px] text-gray-400 line-through mt-0.5">مسدود</span>
                                                    @elseif($priceVal > 0)
                                                        <span class="text-[11px] sm:text-xs font-black font-sans mt-0.5 tracking-tight text-gray-900 dark:text-white"
                                                              :class="(checkIn === '{{ $iso }}' || checkOut === '{{ $iso }}') ? '!text-white' : ''">
                                                            {{ $priceThousands }}
                                                        </span>
                                                    @endif
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- ب) نسخه دسکتاپ: نمایش دو ماه در کنار هم با کلیدهای ورق‌زدن (دقیقاً مشابه نمونه تصویر اول) --}}
                        <div class="hidden md:block">
                            <div class="grid grid-cols-2 gap-8">
                                {{-- ستون راست: ماه اول --}}
                                <div class="space-y-3">
                                    @foreach($calendarData['months'] as $mIdx => $m)
                                        @if($mIdx < count($calendarData['months']) - 1)
                                            <div x-show="desktopMonthIndex === {{ $mIdx }}">
                                                <div class="flex items-center justify-between mb-4 px-1">
                                                    <button type="button" 
                                                            @click="prevMonthDesktop()" 
                                                            :disabled="desktopMonthIndex <= 0"
                                                            :class="desktopMonthIndex <= 0 ? 'opacity-20 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-800 dark:text-gray-200'"
                                                            class="w-9 h-9 rounded-full flex items-center justify-center border border-gray-200 dark:border-zinc-700 transition font-bold"
                                                            title="ماه قبل">
                                                        <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                                                    </button>
                                                    <span class="text-base font-black text-gray-900 dark:text-white font-sans">
                                                        {{ $m['month_title'] ?? (($m['month_name'] ?? '') . ' ' . ($m['year'] ?? '')) }}
                                                    </span>
                                                    <div class="w-9"></div>
                                                </div>

                                                <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-gray-400 dark:text-gray-500 py-2.5 border-b border-gray-200 dark:border-zinc-800 mb-4">
                                                    <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span class="text-rose-500">ج</span>
                                                </div>

                                                <div class="grid grid-cols-7 gap-1.5 lg:gap-2 text-center text-xs">
                                                    @for($e = 0; $e < ($m['empty_cells_start'] ?? ($m['first_day_of_week'] ?? 0)); $e++)
                                                        <div class="h-16 lg:h-18"></div>
                                                    @endfor

                                                    @foreach($m['days'] as $day)
                                                        @php
                                                            $isPast = !empty($day['is_past']);
                                                            $isBlocked = !empty($day['is_blocked']);
                                                            $iso = $day['carbon_date'];
                                                            $dayNum = $day['day_num'] ?? ($day['day'] ?? '');
                                                            $priceVal = !empty($day['price']) ? (float) $day['price'] : 0;
                                                            $priceThousands = $priceVal > 0 ? number_format(round($priceVal / 1000)) : '';
                                                            $isHoliday = !empty($day['is_holiday']);
                                                            $isFriday = ($day['day_of_week'] ?? 0) == 6;
                                                            $isHolidayOrFriday = $isHoliday || $isFriday;
                                                        @endphp

                                                        @if($isPast)
                                                            <div class="h-16 lg:h-18 flex items-center justify-center text-sm font-sans {{ $isHolidayOrFriday ? 'text-rose-300 dark:text-rose-900/60' : 'text-gray-300 dark:text-zinc-600' }}">
                                                                {{ $dayNum }}
                                                            </div>
                                                        @else
                                                            <button type="button" 
                                                                    @if(!$isBlocked) @click="selectCalendarDate('{{ $iso }}')" @endif
                                                                    :class="getDayClasses('{{ $iso }}', false, {{ $isBlocked ? 'true' : 'false' }})"
                                                                    class="relative rounded-2xl border border-dashed border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 hover:border-emerald-500 transition-all flex flex-col items-center justify-center py-2 px-1 min-h-[66px] lg:min-h-[72px] font-sans shadow-2xs {{ $isBlocked ? 'opacity-35 cursor-not-allowed bg-gray-50 dark:bg-zinc-800/40' : 'cursor-pointer' }}">
                                                                
                                                                @if(!$isBlocked)
                                                                    <span :class="(checkIn === '{{ $iso }}' || checkOut === '{{ $iso }}') ? 'hidden' : ''" 
                                                                          class="absolute top-0 right-0 w-2.5 h-2.5 bg-amber-400 rounded-bl-md"></span>
                                                                @endif

                                                                <span class="text-xs sm:text-sm font-black font-sans {{ $isHolidayOrFriday ? 'text-rose-600 dark:text-rose-400' : 'text-gray-800 dark:text-gray-100' }}"
                                                                      :class="(checkIn === '{{ $iso }}' || checkOut === '{{ $iso }}') ? '!text-white' : ''">
                                                                    {{ $dayNum }}
                                                                </span>

                                                                @if($isBlocked)
                                                                    <span class="text-[9px] text-gray-400 line-through mt-0.5">مسدود</span>
                                                                @elseif($priceVal > 0)
                                                                    <span class="text-[11px] sm:text-xs font-black font-sans mt-0.5 tracking-tight text-gray-900 dark:text-white"
                                                                          :class="(checkIn === '{{ $iso }}' || checkOut === '{{ $iso }}') ? '!text-white' : ''">
                                                                        {{ $priceThousands }}
                                                                    </span>
                                                                @endif
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- ستون چپ: ماه دوم --}}
                                <div class="space-y-3">
                                    @foreach($calendarData['months'] as $mIdx => $m)
                                        @if($mIdx > 0)
                                            <div x-show="desktopMonthIndex + 1 === {{ $mIdx }}">
                                                <div class="flex items-center justify-between mb-4 px-1">
                                                    <div class="w-9"></div>
                                                    <span class="text-base font-black text-gray-900 dark:text-white font-sans">
                                                        {{ $m['month_title'] ?? (($m['month_name'] ?? '') . ' ' . ($m['year'] ?? '')) }}
                                                    </span>
                                                    <button type="button" 
                                                            @click="nextMonthDesktop()" 
                                                            :disabled="desktopMonthIndex >= {{ count($calendarData['months']) - 2 }}"
                                                            :class="desktopMonthIndex >= {{ count($calendarData['months']) - 2 }} ? 'opacity-20 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-800 dark:text-gray-200'"
                                                            class="w-9 h-9 rounded-full flex items-center justify-center border border-gray-200 dark:border-zinc-700 transition font-bold"
                                                            title="ماه بعد">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                                                    </button>
                                                </div>

                                                <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-gray-400 dark:text-gray-500 py-2.5 border-b border-gray-200 dark:border-zinc-800 mb-4">
                                                    <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span class="text-rose-500">ج</span>
                                                </div>

                                                <div class="grid grid-cols-7 gap-1.5 lg:gap-2 text-center text-xs">
                                                    @for($e = 0; $e < ($m['empty_cells_start'] ?? ($m['first_day_of_week'] ?? 0)); $e++)
                                                        <div class="h-16 lg:h-18"></div>
                                                    @endfor

                                                    @foreach($m['days'] as $day)
                                                        @php
                                                            $isPast = !empty($day['is_past']);
                                                            $isBlocked = !empty($day['is_blocked']);
                                                            $iso = $day['carbon_date'];
                                                            $dayNum = $day['day_num'] ?? ($day['day'] ?? '');
                                                            $priceVal = !empty($day['price']) ? (float) $day['price'] : 0;
                                                            $priceThousands = $priceVal > 0 ? number_format(round($priceVal / 1000)) : '';
                                                            $isHoliday = !empty($day['is_holiday']);
                                                            $isFriday = ($day['day_of_week'] ?? 0) == 6;
                                                            $isHolidayOrFriday = $isHoliday || $isFriday;
                                                        @endphp

                                                        @if($isPast)
                                                            <div class="h-16 lg:h-18 flex items-center justify-center text-sm font-sans {{ $isHolidayOrFriday ? 'text-rose-300 dark:text-rose-900/60' : 'text-gray-300 dark:text-zinc-600' }}">
                                                                {{ $dayNum }}
                                                            </div>
                                                        @else
                                                            <button type="button" 
                                                                    @if(!$isBlocked) @click="selectCalendarDate('{{ $iso }}')" @endif
                                                                    :class="getDayClasses('{{ $iso }}', false, {{ $isBlocked ? 'true' : 'false' }})"
                                                                    class="relative rounded-2xl border border-dashed border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 hover:border-emerald-500 transition-all flex flex-col items-center justify-center py-2 px-1 min-h-[66px] lg:min-h-[72px] font-sans shadow-2xs {{ $isBlocked ? 'opacity-35 cursor-not-allowed bg-gray-50 dark:bg-zinc-800/40' : 'cursor-pointer' }}">
                                                                
                                                                @if(!$isBlocked)
                                                                    <span :class="(checkIn === '{{ $iso }}' || checkOut === '{{ $iso }}') ? 'hidden' : ''" 
                                                                          class="absolute top-0 right-0 w-2.5 h-2.5 bg-amber-400 rounded-bl-md"></span>
                                                                @endif

                                                                <span class="text-xs sm:text-sm font-black font-sans {{ $isHolidayOrFriday ? 'text-rose-600 dark:text-rose-400' : 'text-gray-800 dark:text-gray-100' }}"
                                                                      :class="(checkIn === '{{ $iso }}' || checkOut === '{{ $iso }}') ? '!text-white' : ''">
                                                                    {{ $dayNum }}
                                                                </span>

                                                                @if($isBlocked)
                                                                    <span class="text-[9px] text-gray-400 line-through mt-0.5">مسدود</span>
                                                                @elseif($priceVal > 0)
                                                                    <span class="text-[11px] sm:text-xs font-black font-sans mt-0.5 tracking-tight text-gray-900 dark:text-white"
                                                                          :class="(checkIn === '{{ $iso }}' || checkOut === '{{ $iso }}') ? '!text-white' : ''">
                                                                        {{ $priceThousands }}
                                                                    </span>
                                                                @endif
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- راهنمای رنگ‌های تقویم و واحد پولی --}}
                        <div class="flex items-center justify-between flex-wrap gap-4 pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 font-sans">
                            <div class="flex items-center gap-5 flex-wrap">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-3.5 rounded-lg bg-emerald-600 shadow-2xs"></span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">تاریخ انتخابی شما</span>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-bl-md bg-amber-400"></span>
                                    <span>نرخ آخر هفته و پیک</span>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="text-rose-600 font-black text-sm">۱۵</span>
                                    <span>جمعه و تعطیلات رسمی</span>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-3.5 rounded-lg border border-dashed border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800"></span>
                                    <span>غیرقابل رزرو</span>
                                </span>
                            </div>

                            <span class="text-[11px] text-gray-400 font-medium">
                                * مبالغ داخل تقویم بر حسب هزار تومان است.
                            </span>
                        </div>
                    @endif
                </div>

                {{-- ۱۱. محدوده مکانی و حریم خصوصی نقشه --}}
                @if($canShowMap && $property->latitude && $property->longitude)
                    <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                                موقعیت و دسترسی
                            </h3>
                            <span class="text-xs text-gray-400">
                                {{ $isMapExact ? 'موقعیت دقیق اقامتگاه' : 'محدوده تقریبی اقامتگاه' }}
                            </span>
                        </div>
                        <div id="map" class="w-full h-72 rounded-2xl overflow-hidden border border-gray-200 dark:border-zinc-800 z-10 shadow-xs"></div>
                        <p class="text-[11px] text-gray-400 leading-relaxed">
                            جهت حفظ امنیت و حریم خصوصی میزبان، آدرس و لوکیشن دقیق پس از نهایی شدن رزرو در پیش‌فاکتور و پیامک ارسال می‌گردد.
                        </p>
                    </div>
                @endif

                {{-- ۱۲. قوانین و مقررات پذیرش و کنسلی --}}
                <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-zinc-800">
                    <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                        قوانین و مقررات اقامتگاه
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                        <div class="p-3 rounded-xl bg-gray-50/70 dark:bg-zinc-900/60 border border-gray-200/60 dark:border-zinc-800/60 flex items-center justify-between">
                            <span class="text-gray-500">استعمال دخانیات</span>
                            <span class="font-bold {{ $smokingAllowed ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $smokingAllowed ? 'مجاز' : 'ممنوع در فضای بسته' }}
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50/70 dark:bg-zinc-900/60 border border-gray-200/60 dark:border-zinc-800/60 flex items-center justify-between">
                            <span class="text-gray-500">ورود حیوانات خانگی</span>
                            <span class="font-bold {{ $petsAllowed ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $petsAllowed ? 'مجاز' : 'ممنوع' }}
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50/70 dark:bg-zinc-900/60 border border-gray-200/60 dark:border-zinc-800/60 flex items-center justify-between">
                            <span class="text-gray-500">برگزاری جشن و مهمانی</span>
                            <span class="font-bold {{ $partiesAllowed ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $partiesAllowed ? 'مجاز با هماهنگی' : 'ممنوع' }}
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50/70 dark:bg-zinc-900/60 border border-gray-200/60 dark:border-zinc-800/60 flex items-center justify-between">
                            <span class="text-gray-500">ارائه مدارک شناسایی</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $idRequired ? 'الزامی (کارت ملی هوشمند)' : 'طبق ضوابط میزبان' }}
                            </span>
                        </div>
                    </div>

                    {{-- مقررات کنسلی شفاف --}}
                    <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 text-xs space-y-1">
                        <strong class="text-amber-900 dark:text-amber-200 block font-bold">مقررات لغو رزرو (شفاف و منصفانه):</strong>
                        <p class="text-amber-800/80 dark:text-amber-300/80 leading-relaxed">
                            تا ۷۲ ساعت قبل از تاریخ ورود، کل وجه پرداختی با کسر کارمزد بانکی بازگردانده می‌شود. کمتر از ۴۸ ساعت تا موعد ورود، هزینه شب اول کسر و مابقی مسترد می‌گردد.
                        </p>
                    </div>
                </div>

                {{-- ۱۳. اقامتگاه‌های مشابه در لفور --}}
                @if($similarList->isNotEmpty())
                    <div class="space-y-4 pt-6 border-t border-gray-100 dark:border-zinc-800">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                            اقامتگاه‌های مشابه در لفور
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($similarList as $sim)
                                @php
                                    $simPrice = $sim->rentalConfig?->price_per_night ?? $sim->rent_price ?? $sim->price ?? null;
                                    $simCover = $sim->cover_image ? (str_starts_with($sim->cover_image, 'http') ? $sim->cover_image : asset('storage/' . $sim->cover_image)) : asset('images/property-placeholder.svg');
                                    $simGuests = $sim->rentalConfig?->max_guests ?? $sim->max_guests ?? null;
                                @endphp
                                <a href="{{ route('properties.show', $sim->slug ?: $sim->id) }}" class="group block p-3 rounded-2xl border border-gray-200/80 dark:border-zinc-800 hover:border-emerald-500 transition-all bg-white dark:bg-zinc-900 shadow-2xs">
                                    <div class="aspect-[16/10] rounded-xl overflow-hidden mb-2.5">
                                        <img src="{{ $simCover }}" alt="{{ $sim->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    </div>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-emerald-600 transition">
                                        {{ $sim->title }}
                                    </h4>
                                    <div class="flex items-center justify-between text-[11px] text-gray-500 mt-1">
                                        <span>{{ $simGuests ? 'تا ' . $simGuests . ' نفر' : ($sim->city ?? '') }}</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 font-sans">
                                            @if($simPrice && $simPrice > 0)
                                                {{ number_format($simPrice) }} {{ $currencyLabel }}
                                            @else
                                                استعلام قیمت
                                            @endif
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- ستون چپ: ویجت چسبان استعلام نرخ و رزرو (دسکتاپ) --}}
            <div class="hidden lg:block lg:col-span-5 xl:col-span-4 sticky top-24">
                <div class="border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-xl shadow-gray-200/50 dark:shadow-none space-y-5">
                    
                    {{-- هدر قیمت و تخفیف --}}
                    <div class="pb-4 border-b border-gray-100 dark:border-zinc-800">
                        <div class="flex items-baseline justify-between">
                            <div>
                                @if($hasDiscount)
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs text-gray-400 line-through">{{ number_format($baseNightPrice) }}</span>
                                        <span class="px-2 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-[11px] font-bold border border-rose-200/60 dark:border-rose-800/40">
                                            {{ $discountBadge }}
                                        </span>
                                    </div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                                            {{ number_format($payablePrice) }}
                                        </span>
                                        <span class="text-xs font-bold text-gray-400 mr-1">{{ $currencyLabel }} / هر شب</span>
                                    </div>
                                @else
                                    <span class="text-2xl font-black text-gray-900 dark:text-white">
                                        {{ number_format($baseNightPrice) }}
                                    </span>
                                    <span class="text-xs font-bold text-gray-400 mr-1">{{ $currencyLabel }} / هر شب</span>
                                @endif
                            </div>

                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold">
                                تضمین بهترین نرخ
                            </span>
                        </div>
                    </div>

                    {{-- انتخابگر تاریخ ورود و خروج --}}
                    <div class="grid grid-cols-2 gap-2 p-3 bg-gray-50 dark:bg-zinc-800/70 rounded-2xl border border-gray-200/60 dark:border-zinc-700/60 text-xs">
                        <button type="button" 
                                @click="document.getElementById('calendar-section')?.scrollIntoView({ behavior: 'smooth' })" 
                                class="text-right space-y-1 p-1 hover:opacity-80 transition cursor-pointer">
                            <span class="text-[10px] text-gray-400 block font-medium">تاریخ ورود</span>
                            <span class="font-bold text-gray-900 dark:text-white block truncate text-xs font-sans" x-text="formatDateDisplay(checkIn)"></span>
                        </button>
                        <button type="button" 
                                @click="document.getElementById('calendar-section')?.scrollIntoView({ behavior: 'smooth' })" 
                                class="text-right space-y-1 p-1 border-r border-gray-200 dark:border-zinc-700 pr-3 hover:opacity-80 transition cursor-pointer">
                            <span class="text-[10px] text-gray-400 block font-medium">تاریخ خروج</span>
                            <span class="font-bold text-gray-900 dark:text-white block truncate text-xs font-sans" x-text="formatDateDisplay(checkOut)"></span>
                        </button>
                    </div>

                    {{-- انتخابگر تعداد مهمانان --}}
                    <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-zinc-800/70 rounded-2xl border border-gray-200/60 dark:border-zinc-700/60 text-xs">
                        <div>
                            <span class="text-[10px] text-gray-400 block font-medium">تعداد مهمانان</span>
                            <span class="font-bold text-gray-900 dark:text-white text-xs mt-0.5 block" x-text="formatNumber(guests) + ' نفر (حداکثر ' + formatNumber(maxGuests) + ' نفر)'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="decreaseGuests()" 
                                    :disabled="guests <= 1"
                                    :class="guests <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-700'"
                                    class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 font-bold flex items-center justify-center transition shadow-2xs">
                                −
                            </button>
                            <span class="w-6 text-center font-bold text-gray-900 dark:text-white" x-text="formatNumber(guests)"></span>
                            <button type="button" 
                                    @click="increaseGuests()" 
                                    :disabled="guests >= maxGuests"
                                    :class="guests >= maxGuests ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-700'"
                                    class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 font-bold flex items-center justify-center transition shadow-2xs">
                                +
                            </button>
                        </div>
                    </div>

                    {{-- ریزمحاسبات فاکتور پس از انتخاب تاریخ --}}
                    <div x-show="nightsCount > 0" x-transition class="space-y-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs">
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                            <div>
                                <span x-text="'مجموع هزینه ' + formatNumber(nightsCount) + ' شب اقامت:'"></span>
                                <span class="block text-[11px] text-gray-400 dark:text-gray-500 font-sans mt-0.5" x-text="formatDateDisplay(checkIn, 'short') + ' الی ' + formatDateDisplay(checkOut, 'short')"></span>
                            </div>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="formatNumber(originalNightsTotal) + ' ' + currencyLabel"></span>
                        </div>

                        <div x-show="totalDiscount > 0" class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                            <span>تخفیف لحظه آخری اعمال‌شده:</span>
                            <span x-text="'- ' + formatNumber(totalDiscount) + ' ' + currencyLabel"></span>
                        </div>

                        <div x-show="extraGuestsFeeTotal > 0" class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                            <span>هزینه نفرات اضافه:</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="formatNumber(extraGuestsFeeTotal) + ' ' + currencyLabel"></span>
                        </div>

                        <div x-show="cleaningFee > 0" class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                            <span>هزینه نظافت اقامتگاه:</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="formatNumber(cleaningFee) + ' ' + currencyLabel"></span>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-zinc-800 text-sm font-black text-gray-900 dark:text-white">
                            <span>مبلغ نهایی قابل پرداخت:</span>
                            <span class="text-base text-emerald-600 dark:text-emerald-400" x-text="formatNumber(grandTotal) + ' ' + currencyLabel"></span>
                        </div>
                    </div>

                    {{-- دکمه ثبت درخواست و نهایی‌سازی رزرو --}}
                    <div class="space-y-3 pt-2">
                        <button type="button" 
                                @click="goToBooking()" 
                                class="w-full py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs sm:text-sm transition-all shadow-lg shadow-emerald-600/25 flex items-center justify-center gap-2 active:scale-[0.99]">
                            <span x-text="nightsCount > 0 ? 'ادامه و صدور پیش‌فاکتور (' + formatNumber(nightsCount) + ' شب)' : 'ثبت درخواست رزرو اقامتگاه'"></span>
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>

                        <p class="text-[11px] text-gray-400 text-center font-medium">
                            پرداخت وجه پس از صدور پیش‌فاکتور و تایید نهایی انجام می‌شود.
                        </p>
                    </div>

                    {{-- تضمین‌های مستر ویلا لفور --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 space-y-2 text-[11px] text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span>ضمانت تطابق ۱۰۰٪ اقامتگاه با تصاویر و مشخصات</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span>پشتیبانی شبانه‌روزی و پیگیری از لحظه رزرو تا خروج</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- نوار شناور پایین صفحه برای موبایل (Mobile Sticky Bar) --}}
        <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-t border-gray-200 dark:border-zinc-800 p-3.5 z-40 pb-safe shadow-2xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] text-gray-400 block font-medium">مبلغ هر شب</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-sans" x-text="nightsCount > 0 ? formatNumber(grandTotal) + ' ' + currencyLabel : '{{ number_format($payablePrice) }} {{ $currencyLabel }}'">
                            {{ number_format($payablePrice) }} {{ $currencyLabel }}
                        </span>
                    </div>
                </div>

                <button type="button" 
                        @click="goToBooking()" 
                        class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-md shadow-emerald-600/20 flex items-center gap-1.5 shrink-0">
                    <span x-text="nightsCount > 0 ? 'صدور پیش‌فاکتور' : 'انتخاب تاریخ و رزرو'"></span>
                    <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </div>

    </div>

    {{-- مودال لایت‌باکس نمایش گالری تصاویر --}}
    <div id="lightbox" class="hidden fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex flex-col items-center justify-between p-4 opacity-0 transition-opacity duration-300">
        <div class="w-full flex items-center justify-between px-4 py-2 z-10">
            <span id="lightbox-counter" class="text-white text-xs font-bold bg-white/10 px-3 py-1.5 rounded-full font-sans"></span>
            <button type="button" onclick="closeLightbox()" class="text-white text-lg font-bold w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">✕</button>
        </div>

        <div class="relative w-full max-w-5xl flex items-center justify-center flex-1">
            {{-- دکمه بعدی (سمت راست با فلش راست) --}}
            <button type="button" 
                    onclick="nextImage()" 
                    class="absolute right-4 text-white w-12 h-12 rounded-full bg-black/50 hover:bg-black/75 flex items-center justify-center transition text-xl font-bold z-10" 
                    title="تصویر بعدی" 
                    aria-label="تصویر بعدی">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            {{-- دکمه قبلی (سمت چپ با فلش چپ) --}}
            <button type="button" 
                    onclick="prevImage()" 
                    class="absolute left-4 text-white w-12 h-12 rounded-full bg-black/50 hover:bg-black/75 flex items-center justify-center transition text-xl font-bold z-10" 
                    title="تصویر قبلی" 
                    aria-label="تصویر قبلی">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <img id="lightbox-image" src="" alt="{{ $property->title }}" class="max-h-[75vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl transition-all duration-200">
        </div>

        {{-- نوار بندانگشتی تصاویر --}}
        <div class="w-full max-w-4xl overflow-x-auto scrollbar-hide py-3 flex items-center gap-2 justify-center">
            @foreach($allGalleryUrls as $idx => $url)
                <button type="button" onclick="openLightbox({{ $idx }})" class="w-14 h-14 rounded-xl overflow-hidden border-2 border-transparent hover:border-emerald-500 shrink-0 transition-all opacity-70 hover:opacity-100">
                    <img src="{{ $url }}" alt="بندانگشتی {{ $idx + 1 }}" class="w-full h-full object-cover">
                </button>
            @endforeach
        </div>
    </div>

    {{-- موتور جاوااسکریپت تعاملی صفحه اقامتگاه --}}
    <script>
        function rentalBookingApp(flatDates, config, currencyLabel, propertyTitle, propertyCode, clientPayload, checkoutBaseUrl, hasDiscountInitial, discountType, discountValue) {
            return {
                flatDates: flatDates || {},
                config: config || {},
                currencyLabel: currencyLabel,
                propertyTitle: propertyTitle,
                propertyCode: propertyCode,
                client: clientPayload,
                checkoutBaseUrl: checkoutBaseUrl,

                checkIn: '',
                checkOut: '',
                guests: Number(config?.base_guests || 1),
                baseGuests: Number(config?.base_guests || 1),
                maxGuests: Number(config?.max_guests || config?.base_guests || 1),
                basePrice: Number(config?.price_per_night || {{ (float)$baseNightPrice }}),
                extraGuestFee: Number(config?.extra_guest_fee || 0),
                cleaningFee: Number(config?.cleaning_fee || 0),
                minStayNights: Number(config?.min_stay_nights || 1),

                hasDiscount: hasDiscountInitial,
                discountType: discountType || 'percent',
                discountValue: Number(discountValue || 0),

                mobileMonthIndex: 0,
                desktopMonthIndex: 0,
                totalMonthsCount: {{ count($calendarData['months'] ?? []) }},

                prevMonthMobile() {
                    if (this.mobileMonthIndex > 0) {
                        this.mobileMonthIndex--;
                    }
                },
                nextMonthMobile() {
                    if (this.mobileMonthIndex < this.totalMonthsCount - 1) {
                        this.mobileMonthIndex++;
                    }
                },
                prevMonthDesktop() {
                    if (this.desktopMonthIndex > 0) {
                        this.desktopMonthIndex--;
                    }
                },
                nextMonthDesktop() {
                    if (this.desktopMonthIndex < this.totalMonthsCount - 2) {
                        this.desktopMonthIndex++;
                    }
                },

                nightsCount: 0,
                nightsTotal: 0,
                originalNightsTotal: 0,
                totalDiscount: 0,
                extraGuestsFeeTotal: 0,
                grandTotal: 0,
                hasBlockedDays: false,

                normalizeDate(d) {
                    return d ? d.trim() : '';
                },

                getDayClasses(dateStr, isPast, isBlocked) {
                    if (isPast || isBlocked) return '';
                    let d = this.normalizeDate(dateStr);
                    let cIn = this.normalizeDate(this.checkIn);
                    let cOut = this.normalizeDate(this.checkOut);

                    if (d === cIn) {
                        return '!bg-emerald-600 !border-emerald-600 !text-white font-black shadow-md z-10 ' + (cOut ? '!rounded-r-2xl !rounded-l-none' : '!rounded-2xl');
                    }
                    if (d === cOut) {
                        return '!bg-emerald-600 !border-emerald-600 !text-white font-black shadow-md z-10 !rounded-l-2xl !rounded-r-none';
                    }
                    if (cIn && cOut && d > cIn && d < cOut) {
                        return '!bg-emerald-50 dark:!bg-emerald-950/40 !border-y !border-x-0 !border-emerald-200 dark:!border-emerald-800/60 !text-emerald-700 dark:!text-emerald-300 font-bold !rounded-none';
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
                    this.originalNightsTotal = 0;
                    this.totalDiscount = 0;
                    this.extraGuestsFeeTotal = 0;
                    this.grandTotal = 0;

                    let cIn = this.normalizeDate(this.checkIn);
                    let cOut = this.normalizeDate(this.checkOut);

                    if (!cIn || !cOut || cOut <= cIn) return;

                    let checkInDay = this.flatDates[cIn];
                    let checkOutDay = this.flatDates[cOut];
                    if (!checkInDay || !checkOutDay) return;

                    let startCarbon = new Date(checkInDay.carbon_date);
                    let endCarbon = new Date(checkOutDay.carbon_date);
                    let daysDiff = Math.round((endCarbon - startCarbon) / (1000 * 3600 * 24));
                    if (daysDiff <= 0) return;

                    this.nightsCount = daysDiff;
                    let sumEffective = 0;
                    let sumOriginal = 0;
                    let blocked = false;

                    let curr = new Date(startCarbon);
                    for (let i = 0; i < daysDiff; i++) {
                        let y = curr.getFullYear();
                        let m = String(curr.getMonth() + 1).padStart(2, '0');
                        let d = String(curr.getDate()).padStart(2, '0');
                        let isoKey = `${y}-${m}-${d}`;

                        let dayData = this.flatDates[isoKey];
                        if (dayData) {
                            if (dayData.is_blocked) blocked = true;
                            let p = Number(dayData.price || this.basePrice);
                            let origP = Number(dayData.original_price || dayData.price || this.basePrice);
                            sumEffective += p;
                            sumOriginal += origP;
                        } else {
                            sumEffective += this.basePrice;
                            sumOriginal += this.basePrice;
                        }
                        curr.setDate(curr.getDate() + 1);
                    }

                    this.hasBlockedDays = blocked;
                    this.nightsTotal = sumEffective;
                    this.originalNightsTotal = sumOriginal;
                    this.totalDiscount = Math.max(0, sumOriginal - sumEffective);

                    let extraGuests = Math.max(0, this.guests - this.baseGuests);
                    this.extraGuestsFeeTotal = extraGuests * this.extraGuestFee * this.nightsCount;
                    this.grandTotal = this.nightsTotal + this.extraGuestsFeeTotal + this.cleaningFee;
                },

                goToBooking() {
                    let cIn = this.normalizeDate(this.checkIn);
                    let cOut = this.normalizeDate(this.checkOut);
                    if (!cIn || !cOut) {
                        const cal = document.getElementById('calendar-section');
                        if (cal) {
                            cal.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                        alert('لطفاً ابتدا تاریخ ورود و تاریخ خروج خود را از تقویم مشخص فرمایید.');
                        return;
                    }
                    if (this.hasBlockedDays) {
                        alert('برخی از تاریخ‌های انتخابی شما در تقویم مسدود یا رزرو شده هستند. لطفاً بازه دیگری را انتخاب فرمایید.');
                        return;
                    }
                    if (this.nightsCount < this.minStayNights) {
                        alert('حداقل مدت اقامت در این اقامتگاه ' + this.formatNumber(this.minStayNights) + ' شب می‌باشد.');
                        return;
                    }

                    let checkoutUrl = this.checkoutBaseUrl
                        + '?check_in=' + encodeURIComponent(cIn)
                        + '&check_out=' + encodeURIComponent(cOut)
                        + '&guests=' + encodeURIComponent(this.guests);

                    window.location.href = checkoutUrl;
                },

                formatDateDisplay(dateStr, formatType = 'full') {
                    if (!dateStr) return 'انتخاب از تقویم';
                    let normalized = this.normalizeDate(dateStr);
                    let dayData = this.flatDates[normalized];
                    if (dayData) {
                        if (formatType === 'full' && dayData.human_jalali_full) {
                            return dayData.human_jalali_full;
                        }
                        if (formatType === 'short' && dayData.human_jalali) {
                            return dayData.human_jalali;
                        }
                        if (dayData.human_jalali) {
                            return dayData.human_jalali;
                        }
                        if (dayData.jalali_date) {
                            return dayData.jalali_date;
                        }
                    }
                    try {
                        let parts = normalized.split('-');
                        if (parts.length === 3) {
                            let d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                            if (!isNaN(d.getTime())) {
                                if (formatType === 'full') {
                                    return new Intl.DateTimeFormat('fa-IR-u-nu-arabext', { weekday: 'short', day: 'numeric', month: 'long' }).format(d);
                                }
                                return new Intl.DateTimeFormat('fa-IR-u-nu-arabext', { day: 'numeric', month: 'long' }).format(d);
                            }
                        }
                    } catch (e) {}
                    return dateStr;
                },

                toPersianDigits(str) {
                    if (!str && str !== 0) return '';
                    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    return String(str).replace(/\d/g, x => persianDigits[x]);
                },

                formatNumber(val) {
                    if (!val && val !== 0) return '۰';
                    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    const formatted = Math.round(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    return formatted.replace(/\d/g, x => persianDigits[x]);
                }
            }
        }

        const galleryImages = @json($allGalleryUrls);
        let currentImageIndex = 0;

        function openLightbox(index = 0) {
            if (galleryImages.length === 0) return;
            currentImageIndex = index;
            updateLightbox();
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

        function updateLightbox() {
            document.getElementById('lightbox-image').src = galleryImages[currentImageIndex];
            const counter = document.getElementById('lightbox-counter');
            if (counter) {
                const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                let cur = String(currentImageIndex + 1).replace(/\d/g, x => persianDigits[x]);
                let tot = String(galleryImages.length).replace(/\d/g, x => persianDigits[x]);
                counter.innerText = 'تصویر ' + cur + ' از ' + tot;
            }
        }

        function nextImage() {
            if (galleryImages.length <= 1) return;
            currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
            updateLightbox();
        }

        function prevImage() {
            if (galleryImages.length <= 1) return;
            currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
            updateLightbox();
        }

        document.addEventListener('keydown', function(e) {
            const lb = document.getElementById('lightbox');
            if (!lb || lb.classList.contains('hidden')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
        });

        function shareProperty() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ addslashes($property->title) }}',
                    text: 'مشاهده و رزرو آنلاین اقامتگاه در لفور',
                    url: window.location.href,
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('لینک این اقامتگاه با موفقیت در کلیپ‌بورد کپی شد.');
                }).catch(() => {});
            }
        }

        @if($canShowMap && $property->latitude && $property->longitude)
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof L !== 'undefined') {
                    const lat = {{ $property->latitude }};
                    const lng = {{ $property->longitude }};
                    @if($isMapExact)
                        const map = L.map('map').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(map);
                        L.marker([lat, lng]).addTo(map).bindPopup('موقعیت دقیق اقامتگاه').openPopup();
                    @else
                        const map = L.map('map').setView([lat, lng], 14);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(map);
                        L.circle([lat, lng], {
                            color: '#059669',
                            fillColor: '#10b981',
                            fillOpacity: 0.25,
                            weight: 2,
                            radius: {{ $circleRadius }}
                        }).addTo(map).bindPopup('محدوده تقریبی اقامتگاه');
                    @endif
                }
            });
        @endif
    </script>
@endsection
