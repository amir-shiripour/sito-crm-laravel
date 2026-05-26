@extends('layouts.web')

@section('title', $displayType === 'by_product' ? 'فروشگاه' : 'فروشندگان و فروشگاه‌ها')

@section('content')
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 w-full py-8">

        {{-- Hero Section (ویترینی) --}}
        <div class="mb-12 animate-in fade-in slide-in-from-bottom-4 duration-700 text-center bg-white dark:bg-gray-900/50 rounded-[3rem] p-10 lg:p-16 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
                <div class="absolute -top-32 -right-32 w-96 h-96 {{ $t['blob_1'] ?? 'bg-indigo-500/10' }} blur-[100px] rounded-full opacity-60"></div>
                <div class="absolute -bottom-32 -left-32 w-96 h-96 {{ $t['blob_2'] ?? 'bg-purple-500/10' }} blur-[100px] rounded-full opacity-60"></div>
            </div>

            <div class="relative z-10">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white tracking-tight mb-6">
                    به فروشگاه <span class="text-transparent bg-clip-text bg-gradient-to-r {{ $t['gradient_text'] ?? 'from-indigo-600 to-purple-600' }}">هوشمند</span> ما خوش آمدید
                </h1>
                <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed mb-8 font-medium">
                    @if($displayType === 'by_product')
                        جدیدترین و محبوب‌ترین محصولات را مرور کنید. برای جستجوی دقیق‌تر و استفاده از فیلترها به صفحه دسته‌بندی مراجعه کنید.
                    @else
                        از بین فروشگاه‌های معتبر سیستم انتخاب کنید و محصولات آن‌ها را به صورت مستقیم خریداری نمایید.
                    @endif
                </p>

                @if($displayType === 'by_product')
                    <a href="{{ route('market.public.category') }}" class="inline-flex items-center justify-center gap-2 {{ $t['bg'] ?? 'bg-indigo-600' }} {{ $t['bg_hover'] ?? 'hover:bg-indigo-700' }} text-white font-bold py-4 px-8 rounded-2xl transition-all duration-300 shadow-lg {{ $t['shadow'] ?? 'shadow-indigo-500/30' }} hover:-translate-y-1 active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        مشاهده همه دسته‌بندی‌ها و فیلترها
                    </a>
                @endif
            </div>
        </div>

        {{-- Section Title --}}
        <div class="flex items-center justify-between mb-8 px-2">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <div class="w-2.5 h-8 {{ $t['bg'] ?? 'bg-indigo-600' }} rounded-full shadow-sm"></div>
                @if($displayType === 'by_product') جدیدترین محصولات @else برترین فروشگاه‌ها @endif
            </h2>
        </div>

        {{-- Items Grid --}}
        @if(isset($items) && $items->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-100">
                @foreach($items as $item)
                    @if($displayType === 'by_product')
                        @php
                            $minPrice = null;
                            $originalPrice = null;
                            $hasStock = false;
                            $totalStock = 0;
                            $activeVariantsCount = 0;
                            $pSlug = '';
                            $attributes = [];
                            $productUrl = '#';
                            $bestVpId = null;
                            $targetVariantId = null;

                            if ($variantMode === 'separated') {
                                // در حالت مجزا، item یک ProductVariant است
                                $pSlug = $item->masterProduct->slug;
                                $productUrl = route('market.public.product.show', ['slug' => $pSlug, 'variant' => $item->id]); // لینک همراه با آیدی متغیر
                                $targetVariantId = $item->id;

                                // 💡 فیلتر کردن مقادیر "هر X" از ویژگی‌های قابل نمایش روی کارت
                                $rawAttrs = is_array($item->variant_attributes) ? $item->variant_attributes : [];
                                foreach($rawAttrs as $k => $v) {
                                     if(!str_starts_with($v, 'هر ')) {
                                          $attributes[$k] = $v;
                                     }
                                }

                                foreach ($item->vendorProducts as $vp) {
                                    if ($vp->status === 'published' && $vp->stock > 0) {
                                        $hasStock = true;
                                        $totalStock += $vp->stock;
                                        $activePrice = $vp->discount_price > 0 ? $vp->discount_price : $vp->price;

                                        if ($minPrice === null || $activePrice < $minPrice) {
                                            $minPrice = $activePrice;
                                            $originalPrice = $vp->price;
                                            $bestVpId = $vp->id;
                                        }
                                    }
                                }
                            } else {
                                // در حالت گروهی، item یک MasterProduct است
                                $pSlug = $item->slug;
                                $productUrl = route('market.public.product.show', $pSlug); // لینک ساده

                                foreach ($item->variants as $variant) {
                                    $variantHasStock = false;
                                    foreach ($variant->vendorProducts as $vp) {
                                        if ($vp->status === 'published' && $vp->stock > 0) {
                                            $hasStock = true;
                                            $variantHasStock = true;
                                            $activePrice = $vp->discount_price > 0 ? $vp->discount_price : $vp->price;

                                            if ($minPrice === null || $activePrice < $minPrice) {
                                                $minPrice = $activePrice;
                                                $originalPrice = $vp->price;
                                                $bestVpId = $vp->id;
                                                $targetVariantId = $variant->id;
                                            }
                                        }
                                    }
                                    if ($variantHasStock) $activeVariantsCount++;
                                }
                            }

                            $discountPercent = 0;
                            if ($originalPrice && $minPrice && $originalPrice > $minPrice) {
                                $discountPercent = round((($originalPrice - $minPrice) / $originalPrice) * 100);
                            }

                            // نشان‌های نمایشی برای افزایش نرخ تبدیل (وقتی موجودی نرمال است)
                            $promoBadges = [
                                ['text' => 'ارسال سریع', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => 'text-teal-600 dark:text-teal-400', 'bg' => 'bg-teal-50 dark:bg-teal-900/20', 'border' => 'border-teal-100 dark:border-teal-800/30'],
                                ['text' => 'محبوب خریداران', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'color' => 'text-rose-600 dark:text-rose-400', 'bg' => 'bg-rose-50 dark:bg-rose-900/20', 'border' => 'border-rose-100 dark:border-rose-800/30'],
                                ['text' => 'پیشنهاد شگفت‌انگیز', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'color' => 'text-amber-600 dark:text-amber-400', 'bg' => 'bg-amber-50 dark:bg-amber-900/20', 'border' => 'border-amber-100 dark:border-amber-800/30'],
                            ];
                            $promoBadge = $promoBadges[$item->id % count($promoBadges)];
                        @endphp

                        {{-- Product Card --}}
                        <div class="h-full relative {{ !$hasStock ? 'opacity-80' : '' }}">
                            <div class="group flex flex-col bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] dark:shadow-none hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 overflow-hidden h-full">
                                <a href="{{ $productUrl }}" class="flex-1 flex flex-col">
                                    {{-- Product Image & Badges --}}
                                    <div class="relative h-60 w-full overflow-hidden bg-gray-50/80 dark:bg-gray-800/40 p-6 flex items-center justify-center">
                                        @php
                                            $imgSrc = $variantMode === 'separated' && isset($item->masterProduct->main_image)
                                                ? $item->masterProduct->main_image
                                                : ($item->main_image ?? null);
                                        @endphp

                                        @if($imgSrc)
                                            <img src="{{ asset('storage/' . $imgSrc) }}" alt="{{ $item->title ?? ($item->masterProduct->title ?? 'محصول') }}" class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal transition-transform duration-500 group-hover:scale-110">
                                        @else
                                            <svg class="w-20 h-20 opacity-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        @endif

                                        {{-- Brand Badge --}}
                                        @php
                                            $brand = $variantMode === 'separated' ? $item->masterProduct->brand : $item->brand;
                                        @endphp
                                        @if($brand)
                                            <div class="absolute top-3 right-3 px-2.5 py-1 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md text-[10px] font-bold text-gray-700 dark:text-gray-300 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                                                {{ $brand->name }}
                                            </div>
                                        @endif

                                        {{-- Discount Badge --}}
                                        @if($discountPercent > 0 && $hasStock)
                                            <div class="absolute top-3 left-3 px-2.5 py-1 bg-rose-500 text-[11px] font-black text-white rounded-lg shadow-md shadow-rose-500/30">
                                                فروش ویژه
                                            </div>
                                        @endif

                                        {{-- استایل محو برای محصول ناموجود --}}
                                        @if(!$hasStock)
                                            <div class="absolute inset-0 bg-white/40 dark:bg-gray-900/60 backdrop-blur-[2px] z-10 pointer-events-none"></div>
                                        @endif
                                    </div>

                                    {{-- Product Info --}}
                                    <div class="p-5 flex-1 flex flex-col relative bg-white dark:bg-gray-900 z-10">

                                        {{-- عنوان محصول --}}
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200 line-clamp-2 mb-1.5 leading-relaxed min-h-[44px]">
                                            {{ $item->title ?? ($item->masterProduct->title ?? 'محصول') }}
                                        </h3>

                                        {{-- دسته‌بندی محصول --}}
                                        @php
                                            $category = $variantMode === 'separated' ? $item->masterProduct->category : $item->category;
                                        @endphp
                                        @if(isset($showCategoryOnCard) && $showCategoryOnCard && $category)
                                            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-3">{{ $category->name }}</span>
                                        @endif

                                        {{-- نمایش ویژگی‌ها در حالت مجزا (پس از فیلتر شدن "هر X") --}}
                                        @if($variantMode === 'separated' && !empty($attributes))
                                            <div class="flex flex-wrap gap-1.5 mb-4">
                                                @foreach($attributes as $key => $val)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                                        <span class="text-gray-500 dark:text-gray-400 font-medium ml-1">{{ $key }}:</span> {{ $val }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- هشدار موجودی کم و امتیاز --}}
                                        @if($hasStock)
                                            <div class="mb-3 flex items-center justify-between">
                                                <div>
                                                    @if($variantMode === 'grouped' && $activeVariantsCount > 1)
                                                        <div class="inline-flex items-center gap-1 bg-gray-50 dark:bg-gray-800/50 px-2.5 py-1 rounded-xl border border-gray-100 dark:border-gray-700">
                                                            <div class="w-2.5 h-2.5 rounded-full bg-indigo-500"></div>
                                                            <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                                                            <div class="w-2.5 h-2.5 rounded-full bg-teal-500"></div>
                                                            <span class="text-[10px] font-bold text-gray-600 dark:text-gray-300 mr-1.5">+{{ $activeVariantsCount }} تنوع</span>
                                                        </div>
                                                    @elseif($variantMode === 'separated' && $totalStock <= 3)
                                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 text-[10px] font-bold rounded-xl border border-rose-100 dark:border-rose-800">
                                                            <span class="flex h-2 w-2 relative">
                                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                                            </span>
                                                            تنها {{ $totalStock }} عدد در انبار باقی مانده
                                                        </div>
                                                    @else
                                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 {{ $promoBadge['bg'] }} {{ $promoBadge['color'] }} text-[10px] font-bold rounded-xl border {{ $promoBadge['border'] }}">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $promoBadge['icon'] }}"/></svg>
                                                            {{ $promoBadge['text'] }}
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- امتیاز --}}
                                                <div class="flex items-center gap-1 text-amber-500 bg-amber-50 dark:bg-amber-900/20 px-1.5 py-0.5 rounded-md">
                                                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 mt-0.5">۴.۵</span>
                                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="h-8"></div> {{-- اسپیسر برای تراز ماندن کارت‌های ناموجود --}}
                                        @endif
                                        <div class="mt-auto"></div>
                                    </div>
                                </a>
                                <div class="p-5 pt-0">
                                    <div class="border-t border-gray-50 dark:border-gray-800/50 pt-4">
                                        @if($hasStock && $minPrice !== null)
                                            <div class="flex items-end justify-between">
                                                {{-- دکمه سبد خرید جدید --}}
                                                @livewire('market::web.add-to-cart-button', ['variantId' => $targetVariantId, 'vendorProductId' => $bestVpId, 't' => $t], key($targetVariantId . '-' . $bestVpId))

                                                <div class="flex flex-col text-left">
                                                    @if($discountPercent > 0)
                                                        <div class="flex items-center justify-end gap-1.5 mb-1">
                                                            <span class="px-2 py-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-md">٪{{ $discountPercent }}</span>
                                                            <span class="text-sm text-gray-400 line-through decoration-gray-400 relative">
                                                                {{ number_format($originalPrice) }}
                                                            </span>
                                                        </div>
                                                    @elseif($variantMode === 'grouped' && $minPrice != ($maxPrice ?? $minPrice))
                                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 mb-1">شروع قیمت از:</span>
                                                    @endif

                                                    {{-- بزرگ شدن فونت قیمت --}}
                                                    <div class="text-gray-900 dark:text-white font-black flex items-center justify-end gap-1 text-xl sm:text-2xl tracking-tight">
                                                        {{ number_format($minPrice) }}
                                                        <span class="text-[10px] sm:text-xs font-medium text-gray-500">تومان</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- باکس اختصاصی کالای ناموجود --}}
                                            <div class="w-full h-11 bg-gray-50 dark:bg-gray-800/80 rounded-2xl flex items-center justify-center gap-2 border border-gray-100 dark:border-gray-700">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                                <span class="text-sm font-bold text-gray-500 dark:text-gray-400">ناموجود</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Vendor Card --}}
                        <a href="#" class="group flex flex-col bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-lg shadow-gray-200/20 dark:shadow-none hover:border-gray-300 dark:hover:border-gray-600 hover:-translate-y-1.5 transition-all duration-300 overflow-hidden h-full">
                            <div class="relative h-32 w-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                @if($item->cover_image)
                                    <img src="{{ asset('storage/' . $item->cover_image) }}" alt="{{ $item->store_name }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-transform duration-700 group-hover:scale-105">
                                @else
                                    <div class="w-full h-full bg-gradient-to-tr {{ $t['gradient_text'] ?? 'from-indigo-600 to-purple-600' }} opacity-70 group-hover:opacity-100 transition-opacity duration-300"></div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            </div>
                            <div class="p-6 flex-1 flex flex-col relative pt-0">
                                <div class="w-24 h-24 rounded-[2rem] bg-white dark:bg-gray-900 border-4 border-white dark:border-gray-900 shadow-xl flex items-center justify-center -mt-12 mb-5 overflow-hidden relative z-10 mx-auto transition-transform duration-300 group-hover:-translate-y-2">
                                    @if($item->logo)
                                        <img src="{{ asset('storage/' . $item->logo) }}" alt="{{ $item->store_name }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    @endif
                                </div>
                                <div class="text-center flex-1 flex flex-col">
                                    <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $item->store_name }}</h3>
                                    @if($item->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">{{ $item->description }}</p>
                                    @endif

                                    <div class="mt-auto pt-6 flex items-center justify-center gap-2 text-xs font-bold {{ $t['text'] ?? 'text-indigo-600' }} {{ $t['text_dark'] ?? 'dark:text-indigo-400' }} opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                                        مشاهده فروشگاه
                                        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center bg-gray-50 dark:bg-gray-900/50 rounded-[3rem] border border-gray-100 dark:border-gray-800">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m14 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m14 0H6m4-8h4m-4 4h4" /></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">موردی یافت نشد</h3>
                <p class="text-gray-500 dark:text-gray-400">در حال حاضر هیچ داده‌ای برای نمایش در این بخش وجود ندارد.</p>
            </div>
        @endif

        {{-- فراخوانی مخفی کامپوننت لایووایر مدیر سبد خرید --}}
        @livewire('market::web.cart-manager')
    </div>
@endsection
