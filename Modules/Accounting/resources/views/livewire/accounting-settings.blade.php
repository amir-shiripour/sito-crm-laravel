@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

<div x-data="accountingSettingsForm({
    numbering_include_year: @entangle('numbering_include_year').live,
    numbering_prefix: @entangle('numbering_prefix').live,
    numbering_separator: @entangle('numbering_separator').live,
    numbering_length: @entangle('numbering_length').live,
})">
    <form wire:submit.prevent="saveSettings">
        {{-- Main content area. Add padding to the bottom to avoid being obscured by the sticky bar --}}
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 pb-24">
            <div x-data="{ activeTab: 'general' }">

                <div class="border-b border-gray-200 dark:border-gray-700 mb-8">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <a href="#" @click.prevent="activeTab = 'general'"
                           :class="activeTab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            عمومی
                        </a>
                        <a href="#" @click.prevent="activeTab = 'appearance'"
                           :class="activeTab === 'appearance' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            ظاهری
                        </a>
                        <a href="#" @click.prevent="activeTab = 'expense_categories'"
                           :class="activeTab === 'expense_categories' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            دسته‌بندی هزینه‌ها
                        </a>
                    </nav>
                </div>

                {{-- Tab Content --}}
                <div>
                    {{-- تب عمومی --}}
                    <div x-show="activeTab === 'general'" x-transition.opacity class="space-y-8">
                        {{-- کارت جدید: تنظیمات بانکی --}}
                        <div class="{{ $cardClass }}">
                            <div class="{{ $headerClass }}">
                                <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/20 flex items-center justify-center text-teal-600 dark:text-teal-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات بانکی</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">قوانین مربوط به حساب‌های بانکی و موجودی</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <span class="flex-grow flex flex-col">
                                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">اجازه منفی شدن موجودی حساب</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">در صورت غیرفعال بودن، سیستم از ثبت هزینه یا چک پرداختی در صورت عدم موجودی کافی جلوگیری می‌کند.</span>
                                    </span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="allow_negative_balance" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- کارت ۱: تنظیمات مالی و مالیات --}}
                        <div class="{{ $cardClass }}">
                            <div class="{{ $headerClass }}">
                                <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600 dark:text-red-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 14l-6-6" /><path d="M3 8.25V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V8.25a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 8.25z" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات مالی و مالیات</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">فعال‌سازی مالیات و نحوه گرد کردن مبالغ فاکتور</p>
                                </div>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="flex items-center justify-between">
                                    <span class="flex-grow flex-col"><span class="text-sm font-medium text-gray-800 dark:text-gray-200">فعال‌سازی سیستم مالیات</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">در صورت فعال بودن، درصد مالیات در صدور فاکتور اعمال می‌شود.</span>
                                    </span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="tax_enabled" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                                <div x-show="$wire.tax_enabled" x-transition class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                    <label for="tax_percentage" class="{{ $labelClass }}">درصد مالیات پیش‌فرض</label>
                                    <div class="relative">
                                        <input type="text" wire:model.lazy="tax_percentage" id="tax_percentage" class="{{ $inputClass }} sm:w-1/3" placeholder="مثلا: 9">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-gray-500 sm:text-sm">%</span>
                                        </div>
                                    </div>
                                    @error('tax_percentage') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                                </div>

                                {{-- Rounding Settings --}}
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                    <label class="{{ $labelClass }}">گرد کردن مبلغ نهایی فاکتور</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                                        <label class="relative flex cursor-pointer rounded-xl border bg-gray-50 p-3 focus:outline-none dark:bg-gray-800/50 transition-all @if($this->rounding_mode === 'none') border-indigo-500 ring-1 ring-indigo-500 shadow-sm @else border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 @endif">
                                            <input type="radio" wire:model.live="rounding_mode" value="none" class="sr-only">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white mx-auto">بدون گرد کردن</span>
                                        </label>
                                        <label class="relative flex cursor-pointer rounded-xl border bg-gray-50 p-3 focus:outline-none dark:bg-gray-800/50 transition-all @if($this->rounding_mode === 'up') border-indigo-500 ring-1 ring-indigo-500 shadow-sm @else border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 @endif">
                                            <input type="radio" wire:model.live="rounding_mode" value="up" class="sr-only">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white mx-auto">گرد به بالا</span>
                                        </label>
                                        <label class="relative flex cursor-pointer rounded-xl border bg-gray-50 p-3 focus:outline-none dark:bg-gray-800/50 transition-all @if($this->rounding_mode === 'down') border-indigo-500 ring-1 ring-indigo-500 shadow-sm @else border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 @endif">
                                            <input type="radio" wire:model.live="rounding_mode" value="down" class="sr-only">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white mx-auto">گرد به پایین</span>
                                        </label>
                                    </div>

                                    <div x-show="$wire.rounding_mode !== 'none'" x-transition>
                                        <label for="rounding_amount" class="{{ $labelClass }}">ضریب گرد کردن (ریال)</label>
                                        <select wire:model.lazy="rounding_amount" id="rounding_amount" class="{{ $selectClass }} sm:w-1/3">
                                            <option value="10">۱۰ ریال</option>
                                            <option value="100">۱۰۰ ریال</option>
                                            <option value="1000">۱,۰۰۰ ریال (تومان)</option>
                                            <option value="10000">۱۰,۰۰۰ ریال</option>
                                            <option value="100000">۱۰۰,۰۰۰ ریال</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-2">مثال: اگر ۱۰۰۰ را انتخاب کنید، مبلغ ۱۲,۳۴۵ به ۱۳,۰۰۰ (بالا) یا ۱۲,۰۰۰ (پایین) گرد می‌شود.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- کارت ۲: تنظیمات شماره گذاری --}}
                        <div class="{{ $cardClass }}">
                            <div class="{{ $headerClass }}">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M10.5 7H20M10.5 11H20M10.5 15H20" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات شماره‌گذاری</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی فرمت شماره فاکتورها و اسناد</p>
                                </div>
                            </div>
                            <div class="p-6 space-y-6">
                                <div>
                                    <label class="{{ $labelClass }}">حالت شماره گذاری</label>
                                    <div class="flex w-full sm:w-2/3 bg-gray-100 dark:bg-gray-900 p-1 rounded-xl">
                                        <input type="radio" id="numbering_mode_auto" wire:model.live="numbering_mode" value="auto" class="sr-only peer/auto">
                                        <label for="numbering_mode_auto" class="w-full text-center cursor-pointer py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 peer-checked/auto:bg-white dark:peer-checked/auto:bg-gray-800 peer-checked/auto:shadow peer-checked/auto:text-gray-900 dark:peer-checked/auto:text-white transition-all">
                                            خودکار
                                        </label>
                                        <input type="radio" id="numbering_mode_manual" wire:model.live="numbering_mode" value="manual" class="sr-only peer/manual">
                                        <label for="numbering_mode_manual" class="w-full text-center cursor-pointer py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 peer-checked/manual:bg-white dark:peer-checked/manual:bg-gray-800 peer-checked/manual:shadow peer-checked/manual:text-gray-900 dark:peer-checked/manual:text-white transition-all">
                                            دستی
                                        </label>
                                    </div>
                                </div>

                                <div x-show="$wire.numbering_mode === 'auto'" x-transition class="space-y-6 border-t border-gray-100 dark:border-gray-700 pt-6">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">درج سال شمسی در شماره</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" wire:model.live="numbering_include_year" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                        <div>
                                            <label for="numbering_prefix" class="{{ $labelClass }}">پیشوند</label>
                                            <input type="text" wire:model.live="numbering_prefix" id="numbering_prefix" class="{{ $inputClass }}" placeholder="مثال: INV">
                                        </div>
                                        <div>
                                            <label for="numbering_separator" class="{{ $labelClass }}">جداکننده</label>
                                            <input type="text" wire:model.live="numbering_separator" id="numbering_separator" class="{{ $inputClass }}" placeholder="مثال: -">
                                        </div>
                                        <div>
                                            <label for="numbering_length" class="{{ $labelClass }}">طول شماره</label>
                                            <input type="number" wire:model.live="numbering_length" id="numbering_length" class="{{ $inputClass }}" placeholder="مثال: 4">
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                                        <p class="{{ $labelClass }}">پیش‌نمایش زنده:</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1" dir="ltr" x-text="generatePreview()"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- کارت ۳: مدیریت واحدها --}}
                        <div class="{{ $cardClass }}">
                             <div class="{{ $headerClass }}">
                                <div class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600 dark:text-green-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10m16-10v10M4 17h16M4 7h16" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">مدیریت واحدها</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تعریف واحدهای شمارش برای استفاده در فاکتورها</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    @foreach($units as $index => $unit)
                                        <div class="flex items-center space-x-3 space-x-reverse">
                                            <div class="flex-1">
                                                <label for="unit_{{ $index }}" class="sr-only">نام واحد</label>
                                                <input type="text" wire:model.lazy="units.{{ $index }}" id="unit_{{ $index }}" placeholder="مثلا: کیلوگرم، عدد، متر" class="{{ $inputClass }}">
                                            </div>
                                            <button type="button" wire:click="removeUnit({{ $index }})" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4">
                                    <button type="button" wire:click="addUnit" class="inline-flex items-center px-3 py-2 border border-dashed border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        افزودن واحد جدید
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- تب ظاهری --}}
                    <div x-show="activeTab === 'appearance'" x-transition.opacity class="space-y-8">
                        {{-- تنظیمات قالب فاکتور --}}
                        <div class="{{ $cardClass }}">
                            <div class="{{ $headerClass }}">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">قالب فاکتور</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">انتخاب پوسته و قالب پیش‌فرض برای چاپ فاکتورها</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- قالب استاندارد --}}
                                    <label class="relative flex cursor-pointer rounded-2xl border bg-gray-50 p-4 focus:outline-none dark:bg-gray-800/50 transition-all @if($this->invoice_template === 'standard') border-indigo-500 ring-1 ring-indigo-500 shadow-md @else border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 @endif">
                                        <input type="radio" wire:model.live="invoice_template" value="standard" class="sr-only">
                                        <div class="flex w-full items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="text-sm">
                                                    <p class="font-bold text-gray-900 dark:text-white">قالب پیش‌فرض (استاندارد)</p>
                                                    <div class="text-gray-500 dark:text-gray-400 mt-1">قالب ساده و کاربردی برای تمامی مصارف عمومی.</div>
                                                </div>
                                            </div>
                                            @if($this->invoice_template === 'standard')
                                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </div>
                                    </label>

                                    {{-- قالب رسمی --}}
                                    <label class="relative flex cursor-pointer rounded-2xl border bg-gray-50 p-4 focus:outline-none dark:bg-gray-800/50 transition-all @if($this->invoice_template === 'official') border-indigo-500 ring-1 ring-indigo-500 shadow-md @else border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 @endif">
                                        <input type="radio" wire:model.live="invoice_template" value="official" class="sr-only">
                                        <div class="flex w-full items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="text-sm">
                                                    <p class="font-bold text-gray-900 dark:text-white">قالب فاکتور رسمی</p>
                                                    <div class="text-gray-500 dark:text-gray-400 mt-1">مناسب برای ارائه به دارایی و دارای استانداردهای رسمی.</div>
                                                </div>
                                            </div>
                                            @if($this->invoice_template === 'official')
                                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="{{ $cardClass }}">
                            <div class="{{ $headerClass }}">
                                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">اطلاعات فروشنده</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">این اطلاعات در سربرگ فاکتورها نمایش داده می‌شود</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="seller_name" class="{{ $labelClass }}">نام شخص حقیقی/حقوقی</label>
                                        <input type="text" wire:model.lazy="seller_name" id="seller_name" class="{{ $inputClass }}">
                                    </div>
                                    <div>
                                        <label for="national_id" class="{{ $labelClass }}">شناسه ملی</label>
                                        <input type="text" wire:model.lazy="national_id" id="national_id" class="{{ $inputClass }}">
                                    </div>
                                    <div>
                                        <label for="economic_number" class="{{ $labelClass }}">شماره اقتصادی</label>
                                        <input type="text" wire:model.lazy="economic_number" id="economic_number" class="{{ $inputClass }}">
                                    </div>
                                    <div>
                                        <label for="registration_number" class="{{ $labelClass }}">شماره ثبت</label>
                                        <input type="text" wire:model.lazy="registration_number" id="registration_number" class="{{ $inputClass }}">
                                    </div>
                                    <div>
                                        <label for="province_city" class="{{ $labelClass }}">استان - شهر</label>
                                        <input type="text" wire:model.lazy="province_city" id="province_city" class="{{ $inputClass }}">
                                    </div>
                                    <div>
                                        <label for="postal_code" class="{{ $labelClass }}">کد پستی</label>
                                        <input type="text" wire:model.lazy="postal_code" id="postal_code" class="{{ $inputClass }}">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="address" class="{{ $labelClass }}">نشانی</label>
                                        <input type="text" wire:model.lazy="address" id="address" class="{{ $inputClass }}">
                                    </div>
                                    <div>
                                        <label for="phone_fax" class="{{ $labelClass }}">شماره تلفن / نمابر</label>
                                        <input type="text" wire:model.lazy="phone_fax" id="phone_fax" class="{{ $inputClass }}">
                                    </div>
                                </div>

                                {{-- Stamp & Signature Upload --}}
                                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <h4 class="text-md font-medium text-gray-800 dark:text-white">مهر و امضای فروشنده</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-4">تصویری بدون پس‌زمینه (PNG) از مهر و امضای خود آپلود کنید تا در زیر فاکتورها چاپ شود.</p>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        {{-- Image Preview --}}
                                        <div class="md:col-span-1">
                                            <label class="{{ $labelClass }}">پیش‌نمایش</label>
                                            <div class="w-full overflow-x-auto border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl flex items-center justify-center bg-gray-50 dark:bg-gray-800 relative group p-2 min-h-[8rem]">
                                                @if($stamp_signature_image_file)
                                                    <img src="{{ $stamp_signature_image_file->temporaryUrl() }}" class="object-contain max-h-32" style="width: {{ $stamp_signature_width ?: 'auto' }}px;">
                                                @elseif($stamp_signature_image)
                                                    <img src="{{ Storage::url($stamp_signature_image) }}" class="object-contain max-h-32" style="width: {{ $stamp_signature_width ?: 'auto' }}px;">
                                                    <button type="button" wire:click="removeStampImage" class="absolute inset-0 bg-red-600/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                @else
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- File Input & Width --}}
                                        <div class="md:col-span-2 space-y-4">
                                            <div>
                                                <label class="{{ $labelClass }}">آپلود تصویر</label>
                                                <input type="file" wire:model="stamp_signature_image_file" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                                                <div wire:loading wire:target="stamp_signature_image_file" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400">در حال آپلود تصویر...</div>
                                                @error('stamp_signature_image_file') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label for="stamp_signature_width" class="{{ $labelClass }}">عرض امضا در فاکتور (پیکسل)</label>
                                                <input type="number" wire:model.lazy="stamp_signature_width" id="stamp_signature_width" class="{{ $inputClass }}" placeholder="مثلا: 150">
                                                <p class="text-xs text-gray-500 mt-1">این مقدار عرض تصویر را در فاکتور چاپی تعیین می‌کند. برای اندازه خودکار، خالی بگذارید.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Custom Fields --}}
                                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <h4 class="text-md font-medium text-gray-800 dark:text-white">فیلدهای اضافی</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">فیلدهای دلخواه برای نمایش در فاکتور</p>
                                    <div class="mt-4 space-y-4">
                                        @foreach($custom_fields as $index => $field)
                                            <div class="flex items-center space-x-3 space-x-reverse">
                                                <div class="flex-1">
                                                    <label for="custom_key_{{ $index }}" class="sr-only">عنوان فیلد</label>
                                                    <input type="text" wire:model.lazy="custom_fields.{{ $index }}.key" id="custom_key_{{ $index }}" placeholder="عنوان فیلد" class="{{ $inputClass }}">
                                                </div>
                                                <div class="flex-1">
                                                    <label for="custom_value_{{ $index }}" class="sr-only">مقدار فیلد</label>
                                                    <input type="text" wire:model.lazy="custom_fields.{{ $index }}.value" id="custom_value_{{ $index }}" placeholder="مقدار" class="{{ $inputClass }}">
                                                </div>
                                                <button type="button" wire:click="removeCustomField({{ $index }})" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-4">
                                        <button type="button" wire:click="addCustomField" class="inline-flex items-center px-3 py-2 border border-dashed border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                            افزودن فیلد جدید
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- تب دسته‌بندی هزینه‌ها --}}
                    <div x-show="activeTab === 'expense_categories'" x-transition.opacity>
                        <div class="{{ $cardClass }}">
                            <div class="{{ $headerClass }}">
                                <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600 dark:text-orange-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M10.5 7H20M10.5 11H20M10.5 15H20" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">دسته‌بندی هزینه‌ها</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">دسته‌بندی‌های مورد استفاده برای ثبت هزینه‌ها</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    @foreach($expenseCategories as $index => $category)
                                        <div class="flex items-center space-x-3 space-x-reverse">
                                            <div class="flex-1">
                                                <label for="expense_category_{{ $index }}" class="sr-only">عنوان دسته‌بندی</label>
                                                <input type="text" wire:model.lazy="expenseCategories.{{ $index }}.title" id="expense_category_{{ $index }}"
                                                       placeholder="مثلا: اجاره، حقوق، حمل و نقل"
                                                       class="{{ $inputClass }} {{ $category['is_system'] ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' : '' }}"
                                                       @if($category['is_system']) disabled @endif>
                                                @error('expenseCategories.'.$index.'.title') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                                            </div>
                                            @if(!$category['is_system'])
                                                <button type="button" wire:click="removeCategory({{ $index }})" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            @else
                                                <div class="w-8 h-8 flex items-center justify-center" title="دسته‌بندی سیستمی و غیرقابل حذف">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 0h12M9 10h.01M15 10h.01M4.879 6.879A2 2 0 016.75 6h10.5a2 2 0 011.871.879l3.129 4.693A2 2 0 0121 13.242V15a2 2 0 01-2-2H5a2 2 0 01-2-2v-1.758a2 2 0 01.879-1.67l3.129-4.693z" /></svg>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4">
                                    <button type="button" wire:click="addCategory" class="inline-flex items-center px-3 py-2 border border-dashed border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        افزودن دسته‌بندی جدید
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sticky Footer --}}
        <div class="sticky bottom-4 z-40 max-w-7xl mx-auto">
            <div class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">

                {{-- Left side in RTL: Save Button --}}
                <button type="submit"
                        class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    ذخیره تنظیمات
                    <div wire:loading wire:target="saveSettings, stamp_signature_image_file, removeStampImage" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                </button>

                {{-- Right side in RTL: Success Message --}}
                <div class="min-h-[2rem] flex items-center"> {{-- Wrapper to hold space and center message vertically --}}
                    <div x-data="{ show: false, text: '' }"
                         @notify.window="show = true; text = $event.detail[0]?.text || 'تنظیمات با موفقیت ذخیره شد.'; setTimeout(() => show = false, 3000)"
                         x-show="show"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="text-sm font-medium text-green-600"
                         style="display: none;">
                        <p x-text="text"></p>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function accountingSettingsForm(data) {
        return {
            ...data,
            generatePreview() {
                let parts = [];
                if (this.numbering_include_year) {
                    parts.push(new Date().toLocaleDateString('fa-IR-u-nu-latn').slice(0, 4));
                }
                if (this.numbering_prefix && this.numbering_prefix.trim() !== '') {
                    parts.push(this.numbering_prefix.trim());
                }
                let exampleNumber = '1'.padStart(this.numbering_length || 4, '0');
                parts.push(exampleNumber);
                return parts.join(this.numbering_separator || '-');
            },
            toEnglishDigits(value) {
                if (value === null || typeof value === 'undefined') return '';
                return String(value)
                    .replace(/[\u0660-\u0669]/g, c => c.charCodeAt(0) - 0x0660)
                    .replace(/[\u06F0-\u06F9]/g, c => c.charCodeAt(0) - 0x06F0)
                    .replace(/[^0-9.]/g, '');
            }
        }
    }
</script>
@endpush
