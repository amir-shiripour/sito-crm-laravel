@extends('layouts.web')

@section('title', $property->title . ' - اجاره ویلا و اقامتگاه در لفور')

@push('styles')
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* استایل‌های اختصاصی پاپ‌آپ نقشه */
        .leaflet-popup-content-wrapper {
            border-radius: 1rem !important; padding: 0 !important; overflow: hidden !important;
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
        $checkInTimeFormatted = $formatTimeOnly($config?->check_in_time, '14:00');
        $checkOutTimeFormatted = $formatTimeOnly($config?->check_out_time, '12:00');

        $features = $property->attributeValues->filter(function($attr) {
            return $attr->attribute && $attr->attribute->section === 'features' && ($attr->value == '1' || $attr->value === true);
        });

        $houseRules = $config ? ($config->house_rules ?? []) : [];
        $customRules = $houseRules['custom_rules'] ?? [];
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
    @endphp

    <div class="max-w-[1360px] mx-auto px-4 sm:px-8 py-8 w-full font-sans" 
         x-data="rentalBookingApp(@js($calendarData['flatDates'] ?? []), @js($config), '{{ $currencyLabel }}', '{{ addslashes($property->title) }}', '{{ $property->code }}', @js($clientPayload), '{{ route('properties.book', $property->slug ?: $property->code) }}')">

        {{-- Breadcrumb & Title --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div class="flex items-center gap-2 text-xs font-medium text-zinc-400">
                <a href="{{ url('/') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">مستر ویلا لفور</a>
                <span>/</span>
                <a href="{{ route('properties.index') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">اقامتگاه‌ها</a>
                <span>/</span>
                <span class="text-[#18181B] dark:text-zinc-100 font-bold line-clamp-1">{{ $property->title }}</span>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 text-xs font-bold border border-[#007A5E]/20">
                    کد اقامتگاه: {{ $property->code }}
                </span>
                @if($config && $config->instant_booking)
                    <span class="px-3 py-1 rounded-full bg-[#007A5E] text-white text-xs font-bold shadow-sm">
                        رزرو آنی
                    </span>
                @endif
            </div>
        </div>

        {{-- Header Titles & Actions --}}
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-[#18181B] dark:text-zinc-100 tracking-tight leading-snug">
                    {{ $property->title }}
                </h1>
                <p class="text-xs sm:text-sm text-zinc-400 mt-1 flex items-center gap-2">
                    <span>{{ $property->city ?? 'لفور' }}</span>
                    <span>•</span>
                    <span>ظرفیت تا {{ $config?->max_guests ?? ($property->max_guests ?? 4) }} نفر</span>
                    <span>•</span>
                    <span>{{ $property->bedrooms ?? 2 }} اتاق خواب</span>
                </p>
            </div>

            <div class="flex items-center gap-2 self-start">
                <button type="button" onclick="copyToClipboard(window.location.href)" class="px-3 py-2 rounded-xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-400 text-xs font-bold text-zinc-700 dark:text-zinc-300 transition-colors flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span>اشتراک‌گذاری</span>
                </button>
            </div>
        </div>

        {{-- Gallery Grid (Flat Minimalist Layout) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 rounded-3xl overflow-hidden mb-10 border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-900 p-2">
            <div class="md:col-span-2 md:row-span-2 aspect-[4/3] rounded-2xl overflow-hidden cursor-pointer relative group" onclick="openLightbox(0)">
                <img src="{{ $allGalleryUrls[0] }}" alt="{{ $property->title }}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors"></div>
            </div>

            @for($i = 1; $i < min(5, count($allGalleryUrls)); $i++)
                <div class="aspect-[4/3] rounded-2xl overflow-hidden cursor-pointer relative group hidden sm:block" onclick="openLightbox({{ $i }})">
                    <img src="{{ $allGalleryUrls[$i] }}" alt="{{ $property->title }}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @if($i === 4 && count($allGalleryUrls) > 5)
                        <div class="absolute inset-0 bg-black/60 flex items-center justify-center text-white text-xs font-black">
                            + {{ count($allGalleryUrls) - 5 }} تصویر دیگر
                        </div>
                    @endif
                </div>
            @endfor
        </div>

        {{-- Main Two-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- Right Column: Information, Specs, Host, Calendar, Map --}}
            <div class="lg:col-span-7 space-y-10">

                {{-- Host Box (Multi-Host Marketplace Feature) --}}
                @if($canShowHostBox && $host)
                    <div class="p-5 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 flex items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 min-w-0">
                            @if($canShowHostAvatar && $host->avatar)
                                <img src="{{ asset('storage/' . $host->avatar) }}" alt="{{ $host->display_name }}" class="w-12 h-12 rounded-full object-cover border border-[#ECECEE] dark:border-zinc-700 shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-full bg-[#ECFDF5] dark:bg-emerald-950/50 text-[#007A5E] dark:text-emerald-400 font-black text-sm flex items-center justify-center shrink-0">
                                    {{ mb_substr($host->display_name ?: 'م', 0, 1) }}
                                </div>
                            @endif
                            <div class="truncate">
                                <div class="flex items-center gap-1.5">
                                    <h3 class="text-sm font-bold text-[#18181B] dark:text-zinc-100 truncate">
                                        میزبان: {{ $host->display_name ?: ($host->user?->name ?: 'میزبان اقامتگاه') }}
                                    </h3>
                                    @if($canShowHostBadge && ($host->kyc_status === 'approved' || $host->is_verified))
                                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold border border-emerald-200 dark:border-emerald-800">
                                            ✓ احراز شده
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-zinc-400 mt-0.5 truncate">
                                    عضویت در مستر ویلا لفور • پاسخگویی سریع
                                </p>
                            </div>
                        </div>

                        @if($canShowDirectCall && $hostPhone)
                            <a href="tel:{{ $hostPhone }}" class="px-4 py-2 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800 hover:bg-[#ECECEE] dark:hover:bg-zinc-700 text-xs font-bold text-[#18181B] dark:text-zinc-200 transition-colors shrink-0">
                                تماس مستقیم
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Specs Grid (4 Clean Flat Badges) --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="p-3.5 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 text-center">
                        <span class="text-[11px] text-zinc-400 block font-medium">ظرفیت استاندارد</span>
                        <strong class="text-xs font-black text-[#18181B] dark:text-zinc-100 mt-1 block">{{ $config?->base_guests ?? 2 }} نفر</strong>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 text-center">
                        <span class="text-[11px] text-zinc-400 block font-medium">حداکثر ظرفیت</span>
                        <strong class="text-xs font-black text-[#18181B] dark:text-zinc-100 mt-1 block">{{ $config?->max_guests ?? 4 }} نفر</strong>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 text-center">
                        <span class="text-[11px] text-zinc-400 block font-medium">ساعت تحویل</span>
                        <strong class="text-xs font-black text-[#007A5E] dark:text-emerald-400 mt-1 block">{{ $checkInTimeFormatted }}</strong>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 text-center">
                        <span class="text-[11px] text-zinc-400 block font-medium">ساعت تخلیه</span>
                        <strong class="text-xs font-black text-rose-600 dark:text-rose-400 mt-1 block">{{ $checkOutTimeFormatted }}</strong>
                    </div>
                </div>

                {{-- Description --}}
                <div class="space-y-3">
                    <h3 class="text-base font-black text-[#18181B] dark:text-zinc-100">درباره این اقامتگاه</h3>
                    <div class="text-xs leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-2 whitespace-pre-line">
                        {{ $property->description ?: 'این اقامتگاه زیبا با چشم‌اندازی بی‌نظیر از طبیعت بکر و جنگل‌های لفور، محیطی آرامش‌بخش برای اقامت شما و خانواده فراهم می‌کند.' }}
                    </div>
                </div>

                {{-- Amenities / Features --}}
                @if($features->isNotEmpty())
                    <div class="space-y-3 pt-6 border-t border-[#ECECEE] dark:border-zinc-800">
                        <h3 class="text-base font-black text-[#18181B] dark:text-zinc-100">امکانات رفاهی و اقامتی</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            @foreach($features as $feature)
                                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 text-xs font-bold text-[#18181B] dark:text-zinc-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#007A5E] dark:bg-emerald-400"></span>
                                    <span>{{ $feature->attribute->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Interactive Calendar Section --}}
                <div class="space-y-4 pt-6 border-t border-[#ECECEE] dark:border-zinc-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-black text-[#18181B] dark:text-zinc-100">تقویم رزرو و ظرفیت</h3>
                            <p class="text-[11px] text-zinc-400 mt-0.5">روزهای مورد نظر را با کلیک روی تاریخ‌ها انتخاب فرمایید</p>
                        </div>
                        <button type="button" @click="clearDates()" class="text-xs text-[#007A5E] dark:text-emerald-400 font-bold hover:underline">
                            پاک کردن تاریخ‌ها
                        </button>
                    </div>

                    @if(isset($calendarData['months']) && count($calendarData['months']) > 0)
                        <div class="space-y-6">
                            @foreach($calendarData['months'] as $m)
                                <div class="border border-[#ECECEE] dark:border-zinc-800 rounded-2xl p-4 bg-white dark:bg-zinc-900 shadow-sm">
                                    <div class="text-xs font-black text-[#18181B] dark:text-zinc-100 mb-3 text-center">
                                        {{ $m['month_title'] ?? (($m['month_name'] ?? '') . ' ' . ($m['year'] ?? '')) }}
                                    </div>
                                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] text-zinc-400 font-bold mb-2">
                                        <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                        @for($e = 0; $e < ($m['empty_cells_start'] ?? ($m['first_day_of_week'] ?? 0)); $e++)
                                            <div></div>
                                        @endfor

                                        @foreach($m['days'] as $day)
                                            @php
                                                $isPast = !empty($day['is_past']);
                                                $isBlocked = !empty($day['is_blocked']);
                                                $iso = $day['carbon_date'];
                                                $priceFormatted = !empty($day['price']) ? number_format($day['price'] / 1000) . 'هـ' : '';
                                                $dayLabel = $day['day_num'] ?? ($day['day'] ?? '');
                                            @endphp
                                            <button type="button" 
                                                    @if(!$isPast && !$isBlocked) @click="selectCalendarDate('{{ $iso }}')" @endif
                                                    :class="getDayClasses('{{ $iso }}', {{ $isPast ? 'true' : 'false' }}, {{ $isBlocked ? 'true' : 'false' }})"
                                                    class="py-1.5 px-0.5 rounded-xl flex flex-col items-center justify-center transition-all min-h-[46px] border border-transparent {{ $isPast || $isBlocked ? 'opacity-40 cursor-not-allowed bg-zinc-50 dark:bg-zinc-900/40 text-zinc-400' : 'hover:border-[#007A5E] text-[#18181B] dark:text-zinc-100' }}">
                                                <span class="font-bold text-xs">{{ $dayLabel }}</span>
                                                <span class="text-[9px] text-zinc-400 mt-0.5">{{ $isBlocked ? 'مسدود' : $priceFormatted }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Accommodation Rules --}}
                <div class="space-y-3 pt-6 border-t border-[#ECECEE] dark:border-zinc-800">
                    <h3 class="text-base font-black text-[#18181B] dark:text-zinc-100">قوانین و مقررات اقامتگاه</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">استعمال دخانیات</span>
                            <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ !empty($houseRules['smoking_allowed']) ? 'مجاز' : 'ممنوع' }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">ورود حیوانات خانگی</span>
                            <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ !empty($houseRules['pets_allowed']) ? 'مجاز' : 'ممنوع' }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">برگزاری مهمانی و جشن</span>
                            <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ !empty($houseRules['parties_allowed']) ? 'مجاز' : 'ممنوع' }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">ارائه مدارک شناسایی</span>
                            <span class="font-bold text-[#007A5E] dark:text-emerald-400">الزامی (کارت ملی هوشمند)</span>
                        </div>
                    </div>
                </div>

                {{-- Map Area --}}
                @if($canShowMap && $property->latitude && $property->longitude)
                    <div class="space-y-3 pt-6 border-t border-[#ECECEE] dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-black text-[#18181B] dark:text-zinc-100">موقعیت اقامتگاه روی نقشه</h3>
                            <span class="text-[11px] text-zinc-400">
                                {{ $isMapExact ? 'موقعیت دقیق' : 'محدوده تقریبی اقامتگاه' }}
                            </span>
                        </div>
                        <div id="map" class="w-full h-72 rounded-2xl overflow-hidden border border-[#ECECEE] dark:border-zinc-800 z-10"></div>
                    </div>
                @endif

            </div>

            {{-- Left Column: Sticky Reservation & Quote Box --}}
            <div class="lg:col-span-5 sticky top-24">
                <div class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-xl space-y-6">
                    
                    {{-- Price Header --}}
                    <div class="flex items-baseline justify-between pb-5 border-b border-[#ECECEE] dark:border-zinc-800">
                        <div>
                            <span class="text-[11px] text-zinc-400 block font-medium">شروع نرخ از</span>
                            <div class="text-xl font-black text-[#18181B] dark:text-zinc-100 mt-0.5">
                                {{ number_format($config?->price_per_night ?: ($property->rent_price ?: $property->price ?: 2200000)) }}
                                <span class="text-xs font-normal text-zinc-400">{{ $currencyLabel }} / شب</span>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded-full bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 text-xs font-bold">
                            تضمین بهترین قیمت
                        </span>
                    </div>

                    {{-- Dates Selector Inputs --}}
                    <div class="grid grid-cols-2 gap-2 p-3 bg-[#F7F8F9] dark:bg-zinc-800 rounded-2xl text-xs">
                        <div class="space-y-1">
                            <span class="text-[10px] text-zinc-400 block">تاریخ ورود</span>
                            <span class="font-bold text-[#18181B] dark:text-zinc-100 block truncate" x-text="checkIn ? checkIn : 'انتخاب نشده'"></span>
                        </div>
                        <div class="space-y-1 border-r border-[#ECECEE] dark:border-zinc-700 pr-2">
                            <span class="text-[10px] text-zinc-400 block">تاریخ خروج</span>
                            <span class="font-bold text-[#18181B] dark:text-zinc-100 block truncate" x-text="checkOut ? checkOut : 'انتخاب نشده'"></span>
                        </div>
                    </div>

                    {{-- Guests Selector --}}
                    <div class="flex items-center justify-between p-3 bg-[#F7F8F9] dark:bg-zinc-800 rounded-2xl text-xs">
                        <div>
                            <span class="text-[10px] text-zinc-400 block">تعداد مهمانان</span>
                            <span class="font-bold text-[#18181B] dark:text-zinc-100" x-text="formatNumber(guests) + ' نفر'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="increaseGuests()" class="w-7 h-7 rounded-lg bg-white dark:bg-zinc-700 border border-[#ECECEE] dark:border-zinc-600 font-bold">+</button>
                            <button type="button" @click="decreaseGuests()" class="w-7 h-7 rounded-lg bg-white dark:bg-zinc-700 border border-[#ECECEE] dark:border-zinc-600 font-bold">−</button>
                        </div>
                    </div>

                    {{-- Live Price Breakdown (If Dates Chosen) --}}
                    <div x-show="nightsCount > 0" x-transition class="space-y-2 pt-4 border-t border-[#ECECEE] dark:border-zinc-800 text-xs">
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span x-text="'اقامت برای ' + formatNumber(nightsCount) + ' شب'"></span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-200" x-text="formatNumber(nightsTotal) + ' ' + currencyLabel"></span>
                        </div>

                        <div x-show="extraGuestsFeeTotal > 0" class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>هزینه نفرات اضافه</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-200" x-text="formatNumber(extraGuestsFeeTotal) + ' ' + currencyLabel"></span>
                        </div>

                        <div x-show="cleaningFee > 0" class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>هزینه نظافت</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-200" x-text="formatNumber(cleaningFee) + ' ' + currencyLabel"></span>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-[#ECECEE] dark:border-zinc-800 text-sm font-black text-[#18181B] dark:text-zinc-100">
                            <span>مبلغ کل برآورد:</span>
                            <span class="text-[#007A5E] dark:text-emerald-400" x-text="formatNumber(grandTotal) + ' ' + currencyLabel"></span>
                        </div>
                    </div>

                    {{-- Action CTA --}}
                    <div class="space-y-2 pt-2">
                        <button type="button" 
                                @click="openBookingModal()" 
                                class="w-full py-4 rounded-2xl bg-[#007A5E] hover:bg-[#005F48] text-white font-bold text-xs transition-colors shadow-lg shadow-emerald-900/10 flex items-center justify-center gap-2">
                            <span>ثبت درخواست رزرو / مشاهده پیش‌فاکتور</span>
                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>

                        <p class="text-[10px] text-zinc-400 text-center">
                            هیچ مبلغی در این مرحله کسر نمی‌شود.
                        </p>
                    </div>

                </div>
            </div>

        </div>

    </div>

    {{-- Lightbox Modal --}}
    <div id="lightbox" class="hidden fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <button type="button" onclick="closeLightbox()" class="absolute top-6 left-6 text-white text-lg font-bold w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">✕</button>
        <button type="button" onclick="prevImage()" class="absolute right-6 top-1/2 -translate-y-1/2 text-white w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">❮</button>
        <button type="button" onclick="nextImage()" class="absolute left-6 top-1/2 -translate-y-1/2 text-white w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">❯</button>
        <img id="lightbox-image" src="" alt="" class="max-h-[85vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl">
    </div>

    {{-- Script Engine --}}
    <script>
        function rentalBookingApp(flatDates, config, currencyLabel, propertyTitle, propertyCode, clientPayload, bookingUrl) {
            return {
                flatDates: flatDates || {},
                config: config || {},
                currencyLabel: currencyLabel,
                propertyTitle: propertyTitle,
                propertyCode: propertyCode,
                client: clientPayload,
                bookingUrl: bookingUrl,

                checkIn: '',
                checkOut: '',
                guests: Number(config?.base_guests || 2),
                baseGuests: Number(config?.base_guests || 2),
                maxGuests: Number(config?.max_guests || 6),
                basePrice: Number(config?.price_per_night || 2000000),
                extraGuestFee: Number(config?.extra_guest_fee || 0),
                cleaningFee: Number(config?.cleaning_fee || 0),
                minStayNights: Number(config?.min_stay_nights || 1),

                nightsCount: 0,
                nightsTotal: 0,
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

                    if (d === cIn || d === cOut) {
                        return '!bg-[#007A5E] !text-white font-black shadow-sm';
                    }
                    if (cIn && cOut && d > cIn && d < cOut) {
                        return '!bg-[#ECFDF5] dark:!bg-emerald-950/40 !text-[#007A5E] dark:!text-emerald-400 font-bold';
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

                    if (!cIn || !cOut || cOut <= cIn) return;

                    let checkInDay = this.flatDates[cIn];
                    let checkOutDay = this.flatDates[cOut];
                    if (!checkInDay || !checkOutDay) return;

                    let startCarbon = new Date(checkInDay.carbon_date);
                    let endCarbon = new Date(checkOutDay.carbon_date);
                    let daysDiff = Math.round((endCarbon - startCarbon) / (1000 * 3600 * 24));
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
                            if (dayData.is_blocked) blocked = true;
                            totalNightsPrice += Number(dayData.price || this.basePrice);
                        } else {
                            totalNightsPrice += this.basePrice;
                        }
                        curr.setDate(curr.getDate() + 1);
                    }

                    this.hasBlockedDays = blocked;
                    this.nightsTotal = totalNightsPrice;
                    let extraGuests = Math.max(0, this.guests - this.baseGuests);
                    this.extraGuestsFeeTotal = extraGuests * this.extraGuestFee * this.nightsCount;
                    this.grandTotal = this.nightsTotal + this.extraGuestsFeeTotal + this.cleaningFee;
                },

                openBookingModal() {
                    let cIn = this.normalizeDate(this.checkIn);
                    let cOut = this.normalizeDate(this.checkOut);
                    if (!cIn || !cOut) {
                        alert('لطفاً ابتدا تاریخ ورود و خروج را در تقویم انتخاب نمایید.');
                        return;
                    }
                    if (this.hasBlockedDays) {
                        alert('برخی از روزهای انتخابی شما مسدود یا رزرو شده است.');
                        return;
                    }

                    let checkoutUrl = '{{ route("properties.checkout", $property->slug ?: $property->code) }}'
                        + '?check_in=' + encodeURIComponent(cIn)
                        + '&check_out=' + encodeURIComponent(cOut)
                        + '&guests=' + encodeURIComponent(this.guests);

                    window.location.href = checkoutUrl;
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

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('لینک اقامتگاه با موفقیت کپی شد.');
            }).catch(() => {});
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
                            color: '#007A5E',
                            fillColor: '#007A5E',
                            fillOpacity: 0.2,
                            weight: 2,
                            radius: {{ $circleRadius }}
                        }).addTo(map).bindPopup('محدوده تقریبی اقامتگاه');
                    @endif
                }
            });
        @endif
    </script>
@endsection
