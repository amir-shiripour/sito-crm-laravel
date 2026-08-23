
@php
    $currentTransitions = $status?->allowed_transitions ?? [];
    if (is_string($currentTransitions)) {
        $currentTransitions = json_decode($currentTransitions, true) ?? [];
    }
    $transitionTargets = $allStatuses->when($status, fn($c) => $c->where('id', '!=', $status->id));
@endphp

@if($transitionTargets->isNotEmpty())
    <div class="col-span-1 sm:col-span-3 border-t border-gray-100 dark:border-gray-700/50 pt-4 mt-2"
         x-data="{ limitTransitions: {{ !empty($currentTransitions) ? 'true' : 'false' }} }">

        <div class="flex items-center justify-between mb-3">
            <label class="block text-xs font-bold text-gray-600 dark:text-gray-400">
                انتقال‌های مجاز
                <span class="text-gray-400 font-normal mr-1">(از این وضعیت می‌توان به کجا رفت؟)</span>
            </label>
            <label
                class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 cursor-pointer select-none">
                <input type="checkbox" x-model="limitTransitions"
                       class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span x-text="limitTransitions ? 'محدود (فقط موارد انتخابی)' : 'آزاد (هر انتقالی مجاز)'"></span>
            </label>
        </div>

        <p x-show="!limitTransitions" x-cloak
           class="text-xs text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30 rounded-xl px-3 py-2 mb-3">
            همه انتقال‌ها آزاد هستند — کاربر می‌تواند از این وضعیت به هر وضعیت دیگری برود.
        </p>

        <div x-show="limitTransitions" x-cloak
             class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700">
            <p class="text-[11px] text-gray-400 mb-3">
                فقط وضعیت‌هایی که تیک زده‌اید در منوی تغییر وضعیت نمایش داده می‌شوند.
            </p>
            <div class="flex flex-wrap gap-3">
                @foreach($transitionTargets as $target)
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox"
                               name="allowed_transitions[]"
                               value="{{ $target->id }}"
                               :disabled="!limitTransitions"
                               @checked(in_array($target->id, $currentTransitions))
                               class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border transition-all"
                            style="background: {{ $target->color }}15; color: {{ $target->color }}; border-color: {{ $target->color }}40">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $target->color }}"></span>
                            {{ $target->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        @if($status && !empty($currentTransitions))
            <p class="text-[11px] text-indigo-500 dark:text-indigo-400 mt-2">
                این وضعیت محدود به {{ count($currentTransitions) }} انتقال مجاز است.
            </p>
        @endif
    </div>
@endif
