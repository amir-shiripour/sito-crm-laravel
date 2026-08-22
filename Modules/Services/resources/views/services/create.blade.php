@extends('layouts.user')
@section('title', (isset($service) && $service) ? 'ویرایش سرویس: '.($service->name ?? '') : 'ثبت سرویس جدید')

@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";
    $isEdit     = isset($service) && $service;
    $action     = $isEdit ? route('services.services.update', $service) : route('services.services.store');

    $initialFormData = [
        'billing_type'        => old('billing_type', $service->billing_type ?? 'one_time'),
        'base_price'          => (float) old('base_price', $service->base_price ?? 0),
        'has_unit_pricing'    => old('has_unit_pricing', $service->has_unit_pricing ?? false),
        'unit_name'           => old('unit_name', $service->unit_name ?? ''),
        'unit_price'          => (float) old('unit_price', $service->unit_price ?? 0),
        'custom_fields'       => isset($service) ? $service->customFields : [],
        'renewal_prices'      => [
            'monthly'     => (float) old('renewal_prices.monthly', $service->renewal_prices['monthly'] ?? 0),
            'quarterly'   => (float) old('renewal_prices.quarterly', $service->renewal_prices['quarterly'] ?? 0),
            'semi_annual' => (float) old('renewal_prices.semi_annual', $service->renewal_prices['semi_annual'] ?? 0),
            'annual'      => (float) old('renewal_prices.annual', $service->renewal_prices['annual'] ?? 0),
        ],
    ];

    $currency      = $currency ?? 'toman';
    $currencyLabel = $currency === 'rial' ? 'ریال' : 'تومان';
    $activeTab = request()->get('tab', 'details');
@endphp

