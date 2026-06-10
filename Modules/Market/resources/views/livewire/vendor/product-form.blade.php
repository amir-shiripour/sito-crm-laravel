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
        @if($selectedMasterProduct)
            <div class="space-y-4 animate-in fade-in slide-in-from-bottom-4">
                @if($isStandardOnly)
                    @php $variant = $available_variants->first(); @endphp
                    @if($variant)
                        <div class="bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 p-6 rounded-3xl shadow-sm">
                            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-6 border-b border-gray-100 dark:border-gray-700 pb-3">
                                قیمت‌گذاری و موجودی کالا
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                                {{-- قیمت اصلی --}}
                                <div class="lg:col-span-1" x-data="{
                                    rawPrice: @entangle('vendor_variants.'.$variant->id.'.price'),
                                    formattedPrice: '',
                                    init() {
                                        this.formatPrice(this.rawPrice);
                                        this.$watch('rawPrice', val => this.formatPrice(val));
                                    },
                                    formatPrice(val) {
                                        this.formattedPrice = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                    },
                                    updatePrice() {
                                        this.rawPrice = this.formattedPrice.replace(/,/g, '');
                                    }
                                }">
                                    <label class="{{ $labelClass }}">قیمت برای مصرف‌کننده <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="text" x-model="formattedPrice" @input="updatePrice()" class="{{ $inputClass }} font-mono dir-ltr text-center font-bold text-indigo-700 dark:text-indigo-300 pr-10" placeholder="مثلا 1,500,000">
                                        <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-gray-400">تومان</span>
                                    </div>
                                </div>

                                {{-- آپدیت: محاسبه خودکار درصد و قیمت تخفیف --}}
                                <div class="lg:col-span-2 grid grid-cols-2 gap-2 bg-rose-50/50 dark:bg-rose-900/10 p-2 rounded-xl border border-rose-100 dark:border-rose-800" x-data="{
                                    rawPrice: @entangle('vendor_variants.'.$variant->id.'.price'),
                                    rawDiscount: @entangle('vendor_variants.'.$variant->id.'.discount_price'),
                                    formattedDiscount: '',
                                    percent: '',
                                    init() {
                                        this.formatDiscount(this.rawDiscount);
                                        this.calcPercent();
                                        this.$watch('rawDiscount', val => {
                                            this.formatDiscount(val);
                                            this.calcPercent();
                                        });
                                        this.$watch('rawPrice', () => this.calcPercent());
                                    },
                                    formatDiscount(val) {
                                        this.formattedDiscount = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                    },
                                    updateFromPrice() {
                                        this.rawDiscount = this.formattedDiscount.replace(/,/g, '');
                                        this.calcPercent();
                                    },
                                    updateFromPercent() {
                                        let p = parseFloat(this.percent);
                                        let pr = parseFloat(this.rawPrice);
                                        if (p > 0 && p <= 100 && pr > 0) {
                                            let d = pr - (pr * (p / 100));
                                            this.rawDiscount = d.toString();
                                        } else {
                                            this.rawDiscount = '';
                                        }
                                    },
                                    calcPercent() {
                                        let pr = parseFloat(this.rawPrice);
                                        let d = parseFloat(this.rawDiscount);
                                        if (pr > 0 && d > 0 && d < pr) {
                                            this.percent = Math.round(((pr - d) / pr) * 100);
                                        } else {
                                            this.percent = '';
                                        }
                                    }
                                }">
                                    <div>
                                        <label class="{{ $labelClass }} !text-rose-600 dark:!text-rose-400">درصد تخفیف</label>
                                        <div class="relative">
                                            <input type="number" x-model="percent" @input="updateFromPercent()" class="{{ $inputClass }} dir-ltr text-center !border-rose-200 focus:!border-rose-500 focus:!ring-rose-500/20 dark:!border-rose-800" placeholder="%">
                                            <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-rose-400">٪</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }} !text-rose-600 dark:!text-rose-400">قیمت نهایی</label>
                                        <input type="text" x-model="formattedDiscount" @input="updateFromPrice()" class="{{ $inputClass }} font-mono dir-ltr text-center font-bold text-rose-600 dark:text-rose-400 !border-rose-200 focus:!border-rose-500 focus:!ring-rose-500/20 dark:!border-rose-800" placeholder="قیمت با تخفیف">
                                    </div>
                                </div>

                                {{-- کنترل انبار --}}
                                <div class="grid grid-cols-3 lg:col-span-2 gap-2 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1 text-center">موجودی انبار</label>
                                        <input type="number" wire:model="vendor_variants.{{ $variant->id }}.stock" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 dark:text-white rounded-lg text-center text-sm py-1.5 focus:ring-1 focus:ring-indigo-500 font-bold"
                                            @if($isWmsActive) readonly @endif>
                                        @if($isWmsActive)
                                            <p class="text-[9px] text-center text-gray-400 mt-1">از بخش انبارگردانی</p>
                                        @endif
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
                            </div>

                            {{-- مدیریت زمان و شرایط تخفیف (اگر تخفیف وارد شده باشد) --}}
                            <div x-data="{ hasDiscount: @entangle('vendor_variants.'.$variant->id.'.discount_price') }" x-show="hasDiscount" x-collapse>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4 bg-rose-50/30 dark:bg-rose-900/5 p-4 rounded-xl border border-rose-100 dark:border-rose-800/50" wire:ignore>
                                    <div>
                                        <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">تاریخ شروع تخفیف</label>
                                        <input type="text" data-jdp-with-time wire:model.defer="vendor_variants.{{ $variant->id }}.discount_start_date" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800">
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">تاریخ پایان تخفیف</label>
                                        <input type="text" data-jdp-with-time wire:model.defer="vendor_variants.{{ $variant->id }}.discount_end_date" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800">
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">تعداد موجودی در تخفیف</label>
                                        <input type="number" wire:model.defer="vendor_variants.{{ $variant->id }}.discount_stock" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800" placeholder="تا اتمام موجودی کل">
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">حداکثر خرید در تخفیف</label>
                                        <input type="number" wire:model.defer="vendor_variants.{{ $variant->id }}.max_discount_purchase_qty" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800" placeholder="نامحدود">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <label class="flex items-center gap-2 cursor-pointer bg-gray-50 dark:bg-gray-900/50 px-4 py-2 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" wire:model="vendor_variants.{{ $variant->id }}.is_active" class="peer sr-only">
                                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300">نمایش کالا در سایت</span>
                                </label>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                        <div class="flex items-center gap-4">
                            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200">تنوع‌های انبار شما</h3>
                            <div class="flex items-center gap-1 bg-gray-50 dark:bg-gray-800/50 p-1 rounded-lg border border-gray-100 dark:border-gray-700/50">
                                <button type="button" wire:click="toggleAllVariants(true)" class="px-3 py-1.5 hover:bg-gray-200/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md text-[11px] font-bold transition-all">انتخاب همه</button>
                                <button type="button" wire:click="toggleAllVariants(false)" class="px-3 py-1.5 hover:bg-gray-200/60 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-md text-[11px] font-bold transition-all">لغو همه</button>
                            </div>
                        </div>
                        @if($allow_custom_variants)
                            <button wire:click="addCustomVariant" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50 rounded-lg text-xs font-bold transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                افزودن تنوع جدید
                            </button>
                        @else
                             <span class="text-[11px] text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-full font-bold">برای حذف از انبار، تیک آن را بردارید</span>
                        @endif
                    </div>

                    {{-- پنل تنظیمات گروهی (Batch Editing) --}}
                    <div x-data="{ showBatchPanel: false }" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-6 shadow-sm transition-all hover:border-indigo-300 dark:hover:border-indigo-600">
                        <button type="button" @click="showBatchPanel = !showBatchPanel" class="w-full flex items-center justify-between text-gray-800 dark:text-gray-200 font-bold text-sm">
                            <span class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                </div>
                                تنظیم گروهی و خودکار تمام تنوع‌ها (افزایش سرعت ثبت)
                            </span>
                            <div class="flex items-center gap-2 text-xs font-normal text-gray-500 dark:text-gray-400">
                                <span x-show="!showBatchPanel">باز کردن پنل</span>
                                <span x-show="showBatchPanel" style="display: none;">بستن پنل</span>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': showBatchPanel}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </button>

                        <div x-show="showBatchPanel" x-collapse class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-4" style="display: none;">
                            <div class="p-3 bg-indigo-50/50 dark:bg-indigo-900/10 rounded-xl border border-indigo-100/50 dark:border-indigo-800/30 mb-4">
                                <p class="text-xs text-indigo-700 dark:text-indigo-300 font-medium leading-relaxed flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    مقادیری که در این بخش وارد می‌کنید، با کلیک روی دکمه "اعمال"، روی <span class="font-bold underline underline-offset-4">تمام تنوع‌هایی که تیک خورده‌اند (فعال هستند)</span> کپی خواهد شد. فیلدهای خالی تغییری ایجاد نمی‌کنند.
                                </p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                {{-- قیمت مصرف کننده گروهی --}}
                                <div x-data="{
                                    val: @entangle('batchPrice'),
                                    format() {
                                        this.$el.querySelector('input').value = this.val ? this.val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                    }
                                }" x-init="$watch('val', () => format())">
                                    <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">قیمت مصرف‌کننده گروهی</label>
                                    <div class="relative">
                                        <input type="text" x-on:input="val = $event.target.value.replace(/\D/g, '')" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs font-mono text-center font-bold text-indigo-700 dark:text-indigo-300 pr-10" placeholder="مثلا 1,500,000">
                                        <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-gray-400">تومان</span>
                                    </div>
                                </div>

                                {{-- درصد تخفیف گروهی --}}
                                <div>
                                    <label class="block text-[10px] font-bold text-rose-600 dark:text-rose-400 mb-1">درصد تخفیف گروهی</label>
                                    <div class="relative">
                                        <input type="number" wire:model.defer="batchDiscountPercent" class="w-full bg-white dark:bg-gray-800 border border-rose-200 dark:border-rose-800 rounded-xl px-3 py-2 text-xs font-mono text-center font-bold text-rose-600 dark:text-rose-400 pr-8" placeholder="مثلا 15">
                                        <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-rose-400">٪</span>
                                    </div>
                                </div>

                                {{-- قیمت نهایی تخفیف گروهی --}}
                                <div x-data="{
                                    val: @entangle('batchDiscountPrice'),
                                    format() {
                                        this.$el.querySelector('input').value = this.val ? this.val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                    }
                                }" x-init="$watch('val', () => format())">
                                    <label class="block text-[10px] font-bold text-rose-600 dark:text-rose-400 mb-1">قیمت نهایی تخفیف گروهی</label>
                                    <div class="relative">
                                        <input type="text" x-on:input="val = $event.target.value.replace(/\D/g, '')" class="w-full bg-white dark:bg-gray-800 border border-rose-200 dark:border-rose-800 rounded-xl px-3 py-2 text-xs font-mono text-center font-bold text-rose-600 dark:text-rose-400 pr-10" placeholder="قیمت مستقیم با تخفیف">
                                        <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-rose-400">تومان</span>
                                    </div>
                                </div>

                                {{-- موجودی انبار گروهی --}}
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">موجودی انبار گروهی</label>
                                    <input type="number" wire:model.defer="batchStock" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs text-center font-mono" placeholder="فقط در صورت غیرفعال بودن انبارداری" @if($isWmsActive) disabled @endif>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                {{-- حداقل خرید گروهی --}}
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">حداقل خرید گروهی</label>
                                    <input type="number" wire:model.defer="batchMinPurchase" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs text-center" placeholder="مثلا 1">
                                </div>

                                {{-- حداکثر خرید گروهی --}}
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">حداکثر خرید گروهی</label>
                                    <input type="number" wire:model.defer="batchMaxPurchase" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs text-center" placeholder="نامحدود">
                                </div>

                                {{-- تعداد در تخفیف گروهی --}}
                                <div>
                                    <label class="block text-[10px] font-bold text-rose-600 dark:text-rose-400 mb-1">تعداد در تخفیف گروهی</label>
                                    <input type="number" wire:model.defer="batchDiscountStock" class="w-full bg-white dark:bg-gray-800 border border-rose-200 dark:border-rose-800 rounded-xl px-3 py-2 text-xs text-center" placeholder="مثلا 5">
                                </div>

                                {{-- حداکثر خرید در تخفیف گروهی --}}
                                <div>
                                    <label class="block text-[10px] font-bold text-rose-600 dark:text-rose-400 mb-1">حداکثر خرید در تخفیف گروهی</label>
                                    <input type="number" wire:model.defer="batchMaxDiscountQty" class="w-full bg-white dark:bg-gray-800 border border-rose-200 dark:border-rose-800 rounded-xl px-3 py-2 text-xs text-center" placeholder="مثلا 1">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" wire:ignore>
                                {{-- تاریخ شروع تخفیف گروهی --}}
                                <div>
                                    <label class="block text-[10px] font-bold text-rose-600 dark:text-rose-400 mb-1">تاریخ شروع تخفیف گروهی</label>
                                    <input type="text" data-jdp-with-time wire:model.defer="batchDiscountStart" class="w-full bg-white dark:bg-gray-800 border border-rose-200 dark:border-rose-800 rounded-xl px-3 py-2 text-xs text-center" placeholder="انتخاب تاریخ و ساعت">
                                </div>

                                {{-- تاریخ پایان تخفیف گروهی --}}
                                <div>
                                    <label class="block text-[10px] font-bold text-rose-600 dark:text-rose-400 mb-1">تاریخ پایان تخفیف گروهی</label>
                                    <input type="text" data-jdp-with-time wire:model.defer="batchDiscountEnd" class="w-full bg-white dark:bg-gray-800 border border-rose-200 dark:border-rose-800 rounded-xl px-3 py-2 text-xs text-center" placeholder="انتخاب تاریخ و ساعت">
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="button" wire:click="applyBatchSettings" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-500/25 transition-all flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    اعمال گروهی بر روی تنوع‌های فعال
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- تنوع‌های ایجاد شده توسط ادمین --}}
                    @foreach($available_variants as $variant)
                        <div class="bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 p-5 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-300" x-data="{ sell: @entangle('vendor_variants.'.$variant->id.'.sell_this') }">

                            <label class="flex items-center gap-3 mb-4 cursor-pointer">
                                <input type="checkbox" x-model="sell" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 transition-colors">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 dark:text-white text-sm" :class="sell ? 'text-indigo-700 dark:text-indigo-300' : ''">
                                        {{ $vendor_variants[$variant->id]['display_name'] ?? 'تنوع استاندارد' }}
                                    </span>
                                    <span class="font-mono text-[10px] text-gray-400 dark:text-gray-500">{{ $variant->variant_code }}</span>
                                </div>
                            </label>

                            <div x-show="sell" x-collapse>
                                <div class="pt-4 border-t border-dashed border-gray-100 dark:border-gray-700">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                                        {{-- قیمت اصلی --}}
                                        <div class="lg:col-span-1" x-data="{
                                            rawPrice: @entangle('vendor_variants.'.$variant->id.'.price'),
                                            formattedPrice: '',
                                            init() {
                                                this.formatPrice(this.rawPrice);
                                                this.$watch('rawPrice', val => this.formatPrice(val));
                                            },
                                            formatPrice(val) {
                                                this.formattedPrice = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                            },
                                            updatePrice() {
                                                this.rawPrice = this.formattedPrice.replace(/,/g, '');
                                            }
                                        }">
                                            <label class="{{ $labelClass }}">قیمت برای مصرف‌کننده <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input type="text" x-model="formattedPrice" @input="updatePrice()" class="{{ $inputClass }} font-mono dir-ltr text-center font-bold text-indigo-700 dark:text-indigo-300 pr-10" placeholder="مثلا 1,500,000">
                                                <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-gray-400">تومان</span>
                                            </div>
                                        </div>

                                        {{-- 💡 آپدیت: محاسبه خودکار درصد و قیمت تخفیف --}}
                                        <div class="lg:col-span-2 grid grid-cols-2 gap-2 bg-rose-50/50 dark:bg-rose-900/10 p-2 rounded-xl border border-rose-100 dark:border-rose-800" x-data="{
                                            rawPrice: @entangle('vendor_variants.'.$variant->id.'.price'),
                                            rawDiscount: @entangle('vendor_variants.'.$variant->id.'.discount_price'),
                                            formattedDiscount: '',
                                            percent: '',
                                            init() {
                                                this.formatDiscount(this.rawDiscount);
                                                this.calcPercent();
                                                this.$watch('rawDiscount', val => {
                                                    this.formatDiscount(val);
                                                    this.calcPercent();
                                                });
                                                this.$watch('rawPrice', () => this.calcPercent());
                                            },
                                            formatDiscount(val) {
                                                this.formattedDiscount = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                            },
                                            updateFromPrice() {
                                                this.rawDiscount = this.formattedDiscount.replace(/,/g, '');
                                                this.calcPercent();
                                            },
                                            updateFromPercent() {
                                                let p = parseFloat(this.percent);
                                                let pr = parseFloat(this.rawPrice);
                                                if (p > 0 && p <= 100 && pr > 0) {
                                                    let d = pr - (pr * (p / 100));
                                                    this.rawDiscount = d.toString();
                                                } else {
                                                    this.rawDiscount = '';
                                                }
                                            },
                                            calcPercent() {
                                                let pr = parseFloat(this.rawPrice);
                                                let d = parseFloat(this.rawDiscount);
                                                if (pr > 0 && d > 0 && d < pr) {
                                                    this.percent = Math.round(((pr - d) / pr) * 100);
                                                } else {
                                                    this.percent = '';
                                                }
                                            }
                                        }">
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-600 dark:!text-rose-400">درصد تخفیف</label>
                                                <div class="relative">
                                                    <input type="number" x-model="percent" @input="updateFromPercent()" class="{{ $inputClass }} dir-ltr text-center !border-rose-200 focus:!border-rose-500 focus:!ring-rose-500/20 dark:!border-rose-800" placeholder="%">
                                                    <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-rose-400">٪</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-600 dark:!text-rose-400">قیمت نهایی</label>
                                                <input type="text" x-model="formattedDiscount" @input="updateFromPrice()" class="{{ $inputClass }} font-mono dir-ltr text-center font-bold text-rose-600 dark:text-rose-400 !border-rose-200 focus:!border-rose-500 focus:!ring-rose-500/20 dark:!border-rose-800" placeholder="قیمت با تخفیف">
                                            </div>
                                        </div>

                                        {{-- کنترل انبار --}}
                                        <div class="grid grid-cols-3 lg:col-span-2 gap-2 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-100 dark:border-gray-800">
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1 text-center">موجودی انبار</label>
                                                <input type="number" wire:model="vendor_variants.{{ $variant->id }}.stock" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 dark:text-white rounded-lg text-center text-sm py-1.5 focus:ring-1 focus:ring-indigo-500 font-bold"
                                                    @if($isWmsActive) readonly @endif>
                                                @if($isWmsActive)
                                                    <p class="text-[9px] text-center text-gray-400 mt-1">از بخش انبارگردانی</p>
                                                @endif
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
                                    </div>

                                    {{-- 💡 NEW: مدیریت زمان و شرایط تخفیف (اگر تخفیف وارد شده باشد) --}}
                                    <div x-data="{ hasDiscount: @entangle('vendor_variants.'.$variant->id.'.discount_price') }" x-show="hasDiscount" x-collapse>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4 bg-rose-50/30 dark:bg-rose-900/5 p-4 rounded-xl border border-rose-100 dark:border-rose-800/50" wire:ignore>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">تاریخ شروع تخفیف</label>
                                                <input type="text" data-jdp-with-time wire:model.defer="vendor_variants.{{ $variant->id }}.discount_start_date" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800">
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">تاریخ پایان تخفیف</label>
                                                <input type="text" data-jdp-with-time wire:model.defer="vendor_variants.{{ $variant->id }}.discount_end_date" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800">
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">تعداد موجودی در تخفیف</label>
                                                <input type="number" wire:model.defer="vendor_variants.{{ $variant->id }}.discount_stock" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800" placeholder="تا اتمام موجودی کل">
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">حداکثر خرید در تخفیف</label>
                                                <input type="number" wire:model.defer="vendor_variants.{{ $variant->id }}.max_discount_purchase_qty" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800" placeholder="نامحدود">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex justify-end">
                                        <label class="flex items-center gap-2 cursor-pointer bg-gray-50 dark:bg-gray-900/50 px-4 py-2 rounded-xl border border-gray-100 dark:border-gray-800">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" wire:model="vendor_variants.{{ $variant->id }}.is_active" class="peer sr-only">
                                                <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                                            </div>
                                            <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300">نمایش کالا در سایت</span>
                                        </label>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- تنوع‌های ایجاد شده توسط فروشنده (کاستوم) --}}
                @if($allow_custom_variants && count($vendor_custom_variants) > 0)
                    @foreach($vendor_custom_variants as $index => $customVariant)
                        <div class="bg-indigo-50/30 dark:bg-indigo-900/10 border-2 border-dashed border-indigo-200 dark:border-indigo-800/50 p-5 rounded-2xl relative">
                            <button wire:click="removeCustomVariant({{ $index }})" class="absolute top-4 left-4 text-gray-400 hover:text-red-500 bg-white dark:bg-gray-800 p-1.5 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>

                            <div class="mb-4 pr-2 border-r-4 border-indigo-500">
                                <h4 class="font-bold text-indigo-900 dark:text-indigo-300 text-sm">ایجاد تنوع جدید</h4>
                                <p class="text-[10px] text-gray-500 mt-1">ویژگی‌های زیر را برای ساخت تنوع خود انتخاب کنید.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                @foreach($allowed_axes_options as $axisName => $options)
                                    <div>
                                        <label class="{{ $labelClass }}">{{ $axisName }} <span class="text-red-500">*</span></label>
                                        <select wire:model.defer="vendor_custom_variants.{{ $index }}.attributes.{{ $axisName }}" class="{{ $inputClass }}">
                                            <option value="">انتخاب کنید...</option>
                                            @foreach($options as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-4 border-t border-dashed border-indigo-200 dark:border-indigo-800">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                                    <div class="lg:col-span-1" x-data="{
                                        rawPrice: @entangle('vendor_custom_variants.'.$index.'.price'),
                                        formattedPrice: '',
                                        init() {
                                            this.formatPrice(this.rawPrice);
                                            this.$watch('rawPrice', val => this.formatPrice(val));
                                        },
                                        formatPrice(val) {
                                            this.formattedPrice = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                        },
                                        updatePrice() {
                                            this.rawPrice = this.formattedPrice.replace(/,/g, '');
                                        }
                                    }">
                                        <label class="{{ $labelClass }}">قیمت برای مصرف‌کننده <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="text" x-model="formattedPrice" @input="updatePrice()" class="{{ $inputClass }} font-mono dir-ltr text-center font-bold text-indigo-700 dark:text-indigo-300 pr-10" placeholder="مثلا 1,500,000">
                                            <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-gray-400">تومان</span>
                                        </div>
                                    </div>

                                    {{-- آپدیت محاسبه تخفیف برای تنوع دستی --}}
                                    <div class="lg:col-span-2 grid grid-cols-2 gap-2 bg-rose-50/50 dark:bg-rose-900/10 p-2 rounded-xl border border-rose-100 dark:border-rose-800" x-data="{
                                        rawPrice: @entangle('vendor_custom_variants.'.$index.'.price'),
                                        rawDiscount: @entangle('vendor_custom_variants.'.$index.'.discount_price'),
                                        formattedDiscount: '',
                                        percent: '',
                                        init() {
                                            this.formatDiscount(this.rawDiscount);
                                            this.calcPercent();
                                            this.$watch('rawDiscount', val => {
                                                this.formatDiscount(val);
                                                this.calcPercent();
                                            });
                                            this.$watch('rawPrice', () => this.calcPercent());
                                        },
                                        formatDiscount(val) {
                                            this.formattedDiscount = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                        },
                                        updateFromPrice() {
                                            this.rawDiscount = this.formattedDiscount.replace(/,/g, '');
                                            this.calcPercent();
                                        },
                                        updateFromPercent() {
                                            let p = parseFloat(this.percent);
                                            let pr = parseFloat(this.rawPrice);
                                            if (p > 0 && p <= 100 && pr > 0) {
                                                let d = pr - (pr * (p / 100));
                                                this.rawDiscount = d.toString();
                                            } else {
                                                this.rawDiscount = '';
                                            }
                                        },
                                        calcPercent() {
                                            let pr = parseFloat(this.rawPrice);
                                            let d = parseFloat(this.rawDiscount);
                                            if (pr > 0 && d > 0 && d < pr) {
                                                this.percent = Math.round(((pr - d) / pr) * 100);
                                            } else {
                                                this.percent = '';
                                            }
                                        }
                                    }">
                                        <div>
                                            <label class="{{ $labelClass }} !text-rose-600 dark:!text-rose-400">درصد تخفیف</label>
                                            <div class="relative">
                                                <input type="number" x-model="percent" @input="updateFromPercent()" class="{{ $inputClass }} dir-ltr text-center !border-rose-200 focus:!border-rose-500 focus:!ring-rose-500/20 dark:!border-rose-800" placeholder="%">
                                                <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-rose-400">٪</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }} !text-rose-600 dark:!text-rose-400">قیمت نهایی</label>
                                            <input type="text" x-model="formattedDiscount" @input="updateFromPrice()" class="{{ $inputClass }} font-mono dir-ltr text-center font-bold text-rose-600 dark:text-rose-400 !border-rose-200 focus:!border-rose-500 focus:!ring-rose-500/20 dark:!border-rose-800" placeholder="قیمت با تخفیف">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 lg:col-span-2 gap-2 bg-white dark:bg-gray-800 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1 text-center">موجودی انبار</label>
                                            <input type="number" wire:model.defer="vendor_custom_variants.{{ $index }}.stock" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 dark:text-white rounded-lg text-center text-sm py-1.5 focus:ring-1 focus:ring-indigo-500 font-bold">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1 text-center text-nowrap">حداقل خرید</label>
                                            <input type="number" wire:model.defer="vendor_custom_variants.{{ $index }}.min_purchase" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 dark:text-white rounded-lg text-center text-sm py-1.5 focus:ring-1 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1 text-center text-nowrap">حداکثر خرید</label>
                                            <input type="number" wire:model.defer="vendor_custom_variants.{{ $index }}.max_purchase" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 dark:text-white rounded-lg text-center text-sm py-1.5 focus:ring-1 focus:ring-indigo-500" placeholder="نامحدود">
                                        </div>
                                    </div>
                                </div>

                                {{-- مدیریت زمان تخفیف تنوع دستی --}}
                                <div x-data="{ hasDiscount: @entangle('vendor_custom_variants.'.$index.'.discount_price') }" x-show="hasDiscount" x-collapse>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4 bg-rose-50/30 dark:bg-rose-900/5 p-4 rounded-xl border border-rose-100 dark:border-rose-800/50" wire:ignore>
                                        <div>
                                            <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">تاریخ شروع تخفیف</label>
                                            <input type="text" data-jdp-with-time wire:model.defer="vendor_custom_variants.{{ $index }}.discount_start_date" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800">
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">تاریخ پایان تخفیف</label>
                                            <input type="text" data-jdp-with-time wire:model.defer="vendor_custom_variants.{{ $index }}.discount_end_date" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800">
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">تعداد موجودی در تخفیف</label>
                                            <input type="number" wire:model.defer="vendor_custom_variants.{{ $index }}.discount_stock" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800" placeholder="تا اتمام موجودی کل">
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">حداکثر خرید در تخفیف</label>
                                            <input type="number" wire:model.defer="vendor_custom_variants.{{ $index }}.max_discount_purchase_qty" class="{{ $inputClass }} !border-rose-200 focus:!border-rose-500 dark:!border-rose-800" placeholder="نامحدود">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <label class="flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-800 px-4 py-2 rounded-xl border border-gray-100 dark:border-gray-700">
                                        <div class="relative flex items-center">
                                            <input type="checkbox" wire:model.defer="vendor_custom_variants.{{ $index }}.is_active" class="peer sr-only">
                                            <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                                        </div>
                                        <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300">نمایش کالا در سایت</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

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
    @includeIf('partials.jalali-date-picker')
</div>
