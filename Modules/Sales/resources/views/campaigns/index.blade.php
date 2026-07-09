@extends('layouts.user')
@php($title = 'مدیریت کمپین‌های فروش')
@section('content')
    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">مدیریت کمپین‌های فروش</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">تعریف و پیگیری کمپین‌های بازاریابی</p>
                </div>
            </div>
            <a href="{{ route('user.sales.campaigns.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                کمپین جدید
            </a>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/40 dark:shadow-none border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4">نام کمپین</th>
                            <th class="px-6 py-4">نوع</th>
                            <th class="px-6 py-4">وضعیت</th>
                            <th class="px-6 py-4">شروع / پایان</th>
                            <th class="px-6 py-4 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($campaigns as $campaign)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ $campaign->name }}
                                </td>
                                <td class="px-6 py-4">
                                    @switch($campaign->type)
                                        @case('sms')
                                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-blue-900/40 dark:text-blue-300">پیامکی</span>
                                            @break
                                        @case('email')
                                            <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-purple-900/40 dark:text-purple-300">ایمیلی</span>
                                            @break
                                        @case('call')
                                            <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-indigo-900/40 dark:text-indigo-300">تلفنی</span>
                                            @break
                                        @case('social')
                                            <span class="bg-pink-100 text-pink-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-pink-900/40 dark:text-pink-300">شبکه‌های اجتماعی</span>
                                            @break
                                        @default
                                            <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-gray-700 dark:text-gray-300">{{ $campaign->type }}</span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-4">
                                    @switch($campaign->status)
                                        @case('active')
                                            <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-emerald-950/40 dark:text-emerald-400">فعال</span>
                                            @break
                                        @case('draft')
                                            <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-gray-700 dark:text-gray-400">پیش‌نویس</span>
                                            @break
                                        @case('paused')
                                            <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-amber-950/40 dark:text-amber-400">متوقف‌شده</span>
                                            @break
                                        @case('completed')
                                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-blue-950/40 dark:text-blue-400">تکمیل‌نشده</span>
                                            @break
                                        @case('cancelled')
                                            <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-red-950/40 dark:text-red-400">لغوشده</span>
                                            @break
                                        @default
                                            <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-1 rounded-md dark:bg-gray-700 dark:text-gray-300">فعال</span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 dir-ltr text-right text-xs">
                                    {{ $campaign->start_date ? \Morilog\Jalali\Jalalian::fromCarbon($campaign->start_date)->format('Y/m/d') : '---' }}
                                    تا
                                    {{ $campaign->end_date ? \Morilog\Jalali\Jalalian::fromCarbon($campaign->end_date)->format('Y/m/d') : '---' }}
                                </td>
                                <td class="px-6 py-4 text-center flex items-center justify-center gap-2">
                                    <a href="{{ route('user.sales.campaigns.show', $campaign) }}" class="px-2.5 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-400 dark:hover:bg-indigo-900/50 transition-colors">
                                        مشاهده
                                    </a>
                                    <a href="{{ route('user.sales.campaigns.edit', $campaign) }}" class="px-2.5 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-400 dark:hover:bg-amber-900/50 transition-colors">
                                        ویرایش
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                    هیچ کمپین فعالی ثبت نشده است. همین حالا اولین کمپین خود را تعریف کنید.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
