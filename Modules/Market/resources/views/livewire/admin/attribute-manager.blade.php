@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
@endphp

<div class="space-y-6 pb-10">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">ویژگی‌های تنوع‌ساز</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مدیریت محورهای تنوع سراسری (مانند رنگ‌ها و سایزها)</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openForm" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                ایجاد ویژگی جدید
            </button>
        </div>
    </div>

    {{-- دکمه‌های ایجاد سریع --}}
    @if(!$isFormOpen)
        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-2xl border border-indigo-100 dark:border-indigo-800/30 mb-6 space-y-4">
            <div class="flex items-center">
                <span class="text-sm font-bold text-indigo-800 dark:text-indigo-300">ایجاد سریع ویژگی‌های پرکاربرد:</span>
            </div>

            {{-- دسته‌بندی رنگ و ظاهر --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-gray-500 w-full sm:w-auto min-w-[80px]">رنگ و ظاهر:</span>
                <button wire:click="quickCreate('color')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-tr from-red-500 via-green-500 to-blue-500"></span> رنگ
                </button>
            </div>

            {{-- دسته‌بندی دیجیتال --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-gray-500 w-full sm:w-auto min-w-[80px]">دیجیتال:</span>
                <button wire:click="quickCreate('ram')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">حافظه رم</button>
                <button wire:click="quickCreate('storage')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">حافظه داخلی</button>
                <button wire:click="quickCreate('screen_size')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">اندازه صفحه</button>
                <button wire:click="quickCreate('battery')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">باتری</button>
                <button wire:click="quickCreate('camera')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">دوربین</button>
                <button wire:click="quickCreate('sim')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">سیم‌کارت</button>
                <button wire:click="quickCreate('os')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">سیستم عامل</button>
                <button wire:click="quickCreate('network')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">شبکه</button>
            </div>

            {{-- دسته‌بندی پوشاک --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-gray-500 w-full sm:w-auto min-w-[80px]">پوشاک و مد:</span>
                <button wire:click="quickCreate('size')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">سایز لباس</button>
                <button wire:click="quickCreate('size_eu')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">سایز EU</button>
                <button wire:click="quickCreate('shoes')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">سایز کفش</button>
                <button wire:click="quickCreate('gender')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">جنسیت</button>
                <button wire:click="quickCreate('material')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">متریال</button>
                <button wire:click="quickCreate('sleeve')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">آستین</button>
            </div>

            {{-- دسته‌بندی لوازم خانگی --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-gray-500 w-full sm:w-auto min-w-[80px]">لوازم خانگی:</span>
                <button wire:click="quickCreate('power')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">توان دستگاه</button>
                <button wire:click="quickCreate('capacity_lit')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">ظرفیت (لیتر)</button>
                <button wire:click="quickCreate('energy_grade')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">رتبه انرژی</button>
                <button wire:click="quickCreate('washing_capacity')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">ظرفیت شستشو</button>
                <button wire:click="quickCreate('tv_size')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">سایز تلویزیون</button>
                <button wire:click="quickCreate('tv_resolution')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">رزولوشن تلویزیون</button>
            </div>

            {{-- مبلمان و دکور --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-gray-500 w-full sm:w-auto min-w-[80px]">مبلمان/دکور:</span>
                <button wire:click="quickCreate('dimensions')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">ابعاد</button>
                <button wire:click="quickCreate('weight')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">وزن</button>
                <button wire:click="quickCreate('frame_material')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">جنس فریم</button>
            </div>

            {{-- سوپرمارکتی و آرایشی --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-gray-500 w-full sm:w-auto min-w-[80px]">سوپرمارکت/آرایشی:</span>
                <button wire:click="quickCreate('package_size')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">وزن بسته</button>
                <button wire:click="quickCreate('package_count')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">تعداد در بسته</button>
                <button wire:click="quickCreate('flavor')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">طعم</button>
                <button wire:click="quickCreate('skin_type')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">نوع پوست</button>
                <button wire:click="quickCreate('hair_type')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">نوع مو</button>
                <button wire:click="quickCreate('volume_ml')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">حجم (ml)</button>
                <button wire:click="quickCreate('sunscreen_spf')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">SPF</button>
            </div>

            {{-- سایر موارد --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-gray-500 w-full sm:w-auto min-w-[80px]">سایر موارد:</span>
                <button wire:click="quickCreate('dumbbell_weight')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">وزن دمبل</button>
                <button wire:click="quickCreate('sport_size')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">سایز ورزشی</button>
                <button wire:click="quickCreate('car_brand')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">برند خودرو</button>
                <button wire:click="quickCreate('tire_size')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">سایز لاستیک</button>
                <button wire:click="quickCreate('oil_viscosity')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">ویسکوزیته روغن</button>
                <button wire:click="quickCreate('language')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">زبان</button>
                <button wire:click="quickCreate('edition')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">نسخه/ویرایش</button>
                <button wire:click="quickCreate('age_range')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">رده سنی</button>
                <button wire:click="quickCreate('warranty')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">گارانتی</button>
                <button wire:click="quickCreate('packaging')" class="text-xs px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-indigo-300 hover:text-indigo-600 transition-colors">بسته‌بندی</button>
            </div>
        </div>
    @endif

    {{-- Form --}}
    @if($isFormOpen)
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 sm:p-8 rounded-3xl shadow-xl shadow-gray-200/40 dark:shadow-none animate-in fade-in slide-in-from-top-4">
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    {{ $attribute_id ? 'ویرایش ویژگی: ' . $name : 'ثبت ویژگی جدید' }}
                </h2>
                <button wire:click="closeForm" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="{{ $labelClass }}">عنوان ویژگی (گروه) <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="name" class="{{ $inputClass }}" placeholder="مثلاً: رنگ گوشی، حافظه داخلی...">
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">نوع ویژگی</label>
                    <select wire:model.live="type" class="{{ $inputClass }}">
                        <option value="select">انتخابی ساده (فقط متن)</option>
                        <option value="color">رنگ (کد رنگ + عکس پترن)</option>
                        <option value="image">عکس و متن (آیکون/تصویر)</option>
                    </select>
                </div>

                {{-- 💡 فیلد واحد اندازه‌گیری --}}
                <div class="animate-in fade-in">
                    <label class="{{ $labelClass }}">واحد اندازه‌گیری (اختیاری)</label>
                    <select wire:model.defer="unit" class="{{ $inputClass }}">
                        @foreach($unitsList as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- بخش مدیریت مقادیر (Values) --}}
            <div class="bg-gray-50 dark:bg-gray-900/30 p-6 rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">مقادیر مجاز برای این ویژگی</h3>
                        <p class="text-[11px] text-gray-500 mt-1">
                            @if($type === 'color')
                                می‌توانید کد رنگ را انتخاب کنید، یا اگر پارچه طرح‌دار است، با کلیک روی آیکون تصویر، یک الگو (پترن) برای آن آپلود کنید.
                            @elseif($type === 'image')
                                یک تصویر یا آیکون همراه با نام برای هر متغیر آپلود کنید.
                            @else
                                گزینه‌های متنی را وارد کنید (واحد اندازه‌گیری به صورت خودکار به انتهای آن‌ها اضافه خواهد شد).
                            @endif
                        </p>
                    </div>
                    <button wire:click="addValue" class="inline-flex items-center gap-2 text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 px-4 py-2 rounded-xl hover:bg-indigo-50 transition-colors shadow-sm font-bold">
                        + افزودن گزینه
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse($values as $index => $val)
                        <div class="flex items-center gap-3 bg-white dark:bg-gray-800 p-2 pl-3 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm group">

                            {{-- کالرپیکر برای نوع رنگ --}}
                            @if($type === 'color')
                                {{-- اگر عکسی برای رنگ آپلود نشده بود، کالر پیکر نشان بده --}}
                                @if(empty($val['new_image']) && (!isset($val['meta_value']) || !str_starts_with($val['meta_value'], 'attributes/')))
                                    <input type="color" wire:model.defer="values.{{ $index }}.meta_value" class="w-10 h-10 rounded border-0 cursor-pointer p-0 bg-transparent flex-shrink-0 shadow-sm" title="انتخاب رنگ">
                                @endif
                            @endif

                            {{-- دکمه/پیش‌نمایش آپلود عکس (برای نوع Image و Color) --}}
                            @if($type === 'image' || $type === 'color')
                                <div class="relative flex-shrink-0">
                                    <input type="file" wire:model="values.{{ $index }}.new_image" id="file_{{ $index }}" class="hidden" accept="image/png, image/jpeg, image/webp">

                                    <label for="file_{{ $index }}" class="w-10 h-10 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center cursor-pointer hover:border-indigo-500 overflow-hidden bg-gray-50 dark:bg-gray-900 transition-colors" title="آپلود تصویر/پترن">
                                        {{-- پیش‌نمایش عکس جدید (در صورت آپلود) --}}
                                        @if(!empty($val['new_image']))
                                            <img src="{{ $val['new_image']->temporaryUrl() }}" class="w-full h-full object-cover">
                                            {{-- پیش‌نمایش عکس قبلی دیتابیس --}}
                                        @elseif(!empty($val['meta_value']) && str_starts_with($val['meta_value'], 'attributes/'))
                                            <img src="{{ Storage::url($val['meta_value']) }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @endif
                                    </label>
                                </div>
                            @endif

                            {{-- اینپوت نام مقدار --}}
                            <div class="flex-1 relative flex items-center">
                                <input type="text" wire:model.defer="values.{{ $index }}.value" placeholder="نام (مثلا: قرمز یا XXL)" class="w-full border-0 focus:ring-0 text-sm bg-transparent dark:text-white px-2">
                                @if($unit)
                                    <span class="absolute left-2 text-xs font-bold text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded dir-ltr">{{ $unit }}</span>
                                @endif
                            </div>

                            <button wire:click="removeValue({{ $index }})" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>

                        {{-- نمایش ارورهای فرم برای هر ردیف --}}
                        <div class="px-2">
                            @error("values.{$index}.value") <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                            @error("values.{$index}.new_image") <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                        </div>
                    @empty
                        <div class="text-center py-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">هیچ گزینه‌ای تعریف نشده است. روی "افزودن گزینه" کلیک کنید.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button wire:click="save" class="px-8 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2">
                    <span wire:loading.remove wire:target="save">ذخیره ویژگی و مقادیر</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        درحال ذخیره‌سازی...
                    </span>
                </button>
            </div>
        </div>
    @endif

    {{-- List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($attributes as $attr)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 hover:shadow-md transition-shadow flex flex-col">
                <div class="flex items-start justify-between mb-4 pb-4 border-b border-gray-50 dark:border-gray-700/50">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $attr->name }}</h3>
                            @if($attr->unit)
                                <span class="text-[9px] font-mono bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded dir-ltr">{{ $attr->unit }}</span>
                            @endif
                        </div>
                        <span class="text-[10px] text-gray-500 bg-gray-100 dark:bg-gray-900 px-2 py-0.5 rounded-full mt-1.5 inline-block">
                            {{ $attr->type === 'color' ? 'پالت رنگی / پترن' : ($attr->type === 'image' ? 'عکس و متن' : 'ساده (select)') }}
                        </span>
                    </div>
                    <div class="flex gap-1">
                        <button wire:click="openForm({{ $attr->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                        <button wire:click="delete({{ $attr->id }})" wire:confirm="آیا از حذف این ویژگی و تمامی مقادیر آن مطمئن هستید؟" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mt-auto">
                    @foreach($attr->values->take(10) as $val)
                        @if($attr->type === 'color' || $attr->type === 'image')
                            <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 px-2 py-1 rounded-lg" title="{{ $val->value }}">
                                {{-- اگر عکس داشت عکس رو نشون بده، در غیر اینصورت هکس کالر رو --}}
                                @if($val->meta_value && str_starts_with($val->meta_value, 'attributes/'))
                                    <img src="{{ Storage::url($val->meta_value) }}" class="w-4 h-4 rounded-full object-cover shadow-sm border border-gray-200 dark:border-gray-700">
                                @else
                                    <span class="w-4 h-4 rounded-full shadow-sm border border-gray-200 dark:border-gray-700" style="background-color: {{ $val->meta_value ?? '#000' }}"></span>
                                @endif
                                <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($val->value, 10) }}</span>
                            </div>
                        @else
                            <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 px-2 py-1 rounded-lg">
                                {{ $val->value }}
                                @if($attr->unit) <span class="opacity-60">{{ $attr->unit }}</span> @endif
                            </span>
                        @endif
                    @endforeach

                    @if($attr->values->count() > 10)
                        <span class="text-[10px] font-bold text-gray-400 px-1 py-1">و {{ $attr->values->count() - 10 }} مورد دیگر...</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="md:col-span-2 lg:col-span-3 text-center py-10 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700">
                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">هیچ ویژگی ثبت نشده است</h3>
                <p class="text-xs text-gray-500 mt-1">با استفاده از دکمه بالا اولین ویژگی تنوع‌ساز را بسازید.</p>
            </div>
        @endforelse
    </div>
</div>
