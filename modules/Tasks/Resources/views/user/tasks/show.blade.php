@php
    use Modules\Reminders\Entities\Reminder;
    use Morilog\Jalali\Jalalian;
    $taskReminders = class_exists(Reminder::class)
            ? Reminder::where('related_type', 'TASK')
                ->where('related_id', $task->id)
                ->orderBy('remind_at')
                ->get()
            : collect();
@endphp

@extends('layouts.user')

@section('content')
    <div class="w-full mx-auto px-4 py-8">
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
                <a href="{{ route('user.tasks.edit', $task) }}
"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-700">
                    ویرایش
                </a>
                <a href="{{ route('user.tasks.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    بازگشت
                </a>
            </div>
        </div>

        <div class="space-y-6 mb-6">
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
                            نوع: {{ $types[$task->task_type] ?? $task->task_type }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                            @if($task->status === \Modules\Tasks\Entities\Task::STATUS_DONE)
                                bg-emerald-50 text-emerald-700
                            @else
                                bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100
                            @endif">
                            وضعیت: {{ $statuses[$task->status] ?? $task->status }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                            @if($task->priority === \Modules\Tasks\Entities\Task::PRIORITY_HIGH || $task->priority === \Modules\Tasks\Entities\Task::PRIORITY_CRITICAL)
                                bg-red-50 text-red-700
                            @elseif($task->priority === \Modules\Tasks\Entities\Task::PRIORITY_MEDIUM)
                                bg-amber-50 text-amber-700
                            @else
                                bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100
                            @endif">
                            اولویت: {{ $priorities[$task->priority] ?? $task->priority }}
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
                            @if($task->due_at)
                                <span class="dir-ltr">
                                    {{ Jalalian::fromCarbon($task->due_at)->format('Y/m/d H:i') }}
                                </span>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تاریخ ایجاد</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            @if($task->created_at)
                                <span class="dir-ltr">
                                    {{ Jalalian::fromCarbon($task->created_at)->format('Y/m/d H:i') }}
                                </span>
                            @else
                                —
                            @endif
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

                @php
                    $assigneeMode    = $meta['assignee_mode'] ?? 'single_user';
                    $assigneeRoleIds = collect($meta['assignee_role_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div class="space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400">روش تعیین مسئول</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            @if($assigneeMode === 'by_roles')
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
                        @if(!empty($assigneeRoleIds))
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($allRoles->whereIn('id', $assigneeRoleIds) as $role)
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

                @if($relatedTarget === 'user' && $relatedUser)
                    @php
                        $relatedUserRoleIds = collect($meta['related_user_role_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">نوع موجودیت</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">کاربر داخلی (User)</div>

                            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 mb-1">کاربر مرتبط</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $relatedUser->name }} @if($relatedUser->email) <span class="text-xs text-gray-400">({{ $relatedUser->email }})</span> @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">نقش‌های مرتبط</div>
                            @if(!empty($relatedUserRoleIds))
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($allRoles->whereIn('id', $relatedUserRoleIds) as $role)
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
                @elseif($relatedTarget === 'client' && $relatedClient)
                    @php
                        $relatedClientStatusIds = collect($meta['related_client_status_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">نوع موجودیت</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">مشتری (Client)</div>

                            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 mb-1">مشتری مرتبط</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $relatedClient->full_name }} @if($relatedClient->phone) <span class="text-xs text-gray-400">({{ $relatedClient->phone }})</span> @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">وضعیت‌های مشتری مرتبط</div>
                            @if(!empty($relatedClientStatusIds))
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
                    <input type="hidden" name="related_id" value="{{ $task->id }}">

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
                                   value="یادآوری انجام وظیفه: {{ $task->title }}"
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
