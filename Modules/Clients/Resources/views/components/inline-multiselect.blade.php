{{--
    کامپوننت اینلاین مولتی‌سلکت (Inline MultiSelect)

    این کامپوننت برای کارکرد صحیح نیاز به یک کانتینر والد با x-data دارد که متدهای زیر را فراهم کند:
    - toggle(value)
    - remove(value)
    - متغیرهای: options, selectedValues, search, open
--}}

<div class="relative group" @click.outside="open = false">

    {{-- کانتینر اصلی (شبیه اینپوت) --}}
    <div class="flex flex-wrap gap-1.5 min-h-[42px] w-full items-center rounded-xl border px-2 py-1.5 transition-all duration-200 cursor-text shadow-sm"
         :class="open
            ? 'border-indigo-500 ring-1 ring-indigo-500 bg-white dark:bg-gray-800'
            : 'border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500'"
         @click="open = true; $nextTick(() => $refs.searchInput.focus())">
        {{-- متن راهنما (زمانی که خالی است) --}}
        <template x-if="selectedValues.length === 0 && !search">
            <span class="text-xs text-gray-400 dark:text-gray-500 px-1 pointer-events-none absolute select-none">
                <span x-text="placeholder || 'انتخاب کنید...'"></span>
            </span>
        </template>

        {{-- تگ‌های انتخاب شده --}}
        <template x-for="value in selectedValues" :key="value">
            <div
                class="inline-flex items-center gap-1 pl-2 pr-1 py-0.5 text-xs font-medium rounded-lg
                        bg-indigo-100 text-indigo-700 border border-transparent
                        dark:bg-indigo-500/20 dark:text-indigo-300 dark:border-indigo-500/10 transition-colors animate-in fade-in zoom-in duration-200">

                {{-- محاسبه لیبل --}}
                <span x-text="options.find(o => o.value == value)?.label ?? value"></span>

                <button type="button"
                        class="p-0.5 rounded-md hover:bg-indigo-200/50 dark:hover:bg-white/20 text-indigo-600 dark:text-indigo-300 transition-colors"
                        @click.stop="remove(value)">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </template>

        {{-- اینپوت جستجو (داخل کانتینر) --}}
        <input x-ref="searchInput" type="text" x-model="search"
               class="flex-1 min-w-[60px] border-0 bg-transparent p-0 text-xs text-gray-900 focus:ring-0 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 leading-relaxed"
               @keydown.backspace="if(search === '' && selectedValues.length > 0) remove(selectedValues[selectedValues.length - 1])">

        {{-- آیکون فلش --}}
        <div class="ml-auto pl-1 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    {{-- لیست کشویی --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1" style="display: none;" class="absolute z-50 mt-1 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl ring-1 ring-black ring-opacity-5
               dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
        <ul class="max-h-60 overflow-y-auto p-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">

            {{-- گزینه‌ها --}}
            <template x-for="option in filteredOptions" :key="option.value">
                <li @click="toggle(option.value); $refs.searchInput.focus()"
                    class="relative cursor-pointer select-none py-2 pl-9 pr-3 text-right text-xs rounded-lg transition-colors group"
                    :class="selectedValues.includes(option.value)
                        ? 'bg-indigo-50 text-indigo-700 font-medium dark:bg-indigo-900/30 dark:text-indigo-300'
                        : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700/50'">
                    <span x-text="option.label"></span>

                    {{-- تیک انتخاب (سمت چپ) --}}
                    <span x-show="selectedValues.includes(option.value)"
                          class="absolute inset-y-0 left-2 flex items-center text-indigo-600 dark:text-indigo-400">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                  clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
            </template>

            {{-- پیام خالی --}}
            <li x-show="filteredOptions.length === 0"
                class="py-3 text-center text-xs text-gray-500 dark:text-gray-400 italic">
                موردی یافت نشد.
            </li>
        </ul>
    </div>
</div>
