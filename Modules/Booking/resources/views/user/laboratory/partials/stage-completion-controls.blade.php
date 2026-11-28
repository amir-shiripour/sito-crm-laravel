@if($stage)
    @php
        $actionVal = $stageCompletionAction ?? 'complete';
    @endphp

    @if($stage->isOptionalStage() && !$stage->isDecisionStage())
        <div class="grid grid-cols-2 gap-2 p-2.5 rounded-2xl bg-slate-100/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
            <label class="flex items-center justify-center gap-2 p-2 rounded-xl border cursor-pointer text-xs font-bold transition-all {{ $actionVal === 'complete' ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-slate-700 dark:text-slate-300' }}">
                <input type="radio" wire:model.live="stageCompletionAction" value="complete" class="sr-only">
                <span>✓ انجام و تکمیل مرحله</span>
            </label>
            <label class="flex items-center justify-center gap-2 p-2 rounded-xl border cursor-pointer text-xs font-bold transition-all {{ $actionVal === 'skip' ? 'bg-slate-700 text-white border-slate-700 shadow-xs' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-slate-700 dark:text-slate-300' }}">
                <input type="radio" wire:model.live="stageCompletionAction" value="skip" class="sr-only">
                <span>⏭ عدم نیاز (رد کردن)</span>
            </label>
        </div>
    @endif

    @if($stage->isSelectionStage() && $actionVal !== 'skip')
        @php
            $options = $stage->getAvailableOptions();
            $cfg = is_array($stage->config_snapshot) ? $stage->config_snapshot : [];
            $syncPartner = !empty($cfg['sync_order_partner']);
        @endphp
        <div class="space-y-2.5 p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/25 border border-indigo-200/80 dark:border-indigo-800/50">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <span class="text-xs font-bold text-indigo-800 dark:text-indigo-300">انتخاب گزینه مرحله «{{ $stage->stage_title }}» *</span>
                @if($syncPartner)
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300">
                        به‌روزرسانی خودکار مقصد سفارش
                    </span>
                @endif
            </div>

            @if(!empty($options))
                <div class="grid grid-cols-1 gap-2">
                    @foreach($options as $opt)
                        @php $optKey = $opt['key'] ?? ''; @endphp
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all {{ $selectedStageOptionKey === $optKey ? 'border-indigo-600 bg-white dark:bg-indigo-500/20 dark:border-indigo-400 ring-1 ring-indigo-500/40' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-indigo-300 dark:hover:border-indigo-600' }}">
                            <input type="radio" wire:model.live="selectedStageOptionKey" value="{{ $optKey }}" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-gray-900 dark:text-slate-100">{{ $opt['label'] ?? $optKey }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="text-[11px] text-amber-700 dark:text-amber-300">هیچ گزینه‌ای برای این مرحله تعریف نشده است (می‌توانید از تنظیمات اضافه کنید).</p>
            @endif
        </div>
    @elseif($stage->isDecisionStage())
        @php
            $outcomes = $stage->getAvailableOutcomes();
            $selectedOutcome = collect($outcomes)->firstWhere('key', $selectedStageOptionKey);
            $nestedDecision = null;
            if ($selectedOutcome) {
                $eff = $selectedOutcome['effect'] ?? 'continue';
                if ($eff === 'spawn_stages' && count($selectedOutcome['stages'] ?? []) === 1) {
                    $firstChild = $selectedOutcome['stages'][0] ?? null;
                    if ($firstChild && ($firstChild['stage_type'] ?? 'standard') === 'decision') {
                        $nestedDecision = $firstChild;
                    }
                } elseif ($eff === 'loop_to_stage' && !empty($selectedOutcome['target_stage_key']) && $stage->order) {
                    $tpl = \Modules\Booking\Entities\BookingSetting::findLaboratoryStageTemplateByKey(
                        $stage->order->lab_type,
                        $stage->order->category_type,
                        $stage->order->variant_key,
                        $selectedOutcome['target_stage_key']
                    );
                    if ($tpl && ($tpl['stage_type'] ?? 'standard') === 'decision') {
                        $nestedDecision = $tpl;
                    }
                }
            }
        @endphp
        <div class="space-y-3 p-3.5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/50">
            <span class="block text-xs font-bold text-amber-800 dark:text-amber-300">انتخاب مسیر تصمیم‌گیری «{{ $stage->stage_title }}» *</span>

            <div class="grid grid-cols-1 gap-2">
                @foreach($outcomes as $out)
                    @php
                        $outKey = $out['key'] ?? '';
                        $eff = $out['effect'] ?? 'continue';
                        $effBadge = match($eff) {
                            'finish' => ['text' => 'اتمام و دریافت نهایی (سبز)', 'cls' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'],
                            'skip' => ['text' => 'عدم نیاز (انتقال به مرحله بعد)', 'cls' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'],
                            'spawn_stages' => ['text' => 'باز شدن زیرمراحل جدید (' . count($out['stages'] ?? []) . ' گام)', 'cls' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300'],
                            'loop_to_stage' => ['text' => 'حلقه اصلاحیه مجدد', 'cls' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300'],
                            default => ['text' => 'ادامه', 'cls' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'],
                        };
                    @endphp
                    <label class="flex items-center justify-between gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all {{ $selectedStageOptionKey === $outKey ? 'border-indigo-600 bg-white dark:bg-indigo-500/20 dark:border-indigo-400 ring-1 ring-indigo-500/40' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-indigo-300 dark:hover:border-indigo-600' }}">
                        <span class="flex items-center gap-2">
                            <input type="radio" wire:model.live="selectedStageOptionKey" value="{{ $outKey }}" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-gray-900 dark:text-slate-100">{{ $out['label'] ?? $outKey }}</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $effBadge['cls'] }}">{{ $effBadge['text'] }}</span>
                    </label>
                @endforeach
            </div>

            @if($nestedDecision && !empty($nestedDecision['outcomes']))
                <div class="pt-2.5 border-t border-amber-200/70 dark:border-amber-800/40 space-y-2">
                    <span class="block text-[11px] font-bold text-indigo-700 dark:text-indigo-300">
                        انتخاب سریع «{{ $nestedDecision['title'] ?? 'نوع اصلاحیه' }}» (اختیاری - قابل تعیین در مرحله بعد):
                    </span>
                    <div class="flex flex-wrap gap-2">
                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border text-[11px] cursor-pointer {{ $selectedStageSubOptionKey === '' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300' }}">
                            <input type="radio" wire:model.live="selectedStageSubOptionKey" value="" class="text-indigo-600">
                            <span>بعداً مشخص شود</span>
                        </label>
                        @foreach($nestedDecision['outcomes'] as $subOut)
                            @php $subKey = $subOut['key'] ?? ''; @endphp
                            <label class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border text-[11px] cursor-pointer {{ $selectedStageSubOptionKey === $subKey ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200' }}">
                                <input type="radio" wire:model.live="selectedStageSubOptionKey" value="{{ $subKey }}" class="text-indigo-600">
                                <span>{{ $subOut['label'] ?? $subKey }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
@endif
