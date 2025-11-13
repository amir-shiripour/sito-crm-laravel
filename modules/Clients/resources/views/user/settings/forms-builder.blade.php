{{-- clients::user.settings.forms-builder --}}
@php
    $title = 'فرم‌ساز ' . config('clients.labels.singular');
    // استایل پایه اینپوت‌ها
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1";
    $checkboxClass = "w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer";
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- هدر صفحه --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">فرم‌ساز هوشمند</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت فیلدهای سفارشی و ساختار فرم‌های {{ config('clients.labels.singular') }}</p>
        </div>
        <button wire:click="saveForm"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 transition-all active:scale-95">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
            ذخیره تغییرات
        </button>
    </div>

    <div class="grid grid-cols-12 gap-6 items-start">

        {{-- ستون کناری: لیست فرم‌ها --}}
        <div class="col-span-12 lg:col-span-3 space-y-4 sticky top-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="p-4 bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        لیست فرم‌ها
                    </h2>
                </div>
                <ul class="p-2 space-y-1">
                    @foreach($forms as $f)
                        <li>
                            <button wire:click="loadForm({{ $f->id }})"
                                    class="group w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all duration-200
                                           {{ $activeFormId === $f->id
                                              ? 'bg-indigo-50 text-indigo-700 font-medium dark:bg-indigo-900/20 dark:text-indigo-300'
                                              : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700/50' }}">
                                <span>{{ $f->name }}</span>
                                @if($f->is_default)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">پیش‌فرض</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- ستون اصلی: ویرایشگر --}}
        <div class="col-span-12 lg:col-span-9 space-y-6">

            {{-- باکس تنظیمات کلی فرم --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    تنظیمات عمومی فرم
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="{{ $labelClass }}">نام نمایشی فرم</label>
                        <input type="text" wire:model="name" class="{{ $inputClass }}" placeholder="مثلاً: فرم حقوقی">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">شناسه سیستمی (Key)</label>
                        <input type="text" wire:model="key" class="{{ $inputClass }} dir-ltr font-mono text-xs" placeholder="e.g: legal_form">
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="inline-flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer w-full border border-transparent hover:border-gray-200 dark:hover:border-gray-600">
                            <input type="checkbox" wire:model="is_default" class="{{ $checkboxClass }}">
                            <span class="text-sm text-gray-700 dark:text-gray-300">تنظیم به عنوان فرم پیش‌فرض</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- جعبه ابزار افزودن فیلد --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    افزودن فیلد جدید
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach(['text','textarea','email','number','date','checkbox','radio','file','select','select-province-city','select-user-by-role','profile-photo'] as $t)
                        <button type="button" wire:click="addField('{{ $t }}')"
                                class="px-3 py-2 rounded-lg text-xs font-medium border border-gray-200 bg-gray-50 text-gray-600 hover:bg-white hover:border-indigo-300 hover:text-indigo-600 hover:shadow-sm transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:border-gray-500">
                            + {{ $t }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- لیست فیلدها (Schema) --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="font-medium text-gray-900 dark:text-white">فیلدهای فعال</h3>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-lg dark:bg-gray-700 dark:text-gray-400">{{ count($schema['fields']) }} مورد</span>
                </div>

                @foreach($schema['fields'] as $i => $field)
                    <div class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all overflow-hidden"
                         wire:key="field-{{ $field['id'] ?? $i }}">

                        {{-- نوار رنگی کنار کارت برای تفکیک بصری --}}
                        <div class="absolute right-0 top-0 bottom-0 w-1 bg-indigo-500/20 group-hover:bg-indigo-500 transition-colors"></div>

                        {{-- هدر فیلد --}}
                        <div class="flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/30 px-5 py-3 border-b border-gray-100 dark:border-gray-700/50">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-mono font-semibold bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 uppercase">
                                    {{ $field['type'] }}
                                </span>
                                <input type="text" wire:model="schema.fields.{{ $i }}.label"
                                       class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 dark:text-white placeholder-gray-400"
                                       placeholder="عنوان فیلد (Label)">
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">ID:</span>
                                    <input type="text" wire:model="schema.fields.{{ $i }}.id"
                                           class="bg-transparent border-b border-gray-300 text-xs font-mono text-gray-600 focus:border-indigo-500 focus:ring-0 w-24 text-left dir-ltr dark:border-gray-600 dark:text-gray-400"
                                           placeholder="field_id">
                                </div>
                                {{-- دکمه حذف (اختیاری - صرفا جهت زیبایی UI اضافه شده در صورت نیاز به متد deleteField) --}}
                                <button type="button" class="text-gray-400 hover:text-red-500 transition-colors" title="حذف فیلد">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        {{-- بدنه فیلد --}}
                        <div class="p-5 space-y-5">
                            {{-- تنظیمات اصلی --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                    <input type="checkbox" class="{{ $checkboxClass }}" wire:model="schema.fields.{{ $i }}.required">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">الزامی (Required)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                    <input type="checkbox" class="{{ $checkboxClass }}" wire:model="schema.fields.{{ $i }}.quick_create">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">نمایش در ایجاد سریع</span>
                                </label>

                                @if(in_array($field['type'], ['text','email','number','date','textarea']))
                                    <div class="sm:col-span-2">
                                        <input type="text" class="{{ $inputClass }} !py-1.5 !text-xs" placeholder="Validation Rules (e.g: string|max:255)"
                                               wire:model="schema.fields.{{ $i }}.validate">
                                    </div>
                                @endif
                            </div>

                            {{-- تنظیمات اختصاصی بر اساس نوع --}}
                            @if($field['type'] === 'file')
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-900/30">
                                    <div>
                                        <label class="{{ $labelClass }}">Max Size (MB)</label>
                                        <input type="number" class="{{ $inputClass }}" wire:model="schema.fields.{{ $i }}.max_mb">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="{{ $labelClass }}">Allowed Types</label>
                                        <input type="text" class="{{ $inputClass }} dir-ltr" placeholder="image/*,application/pdf"
                                               wire:model="schema.fields.{{ $i }}.accept">
                                    </div>
                                </div>
                            @endif

                            @if($field['type'] === 'select' || $field['type'] === 'radio')
                                <div class="space-y-2">
                                    <label class="{{ $labelClass }}">گزینه‌ها (JSON Format: <code>{"key":"Label"}</code>)</label>
                                    <textarea class="{{ $inputClass }} font-mono text-xs dir-ltr" rows="3"
                                              placeholder='{"m":"مرد", "f":"زن"}'
                                              wire:model.lazy="schema.fields.{{ $i }}.options_json"></textarea>
                                </div>
                            @endif

                            @if($field['type'] === 'select-province-city')
                                <div class="flex items-center gap-2 text-sm text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    داده‌های این فیلد به صورت خودکار از لیست استان‌ها و شهرهای ایران بارگذاری می‌شود.
                                </div>
                            @endif

                            @if($field['type'] === 'select-user-by-role')
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-gray-700/30 dark:border-gray-600">
                                    <div>
                                        <label class="{{ $labelClass }}">Role Name</label>
                                        <input type="text" class="{{ $inputClass }} dir-ltr" placeholder="e.g: sales"
                                               wire:model="schema.fields.{{ $i }}.role">
                                    </div>
                                    <div class="flex flex-col justify-end pb-2 space-y-2">
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" class="{{ $checkboxClass }}" wire:model="schema.fields.{{ $i }}.multiple">
                                            <span class="text-xs text-gray-600 dark:text-gray-400">انتخاب چندگانه (Multiple)</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" class="{{ $checkboxClass }}" wire:model="schema.fields.{{ $i }}.lock_current_if_role">
                                            <span class="text-xs text-gray-600 dark:text-gray-400">قفل روی کاربر فعلی</span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if(empty($schema['fields']))
                    <div class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">هنوز فیلدی اضافه نشده است</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">از جعبه ابزار بالا برای افزودن اولین فیلد استفاده کنید.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
