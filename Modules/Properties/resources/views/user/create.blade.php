@extends('layouts.user')

@php
    $title = 'افزودن ملک جدید - مرحله ۱: مشخصات اصلی';
@endphp

@section('content')

<style>
    /* Fix Geosearch Z-Index & Dark Mode */
    .leaflet-control-geosearch {
        z-index: 800;
    }
    .leaflet-control-geosearch form {
        background: white;
        border-radius: 0.75rem; /* rounded-xl */
        padding: 0.25rem;
        border: 1px solid #e5e7eb; /* border-gray-200 */
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }

    /* Dark Mode Styles */
    .dark .leaflet-control-geosearch form {
        background: #1f2937; /* bg-gray-800 */
        border-color: #374151; /* border-gray-700 */
        color: #f3f4f6; /* text-gray-100 */
    }

    .leaflet-control-geosearch input {
        color: inherit;
        background: transparent;
        border-radius: 0.5rem;
    }

    .dark .leaflet-control-geosearch input {
        color: #f3f4f6;
    }

    .leaflet-control-geosearch .results {
        background: white;
        border-radius: 0.5rem;
        margin-top: 4px;
        border: 1px solid #e5e7eb;
    }

    .dark .leaflet-control-geosearch .results {
        background: #1f2937;
        border-color: #374151;
    }

    .leaflet-control-geosearch .results > * {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
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
        color: #6b7280;
        padding: 0 8px;
        line-height: 30px;
    }

    .dark .leaflet-control-geosearch a.reset {
        color: #9ca3af;
        background: #1f2937;
    }
</style>

