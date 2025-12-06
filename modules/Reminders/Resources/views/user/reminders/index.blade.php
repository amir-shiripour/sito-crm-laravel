@extends('layouts.user')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-4">
    <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">
        یادآوری‌ها (Reminders)
    </h1>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">متن</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">زمان یادآوری</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">کانال</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">وضعیت</th>
                    <th class="px-4 py-2 text-xs"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($reminders as $reminder)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
                    <td class="px-4 py-2">
                        {{ $reminder->message ?? 'یادآوری مرتبط با ' . $reminder->related_type . ' #' . $reminder->related_id }}
                    </td>
                    <td class="px-4 py-2 text-xs">
                        {{ optional($reminder->remind_at)->format('Y-m-d H:i') }}
                    </td>
                    <td class="px-4 py-2 text-xs">
                        {{ $reminder->channel }}
                    </td>
                    <td class="px-4 py-2 text-xs">
                        {{ $reminder->is_sent ? 'ارسال شده' : 'در انتظار' }}
                    </td>
                    <td class="px-4 py-2 text-xs text-left">
                        <form method="POST" action="{{ route('user.reminders.destroy', $reminder) }}"
                              onsubmit="return confirm('حذف این یادآوری؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">
                                حذف
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        یادآوری فعالی ندارید.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
