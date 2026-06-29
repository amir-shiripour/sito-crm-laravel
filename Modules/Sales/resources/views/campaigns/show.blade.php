@extends('layouts.user')
@php($title = 'جزئیات کمپین')
@section('content')
    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.sales.campaigns.index') }}" class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">کمپین {{ $campaign->name ?? '' }}</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مشاهده جزئیات و آمار کمپین</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button class="px-5 py-2.5 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-xl text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all active:scale-95">ویرایش</button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">آمار کمپین</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">به زودی نمودارهای آماری اضافه خواهد شد.</p>
                </div>
            </div>
            <div>
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">اطلاعات کلی</h3>
                    <ul class="space-y-4">
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">نوع:</span>
                            <span class="font-bold text-gray-900 dark:text-white">پیامک</span>
                        </li>
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">وضعیت:</span>
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-md dark:bg-green-900 dark:text-green-300">فعال</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
