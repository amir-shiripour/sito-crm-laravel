@csrf
@includeIf('partials.jalali-date-picker')

@php
    $isEdit = isset($receipt) && $receipt->exists;
    $searchUrl = route('admin.warehouse.purchases.search-items');

    // Prepare initial lines from old() -> $receipt->items -> $prefillItem -> empty row
    $initialLines = [];
    if (old('items') && is_array(old('items'))) {
        foreach (old('items') as $oldLine) {
            $itemId = $oldLine['item_id'] ?? null;
            $itemModel = $itemId ? \Modules\Warehouse\Entities\WarehouseItem::with('activeBatches')->find($itemId) : null;
            $qtyRaw = str_replace([',', '،', ' '], '', (string)($oldLine['quantity'] ?? 1));
            $priceRaw = str_replace([',', '،', ' '], '', (string)($oldLine['unit_price'] ?? 0));
            $initialLines[] = [
                'uid' => uniqid('line_'),
                'item_id' => $itemId ?: '',
                'item_name' => $itemModel?->name ?? ($oldLine['item_name'] ?? ''),
                'item_code' => $itemModel?->code ?? ($oldLine['item_code'] ?? ''),
                'item_system_code' => $itemModel?->system_code ?? '',
                'unit' => $itemModel?->unit ?? 'عدد',
                'current_stock' => $itemModel ? (int)round($itemModel->total_stock) : 0,
                'has_expiry' => (bool)($itemModel?->has_expiry ?? false),
                'search_query' => $itemModel ? ($itemModel->code . ' — ' . $itemModel->name) : '',
                'warehouse_id' => $oldLine['warehouse_id'] ?? '',
                'quantity' => $qtyRaw !== '' ? (int)$qtyRaw : 1,
                'unit_price_raw' => $priceRaw !== '' ? preg_replace('/\D/', '', $priceRaw) : '0',
                'unit_price_display' => '',
                'expiry_date' => $oldLine['expiry_date'] ?? '',
                'notes' => $oldLine['notes'] ?? '',
                'results' => [],
                'dropdown_open' => false,
                'searching' => false,
            ];
        }
    } elseif ($isEdit && $receipt->items->isNotEmpty()) {
        foreach ($receipt->items as $line) {
            $expiryJalali = '';
            if ($line->expiry_date && function_exists('jdate')) {
                $expiryJalali = jdate($line->expiry_date)->format('Y/m/d');
            }
            $initialLines[] = [
                'uid' => uniqid('line_'),
                'item_id' => $line->item_id,
                'item_name' => $line->item?->name ?? '',
                'item_code' => $line->item?->code ?? '',
                'item_system_code' => $line->item?->system_code ?? '',
                'unit' => $line->item?->unit ?? 'عدد',
                'current_stock' => $line->item ? (int)round($line->item->total_stock) : 0,
                'has_expiry' => (bool)($line->item?->has_expiry ?? false),
                'search_query' => $line->item ? ($line->item->code . ' — ' . $line->item->name) : '',
                'warehouse_id' => $line->warehouse_id,
                'quantity' => (int)round((float)$line->quantity),
                'unit_price_raw' => (string)(int)round((float)$line->unit_price),
                'unit_price_display' => '',
                'expiry_date' => $expiryJalali,
                'notes' => $line->notes ?? '',
                'results' => [],
                'dropdown_open' => false,
                'searching' => false,
            ];
        }
    } elseif (!empty($prefillItem)) {
        $initialLines[] = [
            'uid' => uniqid('line_'),
            'item_id' => $prefillItem['id'],
            'item_name' => $prefillItem['name'],
            'item_code' => $prefillItem['code'],
            'item_system_code' => $prefillItem['system_code'],
            'unit' => $prefillItem['unit'],
            'current_stock' => $prefillItem['current_stock'],
            'has_expiry' => $prefillItem['has_expiry'],
            'search_query' => $prefillItem['code'] . ' — ' . $prefillItem['name'],
            'warehouse_id' => $prefillItem['default_warehouse_id'] ?: ($mainWarehouse?->id ?? ''),
            'quantity' => $prefillItem['suggested_quantity'] ?? 1,
            'unit_price_raw' => (string)($prefillItem['last_purchase_price'] ?: ($prefillItem['current_price'] ?: 0)),
            'unit_price_display' => '',
            'expiry_date' => '',
            'notes' => '',
            'results' => [],
            'dropdown_open' => false,
            'searching' => false,
        ];
    } else {
        $initialLines[] = [
            'uid' => uniqid('line_'),
            'item_id' => '',
            'item_name' => '',
            'item_code' => '',
            'item_system_code' => '',
            'unit' => 'عدد',
            'current_stock' => 0,
            'has_expiry' => false,
            'search_query' => '',
            'warehouse_id' => $mainWarehouse?->id ?? '',
            'quantity' => 1,
            'unit_price_raw' => '',
            'unit_price_display' => '',
            'expiry_date' => '',
            'notes' => '',
            'results' => [],
            'dropdown_open' => false,
            'searching' => false,
        ];
    }

    $defaultReceiptWh = old('warehouse_id', $receipt->warehouse_id ?? ($mainWarehouse?->id ?? ''));
    $defaultDateValue = old('purchase_date', $todayJalali ?? '');
