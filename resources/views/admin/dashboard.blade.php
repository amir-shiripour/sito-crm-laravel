@extends('layouts.admin')
@php($title = 'داشبورد')
@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">کاربران کل</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">1,248</div>
        </div>
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">نقش‌ها</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">4</div>
        </div>
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">ماژول‌های فعال</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">3</div>
        </div>
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">اعلان‌ها</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">7</div>
        </div>
    </div>


    <div class="mt-8 grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">فعالیت اخیر</h3>
                <a href="#" class="text-xs text-indigo-600 dark:text-indigo-300">نمایش همه</a>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">... محتوای نمونه ...</div>
        </div>
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">میانبرها</h3>
            <div class="flex flex-wrap gap-3 text-sm">
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">کاربران</a>
                <a href="{{ route('admin.roles.index') }}" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">نقش‌ها</a>
                <a href="{{ route('admin.modules.index') }}" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">ماژول‌ها</a>
            </div>
        </div>
    </div>
@endsection
