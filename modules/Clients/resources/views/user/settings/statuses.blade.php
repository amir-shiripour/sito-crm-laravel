{{-- clients::user.settings.statuses --}}
@php
    $title = 'مدیریت وضعیت‌های ' . config('clients.labels.singular');

    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                   focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all
                   dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";

    $labelClass = "block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1";

    $checkboxClass = "w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500
                      dark:border-gray-600 dark:bg-gray-800 cursor-pointer";
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    {{-- هدر صفحه --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                مدیریت وضعیت‌های {{ config('clients.labels.singular') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                تعریف، ویرایش و تنظیم وابستگی وضعیت‌ها (workflow) برای پرونده‌های {{ config('clients.labels.plural') }}.
            </p>
        </div>

        <button
            type="button"
            wire:click="createNew"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium
                       hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40
                       transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4"/>
            </svg>
            وضعیت جدید
        </button>
    </div>

    <div class="grid grid-cols-12 gap-6 items-start">

        {{-- لیست وضعیت‌ها --}}
        <div class="col-span-12 lg:col-span-6 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        لیست وضعیت‌ها
                    </h2>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $statuses->count() }} مورد
                    </span>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($statuses as $status)
                        <div class="px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/70 transition-colors">
                            <div class="flex items-center gap-3">
                                {{-- رنگ وضعیت --}}
                                <span class="inline-flex h-5 w-5 rounded-full border border-gray-200 dark:border-gray-700"
                                      style="background-color: {{ $status->color ?? '#e5e7eb' }}"></span>

                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $status->label }}
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-gray-100 text-gray-600
                                                     dark:bg-gray-700 dark:text-gray-300">
                                            {{ $status->key }}
                                        </span>
                                        @if($status->is_system)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
                                                سیستمی
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $status->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                            {{ $status->is_active ? 'فعال' : 'غیرفعال' }}
                                        </span>

                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ $status->show_in_quick ? 'نمایش در ایجاد سریع' : 'عدم نمایش در ایجاد سریع' }}
                                        </span>

                                        @if(!empty($status->allowed_from))
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M13 7h8m0 0v8m0-8l-8 8M11 17H3m0 0V9m0 8l8-8"/>
                                                </svg>
                                                از:
                                                {{ implode('، ', $status->allowed_from ?? []) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="edit({{ $status->id }})"
                                    class="px-2 py-1 rounded-lg text-xs text-indigo-600 bg-indigo-50 hover:bg-indigo-100
                                           dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50 transition-colors">
                                    ویرایش
                                </button>

                                <button
                                    type="button"
                                    @if($status->is_system)
                                        disabled
                                    class="px-2 py-1 rounded-lg text-xs text-gray-400 bg-gray-100 cursor-not-allowed
                                               dark:bg-gray-700 dark:text-gray-500"
                                    @else
                                        wire:click="delete({{ $status->id }})"
                                    onclick="return confirm('این وضعیت حذف شود؟');"
                                    class="px-2 py-1 rounded-lg text-xs text-red-600 bg-red-50 hover:bg-red-100
                                               dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50 transition-colors"
                                    @endif
                                >
                                    حذف
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            هنوز وضعیتی ثبت نشده است.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- فرم ایجاد / ویرایش --}}
        <div class="col-span-12 lg:col-span-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-5">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        {{ $editingId ? 'ویرایش وضعیت' : 'ایجاد وضعیت جدید' }}
                    </h2>
                    @if($editingId)
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            #{{ $editingId }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- key --}}
                    <div>
                        <label class="{{ $labelClass }}">کلید سیستمی (key)</label>
                        <input
                            type="text"
                            class="{{ $inputClass }} dir-ltr font-mono text-xs"
                            wire:model.defer="key"
                            @if($is_system) disabled @endif
                            placeholder="مثلاً: new, active, canceled"
                        >
                        @error('key')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                        @if($is_system)
                            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                کلید وضعیت سیستمی قابل ویرایش نیست.
                            </p>
                        @endif
                    </div>

                    {{-- label --}}
                    <div>
                        <label class="{{ $labelClass }}">عنوان نمایشی</label>
                        <input
                            type="text"
                            class="{{ $inputClass }}"
                            wire:model.defer="label"
                            placeholder="مثلاً: جدید، فعال، لغو شده"
                        >
                        @error('label')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- color --}}
                    <div>
                        <label class="{{ $labelClass }}">رنگ (badge)</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="color"
                                class="h-9 w-12 rounded-lg border border-gray-200 dark:border-gray-600 bg-transparent cursor-pointer"
                                wire:model.defer="color"
                            >
                            <input
                                type="text"
                                class="{{ $inputClass }} dir-ltr font-mono text-xs"
                                wire:model.defer="color"
                                placeholder="#10b981"
                            >
                        </div>
                        @error('color')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- sort_order --}}
                    <div>
                        <label class="{{ $labelClass }}">ترتیب نمایش (sort_order)</label>
                        <input
                            type="number"
                            class="{{ $inputClass }}"
                            wire:model.defer="sort_order"
                            placeholder="مثلاً: 10"
                        >
                        @error('sort_order')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="inline-flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-700/40">
                        <input
                            type="checkbox"
                            class="{{ $checkboxClass }}"
                            wire:model.defer="is_active"
                        >
                        <span class="text-xs text-gray-700 dark:text-gray-300">فعال</span>
                    </label>

                    <label class="inline-flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-700/40">
                        <input
                            type="checkbox"
                            class="{{ $checkboxClass }}"
                            wire:model.defer="show_in_quick"
                        >
                        <span class="text-xs text-gray-700 dark:text-gray-300">نمایش در ایجاد سریع</span>
                    </label>

                    @if($is_system)
                        <div class="flex items-center text-[11px] text-gray-500 dark:text-gray-400">
                            وضعیت سیستمی (غیرقابل حذف)
                        </div>
                    @endif
                </div>

                {{-- وابستگی‌ها: از چه وضعیت‌هایی می‌توان به این وضعیت رسید --}}
                <div class="mt-4 space-y-2">
                    <label class="{{ $labelClass }}">وابستگی وضعیت‌ها (allowed_from)</label>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-2">
                        اگر انتخاب کنید، فقط وقتی می‌توان این وضعیت را برای یک پرونده انتخاب کرد که وضعیت فعلی آن
                        یکی از گزینه‌های زیر باشد. اگر خالی بگذارید، از همه وضعیت‌ها قابل انتخاب است.
                    </p>

                    <div class="flex flex-wrap gap-2">
                        @foreach($statuses as $st)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-gray-200 text-xs cursor-pointer
                                          hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40
                                          {{ in_array($st->key, $allowed_from, true) ? 'bg-indigo-50 border-indigo-300 text-indigo-700 dark:bg-indigo-900/40 dark:border-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300' }}">
                                <input
                                    type="checkbox"
                                    class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-500"
                                    wire:model.defer="allowed_from"
                                    value="{{ $st->key }}"
                                >
                                <span>{{ $st->label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('allowed_from')
                    <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                {{-- دکمه‌ها --}}
                <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        wire:click="createNew"
                        class="px-4 py-2 rounded-xl border border-gray-300 text-sm text-gray-700 bg-white hover:bg-gray-50
                               dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                        انصراف / ایجاد جدید
                    </button>

                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="relative px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium
                               hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50
                               focus:ring-4 focus:ring-indigo-500/30 transition-all active:scale-95
                               disabled:opacity-70 disabled:cursor-not-allowed">
                        <span wire:loading.remove>ذخیره وضعیت</span>
                        <span wire:loading.flex class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2
                                         5.291A7.962 7.962 0 014 12H0c0
                                         3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            در حال ذخیره...
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
