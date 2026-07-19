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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                {{-- آیکون دسته --}}
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

                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">نام دسته <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="name" class="{{ $inputClass }}" placeholder="مثلاً: لپ‌تاپ">
                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @php
                        $brandOptions = [];
                        $brandOptions[] = ['value' => '', 'label' => '-- بدون برند --'];
                        foreach($brands as $brand) {
                            $brandOptions[] = ['value' => (string)$brand->id, 'label' => $brand->name];
                        }
                    @endphp

                    {{-- دراپ‌داون کاستوم و حرفه‌ای برای برند (بیاید اول) --}}
                    <div>
                        <label class="{{ $labelClass }}">اتصال به برند (انتخابی)</label>
                        <div class="relative" x-data="{
                            open: false,
                            selected: @entangle('brand_id').live,
                            isLocked: @entangle('has_parent_brand'),
                            options: {{ json_encode($brandOptions) }},
                            get selectedLabel() {
                                let opt = this.options.find(o => o.value == this.selected);
                                return opt ? opt.label : '-- بدون برند --';
                            }
                        }" @click.away="open = false">
                            
                            {{-- دکمه تریگر --}}
                            <div @click="!isLocked ? open = !open : null" 
                                 class="{{ $inputClass }} flex justify-between items-center transition-colors select-none" 
                                 :class="{
                                    'bg-gray-100 dark:bg-gray-900/30 opacity-70 cursor-not-allowed text-gray-500': isLocked, 
                                    'cursor-pointer ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-900': open && !isLocked, 
                                    'bg-gray-50 dark:bg-gray-900/50 cursor-pointer': !open && !isLocked
                                 }">
                                <span x-text="selectedLabel" class="block truncate font-bold text-gray-800 dark:text-gray-200" :class="{'!text-gray-500 dark:!text-gray-400 font-normal': selected === '' || selected == null}"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open && !isLocked}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>

                            {{-- لیست کشویی --}}
                            <div x-show="open && !isLocked"
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
                                            'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': selected != option.value
                                         }">
                                        <span x-text="option.label"></span>
                                        <svg x-show="selected == option.value" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </template>
                            </div>
                            <template x-if="isLocked">
                                <span class="text-[10px] text-amber-600 dark:text-amber-400 mt-1 block">💡 برند از والد به ارث رسیده است.</span>
                            </template>
                        </div>
                        @error('brand_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- دراپ‌داون کاستوم و حرفه‌ای برای دسته والد (بعد از برند بیاید) --}}
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

                    <div class="md:col-span-3">
                        <label class="{{ $labelClass }}">آفست سیستمی (Offset Code) <span class="text-red-500">*</span></label>
                        <input type="number" wire:model.defer="code_offset" class="{{ $inputClass }} bg-gray-100 dark:bg-gray-900 text-center font-mono font-bold text-gray-500 cursor-not-allowed" readonly>
                    </div>
                </div>
            </div>

            {{-- فرم‌ساز داینامیک ویژگی‌ها --}}
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

            {{-- 💡 پردازش ویژگی‌های سراسری --}}
            @php
                $globalAttrOptions = [];
                $globalAttrOptions[] = ['value' => '', 'label' => 'انتخاب ویژگی سراسری...'];
                foreach($globalAttributes as $gAttr) {
                    $label = $gAttr->name . ($gAttr->unit ? " ({$gAttr->unit})" : "");
                    $globalAttrOptions[] = ['value' => (string)$gAttr->id, 'label' => $label];
                }
            @endphp

            {{-- بخش آپدیت شده: محورهای تنوع (Variant Axes) --}}
            <div class="bg-fuchsia-50 dark:bg-fuchsia-900/10 p-6 rounded-2xl border border-fuchsia-100 dark:border-fuchsia-800/30 mt-6"
                 x-data="{
                    // همه انتخاب‌های فعلی را برای دسترسی کلی ذخیره می‌کنیم
                    variantFields: @entangle('variant_fields'),
                    // متدی برای بررسی اینکه آیا یک گزینه قبلاً انتخاب شده یا نه
                    isOptionSelected(value, currentIndex) {
                        if(value === '') return false;
                        for(let i in this.variantFields) {
                             if(i != currentIndex && String(this.variantFields[i]) === String(value)) {
                                 return true;
                             }
                        }
                        return false;
                    }
                 }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-fuchsia-200 dark:border-fuchsia-800/50">
                    <div>
                        <h3 class="text-sm font-bold text-fuchsia-800 dark:text-fuchsia-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                            ویژگی‌های تنوع‌ساز (Variant Axes)
                        </h3>
                        <p class="text-xs text-fuchsia-600/70 dark:text-fuchsia-400/70 mt-1">ویژگی‌هایی که باعث اختلاف قیمت می‌شوند. هر ویژگی را فقط یک بار می‌توانید انتخاب کنید.</p>
                    </div>
                    <button wire:click="addVariantField" class="inline-flex items-center gap-1.5 text-xs bg-white dark:bg-gray-800 border border-fuchsia-200 dark:border-fuchsia-700/50 text-fuchsia-600 dark:text-fuchsia-400 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-900/30 px-4 py-2 rounded-xl font-bold shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        افزودن محور تنوع
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach($variant_fields as $index => $vField)
                        <div class="flex items-center bg-white dark:bg-gray-800 p-1.5 rounded-xl border border-fuchsia-100 dark:border-fuchsia-800/60 shadow-sm focus-within:ring-2 focus-within:ring-fuchsia-500/20 focus-within:border-fuchsia-300 dark:focus-within:border-fuchsia-600 transition-all group">

                            {{-- آیکون تزئینی کنار انتخابگر --}}
                            <div class="w-9 h-9 rounded-lg bg-fuchsia-50 dark:bg-fuchsia-900/20 flex items-center justify-center text-fuchsia-500 dark:text-fuchsia-400 flex-shrink-0 ml-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            </div>

                            {{-- دراپ‌داون کاستوم برای ویژگی‌های سراسری --}}
                            <div x-data="{
                                open: false,
                                selected: @entangle('variant_fields.'.$index),
                                options: {{ json_encode($globalAttrOptions) }},
                                currentIndex: {{ $index }},
                                showWarning: false,
                                get selectedLabel() {
                                    let opt = this.options.find(o => o.value == this.selected);
                                    return opt ? opt.label : 'انتخاب ویژگی سراسری...';
                                },
                                // متد جدید برای انتخاب ویژگی با جلوگیری از تکرار
                                selectOption(val) {
                                    if(val === '') {
                                        this.selected = val;
                                        this.open = false;
                                        return;
                                    }

                                    // بررسی تکراری بودن با استفاده از متد کانتینر والد
                                    if(this.isOptionSelected(val, this.currentIndex)) {
                                        this.showWarning = true;
                                        setTimeout(() => {
                                            this.showWarning = false;
                                        }, 2000);
                                        return; // اگر تکراری بود انتخاب انجام نمیشه
                                    }

                                    this.selected = val;
                                    this.open = false;
                                }
                            }" @click.away="open = false" class="relative flex-1">

                                {{-- دکمه تریگر --}}
                                <div @click="open = !open" class="w-full border-0 focus:ring-0 text-sm bg-transparent dark:text-gray-200 cursor-pointer flex justify-between items-center py-2 px-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors select-none" :class="{'bg-gray-50 dark:bg-gray-700/50': open}">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        <span x-text="selectedLabel" class="font-bold truncate text-gray-800 dark:text-gray-200" :class="{'font-normal text-gray-400 dark:text-gray-500': selected === '' || selected == null}"></span>
                                        <span x-show="showWarning" x-transition class="text-xs text-red-500 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded animate-pulse whitespace-nowrap">این ویژگی قبلاً انتخاب شده!</span>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-fuchsia-500': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>

                                {{-- لیست کشویی --}}
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-fuchsia-100 dark:border-fuchsia-800/60 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2 right-0"
                                     style="display: none;">
                                    <template x-for="option in options" :key="option.value">
                                        <div @click="selectOption(option.value)"
                                             class="px-4 py-2.5 transition-all flex items-center justify-between group"
                                             :class="{
                                                'bg-fuchsia-50 dark:bg-fuchsia-900/40 text-fuchsia-700 dark:text-fuchsia-300 font-bold': selected == option.value,
                                                'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer': selected != option.value && !isOptionSelected(option.value, currentIndex),
                                                'opacity-40 cursor-not-allowed bg-gray-50 dark:bg-gray-800': selected != option.value && isOptionSelected(option.value, currentIndex)
                                             }">
                                            <div class="flex items-center gap-2">
                                                <span x-text="option.label"></span>
                                                {{-- نشانگر برای مواردی که در ردیف دیگری انتخاب شده‌اند --}}
                                                <span x-show="selected != option.value && isOptionSelected(option.value, currentIndex)" class="text-[10px] bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-1.5 rounded">انتخاب شده</span>
                                            </div>
                                            <svg x-show="selected == option.value" class="w-4 h-4 text-fuchsia-600 dark:text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <button wire:click="removeVariantField({{ $index }})" class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:text-rose-400 dark:hover:bg-rose-900/20 rounded-lg transition-colors flex-shrink-0" title="حذف این محور">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
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
    <div class="space-y-6">
        @php
            // جدا کردن دسته‌های بدون برند و گروه‌بندی دسته‌های دارای برند بر اساس brand_id
            $noBrandCategories = $categoriesTree->filter(fn($c) => is_null($c->brand_id));
            $brandedCategories = $categoriesTree->filter(fn($c) => !is_null($c->brand_id));
            $groupedByBrand = $brandedCategories->groupBy('brand_id');
        @endphp

        @if($categoriesTree->count() > 0)
            {{-- دسته‌بندی‌های دارای برند (هر برند به صورت گروه جداگانه) --}}
            @foreach($groupedByBrand as $brandId => $cats)
                @php $brandObj = $cats->first()->brand; @endphp
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden p-5 sm:p-6 space-y-4" x-data="{ expanded: false }">
                    <div @click="expanded = !expanded" class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50 cursor-pointer select-none group/hdr">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover/hdr:scale-105 transition-transform">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white group-hover/hdr:text-indigo-600 dark:group-hover/hdr:text-indigo-450 transition-colors">گروه برند: {{ $brandObj?->name ?? 'نامشخص' }}</h3>
                                <p class="text-[10px] text-gray-400">دسته‌بندی‌های متصل به این برند تجاری (برای باز کردن کلیک کنید)</p>
                            </div>
                        </div>
                        <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 text-gray-500 hover:text-indigo-650 hover:border-indigo-200 dark:hover:border-indigo-500 transition-all shadow-sm">
                            <svg class="w-4 h-4 transform transition-transform duration-300" :class="expanded ? 'rotate-90' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                    </div>
                    <div class="space-y-2" x-show="expanded" x-collapse>
                        @foreach($cats as $category)
                            @include('market::livewire.admin.partials.category-tree-item', ['category' => $category])
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- دسته‌بندی‌های فاقد برند --}}
            @if($noBrandCategories->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden p-5 sm:p-6 space-y-4" x-data="{ expanded: false }">
                    <div @click="expanded = !expanded" class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50 cursor-pointer select-none group/hdr">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-500 group-hover/hdr:scale-105 transition-transform">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white group-hover/hdr:text-indigo-600 dark:group-hover/hdr:text-indigo-450 transition-colors">دسته‌بندی‌های عمومی (فاقد برند)</h3>
                                <p class="text-[10px] text-gray-400">دسته‌بندی‌های بدون برند تجاری خاص (برای باز کردن کلیک کنید)</p>
                            </div>
                        </div>
                        <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 text-gray-500 hover:text-indigo-650 hover:border-indigo-200 dark:hover:border-indigo-500 transition-all shadow-sm">
                            <svg class="w-4 h-4 transform transition-transform duration-300" :class="expanded ? 'rotate-90' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                    </div>
                    <div class="space-y-2" x-show="expanded" x-collapse>
                        @foreach($noBrandCategories as $category)
                            @include('market::livewire.admin.partials.category-tree-item', ['category' => $category])
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 p-10 text-center text-gray-500 dark:text-gray-400">
                هیچ دسته‌بندی تاکنون تعریف نشده است.
            </div>
        @endif
    </div>

    {{-- مدال حذف پیشرفته دسته‌بندی دارای محصول --}}
    @if($confirmingDeletion)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md transition-opacity" wire:click="closeDeleteModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-700 relative">
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 pb-4 mb-6">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/20 text-amber-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white" id="modal-title">تأیید عملیات حذف دسته‌بندی</h3>
                                <p class="text-[10px] text-gray-400 mt-0.5">دسته‌بندی «{{ $deleteTargetName }}» دارای وابستگی در سیستم است.</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            {{-- آمار وابستگی‌ها --}}
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl text-xs space-y-2">
                                <div class="flex justify-between text-gray-700 dark:text-gray-300">
                                    <span>تعداد محصولات متصل به این شاخه:</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $deleteProductCount }} محصول</span>
                                </div>
                                @if($deleteSubCategoryCount > 0)
                                    <div class="flex justify-between text-gray-700 dark:text-gray-300">
                                        <span>تعداد زیردسته‌های مرتبط:</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $deleteSubCategoryCount }} زیردسته</span>
                                    </div>
                                @endif
                            </div>

                            {{-- گزینه‌های تصمیم‌گیری --}}
                            <div class="space-y-3">
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">نحوه برخورد با داده‌ها را انتخاب کنید:</label>
                                
                                <div class="grid grid-cols-1 gap-3">
                                    {{-- گزینه ۱: انتقال محصولات --}}
                                    <label class="flex items-start gap-3 p-3.5 border rounded-2xl cursor-pointer transition-all duration-200 {{ $deleteActionType === 'move' ? 'border-fuchsia-500 bg-fuchsia-50/10 dark:bg-fuchsia-950/5' : 'border-gray-200 dark:border-gray-750 hover:bg-gray-50 dark:hover:bg-gray-750/30' }}">
                                        <input type="radio" wire:model.live="deleteActionType" value="move" class="mt-0.5 text-fuchsia-600 border-gray-300 focus:ring-fuchsia-500">
                                        <div class="text-xs">
                                            <span class="font-bold text-gray-850 dark:text-gray-200">انتقال محصولات به دسته‌بندی دیگر</span>
                                            <p class="text-[10px] text-gray-400 mt-1">محصولات به دسته‌بندی جدید منتقل شده و خود دسته‌بندی حذف می‌گردد.</p>
                                        </div>
                                    </label>

                                    {{-- گزینه ۲: حذف همه --}}
                                    <label class="flex items-start gap-3 p-3.5 border rounded-2xl cursor-pointer transition-all duration-200 {{ $deleteActionType === 'delete_all' ? 'border-red-500 bg-red-50/10 dark:bg-red-950/5' : 'border-gray-200 dark:border-gray-750 hover:bg-gray-50 dark:hover:bg-gray-750/30' }}">
                                        <input type="radio" wire:model.live="deleteActionType" value="delete_all" class="mt-0.5 text-red-650 border-gray-300 focus:ring-red-500">
                                        <div class="text-xs">
                                            <span class="font-bold text-red-650 dark:text-red-400">حذف کامل دسته‌بندی و تمام محصولات مرتبط</span>
                                            <p class="text-[10px] text-gray-400 mt-1">دسته‌بندی، زیردسته‌ها و تمامی محصولات متصل به آن‌ها به طور کامل حذف می‌شوند.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- بخش فیلدهای وابسته به گزینه انتخابی --}}
                            @if($deleteActionType === 'move')
                                <div class="space-y-1.5 animate-in fade-in slide-in-from-top-2 duration-200">
                                    <label class="block text-xs font-bold text-gray-850 dark:text-gray-300">دسته‌بندی مقصد را انتخاب کنید <span class="text-red-500">*</span></label>
                                    <select wire:model="deleteMoveToCategoryId" class="w-full h-10 px-3 text-xs bg-gray-50 dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-fuchsia-500 focus:ring-1 focus:ring-fuchsia-500 transition-all outline-none">
                                        <option value="">-- انتخاب دسته‌بندی مقصد --</option>
                                        @foreach($deleteMoveOptions as $option)
                                            <option value="{{ $option['id'] }}">{!! $option['name'] !!}</option>
                                        @endforeach
                                    </select>
                                    @error('deleteMoveToCategoryId') <span class="text-xs text-red-500 block mt-1 font-semibold">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            @if($deleteActionType === 'delete_all')
                                <div class="space-y-3 animate-in fade-in slide-in-from-top-2 duration-200">
                                    <div class="p-3 bg-red-50 dark:bg-red-950/20 border border-red-150 dark:border-red-900/30 rounded-2xl text-[11px] leading-relaxed text-red-700 dark:text-red-400 font-semibold">
                                        هشدار غیرقابل بازگشت: تمام محصولات متصل به این شاخه نیز حذف خواهند شد.
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-gray-850 dark:text-gray-300">جهت تأیید، نام دقیق دسته‌بندی را بنویسید ({{ $deleteTargetName }}):</label>
                                        <input type="text" wire:model="deleteConfirmName" class="w-full h-10 px-3 text-xs bg-gray-50 dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all outline-none dir-ltr text-left" placeholder="نام دسته بندی را وارد کنید">
                                        @error('deleteConfirmName') <span class="text-xs text-red-500 block mt-1 font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- دکمه‌های کنترل مدال حذف --}}
                        <div class="flex items-center justify-end gap-3 border-t border-gray-150 dark:border-gray-700 pt-5 mt-6">
                            <button type="button" wire:click="closeDeleteModal" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-250 text-gray-600 dark:bg-gray-700 dark:hover:bg-gray-650 dark:text-gray-300 rounded-xl text-xs font-bold transition-all">
                                انصراف
                            </button>
                            <button type="button" wire:click="confirmDelete" class="px-6 py-2.5 rounded-xl text-xs font-bold shadow-lg transition-all {{ $deleteActionType === 'delete_all' ? 'bg-red-600 hover:bg-red-700 text-white shadow-red-500/20' : 'bg-fuchsia-600 hover:bg-fuchsia-700 text-white shadow-fuchsia-500/20' }}">
                                تأیید و اجرای عملیات
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