@section('content')
    <style>[x-cloak] {
            display: none !important;
        }</style>

    {{-- تغییر به max-w-screen-2xl --}}
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="serviceForm(@js($initialFormData), '{{ $activeTab }}')">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl {{ $isEdit ? 'bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-amber-500/30' : 'bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-indigo-500/30' }} shadow-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        @if($isEdit)
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        @endif
                    </svg>
                </span>
                {{ $isEdit ? 'ویرایش سرویس' : 'ثبت سرویس جدید' }}
            </h1>
            <a href="{{ route('services.services.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست
            </a>
        </div>

        @if($errors->any())
            <div class="p-5 mb-4 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm animate-fade-in">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <div>
                    <p class="font-black text-base mb-2">خطا در ثبت اطلاعات!</p>
                    <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ $action }}" class="space-y-8">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            {{-- Tabs Navigation --}}
            <div class="border-b border-gray-200 dark:border-gray-700 mb-8">
                <div class="flex gap-1 text-sm font-semibold overflow-x-auto scrollbar-thin">
                    <button type="button" @click="activeTab = 'details'"
                            :class="activeTab === 'details' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent'"
                            class="pb-4 px-5 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        اطلاعات و قیمت‌گذاری
                    </button>
                    <button type="button" @click="activeTab = 'custom_fields'"
                            :class="activeTab === 'custom_fields' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent'"
                            class="pb-4 px-5 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        فیلدهای سفارشی
                    </button>
                </div>
            </div>

            {{-- Tab 1: Basic Info & Pricing --}}
            <div x-show="activeTab === 'details'" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-8">

                {{-- Basic Info Section --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                        <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            اطلاعات پایه
                        </h2>
                    </div>
                    {{-- آپدیت گرید برای فرم‌های عریض --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-6">
                        <div class="md:col-span-2 xl:col-span-2">
                            <label class="{{ $labelClass }}">نام سرویس</label>
                            <input type="text" name="name" value="{{ old('name', $service->name ?? '') }}"
                                   class="{{ $inputClass }}" placeholder="مثال: طراحی سایت فروشگاهی اختصاصی">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">کد سرویس (اختیاری)</label>
                            <input type="text" name="code" value="{{ old('code', $service->code ?? '') }}"
                                   class="{{ $inputClass }} font-mono text-start dir-ltr" placeholder="SRC-001">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">دسته‌بندی</label>
                            <select name="category_id" class="{{ $inputClass }} cursor-pointer">
                                <option value="">بدون دسته‌بندی</option>
                                @foreach($categories as $cat)
                                    <option
                                        value="{{ $cat->id }}" @selected(old('category_id', $service->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(isset($accountingCategories) && count($accountingCategories) > 0)
                            @php
                                $selectedAccCatId = old('accounting_category_id', $service->accounting_category_id ?? '');
                                $selectedAccCatTitle = '';
                                $accCatOptions = [];
                                foreach($accountingCategories as $accCat) {
                                    $typeTitle = match($accCat->type){
                                        'income' => 'درآمد',
                                        'expense' => 'هزینه',
                                        'asset' => 'دارایی',
                                        'liability' => 'بدهی',
                                        'equity' => 'سرمایه',
                                        default => $accCat->type
                                    };
                                    $codePrefix = $accCat->account_code ? "کد {$accCat->account_code} - " : "";
                                    $connectedAccounts = ($accCat->fundAccounts && $accCat->fundAccounts->count() > 0)
                                        ? " | متصل به: " . $accCat->fundAccounts->pluck('name')->implode('، ')
                                        : "";
                                    $fullTitle = "{$codePrefix}{$accCat->title} ({$typeTitle}){$connectedAccounts}";
                                    if ((string)$selectedAccCatId === (string)$accCat->id) {
                                        $selectedAccCatTitle = $fullTitle;
                                    }
                                    $accCatOptions[] = [
                                        'id' => (string)$accCat->id,
                                        'title' => $fullTitle
                                    ];
                                }
                            @endphp
                            <div>
                                <label class="{{ $labelClass }}">سرفصل مالی (حسابداری)</label>
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: '{{ $selectedAccCatId }}',
                                    selectedTitle: '{{ addslashes($selectedAccCatTitle) }}',
                                    options: @js($accCatOptions),
                                    get filteredOptions() {
                                        if (!this.search.trim()) return this.options;
                                        return this.options.filter(o => o.title.toLowerCase().includes(this.search.toLowerCase()));
                                    },
                                    select(opt) {
                                        if (opt) {
                                            this.selectedId = opt.id;
                                            this.selectedTitle = opt.title;
                                        } else {
                                            this.selectedId = '';
                                            this.selectedTitle = '';
                                        }
                                        this.open = false;
                                        this.search = '';
                                    }
                                }" class="relative">
                                    <input type="hidden" name="accounting_category_id" :value="selectedId">

                                    <button type="button" @click="open = !open"
                                            class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start">
                                        <span x-text="selectedTitle || 'بدون سرفصل مالی'" class="truncate text-sm"></span>
                                        <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-50 mt-1.5 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl p-2 max-h-64 overflow-y-auto">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو در سرفصل‌ها..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>

                                        <div @click="select(null)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-gray-500 font-bold mb-1">
                                            بدون سرفصل مالی
                                        </div>

                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors"
                                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': selectedId === opt.id }"
                                                 x-text="opt.title">
                                            </div>
                                        </template>

                                        <div x-show="filteredOptions.length === 0" class="p-3 text-xs text-gray-400 text-center">
                                            هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div>
                            <label class="{{ $labelClass }}">وضعیت نمایش</label>
                            <select name="status_id" class="{{ $inputClass }} cursor-pointer">
                                @foreach($statuses as $st)
                                    <option value="{{ $st->id }}" @selected(old('status_id', $service->status_id ?? '') == $st->id)>
                                        {{ $st->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 xl:col-span-6">
                            <label class="{{ $labelClass }}">توضیحات کلی</label>
                            <textarea name="description" rows="4" class="{{ $inputClass }} resize-none"
                                       placeholder="توضیحات و ویژگی‌های کلی این سرویس را اینجا بنویسید...">{{ old('description', $service->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-r from-emerald-50/50 to-violet-50/50 dark:from-emerald-900/20 dark:to-violet-900/20">
                        <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div
                                class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            ساختار قیمت‌گذاری
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="{{ $labelClass }}">نوع چرخه پرداخت</label>
                                <select name="billing_type" x-model="billingType"
                                        class="{{ $inputClass }} cursor-pointer">
                                    <option value="one_time">پرداخت یک‌باره</option>
                                    <option value="recurring">اشتراک دوره‌ای</option>
                                </select>
                            </div>
                            {{-- Base Price --}}
                            <div class="md:col-span-1 lg:col-span-2">
                                <label class="{{ $labelClass }}">قیمت پایه</label>
                                <div class="relative">
                                    <input type="text" x-model="basePriceDisplay"
                                           @input="onBasePriceInput($event.target.value)"
                                           @blur="formatBasePriceDisplay()"
                                           class="{{ $inputClass }} dir-ltr text-end tabular-nums text-lg font-semibold pl-16"
                                           placeholder="۰">
                                    <span
                                        class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 dark:text-gray-500 text-sm font-bold">{{ $currencyLabel }}</span>
                                </div>
                                <input type="hidden" name="base_price" x-model.number="basePrice">
                            </div>
                        </div>

                            {{-- بخش فروش بر اساس واحد --}}
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50">
                                <label
                                    class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer w-max mb-4 transition-colors hover:border-indigo-400">
                                    <input type="checkbox" name="has_unit_pricing" value="1" x-model="hasUnitPricing"
                                           class="w-5 h-5 text-indigo-600 rounded">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">فروش بر اساس واحد (مثل متراژ، کیلوگرم، نفر و...)</span>
                                </label>

                                <div x-show="hasUnitPricing" x-collapse
                                     class="grid grid-cols-1 gap-6 p-5 bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/50 rounded-xl">
                                    <div>
                                        <label class="{{ $labelClass }}">نام واحد (مثال: متر، کیلوگرم، عدد)</label>
                                        <input type="text" name="unit_name"
                                               value="{{ old('unit_name', $service->unit_name ?? '') }}"
                                               class="{{ $inputClass }}" placeholder="مثال: متر">
                                    </div>
                                </div>
                            </div>

                        {{-- Renewal Prices --}}
                        <div x-show="billingType === 'recurring'" x-collapse
                             class="space-y-4 pt-6 border-t border-dashed border-gray-200 dark:border-gray-700">
                            <h3 class="text-md font-bold text-gray-800 dark:text-gray-200">قیمت تمدید دوره‌ای</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-2">
                                در این بخش قیمت‌های تمدید را برای دوره‌های مختلف وارد کنید. این قیمت‌ها هنگام صدور فاکتور برای سرویس‌های دوره‌ای استفاده خواهند شد.
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-x-6 gap-y-4">
                                @foreach(['monthly' => 'ماهانه', 'quarterly' => 'فصلی', 'semi_annual' => 'شش‌ماهه', 'annual' => 'سالانه'] as $period => $label)
                                    <div>
                                        <label class="{{ $labelClass }}">{{ $label }}</label>
                                        <div class="relative">
                                            <input type="text"
                                                   x-model="renewalPricesDisplay.{{$period}}"
                                                   @input="onRenewalPriceInput('{{$period}}', $event.target.value)"
                                                   @blur="formatRenewalPriceDisplay('{{$period}}')"
                                                   class="{{ $inputClass }} dir-ltr text-end tabular-nums font-medium pl-16"
                                                   placeholder="۰">
                                            <span
                                                class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 dark:text-gray-500 text-sm font-bold">{{ $currencyLabel }}</span>
                                            <input type="hidden" name="renewal_prices[{{$period}}]"
                                                   :value="renewalPrices.{{$period}}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Tab 2: Custom Fields --}}
            <div x-show="activeTab === 'custom_fields'" x-cloak x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">

                <div class="flex justify-between items-center">
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-200 text-sm rounded-xl p-4 flex-1 ml-4">
                        فیلدهای سفارشی در فرم ثبت پروژه و فاکتور برای این سرویس نمایش داده می‌شوند.
                    </div>
                    <button type="button" @click="addField()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl hover:from-indigo-700 hover:to-violet-700 transition-all shadow-lg active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        افزودن فیلد
                    </button>
                </div>

                <div id="fields-list" class="flex flex-col gap-4">
                    <template x-for="(field, idx) in fields" :key="field._key">
                        <div
                            class="group relative bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden transition-colors duration-200 hover:border-indigo-400 dark:hover:border-indigo-500"
                            :class="field._dragging ? 'opacity-50 scale-95 border-indigo-500 shadow-2xl shadow-indigo-500/30' : ''"
                            draggable="true" @dragstart="dragStart(idx)" @dragover.prevent="dragOver(idx)"
                            @drop="dragEnd(idx)" @dragend="dragging = null">

                            <input type="hidden" :name="'custom_fields[' + idx + '][id]'" :value="field.id ?? ''">
                            <input type="hidden" :name="'custom_fields[' + idx + '][sort_order]'" :value="idx">

                            <div
                                class="flex items-center gap-3 p-4 cursor-pointer bg-linear-to-r from-gray-50 to-transparent dark:from-gray-800/50 dark:to-transparent"
                                @click="field._open = !field._open">
                                <div class="p-1.5 text-gray-400 hover:text-gray-600 cursor-grab" @click.stop>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/>
                                    </svg>
                                </div>
                                <div
                                    class="w-8 h-8 flex items-center justify-center bg-linear-to-br from-indigo-500 to-violet-600 text-white text-xs font-black rounded-lg"
                                    x-text="idx + 1"></div>
                                <div class="flex-1 font-bold text-gray-900 dark:text-white text-sm truncate"
                                     x-text="field.label || 'فیلد بدون نام'"></div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2.5 py-1 text-xs font-bold bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg"
                                        x-text="typeLabel(field.type)"></span>
                                    <span
                                        class="px-2.5 py-1 text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-lg"
                                        x-show="field.is_required">اجباری</span>
                                    <span
                                        class="px-2.5 py-1 text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg"
                                        x-show="field.has_pricing">+قیمت</span>
                                </div>
                                <button type="button"
                                        class="flex items-center justify-center w-9 h-9 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all"
                                        @click.stop="removeField(idx)">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            <div
                                class="border-t-2 border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 p-5"
                                x-show="field._open" x-collapse>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 mb-2">عنوان فیلد *</label>
                                        <input type="text" :name="'custom_fields[' + idx + '][label]'"
                                               x-model="field.label" class="{{ $inputClass }}"
                                               placeholder="مثال: رنگ محصول" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 mb-2">نوع فیلد</label>
                                        <select :name="'custom_fields[' + idx + '][type]'" x-model="field.type"
                                                class="{{ $inputClass }} cursor-pointer">
                                            <option value="text">متن کوتاه</option>
                                            <option value="textarea">متن بلند</option>
                                            <option value="number">عدد</option>
                                            <option value="select">انتخابی (Dropdown)</option>
                                            <option value="multiselect">چند انتخابی</option>
                                            <option value="checkbox">چک‌باکس</option>
                                            <option value="radio">رادیو</option>
                                            <option value="date">تاریخ</option>
                                            <option value="datetime">تاریخ و زمان</option>
                                            <option value="file">فایل</option>
                                            <option value="email">ایمیل</option>
                                            <option value="url">لینک (URL)</option>
                                            <option value="phone">تلفن</option>
                                        </select>
                                    </div>
                                </div>

                                <div
                                    x-show="field.type === 'select' || field.type === 'radio' || field.type === 'multiselect'"
                                    class="mb-5 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-200">
                                            گزینه‌ها
                                            <span x-show="field.has_pricing" class="text-indigo-600 dark:text-indigo-400 font-semibold">(تعیین عنوان و قیمت اختصاصی هر گزینه)</span>
                                        </label>
                                        <button type="button" @click="addOption(field)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            افزودن گزینه
                                        </button>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="(opt, optIdx) in (field.options || [])" :key="optIdx">
                                            <div class="flex items-center gap-2 p-2.5 bg-gray-50/80 dark:bg-gray-900/40 rounded-xl border border-gray-200/70 dark:border-gray-700/60">
                                                <span class="w-6 h-6 flex items-center justify-center text-xs font-bold text-gray-400 dark:text-gray-500 shrink-0" x-text="optIdx + 1"></span>
                                                <div class="flex-1">
                                                    <input type="text"
                                                           :name="'custom_fields[' + idx + '][options][' + optIdx + '][label]'"
                                                           x-model="opt.label"
                                                           class="{{ $inputClass }} py-2 text-xs"
                                                           placeholder="عنوان گزینه (مثال: هاست ۱ گیگ)" required>
                                                </div>
                                                <div class="w-36 sm:w-44" x-show="field.has_pricing">
                                                    <div class="relative">
                                                        <input type="text"
                                                               :value="formatPriceDisplay(opt.price)"
                                                               @input="handleOptionPriceInput($event, opt)"
                                                               class="{{ $inputClass }} py-2 text-xs text-center dir-ltr pe-12"
                                                               placeholder="قیمت: ۰">
                                                        <input type="hidden"
                                                               :name="'custom_fields[' + idx + '][options][' + optIdx + '][price]'"
                                                               :value="opt.price || 0">
                                                        <span class="absolute end-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                                    </div>
                                                </div>
                                                <input type="hidden" :name="'custom_fields[' + idx + '][options][' + optIdx + '][pricing_type]'" :value="opt.pricing_type || 'fixed'">
                                                <button type="button" @click="removeOption(field, optIdx)"
                                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors shrink-0"
                                                        title="حذف گزینه">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </template>

                                        <div x-show="!field.options || field.options.length === 0" class="p-4 text-center text-xs text-gray-400 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                                            هنوز گزینه‌ای اضافه نشده است. روی دکمه «افزودن گزینه» کلیک کنید.
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                                    <label
                                        class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer">
                                        <input type="checkbox" :name="'custom_fields[' + idx + '][is_required]'"
                                               value="1" x-model="field.is_required"
                                               class="w-5 h-5 text-indigo-600 rounded">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">اجباری</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer">
                                        <input type="checkbox" :name="'custom_fields[' + idx + '][show_in_invoice]'"
                                               value="1" x-model="field.show_in_invoice"
                                               class="w-5 h-5 text-indigo-600 rounded">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">نمایش در فاکتور</span>
                                    </label>
                                </div>

                                <label
                                    class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer mb-4">
                                    <input type="checkbox" :name="'custom_fields[' + idx + '][has_pricing]'" value="1"
                                           x-model="field.has_pricing" class="w-5 h-5 text-indigo-600 rounded">
                                    <span
                                        class="text-sm font-bold text-gray-900 dark:text-white">تأثیر روی قیمت نهایی</span>
                                </label>

                                <div x-show="field.has_pricing" x-collapse
                                     class="p-5 bg-linear-to-br from-indigo-50 to-violet-50 dark:from-indigo-900/20 dark:to-violet-900/20 border-2 border-indigo-200 dark:border-indigo-800 rounded-xl">
                                    <template x-if="field.type === 'multiselect' || field.type === 'select' || field.type === 'radio'">
                                        <div class="text-xs text-indigo-800 dark:text-indigo-300 font-medium">
                                             قیمت هر گزینه را می‌توانید به صورت مجزا در بخش «گزینه‌ها» بالا وارد کنید. همچنین در صورت تمایل می‌توانید مبلغ پیش‌فرضی در زیر تعیین نمایید.
                                        </div>
                                    </template>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" :class="(field.type === 'multiselect' || field.type === 'select' || field.type === 'radio') ? 'mt-3' : ''">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 mb-2">نوع
                                                قیمت‌گذاری</label>
                                            <select :name="'custom_fields[' + idx + '][pricing_type]'"
                                                    x-model="field.pricing_type"
                                                    class="{{ $inputClass }} cursor-pointer">
                                                <option value="fixed">مبلغ ثابت</option>
                                                <option value="percentage">درصد از قیمت پایه</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 mb-2"
                                                   x-text="field.pricing_type === 'percentage' ? 'درصد اضافه (%)' : 'مبلغ اضافه ({{ $currencyLabel }})'"></label>
                                            <input type="hidden" :name="'custom_fields[' + idx + '][pricing_amount]'"
                                                   :value="field.pricing_amount">

                                            <input type="text"
                                                   :value="formatPriceDisplay(field.pricing_amount)"
                                                   @input="handlePricingInput($event, field)"
                                                   class="{{ $inputClass }} dir-ltr text-end"
                                                   placeholder="۰">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="text-center py-12" x-show="fields.length === 0">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">فیلد سفارشی وجود ندارد</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">برای افزودن فیلد، روی دکمه «افزودن فیلد»
                            کلیک کنید.</p>
                    </div>
                </div>
            </div>

            {{-- Sticky Submit Bar --}}
            <div class="sticky bottom-4 z-40">
                <div
                    class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-[0_10px_40px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl {{ $isEdit ? 'bg-gradient-to-r from-amber-500 to-amber-600 shadow-amber-500/30 hover:shadow-amber-500/50 hover:from-amber-400 hover:to-amber-500' : 'bg-gradient-to-r from-indigo-600 to-indigo-700 shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-500 hover:to-indigo-600' }} text-white font-black shadow-lg transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $isEdit ? 'ذخیره تغییرات سرویس' : 'ثبت نهایی سرویس' }}
                    </button>
                    <a href="{{ route('services.services.index') }}"
                       class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">انصراف</a>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toPersianNumber(str) {
            const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return str.toString().replace(/\d/g, digit => persianDigits[digit]);
        }

        function toEnglishNumber(str) {
            const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            return str.toString().replace(/[۰-۹]/g, digit => persianDigits.indexOf(digit)).replace(/[٠-٩]/g, digit => arabicDigits.indexOf(digit));
        }

        function addThousandSeparator(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('serviceForm', (data, activeTab) => ({
                activeTab: activeTab || 'details',
                billingType: data.billing_type || 'one_time',

                basePrice: Number(data.base_price || 0),
                basePriceDisplay: '',

                // اضافه کردن استیت فروش واحدی
                hasUnitPricing: Boolean(data.has_unit_pricing),
                unitPrice: Number(data.unit_price || 0),
                unitPriceDisplay: '',

                renewalPrices: {
                    monthly: Number(data.renewal_prices.monthly),
                    quarterly: Number(data.renewal_prices.quarterly),
                    semi_annual: Number(data.renewal_prices.semi_annual),
                    annual: Number(data.renewal_prices.annual),
                },
                renewalPricesDisplay: {monthly: '', quarterly: '', semi_annual: '', annual: ''},

                // Custom Fields State
                fields: [],
                draggingIdx: null,

                init() {
                    this.formatBasePriceDisplay();
                    this.formatUnitPriceDisplay();
                    ['monthly', 'quarterly', 'semi_annual', 'annual'].forEach(p => {
                        this.formatRenewalPriceDisplay(p);
                    });

                    // Init Custom Fields
                    this.fields = (data.custom_fields || []).map((f, i) => {
                        let rawOptions = f.options || [];
                        if (typeof rawOptions === 'string') {
                            try { rawOptions = JSON.parse(rawOptions); } catch (e) { rawOptions = []; }
                        }
                        let normalizedOptions = [];
                        if (Array.isArray(rawOptions)) {
                            normalizedOptions = rawOptions.map(opt => {
                                if (typeof opt === 'object' && opt !== null) {
                                    return {
                                        label: opt.label || opt.title || opt.name || '',
                                        price: Number(opt.price || opt.pricing_amount || 0),
                                        pricing_type: opt.pricing_type || 'fixed'
                                    };
                                }
                                return {
                                    label: String(opt || ''),
                                    price: 0,
                                    pricing_type: 'fixed'
                                };
                            }).filter(o => o.label.trim() !== '');
                        }

                        return {
                            ...f,
                            _key: i + '_' + Math.random().toString(36).substr(2, 9),
                            _open: false,
                            _dragging: false,
                            options: normalizedOptions,
                            pricing_type: f.pricing_type || 'fixed',
                            pricing_amount: f.pricing_type === 'percentage'
                                ? Math.min(100, Math.max(0, Number(f.pricing_amount) || 0))
                                : (f.pricing_amount || 0),
                            has_pricing: !!f.has_pricing,
                            is_required: !!f.is_required,
                            show_in_invoice: f.show_in_invoice !== false,
                        };
                    });
                },

                onBasePriceInput(input) {
                    const numeric = toEnglishNumber(input).replace(/[^\d]/g, '');
                    this.basePrice = numeric ? Number(numeric) : 0;
                    this.formatBasePriceDisplay();
                },
                formatBasePriceDisplay() {
                    this.basePriceDisplay = this.basePrice > 0 ? toPersianNumber(addThousandSeparator(this.basePrice.toString())) : '';
                },

                onUnitPriceInput(input) {
                    const numeric = toEnglishNumber(input).replace(/[^\d]/g, '');
                    this.unitPrice = numeric ? Number(numeric) : 0;
                    this.formatUnitPriceDisplay();
                },
                formatUnitPriceDisplay() {
                    this.unitPriceDisplay = this.unitPrice > 0 ? toPersianNumber(addThousandSeparator(this.unitPrice.toString())) : '';
                },

                onRenewalPriceInput(period, input) {
                    const numeric = toEnglishNumber(input).replace(/[^\d]/g, '');
                    this.renewalPrices[period] = numeric ? Number(numeric) : 0;
                    this.formatRenewalPriceDisplay(period);
                },
                formatRenewalPriceDisplay(period) {
                    const price = this.renewalPrices[period];
                    this.renewalPricesDisplay[period] = price > 0 ? toPersianNumber(addThousandSeparator(price.toString())) : '';
                },

                // Custom Fields Methods
                addField() {
                    this.fields.push({
                        id: null, label: '', type: 'text',
                        options: [{ label: '', price: 0, pricing_type: 'fixed' }],
                        is_required: false, has_pricing: false, show_in_invoice: true,
                        pricing_type: 'fixed', pricing_amount: 0, sort_order: this.fields.length,
                        _key: Date.now(), _open: true, _dragging: false,
                    });
                    this.$nextTick(() => {
                        const el = document.querySelector('#fields-list > div:last-child');
                        el?.scrollIntoView({behavior: 'smooth', block: 'center'});
                    });
                },
                removeField(idx) {
                    if (confirm('این فیلد حذف شود؟')) {
                        this.fields.splice(idx, 1);
                    }
                },
                addOption(field) {
                    if (!Array.isArray(field.options)) field.options = [];
                    field.options.push({ label: '', price: 0, pricing_type: 'fixed' });
                },
                removeOption(field, optIdx) {
                    field.options.splice(optIdx, 1);
                },
                typeLabel(type) {
                    const map = {
                        text: 'متن',
                        textarea: 'متن بلند',
                        number: 'عدد',
                        select: 'انتخابی',
                        multiselect: 'چند انتخابی',
                        checkbox: 'چک‌باکس',
                        radio: 'رادیو',
                        date: 'تاریخ',
                        datetime: 'تاریخ و زمان',
                        file: 'فایل',
                        email: 'ایمیل',
                        url: 'لینک',
                        phone: 'تلفن'
                    };
                    return map[type] || type;
                },
                dragStart(idx) {
                    this.draggingIdx = idx;
                    this.fields[idx]._dragging = true;
                },
                dragOver(idx) {
                    if (this.draggingIdx === null || this.draggingIdx === idx) return;
                    const moved = this.fields.splice(this.draggingIdx, 1)[0];
                    this.fields.splice(idx, 0, moved);
                    this.draggingIdx = idx;
                },
                dragEnd() {
                    this.fields.forEach(f => f._dragging = false);
                    this.draggingIdx = null;
                },
                formatPriceDisplay(val) {
                    return val ? Number(val).toLocaleString('en-US') : '';
                },

                handlePricingInput(e, field) {
                    let raw = toEnglishNumber(e.target.value).replace(/[^\d]/g, '');
                    let num = raw ? Number(raw) : 0;

                    if (field.pricing_type === 'percentage' && num > 100) {
                        num = 100;
                    }

                    field.pricing_amount = num;
                    e.target.value = num > 0 ? num.toLocaleString('en-US') : '';
                },

                handleOptionPriceInput(e, opt) {
                    let raw = toEnglishNumber(e.target.value).replace(/[^\d]/g, '');
                    let num = raw ? Number(raw) : 0;
                    opt.price = num;
                    e.target.value = num > 0 ? num.toLocaleString('en-US') : '';
                }
            }));

            Alpine.data('persianField', (initialValue = 0, max = null) => ({
                value: initialValue,
                display: '',
                max: max,
                init() {
                    this.display = this.value ? toPersianNumber(this.value.toString()) : '';
                },
                handleInput(input) {
                    let englishValue = toEnglishNumber(input.toString());
                    let numericValue = englishValue.replace(/[^\d.]/g, '');
                    let num = Number(numericValue);
                    if (this.max !== null && num > this.max) {
                        num = this.max;
                        numericValue = num.toString();
                    }
                    this.value = numericValue;
                    this.display = numericValue ? toPersianNumber(numericValue) : '';
                }
            }));
        });
    </script>
@endsection
