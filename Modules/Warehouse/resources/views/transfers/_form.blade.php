@csrf
@includeIf('partials.jalali-date-picker')

@php
    $searchUrl = route('admin.warehouse.transfers.search-items');
    $initSourceId = (string)old('source_warehouse_id', $defaultSourceId ?? '');
    $initDestId = (string)old('destination_warehouse_id', $defaultDestId ?? '');

    $initialLines = [];
    if (old('items') && is_array(old('items'))) {
        foreach (old('items') as $oldLine) {
            $itemId = $oldLine['item_id'] ?? null;
            $itemModel = $itemId ? \Modules\Warehouse\Entities\WarehouseItem::with('activeBatches')->find($itemId) : null;
            $qtyRaw = str_replace([',', '،', ' '], '', (string)($oldLine['quantity'] ?? 1));
            $srcStock = ($itemModel && $initSourceId)
                ? (int)round((float)$itemModel->activeBatches->where('warehouse_id', (int)$initSourceId)->sum('remaining_quantity'))
                : 0;
            $dstStock = ($itemModel && $initDestId)
                ? (int)round((float)$itemModel->activeBatches->where('warehouse_id', (int)$initDestId)->sum('remaining_quantity'))
                : 0;

            $initialLines[] = [
                'uid' => uniqid('tline_'),
                'item_id' => $itemId ?: '',
                'item_name' => $itemModel?->name ?? '',
                'item_code' => $itemModel?->code ?? '',
                'item_system_code' => $itemModel?->system_code ?? '',
                'unit' => $itemModel?->unit ?? 'عدد',
                'source_stock' => $srcStock,
                'destination_stock' => $dstStock,
                'search_query' => $itemModel ? ($itemModel->code . ' — ' . $itemModel->name) : '',
                'quantity' => $qtyRaw !== '' ? (int)$qtyRaw : 1,
                'notes' => $oldLine['notes'] ?? '',
                'results' => [],
                'dropdown_open' => false,
                'searching' => false,
            ];
        }
    } elseif (!empty($prefillItem)) {
        $initialLines[] = [
            'uid' => uniqid('tline_'),
            'item_id' => $prefillItem['id'],
            'item_name' => $prefillItem['name'],
            'item_code' => $prefillItem['code'],
            'item_system_code' => $prefillItem['system_code'],
            'unit' => $prefillItem['unit'],
            'source_stock' => $prefillItem['source_stock'],
            'destination_stock' => $prefillItem['destination_stock'],
            'search_query' => $prefillItem['code'] . ' — ' . $prefillItem['name'],
            'quantity' => 1,
            'notes' => '',
            'results' => [],
            'dropdown_open' => false,
            'searching' => false,
        ];
    } else {
        $initialLines[] = [
            'uid' => uniqid('tline_'),
            'item_id' => '',
            'item_name' => '',
            'item_code' => '',
            'item_system_code' => '',
            'unit' => 'عدد',
            'source_stock' => 0,
            'destination_stock' => 0,
            'search_query' => '',
            'quantity' => 1,
            'notes' => '',
            'results' => [],
            'dropdown_open' => false,
            'searching' => false,
        ];
    }

    $initRequesterUser = null;
    if (!$canSelectRequester) {
        $initRequesterUser = $lockedRequester;
    } else {
        if (old('requester_user_id')) {
            $oldReqModel = \App\Models\User::find((int)old('requester_user_id'));
            if ($oldReqModel && \Modules\Warehouse\Support\WarehouseUserVisibility::canSeeUser($oldReqModel)) {
                $initRequesterUser = \Modules\Warehouse\Support\WarehouseUserVisibility::formatUserOption($oldReqModel);
            }
        }
        if (!$initRequesterUser && $initDestId && !empty($warehouseManagersMap[$initDestId][0])) {
            $initRequesterUser = $warehouseManagersMap[$initDestId][0];
        }
        if (!$initRequesterUser) {
            $initRequesterUser = $lockedRequester;
        }
    }
@endphp

