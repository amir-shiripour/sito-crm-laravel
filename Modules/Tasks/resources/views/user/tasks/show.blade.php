@extends('layouts.user')

@section('content')
    @php
        use Modules\Tasks\Entities\Task;
        use Morilog\Jalali\Jalalian;
        use Modules\Reminders\Entities\Reminder;
        use Illuminate\Support\Str;

        // دریافت آپشن‌ها
        $statuses   = $statuses   ?? Task::statusOptions();
        $priorities = $priorities ?? Task::priorityOptions();
        $types      = $types      ?? Task::typeOptions();

        // لیبل‌ها
        $statusLabel   = $statuses[$task->status]   ?? $task->status;
        $priorityLabel = $priorities[$task->priority] ?? $task->priority;
        $typeLabel     = $types[$task->task_type]   ?? $task->task_type;

        // دریافت یادآوری‌ها
        $taskReminders = class_exists(Reminder::class)
            ? Reminder::where('related_type', 'TASK')
                ->where('related_id', $task->id)
                ->orderBy('remind_at')
                ->get()
            : collect();

        // --- هلپرهای استایل ---

        // استایل وضعیت اصلی
        $mainStatusClass = match($task->status) {
            Task::STATUS_DONE => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
            Task::STATUS_CANCELED => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
            Task::STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
            default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700/50 dark:text-gray-300 dark:border-gray-600',
        };

        // استایل اولویت
        $priorityClass = match($task->priority) {
            Task::PRIORITY_CRITICAL => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
            Task::PRIORITY_HIGH => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-800',
            Task::PRIORITY_MEDIUM => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800',
            default => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/50 dark:text-gray-400 dark:border-gray-600',
        };

        // استایل اینپوت‌ها
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dark:focus:border-blue-500/50";
        $labelClass = "block mb-1.5 text-xs font-bold text-gray-700 dark:text-gray-300";

    @endphp

    <div class="w-full max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-8 bg-blue-600 rounded-full hidden sm:block"></span>
                    جزئیات وظیفه
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:pr-4">
                    مشاهده و مدیریت اطلاعات کامل وظیفه و یادآوری‌های مرتبط
                </p>
            </div>
            <div class="flex items-center gap-3">
                @can('tasks.edit')
                    <a href="{{ route('user.tasks.edit', $task) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        ویرایش
                    </a>
                @endcan
                <a href="{{ route('user.tasks.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    بازگشت
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ستون اصلی (چپ / وسط) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- کارت اطلاعات اصلی --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 relative overflow-hidden">
                    {{-- بج‌های وضعیت گوشه کارت --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $mainStatusClass }}">
                            {{ $statusLabel }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $priorityClass }}">
                            {{ $priorityLabel }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700/50 dark:text-gray-300 dark:border-gray-600">
                            {{ $typeLabel }}
                        </span>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 leading-snug">
                        {{ $task->title }}
                    </h2>

                    @if($task->description)
                        <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700/50">
                            <p class="whitespace-pre-line leading-relaxed">{{ $task->description }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 italic">توضیحاتی ثبت نشده است.</p>
                    @endif

                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- مشتری --}}
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-pink-50 text-pink-600 dark:bg-pink-500/10 dark:text-pink-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">مشتری مرتبط</span>
                                @if($relatedClient)
                                    <a href="{{ route('user.clients.show', $relatedClient) }}" class="text-sm font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {{ $relatedClient->full_name }}
                                    </a>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 dir-ltr text-right font-mono">
                                        {{ $relatedClient->phone ?? '' }}
                                    </div>
                                @elseif($task->client)
                                    <a href="{{ route('user.clients.show', $task->client) }}" class="text-sm font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {{ $task->client->full_name }}
                                    </a>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 dir-ltr text-right font-mono">
                                        {{ $task->client->phone ?? '' }}
                                    </div>
                                @else
                                    <span class="text-sm font-medium text-gray-400">—</span>
                                @endif
                            </div>
                        </div>

                        {{-- مسئول --}}
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">مسئول انجام</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ optional($task->assignee)->name ?? '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- بخش یادآوری‌ها --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            </span>
                            یادآوری‌ها
                        </h3>
                        <span class="text-xs font-medium px-2 py-1 rounded-md bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            {{ $taskReminders->count() }} مورد
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse($taskReminders as $reminder)
                            @php
                                $dateJal = $reminder->remind_at
                                    ? Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i')
                                    : '—';
                                $statusLabel = Reminder::statusOptions()[$reminder->status] ?? $reminder->status;

                                // استایل آیتم یادآوری
                                $itemClass = match($reminder->status) {
                                    Reminder::STATUS_DONE => 'bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/20',
                                    Reminder::STATUS_CANCELED => 'bg-red-50 border-red-200 dark:bg-red-500/10 dark:border-red-500/20',
                                    default => 'bg-gray-50 border-gray-200 dark:bg-gray-900/40 dark:border-gray-700',
                                };
                                $textClass = match($reminder->status) {
                                    Reminder::STATUS_DONE => 'text-emerald-700 dark:text-emerald-400',
                                    Reminder::STATUS_CANCELED => 'text-red-700 dark:text-red-400',
                                    default => 'text-gray-700 dark:text-gray-300',
                                };
                            @endphp

                            <div class="group flex items-center justify-between gap-3 p-3 rounded-xl border {{ $itemClass }} transition-all hover:shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold {{ $textClass }} truncate">
                                            {{ $reminder->message ?? 'یادآوری' }}
                                        </span>
                                        @if($reminder->status === Reminder::STATUS_DONE)
                                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 text-[10px] text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center gap-1 dir-ltr font-mono">
                                            <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            {{ $dateJal }}
                                        </span>
                                        <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                        <span>{{ $statusLabel }}</span>
                                    </div>
                                </div>

                                @can('reminders.edit')
                                    @if($reminder->status !== Reminder::STATUS_DONE && $reminder->status !== Reminder::STATUS_CANCELED)
                                        <form method="POST" action="{{ route('user.reminders.update-status', $reminder) }}" class="shrink-0">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ Reminder::STATUS_DONE }}">
                                            <button type="submit" class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white border border-gray-200 text-[10px] font-bold text-emerald-600 shadow-sm hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 dark:bg-gray-800 dark:border-gray-600 dark:text-emerald-400 dark:hover:bg-emerald-900/30 dark:hover:border-emerald-500/30 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                <span class="hidden sm:inline">انجام شد</span>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700/50 dark:text-gray-500 mb-2">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">هیچ یادآوری‌ای ثبت نشده است.</p>
                            </div>
                        @endforelse
                    </div>

                    @can('reminders.create')
                        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-3">افزودن یادآوری جدید</h4>
                            <form method="POST" action="{{ route('user.reminders.store') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                @csrf
                                <input type="hidden" name="related_type" value="TASK">
                                <input type="hidden" name="related_id" value="{{ $task->id }}">

                                <div class="sm:col-span-3">
                                    <label class="{{ $labelClass }}">تاریخ</label>
                                    <input type="text" name="remind_date_jalali" data-jdp-only-date
                                           value="{{ Jalalian::fromCarbon(now())->format('Y/m/d') }}"
                                           class="{{ $inputClass }} text-center" placeholder="1402/01/01">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}">ساعت</label>
                                    <input type="text" name="remind_time" data-jdp-only-time
                                           class="{{ $inputClass }} text-center dir-ltr" placeholder="10:30">
                                </div>

                                <div class="sm:col-span-5">
                                    <label class="{{ $labelClass }}">متن یادآوری</label>
                                    <input type="text" name="message"
                                           value="یادآوری: {{ Str::limit($task->title, 20) }}"
                                           class="{{ $inputClass }}" placeholder="متن یادآوری...">
                                </div>

                                <div class="sm:col-span-2 flex items-end">
                                    <button type="submit" class="w-full py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all active:scale-95 h-[34px]">
                                        ثبت
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endcan
                </div>

            </div>

            {{-- ستون کناری (راست) --}}
            <div class="space-y-6">

                {{-- کارت زمان‌بندی --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        زمان‌بندی
                    </h3>
                    <div class="space-y-4 relative">
                        {{-- خط اتصال --}}
                        <div class="absolute top-2 bottom-2 right-[5px] w-0.5 bg-gray-100 dark:bg-gray-700"></div>

                        <div class="relative pr-4">
                            <div class="absolute right-0 top-1.5 w-3 h-3 rounded-full bg-blue-600 border-2 border-white dark:border-gray-800"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">تاریخ سررسید</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white dir-ltr text-right">
                                {{ $task->due_at ? Jalalian::fromCarbon($task->due_at)->format('Y/m/d H:i') : '—' }}
                            </div>
                        </div>

                        <div class="relative pr-4">
                            <div class="absolute right-0 top-1.5 w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 border-2 border-white dark:border-gray-800"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">تاریخ ایجاد</div>
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 dir-ltr text-right">
                                {{ $task->created_at ? Jalalian::fromCarbon($task->created_at)->format('Y/m/d H:i') : '—' }}
                            </div>
                        </div>

                        <div class="relative pr-4">
                            <div class="absolute right-0 top-1.5 w-3 h-3 rounded-full {{ $task->completed_at ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700' }} border-2 border-white dark:border-gray-800"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">تاریخ اتمام</div>
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 dir-ltr text-right">
                                {{ $task->completed_at ? Jalalian::fromCarbon($task->completed_at)->format('Y/m/d H:i') : '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- کارت اطلاعات سیستمی --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                        اطلاعات سیستمی
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-700/50">
                            <span class="text-gray-500 dark:text-gray-400">شناسه (ID)</span>
                            <span class="font-mono text-gray-700 dark:text-gray-300">#{{ $task->id }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-700/50">
                            <span class="text-gray-500 dark:text-gray-400">ایجاد کننده</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ optional($task->creator)->name ?? 'سیستم' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-500 dark:text-gray-400">آخرین بروزرسانی</span>
                            <span class="dir-ltr text-gray-700 dark:text-gray-300">{{ $task->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                {{-- کارت مسئولیت و موجودیت مرتبط (سایدبار) --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        مسئولیت و ارتباطات
                    </h3>

                    @php
                        $assigneeMode    = $meta['assignee_mode'] ?? 'single_user';
                        $assigneeRoleIds = collect($meta['assignee_role_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                    @endphp

                    <div class="space-y-4 text-xs">
                        {{-- مسئول --}}
                        <div>
                            <div class="text-gray-500 dark:text-gray-400 mb-1">روش تعیین مسئول</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                @if($assigneeMode === 'by_roles')
                                    بر اساس نقش‌ها
                                @else
                                    کاربر مشخص
                                @endif
                            </div>
                        </div>

                        @if($assigneeMode === 'by_roles')
                            <div>
                                <div class="text-gray-500 dark:text-gray-400 mb-1">نقش‌های مجاز</div>
                                @if(!empty($assigneeRoleIds))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($allRoles->whereIn('id', $assigneeRoleIds) as $role)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 text-[10px] dark:bg-purple-900/20 dark:text-purple-300">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </div>
                        @endif

                        {{-- موجودیت مرتبط --}}
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                            @if($relatedTarget === 'user' && $relatedUser)
                                @php
                                    $relatedUserRoleIds = collect($meta['related_user_role_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                                @endphp
                                <div class="mb-2">
                                    <span class="block text-gray-500 dark:text-gray-400 mb-0.5">کاربر مرتبط</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $relatedUser->name }}</span>
                                </div>
                                @if(!empty($relatedUserRoleIds))
                                    <div>
                                        <span class="block text-gray-500 dark:text-gray-400 mb-1">نقش‌های مرتبط</span>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($allRoles->whereIn('id', $relatedUserRoleIds) as $role)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] dark:bg-indigo-900/20 dark:text-indigo-300">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            @elseif($relatedTarget === 'client' && $relatedClient)
                                @php
                                    $relatedClientStatusIds = collect($meta['related_client_status_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                                @endphp
                                <div class="mb-2">
                                    <span class="block text-gray-500 dark:text-gray-400 mb-0.5">مشتری مرتبط</span>
                                    <a href="{{ route('user.clients.show', $relatedClient) }}" class="font-bold text-gray-900 dark:text-white hover:text-blue-600 transition-colors">
                                        {{ $relatedClient->full_name }}
                                    </a>
                                </div>
                                @if(!empty($relatedClientStatusIds))
                                    <div>
                                        <span class="block text-gray-500 dark:text-gray-400 mb-1">وضعیت‌های مرتبط</span>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($clientStatuses->whereIn('id', $relatedClientStatusIds) as $st)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-pink-50 text-pink-700 text-[10px] dark:bg-pink-900/20 dark:text-pink-300">
                                                    {{ $st->label ?? $st->key }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                <span class="text-gray-400 italic">بدون ارتباط خاص</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@includeIf('partials.jalali-date-picker')
