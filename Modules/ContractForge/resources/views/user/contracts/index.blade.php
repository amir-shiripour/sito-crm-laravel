@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">قراردادهای بیماران</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مشاهده و مدیریت اسناد تعهدنامه و قراردادهای صادر شده برای بیماران کلینیک</p>
            </div>
            <div class="flex flex-wrap gap-2 justify-end">
                <a href="{{ route('user.contracts.templates.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-750 dark:text-gray-300 text-sm font-medium hover:bg-gray-250 transition-all duration-200">
                    قالب‌ها
                </a>
                <a href="{{ route('user.contracts.rules.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-750 dark:text-gray-300 text-sm font-medium hover:bg-gray-250 transition-all duration-200">
                    قوانین
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters & Search --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <form action="{{ route('user.contracts.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">جستجو</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm" placeholder="شماره قرارداد یا عنوان...">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">وضعیت قرارداد</label>
                    <select name="status" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>در انتظار امضا</option>
                        <option value="signed" {{ request('status') === 'signed' ? 'selected' : '' }}>امضا شده</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>لغو شده</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-gray-800 text-white text-sm font-medium hover:bg-gray-900 transition-colors">
                        اعمال فیلتر
                    </button>
                    <a href="{{ route('user.contracts.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-750 text-gray-700 dark:text-gray-350 text-sm font-medium hover:bg-gray-200 transition-colors">
                        ریست فیلتر
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-xs font-semibold uppercase tracking-wider">
                            <th class="p-4">شماره قرارداد</th>
                            <th class="p-4">عنوان</th>
                            <th class="p-4">نام بیمار</th>
                            <th class="p-4">وضعیت</th>
                            <th class="p-4">کاربر ایجادکننده</th>
                            <th class="p-4">تاریخ صدور</th>
                            <th class="p-4 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        @forelse($contracts as $contract)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                <td class="p-4 font-mono text-xs font-bold text-gray-900 dark:text-gray-100">
                                    {{ $contract->contract_number }}
                                </td>
                                <td class="p-4 text-gray-700 dark:text-gray-300 font-medium">
                                    {{ $contract->title }}
                                </td>
                                <td class="p-4 text-gray-600 dark:text-gray-300">
                                    {{ $contract->client->full_name ?? ($contract->contractable->patient_name ?? 'بدون نام') }}
                                </td>
                                <td class="p-4">
                                    @if($contract->status === 'signed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                                            امضا شده
                                        </span>
                                    @elseif($contract->status === 'cancelled')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-lg">
                                            لغو شده
                                        </span>
                                    @elseif($contract->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg">
                                            در انتظار امضا
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg">
                                            پیش‌نویس
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $contract->user->name ?? '-' }}
                                </td>
                                <td class="p-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ \Morilog\Jalali\Jalalian::fromCarbon($contract->created_at)->format('Y/m/d H:i') }}
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('user.contracts.show', $contract->id) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors" title="مشاهده">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('user.contracts.print', $contract->id) }}" target="_blank" class="p-1.5 text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors" title="پرینت">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('user.contracts.pdf', $contract->id) }}" class="p-1.5 text-gray-500 hover:text-emerald-600 dark:hover:text-emerald-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors" title="دانلود PDF">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('user.contracts.destroy', $contract->id) }}" method="POST" onsubmit="return confirm('آیا از حذف این قرارداد اطمینان دارید؟');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-500 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors" title="حذف">
                                                <svg xmlns="http://www.w3.org/2059/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                    هیچ قراردادی یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($contracts->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
