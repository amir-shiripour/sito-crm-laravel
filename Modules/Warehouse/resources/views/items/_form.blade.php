@csrf

@php
    $suggestedBrands = $suggestedBrands ?? [];
    $suggestedTypes = $suggestedTypes ?? [];
    $suggestedSpecs = $suggestedSpecs ?? [];
    $suggestedUnits = $suggestedUnits ?? ['عدد', 'بسته', 'جعبه', 'ست', 'ویال', 'سرنگ', 'تیوب', 'کیلوگرم', 'لیتر', 'متر'];
    $systemCode = $systemCode ?? '';

    $allCategoriesJson = ($categories ?? collect([]))->map(function ($c) {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code ?? '',
            'subs' => $c->children->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code ?? '',
            ])->values(),
        ];
    })->values();

    $initialCatId = old('category_id', $item->category_id ?? '');
    $initialSubId = old('subcategory_id', $item->subcategory_id ?? '');
@endphp

<div x-data="{
    allCategories: {{ Js::from($allCategoriesJson) }},
    selectedCatId: '{{ $initialCatId }}',
    selectedSubId: '{{ $initialSubId }}',
    availableSubs: [],

    brand: '{{ old('brand', $item->brand ?? '') }}',
    brandCode: '{{ old('brand_code', $item->brand_code ?? '') }}',
    itemType: '{{ old('item_type', $item->item_type ?? '') }}',
    itemTypeCode: '{{ old('item_type_code', $item->item_type_code ?? '') }}',
    modelSpec: '{{ old('model_spec', $item->model_spec ?? '') }}',

    code: '{{ old('code', $item->code ?? '') }}',
    name: '{{ old('name', $item->name ?? '') }}',
    codeManuallyEdited: {{ old('code') ? 'true' : ($item->exists ? 'true' : 'false') }},
    nameManuallyEdited: {{ old('name') ? 'true' : ($item->exists ? 'true' : 'false') }},

    // Price formatting helpers (3-digit comma separation)
    currentPriceRaw: '{{ old('current_price', $item->current_price !== null ? (int)$item->current_price : '') }}',
    currentPriceDisplay: '',
    openingPriceRaw: '{{ old('opening_purchase_price', '') }}',
    openingPriceDisplay: '',

    init() {
        this.updateSubs();
        this.formatPrices();
    },

    formatNumberWithCommas(val) {
        if (!val && val !== 0) return '';
        const numStr = String(val).replace(/\D/g, '');
        if (!numStr) return '';
        return Number(numStr).toLocaleString('en-US');
    },

    formatPrices() {
        this.currentPriceDisplay = this.formatNumberWithCommas(this.currentPriceRaw);
        this.openingPriceDisplay = this.formatNumberWithCommas(this.openingPriceRaw);
    },

    onPriceInput(type, event) {
        let val = event.target.value.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
        val = val.replace(/\D/g, '');
        if (type === 'current') {
            this.currentPriceRaw = val;
            this.currentPriceDisplay = this.formatNumberWithCommas(val);
        } else if (type === 'opening') {
            this.openingPriceRaw = val;
            this.openingPriceDisplay = this.formatNumberWithCommas(val);
        }
    },

    updateSubs() {
        if (!this.selectedCatId) {
            this.availableSubs = [];
            return;
        }
        const cat = this.allCategories.find(c => String(c.id) === String(this.selectedCatId));
        this.availableSubs = cat ? cat.subs : [];
    },

    onCategoryChange() {
        this.updateSubs();
        this.selectedSubId = '';
        this.autoGenerate();
    },

    onBrandInput() {
        if (this.brand && !this.brandCode) {
            const m = this.brand.match(/\((.*?)\)/);
            if (m && m[1]) {
                this.brandCode = m[1].trim().toUpperCase();
            }
        }
        this.autoGenerate();
    },

    onItemTypeInput() {
        if (this.itemType && !this.itemTypeCode) {
            const clean = this.itemType.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            if (clean && clean.length <= 10) {
                this.itemTypeCode = clean;
            }
        }
        this.autoGenerate();
    },

    computeSku() {
        let catCode = '';
        let subcatCode = '';

        if (this.selectedCatId) {
            const cat = this.allCategories.find(c => String(c.id) === String(this.selectedCatId));
            if (cat && cat.code) catCode = cat.code.trim();
        }

        if (this.selectedSubId && this.availableSubs) {
            const sub = this.availableSubs.find(s => String(s.id) === String(this.selectedSubId));
            if (sub && sub.code) subcatCode = sub.code.trim();
        }

        const typeCode = (this.itemTypeCode || '').trim();
        const brandCode = (this.brandCode || '').trim();
        const size = (this.modelSpec || '').replace(/\s*x\s*/gi, 'x').trim();

        const parts = [];
        if (catCode) parts.push(catCode);
        if (subcatCode) parts.push(subcatCode);
        if (typeCode) parts.push(typeCode);
        if (brandCode) parts.push(brandCode);
        if (size) parts.push(size);

        return parts.join('-').toUpperCase();
    },

    computeName() {
        let subcatName = '';
        if (this.selectedSubId && this.availableSubs) {
            const sub = this.availableSubs.find(s => String(s.id) === String(this.selectedSubId));
            if (sub && sub.name) subcatName = sub.name.trim();
        } else if (this.selectedCatId) {
            const cat = this.allCategories.find(c => String(c.id) === String(this.selectedCatId));
            if (cat && cat.name) subcatName = cat.name.trim();
        }

        let brandClean = (this.brand || '').trim();
        if (brandClean.includes('(')) {
            brandClean = brandClean.replace(/\(.*?\)/g, '').trim();
        }

        const typeName = (this.itemType || '').trim();
        const size = (this.modelSpec || '').trim();

        const parts = [];
        if (subcatName) parts.push(subcatName);
        if (typeName) parts.push(typeName);
        if (brandClean) parts.push(brandClean);
        if (size) parts.push(size);

        return parts.join(' ');
    },

    autoGenerate() {
        if (!this.codeManuallyEdited || !this.code) {
            this.code = this.computeSku();
        }
        if (!this.nameManuallyEdited || !this.name) {
            this.name = this.computeName();
        }
    },

    forceRegenerate() {
        this.code = this.computeSku();
        this.name = this.computeName();
        this.codeManuallyEdited = false;
        this.nameManuallyEdited = false;
    }
}" class="space-y-6">

    {{-- Card 1: دسته‌بندی و مشخصات فنی کالا --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 sm:p-7 space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100 dark:border-gray-700/80">
            <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                دسته‌بندی و مشخصات کالا
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            {{-- Category --}}
            <div>
                <label for="category_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    دسته اصلی <span class="text-rose-500">*</span>
                </label>
                <select name="category_id"
                        id="category_id"
                        x-model="selectedCatId"
                        @change="onCategoryChange()"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                    <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">انتخاب دسته...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-bold">
                            {{ $cat->name }} {{ $cat->code ? "({$cat->code})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Subcategory --}}
            <div>
                <label for="subcategory_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    زیردسته
                </label>
                <select name="subcategory_id"
                        id="subcategory_id"
                        x-model="selectedSubId"
                        @change="autoGenerate()"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                    <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">انتخاب زیردسته...</option>
                    <template x-for="sub in availableSubs" :key="sub.id">
                        <option :value="sub.id" x-text="sub.name + (sub.code ? ' (' + sub.code + ')' : '')" :selected="String(sub.id) === String(selectedSubId)"></option>
                    </template>
                </select>
            </div>

            {{-- Brand --}}
            <div>
                <label for="brand" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    برند / سازنده
                </label>
                <input type="text"
                       name="brand"
                       id="brand"
                       list="brandsList"
                       x-model="brand"
                       @input="onBrandInput()"
                       placeholder="مثال: بگو"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                <datalist id="brandsList">
                    @foreach($suggestedBrands as $sb)
                        <option value="{{ $sb }}"></option>
                    @endforeach
                </datalist>
            </div>

            {{-- Brand Code --}}
            <div>
                <label for="brand_code" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    کد اختصاری برند
                </label>
                <input type="text"
                       name="brand_code"
                       id="brand_code"
                       x-model="brandCode"
                       @input="autoGenerate()"
                       placeholder="مثال: BEGO"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
            </div>

            {{-- Item Type --}}
            <div>
                <label for="item_type" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    جنس / نوع / تیپ
                </label>
                <input type="text"
                       name="item_type"
                       id="item_type"
                       list="typesList"
                       x-model="itemType"
                       @input="onItemTypeInput()"
                       placeholder="مثال: RS PRO"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                <datalist id="typesList">
                    @foreach($suggestedTypes as $st)
                        <option value="{{ $st }}"></option>
                    @endforeach
                </datalist>
            </div>

            {{-- Item Type Code --}}
            <div>
                <label for="item_type_code" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    کد اختصاری جنس
                </label>
                <input type="text"
                       name="item_type_code"
                       id="item_type_code"
                       x-model="itemTypeCode"
                       @input="autoGenerate()"
                       placeholder="مثال: RSPRO"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
            </div>

            {{-- Model / Size --}}
            <div>
                <label for="model_spec" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    سایز / مدل
                </label>
                <input type="text"
                       name="model_spec"
                       id="model_spec"
                       list="specsList"
                       x-model="modelSpec"
                       @input="autoGenerate()"
                       placeholder="مثال: 3.75x8.5"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
                <datalist id="specsList">
                    @foreach($suggestedSpecs as $ss)
                        <option value="{{ $ss }}"></option>
                    @endforeach
                </datalist>
            </div>

            {{-- Unit --}}
            <div>
                <label for="unit" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    واحد سنجش <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="unit"
                       id="unit"
                       list="unitsList"
                       value="{{ old('unit', $item->unit ?? 'عدد') }}"
                       required
                       placeholder="مثال: عدد یا بسته"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                <datalist id="unitsList">
                    @foreach($suggestedUnits as $su)
                        <option value="{{ $su }}"></option>
                    @endforeach
                </datalist>
            </div>
        </div>
    </div>

    {{-- Card 2: نام و شناسه‌های کالا (تولید خودکار بر اساس مشخصات) --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 sm:p-7 space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/80">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-teal-600"></span>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                    نام و کد کالا
                </h3>
            </div>

            <button type="button"
                    @click="forceRegenerate()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                تولید مجدد بر اساس مشخصات
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            {{-- Full Item Name --}}
            <div class="md:col-span-2 lg:col-span-4">
                <label for="name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    نام کالا <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       x-model="name"
                       @input="nameManuallyEdited = true"
                       placeholder="عنوان کالا بر اساس مشخصات فوق به صورت خودکار پیشنهاد می‌شود"
                       required
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm font-bold placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </div>

            {{-- System Code --}}
            <div>
                <label for="system_code" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    کد سیستمی
                </label>
                <input type="text"
                       name="system_code"
                       id="system_code"
                       value="{{ old('system_code', $item->system_code ?? ($systemCode ?? '')) }}"
                       readonly
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-indigo-600 dark:text-indigo-400 px-3.5 py-2.5 text-sm font-sans font-bold cursor-not-allowed dir-ltr text-left">
            </div>

            {{-- Item SKU --}}
            <div class="lg:col-span-2">
                <label for="code" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    کد کالا (SKU) <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="code"
                       id="code"
                       x-model="code"
                       @input="codeManuallyEdited = true"
                       placeholder="مثال: IMP-FX-RSPRO-BEGO-3.75x8.5"
                       required
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm font-bold placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
            </div>

            {{-- Barcode --}}
            <div>
                <label for="barcode" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    بارکد
                </label>
                <input type="text"
                       name="barcode"
                       id="barcode"
                       value="{{ old('barcode', $item->barcode) }}"
                       placeholder="شناسه یا بارکد کالا"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
            </div>
        </div>
    </div>

    {{-- Card 3: موقعیت انبار و شرایط نگهداری --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 sm:p-7 space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100 dark:border-gray-700/80">
            <span class="w-3 h-3 rounded-full bg-blue-600"></span>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                موقعیت انبار و شرایط نگهداری
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {{-- Default Warehouse --}}
            <div class="lg:col-span-2">
                <label for="default_warehouse_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    انبار پیش‌فرض
                </label>
                @if($warehouses->isEmpty())
                    <div class="p-3 rounded-xl border border-dashed border-amber-300 dark:border-amber-700/80 bg-amber-50/60 dark:bg-amber-950/30 text-xs text-amber-800 dark:text-amber-300 flex items-center justify-between gap-3">
                        <span>هنوز هیچ انبار یا انبارکی در سیستم تعریف نشده است.</span>
                        <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'inventory']) }}"
                           target="_blank"
                           class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold transition-all shadow-xs shrink-0">
                            + تعریف انبار
                        </a>
                    </div>
                @else
                    <select name="default_warehouse_id"
                            id="default_warehouse_id"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                        <option value="">انتخاب انبار پیش‌فرض...</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}"
                                    {{ (string)old('default_warehouse_id', $item->default_warehouse_id) === (string)$w->id ? 'selected' : '' }}>
                                {{ $w->name }} ({{ $w->code }}) {{ $w->is_main ? '— انبار مرکزی' : '— انبارک' }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Physical Location / Shelf --}}
            <div>
                <label for="default_location" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    قفسه / ردیف در انبار
                </label>
                <input type="text"
                       name="default_location"
                       id="default_location"
                       value="{{ old('default_location', $item->default_location) }}"
                       placeholder="مثال: قفسه A، طبقه ۲"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </div>

            {{-- Has Expiry Toggle --}}
            <div class="lg:col-span-3 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-900 dark:text-white">رهگیری تاریخ انقضا</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="has_expiry" value="1" {{ old('has_expiry', $item->has_expiry) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-teal-600"></div>
                </label>
            </div>
        </div>
    </div>

    {{-- Card 4: آستانه‌های موجودی و پارامترهای مالی --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 sm:p-7 space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100 dark:border-gray-700/80">
            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                آستانه‌های موجودی و قیمت‌گذاری
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Min Stock --}}
            <div>
                <label for="min_stock" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    حداقل موجودی
                </label>
                <input type="number"
                       step="1"
                       min="0"
                       name="min_stock"
                       id="min_stock"
                       value="{{ old('min_stock', $item->min_stock !== null ? (int)$item->min_stock : 0) }}"
                       placeholder="0"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
            </div>

            {{-- Reorder Point --}}
            <div>
                <label for="reorder_point" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    نقطه سفارش
                </label>
                <input type="number"
                       step="1"
                       min="0"
                       name="reorder_point"
                       id="reorder_point"
                       value="{{ old('reorder_point', $item->reorder_point !== null ? (int)$item->reorder_point : 0) }}"
                       placeholder="0"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
            </div>

            {{-- Current Market Price --}}
            <div>
                <label for="current_price_input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    قیمت روز (ریال)
                </label>
                <input type="text"
                       id="current_price_input"
                       x-model="currentPriceDisplay"
                       @input="onPriceInput('current', $event)"
                       placeholder="مثال: ۲,۰۰۰,۰۰۰"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
                <input type="hidden" name="current_price" :value="currentPriceRaw">
            </div>
        </div>

        {{-- Initial Opening Stock & Batch (Only on Create) --}}
        @if(!$item->exists)
            <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200">
                    موجودی اول دوره
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label for="opening_stock" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            تعداد موجودی اولیه
                        </label>
                        <input type="number"
                               step="1"
                               min="0"
                               name="opening_stock"
                               id="opening_stock"
                               value="{{ old('opening_stock', 0) }}"
                               placeholder="0"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
                    </div>

                    <div>
                        <label for="opening_purchase_price_input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            قیمت خرید اول دوره (ریال)
                        </label>
                        <input type="text"
                               id="opening_purchase_price_input"
                               x-model="openingPriceDisplay"
                               @input="onPriceInput('opening', $event)"
                               placeholder="مثال: ۱,۵۰۰,۰۰۰"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
                        <input type="hidden" name="opening_purchase_price" :value="openingPriceRaw">
                    </div>

                    <div>
                        <label for="opening_warehouse_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            انبار استقرار
                        </label>
                        @if($warehouses->isEmpty())
                            <div class="p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs text-gray-500 dark:text-gray-400">
                                انبار تعریف نشده است
                            </div>
                        @else
                            <select name="warehouse_id"
                                    id="opening_warehouse_id"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" {{ (string)old('warehouse_id') === (string)$w->id ? 'selected' : ($w->is_main ? 'selected' : '') }}>
                                        {{ $w->name }} {{ $w->is_main ? '— انبار مرکزی' : '— انبارک' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Card 5: توضیحات و وضعیت --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 sm:p-7 space-y-5">
        <div>
            <label for="description" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                توضیحات تکمیلی
            </label>
            <textarea name="description"
                      id="description"
                      rows="3"
                      placeholder="یادداشت‌های اختصاصی، شرایط نگهداری..."
                      class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">{{ old('description', $item->description) }}</textarea>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-900 dark:text-white">وضعیت فعال بودن کالا</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}
                       class="sr-only peer">
                <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
            </label>
        </div>
    </div>

    {{-- Bottom Action Buttons --}}
    <div class="flex items-center justify-between gap-3 pt-2">
        <a href="{{ route('admin.warehouse.items.index') }}"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold transition-all">
            انصراف
        </a>
        <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $item->exists ? 'ذخیره تغییرات' : 'ثبت کالا' }}
        </button>
    </div>

</div>
