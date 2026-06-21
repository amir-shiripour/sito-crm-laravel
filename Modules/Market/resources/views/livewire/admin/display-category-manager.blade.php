@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
@endphp

<div class="space-y-6 pb-10">
    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">مدیریت دسته‌بندی مجزا (نمایشی)</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ساختار درختی دسته‌بندی‌های نمایشی برای نمایش به کاربران</p>
            </div>
        </div>
        <button wire:click="openForm" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            ایجاد دسته جدید
        </button>
    </div>

    @if($isFormOpen)
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 sm:p-8 rounded-3xl shadow-xl shadow-gray-200/40 dark:shadow-none animate-in fade-in slide-in-from-top-4">
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    {{ $category_id ? 'ویرایش دسته‌بندی مجزا: ' . $name : 'ثبت دسته‌بندی مجزا جدید' }}
                </h2>
                <button wire:click="closeForm" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                {{-- آیکون دسته نمایشی --}}
                <div class="md:col-span-1">
                    <label class="{{ $labelClass }}">آیکون دسته</label>
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 border-gray-300 dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 overflow-hidden relative transition-colors">
                        @if($icon && !is_string($icon))
                            <img src="{{ $icon->temporaryUrl() }}" class="w-full h-full object-contain p-2">
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity"><span class="text-white text-xs font-bold">تغییر آیکون</span></div>
                        @elseif($existing_icon)
                            <img src="{{ Storage::url($existing_icon) }}" class="w-full h-full object-contain p-2">
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity"><span class="text-white text-xs font-bold">تغییر آیکون</span></div>
                        @else
                            <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            <p class="text-[10px] text-gray-500 text-center px-2">برای آپلود آیکون کلیک کنید</p>
                        @endif
                        <input type="file" wire:model="icon" class="hidden" accept="image/*" />
                    </label>
                    @error('icon') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">نام دسته <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="name" class="{{ $inputClass }}" placeholder="مثلاً: پوشاک">
                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- دراپ‌داون کاستوم و حرفه‌ای برای دسته والد --}}
                    <div class="relative" x-data="{
                        open: false,
                        selected: @entangle('parent_id').live,
                        options: @entangle('parentOptions'),
                        get selectedLabel() {
                            let opt = (this.options || []).find(o => o.value == this.selected);
                            return opt ? opt.label : '-- دسته اصلی (بدون والد) --';
                        }
                    }" @click.away="open = false">
                        <label class="{{ $labelClass }}">دسته والد (زیرمجموعهِ...)</label>

                        {{-- دکمه تریگر --}}
                        <div @click="open = !open" class="{{ $inputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-900': open, 'bg-gray-50 dark:bg-gray-900/50': !open}">
                            <span x-text="selectedLabel" class="block truncate font-bold text-gray-800 dark:text-gray-200" :class="{'!text-gray-500 dark:!text-gray-400 font-normal': selected === '' || selected == null}"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>

                        {{-- لیست کشویی --}}
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2"
                             style="display: none;">
                            <template x-for="option in options" :key="option.value">
                                <div @click="selected = option.value; open = false"
                                     class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group"
                                     :class="{
                                        'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': selected == option.value,
                                        'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': selected != option.value,
                                        'text-sm border-r-2 border-transparent': option.isSub,
                                        'border-indigo-500 dark:border-indigo-400': option.isSub && selected == option.value
                                     }"
                                     :style="option.isSub ? 'padding-right: ' + (option.depth * 1.25 + 0.75) + 'rem' : ''">
                                    <span x-show="option.isSub" class="text-gray-300 dark:text-gray-600 group-hover:text-indigo-400 transition-colors">↳</span>
                                    <span x-text="option.label"></span>
                                    <svg x-show="selected == option.value" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button wire:click="save" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95">ذخیره دسته‌بندی</button>
            </div>
        </div>
    @endif

    {{-- لیست درختی جدید و شیک --}}
    <div class="space-y-6">
        @if($categoriesTree->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 sm:p-6 space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">لیست دسته‌بندی‌های مجزا</h3>
                        <p class="text-[10px] text-gray-400">سلسله مراتب درختی دسته‌های نمایشی</p>
                    </div>
                </div>
                <div class="space-y-2">
                    @foreach($categoriesTree as $category)
                        @include('market::livewire.admin.partials.display-category-tree-item', ['category' => $category])
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 p-10 text-center text-gray-500 dark:text-gray-400">
                هیچ دسته‌بندی مجزایی تاکنون تعریف نشده است.
            </div>
        @endif
    </div>
</div>