@endphp

<div x-data="{
    searchUrl: '{{ $searchUrl }}',
    defaultWarehouseId: '{{ $defaultReceiptWh }}',
    lines: {{ Js::from($initialLines) }},
    searchTimer: null,

    init() {
        this.lines.forEach(line => {
            line.unit_price_display = this.formatWithCommas(line.unit_price_raw);
        });
    },

    normalizeDigits(str) {
        return String(str || '').replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
            .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
    },

    formatWithCommas(val) {
        const clean = this.normalizeDigits(val).replace(/\D/g, '');
        if (!clean) return '';
        return Number(clean).toLocaleString('en-US');
    },

    onDefaultWarehouseChange() {
        this.lines.forEach(line => {
            if (!line.item_id || !line.warehouse_id) {
                line.warehouse_id = this.defaultWarehouseId;
            }
        });
    },

    addLine() {
        this.lines.push({
            uid: 'line_' + Date.now() + '_' + Math.random().toString(36).slice(2, 6),
            item_id: '',
            item_name: '',
            item_code: '',
            item_system_code: '',
            unit: 'عدد',
            current_stock: 0,
            has_expiry: false,
            search_query: '',
            warehouse_id: this.defaultWarehouseId,
            quantity: 1,
            unit_price_raw: '',
            unit_price_display: '',
            expiry_date: '',
            notes: '',
            results: [],
            dropdown_open: false,
            searching: false,
        });
    },

    removeLine(index) {
        if (this.lines.length <= 1) return;
        this.lines.splice(index, 1);
    },

    searchItems(line, query) {
        line.search_query = query;
        if (this.searchTimer) clearTimeout(this.searchTimer);

        if (line.item_id && query.trim() !== (line.item_code + ' — ' + line.item_name)) {
            line.item_id = '';
        }

        line.searching = true;
        line.dropdown_open = true;

        this.searchTimer = setTimeout(async () => {
            try {
                const res = await fetch(this.searchUrl + '?q=' + encodeURIComponent(query.trim()) + '&limit=15', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                line.results = data.results || [];
            } catch (e) {
                line.results = [];
            } finally {
                line.searching = false;
            }
        }, 180);
    },

    openInitialSearch(line) {
        if (line.results && line.results.length > 0) {
            line.dropdown_open = true;
            return;
        }
        this.searchItems(line, '');
    },

    selectItem(line, item) {
        line.item_id = item.id;
        line.item_name = item.name;
        line.item_code = item.code;
        line.item_system_code = item.system_code;
        line.unit = item.unit || 'عدد';
        line.current_stock = item.current_stock || 0;
        line.has_expiry = !!item.has_expiry;
        line.search_query = item.code + ' — ' + item.name;
        line.dropdown_open = false;

        if (!line.warehouse_id) {
            line.warehouse_id = item.default_warehouse_id || this.defaultWarehouseId;
        } else if (item.default_warehouse_id && !this.defaultWarehouseId) {
            line.warehouse_id = item.default_warehouse_id;
        }

        if (!line.unit_price_raw || line.unit_price_raw === '0') {
            const suggested = item.last_purchase_price || item.current_price || 0;
            line.unit_price_raw = suggested > 0 ? String(suggested) : '0';
            line.unit_price_display = this.formatWithCommas(line.unit_price_raw);
        }
    },

    clearItem(line) {
        line.item_id = '';
        line.item_name = '';
        line.item_code = '';
        line.item_system_code = '';
        line.search_query = '';
        line.unit = 'عدد';
        line.current_stock = 0;
        this.searchItems(line, '');
    },

    onPriceInput(line, val) {
        const clean = this.normalizeDigits(val).replace(/\D/g, '');
        line.unit_price_raw = clean;
        line.unit_price_display = this.formatWithCommas(clean);
    },

    onQuantityInput(line, val) {
        const clean = this.normalizeDigits(val).replace(/\D/g, '');
        line.quantity = clean !== '' ? Math.max(1, parseInt(clean, 10)) : '';
    },

    getLineTotal(line) {
        const qty = parseInt(line.quantity || 0, 10) || 0;
        const price = parseInt(line.unit_price_raw || 0, 10) || 0;
        return qty * price;
    },

    getTotalQuantity() {
        return this.lines.reduce((sum, l) => sum + (parseInt(l.quantity || 0, 10) || 0), 0);
    },

    getGrandTotal() {
        return this.lines.reduce((sum, l) => sum + this.getLineTotal(l), 0);
    },

    formatDisplayNumber(num) {
        return Number(num || 0).toLocaleString('en-US');
    }
}" class="space-y-6 font-sans">

    {{-- Header Info Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/80">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-indigo-600 shrink-0"></span>
                <h2 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                    مشخصات فاکتور و رسید ورود به انبار
                </h2>
            </div>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 dir-ltr font-sans">
                {{ $receipt->receipt_number }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- تاریخ خرید --}}
            <div>
                <label for="purchase_date" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    تاریخ خرید <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="purchase_date"
                       id="purchase_date"
                       value="{{ $defaultDateValue }}"
                       data-jdp-only-date
                       required
                       autocomplete="off"
                       placeholder="1405/09/03"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm dir-ltr text-center focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </div>

            {{-- شماره فاکتور --}}
            <div>
                <label for="invoice_number" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    شماره فاکتور خرید
                </label>
                <input type="text"
                       name="invoice_number"
                       id="invoice_number"
                       value="{{ old('invoice_number', $receipt->invoice_number) }}"
                       placeholder="مثلاً 1405-842"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm dir-ltr text-right placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </div>

            {{-- تأمین‌کننده / فروشنده --}}
            <div>
                <label for="supplier_name" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    تأمین‌کننده / شرکت فروشنده
                </label>
                <input type="text"
                       name="supplier_name"
                       id="supplier_name"
                       list="warehouse_suppliers_list"
                       value="{{ old('supplier_name', $receipt->supplier_name) }}"
                       placeholder="نام شرکت یا فروشنده"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                <datalist id="warehouse_suppliers_list">
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup }}">
                    @endforeach
                </datalist>
            </div>

            {{-- انبار مقصد پیش‌فرض --}}
            <div>
                <label for="header_warehouse_id" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    انبار مقصد پیش‌فرض
                </label>
                <select name="warehouse_id"
                        id="header_warehouse_id"
                        x-model="defaultWarehouseId"
                        @change="onDefaultWarehouseChange()"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                    <option value="">بر اساس پیش‌فرض هر کالا</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected((string)$defaultReceiptWh === (string)$w->id)>
                            {{ $w->name }} {{ $w->is_main ? '(انبار مرکزی)' : '(انبارک)' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-center pt-1">
            <div class="lg:col-span-2">
                <label for="header_notes" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    توضیحات رسید
                </label>
                <input type="text"
                       name="notes"
                       id="header_notes"
                       value="{{ old('notes', $receipt->notes) }}"
                       placeholder="شرح خرید، نحوه تسویه یا یادداشت انبار..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </div>

            <div class="lg:pt-5">
                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 cursor-pointer select-none">
                    <input type="checkbox"
                           name="update_current_prices"
                           value="1"
                           {{ old('update_current_prices', $receipt->update_current_prices ?? false) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                        به‌روزرسانی نرخ روز کالاها با فی این فاکتور
                    </span>
                </label>
            </div>
        </div>
    </div>

    {{-- Line Items Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-teal-600 shrink-0"></span>
                <h2 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                    اقلام خرید و جزئیات ورود به انبار
                </h2>
                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 font-sans"
                      x-text="lines.length + ' قلم'"></span>
            </div>

            <button type="button"
                    @click="addLine()"
                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 text-xs font-bold transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                افزودن ردیف کالا
            </button>
        </div>

        {{-- Lines List (No overflow-hidden/overflow-x-auto so autocomplete dropdowns never clip) --}}
        <div class="divide-y divide-gray-200 dark:divide-gray-700/80">
            <template x-for="(line, index) in lines" :key="line.uid">
                <div class="p-4 sm:p-5 space-y-3 hover:bg-gray-50/40 dark:hover:bg-gray-900/20 transition-colors">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-700 text-slate-700 dark:text-slate-200 text-xs font-bold font-sans"
                                  x-text="index + 1"></span>
                            <template x-if="line.item_id">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/30 font-bold dir-ltr font-sans"
                                          x-text="line.item_code"></span>
                                    <span class="text-slate-600 dark:text-slate-300 font-bold"
                                          x-text="'موجودی فعلی: ' + formatDisplayNumber(line.current_stock) + ' ' + line.unit"></span>
                                </div>
                            </template>
                        </div>

                        <button type="button"
                                x-show="lines.length > 1"
                                @click="removeLine(index)"
                                title="حذف ردیف"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/15 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            حذف
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                        {{-- Item Search / Combobox --}}
                        <div class="md:col-span-5 relative" @click.away="line.dropdown_open = false">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                کد کالا یا نام کالا <span class="text-rose-500">*</span>
                            </label>
                            <input type="hidden" :name="`items[${index}][item_id]`" :value="line.item_id" required>

                            <div class="relative">
                                <input type="text"
                                       :value="line.search_query"
                                       @focus="openInitialSearch(line)"
                                       @input="searchItems(line, $event.target.value)"
                                       placeholder="جستجو با کد کالا (SKU)، کد سیستمی یا نام..."
                                       autocomplete="off"
                                       class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">

                                <button type="button"
                                        x-show="line.item_id || line.search_query"
                                        @click="clearItem(line)"
                                        class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-gray-400 hover:text-rose-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Autocomplete Results --}}
                            <div x-show="line.dropdown_open"
                                 x-cloak
                                 class="absolute z-40 mt-1.5 w-full rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xl max-h-64 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/70">
                                <template x-if="line.searching">
                                    <div class="p-3 text-xs text-slate-600 dark:text-slate-300 text-center">در حال جستجو...</div>
                                </template>
                                <template x-if="!line.searching && line.results.length === 0">
                                    <div class="p-3 text-xs text-slate-600 dark:text-slate-300 text-center">کالایی یافت نشد.</div>
                                </template>
                                <template x-for="res in line.results" :key="res.id">
                                    <button type="button"
                                            @click="selectItem(line, res)"
                                            class="w-full text-right px-3.5 py-2.5 hover:bg-indigo-50/80 dark:hover:bg-indigo-500/15 transition-colors flex items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="res.name"></div>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 dir-ltr justify-end font-sans">
                                                <span x-text="res.code"></span>
                                                <span>•</span>
                                                <span x-text="res.system_code"></span>
                                            </div>
                                        </div>
                                        <div class="text-left shrink-0">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold bg-gray-100 dark:bg-gray-700 text-slate-700 dark:text-slate-200 font-sans"
                                                  x-text="formatDisplayNumber(res.current_stock) + ' ' + res.unit"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- تعداد --}}
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                تعداد (<span x-text="line.unit || 'عدد'"></span>) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number"
                                   :name="`items[${index}][quantity]`"
                                   :value="line.quantity"
                                   @input="onQuantityInput(line, $event.target.value)"
                                   step="1"
                                   min="1"
                                   required
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2.5 text-sm text-center font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                        </div>

                        {{-- فی (Unit Price) --}}
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                فی خرید (ریال) <span class="text-rose-500">*</span>
                            </label>
                            <input type="hidden" :name="`items[${index}][unit_price]`" :value="line.unit_price_raw !== '' ? line.unit_price_raw : 0">
                            <input type="text"
                                   inputmode="numeric"
                                   :value="line.unit_price_display"
                                   @input="onPriceInput(line, $event.target.value)"
                                   placeholder="0"
                                   required
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2.5 text-sm dir-ltr text-left font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                        </div>

                        {{-- انبار مقصد --}}
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                انبار مقصد
                            </label>
                            <select :name="`items[${index}][warehouse_id]`"
                                    x-model="line.warehouse_id"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }} {{ $w->is_main ? '(مرکزی)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Second compact row: expiry date, notes, line total --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center pt-1">
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                تاریخ انقضا
                            </label>
                            <input type="text"
                                   :name="`items[${index}][expiry_date]`"
                                   x-model="line.expiry_date"
                                   data-jdp-only-date
                                   @focus="if (window.jalaliDatepicker) window.jalaliDatepicker.show($el)"
                                   autocomplete="off"
                                   placeholder="1407/01/01"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs dir-ltr text-center placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                توضیحات ردیف
                            </label>
                            <input type="text"
                                   :name="`items[${index}][notes]`"
                                   x-model="line.notes"
                                   placeholder="شماره لات یا یادداشت ردیف..."
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                        </div>

                        <div class="md:col-span-3 flex items-center justify-between md:justify-end gap-2 pt-3 md:pt-4">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">جمع ردیف:</span>
                            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-300 font-sans">
                                <span x-text="formatDisplayNumber(getLineTotal(line))"></span>
                                <span class="text-[11px] font-normal">ریال</span>
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer Summary --}}
        <div class="px-5 py-4 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700/80 rounded-b-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-5 text-xs font-bold">
                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                    <span>تعداد ردیف‌ها:</span>
                    <span class="text-gray-900 dark:text-white font-sans" x-text="lines.length"></span>
                </div>
                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                    <span>جمع تعداد اقلام:</span>
                    <span class="text-indigo-600 dark:text-indigo-300 font-sans" x-text="formatDisplayNumber(getTotalQuantity())"></span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">جمع کل فاکتور:</span>
                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 font-sans">
                    <span x-text="formatDisplayNumber(getGrandTotal())"></span>
                    <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between gap-3 pt-1">
        <a href="{{ route('admin.warehouse.purchases.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all">
            انصراف و بازگشت
        </a>

        <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $isEdit ? 'ذخیره تغییرات رسید خرید' : 'ثبت رسید خرید و ورود به انبار' }}
        </button>
    </div>
</div>
