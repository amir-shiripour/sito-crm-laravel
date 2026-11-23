@props(['warehouse'])

@php
    $color = $warehouse->effective_color;
@endphp

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 transition-colors hover:border-gray-300 dark:hover:border-gray-600 flex flex-col justify-between">
    <div>
        {{-- Header: Color Tag + Name + Badges --}}
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs" style="background-color: {{ $color }}"></span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                            {{ $warehouse->name }}
                        </h4>
                        @if($warehouse->is_main)
                            <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                                انبار مرکزی
                            </span>
                        @endif
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-sans tracking-wide block mt-0.5">
                        {{ $warehouse->code }}
                    </span>
                </div>
            </div>

            {{-- Actions (Panel Standard Icon Buttons) --}}
            <div class="flex items-center gap-1 shrink-0">
                <a href="{{ route('admin.warehouse.warehouses.edit', $warehouse) }}"
                   class="p-2 rounded-xl text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-indigo-50/60 dark:hover:bg-indigo-950/50 transition-colors"
                   title="ویرایش انبار">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/>
                    </svg>
                </a>

                @if(!$warehouse->is_main)
                    <form action="{{ route('admin.warehouse.warehouses.destroy', $warehouse) }}"
                          method="POST"
                          onsubmit="return confirm('آیا از حذف این انبارک اطمینان دارید؟');"
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="p-2 rounded-xl text-gray-500 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400 hover:bg-rose-50/60 dark:hover:bg-rose-950/50 transition-colors"
                                title="حذف انبار">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Metadata / Details --}}
        <div class="space-y-1.5 pt-2.5 border-t border-gray-100 dark:border-gray-700/60 text-xs">
            @if($warehouse->location)
                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <span class="text-gray-400">مکان:</span>
                    <span class="truncate">{{ $warehouse->location }}</span>
                </div>
            @endif

            @if($warehouse->manager)
                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <span class="text-gray-400">مسئول:</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $warehouse->manager }}</span>
                </div>
            @endif

            @if($warehouse->phone)
                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <span class="text-gray-400">تماس:</span>
                    <span class="font-sans dir-ltr text-left">{{ $warehouse->phone }}</span>
                </div>
            @endif

            @if($warehouse->children_count ?? $warehouse->children->count())
                <div class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 font-bold pt-1">
                    <span>{{ $warehouse->children_count ?? $warehouse->children->count() }} انبارک متصل</span>
                </div>
            @endif
        </div>
    </div>
</div>
