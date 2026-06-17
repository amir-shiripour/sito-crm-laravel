<div class="space-y-6">
    <form wire:submit.prevent="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-right" dir="rtl">

        {{-- Left Section: Items, Customer Selection, Dynamic Metadata --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Client Selection Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                @if($clientId)
                    @php
                        $selectedClient = \Modules\Clients\Entities\Client::find($clientId);
                    @endphp
                    @if($selectedClient)
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-5 bg-indigo-50/20 dark:bg-indigo-950/10 rounded-2xl border border-indigo-200/60 dark:border-indigo-900/40 shadow-inner">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="font-black text-gray-900 dark:text-white text-base">{{ $selectedClient->full_name }}</h4>
                                        <span class="px-2.5 py-0.5 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-[10px] font-extrabold">مشتری انتخاب شده</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                                        @if($selectedClient->phone)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                                موبایل: {{ $selectedClient->phone }}
                                            </span>
                                        @endif
                                        @if($selectedClient->email)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                ایمیل: {{ $selectedClient->email }}
                                            </span>
                                        @endif
                                        @if($selectedClient->national_code)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" /></svg>
                                                کد ملی: {{ $selectedClient->national_code }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button type="button" wire:click="clearClient" class="px-4 py-2 text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:hover:bg-rose-900/40 rounded-xl transition-all active:scale-95 shrink-0 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                تغییر مشتری
                            </button>
                        </div>
                    @endif
                @else
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-black text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                انتخاب مشتری
                            </h3>
                            <button type="button" wire:click="$set('showQuickClientModal', true)" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors flex items-center gap-1 bg-indigo-50 dark:bg-indigo-950/40 px-3 py-1.5 rounded-xl">
                                + مشتری جدید سریع
                            </button>
                        </div>

                        <div class="relative" x-data="{ open: true }" @click.outside="open = false" @click="open = true">
                            <input type="text" wire:model.live.debounce.300ms="searchClient" wire:input="searchClients"
                                   placeholder="جستجوی مشتری بر اساس نام، موبایل یا ایمیل..."
                                   class="w-full px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200 text-sm placeholder:text-gray-400 dark:placeholder:text-gray-500">

                            @if($clientsList->isNotEmpty() && $searchClient !== '')
                                <div x-show="open" class="absolute z-30 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 animate-in fade-in slide-in-from-top-2 duration-250">
                                    @foreach($clientsList as $c)
                                        <button type="button" wire:click="$set('clientId', {{ $c->id }}); $set('searchClient', '{{ addslashes($c->full_name) }}'); $set('clientsList', [])"
                                                class="w-full text-right px-4 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex justify-between items-center text-sm group">
                                            <div>
                                                <span class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $c->full_name }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 mr-2 group-hover:text-gray-600 dark:group-hover:text-gray-300">({{ $c->phone }})</span>
                                            </div>
                                            <svg class="w-4 h-4 text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @error('clientId') <span class="text-xs text-rose-600 font-bold block mt-2">{{ $message }}</span> @enderror
            </div>

            {{-- Checkout Form Template & Dynamic Fields Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-black text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    قالب و داده‌های فرم تسویه حساب
                </h3>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">قالب فرم تسویه حساب</label>
                    <select wire:model.live="formId" class="w-full px-4 py-3 rounded-2xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200 text-sm">
                        <option value="">-- انتخاب قالب فرم --</option>
                        @foreach($checkoutFormsList as $f)
                            <option value="{{ $f['id'] }}">{{ $f['name'] }}</option>
                        @endforeach
                    </select>
                    @error('formId') <span class="text-xs text-rose-600 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                @if(!empty($checkoutFields))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-gray-200 dark:border-gray-700/50">
                        @foreach($checkoutFields as $field)
                            @php
                                $fieldId = $field['id'];
                                $label = $field['label'] ?? $field['name'] ?? $fieldId;
                                $required = false;
                                if (!empty($field['required'])) {
                                    if (empty($field['required_payment_methods'])) {
                                        $required = true;
                                    } elseif (in_array($payment_method, $field['required_payment_methods'] ?? [])) {
                                        $required = true;
                                    }
                                }
                                $type = $field['type'] ?? 'text';
                            @endphp
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    {{ $label }}
                                    @if($required) <span class="text-rose-500">*</span> @endif
                                </label>

                                @if($type === 'select-province-city')
                                    <div class="grid grid-cols-2 gap-2">
                                        {{-- Province Alpine Select --}}
                                        <div x-data="{ open: false, search: '', options: @js($provinces), get filtered() { return this.search === '' ? this.options : this.options.filter(i => i.includes(this.search)); } }" class="relative" @click.outside="open = false">
                                            <button type="button" @click="open = !open" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none text-right flex justify-between items-center transition-all bg-white hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <span x-text="$wire.shipping_address.province || 'استان'"></span>
                                                <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div x-show="open" x-cloak class="absolute z-40 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-48 flex flex-col">
                                                <div class="p-1.5 border-b border-gray-100 dark:border-gray-700">
                                                    <input type="text" x-model="search" placeholder="جستجو..." class="w-full px-2 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-xs focus:outline-none">
                                                </div>
                                                <ul class="overflow-y-auto p-1 text-xs">
                                                    <template x-for="item in filtered" :key="item">
                                                        <li @click="$wire.set('shipping_address.province', item); open = false" class="px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer rounded-lg text-gray-700 dark:text-gray-300" x-text="item"></li>
                                                    </template>
                                                    <li x-show="filtered.length === 0" class="px-2 py-1.5 text-gray-500 text-center">موردی یافت نشد</li>
                                                </ul>
                                            </div>
                                        </div>

                                        {{-- City Alpine Select --}}
                                        <div x-data="{ open: false, search: '', options: @js($cities), get filtered() { return this.search === '' ? this.options : this.options.filter(i => i.includes(this.search)); } }" x-init="$watch('$wire.cities', value => options = value)" class="relative" @click.outside="open = false">
                                            <button type="button" @click="open = !open" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none text-right flex justify-between items-center transition-all bg-white hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <span x-text="$wire.shipping_address.city || 'شهر'"></span>
                                                <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div x-show="open" x-cloak class="absolute z-40 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-48 flex flex-col">
                                                <div class="p-1.5 border-b border-gray-100 dark:border-gray-700">
                                                    <input type="text" x-model="search" placeholder="جستجو..." class="w-full px-2 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-xs focus:outline-none">
                                                </div>
                                                <ul class="overflow-y-auto p-1 text-xs">
                                                    <template x-for="item in filtered" :key="item">
                                                        <li @click="$wire.set('shipping_address.city', item); open = false" class="px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer rounded-lg text-gray-700 dark:text-gray-300" x-text="item"></li>
                                                    </template>
                                                    <li x-show="filtered.length === 0" class="px-2 py-1.5 text-gray-500 text-center">موردی یافت نشد</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($type === 'textarea')
                                    <textarea wire:model.defer="formData.{{ $fieldId }}" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200"></textarea>
                                @else
                                    <input type="{{ $type }}" wire:model.defer="formData.{{ $fieldId }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200">
                                @endif

                                @error('formData.' . $fieldId) <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Products & Items Selector Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-black text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </span>
                    محصولات سفارش
                </h3>

                <div class="relative" x-data="{ open: true }" @click.outside="open = false" @click="open = true">
                    <input type="text" wire:model.live.debounce.300ms="searchProduct" wire:input="searchProducts"
                           placeholder="جستجوی محصول جهت افزودن به سفارش..."
                           class="w-full px-4 py-3.5 rounded-2xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200 text-sm placeholder:text-gray-400 dark:placeholder:text-gray-500">

                    @if(!empty($productsList) && $searchProduct !== '')
                        <div x-show="open" class="absolute z-30 w-full mt-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800 animate-in fade-in slide-in-from-top-2 duration-250">
                            @foreach($productsList as $p)
                                <button type="button" wire:click="addItem({{ $p['id'] }}); $set('searchProduct', ''); $set('productsList', [])"
                                        class="w-full text-right px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex justify-between items-center text-sm group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 overflow-hidden shrink-0 flex items-center justify-center border border-gray-200 dark:border-gray-700">
                                            @if(!empty($p['image']))
                                                <img src="{{ $p['image'] }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $p['title'] }}</div>
                                            @if(!empty($p['variant_name']) && $p['variant_name'] !== 'استاندارد')
                                                <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">تنوع: {{ $p['variant_name'] }}</div>
                                            @endif
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                فروشنده: {{ $p['vendor_name'] }} | موجودی: {{ $p['stock'] }} عدد
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">{{ number_format($p['price']) }} تومان</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(!empty($items))
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-2xl">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr class="text-gray-500 dark:text-gray-400 text-xs font-bold">
                                    <th class="py-3 px-4 text-right">عنوان محصول</th>
                                    <th class="py-3 px-4 text-center">قیمت واحد (تومان)</th>
                                    <th class="py-3 px-4 text-center">تعداد</th>
                                    <th class="py-3 px-4 text-center">قیمت کل (تومان)</th>
                                    <th class="py-3 px-4 text-left">عملیات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @foreach($items as $idx => $item)
                                    <tr class="text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="py-4 px-4 text-xs">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 overflow-hidden shrink-0 flex items-center justify-center">
                                                    @if(!empty($item['image']))
                                                        <img src="{{ $item['image'] }}" class="w-full h-full object-cover">
                                                    @else
                                                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900 dark:text-gray-100">{{ $item['title'] }}</div>
                                                    @if(!empty($item['variant_name']) && $item['variant_name'] !== 'استاندارد')
                                                        <div class="text-[10px] text-indigo-600 dark:text-indigo-400 mt-1 font-bold">تنوع: {{ $item['variant_name'] }} (کد: {{ $item['variant_code'] }})</div>
                                                    @endif
                                                    @if(!empty($item['vendor_name']))
                                                        <div class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5">فروشنده: {{ $item['vendor_name'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center text-xs font-medium">
                                            {{ number_format($item['price']) }} <span class="text-[10px] text-gray-500 dark:text-gray-400">تومان</span>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <div class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-1">
                                                <button type="button" wire:click="incrementItem({{ $idx }})" class="p-1 rounded-lg text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white dark:hover:bg-gray-800 transition-all active:scale-95 shadow-sm">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                                <span class="px-2 font-black text-xs">{{ $item['quantity'] }}</span>
                                                <button type="button" wire:click="decrementItem({{ $idx }})" class="p-1 rounded-lg text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white dark:hover:bg-gray-800 transition-all active:scale-95 shadow-sm">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center text-xs font-black text-emerald-600 dark:text-emerald-400">
                                            {{ number_format($item['price'] * $item['quantity']) }} <span class="text-[10px] font-normal text-emerald-500 dark:text-emerald-500/80">تومان</span>
                                        </td>
                                        <td class="py-4 px-4 text-left">
                                            <button type="button" wire:click="removeItem({{ $idx }})" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all">
                                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-10 text-center text-gray-400 dark:text-gray-500 text-xs border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl">
                        هیچ محصولی به سفارش اضافه نشده است.
                    </div>
                @endif
                @error('items') <span class="text-xs text-rose-600 font-bold block">{{ $message }}</span> @enderror
            </div>

            {{-- Shipping Address Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-black text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    مشخصات تحویل و آدرس گیرنده
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">استان <span class="text-rose-500">*</span></label>
                        <div x-data="{ open: false, search: '', options: @js($provinces), get filtered() { return this.search === '' ? this.options : this.options.filter(i => i.includes(this.search)); } }" class="relative" @click.outside="open = false">
                            <button type="button" @click="open = !open" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none text-right flex justify-between items-center transition-all bg-white hover:bg-gray-50 dark:hover:bg-gray-700">
                                <span x-text="$wire.shipping_address.province || '-- انتخاب استان --'"></span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="open" x-cloak class="absolute z-40 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 flex flex-col">
                                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                    <input type="text" x-model="search" placeholder="جستجوی استان..." class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-xs focus:outline-none">
                                </div>
                                <ul class="overflow-y-auto p-1 text-xs">
                                    <template x-for="item in filtered" :key="item">
                                        <li @click="$wire.set('shipping_address.province', item); open = false" class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer rounded-lg text-gray-700 dark:text-gray-300" x-text="item"></li>
                                    </template>
                                    <li x-show="filtered.length === 0" class="px-3 py-2 text-gray-500 text-center">موردی یافت نشد</li>
                                </ul>
                            </div>
                        </div>
                        @error('shipping_address.province') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">شهر <span class="text-rose-500">*</span></label>
                        <div x-data="{ open: false, search: '', options: @js($cities), get filtered() { return this.search === '' ? this.options : this.options.filter(i => i.includes(this.search)); } }" x-init="$watch('$wire.cities', value => options = value)" class="relative" @click.outside="open = false">
                            <button type="button" @click="open = !open" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none text-right flex justify-between items-center transition-all bg-white hover:bg-gray-50 dark:hover:bg-gray-700">
                                <span x-text="$wire.shipping_address.city || '-- انتخاب شهر --'"></span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="open" x-cloak class="absolute z-40 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 flex flex-col">
                                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                    <input type="text" x-model="search" placeholder="جستجوی شهر..." class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-xs focus:outline-none">
                                </div>
                                <ul class="overflow-y-auto p-1 text-xs">
                                    <template x-for="item in filtered" :key="item">
                                        <li @click="$wire.set('shipping_address.city', item); open = false" class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer rounded-lg text-gray-700 dark:text-gray-300" x-text="item"></li>
                                    </template>
                                    <li x-show="filtered.length === 0" class="px-3 py-2 text-gray-500 text-center">موردی یافت نشد</li>
                                </ul>
                            </div>
                        </div>
                        @error('shipping_address.city') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">آدرس دقیق پستی</label>
                        <textarea wire:model.defer="shipping_address.address" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200"></textarea>
                        @error('shipping_address.address') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">نام گیرنده (در صورت تفاوت با خریدار)</label>
                        <input type="text" wire:model.defer="shipping_address.recipient_name" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200">
                        @error('shipping_address.recipient_name') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">موبایل گیرنده</label>
                        <input type="text" wire:model.defer="shipping_address.recipient_mobile" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200">
                        @error('shipping_address.recipient_mobile') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Shipping Management Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-black text-gray-900 dark:text-gray-100 text-base flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 011-1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </span>
                    مدیریت حمل و نقل و ارسال مرسوله
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">روش ارسال</label>
                        <select wire:model.live="shipping_method" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none">
                            <option value="">-- انتخاب روش ارسال --</option>
                            @foreach($shippingMethodsList as $method)
                                <option value="{{ $method['code'] }}">{{ $method['name'] }} ({{ $method['code'] }})</option>
                            @endforeach
                        </select>
                        @error('shipping_method') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">هزینه حمل و نقل (تومان)</label>
                        <input type="number" wire:model.live.debounce.300ms="total_shipping_cost" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none">
                        @error('total_shipping_cost') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">کد رهگیری مرسوله</label>
                        <input type="text" wire:model.defer="tracking_code" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none">
                        @error('tracking_code') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Section: Totals, Actions, Gateway and Status Configuration --}}
        <div class="space-y-6">

            {{-- Summary & Totals Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-5">
                <h3 class="font-black text-gray-900 dark:text-gray-100 text-lg border-b border-gray-200 dark:border-gray-700 pb-3">خلاصه مالی فاکتور</h3>
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">مجموع اقلام:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($this->subtotal) }} تومان</span>
                    </div>
                    @if((float)$this->total_shipping_cost > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">هزینه حمل و نقل:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format((float)$this->total_shipping_cost) }} تومان</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-4 text-base font-black text-gray-900 dark:text-white">
                        <span>مبلغ کل فاکتور:</span>
                        <span class="text-indigo-600 dark:text-indigo-400">{{ number_format($this->subtotal + (float)$this->total_shipping_cost) }} تومان</span>
                    </div>
                </div>

                <div class="pt-2 space-y-2">
                    <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-600/10 hover:shadow-indigo-600/20 hover:scale-[1.01] transition-all focus:outline-none focus:ring-4 focus:ring-indigo-500/20">
                        {{ $isEdit ? 'به‌روزرسانی و ثبت سفارش' : 'ثبت و ذخیره نهایی سفارش' }}
                    </button>
                    <a href="{{ route('user.market.orders.index') }}" class="w-full block text-center py-3.5 px-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold rounded-2xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors shadow-sm">
                        انصراف و بازگشت
                    </a>
                </div>
            </div>

            {{-- Settings & Gateway Settings --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-5">
                <h3 class="font-black text-gray-900 dark:text-gray-100 text-lg border-b border-gray-200 dark:border-gray-700 pb-3">تنظیمات پرداخت و ارسال</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">روش پرداخت</label>
                        <select wire:model.live="payment_method" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none">
                            <option value="zibal">زیبال (Zibal)</option>
                            <option value="zarinpal">زرین‌پال (Zarinpal)</option>
                            <option value="behpardakht">به پرداخت ملت (Behpardakht)</option>
                            <option value="pos">پرداخت در محل (کارتخوان - POS)</option>
                            <option value="transfer">کارت به کارت / واریز فیش (Transfer)</option>
                            <option value="cod">پرداخت در محل (نقدی - COD)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت پرداخت</label>
                        <select wire:model.defer="payment_status" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none">
                            <option value="unpaid">در انتظار پرداخت (Unpaid)</option>
                            <option value="paid">پرداخت شده (Paid)</option>
                            <option value="failed">پرداخت ناموفق (Failed)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت سفارش</label>
                        <select wire:model.defer="market_order_status_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none">
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->admin_label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Inline Quick Customer Creation Modal --}}
    @if($showQuickClientModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm transition-all duration-300" dir="rtl">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden text-right animate-in fade-in zoom-in-95 duration-200">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-6 text-white flex justify-between items-center">
                    <h3 class="font-extrabold text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        ثبت سریع مشتری جدید
                    </h3>
                    <button type="button" wire:click="$set('showQuickClientModal', false)" class="text-white/80 hover:text-white transition-colors bg-white/10 p-1.5 rounded-xl hover:scale-105 transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="createQuickClient" class="p-6 space-y-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">نام و نام خانوادگی <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="newClient.full_name" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200">
                        @error('newClient.full_name') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">شماره موبایل <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="newClient.phone" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200">
                        @error('newClient.phone') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">ایمیل</label>
                        <input type="email" wire:model.defer="newClient.email" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200">
                        @error('newClient.email') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">کد ملی</label>
                        <input type="text" wire:model.defer="newClient.national_code" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all duration-200">
                        @error('newClient.national_code') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex gap-3 justify-end">
                        <button type="button" wire:click="$set('showQuickClientModal', false)" class="py-2.5 px-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold rounded-xl text-xs hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            انصراف
                        </button>
                        <button type="submit" class="py-2.5 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md shadow-indigo-600/10 hover:shadow-indigo-600/20 hover:scale-[1.02] transition-all">
                            ثبت مشتری
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
