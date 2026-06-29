@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">قوانین تولید خودکار قرارداد</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تعیین شرط‌های لازم جهت تولید اتوماتیک اسناد قرارداد به محض رخداد رویدادهای مشخص</p>
            </div>
            <div>
                <a href="{{ route('user.contracts.rules.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    ایجاد قانون جدید
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-xs font-semibold uppercase tracking-wider">
                            <th class="p-4">نام قانون</th>
                            <th class="p-4">قالب متصل</th>
                            <th class="p-4">رویداد تحریک</th>
                            <th class="p-4">وضعیت‌های مجاز</th>
                            <th class="p-4">اولویت</th>
                            <th class="p-4">جلوگیری از تکرار</th>
                            <th class="p-4 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        @forelse($rules as $rule)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                <td class="p-4 font-medium text-gray-900 dark:text-gray-100">
                                    {{ $rule->name }}
                                </td>
                                <td class="p-4 text-gray-650 dark:text-gray-300">
                                    {{ $rule->template->name ?? '-' }}
                                </td>
                                <td class="p-4 text-gray-600 dark:text-gray-350">
                                    @if($rule->trigger_event === 'created')
                                        ایجاد موجودیت
                                    @elseif($rule->trigger_event === 'status_changed')
                                        تغییر وضعیت
                                    @else
                                        {{ $rule->trigger_event }}
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if(is_array($rule->trigger_statuses) && count($rule->trigger_statuses) > 0)
                                        @foreach($rule->trigger_statuses as $st)
                                            <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 rounded-md text-gray-700 dark:text-gray-300">{{ $st === 'confirmed' ? 'تأیید شده' : ($st === 'draft' ? 'پیش‌نویس' : $st) }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-gray-400">همه وضعیت‌ها</span>
                                    @endif
                                </td>
                                <td class="p-4 font-mono text-xs">
                                    {{ $rule->priority }}
                                </td>
                                <td class="p-4">
                                    @if($rule->prevent_duplicate)
                                        <span class="text-emerald-600 dark:text-emerald-450 text-xs font-semibold">بله</span>
                                    @else
                                        <span class="text-gray-400 text-xs">خیر</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('user.contracts.rules.edit', $rule->id) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors" title="ویرایش">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('user.contracts.rules.destroy', $rule->id) }}" method="POST" onsubmit="return confirm('آیا از حذف این قانون اطمینان دارید؟');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-500 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors" title="حذف">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500 dark:text-gray-400">
                                    هیچ قانونی تعریف نشده است.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
