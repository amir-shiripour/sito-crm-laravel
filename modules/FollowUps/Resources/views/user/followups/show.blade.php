@extends('layouts.user')

@section('content')
    @php
        use Modules\Tasks\Entities\Task;
        use Morilog\Jalali\Jalalian;
        use Modules\Reminders\Entities\Reminder;

        $statuses   = $statuses   ?? Task::statusOptions();
        $priorities = $priorities ?? Task::priorityOptions();
        $types      = $types      ?? Task::typeOptions();

        $statusLabel   = $statuses[$followUp->status]   ?? $followUp->status;
        $priorityLabel = $priorities[$followUp->priority] ?? $followUp->priority;
        $typeLabel     = $types[$followUp->task_type]   ?? $followUp->task_type;

        $taskReminders = class_exists(Reminder::class)
            ? Reminder::where('related_type', 'TASK')
                ->where('related_id', $followUp->id)
                ->orderBy('remind_at')
                ->get()
            : collect();

    @endphp

    <div class="w-full mx-auto px-4 py-8">
        {{-- هدر --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text:white">
                    جزئیات پیگیری
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    اطلاعات کامل این پیگیری در زیر نمایش داده شده است.
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('followups.edit')
                    <a href="{{ route('user.followups.edit', $followUp) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-amber-500 text:white hover:bg-amber-600">
                        ویرایش
                    </a>
                @endcan
                <a href="{{ route('user.followups.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium border border-gray-300 text-gray-700 bg:white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    بازگشت
                </a>
            </div>
        </div>

        <div class="space-y-6 mb-6">
            {{-- کارت اصلی --}}
            <div class="bg:white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $followUp->title }}
                        </h2>
                        @if($followUp->description)
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                {{ $followUp->description }}
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-2 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100">
                            نوع: {{ $typeLabel }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                            @if($followUp->status === Task::STATUS_DONE)
                                bg-emerald-50 text-emerald-700
                            @else
                                bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100
                            @endif">
                            وضعیت: {{ $statusLabel }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                            @if($followUp->priority === Task::PRIORITY_HIGH || $followUp->priority === Task::PRIORITY_CRITICAL)
                                bg-red-50 text-red-700
                            @elseif($followUp->priority === Task::PRIORITY_MEDIUM)
                                bg-amber-50 text-amber-700
                            @else
                                bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100
                            @endif">
                            اولویت: {{ $priorityLabel }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">مسئول</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ optional($followUp->assignee)->name ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">مشتری</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            @if($followUp->client)
                                <a href="{{ route('user.clients.show', $followUp->client) }}"
                                   class="hover:text-indigo-600">
                                    {{ $followUp->client->full_name }}
                                </a>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تاریخ سررسید</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            @if($followUp->due_at)
                                <span class="dir-ltr">
                                    {{ Jalalian::fromCarbon($followUp->due_at)->format('Y/m/d H:i') }}
                                </span>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- کارت متادیتا / زمان‌ها --}}
            <div class="bg:white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="w-1 h-4 bg-indigo-500 rounded-full"></span>
                    اطلاعات سیستمی
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">ایجاد کننده</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ optional($followUp->creator)->name ?? 'سیستم' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تاریخ ایجاد</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $followUp->created_at ? Jalalian::fromCarbon($followUp->created_at)->format('Y/m/d H:i') : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تاریخ اتمام</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            @if($followUp->completed_at)
                                {{ Jalalian::fromCarbon($followUp->completed_at)->format('Y/m/d H:i') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- کارت ارتباط با مشتری (جزئی‌تر) --}}
            <div class="bg:white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="w-1 h-4 bg-pink-500 rounded-full"></span>
                    ارتباط با مشتری
                </h3>

                @if($followUp->client)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">نام مشتری</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                <a href="{{ route('user.clients.show', $followUp->client) }}"
                                   class="hover:text-indigo-600">
                                    {{ $followUp->client->full_name }}
                                </a>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تلفن</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100 dir-ltr text-right">
                                {{ $followUp->client->phone ?? '—' }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        این پیگیری به هیچ مشتری‌ای متصل نشده است.
                    </div>
                @endif
            </div>
        </div>
        {{-- یادآوری‌های مرتبط با این وظیفه --}}

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                یادآوری‌ها
            </h3>

            @if($taskReminders->isEmpty())
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    برای این وظیفه هنوز یادآوری‌ای ثبت نشده است.
                </p>
            @else
                <div class="space-y-2 text-xs">
                    @foreach($taskReminders as $reminder)
                        @php
                            $dateJal = $reminder->remind_at
                                ? Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i')
                                : '—';

                            $statusLabel = Reminder::statusOptions()[$reminder->status] ?? $reminder->status;
                        @endphp

                        <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                            <div class="flex-1">
                                <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] font-medium text-gray-900 dark:text-gray-100">
                                            {{ $reminder->message ?? 'یادآوری' }}
                                        </span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 dir-ltr">
                                            {{ $dateJal }}
                                        </span>
                                </div>
                                <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    وضعیت: {{ $statusLabel }}
                                </div>
                            </div>

                            @can('reminders.edit')
                                <form method="POST"
                                      action="{{ route('user.reminders.update-status', $reminder) }}"
                                      class="shrink-0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}">

                                    <button type="submit"
                                            class="inline-flex items-center px-2 py-1 rounded-lg bg-emerald-600 text-white text-[11px] hover:bg-emerald-700">
                                        انجام شد
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                </div>
            @endif

            @can('reminders.create')
                {{-- فرم ساخت سریع یادآوری جدید برای همین وظیفه --}}
                <form method="POST" action="{{ route('user.reminders.store') }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    @csrf
                    <input type="hidden" name="related_type" value="TASK">
                    <input type="hidden" name="related_id" value="{{ $followUp->id }}">

                    <div>
                        <label class="block mb-1 text-gray-600 dark:text-gray-300">تاریخ یادآوری</label>
                        <input type="text" name="remind_date_jalali" data-jdp-only-date
                               value="{{ Jalalian::fromCarbon(now())->format('Y/m/d') }}"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                          dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100">
                    </div>

                    <div>
                        <label class="block mb-1 text-gray-600 dark:text-gray-300">ساعت</label>
                        <input type="text" name="remind_time" data-jdp-only-time
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900 dir-ltr
                                          dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100">
                    </div>

                    <div>
                        <label class="block mb-1 text-gray-600 dark:text-gray-300">متن یادآوری</label>
                        <div class="flex gap-2">
                            <input type="text" name="message"
                                   value="یادآوری انجام وظیفه: {{ $followUp->title }}"
                                   class="flex-1 rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                              dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100">
                            <button type="submit"
                                    class="px-3 py-2 rounded-xl bg-emerald-600 text-white text-[11px] font-medium hover:bg-emerald-700">
                                ثبت
                            </button>
                        </div>
                    </div>
                </form>
            @endcan
        </div>
    </div>
@endsection
@includeIf('partials.jalali-date-picker')
