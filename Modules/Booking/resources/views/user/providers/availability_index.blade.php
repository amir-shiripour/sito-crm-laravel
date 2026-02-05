@extends('layouts.user')

@section('content')
    <div class="space-y-5">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">برنامه زمانی {{ config('booking.labels.providers') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">لیست {{ config('booking.labels.providers') }} برای مدیریت شیفت</p>
            </div>
            <a href="{{ route('user.booking.dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                بازگشت به نوبت‌دهی
            </a>
        </div>

        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 space-y-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">جستجو در نام / ایمیل /
                        موبایل</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}"
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                           placeholder="مثلاً دکتر احمدی">
                </div>
                <div>
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                        جستجو
                    </button>
                </div>
            </form>

            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-right">
                        <thead class="bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-3 py-2 font-semibold text-gray-600 dark:text-gray-300">#</th>
                            <th class="px-3 py-2 font-semibold text-gray-600 dark:text-gray-300">نام</th>
                            <th class="px-3 py-2 font-semibold text-gray-600 dark:text-gray-300">ایمیل</th>
                            <th class="px-3 py-2 font-semibold text-gray-600 dark:text-gray-300">موبایل</th>
                            <th class="px-3 py-2 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($providers as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-3 py-2 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $user->id }}
                                </td>
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $user->email }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $user->phone ?? '-' }}</td>
                                <td class="px-3 py-2 text-left">
                                    <a href="{{ route('user.booking.providers.availability.edit', $user) }}"
                                       class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-200 dark:hover:bg-indigo-900/50 transition">
                                        برنامه زمانی
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                                    هیچ کاربری یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end">
                {{ $providers->links() }}
            </div>
        </div>
    </div>
@endsection
