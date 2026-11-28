@props(['warehouse', 'stockSummary' => null])

@php
    $color = $warehouse->effective_color;
    $managers = \Modules\Warehouse\Support\WarehouseUserVisibility::filterVisibleManagers($warehouse->managers ?? collect([]));
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
                @if($warehouse->type === 'inventory')
                    <a href="{{ route('admin.warehouse.consumptions.create', ['warehouse_id' => $warehouse->id]) }}"
                       class="p-2 rounded-xl text-gray-500 hover:text-rose-600 dark:text-gray-300 dark:hover:text-rose-300 hover:bg-rose-50/60 dark:hover:bg-rose-500/15 transition-colors"
                       title="ثبت خروج / مصرف از این انبار">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>

                    <a href="{{ route('admin.warehouse.stock-levels.index', ['warehouse_id' => $warehouse->id]) }}"
                       class="p-2 rounded-xl text-gray-500 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-300 hover:bg-indigo-50/60 dark:hover:bg-indigo-500/15 transition-colors"
                       title="مشاهده موجودی این انبار">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                @endif

                @if($warehouse->type === 'inventory' && !$warehouse->is_main)
                    <a href="{{ route('admin.warehouse.transfers.create', ['destination_warehouse_id' => $warehouse->id]) }}"
                       class="p-2 rounded-xl text-gray-500 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-300 hover:bg-indigo-50/60 dark:hover:bg-indigo-500/15 transition-colors"
                       title="ثبت درخواست / انتقال کالا به این انبارک">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </a>
                @endif

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
            @elseif($warehouse->manager && $warehouse->managers->isEmpty())
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

            @if($warehouse->type === 'inventory' && is_array($stockSummary))
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center gap-2 text-[11px] font-sans">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/70 dark:border-indigo-500/30">
                            {{ number_format((int) ($stockSummary['distinct_items'] ?? 0)) }} قلم
                        </span>
                        <span class="text-slate-600 dark:text-slate-300 font-medium">
                            {{ rtrim(rtrim(number_format((float) ($stockSummary['total_units'] ?? 0), 2), '0'), '.') }} واحد
                        </span>
                    </div>
                    <a href="{{ route('admin.warehouse.stock-levels.index', ['warehouse_id' => $warehouse->id]) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 text-[11px] font-bold transition-colors no-underline">
                        <span>موجودی</span>
                        <svg class="w-3 h-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endif

            <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/60 text-[11px] text-slate-600 dark:text-slate-300">
                <span>ثبت‌کننده: <strong class="text-gray-800 dark:text-gray-200">{{ $warehouse->creator_name }}</strong></span>
                @if($warehouse->created_at)
                    <span class="dir-ltr font-sans">{{ function_exists('jdate') ? jdate($warehouse->created_at)->format('Y/m/d') : $warehouse->created_at->format('Y-m-d') }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
