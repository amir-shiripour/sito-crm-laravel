@extends('layouts.user')

@php
    use Modules\Reminders\Entities\Reminder;
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;

    $status = $status ?? 'open';
    $period = $period ?? 'today';

    $statusBadges = [
        Reminder::STATUS_OPEN => [
            'label' => 'باز',
            'class' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-700',
        ],
        Reminder::STATUS_DONE => [
            'label' => 'انجام‌شده',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-700',
        ],
        Reminder::STATUS_CANCELED => [
            'label' => 'لغو شده',
            'class' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600',
        ],
    ];

    function taskPriorityLabel($priority) {
        return [
            Task::PRIORITY_LOW      => 'کم',
            Task::PRIORITY_MEDIUM   => 'معمولی',
            Task::PRIORITY_HIGH     => 'زیاد',
            Task::PRIORITY_CRITICAL => 'بحرانی',
        ][$priority] ?? 'نامشخص';
    }
@endphp

@section('content')
    <div class="space-y-6 max-w-6xl mx-auto px-4 py-6">

        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    مدیریت یادآوری‌ها
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    مشاهده و مدیریت یادآوری‌های مرتبط با وظایف و پیگیری‌ها، همراه با فیلتر زمانی و تغییر وضعیت گروهی.
                </p>
            </div>
        </div>

        {{-- فیلترها --}}
        <form method="GET" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- وضعیت یادآوری --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        وضعیت یادآوری
                    </label>
                    <select name="status"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                   focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                   dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                        @foreach($statusFilterOptions as $key => $label)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- دوره زمانی --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        بازه زمانی
                    </label>
                    <select name="period"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                   focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                   dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                        @foreach($periodOptions as $key => $label)
                            <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- بازه دلخواه (شمسی) --}}
                <div class="grid grid-cols-2 gap-2" x-data>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            از تاریخ (شمسی)
                        </label>
                        <input type="text" name="from_jalali" data-jdp-only-date
                               value="{{ request('from_jalali') }}"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                      focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                      dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            تا تاریخ (شمسی)
                        </label>
                        <input type="text" name="to_jalali" data-jdp-only-date
                               value="{{ request('to_jalali') }}"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                      focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                      dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <a href="{{ route('user.reminders.index') }}"
                   class="px-3 py-1.5 rounded-xl border border-gray-300 text-xs text-gray-600 bg-white hover:bg-gray-50
                          dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    حذف فیلترها
                </a>
                <button type="submit"
                        class="px-4 py-1.5 rounded-xl bg-amber-500 text-xs font-semibold text-white hover:bg-amber-600 shadow-sm shadow-amber-500/30">
                    اعمال فیلتر
                </button>
            </div>
        </form>

        {{-- فرم تغییر وضعیت گروهی --}}
        <form method="POST" action="{{ route('user.reminders.bulk-status') }}" id="bulk-status-form">
            @csrf

            <input type="hidden" name="status" id="bulk-status-value">

            {{-- دسته: وظایف --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 text-xs font-bold">
                            {{ $taskReminders->count() }}
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                یادآوری‌های مرتبط با وظایف
                            </h2>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                مرتب شده بر اساس اولویت وظیفه و زمان یادآوری.
                            </p>
                        </div>
                    </div>

                    @if($taskReminders->isNotEmpty())
                        <div class="flex items-center gap-2 text-[11px]">
                            <button type="button"
                                    onclick="setBulkStatusAndSubmit('{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}')"
                                    class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100
                                           dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800">
                                علامت‌گذاری به عنوان انجام‌شده
                            </button>
                            <button type="button"
                                    onclick="setBulkStatusAndSubmit('{{ \Modules\Reminders\Entities\Reminder::STATUS_CANCELED }}')"
                                    class="px-3 py-1.5 rounded-xl bg-gray-50 text-gray-700 border border-gray-200 hover:bg-gray-100
                                           dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">
                                لغو کردن
                            </button>
                        </div>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-right">
                        <thead class="bg-gray-50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-3 py-2 w-10">
                                <input type="checkbox" onclick="toggleAll(this, 'task')">
                            </th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">متن یادآوری</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">وظیفه</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">اولویت</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">زمان یادآوری</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">وضعیت</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300 text-left">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($taskReminders as $reminder)
                            @php
                                $task = $reminder->task;
                                $statusInfo = $statusBadges[$reminder->status] ?? $statusBadges[\Modules\Reminders\Entities\Reminder::STATUS_OPEN];
                                $remindJalali = $reminder->remind_at
                                    ? Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i')
                                    : '—';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70 transition-colors">
                                <td class="px-3 py-2 align-top">
                                    <input type="checkbox"
                                           name="ids[]"
                                           value="{{ $reminder->id }}"
                                           class="reminder-checkbox reminder-checkbox-task">
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="text-gray-800 dark:text-gray-100">
                                        {{ $reminder->message ?? ('یادآوری انجام وظیفه: ' . ($task->title ?? ('Task #' . $task->id))) }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        شناسه یادآوری: {{ $reminder->id }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    @if($task)
                                        <a href="{{ route('user.tasks.show', $task) }}"
                                           class="text-[11px] text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200">
                                            {{ $task->title ?? ('Task #' . $task->id) }}
                                        </a>
                                    @else
                                        <span class="text-[11px] text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 align-top">
                                    @if($task)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px]
                                                     bg-gray-50 text-gray-700 border-gray-200
                                                     dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                                            {{ taskPriorityLabel($task->priority) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 align-top dir-ltr text-[11px] text-gray-700 dark:text-gray-200">
                                    {{ $remindJalali }}
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full border text-[10px] {{ $statusInfo['class'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current/40"></span>
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 align-top text-left">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- تغییر وضعیت سریع به DONE --}}
                                        @if($reminder->status !== \Modules\Reminders\Entities\Reminder::STATUS_DONE)
                                            <form method="POST" action="{{ route('user.reminders.update-status', $reminder) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}">
                                                <button type="submit"
                                                        class="px-2 py-1 rounded-lg bg-emerald-50 text-[10px] text-emerald-700 hover:bg-emerald-100
                                                               dark:bg-emerald-900/40 dark:text-emerald-200">
                                                    انجام‌شد
                                                </button>
                                            </form>
                                        @endif

                                        {{-- حذف بدون confirm جاوااسکریپتی --}}
                                        <form method="POST" action="{{ route('user.reminders.destroy', $reminder) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2 py-1 rounded-lg bg-red-50 text-[10px] text-red-600 hover:bg-red-100
                                                           dark:bg-red-900/40 dark:text-red-200">
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-[12px] text-gray-500 dark:text-gray-400">
                                    یادآوری‌ای مرتبط با وظایف در این بازه زمانی یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- دسته: پیگیری‌ها --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm mt-6">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-bold">
                            {{ $followUpReminders->count() }}
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                یادآوری‌های مرتبط با پیگیری‌ها
                            </h2>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                برای هر پیگیری که موعد آن نزدیک است، یادآوری ثبت شده است.
                            </p>
                        </div>
                    </div>

                    @if($followUpReminders->isNotEmpty())
                        <div class="flex items-center gap-2 text-[11px]">
                            <button type="button"
                                    onclick="setBulkStatusAndSubmit('{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}')"
                                    class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100
                                           dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800">
                                انجام‌شده (انتخاب‌شده‌ها)
                            </button>
                        </div>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-right">
                        <thead class="bg-gray-50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-3 py-2 w-10">
                                <input type="checkbox" onclick="toggleAll(this, 'followup')">
                            </th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">متن یادآوری</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">پیگیری</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">اولویت</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">زمان یادآوری</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300">وضعیت</th>
                            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-300 text-left">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($followUpReminders as $reminder)
                            @php
                                $followUp = $reminder->followUp ?? $reminder->task; // اگر رابطه followUp خالی بود، خود task
                                $statusInfo = $statusBadges[$reminder->status] ?? $statusBadges[\Modules\Reminders\Entities\Reminder::STATUS_OPEN];
                                $remindJalali = $reminder->remind_at
                                    ? Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i')
                                    : '—';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70 transition-colors">
                                <td class="px-3 py-2 align-top">
                                    <input type="checkbox"
                                           name="ids[]"
                                           value="{{ $reminder->id }}"
                                           class="reminder-checkbox reminder-checkbox-followup">
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="text-gray-800 dark:text-gray-100">
                                        {{ $reminder->message ?? ('یادآوری پیگیری: ' . ($followUp->title ?? ('FollowUp #' . $followUp->id))) }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        شناسه یادآوری: {{ $reminder->id }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    @if($followUp)
                                        <a href="{{ route('user.followups.show', $followUp) }}"
                                           class="text-[11px] text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200">
                                            {{ $followUp->title ?? ('FollowUp #' . $followUp->id) }}
                                        </a>
                                    @else
                                        <span class="text-[11px] text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 align-top">
                                    @if($followUp)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px]
                                                     bg-gray-50 text-gray-700 border-gray-200
                                                     dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                                            {{ taskPriorityLabel($followUp->priority) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 align-top dir-ltr text-[11px] text-gray-700 dark:text-gray-200">
                                    {{ $remindJalali }}
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full border text-[10px] {{ $statusInfo['class'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current/40"></span>
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 align-top text-left">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($reminder->status !== \Modules\Reminders\Entities\Reminder::STATUS_DONE)
                                            <form method="POST" action="{{ route('user.reminders.update-status', $reminder) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}">
                                                <button type="submit"
                                                        class="px-2 py-1 rounded-lg bg-emerald-50 text-[10px] text-emerald-700 hover:bg-emerald-100
                                                               dark:bg-emerald-900/40 dark:text-emerald-200">
                                                    انجام‌شد
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('user.reminders.destroy', $reminder) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2 py-1 rounded-lg bg-red-50 text-[10px] text-red-600 hover:bg-red-100
                                                           dark:bg-red-900/40 dark:text-red-200">
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-[12px] text-gray-500 dark:text-gray-400">
                                    یادآوری‌ای مرتبط با پیگیری‌ها در این بازه زمانی یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleAll(source, type) {
            const selector = type === 'task'
                ? '.reminder-checkbox-task'
                : '.reminder-checkbox-followup';

            document.querySelectorAll(selector).forEach(cb => {
                cb.checked = source.checked;
            });
        }

        function setBulkStatusAndSubmit(status) {
            const anyChecked = document.querySelectorAll('.reminder-checkbox:checked').length > 0;
            if (!anyChecked) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type: 'error', text: 'هیچ یادآوری‌ای انتخاب نشده است.' }
                }));
                return;
            }

            document.getElementById('bulk-status-value').value = status;
            document.getElementById('bulk-status-form').submit();
        }
    </script>
@endsection
@includeIf('partials.jalali-date-picker')
