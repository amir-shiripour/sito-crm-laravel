@extends('layouts.user')

@php
    $title = 'ویرایش ملک: ' . $property->title;
    $currency = \Modules\Properties\Entities\PropertySetting::get('currency', 'toman');
    $currencyLabel = $currency == 'toman' ? 'تومان' : 'ریال';

    // Load attributes for details and features tabs
    $detailsAttributes = \Modules\Properties\Entities\PropertyAttribute::where('section', 'details')->where('is_active', true)->orderBy('sort_order')->get();
    $featuresAttributes = \Modules\Properties\Entities\PropertyAttribute::where('section', 'features')->where('is_active', true)->orderBy('sort_order')->get();
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

<div class="max-w-6xl mx-auto" x-data="editPropertyForm()">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ویرایش ملک</h1>
        <a href="{{ route('user.properties.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">بازگشت به لیست</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

        {{-- Tabs --}}
        <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <button @click="activeTab = 'details'; setTimeout(() => map.invalidateSize(), 200)" :class="activeTab === 'details' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="flex-1 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                مشخصات اصلی
            </button>
            <button @click="activeTab = 'pricing'" :class="activeTab === 'pricing' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="flex-1 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                قیمت‌گذاری
            </button>
            <button @click="activeTab = 'attributes'" :class="activeTab === 'attributes' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="flex-1 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                اطلاعات تکمیلی
            </button>
            <button @click="activeTab = 'features'" :class="activeTab === 'features' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="flex-1 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                امکانات
            </button>
        </div>

        <div class="p-6">

            {{-- Details Tab (Main Info) --}}
            <div x-show="activeTab === 'details'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @include('properties::user.partials.edit-details-form')
            </div>

            {{-- Pricing Tab --}}
            <div x-show="activeTab === 'pricing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <form action="{{ route('user.properties.pricing.update', $property) }}" method="POST" x-data="priceFormatter()">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6 max-w-2xl mx-auto">
                        <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200 p-4 rounded-xl text-sm mb-4">
                            شما در حال ویرایش قیمت برای نوع فایل <strong>{{ $property->listing_type == 'sale' ? 'فروش' : ($property->listing_type == 'rent' ? 'رهن و اجاره' : 'پیش‌فروش') }}</strong> هستید.
                        </div>

                        @if($property->listing_type == 'sale')
                            {{-- Sale Fields --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت اعلامی ({{ $currencyLabel }})</label>
                                <input type="text" name="price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('price', number_format($property->price)) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت کف ({{ $currencyLabel }})</label>
                                <input type="text" name="min_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('min_price', number_format($property->min_price)) }}">
                            </div>

                        @elseif($property->listing_type == 'rent')
                            {{-- Rent Fields --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">مبلغ رهن ({{ $currencyLabel }})</label>
                                <input type="text" name="deposit_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('deposit_price', number_format($property->deposit_price)) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">اجاره ماهیانه ({{ $currencyLabel }})</label>
                                <input type="text" name="rent_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('rent_price', number_format($property->rent_price)) }}">
                            </div>

                        @elseif($property->listing_type == 'presale')
                            {{-- Presale Fields --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">مبلغ پیش‌پرداخت ({{ $currencyLabel }})</label>
                                <input type="text" name="advance_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('advance_price', number_format($property->advance_price)) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت کل اعلامی ({{ $currencyLabel }})</label>
                                <input type="text" name="price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('price', number_format($property->price)) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت کف ({{ $currencyLabel }})</label>
                                <input type="text" name="min_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('min_price', number_format($property->min_price)) }}">
                            </div>
                        @endif

                        {{-- Convertible Option --}}
                        <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <label class="flex items-center gap-2 cursor-pointer mb-2">
                                <input type="checkbox" name="is_convertible" value="1" x-model="isConvertible" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">قابل تبدیل است</span>
                            </label>

                            <div x-show="isConvertible" x-transition>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">شرایط تبدیل</label>
                                <input type="text" name="convertible_with" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="مثلا: قابل تبدیل به رهن کامل یا معاوضه با خودرو" value="{{ old('convertible_with', $property->convertible_with) }}">
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                                ذخیره قیمت‌ها
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Attributes Tab --}}
            <div x-show="activeTab === 'attributes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <form action="{{ route('user.properties.details.update', $property) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        {{-- Pre-defined Attributes --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($detailsAttributes as $attr)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $attr->name }}</label>

                                    @php
                                        $value = $property->attributeValues->where('attribute_id', $attr->id)->first()->value ?? '';
                                    @endphp

                                    @if($attr->type === 'text')
                                        <input type="text" name="attributes[{{ $attr->id }}]" value="{{ $value }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                    @elseif($attr->type === 'number')
                                        <input type="number" name="attributes[{{ $attr->id }}]" value="{{ $value }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                    @elseif($attr->type === 'select')
                                        <select name="attributes[{{ $attr->id }}]" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">انتخاب کنید</option>
                                            @foreach($attr->options ?? [] as $option)
                                                <option value="{{ $option }}" {{ $value == $option ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            @endforeach

                            @if($detailsAttributes->isEmpty())
                                <div class="col-span-2 text-center py-8 text-gray-500">
                                    هیچ فیلد اطلاعات تکمیلی تعریف نشده است.
                                </div>
                            @endif
                        </div>

                        {{-- Custom Attributes Repeater --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">ویژگی‌های سفارشی</h3>
                            <div class="space-y-3">
                                <template x-for="(field, index) in customDetails" :key="index">
                                    <div class="flex items-center gap-3">
                                        <input type="text" :name="`meta[${index}][key]`" x-model="field.key" placeholder="عنوان ویژگی" class="w-1/3 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                        <input type="text" :name="`meta[${index}][value]`" x-model="field.value" placeholder="مقدار" class="flex-1 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                        <button type="button" @click="removeDetailField(index)" class="p-2 text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addDetailField" class="mt-4 text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                افزودن ویژگی جدید
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                            ذخیره اطلاعات تکمیلی
                        </button>
                    </div>
                </form>
            </div>

            {{-- Features Tab --}}
            <div x-show="activeTab === 'features'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <form action="{{ route('user.properties.features.update', $property) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        {{-- Pre-defined Features --}}
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($featuresAttributes as $attr)
                                @php
                                    $hasValue = $property->attributeValues->where('attribute_id', $attr->id)->isNotEmpty();
                                @endphp
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                    <input type="checkbox" name="attributes[]" value="{{ $attr->id }}" {{ $hasValue ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $attr->name }}</span>
                                </label>
                            @endforeach

                            @if($featuresAttributes->isEmpty())
                                <div class="col-span-3 text-center py-8 text-gray-500">
                                    هیچ امکاناتی تعریف نشده است.
                                </div>
                            @endif
                        </div>

                        {{-- Custom Features Repeater --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">امکانات سفارشی</h3>
                            <div class="space-y-3">
                                <template x-for="(field, index) in customFeatures" :key="index">
                                    <div class="flex items-center gap-3">
                                        <input type="text" :name="`meta[${index}][value]`" x-model="field.value" placeholder="عنوان امکانات" class="flex-1 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                        <button type="button" @click="removeFeatureField(index)" class="p-2 text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addFeatureField" class="mt-4 text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                افزودن امکانات جدید
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                            ذخیره امکانات
                        </button>
                    </div>
                </form>
            </div>

        </div>
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
                                <template x-if="errors.general">
                                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                        <span class="block sm:inline" x-text="errors.general[0]"></span>
                                    </div>
                                </template>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نام</label>
                                        <input type="text" x-model="newOwner.first_name" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                        <template x-if="errors.first_name">
                                            <p class="text-red-500 text-xs mt-1" x-text="errors.first_name[0]"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نام خانوادگی</label>
                                        <input type="text" x-model="newOwner.last_name" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                        <template x-if="errors.last_name">
                                            <p class="text-red-500 text-xs mt-1" x-text="errors.last_name[0]"></p>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">شماره تماس</label>
                                    <input type="text" x-model="newOwner.phone" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                    <template x-if="errors.phone">
                                        <p class="text-red-500 text-xs mt-1" x-text="errors.phone[0]"></p>
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

    function editPropertyForm() {
        return {
            activeTab: 'details',
            listingType: '{{ $property->listing_type }}',
            propertyType: '{{ $property->property_type }}',

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

            init() {
                this.initMap();
                this.$watch('showOwnerModal', (value) => {
                    if (value) {
                        this.errors = {};
                    }
                });
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
                        this.owners.unshift(data.owner);
                        this.selectedOwner = data.owner.id;
                        this.showOwnerModal = false;
                        this.newOwner = { first_name: '', last_name: '', phone: '' };
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: 'مالک جدید با موفقیت ایجاد شد.' } }));
                        window.dispatchEvent(new CustomEvent('owner-created', { detail: data.owner }));
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
