@extends('layouts.user')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    لیست وظایف
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    وظایف ثبت‌شده در سیستم را مشاهده و مدیریت کنید.
                </p>
            </div>

            <a href="{{ route('user.tasks.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>ایجاد وظیفه جدید</span>
            </a>
        </div>

        {{-- فیلترها (در صورت نیاز می‌تونی با فرم GET تکمیلش کنی) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">جستجو</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="عنوان، توضیحات..."
                           class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">وضعیت</label>
                    <select name="status"
                            class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="">همه</option>
                        @foreach($statuses ?? [] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">اولویت</label>
                    <select name="priority"
                            class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="">همه</option>
                        @foreach($priorities ?? [] as $value => $label)
                            <option value="{{ $value }}" @selected(request('priority') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 rounded-xl text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700">
                        اعمال فیلتر
                    </button>
                    <a href="{{ route('user.tasks.index') }}"
                       class="px-3 py-2 rounded-xl text-xs font-medium border border-gray-300 text-gray-600 bg-white hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300">
                        پاک‌سازی
                    </a>
                </div>
            </form>
        </div>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($tasks->count())
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">عنوان</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">نوع</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">مسئول</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">وضعیت</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">اولویت</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">سررسید</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">اقدامات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($tasks as $task)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/30">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                <a href="{{ route('user.tasks.show', $task) }}"
                                   class="font-medium hover:text-emerald-600">
                                    {{ $task->title }}
                                </a>
                                @if($task->description)
                                    <div class="mt-0.5 text-xs text-gray-500 line-clamp-1">
                                        {{ $task->description }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">
                                {{ $types[$task->task_type] ?? $task->task_type }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">
                                {{ optional($task->assignee)->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                 text-[11px] font-medium
                                                 {{ $task->status === \Modules\Tasks\Entities\Task::STATUS_DONE ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100' }}">
                                        {{ $statuses[$task->status] ?? $task->status }}
                                    </span>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                        @if($task->priority === \Modules\Tasks\Entities\Task::PRIORITY_HIGH)
                                            bg-red-50 text-red-700
                                        @elseif($task->priority === \Modules\Tasks\Entities\Task::PRIORITY_MEDIUM)
                                            bg-amber-50 text-amber-700
                                        @else
                                            bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100
                                        @endif">
                                        {{ $priorities[$task->priority] ?? $task->priority }}
                                    </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">
                                {{ $task->due_at_view ?? ($task->due_at?->format('Y-m-d') ?? '—') }}
                            </td>
                            <td class="px-4 py-3 text-left text-xs">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('user.tasks.show', $task) }}"
                                       class="px-2 py-1 rounded-lg border border-gray-200 text-[11px] text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                                        مشاهده
                                    </a>
                                    <a href="{{ route('user.tasks.edit', $task) }}"
                                       class="px-2 py-1 rounded-lg border border-indigo-200 text-[11px] text-indigo-600 bg-indigo-50/40 hover:bg-indigo-100 dark:border-indigo-600 dark:text-indigo-300 dark:bg-indigo-900/30">
                                        ویرایش
                                    </a>
                                    <form method="POST"
                                          action="{{ route('user.tasks.destroy', $task) }}"
                                          onsubmit="return confirm('آیا از حذف این وظیفه مطمئن هستید؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-2 py-1 rounded-lg border border-red-200 text-[11px] text-red-600 bg-red-50/40 hover:bg-red-100 dark:border-red-600 dark:text-red-300 dark:bg-red-900/30">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $tasks->links() }}
                </div>
            @else
                <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    هنوز وظیفه‌ای ثبت نشده است.
                </div>
            @endif
        </div>
    </div>
@endsection
