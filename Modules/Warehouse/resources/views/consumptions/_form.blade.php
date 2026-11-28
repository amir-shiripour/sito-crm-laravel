@csrf
@includeIf('partials.jalali-date-picker')

@php
    $initialLines = [];
    if (old('items') && is_array(old('items'))) {
        foreach (old('items') as $oldLine) {
            $itemId = $oldLine['item_id'] ?? null;
            $itemModel = $itemId ? \Modules\Warehouse\Entities\WarehouseItem::with('activeBatches')->find($itemId) : null;
            $perWh = [];
            if ($itemModel) {
                foreach ($itemModel->activeBatches->groupBy('warehouse_id') as $wId => $grp) {
                    $perWh[(int)$wId] = (float)$grp->sum('remaining_quantity');
                }
            }
            $initialLines[] = [
                'uid' => uniqid('c_line_'),
                'item_id' => $itemId ?: '',
                'item_name' => $itemModel?->name ?? '',
                'item_code' => $itemModel?->code ?? '',
                'unit' => $itemModel?->unit ?? 'عدد',
                'warehouse_id' => $oldLine['warehouse_id'] ?? $defaultWarehouseId,
                'quantity' => $oldLine['quantity'] ?? 1,
                'notes' => $oldLine['notes'] ?? '',
                'per_warehouse' => $perWh,
                'search_query' => $itemModel ? ($itemModel->code . ' — ' . $itemModel->name) : '',
                'results' => [],
                'dropdown_open' => false,
                'searching' => false,
            ];
        }
    } elseif (!empty($prefillItem)) {
        $initialLines[] = [
            'uid' => uniqid('c_line_'),
            'item_id' => $prefillItem['id'],
            'item_name' => $prefillItem['name'],
            'item_code' => $prefillItem['code'],
            'unit' => $prefillItem['unit'],
            'warehouse_id' => $defaultWarehouseId,
            'quantity' => 1,
            'notes' => '',
            'per_warehouse' => $prefillItem['per_warehouse'] ?? [],
            'search_query' => $prefillItem['code'] . ' — ' . $prefillItem['name'],
            'results' => [],
            'dropdown_open' => false,
            'searching' => false,
        ];
    } else {
        $initialLines[] = [
            'uid' => uniqid('c_line_'),
            'item_id' => '',
            'item_name' => '',
            'item_code' => '',
            'unit' => 'عدد',
            'warehouse_id' => $defaultWarehouseId,
            'quantity' => 1,
            'notes' => '',
            'per_warehouse' => [],
            'search_query' => '',
            'results' => [],
            'dropdown_open' => false,
            'searching' => false,
        ];
    }
@endphp

