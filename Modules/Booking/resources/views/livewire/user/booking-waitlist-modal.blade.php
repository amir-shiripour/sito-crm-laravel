<div>
    @if ($isOpen)
        <div x-data="{
                open: @entangle('isOpen'),
                services: @js($servicesData),
                providers: @js($providersData),
                serviceId: @entangle('serviceId').live,
                providerId: @entangle('providerId').live,
                serviceSearch: '',
                providerSearch: '',
                openService: false,
                openProvider: false,

                get availableServices() {
                    let list = this.services;
                    if (this.providerId) {
                        list = list.filter(s => s.provider_ids.includes(Number(this.providerId)));
                    }
                    if (this.serviceSearch.trim()) {
                        const sTerm = this.serviceSearch.toLowerCase();
                        list = list.filter(s => s.name.toLowerCase().includes(sTerm));
                    }
                    return list;
                },

                get availableProviders() {
                    let list = this.providers;
                    if (this.serviceId) {
                        list = list.filter(p => p.service_ids.includes(Number(this.serviceId)));
                    }
                    if (this.providerSearch.trim()) {
                        const pTerm = this.providerSearch.toLowerCase();
                        list = list.filter(p => p.name.toLowerCase().includes(pTerm));
                    }
                    return list;
                },

                get selectedServiceName() {
                    if (!this.serviceId) return 'صف عمومی (بدون سرویس خاص)';
                    const s = this.services.find(item => item.id == this.serviceId);
                    return s ? s.name : 'انتخاب سرویس...';
                },

                get selectedProviderName() {
                    if (!this.providerId) return 'هر ارائه‌دهنده‌ای (بدون ترجیح)';
                    const p = this.providers.find(item => item.id == this.providerId);
                    return p ? p.name : 'انتخاب ارائه‌دهنده...';
                },

                selectService(id) {
                    this.serviceId = id;
                    this.openService = false;
                    this.serviceSearch = '';
                    if (id && this.providerId) {
                        const s = this.services.find(item => item.id == id);
                        if (s && !s.provider_ids.includes(Number(this.providerId))) {
                            this.providerId = null;
                        }
                    }
                },

                selectProvider(id) {
                    this.providerId = id;
                    this.openProvider = false;
                    this.providerSearch = '';
                    if (id && this.serviceId) {
                        const p = this.providers.find(item => item.id == id);
                        if (p && !p.service_ids.includes(Number(this.serviceId))) {
                            this.serviceId = null;
                        }
                    }
                }
             }"
             x-init="
                $watch('open', value => {
                    if (value) { document.body.classList.add('overflow-hidden'); }
                    else { document.body.classList.remove('overflow-hidden'); }
                });
                document.body.classList.add('overflow-hidden');
             "
             x-on:keydown.escape.window="if(open) { $wire.closeModal(); }"
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full shadow-2xl border border-gray-100 dark:border-gray-700 relative overflow-hidden animate-in fade-in zoom-in-95 duration-200"
                 @click.outside="$wire.closeModal()">
                
                {{-- Top Accent Bar --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-amber-500 via-teal-500 to-emerald-500"></div>

                <div class="p-6 space-y-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="text-amber-500">⏳</span> افزودن مراجع به صف انتظار
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-bold p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Error Message Alert --}}
                    @if ($errorMessage)
                        <div class="p-3 bg-rose-50 dark:bg-rose-900/40 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-800 dark:text-rose-200 text-xs font-bold flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $errorMessage }}</span>
                        </div>
                    @endif

                    <div class="space-y-4 text-xs font-bold">
                        {{-- Client Selector --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1.5 flex items-center justify-between">
                                <span>انتخاب {{ config('clients.labels.singular', 'مراجع') }} <span class="text-rose-500">*</span></span>
                            </label>
                            
                            @if ($selectedClient)
                                <div class="p-3.5 rounded-2xl border border-amber-200/80 bg-amber-50/70 dark:bg-amber-950/30 dark:border-amber-800/60 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-black">
                                            {{ mb_substr($selectedClient->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-black text-gray-900 dark:text-gray-100 text-sm block">{{ $selectedClient->full_name }}</span>
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-2 mt-0.5 dir-ltr text-right">
                                                @if($selectedClient->phone)
                                                    <span>📞 {{ $selectedClient->phone }}</span>
                                                @endif
                                                @if($selectedClient->case_number)
                                                    <span>• پرونده: {{ $selectedClient->case_number }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$lockClient)
                                        <button type="button" wire:click="$set('clientId', null)" class="px-2.5 py-1 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg text-[10px] font-bold hover:bg-gray-50 transition shadow-2xs">
                                            تغییر
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="clientSearch" placeholder="جستجوی نام، شماره تماس، کد ملی یا پرونده..."
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500">
                                </div>
                                @if (!empty($clients) && count($clients) > 0)
                                    <div class="mt-1.5 max-h-40 overflow-y-auto rounded-2xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800 shadow-md">
                                        <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/30">
                                            <span>{{ empty($clientSearch) ? '۳ مراجع اخیر' : 'نتایج جستجو (' . count($clients) . ' مورد)' }}</span>
                                            <span class="text-[9px] text-amber-600 dark:text-amber-400">برای انتخاب کلیک کنید</span>
                                        </div>
                                        @foreach($clients as $c)
                                            <div wire:click="selectClient({{ $c->id }})" class="p-2.5 hover:bg-amber-50/80 dark:hover:bg-amber-950/40 cursor-pointer flex items-center justify-between transition-colors">
                                                <div>
                                                    <span class="font-black text-gray-900 dark:text-white text-xs">{{ $c->full_name }}</span>
                                                    <div class="text-[10px] text-gray-400 mt-0.5 dir-ltr text-right">
                                                        {{ $c->phone }} @if($c->case_number) • پرونده: {{ $c->case_number }} @endif
                                                    </div>
                                                </div>
                                                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded-md">انتخاب</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Service & Provider Row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- انتخاب سرویس با دراپ‌داون پیشرفته Alpine --}}
                            <div class="relative" @click.outside="openService = false">
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                                    <span>🛠️ سرویس نوبت (اختیاری)</span>
                                    <template x-if="providerId">
                                        <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-normal">وابسته به ارائه‌دهنده</span>
                                    </template>
                                </label>

                                <div @click="openService = !openService"
                                     class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs font-semibold text-gray-900 dark:text-gray-100 flex items-center justify-between cursor-pointer focus:ring-2 focus:ring-amber-500 transition">
                                    <span class="truncate" x-text="selectedServiceName"></span>
                                    <div class="flex items-center gap-1 text-gray-400">
                                        <template x-if="serviceId">
                                            <button type="button" @click.stop="selectService(null)" class="hover:text-rose-500 font-bold text-xs p-0.5" title="پاک کردن انتخاب">✕</button>
                                        </template>
                                        <svg class="w-3.5 h-3.5 transition-transform" :class="openService ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>

                                <div x-show="openService" x-transition.opacity class="absolute top-full right-0 left-0 mt-1 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl p-2 max-h-56 overflow-y-auto space-y-1">
                                    <input type="text" x-model="serviceSearch" placeholder="🔍 جستجوی سرویس..." @click.stop
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-1.5 text-xs text-gray-900 dark:text-gray-100 mb-1 focus:ring-2 focus:ring-amber-500">

                                    <div @click="selectService(null)"
                                         :class="!serviceId ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                         class="p-2 rounded-xl text-xs cursor-pointer flex items-center justify-between transition-colors">
                                        <span>🌐 صف عمومی (بدون سرویس خاص)</span>
                                        <template x-if="!serviceId"><span class="text-amber-600 dark:text-amber-400 font-bold">✓</span></template>
                                    </div>

                                    <template x-for="s in availableServices" :key="s.id">
                                        <div @click="selectService(s.id)"
                                             :class="serviceId == s.id ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                             class="p-2 rounded-xl text-xs cursor-pointer flex items-center justify-between transition-colors">
                                            <span class="truncate" x-text="s.name"></span>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400" x-text="s.provider_ids.length + ' ارائه‌دهنده'"></span>
                                                <template x-if="serviceId == s.id"><span class="text-amber-600 dark:text-amber-400 font-bold">✓</span></template>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="availableServices.length === 0">
                                        <div class="p-2.5 text-center text-[11px] text-gray-400">سرویسی متناسب یافت نشد</div>
                                    </template>
                                </div>
                            </div>

                            {{-- انتخاب ارائه‌دهنده با دراپ‌داون پیشرفته Alpine --}}
                            <div class="relative" @click.outside="openProvider = false">
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                                    <span>👨‍⚕️ {{ config('booking.labels.provider', 'ارائه‌دهنده') }} (اختیاری)</span>
                                    <template x-if="serviceId">
                                        <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-normal">وابسته به سرویس</span>
                                    </template>
                                </label>

                                <div @click="openProvider = !openProvider"
                                     class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs font-semibold text-gray-900 dark:text-gray-100 flex items-center justify-between cursor-pointer focus:ring-2 focus:ring-amber-500 transition">
                                    <span class="truncate" x-text="selectedProviderName"></span>
                                    <div class="flex items-center gap-1 text-gray-400">
                                        <template x-if="providerId">
                                            <button type="button" @click.stop="selectProvider(null)" class="hover:text-rose-500 font-bold text-xs p-0.5" title="پاک کردن انتخاب">✕</button>
                                        </template>
                                        <svg class="w-3.5 h-3.5 transition-transform" :class="openProvider ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>

                                <div x-show="openProvider" x-transition.opacity class="absolute top-full right-0 left-0 mt-1 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl p-2 max-h-56 overflow-y-auto space-y-1">
                                    <input type="text" x-model="providerSearch" placeholder="🔍 جستجوی ارائه‌دهنده..." @click.stop
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-1.5 text-xs text-gray-900 dark:text-gray-100 mb-1 focus:ring-2 focus:ring-amber-500">

                                    <div @click="selectProvider(null)"
                                         :class="!providerId ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                         class="p-2 rounded-xl text-xs cursor-pointer flex items-center justify-between transition-colors">
                                        <span>بدون ترجیح (هر ارائه‌دهنده‌ای)</span>
                                        <template x-if="!providerId"><span class="text-amber-600 dark:text-amber-400 font-bold">✓</span></template>
                                    </div>

                                    <template x-for="p in availableProviders" :key="p.id">
                                        <div @click="selectProvider(p.id)"
                                             :class="providerId == p.id ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                             class="p-2 rounded-xl text-xs cursor-pointer flex items-center justify-between transition-colors">
                                            <span class="truncate" x-text="p.name"></span>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400" x-text="p.service_ids.length + ' سرویس'"></span>
                                                <template x-if="providerId == p.id"><span class="text-amber-600 dark:text-amber-400 font-bold">✓</span></template>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="availableProviders.length === 0">
                                        <div class="p-2.5 text-center text-[11px] text-amber-600 dark:text-amber-400 font-medium">هیچ ارائه‌دهنده‌ای برای این سرویس فعال نیست</div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Preferred Date --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">📅 تاریخ ترجیحی (اختیاری)</label>
                            <input type="text" wire:model="preferredDateJalali" placeholder="مثال: ۱۴۰۵/۰۶/۰۱" data-jdp-only-date
                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs font-bold text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-amber-500">
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 block mt-1">در صورت خالی بودن، اولین وقت خالی سیستم در نظر گرفته می‌شود.</span>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">📝 توضیحات و یادداشت</label>
                            <textarea wire:model="notes" rows="2" placeholder="علت قرارگیری در صف، ترجیحات زمانی یا درخواست خاص..."
                                      class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500"></textarea>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="closeModal" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 transition cursor-pointer">
                            انصراف
                        </button>
                        <button type="button" wire:click="save" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md shadow-amber-600/20 transition active:scale-95 disabled:opacity-50 cursor-pointer">
                            <span wire:loading.remove wire:target="save">ثبت در صف انتظار</span>
                            <span wire:loading wire:target="save" class="inline-flex items-center gap-1">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                در حال ثبت...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
