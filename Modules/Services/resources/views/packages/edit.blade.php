@extends('layouts.user')

@section('title', 'ویرایش پکیج سرویس و خدمات')

@include('partials.jalali-date-picker')

@php
    $currencyLabel = ($currency ?? 'toman') === 'rial' ? 'ریال' : 'تومان';
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl";

    $marketAttributesForJs = !empty($marketAttributes) ? $marketAttributes->map(function($attr) {
        return [
            'id' => $attr->id,
            'name' => $attr->name,
            'values' => $attr->values ? $attr->values->pluck('value')->toArray() : []
        ];
    })->values() : [];
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="packageBuilder()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-lg shadow-amber-500/30">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </span>
            ویرایش پکیج «{{ $package->name }}»
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

    <form action="{{ route('services.packages.update', $package) }}" method="POST" @submit="onSubmitCheck($event)"
          class="space-y-8">
        @csrf
        @method('PUT')
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
                    <input type="text" name="name" required class="{{ $inputClass }}" placeholder="عنوان پکیج..."
                           value="{{ old('name', $package->name) }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">کد پکیج</label>
                    <input type="text" name="code" class="{{ $inputClass }}" placeholder="کد پکیج..."
                           value="{{ old('code', $package->code) }}" dir="ltr">
                </div>

                <div>
                    <label class="{{ $labelClass }}">وضعیت پکیج <span class="text-rose-500 font-black">*</span></label>
                    <select name="status" required class="{{ $inputClass }}">
                        <option value="active" {{ old('status', $package->status) === 'active' ? 'selected' : '' }}>
                            فعال
                        </option>
                        <option value="inactive" {{ old('status', $package->status) === 'inactive' ? 'selected' : '' }}>
                            غیرفعال
                        </option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="{{ $labelClass }}">توضیحات پکیج</label>
                    <textarea name="description" rows="2" class="{{ $inputClass }}"
                              placeholder="توضیحات کامل درباره پکیج...">{{ old('description', $package->description) }}</textarea>
                </div>
            </div>
        </div>
        <div class="{{ $cardClass }} relative"
             :class="items.some(i => i._showServiceDropdown || i._hasOpenSelectDropdown) ? 'z-20 overflow-visible' : 'z-10 overflow-hidden'">
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
                    <button type="button" @click="addItem('service')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-xl transition-all dark:bg-indigo-500/20 dark:text-indigo-400 dark:hover:bg-indigo-500/30 active:scale-95 shadow-sm">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        انتخاب از سرویس‌ها
                    </button>
                    @if(!empty($marketModuleEnabled))
                        <button type="button" @click="addItem('product')"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-xl transition-all dark:bg-emerald-500/20 dark:text-emerald-400 dark:hover:bg-emerald-500/30 active:scale-95 shadow-sm">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            انتخاب از فروشگاه
                        </button>
                    @endif
                    <button type="button" @click="addItem('manual')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-violet-700 bg-violet-100 hover:bg-violet-200 rounded-xl transition-all dark:bg-violet-500/20 dark:text-violet-400 dark:hover:bg-violet-500/30 active:scale-95 shadow-sm">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h6"/>
                        </svg>
                        ردیف دستی
                    </button>
                </div>
            </div>

            <div class="w-full transition-all duration-300"
                 :class="items.some(i => i._showServiceDropdown || i._hasOpenSelectDropdown) ? 'overflow-visible' : 'overflow-x-auto'">
                <table class="w-full text-sm text-start border-collapse min-w-[900px]">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 w-[28%] min-w-[200px] max-w-[280px] font-bold">سرویس / عنوان</th>
                        <th class="px-4 py-3 w-[26%] min-w-[180px] max-w-[240px] font-bold">شرح</th>
                        <th class="px-4 py-3 w-[14%] min-w-[130px] max-w-[150px] font-bold text-center">تعداد / واحد
                        </th>
                        <th class="px-4 py-3 w-[18%] min-w-[170px] max-w-[200px] font-bold text-center">مبلغ واحد</th>
                        <th class="px-4 py-3 w-[14%] min-w-[130px] max-w-[150px] font-bold text-center">جمع ردیف</th>
                        <th class="px-4 py-3 w-12 text-center"></th>
                    </tr>
                    </thead>
                    <template x-for="item in items" :key="items.indexOf(item)">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 transition-all"
                               x-data="{ get index() { return items.indexOf(item); } }"
                               :class="(item._showServiceDropdown || item._showProductDropdown || item._hasOpenSelectDropdown) ? 'relative z-50' : 'relative z-10'">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group"
                            :class="(item._showServiceDropdown || item._showProductDropdown || item._hasOpenSelectDropdown) ? 'relative z-50' : 'relative z-10'">
                            <td class="px-4 py-3 align-top"
                                :class="(item._showServiceDropdown || item._showProductDropdown || item._hasOpenSelectDropdown) ? 'overflow-visible' : ''">
                                <input type="hidden" :name="'items[' + index + '][mode]'" :value="item.mode">
                                <input type="hidden" :name="'items[' + index + '][service_id]'" :value="item.service_id">
                                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                <input type="hidden" :name="'items[' + index + '][product_variant_id]'" :value="item.product_variant_id">

                                {{-- Mode: Service --}}
                                <template x-if="item.mode === 'service'">
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
                                                                x-text="formatMoney(s.base_price ? s.base_price : (s.unit_price || 0)) + ' {{ $currencyLabel }}' + (s['has_unit_pricing'] && s['unit_name'] ? ' / ' + s['unit_name'] : '')"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                            <button type="button"
                                                    x-show="item.service_custom_fields && item.service_custom_fields.length > 0"
                                                    @click="item['_showCustomFields'] = !item['_showCustomFields']"
                                                    class="shrink-0 relative w-10 h-10 flex items-center justify-center rounded-xl transition-all border shadow-sm outline-none focus:ring-2 focus:ring-indigo-500/20 active:scale-95"
                                                    :class="item['_showCustomFields'] ? 'bg-indigo-50 border-indigo-300 text-indigo-600 dark:bg-indigo-500/20 dark:border-indigo-500/40 dark:text-indigo-400' : 'bg-white border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'"
                                                    title="مشاهده فیلدهای سفارشی">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                                </svg>
                                                <span
                                                    class="absolute -top-1.5 -end-1.5 flex items-center justify-center min-w-4 h-4 px-1 rounded-full text-[9px] tabular-nums font-black shadow-sm"
                                                    :class="item['_showCustomFields'] ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                                                    x-text="item.service_custom_fields.length"></span>
                                            </button>
                                        </div>
                                        <div x-show="item.service_raw && item.service_raw.billing_type === 'recurring'"
                                             class="mt-2">
                                            <input type="hidden" :name="'items[' + index + '][billing_period]'"
                                                   :value="item.billing_period">
                                            <select x-model="item.billing_period"
                                                    @change="updatePriceForPeriod(index)"
                                                    class="{{ $inputClass }} py-2 text-xs">
                                                <option value="">انتخاب دوره</option>
                                                <template x-for="period in Object.keys(periodLabels)" :key="period">
                                                    <option :value="period" x-text="periodLabels[period]"
                                                            :selected="item.billing_period === period"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </template>

                                {{-- Mode: Product --}}
                                <template x-if="item.mode === 'product'">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 w-full">
                                            <div class="relative flex-1 min-w-0"
                                                 @click.outside="item._showProductDropdown = false">
                                                <input type="text"
                                                       :name="'items[' + index + '][custom_service_name]'"
                                                       :value="item.custom_service_name"
                                                       @input.debounce.300ms="item.custom_service_name = $event.target.value; onProductInput(index)"
                                                       @focus="if ((item.custom_service_name || '').trim().length >= 2) item._showProductDropdown = true"
                                                       class="{{ $inputClass }} py-2.5 text-xs w-full"
                                                       placeholder="نام یا کد کالا را تایپ کنید...">
                                                <div
                                                    x-show="item._showProductDropdown && filteredProducts(index).length > 0"
                                                    x-transition
                                                    class="absolute z-[100] mt-1 w-full max-h-48 overflow-y-auto overscroll-contain bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                                                    <template x-for="p in filteredProducts(index)" :key="p.id">
                                                        <button type="button" @click="selectProductInline(index, p)"
                                                                class="w-full text-start px-4 py-3 text-xs hover:bg-emerald-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700/50 last:border-0 transition-colors flex justify-between items-center">
                                                            <div>
                                                                <span class="font-bold text-gray-900 dark:text-white block"
                                                                      x-text="p.name"></span>
                                                                <span
                                                                    class="text-[10px] text-gray-400 dark:text-gray-500 block mt-1"
                                                                    x-text="formatMoney(p.price) + ' {{ $currencyLabel }}'"></span>
                                                            </div>
                                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded"
                                                                  :class="p.stock > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400'"
                                                                  x-text="p.stock > 0 ? p.stock + ' موجود' : 'ناموجود'"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                            <button type="button" @click="openProductModal(index)"
                                                    class="shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-100 dark:hover:bg-emerald-500/30 transition-all shadow-sm active:scale-95"
                                                    title="انتخاب کالا با فیلتر پیشرفته">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                {{-- Mode: Manual --}}
                                <template x-if="item.mode === 'manual'">
                                    <div>
                                        <input type="text"
                                               :name="'items[' + index + '][custom_service_name]'"
                                               :value="item.custom_service_name"
                                               @input="item.custom_service_name = $event.target.value"
                                               class="{{ $inputClass }} py-2.5 text-xs w-full"
                                               placeholder="عنوان خدمت یا آیتم دستی...">
                                    </div>
                                </template>
                            </td>
                            <td class="px-4 py-3 align-top max-w-[240px]">
                                <input type="text" x-model="item.description"
                                       :name="'items[' + index + '][description]'"
                                       class="{{ $inputClass }} py-2.5 text-xs w-full max-w-full"
                                       placeholder="توضیحات ردیف...">
                            </td>
                            <td class="px-4 py-3 align-top max-w-[150px]">
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
                            <td class="px-4 py-3 align-top max-w-[200px]">
                                <div class="flex items-center gap-1.5 w-full">
                                    <div class="relative w-full">
                                        <input type="text"
                                               x-effect="if(document.activeElement !== $el) $el.value = formatPriceInput(item.unit_price)"
                                               @input="let val = parsePriceInput($event.target.value); item.unit_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                               @blur="$el.value = formatPriceInput(item.unit_price)"
                                               :name="'items[' + index + '][unit_price]'" required
                                               :readonly="(item.mode !== 'manual' && (item.service_id || item.product_id)) && !item._priceUnlocked"
                                               :class="((item.mode !== 'manual' && (item.service_id || item.product_id)) && !item._priceUnlocked) ? 'bg-gray-100 dark:bg-gray-900 cursor-not-allowed text-gray-500 dark:text-gray-400' : ''"
                                               class="{{ $inputClass }} py-2.5 text-sm font-black text-center tabular-nums w-full pe-12"
                                               dir="ltr" placeholder="۰">
                                        <span
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                    </div>
                                    <button type="button" x-show="item.mode !== 'manual' && (item.service_id || item.product_id)"
                                            @click="item._priceUnlocked = !item._priceUnlocked; if(item._priceUnlocked) { $nextTick(() => { const inp = $el.closest('div.flex').querySelector('input[type=text]'); if(inp) { inp.removeAttribute('readonly'); inp.focus(); inp.select(); } }); }"
                                            class="shrink-0 p-2.5 rounded-lg border transition-colors"
                                            :class="item._priceUnlocked ? 'border-amber-400 bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400' : 'border-gray-200 text-gray-400 hover:text-amber-500 hover:border-amber-300 dark:border-gray-700'"
                                            title="ویرایش مبلغ واحد">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div
                                    x-show="item.service_raw && !item.service_raw['has_unit_pricing'] && getPeriodPrice(item) > 0"
                                    class="text-[10px] text-gray-500 dark:text-gray-400 mt-1.5 text-center bg-gray-100 dark:bg-gray-800/50 p-1 rounded-md">
                                    (پایه: <span x-text="formatMoney(item.service_raw?.base_price || 0)"></span> +
                                    اشتراک: <span x-text="formatMoney(getPeriodPrice(item) || 0)"></span>)
                                </div>
                            </td>
                            <td class="px-4 py-3 tabular-nums font-bold text-gray-800 dark:text-gray-100 text-center whitespace-nowrap align-top max-w-[150px]">
                                <div class="py-2">
                                    <span x-text="formatMoney(calculateRowTotal(item))"></span>
                                    <span
                                        class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center align-top w-12">
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
                        <template x-if="item.service_custom_fields && item.service_custom_fields.length > 0">
                            <template x-for="field in item.service_custom_fields" :key="field.id + '_subrows_group'">
                                <tbody class="contents">
                                <template x-if="field.type === 'multiselect' && field.has_pricing">
                                    <template
                                        x-for="opt in (Array.isArray(item.custom_field_values[field.id]) ? item.custom_field_values[field.id] : [])"
                                        :key="field.id + '_' + opt + '_subrow'">
                                        <tr class="bg-indigo-50/20 dark:bg-indigo-500/5 border-y border-dashed border-indigo-100/70 dark:border-indigo-500/10 transition-all group relative">
                                            <td class="px-4 py-2.5 relative align-middle max-w-[280px]">
                                                <div
                                                    class="absolute top-0 bottom-0 right-5 w-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                                <div
                                                    class="absolute top-1/2 right-5 w-3 h-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                                <div class="pe-4 ps-6 flex items-center gap-2 min-w-0">
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
                                                        :title="field.label + ': ' + opt"
                                                        x-text="field.label + ': ' + opt"></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle max-w-[240px]">
                                                <div class="max-w-full overflow-hidden">
                                                        <span
                                                            class="inline-block truncate text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100/70 dark:bg-gray-800/60 px-2.5 py-1 rounded-lg border border-gray-200/40 dark:border-gray-700/40 max-w-full"
                                                            :title="opt"
                                                            x-text="opt"></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle max-w-[150px]">
                                                <input type="text"
                                                       :value="toPersianNum(getCustomFieldQuantity(item, field, opt))"
                                                       @input="setCustomFieldQuantity(item, field, opt, $event.target.value)"
                                                       :name="'items[' + index + '][custom_fields_quantities][' + field.id + '][' + opt + ']'"
                                                       class="{{ $inputClass }} py-1.5 text-xs text-center tabular-nums font-bold border-indigo-200 dark:border-indigo-800/60 shadow-none w-32 mx-auto"
                                                       dir="ltr" placeholder="۱">
                                            </td>
                                            <td class="px-4 py-2.5 align-middle max-w-[200px]">
                                                <div class="flex items-center gap-1.5 w-full">
                                                    <div class="relative w-full">
                                                        <input type="text"
                                                               :value="formatPriceInput(getCustomFieldPrice(item, field, opt))"
                                                               @input="setCustomFieldPrice(item, field, opt, $event.target.value)"
                                                               :name="'items[' + index + '][custom_fields_prices][' + field.id + '][' + opt + ']'"
                                                               :readonly="!isCustomPriceUnlocked(item, field, opt)"
                                                               :class="!isCustomPriceUnlocked(item, field, opt) ? 'bg-gray-100 dark:bg-gray-900/50 cursor-not-allowed text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-800' : 'bg-white dark:bg-gray-900 border-indigo-300'"
                                                               class="{{ $inputClass }} py-2 text-sm text-center tabular-nums font-black w-full pe-14 shadow-none"
                                                               dir="ltr" placeholder="۰">
                                                        <span
                                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                                    </div>
                                                    <button type="button"
                                                            @click="toggleCustomPriceUnlock(item, field, opt)"
                                                            class="shrink-0 p-1.5 rounded-lg border transition-colors"
                                                            :class="isCustomPriceUnlocked(item, field, opt) ? 'border-amber-400 bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400' : 'border-gray-200 text-gray-400 hover:text-amber-500 hover:border-amber-300 dark:border-gray-700'"
                                                            title="ویرایش مبلغ گزینه">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle tabular-nums font-black text-indigo-600 dark:text-indigo-400 text-center whitespace-nowrap text-sm max-w-[150px]">
                                                <span
                                                    x-text="formatMoney(getCustomFieldRowTotal(item, field, opt))"></span>
                                                <span
                                                    class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle text-center w-12">
                                                <button type="button"
                                                        @click="removeMultiselectOption(item, field.id, opt)"
                                                        class="text-gray-300 hover:text-red-500 dark:hover:bg-red-500/10 hover:bg-red-50 rounded-lg p-1.5 transition-colors"
                                                        title="حذف این گزینه">
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
                                    <template x-if="field.type !== 'multiselect' && field.has_pricing">
                                        <tr x-show="isFieldSelected(field, item.custom_field_values[field.id])"
                                            class="bg-indigo-50/20 dark:bg-indigo-500/5 border-y border-dashed border-indigo-100/70 dark:border-indigo-500/10 transition-all group relative">
                                            <td class="px-4 py-2.5 relative align-middle max-w-[280px]">
                                                <div
                                                    class="absolute top-0 bottom-0 right-5 w-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                                <div
                                                    class="absolute top-1/2 right-5 w-3 h-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                                <div class="pe-4 ps-6 flex items-center gap-2 min-w-0">
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
                                                        :title="field.label"
                                                        x-text="field.label"></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle max-w-[240px]">
                                                <div class="max-w-full overflow-hidden">
                                                    <span
                                                        class="block truncate text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100/70 dark:bg-gray-800/60 px-2.5 py-1 rounded-lg border border-gray-200/40 dark:border-gray-700/40 max-w-full"
                                                        :title="getFieldValueLabel(field, item.custom_field_values[field.id])"
                                                        x-text="getFieldValueLabel(field, item.custom_field_values[field.id])"></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle max-w-[150px]">
                                                <input type="text"
                                                       :value="toPersianNum(getCustomFieldQuantity(item, field))"
                                                       @input="
                                                           setCustomFieldQuantity(item, field, null, $event.target.value);
                                                           if (field.type === 'number') {
                                                               item.custom_field_values[field.id] = parsePriceInput($event.target.value);
                                                           }
                                                       "
                                                       :disabled="!isFieldSelected(field, item.custom_field_values[field.id])"
                                                       :name="'items[' + index + '][custom_fields_quantities][' + field.id + ']'"
                                                       class="{{ $inputClass }} py-1.5 text-xs text-center tabular-nums font-bold border-indigo-200 dark:border-indigo-800/60 shadow-none w-32 mx-auto"
                                                       dir="ltr" placeholder="۱">
                                            </td>
                                            <td class="px-4 py-2.5 align-middle max-w-[200px]">
                                                <div class="flex items-center gap-1.5 w-full">
                                                    <div class="relative w-full">
                                                        <input type="text"
                                                               :value="formatPriceInput(getCustomFieldPrice(item, field))"
                                                               @input="setCustomFieldPrice(item, field, null, $event.target.value)"
                                                               :disabled="!isFieldSelected(field, item.custom_field_values[field.id])"
                                                               :name="'items[' + index + '][custom_fields_prices][' + field.id + ']'"
                                                               :readonly="!isCustomPriceUnlocked(item, field)"
                                                               :class="!isCustomPriceUnlocked(item, field) ? 'bg-gray-100 dark:bg-gray-900/50 cursor-not-allowed text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-800' : 'bg-white dark:bg-gray-900 border-indigo-300'"
                                                               class="{{ $inputClass }} py-2 text-sm text-center tabular-nums font-black w-full pe-14 shadow-none"
                                                               dir="ltr" placeholder="۰">
                                                        <span
                                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                                    </div>
                                                    <button type="button"
                                                            @click="toggleCustomPriceUnlock(item, field)"
                                                            class="shrink-0 p-1.5 rounded-lg border transition-colors"
                                                            :class="isCustomPriceUnlocked(item, field) ? 'border-amber-400 bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400' : 'border-gray-200 text-gray-400 hover:text-amber-500 hover:border-amber-300 dark:border-gray-700'"
                                                            title="ویرایش مبلغ فیلد">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle tabular-nums font-black text-indigo-600 dark:text-indigo-400 text-center whitespace-nowrap text-sm max-w-[150px]">
                                                <span x-text="formatMoney(getCustomFieldRowTotal(item, field))"></span>
                                                <span
                                                    class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle text-center w-12">
                                                <button type="button"
                                                        @click="clearFieldValue(item, field)"
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
                                    </tbody>
                                </template>
                            </template>
                        <tr x-show="item.service_custom_fields && item.service_custom_fields.length > 0"
                            :class="item._hasOpenSelectDropdown ? 'relative z-50' : 'relative z-10'">
                            <td colspan="6" class="p-0 border-0">
                                <div x-show="item['_showCustomFields']"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-2"
                                     class="bg-slate-50/70 dark:bg-slate-800/40 border-y border-slate-200/80 dark:border-slate-700/80 shadow-[inset_0_4px_6px_-4px_rgba(0,0,0,0.05)] relative"
                                     :class="item._hasOpenSelectDropdown ? 'z-50 overflow-visible' : 'z-0'">
                                    <div class="flex">
                                        <div
                                            class="w-1.5 bg-indigo-400/80 dark:bg-indigo-600/80 shadow-[2px_0_8px_rgba(99,102,241,0.2)]"></div>
                                        <div class="p-6 w-full">
                                            <div
                                                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                                <template x-for="field in item.service_custom_fields"
                                                          :key="field.id">
                                                    <div x-data="{ open: false }"
                                                         :class="open ? 'relative z-[100]' : 'relative z-10'"
                                                         class="p-3 rounded-xl bg-white dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/60 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-colors group">
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
                                                            <template
                                                                x-if="['text', 'email', 'phone', 'url'].includes(field.type)">
                                                                <input :type="field.type === 'url' ? 'url' : field.type"
                                                                       :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                       x-model="item.custom_field_values[field.id]"
                                                                       class="{{ $inputClass }} py-2 text-xs"
                                                                       :placeholder="field.placeholder || field.label"
                                                                       :required="field.is_required">
                                                            </template>

                                                            <template x-if="field.type === 'datetime'">
                                                                <div class="relative w-full">
                                                                    <input type="text" readonly data-jdp-with-time
                                                                           :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                           x-model="item.custom_field_values[field.id]"
                                                                           @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: true, hasSecond: false}); jalaliDatepicker.show($el); }"
                                                                           @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: true, hasSecond: false}); jalaliDatepicker.show($el); }"
                                                                           @change="item.custom_field_values[field.id] = $el.value"
                                                                           class="{{ $inputClass }} py-2 text-xs cursor-pointer pl-8"
                                                                           placeholder="انتخاب تاریخ و ساعت"
                                                                           autocomplete="off"
                                                                           :required="field.is_required">
                                                                    <button type="button"
                                                                            x-show="item.custom_field_values[field.id]"
                                                                            @click="item.custom_field_values[field.id] = '';"
                                                                            class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 p-0.5"
                                                                            title="پاک کردن تاریخ">
                                                                        <svg class="w-3.5 h-3.5" fill="none"
                                                                             viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                  stroke-linejoin="round"
                                                                                  stroke-width="2"
                                                                                  d="M6 18L18 6M6 6l12 12"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </template>

                                                            <template x-if="field.type === 'date'">
                                                                <div class="relative w-full">
                                                                    <input type="text" readonly data-jdp-only-date
                                                                           :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                           x-model="item.custom_field_values[field.id]"
                                                                           @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                           @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                           @change="item.custom_field_values[field.id] = $el.value"
                                                                           class="{{ $inputClass }} py-2 text-xs cursor-pointer pl-8"
                                                                           placeholder="انتخاب تاریخ"
                                                                           autocomplete="off"
                                                                           :required="field.is_required">
                                                                    <button type="button"
                                                                            x-show="item.custom_field_values[field.id]"
                                                                            @click="item.custom_field_values[field.id] = '';"
                                                                            class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 p-0.5"
                                                                            title="پاک کردن تاریخ">
                                                                        <svg class="w-3.5 h-3.5" fill="none"
                                                                             viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                  stroke-linejoin="round"
                                                                                  stroke-width="2"
                                                                                  d="M6 18L18 6M6 6l12 12"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </template>

                                                            <template x-if="field.type === 'number'">
                                                                <input type="text"
                                                                       :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                       :value="formatPriceInput(item.custom_field_values[field.id])"
                                                                       @input="
                                                                               let val = parsePriceInput($event.target.value);
                                                                               item.custom_field_values[field.id] = val;
                                                                               if (field.has_pricing) {
                                                                                   setCustomFieldQuantity(item, field, null, val);
                                                                               }
                                                                           "
                                                                       class="{{ $inputClass }} py-2 text-xs text-center tabular-nums font-bold"
                                                                       :placeholder="field.placeholder || field.label"
                                                                       dir="ltr"
                                                                       :required="field.is_required">
                                                            </template>

                                                            <template x-if="field.type === 'select'">
                                                                <div class="relative w-full"
                                                                     @click.outside="open = false; item._hasOpenSelectDropdown = false">
                                                                    <button type="button"
                                                                            @click="open = !open; item._hasOpenSelectDropdown = open"
                                                                            class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none cursor-pointer transition-all text-start">
                                                                         <span
                                                                             x-text="item.custom_field_values[field.id] || 'انتخاب کنید...'"
                                                                             :class="item.custom_field_values[field.id] ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'"></span>
                                                                        <svg
                                                                            class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                                                                            :class="open ? 'rotate-180 text-indigo-600' : ''"
                                                                            fill="none" viewBox="0 0 24 24"
                                                                            stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                  stroke-linejoin="round"
                                                                                  stroke-width="2"
                                                                                  d="M19 9l-7 7-7-7"/>
                                                                        </svg>
                                                                    </button>
                                                                    <input type="hidden"
                                                                           :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                           :value="item.custom_field_values[field.id]"
                                                                           :required="field.is_required && !item.custom_field_values[field.id]">

                                                                    <div x-show="open"
                                                                         x-transition.opacity.duration.150ms
                                                                         style="display: none;"
                                                                         class="absolute z-[100] mt-1 w-full max-h-48 overflow-y-auto overscroll-contain bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-1">
                                                                        <button type="button"
                                                                                @click="item.custom_field_values[field.id] = ''; open = false; item._hasOpenSelectDropdown = false; calculateTotals();"
                                                                                class="w-full text-start px-3 py-2 text-xs text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                                            انتخاب کنید...
                                                                        </button>
                                                                        <template
                                                                            x-for="opt in getFieldOptionsList(field)"
                                                                            :key="opt.label">
                                                                            <button type="button"
                                                                                    @click="item.custom_field_values[field.id] = opt.label; open = false; item._hasOpenSelectDropdown = false; calculateTotals();"
                                                                                    class="w-full flex items-center justify-between text-start px-3 py-2 text-xs hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border-t border-gray-100 dark:border-gray-700/40 transition-colors"
                                                                                    :class="item.custom_field_values[field.id] === opt.label ? 'bg-indigo-50/70 dark:bg-indigo-500/20 font-bold text-indigo-600 dark:text-indigo-400' : 'text-gray-800 dark:text-gray-200'">
                                                                                <span x-text="opt.label"
                                                                                      class="font-medium"></span>
                                                                                <span
                                                                                    x-show="field.has_pricing && opt.price > 0"
                                                                                    class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-200/50 dark:border-emerald-500/20"
                                                                                    x-text="'+ ' + (opt.pricing_type === 'percentage' ? (opt.price + '%') : (formatMoney(opt.price) + ' {{ $currencyLabel }}'))"></span>
                                                                            </button>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                                <template x-if="field.type === 'multiselect'">
                                                                    <div
                                                                        class="flex flex-col gap-1.5 w-full max-h-40 overflow-y-auto">
                                                                        <template
                                                                            x-for="opt in getFieldOptionsList(field)"
                                                                            :key="opt.label">
                                                                            <label
                                                                                class="flex items-center justify-between gap-2.5 cursor-pointer text-xs px-2 py-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                                                                <div class="flex items-center gap-2">
                                                                                    <input type="checkbox"
                                                                                           :name="'items[' + index + '][custom_fields][' + field.id + '][]'"
                                                                                           :value="opt.label"
                                                                                           :checked="Array.isArray(item.custom_field_values[field.id]) && item.custom_field_values[field.id].includes(opt.label)"
                                                                                           @change="toggleMultiselect(item, field.id, opt.label, $event.target.checked); calculateTotals();"
                                                                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                                    <span x-text="opt.label"
                                                                                          class="text-gray-700 dark:text-gray-300 font-medium"></span>
                                                                                </div>
                                                                                <template
                                                                                    x-if="field.has_pricing && opt.price > 0">
                                                                                    <span
                                                                                        class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-1.5 py-0.5 rounded border border-indigo-200 dark:border-indigo-500/20"
                                                                                        x-text="'+ ' + formatMoney(opt.price) + ' {{ $currencyLabel }}'"></span>
                                                                                </template>
                                                                            </label>
                                                                        </template>
                                                                    </div>
                                                                </template>

                                                                <template x-if="field.type === 'radio'">
                                                                    <div class="flex flex-col gap-1.5 w-full">
                                                                        <template
                                                                            x-for="opt in getFieldOptionsList(field)"
                                                                            :key="opt.label">
                                                                            <label
                                                                                class="flex items-center justify-between gap-2.5 cursor-pointer text-xs px-2 py-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                                                                <div class="flex items-center gap-2">
                                                                                    <input type="radio"
                                                                                           :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                           x-model="item.custom_field_values[field.id]"
                                                                                           :value="opt.label"
                                                                                           @change="calculateTotals()"
                                                                                           class="text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600">
                                                                                    <span x-text="opt.label"
                                                                                          class="text-gray-700 dark:text-gray-300 font-medium"></span>
                                                                                </div>
                                                                                <template
                                                                                    x-if="field.has_pricing && opt.price > 0">
                                                                                    <span
                                                                                        class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-1.5 py-0.5 rounded border border-indigo-200 dark:border-indigo-500/20"
                                                                                        x-text="'+ ' + formatMoney(opt.price) + ' {{ $currencyLabel }}'"></span>
                                                                                </template>
                                                                            </label>
                                                                        </template>
                                                                    </div>
                                                                </template>

                                                                <template x-if="field.has_pricing && ['select', 'multiselect', 'radio'].includes(field.type)">
                                                                    <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                                                                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] text-gray-600 dark:text-gray-400 select-none">
                                                                            <input type="checkbox"
                                                                                   :name="'items[' + index + '][custom_fields_use_default_price][' + field.id + ']'"
                                                                                   value="1"
                                                                                   x-model="item.custom_field_use_default_price[field.id]"
                                                                                   @change="calculateTotals()"
                                                                                   class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                            <span>استفاده از قیمت پیش‌فرض</span>
                                                                        </label>
                                                                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500"
                                                                              x-text="'(پیش‌فرض: ' + formatMoney(field.pricing_amount || 0) + ' ' + (field.pricing_type === 'percentage' ? '%' : '{{ $currencyLabel }}') + ')'"></span>
                                                                    </div>
                                                                </template>

                                                                <template x-if="field.type === 'textarea'">
                                                                    <textarea
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        x-model="item.custom_field_values[field.id]"
                                                                        rows="2" class="{{ $inputClass }} py-2 text-xs"
                                                                        :placeholder="field.placeholder || field.label"
                                                                        :required="field.is_required"></textarea>
                                                                </template>

                                                                <template x-if="field.type === 'checkbox'">
                                                                    <label
                                                                        class="inline-flex items-center gap-2 cursor-pointer mt-1">
                                                                        <input type="checkbox"
                                                                               :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                               x-model="item.custom_field_values[field.id]"
                                                                               value="1"
                                                                               @change="calculateTotals()"
                                                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                                        <span
                                                                            class="text-xs font-bold text-gray-700 dark:text-gray-300"
                                                                            x-text="field.label"></span>
                                                                    </label>
                                                                </template>

                                                                <template x-if="field.type === 'file'">
                                                                    <input type="file"
                                                                           :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                           @change="item.custom_field_values[field.id] = $event.target.files[0]?.name || ''"
                                                                           class="w-full text-xs rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2 text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-400"
                                                                           :required="field.is_required">
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
                        class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 shadow-amber-500/30 hover:shadow-amber-500/50 hover:from-amber-400 hover:to-amber-500 text-white font-black shadow-lg transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    ذخیره تغییرات پکیج
                </button>
                <a href="{{ route('services.packages.index') }}"
                   class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    انصراف
                </a>
            </div>
        </div>
    </form>
    @if(!empty($marketModuleEnabled))
    <div x-show="activeProductModalIndex !== null" x-transition.opacity
         class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;" @click.self="closeProductModal()">
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden"
            x-transition.scale.origin.center @click.outside="closeProductModal()">
            <div
                class="p-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/30">
                <h3 class="text-lg font-black text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    انتخاب کالا از فروشگاه
                </h3>
                <button @click="closeProductModal()"
                        class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 hover:text-rose-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div
                class="p-5 border-b border-gray-200 dark:border-gray-700 space-y-4 bg-gray-50/50 dark:bg-gray-800/50">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Search Input --}}
                    <div class="lg:col-span-2 relative">
                        <input type="text" x-model="modalProductSearch"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all placeholder-gray-400"
                               placeholder="جستجو بر اساس نام، مدل یا بارکد کالا...">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Stock Status Filter --}}
                    <div>
                        <select x-model="modalStockStatus"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none transition-all cursor-pointer">
                            <option value="all">وضعیت موجودی (همه)</option>
                            <option value="in_stock">فقط کالاهای موجود</option>
                            <option value="out_of_stock">فقط ناموجود</option>
                        </select>
                    </div>

                    {{-- Brand Filter --}}
                    <div>
                        <select x-model="modalSelectedBrand"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none transition-all cursor-pointer">
                            <option value="">برند (همه)</option>
                            <template x-for="b in modalBrands" :key="b.id">
                                <option :value="b.id" x-text="b.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Attributes Filters --}}
                    <template x-for="attr in modalProductAttributes" :key="attr.name">
                        <div class="relative" x-data="{ open: false }"
                             x-init="if(!modalSelectedAttributes[attr.name]) modalSelectedAttributes = { ...modalSelectedAttributes, [attr.name]: [] }"
                             @click.outside="open = false">
                            <button type="button" @click="open = !open"
                                    class="w-full flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none transition-all cursor-pointer">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="truncate" x-text="attr.name"></span>
                                    <template
                                        x-if="modalSelectedAttributes[attr.name] && modalSelectedAttributes[attr.name].length > 0">
                                        <span
                                            class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400 text-[10px] font-bold"
                                            x-text="toPersianNum(modalSelectedAttributes[attr.name].length)"></span>
                                    </template>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" style="display: none;"
                                 class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <div class="max-h-48 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                                    <template x-for="v in attr.values" :key="v">
                                        <label
                                            class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium rounded-lg cursor-pointer hover:bg-emerald-50 dark:hover:bg-gray-700 transition-colors">
                                            <input type="checkbox" :value="v"
                                                   x-model="modalSelectedAttributes[attr.name]"
                                                   @change="modalSelectedAttributes = { ...modalSelectedAttributes }"
                                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 bg-gray-50 dark:bg-gray-900 dark:border-gray-600 h-4 w-4">
                                            <span x-text="v" class="text-gray-700 dark:text-gray-300"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="p-5 flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900/20">
                <template x-if="modalCategories.length === 0">
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                        </svg>
                        <span class="text-sm font-bold">کالایی با این مشخصات یافت نشد</span>
                    </div>
                </template>

                <template x-for="cat in modalCategories" :key="cat.id + '-' + cat.name">
                    <div
                        class="mb-4 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden bg-white dark:bg-gray-800"
                        x-data="{ open: true }">
                        <button @click="open = !open"
                                class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="w-1.5 h-6 bg-emerald-500 rounded-full"></span>
                                <span class="font-black text-sm text-gray-800 dark:text-white"
                                      x-text="cat.name"></span>
                                <span
                                    class="text-[10px] font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md"
                                    x-text="cat.products.length + ' کالا'"></span>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                 :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition.duration.200ms
                             class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 border-t border-gray-100 dark:border-gray-700">
                            <template x-for="prod in cat.products" :key="prod.id">
                                <button @click="selectProductFromModal(prod)"
                                        class="flex flex-col p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-emerald-500 hover:shadow-md bg-white dark:bg-gray-800/80 transition-all text-right group relative overflow-hidden">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <span
                                            class="font-bold text-sm text-gray-800 dark:text-white group-hover:text-emerald-600 transition-colors line-clamp-2"
                                            x-text="prod.name"></span>
                                        <span
                                            class="text-[10px] font-black px-2 py-0.5 rounded-full shrink-0"
                                            :class="prod.stock > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400'"
                                            x-text="prod.stock > 0 ? prod.stock + ' ' + (prod.unit || 'عدد') : 'ناموجود'"></span>
                                    </div>
                                    <div class="mt-auto pt-2 flex items-center justify-between border-t border-gray-50 dark:border-gray-700/50">
                                        <span class="text-xs text-gray-400" x-text="prod.brand_name || 'بدون برند'"></span>
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 tabular-nums"
                                              x-text="formatMoney(prod.price) + ' {{ $currencyLabel }}'"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
    <script>
        function toEnglishNum(v) {
            if (v === '' || v === null || v === undefined) return '';
            const p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            const a = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            return v.toString().replace(/[۰-۹]/g, d => p.indexOf(d)).replace(/[٠-٩]/g, d => a.indexOf(d));
        }

        function toPersianNum(v) {
            if (v === '' || v === null || v === undefined) return '';
            const p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return v.toString().replace(/\d/g, d => p[d]);
        }

        function formatMoney(v) {
            if (v === '' || v === null || v === undefined || isNaN(v)) return '۰';
            let n = Math.round(Number(v)).toString();
            let f = n.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return toPersianNum(f);
        }

        function formatPriceInput(v) {
            if (v === '' || v === null || v === undefined) return '';
            let n = toEnglishNum(v.toString()).replace(/,/g, '').replace(/[^\d]/g, '');
            if (!n || Number(n) === 0) return '';
            return toPersianNum(Number(n).toLocaleString('en-US'));
        }

        function parsePriceInput(v) {
            if (v === '' || v === null || v === undefined) return 0;
            let n = toEnglishNum(v.toString()).replace(/,/g, '').replace(/[^\d.]/g, '');
            return n ? Number(n) : 0;
        }

        function calculateTotals() {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].calculateTotals === 'function') {
                    return el._x_dataStack[0].calculateTotals();
                }
            }
        }

        function onProductInput(index) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].onProductInput === 'function') {
                    return el._x_dataStack[0].onProductInput(index);
                }
            }
        }

        function selectProductInline(index, prod) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].selectProductInline === 'function') {
                    return el._x_dataStack[0].selectProductInline(index, prod);
                }
            }
        }

        function onServiceInput(index) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].onServiceInput === 'function') {
                    return el._x_dataStack[0].onServiceInput(index);
                }
            }
        }

        function selectService(index, s) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].selectService === 'function') {
                    return el._x_dataStack[0].selectService(index, s);
                }
            }
        }

        function filteredServices(index) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].filteredServices === 'function') {
                    return el._x_dataStack[0].filteredServices(index);
                }
            }
            return [];
        }

        function filteredProducts(index) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].filteredProducts === 'function') {
                    return el._x_dataStack[0].filteredProducts(index);
                }
            }
            return [];
        }

        function openProductModal(index) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].openProductModal === 'function') {
                    return el._x_dataStack[0].openProductModal(index);
                }
            }
        }

        function closeProductModal() {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].closeProductModal === 'function') {
                    return el._x_dataStack[0].closeProductModal();
                }
            }
        }

        function selectProductFromModal(prod) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].selectProductFromModal === 'function') {
                    return el._x_dataStack[0].selectProductFromModal(prod);
                }
            }
        }

        function updatePriceForPeriod(index) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].updatePriceForPeriod === 'function') {
                    return el._x_dataStack[0].updatePriceForPeriod(index);
                }
            }
        }

        function getPeriodPrice(item) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].getPeriodPrice === 'function') {
                    return el._x_dataStack[0].getPeriodPrice(item);
                }
            }
            return 0;
        }

        function calculateRowTotal(item) {
            if (window.Alpine && document.querySelector('[x-data]')) {
                const el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack[0] && typeof el._x_dataStack[0].calculateRowTotal === 'function') {
                    return el._x_dataStack[0].calculateRowTotal(item);
                }
            }
            return 0;
        }

        function packageBuilder() {
            const initialItems = @json($package->items);

            return {
                servicesList: @json($services),
                productsList: @json($products ?? []),
                marketAttributesList: @json($marketAttributesForJs ?? []),
                items: initialItems.map(item => {
                    let serviceRaw = item.service || null;
                    let sFields = (serviceRaw ? (serviceRaw.custom_fields || serviceRaw.customFields) : []) || [];
                    let rawQuantities = item.custom_fields_quantities || item.custom_field_quantities || {};
                    let customFieldQuantities = {};
                    if (typeof rawQuantities === 'object' && rawQuantities !== null) {
                        Object.keys(rawQuantities).forEach(k => {
                            let v = rawQuantities[k];
                            if (typeof v === 'object' && v !== null) {
                                customFieldQuantities[k] = {};
                                Object.keys(v).forEach(subK => {
                                    let n = Number(String(v[subK]).replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[^\d.]/g, ''));
                                    customFieldQuantities[k][subK] = isNaN(n) || n <= 0 ? 1 : n;
                                });
                            } else {
                                let n = Number(String(v).replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[^\d.]/g, ''));
                                customFieldQuantities[k] = isNaN(n) || n <= 0 ? 1 : n;
                            }
                        });
                    }
                    let customFieldValues = {};
                    if (item.custom_fields && typeof item.custom_fields === 'object') {
                        customFieldValues = JSON.parse(JSON.stringify(item.custom_fields));
                    }
                    sFields.forEach(f => {
                        if (f.type === 'number') {
                            let rawV = customFieldValues[f.id] !== undefined ? customFieldValues[f.id] : null;
                            let rawQ = customFieldQuantities[f.id] !== undefined ? customFieldQuantities[f.id] : null;
                            let numFromV = rawV !== null ? Number(String(rawV).replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[^\d.]/g, '')) : NaN;
                            let numFromQ = rawQ !== null ? Number(String(rawQ).replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[^\d.]/g, '')) : NaN;

                            if (!isNaN(numFromV) && numFromV > 0) {
                                customFieldValues[f.id] = numFromV;
                                if (f.has_pricing) customFieldQuantities[f.id] = numFromV;
                            } else if (!isNaN(numFromQ) && numFromQ > 0) {
                                customFieldValues[f.id] = numFromQ;
                                if (f.has_pricing) customFieldQuantities[f.id] = numFromQ;
                            }
                        } else if (f.has_pricing) {
                            if (f.type === 'multiselect') {
                                if (Array.isArray(customFieldValues[f.id])) {
                                    if (!customFieldQuantities[f.id] || typeof customFieldQuantities[f.id] !== 'object') {
                                        customFieldQuantities[f.id] = {};
                                    }
                                    customFieldValues[f.id].forEach(opt => {
                                        let optQ = customFieldQuantities[f.id][opt];
                                        let n = Number(String(optQ || '').replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[^\d.]/g, ''));
                                        customFieldQuantities[f.id][opt] = (!isNaN(n) && n > 0) ? n : 1;
                                    });
                                }
                            } else {
                                let curQ = customFieldQuantities[f.id];
                                let n = Number(String(curQ || '').replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[^\d.]/g, ''));
                                if (isNaN(n) || n <= 0) {
                                    customFieldQuantities[f.id] = 1;
                                }
                            }
                        }
                    });

                    let mode = item.mode || (item.product_id ? 'product' : (item.service_id ? 'service' : 'manual'));

                    return {
                        mode: mode,
                        service_id: item.service_id ? String(item.service_id) : '',
                        product_id: item.product_id ? String(item.product_id) : '',
                        product_variant_id: item.product_variant_id ? String(item.product_variant_id) : '',
                        service_raw: serviceRaw,
                        custom_service_name: item.custom_service_name || (serviceRaw ? serviceRaw.name : ''),
                        description: item.description || '',
                        quantity: item.quantity || 1,
                        unit: item.unit || 'عدد',
                        unit_price: item.unit_price || 0,
                        billing_period: item.billing_period || '',
                        service_custom_fields: sFields,
                        custom_field_values: customFieldValues,
                        custom_field_custom_prices: item.custom_fields_prices || {},
                        custom_field_quantities: customFieldQuantities,
                        custom_field_use_default_price: item.custom_fields_use_default_price || {},
                        _customPricesUnlocked: {},
                        _showServiceDropdown: false,
                        _showProductDropdown: false,
                        _hasOpenSelectDropdown: false,
                        _showCustomFields: sFields.length > 0,
                        _priceUnlocked: mode === 'manual',
                    };
                }),
                discountType: @json($package->discount_type ?? 'amount'),
                discountValue: @json($package->discount_value ?? 0),
                periodLabels: {monthly: 'ماهانه', quarterly: 'فصلی', semi_annual: 'شش ماهه', annual: 'سالانه'},
                activeProductModalIndex: null,
                modalProductSearch: '',
                modalStockStatus: 'all',
                modalSelectedBrand: '',
                modalSelectedAttributes: {},

                init() {
                    if (this.items.length === 0) {
                        this.addItem();
                    } else {
                        this.items.forEach((it, idx) => {
                            if (it.service_raw && it.service_raw.billing_type === 'recurring' && it.billing_period) {
                                this.updatePriceForPeriod(idx);
                            }
                            if (it.service_custom_fields && it.service_custom_fields.length > 0) {
                                it._showCustomFields = true;
                            }
                        });
                    }
                    if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
                        window.jalaliDatepicker.startWatch({
                            minDate: 'attr',
                            maxDate: 'attr',
                            time: true,
                            date: true,
                            hasSecond: false,
                            autoHide: true
                        });
                    }
                    document.addEventListener('jdp:change', (e) => {
                        e.target.dispatchEvent(new Event('input', {bubbles: true}));
                    });
                },
                addItem(mode = 'service') {
                    this.items.push({
                        mode: mode,
                        service_id: '',
                        product_id: '',
                        product_variant_id: '',
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
                        custom_field_quantities: {},
                        custom_field_use_default_price: {},
                        _customPricesUnlocked: {},
                        _showServiceDropdown: false,
                        _showProductDropdown: false,
                        _hasOpenSelectDropdown: false,
                        _showCustomFields: false,
                        _priceUnlocked: true,
                    });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                openProductModal(index) {
                    this.activeProductModalIndex = index;
                    this.modalProductSearch = '';
                    this.modalStockStatus = 'all';
                    this.modalSelectedBrand = '';
                    this.modalSelectedAttributes = {};
                },
                closeProductModal() {
                    this.activeProductModalIndex = null;
                },
                selectProductFromModal(prod) {
                    if (this.activeProductModalIndex === null) return;
                    this.selectProductInline(this.activeProductModalIndex, prod);
                    this.closeProductModal();
                },
                onProductInput(index) {
                    const item = this.items[index];
                    item.product_id = '';
                    item.product_variant_id = '';
                    item._showProductDropdown = (item.custom_service_name || '').trim().length >= 2;
                },
                filteredProducts(index) {
                    const item = this.items[index];
                    if (!item || !item.custom_service_name || !item.custom_service_name.trim()) return [];
                    const q = item.custom_service_name.toLowerCase().trim();
                    return (this.productsList || []).filter(p => (p.name || '').toLowerCase().includes(q) || (p.search_text || '').toLowerCase().includes(q)).slice(0, 10);
                },
                selectProductInline(index, prod) {
                    const item = this.items[index];
                    item.mode = 'product';
                    item.product_id = String(prod.master_id || prod.id || '');
                    item.product_variant_id = prod.variant_id ? String(prod.variant_id) : '';
                    item.service_id = '';
                    item.service_raw = null;
                    item.custom_service_name = prod.name;
                    item.unit = prod.unit || 'عدد';
                    item.unit_price = prod.price || 0;
                    item.billing_period = '';
                    item.service_custom_fields = [];
                    item.custom_field_values = {};
                    item.custom_field_custom_prices = {};
                    item.custom_field_quantities = {};
                    item.custom_field_use_default_price = {};
                    item._showProductDropdown = false;
                    item._showCustomFields = false;
                    item._priceUnlocked = false;
                },
                get modalFilterProducts() {
                    let list = this.productsList || [];
                    if (this.modalProductSearch && this.modalProductSearch.trim()) {
                        const q = this.modalProductSearch.toLowerCase().trim();
                        list = list.filter(p => (p.name || '').toLowerCase().includes(q) || (p.search_text || '').toLowerCase().includes(q));
                    }
                    if (this.modalStockStatus === 'in_stock') {
                        list = list.filter(p => (p.stock || 0) > 0);
                    } else if (this.modalStockStatus === 'out_of_stock') {
                        list = list.filter(p => (p.stock || 0) <= 0);
                    }
                    if (this.modalSelectedBrand) {
                        list = list.filter(p => String(p.brand_id) === String(this.modalSelectedBrand));
                    }
                    if (this.modalSelectedAttributes && typeof this.modalSelectedAttributes === 'object') {
                        Object.keys(this.modalSelectedAttributes).forEach(attrName => {
                            const selectedValues = this.modalSelectedAttributes[attrName];
                            if (Array.isArray(selectedValues) && selectedValues.length > 0) {
                                list = list.filter(p => {
                                    if (!p.attributes || typeof p.attributes !== 'object') return false;
                                    const prodVal = p.attributes[attrName];
                                    return prodVal && selectedValues.includes(prodVal);
                                });
                            }
                        });
                    }
                    return list;
                },
                get modalBrands() {
                    const map = new Map();
                    (this.productsList || []).forEach(p => {
                        if (p.brand_id && p.brand_name && !map.has(p.brand_id)) {
                            map.set(p.brand_id, { id: p.brand_id, name: p.brand_name });
                        }
                    });
                    return Array.from(map.values());
                },
                get modalProductAttributes() {
                    return this.marketAttributesList || [];
                },
                get modalCategories() {
                    const groups = {};
                    this.modalFilterProducts.forEach(p => {
                        const catKey = (p.category_id || 0) + '_' + (p.category_name || 'سایر');
                        if (!groups[catKey]) {
                            groups[catKey] = {
                                id: p.category_id || 0,
                                name: p.category_name || 'سایر',
                                products: []
                            };
                        }
                        groups[catKey].products.push(p);
                    });
                    return Object.values(groups);
                },
                onServiceInput(index) {
                    const item = this.items[index];
                    item.service_id = '';
                    item.service_raw = null;
                    item.service_custom_fields = [];
                    item.custom_field_values = {};
                    item.custom_field_custom_prices = {};
                    item.custom_field_quantities = {};
                    item.custom_field_use_default_price = {};
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
                    item.custom_field_quantities = {};
                    item.custom_field_use_default_price = {};
                    item._customPricesUnlocked = {};
                    item._showServiceDropdown = false;
                    item._showCustomFields = item.service_custom_fields.length > 0;
                    item._priceUnlocked = false;
                    this.updatePriceForPeriod(index);
                },
                getFieldOptionsList(f) {
                    if (!f || !f.options) return [];
                    let opts = f.options;
                    if (typeof opts === 'string') {
                        try {
                            opts = JSON.parse(opts);
                        } catch (e) {
                            opts = [];
                        }
                    }
                    if (!Array.isArray(opts)) return [];
                    return opts.map(opt => {
                        if (typeof opt === 'object' && opt !== null) {
                            return {
                                label: String(opt.label || opt.title || opt.name || opt.value || ''),
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
                },
                parseOptions(opts) {
                    return this.getFieldOptionsList({options: opts});
                },
                toggleMultiselect(it, fId, o, c) {
                    if (!Array.isArray(it.custom_field_values[fId])) it.custom_field_values[fId] = [];
                    const a = it.custom_field_values[fId];
                    const i = a.indexOf(o);
                    if (c && i === -1) a.push(o);
                    if (!c && i !== -1) a.splice(i, 1);
                },
                isFieldSelected(f, v) {
                    if (v === undefined || v === null || v === '') return false;
                    if (f.type === 'checkbox') return (v === true || v === '1' || v === 1 || String(v) === 'true');
                    if (f.type === 'multiselect') return Array.isArray(v) && v.length > 0;
                    if (f.type === 'number') {
                        if (f.has_pricing) {
                            let num = Number(this.toEnglishNum(v.toString()).replace(/[^\d.]/g, ''));
                            return !isNaN(num) && num > 0;
                        }
                        return (v !== '' && v !== null && v !== undefined);
                    }
                    return (v !== '' && v !== null && v !== undefined);
                },
                getFieldValueLabel(f, v) {
                    if (!this.isFieldSelected(f, v)) return '';
                    if (f.type === 'checkbox') return 'انتخاب شده';
                    if (f.type === 'multiselect' && Array.isArray(v)) return v.join('، ');
                    if (f.type === 'number' && f.has_pricing) return '-';
                    return v;
                },
                getCustomFieldQuantity(it, f, opt = null) {
                    if (!it) return 1;
                    const fId = (f && f.id !== undefined) ? f.id : f;
                    let fieldObj = (typeof f === 'object' && f !== null) ? f : (it.service_custom_fields || []).find(cf => String(cf.id) === String(fId));
                    let rawFieldQ = it.custom_field_quantities ? (it.custom_field_quantities[fId] !== undefined ? it.custom_field_quantities[fId] : it.custom_field_quantities[String(fId)]) : undefined;
                    if (opt !== null && opt !== undefined) {
                        if (rawFieldQ && typeof rawFieldQ === 'object' && rawFieldQ[opt] !== undefined) {
                            let q = Number(this.toEnglishNum((rawFieldQ[opt] || '').toString()).replace(/[^\d.]/g, ''));
                            return isNaN(q) || q <= 0 ? 1 : q;
                        }
                        if (rawFieldQ !== undefined && typeof rawFieldQ !== 'object') {
                            let q = Number(this.toEnglishNum((rawFieldQ || '').toString()).replace(/[^\d.]/g, ''));
                            return isNaN(q) || q <= 0 ? 1 : q;
                        }
                        let itemQ = parseFloat(it.quantity) || 1;
                        return itemQ <= 0 ? 1 : itemQ;
                    }
                    if (fieldObj && fieldObj.type === 'number' && fieldObj.has_pricing) {
                        if (rawFieldQ !== undefined && typeof rawFieldQ !== 'object' && rawFieldQ !== '') {
                            let q = Number(this.toEnglishNum((rawFieldQ || '').toString()).replace(/[^\d.]/g, ''));
                            if (!isNaN(q) && q > 0) return q;
                        }
                        if (it.custom_field_values && it.custom_field_values[fId] !== undefined && it.custom_field_values[fId] !== '') {
                            let q = Number(this.toEnglishNum((it.custom_field_values[fId] || '').toString()).replace(/[^\d.]/g, ''));
                            return isNaN(q) ? 0 : q;
                        }
                        return 0;
                    }
                    if (rawFieldQ !== undefined && typeof rawFieldQ !== 'object') {
                        let q = Number(this.toEnglishNum((rawFieldQ || '').toString()).replace(/[^\d.]/g, ''));
                        return isNaN(q) || q <= 0 ? 1 : q;
                    }
                    let itemQ = parseFloat(it.quantity) || 1;
                    return itemQ <= 0 ? 1 : itemQ;
                },
                setCustomFieldQuantity(it, f, opt = null, val) {
                    let num = Number(this.toEnglishNum((val !== null && val !== undefined ? val : '').toString()).replace(/[^\d.]/g, ''));
                    if (isNaN(num)) num = 0;
                    const fId = (f && f.id !== undefined) ? f.id : f;
                    let fieldObj = (typeof f === 'object' && f !== null) ? f : (it.service_custom_fields || []).find(cf => String(cf.id) === String(fId));

                    if (!it.custom_field_quantities) it.custom_field_quantities = {};
                    if (opt !== null && opt !== undefined) {
                        if (typeof it.custom_field_quantities[fId] !== 'object' || it.custom_field_quantities[fId] === null) {
                            it.custom_field_quantities[fId] = {};
                        }
                        it.custom_field_quantities[fId][opt] = num;
                        if (it.custom_field_quantities[String(fId)] && String(fId) !== fId) {
                            it.custom_field_quantities[String(fId)][opt] = num;
                        }
                    } else {
                        it.custom_field_quantities[fId] = num;
                        it.custom_field_quantities[String(fId)] = num;
                        if (fieldObj && fieldObj.type === 'number' && fieldObj.has_pricing) {
                            if (!it.custom_field_values) it.custom_field_values = {};
                            it.custom_field_values[fId] = num > 0 ? num : (val === '' ? '' : num);
                        }
                    }
                },
                isCustomPriceUnlocked(it, f, opt = null) {
                    if (!it._customPricesUnlocked) it._customPricesUnlocked = {};
                    const fId = (f && f.id !== undefined) ? f.id : f;
                    if (opt !== null && opt !== undefined) {
                        return !!(typeof it._customPricesUnlocked[fId] === 'object' ? it._customPricesUnlocked[fId]?.[opt] : it._customPricesUnlocked[fId]);
                    }
                    return !!it._customPricesUnlocked[fId];
                },
                toggleCustomPriceUnlock(it, f, opt = null) {
                    const fId = (f && f.id !== undefined) ? f.id : f;
                    if (!it._customPricesUnlocked) it._customPricesUnlocked = {};
                    if (opt !== null && opt !== undefined) {
                        if (typeof it._customPricesUnlocked[fId] !== 'object' || it._customPricesUnlocked[fId] === null) {
                            it._customPricesUnlocked[fId] = {};
                        }
                        it._customPricesUnlocked[fId][opt] = !it._customPricesUnlocked[fId][opt];
                        if (it._customPricesUnlocked[fId][opt]) {
                            if (!it.custom_field_custom_prices) it.custom_field_custom_prices = {};
                            if (typeof it.custom_field_custom_prices[fId] !== 'object' || it.custom_field_custom_prices[fId] === null) {
                                it.custom_field_custom_prices[fId] = {};
                            }
                            it.custom_field_custom_prices[fId][opt] = this.getCustomFieldPrice(it, f, opt);
                        } else {
                            if (it.custom_field_custom_prices && it.custom_field_custom_prices[fId]) {
                                delete it.custom_field_custom_prices[fId][opt];
                            }
                        }
                    } else {
                        it._customPricesUnlocked[fId] = !it._customPricesUnlocked[fId];
                        if (it._customPricesUnlocked[fId]) {
                            if (!it.custom_field_custom_prices) it.custom_field_custom_prices = {};
                            it.custom_field_custom_prices[fId] = this.getCustomFieldPrice(it, f);
                        } else {
                            if (it.custom_field_custom_prices) {
                                delete it.custom_field_custom_prices[fId];
                            }
                        }
                    }
                    if (typeof this.calculateTotals === 'function') {
                        this.calculateTotals();
                    }
                },
                setCustomFieldPrice(it, f, opt = null, val) {
                    let num = this.parsePriceInput(val);
                    const fId = (f && f.id !== undefined) ? f.id : f;
                    if (!it.custom_field_custom_prices) it.custom_field_custom_prices = {};
                    if (opt !== null && opt !== undefined) {
                        if (typeof it.custom_field_custom_prices[fId] !== 'object' || it.custom_field_custom_prices[fId] === null) {
                            it.custom_field_custom_prices[fId] = {};
                        }
                        it.custom_field_custom_prices[fId][opt] = num;
                    } else {
                        it.custom_field_custom_prices[fId] = num;
                    }
                    if (typeof this.calculateTotals === 'function') {
                        this.calculateTotals();
                    }
                },
                removeMultiselectOption(it, fId, opt) {
                    if (Array.isArray(it.custom_field_values[fId])) {
                        const idx = it.custom_field_values[fId].indexOf(opt);
                        if (idx !== -1) {
                            it.custom_field_values[fId].splice(idx, 1);
                        }
                    }
                    if (typeof this.calculateTotals === 'function') {
                        this.calculateTotals();
                    }
                },
                clearFieldValue(it, f) {
                    const fId = (f && f.id !== undefined) ? f.id : f;
                    if (f && f.type === 'checkbox') it.custom_field_values[fId] = false;
                    else if (f && f.type === 'multiselect') it.custom_field_values[fId] = [];
                    else it.custom_field_values[fId] = '';
                    if (typeof this.calculateTotals === 'function') {
                        this.calculateTotals();
                    }
                },
                getCustomFieldPrice(it, f, opt = null) {
                    if (!it || !f) return 0;
                    const fId = (f && f.id !== undefined) ? f.id : f;
                    let fieldObj = (typeof f === 'object' && f !== null) ? f : (it.service_custom_fields || []).find(cf => String(cf.id) === String(fId));
                    if (!fieldObj) return 0;

                    let p = parseFloat(it.unit_price) || 0;
                    let a = Number(fieldObj.pricing_amount) || 0;
                    if (isNaN(a)) a = 0;
                    let defaultFieldPrice = fieldObj.pricing_type === 'percentage' ? p * (a / 100) : a;

                    let useDefault = !!(it.custom_field_use_default_price && (it.custom_field_use_default_price[fId] === true || it.custom_field_use_default_price[fId] === '1' || it.custom_field_use_default_price[fId] === 1));

                    if (opt !== null && opt !== undefined) {
                        if (this.isCustomPriceUnlocked(it, fieldObj, opt)) {
                            if (it.custom_field_custom_prices && it.custom_field_custom_prices[fId] && typeof it.custom_field_custom_prices[fId] === 'object' && it.custom_field_custom_prices[fId][opt] !== undefined && it.custom_field_custom_prices[fId][opt] !== '') {
                                let customVal = Number(it.custom_field_custom_prices[fId][opt]);
                                if (!isNaN(customVal)) return customVal;
                            }
                        }

                        if (useDefault) {
                            return defaultFieldPrice;
                        }

                        const optList = this.getFieldOptionsList(fieldObj);
                        const match = optList.find(o => String(o.label) === String(opt));
                        if (match && match.price !== undefined && match.price !== null && match.price !== '') {
                            let optP = Number(match.price) || 0;
                            return match.pricing_type === 'percentage' ? p * (optP / 100) : optP;
                        }

                        return defaultFieldPrice;
                    }

                    if (this.isCustomPriceUnlocked(it, fieldObj)) {
                        if (it.custom_field_custom_prices && it.custom_field_custom_prices[fId] !== undefined && typeof it.custom_field_custom_prices[fId] !== 'object' && it.custom_field_custom_prices[fId] !== null && it.custom_field_custom_prices[fId] !== '') {
                            let customVal = Number(it.custom_field_custom_prices[fId]);
                            if (!isNaN(customVal)) return customVal;
                        }
                    }

                    if (useDefault) {
                        return defaultFieldPrice;
                    }

                    if (['select', 'radio'].includes(fieldObj.type) && it.custom_field_values?.[fId]) {
                        let val = it.custom_field_values[fId];
                        const optList = this.getFieldOptionsList(fieldObj);
                        const match = optList.find(o => String(o.label) === String(val));
                        if (match && match.price !== undefined && match.price !== null && match.price !== '') {
                            let optP = Number(match.price) || 0;
                            return match.pricing_type === 'percentage' ? p * (optP / 100) : optP;
                        }
                    }

                    return defaultFieldPrice;
                },
                getCustomFieldRowTotal(it, f, opt = null) {
                    if (opt !== null && opt !== undefined) {
                        let optQ = this.getCustomFieldQuantity(it, f, opt);
                        let optP = this.getCustomFieldPrice(it, f, opt);
                        return optP * optQ;
                    }
                    if (f.type === 'multiselect') {
                        const selectedOpts = Array.isArray(it.custom_field_values[f.id]) ? it.custom_field_values[f.id] : [];
                        let total = 0;
                        selectedOpts.forEach(opt => {
                            let optQ = this.getCustomFieldQuantity(it, f, opt);
                            let optP = this.getCustomFieldPrice(it, f, opt);
                            total += optP * optQ;
                        });
                        return total;
                    }
                    let q = this.getCustomFieldQuantity(it, f);
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
                    let n = this.toEnglishNum(val.toString()).replace(/,/g, '').replace(/[^\d.]/g, '');
                    return n ? Number(n) : 0;
                },
                formatPriceInput(val) {
                    if (val === '' || val === null || val === undefined) return '';
                    let n = this.toEnglishNum(val.toString()).replace(/,/g, '').replace(/[^\d]/g, '');
                    if (!n || Number(n) === 0) return '';
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
                            if (!f.has_pricing) return;
                            if (f.type === 'multiselect') {
                                const selectedOpts = Array.isArray(item.custom_field_values[f.id]) ? item.custom_field_values[f.id] : [];
                                selectedOpts.forEach(opt => {
                                    total += this.getCustomFieldRowTotal(item, f, opt);
                                });
                            } else if (this.isFieldSelected(f, item.custom_field_values[f.id])) {
                                total += this.getCustomFieldRowTotal(item, f);
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
                    const nF = f.querySelectorAll('input[name*="[quantity]"], input[name*="[custom_fields_quantities]"], input[name*="[unit_price]"], input[name*="[custom_fields_prices]"]');
                    nF.forEach(i => {
                        i.value = this.toEnglishNum(i.value).replace(/[^\d.]/g, '');
                    });
                }
            };
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('packageBuilder', packageBuilder);
        });

document.addEventListener('click', function (e) {
    if (window.jalaliDatepicker && typeof window.jalaliDatepicker.hide === 'function') {
        const dp = document.querySelector('jdp-container');
        if (!dp) return;
        const isInsideDp = dp === e.target || dp.contains(e.target);
        const isInput = e.target && e.target.closest && e.target.closest('[data-jdp], [data-jdp-only-date], [data-jdp-with-time], [data-jdp-only-time], input[data-jdp]');
        if (!isInsideDp && !isInput) {
            window.jalaliDatepicker.hide();
        }
    }
}, true);
</script>
@endpush
@endsection