<div class="space-y-6 font-sans"
     x-data="consumptionFormManager({
         defaultWarehouseId: '{{ old('source_warehouse_id', $defaultWarehouseId) }}',
         issueType: '{{ old('issue_type', 'patient_consumption') }}',
        clientId: '{{ old('client_id', $prefillClient['id'] ?? '') }}',
        patientName: @js(old('patient_name', $prefillClient['full_name'] ?? '')),
        caseNumber: @js(old('patient_case_number', $prefillClient['case_number'] ?? '')),
        clientSearchUrl: '{{ route('admin.warehouse.consumptions.search-clients') }}',
        itemSearchUrl: '{{ route('admin.warehouse.consumptions.search-items') }}',
        userSearchUrl: '{{ route('admin.warehouse.users.search') }}',
        consumerUserId: '{{ old('consumer_user_id', '') }}',
        consumerName: @js(old('consumer_name', '')),
        consumerResults: [],
        consumerDropdown: false,
        initialLines: @js($initialLines)
     })">

    {{-- Header Details Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                مشخصات سند خروج و پرونده مصرف
            </h2>
            <div class="text-[11px] font-bold text-slate-600 dark:text-slate-300">
                شماره سند، ثبت‌کننده و زمان ثبت به‌صورت سیستمی پس از ذخیره تولید می‌شود.
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    نوع خروج / مصرف <span class="text-rose-500">*</span>
                </label>
                <select name="issue_type"
                        x-model="issueType"
                        required
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="patient_consumption">مصرف بیمار / پرونده</option>
                    <option value="department_consumption">مصرف داخلی کلینیک / بخش</option>
                    <option value="waste_expired">ضایعات / منقضی / آسیب‌دیده</option>
                    <option value="other">سایر خروجی‌ها</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    انبار / انبارک مبدأ پیش‌فرض <span class="text-rose-500">*</span>
                </label>
                <select name="source_warehouse_id"
                        x-model="defaultWarehouseId"
                        @change="syncDefaultWarehouse($event.target.value)"
                        required
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">
                            {{ $w->name }} ({{ $w->is_main ? 'انبار مرکزی' : 'انبارک فرعی' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    تاریخ مصرف / خروج <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="issue_date"
                       value="{{ old('issue_date', $todayJalali) }}"
                       data-jdp
                       required
                       placeholder="مثلاً 1405/09/05"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dir-ltr text-right font-sans">
            </div>

            <div class="relative">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    تحویل‌گیرنده / پزشک مصرف‌کننده
                </label>
                <input type="hidden" name="consumer_user_id" :value="consumerUserId">
                <input type="text"
                       name="consumer_name"
                       x-model="consumerName"
                       @input.debounce.280ms="searchConsumerUsers()"
                       @focus="if (consumerResults.length) consumerDropdown = true"
                       placeholder="جستجوی کاربر یا تایپ نام پزشک / بخش..."
                       autocomplete="off"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">

                <div x-show="consumerDropdown && consumerResults.length > 0"
                     @click.outside="consumerDropdown = false"
                     style="display: none;"
                     class="absolute z-30 mt-1 w-full rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg max-h-52 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="u in consumerResults" :key="u.id">
                        <button type="button"
                                @click="selectConsumerUser(u)"
                                class="w-full text-right px-3 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-500/15 text-xs transition-colors block">
                            <div class="font-bold text-gray-900 dark:text-white" x-text="u.name"></div>
                            <div class="text-[10px] text-slate-600 dark:text-slate-300 dir-ltr text-right" x-text="u.mobile || u.email || ''"></div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Patient CRM Lookup + Manual Fields --}}
        <div x-show="issueType === 'patient_consumption'"
             class="pt-4 border-t border-gray-100 dark:border-gray-700/60 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="relative">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    جستجوی بیمار از پرونده‌های CRM (اختیاری)
                </label>
                <input type="hidden" name="client_id" :value="clientId">
                <input type="text"
                       x-model="clientQuery"
                       @input.debounce.300ms="searchClients()"
                       @focus="if (clientResults.length) clientDropdown = true"
                       placeholder="نام، شماره پرونده، کد ملی یا موبایل..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">

                <div x-show="clientDropdown && clientResults.length > 0"
                     @click.outside="clientDropdown = false"
                     style="display: none;"
                     class="absolute z-30 mt-1 w-full rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg max-h-56 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="c in clientResults" :key="c.id">
                        <button type="button"
                                @click="selectClient(c)"
                                class="w-full text-right px-3.5 py-2.5 hover:bg-indigo-50 dark:hover:bg-indigo-500/15 text-xs transition-colors block">
                            <div class="font-bold text-gray-900 dark:text-white" x-text="c.full_name"></div>
                            <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5" x-text="c.label"></div>
                        </button>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    نام بیمار
                </label>
                <input type="text"
                       name="patient_name"
                       x-model="patientName"
                       placeholder="نام و نام خانوادگی بیمار"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    شماره پرونده
                </label>
                <div class="flex items-center gap-2">
                    <input type="text"
                           name="patient_case_number"
                           x-model="caseNumber"
                           placeholder="مثلاً 1405-412"
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <button type="button"
                            x-show="clientId"
                            @click="clearClient()"
                            class="px-2.5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-slate-700 dark:text-slate-200 text-xs font-bold shrink-0">
                        پاک
                    </button>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                توضیحات کلی سند
            </label>
            <input type="text"
                   name="notes"
                   value="{{ old('notes') }}"
                   placeholder="شرح درمان، بخش یا توضیحات خروج کالا..."
                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
        </div>
    </div>

    {{-- Multi-Item Lines Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between gap-2 pb-3 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                اقلام خروجی / مصرفی
            </h2>
            <button type="button"
                    @click="addLine()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 text-xs font-bold transition-colors">
                + افزودن ردیف کالا
            </button>
        </div>

        <div class="space-y-3">
            <template x-for="(line, index) in lines" :key="line.uid">
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 p-4 grid grid-cols-1 lg:grid-cols-12 gap-3 items-start">
                    <input type="hidden" :name="`items[${index}][item_id]`" :value="line.item_id">

                    {{-- Item Autocomplete --}}
                    <div class="lg:col-span-5 relative">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                            انتخاب کالا (کد یا نام) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               x-model="line.search_query"
                               @input.debounce.250ms="searchItems(line)"
                               @focus="if (line.results.length) line.dropdown_open = true"
                               placeholder="جستجوی نام یا کد کالا..."
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">

                        <div x-show="line.dropdown_open && line.results.length > 0"
                             @click.outside="line.dropdown_open = false"
                             style="display: none;"
                             class="absolute z-30 mt-1 w-full rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="res in line.results" :key="res.id">
                                <button type="button"
                                        @click="selectItem(line, res)"
                                        class="w-full text-right px-3 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-500/15 text-xs transition-colors flex items-center justify-between gap-2">
                                    <div>
                                        <span class="font-bold text-indigo-600 dark:text-indigo-300" x-text="res.code"></span>
                                        <span class="text-gray-900 dark:text-white font-bold mx-1">—</span>
                                        <span class="text-gray-900 dark:text-white" x-text="res.name"></span>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300 shrink-0"
                                          x-text="`موجودی انبار: ${getStockInWarehouse(res.per_warehouse, line.warehouse_id)} ${res.unit}`"></span>
                                </button>
                            </template>
                        </div>

                        <div x-show="line.item_id" class="mt-1 text-[11px] font-bold"
                             :class="getLineAvailable(line) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                            موجودی قابل برداشت از این انبار:
                            <span x-text="getLineAvailable(line)"></span>
                            <span x-text="line.unit"></span>
                        </div>
                    </div>

                    {{-- Source Warehouse for Line (Central or Any Sub-warehouse) --}}
                    <div class="lg:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                            انبار / انبارک مبدأ <span class="text-rose-500">*</span>
                        </label>
                        <select :name="`items[${index}][warehouse_id]`"
                                x-model="line.warehouse_id"
                                required
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">
                                    {{ $w->name }} ({{ $w->is_main ? 'مرکزی' : 'انبارک' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Quantity --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                            تعداد مصرفی (<span x-text="line.unit"></span>) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number"
                               step="any"
                               min="0.01"
                               :name="`items[${index}][quantity]`"
                               x-model="line.quantity"
                               required
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    </div>

                    {{-- Line Notes & Remove --}}
                    <div class="lg:col-span-2 flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                شرح ردیف
                            </label>
                            <input type="text"
                                   :name="`items[${index}][notes]`"
                                   x-model="line.notes"
                                   placeholder="اختیاری..."
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-xs font-sans">
                        </div>
                        <button type="button"
                                x-show="lines.length > 1"
                                @click="removeLine(index)"
                                title="حذف ردیف"
                                class="p-2 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/25 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/60">
            <a href="{{ route('admin.warehouse.consumptions.index') }}"
               class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                انصراف
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                ثبت نهایی و کسر موجودی
            </button>
        </div>
    </div>
</div>

<script>
function consumptionFormManager(config) {
    return {
        defaultWarehouseId: config.defaultWarehouseId,
        issueType: config.issueType || 'patient_consumption',
        clientId: config.clientId || '',
        patientName: config.patientName || '',
        caseNumber: config.caseNumber || '',
        clientQuery: '',
        clientResults: [],
        clientDropdown: false,
        clientSearchUrl: config.clientSearchUrl,
        itemSearchUrl: config.itemSearchUrl,
        userSearchUrl: config.userSearchUrl,
        consumerUserId: config.consumerUserId || '',
        consumerName: config.consumerName || '',
        consumerResults: [],
        consumerDropdown: false,
        lines: config.initialLines || [],

        async searchConsumerUsers() {
            this.consumerUserId = '';
            const q = (this.consumerName || '').trim();
            if (q.length < 1) {
                this.consumerResults = [];
                this.consumerDropdown = false;
                return;
            }
            try {
                const res = await fetch(`${this.userSearchUrl}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.consumerResults = data.results || [];
                this.consumerDropdown = true;
            } catch (e) {}
        },

        selectConsumerUser(u) {
            this.consumerUserId = u.id;
            this.consumerName = u.name;
            this.consumerDropdown = false;
        },

        syncDefaultWarehouse(newId) {
            this.lines.forEach(l => {
                if (!l.item_id) {
                    l.warehouse_id = newId;
                }
            });
        },

        addLine() {
            this.lines.push({
                uid: 'c_' + Date.now() + '_' + Math.random().toString(36).slice(2, 6),
                item_id: '',
                item_name: '',
                item_code: '',
                unit: 'عدد',
                warehouse_id: this.defaultWarehouseId,
                quantity: 1,
                notes: '',
                per_warehouse: {},
                search_query: '',
                results: [],
                dropdown_open: false,
                searching: false,
            });
        },

        removeLine(idx) {
            if (this.lines.length > 1) {
                this.lines.splice(idx, 1);
            }
        },

        async searchClients() {
            const q = (this.clientQuery || '').trim();
            if (q.length < 1) {
                this.clientResults = [];
                this.clientDropdown = false;
                return;
            }
            try {
                const res = await fetch(`${this.clientSearchUrl}?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                this.clientResults = data.clients || [];
                this.clientDropdown = true;
            } catch (e) {}
        },

        selectClient(c) {
            this.clientId = c.id;
            this.patientName = c.full_name || '';
            this.caseNumber = c.case_number || '';
            this.clientQuery = c.label || c.full_name;
            this.clientDropdown = false;
        },

        clearClient() {
            this.clientId = '';
            this.clientQuery = '';
        },

        async searchItems(line) {
            const q = (line.search_query || '').trim();
            if (q.length < 1) {
                line.results = [];
                line.dropdown_open = false;
                return;
            }
            line.searching = true;
            try {
                const url = `${this.itemSearchUrl}?q=${encodeURIComponent(q)}&warehouse_id=${encodeURIComponent(line.warehouse_id || '')}`;
                const res = await fetch(url);
                const data = await res.json();
                line.results = data.items || [];
                line.dropdown_open = true;
            } catch (e) {
            } finally {
                line.searching = false;
            }
        },

        selectItem(line, item) {
            line.item_id = item.id;
            line.item_name = item.name;
            line.item_code = item.code;
            line.unit = item.unit || 'عدد';
            line.per_warehouse = item.per_warehouse || {};
            line.search_query = `${item.code} — ${item.name}`;
            line.dropdown_open = false;
        },

        getStockInWarehouse(perWarehouse, whId) {
            if (!perWarehouse || !whId) return 0;
            return perWarehouse[String(whId)] ?? perWarehouse[Number(whId)] ?? 0;
        },

        getLineAvailable(line) {
            return this.getStockInWarehouse(line.per_warehouse, line.warehouse_id);
        }
    };
}
</script>
