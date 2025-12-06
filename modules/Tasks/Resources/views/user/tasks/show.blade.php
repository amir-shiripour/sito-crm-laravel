@extends('layouts.user')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">
            {{ $task->title }}
        </h1>
        <div class="flex items-center gap-2">
            @can('tasks.edit')
                <a href="{{ route('user.tasks.edit', $task) }}"
                   class="px-3 py-1.5 text-xs rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
                    ویرایش
                </a>
            @endcan
            @can('tasks.delete')
                <form method="POST" action="{{ route('user.tasks.destroy', $task) }}"
                      onsubmit="return confirm('حذف این وظیفه؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1.5 text-xs rounded-xl bg-red-600 text-white hover:bg-red-700">
                        حذف
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 space-y-3">
        <div class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-line">
            {{ $task->description ?? 'بدون توضیحات.' }}
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <dt class="text-gray-500 dark:text-gray-400 mb-0.5">نوع</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $task->task_type }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 mb-0.5">وضعیت</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $task->status }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 mb-0.5">اولویت</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $task->priority }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 mb-0.5">مسئول</dt>
                <dd class="text-gray-900 dark:text-gray-100">
                    {{ optional($task->assignee)->name ?? '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 mb-0.5">ایجادکننده</dt>
                <dd class="text-gray-900 dark:text-gray-100">
                    {{ optional($task->creator)->name ?? '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 mb-0.5">موعد انجام</dt>
                <dd class="text-gray-900 dark:text-gray-100">
                    {{ optional($task->due_at)->format('Y-m-d H:i') ?? '—' }}
                </dd>
            </div>
        </dl>
    </div>
</div>
@endsection
