@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 overflow-hidden";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800";
    $checkboxClass = "w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer transition-colors";
@endphp

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">موجودی انبار: {{ $warehouse->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت و تعدیل موجودی محصولات در این انبار</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openAddProductModal" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                افزودن محصول به انبار
            </button>
            <a href="{{ route('user.market.warehouses.index') }}" class="px-4 py-2.5 rounded-xl bg-gray-200 text-gray-700 font-bold shadow-sm hover:bg-gray-300 transition-all transform active:scale-95 flex items-center gap-2 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                بازگشت
            </a>
        </div>
    </div>

    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="relative w-full max-w-md">
                <input type="text" class="{{ $inputClass }} pl-10" placeholder="جستجو در عنوان محصول..." wire:model.live.debounce.300ms="search">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">محصول (واریانت)</th>
                        <th scope="col" class="px-6 py-3">فروشنده</th>
                        <th scope="col" class="px-6 py-3">موجودی فیزیکی</th>
                        <th scope="col" class="px-6 py-3">موجودی رزرو شده</th>
                        <th scope="col" class="px-6 py-3">موجودی قابل فروش</th>
                        <th scope="col" class="px-6 py-3 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        @php
                            $masterProduct = $stock->vendorProduct->masterProduct ?? $stock->productVariant->masterProduct;
                        @endphp
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $masterProduct->title ?? 'N/A' }} ({{ $stock->productVariant->name ?? 'N/A' }})
                            </th>
                            <td class="px-6 py-4">{{ $stock->vendorProduct->vendor->store_name ?? 'مرکزی' }}</td>
                            <td class="px-6 py-4 font-mono">{{ $stock->physical_stock }}</td>
                            <td class="px-6 py-4 font-mono">{{ $stock->reserved_stock }}</td>
                            <td class="px-6 py-4 font-mono">{{ $stock->physical_stock - $stock->reserved_stock }}</td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="edit({{ $stock->id }})" class="font-medium text-amber-600 dark:text-amber-500 hover:underline">تعدیل موجودی</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">انباری خالی از محصول</h3>
                                <p class="mt-1 text-sm text-gray-500">با کلیک روی دکمه "افزودن محصول" شروع کنید.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>

    {{-- Modal: Adjust Stock --}}
    @if($isModalOpen)
        <div class="fixed inset-0 bg-gray-900/80 z-40" x-on:click="$dispatch('close-modal')"></div>
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md m-4" @click.away="$dispatch('close-modal')">
                <form wire:submit.prevent="adjustStock">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">تعدیل موجودی</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300">موجودی فیزیکی فعلی: <strong class="font-mono">{{ $physical_stock }}</strong></p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">موجودی رزرو شده فعلی: <strong class="font-mono">{{ $reserved_stock }}</strong></p>
                        <div>
                            <label for="adjustment_quantity" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">مقدار تعدیل</label>
                            <input type="number" id="adjustment_quantity" class="{{ $inputClass }} font-mono" wire:model="adjustment_quantity" placeholder="مثال: 10 برای افزایش، -5 برای کاهش">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">برای افزایش موجودی عدد مثبت و برای کاهش عدد منفی وارد کنید.</p>
                            @error('adjustment_quantity') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="adjustment_description" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">دلیل تعدیل</label>
                            <textarea id="adjustment_description" class="{{ $inputClass }}" wire:model="adjustment_description" rows="3"></textarea>
                            @error('adjustment_description') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/30 rounded-b-2xl flex items-center justify-end gap-4">
                        <button type="button" x-on:click="$dispatch('close-modal')" class="px-4 py-2 rounded-lg text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">انصراف</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 text-white font-bold shadow-lg shadow-amber-500/30 hover:bg-amber-700 transition-all transform active:scale-95 flex items-center gap-2">
                            <span wire:loading.remove wire:target="adjustStock">ثبت تعدیل</span>
                            <span wire:loading wire:target="adjustStock">در حال پردازش...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Add Product --}}
    @if($isAddProductModalOpen)
        <div class="fixed inset-0 bg-gray-900/80 z-40" x-on:click="$dispatch('close-modal')"></div>
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg m-4" @click.away="$dispatch('close-modal')">
                <form wire:submit.prevent="addProductToStock">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">افزودن محصول به انبار</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- 💡 فراخوانی کامپوننت ProductVariantSelector --}}
                        @livewire('market::admin.product-variant-selector')

                        @if($selectedVariantId)
                            <div class="grid grid-cols-2 gap-4 pt-4">
                                <div>
                                    <label for="initial_stock" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">موجودی اولیه</label>
                                    <input type="number" id="initial_stock" class="{{ $inputClass }} font-mono" wire:model.live="initialStock">
                                    @error('initialStock') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="initial_stock_description" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">توضیحات (مثلا: شمارش اولیه)</label>
                                    <input type="text" id="initial_stock_description" class="{{ $inputClass }}" wire:model="initialStockDescription">
                                    @error('initialStockDescription') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/30 rounded-b-2xl flex items-center justify-end gap-4">
                        <button type="button" x-on:click="$dispatch('close-modal')" class="px-4 py-2 rounded-lg text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">انصراف</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center gap-2"
                            @if(empty($selectedVariantId) || !isset($initialStock) || $initialStock === '') disabled @endif>
                            <span wire:loading.remove wire:target="addProductToStock">افزودن</span>
                            <span wire:loading wire:target="addProductToStock">در حال پردازش...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
