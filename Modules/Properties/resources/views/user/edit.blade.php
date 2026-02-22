@extends('layouts.user')

@php
    $title = 'ویرایش ملک: ' . $property->title;
    $currency = \Modules\Properties\Entities\PropertySetting::get('currency', 'toman');
    $currencyLabel = $currency == 'toman' ? 'تومان' : 'ریال';

    // دریافت ویژگی‌ها
    $detailsAttributes = \Modules\Properties\Entities\PropertyAttribute::where('section', 'details')->where('is_active', true)->orderBy('sort_order')->get();
    $featuresAttributes = \Modules\Properties\Entities\PropertyAttribute::where('section', 'features')->where('is_active', true)->orderBy('sort_order')->get();

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";

    // دریافت نقش‌های مجاز برای مشاور بودن از تنظیمات
    $agentRoles = json_decode(\Modules\Properties\Entities\PropertySetting::get('agent_roles', '[]'), true);
    $user = auth()->user();

    // بررسی اینکه آیا کاربر فعلی یکی از نقش‌های مشاور را دارد
    $isAgent = $user->hasAnyRole($agentRoles);

    // بررسی اینکه آیا کاربر ادمین یا سوپر ادمین است (برای دسترسی کامل به تغییر مشاور)
    $isAdmin = $user->hasRole(['super-admin', 'admin']);

    // تعیین اینکه آیا کاربر می‌تواند مشاور را تغییر دهد
    $canChangeAgent = $isAdmin || !$isAgent;

    $currentAgentId = $property->agent_id ?? $property->created_by;
    $currentAgentName = optional($property->agent)->name ?? optional($property->creator)->name;

    // بررسی فعال بودن هوش مصنوعی
    $aiEnabled = \Modules\Properties\Entities\PropertySetting::get('ai_property_completion', 0);
@endphp

