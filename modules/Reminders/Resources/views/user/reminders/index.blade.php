@extends('layouts.user')

@php
    use Modules\Reminders\Entities\Reminder;
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;

    // مقادیر پیش‌فرض
    $status = $status ?? 'open';
    $period = $period ?? 'today';

    // تنظیمات بج وضعیت (Status Badges)
    $statusBadges = [
        Reminder::STATUS_OPEN => [
            'label' => 'باز',
            'class' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800',
            'icon' => '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
        ],
        Reminder::STATUS_DONE => [
            'label' => 'انجام‌شده',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800',
            'icon' => '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>'
        ],
        Reminder::STATUS_CANCELED => [
            'label' => 'لغو شده',
            'class' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/50 dark:text-gray-300 dark:border-gray-600',
            'icon' => '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>'
        ],
    ];

    // تابع کمکی برای لیبل و رنگ اولویت وظایف
    if (!function_exists('taskPriorityLabel')) {
        function taskPriorityLabel($priority) {
            $colors = [
                Task::PRIORITY_LOW      => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                Task::PRIORITY_MEDIUM   => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300',
                Task::PRIORITY_HIGH     => 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-300',
                Task::PRIORITY_CRITICAL => 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-300',
            ];

            $labels = [
                Task::PRIORITY_LOW      => 'کم',
                Task::PRIORITY_MEDIUM   => 'معمولی',
                Task::PRIORITY_HIGH     => 'زیاد',
                Task::PRIORITY_CRITICAL => 'بحرانی',
            ];

            return [
                'label' => $labels[$priority] ?? 'نامشخص',
                'class' => $colors[$priority] ?? 'bg-gray-100 text-gray-600'
            ];
        }
    }

    // کلاس‌های استایل مشترک اینپوت‌ها
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

