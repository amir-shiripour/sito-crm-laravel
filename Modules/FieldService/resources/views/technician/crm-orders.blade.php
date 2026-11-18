@extends('layouts.user')

@section('title', 'ماموریت‌ها و سرویس‌های من')

@section('content')
<div class="space-y-6 font-sans">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">ماموریت‌ها و سرویس‌های من</h1>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">فهرست تمامی سرویس‌های ارجاع‌شده و انجام‌شده توسط شما</p>
        </div>

        <a href="{{ route('fieldservice.technician') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition">
            بازگشت به کنسول نقشه
        </a>
    </div>

    <form method="GET" class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجو بر اساس شماره سفارش یا نام مشتری..."
               class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 px-4 py-2">
        <select name="status" class="rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 px-4 py-2">
            <option value="all">همه وضعیت‌ها</option>
            @foreach(\Modules\FieldService\App\Enums\OrderStatus::cases() as $st)
                <option value="{{ $st->value }}" @selected(request('status') === $st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">فیلتر</button>
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-right">
                <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs text-slate-600 dark:text-slate-300 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3.5 font-bold">شماره سفارش</th>
                        <th class="px-6 py-3.5 font-bold">مشتری</th>
                        <th class="px-6 py-3.5 font-bold">دستگاه / خدمت</th>
                        <th class="px-6 py-3.5 font-bold">آدرس</th>
                        <th class="px-6 py-3.5 font-bold">وضعیت</th>
                        <th class="px-6 py-3.5 font-bold">سهم خالص شما</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $order->customer_name ?? $order->client?->full_name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $order->category?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300 max-w-xs truncate">{{ $order->city }} - {{ $order->address }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $order->status->badgeClass() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $order->invoice ? number_format($order->invoice->technician_net_amount / 10) . ' تومان' : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-xs text-slate-600 dark:text-slate-300">
                                هیچ ماموریتی یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
