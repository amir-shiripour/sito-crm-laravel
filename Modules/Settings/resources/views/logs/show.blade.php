@extends('layouts.admin')

@section('title', 'جزئیات درخواست هوش مصنوعی')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                جزئیات درخواست #{{ $log->id }}
            </h1>
            <a href="{{ route('settings.gapgpt-logs.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                بازگشت به لیست
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- اطلاعات کلی --}}
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">اطلاعات کلی</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">کاربر:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $log->user ? $log->user->name : 'سیستم' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">مدل:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $log->model }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">وضعیت:</span>
                            @if($log->status === 'success')
                                <span class="text-green-600 font-bold">موفق</span>
                            @else
                                <span class="text-red-600 font-bold">خطا</span>
                            @endif
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">زمان پاسخ:</span>
                            <span class="font-medium text-gray-900 dark:text-white dir-ltr">{{ $log->duration_ms }} ms</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">تاریخ:</span>
                            <span class="font-medium text-gray-900 dark:text-white dir-ltr">{{ $log->created_at }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">مصرف توکن</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">ورودی (Prompt):</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $log->prompt_tokens }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">خروجی (Completion):</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $log->completion_tokens }}</span>
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-2 flex justify-between">
                            <span class="font-bold text-gray-700 dark:text-gray-300">مجموع:</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $log->total_tokens }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- جزئیات درخواست و پاسخ --}}
            <div class="md:col-span-2 space-y-6">
                @if($log->error_message)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-4 text-red-700 dark:text-red-400 text-sm">
                        <strong>خطا:</strong> {{ $log->error_message }}
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">درخواست (Prompt)</h3>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-sm font-mono text-gray-700 dark:text-gray-300 overflow-x-auto dir-ltr text-left">
                        <pre>{{ json_encode($log->prompt, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">پاسخ (Response)</h3>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-sm font-mono text-gray-700 dark:text-gray-300 overflow-x-auto dir-ltr text-left">
                        <pre>{{ json_encode($log->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
