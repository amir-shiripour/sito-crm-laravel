@extends('layouts.user')

@section('title', 'درآمد و تسویه‌های من')

@section('content')
<div class="space-y-6 font-sans">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">درآمد و تسویه‌های بانکی من</h1>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">گزارش واریزهای پایا و مبالغ در انتظار تسویه حساب شما</p>
        </div>

        <a href="{{ route('fieldservice.technician') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition">
            بازگشت به کنسول نقشه
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">در انتظار واریز پایا</div>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-300">{{ number_format($pendingTotal / 10) }} <span class="text-xs">تومان</span></div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">مجموع تسویه‌شده</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($completedTotal / 10) }} <span class="text-xs">تومان</span></div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-right">
                <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs text-slate-600 dark:text-slate-300 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3.5 font-bold">کد پیگیری</th>
                        <th class="px-6 py-3.5 font-bold">شماره شبا</th>
                        <th class="px-6 py-3.5 font-bold">مبلغ</th>
                        <th class="px-6 py-3.5 font-bold">وضعیت</th>
                        <th class="px-6 py-3.5 font-bold">تاریخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($settlements as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $item->tracking_code }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300" dir="ltr">{{ $item->shaba_number ?? '—' }}</td>
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ number_format($item->amount / 10) }} تومان</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $item->status->badgeClass() }}">
                                    {{ $item->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-300">{{ $item->created_at?->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs text-slate-600 dark:text-slate-300">
                                هنوز تسویه‌حسابی برای شما ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($settlements->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $settlements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
