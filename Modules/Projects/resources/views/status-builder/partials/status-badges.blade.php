@if($status->is_default)
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round"
                                                                                         stroke-linejoin="round"
                                                                                         stroke-width="2.5"
                                                                                         d="M5 13l4 4L19 7"/></svg>
        پیش‌فرض
    </span>
@endif
@if($status->is_final)
    <span
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-red-50 text-red-700 border border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/30">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round"
                                                                                         stroke-linejoin="round"
                                                                                         stroke-width="2.5"
                                                                                         d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        نهایی
    </span>
@endif
@if($status->is_readonly)
    <span
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/30">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round"
                                                                                         stroke-linejoin="round"
                                                                                         stroke-width="2.5"
                                                                                         d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        فقط‌خواندنی
    </span>
@endif

@if($status->isQueued())
    <span
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/30">
        در صف
    </span>
@endif
@if($status->isInProgress())
    <span
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30">
        در حال انجام
    </span>
@endif
@if($status->isCompleted())
    <span
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30">
        تکمیل
    </span>
@endif
@if($status->isCanceled())
    <span
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/30">
        لغو شده
    </span>
@endif
@if($status->isDelayed())
    <span
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-orange-50 text-orange-700 border border-orange-100 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800/30">
        تعویق
    </span>
@endif

@php $transitions = $status->allowed_transitions ?? []; @endphp
@if(!empty($transitions))
    <span
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-violet-50 text-violet-700 border border-violet-100 dark:bg-violet-900/20 dark:text-violet-400 dark:border-violet-800/30"
        title="{{ count($transitions) }} انتقال مجاز تعریف شده">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round"
                                                                                         stroke-linejoin="round"
                                                                                         stroke-width="2"
                                                                                         d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        {{ count($transitions) }} انتقال
    </span>
@endif
