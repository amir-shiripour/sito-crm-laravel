@props(['marketWarehouse'])

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-amber-200/80 dark:border-amber-900/50 p-4 transition-colors hover:border-amber-300 dark:hover:border-amber-800 flex flex-col justify-between">
    <div>
        {{-- Header --}}
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-3.5 h-3.5 rounded-full bg-amber-500 shrink-0"></span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                            {{ $marketWarehouse->name }}
                        </h4>
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900">
                            انبار فروشگاه
                        </span>
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-sans tracking-wide block mt-0.5">
                        {{ $marketWarehouse->code }}
                    </span>
                </div>
            </div>

            @if(\Illuminate\Support\Facades\Route::has('admin.market.warehouses.index'))
                <a href="{{ route('admin.market.warehouses.index') }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60 transition-colors shrink-0">
                    <span>مشاهده</span>
                    <svg class="w-3 h-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endif
        </div>

        {{-- Details --}}
        <div class="space-y-1.5 pt-2.5 border-t border-gray-100 dark:border-gray-700/60 text-xs">
            <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                <span class="text-gray-400">فروشنده/مالک:</span>
                <span class="font-bold text-gray-800 dark:text-gray-200 truncate">
                    {{ $marketWarehouse->vendor?->store_name ?? 'انبار مرکزی فروشگاه' }}
                </span>
            </div>
            @if(isset($marketWarehouse->stocks))
                <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                    <span class="text-gray-400">تنوع اقلام:</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 font-sans">
                        {{ $marketWarehouse->stocks->count() }} ردیف کالا
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
