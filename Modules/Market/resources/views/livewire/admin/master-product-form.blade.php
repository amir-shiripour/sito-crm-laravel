@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all outline-none";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";

    $steps = [
        1 => 'اطلاعات پایه',
        2 => 'محتوا و تنظیمات',
        3 => 'حمل و نقل و ویژگی‌ها',
        4 => 'تصاویر و تنوع‌ها',
    ];
@endphp

<div class="mx-auto pb-10">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none transition-colors duration-300">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700 pb-5 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $product?->exists ? 'ویرایش کاتالوگ کالا: ' . $title : 'ثبت کاتالوگ اصلی و تنوع‌ها' }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ساخت و تکمیل کاتالوگ محصول به صورت مرحله به مرحله.</p>
            </div>

            <a href="{{ route('user.market.master-products.index') }}" class="group inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all shadow-sm">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                بازگشت
            </a>
        </div>

        {{-- استپر (Stepper UI) --}}
        <div class="flex items-center justify-between mb-8 overflow-x-auto pb-4 custom-scrollbar">
            <div class="flex items-center w-full min-w-[600px]">
                @foreach($steps as $stepNum => $stepName)
                    <button type="button" wire:click="setStep({{ $stepNum }})" class="flex items-center gap-3 relative z-10 group outline-none cursor-pointer">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-sm shadow-sm transition-all {{ $currentStep == $stepNum ? 'bg-indigo-600 text-white ring-4 ring-indigo-600/20 dark:ring-indigo-500/30' : ($currentStep > $stepNum ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700/50 dark:text-gray-500') }}">
                            @if($currentStep > $stepNum)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $stepNum }}
                            @endif
                        </div>
                        <span class="font-bold text-sm whitespace-nowrap {{ $currentStep == $stepNum ? 'text-indigo-600 dark:text-indigo-400' : ($currentStep > $stepNum ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500') }}">{{ $stepName }}</span>
                    </button>
                    @if(!$loop->last)
                        <div class="flex-1 h-1 mx-4 rounded-full transition-colors {{ $currentStep > $stepNum ? 'bg-indigo-600 dark:bg-indigo-500' : 'bg-gray-100 dark:bg-gray-700/50' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ===================== مرحله 1: اطلاعات پایه ===================== --}}
        @if($currentStep == 1)
            <div class="animate-in fade-in slide-in-from-right-4 duration-300">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                    {{-- 💡 کنترل هوشمند اسلاگ با Alpine (حل مشکل تایپ ناقص و فارسی‌سازی) --}}
                    <div x-data="{
                        isNew: {{ $product?->exists ? 'false' : 'true' }},
                        autoSlug: {{ $product?->exists ? 'false' : 'true' }},
                        title: @entangle('title').live,
                        slug: @entangle('slug').live,
                        generateSlug() {
                            if (this.title) {
                                this.slug = this.title
                                    .toLowerCase()
                                    .replace(/[^a-z0-9\u0600-\u06FF\s-]/g, '') // حفظ حروف فارسی و انگلیسی
                                    .trim()
                                    .replace(/\s+/g, '-') // تبدیل فواصل به خط تیره
                                    .replace(/-+/g, '-'); // حذف خط تیره‌های تکراری
                            } else {
                                this.slug = '';
                            }
                        }
                    }" x-init="
                        $watch('title', value => { if(isNew && autoSlug) generateSlug() });
                        $watch('autoSlug', value => { if(isNew && value) generateSlug() });
                    ">
                        <label class="{{ $labelClass }}">نام محصول <span class="text-red-500">*</span></label>
                        <input type="text" x-model="title" class="{{ $inputClass }}" placeholder="مثلاً: لپ‌تاپ ایسوس مدل X515">
                        @error('title') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror

                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-2">
                                <label class="{{ $labelClass }} !mb-0">پیوند یکتا (Slug) <span class="text-red-500">*</span></label>

                                {{-- نمایش گزینه خودکار فقط هنگام ساخت کاتالوگ جدید --}}
                                @if(!$product?->exists)
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" x-model="autoSlug" class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                                        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">ساخت خودکار از عنوان</span>
                                    </label>
                                @endif
                            </div>
                            <div class="flex items-center">
                                <span class="px-3 py-2.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 border-l-0 rounded-r-xl text-gray-500 text-xs dir-ltr shadow-sm">/p/</span>
                                <input type="text" x-model="slug" :readonly="isNew && autoSlug" :class="{'bg-gray-100/50 dark:bg-gray-800/40 text-gray-500': isNew && autoSlug, 'bg-gray-50 dark:bg-gray-900/50': !(isNew && autoSlug)}" class="{{ $inputClass }} rounded-r-none border-l border-gray-200 dark:border-gray-700 dir-ltr text-left transition-colors" placeholder="مثلاً: asus-x515">
                            </div>
                            @error('slug') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-6">
                        <div>
                            <label class="block text-xs font-bold text-indigo-600 dark:text-indigo-400 mb-2">شناسه هوشمند (Smart SKU)</label>
                            <div class="{{ $inputClass }} bg-indigo-50 text-indigo-700 font-mono text-center font-bold dark:bg-indigo-900/20 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/50 cursor-not-allowed">{{ $crm_code }}</div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">شناسه جهانی کالا (GTIN/UPC/EAN)</label>
                                <input type="text" wire:model.defer="gtin" class="{{ $inputClass }} font-mono dir-ltr text-left" placeholder="کد EAN/UPC">
                                @error('gtin') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">شناسه محصول (کد داخلی / بارکد)</label>
                                <input type="text" wire:model.defer="barcode" class="{{ $inputClass }} font-mono dir-ltr text-left" placeholder="بارکد یا کد داخلی">
                                @error('barcode') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- فیلد برند به دراپ‌داون کاستوم با قابلیت جستجو --}}
                    @php
                        $brandOptions = [];
                        $brandOptions[] = ['value' => '', 'label' => 'انتخاب برند...'];
                        foreach($brands as $b) {
                            $brandOptions[] = ['value' => (string)$b->id, 'label' => $b->name];
                        }
                    @endphp
                    <div class="relative" x-data="{
                        open: false,
                        selected: @entangle('brand_id').live,
                        search: '',
                        options: {{ json_encode($brandOptions) }},
                        get filteredOptions() {
                            if (!this.search) return this.options;
                            return this.options.filter(o => o.value === '' || o.label.toLowerCase().includes(this.search.toLowerCase()));
                        },
                        get selectedLabel() {
                            let opt = this.options.find(o => o.value == this.selected);
                            return opt ? opt.label : 'انتخاب برند...';
                        }
                    }" @click.away="open = false">
                        <label class="{{ $labelClass }}">برند <span class="text-red-500">*</span></label>

                        <div @click="open = !open" class="{{ $inputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-900': open, 'bg-gray-50 dark:bg-gray-900/50': !open}">
                            <span x-text="selectedLabel" class="block truncate font-bold text-gray-800 dark:text-gray-200" :class="{'!text-gray-500 dark:!text-gray-400 font-normal': selected === '' || selected == null}"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                            <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700 mb-1 sticky top-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md z-10">
                                <input type="text" x-model="search" placeholder="جستجوی برند..." class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2.5 py-1.5 focus:border-indigo-500 focus:outline-none dark:text-white" @click.stop>
                            </div>
                            <template x-for="option in filteredOptions" :key="option.value">
                                <div @click="selected = option.value; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': selected == option.value, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': selected != option.value}">
                                    <span x-text="option.label"></span>
                                    <svg x-show="selected == option.value" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </template>
                        </div>
                        @error('brand_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- فیلد دسته‌بندی با قابلیت جستجو و عمق بی‌نهایت --}}
                    <div class="relative" x-data="{
                        open: false,
                        selected: @entangle('category_id').live,
                        search: '',
                        options: @entangle('catOptions'),
                        get filteredOptions() {
                            if (!this.search) return this.options;
                            return this.options.filter(o => o.value === '' || o.label.toLowerCase().includes(this.search.toLowerCase()));
                        },
                        get selectedLabel() {
                            let opt = this.options.find(o => o.value == this.selected);
                            return opt ? opt.label : 'انتخاب دسته...';
                        }
                    }" @click.away="open = false">
                        <label class="{{ $labelClass }}">دسته‌بندی <span class="text-red-500">*</span></label>

                        <div @click="open = !open" class="{{ $inputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-900': open, 'bg-gray-50 dark:bg-gray-900/50': !open}">
                            <span x-text="selectedLabel" class="block truncate font-bold text-gray-800 dark:text-gray-200" :class="{'!text-gray-500 dark:!text-gray-400 font-normal': selected === '' || selected == null}"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                            <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700 mb-1 sticky top-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md z-10">
                                <input type="text" x-model="search" placeholder="جستجوی دسته‌بندی..." class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2.5 py-1.5 focus:border-indigo-500 focus:outline-none dark:text-white" @click.stop>
                            </div>
                            <template x-for="option in filteredOptions" :key="option.value">
                                <div @click="selected = option.value; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group text-sm" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': selected == option.value, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': selected != option.value, 'border-indigo-500 dark:border-indigo-400': option.isSub && selected == option.value}" :style="option.isSub ? 'padding-right: ' + (option.depth * 1.25 + 0.75) + 'rem' : ''">
                                    <span x-show="option.isSub" class="text-gray-300 dark:text-gray-600 group-hover:text-indigo-400 transition-colors">↳</span>
                                    <span x-text="option.label"></span>
                                    <svg x-show="selected == option.value" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </template>
                        </div>
                        @error('category_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- دسته‌بندی‌های نمایش فروشگاه در صورت فعال بودن سیستم مجزا --}}
                    @if($separate_category_enabled)
                        <div class="col-span-1 md:col-span-2 relative" x-data="{
                            open: false,
                            selected: @entangle('selectedDisplayCategories').live,
                            options: {{ json_encode($displayCategoryOptions) }},
                            search: '',
                            get filteredOptions() {
                                if (!this.search) return this.options;
                                return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            toggle(val) {
                                let index = this.selected.indexOf(val);
                                if (index > -1) {
                                    // Deselect it
                                    this.selected.splice(index, 1);
                                    // Also auto-deselect all child categories recursively
                                    let deselectChildren = (parentId) => {
                                        this.options.forEach(o => {
                                            if (o.parent_id == parentId) {
                                                let idx = this.selected.indexOf(o.value);
                                                if (idx > -1) {
                                                    this.selected.splice(idx, 1);
                                                    deselectChildren(o.value);
                                                }
                                            }
                                        });
                                    };
                                    deselectChildren(val);
                                } else {
                                    this.selected.push(val);
                                    // Auto-select parents recursively
                                    let current = this.options.find(o => o.value == val);
                                    while (current && current.parent_id) {
                                        let pId = current.parent_id.toString();
                                        if (!this.selected.includes(pId)) {
                                            this.selected.push(pId);
                                        }
                                        current = this.options.find(o => o.value == pId);
                                    }
                                }
                            }
                        }" @click.away="open = false">
                            <label class="{{ $labelClass }}">دسته‌بندی‌های نمایش فروشگاه (سیستم مجزا)</label>
                            
                            <div @click="open = !open" class="{{ $inputClass }} cursor-pointer flex justify-between items-center transition-colors select-none min-h-[44px] py-1.5" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-900': open, 'bg-gray-50 dark:bg-gray-900/50': !open}">
                                <div class="flex flex-wrap gap-1.5 items-center max-w-[92%]">
                                    <template x-if="selected.length === 0">
                                        <span class="text-gray-500 dark:text-gray-400 font-normal">انتخاب دسته‌بندی‌های مجزا...</span>
                                    </template>
                                    <template x-for="val in selected" :key="val">
                                        <div class="inline-flex items-center gap-1 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-lg text-xs font-bold border border-indigo-100 dark:border-indigo-800/40">
                                            <span x-text="options.find(o => o.value == val)?.label || val"></span>
                                            <button type="button" @click.stop="toggle(val)" class="hover:text-indigo-900 dark:hover:text-white transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>

                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700 mb-1 sticky top-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md z-10">
                                    <input type="text" x-model="search" placeholder="جستجوی دسته‌بندی مجزا..." class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2.5 py-1.5 focus:border-indigo-500 focus:outline-none dark:text-white" @click.stop>
                                </div>
                                <template x-for="option in filteredOptions" :key="option.value">
                                    <div @click="toggle(option.value)" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-3 group text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50" :style="option.isSub ? 'padding-right: ' + (option.depth * 1.25 + 0.75) + 'rem' : ''">
                                        <input type="checkbox" :checked="selected.includes(option.value)" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 pointer-events-none">
                                        <span x-show="option.isSub" class="text-gray-300 dark:text-gray-600 transition-colors">↳</span>
                                        <span x-text="option.label" class="text-gray-700 dark:text-gray-300" :class="{'font-bold text-indigo-700 dark:text-indigo-300': selected.includes(option.value)}"></span>
                                    </div>
                                </template>
                            </div>
                            @error('selectedDisplayCategories') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="col-span-1 md:col-span-2">
                        <label class="{{ $labelClass }}">توضیح کوتاه (نمایش در نتایج جستجو)</label>
                        <textarea wire:model.defer="short_description" rows="3" class="{{ $inputClass }}" placeholder="یک یا دو پاراگراف برای معرفی سریع محصول..."></textarea>
                        @error('short_description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- ===================== مرحله 2: محتوا و تنظیمات ===================== --}}
        @if($currentStep == 2)
            <div class="animate-in fade-in slide-in-from-right-4 duration-300">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                    {{-- تنظیمات انتشار --}}
                    @php
                        $statusOptions = [
                            ['value' => 'active', 'label' => 'فعال (قابل انتخاب توسط فروشندگان)'],
                            ['value' => 'draft', 'label' => 'پیش‌نویس (مخفی از فروشندگان)'],
                            ['value' => 'archived', 'label' => 'بایگانی شده']
                        ];
                    @endphp
                    @if($isVendor)
                        <div>
                            <label class="{{ $labelClass }}">وضعیت انتشار در کاتالوگ</label>
                            <div class="{{ $inputClass }} bg-gray-100/50 text-gray-500 dark:bg-gray-800/40 cursor-not-allowed font-bold">
                                @if($status === 'active')
                                    فعال (انتشار مستقیم و خودکار)
                                @elseif($status === 'draft')
                                    پیش‌نویس (نیاز به بررسی و فعال‌سازی توسط ادمین)
                                @else
                                    بایگانی شده
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="relative" x-data="{
                            open: false,
                            selected: @entangle('status'),
                            options: {{ json_encode($statusOptions) }},
                            get selectedLabel() {
                                let opt = this.options.find(o => o.value == this.selected);
                                return opt ? opt.label : 'وضعیت انتشار در کاتالوگ';
                            }
                        }" @click.away="open = false">
                            <label class="{{ $labelClass }}">وضعیت انتشار در کاتالوگ <span class="text-red-500">*</span></label>

                            <div @click="open = !open" class="{{ $inputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-900': open, 'bg-gray-50 dark:bg-gray-900/50': !open}">
                                <span x-text="selectedLabel" class="block truncate font-bold text-gray-800 dark:text-gray-200"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>

                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                <template x-for="option in options" :key="option.value">
                                    <div @click="selected = option.value; open = false" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': selected == option.value, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': selected != option.value}">
                                        <span x-text="option.label"></span>
                                        <svg x-show="selected == option.value" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </template>
                            </div>
                            @error('status') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- سوئیچ‌های تنظیمات --}}
                    <div class="flex flex-col gap-4">
                        <label class="flex items-center justify-between cursor-pointer p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm transition-colors group">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">فروش تکی (محدودیت ۱ عدد)</span>
                                <span class="text-[10px] text-gray-500 mt-1">کاربر در هر سفارش فقط یک عدد می‌تواند بخرد.</span>
                            </div>
                            <div class="relative flex items-center">
                                <input type="checkbox" wire:model="single_sell" class="peer sr-only">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </div>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm transition-colors group">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">فعال بودن نقد و بررسی</span>
                                <span class="text-[10px] text-gray-500 mt-1">کاربران بتوانند نظر ثبت کنند.</span>
                            </div>
                            <div class="relative flex items-center">
                                <input type="checkbox" wire:model="enable_reviews" class="peer sr-only">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </div>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm transition-colors group">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">فعال بودن پرسش و پاسخ</span>
                                <span class="text-[10px] text-gray-500 mt-1">امکان مطرح کردن پرسش و پاسخ برای کالا.</span>
                            </div>
                            <div class="relative flex items-center">
                                <input type="checkbox" wire:model="enable_questions" class="peer sr-only">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </div>
                        </label>
                    </div>

                    {{-- 💡 ادیتور Quill با استایل کاملاً یکپارچه، بدون نقص در دارک مود و سازگار با فارسی --}}
                    <div class="col-span-1 md:col-span-2 mt-4 relative">
                        <label class="{{ $labelClass }}">توضیحات معرفی (معرفی کامل محصول)</label>
                        <div
                            wire:ignore
                            x-data="{
                                content: @entangle('description'),
                                initQuill() {
                                    let quill = new Quill(this.$refs.editor, {
                                        theme: 'snow',
                                        placeholder: 'توضیحات کامل محصول را اینجا بنویسید...',
                                        modules: {
                                            toolbar: [
                                                [{ 'header': [1, 2, 3, false] }],
                                                ['bold', 'italic', 'underline', 'strike'],
                                                [{ 'color': [] }, { 'background': [] }],
                                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                [{ 'align': [] }, { 'direction': 'rtl' }],
                                                ['link', 'image', 'video'],
                                                ['clean']
                                            ]
                                        }
                                    });
                                    quill.on('text-change', () => {
                                        this.content = quill.root.innerHTML;
                                    });
                                    quill.root.innerHTML = this.content;
                                }
                            }"
                            x-init="initQuill()"
                            class="custom-quill-wrapper border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-900/50 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 shadow-sm"
                        >
                            <div x-ref="editor" class="focus:outline-none"></div>
                        </div>
                        @error('description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                </div>
            </div>
        @endif

        {{-- ===================== مرحله 3: حمل و نقل و ویژگی‌ها ===================== --}}
        @if($currentStep == 3)
            <div class="animate-in fade-in slide-in-from-right-4 duration-300">

                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-800 pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                    مشخصات فیزیکی و حمل و نقل
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <div>
                        <label class="{{ $labelClass }}">وزن (کیلوگرم)</label>
                        <div class="relative">
                            <input type="number" step="0.01" wire:model.defer="weight" class="{{ $inputClass }} dir-ltr text-right pl-16" placeholder="0.00">
                            <span class="absolute top-1/2 -translate-y-1/2 left-4 text-[10px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">کیلوگرم</span>
                        </div>
                        @error('weight') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">ابعاد بسته‌بندی (سانتی‌متر)</label>
                        <div class="flex items-center gap-2 relative">
                            <input type="number" step="0.1" wire:model.defer="length" class="{{ $inputClass }} dir-ltr text-center px-1" placeholder="طول">
                            <span class="text-gray-400 font-bold">×</span>
                            <input type="number" step="0.1" wire:model.defer="width" class="{{ $inputClass }} dir-ltr text-center px-1" placeholder="عرض">
                            <span class="text-gray-400 font-bold">×</span>
                            <input type="number" step="0.1" wire:model.defer="height" class="{{ $inputClass }} dir-ltr text-center px-1 pl-12" placeholder="ارتفاع">

                            <span class="absolute top-1/2 -translate-y-1/2 left-3 text-[10px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded pointer-events-none">Cm</span>
                        </div>
                        @error('length') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @error('width') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @error('height') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- کلاس حمل و نقل --}}
                    @php
                        $shippingOptions = [
                            ['value' => 'standard', 'label' => 'استاندارد (پیک / پست)'],
                            ['value' => 'heavy', 'label' => 'کالای سنگین (پس‌کرایه / باربری)'],
                            ['value' => 'fragile', 'label' => 'شکستنی و حساس'],
                            ['value' => 'digital', 'label' => 'دانلودی / مجازی (بدون ارسال فیزیکی)'],
                        ];
                    @endphp
                    <div class="relative md:col-span-2" x-data="{
                        open: false,
                        selected: @entangle('shipping_class'),
                        options: {{ json_encode($shippingOptions) }},
                        get selectedLabel() {
                            let opt = this.options.find(o => o.value == this.selected);
                            return opt ? opt.label : 'استاندارد (پیک / پست)';
                        }
                    }" @click.away="open = false">
                        <label class="{{ $labelClass }}">کلاس حمل و نقل <span class="text-red-500">*</span></label>

                        <div @click="open = !open" class="{{ $inputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-900': open, 'bg-gray-50 dark:bg-gray-900/50': !open}">
                            <span x-text="selectedLabel" class="block truncate font-bold text-gray-800 dark:text-gray-200"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                            <template x-for="option in options" :key="option.value">
                                <div @click="selected = option.value; open = false" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': selected == option.value, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': selected != option.value}">
                                    <span x-text="option.label"></span>
                                    <svg x-show="selected == option.value" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </template>
                        </div>
                        @error('shipping_class') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if(count($categoryFields) > 0)
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-800 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                        ویژگی‌های اختصاصی این دسته
                    </h3>
                    <div class="bg-gray-50/80 dark:bg-gray-900/30 p-6 rounded-3xl border border-gray-200 dark:border-gray-800/50 mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            @foreach($categoryFields as $field)
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">{{ $field }}</label>
                                    <input type="text" wire:model.defer="dynamicAttributes.{{ $field }}" class="{{ $inputClass }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50 dark:bg-gray-900/30 p-6 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 text-center">
                        <p class="text-sm font-bold text-gray-600 dark:text-gray-400">هیچ ویژگی اختصاصی برای این دسته تعریف نشده است.</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===================== مرحله 4: تصاویر و تنوع‌ها ===================== --}}
        @if($currentStep == 4)
            <div class="animate-in fade-in slide-in-from-right-4 duration-300">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="md:col-span-1">
                        <label class="{{ $labelClass }}">تصویر اصلی کالا</label>
                        <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 border-gray-300 dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 overflow-hidden relative transition-colors">
                            {{-- 💡 حل خطای temporaryUrl --}}
                            @if($main_image && !is_string($main_image))
                                <img src="{{ $main_image->temporaryUrl() }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity"><span class="text-white text-xs font-bold">تغییر عکس</span></div>
                            @elseif($existing_main_image)
                                <img src="{{ Storage::url($existing_main_image) }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity"><span class="text-white text-xs font-bold">تغییر عکس</span></div>
                            @else
                                <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                <p class="text-xs text-gray-500">برای آپلود کلیک کنید</p>
                            @endif
                            <input type="file" wire:model="main_image" class="hidden" accept="image/*" />
                        </label>
                        @error('main_image') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">گالری تصاویر (چند عکس انتخاب کنید)</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                            <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 border-gray-300 dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-100 transition-colors">
                                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <input type="file" wire:model="gallery_images" multiple class="hidden" accept="image/*" />
                            </label>

                            @foreach($existing_gallery as $idx => $img)
                                <div class="relative w-full h-24 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 group">
                                    <img src="{{ Storage::url($img) }}" class="w-full h-full object-cover">
                                    <button wire:click.prevent="removeExistingGalleryImage({{ $idx }})" type="button" class="absolute inset-0 bg-red-500/80 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            @endforeach

                            @foreach($gallery_images as $idx => $img)
                                {{-- 💡 حل خطای temporaryUrl --}}
                                @if($img && !is_string($img))
                                    <div class="relative w-full h-24 rounded-xl overflow-hidden border border-indigo-200 dark:border-indigo-700 group">
                                        <img src="{{ $img->temporaryUrl() }}" class="w-full h-full object-cover">
                                        <button wire:click.prevent="removeNewGalleryImage({{ $idx }})" type="button" class="absolute inset-0 bg-red-500/80 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('gallery_images.*') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- بخش چهارم: مدیریت تنوع‌ها (Smart Variant Builder با قابلیت Multi-Select) --}}
                <div class="bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30 p-6 rounded-3xl mb-4">
                    {{-- 💡 استایل هدر و دکمه‌های کنترل تنوع‌ها اصلاح شد --}}
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6 mb-6 pb-6 border-b border-indigo-200 dark:border-indigo-800/50">
                        <div class="flex-1">
                            <h3 class="text-base font-bold text-indigo-900 dark:text-indigo-300 mb-1">تعریف محدودیت تنوع‌ها (مختص این کالا)</h3>
                            <p class="text-[11px] text-indigo-500/80 dark:text-indigo-400/80 mb-4 max-w-xl leading-relaxed">
                                @if($storeType === 'multi' && $vendorCanCreateVariants && $vendorCanManagePrices)
                                    ویژگی‌های مجازی که فروشندگان می‌توانند برای این محصول وارد کنند را انتخاب کنید. اگر «هر رنگ» را انتخاب کنید، فروشنده مجاز به انتخاب هر رنگی خواهد بود.
                                @else
                                    ترکیب تنوع‌هایی که می‌خواهید در سایت نمایش داده شوند را بسازید.
                                @endif
                            </p>

                            @if(count($variantAxes) > 0)
                                <div class="flex flex-wrap items-start gap-4">
                                    @foreach($variantAxes as $axis)
                                        <div class="relative min-w-[220px]" x-data="{
                                            open: false,
                                            // اتصال به آرایه انتخابی محور فعلی در Livewire
                                            selectedOptions: @entangle('selectedAxisValues.' . $axis['name']).live,
                                            options: {{ json_encode($axis['values']) }},
                                            type: '{{ $axis['type'] }}',

                                            toggleOption(value) {
                                                if(value === 'هر {{ $axis['name'] }}') {
                                                    this.selectedOptions = [value];
                                                    return;
                                                }

                                                let idx = this.selectedOptions.indexOf('هر {{ $axis['name'] }}');
                                                if(idx !== -1) {
                                                    this.selectedOptions.splice(idx, 1);
                                                }

                                                let valIdx = this.selectedOptions.indexOf(value);
                                                if(valIdx !== -1) {
                                                    this.selectedOptions.splice(valIdx, 1);
                                                } else {
                                                    this.selectedOptions.push(value);
                                                }
                                            },
                                            get displayLabel() {
                                                if (!this.selectedOptions || this.selectedOptions.length === 0) return 'انتخاب کنید...';
                                                if (this.selectedOptions.length === 1) return this.selectedOptions[0];
                                                return this.selectedOptions.length + ' مورد انتخاب شد';
                                            }
                                        }" @click.away="open = false">
                                            <label class="block text-[10px] font-bold text-indigo-800 dark:text-indigo-300 mb-1.5 px-1">{{ $axis['name'] }} مجاز</label>

                                            <div @click="open = !open" class="w-full rounded-xl border border-indigo-200 bg-white px-3 py-2.5 text-sm cursor-pointer flex justify-between items-center transition-all dark:border-indigo-700 dark:bg-gray-800 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-600" :class="{'ring-2 ring-indigo-500/30 border-indigo-500': open}">
                                                <div class="flex flex-wrap gap-1 items-center">
                                                    <span x-text="displayLabel" class="font-bold text-xs text-gray-700 dark:text-gray-200"></span>
                                                </div>
                                                <svg class="w-4 h-4 text-indigo-400 transition-transform duration-200 flex-shrink-0" :class="{'rotate-180 text-indigo-600': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </div>

                                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                                <template x-for="option in options" :key="option.id">
                                                    <div @click="toggleOption(option.value)" class="px-3 py-2 cursor-pointer transition-all flex items-center gap-3 group hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                        <div class="w-4 h-4 rounded border flex items-center justify-center flex-shrink-0 transition-colors"
                                                             :class="selectedOptions.includes(option.value) ? 'bg-indigo-500 border-indigo-500 text-white dark:border-indigo-500' : 'bg-white border-gray-300 dark:bg-gray-900 dark:border-gray-600'">
                                                            <svg x-show="selectedOptions.includes(option.value)" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        </div>
                                                        <template x-if="option.id !== 'any' && (type === 'color' || type === 'image')">
                                                            <div class="w-5 h-5 rounded-md border border-gray-200 dark:border-gray-600 shadow-sm flex-shrink-0 overflow-hidden" :style="type === 'color' && option.meta_value && !option.meta_value.startsWith('attributes/') ? 'background-color: ' + option.meta_value : ''">
                                                                <template x-if="option.meta_value && option.meta_value.startsWith('attributes/')">
                                                                    <img :src="'/storage/' + option.meta_value" class="w-full h-full object-cover">
                                                                </template>
                                                            </div>
                                                        </template>

                                                        <span x-text="option.value" class="text-xs text-gray-700 dark:text-gray-300 font-medium" :class="selectedOptions.includes(option.value) ? '!font-bold text-indigo-700 dark:text-indigo-300' : ''"></span>
                                                     </div>
                                                 </template>
                                             </div>
                                         </div>
                                     @endforeach
                                 </div>
                             @endif
                         </div>

                         @if(count($variantAxes) > 0)
                             <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-end gap-3 mt-4 lg:mt-0 lg:ml-auto w-full lg:w-auto">
                                 <button wire:click="generateAllCombinations" class="inline-flex items-center justify-center gap-2 px-5 py-3 lg:py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all active:scale-95 whitespace-nowrap w-full sm:w-auto">
                                     <svg class="w-5 h-5 lg:w-4 lg:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                     @if($storeType === 'multi' && $vendorCanCreateVariants && $vendorCanManagePrices) ثبت گزینه‌های مجاز @else تولید ترکیب‌های مجاز @endif
                                 </button>
                                 <button wire:click="clearAllVariants" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 lg:py-1.5 text-xs text-rose-500 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 rounded-xl lg:rounded-lg transition-colors font-bold w-full sm:w-auto border border-rose-100 dark:border-rose-800/30 lg:border-transparent">
                                     <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                     پاکسازی کل لیست
                                 </button>
                             </div>
                         @endif
                     </div>
                           {{-- لیست تنوع‌های ساخته شده (در صورتی نمایش داده می‌شود که ادمین در حال ساخت تنوع ثابت باشد یا قیمت‌گذاری فروشندگان غیرفعال باشد) --}}
                    @if(!($storeType === 'multi' && $vendorCanCreateVariants && $vendorCanManagePrices))
                        <div class="space-y-3">
                            @if(count($variantAxes) > 0)
                                @if(count($variants) > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                                        @foreach($variants as $index => $variant)
                                            <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between gap-3 group relative overflow-hidden transition-all hover:border-indigo-200 dark:hover:border-indigo-800">
                                                <div class="flex items-center justify-between w-full">
                                                    <div class="flex flex-col gap-1 z-10 relative">
                                                        @foreach($variantAxes as $axis)
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-[9px] text-gray-400 dark:text-gray-500">{{ $axis['name'] }}:</span>
                                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $variant['values'][$axis['name']] ?? 'نامشخص' }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <div class="flex items-center gap-2 z-10 relative">
                                                        <label class="relative flex items-center cursor-pointer" title="فعال در کاتالوگ">
                                                            <input type="checkbox" wire:model.defer="variants.{{ $index }}.is_active" class="peer sr-only">
                                                            <div class="w-7 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-500"></div>
                                                        </label>
                                                        <button wire:click="removeVariant({{ $index }})" class="text-gray-400 hover:text-rose-500 transition-colors bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-900/30 rounded-md p-1 shadow-sm border border-gray-100 dark:border-gray-700" title="حذف این ترکیب">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                @if(!$vendorCanManagePrices)
                                                    <div class="w-full pt-2 border-t border-gray-100 dark:border-gray-700" x-data="{
                                                        rawVal: @entangle('variants.'.$index.'.price'),
                                                        formattedVal: '',
                                                        init() {
                                                            this.format(this.rawVal);
                                                            this.$watch('rawVal', val => this.format(val));
                                                        },
                                                        format(val) {
                                                            this.formattedVal = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                                        },
                                                        update() {
                                                            this.rawVal = this.formattedVal.replace(/,/g, '');
                                                        }
                                                    }">
                                                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">قیمت کاتالوگ (تومان) <span class="text-red-500">*</span></label>
                                                        <div class="relative">
                                                            <input type="text"
                                                                   x-model="formattedVal"
                                                                   @input="update()"
                                                                   class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-2 py-1 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:text-gray-100 transition-all outline-none text-center font-mono font-bold"
                                                                   placeholder="تعیین قیمت...">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 bg-white dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                                        <div class="w-12 h-12 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3 border border-gray-100 dark:border-gray-700">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012-2h-2a2 2 0 01-2-2v-2z" /></svg>
                                        </div>
                                        <p class="text-sm font-bold text-gray-600 dark:text-gray-300">لیست تنوع‌های مجاز خالی است.</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ابتدا ویژگی‌ها را از بالا انتخاب کرده و دکمه "تولید ترکیب‌های مجاز" را بزنید.</p>
                                    </div>
                                @endif
                            @else
                                <div class="bg-white dark:bg-gray-800/50 p-6 rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 text-center">
                                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300">این دسته دارای محور تنوع (مانند رنگ یا سایز) نیست.</p>
                                    <p class="text-xs text-gray-500 mt-1">یک مدل "استاندارد" به صورت خودکار برای قیمت‌گذاری فروشندگان ایجاد خواهد شد.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ===================== Navigation Buttons ===================== --}}
        <div class="flex flex-col sm:flex-row items-center justify-between mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 gap-4">
            @if($currentStep > 1)
                <button type="button" wire:click="prevStep" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">مرحله قبل</button>
            @else
                <div class="hidden sm:block"></div>
            @endif

            @if($currentStep < 4)
                <button type="button" wire:click="nextStep" class="w-full sm:w-auto px-8 py-2.5 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all flex items-center justify-center gap-2">
                    مرحله بعدی
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            @else
                <button wire:click="save" wire:loading.attr="disabled" class="w-full sm:w-auto px-8 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-all active:scale-95 flex justify-center items-center gap-2">
                    <span wire:loading.remove>ذخیره نهایی کاتالوگ</span>
                    <span wire:loading.flex class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        درحال پردازش...
                    </span>
                </button>
            @endif
        </div>

    </div>
</div>
