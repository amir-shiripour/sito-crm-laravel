<div x-data="{ expanded: false }" class="flex flex-col w-full">
    {{-- ردیف دسته‌بندی --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all duration-200 border border-transparent hover:border-gray-100 dark:hover:border-gray-600 group">

        <div class="flex items-center gap-3">
            {{-- دکمه باز و بسته کردن (فقط اگر زیردسته داشته باشد) --}}
            @if($category->children->count() > 0)
                <button @click="expanded = !expanded" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-colors shadow-sm">
                    <svg class="w-4 h-4 transform transition-transform duration-300" :class="expanded ? 'rotate-90' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
            @else
                <div class="w-7 h-7 flex items-center justify-center text-gray-300 dark:text-gray-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50"></span>
                </div>
            @endif

            <div class="flex items-center gap-2">
                @if($category->icon)
                    <div class="w-8 h-8 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-0.5 overflow-hidden">
                        <img src="{{ Storage::url($category->icon) }}" class="w-full h-full object-contain">
                    </div>
                @endif
                <span class="font-bold text-gray-800 dark:text-gray-100 text-sm">{{ $category->name }}</span>
                <span class="text-[10px] font-mono text-gray-400 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-md">#{{ $category->code_offset }}</span>
                @if($category->brand)
                    <span class="text-[10px] bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400 px-2 py-0.5 rounded-md font-bold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        {{ $category->brand->name }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4 pr-10 sm:pr-0">
            {{-- نشانگر تعداد فیلدها --}}
            @php $count = is_array($category->target_attributes) ? count($category->target_attributes) : 0; @endphp
            @if($count > 0)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-fuchsia-50 text-fuchsia-600 dark:bg-fuchsia-900/20 dark:text-fuchsia-400 text-[11px] font-bold border border-fuchsia-100 dark:border-fuchsia-800/30">
                    {{ $count }} فیلد
                </span>
            @endif

            {{-- عملیات --}}
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button wire:click="openForm({{ $category->id }})" class="p-1.5 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="ویرایش">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button wire:click="delete({{ $category->id }})" onclick="confirm('حذف این دسته؟') || event.stopImmediatePropagation()" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="حذف">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- زیردسته‌ها (فراخوانی بازگشتی همین فایل) --}}
    @if($category->children->count() > 0)
        <div x-show="expanded" x-collapse>
            <div class="pr-5 mt-1 border-r-2 border-gray-100 dark:border-gray-700/50 space-y-1">
                @foreach($category->children as $childCategory)
                    @include('market::livewire.admin.partials.category-tree-item', ['category' => $childCategory])
                @endforeach
            </div>
        </div>
    @endif
</div>