@section('content')
    <div class="w-full mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </span>
                    مدیریت یادآوری‌ها
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    لیست کامل یادآوری‌های مرتبط با وظایف و پیگیری‌ها
                </p>
            </div>
        </div>

        {{-- باکس فیلترها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

                {{-- فیلتر وضعیت --}}
                <div>
                    <label class="{{ $labelClass }}">وضعیت یادآوری</label>
                    <div class="relative">
                        <select name="status" class="{{ $inputClass }} appearance-none cursor-pointer">
                            @foreach($statusFilterOptions as $key => $label)
                                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-2.5 text-gray-500 dark:text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>

                {{-- فیلتر بازه زمانی --}}
                <div>
                    <label class="{{ $labelClass }}">بازه زمانی</label>
                    <div class="relative">
                        <select name="period" class="{{ $inputClass }} appearance-none cursor-pointer">
                            @foreach($periodOptions as $key => $label)
                                <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-2.5 text-gray-500 dark:text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>

                {{-- فیلتر تاریخ دلخواه --}}
                <div class="md:col-span-2 grid grid-cols-2 gap-3" x-data>
                    <div>
                        <label class="{{ $labelClass }}">از تاریخ</label>
                        <input type="text" name="from_jalali" data-jdp-only-date
                               value="{{ request('from_jalali') }}"
                               placeholder="1402/01/01"
                               class="{{ $inputClass }} text-center placeholder-gray-300 dark:placeholder-gray-600">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">تا تاریخ</label>
                        <input type="text" name="to_jalali" data-jdp-only-date
                               value="{{ request('to_jalali') }}"
                               placeholder="1402/12/29"
                               class="{{ $inputClass }} text-center placeholder-gray-300 dark:placeholder-gray-600">
                    </div>
                </div>

                {{-- دکمه‌های فیلتر --}}
                <div class="md:col-span-4 flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    @if(request()->anyFilled(['status', 'period', 'from_jalali', 'to_jalali']))
                        <a href="{{ route('user.reminders.index') }}"
                           class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-red-500 transition-colors dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            حذف فیلترها
                        </a>
                    @endif
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-indigo-600 text-xs font-bold text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                        اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>

        {{-- فرم عملیات گروهی (شامل هر دو لیست) --}}
        <form method="POST" action="{{ route('user.reminders.bulk-status') }}" id="bulk-status-form">
            @csrf
            <input type="hidden" name="status" id="bulk-status-value">

            {{-- --- بخش ۱: وظایف (Tasks) --- --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mb-6">

                {{-- هدر جدول وظایف --}}
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/30">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 shadow-sm">
                            <span class="text-sm font-bold font-mono">{{ $taskReminders->count() }}</span>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900 dark:text-white">وظایف (Tasks)</h2>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">مرتب‌سازی بر اساس اولویت و زمان</p>
                        </div>
                    </div>

                    @if($taskReminders->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="setBulkStatusAndSubmit('{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}')"
                                    class="group inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-[11px] font-medium text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-emerald-400 dark:hover:bg-gray-700 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                انجام شد
                            </button>
                            <button type="button" onclick="setBulkStatusAndSubmit('{{ \Modules\Reminders\Entities\Reminder::STATUS_CANCELED }}')"
                                    class="group inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-[11px] font-medium text-gray-500 hover:bg-gray-50 hover:text-red-500 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                لغو کردن
                            </button>
                        </div>
                    @endif
                </div>

                {{-- جدول وظایف --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 w-10 text-center">
                                <input type="checkbox" onclick="toggleAll(this, 'task')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 cursor-pointer">
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400">شرح یادآوری</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400">وظیفه مرتبط</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400">اولویت</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400">زمان یادآوری</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400">وضعیت</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 pl-6">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @forelse($taskReminders as $reminder)
                            @php
                                $task = $reminder->task;
                                $statusInfo = $statusBadges[$reminder->status] ?? $statusBadges[\Modules\Reminders\Entities\Reminder::STATUS_OPEN];
                                $remindJalali = $reminder->remind_at ? Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i') : '—';
                                $priorityInfo = $task ? taskPriorityLabel($task->priority) : ['label' => '—', 'class' => 'bg-gray-100'];
                            @endphp
                            <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-4 py-3 text-center align-middle">
                                    <input type="checkbox" name="ids[]" value="{{ $reminder->id }}" class="reminder-checkbox reminder-checkbox-task rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 cursor-pointer">
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-100 line-clamp-1" title="{{ $reminder->message }}">
                                        {{ $reminder->message ?? ('یادآوری انجام وظیفه: ' . ($task->title ?? 'Task #'.$task->id)) }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-mono mt-0.5">ID: {{ $reminder->id }}</div>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    @if($task)
                                        <a href="{{ route('user.tasks.show', $task) }}" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline">
                                            {{ $task->title ?? ('Task #' . $task->id) }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle text-center">
                                    @if($task)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium border border-transparent {{ $priorityInfo['class'] }}">
                                            {{ $priorityInfo['label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle text-center">
                                    <span class="text-xs font-mono font-medium text-gray-600 dark:text-gray-300 dir-ltr">{{ $remindJalali }}</span>
                                </td>
                                <td class="px-4 py-3 align-middle text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-medium border {{ $statusInfo['class'] }}">
                                        {!! $statusInfo['icon'] !!}
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-middle text-left">
                                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        @if($reminder->status !== \Modules\Reminders\Entities\Reminder::STATUS_DONE)
                                            <form method="POST" action="{{ route('user.reminders.update-status', $reminder) }}" class="inline-block">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}">
                                                <button type="submit" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30 transition-colors" title="انجام شد">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('user.reminders.destroy', $reminder) }}" class="inline-block" onsubmit="return confirm('آیا از حذف این یادآوری اطمینان دارید؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                        <span class="text-sm font-medium">هیچ یادآوری وظیفه‌ای در این بازه یافت نشد.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- --- بخش ۲: پیگیری‌ها (FollowUps) --- --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

                {{-- هدر جدول پیگیری‌ها --}}
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/30">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300 shadow-sm">
                            <span class="text-sm font-bold font-mono">{{ $followUpReminders->count() }}</span>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900 dark:text-white">پیگیری‌ها (FollowUps)</h2>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">یادآوری‌های مرتبط با تعاملات مشتری</p>
                        </div>
                    </div>

                    @if($followUpReminders->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="setBulkStatusAndSubmit('{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}')"
                                    class="group inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-[11px] font-medium text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-emerald-400 dark:hover:bg-gray-700 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                انجام شد
                            </button>
                        </div>
                    @endif
                </div>

                {{-- جدول پیگیری --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 w-10 text-center">
                                <input type="checkbox" onclick="toggleAll(this, 'followup')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 cursor-pointer">
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400">شرح یادآوری</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400">مورد پیگیری</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400">اولویت</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400">زمان یادآوری</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400">وضعیت</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 pl-6">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @forelse($followUpReminders as $reminder)
                            @php
                                $followUp = $reminder->followUp ?? $reminder->task;
                                $statusInfo = $statusBadges[$reminder->status] ?? $statusBadges[\Modules\Reminders\Entities\Reminder::STATUS_OPEN];
                                $remindJalali = $reminder->remind_at ? Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i') : '—';
                                $priorityInfo = $followUp ? taskPriorityLabel($followUp->priority) : ['label' => '—', 'class' => 'bg-gray-100'];
                            @endphp
                            <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-4 py-3 text-center align-middle">
                                    <input type="checkbox" name="ids[]" value="{{ $reminder->id }}" class="reminder-checkbox reminder-checkbox-followup rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 cursor-pointer">
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-100 line-clamp-1" title="{{ $reminder->message }}">
                                        {{ $reminder->message ?? ('یادآوری پیگیری: ' . ($followUp->title ?? 'FollowUp #'.$followUp->id)) }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-mono mt-0.5">ID: {{ $reminder->id }}</div>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    @if($followUp)
                                        <a href="{{ route('user.followups.show', $followUp) }}" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline">
                                            {{ $followUp->title ?? ('FollowUp #' . $followUp->id) }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle text-center">
                                    @if($followUp)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium border border-transparent {{ $priorityInfo['class'] }}">
                                            {{ $priorityInfo['label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle text-center">
                                    <span class="text-xs font-mono font-medium text-gray-600 dark:text-gray-300 dir-ltr">{{ $remindJalali }}</span>
                                </td>
                                <td class="px-4 py-3 align-middle text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-medium border {{ $statusInfo['class'] }}">
                                        {!! $statusInfo['icon'] !!}
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-middle text-left">
                                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        @if($reminder->status !== \Modules\Reminders\Entities\Reminder::STATUS_DONE)
                                            <form method="POST" action="{{ route('user.reminders.update-status', $reminder) }}" class="inline-block">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="{{ \Modules\Reminders\Entities\Reminder::STATUS_DONE }}">
                                                <button type="submit" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30 transition-colors" title="انجام شد">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('user.reminders.destroy', $reminder) }}" class="inline-block" onsubmit="return confirm('آیا از حذف اطمینان دارید؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span class="text-sm font-medium">هیچ یادآوری پیگیری‌ای در این بازه یافت نشد.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    {{-- اسکریپت‌های مدیریت چک‌باکس --}}
    <script>
        function toggleAll(source, type) {
            const selector = type === 'task' ? '.reminder-checkbox-task' : '.reminder-checkbox-followup';
            document.querySelectorAll(selector).forEach(cb => {
                cb.checked = source.checked;
            });
        }

        function setBulkStatusAndSubmit(status) {
            const checkboxes = document.querySelectorAll('.reminder-checkbox:checked');
            if (checkboxes.length === 0) {
                if(typeof window.dispatchEvent !== 'undefined') {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { type: 'error', text: 'هیچ یادآوری‌ای انتخاب نشده است.' }
                    }));
                } else {
                    alert('هیچ یادآوری‌ای انتخاب نشده است.');
                }
                return;
            }

            if(confirm('آیا از تغییر وضعیت موارد انتخاب شده اطمینان دارید؟')) {
                document.getElementById('bulk-status-value').value = status;
                document.getElementById('bulk-status-form').submit();
            }
        }
    </script>
@endsection

@includeIf('partials.jalali-date-picker')
