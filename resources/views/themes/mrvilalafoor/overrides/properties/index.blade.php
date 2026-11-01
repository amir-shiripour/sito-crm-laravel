@extends('layouts.web')

@section('title', 'لیست املاک و اقامتگاه‌ها - مستر ویلا لفور')

@section('content')
<style>
    .card-title-fixed-height {
        line-height: 2em !important;
        height: 4em !important;
        min-height: 4em !important;
        max-height: 4em !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        word-break: break-word;
    }
</style>
<div class="max-w-[1360px] mx-auto px-4 sm:px-8 py-8 w-full font-sans space-y-8">

    {{-- Breadcrumb & Title --}}
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-xs font-medium text-zinc-400">
            <a href="{{ url('/') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">مستر ویلا لفور</a>
            <span>/</span>
            <span class="text-[#18181B] dark:text-zinc-100 font-bold">تمام اقامتگاه‌ها و فایل‌ها</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#18181B] dark:text-zinc-100 tracking-tight">
            لیست کامل اقامتگاه‌ها و املاک
        </h1>
    </div>

    {{-- Filters Card --}}
    <form action="{{ route('properties.index') }}" method="GET" class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
            
            {{-- Text Search --}}
            <div class="lg:col-span-2">
                <label class="block text-[11px] text-zinc-400 font-medium mb-1">جستجو در عنوان یا محله</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="لفور، کلبه، استخر..." class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none">
            </div>

            {{-- Province & City Destination --}}
            @php
                $destinationsList = $availableDestinations ?? [];
            @endphp
            <div>
                <label class="block text-[11px] text-zinc-400 font-medium mb-1">استان و شهر</label>
                <select name="city" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none cursor-pointer">
                    <option value="">همه مقاصد</option>
                    @foreach($destinationsList as $destGroup)
                        <optgroup label="استان {{ $destGroup['province'] }}">
                            @foreach($destGroup['cities'] as $c)
                                <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }} ({{ $destGroup['province'] }})</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            {{-- Listing Type --}}
            <div>
                <label class="block text-[11px] text-zinc-400 font-medium mb-1">نوع معامله</label>
                <select name="listing_type" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none cursor-pointer">
                    <option value="">همه معاملات</option>
                    <option value="daily_rental" {{ request('listing_type') == 'daily_rental' ? 'selected' : '' }}>اجاره روزانه اقامتگاه</option>
                    <option value="rent" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>رهن و اجاره سالانه</option>
                    <option value="sale" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>خرید و فروش</option>
                </select>
            </div>

            {{-- Property Type --}}
            <div>
                <label class="block text-[11px] text-zinc-400 font-medium mb-1">نوع ملک</label>
                <select name="property_type" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none cursor-pointer">
                    <option value="">همه انواع</option>
                    <option value="villa" {{ request('property_type') == 'villa' ? 'selected' : '' }}>ویلا و کلبه</option>
                    <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>آپارتمان و سوئیت</option>
                    <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>زمین و باغ</option>
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-[#007A5E] hover:bg-[#005F48] text-white py-2.5 rounded-xl font-bold text-xs transition-colors shadow-sm">
                    اعمال فیلتر
                </button>
                <a href="{{ route('properties.index') }}" class="px-3 py-2.5 rounded-xl border border-[#ECECEE] dark:border-zinc-700 bg-[#F7F8F9] dark:bg-zinc-800 hover:bg-[#ECECEE] text-zinc-600 dark:text-zinc-300 font-bold text-xs transition-colors">
                    حذف
                </a>
            </div>

        </div>
    </form>

    {{-- Strictly 2 Cards Per Row Grid (Consistent with mrvilalafoor-1.html) --}}
    <div class="grid grid-cols-2 gap-3.5 sm:gap-8">
        @forelse($properties as $property)
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
                $priceDisplay = '';
                if ($property->listing_type === 'daily_rental') {
                    $rate = $rentalConfig?->price_per_night ?: ($property->rent_price ?: $property->price);
                    $priceDisplay = ($rate > 0 ? number_format($rate) : 'توافقی') . ' تومان / شب';
                } elseif ($property->listing_type === 'rent') {
                    $priceDisplay = 'رهن: ' . ($property->deposit_price > 0 ? number_format($property->deposit_price) : 'توافقی') . ' تومان';
                } else {
                    $priceDisplay = ($property->price > 0 ? number_format($property->price) : 'توافقی') . ' تومان';
                }
            @endphp

            <article class="group select-none bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800/80 hover:border-zinc-300 dark:hover:border-zinc-700 rounded-2xl p-2.5 sm:p-4 transition-all duration-200 shadow-[0_2px_8px_rgba(0,0,0,0.03)] dark:shadow-[0_2px_8px_rgba(0,0,0,0.2)] flex flex-col justify-between h-full">
                <a href="{{ route('properties.show', $property->slug ?: $property->code) }}" class="flex flex-col justify-between h-full">
                    <div>
                        <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-[#F7F8F9] dark:bg-zinc-800 mb-2.5 sm:mb-3.5 border border-[#ECECEE]/60 dark:border-zinc-800/60">
                            <img src="{{ $coverUrl }}" alt="{{ $property->title }}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300">
                            
                            @if($rentalConfig && $rentalConfig->instant_booking)
                                <div class="absolute top-2.5 right-2.5 bg-[#007A5E] text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                    رزرو آنی
                                </div>
                            @endif

                            <div class="absolute bottom-2.5 right-2.5 bg-black/60 backdrop-blur-sm text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 rounded-lg">
                                کد: {{ $property->code }}
                            </div>
                        </div>

                        <div class="flex items-start justify-between gap-1.5 px-0.5">
                            <h3 class="card-title-fixed-height text-xs sm:text-sm font-bold text-[#18181B] dark:text-zinc-100 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors flex-1">
                                {{ $property->title }}
                            </h3>
                            <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold text-[#18181B] dark:text-zinc-100 shrink-0 mt-0.5">
                                <span class="text-amber-400 dark:text-amber-400 text-xs">★</span>
                                <span>۴.۹</span>
                            </span>
                        </div>

                        <p class="text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-400 mt-0.5 sm:mt-1 truncate px-0.5">
                            {{ $property->city ?? 'لفور' }} • تا {{ $rentalConfig?->max_guests ?? ($property->max_guests ?? 4) }} نفر
                        </p>
                    </div>

                    <div class="mt-2.5 pt-2 border-t border-[#ECECEE]/70 dark:border-zinc-800/70 text-xs sm:text-sm font-black text-[#18181B] dark:text-zinc-100 truncate px-0.5">
                        {{ $priceDisplay }}
                    </div>
                </a>
            </article>
        @empty
            <div class="col-span-2 py-20 text-center">
                <p class="text-sm font-bold text-[#18181B] dark:text-zinc-200 mb-2">فایلی با فیلترهای مشخص‌شده یافت نشد.</p>
                <a href="{{ route('properties.index') }}" class="mt-2 inline-block px-5 py-2.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800 hover:bg-[#ECECEE] text-xs font-bold text-[#18181B] dark:text-zinc-200 transition-colors">
                    نمایش همه اقامتگاه‌ها
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($properties->hasPages())
        <div class="pt-6 border-t border-[#ECECEE] dark:border-zinc-800">
            {{ $properties->links() }}
        </div>
    @endif

</div>
@endsection
