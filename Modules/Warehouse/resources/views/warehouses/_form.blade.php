@csrf

@php
    $suggestedColors = [
        '#4f46e5' => 'نیلی سازمانی',
        '#0d9488' => 'یشمی زمردی',
        '#2563eb' => 'آبی اقیانوسی',
        '#059669' => 'سبز',
        '#d97706' => 'کهربایی',
        '#dc2626' => 'قرمز اناری',
        '#7c3aed' => 'بنفش سلطنتی',
        '#4b5563' => 'طوسی تیره',
    ];

    $currentColor = old('color', $warehouse->color ?? ($warehouse->type === 'inventory' ? '#4f46e5' : '#0d9488'));
@endphp

<div x-data="{
    selectedColor: '{{ $currentColor }}',
    isActive: {{ old('is_active', $warehouse->is_active ?? true) ? 'true' : 'false' }},
    setColor(c) {
        this.selectedColor = c;
    }
}" class="space-y-6">

    @if($warehouse->exists && $warehouse->is_main)
        <input type="hidden" name="type" value="{{ $warehouse->type }}">
    @elseif($warehouse->exists)
        <input type="hidden" name="type" value="{{ $warehouse->type }}">
    @else
        <input type="hidden" name="type" value="{{ $type ?? 'inventory' }}">
    @endif

    {{-- Main Form Card (Fully Harmonized with Panel Dark & Light Theme) --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8 space-y-6">

        {{-- Section Title --}}
        <div class="flex items-center gap-2.5 pb-4 border-b border-gray-200 dark:border-gray-700">
            <span class="w-3 h-3 rounded-full shrink-0" :style="'background-color: ' + selectedColor"></span>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                مشخصات و اطلاعات انبار
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Name --}}
            <div>
                <label for="name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    نام انبار <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', $warehouse->name) }}"
                       placeholder="مثال: انبارک غرب تهران یا سوله قطعات"
                       required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900 font-sans">
            </div>

            {{-- Code --}}
            <div>
                <label for="code" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    کد انبار <span class="text-gray-400 text-[11px] font-normal">(در صورت خالی ماندن خودکار تولید می‌شود)</span>
                </label>
                <input type="text"
                       name="code"
                       id="code"
                       value="{{ old('code', $warehouse->code) }}"
                       placeholder="مثال: WH-INV-02"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900 font-sans dir-ltr text-left">
            </div>

            {{-- Parent Warehouse (only for sub-warehouses) --}}
            @if(!$warehouse->exists || !$warehouse->is_main)
                <div>
                    <label for="parent_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                        انبار والد (بالادستی)
                    </label>
                    <select name="parent_id"
                            id="parent_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 font-sans">
                        <option value="">بدون والد مستقیم (سطح ریشه)</option>
                        @foreach($parentWarehouses as $pWarehouse)
                            <option value="{{ $pWarehouse->id }}" {{ (string)old('parent_id', $warehouse->parent_id) === (string)$pWarehouse->id ? 'selected' : '' }}>
                                {{ $pWarehouse->name }} ({{ $pWarehouse->code }}) {{ $pWarehouse->is_main ? '— [انبار مرکزی]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Manager --}}
            <div>
                <label for="manager" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    مسئول انبار / امین اموال
                </label>
                <input type="text"
                       name="manager"
                       id="manager"
                       value="{{ old('manager', $warehouse->manager) }}"
                       placeholder="مثال: مهندس حسینی"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900 font-sans">
            </div>

            {{-- Phone --}}
            <div>
                <label for="phone" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    شماره تماس مستقیم یا داخلی
                </label>
                <input type="text"
                       name="phone"
                       id="phone"
                       value="{{ old('phone', $warehouse->phone) }}"
                       placeholder="مثال: 02188888888"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900 font-sans dir-ltr text-left">
            </div>

            {{-- Location --}}
            <div>
                <label for="location" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                    موقعیت فیزیکی یا نشانی
                </label>
                <input type="text"
                       name="location"
                       id="location"
                       value="{{ old('location', $warehouse->location) }}"
                       placeholder="مثال: طبقه همکف، سوله غربی، قفسه شماره ۳"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900 font-sans">
            </div>
        </div>

        {{-- Color Selection --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                رنگ سازمانی و شناسه بصری انبار
            </label>

            <div class="flex items-center gap-2 flex-wrap">
                @foreach($suggestedColors as $hex => $label)
                    <button type="button"
                            @click="setColor('{{ $hex }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition-all"
                            :class="selectedColor.toLowerCase() === '{{ strtolower($hex) }}'
                                ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:border-indigo-400 dark:bg-indigo-950/60 dark:text-indigo-300'
                                : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                        <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs" style="background-color: {{ $hex }}"></span>
                        <span>{{ $label }}</span>
                    </button>
                @endforeach
            </div>

            <div class="flex items-center gap-3 pt-1">
                <span class="text-xs text-gray-500 dark:text-gray-400">یا انتخاب رنگ دلخواه:</span>
                <input type="color"
                       x-model="selectedColor"
                       class="w-9 h-9 rounded-xl cursor-pointer border border-gray-200 dark:border-gray-700 bg-transparent p-0.5">
                <input type="text"
                       name="color"
                       x-model="selectedColor"
                       placeholder="#4f46e5"
                       class="w-28 rounded-xl border border-gray-200 bg-gray-50/70 px-3 py-1.5 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 font-sans text-center dir-ltr">
            </div>
        </div>

        {{-- Description --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <label for="description" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                توضیحات و یادداشت تکمیلی
            </label>
            <textarea name="description"
                      id="description"
                      rows="3"
                      placeholder="یادداشت‌های فنی، دسترسی و شرایط نگهداری اقلام..."
                      class="w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900 font-sans">{{ old('description', $warehouse->description) }}</textarea>
        </div>

        {{-- Active Status Toggle --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">وضعیت فعالیت انبار</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">انبارهای غیرفعال در لیست فعال داشبورد نمایش داده نخواهند شد.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       x-model="isActive"
                       class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
            </label>
        </div>
    </div>

    {{-- Bottom Action Buttons (Standard Panel Buttons) --}}
    <div class="flex items-center justify-between gap-3 pt-2">
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-bold transition-all">
            انصراف
        </a>
        <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $warehouse->exists ? 'ذخیره تغییرات' : 'ثبت و ایجاد انبار' }}
        </button>
    </div>
</div>
