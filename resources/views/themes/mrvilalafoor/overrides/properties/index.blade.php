@extends('layouts.web')

@section('title', 'لیست کامل اقامتگاه‌ها و ویلاها - مستر ویلا لفور')

@section('content')
<style>
    .card-title-fixed-height {
        line-height: 1.4em !important;
        height: 2.8em !important;
        min-height: 2.8em !important;
        max-height: 2.8em !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        word-break: break-word;
    }
</style>

@php
    $rentalModeEnabled = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_mode_enabled', 0);
    $multiHostEnabled = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_multi_host_enabled', 0);
    $rentalPropertyTypes = \Modules\Properties\Entities\Property::getRentalPropertyTypes(true);
    $activeHosts = $multiHostEnabled ? \Modules\Properties\Entities\PropertyHost::where('status', 'active')->get() : collect();
    $categoriesList = $categories ?? \Modules\Properties\Entities\PropertyCategory::all();
    $destinationsList = $availableDestinations ?? [];
    $currLabel = $currencyLabel ?? (\Modules\Properties\Entities\PropertySetting::getCurrencyLabel() ?? 'تومان');

    // Dynamic Multi-Host Filter Query Support
    $displayProperties = $properties;
    if ($multiHostEnabled && request()->filled('host_id')) {
        $hQuery = \Modules\Properties\Entities\Property::visibleToUser()
            ->where('publication_status', 'published')
            ->whereHas('status', function ($q) {
                $q->where('show_in_crm', true);
            })
            ->where('host_id', request('host_id'))
            ->with(['status', 'attributeValues', 'category', 'building', 'rentalConfig', 'host'])
            ->latest();

        if (request()->filled('search')) {
            $s = trim(request('search'));
            $hQuery->where(function($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%");
            });
        }
        if (request()->filled('city')) {
            $hQuery->where('city', request('city'));
        }
        if (request()->filled('property_type')) {
            $hQuery->where('property_type', request('property_type'));
        }
        if (request()->filled('category_id')) {
            $hQuery->where('category_id', request('category_id'));
        }
        if (request()->filled('instant_booking') && (request('instant_booking') == '1' || request('instant_booking') === 'true')) {
            $hQuery->whereHas('rentalConfig', function($rq) {
                $rq->where('instant_booking', true);
            });
        }
        if (request()->filled('min_price')) {
            $hQuery->where('price', '>=', request('min_price'));
        }
        if (request()->filled('max_price')) {
            $hQuery->where('price', '<=', request('max_price'));
        }
        $displayProperties = $hQuery->paginate(12)->withQueryString();
    }

    // Active filters detector for quick pills
    $activeFilterPills = [];
    if (request()->filled('search')) {
        $activeFilterPills[] = ['label' => 'جستجو: ' . request('search'), 'param' => 'search'];
    }
    if (request()->filled('city')) {
        $activeFilterPills[] = ['label' => 'شهر: ' . request('city'), 'param' => 'city'];
    }
    if (request()->filled('property_type')) {
        $typeLabel = $rentalPropertyTypes[request('property_type')]['label'] ?? request('property_type');
        $activeFilterPills[] = ['label' => 'نوع: ' . $typeLabel, 'param' => 'property_type'];
    }
    if (request()->filled('category_id')) {
        $foundCat = $categoriesList->firstWhere('id', request('category_id'));
        $catLabel = $foundCat ? $foundCat->name : ('دسته #' . request('category_id'));
        $activeFilterPills[] = ['label' => 'دسته‌بندی: ' . $catLabel, 'param' => 'category_id'];
    }
    if (request()->filled('instant_booking') && request('instant_booking') == '1') {
        $activeFilterPills[] = ['label' => '⚡ رزرو آنی', 'param' => 'instant_booking'];
    }
    if ($multiHostEnabled && request()->filled('host_id')) {
        $foundHost = $activeHosts->firstWhere('id', request('host_id'));
        $hostLabel = $foundHost ? $foundHost->display_name : ('میزبان #' . request('host_id'));
        $activeFilterPills[] = ['label' => 'میزبان: ' . $hostLabel, 'param' => 'host_id'];
    }
@endphp

