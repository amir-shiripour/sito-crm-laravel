<div class="relative group">
    {{-- کانتینر اصلی شبیه اینپوت --}}
    <div
        class="flex flex-wrap gap-1.5 min-h-[42px] items-center rounded-xl border border-gray-300 bg-white px-2 py-1.5 transition-all duration-200
               focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500
               dark:bg-gray-800 dark:border-gray-600 dark:focus-within:border-emerald-500 cursor-text shadow-sm"
        @click="open = true; $nextTick(() => $refs.searchInput.focus())"
    >
        {{-- نمایش متن راهنما وقتی خالی است --}}
        <template x-if="selectedValues.length === 0">
            <span class="text-sm text-gray-400 dark:text-gray-500 px-1 select-none pointer-events-none">
                انتخاب کنید...
            </span>
        </template>

        {{-- تگ‌های انتخاب شده --}}
        <template x-for="value in selectedValues" :key="value">
            <div
                class="inline-flex items-center gap-1 pl-2 pr-1.5 py-0.5 text-xs font-medium rounded-lg
                       bg-emerald-50 text-emerald-700 border border-emerald-100
                       dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 transition-colors"
            >
                <span
                    x-text="value === '__all__'
                        ? allLabel
                        : (options.find(o => o.value == value)?.label ?? value)"
                ></span>
                <button type="button"
                        class="p-0.5 rounded-full hover:bg-emerald-200/50 dark:hover:bg-emerald-500/30 text-emerald-600 dark:text-emerald-400 transition-colors"
                        @click.stop="clearValue(value)">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </template>

        {{-- اینپوت جستجو --}}
        <input
            x-ref="searchInput"
            type="text"
            x-model="search"
            class="flex-1 min-w-[80px] border-0 bg-transparent p-0 text-sm text-gray-900 focus:ring-0 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600"
            placeholder=""
            @keydown.backspace="if(search === '' && selectedValues.length > 0) selectedValues.pop()"
        >

        {{-- آیکون فلش کوچک --}}
        <div class="absolute left-2 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    {{-- لیست کشویی --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        x-cloak
        @click.away="open = false"
        class="absolute z-50 mt-1 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg ring-1 ring-black ring-opacity-5
               dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10"
    >
        <div class="max-h-60 overflow-y-auto py-1 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
            {{-- گزینه "همه" --}}
            <template x-if="allLabel">
                <button
                    type="button"
                    class="relative w-full cursor-pointer select-none py-2.5 pl-3 pr-9 text-right text-sm hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                    :class="isSelected('__all__') ? 'text-emerald-700 dark:text-emerald-400 font-semibold bg-emerald-50/50 dark:bg-emerald-900/10' : 'text-gray-700 dark:text-gray-200'"
                    @click.prevent="toggle('__all__'); $refs.searchInput.focus()"
                >
                    <span x-text="allLabel"></span>
                    <span x-show="isSelected('__all__')" class="absolute inset-y-0 left-0 flex items-center pl-3 text-emerald-600 dark:text-emerald-400">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
            </template>

            {{-- جداکننده --}}
            <template x-if="allLabel && filteredOptions().length > 0">
                <div class="h-px bg-gray-100 dark:bg-gray-700 my-1"></div>
            </template>

            {{-- گزینه‌ها --}}
            <template x-for="opt in filteredOptions()" :key="opt.value">
                <button
                    type="button"
                    class="relative w-full cursor-pointer select-none py-2 pl-3 pr-9 text-right text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :class="isSelected(opt.value) ? 'font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700/50' : 'text-gray-700 dark:text-gray-200'"
                    @click.prevent="toggle(opt.value); $refs.searchInput.focus()"
                >
                    <span x-text="opt.label"></span>
                    <span x-show="isSelected(opt.value)" class="absolute inset-y-0 left-0 flex items-center pl-3 text-emerald-600 dark:text-emerald-400">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
            </template>

            {{-- حالت خالی --}}
            <template x-if="filteredOptions().length === 0">
                <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center italic">
                    موردی یافت نشد.
                </div>
            </template>
        </div>
    </div>

    {{-- هیدن اینپوت‌ها --}}
    <template x-for="value in selectedValues" :key="'hidden-' + name + '-' + value">
        <input type="hidden" :name="name + '[]'" :value="value">
    </template>
</div>
