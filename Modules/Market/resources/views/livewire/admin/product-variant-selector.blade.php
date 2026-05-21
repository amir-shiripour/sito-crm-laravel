@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800";
@endphp

<div>
    @if(!$selectedMasterProduct)
        <div>
            <label for="product_search_selector" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">جستجوی محصول اصلی</label>
            <input type="text" id="product_search_selector" class="{{ $inputClass }}" wire:model.live.debounce.300ms="searchQuery" placeholder="نام یا کد محصول اصلی را جستجو کنید...">
        </div>

        @if(!empty($this->searchResults))
            <div class="mt-4 max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                @foreach($this->searchResults as $product)
                    <div wire:click="selectProduct({{ $product->id }})" class="flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $product->title }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">- {{ $product->crm_code }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <span class="font-medium text-gray-900 dark:text-white">محصول انتخاب شده: {{ $selectedMasterProduct->title }}</span>
            <button wire:click="clearSelection" class="text-red-600 hover:text-red-800 text-sm">تغییر</button>
        </div>

        <div class="mt-4">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">انتخاب تنوع محصول</label>
            @if($selectedMasterProduct->variants->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">این محصول تنوعی ندارد.</p>
            @else
                <div class="max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    @foreach($selectedMasterProduct->variants as $variant)
                        <label class="flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                            <input type="radio" name="selected_variant_id" wire:model.live="selectedVariantId" value="{{ $variant->id }}" class="text-indigo-600 focus:ring-indigo-500">
                            <span>{{ $variant->name }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
