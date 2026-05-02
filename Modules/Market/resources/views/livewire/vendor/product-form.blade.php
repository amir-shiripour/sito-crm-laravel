@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white transition-all";
    $labelClass = "block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1.5";
@endphp

<div class="mx-auto pb-10">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700 pb-5 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">ویرایش و فروش محصول</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">تنوع‌های مدنظر خود را انتخاب کرده و موجودی و قیمت را وارد کنید.</p>
                </div>
            </div>
            <a href="{{ route('user.market.vendor.products.index') }}" class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-gray-700 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors whitespace-nowrap">بازگشت به انبار</a>
        </div>

        {{-- بخش ۱: انتخاب کالا (Live Search) --}}
        <div class="mb-8">
            @if(!$selectedMasterProduct)
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" class="w-full rounded-2xl border-gray-200 bg-gray-50 py-4 pr-12 pl-4 text-base focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white transition-all shadow-inner" placeholder="نام کالا یا کد هوشمند (SKU) آن را جستجو کنید...">

                    {{-- دراپ‌داون نتایج --}}
                    @if(strlen($searchQuery) >= 2)
                        <div class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden animate-in fade-in slide-in-from-top-2">
                            @forelse($this->searchResults as $res)
                                <button wire:click="selectProduct({{ $res->id }})" class="w-full text-right flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-50 dark:border-gray-700/50 transition-colors last:border-0">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-900 flex-shrink-0 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700">
                                        @if($res->main_image)
                                            <img src="{{ Storage::url($res->main_image) }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ $res->title }}</h4>
                                        <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="font-mono">{{ $res->crm_code }}</span>
                                            <span>•</span>
                                            <span>{{ $res->category->name ?? 'بدون دسته' }}</span>
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <div class="p-6 text-center text-gray-500 dark:text-gray-400">هیچ محصولی با این نام در کاتالوگ یافت نشد.</div>
                            @endforelse
                        </div>
                    @endif
                </div>
            @else
                {{-- محصول انتخاب شده --}}
                <div class="bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30 p-4 rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl bg-white dark:bg-gray-800 flex-shrink-0 flex items-center justify-center overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700">
                            @if($selectedMasterProduct->main_image)
                                <img src="{{ Storage::url($selectedMasterProduct->main_image) }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold text-indigo-900 dark:text-indigo-100 text-lg">{{ $selectedMasterProduct->title }}</h3>
                            <div class="flex items-center gap-2 mt-1 text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                                <span class="font-mono bg-white dark:bg-gray-800 px-2 py-0.5 rounded shadow-sm">{{ $selectedMasterProduct->crm_code }}</span>
                                <span>{{ $selectedMasterProduct->brand->name ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                    <button wire:click="clearSelection" class="p-2 text-gray-400 hover:text-red-500 hover:bg-white dark:hover:bg-gray-800 rounded-xl transition-colors shadow-sm" title="تغییر کالا">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
        </div>

        {{-- بخش ۲: گرید تنوع‌ها و قیمت‌گذاری --}}
        @if($selectedMasterProduct && count($available_variants) > 0)
            <div class="space-y-4 animate-in fade-in slide-in-from-bottom-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200">تنوع‌های انبار شما</h3>
                    <span class="text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-full font-bold">برای حذف از انبار، تیک آن را بردارید</span>
                </div>

                @foreach($available_variants as $variant)
                    <div class="bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 p-5 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-300" x-data="{ sell: @entangle('vendor_variants.'.$variant->id.'.sell_this') }">

                        <label class="flex items-center gap-3 mb-4 cursor-pointer">
                            <input type="checkbox" wire:model="vendor_variants.{{ $variant->id }}.sell_this" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 transition-colors">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900 dark:text-white text-sm" :class="sell ? 'text-indigo-700 dark:text-indigo-300' : ''">
                                    {{ $vendor_variants[$variant->id]['display_name'] }}
                                </span>
                                <span class="font-mono text-[10px] text-gray-400 dark:text-gray-500">{{ $variant->variant_code }}</span>
                            </div>
                        </label>

                        {{-- گرید ورودی‌های فروشنده (لود ایمن با جاوااسکریپت) --}}
                        <div x-show="sell" x-collapse>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 pt-4 border-t border-dashed border-gray-100 dark:border-gray-700">

                                {{-- قیمت اصلی --}}
                                <div class="lg:col-span-1" x-data="{
                                    raw: @entangle('vendor_variants.'.$variant->id.'.price'),
                                    formatted: '',
                                    init() {
                                        this.format(this.raw);
                                        this.$watch('raw', val => this.format(val));
                                    },
                                    format(val) {
                                        this.formatted = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                    },
                                    update() {
                                        this.raw = this.formatted.replace(/,/g, '');
                                    }
                                }">
                                    <label class="{{ $labelClass }}">قیمت برای مصرف‌کننده <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="text" x-model="formatted" @input="update()" class="{{ $inputClass }} font-mono dir-ltr text-center font-bold text-indigo-700 dark:text-indigo-300 pr-10" placeholder="مثلا 1,500,000">
                                        <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-gray-400">تومان</span>
                                    </div>
                                </div>

                                {{-- قیمت تخفیف‌خورده --}}
                                <div class="lg:col-span-1" x-data="{
                                    raw: @entangle('vendor_variants.'.$variant->id.'.discount_price'),
                                    formatted: '',
                                    init() {
                                        this.format(this.raw);
                                        this.$watch('raw', val => this.format(val));
                                    },
                                    format(val) {
                                        this.formatted = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                    },
                                    update() {
                                        this.raw = this.formatted.replace(/,/g, '');
                                    }
                                }">
                                    <label class="{{ $labelClass }}">قیمت بعد از تخفیف</label>
                                    <input type="text" x-model="formatted" @input="update()" class="{{ $inputClass }} font-mono dir-ltr text-center text-rose-600 dark:text-rose-400" placeholder="بدون تخفیف">
                                </div>

                                {{-- کنترل انبار --}}
                                <div class="grid grid-cols-3 lg:col-span-2 gap-2 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1 text-center">موجودی انبار</label>
                                        <input type="number" wire:model="vendor_variants.{{ $variant->id }}.stock" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 dark:text-white rounded-lg text-center text-sm py-1.5 focus:ring-1 focus:ring-indigo-500 font-bold">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1 text-center text-nowrap" title="حداقل خرید">حداقل خرید</label>
                                        <input type="number" wire:model="vendor_variants.{{ $variant->id }}.min_purchase" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 dark:text-white rounded-lg text-center text-sm py-1.5 focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1 text-center text-nowrap" title="حداکثر خرید">حداکثر خرید</label>
                                        <input type="number" wire:model="vendor_variants.{{ $variant->id }}.max_purchase" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 dark:text-white rounded-lg text-center text-sm py-1.5 focus:ring-1 focus:ring-indigo-500" placeholder="نامحدود">
                                    </div>
                                </div>

                                {{-- وضعیت انتشار --}}
                                <div class="lg:col-span-1 flex items-end pb-1">
                                    <label class="flex items-center gap-2 cursor-pointer w-full justify-center bg-gray-50 dark:bg-gray-900/50 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                        <div class="relative flex items-center">
                                            <input type="checkbox" wire:model="vendor_variants.{{ $variant->id }}.is_active" class="peer sr-only">
                                            <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                                        </div>
                                        <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300">نمایش کالا</span>
                                    </label>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="pt-6 mt-6 flex justify-end">
                    <button wire:click="save" wire:loading.attr="disabled" class="px-8 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 w-full sm:w-auto flex items-center justify-center gap-2">
                        <span wire:loading.remove>ذخیره موجودی و قیمت‌ها</span>
                        <span wire:loading.flex class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            درحال پردازش...
                        </span>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