<div class="max-w-[1360px] mx-auto px-4 sm:px-8 py-8 w-full font-sans space-y-6">

    {{-- Breadcrumb & Title --}}
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-xs font-medium text-zinc-400">
            <a href="{{ url('/') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">مستر ویلا لفور</a>
            <span>/</span>
            <span class="text-[#18181B] dark:text-zinc-100 font-bold">اقامتگاه‌ها و ویلاها</span>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-[#18181B] dark:text-zinc-100 tracking-tight">
                    لیست اقامتگاه‌ها و ویلاها
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    رزرو آنلاین انواع اقامتگاه در سوادکوه، لفور و بهترین مقاصد شمال
                </p>
            </div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800/60 px-3 py-1.5 rounded-xl font-medium">
                تعداد کل: <span class="font-black text-[#007A5E] dark:text-emerald-400">{{ $displayProperties->total() }}</span> اقامتگاه
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <form action="{{ route('properties.index') }}" method="GET" class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-{{ $multiHostEnabled ? '6' : '5' }} gap-3 text-xs">
            
            {{-- Text Search --}}
            <div class="sm:col-span-2 md:col-span-1 lg:col-span-2">
                <label class="block text-[11px] text-zinc-400 font-medium mb-1">جستجو در عنوان یا کد اقامتگاه</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="نام اقامتگاه، کد ملک یا منطقه..." class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] dark:focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none transition-colors">
                    @if(request('search'))
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute left-2.5 top-2.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xs">×</a>
                    @endif
                </div>
            </div>

            {{-- Province & City Destination --}}
            <div>
                <label class="block text-[11px] text-zinc-400 font-medium mb-1">مقصد (شهر و استان)</label>
                <select name="city" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] dark:focus:border-emerald-500 rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none cursor-pointer transition-colors">
                    <option value="">همه مقاصد</option>
                    @foreach($destinationsList as $destGroup)
                        <optgroup label="استان {{ $destGroup['province'] }}">
                            @foreach($destGroup['cities'] as $c)
                                <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            {{-- Rental Property Types (Synced with rental_mode_enabled and types) --}}
            @if($rentalModeEnabled)
                <div>
                    <label class="block text-[11px] text-zinc-400 font-medium mb-1">نوع اقامتگاه</label>
                    <select name="property_type" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] dark:focus:border-emerald-500 rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none cursor-pointer transition-colors">
                        <option value="">همه انواع اقامتگاه</option>
                        @foreach($rentalPropertyTypes as $typeKey => $tConfig)
                            <option value="{{ $typeKey }}" {{ request('property_type') == $typeKey ? 'selected' : '' }}>
                                {{ $tConfig['label'] ?? $typeKey }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <label class="block text-[11px] text-zinc-400 font-medium mb-1">نوع معامله</label>
                    <select name="listing_type" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] dark:focus:border-emerald-500 rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none cursor-pointer">
                        <option value="">همه معاملات</option>
                        <option value="daily_rental" {{ request('listing_type') == 'daily_rental' ? 'selected' : '' }}>اجاره روزانه</option>
                        <option value="rent" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>رهن و اجاره</option>
                        <option value="sale" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>خرید و فروش</option>
                    </select>
                </div>
            @endif

            {{-- Categories Filter --}}
            @if($categoriesList->isNotEmpty())
                <div>
                    <label class="block text-[11px] text-zinc-400 font-medium mb-1">دسته‌بندی</label>
                    <select name="category_id" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] dark:focus:border-emerald-500 rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none cursor-pointer transition-colors">
                        <option value="">همه دسته‌ها</option>
                        @foreach($categoriesList as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Multi-Host Filter (Active when rental_multi_host_enabled is on) --}}
            @if($multiHostEnabled && $activeHosts->isNotEmpty())
                <div>
                    <label class="block text-[11px] text-zinc-400 font-medium mb-1">میزبان اقامتگاه</label>
                    <select name="host_id" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] dark:focus:border-emerald-500 rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none cursor-pointer transition-colors">
                        <option value="">همه میزبان‌ها</option>
                        @foreach($activeHosts as $h)
                            <option value="{{ $h->id }}" {{ request('host_id') == $h->id ? 'selected' : '' }}>
                                {{ $h->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Instant Booking Filter --}}
            <div>
                <label class="block text-[11px] text-zinc-400 font-medium mb-1">نحوه رزرو</label>
                <select name="instant_booking" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] dark:focus:border-emerald-500 rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none cursor-pointer transition-colors">
                    <option value="">همه اقامتگاه‌ها</option>
                    <option value="1" {{ request('instant_booking') == '1' ? 'selected' : '' }}>⚡ رزرو قطعی آنی</option>
                </select>
            </div>

        </div>

        {{-- Filter Buttons Row --}}
        <div class="flex items-center justify-between pt-2 border-t border-[#ECECEE] dark:border-zinc-800">
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-[#007A5E] hover:bg-[#005F48] text-white px-6 py-2.5 rounded-xl font-bold text-xs transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>اعمال فیلترها</span>
                </button>
                @if(count($activeFilterPills) > 0)
                    <a href="{{ route('properties.index') }}" class="px-4 py-2.5 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 text-rose-600 dark:text-rose-400 font-bold text-xs transition-colors flex items-center gap-1">
                        <span>حذف همه فیلترها</span>
                    </a>
                @endif
            </div>

            @if(count($activeFilterPills) > 0)
                <div class="hidden sm:flex items-center gap-1.5 flex-wrap">
                    @foreach($activeFilterPills as $pill)
                        <a href="{{ request()->fullUrlWithQuery([$pill['param'] => null]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 text-[11px] font-bold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                            <span>{{ $pill['label'] }}</span>
                            <span class="text-zinc-400 hover:text-rose-500 font-bold">×</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </form>

    {{-- Properties Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-6">
        @forelse($displayProperties as $property)
            @php
                $rentalConfig = $property->rentalConfig;
                $coverUrl = null;
                if (!empty($property->cover_image)) {
                    $coverUrl = str_starts_with($property->cover_image, 'http')
                        ? $property->cover_image
                        : asset('storage/' . $property->cover_image);
                } else {
                    $coverUrl = asset('images/property-placeholder.svg');
                }

                $rate = $rentalConfig?->price_per_night ?: ($property->rent_price ?: $property->price);
                $oldRate = $rentalConfig?->normal_price_per_night ?: ($property->price > $rate ? $property->price : null);
                $hasDiscount = $oldRate && $oldRate > $rate;
                $discountPercent = $hasDiscount ? round((($oldRate - $rate) / $oldRate) * 100) : 0;

                // Specs Line
                $rooms = $property->bedrooms ?? $property->rooms ?? null;
                $maxGuests = $rentalConfig?->max_guests ?? $property->max_guests ?? null;
                $area = $property->area ?? null;

                $specs = [];
                if (!empty($rooms) && (int)$rooms > 0) {
                    $specs[] = (int)$rooms . ' خوابه';
                }
                if (!empty($maxGuests) && (int)$maxGuests > 0) {
                    $specs[] = 'تا ' . (int)$maxGuests . ' نفر';
                }
                if (!empty($area) && (int)$area > 0) {
                    $specs[] = (int)$area . ' متر';
                }
                $specsText = count($specs) > 0 ? implode(' • ', $specs) : 'اقامتگاه مبله';
            @endphp

            <article class="group select-none bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800/80 hover:border-zinc-300 dark:hover:border-zinc-700 rounded-2xl p-2.5 sm:p-3 transition-all duration-200 shadow-[0_2px_8px_rgba(0,0,0,0.03)] dark:shadow-[0_2px_8px_rgba(0,0,0,0.2)] flex flex-col justify-between h-full">
                <a href="{{ route('properties.show', $property->slug ?: $property->code) }}" class="flex flex-col justify-between h-full">
                    <div>
                        {{-- Image Container --}}
                        <div class="relative aspect-[4/3] w-full rounded-xl overflow-hidden bg-[#F7F8F9] dark:bg-zinc-800 mb-2.5 sm:mb-3 border border-[#ECECEE]/60 dark:border-zinc-800/60">
                            <img src="{{ $coverUrl }}" alt="{{ $property->title }}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300" loading="lazy">
                            
                            {{-- Special Badge --}}
                            @if(!empty($property->is_special) || (!empty($property->meta) && !empty($property->meta['is_special'])))
                                <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-lg bg-amber-500 text-white text-[10px] sm:text-[11px] font-bold shadow-sm font-sans">
                                    ویژه
                                </span>
                            @endif

                            {{-- Instant Booking Badge --}}
                            @if($rentalConfig && $rentalConfig->instant_booking)
                                <span class="absolute bottom-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/95 dark:bg-zinc-900/95 text-amber-600 dark:text-amber-400 text-[10px] font-bold shadow-xs backdrop-blur-xs font-sans">
                                    <span class="text-xs">⚡</span>
                                    <span>رزرو آنی</span>
                                </span>
                            @endif
                        </div>

                        {{-- Title & Rating --}}
                        <div class="flex items-start justify-between gap-1.5 px-0.5">
                            <h3 class="card-title-fixed-height text-xs sm:text-sm font-bold text-[#18181B] dark:text-zinc-100 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors flex-1">
                                {{ $property->title }}
                            </h3>
                            @if(!empty($property->rating))
                                <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold text-[#18181B] dark:text-zinc-100 shrink-0 mt-0.5">
                                    <span class="text-amber-400 dark:text-amber-400 text-xs">★</span>
                                    <span>{{ number_format($property->rating, 1) }}</span>
                                </span>
                            @endif
                        </div>

                        {{-- Multi-Host Info (When Multi-Host Enabled) --}}
                        @if($multiHostEnabled && $property->host)
                            <div class="flex items-center gap-1 mt-1 px-0.5 text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">
                                <svg class="w-3.5 h-3.5 text-[#007A5E] dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="truncate">میزبان: {{ $property->host->display_name }}</span>
                            </div>
                        @endif

                        {{-- Specs & City --}}
                        <div class="flex items-center justify-between gap-1.5 mt-1.5 px-0.5">
                            <p class="text-[11px] sm:text-xs text-zinc-500 dark:text-zinc-400 truncate font-sans">
                                {{ $specsText }}
                            </p>
                            @if(!empty($property->city))
                                <span class="text-[11px] sm:text-xs text-zinc-500 dark:text-zinc-400 shrink-0 font-sans">
                                    {{ $property->city }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Pricing Section --}}
                    <div class="mt-2.5 pt-2 border-t border-[#ECECEE]/70 dark:border-zinc-800/70 flex items-end justify-between gap-1.5 px-0.5 font-sans">
                        <div>
                            @if($hasDiscount)
                                <span class="block text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-500 line-through font-normal leading-tight font-sans">
                                    {{ number_format($oldRate) }}
                                </span>
                            @endif
                            <div class="text-sm sm:text-base font-black {{ $hasDiscount ? 'text-[#007A5E] dark:text-emerald-400' : 'text-[#18181B] dark:text-zinc-100' }} leading-tight font-sans mt-0.5">
                                @if($rate > 0)
                                    {{ number_format($rate) }} <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 font-sans">{{ $currLabel }}</span> <span class="text-[10px] sm:text-[11px] font-normal text-zinc-400 dark:text-zinc-500 font-sans">/ شب</span>
                                @else
                                    <span class="text-xs font-medium text-zinc-500">استعلام قیمت</span>
                                @endif
                            </div>
                        </div>

                        @if($hasDiscount && $discountPercent > 0)
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-[#ECFDF5] dark:bg-emerald-950/60 text-[#007A5E] dark:text-emerald-400 text-xs font-black font-sans leading-none shrink-0 mb-0.5">
                                {{ $discountPercent }}٪ تخفیف
                            </span>
                        @endif
                    </div>
                </a>
            </article>
        @empty
            <div class="col-span-2 md:col-span-3 lg:col-span-4 py-20 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-2xl text-zinc-400">
                    🔍
                </div>
                <p class="text-base font-bold text-[#18181B] dark:text-zinc-200 mb-2">اقامتگاهی با فیلترهای مشخص‌شده یافت نشد.</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">می‌توانید فیلترها را تغییر داده یا همه اقامتگاه‌ها را مشاهده نمایید.</p>
                <a href="{{ route('properties.index') }}" class="inline-block px-6 py-2.5 rounded-xl bg-[#007A5E] hover:bg-[#005F48] text-white text-xs font-bold transition-colors shadow-sm">
                    نمایش همه اقامتگاه‌ها
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($displayProperties->hasPages())
        <div class="pt-6 border-t border-[#ECECEE] dark:border-zinc-800">
            {{ $displayProperties->links() }}
        </div>
    @endif

</div>
@endsection
