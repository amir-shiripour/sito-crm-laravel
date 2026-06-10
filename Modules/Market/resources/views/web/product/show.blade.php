@extends('layouts.web')

@section('title', $product->title)

@section('content')
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 w-full py-6 lg:py-10">

        {{-- Breadcrumb --}}
        <div class="mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <nav class="flex text-xs sm:text-sm text-gray-500 dark:text-gray-400 overflow-x-auto whitespace-nowrap custom-scrollbar pb-2 font-medium">
                <ol class="flex items-center space-x-2 space-x-reverse">
                    <li><a href="{{ url('/') }}" class="hover:text-{{ $t['name'] ?? 'indigo' }}-600 dark:hover:text-{{ $t['name'] ?? 'indigo' }}-400 transition-colors cursor-pointer">خانه</a></li>
                    <li><span class="mx-1.5 opacity-50">/</span></li>
                    <li><a href="{{ route('market.public.index') }}" class="hover:text-{{ $t['name'] ?? 'indigo' }}-600 dark:hover:text-{{ $t['name'] ?? 'indigo' }}-400 transition-colors cursor-pointer">فروشگاه</a></li>
                    @php
                        $breadcrumbsPath = [];
                        if (isset($separateCategoryEnabled) && $separateCategoryEnabled) {
                            $displayCategories = $product->displayCategories;
                            if ($displayCategories && $displayCategories->count() > 0) {
                                $parentIds = $displayCategories->pluck('parent_id')->filter()->toArray();
                                $displayCategory = $displayCategories->reject(function($cat) use ($parentIds) {
                                    return in_array($cat->id, $parentIds);
                                })->first();
                                if (!$displayCategory) {
                                    $displayCategory = $displayCategories->first();
                                }
                                $curr = $displayCategory;
                            } else {
                                $curr = null;
                            }
                        } else {
                            $curr = $product->category;
                        }

                        while ($curr) {
                            array_unshift($breadcrumbsPath, $curr);
                            $curr = $curr->parent;
                        }
                    @endphp
                    @foreach($breadcrumbsPath as $cat)
                        <li><span class="mx-1.5 opacity-50">/</span></li>
                        <li><a href="{{ route('market.public.category.show', $cat->slug) }}" class="hover:text-{{ $t['name'] ?? 'indigo' }}-600 dark:hover:text-{{ $t['name'] ?? 'indigo' }}-400 transition-colors cursor-pointer">{{ $cat->name }}</a></li>
                    @endforeach
                    <li><span class="mx-1.5 opacity-50">/</span></li>
                    <li class="font-bold text-gray-800 dark:text-gray-200 truncate max-w-[200px] sm:max-w-xs" title="{{ $product->title }}">{{ $product->title }}</li>
                </ol>
            </nav>
        </div>

        {{-- پردازش داده‌ها برای جاوااسکریپت و منطق انتخاب هوشمند --}}
        @php
            $btnActiveClasses = 'cursor-pointer ' .
                                ($t['border'] ?? 'border-indigo-600') . ' ' .
                                ($t['bg_light'] ?? 'bg-indigo-50') . ' ' .
                                ($t['text'] ?? 'text-indigo-600') . ' ' .
                                ($t['bg_light_dark'] ?? 'dark:bg-gray-800') . ' ' .
                                ($t['text_dark'] ?? 'dark:text-white');

            $btnInactiveClasses = 'cursor-pointer border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800/50 ' .
                                  ($t['border_hover'] ?? 'hover:border-indigo-500/50') . ' ' .
                                  'dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400';

            $requestedVariantId = request()->query('variant');
            $jsVariants = [];
            $availableAttributes = [];
            $selectedVariant = null;
            $lowestPrice = null;

            foreach ($product->variants as $variant) {
                $variantStock = 0;
                $variantMinPrice = null;
                $variantOriginalPrice = null;
                $activeVp = null;

                foreach ($variant->vendorProducts as $vp) {
                    if ($vp->status === 'published' && $vp->stock > 0) {
                        $variantStock += $vp->stock;
                        $price = $vp->discount_price > 0 ? $vp->discount_price : $vp->price;

                        if ($variantMinPrice === null || $price < $variantMinPrice) {
                            $variantMinPrice = $price;
                            $variantOriginalPrice = $vp->price;
                            $activeVp = $vp;
                        }
                    }
                }

                $variant->calculated_stock = $variantStock;
                $variant->calculated_min_price = $variantMinPrice;
                $variant->best_vendor_product = $activeVp;
                $attrs = is_array($variant->variant_attributes) ? $variant->variant_attributes : [];

                foreach ($attrs as $key => $val) {
                    if (in_array($key, ['name', 'نام']) && $val === 'استاندارد') {
                        continue;
                    }
                    if (!isset($availableAttributes[$key])) $availableAttributes[$key] = [];

                    if (str_starts_with($val, 'هر ')) {
                        $dictAttr = $attributeDictionary->firstWhere('name', $key);
                        if ($dictAttr) {
                            foreach ($dictAttr->values as $dictVal) {
                                if (!in_array($dictVal->value, $availableAttributes[$key])) {
                                    $availableAttributes[$key][] = $dictVal->value;
                                }
                            }
                        }
                    } else {
                        if (!in_array($val, $availableAttributes[$key])) {
                             $availableAttributes[$key][] = $val;
                        }
                    }
                }

                $jsVariants[] = [
                    'id' => $variant->id,
                    'vendor_product_id' => $activeVp ? $activeVp->id : null, // Added for Cart Logic
                    'attributes' => $attrs,
                    'stock' => $variantStock,
                    'price' => $variantOriginalPrice ?? 0,
                    'discount_price' => $activeVp ? $activeVp->discount_price : 0,
                    'final_price' => $variantMinPrice ?? 0,
                    'vendor_name' => $activeVp->vendor->store_name ?? 'نامشخص',
                    'vendor_logo' => ($activeVp && $activeVp->vendor->logo) ? asset('storage/' . $activeVp->vendor->logo) : null,
                    'discount_end_date' => ($activeVp && $activeVp->discount_price > 0 && $activeVp->discount_end_date) ? $activeVp->discount_end_date->toIso8601String() : null,
                    'discount_stock' => $activeVp ? $activeVp->discount_stock : null,
                    'max_discount_purchase_qty' => $activeVp ? $activeVp->max_discount_purchase_qty : null,
                ];

                if ($requestedVariantId && $variant->id == $requestedVariantId) {
                    $selectedVariant = $variant;
                }

                if (!$requestedVariantId && $variantStock > 0) {
                    if ($lowestPrice === null || $variantMinPrice < $lowestPrice) {
                        $lowestPrice = $variantMinPrice;
                        $selectedVariant = $variant;
                    }
                }
            }

            if (!$selectedVariant && $product->variants->count() > 0) {
                $selectedVariant = $product->variants->first();
            }

            $initialSelectedAttributes = [];
            if($selectedVariant && is_array($selectedVariant->variant_attributes)) {
                 foreach($selectedVariant->variant_attributes as $k => $v) {
                      if(str_starts_with($v, 'هر ') && isset($availableAttributes[$k][0])) {
                           $initialSelectedAttributes[$k] = $availableAttributes[$k][0];
                      } else {
                           $initialSelectedAttributes[$k] = $v;
                      }
                 }
            }
        @endphp

        {{-- افکت پس‌زمینه محو --}}
        <div class="absolute top-0 right-0 w-full h-[500px] overflow-hidden pointer-events-none z-[-1]">
            <div class="absolute -top-32 -right-32 w-96 h-96 {{ $t['blob_1'] ?? 'bg-indigo-500/10' }} blur-3xl rounded-full opacity-40"></div>
        </div>

        {{-- ساختار اصلی صفحه --}}
        <div class="flex flex-col lg:flex-row gap-8 xl:gap-12 animate-in fade-in slide-in-from-bottom-8 duration-700 relative z-10 mb-16">

            {{-- 1. Image Gallery --}}
            <div class="w-full lg:w-5/12 xl:w-4/12 flex-shrink-0 flex flex-col gap-4">
                <div class="sticky top-24">
                    <div onclick="openLightbox(document.getElementById('main-product-image').src)" class="relative w-full aspect-square bg-gray-50 dark:bg-gray-800/40 rounded-3xl border border-gray-100 dark:border-gray-800 flex items-center justify-center p-8 group overflow-hidden cursor-zoom-in transition-all hover:shadow-lg dark:hover:shadow-none hover:shadow-gray-200/50">
                        @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->title }}" class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal transition-transform duration-700 group-hover:scale-105" id="main-product-image">
                        @else
                            <svg class="w-32 h-32 opacity-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        @endif

                        <div class="absolute bottom-4 left-4 w-10 h-10 bg-white/90 dark:bg-gray-900/90 backdrop-blur rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                        </div>
                    </div>

                    @if(!empty($product->gallery_images) && is_array($product->gallery_images))
                        <div class="flex gap-3 overflow-x-auto custom-scrollbar pb-2 mt-4">
                            @if($product->main_image)
                                <button type="button" onclick="changeMainImage('{{ asset('storage/' . $product->main_image) }}', this)" class="gallery-thumb cursor-pointer w-20 h-20 flex-shrink-0 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border-2 {{ $t['border'] ?? 'border-indigo-600' }} p-2 overflow-hidden transition-all opacity-100">
                                    <img src="{{ asset('storage/' . $product->main_image) }}" class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal">
                                </button>
                            @endif
                            @foreach($product->gallery_images as $img)
                                <button type="button" onclick="changeMainImage('{{ asset('storage/' . $img) }}', this)" class="gallery-thumb cursor-pointer w-20 h-20 flex-shrink-0 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-200 dark:border-gray-700 {{ $t['border_hover'] ?? 'hover:border-indigo-500/50' }} p-2 overflow-hidden transition-all opacity-70 hover:opacity-100">
                                    <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- بخش میانی و سمت چپ: دربرگیرنده کل محتوا --}}
            <div class="w-full lg:w-7/12 xl:w-8/12 flex flex-col">

                {{-- هدر محصول --}}
                <div class="mb-6 w-full">
                    <div class="flex items-center gap-3 mb-4">
                        @if(isset($showBrandOnProductPage) && $showBrandOnProductPage && $product->brand)
                            <a href="{{ route('market.public.category', ['brands' => [$product->brand->id]]) }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-{{ $t['name'] ?? 'indigo' }}-600 dark:hover:text-{{ $t['name'] ?? 'indigo' }}-400 transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                {{ $product->brand->name }}
                            </a>
                            <span class="text-gray-300 dark:text-gray-700">•</span>
                        @endif

                        @if($product->enable_reviews && $product->approved_reviews_count > 0)
                            <div class="flex items-center gap-1 text-sm font-bold text-amber-500">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span class="pt-0.5">{{ number_format($product->average_rating, 1) }}</span>
                                <span class="text-gray-400 font-normal pr-1 text-xs">({{ $product->approved_reviews_count }} نظر)</span>
                            </div>
                        @endif
                    </div>

                    <h1 class="text-xl lg:text-2xl font-bold text-gray-900 dark:text-white leading-relaxed">
                        {{ $product->title }}
                    </h1>
                </div>

                <div class="w-full h-px bg-gray-100 dark:bg-gray-800 mb-8"></div>

                {{-- اسپلیت شدن محتوا --}}
                <div class="flex flex-col xl:flex-row gap-8 xl:gap-10">

                    {{-- 2. Content Box (Center / Info) --}}
                    <div class="w-full xl:w-7/12 flex flex-col">

                        {{-- انتخابگر ویژگی‌های پویا --}}
                        @if(!empty($availableAttributes))
                            <div class="mb-10">
                                <div class="space-y-6">
                                    @foreach($availableAttributes as $attrKey => $attrValues)
                                        @php
                                            $safeGroupKey = md5($attrKey);
                                            // پیدا کردن مدل دیکشنری برای این گروه
                                            $dictAttr = $attributeDictionary->firstWhere('name', $attrKey);
                                            $type = $dictAttr ? $dictAttr->type : 'select';
                                            $unit = $dictAttr ? $dictAttr->unit : ''; // استخراج واحد اندازه‌گیری
                                        @endphp
                                        <div>
                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $attrKey }}:</span>
                                                <span id="selected-label-{{ $safeGroupKey }}" class="text-sm font-bold {{ $t['text'] ?? 'text-indigo-600' }} {{ $t['text_dark'] ?? 'dark:text-indigo-400' }}">
                                                    {{ $initialSelectedAttributes[$attrKey] ?? '' }} @if($unit)<span class="text-xs opacity-75">{{ $unit }}</span>@endif
                                                </span>
                                            </div>

                                            <div class="flex flex-wrap gap-3">
                                                @foreach($attrValues as $val)
                                                    @php
                                                        $isActive = isset($initialSelectedAttributes[$attrKey]) && $initialSelectedAttributes[$attrKey] == $val;
                                                        // پیدا کردن گرافیک مربوطه در مقادیر دیکشنری
                                                        $metaValue = null;
                                                        if($dictAttr) {
                                                            $dictVal = $dictAttr->values->firstWhere('value', $val);
                                                            if($dictVal) {
                                                                $metaValue = $dictVal->meta_value;
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="relative group flex-shrink-0">
                                                        <button
                                                            type="button"
                                                            onclick="updateVariantSelection(this)"
                                                            class="relative transition-all flex items-center justify-center overflow-hidden outline-none border-2
                                                            {{ $isActive ? $btnActiveClasses : $btnInactiveClasses }}
                                                            {{ $type === 'color' ? 'w-10 h-10 p-1 rounded-full' : ($type === 'image' ? 'w-14 h-14 p-1 rounded-xl' : 'px-5 py-2.5 text-sm font-bold rounded-xl') }}"
                                                            data-key="{{ $attrKey }}"
                                                            data-val="{{ $val }}"
                                                            data-group="{{ $safeGroupKey }}"
                                                            data-unit="{{ $unit }}">

                                                            {{-- ساختار گرافیکی بر اساس نوع --}}
                                                            @if($type === 'color' || $type === 'image')
                                                                @if($metaValue && str_starts_with($metaValue, 'attributes/'))
                                                                    {{-- نمایش عکس برای کالر (پترن) یا تصویر --}}
                                                                    <img src="{{ Storage::url($metaValue) }}" class="w-full h-full object-cover {{ $type === 'color' ? 'rounded-full' : 'rounded-lg' }}">
                                                                @else
                                                                    @if($type === 'color')
                                                                        <span class="w-full h-full rounded-full shadow-inner" style="background-color: {{ $metaValue ?? '#ccc' }}"></span>
                                                                    @else
                                                                        <span class="text-[10px] leading-tight text-center">{{ $val }}</span>
                                                                    @endif
                                                                @endif
                                                            @else
                                                                {{ $val }}
                                                            @endif

                                                            {{-- تیک انتخاب برای حالت رنگی --}}
                                                            @if($type === 'color')
                                                                <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity pointer-events-none" style="opacity: {{ $isActive ? '1' : '0' }}">
                                                                    <div class="bg-white/80 dark:bg-black/50 rounded-full w-full h-full flex items-center justify-center">
                                                                        <svg class="w-5 h-5 {{ $t['text'] ?? 'text-indigo-600' }} {{ $t['text_dark'] ?? 'dark:text-indigo-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </button>

                                                        @if($type === 'color' || $type === 'image')
                                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs font-bold rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 pointer-events-none whitespace-nowrap transform translate-y-1 group-hover:translate-y-0">
                                                                {{ $val }}
                                                                <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-white"></div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Features List --}}
                        @if(!empty($product->attributes) && is_array($product->attributes))
                            <div class="space-y-4 mb-8 relative">
                                <div class="flex items-center justify-between mb-5">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-5 h-5 {{ $t['text'] ?? 'text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        ویژگی‌های کلیدی
                                    </h3>
                                    <button type="button" onclick="document.getElementById('product-specifications-section')?.scrollIntoView({behavior: 'smooth'}) || document.getElementById('product-description-section')?.scrollIntoView({behavior: 'smooth'})" class="cursor-pointer text-xs font-bold {{ $t['text'] ?? 'text-indigo-600' }} {{ $t['text_dark'] ?? 'dark:text-indigo-400' }} flex items-center gap-1 hover:opacity-80 transition-opacity">
                                        مشاهده همه
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-2 gap-3 sm:gap-4">
                                    @foreach(array_slice($product->attributes, 0, 6) as $key => $val)
                                        <div class="bg-gray-50/80 dark:bg-gray-800/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-800 flex flex-col gap-1.5 hover:bg-gray-100 dark:hover:bg-gray-800/60 transition-colors cursor-default">
                                            <span class="text-[11px] sm:text-xs font-medium text-gray-500 dark:text-gray-400">{{ is_string($key) ? $key : 'ویژگی' }}</span>
                                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $val }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- 3. باکس خرید (Buy Box) - فقط نمایش در دسکتاپ --}}
                    <div class="hidden md:flex w-full xl:w-5/12 flex-shrink-0 flex-col">
                        <div class="sticky top-24 bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] p-6 xl:p-8 flex flex-col relative overflow-hidden">

                            <div id="buybox-available" class="block">

                                {{-- تگ جشنواره --}}
                                <div id="buybox-discount-banner" class="hidden absolute top-0 left-0 right-0 {{ $t['bg'] ?? 'bg-indigo-600' }} text-white text-[11px] sm:text-xs font-bold py-2 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    پیشنهاد شگفت‌انگیز
                                    <div id="countdown-timer-desktop" class="mr-4 flex items-center gap-1 tracking-wider" style="direction: ltr;"></div>
                                </div>
                                <div id="buybox-discount-spacer" class="hidden pt-8"></div>

                                {{-- نمایش فروشنده --}}
                                @if($showVendorInProductPage)
                                    <div id="buybox-vendor-section" class="flex items-center gap-3 pb-5 border-b border-gray-50 dark:border-gray-800/50 mb-5">
                                        <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                                            <img id="buybox-vendor-logo" src="" alt="Logo" class="w-full h-full object-cover hidden">
                                            <svg id="buybox-vendor-icon" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[11px] text-gray-400">فروشنده کالا</span>
                                            <span id="buybox-vendor-name" class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">فروشگاه</span>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-auto">
                                    <div class="flex items-end justify-between mb-5">
                                        <span class="text-sm font-medium text-gray-500 pb-1">قیمت نهایی</span>
                                        <div class="flex flex-col items-end">
                                            <div id="buybox-discount-section" class="hidden flex-col items-end mb-1">
                                                <div class="flex items-center gap-2">
                                                    <span id="buybox-discount-badge" class="px-2 py-0.5 bg-rose-500 text-white text-[11px] font-bold rounded-md">٪0</span>
                                                    <span id="buybox-old-price" class="text-sm text-gray-400 line-through decoration-gray-300 dark:decoration-gray-600 font-medium">0</span>
                                                </div>
                                            </div>
                                            <div class="text-gray-900 dark:text-white font-black text-3xl tracking-tight flex items-baseline gap-1.5">
                                                <span id="buybox-final-price">0</span>
                                                <span class="text-sm font-medium text-gray-500">تومان</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- هشدار موجودی --}}
                                    @if($showStockWarning)
                                        <div id="buybox-stock-warning" class="hidden items-center justify-between bg-rose-50 dark:bg-rose-900/10 px-4 py-3 rounded-xl border border-rose-100 dark:border-rose-800/20 mb-4">
                                            <span id="buybox-stock-text" class="text-xs font-bold text-rose-600 dark:text-rose-400">فقط X عدد در انبار باقیست</span>
                                            <span class="flex h-2.5 w-2.5 relative">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                                        </span>
                                        </div>
                                    @endif

                                    {{-- نوار پیشرفت موجودی تخفیف --}}
                                    <div id="buybox-discount-stock-progress" class="hidden my-4 space-y-2">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="font-bold text-rose-600 dark:text-rose-400">موجودی شگفت‌انگیز</span>
                                            <span id="discount-stock-label" class="font-bold"></span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                            <div id="discount-stock-bar" class="bg-rose-500 h-2.5 rounded-full transition-all" style="width: 50%"></div>
                                        </div>
                                    </div>

                                    {{-- دکمه سبد خرید با اتصال به Livewire و مدیریت تعداد --}}
                                    <div class="mb-5 relative">
                                        <input type="hidden" id="buybox-variant-id" value="">
                                        <input type="hidden" id="buybox-vendor-product-id" value="">

                                        {{-- دکمه افزودن مستقیم --}}
                                        <button type="button" onclick="submitToCart()" id="buybox-add-btn" class="cursor-pointer w-full h-14 rounded-2xl {{ $t['bg'] ?? 'bg-indigo-600' }} {{ $t['bg_hover'] ?? 'hover:bg-indigo-700' }} text-white font-bold text-base flex items-center justify-center gap-2 transition-all transform active:scale-95 shadow-lg {{ $t['shadow'] ?? 'shadow-indigo-500/30' }}">
                                            افزودن به سبد خرید
                                        </button>

                                        {{-- کنترلر مثبت و منفی دسکتاپ --}}
                                        <div id="buybox-quantity-selector" class="hidden items-center justify-between w-full h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-opacity duration-200">
                                            <button type="button" onclick="incrementCart()" class="px-5 py-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                            </button>
                                            <span id="buybox-quantity-val" class="text-xl font-bold text-gray-900 dark:text-white">0</span>
                                            <button type="button" onclick="decrementCart()" class="px-5 py-2 text-gray-600 dark:text-gray-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- مزایای خرید --}}
                                    <div class="space-y-3 pt-4 border-t border-gray-50 dark:border-gray-800/50">
                                        <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5 text-teal-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            موجود در انبار
                                        </div>
                                        <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                            تضمین اصالت و سلامت
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- حالت ناموجود --}}
                            <div id="buybox-out-of-stock" class="hidden text-center py-6">
                                <div class="w-14 h-14 rounded-full bg-gray-50 dark:bg-gray-800 mx-auto flex items-center justify-center text-gray-400 mb-4">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                </div>
                                <h4 class="font-bold text-gray-900 dark:text-white mb-2">ناموجود</h4>
                                <p class="text-xs text-gray-500 mb-6 leading-relaxed">این ترکیب از محصول در حال حاضر در انبار هیچ فروشنده‌ای موجود نیست.</p>
                                <button class="cursor-pointer w-full h-12 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm flex items-center justify-center gap-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    موجود شد خبرم کن
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $hasDescription = !empty(trim(strip_tags($product->description)));
            $hasAttributes = !empty($product->attributes) && is_array($product->attributes);
        @endphp

        @if($hasDescription || $hasAttributes)
        <div class="mt-12 border-t border-gray-100 dark:border-gray-800 pt-6">
            <!-- Dynamic Sticky Tab Navigation -->
            <div id="product-sticky-nav-container" class="sticky z-40 bg-white/90 dark:bg-gray-950/90 backdrop-blur-md border-b border-gray-100 dark:border-gray-800/80 -mx-4 px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0 transition-all">
                <div class="max-w-[1440px] mx-auto">
                    <nav class="flex gap-8 overflow-x-auto whitespace-nowrap custom-scrollbar py-4" id="product-sticky-nav">
                        @if($hasDescription)
                            <a href="#product-description-section" class="scrollspy-link pb-3 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-{{ $t['name'] ?? 'indigo' }}-600 dark:hover:text-{{ $t['name'] ?? 'indigo' }}-400 border-b-2 border-transparent transition-all">معرفی</a>
                        @endif
                        @if($hasAttributes)
                            <a href="#product-specifications-section" class="scrollspy-link pb-3 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-{{ $t['name'] ?? 'indigo' }}-600 dark:hover:text-{{ $t['name'] ?? 'indigo' }}-400 border-b-2 border-transparent transition-all">مشخصات</a>
                        @endif
                        @if($product->enable_reviews)
                            <a href="#product-reviews-section" class="scrollspy-link pb-3 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-{{ $t['name'] ?? 'indigo' }}-600 dark:hover:text-{{ $t['name'] ?? 'indigo' }}-400 border-b-2 border-transparent transition-all">دیدگاه‌ها</a>
                        @endif
                        <a href="#product-questions-section" class="scrollspy-link pb-3 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-{{ $t['name'] ?? 'indigo' }}-600 dark:hover:text-{{ $t['name'] ?? 'indigo' }}-400 border-b-2 border-transparent transition-all">پرسش‌ها</a>
                    </nav>
                </div>
            </div>

            <!-- Main Layout Grid: Right content sections, Left sticky BuyBox sidebar -->
            <div class="flex flex-col lg:flex-row gap-8 xl:gap-12 mt-8">
                
                <!-- Right Column: Product Info Content Sections -->
                <div class="w-full lg:w-8/12 xl:w-9/12 flex flex-col gap-12">
                    
                    {{-- Description Section --}}
                    @if($hasDescription)
                        <div id="product-description-section" class="scroll-mt-36">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-6 bg-{{ $t['name'] ?? 'indigo' }}-600 rounded-full"></span>
                                معرفی و مشخصات کامل
                            </h2>
                            <div class="prose prose-sm sm:prose-base prose-gray dark:prose-invert max-w-none leading-loose text-justify text-gray-600 dark:text-gray-300">
                                {!! $product->description !!}
                            </div>
                        </div>
                    @endif

                    {{-- Specifications Section --}}
                    @if($hasAttributes)
                        <div id="product-specifications-section" class="scroll-mt-36">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-6 bg-{{ $t['name'] ?? 'indigo' }}-600 rounded-full"></span>
                                مشخصات کالا
                            </h2>
                            <div class="bg-white dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                <table class="w-full text-right border-collapse">
                                    <tbody>
                                        @foreach($product->attributes as $key => $val)
                                            <tr class="border-b border-gray-50 dark:border-gray-800/50 last:border-0 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                                <td class="w-1/3 py-4 px-6 text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-800/20">
                                                    {{ is_string($key) ? $key : 'ویژگی' }}
                                                </td>
                                                <td class="w-2/3 py-4 px-6 text-sm font-bold text-gray-800 dark:text-gray-200">
                                                    {{ $val }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Reviews Section (دیدگاه‌ها) --}}
                    @if($product->enable_reviews)
                        @livewire('market::web.product-reviews', ['product' => $product])
                    @endif

                    {{-- Questions Section (پرسش‌ها) --}}
                    <div id="product-questions-section" class="scroll-mt-36">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-{{ $t['name'] ?? 'indigo' }}-600 rounded-full"></span>
                            پرسش و پاسخ
                        </h2>
                        <div class="bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 md:p-8 space-y-6">
                            <div class="flex items-start gap-3 border-b border-gray-100 dark:border-gray-800/60 pb-6">
                                <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black shrink-0">پرسش</span>
                                <div class="space-y-2">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">آیا این محصول دارای گارانتی معتبر شرکتی می‌باشد؟</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">پاسخ: بله، تمامی نسخه‌های ارائه شده توسط فروشندگان مجاز در سامانه دارای ۱۸ ماه گارانتی رسمی می‌باشند.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black shrink-0">پرسش</span>
                                <div class="space-y-2">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">مدت زمان ارسال کالا چقدر است؟</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">پاسخ: ارسال تهران در کمتر از ۲۴ ساعت و شهرستان‌ها بین ۴۸ تا ۷۲ ساعت کاری انجام می‌پذیرد.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Left Column: Sticky Mini BuyBox Sidebar -->
                <div class="hidden lg:block lg:w-4/12 xl:w-3/12 flex-shrink-0">
                    <div id="mini-buybox-container" class="sticky top-32 bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] flex flex-col relative overflow-hidden transition-all duration-300">
                        
                        {{-- تگ جشنواره در مینی بای‌باکس --}}
                        <div id="mini-buybox-discount-banner" class="hidden top-0 left-0 right-0 {{ $t['bg'] ?? 'bg-indigo-600' }} text-white text-[10px] font-bold py-2 flex items-center justify-center gap-2 z-10 rounded-t-3xl">
                            <svg class="w-3.5 h-3.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>پیشنهاد شگفت‌انگیز</span>
                            <div id="mini-countdown-timer" class="mr-1 flex items-center gap-0.5 tracking-wider text-[10px]" style="direction: ltr;"></div>
                        </div>

                        <div class="p-5 flex flex-col gap-4">

                            {{-- هدر: تصویر + عنوان محصول --}}
                            <div class="flex gap-3 items-start">
                                <div class="w-14 h-14 bg-gray-50 dark:bg-gray-800/60 rounded-2xl flex items-center justify-center p-1.5 shrink-0 border border-gray-100 dark:border-gray-800">
                                    @if($product->main_image)
                                        <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->title }}" class="w-full h-full object-contain">
                                    @endif
                                </div>
                                <div class="flex flex-col min-w-0 pt-0.5">
                                    <h3 class="text-xs font-bold text-gray-900 dark:text-white leading-tight line-clamp-2" title="{{ $product->title }}">{{ $product->title }}</h3>
                                    {{-- نمایش گرافیکی تنوع انتخاب شده --}}
                                    <div id="mini-buybox-selected-variant" class="flex flex-wrap gap-1 mt-2"></div>
                                </div>
                            </div>

                            {{-- نمایش فروشنده در مینی بای‌باکس --}}
                            @if($showVendorInProductPage)
                                <div id="mini-buybox-vendor-section" class="flex items-center gap-2 py-3 px-3 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                                    <div class="w-7 h-7 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-sm">
                                        <img id="mini-buybox-vendor-logo" src="" alt="Logo" class="w-full h-full object-cover hidden">
                                        <svg id="mini-buybox-vendor-icon" class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    </div>
                                    <span id="mini-buybox-vendor-name" class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate">فروشگاه</span>
                                </div>
                            @endif

                            {{-- Price and Buy details --}}
                            <div id="mini-buybox-available" class="flex flex-col gap-3">
                                
                                {{-- قیمت --}}
                                <div class="flex items-end justify-between">
                                    <span class="text-[10px] text-gray-400 pb-0.5">قیمت نهایی</span>
                                    <div class="flex flex-col items-end">
                                        <div id="mini-buybox-discount-section" class="hidden flex-col items-end mb-0.5">
                                            <div class="flex items-center gap-1.5">
                                                <span id="mini-buybox-discount-badge" class="px-1.5 py-0.5 bg-rose-500 text-white text-[9px] font-bold rounded-md">٪0</span>
                                                <span id="mini-buybox-old-price" class="text-xs text-gray-400 line-through font-medium">0</span>
                                            </div>
                                        </div>
                                        <div class="text-gray-900 dark:text-white font-black text-xl tracking-tight flex items-baseline gap-1">
                                            <span id="mini-buybox-final-price">0</span>
                                            <span class="text-[10px] font-medium text-gray-400">تومان</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- هشدار موجودی در مینی بای‌باکس --}}
                                @if($showStockWarning)
                                    <div id="mini-buybox-stock-warning" class="hidden items-center justify-between bg-rose-50 dark:bg-rose-900/10 px-3 py-2 rounded-xl border border-rose-100 dark:border-rose-800/20">
                                        <span id="mini-buybox-stock-text" class="text-[10px] font-bold text-rose-600 dark:text-rose-400">فقط X عدد در انبار باقیست</span>
                                        <span class="flex h-2 w-2 relative">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                        </span>
                                    </div>
                                @endif

                                {{-- نوار پیشرفت موجودی تخفیف در مینی بای‌باکس --}}
                                <div id="mini-buybox-discount-stock-progress" class="hidden space-y-1.5">
                                    <div class="flex justify-between items-center text-[10px]">
                                        <span class="font-bold text-rose-600 dark:text-rose-400">موجودی شگفت‌انگیز</span>
                                        <span id="mini-discount-stock-label" class="font-bold"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                        <div id="mini-discount-stock-bar" class="bg-rose-500 h-1.5 rounded-full transition-all" style="width: 50%"></div>
                                    </div>
                                </div>

                                {{-- دکمه --}}
                                <div>
                                    <button type="button" onclick="submitToCart()" id="mini-buybox-add-btn" class="cursor-pointer w-full h-11 rounded-2xl {{ $t['bg'] ?? 'bg-indigo-600' }} {{ $t['bg_hover'] ?? 'hover:bg-indigo-700' }} text-white font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-md {{ $t['shadow'] ?? 'shadow-indigo-500/25' }} active:scale-95">
                                        افزودن به سبد خرید
                                    </button>

                                    <div id="mini-buybox-quantity-selector" class="hidden items-center justify-between w-full h-11 rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                        <button type="button" onclick="incrementCart()" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                        </button>
                                        <span id="mini-buybox-quantity-val" class="text-base font-bold text-gray-900 dark:text-white">0</span>
                                        <button type="button" onclick="decrementCart()" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- مزایای خرید --}}
                                <div class="space-y-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                                    <div class="flex items-center gap-2 text-[11px] text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 text-teal-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        موجود در انبار
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                        تضمین اصالت و سلامت
                                    </div>
                                </div>

                            </div>

                            {{-- حالت ناموجود --}}
                            <div id="mini-buybox-out-of-stock" class="hidden">
                                <div class="flex flex-col items-center text-center py-4 px-3 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                    <div class="w-12 h-12 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 mx-auto flex items-center justify-center text-gray-400 mb-3 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </div>
                                    <h4 class="font-bold text-sm text-gray-800 dark:text-white mb-1">ناموجود</h4>
                                    <p class="text-[10px] text-gray-500 leading-relaxed mb-4">این ترکیب در انبار موجود نیست.</p>
                                    <button class="cursor-pointer w-full h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold text-xs flex items-center justify-center gap-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                        موجود شد خبرم کن
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif

    </div>

    {{-- 💡 نوار خرید شناور موبایل (Mobile Sticky Action Bar) --}}
    <div id="mobile-buy-bar" class="md:hidden fixed bottom-0 left-0 right-0 z-[999] bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 shadow-[0_-10px_20px_rgba(0,0,0,0.05)] transition-transform duration-300 pb-safe">

        {{-- هشدار موجودی موبایل با انیمیشن اسلاید ارتفاع و اوپاسیتی --}}
        @if($showStockWarning)
            <div id="mobile-buybox-stock-warning" data-show="false" class="h-0 opacity-0 bg-rose-50 dark:bg-rose-900/10 flex items-center justify-center transition-all duration-300 overflow-hidden">
                <span id="mobile-buybox-stock-text" class="text-[11px] font-bold text-rose-600 dark:text-rose-400 flex items-center gap-1.5 whitespace-nowrap">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                    </span>
                    فقط X عدد در انبار باقیست
                </span>
            </div>
        @endif

        <div class="p-4 flex items-center justify-between gap-4">

            {{-- 💡 بخش دکمه (سمت راست) --}}
            <div class="flex-1 flex gap-2 w-full relative">
                {{-- دکمه افزودن موبایل --}}
                <button type="button" onclick="submitToCart()" id="mobile-buybox-add-btn" class="w-full h-11 rounded-xl {{ $t['bg'] ?? 'bg-indigo-600' }} text-white font-bold text-sm flex items-center justify-center gap-2 active:scale-95 transition-transform shadow-lg {{ $t['shadow'] ?? 'shadow-indigo-500/30' }}">
                    افزودن به سبد
                </button>

                {{-- کنترلر مثبت و منفی موبایل --}}
                <div id="mobile-buybox-quantity-selector" class="hidden items-center justify-between w-full h-11 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-1 transition-opacity duration-200">
                    <button type="button" onclick="incrementCart()" class="px-4 py-2 text-gray-600 dark:text-gray-300 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    </button>
                    <span id="mobile-buybox-quantity-val" class="text-lg font-bold text-gray-900 dark:text-white">0</span>
                    <button type="button" onclick="decrementCart()" class="px-4 py-2 text-gray-600 dark:text-gray-300 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                    </button>
                </div>

                {{-- دکمه موجود شد خبرم کن موبایل --}}
                <button type="button" id="mobile-buybox-notify-btn" class="hidden w-full h-11 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs items-center justify-center gap-2 active:scale-95 transition-transform">
                    موجود شد خبرم کن
                </button>
            </div>

            {{-- 💡 بخش قیمت (سمت چپ) --}}
            <div id="mobile-buybox-available" class="flex flex-col items-end min-w-[120px] text-left">
                <div id="mobile-buybox-discount-section" class="hidden items-center gap-2 mb-0.5 justify-end w-full">
                    <span id="mobile-buybox-discount-badge" class="px-1.5 py-px bg-rose-500 text-white text-[10px] font-bold rounded">٪0</span>
                    <span id="mobile-buybox-old-price" class="text-xs text-gray-400 line-through decoration-gray-300 dark:decoration-gray-600 font-medium">0</span>
                </div>
                <div class="text-gray-900 dark:text-white font-black text-xl tracking-tight flex items-center justify-end gap-1 w-full">
                    <span id="mobile-buybox-final-price">0</span>
                    <span class="text-[10px] font-medium text-gray-500 pb-0.5">تومان</span>
                </div>
            </div>

            {{-- بخش ناموجود موبایل (سمت چپ) --}}
            <div id="mobile-buybox-out-of-stock" class="hidden flex-col items-end min-w-[120px] text-left">
                <span class="text-sm font-bold text-gray-900 dark:text-white w-full text-left ml-2 font-black">ناموجود</span>
            </div>

        </div>
    </div>

    {{-- Lightbox Container --}}
    <div id="image-lightbox" class="fixed inset-0 z-[9999] bg-gray-900/95 backdrop-blur-md hidden flex-col w-full h-[100dvh] opacity-0 transition-opacity duration-300">

        {{-- Close Button --}}
        <button onclick="closeLightbox()" class="cursor-pointer absolute top-4 right-4 md:top-6 md:right-6 text-white bg-white/10 hover:bg-white/20 p-2.5 md:p-3 rounded-full backdrop-blur-md transition-colors z-50">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>

        {{-- Main Image in Lightbox --}}
        <div class="flex-1 w-full flex items-center justify-center p-4 pt-16 pb-4 min-h-0 relative z-10">
            <img id="lightbox-img" src="" class="max-w-full max-h-full object-contain rounded-2xl scale-95 transition-transform duration-300">
        </div>

        {{-- Thumbnails in Lightbox --}}
        @if(!empty($product->gallery_images) && is_array($product->gallery_images) || $product->main_image)
            <div class="w-full bg-black/50 backdrop-blur-md p-4 sm:p-6 shrink-0 relative z-20 pb-safe border-t border-white/10">
                <div class="flex flex-wrap gap-3 justify-center items-center max-w-4xl mx-auto">
                    @if($product->main_image)
                        <button onclick="changeLightboxImage('{{ asset('storage/' . $product->main_image) }}', this)" class="cursor-pointer lightbox-thumb w-14 h-14 md:w-20 md:h-20 flex-shrink-0 bg-white/5 rounded-xl border-2 border-transparent hover:border-white p-2 overflow-hidden transition-all opacity-50 hover:opacity-100">
                            <img src="{{ asset('storage/' . $product->main_image) }}" class="w-full h-full object-contain">
                        </button>
                    @endif
                    @if(!empty($product->gallery_images) && is_array($product->gallery_images))
                        @foreach($product->gallery_images as $img)
                            <button onclick="changeLightboxImage('{{ asset('storage/' . $img) }}', this)" class="cursor-pointer lightbox-thumb w-14 h-14 md:w-20 md:h-20 flex-shrink-0 bg-white/5 rounded-xl border-2 border-transparent hover:border-white p-2 overflow-hidden transition-all opacity-50 hover:opacity-100">
                                <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-contain">
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- فراخوانی مخفی کامپوننت لایووایر مدیر سبد خرید --}}
    @livewire('market::web.cart-manager')

