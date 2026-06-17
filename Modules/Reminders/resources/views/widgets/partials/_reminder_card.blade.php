@php
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;

    $task    = $reminder->related_type === 'TASK' ? $reminder->task : null;
    $isFu    = $task && $task->task_type === Task::TYPE_FOLLOW_UP;
    $priority = $task->priority ?? null;
    $taskStatus = $task->status ?? null;
    
    $relatedModel = null;
    $relatedUrl = null;
    $isClient = false;
    
    if ($task) {
        if ($task->related_type === Task::RELATED_TYPE_CLIENT && $task->relatedClient) {
            if (auth()->user()->can('clients.view') && $task->relatedClient->isVisibleFor(auth()->user())) {
                $relatedModel = $task->relatedClient;
                $relatedUrl = route('user.clients.show', $task->relatedClient->id);
                $isClient = true;
            }
        } elseif ($task->related_type === Task::RELATED_TYPE_USER && $task->relatedUser) {
            if (auth()->user()->can('users.view')) {
                $relatedModel = $task->relatedUser;
            }
        }
    }

    $priorityBadge = match ($priority) {
        Task::PRIORITY_CRITICAL => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/30',
        Task::PRIORITY_HIGH     => 'bg-orange-50 text-orange-700 border-orange-100 dark:bg-orange-950/30 dark:text-orange-400 dark:border-orange-900/30',
        Task::PRIORITY_MEDIUM   => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/30',
        Task::PRIORITY_LOW      => 'bg-gray-50 text-gray-600 border-gray-100 dark:bg-gray-900/30 dark:text-gray-400 dark:border-gray-800/50',
        default                 => 'bg-gray-50 text-gray-600 border-gray-100 dark:bg-gray-900/30 dark:text-gray-400 dark:border-gray-800/50',
    };

    $timeDiff = $reminder->remind_at ? $reminder->remind_at->diffForHumans() : 'بدون زمان';
    $exactTime = $reminder->remind_at ? Jalalian::fromCarbon($reminder->remind_at)->format('H:i') : '';

    // دریافت تنظیمات تعویق
    $snoozeEnabled = get_setting('reminders_snooze_enabled', '1') == '1';
    $inProgressEnabled = get_setting('reminders_in_progress_enabled', '1') == '1';
    $reasonRule = get_setting('reminders_snooze_reason_required', 'optional');
    $snoozeLimit = (int) get_setting('reminders_snooze_limit', 5);
@endphp

