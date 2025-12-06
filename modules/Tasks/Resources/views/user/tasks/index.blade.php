@extends('layouts.user')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 space-y-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">
            وظایف (Tasks)
        </h1>
        @can('tasks.create')
            <a href="{{ route('user.tasks.create') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm hover:bg-emerald-700">
                ایجاد وظیفه جدید
            </a>
        @endcan
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">عنوان</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">نوع</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">وضعیت</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">اولویت</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">مسئول</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">موعد</th>
                    <th class="px-4 py-2 text-xs"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($tasks as $task)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
                    <td class="px-4 py-2">
                        <a href="{{ route('user.tasks.show', $task) }}"
                           class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-emerald-600">
                            {{ $task->title }}
                        </a>
                    </td>
                    <td class="px-4 py-2 text-xs text-gray-600 dark:text-gray-300">
                        {{ $task->task_type }}
                    </td>
                    <td class="px-4 py-2 text-xs">
                        {{ $task->status }}
                    </td>
                    <td class="px-4 py-2 text-xs">
                        {{ $task->priority }}
                    </td>
                    <td class="px-4 py-2 text-xs">
                        {{ optional($task->assignee)->name ?? '—' }}
                    </td>
                    <td class="px-4 py-2 text-xs">
                        {{ optional($task->due_at)->format('Y-m-d H:i') ?? '—' }}
                    </td>
                    <td class="px-4 py-2 text-xs text-left">
                        <a href="{{ route('user.tasks.edit', $task) }}"
                           class="text-indigo-600 hover:text-indigo-800">ویرایش</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        هنوز هیچ وظیفه‌ای ثبت نشده است.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
@endsection
