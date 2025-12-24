@extends('layouts.user')

@section('title', 'ایجاد گردش کار')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">ایجاد گردش کار جدید</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">یک گردش کار جدید برای خودکارسازی فرآیندها تعریف کنید.</p>
            </div>
            <a href="{{ route('user.workflows.index') }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                بازگشت به لیست
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                @include('workflows::user.workflows._form', [
                    'workflow' => new \Modules\Workflows\Entities\Workflow(),
                    'action' => route('user.workflows.store'),
                    'method' => 'post'
                ])
            </div>
        </div>
    </div>
@endsection
