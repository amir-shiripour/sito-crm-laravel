@extends('layouts.user')

@section('content')
    @php
        use Modules\Tasks\Entities\Task;
        use Morilog\Jalali\Jalalian;

        $statuses   = $statuses   ?? Task::statusOptions();
        $priorities = $priorities ?? Task::priorityOptions();
        $types      = $types      ?? Task::typeOptions();

        $statusLabel   = $statuses[$followUp->status]   ?? $followUp->status;
        $priorityLabel = $priorities[$followUp->priority] ?? $followUp->priority;
        $typeLabel     = $types[$followUp->task_type]   ?? $followUp->task_type;
    @endphp

    <div class="max-w-4xl mx-auto px-4 py-8">
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

        <div class="space-y-6">
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
                                {{ Jalalian::fromCarbon($followUp->due_at)->format('Y/m/d') }}
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
    </div>
@endsection
