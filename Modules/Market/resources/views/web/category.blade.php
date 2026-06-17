@extends('layouts.web')

@section('title', isset($currentCategory) ? 'دسته‌بندی ' . $currentCategory->name : 'جستجو و دسته‌بندی محصولات')

@section('content')
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 w-full py-8">

        {{-- Breadcrumb --}}
        <div class="mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <nav class="flex text-sm text-gray-500 dark:text-gray-400 mb-4">
                <ol class="flex items-center space-x-2 space-x-reverse">
                    <li><a href="{{ url('/') }}" class="hover:text-gray-900 dark:hover:text-white transition-colors">خانه</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('market.public.index') }}" class="{{ $t['hover_text'] ?? 'hover:text-indigo-600' }} transition-colors">فروشگاه</a></li>
                    @if(isset($currentCategory))
                        <li><span class="mx-2">/</span></li>
                        <li class="font-bold text-gray-900 dark:text-gray-100">{{ $currentCategory->name }}</li>
                    @endif
                </ol>
            </nav>
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                @if(isset($currentCategory))
                    خرید از دسته‌بندی <span class="text-transparent bg-clip-text bg-gradient-to-r {{ $t['gradient_text'] ?? 'from-indigo-600 to-purple-600' }}">{{ $currentCategory->name }}</span>
                @else
                    جستجو و <span class="text-transparent bg-clip-text bg-gradient-to-r {{ $t['gradient_text'] ?? 'from-indigo-600 to-purple-600' }}">فیلتر محصولات</span>
                @endif
            </h1>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">

            {{-- دکمه فیلتر موبایل --}}
            <div class="lg:hidden mb-2">
                <button onclick="toggleFilters()" class="w-full flex items-center justify-center gap-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl py-3.5 font-bold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    فیلتر محصولات
                </button>
            </div>

            {{-- ================= Sidebar Filters (دیجی‌کالا استایل) ================= --}}
            <!-- Filter Overlay for Mobile -->
            <div id="filter-overlay" onclick="toggleFilters()" class="fixed inset-0 bg-gray-900/60 dark:bg-black/80 z-[100] hidden lg:hidden opacity-0 transition-opacity duration-300"></div>

            <!-- Filter Sidebar -->
            <aside id="filter-sidebar" class="fixed inset-y-0 right-0 z-[110] w-full max-w-[320px] bg-white dark:bg-gray-950 lg:bg-transparent lg:dark:bg-transparent transform translate-x-full lg:translate-x-0 transition-transform duration-300 lg:transition-none lg:static lg:block lg:w-1/4 xl:w-[320px] flex-shrink-0 flex flex-col h-[100dvh] lg:h-auto">

                <!-- Mobile Header -->
                <div class="lg:hidden flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-950 flex-shrink-0">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">فیلتر محصولات</h2>
                    <button onclick="toggleFilters()" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Filter Form -->
                <form id="main-filter-form" action="{{ url()->current() }}" method="GET" class="flex-1 overflow-y-auto lg:overflow-visible p-4 lg:p-0 flex flex-col gap-4 lg:sticky lg:top-24 custom-scrollbar pb-24 lg:pb-0">

                    @if(request()->has('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif

                    <!-- Desktop Header -->
                    <div class="hidden lg:flex items-center justify-between mb-2">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            فیلترها
                        </h2>
                        @if(request()->hasAny(['q', 'categories', 'brands', 'min_price', 'max_price', 'in_stock', 'attrs']))
                            <a href="{{ isset($currentCategory) ? route('market.public.category.show', $currentCategory->slug) : route('market.public.category') }}" class="text-xs font-bold text-rose-500 hover:text-rose-600 transition-colors bg-rose-50 dark:bg-rose-900/20 px-2 py-1 rounded-lg">حذف همه</a>
                        @endif
                    </div>

                    <!-- In Stock Switch -->
                    <label class="flex items-center justify-between cursor-pointer p-4 bg-white dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm hover:border-gray-200 dark:hover:border-gray-700 transition-colors group">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">فقط کالاهای موجود</span>
                        <div class="relative flex items-center">
                            <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} class="peer sr-only" onchange="if(window.innerWidth >= 1024) this.form.submit()">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </div>
                    </label>

                    <!-- Accordions Container -->
                    <div class="bg-white dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-800">

                        <!-- Search Accordion -->
                        <div x-data="{ expanded: true }" class="flex flex-col">
                            <button type="button" @click="expanded = !expanded" class="flex items-center justify-between p-4 w-full hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors rounded-t-2xl outline-none">
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">جستجو در نتایج</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="expanded" x-collapse>
                                <div class="p-4 pt-0">
                                    <div class="relative">
                                        <input type="text" name="q" value="{{ request('q') }}" placeholder="نام محصول..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl py-2.5 px-4 pr-10 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-gray-900 dark:text-white transition-all outline-none">
                                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Categories Accordion (سلسله مراتبی) -->
                        @if(isset($categoriesTree) && $categoriesTree->count() > 0)
                            <div x-data="{ expanded: true }" class="flex flex-col">
                                <button type="button" @click="expanded = !expanded" class="flex items-center justify-between p-4 w-full hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors outline-none">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">دسته‌بندی‌ها</span>
                                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div x-show="expanded" x-collapse>
                                    <div class="p-4 pt-0 space-y-1 max-h-60 overflow-y-auto custom-scrollbar">
                                        @foreach($categoriesTree as $mainCat)
                                            <div class="flex flex-col">
                                                <label class="flex items-center gap-3 cursor-pointer group py-1.5">
                                                    <div class="relative flex items-center justify-center w-5 h-5 flex-shrink-0">
                                                        <input type="checkbox" name="categories[]" value="{{ $mainCat->id }}" onchange="if(window.innerWidth >= 1024) this.form.submit()"
                                                               {{ in_array($mainCat->id, request('categories', [])) || (isset($currentCategory) && $currentCategory->id === $mainCat->id) ? 'checked' : '' }}
                                                               class="peer appearance-none w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-md cursor-pointer checked:bg-indigo-500 checked:border-indigo-500 transition-colors outline-none">
                                                        <svg class="w-3.5 h-3.5 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors select-none line-clamp-1">{{ $mainCat->name }}</span>
                                                </label>

                                                {{-- زیر دسته‌های سطح دوم --}}
                                                @if($mainCat->children && $mainCat->children->count() > 0)
                                                    <div class="pr-7 space-y-1 mt-1 border-r-2 border-gray-100 dark:border-gray-800 mr-2.5">
                                                        @foreach($mainCat->children as $subCat)
                                                            <div class="flex flex-col">
                                                                <label class="flex items-center gap-3 cursor-pointer group py-1.5">
                                                                    <div class="relative flex items-center justify-center w-4 h-4 flex-shrink-0">
                                                                        <input type="checkbox" name="categories[]" value="{{ $subCat->id }}" onchange="if(window.innerWidth >= 1024) this.form.submit()"
                                                                               {{ in_array($subCat->id, request('categories', [])) || (isset($currentCategory) && $currentCategory->id === $subCat->id) ? 'checked' : '' }}
                                                                               class="peer appearance-none w-4 h-4 border-2 border-gray-300 dark:border-gray-600 rounded cursor-pointer checked:bg-indigo-500 checked:border-indigo-500 transition-colors outline-none">
                                                                        <svg class="w-3 h-3 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                    </div>
                                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors select-none line-clamp-1">{{ $subCat->name }}</span>
                                                                </label>

                                                                {{-- زیر دسته‌های سطح سوم --}}
                                                                @if($subCat->children && $subCat->children->count() > 0)
                                                                    <div class="pr-6 space-y-1 mt-1 border-r-2 border-gray-100 dark:border-gray-800 mr-2 mb-1">
                                                                        @foreach($subCat->children as $childCat)
                                                                            <label class="flex items-center gap-3 cursor-pointer group py-1">
                                                                                <div class="relative flex items-center justify-center w-4 h-4 flex-shrink-0">
                                                                                    <input type="checkbox" name="categories[]" value="{{ $childCat->id }}" onchange="if(window.innerWidth >= 1024) this.form.submit()"
                                                                                           {{ in_array($childCat->id, request('categories', [])) || (isset($currentCategory) && $currentCategory->id === $childCat->id) ? 'checked' : '' }}
                                                                                           class="peer appearance-none w-4 h-4 border-2 border-gray-300 dark:border-gray-600 rounded cursor-pointer checked:bg-indigo-500 checked:border-indigo-500 transition-colors outline-none">
                                                                                    <svg class="w-3 h-3 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                                </div>
                                                                                <span class="text-xs text-gray-600 dark:text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors select-none line-clamp-1">{{ $childCat->name }}</span>
                                                                            </label>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Brands Accordion -->
                        @if(isset($brands) && $brands->count() > 1)
                            <div x-data="{ expanded: true }" class="flex flex-col">
                                <button type="button" @click="expanded = !expanded" class="flex items-center justify-between p-4 w-full hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors outline-none">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">برندها</span>
                                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div x-show="expanded" x-collapse>
                                    <div class="p-4 pt-0 space-y-3 max-h-52 overflow-y-auto custom-scrollbar">
                                        @foreach($brands as $brand)
                                            <label class="flex items-center justify-between cursor-pointer group">
                                                <div class="flex items-center gap-3">
                                                    <div class="relative flex items-center justify-center w-5 h-5 flex-shrink-0">
                                                        <input type="checkbox" name="brands[]" value="{{ $brand->id }}" onchange="if(window.innerWidth >= 1024) this.form.submit()"
                                                               {{ in_array($brand->id, request('brands', [])) ? 'checked' : '' }}
                                                               class="peer appearance-none w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-md cursor-pointer checked:bg-indigo-500 checked:border-indigo-500 transition-colors outline-none">
                                                        <svg class="w-3.5 h-3.5 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors select-none line-clamp-1">{{ $brand->name }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Attributes Accordions (ویژگی‌های داینامیک) -->
                        @if(isset($filterAttributes) && $filterAttributes->count() > 0)
                            @foreach($filterAttributes as $attr)
                                <div x-data="{ expanded: false }" class="flex flex-col">
                                    <button type="button" @click="expanded = !expanded" class="flex items-center justify-between p-4 w-full hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors outline-none">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $attr->name }}</span>
                                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </button>
                                    <div x-show="expanded" x-collapse>
                                        <div class="p-4 pt-0 space-y-3 max-h-52 overflow-y-auto custom-scrollbar">
                                            @foreach($attr->values as $val)
                                                <label class="flex items-center justify-between cursor-pointer group">
                                                    <div class="flex items-center gap-3">
                                                        <div class="relative flex items-center justify-center w-5 h-5 flex-shrink-0">
                                                            <input type="checkbox" name="attrs[{{ $attr->id }}][]" value="{{ $val->value }}" onchange="if(window.innerWidth >= 1024) this.form.submit()"
                                                                   {{ in_array($val->value, request("attrs.{$attr->id}", [])) ? 'checked' : '' }}
                                                                   class="peer appearance-none w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-md cursor-pointer checked:bg-indigo-500 checked:border-indigo-500 transition-colors outline-none">
                                                            <svg class="w-3.5 h-3.5 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        </div>
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors select-none line-clamp-1">{{ $val->value }}</span>
                                                    </div>

                                                    @if($attr->type === 'color' && $val->meta_value)
                                                        @if(str_starts_with($val->meta_value, 'attributes/'))
                                                            <img src="{{ Storage::url($val->meta_value) }}" class="w-5 h-5 rounded-full object-cover shadow-sm border border-gray-200 dark:border-gray-700 flex-shrink-0">
                                                        @else
                                                            <span class="w-5 h-5 rounded-full border border-gray-200 dark:border-gray-700 shadow-sm flex-shrink-0" style="background-color: {{ $val->meta_value }}"></span>
                                                        @endif
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- 💡 Price Range Slider Accordion (دقیقاً مشابه دیجی‌کالا) -->
                        <div x-data="priceRangeSlider()" x-init="initSlider()" class="flex flex-col pb-2">
                            <button type="button" @click="expanded = !expanded" class="flex items-center justify-between p-4 w-full hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors rounded-b-2xl outline-none">
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">محدوده قیمت</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="expanded" x-collapse>
                                <div class="px-6 py-4">

                                    {{-- نمایش رنج قیمت با فرمت هزارگان --}}
                                    <div class="flex items-center justify-between mb-8 text-sm font-bold text-gray-700 dark:text-gray-300">
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs font-normal text-gray-400">از</span>
                                            <span x-text="formatNumber(minPrice)"></span>
                                            <span class="text-[10px] text-gray-500 font-normal">تومان</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs font-normal text-gray-400">تا</span>
                                            <span x-text="formatNumber(maxPrice)"></span>
                                            <span class="text-[10px] text-gray-500 font-normal">تومان</span>
                                        </div>
                                    </div>

                                    {{-- اسلایدر دوگانه با CSS کاستوم (استاندارد وب) --}}
                                    <div class="relative w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full my-6" dir="rtl">
                                        {{-- خط پررنگ انتخاب شده بین دو هندل --}}
                                        <div class="absolute h-full bg-indigo-500 rounded-full pointer-events-none" :style="`right: ${minPercent}%; left: ${100 - maxPercent}%;`"></div>

                                        {{-- اینپوت‌های رنج (کلاس custom-range در پایین صفحه استایل‌دهی شده) --}}
                                        <input type="range" x-model="minPrice" @input="updateMin" :min="absoluteMin" :max="absoluteMax" :step="step" class="custom-range absolute inset-0 w-full h-full bg-transparent outline-none z-20">
                                        <input type="range" x-model="maxPrice" @input="updateMax" :min="absoluteMin" :max="absoluteMax" :step="step" class="custom-range absolute inset-0 w-full h-full bg-transparent outline-none z-20">
                                    </div>

                                    {{-- مقادیر واقعی برای ارسال فرم --}}
                                    <input type="hidden" name="min_price" :value="minPrice > absoluteMin ? formatNumber(minPrice) : ''">
                                    <input type="hidden" name="max_price" :value="maxPrice < absoluteMax ? formatNumber(maxPrice) : ''">

                                    <button type="submit" class="w-full mt-4 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold text-xs py-2.5 rounded-xl border border-indigo-100 dark:border-indigo-800/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">اعمال محدوده قیمت</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>

                <!-- Mobile Footer Action (Only visible on mobile sidebar) -->
                <div class="lg:hidden p-4 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-950 absolute bottom-0 left-0 right-0 flex gap-3 pb-safe z-20 shadow-[0_-10px_20px_rgba(0,0,0,0.05)]">
                    <button type="button" onclick="document.getElementById('main-filter-form').submit()" class="flex-1 bg-indigo-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 active:scale-95 transition-transform text-sm">اعمال فیلترها</button>
                    @if(request()->hasAny(['q', 'categories', 'brands', 'min_price', 'max_price', 'in_stock', 'attrs']))
                        <a href="{{ isset($currentCategory) ? route('market.public.category.show', $currentCategory->slug) : route('market.public.category') }}" class="flex-shrink-0 px-6 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 font-bold py-3.5 rounded-xl active:scale-95 transition-transform text-sm flex items-center justify-center">حذف همه</a>
                    @endif
                </div>
            </aside>

            {{-- ================= Main Content Area ================= --}}
            <div class="flex-1 min-w-0">

                {{-- Top Bar (مرتب‌سازی خطی) --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center bg-white dark:bg-gray-900/50 backdrop-blur-md border border-gray-100 dark:border-gray-800 rounded-2xl p-4 mb-6 shadow-sm overflow-hidden gap-4">

                    <div class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white whitespace-nowrap flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" /></svg>
                        مرتب‌سازی:
                    </div>

                    <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-2 sm:pb-0 w-full">
                        @php
                            $sorts = [
                                'relevant' => 'مرتبط‌ترین',
                                'viewed' => 'پربازدیدترین',
                                'newest' => 'جدیدترین',
                                'bestselling' => 'پرفروش‌ترین',
                                'price_asc' => 'ارزان‌ترین',
                                'price_desc' => 'گران‌ترین',
                                'recommended' => 'پیشنهاد خریداران',
                                'selected' => 'منتخب',
                            ];
                            $currentSort = request('sort', 'newest');
                        @endphp
                        @foreach($sorts as $key => $label)
                            <button type="button" onclick="addSortAndSubmit('{{ $key }}')" class="whitespace-nowrap px-3 py-1.5 rounded-xl text-xs sm:text-sm font-medium transition-all flex-shrink-0 {{ $currentSort == $key ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-100 dark:border-indigo-800' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 border border-transparent' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap hidden xl:block mr-auto flex-shrink-0 border-r border-gray-200 dark:border-gray-700 pr-4">
                        <span class="font-bold text-gray-900 dark:text-white">{{ $items->total() }}</span> کالا
                    </div>
                </div>

                {{-- Items Grid --}}
                @if(isset($items) && $items->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-100">
                        @foreach($items as $item)
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
                                    $pSlug = $item->masterProduct->slug;
                                    $productUrl = route('market.public.product.show', ['slug' => $pSlug, 'variant' => $item->id]); // لینک کامل
                                    $targetVariantId = $item->id;

                                    // فیلتر کردن مقادیر "هر X" از ویژگی‌های قابل نمایش روی کارت
                                    $rawAttrs = is_array($item->variant_attributes) ? $item->variant_attributes : [];
                                    foreach($rawAttrs as $k => $v) {
                                         if(!str_starts_with($v, 'هر ') && !(in_array($k, ['name', 'نام']) && $v === 'استاندارد')) {
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

                                $masterProductForRating = $variantMode === 'separated' ? $item->masterProduct : $item;
                                $ratingScore = $masterProductForRating->average_rating;
                                $reviewsCount = $masterProductForRating->approved_reviews_count;

                                // نشان‌های نمایشی برای افزایش نرخ تبدیل
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

                                            @php
                                                $brand = $variantMode === 'separated' ? $item->masterProduct->brand : $item->brand;
                                            @endphp
                                            @if(isset($showBrandOnCard) && $showBrandOnCard && $brand)
                                                <div class="absolute top-3 right-3 px-2.5 py-1 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md text-[10px] font-bold text-gray-700 dark:text-gray-300 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                                                    {{ $brand->name }}
                                                </div>
                                            @endif

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

                                        <div class="p-5 flex-1 flex flex-col relative bg-white dark:bg-gray-900 z-10">

                                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200 line-clamp-2 mb-1.5 leading-relaxed min-h-[44px]">
                                                {{ $item->title ?? ($item->masterProduct->title ?? 'محصول') }}
                                            </h3>

                                            @php
                                                if (isset($separateCategoryEnabled) && $separateCategoryEnabled) {
                                                    $displayCategories = $variantMode === 'separated' 
                                                        ? $item->masterProduct->displayCategories 
                                                        : $item->displayCategories;
                                                    $parentIds = $displayCategories->pluck('parent_id')->filter()->toArray();
                                                    $category = $displayCategories->reject(function($cat) use ($parentIds) {
                                                        return in_array($cat->id, $parentIds);
                                                    })->first();
                                                } else {
                                                    $category = $variantMode === 'separated' ? $item->masterProduct->category : $item->category;
                                                }
                                            @endphp
                                            @if(isset($showCategoryOnCard) && $showCategoryOnCard && $category)
                                                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-3">{{ $category->name }}</span>
                                            @endif

                                            @if($variantMode === 'separated' && !empty($attributes))
                                                <div class="flex flex-wrap gap-1.5 mb-4">
                                                    @foreach($attributes as $key => $val)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                                            <span class="text-gray-500 dark:text-gray-400 font-medium ml-1">{{ $key }}:</span> {{ $val }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- هشدار موجودی کم / تنوع / نشان ترغیبی --}}
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
                                                    @if($masterProductForRating->enable_reviews && $ratingScore > 0)
                                                        <div class="flex items-center gap-1 text-amber-500 bg-amber-50 dark:bg-amber-900/20 px-1.5 py-0.5 rounded-md" title="{{ $reviewsCount }} نظر">
                                                            <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ number_format($ratingScore, 1) }}</span>
                                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                        </div>
                                                    @endif
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
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($items->hasPages())
                        <div class="mt-12 flex justify-center w-full">
                            {{ $items->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    {{-- Empty State --}}
                    <div class="w-full bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-16 text-center animate-in fade-in duration-500 mt-4">
                        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-50 dark:bg-gray-800 mb-6 shadow-inner">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">هیچ محصولی یافت نشد</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                            با تغییر یا حذف فیلترهای اعمال شده، ممکن است نتایج بیشتری پیدا کنید.
                        </p>
                        @if(request()->hasAny(['q', 'categories', 'min_price', 'max_price', 'in_stock', 'attrs']))
                            <a href="{{ isset($currentCategory) ? route('market.public.category.show', $currentCategory->slug) : route('market.public.category') }}" class="inline-block mt-6 px-6 py-2.5 {{ $t['bg_light'] ?? 'bg-indigo-50' }} {{ $t['bg_light_dark'] ?? 'dark:bg-indigo-900/30' }} {{ $t['text'] ?? 'text-indigo-600' }} {{ $t['text_dark'] ?? 'dark:text-indigo-400' }} font-bold rounded-xl {{ $t['bg_hover'] ?? 'hover:bg-indigo-700' }} hover:text-white transition-colors">
                                پاک کردن همه فیلترها
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- فراخوانی مخفی کامپوننت لایووایر مدیر سبد خرید --}}
        @livewire('market::web.cart-manager')
    </div>
@endsection

@push('scripts')
    <script>
        function addSortAndSubmit(sortValue) {
            const form = document.getElementById('main-filter-form');
            let sortInput = form.querySelector('input[name="sort"]');
            if (!sortInput) {
                sortInput = document.createElement('input');
                sortInput.type = 'hidden';
                sortInput.name = 'sort';
                form.appendChild(sortInput);
            }
            sortInput.value = sortValue;
            form.submit();
        }

        function toggleFilters() {
            const overlay = document.getElementById('filter-overlay');
            const sidebar = document.getElementById('filter-sidebar');

            if (sidebar.classList.contains('translate-x-full')) {
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                sidebar.classList.remove('translate-x-full');
                document.body.style.overflow = 'hidden';
            } else {
                overlay.classList.add('opacity-0');
                sidebar.classList.add('translate-x-full');
                document.body.style.overflow = '';
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        // منطق مربوط به اسلایدر رنج قیمت دوگانه (Alpine.js)
        document.addEventListener('alpine:init', () => {
            Alpine.data('priceRangeSlider', () => ({
                expanded: true,
                absoluteMin: {{ $absoluteMinPrice ?? 0 }},
                absoluteMax: {{ $absoluteMaxPrice ?? 500000000 }},
                step: 1000,
                minPrice: {{ request('min_price') ? str_replace(',', '', request('min_price')) : ($absoluteMinPrice ?? 0) }},
                maxPrice: {{ request('max_price') ? str_replace(',', '', request('max_price')) : ($absoluteMaxPrice ?? 500000000) }},

                initSlider() {
                    this.$watch('minPrice', value => {
                        if (parseInt(value) > parseInt(this.maxPrice)) {
                            this.minPrice = this.maxPrice;
                        }
                    });
                    this.$watch('maxPrice', value => {
                        if (parseInt(value) < parseInt(this.minPrice)) {
                            this.maxPrice = this.minPrice;
                        }
                    });
                },

                get minPercent() {
                    if (this.absoluteMax === this.absoluteMin) return 0;
                    return ((this.minPrice - this.absoluteMin) / (this.absoluteMax - this.absoluteMin)) * 100;
                },

                get maxPercent() {
                    if (this.absoluteMax === this.absoluteMin) return 100;
                    return ((this.maxPrice - this.absoluteMin) / (this.absoluteMax - this.absoluteMin)) * 100;
                },

                formatNumber(val) {
                    if (!val && val !== 0) return '۰';
                    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                },

                updateMin() {
                    // This is triggered by range input
                },

                updateMax() {
                    // This is triggered by range input
                }
            }));
        });
    </script>
@endpush

@push('styles')
    <style>
        .toggle-checkbox:checked { right: 0; border-color: {{ $t['hex'] ?? '#4f46e5' }}; }
        .toggle-checkbox:checked + .toggle-label { background-color: {{ $t['hex'] ?? '#4f46e5' }}; }
        .toggle-checkbox { right: 0; z-index: 1; border-color: #e5e7eb; transition: all 0.3s; }
        .toggle-label { width: 2.5rem; }
        html[dir="rtl"] .toggle-checkbox:checked { transform: translateX(-100%); right: 1.25rem; }

        /* استایل‌های استاندارد اسلایدر قیمت با پشتیبانی از جلوگیری همپوشانی (Overlap) */
        .custom-range {
            -webkit-appearance: none;
            appearance: none;
            pointer-events: none;
        }
        .custom-range:focus {
            outline: none;
        }
        .custom-range::-webkit-slider-thumb {
            pointer-events: auto;
            width: 20px;
            height: 20px;
            -webkit-appearance: none;
            background-color: #ffffff;
            border: 3px solid #6366f1;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            cursor: pointer;
            margin-top: -7px;
            position: relative;
            z-index: 30;
        }
        .dark .custom-range::-webkit-slider-thumb {
            background-color: #1f2937;
        }
        .custom-range::-moz-range-thumb {
            pointer-events: auto;
            width: 20px;
            height: 20px;
            background-color: #ffffff;
            border: 3px solid #6366f1;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            cursor: pointer;
            z-index: 30;
            position: relative;
        }
        .dark .custom-range::-moz-range-thumb {
            background-color: #1f2937;
        }
        .custom-range::-webkit-slider-runnable-track {
            -webkit-appearance: none;
            height: 6px;
            background: transparent;
            border: none;
        }
        .custom-range::-moz-range-track {
            height: 6px;
            background: transparent;
            border: none;
        }
    </style>
@endpush
