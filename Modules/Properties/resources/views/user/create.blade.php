@extends('layouts.user')

@php
    $title = 'افزودن ملک جدید';

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-visible transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
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
    // اگر ادمین باشد یا نقش مشاور نداشته باشد (مثلا کال سنتر)، می‌تواند تغییر دهد.
    // اگر نقش مشاور داشته باشد (و ادمین نباشد)، نمی‌تواند.
    $canChangeAgent = $isAdmin || !$isAgent;

    $defaultAgentId = $isAgent ? $user->id : null; // If user is an agent, default to themselves
    $defaultAgentName = $isAgent ? $user->name : null;

    // بررسی فعال بودن هوش مصنوعی
    $aiEnabled = \Modules\Properties\Entities\PropertySetting::get('ai_property_completion', 0);
@endphp

@section('content')

    {{-- استایل‌های اختصاصی نقشه برای دارک‌مود --}}
    <style>
        .leaflet-control-geosearch form {
            background: var(--bg-white, #ffffff);
            border-radius: 0.75rem;
            padding: 0.25rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .dark .leaflet-control-geosearch form {
            background: #1f2937;
            border-color: #374151;
            color: #f3f4f6;
        }
        .leaflet-control-geosearch input {
            color: inherit;
            background: transparent;
            border-radius: 0.5rem;
            height: 2.5rem;
            font-size: 0.875rem;
        }
        .dark .leaflet-control-geosearch input { color: #f3f4f6; }

        .leaflet-control-geosearch .results {
            background: white;
            border-radius: 0.5rem;
            margin-top: 6px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            z-index: 1000 !important;
        }
        .dark .leaflet-control-geosearch .results {
            background: #1f2937;
            border-color: #374151;
        }
        .leaflet-control-geosearch .results > * {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            font-size: 0.875rem;
        }
        .dark .leaflet-control-geosearch .results > * {
            border-bottom-color: #374151;
            color: #d1d5db;
        }
        .leaflet-control-geosearch .results > :hover {
            background-color: #f9fafb;
        }
        .dark .leaflet-control-geosearch .results > :hover {
            background-color: #374151;
            color: white;
        }
        .leaflet-control-geosearch a.reset {
            background-color: transparent;
            line-height: 40px;
            padding: 0 10px;
            color: #9ca3af;
        }
        .leaflet-container {
            font-family: inherit;
            z-index: 0;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 py-8" x-data="propertyForm()">

        {{-- هدر صفحه --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </span>
                    افزودن ملک جدید
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">مشخصات اصلی و تصاویر ملک را وارد کنید.</p>
            </div>
        </div>

        <form id="property-form" action="{{ route('user.properties.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm" novalidate>
            @csrf
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

            <div class="grid grid-cols-12 gap-6">

                {{-- ستون چپ (مدیا و وضعیت) --}}
                <div class="col-span-12 lg:col-span-4 space-y-6 order-2 lg:order-1">

                    {{-- کارت تصویر شاخص --}}
                    <div class="{{ $cardClass }} p-5">
                        <div class="flex items-center justify-between mb-4">
                            <label class="{{ $labelClass }} mb-0">تصویر شاخص <span class="text-red-500">*</span></label>
                            <span class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded dark:bg-indigo-900/30 dark:text-indigo-300">Cover</span>
                        </div>

                        <div class="relative w-full aspect-[4/3] rounded-xl border-2 border-dashed flex flex-col items-center justify-center transition-all overflow-hidden group"
                             :class="coverPreview ? 'border-indigo-500 bg-white dark:bg-gray-800' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50 hover:border-indigo-400 dark:hover:border-indigo-500'"
                             @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="handleCoverDrop($event)">

                            <template x-if="!coverPreview">
                                <div class="text-center p-4 pointer-events-none">
                                    <div class="w-12 h-12 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3 text-gray-400 dark:text-gray-500">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">کلیک یا رها کردن تصویر</p>
                                </div>
                            </template>

                            <template x-if="coverPreview">
                                <div class="absolute inset-0 w-full h-full group-hover:scale-105 transition-transform duration-500">
                                    <img :src="coverPreview" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button" @click="removeCover" class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 shadow-lg transform hover:scale-110 transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <input type="file" name="cover_image" id="cover_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleCoverSelect" accept="image/*">
                        </div>
                    </div>

                    {{-- کارت گالری --}}
                    <div class="{{ $cardClass }} p-5">
                        <div class="flex items-center justify-between mb-4">
                            <label class="{{ $labelClass }} mb-0">گالری تصاویر</label>
                            <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="enable_gallery" id="enable_gallery" x-model="enableGallery" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer peer checked:right-0 right-5"/>
                                <label for="enable_gallery" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 dark:bg-gray-700 cursor-pointer peer-checked:bg-indigo-500"></label>
                            </div>
                        </div>

                        <div x-show="enableGallery" x-transition class="space-y-3">
                            <div class="grid grid-cols-3 gap-3">
                                <template x-for="(img, index) in galleryPreviews" :key="index">
                                    <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 group">
                                        <img :src="img" class="w-full h-full object-cover">
                                        <button type="button" @click="removeGalleryImage(index)" class="absolute top-1 right-1 bg-red-500/90 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-all hover:scale-110">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </template>

                                <label class="aspect-square border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition-colors text-gray-400 hover:text-indigo-500">
                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span class="text-[10px]">افزودن</span>
                                    <input type="file" name="gallery_images[]" multiple class="hidden" @change="handleGallerySelect" accept="image/*">
                                </label>
                            </div>
                            <p class="text-[10px] text-gray-400 text-center">حداکثر ۱۰ تصویر قابل بارگذاری است.</p>
                        </div>
                    </div>

                    {{-- کارت ویدیو --}}
                    <div class="{{ $cardClass }} p-5">
                        <label class="{{ $labelClass }}">ویدیو معرفی (اختیاری)</label>
                        <div class="relative w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl flex flex-col items-center justify-center hover:border-indigo-400 transition-colors bg-gray-50 dark:bg-gray-900/30 overflow-hidden">

                            <template x-if="!videoPreview">
                                <div class="text-center pointer-events-none">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    <p class="mt-1 text-[10px] text-gray-500">MP4, MKV (Max 20MB)</p>
                                </div>
                            </template>

                            <template x-if="videoPreview">
                                <div class="w-full h-full relative group">
                                    <video :src="videoPreview" class="w-full h-full object-cover"></video>
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" @click="removeVideo" class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <input type="file" name="video" id="video" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleVideoSelect" accept="video/*" x-show="!videoPreview">
                        </div>
                    </div>

                    {{-- کارت تنظیمات انتشار --}}
                    <div class="{{ $cardClass }} p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            تنظیمات انتشار
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="{{ $labelClass }}">تاریخ ثبت</label>
                                <input type="text" name="registered_at" data-jdp value="{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}" class="{{ $inputClass }} text-center">
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">وضعیت ملک</label>
                                <div class="relative">
                                    <select name="status_id" class="{{ $selectClass }}">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">وضعیت انتشار</label>
                                <div class="flex gap-2">
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="publication_status" value="published" class="peer sr-only" checked>
                                        <div class="text-center py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 peer-checked:bg-emerald-50 peer-checked:text-emerald-600 peer-checked:border-emerald-200 dark:peer-checked:bg-emerald-900/20 dark:peer-checked:border-emerald-800 transition-all text-xs font-bold">
                                            منتشر شده
                                        </div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="publication_status" value="draft" class="peer sr-only">
                                        <div class="text-center py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 peer-checked:bg-amber-50 peer-checked:text-amber-600 peer-checked:border-amber-200 dark:peer-checked:bg-amber-900/20 dark:peer-checked:border-amber-800 transition-all text-xs font-bold">
                                            پیش‌نویس
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_special" value="1" x-model="isSpecial" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">آگهی ویژه / فوری</span>
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-7">با فعال کردن این گزینه، ملک با نشان ویژه نمایش داده می‌شود.</p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">یادداشت محرمانه</label>
                                <textarea name="confidential_notes" x-model="confidentialNotes" rows="3" class="{{ $inputClass }} resize-none" placeholder="یادداشت خصوصی برای مدیران..."></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ستون راست (اطلاعات اصلی) --}}
                <div class="col-span-12 lg:col-span-8 space-y-6 order-1 lg:order-2">

                    {{-- کارت اطلاعات پایه --}}
                    <div class="{{ $cardClass }} p-6">
                        <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <span class="w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></span>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">اطلاعات پایه ملک</h2>
                        </div>

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="md:col-span-3">
                                    <label class="{{ $labelClass }}">عنوان ملک <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" x-model="title" class="{{ $inputClass }}" required placeholder="مثلاً: آپارتمان ۱۲۰ متری نوساز در خیابان اصلی">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">نوع فایل</label>
                                    <select name="listing_type" x-model="listingType" class="{{ $selectClass }}">
                                        <option value="sale">فروش</option>
                                        <option value="presale">پیش‌فروش</option>
                                        <option value="rent">رهن و اجاره</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="{{ $labelClass }}">نوع ملک</label>
                                    <select name="property_type" x-model="propertyType" class="{{ $selectClass }}">
                                        <option value="apartment">خانه و آپارتمان</option>
                                        <option value="villa">ویلا و باغچه</option>
                                        <option value="land">زمین و کلنگی</option>
                                        <option value="office">اداری و تجاری</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">نوع سند</label>
                                    <select name="document_type" x-model="documentType" class="{{ $selectClass }}">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach(\Modules\Properties\Entities\Property::DOCUMENT_TYPES as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
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
                                               @focus="if(searchBuildingQuery.length >= 2) showBuildingResults = true"
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="propertyType === 'land' || listingType === 'presale'" x-transition>
                                <div x-show="propertyType === 'land'">
                                    <label class="{{ $labelClass }}">نوع کاربری</label>
                                    <select name="usage_type" x-model="usageType" class="{{ $selectClass }}">
                                        <option value="">انتخاب کنید...</option>
                                        <option value="residential">مسکونی</option>
                                        <option value="industrial">صنعتی</option>
                                        <option value="commercial">اداری / تجاری</option>
                                        <option value="agricultural">کشاورزی</option>
                                    </select>
                                </div>
                                <div x-show="listingType === 'presale'">
                                    <label class="{{ $labelClass }}">تاریخ تحویل</label>
                                    <input type="text" name="delivery_date" x-model="deliveryDate" data-jdp class="{{ $inputClass }} text-center" placeholder="1404/01/01">
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="{{ $labelClass }} mb-0">توضیحات تکمیلی</label>
                                    @if($aiEnabled)
                                        <button type="button" @click="completeWithAI" :disabled="isCompletingAI" class="text-xs flex items-center gap-1.5 px-3 py-1 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:hover:bg-purple-900/40 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg x-show="!isCompletingAI" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                            <svg x-show="isCompletingAI" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            تکمیل هوشمند
                                        </button>
                                    @endif
                                </div>
                                <textarea name="description" x-model="description" rows="4" class="{{ $inputClass }} resize-none leading-relaxed" placeholder="جزئیات بیشتر درباره امکانات، دسترسی‌ها و شرایط ملک..."></textarea>
                                <p class="text-[10px] text-gray-500 mt-1">برای استفاده از هوش مصنوعی، ابتدا توضیحات را بنویسید و سپس دکمه تکمیل هوشمند را بزنید.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-100 dark:border-gray-700/50">
                                <div>
                                    <label class="{{ $labelClass }}">کد ملک</label>
                                    <div class="flex">
                                        <select x-model="codeType" class="{{ $selectClass }} rounded-l-none border-l-0 w-28 bg-white dark:bg-gray-800">
                                            <option value="auto">اتوماتیک</option>
                                            <option value="manual">دستی</option>
                                        </select>
                                        <input type="text" name="code" x-model="code" x-show="codeType === 'manual'" class="{{ $inputClass }} rounded-r-none" placeholder="کد دلخواه">
                                        <div x-show="codeType === 'auto'" class="{{ $inputClass }} rounded-r-none bg-gray-100 text-gray-500 flex items-center justify-center cursor-not-allowed dark:bg-gray-800 dark:text-gray-500">
                                            تولید خودکار توسط سیستم
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">دسته‌بندی داخلی</label>
                                    <select name="category_id" class="{{ $selectClass }} bg-white dark:bg-gray-800">
                                        <option value="">بدون دسته‌بندی</option>
                                        @foreach(\Modules\Properties\Entities\PropertyCategory::where('user_id', auth()->id())->get() as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
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
                            <label class="{{ $labelClass }}">جستجو یا انتخاب مالک</label>
                            <div class="flex gap-2">
                                <input type="hidden" name="owner_id" x-model="selectedOwnerId">

                                <div class="relative flex-1 group">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </div>
                                    <input type="text"
                                           x-model="searchQuery"
                                           @input.debounce.300ms="searchOwners()"
                                           @focus="if(searchQuery.length >= 2) showResults = true"
                                           @click.outside="showResults = false"
                                           class="{{ $inputClass }} pr-10"
                                           placeholder="نام، نام خانوادگی یا شماره تماس..."
                                           autocomplete="off">

                                    {{-- Loading Indicator --}}
                                    <div x-show="isSearching" class="absolute left-3 top-2.5">
                                        <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    {{-- Results Dropdown --}}
                                    <div x-show="showResults && searchResults.length > 0"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar">
                                        <ul class="py-1">
                                            <template x-for="owner in searchResults" :key="owner.id">
                                                <li @click="selectOwner(owner)" class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700/50 last:border-0 transition-colors group/item">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover/item:text-indigo-600 dark:group-hover/item:text-indigo-400" x-text="owner.first_name + ' ' + owner.last_name"></span>
                                                            <span class="text-xs text-gray-500 dark:text-gray-400 dir-ltr text-right mt-0.5" x-text="owner.phone"></span>
                                                        </div>
                                                        <svg class="w-4 h-4 text-gray-300 group-hover/item:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div x-show="showResults && searchResults.length === 0 && !isSearching" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl p-4 text-center">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">مالکی با این مشخصات یافت نشد.</p>
                                    </div>
                                </div>

                                <button type="button" @click="showOwnerModal = true" class="px-4 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300 rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors border border-indigo-100 dark:border-indigo-800" title="افزودن مالک جدید">
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
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">مشاور مسئول (شما)</p>
                                        </div>
                                        <input type="hidden" name="agent_id" value="{{ auth()->id() }}">
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
            <div class="fixed bottom-0 left-0 right-0 z-30 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 px-4 py-4">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <a href="{{ route('user.properties.index') }}" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
                        انصراف
                    </a>
                    <button type="submit"
                            class="px-8 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all flex items-center gap-2"
                            :disabled="isSubmitting"
                            :class="{'opacity-70 cursor-not-allowed': isSubmitting}">
                        <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="isSubmitting ? 'در حال پردازش...' : 'ذخیره و ادامه (قیمت‌گذاری)'"></span>
                        <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </button>
                </div>
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
                        <template x-if="ownerErrors.general">
                            <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                                <span x-text="ownerErrors.general[0]"></span>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">نام</label>
                                <input type="text" x-model="newOwner.first_name" class="{{ $inputClass }}">
                                <template x-if="ownerErrors.first_name">
                                    <p class="text-red-500 text-xs mt-1" x-text="ownerErrors.first_name[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">نام خانوادگی</label>
                                <input type="text" x-model="newOwner.last_name" class="{{ $inputClass }}">
                                <template x-if="ownerErrors.last_name">
                                    <p class="text-red-500 text-xs mt-1" x-text="ownerErrors.last_name[0]"></p>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">شماره تماس</label>
                            <input type="text" x-model="newOwner.phone" class="{{ $inputClass }} dir-ltr text-left" placeholder="0912...">
                            <template x-if="ownerErrors.phone">
                                <p class="text-red-500 text-xs mt-1" x-text="ownerErrors.phone[0]"></p>
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

    {{-- اسکریپت‌های مربوط به نقشه و فرم --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if(typeof jalaliDatepicker !== 'undefined') {
                jalaliDatepicker.startWatch({ minDate: "attr", maxDate: "attr" });
            }
        });

        function propertyForm() {
            return {
                listingType: 'sale',
                propertyType: 'apartment',
                documentType: '',
                usageType: '',
                deliveryDate: '',
                codeType: 'auto',
                code: '',
                isSubmitting: false,
                title: '',
                description: '',
                isSpecial: false,
                confidentialNotes: '',

                // قیمت‌ها
                prices: {
                    price: '',
                    min_price: '',
                    deposit_price: '',
                    rent_price: '',
                    advance_price: ''
                },

                // مدیا
                coverPreview: null,
                enableGallery: false,
                galleryPreviews: [],
                galleryFiles: [],
                videoPreview: null,

                // نقشه
                map: null,
                marker: null,
                lat: 35.6892,
                lng: 51.3890,
                address: '',

                // مالک
                searchQuery: '',
                searchResults: [],
                showResults: false,
                selectedOwnerId: '',
                isSearching: false,
                showOwnerModal: false,
                newOwner: { first_name: '', last_name: '', phone: '' },
                ownerErrors: {},
                isSavingOwner: false,

                // Building Search
                searchBuildingQuery: '',
                searchBuildingResults: [],
                showBuildingResults: false,
                selectedBuildingId: '',
                isSearchingBuilding: false,

                // Agent Search
                searchAgentQuery: '{{ $canChangeAgent ? '' : $defaultAgentName }}',
                searchAgentResults: [],
                showAgentResults: false,
                selectedAgentId: '{{ $canChangeAgent ? '' : $defaultAgentId }}',
                isSearchingAgent: false,
                canChangeAgent: {{ $canChangeAgent ? 'true' : 'false' }}, // Pass PHP variable to Alpine.js

                // AI
                isCompletingAI: false,

                init() {
                    this.initMap();
                    this.$watch('showOwnerModal', (value) => {
                        if (value) this.ownerErrors = {};
                    });
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

                            // پر کردن فیلدها
                            if (data.title) this.title = data.title;
                            if (data.type) this.propertyType = data.type;
                            if (data.listing_type) this.listingType = data.listing_type;
                            if (data.document_type) this.documentType = data.document_type;
                            if (data.usage_type) this.usageType = data.usage_type;
                            if (data.delivery_date) this.deliveryDate = data.delivery_date;
                            if (data.description) this.description = data.description;
                            if (data.code) {
                                this.codeType = 'manual';
                                this.code = data.code;
                            }
                            if (data.address) this.address = data.address;
                            if (data.is_special !== undefined) this.isSpecial = data.is_special;
                            if (data.confidential_notes) this.confidentialNotes = data.confidential_notes;

                            // پر کردن قیمت‌ها
                            if (data.prices) {
                                this.prices = { ...this.prices, ...data.prices };
                            }

                            // ایجاد فیلدهای مخفی برای جزئیات (Details)
                            const attrContainer = document.getElementById('ai-attributes-container');
                            attrContainer.innerHTML = '';
                            if (data.details) {
                                for (const [id, value] of Object.entries(data.details)) {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = `attributes[${id}]`;
                                    input.value = value;
                                    attrContainer.appendChild(input);
                                }
                            }

                            // ایجاد فیلدهای مخفی برای امکانات (Features)
                            const featContainer = document.getElementById('ai-features-container');
                            featContainer.innerHTML = '';
                            if (data.features && Array.isArray(data.features)) {
                                data.features.forEach(id => {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = `features[]`;
                                    input.value = id;
                                    featContainer.appendChild(input);
                                });
                            }

                            // جستجوی خودکار مالک اگر نامش پیدا شد
                            if (data.owner_name) {
                                this.searchQuery = data.owner_name;
                                this.searchOwners();
                            }

                            // جستجوی خودکار ساختمان اگر نامش پیدا شد
                            if (data.building_name) {
                                this.searchBuildingQuery = data.building_name;
                                this.searchBuildings();
                            }

                            // نمایش پیام موفقیت
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: 'اطلاعات با موفقیت تکمیل شد.' } }));

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

                // --- جستجو و ایجاد مالک ---
                async searchOwners() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        this.showResults = false;
                        return;
                    }
                    this.isSearching = true;
                    try {
                        const response = await fetch(`{{ route('user.properties.owners.search') }}?q=${this.searchQuery}`);
                        const data = await response.json();
                        this.searchResults = data.data || data; // هندل کردن ساختار ریسپانس
                        this.showResults = true;
                    } catch (error) {
                        console.error('Search error:', error);
                    } finally {
                        this.isSearching = false;
                    }
                },

                selectOwner(owner) {
                    this.selectedOwnerId = owner.id;
                    this.searchQuery = `${owner.first_name} ${owner.last_name} (${owner.phone})`;
                    this.showResults = false;
                },

                async createOwner() {
                    this.ownerErrors = {};
                    this.isSavingOwner = true;
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const response = await fetch('{{ route("user.properties.owners.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.newOwner)
                        });
                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.selectOwner(data.owner);
                            this.showOwnerModal = false;
                            this.newOwner = { first_name: '', last_name: '', phone: '' };
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: 'مالک ایجاد شد.' } }));
                        } else if (response.status === 422) {
                            this.ownerErrors = data.errors;
                        } else {
                            this.ownerErrors.general = [data.message || 'خطای ناشناخته'];
                        }
                    } catch (error) {
                        this.ownerErrors.general = ['خطا در ارتباط با سرور'];
                    } finally {
                        this.isSavingOwner = false;
                    }
                },

                // --- Building Search ---
                async searchBuildings() {
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
                        this.showBuildingResults = true;
                    } catch (error) {
                        console.error('Building Search error:', error);
                    } finally {
                        this.isSearchingBuilding = false;
                    }
                },

                selectBuilding(building) {
                    this.selectedBuildingId = building.id;
                    this.searchBuildingQuery = building.name;
                    this.showBuildingResults = false;
                },

                // --- Agent Search ---
                async searchAgents() {
                    if (!this.canChangeAgent || this.searchAgentQuery.length < 2) { // Only search if canChangeAgent is true
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
                    if (!this.canChangeAgent) return; // Prevent selection if not allowed

                    this.selectedAgentId = agent.id;
                    this.searchAgentQuery = agent.name;
                    this.showAgentResults = false;
                },

                // --- هندلینگ تصاویر ---
                handleCoverSelect(e) {
                    const file = e.target.files[0];
                    if (file) this.previewFile(file, (url) => this.coverPreview = url);
                },
                handleCoverDrop(e) {
                    const file = e.dataTransfer.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const input = document.getElementById('cover_image');
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
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
                    if (this.galleryPreviews.length + files.length > 10) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'حداکثر ۱۰ تصویر مجاز است.' } }));
                        return;
                    }
                    files.forEach(file => {
                        this.galleryFiles.push(file);
                        this.previewFile(file, (url) => this.galleryPreviews.push(url));
                    });
                    this.updateGalleryInput();
                },
                updateGalleryInput() {
                    const input = document.querySelector('input[name="gallery_images[]"]');
                    const dt = new DataTransfer();
                    this.galleryFiles.forEach(file => dt.items.add(file));
                    input.files = dt.files;
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

                // --- هندلینگ ویدیو ---
                handleVideoSelect(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.videoPreview = URL.createObjectURL(file);
                    }
                },
                removeVideo() {
                    this.videoPreview = null;
                    document.getElementById('video').value = '';
                },

                // --- نقشه ---
                initMap() {
                    // اطمینان از لود شدن کتابخانه لیفلت
                    if (typeof L === 'undefined') return;

                    this.map = L.map('map').setView([this.lat, this.lng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(this.map);

                    // جستجوگر آدرس
                    if (typeof GeoSearchControl !== 'undefined' && typeof OpenStreetMapProvider !== 'undefined') {
                        const provider = new OpenStreetMapProvider();
                        const searchControl = new GeoSearchControl({
                            provider: provider,
                            style: 'bar',
                            searchLabel: 'جستجوی آدرس...',
                            notFoundMessage: 'یافت نشد',
                            showMarker: false,
                            retainZoomLevel: false,
                            animateZoom: true,
                            autoClose: true,
                        });
                        this.map.addControl(searchControl);
                    }

                    this.map.on('geosearch/showlocation', (result) => {
                        const { x, y } = result.location;
                        this.updateLocation(y, x);
                    });

                    this.map.on('click', (e) => {
                        this.updateLocation(e.latlng.lat, e.latlng.lng);
                    });

                    // مارکر اولیه
                    this.updateLocation(this.lat, this.lng);
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
                        navigator.geolocation.getCurrentPosition((pos) => {
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;
                            this.map.setView([lat, lng], 15);
                            this.updateLocation(lat, lng);
                        }, (err) => {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'خطا در دریافت موقعیت' } }));
                        });
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'مرورگر پشتیبانی نمی‌کند' } }));
                    }
                },
                async getAddress(lat, lng) {
                    this.address = 'در حال دریافت آدرس...';
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=fa`);
                        const data = await res.json();
                        if (data && data.address) {
                            const a = data.address;
                            const parts = [
                                a.state,
                                a.city || a.town || a.village || a.county,
                                a.road,
                                a.house_number ? 'پلاک ' + a.house_number : ''
                            ].filter(Boolean);
                            this.address = parts.join('، ');
                        } else if (data.display_name) {
                            this.address = data.display_name;
                        }
                    } catch (e) {
                        this.address = 'خطا در دریافت آدرس';
                    }
                },

                // --- ثبت فرم ---
                async submitForm(e) {
                    if (this.isSubmitting) return;
                    this.isSubmitting = true;
                    const form = e.target;
                    const formData = new FormData(form);

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();

                        if (res.ok) {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: data.message || 'ثبت شد.' } }));
                            if (data.redirect_url) {
                                setTimeout(() => window.location.href = data.redirect_url, 1000);
                            }
                        } else {
                            if (data.errors) {
                                Object.values(data.errors).flat().forEach(err => {
                                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: err } }));
                                });
                            } else {
                                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: data.message || 'خطا در ثبت.' } }));
                            }
                        }
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'خطای سرور' } }));
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
@endsection
