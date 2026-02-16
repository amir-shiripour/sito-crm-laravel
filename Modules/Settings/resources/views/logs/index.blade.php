@extends('layouts.admin')

@section('title', 'تاریخچه درخواست‌های هوش مصنوعی')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-purple-600 text-white shadow-lg shadow-purple-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </span>
                تاریخچه درخواست‌های هوش مصنوعی
            </h1>
            <a href="{{ route('settings.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                بازگشت به تنظیمات
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right">کاربر</th>
                            <th scope="col" class="px-6 py-3 text-right">مدل</th>
                            <th scope="col" class="px-6 py-3 text-right">توکن‌ها (ورودی/خروجی)</th>
                            <th scope="col" class="px-6 py-3 text-right">زمان (ms)</th>
                            <th scope="col" class="px-6 py-3 text-right">وضعیت</th>
                            <th scope="col" class="px-6 py-3 text-right">تاریخ</th>
                            <th scope="col" class="px-6 py-3 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">
                                    {{ $log->user ? $log->user->name : 'سیستم' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">
                                        {{ $log->model }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right dir-ltr">
                                    <span class="text-xs text-gray-500">{{ $log->prompt_tokens }} / {{ $log->completion_tokens }}</span>
                                    <span class="font-bold text-gray-700 dark:text-gray-300 ml-1">({{ $log->total_tokens }})</span>
                                </td>
                                <td class="px-6 py-4 text-right dir-ltr">
                                    {{ $log->duration_ms }} ms
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($log->status === 'success')
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">موفق</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">خطا</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right dir-ltr">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('settings.gapgpt-logs.show', $log) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">مشاهده</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    هیچ درخواستی ثبت نشده است.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection
