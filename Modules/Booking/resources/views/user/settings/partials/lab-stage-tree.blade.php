@php
    $depth = $depth ?? 0;
    $maxDepth = $maxDepth ?? 3;
    $stagesExpr = $stagesExpr ?? 'category.stages';
    $depthStyles = [
        0 => 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/90',
        1 => 'border-indigo-200/80 dark:border-indigo-500/30 bg-indigo-50/30 dark:bg-indigo-950/20',
        2 => 'border-sky-200/80 dark:border-sky-500/30 bg-sky-50/30 dark:bg-sky-950/20',
        3 => 'border-amber-200/80 dark:border-amber-500/30 bg-amber-50/30 dark:bg-amber-950/20',
    ];
    $cardTone = $depthStyles[$depth] ?? $depthStyles[0];
@endphp

<div class="space-y-2">
    <template x-for="(stage, sIndex) in ({{ $stagesExpr }} || [])" :key="stage.key || ('d{{ $depth }}_' + sIndex)">
        <div class="rounded-xl border {{ $cardTone }} p-3 space-y-2.5 shadow-2xs transition-all hover:border-indigo-300 dark:hover:border-indigo-500/50">
            {{-- ردیف جمع‌وجور و تمیز مرحله --}}
            <div class="flex flex-col xl:flex-row xl:items-center gap-2.5">
                {{-- شماره و عنوان --}}
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold flex items-center justify-center shrink-0"
                          x-text="sIndex + 1"></span>

                    <input type="text" x-model="stage.title"
                           class="flex-1 min-w-0 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-2.5 py-1.5 text-xs font-bold text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20"
                           placeholder="عنوان مرحله..." required>
                </div>

                {{-- زمان‌بندی + نوع مرحله + عملیات --}}
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    {{-- گروه زمان‌بندی یکپارچه --}}
                    <div class="inline-flex items-center rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 overflow-hidden">
                        <input type="number" x-model.number="stage.offset" min="0" max="365"
                               class="w-12 border-0 bg-transparent px-1.5 py-1 text-xs font-bold text-center text-gray-900 dark:text-gray-100 focus:ring-0"
                               title="فاصله زمانی" required>
                        <select x-model="stage.unit"
                                class="border-0 border-r border-gray-200 dark:border-gray-700 bg-transparent pr-2 pl-6 py-1 text-[11px] text-slate-700 dark:text-slate-300 focus:ring-0">
                            <option value="days">روز بعد</option>
                            <option value="hours">ساعت بعد</option>
                        </select>
                    </div>

                    {{-- نوع مرحله --}}
                    <select x-model="stage.stage_type"
                            @change="onStageTypeChanged(stage)"
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-2.5 py-1 text-[11px] font-bold text-slate-700 dark:text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20">
                        <option value="standard">عادی</option>
                        <option value="selection">انتخاب‌گر</option>
                        <option value="decision">تصمیم‌گیری (انشعاب)</option>
                    </select>

                    {{-- تیک اختیاری بودن مرحله --}}
                    <label x-show="stage.stage_type !== 'decision'"
                           class="inline-flex items-center gap-1 cursor-pointer select-none px-2 py-1 rounded-lg border text-[11px] font-bold transition-colors"
                           :class="stage.is_optional ? 'bg-sky-50 border-sky-300 text-sky-700 dark:bg-sky-950/40 dark:border-sky-700/60 dark:text-sky-300' : 'bg-gray-50 border-gray-200 text-slate-600 dark:bg-gray-900 dark:border-gray-700 dark:text-slate-300'">
                        <input type="checkbox" x-model="stage.is_optional" class="sr-only">
                        <span x-text="stage.is_optional ? '✓ اختیاری (Skip)' : 'اختیاری؟'"></span>
                    </label>

                    {{-- تیک پایان کار برای مراحل غیرتصمیم‌گیری --}}
                    <label x-show="stage.stage_type !== 'decision'"
                           class="inline-flex items-center gap-1 cursor-pointer select-none px-2 py-1 rounded-lg border text-[11px] font-bold transition-colors"
                           :class="stage.is_receive ? 'bg-emerald-50 border-emerald-300 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-700/60 dark:text-emerald-300' : 'bg-gray-50 border-gray-200 text-slate-600 dark:bg-gray-900 dark:border-gray-700 dark:text-slate-300'">
                        <input type="checkbox" x-model="stage.is_receive" class="sr-only">
                        <span x-text="stage.is_receive ? '✓ دریافت نهایی' : 'پایان کار؟'"></span>
                    </label>

                    {{-- دکمه باز/بسته کردن جزئیات مراحل انتخاب‌گر یا تصمیم‌گیری --}}
                    <button type="button"
                            x-show="stage.stage_type === 'selection' || stage.stage_type === 'decision'"
                            @click="toggleStageDetails(stage)"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors"
                            :class="isStageDetailsOpen(stage)
                                ? 'bg-indigo-600 text-white'
                                : (stage.stage_type === 'decision'
                                    ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-700/60'
                                    : 'bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30')">
                        <span x-text="getStageSummaryBadge(stage)"></span>
                        <svg class="w-3 h-3 transition-transform duration-150" :class="isStageDetailsOpen(stage) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- جابجایی و حذف --}}
                    <div class="flex items-center gap-0.5 mr-auto xl:mr-0">
                        <button type="button" @click="moveStageInList({{ $stagesExpr }}, sIndex, -1)"
                                class="p-1 text-slate-400 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                title="بالا" x-show="sIndex > 0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button type="button" @click="moveStageInList({{ $stagesExpr }}, sIndex, 1)"
                                class="p-1 text-slate-400 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                title="پایین" x-show="sIndex < ({{ $stagesExpr }} || []).length - 1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <button type="button" @click="removeStageFromList({{ $stagesExpr }}, sIndex)"
                                class="p-1 text-slate-400 hover:text-rose-500 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors"
                                title="حذف مرحله">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- بخش بازشونده تنظیمات مرحله انتخاب‌گر (Selection) --}}
            <div x-show="stage.stage_type === 'selection' && isStageDetailsOpen(stage)" x-collapse
                 class="p-3 rounded-xl bg-gray-50/80 dark:bg-gray-900/60 border-r-2 border-indigo-500 space-y-2.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="font-bold text-indigo-700 dark:text-indigo-300">منبع گزینه‌ها:</span>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-slate-700 dark:text-slate-300">
                            <input type="radio" value="custom" x-model="stage.options_source" class="text-indigo-600 focus:ring-indigo-500">
                            <span>گزینه‌های دلخواه</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-slate-700 dark:text-slate-300">
                            <input type="radio" value="partners" x-model="stage.options_source" class="text-indigo-600 focus:ring-indigo-500">
                            <span>لیست مراکز همکار / میلینگ</span>
                        </label>
                    </div>

                    <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        <input type="checkbox" x-model="stage.sync_order_partner" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900">
                        <span>تغییر خودکار مقصد سفارش</span>
                    </label>
                </div>

                <div x-show="stage.options_source === 'partners'" class="flex items-center justify-between gap-2 text-[11px] text-slate-600 dark:text-slate-300 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
                    <span>مراکز فعال: <strong class="text-indigo-600 dark:text-indigo-400" x-text="(partners || []).map(p => p.name).join('، ') || 'مرکزی تعریف نشده'"></strong></span>
                    <button type="button" @click="labSubTab = 'partners'" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline shrink-0">مدیریت مراکز ←</button>
                </div>

                <div x-show="stage.options_source !== 'partners'" class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-for="(opt, oIdx) in (stage.options || [])" :key="opt.key || oIdx">
                            <div class="inline-flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1">
                                <input type="text" x-model="opt.label" placeholder="عنوان گزینه"
                                       class="w-40 border-0 bg-transparent p-0 text-xs text-gray-900 dark:text-gray-100 focus:ring-0">
                                <button type="button" @click="stage.options.splice(oIdx, 1)"
                                        class="text-slate-400 hover:text-rose-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="addSelectionOption(stage)"
                                class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold transition-colors">
                            + گزینه جدید
                        </button>
                    </div>
                </div>
            </div>

            {{-- بخش بازشونده تنظیمات مرحله تصمیم‌گیری و انشعاب (Decision) --}}
            <div x-show="stage.stage_type === 'decision' && isStageDetailsOpen(stage)" x-collapse
                 class="p-3 rounded-xl bg-gray-50/80 dark:bg-gray-900/60 border-r-2 border-amber-500 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold text-amber-700 dark:text-amber-300">مسیرهای تصمیم‌گیری این مرحله:</span>
                    <button type="button" @click="addDecisionOutcome(stage)"
                            class="px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-bold transition-colors">
                        + افزودن مسیر
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(outcome, outIdx) in (stage.outcomes || [])" :key="outcome.key || outIdx">
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2.5 space-y-2.5">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <input type="text" x-model="outcome.label" placeholder="عنوان مسیر (مثلاً: تحویل نهایی یا اصلاحیه)"
                                       class="flex-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-2.5 py-1.5 text-xs font-bold text-gray-900 dark:text-gray-100">

                                <select x-model="outcome.effect"
                                        @change="onOutcomeEffectChanged(outcome)"
                                        class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-2.5 py-1.5 text-[11px] font-bold text-gray-900 dark:text-gray-100">
                                    <option value="finish">اتمام کار (Finish)</option>
                                    <option value="skip">عدم نیاز / رد کردن (Skip)</option>
                                    @if($depth < $maxDepth)
                                        <option value="spawn_stages">باز شدن زیرمراحل</option>
                                    @endif
                                    <option value="loop_to_stage">حلقه به مرحله قبلی (Loop)</option>
                                </select>

                                @if($depth < $maxDepth)
                                    <button type="button"
                                            x-show="outcome.effect === 'spawn_stages'"
                                            @click="toggleOutcomeDetails(outcome)"
                                            class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-colors shrink-0"
                                            :class="isOutcomeDetailsOpen(outcome)
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30'">
                                        <span x-text="isOutcomeDetailsOpen(outcome) ? 'بستن زیرمراحل' : ('زیرمراحل (' + (outcome.stages ? outcome.stages.length : 0) + ')')"></span>
                                    </button>
                                @endif

                                <button type="button" @click="stage.outcomes.splice(outIdx, 1)"
                                        class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 self-end sm:self-auto"
                                        title="حذف مسیر">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- انتخاب هدف حلقه --}}
                            <div x-show="outcome.effect === 'loop_to_stage'" class="flex items-center gap-2 pr-2 border-r-2 border-indigo-400">
                                <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 shrink-0">مبدا حلقه:</span>
                                <select x-model="outcome.target_stage_key"
                                        class="flex-1 rounded-lg border border-indigo-200 dark:border-indigo-700 bg-gray-50 dark:bg-gray-900 px-2.5 py-1 text-xs text-gray-900 dark:text-gray-100">
                                    <option value="">انتخاب مرحله تصمیم‌گیری مبدا...</option>
                                    <template x-for="target in getLoopTargetCandidates(category, cIndex)" :key="target.key">
                                        <option :value="target.key" x-text="target.label"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- زیرمراحل بازشونده شاخه --}}
                            @if($depth < $maxDepth)
                                <div x-show="outcome.effect === 'spawn_stages' && isOutcomeDetailsOpen(outcome)" x-collapse
                                     class="pr-3 border-r-2 border-indigo-400/80 space-y-2 pt-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300"
                                              x-text="'مراحل مسیر «' + (outcome.label || 'جدید') + '»:'"></span>
                                        <button type="button" @click="addChildStage(outcome)"
                                                class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-colors">
                                            + افزودن زیرمرحله
                                        </button>
                                    </div>

                                    @include('booking::user.settings.partials.lab-stage-tree', [
                                        'stagesExpr' => 'outcome.stages',
                                        'depth' => $depth + 1,
                                        'maxDepth' => $maxDepth,
                                    ])

                                    <div x-show="!outcome.stages || outcome.stages.length === 0"
                                         class="p-2.5 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 text-center text-[11px] text-slate-500 dark:text-slate-400">
                                        هنوز زیرمرحله‌ای تعریف نشده است.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
