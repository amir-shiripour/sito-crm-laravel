<div class="relative pl-2 pr-4 space-y-4 before:absolute before:right-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
    @forelse($order->stages as $stIndex => $st)
        @php
            $isSkipped = (bool) $st->is_skipped;
            $isDone = !$isSkipped && $st->is_completed;
            $wasLate = $isDone && $st->wasCompletedLate();
            $completedDelayLabel = $wasLate ? $st->completed_delay_label : null;
            $isOver = !$isDone && !$isSkipped && $st->isOverdue();
            $liveDelayLabel = $isOver ? $st->live_delay_label : null;
            $isToday = !$isDone && !$isSkipped && $st->isDueToday();
            $depth = min((int) ($st->layer_depth ?? 0), 3);
            $indentClass = $depth > 0 ? 'mr-' . ($depth * 4) . ' border-r-2 border-indigo-300/80 dark:border-indigo-500/40 pr-3' : '';

            if ($isSkipped) {
                $dotClass = 'bg-slate-400 dark:bg-slate-600 text-white';
                $cardClass = 'bg-slate-100/70 border-slate-200 dark:bg-slate-800/50 dark:border-slate-700 opacity-85';
            } elseif ($wasLate) {
                $dotClass = 'bg-amber-500 text-white shadow-sm shadow-amber-500/30';
                $cardClass = 'bg-amber-50/75 border-amber-300/90 dark:bg-amber-950/30 dark:border-amber-600/50';
            } elseif ($isDone) {
                $dotClass = 'bg-emerald-600 text-white shadow-sm shadow-emerald-500/30';
                $cardClass = 'bg-emerald-50/40 border-emerald-200/80 dark:bg-emerald-950/20 dark:border-emerald-800/40';
            } elseif ($isOver) {
                $dotClass = 'bg-rose-600 text-white shadow-sm shadow-rose-500/30 animate-pulse';
                $cardClass = 'bg-rose-50/40 border-rose-200/80 dark:bg-rose-950/20 dark:border-rose-800/40';
            } elseif ($isToday) {
                $dotClass = 'bg-amber-500 text-white';
                $cardClass = 'bg-amber-50/40 border-amber-200/80 dark:bg-amber-950/20 dark:border-amber-800/40';
            } else {
                $dotClass = 'bg-slate-300 dark:bg-slate-600 text-slate-700 dark:text-slate-200';
                $cardClass = 'bg-gray-50/70 border-gray-200/80 dark:bg-gray-900/30 dark:border-gray-700/80';
            }
        @endphp
        <div class="relative flex items-start gap-3.5 group {{ $indentClass }}">
            {{-- نقطه وضعیت در خط زمان --}}
            <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 z-10 {{ $dotClass }}">
                @if($isSkipped)
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                @elseif($isDone)
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                @else
                    <span class="text-[10px] font-bold">{{ $stIndex + 1 }}</span>
                @endif
            </div>

            {{-- کارت جزئیات مرحله --}}
            <div class="flex-1 p-3 rounded-xl border {{ $cardClass }} flex items-center justify-between gap-3 flex-wrap">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h5 class="font-bold text-xs {{ $isSkipped ? 'line-through text-slate-500 dark:text-slate-400' : 'text-gray-900 dark:text-white' }}">{{ $st->stage_title }}</h5>

                        @if($st->branch_label)
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300">
                                شاخه: {{ $st->branch_label }}
                            </span>
                        @endif

                        @if($st->isSelectionStage())
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-300">انتخاب‌گر</span>
                        @elseif($st->isDecisionStage())
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">تصمیم‌گیری</span>
                        @elseif($st->isOptionalStage())
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">اختیاری</span>
                        @endif

                        @if($isSkipped)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                رد شده (عدم نیاز)
                            </span>
                        @elseif($wasLate)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-900/60 dark:text-amber-200 dark:border-amber-600/60">
                                ✓ تکمیل با تاخیر @if($completedDelayLabel) ({{ $completedDelayLabel }}) @endif
                            </span>
                        @elseif($isDone)
                            <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400">✓ تکمیل شده به موقع</span>
                        @elseif($isOver)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 dark:bg-rose-950/70 dark:text-rose-300 dark:border-rose-700/60">
                                ! دارای تاخیر @if($liveDelayLabel) ({{ $liveDelayLabel }}) @endif
                            </span>
                        @elseif($isToday)
                            <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400">● موعد امروز</span>
                        @else
                            <span class="text-[10px] text-slate-500 dark:text-slate-300">در انتظار</span>
                        @endif
                    </div>

                    @if($st->selected_option_label)
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200/80 dark:border-indigo-500/30 text-[11px] font-bold text-indigo-700 dark:text-indigo-300">
                            <span>نتیجه انتخاب‌شده:</span>
                            <span>{{ $st->selected_option_label }}</span>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-x-4 text-[11px] text-slate-600 dark:text-slate-300">
                        <span>موعد برنامه‌ریزی: <strong>{{ $st->due_at_jalali }}</strong></span>
                        @if($st->completed_at_jalali)
                            <span class="{{ $wasLate ? 'text-amber-700 dark:text-amber-300 font-bold' : ($isSkipped ? 'text-slate-500 dark:text-slate-400' : 'text-emerald-700 dark:text-emerald-400') }}">
                                {{ $isSkipped ? 'تاریخ رد:' : 'تاریخ تایید:' }} {{ $st->completed_at_jalali }}
                            </span>
                        @endif
                        @if($st->completedBy)
                            <span>توسط: {{ $st->completedBy->name }}</span>
                        @endif
                    </div>

                    @if($st->notes)
                        <div class="text-[11px] text-slate-600 dark:text-slate-300 italic bg-white/70 dark:bg-slate-800/60 p-1.5 rounded-lg mt-1 border border-slate-200/60 dark:border-slate-700/60">
                            توضیحات: {{ $st->notes }}
                        </div>
                    @endif
                </div>

                @if(!$st->is_completed)
                    @canany(['booking.laboratory.stages.manage', 'booking.laboratory.manage'])
                    <button type="button"
                            wire:click="openStageModal({{ $st->id }})"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-bold text-xs shadow-sm shadow-indigo-500/20 transition-all shrink-0 cursor-pointer">
                        <svg wire:loading wire:target="openStageModal({{ $st->id }})" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>{{ ($st->isSelectionStage() || $st->isDecisionStage()) ? 'انتخاب و تکمیل مرحله' : ($st->isOptionalStage() ? 'تایید / عدم نیاز' : 'تایید و ثبت انجام') }}</span>
                    </button>
                    @endcanany
                @else
                    @canany(['booking.laboratory.stages.manage', 'booking.laboratory.manage'])
                    <button type="button"
                            wire:click="openRevertStageModal({{ $st->id }})"
                            wire:loading.attr="disabled"
                            title="بازگشت این مرحله به حالت در انتظار"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 border border-amber-200 dark:border-amber-700/60 text-amber-700 dark:text-amber-300 font-bold text-xs disabled:opacity-60 transition-all shrink-0 cursor-pointer">
                        <svg wire:loading.remove wire:target="openRevertStageModal({{ $st->id }})" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                        <svg wire:loading wire:target="openRevertStageModal({{ $st->id }})" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>برگشت مرحله</span>
                    </button>
                    @endcanany
                @endif
            </div>
        </div>
    @empty
        <div class="text-slate-400 dark:text-slate-400 text-xs py-2">هیچ مرحله‌ای برای این سفارش تعریف نشده است.</div>
    @endforelse
</div>
