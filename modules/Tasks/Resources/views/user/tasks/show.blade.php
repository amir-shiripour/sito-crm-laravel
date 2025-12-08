@extends('layouts.user')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        {{-- هدر --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    جزئیات وظیفه
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    اطلاعات کامل این وظیفه در زیر نمایش داده شده است.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('user.tasks.edit', $task) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-700">
                    ویرایش
                </a>
                <a href="{{ route('user.tasks.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    بازگشت
                </a>
            </div>
        </div>

        {{-- کارت اصلی --}}
        <div class="space-y-6">
            {{-- اطلاعات اصلی --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $task->title }}
                        </h2>
                        @if($task->description)
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                {{ $task->description }}
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-2 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100">
                            نوع: {{ \Modules\Tasks\Entities\Task::typeOptions()[$task->task_type] ?? $task->task_type }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                            @if($task->status === \Modules\Tasks\Entities\Task::STATUS_DONE)
                                bg-emerald-50 text-emerald-700
                            @else
                                bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100
                            @endif">
                            وضعیت: {{ \Modules\Tasks\Entities\Task::statusOptions()[$task->status] ?? $task->status }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                            @if($task->priority === \Modules\Tasks\Entities\Task::PRIORITY_HIGH)
                                bg-red-50 text-red-700
                            @elseif($task->priority === \Modules\Tasks\Entities\Task::PRIORITY_MEDIUM)
                                bg-amber-50 text-amber-700
                            @else
                                bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100
                            @endif">
                            اولویت: {{ \Modules\Tasks\Entities\Task::priorityOptions()[$task->priority] ?? $task->priority }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">مسئول</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ optional($task->assignee)->name ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تاریخ سررسید</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $task->due_at_view ?? ($task->due_at ? $task->due_at->format('Y-m-d') : '—') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تاریخ ایجاد</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $task->created_at?->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- مسئول و نقش‌ها --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="w-1 h-4 bg-indigo-500 rounded-full"></span>
                    مسئولیت
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div class="space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400">روش تعیین مسئول</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            @if($task->assignee_mode === 'by_roles')
                                بر اساس نقش‌ها
                            @else
                                کاربر مشخص
                            @endif
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-3">کاربر مسئول</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ optional($task->assignee)->name ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">نقش‌های مجاز برای انجام این وظیفه</div>
                        @php
                            $assigneeRoleIds = (array) ($task->assignee_role_ids ?? []);
                        @endphp
                        @if(!empty($assigneeRoleIds) && $roles = ($roles ?? null))
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($roles->whereIn('id', $assigneeRoleIds) as $role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs dark:bg-emerald-900/20 dark:text-emerald-300">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs text-gray-500 dark:text-gray-400">نقشی تنظیم نشده است.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- موجودیت مرتبط --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="w-1 h-4 bg-pink-500 rounded-full"></span>
                    موجودیت مرتبط
                </h3>

                @if($task->related_target === 'user')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">نوع موجودیت</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">کاربر داخلی (User)</div>

                            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 mb-1">کاربر مرتبط</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ optional($task->relatedUser)->name ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">نقش‌های مرتبط</div>
                            @php
                                $relatedUserRoleIds = (array) ($task->related_user_role_ids ?? []);
                            @endphp
                            @if(!empty($relatedUserRoleIds) && $roles = ($roles ?? null))
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($roles->whereIn('id', $relatedUserRoleIds) as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-xs dark:bg-indigo-900/20 dark:text-indigo-300">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-xs text-gray-500 dark:text-gray-400">نقشی تنظیم نشده است.</div>
                            @endif
                        </div>
                    </div>
                @elseif($task->related_target === 'client')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">نوع موجودیت</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">مشتری (Client)</div>

                            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 mb-1">مشتری مرتبط</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ optional($task->relatedClient)->full_name ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">وضعیت‌های مشتری مرتبط</div>
                            @php
                                $relatedClientStatusIds = (array) ($task->related_client_status_ids ?? []);
                            @endphp
                            @if(!empty($relatedClientStatusIds) && $clientStatuses = ($clientStatuses ?? null))
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($clientStatuses->whereIn('id', $relatedClientStatusIds) as $st)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 text-xs dark:bg-pink-900/20 dark:text-pink-300">
                                            {{ $st->label ?? $st->key }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-xs text-gray-500 dark:text-gray-400">وضعیتی تنظیم نشده است.</div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        این وظیفه به موجودیت خاصی متصل نشده است.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
