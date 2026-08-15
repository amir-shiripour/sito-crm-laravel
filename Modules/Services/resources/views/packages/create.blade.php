@extends('layouts.user')

@section('title', 'ایجاد پکیج جدید سرویس و خدمات')

@php
    $currencyLabel = ($currency ?? 'toman') === 'rial' ? 'ریال' : 'تومان';
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl";
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="packageBuilder()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </span>
            ایجاد پکیج جدید سرویس و خدمات
        </h1>
        <a href="{{ route('services.packages.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            بازگشت به لیست
        </a>
    </div>

    @if($errors->any())
        <div
            class="p-5 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
            <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
            <div>
                <p class="font-black text-base mb-2">خطا در ثبت اطلاعات!</p>
                <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('services.packages.store') }}" method="POST" @submit="onSubmitCheck($event)"
          class="space-y-8">
        @csrf
        <div class="{{ $cardClass }} p-6 space-y-6">
            <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/50">
                <div class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                مشخصات عمومی پکیج
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="{{ $labelClass }}">عنوان پکیج <span class="text-rose-500 font-black">*</span></label>
                    <input type="text" name="name" required class="{{ $inputClass }}"
                           placeholder="مثال: پکیج طلایی طراحی سایت" value="{{ old('name') }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">کد پکیج</label>
                    <input type="text" name="code" class="{{ $inputClass }}" placeholder="مثال: PKG-101"
                           value="{{ old('code') }}" dir="ltr">
                </div>

                <div>
                    <label class="{{ $labelClass }}">وضعیت پکیج <span class="text-rose-500 font-black">*</span></label>
                    <select name="status" required class="{{ $inputClass }}">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>فعال
                        </option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="{{ $labelClass }}">توضیحات پکیج</label>
                    <textarea name="description" rows="2" class="{{ $inputClass }}"
                              placeholder="توضیحات کامل درباره پکیج و خدمات شامل شده...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- اقلام پکیج --}}
        <div class="{{ $cardClass }} relative"
             :class="items.some(i => i._showServiceDropdown) ? 'z-20 overflow-visible' : 'z-10 overflow-hidden'">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-wrap gap-4 justify-between items-center">
                <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                    <div
                        class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    سرویس‌ها و خدمات پکیج
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="addItem()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-xl transition-all dark:bg-indigo-500/20 dark:text-indigo-400 dark:hover:bg-indigo-500/30 active:scale-95 shadow-sm">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        انتخاب از سرویس‌ها
                    </button>
                </div>
            </div>

            <div class="w-full transition-all duration-300"
                 :class="items.some(i => i._showServiceDropdown) ? 'overflow-visible' : 'overflow-x-auto'">
                <table class="w-full text-sm text-start border-collapse min-w-[900px]">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 w-[30%] min-w-[200px] font-bold">سرویس / عنوان</th>
                        <th class="px-4 py-3 w-[28%] min-w-[180px] font-bold">شرح</th>
                        <th class="px-4 py-3 w-[15%] min-w-[130px] font-bold text-center">تعداد / واحد</th>
                        <th class="px-4 py-3 w-[20%] min-w-[190px] font-bold text-center">مبلغ واحد</th>
                        <th class="px-4 py-3 w-[12%] min-w-[130px] font-bold text-center">جمع ردیف</th>
                        <th class="px-4 py-3 w-12 text-center"></th>
                    </tr>
                    </thead>
                    <template x-for="(item, index) in items" :key="index">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 transition-all"
                               :class="item._showServiceDropdown ? 'relative z-50' : 'relative z-10'">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group"
                            :class="item._showServiceDropdown ? 'relative z-50' : 'relative z-10'">
                            <td class="px-4 py-3 align-top"
                                :class="item._showServiceDropdown ? 'overflow-visible' : ''">
                                <input type="hidden" :name="'items[' + index + '][service_id]'"
                                       :value="item.service_id">

                                <div class="space-y-2">
                                        <div class="flex items-center gap-2 w-full">
                                            <div class="relative flex-1 min-w-0"
                                                 @click.outside="item._showServiceDropdown = false">
                                                <input type="text"
                                                       :name="'items[' + index + '][custom_service_name]'"
                                                       :value="item.custom_service_name"
                                                       @focus="if ((item.custom_service_name || '').trim().length > 0) item._showServiceDropdown = true"
                                                       @input.debounce.300ms="item.custom_service_name = $event.target.value; onServiceInput(index)"
                                                       class="{{ $inputClass }} py-2.5 text-xs w-full"
                                                       placeholder="نام یا کد سرویس را تایپ کنید...">
                                                <div
                                                    x-show="item._showServiceDropdown && (item.custom_service_name || '').trim().length > 0 && filteredServices(index).length > 0"
                                                    x-transition
                                                    class="absolute z-[100] mt-1 w-full max-h-48 overflow-y-auto overscroll-contain bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                                                    <template x-for="s in filteredServices(index)" :key="s.id">
                                                        <button type="button" @click="selectService(index, s)"
                                                                class="w-full text-start px-4 py-3 text-xs hover:bg-indigo-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700/50 last:border-0 transition-colors">
                                                            <span class="font-bold text-gray-900 dark:text-white block"
                                                                  x-text="s.name"></span>
                                                            <span
                                                                class="text-[10px] text-gray-400 dark:text-gray-500 block mt-1"
                                                                x-text="formatMoney(s.base_price ? s.base_price : (s.unit_price || 0)) + ' {{ $currencyLabel }}' + (s.has_unit_pricing && s.unit_name ? ' / ' + s.unit_name : '')"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                            <button type="button"
                                                    x-show="item.service_custom_fields && item.service_custom_fields.length > 0"
                                                    @click="item._showCustomFields = !item._showCustomFields"
                                                    class="shrink-0 relative w-10 h-10 flex items-center justify-center rounded-xl transition-all border shadow-sm outline-none focus:ring-2 focus:ring-indigo-500/20 active:scale-95"
                                                    :class="item._showCustomFields ? 'bg-indigo-50 border-indigo-300 text-indigo-600 dark:bg-indigo-500/20 dark:border-indigo-500/40 dark:text-indigo-400' : 'bg-white border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'"
                                                    title="مشاهده فیلدهای سفارشی">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                                </svg>
                                                <span
                                                    class="absolute -top-1.5 -end-1.5 flex items-center justify-center min-w-4 h-4 px-1 rounded-full text-[9px] tabular-nums font-black shadow-sm"
                                                    :class="item._showCustomFields ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                                                    x-text="item.service_custom_fields.length"></span>
                                            </button>
                                        </div>

                                        {{-- دراپ‌داون دوره سرویس‌های دوره‌ای --}}
                                        <div x-show="item.service_raw && item.service_raw.billing_type === 'recurring'"
                                             class="mt-2">
                                            <input type="hidden" :name="'items[' + index + '][billing_period]'"
                                                   :value="item.billing_period">
                                            <select x-model="item.billing_period"
                                                    @change="updatePriceForPeriod(index)"
                                                    class="{{ $inputClass }} py-2 text-xs">
                                                <option value="">انتخاب دوره</option>
                                                <template x-for="(label, period) in periodLabels" :key="period">
                                                    <option :value="period" x-text="label"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <input type="text" x-model="item.description"
                                           :name="'items[' + index + '][description]'"
                                           class="{{ $inputClass }} py-2.5 text-xs w-full"
                                           placeholder="توضیحات ردیف...">
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div
                                        class="flex items-stretch w-32 mx-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all shadow-sm">
                                        <input type="text" :value="toPersianNum(item.quantity)"
                                               @input="let v = toEnglishNum($event.target.value).replace(/[^\d.]/g, ''); item.quantity = v;"
                                               :name="'items[' + index + '][quantity]'" required
                                               class="flex-1 min-w-0 w-full border-none bg-transparent py-2.5 px-1.5 font-black text-gray-900 dark:text-white text-center tabular-nums text-sm focus:ring-0 transition-all"
                                               dir="ltr" placeholder="۰">
                                        <div class="w-px bg-gray-200 dark:bg-gray-700 shrink-0"></div>
                                        <div class="w-14 flex items-stretch shrink-0 bg-slate-50 dark:bg-slate-800/80">
                                            <input type="text" x-model="item.unit" :name="'items[' + index + '][unit]'"
                                                   class="w-full min-w-0 border-none bg-transparent py-2 px-1 text-xs font-black text-center text-slate-500 dark:text-slate-400 focus:ring-0">
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex items-center gap-1.5 w-full">
                                        <div class="relative w-full">
                                            <input type="text" :value="formatPriceInput(item.unit_price)"
                                                   @input="item.unit_price = parsePriceInput($event.target.value)"
                                                   :name="'items[' + index + '][unit_price]'" required
                                                   :readonly="!!item.service_id && !item._priceUnlocked"
                                                   :class="(item.service_id && !item._priceUnlocked) ? 'bg-gray-100 dark:bg-gray-900 cursor-not-allowed text-gray-500 dark:text-gray-400' : ''"
                                                   class="{{ $inputClass }} py-2.5 text-sm font-black text-center tabular-nums w-full pe-12"
                                                   dir="ltr" placeholder="۰">
                                            <span
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                        </div>
                                        <button type="button" x-show="item.service_id"
                                                @click="item._priceUnlocked = !item._priceUnlocked"
                                                class="shrink-0 p-2.5 rounded-lg border transition-colors"
                                                :class="item._priceUnlocked ? 'border-indigo-400 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400' : 'border-gray-200 text-gray-400 hover:text-indigo-500 hover:border-indigo-300 dark:border-gray-700'"
                                                title="ویرایش مبلغ واحد">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div
                                        x-show="item.service_raw && !item.service_raw.has_unit_pricing && getPeriodPrice(item) > 0"
                                        class="text-[10px] text-gray-500 dark:text-gray-400 mt-1.5 text-center bg-gray-100 dark:bg-gray-800/50 p-1 rounded-md">
                                        (پایه: <span x-text="formatMoney(item.service_raw?.base_price || 0)"></span> +
                                        اشتراک: <span x-text="formatMoney(getPeriodPrice(item) || 0)"></span>)
                                    </div>
                                </td>
                                <td class="px-4 py-3 tabular-nums font-bold text-gray-800 dark:text-gray-100 text-center whitespace-nowrap align-top">
                                    <div class="py-2">
                                        <span x-text="formatMoney(calculateRowTotal(item))"></span>
                                        <span
                                            class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center align-top">
                                    <button type="button" @click="removeItem(index)"
                                            class="mt-1 text-gray-300 hover:text-red-500 dark:hover:bg-red-500/10 hover:bg-red-50 rounded-lg p-2 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            {{-- ردیف‌های قیمتی فیلدهای سفارشی --}}
                            <template x-if="item.service_custom_fields && item.service_custom_fields.length > 0">
                                <template x-for="field in item.service_custom_fields" :key="field.id + '_subrow'">
                                    <tr x-show="field.has_pricing && isFieldSelected(field, item.custom_field_values[field.id])"
                                        class="bg-indigo-50/20 dark:bg-indigo-500/5 border-y border-dashed border-indigo-100/70 dark:border-indigo-500/10 transition-all group relative">
                                        <td class="px-4 py-2.5 relative align-middle">
                                            <div
                                                class="absolute top-0 bottom-0 right-5 w-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                            <div
                                                class="absolute top-1/2 right-5 w-3 h-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                            <div class="pe-4 ps-6 flex items-center gap-2">
                                                <span
                                                    class="flex items-center justify-center w-5 h-5 rounded-md bg-indigo-100/80 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 shrink-0 shadow-sm">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                </span>
                                                <span
                                                    class="text-xs font-bold text-indigo-900 dark:text-indigo-300 truncate"
                                                    x-text="field.label"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle">
                                            <span
                                                class="inline-block text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100/70 dark:bg-gray-800/60 px-2.5 py-1 rounded-lg border border-gray-200/40 dark:border-gray-700/40"
                                                x-text="getFieldValueLabel(field, item.custom_field_values[field.id])"></span>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle">
                                            <input type="text" readonly :value="toPersianNum(item.quantity)"
                                                   class="{{ $inputClass }} py-1.5 text-xs text-center bg-gray-50/50 dark:bg-gray-900/20 opacity-70 cursor-not-allowed border-gray-200 dark:border-gray-800 text-gray-400 shadow-none w-32 mx-auto">
                                        </td>
                                        <td class="px-4 py-2.5 align-middle">
                                            <div class="flex items-center gap-1.5 w-full">
                                                <div class="relative w-full">
                                                    <input type="text"
                                                           :value="formatPriceInput(getCustomFieldPrice(item, field))"
                                                           @input="item.custom_field_custom_prices[field.id] = parsePriceInput($event.target.value)"
                                                           :name="'items[' + index + '][custom_fields_prices][' + field.id + ']'"
                                                           :readonly="!item._customPricesUnlocked?.[field.id]"
                                                           :class="!item._customPricesUnlocked?.[field.id] ? 'bg-gray-100 dark:bg-gray-900/50 cursor-not-allowed text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-800' : 'bg-white dark:bg-gray-900 border-indigo-300'"
                                                           class="{{ $inputClass }} py-2 text-sm text-center tabular-nums font-black w-full pe-14 shadow-none"
                                                           dir="ltr" placeholder="۰">
                                                    <span
                                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                                </div>
                                                <button type="button"
                                                        @click="item._customPricesUnlocked = item._customPricesUnlocked || {}; item._customPricesUnlocked[field.id] = !item._customPricesUnlocked[field.id]"
                                                        class="shrink-0 p-1.5 rounded-lg border transition-colors"
                                                        :class="item._customPricesUnlocked?.[field.id] ? 'border-indigo-400 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400' : 'border-gray-200 text-gray-400 hover:text-indigo-500 hover:border-indigo-300 dark:border-gray-700'"
                                                        title="ویرایش مبلغ فیلد">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle tabular-nums font-black text-indigo-600 dark:text-indigo-400 text-center whitespace-nowrap text-sm">
                                            <span x-text="formatMoney(getCustomFieldRowTotal(item, field))"></span>
                                            <span
                                                class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle text-center">
                                            <button type="button"
                                                    @click="if (field.type === 'checkbox') item.custom_field_values[field.id] = false; else if (field.type === 'multiselect') item.custom_field_values[field.id] = []; else item.custom_field_values[field.id] = '';"
                                                    class="text-gray-300 hover:text-red-500 dark:hover:bg-red-500/10 hover:bg-red-50 rounded-lg p-1.5 transition-colors"
                                                    title="حذف مقدار فیلد">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </template>

                            {{-- بخش تنظیمات فیلدهای سفارشی --}}
                            <tr x-show="item.service_custom_fields && item.service_custom_fields.length > 0">
                                <td colspan="6" class="p-0 border-0">
                                    <div x-show="item._showCustomFields"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 -translate-y-2"
                                         class="bg-slate-50/70 dark:bg-slate-800/40 border-y border-slate-200/80 dark:border-slate-700/80 shadow-[inset_0_4px_6px_-4px_rgba(0,0,0,0.05)] relative z-0">
                                        <div class="flex">
                                            <div
                                                class="w-1.5 bg-indigo-400/80 dark:bg-indigo-600/80 shadow-[2px_0_8px_rgba(99,102,241,0.2)]"></div>
                                            <div class="p-6 w-full">
                                                <div
                                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                                    <template x-for="field in item.service_custom_fields"
                                                              :key="field.id">
                                                        <div
                                                            class="relative p-3 rounded-xl bg-white dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/60 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-colors group">
                                                            <div class="flex justify-between items-center mb-2">
                                                                <label
                                                                    class="text-xs font-black text-gray-700 dark:text-gray-200 truncate"
                                                                    :title="field.label" x-text="field.label"></label>
                                                                <div class="flex items-center gap-2 shrink-0">
                                                                    <template
                                                                        x-if="field.has_pricing && getCustomFieldPrice(item, field) > 0">
                                                                        <span
                                                                            class="flex items-center gap-1 text-xs font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-500/20 shadow-sm">
                                                                            +<span
                                                                                x-text="formatMoney(getCustomFieldPrice(item, field))"></span>
                                                                            <span
                                                                                class="text-[9px] font-bold text-indigo-500 dark:text-indigo-400/80">{{ $currencyLabel }}</span>
                                                                        </span>
                                                                    </template>
                                                                    <span x-show="field.is_required"
                                                                          class="text-[9px] font-black text-rose-500 bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 rounded-md border border-rose-100 dark:border-rose-500/20">الزامی</span>
                                                                </div>
                                                            </div>
                                                            <div class="relative">
                                                                <template x-if="field.type === 'text'">
                                                                    <input type="text"
                                                                           :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                           x-model="item.custom_field_values[field.id]"
                                                                           class="{{ $inputClass }} py-2 text-xs"
                                                                           :placeholder="field.placeholder || field.label">
                                                                </template>

                                                                <template x-if="field.type === 'number'">
                                                                    <input type="text"
                                                                           :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                           x-model="item.custom_field_values[field.id]"
                                                                           class="{{ $inputClass }} py-2 text-xs"
                                                                           :placeholder="field.placeholder || field.label"
                                                                           dir="ltr">
                                                                </template>

                                                                <template x-if="field.type === 'select'">
                                                                    <select
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        x-model="item.custom_field_values[field.id]"
                                                                        class="{{ $inputClass }} py-2 text-xs">
                                                                        <option value="">انتخاب کنید...</option>
                                                                        <template
                                                                            x-for="opt in parseOptions(field.options)"
                                                                            :key="opt.value">
                                                                            <option :value="opt.value"
                                                                                    x-text="opt.label"></option>
                                                                        </template>
                                                                    </select>
                                                                </template>

                                                                <template x-if="field.type === 'multiselect'">
                                                                    <div
                                                                        class="flex flex-col gap-1.5 w-full max-h-40 overflow-y-auto">
                                                                        <template
                                                                            x-for="opt in parseOptions(field.options)"
                                                                            :key="opt.value">
                                                                            <label
                                                                                class="flex items-center gap-2 cursor-pointer text-xs">
                                                                                <input type="checkbox"
                                                                                       :name="'items[' + index + '][custom_fields][' + field.id + '][]'"
                                                                                       :value="opt.value"
                                                                                       x-model="item.custom_field_values[field.id]"
                                                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                                <span x-text="opt.label"
                                                                                      class="text-gray-700 dark:text-gray-300"></span>
                                                                            </label>
                                                                        </template>
                                                                    </div>
                                                                </template>

                                                                <template x-if="field.type === 'radio'">
                                                                    <div class="flex flex-col gap-1.5 w-full">
                                                                        <template
                                                                            x-for="opt in parseOptions(field.options)"
                                                                            :key="opt.value">
                                                                            <label
                                                                                class="flex items-center gap-2 cursor-pointer text-xs">
                                                                                <input type="radio"
                                                                                       :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                       x-model="item.custom_field_values[field.id]"
                                                                                       :value="opt.value"
                                                                                       class="text-indigo-600 focus:ring-indigo-500">
                                                                                <span x-text="opt.label"
                                                                                      class="text-gray-700 dark:text-gray-300"></span>
                                                                            </label>
                                                                        </template>
                                                                    </div>
                                                                </template>

                                                                <template x-if="field.type === 'textarea'">
                                                                    <textarea
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        x-model="item.custom_field_values[field.id]"
                                                                        rows="2" class="{{ $inputClass }} py-2 text-xs"
                                                                        :placeholder="field.placeholder || field.label"></textarea>
                                                                </template>

                                                                <template x-if="field.type === 'checkbox'">
                                                                    <label
                                                                        class="inline-flex items-center gap-2 cursor-pointer mt-1">
                                                                        <input type="checkbox"
                                                                               :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                               x-model="item.custom_field_values[field.id]"
                                                                               value="1"
                                                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                                        <span
                                                                            class="text-xs font-bold text-gray-700 dark:text-gray-300"
                                                                            x-text="field.label"></span>
                                                                    </label>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </template>
                </table>
            </div>

            {{-- Totals --}}
            <div class="bg-gray-50/50 dark:bg-gray-900/20 p-6 border-t border-gray-100 dark:border-gray-700/50">
                <input type="hidden" name="discount_type" :value="discountType">
                <input type="hidden" name="discount_value" :value="discountValue">

                <div class="w-full md:w-[28rem] ms-auto">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400 font-medium">
                            <span>جمع کل مبالغ</span>
                            <span class="tabular-nums font-medium">
                                <span x-text="formatMoney(totals.subtotal)"></span>
                                <span class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span>
                            </span>
                        </div>

                        <div class="flex justify-between items-center text-red-500 dark:text-red-400 font-medium gap-3">
                            <span class="flex items-center gap-2 shrink-0 flex-wrap">
                                تخفیف کل پکیج
                                <span class="relative inline-flex items-center gap-1">
                                    <input type="text"
                                           :value="discountType === 'percent' ? toPersianNum(discountValue) : formatPriceInput(discountValue)"
                                           @input="onDiscountInput($event)"
                                           :class="discountType === 'percent' ? 'w-14' : 'w-28'"
                                           class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-2 py-1 text-xs text-center tabular-nums font-bold text-red-700 dark:text-red-400 focus:ring-2 focus:ring-red-500/20 outline-none transition-all"
                                           dir="ltr"
                                           placeholder="۰">
                                    <button type="button" @click="toggleDiscountType()"
                                            class="px-2 py-1 rounded-lg text-[10px] font-black border transition-colors"
                                            :class="discountType === 'percent' ? 'bg-red-600 text-white border-red-600' : 'bg-white dark:bg-gray-800 text-red-500 border-red-200 dark:border-red-800'"
                                            title="تغییر نوع تخفیف (مبلغ / درصد)">
                                        <span x-text="discountType === 'percent' ? '٪' : '{{ $currencyLabel }}'"></span>
                                    </button>
                                </span>
                            </span>
                            <span class="tabular-nums font-medium">
                                − <span x-text="formatMoney(totals.packageDiscount)"></span>
                                <span class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span>
                            </span>
                        </div>

                        <div class="border-t-2 border-dashed border-gray-200 dark:border-gray-700 my-4"></div>

                        <div class="flex justify-between items-start">
                            <span
                                class="text-base font-black text-gray-900 dark:text-white block">مبلغ نهایی پکیج</span>
                            <div class="text-end">
                                <span class="tabular-nums text-xl font-black text-indigo-600 dark:text-indigo-400"
                                      x-text="formatMoney(totals.finalPrice)"></span>
                                <span class="text-xs text-gray-400 block">{{ $currencyLabel }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sticky Bottom Submit Bar --}}
        <div class="sticky bottom-4 z-40">
            <div
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-lg flex flex-row-reverse items-center justify-between gap-4">
                <button type="submit"
                        class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black shadow-lg shadow-indigo-500/30 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    ذخیره پکیج
                </button>
                <a href="{{ route('services.packages.index') }}"
                   class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    انصراف
                </a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('packageBuilder', () => ({
                servicesList: @json($services),
                items: [],
                discountType: 'amount',
                discountValue: 0,
                periodLabels: {monthly: 'ماهانه', quarterly: 'فصلی', semi_annual: 'شش ماهه', annual: 'سالانه'},

                init() {
                    this.addItem();
                },
                addItem() {
                    this.items.push({
                        service_id: '',
                        service_raw: null,
                        custom_service_name: '',
                        description: '',
                        quantity: 1,
                        unit: 'عدد',
                        unit_price: 0,
                        billing_period: '',
                        service_custom_fields: [],
                        custom_field_values: {},
                        custom_field_custom_prices: {},
                        _customPricesUnlocked: {},
                        _showServiceDropdown: false,
                        _showCustomFields: false,
                        _priceUnlocked: true,
                    });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                onServiceInput(index) {
                    const item = this.items[index];
                    item.service_id = '';
                    item.service_raw = null;
                    item.service_custom_fields = [];
                    item.custom_field_values = {};
                    item.custom_field_custom_prices = {};
                    item._customPricesUnlocked = {};
                    item._showServiceDropdown = (item.custom_service_name || '').trim().length > 0;
                    item._showCustomFields = false;
                    item._priceUnlocked = true;
                },
                filteredServices(index) {
                    const item = this.items[index];
                    if (!item || !item.custom_service_name || !item.custom_service_name.trim()) return [];
                    const q = item.custom_service_name.toLowerCase().trim();
                    return (this.servicesList || []).filter(s => (s.name || '').toLowerCase().includes(q) || (s.code || '').toLowerCase().includes(q)).slice(0, 10);
                },
                selectService(index, s) {
                    const item = this.items[index];
                    item.service_id = String(s.id);
                    item.service_raw = s;
                    item.custom_service_name = s.name;
                    item.billing_period = s.default_billing_period || '';
                    item.unit = s.has_unit_pricing ? (s.unit_name || 'عدد') : 'عدد';
                    item.service_custom_fields = s.custom_fields || s.customFields || [];
                    item.custom_field_values = {};
                    item.custom_field_custom_prices = {};
                    item._customPricesUnlocked = {};
                    item._showServiceDropdown = false;
                    item._showCustomFields = item.service_custom_fields.length > 0;
                    item._priceUnlocked = false;
                    this.updatePriceForPeriod(index);
                },
                parseOptions(opts) {
                    if (!opts) return [];
                    if (Array.isArray(opts)) {
                        return opts.map(o => {
                            if (typeof o === 'object' && o !== null) {
                                return {
                                    value: o.value || o.id || o.label || '',
                                    label: o.label || o.name || o.value || ''
                                };
                            }
                            return {value: String(o), label: String(o)};
                        });
                    }
                    if (typeof opts === 'string') {
                        let trimmed = opts.trim();
                        if (trimmed.startsWith('[') || trimmed.startsWith('{')) {
                            try {
                                let parsed = JSON.parse(trimmed);
                                return this.parseOptions(parsed);
                            } catch (e) {
                            }
                        }
                        return trimmed.split(',').map(s => s.trim()).filter(Boolean).map(s => ({value: s, label: s}));
                    }
                    return [];
                },
                isFieldSelected(f, v) {
                    if (v === undefined || v === null || v === '') return false;
                    if (f.type === 'checkbox') return !!v;
                    if (f.type === 'multiselect' && Array.isArray(v)) return v.length > 0;
                    return (v !== '' && v !== null && v !== undefined);
                },
                getFieldValueLabel(f, v) {
                    if (!this.isFieldSelected(f, v)) return '';
                    if (f.type === 'checkbox') return 'انتخاب شده';
                    if (f.type === 'multiselect' && Array.isArray(v)) return v.join('، ');
                    return v;
                },
                getCustomFieldPrice(it, f) {
                    if (it.custom_field_custom_prices && it.custom_field_custom_prices[f.id] !== undefined && it.custom_field_custom_prices[f.id] !== null && it.custom_field_custom_prices[f.id] !== '') {
                        let parsed = this.parsePriceInput(it.custom_field_custom_prices[f.id]);
                        if (!isNaN(parsed) && parsed > 0) return parsed;
                    }
                    let p = parseFloat(it.unit_price) || 0;
                    let a = Number(f.pricing_amount) || 0;
                    return f.pricing_type === 'percentage' ? p * (a / 100) : a;
                },
                getCustomFieldRowTotal(it, f) {
                    let q = parseFloat(it.quantity) || 0;
                    return this.getCustomFieldPrice(it, f) * q;
                },
                getPeriodPrice(it) {
                    if (!it.service_raw || it.service_raw.billing_type !== 'recurring' || !it.billing_period) return 0;
                    const r = it.service_raw.renewal_prices || {};
                    return Number(r[it.billing_period] || 0);
                },
                updatePriceForPeriod(index) {
                    const it = this.items[index];
                    const s = it.service_raw;
                    if (!s) return;
                    let p = s.base_price ? Number(s.base_price) : (Number(s.unit_price) || 0);
                    if (s.billing_type === 'recurring' && it.billing_period) {
                        p += this.getPeriodPrice(it);
                    }
                    it.unit_price = p;
                },
                onDiscountInput(e) {
                    let v = e.target.value;
                    let n = this.parsePriceInput(v);
                    if (this.discountType === 'percent') {
                        if (n > 100) n = 100;
                        if (n < 0) n = 0;
                        e.target.value = this.toPersianNum(n);
                    } else {
                        e.target.value = this.formatPriceInput(n);
                    }
                    this.discountValue = n;
                },
                toggleDiscountType() {
                    this.discountValue = 0;
                    this.discountType = this.discountType === 'amount' ? 'percent' : 'amount';
                },
                parsePriceInput(val) {
                    if (val === '' || val === null || val === undefined) return 0;
                    let n = this.toEnglishNum(val.toString()).replace(/[^\d]/g, '');
                    return n ? Number(n) : 0;
                },
                formatPriceInput(val) {
                    if (val === '' || val === null || val === undefined) return '';
                    let n = this.toEnglishNum(val.toString()).replace(/[^\d]/g, '');
                    if (!n) return '';
                    return this.toPersianNum(Number(n).toLocaleString('en-US'));
                },
                formatMoney(val) {
                    if (!val || isNaN(val)) return '۰';
                    return new Intl.NumberFormat('fa-IR').format(Math.round(val));
                },
                toPersianNum(val) {
                    if (val === '' || val === null || val === undefined) return '';
                    const d = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    return val.toString().replace(/\d/g, n => d[n]);
                },
                toEnglishNum(val) {
                    if (val === '' || val === null || val === undefined) return '';
                    const p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    const a = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                    return val.toString().replace(/[۰-۹]/g, d => p.indexOf(d)).replace(/[٠-٩]/g, d => a.indexOf(d));
                },
                calculateRowTotal(item) {
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    let total = qty * price;
                    if (item.service_custom_fields && item.custom_field_values) {
                        item.service_custom_fields.forEach(f => {
                            if (f.has_pricing && this.isFieldSelected(f, item.custom_field_values[f.id])) {
                                total += this.getCustomFieldPrice(item, f) * qty;
                            }
                        });
                    }
                    return Math.max(0, total);
                },
                get totals() {
                    let subtotal = 0;
                    this.items.forEach(item => {
                        subtotal += this.calculateRowTotal(item);
                    });
                    let pkgDisc = 0;
                    if (this.discountType === 'percent') {
                        pkgDisc = (subtotal * Math.min(100, Math.max(0, parseFloat(this.discountValue) || 0))) / 100;
                    } else {
                        pkgDisc = Math.min(subtotal, parseFloat(this.discountValue) || 0);
                    }
                    return {
                        subtotal: subtotal,
                        packageDiscount: pkgDisc,
                        finalPrice: Math.max(0, subtotal - pkgDisc)
                    };
                },
                onSubmitCheck(e) {
                    const f = e.target;
                    const nF = f.querySelectorAll('input[name*="[quantity]"], input[name*="[unit_price]"]');
                    nF.forEach(i => {
                        i.value = this.toEnglishNum(i.value).replace(/[^\d.]/g, '');
                    });
                }
            }));
});
</script>
@endpush
@endsection
