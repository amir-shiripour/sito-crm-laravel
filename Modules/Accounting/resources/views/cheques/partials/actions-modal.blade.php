<div x-show="modalOpen" class="relative z-[9999]" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    {{-- Background overlay --}}
    <div x-show="modalOpen"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20">
        <div class="flex min-h-full items-end justify-center text-center sm:items-center">
            {{-- Modal panel --}}
            <div x-show="modalOpen" @click.away="closeModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">

                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2"
                        id="modal-title">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        <span x-text="modalTitle"></span>
                    </h3>
                    <button type="button" @click="closeModal"
                            class="p-1 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Deposit Form --}}
                    <form :action="`/admin/accounting/cheques/${currentCheque?.id}/deposit`" method="POST"
                          x-show="modalAction === 'deposit'" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">واگذاری به حساب
                                بانکی <span class="text-rose-500">*</span></label>
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '',
                                getOptions() {
                                    return (fundAccounts || []).filter(acc => acc.type === 'bank').map(a => ({
                                        id: String(a.id),
                                        name: a.name,
                                        bank_name: a.bank_name || '',
                                        account_number: a.account_number || '',
                                        type_label: 'بانک',
                                        type_badge: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50'
                                    })).sort((a, b) => (a.name || '').localeCompare(b.name || ''));
                                },
                                get filteredOptions() {
                                    const opts = this.getOptions();
                                    if (!this.search.trim()) return opts;
                                    const q = this.search.toLowerCase();
                                    return opts.filter(o =>
                                        o.name.toLowerCase().includes(q) ||
                                        (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                        (o.account_number && String(o.account_number).toLowerCase().includes(q))
                                    );
                                },
                                select(acc) {
                                    this.selectedId = acc ? String(acc.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.getOptions().find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب بانک مقصد...';
                                    return found.name + (found.bank_name ? ' — ' + found.bank_name : '');
                                },
                                formatFa(str) {
                                    if (!str) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }" class="relative" :class="{ 'z-50': open }">
                                <input type="hidden" name="fund_account_id" :value="selectedId" required>

                                <button type="button" @click="open = !open"
                                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start">
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search" placeholder="جستجو نام بانک، شماره حساب..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>

                                    <template x-for="acc in filteredOptions" :key="acc.id">
                                        <div @click="select(acc)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedId) === String(acc.id) }">
                                            <div class="flex flex-col gap-0.5 truncate">
                                                <div class="flex items-center gap-1.5 truncate font-medium">
                                                    <span x-text="acc.name"></span>
                                                    <span x-show="acc.bank_name" class="text-[10px] text-gray-400"
                                                          x-text="'(' + acc.bank_name + ')'"></span>
                                                </div>
                                                <span x-show="acc.account_number" class="text-[10px] text-gray-400"
                                                      x-text="'ش‌ح: ' + formatFa(acc.account_number)"></span>
                                            </div>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                  :class="acc.type_badge"
                                                  x-text="acc.type_label"></span>
                                        </div>
                                    </template>

                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ حسابی پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-500/30 transition-all active:scale-95">
                                ثبت واگذاری
                            </button>
                        </div>
                    </form>

                    {{-- Clear Form --}}
                    <form :action="`/admin/accounting/cheques/${currentCheque?.id}/clear`" method="POST"
                          x-show="modalAction === 'clear'" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">واریز به حساب
                                خزانه‌داری <span class="text-rose-500">*</span></label>
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '',
                                getOptions() {
                                    const types = { bank: 'بانک', cash: 'صندوق', gateway: 'درگاه پرداخت', petty_cash: 'تنخواه' };
                                    const typeBadges = {
                                        bank: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
                                        cash: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
                                        gateway: 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
                                        petty_cash: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50'
                                    };
                                    const fundTypePriority = { bank: 1, cash: 2, gateway: 3, petty_cash: 4 };
                                    return (fundAccounts || []).map(a => ({
                                        id: String(a.id),
                                        name: a.name,
                                        type: a.type,
                                        bank_name: a.bank_name || '',
                                        account_number: a.account_number || '',
                                        type_label: types[a.type] || a.type || 'خزانه',
                                        type_badge: typeBadges[a.type] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                    })).sort((a, b) => {
                                        let pA = fundTypePriority[a.type] || 99;
                                        let pB = fundTypePriority[b.type] || 99;
                                        if (pA !== pB) return pA - pB;
                                        return (a.name || '').localeCompare(b.name || '');
                                    });
                                },
                                get filteredOptions() {
                                    const opts = this.getOptions();
                                    if (!this.search.trim()) return opts;
                                    const q = this.search.toLowerCase();
                                    return opts.filter(o =>
                                        o.name.toLowerCase().includes(q) ||
                                        (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                        (o.account_number && String(o.account_number).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(acc) {
                                    this.selectedId = acc ? String(acc.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.getOptions().find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب حساب واریز...';
                                    return found.name + (found.bank_name ? ' — ' + found.bank_name : '');
                                },
                                formatFa(str) {
                                    if (!str) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }" class="relative" :class="{ 'z-50': open }">
                                <input type="hidden" name="fund_account_id" :value="selectedId" required>

                                <button type="button" @click="open = !open"
                                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start">
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search"
                                               placeholder="جستجو نام حساب، بانک، شماره حساب..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>

                                    <template x-for="acc in filteredOptions" :key="acc.id">
                                        <div @click="select(acc)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedId) === String(acc.id) }">
                                            <div class="flex flex-col gap-0.5 truncate">
                                                <div class="flex items-center gap-1.5 truncate font-medium">
                                                    <span x-text="acc.name"></span>
                                                    <span x-show="acc.bank_name" class="text-[10px] text-gray-400"
                                                          x-text="'(' + acc.bank_name + ')'"></span>
                                                </div>
                                                <span x-show="acc.account_number" class="text-[10px] text-gray-400"
                                                      x-text="'ش‌ح: ' + formatFa(acc.account_number)"></span>
                                            </div>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                  :class="acc.type_badge"
                                                  x-text="acc.type_label"></span>
                                        </div>
                                    </template>

                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ حسابی پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-500/30 transition-all active:scale-95">
                                ثبت وصول
                            </button>
                        </div>
                    </form>

                    {{-- Bounce Form --}}
                    <form :action="`/admin/accounting/cheques/${currentCheque?.id}/bounce`" method="POST"
                          x-show="modalAction === 'bounce'" class="space-y-4">
                        @csrf
                        <div
                            class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-sm leading-relaxed font-bold">
                            آیا از اعلام برگشتی این چک اطمینان دارید؟ این عملیات غیرقابل بازگشت است.
                        </div>
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-md shadow-rose-500/30 transition-all active:scale-95">
                                تایید و اعلام برگشتی
                            </button>
                        </div>
                    </form>

                    {{-- Endorse Form --}}
                    <form :action="`/admin/accounting/cheques/${currentCheque?.id}/endorse`" method="POST"
                          x-show="modalAction === 'endorse'" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">خرج کردن چک
                                بابت <span class="text-rose-500">*</span></label>
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '',
                                getOptions() {
                                    const typeLabels = { asset: 'دارایی', liability: 'بدهی', equity: 'سرمایه', income: 'درآمد', expense: 'هزینه' };
                                    const typeBadges = {
                                        asset: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
                                        liability: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
                                        equity: 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
                                        income: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
                                        expense: 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200 dark:border-rose-800/50'
                                    };
                                    const typePriority = { asset: 1, liability: 2, equity: 3, income: 4, expense: 5 };
                                    return (categories || []).map(c => ({
                                        id: String(c.id),
                                        title: c.title,
                                        account_code: c.account_code || '',
                                        type: c.type,
                                        type_label: typeLabels[c.type] || c.type || '',
                                        type_badge: typeBadges[c.type] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                    })).sort((a, b) => {
                                        let pA = typePriority[a.type] || 99;
                                        let pB = typePriority[b.type] || 99;
                                        if (pA !== pB) return pA - pB;
                                        if (a.account_code || b.account_code) return String(a.account_code || '').localeCompare(String(b.account_code || ''));
                                        return (a.title || '').localeCompare(b.title || '');
                                    });
                                },
                                get filteredOptions() {
                                    const opts = this.getOptions();
                                    if (!this.search.trim()) return opts;
                                    const q = this.search.toLowerCase();
                                    return opts.filter(o =>
                                        o.title.toLowerCase().includes(q) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(cat) {
                                    this.selectedId = cat ? String(cat.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.getOptions().find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل...';
                                    return found.title + (found.type_label ? ' (' + found.type_label + ')' : '');
                                },
                                formatFa(str) {
                                    if (!str) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }" class="relative" :class="{ 'z-50': open }">
                                <input type="hidden" name="debit_category_id" :value="selectedId" required>

                                <button type="button" @click="open = !open"
                                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start">
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search" placeholder="جستجو سرفصل، کد یا ماهیت..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>

                                    <template x-for="cat in filteredOptions" :key="cat.id">
                                        <div @click="select(cat)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedId) === String(cat.id) }">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <span x-show="cat.account_code" class="text-[10px] text-gray-400"
                                                      x-text="formatFa(cat.account_code)"></span>
                                                <span x-text="cat.title" class="truncate"></span>
                                            </div>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                  :class="cat.type_badge"
                                                  x-text="cat.type_label"></span>
                                        </div>
                                    </template>

                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ سرفصلی پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="endorse_description"
                                   class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">توضیحات</label>
                            <input type="text" name="description" id="endorse_description"
                                   placeholder="توضیحات بابت خرج چک..."
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-md shadow-amber-500/30 transition-all active:scale-95">
                                ثبت خرج چک
                            </button>
                        </div>
                    </form>

                    {{-- Return with Cash Form --}}
                    <form :action="`/admin/accounting/cheques/${currentCheque?.id}/return-with-cash`" method="POST"
                          x-show="modalAction === 'return-with-cash'" class="space-y-4">
                        @csrf
                        <template x-if="currentCheque?.service_payment_info">
                            <div
                                class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 text-amber-700 dark:text-amber-400 text-sm leading-relaxed font-bold mb-4">
                                <span class="block mb-1"> توجه:</span>
                                با انجام این کار پرداختی این چک داخل فاکتور <span
                                    x-text="currentCheque.service_payment_info.invoice_number"
                                    class="px-1 bg-amber-100 dark:bg-amber-500/20 rounded"></span> لغو خواهد شد و باید
                                پرداخت دستی ثبت کنید. آیا از ادامه عملیات اطمینان دارید؟
                            </div>
                        </template>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2"
                                   x-text="currentCheque?.type === 'receivable' ? 'دریافت وجه نقد در حساب:' : 'پرداخت وجه نقد از حساب:'"></label>
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '',
                                getOptions() {
                                    const types = { bank: 'بانک', cash: 'صندوق', gateway: 'درگاه پرداخت', petty_cash: 'تنخواه' };
                                    const typeBadges = {
                                        bank: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
                                        cash: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
                                        gateway: 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
                                        petty_cash: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50'
                                    };
                                    const fundTypePriority = { bank: 1, cash: 2, gateway: 3, petty_cash: 4 };
                                    return (fundAccounts || []).filter(acc => acc.type === 'cash' || acc.type === 'bank').map(a => ({
                                        id: String(a.id),
                                        name: a.name,
                                        type: a.type,
                                        bank_name: a.bank_name || '',
                                        account_number: a.account_number || '',
                                        type_label: types[a.type] || a.type || 'خزانه',
                                        type_badge: typeBadges[a.type] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                    })).sort((a, b) => {
                                        let pA = fundTypePriority[a.type] || 99;
                                        let pB = fundTypePriority[b.type] || 99;
                                        if (pA !== pB) return pA - pB;
                                        return (a.name || '').localeCompare(b.name || '');
                                    });
                                },
                                get filteredOptions() {
                                    const opts = this.getOptions();
                                    if (!this.search.trim()) return opts;
                                    const q = this.search.toLowerCase();
                                    return opts.filter(o =>
                                        o.name.toLowerCase().includes(q) ||
                                        (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                        (o.account_number && String(o.account_number).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(acc) {
                                    this.selectedId = acc ? String(acc.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.getOptions().find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب حساب...';
                                    return found.name + (found.bank_name ? ' — ' + found.bank_name : '');
                                },
                                formatFa(str) {
                                    if (!str) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }" class="relative" :class="{ 'z-50': open }">
                                <input type="hidden" name="fund_account_id" :value="selectedId" required>

                                <button type="button" @click="open = !open"
                                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start">
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>


                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search"
                                               placeholder="جستجو نام حساب، بانک، شماره حساب..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>

                                    <template x-for="acc in filteredOptions" :key="acc.id">
                                        <div @click="select(acc)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedId) === String(acc.id) }">
                                            <div class="flex flex-col gap-0.5 truncate">
                                                <div class="flex items-center gap-1.5 truncate font-medium">
                                                    <span x-text="acc.name"></span>
                                                    <span x-show="acc.bank_name" class="text-[10px] text-gray-400"
                                                          x-text="'(' + acc.bank_name + ')'"></span>
                                                </div>
                                                <span x-show="acc.account_number" class="text-[10px] text-gray-400"
                                                      x-text="'ش‌ح: ' + formatFa(acc.account_number)"></span>
                                            </div>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                  :class="acc.type_badge"
                                                  x-text="acc.type_label"></span>
                                        </div>
                                    </template>

                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ حسابی پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm shadow-md shadow-purple-500/30 transition-all active:scale-95"
                                    x-text="currentCheque?.type === 'receivable' ? 'ثبت عودت و دریافت نقد' : 'ثبت عودت و پرداخت نقد'"></button>
                        </div>
                    </form>

                    {{-- Revert Clearance Form --}}
                    <form :action="`/admin/accounting/cheques/${currentCheque?.id}/revert-clearance`" method="POST"
                          x-show="modalAction === 'revert-clearance'" class="space-y-4">
                        @csrf
                        <div
                            class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 text-amber-700 dark:text-amber-400 text-sm leading-relaxed font-bold">
                            آیا از لغو عملیات وصول این چک اطمینان دارید؟ با این کار، سند وصول حذف شده و چک به وضعیت قبلی
                            خود باز می‌گردد.
                        </div>
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-md shadow-amber-500/30 transition-all active:scale-95">
                                تایید و لغو وصول
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
