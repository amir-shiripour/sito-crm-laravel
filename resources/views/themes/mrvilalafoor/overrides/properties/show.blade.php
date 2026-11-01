@extends('layouts.web')

@section('title', $property->title ?? 'جزئیات ملک')

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
    $canViewCover = $checkVisibility('cover_image', false);
    $canViewGallery = $checkVisibility('gallery_images', false);

    $coverUrl = ($canViewCover && $property->cover_image) 
        ? (str_starts_with($property->cover_image, 'http') ? $property->cover_image : asset('storage/' . $property->cover_image))
        : asset('images/property-placeholder.svg');

    $agent = $property->agent ?? $property->creator;
    $agentPhone = $agent->mobile ?? $agent->phone ?? null;

    $features = $property->attributeValues->filter(function($attr) {
        return $attr->attribute && $attr->attribute->section === 'features' && ($attr->value == '1' || $attr->value === true);
    });

    $detailAttributes = $property->attributeValues->filter(function($attr) {
        return $attr->attribute && $attr->attribute->section !== 'features' && $attr->value !== null && $attr->value !== '';
    });
@endphp

<div class="max-w-[1360px] mx-auto px-4 sm:px-8 py-8 w-full font-sans space-y-8">
    
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs font-medium text-zinc-400">
        <a href="{{ url('/') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">مستر ویلا لفور</a>
        <span>/</span>
        <a href="{{ route('properties.index') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">املاک</a>
        <span>/</span>
        <span class="text-[#18181B] dark:text-zinc-100 font-bold line-clamp-1">{{ $property->title }}</span>
    </div>

    {{-- Title and Badges --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-3 py-1 rounded-full bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 text-xs font-bold border border-[#007A5E]/20">
                    کد ملک: {{ $property->code }}
                </span>
                <span class="px-3 py-1 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold">
                    {{ match($property->listing_type) { 'sale' => 'فروش', 'rent' => 'رهن و اجاره', 'presale' => 'پیش‌فروش', default => $property->listing_type } }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#18181B] dark:text-zinc-100 tracking-tight">
                {{ $property->title }}
            </h1>
            <p class="text-xs text-zinc-400 mt-1">{{ $property->city ?? 'مازندران، سوادکوه، لفور' }}</p>
        </div>

        <div class="text-left">
            @if($canViewPrice)
                @if($property->listing_type === 'rent')
                    <div class="text-sm font-bold text-zinc-500">رهن: {{ $property->deposit_price > 0 ? number_format($property->deposit_price) . ' تومان' : 'توافقی' }}</div>
                    <div class="text-base font-black text-[#007A5E] dark:text-emerald-400">اجاره: {{ $property->rent_price > 0 ? number_format($property->rent_price) . ' تومان' : 'توافقی' }}</div>
                @else
                    <div class="text-xl font-black text-[#007A5E] dark:text-emerald-400">
                        {{ $property->price > 0 ? number_format($property->price) . ' تومان' : 'قیمت توافقی' }}
                    </div>
                @endif
            @else
                <span class="px-3 py-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-xs font-bold text-zinc-600">تماس جهت استعلام قیمت</span>
            @endif
        </div>
    </div>

    {{-- Main Cover Image --}}
    <div class="rounded-3xl overflow-hidden aspect-[16/9] border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-900 shadow-md">
        <img src="{{ $coverUrl }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
    </div>

    {{-- Two Column Info --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <div class="lg:col-span-8 space-y-8">
            
            {{-- Description --}}
            <div class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-sm space-y-3">
                <h3 class="text-base font-black text-[#18181B] dark:text-zinc-100">توضیحات و مشخصات ملک</h3>
                <div class="text-xs leading-relaxed text-zinc-600 dark:text-zinc-300 whitespace-pre-line">
                    {{ $property->description ?: 'توضیحات کامل این ملک در حال تکمیل می‌باشد.' }}
                </div>
            </div>

            {{-- Detail Specs --}}
            @if($detailAttributes->isNotEmpty())
                <div class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-black text-[#18181B] dark:text-zinc-100">مشخصات فنی</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                        @foreach($detailAttributes as $detail)
                            <div class="p-3 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700">
                                <span class="text-zinc-400 block text-[11px]">{{ $detail->attribute->name }}</span>
                                <strong class="text-[#18181B] dark:text-zinc-200 block font-bold mt-0.5">{{ $detail->value }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Features --}}
            @if($features->isNotEmpty())
                <div class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-black text-[#18181B] dark:text-zinc-100">ویژگی‌ها و امکانات</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach($features as $feat)
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 text-xs font-bold text-[#18181B] dark:text-zinc-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007A5E]"></span>
                                <span>{{ $feat->attribute->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- Agent / Contact Card --}}
        <div class="lg:col-span-4 sticky top-24">
            <div class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-sm space-y-5 text-center">
                <div class="w-14 h-14 rounded-full bg-[#ECFDF5] dark:bg-emerald-950 text-[#007A5E] dark:text-emerald-400 font-black text-lg mx-auto flex items-center justify-center">
                    {{ mb_substr($agent->name ?? 'ک', 0, 1) }}
                </div>
                <div>
                    <h4 class="text-sm font-black text-[#18181B] dark:text-zinc-100">{{ $agent->name ?? 'کارشناس مستر ویلا لفور' }}</h4>
                    <span class="text-[11px] text-zinc-400 mt-0.5 block">پاسخگوی سوالات شما درباره این ملک</span>
                </div>

                @if($agentPhone)
                    <a href="tel:{{ $agentPhone }}" class="w-full py-3.5 rounded-2xl bg-[#007A5E] hover:bg-[#005F48] text-white text-xs font-bold block transition-colors shadow-sm">
                        تماس با کارشناس فایل
                    </a>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
