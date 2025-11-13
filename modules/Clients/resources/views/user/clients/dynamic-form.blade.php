{{-- Modules/Clients/Resources/views/user/clients/dynamic-form.blade.php --}}

{{-- تعریف استایل پایه اینپوت برای استفاده مجدد در فیلدهای ثابت --}}
@php
    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

<div class="mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none overflow-hidden">

        {{-- هدر فرم --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800">
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $client?->id ? 'ویرایش پرونده' : 'ثبت پرونده جدید' }}
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $client?->id ? 'ویرایش اطلاعات '.config('clients.labels.singular', 'مشتری') : 'اطلاعات '.config('clients.labels.singular', 'مشتری').' را با دقت وارد کنید' }}
                </p>
            </div>

            <a href="{{ route('user.clients.index') }}"
               class="group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <span>بازگشت</span>
            </a>
        </div>

        <div class="p-6 sm:p-8 space-y-8">
            {{-- پیام‌ها --}}
            @if (session('success'))
                <div class="flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            {{-- بخش ۱: اطلاعات پایه --}}
            <section>
                <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold dark:bg-indigo-900/50 dark:text-indigo-300">1</span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">اطلاعات هویتی</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- full_name --}}
                    <div class="col-span-1 md:col-span-2 lg:col-span-1">
                        <label class="{{ $labelClass }}">نام و نام خانوادگی <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="full_name" placeholder="مثلاً: علی محمدی" class="{{ $baseInputClass }}" />
                        @error('full_name')
                        <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- phone --}}
                    <div>
                        <label class="{{ $labelClass }}">شماره تماس <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="phone" placeholder="0912..." class="{{ $baseInputClass }} dir-ltr text-right" />
                        @error('phone')
                        <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- email --}}
                    <div>
                        <label class="{{ $labelClass }}">ایمیل</label>
                        <input type="email" wire:model.defer="email" placeholder="example@domain.com" class="{{ $baseInputClass }} dir-ltr" />
                        @error('email')
                        <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- notes --}}
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="{{ $labelClass }}">یادداشت‌های مدیریتی</label>
                        <textarea rows="3" wire:model.defer="notes" placeholder="توضیحات اضافی در مورد این کاربر..."
                                  class="{{ $baseInputClass }} resize-none"></textarea>
                        @error('notes')
                        <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- بخش ۲: فیلدهای داینامیک (اگر وجود داشته باشد) --}}
            @if(!empty($schema['fields']))
                <section class="pt-2">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold dark:bg-indigo-900/50 dark:text-indigo-300">2</span>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">اطلاعات تکمیلی</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($schema['fields'] as $i => $field)
                            @php($fid = $field['id'] ?? "f{$i}")
                            <div wire:key="df-{{ $fid }}" class="relative">
                                <label class="{{ $labelClass }}">
                                    {{ $field['label'] ?? $fid }}
                                    @if(($field['required'] ?? false)) <span class="text-red-500">*</span> @endif
                                </label>
                                @include('clients::user.clients._dynamic-field', ['field' => $field, 'fid' => $fid])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- دکمه‌ها --}}
            <div class="flex items-center justify-end gap-3 pt-6 mt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('user.clients.index') }}"
                   class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 transition-all text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                    انصراف
                </a>
                <button wire:click="save" wire:loading.attr="disabled"
                        class="relative px-6 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 focus:ring-4 focus:ring-indigo-500/30 transition-all transform active:scale-95 text-sm font-medium disabled:opacity-70 disabled:cursor-not-allowed">
                    <span wire:loading.remove>ذخیره تغییرات</span>
                    <span wire:loading.flex class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        در حال پردازش...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
