{{-- modules/Reminders/resources/views/widgets/today-reminders.blade.php --}}

@php
    use Modules\Reminders\Entities\Reminder;
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;

    $user = auth()->user();
    $now  = now();

    $endOfDay = $now->copy()->endOfDay();

    // برای ویجت "یادآوری‌های من"، باید فقط یادآوری‌های شخص کاربر فیلتر شود
    // و همچنین یادآوری‌های گذشته که هنوز باز هستند هم نمایش داده شوند
    $reminders = Reminder::query()
        ->where('user_id', $user->id)
        ->open()
        ->where('remind_at', '<=', $endOfDay)
        ->with('task')
        ->get()
        ->sortByDesc(fn(Reminder $r) => $r->relatedPriorityWeight())
        ->values();

    $statusBadges = [
        Reminder::STATUS_OPEN     => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700/60',
        Reminder::STATUS_DONE     => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700/60',
        Reminder::STATUS_CANCELED => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600',
    ];
@endphp

<div class="h-full space-y-4 text-sm text-gray-800 dark:text-gray-200">
    {{-- هدر ویجت --}}
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3M5 21h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 13h6m-6 4h3"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                    یادآوری‌های امروز من
                </h2>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                    یادآوری‌های باز امروز و روزهای قبل نمایش داده می‌شوند.
                </p>
            </div>
        </div>

        <a href="{{ route('user.reminders.index') }}"
           class="text-[11px] text-emerald-600 hover:text-emerald-700 dark:text-emerald-300 dark:hover:text-emerald-200">
            همه یادآوری‌ها
        </a>
    </div>

    @if($reminders->isEmpty())
        <div class="flex flex-col items-center justify-center py-6 text-xs text-gray-500 dark:text-gray-400">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M18 8a6 6 0 10-12 0v4a6 6 0 0012 0V8z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M13.73 21a2 2 0 01-3.46 0" />
            </svg>
            <p>برای امروز یادآوری بازی ندارید 🎉</p>
        </div>
    @else
        <div class="space-y-3 max-h-72 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
            @foreach($reminders as $reminder)
                @php
                    $task    = $reminder->related_type === 'TASK' ? $reminder->task : null;
                    $isFu    = $task && $task->task_type === Task::TYPE_FOLLOW_UP;
                    $dateJal = $reminder->remind_at ? Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i') : '—';
                    $priority = $task->priority ?? null;

                    $priorityBadge = match ($priority) {
                        Task::PRIORITY_CRITICAL => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/30 dark:text-red-300 dark:border-red-700/60',
                        Task::PRIORITY_HIGH     => 'bg-orange-50 text-orange-700 border-orange-100 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-700/60',
                        Task::PRIORITY_MEDIUM   => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700/60',
                        Task::PRIORITY_LOW      => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600',
                        default                 => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600',
                    };

                    $statusBadge = $statusBadges[$reminder->status] ?? $statusBadges[Reminder::STATUS_OPEN];
                @endphp

                <div class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50/40
                            dark:border-gray-800 dark:bg-gray-900/40">
                    <div class="flex-1 space-y-1">
                        {{-- عنوان و لینک --}}
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full border {{ $priorityBadge }} text-[10px]">
                                    @if($isFu)
                                        پیگیری
                                    @else
                                        وظیفه
                                    @endif
                                </span>

                                @if($reminder->relatedUrl())
                                    <a href="{{ $reminder->relatedUrl() }}"
                                       class="text-xs font-medium text-gray-900 dark:text-gray-100 hover:text-emerald-600 dark:hover:text-emerald-300">
                                        {{ $reminder->relatedTitle() }}
                                    </a>
                                @else
                                    <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                        {{ $reminder->relatedTitle() }}
                                    </span>
                                @endif
                            </div>

                            <span class="text-[10px] text-gray-500 dark:text-gray-400 dir-ltr text-left">
                                {{ $dateJal }}
                            </span>
                        </div>

                        @if($reminder->message)
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ $reminder->message }}
                            </div>
                        @endif

                        {{-- برچسب وضعیت + دکمه انجام شد --}}
                        <div class="flex items-center justify-between mt-1 gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] {{ $statusBadge }}">
                                {{ \Modules\Reminders\Entities\Reminder::statusOptions()[$reminder->status] ?? $reminder->status }}
                            </span>

                            @can('reminders.edit')
                                <form method="POST"
                                      action="{{ route('user.reminders.update-status', $reminder) }}"
                                      class="inline-flex items-center gap-1"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}">

                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px]
                                                   bg-emerald-600 text-white hover:bg-emerald-700
                                                   disabled:opacity-60 disabled:cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>انجام شد</span>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
