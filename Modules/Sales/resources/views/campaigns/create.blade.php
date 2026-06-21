@extends('layouts.user')
@php($title = 'کمپین جدید')
@section('content')
    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.sales.campaigns.index') }}" class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">کمپین جدید</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ایجاد کمپین بازاریابی جدید در سیستم</p>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 sm:p-8">
            <form action="{{ route('user.sales.campaigns.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">نام کمپین <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">نوع کمپین <span class="text-red-500">*</span></label>
                        <select name="type" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all">
                            <option value="sms">پیامکی</option>
                            <option value="email">ایمیلی</option>
                            <option value="call">تلفنی</option>
                            <option value="social">شبکه های اجتماعی</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">تاریخ شروع</label>
                        <input type="date" name="start_date" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">تاریخ پایان</label>
                        <input type="date" name="end_date" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">بودجه (تومان)</label>
                        <input type="number" name="budget" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all dir-ltr" placeholder="0">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">توضیحات</label>
                        <textarea name="description" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all"></textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95">
                        ذخیره کمپین
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