@section('content')

    {{-- استایل‌های نقشه --}}
    <style>
        .leaflet-control-geosearch form { background: var(--bg-white, #ffffff); border-radius: 0.75rem; padding: 0.25rem; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .dark .leaflet-control-geosearch form { background: #1f2937; border-color: #374151; color: #f3f4f6; }
        .leaflet-control-geosearch input { color: inherit; background: transparent; border-radius: 0.5rem; height: 2.5rem; font-size: 0.875rem; }
        .dark .leaflet-control-geosearch input { color: #f3f4f6; }
        .leaflet-control-geosearch .results { background: white; border-radius: 0.5rem; margin-top: 6px; border: 1px solid #e5e7eb; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); z-index: 1000 !important; }
        .dark .leaflet-control-geosearch .results { background: #1f2937; border-color: #374151; }
        .leaflet-control-geosearch .results > * { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; cursor: pointer; font-size: 0.875rem; }
        .dark .leaflet-control-geosearch .results > * { border-bottom-color: #374151; color: #d1d5db; }
        .leaflet-control-geosearch .results > :hover { background-color: #f9fafb; }
        .dark .leaflet-control-geosearch .results > :hover { background-color: #374151; color: white; }
    </style>

    <div class="max-w-7xl mx-auto px-4 py-8" x-data="editPropertyForm()">

        {{-- هدر صفحه --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </span>
                    ویرایش ملک
                </h1>
                <div class="flex items-center gap-2 mt-2 text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $property->title }}</span>
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs">کد: {{ $property->code }}</span>
                </div>
            </div>

            <a href="{{ route('user.properties.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                بازگشت به لیست
            </a>
        </div>

            <form id="property-form" action="{{ route('user.properties.update', $property) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Inputs مخفی نقشه --}}
            <input type="hidden" name="latitude" x-model="lat">
            <input type="hidden" name="longitude" x-model="lng">

            {{-- Inputs مخفی برای قیمت‌ها (تکمیل شده توسط AI) --}}
            <input type="hidden" name="price" x-model="prices.price">
            <input type="hidden" name="min_price" x-model="prices.min_price">
            <input type="hidden" name="deposit_price" x-model="prices.deposit_price">
            <input type="hidden" name="rent_price" x-model="prices.rent_price">
            <input type="hidden" name="advance_price" x-model="prices.advance_price">

            {{-- کانتینر برای ویژگی‌ها و امکانات (توسط JS پر می‌شود) --}}
            <div id="ai-attributes-container"></div>
            <div id="ai-features-container"></div>
            <div id="ai-custom-details-container"></div>
            <div id="ai-custom-features-container"></div>

            <div class="grid grid-cols-12 gap-6">

                {{-- ستون چپ (مدیا و وضعیت) --}}
                <div class="col-span-12 lg:col-span-4 space-y-6 order-2 lg:order-1">
                    @include('properties::user.partials.edit-details-form')
                </div>

                {{-- ستون راست (اطلاعات اصلی و تب‌ها) --}}
                <div class="col-span-12 lg:col-span-8 space-y-6 order-1 lg:order-2">
                    <div class="{{ $cardClass }}">
                        {{-- نوار تب‌ها --}}
                        <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto scrollbar-hide bg-gray-50/50 dark:bg-gray-900/30">
                            <button type="button" @click="activeTab = 'details'; setTimeout(() => map?.invalidateSize(), 200)"
                                    class="flex-1 py-4 px-6 text-center border-b-2 font-bold text-sm transition-all whitespace-nowrap outline-none focus:outline-none flex items-center justify-center gap-2"
                                    :class="activeTab === 'details' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                مشخصات اصلی
                            </button>

                            <button type="button" @click="activeTab = 'pricing'"
                                    class="flex-1 py-4 px-6 text-center border-b-2 font-bold text-sm transition-all whitespace-nowrap outline-none focus:outline-none flex items-center justify-center gap-2"
                                    :class="activeTab === 'pricing' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                قیمت‌گذاری
                            </button>

                            <button type="button" @click="activeTab = 'attributes'"
                                    class="flex-1 py-4 px-6 text-center border-b-2 font-bold text-sm transition-all whitespace-nowrap outline-none focus:outline-none flex items-center justify-center gap-2"
                                    :class="activeTab === 'attributes' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                اطلاعات تکمیلی
                            </button>

                            <button type="button" @click="activeTab = 'features'"
                                    class="flex-1 py-4 px-6 text-center border-b-2 font-bold text-sm transition-all whitespace-nowrap outline-none focus:outline-none flex items-center justify-center gap-2"
                                    :class="activeTab === 'features' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                امکانات
                            </button>
                        </div>

                        <div class="p-6">
                            {{-- Tab 1: Details --}}
                            <div x-show="activeTab === 'details'" x-transition:enter.duration.300ms x-transition:enter.opacity>
                                {{-- محتوای تب مشخصات اصلی --}}
                                <div class="space-y-6">
                                    {{-- ردیف اول --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="md:col-span-2">
                                            <label class="{{ $labelClass }}">عنوان ملک <span class="text-red-500">*</span></label>
                                            <input type="text" name="title" x-model="title" class="{{ $inputClass }}" required>
                                        </div>

                                        <div>
                                            <label class="{{ $labelClass }}">نوع فایل</label>
                                            <select name="listing_type" x-model="listingType" class="{{ $selectClass }}">
                                                <option value="sale">فروش</option>
                                                <option value="presale">پیش‌فروش</option>
                                                <option value="rent">رهن و اجاره</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="{{ $labelClass }}">نوع ملک</label>
                                            <select name="property_type" x-model="propertyType" class="{{ $selectClass }}">
                                                <option value="apartment">خانه و آپارتمان</option>
                                                <option value="villa">ویلا</option>
                                                <option value="land">زمین و باغ</option>
                                                <option value="office">اداری و تجاری</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="{{ $labelClass }}">نوع سند</label>
                                            <select name="document_type" x-model="documentType" class="{{ $selectClass }}">
                                                <option value="">انتخاب کنید</option>
                                                @foreach(\Modules\Properties\Entities\Property::DOCUMENT_TYPES as $key => $label)
                                                    <option value="{{ $key }}" {{ $property->document_type == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="{{ $labelClass }}">ساختمان / برج</label>
                                            <div class="relative">
                                                <input type="hidden" name="building_id" x-model="selectedBuildingId">
                                                <input type="text"
                                                       x-model="searchBuildingQuery"
                                                       @input.debounce.300ms="searchBuildings()"
                                                       @focus="handleBuildingFocus()"
                                                       @click.outside="showBuildingResults = false"
                                                       class="{{ $inputClass }} pr-10"
                                                       placeholder="جستجوی ساختمان..."
                                                       autocomplete="off">

                                                {{-- Loading Indicator --}}
                                                <div x-show="isSearchingBuilding" class="absolute left-3 top-2.5">
                                                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </div>

                                                {{-- Results Dropdown --}}
                                                <div x-show="showBuildingResults && searchBuildingResults.length > 0"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="opacity-0 translate-y-2"
                                                     x-transition:enter-end="opacity-100 translate-y-0"
                                                     class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar">
                                                    <ul class="py-1">
                                                        <template x-for="building in searchBuildingResults" :key="building.id">
                                                            <li @click="selectBuilding(building)" class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700/50 last:border-0 transition-colors group/item">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex flex-col">
                                                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover/item:text-indigo-600 dark:group-hover/item:text-indigo-400" x-text="building.name"></span>
                                                                        <span class="text-xs text-gray-500 dark:text-gray-400 dir-ltr text-right mt-0.5" x-text="building.address"></span>
                                                                    </div>
                                                                    <svg class="w-4 h-4 text-gray-300 group-hover/item:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                                </div>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </div>
                                                <div x-show="showBuildingResults && searchBuildingResults.length === 0 && !isSearchingBuilding" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl p-4 text-center">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">ساختمانی با این مشخصات یافت نشد.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- فیلدهای شرطی --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-100 dark:border-gray-700/50"
                                         x-show="propertyType === 'land' || listingType === 'presale'" x-transition>
                                        <div x-show="propertyType === 'land'">
                                            <label class="{{ $labelClass }}">نوع کاربری</label>
                                            <select name="usage_type" x-model="usageType" class="{{ $selectClass }}">
                                                <option value="">انتخاب کنید...</option>
                                                <option value="residential" {{ $property->usage_type == 'residential' ? 'selected' : '' }}>مسکونی</option>
                                                <option value="industrial" {{ $property->usage_type == 'industrial' ? 'selected' : '' }}>صنعتی</option>
                                                <option value="commercial" {{ $property->usage_type == 'commercial' ? 'selected' : '' }}>اداری / تجاری</option>
                                                <option value="agricultural" {{ $property->usage_type == 'agricultural' ? 'selected' : '' }}>کشاورزی</option>
                                            </select>
                                        </div>
                                        <div x-show="listingType === 'presale'">
                                            <label class="{{ $labelClass }}">تاریخ تحویل</label>
                                            <input type="text" name="delivery_date" x-model="deliveryDate" data-jdp value="{{ $property->delivery_date_jalali }}" class="{{ $inputClass }} text-center">
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="{{ $labelClass }} mb-0">توضیحات تکمیلی</label>
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="toggleVoiceTyping" :disabled="!isVoiceTypingSupported"
                                                        class="text-xs flex items-center gap-1.5 px-3 py-1 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                        :class="{
                                                            'bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40': !isVoiceTyping,
                                                            'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40 animate-pulse': isVoiceTyping
                                                        }">
                                                    <svg x-show="!isVoiceTyping" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14a2 2 0 0 0 2-2V6a2 2 0 0 0-4 0v6a2 2 0 0 0 2 2Zm-2-8a2 2 0 0 1 4 0v6a2 2 0 0 1-4 0V6Zm8 5a1 1 0 0 0-1 1v1a5 5 0 0 1-10 0v-1a1 1 0 1 0-2 0v1a7 7 0 0 0 6 6.92V21a1 1 0 1 0 2 0v-2.08A7 7 0 0 0 20 12v-1a1 1 0 0 0-1-1Z"/></svg>
                                                    <svg x-show="isVoiceTyping" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.793 5.793a1 1 0 0 1 1.414 0L12 10.586l4.793-4.793a1 1 0 1 1 1.414 1.414L13.414 12l4.793 4.793a1 1 0 0 1-1.414 1.414L12 13.414l-4.793 4.793a1 1 0 0 1-1.414-1.414L10.586 12 5.793 7.207a1 1 0 0 1 0-1.414Z"/></svg>
                                                    <span x-text="isVoiceTyping ? 'توقف' : 'صوتی'"></span>
                                                </button>
                                                @if($aiEnabled)
                                                    <button type="button" @click="completeWithAI" :disabled="isCompletingAI" class="text-xs flex items-center gap-1.5 px-3 py-1 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:hover:bg-purple-900/40 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                        <svg x-show="!isCompletingAI" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                        <svg x-show="isCompletingAI" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                        تکمیل هوشمند
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        <textarea name="description" x-model="description" rows="4" class="{{ $inputClass }} resize-none leading-relaxed">{{ old('description', $property->description) }}</textarea>
                                        <p class="text-[10px] text-gray-500 mt-1">برای استفاده از هوش مصنوعی، ابتدا توضیحات را بنویسید و سپس دکمه تکمیل هوشمند را بزنید.</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="{{ $labelClass }}">کد ملک</label>
                                            <input type="text" name="code" x-model="code" value="{{ old('code', $property->code) }}" class="{{ $inputClass }}">
                                        </div>

                                        <div>
                                            <label class="{{ $labelClass }}">دسته‌بندی شخصی</label>
                                            <select name="category_id" class="{{ $selectClass }}">
                                                <option value="">بدون دسته‌بندی</option>
                                                @foreach(\Modules\Properties\Entities\PropertyCategory::where('user_id', auth()->id())->get() as $category)
                                                    <option value="{{ $category->id }}" {{ $property->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 2: Pricing --}}
                            <div x-show="activeTab === 'pricing'" x-transition:enter.duration.300ms x-transition:enter.opacity style="display: none;">
                                <div class="max-w-3xl mx-auto space-y-8" x-data="priceFormatter()">
                                    {{-- پیام نوع فایل --}}
                                    <div class="flex items-center gap-3 p-4 rounded-xl bg-blue-50 text-blue-800 border border-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span class="text-sm">
                                        ویرایش قیمت برای: <span class="font-bold border-b border-blue-300 dark:border-blue-500">{{ $property->listing_type == 'sale' ? 'فروش' : ($property->listing_type == 'rent' ? 'رهن و اجاره' : 'پیش‌فروش') }}</span>
                                    </span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @if($property->listing_type == 'sale')
                                            {{-- فروش --}}
                                            <div>
                                                <label class="{{ $labelClass }}">قیمت اعلامی ({{ $currencyLabel }})</label>
                                                <input type="text" name="price" x-model="prices.price" @input="formatPrice" class="{{ $inputClass }}" required>
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">قیمت کف / حداقل ({{ $currencyLabel }})</label>
                                                <input type="text" name="min_price" x-model="prices.min_price" @input="formatPrice" class="{{ $inputClass }}">
                                            </div>

                                        @elseif($property->listing_type == 'rent')
                                            {{-- رهن و اجاره --}}
                                            <div>
                                                <label class="{{ $labelClass }}">مبلغ رهن ({{ $currencyLabel }})</label>
                                                <input type="text" name="deposit_price" x-model="prices.deposit_price" @input="formatPrice" class="{{ $inputClass }}" required>
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">اجاره ماهیانه ({{ $currencyLabel }})</label>
                                                <input type="text" name="rent_price" x-model="prices.rent_price" @input="formatPrice" class="{{ $inputClass }}" required>
                                            </div>

                                        @elseif($property->listing_type == 'presale')
                                            {{-- پیش‌فروش --}}
                                            <div>
                                                <label class="{{ $labelClass }}">مبلغ پیش‌پرداخت ({{ $currencyLabel }})</label>
                                                <input type="text" name="advance_price" x-model="prices.advance_price" @input="formatPrice" class="{{ $inputClass }}" required>
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">قیمت کل اعلامی ({{ $currencyLabel }})</label>
                                                <input type="text" name="price" x-model="prices.price" @input="formatPrice" class="{{ $inputClass }}" required>
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">قیمت کف ({{ $currencyLabel }})</label>
                                                <input type="text" name="min_price" x-model="prices.min_price" @input="formatPrice" class="{{ $inputClass }}">
                                            </div>
                                        @endif
                                    </div>

                                    {{-- آپشن قابل تبدیل (برای همه نوع ملک‌ها) --}}
                                    <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                        <label class="inline-flex items-center gap-3 cursor-pointer group mb-4">
                                            <div class="relative">
                                                <input type="checkbox" name="is_convertible" value="1" x-model="isConvertible" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                            </div>
                                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">
                                                @if($property->listing_type == 'rent')
                                                    قابل تبدیل به رهن/اجاره دیگر
                                                @else
                                                    قابل معاوضه با ملک یا خودرو
                                                @endif
                                            </span>
                                        </label>

                                        <div x-show="isConvertible" x-transition>
                                            <label class="{{ $labelClass }}">شرایط تبدیل / معاوضه</label>
                                            <input type="text" name="convertible_with" class="{{ $inputClass }}"
                                                   placeholder="@if($property->listing_type == 'rent') مثلاً: تا ۲۰۰ میلیون رهن قابل تبدیل است... @else مثلاً: معاوضه با آپارتمان کوچکتر در منطقه ۱... @endif"
                                                   value="{{ old('convertible_with', $property->convertible_with) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 3: Attributes --}}
                            <div x-show="activeTab === 'attributes'" x-transition:enter.duration.300ms x-transition:enter.opacity style="display: none;">
                                <div class="max-w-4xl mx-auto space-y-8">
                                    {{-- ویژگی‌های سیستمی --}}
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            ویژگی‌های پایه
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            @foreach($detailsAttributes as $attr)
                                                <div>
                                                    <label class="{{ $labelClass }}">{{ $attr->name }}</label>
                                                    @php $value = $property->attributeValues->where('attribute_id', $attr->id)->first()->value ?? ''; @endphp

                                                    @if($attr->type === 'text')
                                                        <input type="text" name="attributes[{{ $attr->id }}]" value="{{ $value }}" class="{{ $inputClass }} ai-attribute" data-attr-id="{{ $attr->id }}">
                                                    @elseif($attr->type === 'number')
                                                        <input type="number" name="attributes[{{ $attr->id }}]" value="{{ $value }}" class="{{ $inputClass }} ai-attribute" data-attr-id="{{ $attr->id }}">
                                                    @elseif($attr->type === 'select')
                                                        <div class="relative">
                                                            <select name="attributes[{{ $attr->id }}]" class="{{ $selectClass }} ai-attribute" data-attr-id="{{ $attr->id }}">
                                                                <option value="">انتخاب کنید</option>
                                                                @foreach($attr->options ?? [] as $option)
                                                                    <option value="{{ $option }}" {{ $value == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($detailsAttributes->isEmpty())
                                            <div class="text-center py-4 text-xs text-gray-400 border border-dashed rounded-xl">هیچ ویژگی پایه‌ای تعریف نشده است.</div>
                                        @endif
                                    </div>

                                    {{-- ویژگی‌های سفارشی --}}
                                    <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                ویژگی‌های سفارشی
                                            </h3>
                                            <button type="button" @click="addDetailField" class="text-xs font-bold text-indigo-600 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors dark:text-indigo-400 dark:hover:bg-indigo-900/30">
                                                + افزودن جدید
                                            </button>
                                        </div>
                                        <div class="space-y-3">
                                            <template x-for="(field, index) in customDetails" :key="index">
                                                <div class="flex gap-3 animate-in fade-in slide-in-from-right-2">
                                                    <input type="text" :name="`meta[details][${index}][key]`" x-model="field.key" placeholder="عنوان" class="{{ $inputClass }} !w-1/3">
                                                    <input type="text" :name="`meta[details][${index}][value]`" x-model="field.value" placeholder="مقدار" class="{{ $inputClass }}">
                                                    <button type="button" @click="removeDetailField(index)" class="p-2 text-red-500 bg-red-50 hover:bg-red-100 rounded-xl transition-colors dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <div x-show="customDetails.length === 0" class="text-center py-4 text-xs text-gray-400 border border-dashed rounded-xl">ویژگی سفارشی اضافه نشده است.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 4: Features --}}
                            <div x-show="activeTab === 'features'" x-transition:enter.duration.300ms x-transition:enter.opacity style="display: none;">
                                <div class="max-w-4xl mx-auto space-y-8">
                                    {{-- امکانات سیستمی --}}
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            امکانات رفاهی
                                        </h3>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                            @foreach($featuresAttributes as $attr)
                                                @php $hasValue = $property->attributeValues->where('attribute_id', $attr->id)->isNotEmpty(); @endphp
                                                <label class="group flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50 hover:bg-white hover:border-indigo-200 cursor-pointer transition-all dark:bg-gray-700/30 dark:border-gray-700 dark:hover:bg-gray-700 dark:hover:border-indigo-700">
                                                    <input type="checkbox" name="features[]" value="{{ $attr->id }}" {{ $hasValue ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 ai-feature" data-attr-id="{{ $attr->id }}">
                                                    <span class="text-xs font-medium text-gray-700 group-hover:text-indigo-700 dark:text-gray-300 dark:group-hover:text-indigo-300">{{ $attr->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @if($featuresAttributes->isEmpty())
                                            <div class="text-center py-4 text-xs text-gray-400 border border-dashed rounded-xl">هیچ امکانی تعریف نشده است.</div>
                                        @endif
                                    </div>

                                    {{-- امکانات سفارشی --}}
                                    <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                امکانات سفارشی
                                            </h3>
                                            <button type="button" @click="addFeatureField" class="text-xs font-bold text-indigo-600 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors dark:text-indigo-400 dark:hover:bg-indigo-900/30">
                                                + افزودن جدید
                                            </button>
                                        </div>
                                        <div class="space-y-3">
                                            <template x-for="(field, index) in customFeatures" :key="index">
                                                <div class="flex gap-3 animate-in fade-in slide-in-from-right-2">
                                                    <input type="text" :name="`meta[features][${index}][value]`" x-model="field.value" placeholder="عنوان امکانات" class="{{ $inputClass }}">
                                                    <button type="button" @click="removeFeatureField(index)" class="p-2 text-red-500 bg-red-50 hover:bg-red-100 rounded-xl transition-colors dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <div x-show="customFeatures.length === 0" class="text-center py-4 text-xs text-gray-400 border border-dashed rounded-xl">امکانات سفارشی اضافه نشده است.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- کارت مالک --}}
                    <div class="{{ $cardClass }} p-6 overflow-visible">
                        <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <span class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">اطلاعات مالک</h2>
                        </div>

                        <div class="relative">
                            {{-- نمایش مالک فعلی (اگر وجود دارد) --}}
                            @if($property->owner)
                                <div class="flex items-center justify-between p-3 mb-4 rounded-xl bg-indigo-50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-800">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center font-bold text-sm dark:bg-indigo-800 dark:text-indigo-200">
                                            {{ mb_substr($property->owner->first_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-indigo-900 dark:text-indigo-200">{{ $property->owner->first_name . ' ' . $property->owner->last_name }}</div>
                                            <div class="text-xs text-indigo-600 dark:text-indigo-400 dir-ltr text-right">{{ $property->owner->phone }}</div>
                                        </div>
                                    </div>
                                    <span class="text-xs bg-white dark:bg-gray-800 px-2 py-1 rounded text-gray-500">مالک فعلی</span>
                                </div>
                            @endif

                            <label class="{{ $labelClass }}">تغییر مالک (جستجو)</label>
                            <div class="flex gap-2">
                                <input type="hidden" name="owner_id" x-model="selectedOwner">

                                <div class="relative flex-1 group">
                                    <input type="text"
                                           x-model="searchQuery"
                                           @input.debounce.300ms="searchOwners()"
                                           @focus="if(searchQuery.length >= 2) showResults = true"
                                           @click.outside="showResults = false"
                                           class="{{ $inputClass }} pr-10"
                                           placeholder="جستجوی مالک جدید...">

                                    {{-- لودینگ --}}
                                    <div x-show="isSearching" class="absolute left-3 top-2.5">
                                        <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </div>

                                    {{-- نتایج جستجو --}}
                                    <div x-show="showResults && searchResults.length > 0"
                                         class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                                        <ul>
                                            <template x-for="owner in searchResults" :key="owner.id">
                                                <li @click="selectOwner(owner)" class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700/50 last:border-0 transition-colors group/item">
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-sm font-bold" x-text="owner.first_name + ' ' + owner.last_name"></span>
                                                        <span class="text-xs text-gray-500 dir-ltr" x-text="owner.phone"></span>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>

                                <button type="button" @click="showOwnerModal = true" class="px-4 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300 rounded-xl hover:bg-indigo-100 transition-colors border border-indigo-100 dark:border-indigo-800">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- کارت مشاور مسئول (جدید) --}}
                    @if(isset($agents) && $agents->count() > 0)
                        <div class="{{ $cardClass }} p-6 overflow-visible">
                            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                                <span class="w-2 h-2 rounded-full bg-purple-500 shadow-[0_0_8px_rgba(168,85,247,0.6)]"></span>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">مشاور مسئول</h2>
                            </div>
                            <div>
                                @if($canChangeAgent)
                                    <label class="{{ $labelClass }}">جستجو یا انتخاب مشاور</label>
                                    <div class="relative">
                                        <input type="hidden" name="agent_id" x-model="selectedAgentId">

                                        <div class="relative flex-1 group">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                            </div>
                                            <input type="text"
                                                   x-model="searchAgentQuery"
                                                   @input.debounce.300ms="searchAgents()"
                                                   @focus="if(searchAgentQuery.length >= 2) showAgentResults = true"
                                                   @click.outside="showAgentResults = false"
                                                   class="{{ $inputClass }} pr-10"
                                                   placeholder="نام، شماره تماس یا ایمیل مشاور..."
                                                   autocomplete="off">

                                            {{-- Loading Indicator --}}
                                            <div x-show="isSearchingAgent" class="absolute left-3 top-2.5">
                                                <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </div>

                                            {{-- Results Dropdown --}}
                                            <div x-show="showAgentResults && searchAgentResults.length > 0"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 translate-y-2"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar">
                                                <ul class="py-1">
                                                    <template x-for="agent in searchAgentResults" :key="agent.id">
                                                        <li @click="selectAgent(agent)" class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700/50 last:border-0 transition-colors group/item">
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex flex-col">
                                                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover/item:text-indigo-600 dark:group-hover/item:text-indigo-400" x-text="agent.name"></span>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400 dir-ltr text-right mt-0.5" x-text="agent.mobile || agent.email"></span>
                                                                </div>
                                                                <svg class="w-4 h-4 text-gray-300 group-hover/item:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                            </div>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                            <div x-show="showAgentResults && searchAgentResults.length === 0 && !isSearchingAgent" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl p-4 text-center">
                                                <p class="text-sm text-gray-500 dark:text-gray-400">مشاوری با این مشخصات یافت نشد.</p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200 dark:bg-gray-900/20 dark:border-gray-700">
                                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg dark:bg-indigo-900/30 dark:text-indigo-300">
                                            {{ substr($currentAgentName ?? auth()->user()->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $currentAgentName ?? auth()->user()->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">مشاور مسئول (شما)</p>
                                        </div>
                                        <input type="hidden" name="agent_id" value="{{ $currentAgentId ?? auth()->id() }}">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- کارت نقشه --}}
                    <div class="{{ $cardClass }} p-6">
                        <div class="flex items-center justify-between gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)]"></span>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">موقعیت مکانی</h2>
                            </div>
                            <button type="button" @click="getCurrentLocation" class="text-xs flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                دریافت لوکیشن من
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div id="map" class="w-full h-80 rounded-2xl z-0 border border-gray-200 dark:border-gray-600 shadow-inner"></div>

                            <div>
                                <label class="{{ $labelClass }}">آدرس دقیق</label>
                                <div class="relative">
                                    <textarea name="address" x-model="address" rows="2" class="{{ $inputClass }} resize-none text-right pr-10 leading-relaxed" placeholder="آدرس به صورت خودکار از روی نقشه دریافت می‌شود..."></textarea>
                                    <div class="absolute right-3 top-3 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- دکمه‌های فوتر --}}
            <div class="fixed bottom-0 left-0 right-0 z-30 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 px-4 py-4 lg:hidden">
                <button type="submit" class="w-full px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg hover:bg-indigo-700 transition-all">
                    ذخیره تغییرات
                </button>
            </div>

            {{-- دکمه ذخیره دسکتاپ --}}
            <div class="hidden lg:flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
                <button type="submit" class="px-8 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                    ذخیره تغییرات
                </button>
            </div>

        </form>

        {{-- مدال افزودن مالک --}}
        <div x-show="showOwnerModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] overflow-y-auto"
             style="display: none;">

            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showOwnerModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="showOwnerModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">

                    <div class="bg-gray-50/50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            افزودن مالک جدید
                        </h3>
                        <button @click="showOwnerModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-5">
                        {{-- نمایش خطاهای عمومی --}}
                        <template x-if="errors.general">
                            <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                                <span x-text="errors.general[0]"></span>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">نام</label>
                                <input type="text" x-model="newOwner.first_name" class="{{ $inputClass }}">
                                <template x-if="errors.first_name">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.first_name[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">نام خانوادگی</label>
                                <input type="text" x-model="newOwner.last_name" class="{{ $inputClass }}">
                                <template x-if="errors.last_name">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.last_name[0]"></p>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">شماره تماس</label>
                            <input type="text" x-model="newOwner.phone" class="{{ $inputClass }} dir-ltr text-left" placeholder="0912...">
                            <template x-if="errors.phone">
                                <p class="text-red-500 text-xs mt-1" x-text="errors.phone[0]"></p>
                            </template>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/30 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="createOwner" :disabled="isSavingOwner"
                                class="inline-flex w-full justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed">
                            <span x-show="!isSavingOwner">ثبت و انتخاب</span>
                            <span x-show="isSavingOwner" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            در حال ذخیره...
                        </span>
                        </button>
                        <button type="button" @click="showOwnerModal = false"
                                class="mt-3 inline-flex w-full justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            انصراف
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            jalaliDatepicker.startWatch({
                minDate: "attr",
                maxDate: "attr"
            });
        });

        function editPropertyForm() {
            return {
                activeTab: 'details',

                // Data Properties
                title: @json($property->title),
                description: @json($property->description),
                listingType: '{{ $property->listing_type }}',
                propertyType: '{{ $property->property_type }}',
                documentType: '{{ $property->document_type }}',
                usageType: '{{ $property->usage_type }}',
                deliveryDate: '{{ $property->delivery_date_jalali }}',
                code: '{{ $property->code }}',
                isSpecial: {{ isset($property->meta['is_special']) && $property->meta['is_special'] ? 'true' : 'false' }},
                confidentialNotes: @json($property->confidential_notes),
                isCompletingAI: false,

                // Prices
                prices: {
                    price: '{{ number_format($property->price) }}',
                    min_price: '{{ number_format($property->min_price) }}',
                    deposit_price: '{{ number_format($property->deposit_price) }}',
                    rent_price: '{{ number_format($property->rent_price) }}',
                    advance_price: '{{ number_format($property->advance_price) }}'
                },

                // Image Upload
                coverPreview: null,
                galleryPreviews: [],
                galleryFiles: [],

                // Video Upload
                videoPreview: null,

                // Custom Fields
                customDetails: @json($customDetails),
                customFeatures: @json($customFeatures),

                // Map
                map: null,
                marker: null,
                lat: {{ $property->latitude ?? 35.6892 }},
                lng: {{ $property->longitude ?? 51.3890 }},
                address: '{{ $property->address }}',

                // Owner Management
                owners: @json($owners),
                selectedOwner: '{{ $property->owner_id }}',
                showOwnerModal: false,
                newOwner: {
                    first_name: '',
                    last_name: '',
                    phone: ''
                },
                errors: {},
                isSavingOwner: false,

                // Search Owner
                searchQuery: '',
                isSearching: false,
                showResults: false,
                searchResults: [],

                // Building Search
                searchBuildingQuery: @json(optional($property->building)->name ?? ''),
                searchBuildingResults: [],
                showBuildingResults: false,
                selectedBuildingId: @json($property->building_id),
                isSearchingBuilding: false,

                // Agent Search
                searchAgentQuery: '{{ $currentAgentName ?? auth()->user()->name }}',
                searchAgentResults: [],
                showAgentResults: false,
                selectedAgentId: '{{ $currentAgentId }}',
                isSearchingAgent: false,

                // Voice Typing
                isVoiceTyping: false,
                isVoiceTypingSupported: false,
                recognition: null,

                init() {
                    this.initMap();
                    this.$watch('showOwnerModal', (value) => {
                        if (value) {
                            this.errors = {};
                        }
                    });
                    this.$watch('searchBuildingQuery', (value) => {
                        if (!value) {
                            this.selectedBuildingId = '';
                        }
                    });

                    // Pre-load building results if value exists
                    if (this.searchBuildingQuery.length >= 2) {
                        this.searchBuildings(false);
                    }
                    this.initVoiceTyping();
                },

                // --- Voice Typing Methods ---
                initVoiceTyping() {
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    if (SpeechRecognition) {
                        this.isVoiceTypingSupported = true;
                        this.recognition = new SpeechRecognition();
                        this.recognition.continuous = true;
                        this.recognition.interimResults = true;
                        this.recognition.lang = 'fa-IR';

                        this.recognition.onresult = (event) => {
                            let finalTranscript = '';
                            for (let i = event.resultIndex; i < event.results.length; ++i) {
                                if (event.results[i].isFinal) {
                                    finalTranscript += event.results[i][0].transcript;
                                }
                            }
                            if (finalTranscript) {
                                this.description = this.description ? this.description.trim() + ' ' + finalTranscript.trim() : finalTranscript.trim();
                            }
                        };

                        this.recognition.onend = () => {
                            this.isVoiceTyping = false;
                        };

                        this.recognition.onerror = (event) => {
                            console.error('Speech recognition error', event.error);
                            let errorMessage = 'خطا در تشخیص گفتار.';
                            if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                                errorMessage = 'دسترسی به میکروفون مجاز نیست. لطفاً دسترسی را فعال کنید.';
                            } else if (event.error === 'no-speech') {
                                errorMessage = 'هیچ صدایی شناسایی نشد.';
                            }
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: errorMessage } }));
                            this.isVoiceTyping = false;
                        };
                    } else {
                        this.isVoiceTypingSupported = false;
                        console.warn('Speech Recognition not supported by this browser.');
                    }
                },

                toggleVoiceTyping() {
                    if (!this.isVoiceTypingSupported) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'مرورگر شما از تایپ صوتی پشتیبانی نمی‌کند.' } }));
                        return;
                    }
                    if (this.isVoiceTyping) {
                        this.recognition.stop();
                        this.isVoiceTyping = false;
                    } else {
                        try {
                            this.recognition.start();
                            this.isVoiceTyping = true;
                        } catch(e) {
                            console.error("Error starting recognition:", e);
                            this.isVoiceTyping = false;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'امکان شروع تایپ صوتی وجود ندارد.' } }));
                        }
                    }
                },

                // --- AI Completion ---
                async completeWithAI() {
                    if (!this.description || this.description.length < 10) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'لطفاً حداقل ۱۰ کاراکتر در توضیحات بنویسید.' } }));
                        return;
                    }

                    this.isCompletingAI = true;
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const response = await fetch('{{ route("user.properties.ai.complete") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ description: this.description })
                        });
                        const result = await response.json();

                        if (response.ok && result.data) {
                            const data = result.data;

                            // فقط فیلدهای خالی یا تغییر یافته را به‌روزرسانی کن
                            if (data.title && !this.title) this.title = data.title;
                            if (data.type && !this.propertyType) this.propertyType = data.type;
                            if (data.listing_type && !this.listingType) this.listingType = data.listing_type;
                            if (data.document_type && !this.documentType) this.documentType = data.document_type;
                            if (data.usage_type && !this.usageType) this.usageType = data.usage_type;
                            if (data.delivery_date && !this.deliveryDate) this.deliveryDate = data.delivery_date;
                            // توضیحات را همیشه به‌روزرسانی نکن مگر اینکه کاربر بخواهد (اینجا فرض بر این است که توضیحات منبع است)
                            // if (data.description) this.description = data.description;
                            if (data.address && !this.address) this.address = data.address;
                            if (data.is_special !== undefined && !this.isSpecial) this.isSpecial = data.is_special;
                            if (data.confidential_notes && !this.confidentialNotes) this.confidentialNotes = data.confidential_notes;

                            // به‌روزرسانی قیمت‌ها
                            if (data.prices) {
                                if (data.prices.price && !this.prices.price) this.prices.price = data.prices.price;
                                if (data.prices.min_price && !this.prices.min_price) this.prices.min_price = data.prices.min_price;
                                if (data.prices.deposit_price && !this.prices.deposit_price) this.prices.deposit_price = data.prices.deposit_price;
                                if (data.prices.rent_price && !this.prices.rent_price) this.prices.rent_price = data.prices.rent_price;
                                if (data.prices.advance_price && !this.prices.advance_price) this.prices.advance_price = data.prices.advance_price;
                            }

                            // به‌روزرسانی ویژگی‌های سیستمی (Attributes)
                            if (data.details) {
                                for (const [id, value] of Object.entries(data.details)) {
                                    // پیدا کردن اینپوت مربوطه در DOM
                                    const input = document.querySelector(`.ai-attribute[data-attr-id="${id}"]`);
                                    if (input && !input.value) { // فقط اگر خالی است پر کن
                                        input.value = value;
                                        // تریگر کردن رویداد input برای اطمینان از ذخیره شدن تغییرات اگر از فریم‌ورک‌های دیگر استفاده می‌شود
                                        input.dispatchEvent(new Event('input'));
                                    }
                                }
                            }

                            // به‌روزرسانی امکانات سیستمی (Features)
                            if (data.features && Array.isArray(data.features)) {
                                data.features.forEach(id => {
                                    const checkbox = document.querySelector(`.ai-feature[data-attr-id="${id}"]`);
                                    if (checkbox && !checkbox.checked) { // فقط اگر تیک نخورده است تیک بزن
                                        checkbox.checked = true;
                                        checkbox.dispatchEvent(new Event('change'));
                                    }
                                });
                            }

                            // به‌روزرسانی ویژگی‌های سفارشی (Custom Details)
                            if (data.custom_details) {
                                for (const [key, value] of Object.entries(data.custom_details)) {
                                    // بررسی تکراری نبودن
                                    const exists = this.customDetails.some(d => d.key === key);
                                    if (!exists) {
                                        this.customDetails.push({ key: key, value: value });
                                    }
                                }
                            }

                            // به‌روزرسانی امکانات سفارشی (Custom Features)
                            if (data.custom_features && Array.isArray(data.custom_features)) {
                                data.custom_features.forEach(feature => {
                                    const exists = this.customFeatures.some(f => f.value === feature);
                                    if (!exists) {
                                        this.customFeatures.push({ value: feature });
                                    }
                                });
                            }

                            // جستجوی خودکار مالک اگر نامش پیدا شد و مالک انتخاب نشده بود
                            if (data.owner_name && !this.selectedOwner) {
                                this.searchQuery = data.owner_name;
                                this.searchOwners();
                            }

                            // جستجوی خودکار ساختمان اگر نامش پیدا شد و ساختمان انتخاب نشده بود
                            if (data.building_name && !this.selectedBuildingId) {
                                this.searchBuildingQuery = data.building_name;
                                this.searchBuildings();
                            }

                            // نمایش پیام موفقیت
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: 'اطلاعات با موفقیت تکمیل شد. (فقط موارد جدید اضافه شدند)' } }));

                        } else {
                            // نمایش خطای دریافتی از سرور
                            const errorMessage = result.error || result.message || 'خطا در دریافت اطلاعات.';
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: errorMessage } }));
                        }
                    } catch (error) {
                        console.error('AI Error:', error);
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'خطا در ارتباط با هوش مصنوعی.' } }));
                    } finally {
                        this.isCompletingAI = false;
                    }
                },

                // --- Owner Management ---
                async createOwner() {
                    this.errors = {};
                    this.isSavingOwner = true;

                    try {
                        const response = await fetch('{{ route("user.properties.owners.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.newOwner)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.selectOwner(data.owner);
                            this.showOwnerModal = false;
                            this.newOwner = { first_name: '', last_name: '', phone: '' };
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: 'مالک جدید با موفقیت ایجاد شد.' } }));
                        } else if (response.status === 422) {
                            this.errors = data.errors;
                        } else {
                            this.errors.general = [data.message || 'یک خطای غیرمنتظره رخ داد.'];
                        }
                    } catch (error) {
                        console.error('Error creating owner:', error);
                        this.errors.general = ['خطا در برقراری ارتباط با سرور.'];
                    } finally {
                        this.isSavingOwner = false;
                    }
                },

                async searchOwners() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        this.showResults = false;
                        return;
                    }

                    this.isSearching = true;
                    this.showResults = true;

                    try {
                        const response = await fetch(`{{ route('user.properties.owners.search') }}?q=${this.searchQuery}`);
                        const data = await response.json();
                        this.searchResults = data.data || data;
                    } catch (error) {
                        console.error('Search error:', error);
                        this.searchResults = [];
                    } finally {
                        this.isSearching = false;
                    }
                },

                selectOwner(owner) {
                    this.selectedOwner = owner.id;
                    this.searchQuery = owner.first_name + ' ' + owner.last_name;
                    this.showResults = false;
                },

                // --- Building Search ---
                async searchBuildings(showDropdown = true) {
                    if (this.searchBuildingQuery.length < 2) {
                        this.searchBuildingResults = [];
                        this.showBuildingResults = false;
                        return;
                    }
                    this.isSearchingBuilding = true;
                    try {
                        const response = await fetch(`{{ route('user.properties.buildings.search') }}?q=${this.searchBuildingQuery}`);
                        const data = await response.json();
                        this.searchBuildingResults = data;
                        if (showDropdown) {
                            this.showBuildingResults = true;
                        }
                    } catch (error) {
                        console.error('Building Search error:', error);
                    } finally {
                        this.isSearchingBuilding = false;
                    }
                },

                handleBuildingFocus() {
                    if (this.searchBuildingQuery.length >= 2) {
                        if (this.searchBuildingResults.length === 0) {
                            this.searchBuildings();
                        }
                        this.showBuildingResults = true;
                    }
                },

                selectBuilding(building) {
                    this.selectedBuildingId = building.id;
                    this.searchBuildingQuery = building.name;
                    this.showBuildingResults = false;
                },

                // --- Agent Search ---
                async searchAgents() {
                    if (this.searchAgentQuery.length < 2) {
                        this.searchAgentResults = [];
                        this.showAgentResults = false;
                        return;
                    }
                    this.isSearchingAgent = true;
                    try {
                        const response = await fetch(`{{ route('user.properties.agents.search') }}?q=${this.searchAgentQuery}`);
                        const data = await response.json();
                        this.searchAgentResults = data;
                        this.showAgentResults = true;
                    } catch (error) {
                        console.error('Agent Search error:', error);
                    } finally {
                        this.isSearchingAgent = false;
                    }
                },

                selectAgent(agent) {
                    this.selectedAgentId = agent.id;
                    this.searchAgentQuery = agent.name;
                    this.showAgentResults = false;
                },

                // --- Custom Fields Handling ---
                addDetailField() {
                    this.customDetails.push({ key: '', value: '' });
                },
                removeDetailField(index) {
                    this.customDetails.splice(index, 1);
                },
                addFeatureField() {
                    this.customFeatures.push({ value: '' });
                },
                removeFeatureField(index) {
                    this.customFeatures.splice(index, 1);
                },

                // --- Image Handling ---
                handleCoverSelect(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.previewFile(file, (url) => this.coverPreview = url);
                    }
                },
                removeCover() {
                    this.coverPreview = null;
                    document.getElementById('cover_image').value = '';
                },

                handleGallerySelect(e) {
                    const files = Array.from(e.target.files);
                    this.processGalleryFiles(files);
                },
                processGalleryFiles(files) {
                    files.forEach(file => {
                        this.galleryFiles.push(file);
                        this.previewFile(file, (url) => this.galleryPreviews.push(url));
                    });
                    this.updateGalleryInput();
                },
                updateGalleryInput() {
                    const input = document.querySelector('input[name="gallery_images[]"]');
                    const dataTransfer = new DataTransfer();
                    this.galleryFiles.forEach(file => dataTransfer.items.add(file));
                    input.files = dataTransfer.files;
                },
                removeGalleryImage(index) {
                    this.galleryPreviews.splice(index, 1);
                    this.galleryFiles.splice(index, 1);
                    this.updateGalleryInput();
                },
                previewFile(file, callback) {
                    const reader = new FileReader();
                    reader.onload = (e) => callback(e.target.result);
                    reader.readAsDataURL(file);
                },

                // --- Video Handling ---
                handleVideoSelect(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const url = URL.createObjectURL(file);
                        this.videoPreview = url;
                    }
                },
                removeVideo() {
                    this.videoPreview = null;
                    document.getElementById('video').value = '';
                },

                async deleteImage(id) {
                    if (!confirm('آیا از حذف این تصویر اطمینان دارید؟')) return;

                    try {
                        const response = await fetch(`{{ url('user/properties/image') }}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            document.getElementById(`image-${id}`).remove();
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: 'تصویر حذف شد' } }));
                        } else {
                            alert('خطا در حذف تصویر');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('خطا در برقراری ارتباط');
                    }
                },

                // --- Map Handling ---
                initMap() {
                    // Initialize map using global L object
                    this.map = L.map('map').setView([this.lat, this.lng], 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(this.map);

                    // Add initial marker if exists
                    if ({{ $property->latitude ? 'true' : 'false' }}) {
                        this.marker = L.marker([this.lat, this.lng]).addTo(this.map);
                    }

                    // Add Search Control
                    const provider = new OpenStreetMapProvider();
                    const searchControl = new GeoSearchControl({
                        provider: provider,
                        style: 'bar',
                        searchLabel: 'جستجوی آدرس...',
                        notFoundMessage: 'آدرس یافت نشد',
                        showMarker: false,
                        retainZoomLevel: false,
                        animateZoom: true,
                        autoClose: true,
                    });
                    this.map.addControl(searchControl);

                    this.map.on('geosearch/showlocation', (result) => {
                        const { x, y } = result.location;
                        this.updateLocation(y, x);
                    });

                    // Add marker on click
                    this.map.on('click', (e) => {
                        this.updateLocation(e.latlng.lat, e.latlng.lng);
                    });
                },
                updateLocation(lat, lng) {
                    this.lat = lat;
                    this.lng = lng;
                    if (this.marker) {
                        this.marker.setLatLng([lat, lng]);
                    } else {
                        this.marker = L.marker([lat, lng]).addTo(this.map);
                    }
                    this.getAddress(lat, lng);
                },
                getCurrentLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition((position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            this.map.setView([lat, lng], 15);
                            this.updateLocation(lat, lng);
                        }, (error) => {
                            alert('خطا در دریافت موقعیت: ' + error.message);
                        });
                    } else {
                        alert('مرورگر شما از موقعیت مکانی پشتیبانی نمی‌کند.');
                    }
                },
                async getAddress(lat, lng) {
                    // Only fetch if user clicked, not on init unless empty
                    this.address = 'در حال دریافت آدرس...';
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=fa`);
                        const data = await res.json();
                        if (data && data.address) {
                            const addr = data.address;
                            const state = addr.state || '';
                            const city = addr.city || addr.town || addr.village || addr.county || '';
                            const details = [];
                            if (addr.suburb) details.push(addr.suburb);
                            if (addr.neighbourhood) details.push(addr.neighbourhood);
                            if (addr.district) details.push(addr.district);
                            if (addr.road) details.push(addr.road);
                            if (addr.pedestrian) details.push(addr.pedestrian);
                            if (addr.house_number) details.push('پلاک ' + addr.house_number);
                            const uniqueDetails = [...new Set(details)].filter(Boolean);
                            const exactAddress = uniqueDetails.join('، ');
                            const parts = [state, city, exactAddress].filter(Boolean);
                            this.address = parts.join('، ');
                        } else if (data && data.display_name) {
                            this.address = data.display_name;
                        } else {
                            this.address = 'آدرس یافت نشد';
                        }
                    } catch (error) {
                        console.error('Error fetching address:', error);
                        this.address = 'خطا در دریافت آدرس';
                    }
                }
            }
        }

        function priceFormatter() {
            return {
                isConvertible: {{ old('is_convertible', $property->is_convertible) ? 'true' : 'false' }},
                formatPrice(event) {
                    let value = event.target.value.replace(/,/g, '');
                    if (!isNaN(value) && value !== '') {
                        event.target.value = parseInt(value).toLocaleString('en-US');
                    }
                }
            }
        }
    </script>
@endsection
