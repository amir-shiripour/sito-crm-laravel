@extends('layouts.user')

@section('title', 'ایجاد گردش کار')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">ایجاد گردش کار جدید</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">نام، کلید و توضیحات گردش کار را مشخص کنید.</p>
            </div>
            <a href="{{ route('user.workflows.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">بازگشت</a>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-4">
            @include('workflows::user.workflows._form', [
                'workflow' => new \Modules\Workflows\Entities\Workflow(),
                'action' => route('user.workflows.store'),
                'method' => 'post'
            ])
        </div>
    </div>
@endsection