<div x-data="{
    searchUrl: '{{ $searchUrl }}',
    userSearchUrl: '{{ route('admin.warehouse.users.search') }}',
    sourceWarehouseId: '{{ $initSourceId }}',
    destinationWarehouseId: '{{ $initDestId }}',
    canSelectRequester: {{ $canSelectRequester ? 'true' : 'false' }},
    selectedRequester: @js($initRequesterUser),
    requesterSearchQuery: '',
    requesterSearchResults: [],
    requesterDropdownOpen: false,
    requesterSearching: false,
    requesterSearchTimer: null,
    requesterManuallyEdited: {{ old('requester_user_id') ? 'true' : 'false' }},
    managersMap: {{ Js::from($warehouseManagersMap) }},
    lines: {{ Js::from($initialLines) }},
    searchTimer: null,
    submissionMode: 'deliver',

    getSuggestedManagers() {
        return this.managersMap[this.destinationWarehouseId] || [];
    },

    onDestinationChange() {
        const managers = this.getSuggestedManagers();
        if (this.canSelectRequester && !this.requesterManuallyEdited && managers.length > 0) {
            this.selectedRequester = managers[0];
        }
        this.refreshLineStocks();
    },

    selectRequesterUser(u) {
        if (!this.canSelectRequester) return;
        this.selectedRequester = u;
        this.requesterManuallyEdited = true;
        this.requesterSearchQuery = '';
        this.requesterSearchResults = [];
        this.requesterDropdownOpen = false;
    },

    clearRequesterUser() {
        if (!this.canSelectRequester) return;
        this.selectedRequester = null;
        this.requesterManuallyEdited = true;
    },

    searchRequesterUsersDebounced() {
        if (!this.canSelectRequester) return;
        clearTimeout(this.requesterSearchTimer);
        const q = (this.requesterSearchQuery || '').trim();
        if (!q) {
            this.requesterSearchResults = [];
            this.requesterSearching = false;
            return;
        }
        this.requesterSearching = true;
        this.requesterSearchTimer = setTimeout(async () => {
            try {
                const res = await fetch(`${this.userSearchUrl}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.requesterSearchResults = data.results || [];
                }
            } catch (e) {
                this.requesterSearchResults = [];
            } finally {
                this.requesterSearching = false;
            }
        }, 280);
    },

    onSourceChange() {
        this.refreshLineStocks();
    },

    async refreshLineStocks() {
        for (const line of this.lines) {
            line.results = [];
            if (!line.item_id || !line.item_system_code) continue;
            try {
                const url = this.searchUrl
                    + '?q=' + encodeURIComponent(line.item_system_code)
                    + '&source_warehouse_id=' + encodeURIComponent(this.sourceWarehouseId || '')
                    + '&destination_warehouse_id=' + encodeURIComponent(this.destinationWarehouseId || '');
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                const found = (data.results || []).find(r => String(r.id) === String(line.item_id));
                if (found) {
                    line.source_stock = found.source_stock;
                    line.destination_stock = found.destination_stock;
                }
            } catch (e) {}
        }
    },

    normalizeDigits(str) {
        return String(str || '').replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
            .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
    },

    addLine() {
        this.lines.push({
            uid: 'tline_' + Date.now() + '_' + Math.random().toString(36).slice(2, 6),
            item_id: '',
            item_name: '',
            item_code: '',
            item_system_code: '',
            unit: 'عدد',
            source_stock: 0,
            destination_stock: 0,
            search_query: '',
            quantity: 1,
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
                const url = this.searchUrl
                    + '?q=' + encodeURIComponent(query.trim())
                    + '&source_warehouse_id=' + encodeURIComponent(this.sourceWarehouseId || '')
                    + '&destination_warehouse_id=' + encodeURIComponent(this.destinationWarehouseId || '')
                    + '&limit=15';
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
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
        line.source_stock = item.source_stock || 0;
        line.destination_stock = item.destination_stock || 0;
        line.search_query = item.code + ' — ' + item.name;
        line.dropdown_open = false;
    },

    clearItem(line) {
        line.item_id = '';
        line.item_name = '';
        line.item_code = '';
        line.item_system_code = '';
        line.search_query = '';
        line.unit = 'عدد';
        line.source_stock = 0;
        line.destination_stock = 0;
        this.searchItems(line, '');
    },

    onQuantityInput(line, val) {
        const clean = this.normalizeDigits(val).replace(/\D/g, '');
        line.quantity = clean !== '' ? Math.max(1, parseInt(clean, 10)) : '';
    },

    hasShortage(line) {
        return line.item_id && (parseInt(line.quantity || 0, 10) > parseInt(line.source_stock || 0, 10));
    },

    anyShortage() {
        return this.lines.some(l => this.hasShortage(l));
    },

    getTotalQuantity() {
        return this.lines.reduce((sum, l) => sum + (parseInt(l.quantity || 0, 10) || 0), 0);
    },

    formatNumber(num) {
        return Number(num || 0).toLocaleString('en-US');
    }
}" class="space-y-6 font-sans">

    <input type="hidden" name="submission_mode" :value="submissionMode">

    {{-- Header Info Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/80">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-indigo-600 shrink-0"></span>
                <h2 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                    مشخصات درخواست و حواله انتقال بین انبارها
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 font-sans">
                    <span>حواله:</span>
                    <span class="dir-ltr">{{ $transfer->transfer_number }}</span>
                </span>
                @if($transfer->request_number)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-gray-100 text-slate-700 dark:bg-gray-700/70 dark:text-slate-200 border border-gray-200 dark:border-gray-600 font-sans">
                        <span>درخواست:</span>
                        <span class="dir-ltr">{{ $transfer->request_number }}</span>
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- تاریخ مورد نیاز --}}
            <div>
                <label for="needed_date" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    تاریخ مورد نیاز (زمان تحویل)
                </label>
                <input type="text"
                       name="needed_date"
                       id="needed_date"
                       value="{{ old('needed_date') }}"
                       data-jdp-only-date
                       autocomplete="off"
                       placeholder="مثلاً 1405/09/10"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm dir-ltr text-center placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </div>

            {{-- انبار مبدأ --}}
            <div>
                <label for="source_warehouse_id" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    انبار مبدأ (تحویل‌دهنده) <span class="text-rose-500">*</span>
                </label>
                <select name="source_warehouse_id"
                        id="source_warehouse_id"
                        x-model="sourceWarehouseId"
                        @change="onSourceChange()"
                        required
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }} {{ $w->is_main ? '(انبار مرکزی)' : '(انبارک)' }}</option>
                    @endforeach
                </select>
            </div>

            {{-- انبارک درخواست‌کننده / مقصد --}}
            <div>
                <label for="destination_warehouse_id" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    انبارک درخواست‌کننده (مقصد) <span class="text-rose-500">*</span>
                </label>
                <select name="destination_warehouse_id"
                        id="destination_warehouse_id"
                        x-model="destinationWarehouseId"
                        @change="onDestinationChange()"
                        required
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                    <option value="">انتخاب انبارک مقصد...</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }} {{ $w->is_main ? '(انبار مرکزی)' : '(انبارک)' }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div x-show="sourceWarehouseId && destinationWarehouseId && sourceWarehouseId === destinationWarehouseId"
             x-cloak
             class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-xs font-bold text-rose-700 dark:text-rose-300">
            انبار مبدأ و انبارک مقصد نمی‌توانند یکسان باشند. لطفاً انبارهای متفاوتی انتخاب کنید.
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start pt-1">
            {{-- درخواست‌کننده (انتخابگر پیشرفته کاربر / قفل بر اساس نقش) --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    درخواست‌کننده <span class="text-rose-500">*</span>
                </label>

                <input type="hidden" name="requester_user_id" :value="selectedRequester ? selectedRequester.id : ''">
                <input type="hidden" name="requester_name" :value="selectedRequester ? selectedRequester.name : ''">

                @if(!$canSelectRequester)
                    <div class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-900/70 border border-gray-200 dark:border-gray-700 text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold shrink-0"
                                  x-text="selectedRequester ? selectedRequester.initial : 'ک'"></span>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-900 dark:text-white truncate" x-text="selectedRequester ? selectedRequester.name : ''"></div>
                                <div class="text-[10px] text-slate-600 dark:text-slate-300 truncate dir-ltr text-right"
                                     x-show="selectedRequester && (selectedRequester.mobile || selectedRequester.email)"
                                     x-text="selectedRequester ? (selectedRequester.mobile || selectedRequester.email) : ''"></div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-slate-600 dark:text-slate-300 shrink-0">
                            <svg class="w-3 h-3 text-slate-500 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            قفل‌شده روی کاربر فعلی
                        </span>
                    </div>
                @else
                    {{-- Selected User Chip --}}
                    <div x-show="selectedRequester" class="mb-2" x-cloak>
                        <div class="inline-flex w-full items-center justify-between gap-2 px-3 py-2 rounded-xl bg-indigo-50/70 dark:bg-indigo-500/15 border border-indigo-200/80 dark:border-indigo-500/30 text-xs font-bold text-gray-900 dark:text-white">
                            <div class="flex items-center gap-2 min-w-0">
                                <template x-if="selectedRequester && selectedRequester.avatar">
                                    <img :src="selectedRequester.avatar" :alt="selectedRequester.name" class="w-6 h-6 rounded-full object-cover shrink-0">
                                </template>
                                <template x-if="selectedRequester && !selectedRequester.avatar">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-[11px] font-bold shrink-0"
                                          x-text="selectedRequester.initial"></span>
                                </template>
                                <div class="min-w-0 text-right">
                                    <div class="truncate" x-text="selectedRequester?.name"></div>
                                    <div x-show="selectedRequester?.mobile || selectedRequester?.email"
                                         x-text="selectedRequester?.mobile || selectedRequester?.email"
                                         class="text-[10px] font-normal text-slate-600 dark:text-slate-300 dir-ltr truncate"></div>
                                </div>
                            </div>
                            <button type="button"
                                    @click="clearRequesterUser()"
                                    class="p-1 rounded-lg text-slate-600 hover:text-rose-600 dark:text-slate-300 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-500/15 transition-colors shrink-0"
                                    title="تغییر درخواست‌کننده">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Searchable Users Dropdown --}}
                    <div class="relative" @click.outside="requesterDropdownOpen = false">
                        <input type="text"
                               x-model="requesterSearchQuery"
                               @focus="if (requesterSearchQuery.trim().length > 0) requesterDropdownOpen = true"
                               @input="requesterDropdownOpen = true; searchRequesterUsersDebounced()"
                               placeholder="جستجوی کاربر (نام، موبایل یا ایمیل)..."
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans"
                               autocomplete="off">

                        <div x-show="requesterSearching" class="absolute inset-y-0 left-0 pl-3 flex items-center" x-cloak>
                            <svg class="animate-spin h-4 w-4 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>

                        <div x-show="requesterDropdownOpen && requesterSearchQuery.trim().length > 0"
                             x-cloak
                             class="absolute z-30 mt-1 w-full rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg max-h-56 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-if="!requesterSearching && requesterSearchResults.length === 0">
                                <div class="px-3.5 py-3 text-xs text-slate-600 dark:text-slate-300 text-center">
                                    کاربری با این مشخصات یافت نشد.
                                </div>
                            </template>
                            <template x-for="u in requesterSearchResults" :key="u.id">
                                <button type="button"
                                        @click="selectRequesterUser(u)"
                                        class="w-full text-right px-3.5 py-2.5 hover:bg-indigo-50 dark:hover:bg-indigo-500/15 text-xs transition-colors flex items-center gap-2.5">
                                    <span class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold shrink-0"
                                          x-text="u.initial"></span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 dark:text-white truncate" x-text="u.name"></div>
                                        <div class="text-[10px] text-slate-600 dark:text-slate-300 dir-ltr text-right truncate"
                                             x-text="u.mobile || u.email || ''"></div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="getSuggestedManagers().length > 0" class="flex flex-wrap items-center gap-1.5 mt-2">
                        <span class="text-[11px] text-slate-600 dark:text-slate-300">مسئولین انبارک:</span>
                        <template x-for="mgr in getSuggestedManagers()" :key="mgr.id">
                            <button type="button"
                                    @click="selectRequesterUser(mgr)"
                                    class="px-2 py-0.5 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 transition-colors"
                                    x-text="mgr.name"></button>
                        </template>
                    </div>
                @endif
            </div>

            {{-- توضیحات --}}
            <div class="lg:col-span-2">
                <label for="header_notes" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    توضیحات درخواست / حواله
                </label>
                <input type="text"
                       name="notes"
                       id="header_notes"
                       value="{{ old('notes', $transfer->notes) }}"
                       placeholder="شرح درخواست، بخش مصرف یا یادداشت انبار..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </div>
        </div>
    </div>

    {{-- Line Items Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-teal-600 shrink-0"></span>
                <h2 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                    اقلام درخواستی جهت انتقال
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

        <div class="divide-y divide-gray-200 dark:divide-gray-700/80">
            <template x-for="(line, index) in lines" :key="line.uid">
                <div class="p-4 sm:p-5 space-y-3 hover:bg-gray-50/40 dark:hover:bg-gray-900/20 transition-colors">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-700 text-slate-700 dark:text-slate-200 text-xs font-bold font-sans"
                                  x-text="index + 1"></span>

                            <template x-if="line.item_id">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/30 font-bold dir-ltr font-sans"
                                          x-text="line.item_code"></span>

                                    <span class="px-2 py-0.5 rounded-md font-bold font-sans border"
                                          :class="hasShortage(line)
                                            ? 'bg-rose-50 text-rose-700 border-rose-200/70 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-500/30'
                                            : 'bg-emerald-50 text-emerald-700 border-emerald-200/70 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30'"
                                          x-text="'موجودی مبدأ: ' + formatNumber(line.source_stock) + ' ' + line.unit"></span>

                                    <span class="text-slate-600 dark:text-slate-300 font-bold font-sans"
                                          x-text="'موجودی مقصد: ' + formatNumber(line.destination_stock) + ' ' + line.unit"></span>
                                </div>
                            </template>
                        </div>

                        <button type="button"
                                x-show="lines.length > 1"
                                @click="removeLine(index)"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/15 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            حذف
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                        {{-- Item Search --}}
                        <div class="md:col-span-6 relative" @click.away="line.dropdown_open = false">
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
                                        <div class="text-left shrink-0 space-y-0.5">
                                            <span class="block px-2 py-0.5 rounded-md text-[11px] font-bold font-sans"
                                                  :class="res.source_stock > 0
                                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                                                    : 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'"
                                                  x-text="'مبدأ: ' + formatNumber(res.source_stock) + ' ' + res.unit"></span>
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

                        {{-- توضیحات ردیف --}}
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                توضیحات ردیف
                            </label>
                            <input type="text"
                                   :name="`items[${index}][notes]`"
                                   x-model="line.notes"
                                   placeholder="علت درخواست یا توضیحات تکمیلی..."
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                        </div>
                    </div>

                    <div x-show="hasShortage(line)"
                         x-cloak
                         class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-500/15 border border-amber-200/70 dark:border-amber-500/30 text-xs font-bold text-amber-800 dark:text-amber-300">
                        تعداد درخواستی از موجودی فعلی انبار مبدأ بیشتر است؛ می‌توانید به‌عنوان «درخواست در انتظار تحویل» ثبت کنید.
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer Summary --}}
        <div class="px-5 py-4 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700/80 rounded-b-2xl flex flex-wrap items-center justify-between gap-4 text-xs font-bold">
            <div class="flex items-center gap-5 text-slate-600 dark:text-slate-300">
                <span>تعداد ردیف‌ها: <strong class="text-gray-900 dark:text-white font-sans" x-text="lines.length"></strong></span>
                <span>جمع کل تعداد درخواستی: <strong class="text-indigo-600 dark:text-indigo-300 font-sans" x-text="formatNumber(getTotalQuantity())"></strong></span>
            </div>
        </div>
    </div>

    {{-- Bottom Action Buttons --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <a href="{{ route('admin.warehouse.transfers.index') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all">
            انصراف و بازگشت
        </a>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <button type="submit"
                    @click="submissionMode = 'pending'"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-amber-300 dark:border-amber-500/40 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/15 dark:hover:bg-amber-500/25 text-amber-800 dark:text-amber-300 font-bold text-xs sm:text-sm transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                ثبت درخواست در انتظار تحویل
            </button>

            <button type="submit"
                    @click="submissionMode = 'deliver'"
                    :disabled="anyShortage() || (sourceWarehouseId && destinationWarehouseId && sourceWarehouseId === destinationWarehouseId)"
                    :class="(anyShortage() || (sourceWarehouseId && destinationWarehouseId && sourceWarehouseId === destinationWarehouseId)) ? 'opacity-50 cursor-not-allowed' : ''"
                    class="inline-flex items-center justify-center gap-2 px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                ثبت و انتقال فوری به انبارک
            </button>
        </div>
    </div>
</div>
