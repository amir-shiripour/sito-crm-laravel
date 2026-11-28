@extends('layouts.user')

@section('title', 'رسید خرید ' . $purchase->receipt_number)

@section('content')
@php
    $jalaliDate = $purchase->purchase_date
        ? (function_exists('jdate') ? jdate($purchase->purchase_date)->format('Y/m/d') : $purchase->purchase_date->format('Y-m-d'))
        : '—';
    $jalaliCreatedAt = $purchase->created_at
        ? (function_exists('jdate') ? jdate($purchase->created_at)->format('Y/m/d — H:i') : $purchase->created_at->format('Y-m-d H:i'))
        : '—';
@endphp

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Top Header (Hidden in print) --}}
    <div class="print:hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.purchases.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">رسید خرید کالا</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white dir-ltr">{{ $purchase->receipt_number }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    جزئیات رسید خرید {{ $purchase->receipt_number }}
                </h1>
                @if($purchase->status === 'cancelled')
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200/70 dark:border-rose-500/30">
                        ابطال‌شده
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-500/30">
                        ثبت‌شده در انبار
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.warehouse.purchases.show-pdf', $purchase) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                خروجی PDF رسمی
            </a>

            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                چاپ رسید
            </button>

            @if($purchase->can_modify)
                <a href="{{ route('admin.warehouse.purchases.edit', $purchase) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    ویرایش رسید
                </a>
            @endif

            <a href="{{ route('admin.warehouse.purchases.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs transition-all">
                بازگشت به فهرست
            </a>
        </div>
    </div>

    <div class="print:hidden">
        @include('warehouse::partials.flash_messages')
    </div>

    {{-- Receipt Meta Summary --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 shadow-xs space-y-5">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 text-xs">
            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">شماره رسید سیستمی</div>
                <div class="text-sm font-bold text-indigo-600 dark:text-indigo-300 dir-ltr text-right font-sans">
                    {{ $purchase->receipt_number }}
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">شماره فاکتور فروشنده</div>
                <div class="text-sm font-bold text-gray-900 dark:text-white dir-ltr text-right font-sans">
                    {{ $purchase->invoice_number ?: '—' }}
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">تاریخ خرید (فاکتور)</div>
                <div class="text-sm font-bold text-gray-900 dark:text-white dir-ltr text-right font-sans">
                    {{ $jalaliDate }}
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">تاریخ ثبت در سیستم</div>
                <div class="text-sm font-bold text-indigo-600 dark:text-indigo-300 dir-ltr text-right font-sans">
                    {{ $jalaliCreatedAt }}
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">تأمین‌کننده</div>
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ $purchase->supplier_name ?: '—' }}
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">جمع کل فاکتور</div>
                <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400 font-sans">
                    {{ number_format($purchase->total_amount) }} ریال
                </div>
            </div>
        </div>

        @if($purchase->notes || $purchase->creator)
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-700/60 text-xs text-slate-600 dark:text-slate-300">
                <div>
                    @if($purchase->notes)
                        <span class="font-bold text-gray-900 dark:text-white">توضیحات:</span> {{ $purchase->notes }}
                    @endif
                </div>
                <div>
                    ثبت‌کننده: <strong class="text-gray-900 dark:text-white">{{ $purchase->creator?->name ?? 'سیستم' }}</strong>
                </div>
            </div>
        @endif
    </div>

    {{-- Line Items Breakdown --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700/80 flex items-center justify-between">
            <h2 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                اقلام ثبت‌شده در رسید ({{ $purchase->items->count() }} قلم)
            </h2>
            @if(!$purchase->can_modify && $purchase->status !== 'cancelled')
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/70 dark:border-amber-500/30">
                    قفل ویرایش فعال (بخشی از موجودی پارت مصرف شده است)
                </span>
            @endif
        </div>

        <table class="w-full text-right text-xs">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">کد کالا</th>
                    <th class="py-3 px-4">نام کالا</th>
                    <th class="py-3 px-4">انبار مقصد</th>
                    <th class="py-3 px-4 text-center">تعداد خرید</th>
                    <th class="py-3 px-4 text-center">باقیمانده پارت</th>
                    <th class="py-3 px-4">فی خرید (ریال)</th>
                    <th class="py-3 px-4">جمع کل (ریال)</th>
                    <th class="py-3 px-4">انقضا / توضیحات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70 font-sans">
                @foreach($purchase->items as $idx => $line)
                    @php
                        $remQty = $line->batch ? (float)$line->batch->remaining_quantity : 0;
                        $expJalali = $line->expiry_date
                            ? (function_exists('jdate') ? jdate($line->expiry_date)->format('Y/m/d') : $line->expiry_date->format('Y-m-d'))
                            : null;
                    @endphp
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-4 font-bold text-slate-600 dark:text-slate-300">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 dir-ltr text-right font-bold">
                            @if($line->item)
                                <a href="{{ route('admin.warehouse.items.show', $line->item) }}"
                                   class="text-indigo-600 dark:text-indigo-300 hover:underline">
                                    {{ $line->item->code }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">
                            {{ $line->item?->name ?? 'کالای حذف‌شده' }}
                        </td>
                        <td class="py-3 px-4 text-slate-700 dark:text-slate-200">
                            {{ $line->warehouse?->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-white">
                            {{ number_format($line->quantity) }} {{ $line->item?->unit }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold {{ $remQty > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-300' }}">
                            {{ number_format($remQty) }} {{ $line->item?->unit }}
                        </td>
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">
                            {{ number_format($line->unit_price) }}
                        </td>
                        <td class="py-3 px-4 font-bold text-indigo-600 dark:text-indigo-300">
                            {{ number_format($line->total_price) }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                            @if($expJalali)
                                <span class="text-amber-700 dark:text-amber-300 font-bold">انقضا: {{ $expJalali }}</span>
                                @if($line->notes) · @endif
                            @endif
                            {{ $line->notes ?: ($expJalali ? '' : '—') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-50/90 dark:bg-gray-900/70 border-t border-gray-200 dark:border-gray-700 font-bold font-sans">
                    <td colspan="4" class="py-3.5 px-4 text-slate-700 dark:text-slate-200">جمع کل</td>
                    <td class="py-3.5 px-4 text-center text-indigo-600 dark:text-indigo-300">{{ number_format($purchase->total_quantity) }}</td>
                    <td colspan="2"></td>
                    <td class="py-3.5 px-4 text-emerald-600 dark:text-emerald-400">{{ number_format($purchase->total_amount) }} ریال</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>
@endsection
