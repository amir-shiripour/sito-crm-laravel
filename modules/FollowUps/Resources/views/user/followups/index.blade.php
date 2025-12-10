@extends('layouts.user')

@section('content')
    @php
        use Modules\Tasks\Entities\Task;
        use Morilog\Jalali\Jalalian;

        $statuses   = $statuses   ?? Task::statusOptions();
        $priorities = $priorities ?? Task::priorityOptions();
        $types      = $types      ?? Task::typeOptions();
    @endphp

    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    لیست پیگیری‌ها
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    همه پیگیری‌هایی که برای مشتریان ثبت شده‌اند.
                </p>
            </div>

            @can('followups.create')
                <a href="{{ route('user.followups.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-amber-500 text-white hover:bg-amber-600 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>ایجاد پیگیری جدید</span>
                </a>
            @endcan
        </div>

        {{-- فیلترها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">جستجو</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="عنوان، توضیحات..."
                           class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">وضعیت</label>
                    <select name="status"
                            class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="">همه</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">اولویت</label>
                    <select name="priority"
                            class="w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <option value="">همه</option>
                        @foreach($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(request('priority') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 rounded-xl text-sm font-medium bg-amber-500 text-white hover:bg-amber-600">
                        اعمال فیلتر
                    </button>
                    <a href="{{ route('user.followups.index') }}"
                       class="px-3 py-2 rounded-xl text-xs font-medium border border-gray-300 text-gray-600 bg-white hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300">
                        پاک‌سازی
                    </a>
                </div>
            </form>
        </div>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($followups->count())
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">عنوان</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">مشتری</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">مسئول</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">وضعیت</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">اولویت</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">سررسید</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">اقدامات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($followups as $fu)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/30">
                            {{-- عنوان --}}
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                <a href="{{ route('user.followups.show', $fu) }}"
                                   class="font-medium hover:text-amber-600">
                                    {{ $fu->title }}
                                </a>
                                @if($fu->description)
                                    <div class="mt-0.5 text-xs text-gray-500 line-clamp-1">
                                        {{ $fu->description }}
                                    </div>
                                @endif
                            </td>

                            {{-- مشتری --}}
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">
                                @if($fu->client)
                                    <a href="{{ route('user.clients.show', $fu->client) }}"
                                       class="hover:text-indigo-600">
                                        {{ $fu->client->full_name }}
                                    </a>
                                    @if($fu->client->phone)
                                        <div class="text-[11px] text-gray-500 dir-ltr">
                                            {{ $fu->client->phone }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- مسئول --}}
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">
                                {{ optional($fu->assignee)->name ?? '—' }}
                            </td>

                            {{-- وضعیت --}}
                            <td class="px-4 py-3 text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                             text-[11px] font-medium
                                             {{ $fu->status === Task::STATUS_DONE ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100' }}">
                                    {{ $statuses[$fu->status] ?? $fu->status }}
                                </span>
                            </td>

                            {{-- اولویت --}}
                            <td class="px-4 py-3 text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                    @if($fu->priority === Task::PRIORITY_HIGH || $fu->priority === Task::PRIORITY_CRITICAL)
                                        bg-red-50 text-red-700
                                    @elseif($fu->priority === Task::PRIORITY_MEDIUM)
                                        bg-amber-50 text-amber-700
                                    @else
                                        bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100
                                    @endif">
                                    {{ $priorities[$fu->priority] ?? $fu->priority }}
                                </span>
                            </td>

                            {{-- سررسید --}}
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">
                                @if($fu->due_at)
                                    {{ Jalalian::fromCarbon($fu->due_at)->format('Y/m/d') }}
                                @else
                                    —
                                @endif
                            </td>

                            {{-- اقدامات --}}
                            <td class="px-4 py-3 text-left text-xs">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('user.followups.show', $fu) }}"
                                       class="px-2 py-1 rounded-lg border border-gray-200 text-[11px] text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                                        مشاهده
                                    </a>
                                    @can('followups.edit')
                                        <a href="{{ route('user.followups.edit', $fu) }}"
                                           class="px-2 py-1 rounded-lg border border-indigo-200 text-[11px] text-indigo-600 bg-indigo-50/40 hover:bg-indigo-100 dark:border-indigo-600 dark:text-indigo-300 dark:bg-indigo-900/30">
                                            ویرایش
                                        </a>
                                    @endcan
                                    @can('followups.delete')
                                        <form method="POST"
                                              action="{{ route('user.followups.destroy', $fu) }}"
                                              onsubmit="return confirm('آیا از حذف این پیگیری مطمئن هستید؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2 py-1 rounded-lg border border-red-200 text-[11px] text-red-600 bg-red-50/40 hover:bg-red-100 dark:border-red-600 dark:text-red-300 dark:bg-red-900/30">
                                                حذف
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $followups->links() }}
                </div>
            @else
                <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    هنوز پیگیری‌ای ثبت نشده است.
                </div>
            @endif
        </div>
    </div>
@endsection