<div class="reminder-card group relative flex flex-col gap-2.5 p-3.5 rounded-xl border border-gray-100 bg-white shadow-sm hover:shadow-md hover:border-gray-200/80 transition-all duration-200 dark:border-gray-800 dark:bg-gray-900/40 dark:hover:bg-gray-900/60 dark:hover:border-gray-700/50"
     x-data="{ showSnoozeForm: false, snoozeDuration: '15m', snoozeReason: '', customMinutes: 60, showCustomMinutes: false }">
    
    <div class="flex justify-between items-start">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-md border {{ $priorityBadge }} text-[10px] font-medium tracking-wide shadow-sm">
                @if($isFu)
                    پیگیری
                @else
                    وظیفه
                @endif
            </span>
            
            <span class="text-[11px] font-medium {{ $isOverdue ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}" dir="rtl">
                {{ $timeDiff }} 
                @if($exactTime)
                    <span class="opacity-75">({{ $exactTime }})</span>
                @endif
            </span>

            {{-- نمایش تعداد تعویق (Snooze Count Badge) --}}
            @if($reminder->snooze_count > 0)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md border text-[9px] font-semibold tracking-wide shadow-sm {{ $reminder->snooze_count >= $snoozeLimit ? 'bg-red-50 text-red-700 border-red-100 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/30' : 'bg-orange-50 text-orange-700 border-orange-100 dark:bg-orange-950/30 dark:text-orange-400 dark:border-orange-900/30' }}">
                     تعویق: {{ $reminder->snooze_count }} بار
                </span>
            @endif
        </div>

        {{-- Dropdown Menu (Alpine) --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>
            
            <div x-show="open" x-transition.opacity.duration.150ms style="display: none;" class="absolute left-0 mt-1.5 w-40 bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-100 dark:border-gray-800 py-1.5 z-20 text-[12px]">
                @if($snoozeEnabled && $reminder->status !== \Modules\Reminders\Entities\Reminder::STATUS_ESCALATED)
                    <button @click="showSnoozeForm = true; open = false" class="w-full text-right px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-800/60 text-indigo-600 dark:text-indigo-400 font-semibold transition-colors">
                        🔁 تعویق یادآوری
                    </button>
                @endif
                
                @if($reminder->snooze_count > 0)
                    <button @click="fetchHistory('{{ route('user.reminders.snooze-history', $reminder) }}', '{{ addslashes($reminder->relatedTitle()) }}'); open = false" class="w-full text-right px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-800/60 text-gray-700 dark:text-gray-200 transition-colors">
                        📋 تاریخچه تعویق
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- نمایش زمان اصلی اولیه --}}
    @if($reminder->original_remind_at && $reminder->snooze_count > 0)
        <div class="text-[10px] text-gray-400 dark:text-gray-500 -mt-1" dir="rtl">
            زمان اولیه: <span class="line-through">{{ Jalalian::fromCarbon($reminder->original_remind_at)->format('H:i - Y/m/d') }}</span>
        </div>
    @endif

    <div>
        @if($reminder->relatedUrl())
            <a href="{{ $reminder->relatedUrl() }}" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors line-clamp-1">
                {{ $reminder->relatedTitle() }}
            </a>
        @else
            <span class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1">
                {{ $reminder->relatedTitle() }}
            </span>
        @endif

        @if($relatedModel)
            <div class="flex items-center justify-between gap-3 mt-2.5 p-2.5 bg-gray-50/50 dark:bg-gray-950/40 rounded-xl border border-gray-100 dark:border-gray-800/80 transition-colors">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $isClient ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100/50 dark:border-indigo-900/20' : 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-900/20' }}">
                        @if($isClient)
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        @else
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-[11px] font-bold text-gray-900 dark:text-gray-100 truncate">
                            {{ $isClient ? $relatedModel->full_name : $relatedModel->name }}
                        </span>
                        @if($isClient && $relatedModel->username)
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono truncate mt-0.5" dir="ltr">
                                {{ '@' . $relatedModel->username }}
                            </span>
                        @elseif(!$isClient)
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                                همکار / کاربر سیستم
                            </span>
                        @endif
                    </div>
                </div>
                
                @if($isClient && $relatedUrl)
                    <div class="flex-shrink-0">
                        <a href="{{ $relatedUrl }}" class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1 text-[10px] font-semibold rounded-lg text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:text-indigo-600 transition-all dark:text-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 dark:hover:text-indigo-400 shadow-sm">
                            <span>مشاهده</span>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @if($reminder->message)
        <div class="text-[12px] text-gray-600 dark:text-gray-400 line-clamp-2 bg-gray-50/50 dark:bg-gray-950/40 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800/80">
            {{ $reminder->message }}
        </div>
    @endif

    {{-- فرم درون‌برنامه‌ای تعویق یادآوری --}}
    <div x-show="showSnoozeForm" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         style="display: none;"
         class="p-3 bg-gray-50 dark:bg-gray-950/30 rounded-xl border border-gray-100 dark:border-gray-850 space-y-3 mt-1">
        
        <div class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
            <span>🔁 تنظیم زمان تعویق</span>
        </div>

        {{-- انتخاب زمان --}}
        <div>
            <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">مدت زمان تعویق</label>
            <div class="grid grid-cols-4 gap-1">
                <button type="button" @click="snoozeDuration = '15m'; showCustomMinutes = false"
                        :class="snoozeDuration === '15m' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300 border border-gray-200 dark:border-gray-800'"
                        class="px-1 py-1.5 text-[10px] font-bold rounded-lg transition-all">
                    ۱۵ دقیقه
                </button>
                <button type="button" @click="snoozeDuration = '1h'; showCustomMinutes = false"
                        :class="snoozeDuration === '1h' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300 border border-gray-200 dark:border-gray-800'"
                        class="px-1 py-1.5 text-[10px] font-bold rounded-lg transition-all">
                    ۱ ساعت
                </button>
                <button type="button" @click="snoozeDuration = '1d'; showCustomMinutes = false"
                        :class="snoozeDuration === '1d' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300 border border-gray-200 dark:border-gray-800'"
                        class="px-1 py-1.5 text-[10px] font-bold rounded-lg transition-all">
                    فردا
                </button>
                <button type="button" @click="snoozeDuration = 'custom'; showCustomMinutes = true"
                        :class="snoozeDuration === 'custom' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300 border border-gray-200 dark:border-gray-800'"
                        class="px-1 py-1.5 text-[10px] font-bold rounded-lg transition-all">
                    سفارشی
                </button>
            </div>
        </div>

        {{-- زمان سفارشی به دقیقه --}}
        <div x-show="showCustomMinutes" style="display: none;" class="animate-in fade-in slide-in-from-top-1">
            <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">مدت به دقیقه</label>
            <input type="number" x-model="customMinutes" min="5" max="10080"
                   class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-950 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
        </div>

        {{-- ثبت دلیل تعویق --}}
        @if($reasonRule !== 'disabled')
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">
                    دلیل تعویق @if($reasonRule === 'required') <span class="text-red-500">*</span> @endif
                </label>
                <textarea x-model="snoozeReason" rows="2" 
                          placeholder="توضیح کوتاه علت تعویق..."
                          class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-950 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"></textarea>
            </div>
        @endif

        {{-- دکمه‌ها --}}
        <div class="flex items-center justify-end gap-2 pt-1">
            <button type="button" @click="showSnoozeForm = false"
                    class="px-3 py-1.5 text-[10px] font-bold rounded-lg text-gray-700 bg-white dark:text-gray-300 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-100 transition-colors">
                انصراف
            </button>
            <button type="button"
                    @click="sendAction('{{ route('user.reminders.snooze', $reminder) }}', 'PATCH', { duration: snoozeDuration, custom_minutes: customMinutes, reason: snoozeReason }, $el); showSnoozeForm = false"
                    class="px-3 py-1.5 text-[10px] font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                ثبت تعویق
            </button>
        </div>
    </div>

    <div class="pt-2.5 mt-auto flex items-center justify-between border-t border-gray-100 dark:border-gray-800/60">
        
        @if($taskStatus === Task::STATUS_TODO)
            <div class="flex items-center gap-1.5">
                @if($inProgressEnabled)
                <button @click="sendAction('{{ route('user.reminders.progress-related', $reminder) }}', 'PATCH', {}, $el)" 
                        class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50/60 hover:bg-blue-100/80 dark:bg-blue-950/40 dark:hover:bg-blue-900/30 border border-blue-100/30 dark:border-blue-900/30 px-2.5 py-1 rounded-lg transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    شروع
                </button>
                @endif
                <button @click="sendAction('{{ route('user.reminders.related-done', $reminder) }}', 'PATCH', {}, $el)" 
                        class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 bg-emerald-50/60 hover:bg-emerald-100/80 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/30 border border-emerald-100/30 dark:border-emerald-900/30 px-2.5 py-1 rounded-lg transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    انجام
                </button>
            </div>
        @else
            <button @click="sendAction('{{ route('user.reminders.related-done', $reminder) }}', 'PATCH', {}, $el)" 
                    class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 bg-emerald-50/60 hover:bg-emerald-100/80 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/30 border border-emerald-100/30 dark:border-emerald-900/30 px-2.5 py-1 rounded-lg transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                انجام {{ $isFu ? 'پیگیری' : 'وظیفه' }}
            </button>
        @endif

        @if($reminder->relatedUrl())
            <a href="{{ $reminder->relatedUrl() }}" 
                    class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100 bg-gray-50 hover:bg-gray-100/80 dark:bg-gray-800/40 dark:hover:bg-gray-800 border border-gray-100 dark:border-gray-700 px-2.5 py-1 rounded-lg transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                مشاهده مورد
            </a>
        @endif

    </div>
</div>
