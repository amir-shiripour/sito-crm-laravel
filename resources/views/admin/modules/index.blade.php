@extends('layouts.admin')
@php($title = 'مدیریت ماژول‌ها')
@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-semibold text-gray-900 dark:text-gray-100">ماژول‌ها</h1>
            <form method="POST" action="{{ route('admin.modules.toggle') }}" class="inline">
                @csrf
                <button class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm">ذخیره تغییرات</button>
            </form>
        </div>
        {{-- Render your modules list here --}}
        <div class="text-sm text-gray-600 dark:text-gray-300">لیست ماژول‌ها به‌زودی…</div>
    </div>
@endsection