<div class="max-w-6xl mx-auto" x-data="propertyForm()">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">افزودن ملک جدید</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">مرحله ۱: مشخصات اصلی ملک را وارد کنید.</p>

        <form id="property-form" action="{{ route('user.properties.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm" novalidate>
            @csrf

            {{-- Hidden Inputs for Map --}}
            <input type="hidden" name="latitude" x-model="lat">
            <input type="hidden" name="longitude" x-model="lng">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Left Column: Images & File Info --}}
                <div class="lg:col-span-1 space-y-6">

                    {{-- Cover Image --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">عکس شاخص (اجباری)</label>
                        <div
                            class="relative w-full h-64 border-2 border-dashed rounded-xl flex flex-col items-center justify-center transition-colors"
                            :class="coverPreview ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-400'"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleCoverDrop($event)"
                        >
                            <template x-if="!coverPreview">
                                <div class="text-center p-4 pointer-events-none">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">کلیک یا رها کردن عکس</p>
                                </div>
                            </template>

                            <template x-if="coverPreview">
                                <div class="absolute inset-0 w-full h-full">
                                    <img :src="coverPreview" class="w-full h-full object-cover rounded-xl">
                                    <button type="button" @click="removeCover" class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>

                            <input type="file" name="cover_image" id="cover_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleCoverSelect" accept="image/*">
                        </div>
                    </div>

                    {{-- Gallery Images --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">گالری تصاویر</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="enableGallery" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                <span class="ms-2 text-xs font-medium text-gray-500 dark:text-gray-400">فعال</span>
                            </label>
                        </div>

                        <div x-show="enableGallery" x-transition>
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <template x-for="(img, index) in galleryPreviews" :key="index">
                                    <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 group">
                                        <img :src="img" class="w-full h-full object-cover">
                                        <button type="button" @click="removeGalleryImage(index)" class="absolute top-1 right-1 bg-red-500/80 text-white p-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </template>

                                <label class="aspect-square border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition-colors">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <input type="file" name="gallery_images[]" multiple class="hidden" @change="handleGallerySelect" accept="image/*">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">حداکثر ۱۰ تصویر</p>
                        </div>
                    </div>

                    {{-- Video Upload --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ویدیو ملک (اختیاری)</label>
                        <div class="relative w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 flex flex-col items-center justify-center hover:border-indigo-400 transition-colors">

                            <template x-if="!videoPreview">
                                <div class="text-center pointer-events-none">
                                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    <p class="mt-1 text-xs text-gray-500">انتخاب ویدیو</p>
                                </div>
                            </template>

                            <template x-if="videoPreview">
                                <div class="w-full relative">
                                    <video :src="videoPreview" controls class="w-full rounded-lg max-h-48"></video>
                                    <button type="button" @click="removeVideo" class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600 shadow-sm z-10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>

                            <input type="file" name="video" id="video" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleVideoSelect" accept="video/*" x-show="!videoPreview">
                        </div>
                    </div>

                    {{-- File Information (Moved Here) --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">اطلاعات فایل</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="registered_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاریخ ثبت ملک</label>
                                <input type="text" id="registered_at" name="registered_at" data-jdp value="{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">وضعیت ملک</label>
                                <select id="status_id" name="status_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">انتخاب کنید</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="publication_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">وضعیت انتشار</label>
                                <select id="publication_status" name="publication_status" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="published" selected>منتشر شده</option>
                                    <option value="draft">پیش‌نویس</option>
                                </select>
                            </div>

                            <div>
                                <label for="confidential_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">یادداشت‌های محرمانه</label>
                                <textarea id="confidential_notes" name="confidential_notes" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="این یادداشت‌ها فقط برای شما قابل مشاهده است..."></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Form Fields --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Title & Type --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">عنوان ملک</label>
                            <input type="text" id="title" name="title" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required placeholder="مثلا: آپارتمان ۱۲۰ متری در زعفرانیه">
                        </div>

                        <div>
                            <label for="listing_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع فایل</label>
                            <select id="listing_type" name="listing_type" x-model="listingType" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="sale">فروش</option>
                                <option value="presale">پیش‌فروش</option>
                                <option value="rent">رهن و اجاره</option>
                            </select>
                        </div>

                        <div>
                            <label for="property_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع ملک</label>
                            <select id="property_type" name="property_type" x-model="propertyType" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="apartment">خانه و آپارتمان</option>
                                <option value="villa">ویلا</option>
                                <option value="land">زمین و باغ</option>
                                <option value="office">اداری و تجاری</option>
                            </select>
                        </div>

                        <div>
                            <label for="document_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع سند</label>
                            <select id="document_type" name="document_type" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">انتخاب کنید</option>
                                @foreach(\Modules\Properties\Entities\Property::DOCUMENT_TYPES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="building_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ساختمان / برج</label>
                            <select id="building_id" name="building_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">انتخاب کنید</option>
                                {{-- Options will be loaded dynamically later --}}
                            </select>
                        </div>

                        {{-- Conditional Fields --}}
                        <div x-show="propertyType === 'land'" x-transition class="md:col-span-2">
                            <label for="usage_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع کاربری</label>
                            <select id="usage_type" name="usage_type" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">انتخاب کنید</option>
                                <option value="residential">مسکونی</option>
                                <option value="industrial">صنعتی</option>
                                <option value="commercial">اداری و تجاری</option>
                                <option value="agricultural">کشاورزی</option>
                            </select>
                        </div>

                        <div x-show="listingType === 'presale'" x-transition class="md:col-span-2">
                            <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاریخ تحویل (حدودی)</label>
                            <input type="text" id="delivery_date" name="delivery_date" data-jdp class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="تاریخ را انتخاب کنید">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">توضیحات</label>
                        <textarea id="description" name="description" rows="4" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="توضیحات کامل در مورد ملک..."></textarea>
                    </div>

                    {{-- Code & Category --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="code_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">کد ملک</label>
                            <div class="flex rounded-xl shadow-sm">
                                <select id="code_type" x-model="codeType" class="w-auto rounded-r-xl rounded-l-none border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="auto">اتوماتیک</option>
                                    <option value="manual">دستی</option>
                                </select>
                                <input type="text" name="code" x-show="codeType === 'manual'" class="w-full rounded-l-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="کد دلخواه">
                            </div>
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">دسته‌بندی شخصی</label>
                            <select id="category_id" name="category_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">بدون دسته‌بندی</option>
                                @foreach(\Modules\Properties\Entities\PropertyCategory::where('user_id', auth()->id())->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Owner Selection with Search --}}
                    <div class="md:col-span-2">
                        <label for="owner_search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">مالک</label>
                        <div class="flex gap-2 relative">
                            <input type="hidden" name="owner_id" x-model="selectedOwnerId">
                            <div class="relative flex-1">
                                <input
                                    type="text"
                                    id="owner_search"
                                    x-model="searchQuery"
                                    @input.debounce.300ms="searchOwners()"
                                    @focus="if(searchQuery.length >= 2) showResults = true"
                                    @click.away="showResults = false"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="جستجو بر اساس نام یا شماره تماس..."
                                    autocomplete="off"
                                >
                                <div x-show="isSearching" class="absolute left-3 top-3">
                                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                {{-- Search Results Dropdown --}}
                                <div x-show="showResults && searchResults.length > 0" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                    <ul>
                                        <template x-for="owner in searchResults" :key="owner.id">
                                            <li @click="selectOwner(owner)" class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                                <div class="font-medium" x-text="owner.first_name + ' ' + owner.last_name"></div>
                                                <div class="text-xs text-gray-500" x-text="owner.phone"></div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <div x-show="showResults && searchResults.length === 0 && !isSearching" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg p-4 text-center text-sm text-gray-500">
                                    موردی یافت نشد.
                                </div>
                            </div>

                            <button type="button" @click="showOwnerModal = true" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Map & Address --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">موقعیت روی نقشه</label>
                            <button type="button" @click="getCurrentLocation" class="text-xs flex items-center gap-1 text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                دریافت موقعیت فعلی
                            </button>
                        </div>

                        <div id="map" class="w-full h-64 rounded-xl z-0 border border-gray-300 dark:border-gray-700"></div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">آدرس دقیق</label>
                            <textarea id="address" name="address" x-model="address" rows="2" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="آدرس به صورت خودکار از نقشه دریافت می‌شود..."></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('user.properties.index') }}" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">انصراف</a>
                <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30 flex items-center gap-2" :disabled="isSubmitting">
                    <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isSubmitting ? 'در حال ذخیره...' : 'ذخیره و ادامه (قیمت‌گذاری)'"></span>
                </button>
            </div>
        </form>
    </div>

    {{-- Create Owner Modal --}}
    <div x-show="showOwnerModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showOwnerModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showOwnerModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                افزودن مالک جدید
                            </h3>
                            <div class="mt-4 space-y-4">
                                <template x-if="ownerErrors.general">
                                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                        <span class="block sm:inline" x-text="ownerErrors.general[0]"></span>
                                    </div>
                                </template>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نام</label>
                                        <input type="text" x-model="newOwner.first_name" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                        <template x-if="ownerErrors.first_name">
                                            <p class="text-red-500 text-xs mt-1" x-text="ownerErrors.first_name[0]"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نام خانوادگی</label>
                                        <input type="text" x-model="newOwner.last_name" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                        <template x-if="ownerErrors.last_name">
                                            <p class="text-red-500 text-xs mt-1" x-text="ownerErrors.last_name[0]"></p>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">شماره تماس</label>
                                    <input type="text" x-model="newOwner.phone" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                    <template x-if="ownerErrors.phone">
                                        <p class="text-red-500 text-xs mt-1" x-text="ownerErrors.phone[0]"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="createOwner" :disabled="isSavingOwner" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!isSavingOwner">ذخیره</span>
                        <span x-show="isSavingOwner">در حال ذخیره...</span>
                    </button>
                    <button type="button" @click="showOwnerModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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

    function propertyForm() {
        return {
            listingType: 'sale',
            propertyType: 'apartment',
            codeType: 'auto',
            isSubmitting: false,

            // Image Upload
            coverPreview: null,
            enableGallery: false,
            galleryPreviews: [],
            galleryFiles: [], // To store File objects

            // Video Upload
            videoPreview: null,

            // Map
            map: null,
            marker: null,
            lat: 35.6892, // Default Tehran
            lng: 51.3890,
            address: '',

            // Owner Management
            searchQuery: '',
            searchResults: [],
            showResults: false,
            selectedOwnerId: '',
            isSearching: false,
            showOwnerModal: false,
            newOwner: {
                first_name: '',
                last_name: '',
                phone: ''
            },
            ownerErrors: {},
            isSavingOwner: false,

            init() {
                this.initMap();
                this.$watch('showOwnerModal', (value) => {
                    if (value) {
                        this.ownerErrors = {};
                    }
                });
            },

            // --- Owner Search & Creation ---
            async searchOwners() {
                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    this.showResults = false;
                    return;
                }

                this.isSearching = true;
                try {
                    const response = await fetch(`{{ route('user.properties.owners.search') }}?q=${this.searchQuery}`);
                    this.searchResults = await response.json();
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
                        this.ownerErrors = data.errors;
                    } else {
                        this.ownerErrors.general = [data.message || 'یک خطای غیرمنتظره رخ داد.'];
                    }
                } catch (error) {
                    console.error('Error creating owner:', error);
                    this.ownerErrors.general = ['خطا در برقراری ارتباط با سرور.'];
                } finally {
                    this.isSavingOwner = false;
                }
            },

            // --- Image Handling ---
            handleCoverSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    this.previewFile(file, (url) => this.coverPreview = url);
                }
            },
            handleCoverDrop(e) {
                const file = e.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    const input = document.getElementById('cover_image');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
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

            // --- Map Handling ---
            initMap() {
                this.map = L.map('map').setView([this.lat, this.lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.map);

                // Add Search Control
                const provider = new OpenStreetMapProvider();
                const searchControl = new GeoSearchControl({
                    provider: provider,
                    style: 'bar',
                    searchLabel: 'جستجوی آدرس...',
                    notFoundMessage: 'آدرس یافت نشد',
                    showMarker: false, // We handle marker manually
                    retainZoomLevel: false,
                    animateZoom: true,
                    autoClose: true,
                });
                this.map.addControl(searchControl);

                // Handle Search Result
                this.map.on('geosearch/showlocation', (result) => {
                    const { x, y, label } = result.location;
                    this.updateLocation(y, x); // y=lat, x=lng
                });

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
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'خطا در دریافت موقعیت: ' + error.message } }));
                    });
                } else {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'مرورگر شما از موقعیت مکانی پشتیبانی نمی‌کند.' } }));
                }
            },
            async getAddress(lat, lng) {
                this.address = 'در حال دریافت آدرس...';
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=fa`);
                    const data = await response.json();
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
            },

            async submitForm(e) {
                if (this.isSubmitting) return;
                this.isSubmitting = true;

                const form = e.target;
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Success
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: data.message || 'عملیات با موفقیت انجام شد.' } }));
                        if (data.redirect_url) {
                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 1000);
                        }
                    } else {
                        // Validation or Server Error
                        if (data.errors) {
                            Object.values(data.errors).flat().forEach(error => {
                                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: error } }));
                            });
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: data.message || 'خطایی رخ داده است.' } }));
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', text: 'خطا در برقراری ارتباط با سرور.' } }));
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
@endsection
