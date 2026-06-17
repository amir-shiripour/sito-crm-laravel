@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm transition-all duration-300";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800";
    $checkboxClass = "w-5 h-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer transition-colors";
@endphp

<div class="space-y-6 pb-24">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                مدیریت انبارها
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت انبارهای مرکزی و نمایندگی‌ها (فروشندگان)</p>
        </div>
        <button wire:click="create()" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            ایجاد انبار جدید
        </button>
    </div>

    {{-- Tabs & Filters --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white dark:bg-gray-800 p-3 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex bg-gray-50 dark:bg-gray-900/50 p-1 rounded-xl w-full md:w-auto">
            <button wire:click="setFilter('central')" class="flex-1 md:flex-none px-5 py-2 rounded-lg text-sm font-bold transition-all {{ $filterType === 'central' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                انبار مرکزی سیستم
            </button>
            <button wire:click="setFilter('vendors')" class="flex-1 md:flex-none px-5 py-2 rounded-lg text-sm font-bold transition-all {{ $filterType === 'vendors' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                انبارهای فروشندگان
            </button>
            <button wire:click="setFilter('all')" class="flex-1 md:flex-none px-5 py-2 rounded-lg text-sm font-bold transition-all {{ $filterType === 'all' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                همه موارد
            </button>
        </div>

        <div class="relative w-full md:w-80">
            <input type="text" class="{{ $inputClass }} pl-10" placeholder="جستجو در انبارها..." wire:model.live.debounce.300ms="search">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>
    </div>

    {{-- Warehouse List Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($warehouses as $warehouse)
            <div class="{{ $cardClass }} p-5 flex flex-col justify-between hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md">
                <div>
                    <div class="flex items-start justify-between border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $warehouse->vendor_id ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-base">{{ $warehouse->name }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">کد: <span class="font-bold">{{ $warehouse->code }}</span></p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg {{ $warehouse->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-400' }}">
                            {{ $warehouse->is_active ? 'فعال' : 'غیرفعال' }}
                        </span>
                    </div>

                    <div class="space-y-3 mb-6">
                        @if($isMultiVendor)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">مالک انبار:</span>
                                @if($warehouse->vendor_id)
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $warehouse->vendor->store_name ?? 'نامشخص' }}</span>
                                @else
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded-lg text-xs">سیستم (مرکزی)</span>
                                @endif
                            </div>
                        @endif
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">تاریخ ایجاد:</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ \Morilog\Jalali\Jalalian::fromCarbon($warehouse->created_at)->format('Y/m/d') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('user.market.warehouse-stock.index', ['selectedWarehouseId' => $warehouse->id, 'warehouseFilter' => $warehouse->vendor_id ? 'vendors' : 'central', 'currentTab' => 'stock']) }}" class="flex-1 text-center py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 dark:text-indigo-400 rounded-xl text-xs font-bold transition-colors">
                        مدیریت موجودی
                    </a>
                    <button wire:click="edit({{ $warehouse->id }})" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 dark:bg-gray-700/50 dark:hover:bg-gray-700 dark:text-gray-300 rounded-xl transition-colors tooltip" title="ویرایش">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </button>
                    <button wire:click="delete({{ $warehouse->id }})" wire:confirm="آیا از حذف این انبار مطمئن هستید؟" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 dark:text-rose-400 rounded-xl transition-colors tooltip" title="حذف">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="w-20 h-20 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">هیچ انباری یافت نشد!</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">برای شروع، یک انبار جدید در سیستم تعریف کنید.</p>
            </div>
        @endforelse
    </div>

    @if($warehouses->hasPages())
        <div class="mt-6">
            {{ $warehouses->links() }}
        </div>
    @endif

    {{-- Modal --}}
    @if($isModalOpen)
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40 transition-opacity" x-on:click="$dispatch('close-modal')"></div>
        <div class="fixed inset-0 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md animate-in zoom-in-95 duration-200" @click.away="$dispatch('close-modal')">
                <form wire:submit.prevent="store">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            </div>
                            {{ $warehouseId ? 'ویرایش انبار' : 'ایجاد انبار جدید' }}
                        </h3>
                        <button type="button" x-on:click="$dispatch('close-modal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-5">
                        @if($isMultiVendor)
                            <div>
                                <label for="vendor_id" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">مالک انبار</label>
                                <select id="vendor_id" class="{{ $inputClass }}" wire:model="vendor_id">
                                    <option value="">سیستم (انبار مرکزی)</option>
                                    @if($adminVendor)
                                        <option value="{{ $adminVendor->id }}">فروشگاه اختصاصی ادمین ({{ $adminVendor->store_name }})</option>
                                    @endif
                                    <optgroup label="سایر فروشندگان">
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->store_name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                @error('vendor_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        
                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">نام انبار</label>
                            <input type="text" id="name" class="{{ $inputClass }}" wire:model="name" placeholder="مثال: انبار غرب تهران">
                            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="code" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">کد انبار</label>
                            <input type="text" id="code" class="{{ $inputClass }} bg-gray-100 dark:bg-gray-700/50" wire:model="code" readonly placeholder="به صورت خودکار تولید می‌شود...">
                            <p class="text-[11px] text-gray-400 mt-1.5">این کد به صورت سیستمی و یکتا تولید می‌شود.</p>
                        </div>
                        
                        <div class="pt-2 bg-gray-50 dark:bg-gray-900/30 p-3 rounded-xl border border-gray-100 dark:border-gray-700/50">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" id="is_active" class="{{ $checkboxClass }}" wire:model="is_active">
                                <div>
                                    <span class="block text-sm font-bold text-gray-700 dark:text-gray-300">انبار فعال باشد</span>
                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">در صورت غیرفعال بودن، عملیات انبارداری متوقف می‌شود.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="p-5 bg-gray-50 dark:bg-gray-900/50 rounded-b-3xl flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" x-on:click="$dispatch('close-modal')" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">انصراف</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center gap-2">
                            <span wire:loading.remove wire:target="store">ذخیره اطلاعات انبار</span>
                            <span wire:loading wire:target="store">در حال ذخیره...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