@endsection

@push('styles')
    <style>
        #product-description-section img,
        #product-description-section video,
        #product-description-section iframe {
            display: block;
            margin-left: auto;
            margin-right: auto;
            max-width: 100%;
        }
        #product-description-section figure {
            display: block;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        #product-sticky-nav-container {
            top: 80px !important;
        }
        @media (min-width: 768px) {
            #product-sticky-nav-container {
                top: 128px !important;
            }
            #mini-buybox-container {
                top: 200px !important;
            }
        }

        body.lightbox-open header,
        body.lightbox-open .md\:hidden.fixed.bottom-4 {
            display: none !important;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        @media (max-width: 767px) {
            /* مخفی کردن منوی شناور عمومی در این صفحه */
            div.md\:hidden.fixed.bottom-4.left-4.right-4.z-\[60\] {
                display: none !important;
            }

            /* تضمین روی فوتر ماندن */
            #mobile-buy-bar {
                z-index: 9999 !important;
            }

            /* تنظیم فاصله تا زیر نوار خرید موبایل نره */
            main { padding-bottom: 6rem !important; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lightbox = document.getElementById('image-lightbox');
            if(lightbox && lightbox.parentElement !== document.body) {
                document.body.appendChild(lightbox);
            }

            // فراخوانی اولیه برای چک کردن اسکرول و هشدار موجودی موبایل
            updateMobileWarningVisibility();

            // دریافت اولیه و یکسان‌سازی وضعیت دکمه‌ها با سبد خرید
            updateCartUI();

            // همگام‌سازی وضعیت در زمان شلیک شدن رویدادهای عمومی سبد خرید از Livewire
            window.addEventListener('cartUpdated', (event) => {
                console.log('Cart updated hook triggered.', event);

                // غیرفعال کردن لودینگ
                setCartLoadingUI(false);

                // استخراج سبد جدید با پشتیبانی از تمامی ورژن‌های لایووایر
                let updatedCart = null;
                if (event.detail) {
                    if (event.detail.cart) {
                        updatedCart = event.detail.cart;
                    } else if (Array.isArray(event.detail) && event.detail[0] && event.detail[0].cart) {
                        updatedCart = event.detail[0].cart;
                    } else if (event.detail[0]) {
                        updatedCart = event.detail[0];
                    } else {
                        updatedCart = event.detail;
                    }
                }

                if (updatedCart && typeof updatedCart === 'object') {
                    let validCartCandidate = null;

                    // بررسی برای وجود ساختار معتبر و فیلد تعداد کالاها
                    if (Object.values(updatedCart).some(item => item && typeof item.quantity !== 'undefined')) {
                        validCartCandidate = updatedCart;
                    } else {
                        // در صورت تغییر فرمت کلاینت، توپرینتهای فرعی را نیز بررسی می کنیم
                        for (const prop in updatedCart) {
                            if (updatedCart[prop] && typeof updatedCart[prop] === 'object') {
                                if (Object.values(updatedCart[prop]).some(item => item && typeof item.quantity !== 'undefined')) {
                                    validCartCandidate = updatedCart[prop];
                                    break;
                                }
                            }
                        }
                    }

                    if (validCartCandidate) {
                        cart = validCartCandidate;
                    }
                }
                updateCartUI();
            });
        });

        // منطق اسکرول پرفورمنس بالا برای نوار موبایل
        let isScrolled = false;
        let ticking = false;

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const currentScroll = window.scrollY > 150;
                    if(isScrolled !== currentScroll) {
                        isScrolled = currentScroll;
                        updateMobileWarningVisibility();
                    }
                    ticking = false;
                });
                ticking = true;
            }
        });

        // مدیریت لغزش نرم نوارهای هشدار موبایل
        function updateMobileWarningVisibility() {
            const warningEl = document.getElementById('mobile-buybox-stock-warning');
            if(!warningEl) return;

            if(warningEl.dataset.show === 'true' && isScrolled) {
                warningEl.classList.remove('h-0', 'opacity-0');
                warningEl.classList.add('h-10', 'opacity-100');
            } else {
                warningEl.classList.remove('h-10', 'opacity-100');
                warningEl.classList.add('h-0', 'opacity-0');
            }
        }

        // دیتای ورودی سمت سرور
        const productVariants = @json($jsVariants);
        let selectedAttributes = @json($initialSelectedAttributes);
        const requestedVariantId = @json($requestedVariantId);

        // تبدیل بومی آرایه خالی لاراول به شیء در جاوا اسکریپت جهت جلوگیری از اخلال در تخصیص کلیدهای رشته‌ای
        let rawCart = @json(Session::get('market_cart', []));
        let cart = (Array.isArray(rawCart) && rawCart.length === 0) ? {} : rawCart;
        let activeVariant = null;
        let isCartLoading = false;

        const uiSettings = {
            showStockWarning: @json($showStockWarning ?? true),
            showVendor: @json($showVendorInProductPage ?? true)
        };

        const activeClasses = "{{ $btnActiveClasses }}".trim().split(/\s+/).filter(Boolean);
        const inactiveClasses = "{{ $btnInactiveClasses }}".trim().split(/\s+/).filter(Boolean);

        // گرفتن کلید محصول در سبد خرید
        function getCartKey(variant) {
            let key = variant.id + '_' + (variant.vendor_product_id || '');
            
            // اگر تنوع دارای مقدار "هر " باشد، ویژگی انتخابی کاربر را به کلید اضافه می‌کنیم
            if (variant.attributes) {
                let hasAnyAttribute = false;
                let suffixParts = [];
                for (const [k, v] of Object.entries(variant.attributes)) {
                    if (v && v.startsWith('هر ')) {
                        hasAnyAttribute = true;
                        suffixParts.push(k + '=' + (selectedAttributes[k] || ''));
                    }
                }
                if (hasAnyAttribute) {
                    suffixParts.sort();
                    key += '_' + suffixParts.join('&');
                }
            }
            return key;
        }

        // تابع هوشمند برای یافتن مقدار سبد با فال‌بک‌های منعطف (جلوگیری از پرش مقدار به صفر پس از تغییر تنوع کالا)
        function getCartQuantity(variant) {
            if (!variant || !cart) return 0;

            // ۱. تلاش برای یافتن کلید دقیق: variantId_vendorProductId
            const exactKey = getCartKey(variant);
            if (cart[exactKey] && typeof cart[exactKey].quantity !== 'undefined') {
                return parseInt(cart[exactKey].quantity);
            }

            // ۲. تلاش برای یافتن بر اساس شناسه تنوع کالا به تنهایی
            if (cart[variant.id] && typeof cart[variant.id].quantity !== 'undefined') {
                return parseInt(cart[variant.id].quantity);
            }

            // ۳. تطبیق فازی شناسه تنوع کالا با کلیدهای موجود در سبد خرید
            for (const key in cart) {
                if (key === String(variant.id) || key.startsWith(variant.id + '_')) {
                    const keyParts = key.split('_');
                    const keySuffix = keyParts[2] || '';
                    const exactParts = exactKey.split('_');
                    const currentSuffix = exactParts[2] || '';
                    
                    if (keySuffix !== currentSuffix) {
                        continue;
                    }

                    if (cart[key] && typeof cart[key].quantity !== 'undefined') {
                        return parseInt(cart[key].quantity);
                    }
                }
            }

            return 0;
        }

        // مدیریت لودینگ دکمه‌ها و سلکتورها بدون بهم‌ریختگی DOM
        function setCartLoadingUI(loading) {
            isCartLoading = loading;
            const desktopAddBtn = document.getElementById('buybox-add-btn');
            const mobileAddBtn = document.getElementById('mobile-buybox-add-btn');
            const desktopSelector = document.getElementById('buybox-quantity-selector');
            const mobileSelector = document.getElementById('mobile-buybox-quantity-selector');

            const miniAddBtn = document.getElementById('mini-buybox-add-btn');
            const miniSelector = document.getElementById('mini-buybox-quantity-selector');

            if (loading) {
                if (desktopAddBtn) {
                    desktopAddBtn.disabled = true;
                    desktopAddBtn.classList.add('opacity-75', 'cursor-wait');
                    desktopAddBtn.innerHTML = `
                        <svg class="animate-spin h-5 w-5 text-white mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>درحال پردازش...</span>
                    `;
                }
                if (mobileAddBtn) {
                    mobileAddBtn.disabled = true;
                    mobileAddBtn.classList.add('opacity-75', 'cursor-wait');
                    mobileAddBtn.innerHTML = `
                        <svg class="animate-spin h-4 w-4 text-white mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>درحال افزودن...</span>
                    `;
                }
                if (desktopSelector) {
                    desktopSelector.classList.add('opacity-60', 'pointer-events-none');
                }
                if (mobileSelector) {
                    mobileSelector.classList.add('opacity-60', 'pointer-events-none');
                }
                if (miniAddBtn) {
                    miniAddBtn.disabled = true;
                    miniAddBtn.classList.add('opacity-75', 'cursor-wait');
                    miniAddBtn.innerHTML = 'درحال افزودن...';
                }
                if (miniSelector) {
                    miniSelector.classList.add('opacity-60', 'pointer-events-none');
                }
            } else {
                if (desktopAddBtn) {
                    desktopAddBtn.disabled = false;
                    desktopAddBtn.classList.remove('opacity-75', 'cursor-wait');
                    desktopAddBtn.innerHTML = `افزودن به سبد خرید`;
                }
                if (mobileAddBtn) {
                    mobileAddBtn.disabled = false;
                    mobileAddBtn.classList.remove('opacity-75', 'cursor-wait');
                    mobileAddBtn.innerHTML = `افزودن به سبد`;
                }
                if (desktopSelector) {
                    desktopSelector.classList.remove('opacity-60', 'pointer-events-none');
                }
                if (mobileSelector) {
                    mobileSelector.classList.remove('opacity-60', 'pointer-events-none');
                }
                if (miniAddBtn) {
                    miniAddBtn.disabled = false;
                    miniAddBtn.classList.remove('opacity-75', 'cursor-wait');
                    miniAddBtn.innerHTML = 'افزودن به سبد خرید';
                }
                if (miniSelector) {
                    miniSelector.classList.remove('opacity-60', 'pointer-events-none');
                }
            }
        }

        // همگام‌سازی رابط کاربری سبد خرید (بدون نیاز به رفرش)
        function updateCartUI() {
            if (!activeVariant) return;
            const qty = getCartQuantity(activeVariant);

            const desktopAddBtn = document.getElementById('buybox-add-btn');
            const desktopSelector = document.getElementById('buybox-quantity-selector');
            const desktopQtyVal = document.getElementById('buybox-quantity-val');

            const mobileAddBtn = document.getElementById('mobile-buybox-add-btn');
            const mobileSelector = document.getElementById('mobile-buybox-quantity-selector');
            const mobileQtyVal = document.getElementById('mobile-buybox-quantity-val');

            const miniAddBtn = document.getElementById('mini-buybox-add-btn');
            const miniSelector = document.getElementById('mini-buybox-quantity-selector');
            const miniQtyVal = document.getElementById('mini-buybox-quantity-val');

            if (qty > 0) {
                // نمایش کنترل‌گر تعداد دسکتاپ
                if (desktopAddBtn) desktopAddBtn.classList.add('hidden');
                if (desktopSelector) {
                    desktopSelector.classList.remove('hidden');
                    desktopSelector.classList.add('flex');
                }
                if (desktopQtyVal) desktopQtyVal.innerText = qty;

                // نمایش کنترل‌گر تعداد موبایل
                if (mobileAddBtn) mobileAddBtn.classList.add('hidden');
                if (mobileSelector) {
                    mobileSelector.classList.remove('hidden');
                    mobileSelector.classList.add('flex');
                }
                if (mobileQtyVal) mobileQtyVal.innerText = qty;

                // نمایش کنترل‌گر تعداد مینی بخر
                if (miniAddBtn) miniAddBtn.classList.add('hidden');
                if (miniSelector) {
                    miniSelector.classList.remove('hidden');
                    miniSelector.classList.add('flex');
                }
                if (miniQtyVal) miniQtyVal.innerText = qty;
            } else {
                // نمایش مجدد دکمه خرید اولیه
                if (desktopAddBtn) desktopAddBtn.classList.remove('hidden');
                if (desktopSelector) {
                    desktopSelector.classList.add('hidden');
                    desktopSelector.classList.remove('flex');
                }

                if (mobileAddBtn) mobileAddBtn.classList.remove('hidden');
                if (mobileSelector) {
                    mobileSelector.classList.add('hidden');
                    mobileSelector.classList.remove('flex');
                }

                if (miniAddBtn) miniAddBtn.classList.remove('hidden');
                if (miniSelector) {
                    miniSelector.classList.add('hidden');
                    miniSelector.classList.remove('flex');
                }
            }
        }

        // ارسال درخواست امن و چندنسخه‌ای به Livewire
        function dispatchLivewireEvent(eventName, payload) {
            if (window.Livewire) {
                if (typeof window.Livewire.dispatch === 'function') {
                    window.Livewire.dispatch(eventName, payload);
                } else if (typeof window.Livewire.emit === 'function') {
                    window.Livewire.emit(eventName, payload);
                }
            }
        }

        // دکمه اول سبد خرید (افزودن اولیه)
        function submitToCart() {
            if (!activeVariant || isCartLoading) return;
            const key = getCartKey(activeVariant);

            // به‌روزرسانی آنی کلاینت (Optimistic UI)
            cart[key] = { quantity: 1 };
            updateCartUI();
            setCartLoadingUI(true);

            // ارسال درخواست سرور
            dispatchLivewireEvent('addToCart', {
                variantId: parseInt(activeVariant.id),
                vendorProductId: activeVariant.vendor_product_id ? parseInt(activeVariant.vendor_product_id) : null,
                quantity: 1,
                selectedAttributes: selectedAttributes
            });
        }

        // افزایش تعداد
        function incrementCart() {
            if (!activeVariant || isCartLoading) return;
            const key = getCartKey(activeVariant);
            const currentQty = getCartQuantity(activeVariant);
            const newQty = currentQty + 1;

            cart[key] = { quantity: newQty };
            updateCartUI();
            setCartLoadingUI(true);

            dispatchLivewireEvent('updateQuantity', { cartKey: key, newQuantity: newQty });
        }

        // کاهش تعداد
        function decrementCart() {
            if (!activeVariant || isCartLoading) return;
            const key = getCartKey(activeVariant);
            const currentQty = getCartQuantity(activeVariant);
            const newQty = currentQty - 1;

            if (newQty <= 0) {
                delete cart[key];
            } else {
                cart[key] = { quantity: newQty };
            }
            updateCartUI();
            setCartLoadingUI(true);

            dispatchLivewireEvent('updateQuantity', { cartKey: key, newQuantity: newQty });
        }

        // به‌روزرسانی ویژگی‌ها و تنوع‌های انتخابی کلاینت
        function updateVariantSelection(btnElement) {
            const attrKey = btnElement.dataset.key;
            const attrValue = btnElement.dataset.val;
            const groupKey = btnElement.dataset.group;
            const unit = btnElement.dataset.unit || '';

            selectedAttributes[attrKey] = attrValue;

            const labelEl = document.getElementById('selected-label-' + groupKey);
            if(labelEl) {
                labelEl.innerHTML = attrValue + (unit ? ` <span class="text-xs opacity-75">${unit}</span>` : '');
            }

            const buttons = document.querySelectorAll(`button[data-group="${groupKey}"]`);
            buttons.forEach(btn => {
                const isColorBtn = btn.classList.contains('w-10');

                if(btn.dataset.val === attrValue) {
                    btn.classList.remove(...inactiveClasses);
                    btn.classList.add(...activeClasses);
                    if(isColorBtn) {
                        btn.querySelector('.absolute.inset-0').style.opacity = '1';
                    }
                } else {
                    btn.classList.remove(...activeClasses);
                    btn.classList.add(...inactiveClasses);
                    if(isColorBtn) {
                        btn.querySelector('.absolute.inset-0').style.opacity = '0';
                    }
                }
            });

            const matchedVariant = productVariants.find(v => {
                for (const key in selectedAttributes) {
                    const selectedVal = selectedAttributes[key];
                    const variantVal = v.attributes[key];

                    if (variantVal === selectedVal) continue;
                    if (variantVal && typeof variantVal === 'string' && variantVal.startsWith('هر ')) continue;
                    return false;
                }
                return true;
            });

            updateBuyBoxDOM(matchedVariant);
        }

        // به‌روزرسانی اطلاعات قیمت و وضعیت دسکتاپ و موبایل در DOM
        function updateBuyBoxDOM(variant) {
            activeVariant = variant;

            const availableBox = document.getElementById('buybox-available');
            const outOfStockBox = document.getElementById('buybox-out-of-stock');

            const mobileAvailableBox = document.getElementById('mobile-buybox-available');
            const mobileOutOfStockBox = document.getElementById('mobile-buybox-out-of-stock');
            const mobileAddBtn = document.getElementById('mobile-buybox-add-btn');
            const mobileNotifyBtn = document.getElementById('mobile-buybox-notify-btn');
            const mobileQtySelector = document.getElementById('mobile-buybox-quantity-selector');

            // --- به‌روزرسانی مینی بای‌باکس سایدبار (همیشه، صرف‌نظر از موجودی) ---
            const miniAvailableBox = document.getElementById('mini-buybox-available');
            const miniOutOfStockBox = document.getElementById('mini-buybox-out-of-stock');
            const miniSelectedVariantEl = document.getElementById('mini-buybox-selected-variant');
            const miniBannerEl = document.getElementById('mini-buybox-discount-banner');

            if (miniAvailableBox && miniOutOfStockBox) {

                // ۱. نمایش فروشنده در مینی بای‌باکس
                if (variant && document.getElementById('mini-buybox-vendor-name') && uiSettings.showVendor) {
                    document.getElementById('mini-buybox-vendor-name').innerText = variant.vendor_name;
                    const logoImg = document.getElementById('mini-buybox-vendor-logo');
                    const defaultIcon = document.getElementById('mini-buybox-vendor-icon');
                    if (variant.vendor_logo) {
                        if (logoImg) { logoImg.src = variant.vendor_logo; logoImg.classList.remove('hidden'); }
                        if (defaultIcon) defaultIcon.classList.add('hidden');
                    } else {
                        if (logoImg) logoImg.classList.add('hidden');
                        if (defaultIcon) defaultIcon.classList.remove('hidden');
                    }
                }

                // ۲. نمایش گرافیکی تنوع انتخاب شده
                if (miniSelectedVariantEl) {
                    const attrSource = (variant && variant.attributes) ? variant.attributes : {};
                    let variantHtml = '';
                    for (const [k, v] of Object.entries(attrSource)) {
                        const effectiveVal = (v && v.startsWith('هر ')) ? (selectedAttributes[k] || v) : v;
                        if (k && effectiveVal && effectiveVal !== 'استاندارد') {
                            let colorHex = null;
                            let isColor = false;
                            const allBtns = document.querySelectorAll('button[data-key]');
                            let matchingBtn = null;
                            for (const btn of allBtns) {
                                if (btn.dataset.key === k && btn.dataset.val === effectiveVal) {
                                    matchingBtn = btn;
                                    break;
                                }
                            }
                            if (matchingBtn) {
                                const colorSpan = matchingBtn.querySelector('span[style*="background-color"]');
                                if (colorSpan) { colorHex = colorSpan.style.backgroundColor; isColor = true; }
                                if (!colorHex) {
                                    const imgTag = matchingBtn.querySelector('img');
                                    if (imgTag) { colorHex = imgTag.src; isColor = 'image'; }
                                }
                            }
                            if (isColor === true && colorHex) {
                                variantHtml += `<div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700/60 text-[10px] font-bold text-gray-700 dark:text-gray-300">`;
                                variantHtml += `<span class="w-3.5 h-3.5 rounded-full border border-gray-300 dark:border-gray-600 shadow-sm shrink-0" style="background-color: ${colorHex}"></span>`;
                                variantHtml += `<span>${effectiveVal}</span></div>`;
                            } else if (isColor === 'image' && colorHex) {
                                variantHtml += `<div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700/60 text-[10px] font-bold text-gray-700 dark:text-gray-300">`;
                                variantHtml += `<img src="${colorHex}" class="w-4 h-4 rounded object-cover shrink-0 border border-gray-200 dark:border-gray-700">`;
                                variantHtml += `<span>${effectiveVal}</span></div>`;
                            } else {
                                variantHtml += `<div class="px-2 py-1 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700/60 text-[10px] font-bold text-gray-700 dark:text-gray-300">`;
                                variantHtml += `<span class="text-gray-400">${k}:</span> ${effectiveVal}</div>`;
                            }
                        }
                    }
                    // اگر تنوعی انتخاب نشده و selectedAttributes دارد مقدار است
                    if (!variantHtml) {
                        for (const [k, v] of Object.entries(selectedAttributes)) {
                            if (k && v && v !== 'استاندارد') {
                                let colorHex = null; let isColor = false;
                                const allBtns = document.querySelectorAll('button[data-key]');
                                for (const btn of allBtns) {
                                    if (btn.dataset.key === k && btn.dataset.val === v) {
                                        const colorSpan = btn.querySelector('span[style*="background-color"]');
                                        if (colorSpan) { colorHex = colorSpan.style.backgroundColor; isColor = true; }
                                        if (!colorHex) { const imgTag = btn.querySelector('img'); if (imgTag) { colorHex = imgTag.src; isColor = 'image'; } }
                                        break;
                                    }
                                }
                                if (isColor === true && colorHex) {
                                    variantHtml += `<div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700/60 text-[10px] font-bold text-gray-700 dark:text-gray-300"><span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0" style="background-color: ${colorHex}"></span><span>${v}</span></div>`;
                                } else if (isColor === 'image' && colorHex) {
                                    variantHtml += `<div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700/60 text-[10px] font-bold text-gray-700 dark:text-gray-300"><img src="${colorHex}" class="w-4 h-4 rounded object-cover shrink-0"><span>${v}</span></div>`;
                                } else {
                                    variantHtml += `<div class="px-2 py-1 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700/60 text-[10px] font-bold text-gray-700 dark:text-gray-300"><span class="text-gray-400">${k}:</span> ${v}</div>`;
                                }
                            }
                        }
                    }
                    miniSelectedVariantEl.innerHTML = variantHtml;
                }

                // ۳. نمایش/مخفی کردن پنل موجود/ناموجود + فروشنده
                const miniVendorSection = document.getElementById('mini-buybox-vendor-section');
                if (!variant || variant.stock === 0) {
                    miniAvailableBox.classList.add('hidden');
                    miniOutOfStockBox.classList.remove('hidden');
                    if (miniBannerEl) { miniBannerEl.classList.add('hidden'); miniBannerEl.classList.remove('flex'); }
                    if (miniVendorSection) miniVendorSection.classList.add('hidden');
                } else {
                    miniOutOfStockBox.classList.add('hidden');
                    miniAvailableBox.classList.remove('hidden');
                    if (miniVendorSection) miniVendorSection.classList.remove('hidden');
                    // قیمت نهایی - بعداً توسط بخش اصلی تنظیم می‌شود
                }
            }
            // --- پایان به‌روزرسانی مینی بای‌باکس ---

            if (!variant || variant.stock === 0) {
                availableBox.classList.add('hidden');
                availableBox.classList.remove('block');
                outOfStockBox.classList.remove('hidden');

                mobileAvailableBox.classList.add('hidden');
                mobileAvailableBox.classList.remove('flex');
                mobileOutOfStockBox.classList.remove('hidden');
                mobileOutOfStockBox.classList.add('flex');
                mobileAddBtn.classList.add('hidden');
                if (mobileQtySelector) mobileQtySelector.classList.add('hidden');
                mobileNotifyBtn.classList.remove('hidden');
                mobileNotifyBtn.classList.add('flex');

                if (variant && history.pushState) {
                    const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?variant=' + variant.id;
                    window.history.pushState({path:newurl}, '', newurl);
                }

                const mobileStockWarning = document.getElementById('mobile-buybox-stock-warning');
                if (mobileStockWarning) {
                    mobileStockWarning.dataset.show = 'false';
                    updateMobileWarningVisibility();
                }

                updateCartUI();
                return;
            }

            outOfStockBox.classList.add('hidden');
            availableBox.classList.remove('hidden');
            availableBox.classList.add('block');

            mobileOutOfStockBox.classList.add('hidden');
            mobileOutOfStockBox.classList.remove('flex');
            mobileAvailableBox.classList.remove('hidden');
            mobileAvailableBox.classList.add('flex');
            mobileNotifyBtn.classList.add('hidden');
            mobileNotifyBtn.classList.remove('flex');
            mobileAddBtn.classList.remove('hidden');

            const vendorSection = document.getElementById('buybox-vendor-section');
            if (vendorSection && uiSettings.showVendor) {
                document.getElementById('buybox-vendor-name').innerText = variant.vendor_name;
                if (variant.vendor_logo) {
                    document.getElementById('buybox-vendor-logo').src = variant.vendor_logo;
                    document.getElementById('buybox-vendor-logo').classList.remove('hidden');
                    document.getElementById('buybox-vendor-icon').classList.add('hidden');
                } else {
                    document.getElementById('buybox-vendor-logo').classList.add('hidden');
                    document.getElementById('buybox-vendor-icon').classList.remove('hidden');
                }
            }

            const stockWarning = document.getElementById('buybox-stock-warning');
            const mobileStockWarning = document.getElementById('mobile-buybox-stock-warning');

            const isLowStock = uiSettings.showStockWarning && variant.stock > 0 && variant.stock <= 3;

            if (stockWarning) {
                if (isLowStock) {
                    document.getElementById('buybox-stock-text').innerText = `فقط ${variant.stock} عدد در انبار باقیست`;
                    stockWarning.classList.remove('hidden');
                    stockWarning.classList.add('flex');
                } else {
                    stockWarning.classList.add('hidden');
                    stockWarning.classList.remove('flex');
                }
            }

            if (mobileStockWarning) {
                if (isLowStock) {
                    const mobileTextNode = document.getElementById('mobile-buybox-stock-text');
                    mobileTextNode.innerHTML = `<span class="flex h-2 w-2 relative"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span></span>فقط ${variant.stock} عدد در انبار باقیست`;
                    mobileStockWarning.dataset.show = 'true';
                } else {
                    mobileStockWarning.dataset.show = 'false';
                }
                updateMobileWarningVisibility();
            }

            const discountSection = document.getElementById('buybox-discount-section');
            const mobileDiscountSection = document.getElementById('mobile-buybox-discount-section');
            const discountStockProgress = document.getElementById('buybox-discount-stock-progress');

            if (variant.discount_price > 0 && variant.discount_price < variant.price) {
                const percent = Math.round(((variant.price - variant.discount_price) / variant.price) * 100);
                const formattedPrice = new Intl.NumberFormat('fa-IR').format(variant.price);

                document.getElementById('buybox-old-price').innerText = formattedPrice;
                document.getElementById('buybox-discount-badge').innerText = `٪${percent}`;
                discountSection.classList.remove('hidden');
                discountSection.classList.add('flex');

                document.getElementById('mobile-buybox-old-price').innerText = formattedPrice;
                document.getElementById('mobile-buybox-discount-badge').innerText = `٪${percent}`;
                mobileDiscountSection.classList.remove('hidden');
                mobileDiscountSection.classList.add('flex');

                const banner = document.getElementById('buybox-discount-banner');
                const spacer = document.getElementById('buybox-discount-spacer');
                const countdownSpan = document.getElementById('countdown-timer-desktop');

                if(variant.discount_end_date) {
                    banner.classList.remove('hidden');
                    banner.classList.add('flex');
                    spacer.classList.remove('hidden');
                    startCountdown(countdownSpan, variant.discount_end_date);
                } else {
                    banner.classList.add('hidden');
                    banner.classList.remove('flex');
                    spacer.classList.add('hidden');
                }

                if (discountStockProgress && variant.discount_stock > 0) {
                    const totalDiscountStock = variant.discount_stock;
                    const currentStock = Math.min(variant.stock, totalDiscountStock);
                    const remainingPercent = Math.max(0, Math.min(100, (currentStock / totalDiscountStock) * 100));

                    document.getElementById('discount-stock-bar').style.width = `${remainingPercent}%`;
                    document.getElementById('discount-stock-label').innerText = `${currentStock} از ${totalDiscountStock}`;
                    discountStockProgress.classList.remove('hidden');
                } else if (discountStockProgress) {
                    discountStockProgress.classList.add('hidden');
                }

            } else {
                discountSection.classList.add('hidden');
                discountSection.classList.remove('flex');
                document.getElementById('buybox-discount-banner').classList.add('hidden');
                document.getElementById('buybox-discount-spacer').classList.add('hidden');

                mobileDiscountSection.classList.add('hidden');
                mobileDiscountSection.classList.remove('flex');
                if (discountStockProgress) {
                    discountStockProgress.classList.add('hidden');
                }
            }

            const formattedFinalPrice = new Intl.NumberFormat('fa-IR').format(variant.final_price);
            document.getElementById('buybox-final-price').innerText = formattedFinalPrice;
            document.getElementById('mobile-buybox-final-price').innerText = formattedFinalPrice;

            document.getElementById('buybox-variant-id').value = variant.id;
            document.getElementById('buybox-vendor-product-id').value = variant.vendor_product_id || '';

            if (miniAvailableBox && miniOutOfStockBox && variant && variant.stock > 0) {
                // قیمت نهایی (فقط در حالت موجود)
                    const miniFinalPriceEl2 = document.getElementById('mini-buybox-final-price');
                    if (miniFinalPriceEl2) miniFinalPriceEl2.innerText = formattedFinalPrice;

                    // هشدار موجودی
                    const miniStockWarning = document.getElementById('mini-buybox-stock-warning');
                    if (miniStockWarning) {
                        if (isLowStock) {
                            document.getElementById('mini-buybox-stock-text').innerText = `فقط ${variant.stock} عدد در انبار باقیست`;
                            miniStockWarning.classList.remove('hidden');
                            miniStockWarning.classList.add('flex');
                        } else {
                            miniStockWarning.classList.add('hidden');
                            miniStockWarning.classList.remove('flex');
                        }
                    }

                    // تخفیف و شگفت‌انگیز در مینی بای‌باکس
                    const miniDiscountSection2 = document.getElementById('mini-buybox-discount-section');
                    const miniDiscountStockProgress2 = document.getElementById('mini-buybox-discount-stock-progress');

                    if (variant.discount_price > 0 && variant.discount_price < variant.price) {
                        const percent2 = Math.round(((variant.price - variant.discount_price) / variant.price) * 100);
                        const formattedOrigPrice2 = new Intl.NumberFormat('fa-IR').format(variant.price);

                        if (document.getElementById('mini-buybox-old-price')) {
                            document.getElementById('mini-buybox-old-price').innerText = formattedOrigPrice2;
                            document.getElementById('mini-buybox-discount-badge').innerText = `٪${percent2}`;
                        }
                        if (miniDiscountSection2) {
                            miniDiscountSection2.classList.remove('hidden');
                            miniDiscountSection2.classList.add('flex');
                        }

                        const countdownSpanMini = document.getElementById('mini-countdown-timer');
                        if (variant.discount_end_date) {
                            if(miniBannerEl) { miniBannerEl.classList.remove('hidden'); miniBannerEl.classList.add('flex'); }
                            startCountdown(countdownSpanMini, variant.discount_end_date);
                        } else {
                            if(miniBannerEl) { miniBannerEl.classList.add('hidden'); miniBannerEl.classList.remove('flex'); }
                        }

                        if (miniDiscountStockProgress2 && variant.discount_stock > 0) {
                            const totalDiscountStock2 = variant.discount_stock;
                            const currentStock2 = Math.min(variant.stock, totalDiscountStock2);
                            const remainingPercent2 = Math.max(0, Math.min(100, (currentStock2 / totalDiscountStock2) * 100));

                            document.getElementById('mini-discount-stock-bar').style.width = `${remainingPercent2}%`;
                            document.getElementById('mini-discount-stock-label').innerText = `${currentStock2} از ${totalDiscountStock2}`;
                            miniDiscountStockProgress2.classList.remove('hidden');
                        } else if (miniDiscountStockProgress2) {
                            miniDiscountStockProgress2.classList.add('hidden');
                        }
                    } else {
                        if (miniDiscountSection2) {
                            miniDiscountSection2.classList.add('hidden');
                            miniDiscountSection2.classList.remove('flex');
                        }
                        if (miniBannerEl) { miniBannerEl.classList.add('hidden'); miniBannerEl.classList.remove('flex'); }
                        if (miniDiscountStockProgress2) miniDiscountStockProgress2.classList.add('hidden');
                    }
            }

            if (history.pushState) {
                const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?variant=' + variant.id;
                window.history.pushState({path:newurl}, '', newurl);
            }

            // پس از بارگذاری اطلاعات، دکمه‌های سبد خرید برای این آیتم همگام‌سازی شوند
            updateCartUI();
        }

        // ثانیه‌شمار معکوس شگفت‌انگیز - نسخه بهبودیافته با تایمر مجزا برای هر نمایش
        let desktopTimer = null;
        let miniTimer = null;
        function startCountdown(element, endDateIso) {
            if (!element) return;
            // تشخیص اینکه کدام timer باید کلیر شود
            const isMini = element.id === 'mini-countdown-timer';
            if (isMini) {
                if (miniTimer) clearInterval(miniTimer);
            } else {
                if (desktopTimer) clearInterval(desktopTimer);
            }

            const targetDate = new Date(endDateIso).getTime();

            const timerFn = setInterval(() => {
                const now = new Date().getTime();
                const distance = targetDate - now;

                if (distance < 0) {
                    clearInterval(timerFn);
                    element.innerHTML = "<span class='text-xs'>پایان یافته</span>";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                let html = '';
                if (days > 0) {
                    html += `<span class="flex flex-col items-center"><span class="font-bold text-base">${String(days).padStart(2, '0')}</span><span class="text-[8px] opacity-70">روز</span></span><span class="font-bold text-base">:</span>`;
                }
                html += `<span class="flex flex-col items-center"><span class="font-bold text-base">${String(hours).padStart(2, '0')}</span><span class="text-[8px] opacity-70">ساعت</span></span><span class="font-bold text-base">:</span>`;
                html += `<span class="flex flex-col items-center"><span class="font-bold text-base">${String(minutes).padStart(2, '0')}</span><span class="text-[8px] opacity-70">دقیقه</span></span><span class="font-bold text-base">:</span>`;
                html += `<span class="flex flex-col items-center"><span class="font-bold text-base">${String(seconds).padStart(2, '0')}</span><span class="text-[8px] opacity-70">ثانیه</span></span>`;

                element.innerHTML = html;
            }, 1000);

            if (isMini) {
                miniTimer = timerFn;
            } else {
                desktopTimer = timerFn;
            }
        }

        // بارگذاری اولیه با لحاظ اولویت شناسه در URL
        document.addEventListener('DOMContentLoaded', () => {
            let initialVariant = null;
            if (requestedVariantId) {
                initialVariant = productVariants.find(v => v.id == requestedVariantId);
            }
            if (!initialVariant) {
                initialVariant = productVariants.find(v => {
                    for (const key in selectedAttributes) {
                        const selectedVal = selectedAttributes[key];
                        const variantVal = v.attributes[key];
                        if (variantVal === selectedVal) continue;
                        if (variantVal && typeof variantVal === 'string' && variantVal.startsWith('هر ')) continue;
                        return false;
                    }
                    return true;
                });
            }
            updateBuyBoxDOM(initialVariant || productVariants[0]);

            // Scrollspy for sticky nav
            const scrollspyLinks = document.querySelectorAll('.scrollspy-link');
            const sections = Array.from(scrollspyLinks).map(link => {
                const id = link.getAttribute('href');
                return document.querySelector(id);
            }).filter(Boolean);

            function updateScrollspy() {
                let currentActiveSection = null;
                const isDesktop = window.innerWidth >= 768;
                const scrollPos = window.scrollY + (isDesktop ? 210 : 150);

                sections.forEach(section => {
                    if (scrollPos >= section.offsetTop) {
                        currentActiveSection = section;
                    }
                });

                if (!currentActiveSection && sections.length > 0) {
                    currentActiveSection = sections[0];
                }

                scrollspyLinks.forEach(link => {
                    const targetId = link.getAttribute('href');
                    const isCurrent = currentActiveSection && ('#' + currentActiveSection.id === targetId);
                    
                    const activeColor = "text-{{ $t['name'] ?? 'indigo' }}-600";
                    const activeBorder = "border-{{ $t['name'] ?? 'indigo' }}-600";
                    const inactiveColor = "text-gray-500";
                    const darkActiveColor = "dark:text-{{ $t['name'] ?? 'indigo' }}-400";
                    const darkActiveBorder = "dark:border-{{ $t['name'] ?? 'indigo' }}-400";
                    const darkInactiveColor = "dark:text-gray-400";

                    if (isCurrent) {
                        link.classList.remove(inactiveColor, darkInactiveColor, 'border-transparent');
                        link.classList.add(activeColor, activeBorder, darkActiveColor, darkActiveBorder);
                    } else {
                        link.classList.remove(activeColor, activeBorder, darkActiveColor, darkActiveBorder);
                        link.classList.add(inactiveColor, darkInactiveColor, 'border-transparent');
                    }
                });
            }

            window.addEventListener('scroll', updateScrollspy);
            updateScrollspy();

            scrollspyLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = link.getAttribute('href');
                    const targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        const isDesktop = window.innerWidth >= 768;
                        window.scrollTo({
                            top: targetEl.offsetTop - (isDesktop ? 190 : 130),
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });

        // گالری تصاویر کوچک
        function changeMainImage(src, btnElement) {
            document.getElementById('main-product-image').src = src;

            const activeBorderClass = "{{ $t['border'] ?? 'border-indigo-600' }}";

            const thumbs = document.querySelectorAll('.gallery-thumb');
            thumbs.forEach(btn => {
                btn.classList.remove('border-2', activeBorderClass, 'opacity-100');
                btn.classList.add('border', 'border-gray-200', 'dark:border-gray-700', 'opacity-70');
            });

            if (btnElement) {
                btnElement.classList.remove('border', 'border-gray-200', 'dark:border-gray-700', 'opacity-70');
                btnElement.classList.add('border-2', activeBorderClass, 'opacity-100');
            }
        }

        // پاپ‌آپ گالری (Lightbox)
        function openLightbox(src) {
            const lb = document.getElementById('image-lightbox');
            const img = document.getElementById('lightbox-img');

            document.body.classList.add('lightbox-open');
            changeLightboxImage(src);

            lb.classList.remove('hidden');
            lb.classList.add('flex');
            void lb.offsetWidth;
            lb.classList.remove('opacity-0');
            img.classList.remove('scale-95');
        }

        function closeLightbox() {
            const lb = document.getElementById('image-lightbox');
            const img = document.getElementById('lightbox-img');
            lb.classList.add('opacity-0');
            img.classList.add('scale-95');
            setTimeout(() => {
                lb.classList.add('hidden');
                lb.classList.remove('flex');
                document.body.classList.remove('lightbox-open');
            }, 300);
        }

        function changeLightboxImage(src, btnElement = null) {
            const img = document.getElementById('lightbox-img');
            img.src = src;

            const thumbs = document.querySelectorAll('.lightbox-thumb');
            thumbs.forEach(btn => {
                btn.classList.remove('border-white', 'opacity-100');
                btn.classList.add('border-transparent', 'opacity-50');
                if (!btnElement) {
                    const imgTag = btn.querySelector('img');
                    if (imgTag && imgTag.src === src) btnElement = btn;
                }
            });

            if (btnElement) {
                btnElement.classList.remove('border-transparent', 'opacity-50');
                btnElement.classList.add('border-white', 'opacity-100');
            }
        }
    </script>
@endpush
