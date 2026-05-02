@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
@endphp

<div class="space-y-6 pb-10">
    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-fuchsia-50 dark:bg-fuchsia-900/30 flex items-center justify-center text-fuchsia-600 dark:text-fuchsia-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">مدیریت دسته‌بندی و فرم‌ساز</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ساختار سلسله مراتبی و تعیین فیلدهای اختصاصی</p>
            </div>
        </div>
        <button wire:click="openForm" class="inline-flex items-center gap-2 px-5 py-2.5 bg-fuchsia-600 text-white rounded-xl text-sm font-bold hover:bg-fuchsia-700 shadow-lg shadow-fuchsia-500/30 transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            ایجاد دسته جدید
        </button>
    </div>

    @if($isFormOpen)
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 sm:p-8 rounded-3xl shadow-xl shadow-gray-200/40 dark:shadow-none animate-in fade-in slide-in-from-top-4">
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-fuchsia-500"></span>
                    {{ $category_id ? 'ویرایش دسته‌بندی: ' . $name : 'ثبت دسته‌بندی جدید' }}
                </h2>
                <button wire:click="closeForm" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="{{ $labelClass }}">نام دسته <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="name" class="{{ $inputClass }}" placeholder="مثلاً: لپ‌تاپ">
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $labelClass }}">دسته والد (زیرمجموعهِ...)</label>
                    <select wire:model.defer="parent_id" class="{{ $inputClass }}">
                        <option value="">-- دسته اصلی (بدون والد) --</option>
                        {{-- 💡 برای تمیزی دراپ‌داون، فاصله‌های بصری اعمال شد --}}
                        @foreach($parentCategories->whereNull('parent_id') as $mainCat)
                            @if($mainCat->id !== $category_id)
                                <option value="{{ $mainCat->id }}" class="font-bold">{{ $mainCat->name }}</option>
                                @foreach($mainCat->children as $subCat)
                                    @if($subCat->id !== $category_id)
                                        <option value="{{ $subCat->id }}">&nbsp;&nbsp;↳ {{ $subCat->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">آفست سیستمی (Offset Code) <span class="text-red-500">*</span></label>
                    <input type="number" wire:model.defer="code_offset" class="{{ $inputClass }} bg-gray-100 dark:bg-gray-900 text-center font-mono font-bold text-gray-500 cursor-not-allowed" readonly>
                </div>
            </div>

            {{-- فرم‌ساز داینامیک ویژگی‌ها (بدون تغییر) --}}
            <div class="bg-gray-50 dark:bg-gray-900/30 p-6 rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            فیلدهای اختصاصی فرم محصول (Attributes)
                        </h3>
                    </div>
                    <button wire:click="addAttribute" class="inline-flex items-center gap-2 text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 px-4 py-2 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors shadow-sm font-bold">
                        + افزودن فیلد
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse($target_attributes as $index => $attr)
                        <div class="flex items-center gap-3 bg-white dark:bg-gray-800 p-2 pl-3 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm group">
                            <input type="text" wire:model.defer="target_attributes.{{ $index }}" placeholder="عنوان ویژگی (مثلاً: ابعاد صفحه نمایش)" class="flex-1 border-0 focus:ring-0 text-sm bg-transparent dark:text-white px-2">
                            <button wire:click="removeAttribute({{ $index }})" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    @empty
                        <div class="text-center py-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">هیچ فیلد اختصاصی تعریف نشده است.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- بخش جدید: محورهای تنوع (Variant Axes) --}}
            <div class="bg-fuchsia-50 dark:bg-fuchsia-900/10 p-6 rounded-2xl border border-fuchsia-100 dark:border-fuchsia-800/30 mt-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-fuchsia-200 dark:border-fuchsia-700">
                    <div>
                        <h3 class="text-sm font-bold text-fuchsia-800 dark:text-fuchsia-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                            ویژگی‌های تنوع‌ساز (Variant Axes)
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">ویژگی‌هایی که باعث اختلاف قیمت می‌شوند (مثلاً: رنگ، سایز، گارانتی).</p>
                    </div>
                    <button wire:click="addVariantField" class="text-xs bg-white dark:bg-gray-800 border border-fuchsia-200 text-fuchsia-600 px-4 py-2 rounded-xl font-bold shadow-sm">+ افزودن محور تنوع</button>
                </div>

                <div class="space-y-3">
                    @foreach($variant_fields as $index => $vField)
                        <div class="flex items-center gap-3 bg-white dark:bg-gray-800 p-2 rounded-xl border border-fuchsia-100 dark:border-fuchsia-800 shadow-sm">
                            <input type="text" wire:model.defer="variant_fields.{{ $index }}" placeholder="مثلاً: رنگ یا ظرفیت حافظه" class="flex-1 border-0 focus:ring-0 text-sm bg-transparent dark:text-white">
                            <button wire:click="removeVariantField({{ $index }})" class="p-2 text-red-400 hover:text-red-500 transition-colors"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button wire:click="save" class="px-8 py-2.5 bg-fuchsia-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-fuchsia-500/30 hover:bg-fuchsia-700 transition-all active:scale-95">ذخیره دسته‌بندی</button>
            </div>
        </div>
    @endif

    {{-- لیست درختی جدید و شیک --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden p-4 sm:p-6">
        @if($categoriesTree->count() > 0)
            <div class="space-y-2">
                @foreach($categoriesTree as $mainCategory)
                    @include('market::livewire.admin.partials.category-tree-item', ['category' => $mainCategory])
                @endforeach
            </div>
        @else
            <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                هیچ دسته‌بندی تاکنون تعریف نشده است.
            </div>
        @endif
    </div>
</div>
