<div>
    @if ($isOpen)
        @php
            $hasCustomForm = !empty($formSchema) && !empty($formSchema['fields']);
        @endphp
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
                    if (value) {
                        document.body.classList.add('overflow-hidden');
                        $nextTick(() => {
                            if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
                                window.jalaliDatepicker.startWatch({
                                    selector: '[data-jdp-only-date]',
                                    minDate: 'attr',
                                });
                            }
                        });
                    }
                    else { document.body.classList.remove('overflow-hidden'); }
                });
                document.body.classList.add('overflow-hidden');
                $nextTick(() => {
                    if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp-only-date]',
                            minDate: 'attr',
                        });
                    }
                });
             "
             x-on:keydown.escape.window="if(open) { $wire.closeModal(); }"
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/65 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6">
            
            <div class="bg-white dark:bg-gray-800 rounded-3xl {{ $hasCustomForm ? 'max-w-5xl lg:max-w-6xl' : 'max-w-2xl' }} w-full shadow-2xl border border-gray-200/80 dark:border-gray-700/80 relative flex flex-col max-h-[92vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                
                {{-- Top Accent Bar --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-amber-500 via-teal-500 to-indigo-500 shrink-0"></div>

                {{-- Header (Fixed) --}}
                <div class="px-6 sm:px-7 py-4.5 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between shrink-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/50 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0 shadow-2xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                                افزودن مراجع به صف انتظار
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                ثبت مشخصات و اولویت‌بندی مراجع در صف نوبت‌دهی
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body (Scrollable) --}}
                <div class="p-6 sm:p-7 overflow-y-auto space-y-6 flex-1">
                    {{-- Error Message Alert --}}
                    @if ($errorMessage)
                        <div class="p-4 bg-rose-50 dark:bg-rose-900/40 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-800 dark:text-rose-200 text-xs sm:text-sm font-bold flex items-center gap-3 shadow-2xs">
                            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $errorMessage }}</span>
                        </div>
                    @endif

                    {{-- Form Layout: 2 Columns if form present, 1 column if basic --}}
                    <div class="{{ $hasCustomForm ? 'grid grid-cols-1 lg:grid-cols-12 gap-6' : 'space-y-5' }}">
                        
                        {{-- Left / Primary Column: Appointment & Client Core Details --}}
                        <div class="{{ $hasCustomForm ? 'lg:col-span-5 space-y-4.5' : 'space-y-4.5' }}">
                            
                            {{-- Client Selector --}}
                            <div class="bg-gray-50/70 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/70 space-y-2.5">
                                <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>انتخاب {{ config('clients.labels.singular', 'مراجع') }}</span>
                                    </span>
                                    <span class="text-rose-500 font-black">*</span>
                                </label>
                                
                                @if ($selectedClient)
                                    <div class="p-3.5 rounded-xl border border-amber-200/90 bg-amber-50/80 dark:bg-amber-950/40 dark:border-amber-800/70 flex items-center justify-between shadow-2xs">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-amber-500/15 dark:bg-amber-500/25 text-amber-700 dark:text-amber-300 flex items-center justify-center text-sm font-black shrink-0">
                                                {{ mb_substr($selectedClient->full_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="font-black text-gray-900 dark:text-gray-100 text-sm block">{{ $selectedClient->full_name }}</span>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2.5 mt-0.5 dir-ltr text-right font-medium">
                                                    @if($selectedClient->phone)
                                                        <span>{{ $selectedClient->phone }}</span>
                                                    @endif
                                                    @if($selectedClient->case_number)
                                                        <span>• پرونده: {{ $selectedClient->case_number }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @if(!$lockClient)
                                            <button type="button" wire:click="$set('clientId', null)" class="px-2.5 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-bold hover:bg-gray-50 transition shadow-2xs cursor-pointer">
                                                تغییر
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.300ms="clientSearch" placeholder="جستجوی نام، شماره تماس، پرونده یا کدملی..."
                                               class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 shadow-2xs">
                                    </div>
                                    @if (!empty($clients) && count($clients) > 0)
                                        <div class="max-h-44 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800 shadow-md">
                                            <div class="px-3.5 py-1.5 text-xs font-bold text-gray-400 dark:text-gray-500 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/30">
                                                <span>{{ empty($clientSearch) ? '۳ مراجع اخیر' : 'نتایج (' . count($clients) . ' مورد)' }}</span>
                                                <span class="text-[10px] text-amber-600 dark:text-amber-400">کلیک برای انتخاب</span>
                                            </div>
                                            @foreach($clients as $c)
                                                <div wire:click="selectClient({{ $c->id }})" class="p-2.5 hover:bg-amber-50/80 dark:hover:bg-amber-950/40 cursor-pointer flex items-center justify-between transition-colors">
                                                    <div>
                                                        <span class="font-bold text-gray-900 dark:text-white text-xs sm:text-sm">{{ $c->full_name }}</span>
                                                        <div class="text-xs text-gray-400 mt-0.5 dir-ltr text-right">
                                                            {{ $c->phone }} @if($c->case_number) • پرونده: {{ $c->case_number }} @endif
                                                        </div>
                                                    </div>
                                                    <span class="text-xs text-amber-600 dark:text-amber-400 font-bold bg-amber-50 dark:bg-amber-900/30 px-2.5 py-1 rounded-md">انتخاب</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- Service Selector --}}
                            <div class="relative" @click.outside="openService = false">
                                <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        <span>سرویس نوبت</span>
                                    </span>
                                    <template x-if="providerId">
                                        <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-normal">وابسته به ارائه‌دهنده</span>
                                    </template>
                                </label>

                                <div @click="openService = !openService"
                                     class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center justify-between cursor-pointer focus:ring-2 focus:ring-amber-500 shadow-2xs transition">
                                    <span class="truncate" x-text="selectedServiceName"></span>
                                    <div class="flex items-center gap-1 text-gray-400">
                                        <template x-if="serviceId">
                                            <button type="button" @click.stop="selectService(null)" class="hover:text-rose-500 font-bold text-xs p-0.5" title="پاک کردن">✕</button>
                                        </template>
                                        <svg class="w-4 h-4 transition-transform" :class="openService ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>

                                <div x-show="openService" x-transition.opacity class="absolute top-full right-0 left-0 mt-1 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl p-2 max-h-56 overflow-y-auto space-y-1">
                                    <input type="text" x-model="serviceSearch" placeholder="🔍 جستجوی سرویس..." @click.stop
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs sm:text-sm text-gray-900 dark:text-gray-100 mb-1 focus:ring-2 focus:ring-amber-500">

                                    <div @click="selectService(null)"
                                         :class="!serviceId ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                         class="p-2.5 rounded-xl text-xs sm:text-sm cursor-pointer flex items-center justify-between transition-colors">
                                        <span>🌐 صف عمومی (بدون سرویس خاص)</span>
                                        <template x-if="!serviceId"><span class="text-amber-600 dark:text-amber-400 font-bold">✓</span></template>
                                    </div>

                                    <template x-for="s in availableServices" :key="s.id">
                                        <div @click="selectService(s.id)"
                                             :class="serviceId == s.id ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                             class="p-2.5 rounded-xl text-xs sm:text-sm cursor-pointer flex items-center justify-between transition-colors">
                                            <span class="truncate" x-text="s.name"></span>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[11px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400" x-text="s.provider_ids.length + ' ارائه‌دهنده'"></span>
                                                <template x-if="serviceId == s.id"><span class="text-amber-600 dark:text-amber-400 font-bold">✓</span></template>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="availableServices.length === 0">
                                        <div class="p-3 text-center text-xs text-gray-400">سرویسی یافت نشد</div>
                                    </template>
                                </div>
                            </div>

                            {{-- Provider Selector --}}
                            <div class="relative" @click.outside="openProvider = false">
                                <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ config('booking.labels.provider', 'ارائه‌دهنده') }}</span>
                                    </span>
                                    <template x-if="serviceId">
                                        <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-normal">وابسته به سرویس</span>
                                    </template>
                                </label>

                                <div @click="openProvider = !openProvider"
                                     class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center justify-between cursor-pointer focus:ring-2 focus:ring-amber-500 shadow-2xs transition">
                                    <span class="truncate" x-text="selectedProviderName"></span>
                                    <div class="flex items-center gap-1 text-gray-400">
                                        <template x-if="providerId">
                                            <button type="button" @click.stop="selectProvider(null)" class="hover:text-rose-500 font-bold text-xs p-0.5" title="پاک کردن">✕</button>
                                        </template>
                                        <svg class="w-4 h-4 transition-transform" :class="openProvider ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>

                                <div x-show="openProvider" x-transition.opacity class="absolute top-full right-0 left-0 mt-1 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl p-2 max-h-56 overflow-y-auto space-y-1">
                                    <input type="text" x-model="providerSearch" placeholder="🔍 جستجوی ارائه‌دهنده..." @click.stop
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs sm:text-sm text-gray-900 dark:text-gray-100 mb-1 focus:ring-2 focus:ring-amber-500">

                                    <div @click="selectProvider(null)"
                                         :class="!providerId ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                         class="p-2.5 rounded-xl text-xs sm:text-sm cursor-pointer flex items-center justify-between transition-colors">
                                        <span>بدون ترجیح (هر ارائه‌دهنده‌ای)</span>
                                        <template x-if="!providerId"><span class="text-amber-600 dark:text-amber-400 font-bold">✓</span></template>
                                    </div>

                                    <template x-for="p in availableProviders" :key="p.id">
                                        <div @click="selectProvider(p.id)"
                                             :class="providerId == p.id ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                             class="p-2.5 rounded-xl text-xs sm:text-sm cursor-pointer flex items-center justify-between transition-colors">
                                            <span class="truncate" x-text="p.name"></span>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[11px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400" x-text="p.service_ids.length + ' سرویس'"></span>
                                                <template x-if="providerId == p.id"><span class="text-amber-600 dark:text-amber-400 font-bold">✓</span></template>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="availableProviders.length === 0">
                                        <div class="p-3 text-center text-xs text-amber-600 dark:text-amber-400 font-medium">هیچ ارائه‌دهنده‌ای برای این سرویس فعال نیست</div>
                                    </template>
                                </div>
                            </div>

                            {{-- Duration & Preferred Date Row --}}
                            <div class="grid grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>مدت زمان (دقیقه)</span>
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-normal">اختیاری</span>
                                    </label>
                                    <input type="number" min="5" step="5" wire:model="durationMinutes" placeholder="مثلاً ۳۰"
                                           class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-amber-500 shadow-2xs">
                                </div>

                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>تاریخ ترجیحی</span>
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-normal">اختیاری</span>
                                    </label>
                                    <input type="text"
                                           wire:model="preferredDateJalali"
                                           placeholder="۱۴۰۵/۰۶/۰۱"
                                           data-jdp
                                           data-jdp-only-date
                                           @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                           @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                           @change="$wire.set('preferredDateJalali', $el.value)"
                                           autocomplete="off"
                                           class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-amber-500 shadow-2xs">
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>یادداشت / توضیحات صف</span>
                                </label>
                                <textarea wire:model="notes" rows="{{ $hasCustomForm ? '2' : '3' }}" placeholder="علت قرارگیری در صف یا درخواست خاص مراجع..."
                                          class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 shadow-2xs"></textarea>
                            </div>
                        </div>

                        {{-- Right Column: Service Custom Dynamic Form & Dental Chart (when present) --}}
                        @if ($hasCustomForm)
                            <div class="lg:col-span-7">
                                <x-booking::service-dynamic-form
                                    :formSchema="$formSchema"
                                    :formType="$formType"
                                    :formName="$formName"
                                    :formResponses="$formResponses"
                                    modelPrefix="formResponses" />
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer (Fixed & Sticky) --}}
                <div class="px-6 sm:px-7 py-4.5 bg-gray-50/90 dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between shrink-0">
                    <button type="button" wire:click="closeModal" class="px-5 py-2.5 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm font-bold transition shadow-2xs cursor-pointer">
                        انصراف
                    </button>
                    <button type="button" wire:click="save" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-7 py-2.5 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-amber-600/25 transition active:scale-98 disabled:opacity-50 cursor-pointer">
                        <span wire:loading.remove wire:target="save">ثبت در صف انتظار</span>
                        <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            در حال ثبت...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
