@extends('layouts.user')

@section('title', 'حواله انتقال ' . $transfer->transfer_number)

@section('content')
@php
    $jalaliDate = $transfer->created_at && (!$transfer->request_date || $transfer->request_date->toDateString() === $transfer->created_at->toDateString())
        ? (function_exists('jdate') ? jdate($transfer->created_at)->format('Y/m/d — H:i') : $transfer->created_at->format('Y-m-d H:i'))
        : ($transfer->request_date
            ? (function_exists('jdate') ? jdate($transfer->request_date)->format('Y/m/d') : $transfer->request_date->format('Y-m-d'))
            : '—');
    $jalaliNeeded = $transfer->needed_date
        ? (function_exists('jdate') ? jdate($transfer->needed_date)->format('Y/m/d') : $transfer->needed_date->format('Y-m-d'))
        : '—';
    $jalaliDeliveredAt = $transfer->delivered_at
        ? (function_exists('jdate') ? jdate($transfer->delivered_at)->format('Y/m/d H:i') : $transfer->delivered_at->format('Y-m-d H:i'))
        : null;
@endphp

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Top Header (Hidden in print) --}}
    <div class="print:hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.transfers.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">درخواست و انتقال کالا</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white dir-ltr">{{ $transfer->transfer_number }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    حواله درخواست و انتقال {{ $transfer->transfer_number }}
                </h1>
                @if($transfer->status === 'delivered')
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-500/30">
                        تحویل و منتقل‌شده
                    </span>
                @elseif($transfer->status === 'pending')
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/70 dark:border-amber-500/30">
                        در انتظار تایید و تحویل
                    </span>
                @elseif($transfer->status === 'rejected')
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200/70 dark:border-rose-500/30">
                        ردشده
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-gray-100 text-slate-700 dark:bg-gray-700 dark:text-slate-300 border border-gray-200 dark:border-gray-600">
                        ابطال‌شده
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.warehouse.transfers.show-pdf', $transfer) }}"
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
                چاپ حواله
            </button>

            @if($transfer->can_cancel)
                <form action="{{ route('admin.warehouse.transfers.destroy', $transfer) }}"
                      method="POST"
                      onsubmit="return confirm('آیا از ابطال حواله {{ $transfer->transfer_number }} و بازگرداندن موجودی اطمینان دارید؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-500/30 font-bold text-xs transition-all">
                        ابطال و برگشت حواله
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.warehouse.transfers.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs transition-all">
                بازگشت به فهرست
            </a>
        </div>
    </div>

    <div class="print:hidden">
        @include('warehouse::partials.flash_messages')
    </div>

    {{-- Meta Summary --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 shadow-xs space-y-5">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 text-xs">
            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">شماره حواله سیستمی</div>
                <div class="text-sm font-bold text-indigo-600 dark:text-indigo-300 dir-ltr text-right font-sans">
                    {{ $transfer->transfer_number }}
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">شماره درخواست</div>
                <div class="text-sm font-bold text-gray-900 dark:text-white dir-ltr text-right font-sans">
                    {{ $transfer->request_number ?: '—' }}
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">تاریخ ثبت درخواست</div>
                <div class="text-sm font-bold text-gray-900 dark:text-white dir-ltr text-right font-sans">
                    {{ $jalaliDate }}
                </div>
                @if($jalaliDeliveredAt)
                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5 dir-ltr text-right">
                        تحویل: {{ $jalaliDeliveredAt }}
                    </div>
                @endif
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">تاریخ مورد نیاز</div>
                <div class="text-sm font-bold text-indigo-600 dark:text-indigo-300 dir-ltr text-right font-sans">
                    {{ $jalaliNeeded }}
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">مسیر انتقال</div>
                <div class="text-xs font-bold text-gray-900 dark:text-white">
                    {{ $transfer->sourceWarehouse?->name ?? 'مبدأ' }} ← <span class="text-indigo-600 dark:text-indigo-300">{{ $transfer->destinationWarehouse?->name ?? 'مقصد' }}</span>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60">
                <div class="text-slate-600 dark:text-slate-300 font-bold mb-1">درخواست‌کننده</div>
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ $transfer->effective_requester_name !== '—' ? $transfer->effective_requester_name : $transfer->creator_name }}
                </div>
            </div>
        </div>

        @if($transfer->notes || $transfer->approver)
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-700/60 text-xs text-slate-600 dark:text-slate-300">
                <div>
                    @if($transfer->notes)
                        <span class="font-bold text-gray-900 dark:text-white">توضیحات:</span> {{ $transfer->notes }}
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <span>ثبت‌کننده: <strong class="text-gray-900 dark:text-white">{{ $transfer->creator_name }}</strong></span>
                    @if($transfer->approver)
                        <span>تایید و تحویل‌دهنده: <strong class="text-gray-900 dark:text-white">{{ $transfer->effective_approver_name }}</strong></span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Pending Fulfillment Action Box --}}
    @if($transfer->can_fulfill)
        <div class="print:hidden bg-white dark:bg-gray-800 rounded-2xl border-2 border-amber-300/80 dark:border-amber-500/40 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">
                        تایید و تحویل اقلام درخواستی به «{{ $transfer->destinationWarehouse?->name }}»
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                        تعداد تحویلی هر قلم را بررسی کرده و با یک کلیک پارت‌های کالا را از «{{ $transfer->sourceWarehouse?->name }}» منتقل کنید.
                    </p>
                </div>

                <form action="{{ route('admin.warehouse.transfers.reject', $transfer) }}" method="POST"
                      onsubmit="return confirm('آیا از رد این درخواست اطمینان دارید؟');">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-500/30 text-xs font-bold transition-colors">
                        رد درخواست
                    </button>
                </form>
            </div>

            <form action="{{ route('admin.warehouse.transfers.fulfill', $transfer) }}" method="POST" class="space-y-4">
                @csrf
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2.5 px-3">کد و نام کالا</th>
                            <th class="py-2.5 px-3 text-center">موجودی فعلی انبار مبدأ</th>
                            <th class="py-2.5 px-3 text-center">تعداد درخواستی</th>
                            <th class="py-2.5 px-3 text-center w-36">تعداد تحویل واقعی</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-sans">
                        @foreach($transfer->items as $line)
                            @php
                                $avail = (int)($sourceStocks[$line->item_id] ?? 0);
                                $suggested = min((int)round($line->requested_quantity), $avail);
                            @endphp
                            <tr>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $line->item?->name }}</div>
                                    <div class="text-[11px] text-indigo-600 dark:text-indigo-300 dir-ltr text-right">{{ $line->item?->code }}</div>
                                </td>
                                <td class="py-3 px-3 text-center font-bold {{ $avail >= $line->requested_quantity ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                    {{ number_format($avail) }} {{ $line->item?->unit }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-gray-900 dark:text-white">
                                    {{ number_format($line->requested_quantity) }} {{ $line->item?->unit }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <input type="number"
                                           name="delivered_quantities[{{ $line->id }}]"
                                           value="{{ max(0, $suggested) }}"
                                           min="0"
                                           max="{{ $avail }}"
                                           step="1"
                                           class="w-28 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-1.5 text-xs text-center font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <input type="text"
                           name="notes"
                           placeholder="توضیحات تحویل کالا..."
                           class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2 text-xs placeholder-gray-400 dark:placeholder-gray-500 font-sans">

                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm transition-all shadow-sm shrink-0">
                        تایید و تحویل نهایی
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Line Items Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700/80 flex items-center justify-between flex-wrap gap-2">
            <h2 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                اقلام حواله ({{ $transfer->items->count() }} قلم)
            </h2>
            @if($transfer->status === 'delivered' && !$transfer->can_cancel)
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/70 dark:border-amber-500/30">
                    قفل ابطال فعال (بخشی از موجودی منتقل‌شده در انبار مقصد مصرف شده است)
                </span>
            @endif
        </div>

        <table class="w-full text-right text-xs">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">کد کالا</th>
                    <th class="py-3 px-4">نام کالا</th>
                    <th class="py-3 px-4 text-center">تعداد درخواستی</th>
                    <th class="py-3 px-4 text-center">تعداد تحویل‌شده</th>
                    <th class="py-3 px-4">بهای تمام‌شده (ریال)</th>
                    <th class="py-3 px-4">توضیحات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70 font-sans">
                @foreach($transfer->items as $idx => $line)
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
                        <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-white">
                            {{ number_format($line->requested_quantity) }} {{ $line->item?->unit }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold {{ $line->delivered_quantity > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-300' }}">
                            {{ number_format($line->delivered_quantity) }} {{ $line->item?->unit }}
                        </td>
                        <td class="py-3 px-4 font-bold text-indigo-600 dark:text-indigo-300">
                            {{ number_format($line->total_cost_value) }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                            {{ $line->notes ?: '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-50/90 dark:bg-gray-900/70 border-t border-gray-200 dark:border-gray-700 font-bold font-sans">
                    <td colspan="3" class="py-3.5 px-4 text-slate-700 dark:text-slate-200">جمع کل</td>
                    <td class="py-3.5 px-4 text-center text-gray-900 dark:text-white">{{ number_format($transfer->total_requested_quantity) }}</td>
                    <td class="py-3.5 px-4 text-center text-emerald-600 dark:text-emerald-400">{{ number_format($transfer->total_delivered_quantity) }}</td>
                    <td class="py-3.5 px-4 text-indigo-600 dark:text-indigo-300">{{ number_format($transfer->total_cost_value) }} ریال</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Transferred Batches Audit Trail --}}
    @if($transfer->transferredBatches->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-gray-200 dark:border-gray-700/80">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                    پارت‌های منتقل‌شده و تفکیک بهای خرید در انبار مقصد
                </h3>
            </div>

            <table class="w-full text-right text-xs font-sans">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                        <th class="py-2.5 px-4">کد پارت مبدا / فاکتور</th>
                        <th class="py-2.5 px-4">کالا</th>
                        <th class="py-2.5 px-4 text-center">تعداد منتقل‌شده</th>
                        <th class="py-2.5 px-4 text-center">باقیمانده فعلی در مقصد</th>
                        <th class="py-2.5 px-4">فی خرید واحد پارت (ریال)</th>
                        <th class="py-2.5 px-4">جمع ارزش پارت (ریال)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @foreach($transfer->transferredBatches as $tBatch)
                        <tr>
                            <td class="py-2.5 px-4 dir-ltr text-right font-bold text-indigo-600 dark:text-indigo-300">
                                @if($tBatch->purchase_receipt_id)
                                    <a href="{{ route('admin.warehouse.purchases.show', $tBatch->purchase_receipt_id) }}" class="hover:underline">
                                        {{ $tBatch->batch_code }}
                                    </a>
                                @else
                                    {{ $tBatch->batch_code }}
                                @endif
                            </td>
                            <td class="py-2.5 px-4 font-bold text-gray-900 dark:text-white">
                                {{ $tBatch->item?->name ?? '—' }}
                            </td>
                            <td class="py-2.5 px-4 text-center font-bold text-gray-900 dark:text-white">
                                {{ number_format($tBatch->initial_quantity) }}
                            </td>
                            <td class="py-2.5 px-4 text-center font-bold {{ $tBatch->remaining_quantity > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-300' }}">
                                {{ number_format($tBatch->remaining_quantity) }}
                            </td>
                            <td class="py-2.5 px-4 font-bold text-gray-900 dark:text-white">
                                {{ number_format($tBatch->unit_purchase_price) }}
                            </td>
                            <td class="py-2.5 px-4 font-bold text-indigo-600 dark:text-indigo-300">
                                {{ number_format($tBatch->initial_quantity * $tBatch->unit_purchase_price) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
