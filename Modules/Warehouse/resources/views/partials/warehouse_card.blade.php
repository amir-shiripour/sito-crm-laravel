@props(['warehouse'])

@php
    $color = $warehouse->effective_color;
    $managers = $warehouse->managers ?? collect([]);
@endphp

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 transition-all hover:border-indigo-300 dark:hover:border-indigo-600/60 flex flex-col justify-between shadow-xs">
    <div class="space-y-3.5">
        {{-- Header: Color Swatch + Name + Code + Badges --}}
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs" style="background-color: {{ $color }}"></span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                            {{ $warehouse->name }}
                        </h4>
                        @if($warehouse->is_main)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/30">
                                انبار مرکزی
                            </span>
                        @endif
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-sans tracking-wide block mt-0.5 dir-ltr text-right">
                        {{ $warehouse->code }}
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-1 shrink-0">
                <a href="{{ route('admin.warehouse.warehouses.edit', $warehouse) }}"
                   class="p-2 rounded-xl text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-indigo-50/60 dark:hover:bg-indigo-500/15 transition-colors"
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
                                class="p-2 rounded-xl text-gray-500 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400 hover:bg-rose-50/60 dark:hover:bg-rose-500/15 transition-colors"
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
        <div class="space-y-2 pt-3 border-t border-gray-100 dark:border-gray-700/60 text-xs">
            @if($warehouse->location)
                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-gray-400 shrink-0">موقعیت:</span>
                    <span class="truncate">{{ $warehouse->location }}</span>
                </div>
            @endif

            @if($managers->isNotEmpty())
                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-gray-400 shrink-0">مسئول:</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200 truncate">
                        {{ $managers->pluck('name')->join('، ') }}
                    </span>
                </div>
            @elseif($warehouse->manager)
                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-gray-400 shrink-0">مسئول:</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $warehouse->manager }}</span>
                </div>
            @endif

            @if($warehouse->phone)
                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span class="text-gray-400 shrink-0">تماس:</span>
                    <span class="font-sans dir-ltr text-left">{{ $warehouse->phone }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
