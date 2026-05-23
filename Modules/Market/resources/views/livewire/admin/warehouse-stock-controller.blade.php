@php
    $inputClass = "w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-mono text-center focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white transition-all";
    $labelClass = "block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1";
@endphp

<div class="space-y-4 pb-24">
    {{-- Header & Filters --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                انبارگردانی و مدیریت موجودی
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ویرایش سریع موجودی فیزیکی و آنلاین محصولات در انبارهای مرتبط</p>
        </div>
    </div>

    {{-- Search & Tabs --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="lg:col-span-2 relative w-full">
            <input type="text" class="w-full rounded-xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white pl-10" placeholder="جستجو در عنوان محصول، ویژگی‌ها یا کد تنوع..." wire:model.live.debounce.300ms="search">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>
        <div class="flex items-center p-1 bg-gray-100/50 dark:bg-gray-800/50 rounded-xl border border-gray-200/50 dark:border-gray-700/50">
            <button wire:click="setStockStatusFilter('all')" class="{{ $stockStatusFilter === 'all' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm transition-all text-center">همه محصولات</button>
            <button wire:click="setStockStatusFilter('in_stock')" class="{{ $stockStatusFilter === 'in_stock' ? 'bg-white dark:bg-gray-700 shadow-sm text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm transition-all text-center">دارای موجودی</button>
            <button wire:click="setStockStatusFilter('out_of_stock')" class="{{ $stockStatusFilter === 'out_of_stock' ? 'bg-white dark:bg-gray-700 shadow-sm text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm transition-all text-center">نیازمند شارژ</button>
        </div>
    </div>

    {{-- Product List --}}
    <div class="space-y-4">
        @forelse($this->products as $variant)
            @php
                $totalPhysicalStock = $variant->total_physical_stock ?? 0;
                $totalOnlineStock = $variant->total_online_stock ?? 0;
                $totalReservedStock = $variant->total_reserved_stock ?? 0;
                $overallStock = $totalPhysicalStock + $totalOnlineStock;
                $isNewForVendor = !$variant->vendor_product_id;
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-300 {{ $editingVariantId === $variant->id ? 'ring-2 ring-indigo-500/50' : '' }}">

                {{-- Product Card Header (Always Visible) --}}
                <div wire:click="toggleEditStock({{ $variant->id }})" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0">
                            <img src="{{ $variant->masterProduct->main_image_url }}" alt="{{ $variant->masterProduct->title }}" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base">{{ $variant->masterProduct->title }}</h3>
                            <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                @if(!empty($variant->variant_attributes))
                                    @foreach($variant->variant_attributes as $attrName => $attrValue)
                                        <span class="text-[10px] font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $attrName }}: {{ $attrValue }}</span>
                                    @endforeach
                                @else
                                    <span class="text-[10px] font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">استاندارد</span>
                                @endif
                                <span class="text-[10px] font-mono bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 px-2 py-0.5 rounded">{{ $variant->variant_code }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto mt-2 sm:mt-0 pt-2 sm:pt-0 border-t sm:border-0 border-gray-100 dark:border-gray-700">
                        <div class="flex gap-4 sm:gap-6">
                            <div class="flex flex-col items-center sm:items-end">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">فیزیکی کل</span>
                                <span class="font-mono font-bold text-sm text-gray-700 dark:text-gray-200">{{ $totalPhysicalStock }}</span>
                            </div>
                            <div class="flex flex-col items-center sm:items-end">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">آنلاین کل</span>
                                <span class="font-mono font-bold text-sm text-gray-700 dark:text-gray-200">{{ $totalOnlineStock }}</span>
                            </div>
                            <div class="flex flex-col items-center sm:items-end">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">وضعیت</span>
                                @if($isNewForVendor)
                                    <span class="text-xs font-bold text-sky-500 dark:text-sky-400 mt-0.5">تنوع جدید</span>
                                @else
                                    <div class="flex items-center gap-1 mt-1">
                                        <div class="w-2 h-2 rounded-full {{ $overallStock > 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                        <span class="text-xs font-bold {{ $overallStock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $overallStock > 0 ? 'موجود' : 'ناموجود' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="p-1 text-gray-400 dark:text-gray-500 transform transition-transform duration-300 {{ $editingVariantId === $variant->id ? 'rotate-180 text-indigo-500' : '' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>

                {{-- Accordion Body (Edit Stocks) --}}
                @if($editingVariantId === $variant->id)
                    <div class="bg-indigo-50/30 dark:bg-gray-900/30 border-t border-indigo-100 dark:border-gray-700 p-4 animate-in slide-in-from-top-2">

                        @if($isNewForVendor)
                            <div class="mb-4 p-3 bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800/50 rounded-xl flex items-start gap-3">
                                <svg class="w-5 h-5 text-sky-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <p class="text-sm font-bold text-sky-800 dark:text-sky-400">این تنوع برای شما جدید است.</p>
                                    <p class="text-xs text-sky-600 dark:text-sky-500 mt-1">با وارد کردن موجودی و ذخیره، این تنوع به لیست محصولات شما اضافه شده و در انبار ثبت می‌شود.</p>
                                </div>
                            </div>
                        @endif

                        <form wire:submit.prevent="saveStocksForVariant({{ $variant->id }})">
                            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                @foreach($this->relevantWarehouses as $warehouse)
                                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-3 flex flex-col gap-3 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                        <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                            <span class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $warehouse->name }}</span>
                                            <span class="text-[9px] font-mono text-gray-400 mr-auto">{{ $warehouse->code }}</span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="{{ $labelClass }}">فیزیکی (قفسه)</label>
                                                <input type="number" wire:model.defer="editableStocks.{{ $warehouse->id }}.physical_stock" class="{{ $inputClass }}">
                                                @error("editableStocks.{$warehouse->id}.physical_stock") <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">آنلاین (سایت)</label>
                                                <input type="number" wire:model.defer="editableStocks.{{ $warehouse->id }}.online_stock" class="{{ $inputClass }} text-indigo-700 dark:text-indigo-400">
                                                @error("editableStocks.{$warehouse->id}.online_stock") <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center text-[10px] text-gray-500 pt-1">
                                            <span>رزرو شده: <span class="font-bold font-mono">{{ $variant->warehouseStocks->firstWhere('warehouse_id', $warehouse->id)->reserved_stock ?? 0 }}</span></span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center justify-end gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button type="button" wire:click="cancelEdit" class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">بستن</button>
                                <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-xs font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                                    <span wire:loading.remove wire:target="saveStocksForVariant({{ $variant->id }})">
                                        {{ $isNewForVendor ? 'اتصال و ثبت موجودی' : 'ذخیره تغییرات موجودی' }}
                                    </span>
                                    <span wire:loading wire:target="saveStocksForVariant({{ $variant->id }})">در حال ثبت...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-10 text-center border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center">
                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">محصولی یافت نشد!</h3>
                <p class="text-sm text-gray-500 mt-1 max-w-sm">با توجه به فیلترها و کلمات جستجو شده، هیچ محصول یا تنوعی برای نمایش وجود ندارد.</p>
            </div>
        @endforelse
    </div>

    @if($this->products->hasPages())
        <div class="mt-6">
            {{ $this->products->links() }}
        </div>
    @endif
</div>
