@extends('layouts.user')

@section('title', $invoice->title . ' شماره ' . $invoice->display_number)

@php
    $cardClass = "bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl";
    $printTemplate = $settings->get('appearance.invoice_template', 'standard');

    // Calculate payment details
    $payments = collect();
    if ($invoice->document) {
        // Assuming payments are credits to the receivable account associated with the invoice's document
        $receivableCatId = \Modules\Accounting\App\Models\AccountingSetting::get('defaults.receivables_category_id');
        $payments = $invoice->document->transactions()
            ->where('category_id', $receivableCatId)
            ->where('credit', '>', 0)
            ->with('fundAccount') // Eager load fund account
            ->get();
    }
    $totalPaid = $payments->sum('credit');
    $balanceDue = $invoice->total - $totalPaid;
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    {{-- Header & Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $invoice->title }} <span class="font-mono">#{{ $invoice->display_number }}</span></h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.accounting.invoices.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gray-200 text-gray-800 font-bold hover:bg-gray-300 transition-all active:scale-95 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                بازگشت به لیست
            </a>

            @can('accounting.invoices.edit')
                <form action="{{ route('admin.accounting.invoices.toggle-status', $invoice) }}" method="POST" onsubmit="return confirm('آیا از انجام این عملیات مطمئن هستید؟');">
                    @csrf
                    @if($invoice->status == 'draft')
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 shadow-lg shadow-green-500/30 transition-all active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            تبدیل به فاکتور
                        </button>
                    @elseif($invoice->status == 'approved' && $payments->isEmpty())
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-yellow-500 text-white font-bold hover:bg-yellow-600 shadow-lg shadow-yellow-500/30 transition-all active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0121 12.434V18m-9-3.578V18m0-3.578L3 12.434V18" /></svg>
                            بازگردانی به پیش‌فاکتور (ابطال سند)
                        </button>
                    @endif
                </form>
            @endcan

            @if($invoice->status !== 'draft' && $invoice->status !== 'paid')
                <a href="{{ route('admin.accounting.receipts.create', ['invoice_id' => $invoice->id]) }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    ثبت پرداختی
                </a>
            @endif
            <a href="{{ route('admin.accounting.invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gray-600 text-white font-bold hover:bg-gray-700 shadow-lg shadow-gray-500/30 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm7-9a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                @if($printTemplate == 'official')
                    چاپ رسمی
                @else
                    چاپ
                @endif
            </a>
        </div>
    </div>

    @cannot('accounting.invoices.edit')
        @if($invoice->status == 'draft')
            <div class="p-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 print:hidden" role="alert">
                شما دسترسی لازم برای تغییر وضعیت این فاکتور را ندارید.
            </div>
        @endif
    @endcannot

    {{-- Invoice Body --}}
    <div class="{{ $cardClass }} print:shadow-none print:border-none">
        <div class="p-8 md:p-12">
            <div class="flex justify-between items-start mb-12">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ $invoice->title }} برای:</h2>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->customer->full_name ?? $invoice->customer->name }}</p>
                    <address class="not-italic text-gray-600 dark:text-gray-400">
                        {{ $invoice->customer->address ?? 'آدرس ثبت نشده' }}<br>
                        تلفن: {{ $invoice->customer->phone ?? 'تلفن ثبت نشده' }}
                    </address>
                </div>
                <div class="text-left">
                    <div class="flex items-center justify-end gap-2">
                        @php
                            $statusClasses = ['draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', 'unpaid' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300', 'approved' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300', 'partially_paid' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300', 'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300', 'cancelled' => 'bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-gray-200'];
                            $statusTexts = ['draft' => 'پیش‌فاکتور', 'unpaid' => 'پرداخت نشده', 'approved' => 'تایید شده', 'partially_paid' => 'پرداخت جزئی', 'paid' => 'پرداخت شده', 'cancelled' => 'باطل شده'];
                        @endphp
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusClasses[$invoice->status] ?? '' }} font-sans">
                            {{ $statusTexts[$invoice->status] ?? $invoice->status }}
                        </span>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $invoice->title }}</h2>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">شماره: <span class="font-mono">{{ $invoice->display_number }}</span></p>
                    <p class="text-gray-500 dark:text-gray-400">تاریخ صدور: <span class="font-mono">{{ jdate($invoice->issue_date)->format('Y/m/d') }}</span></p>
                    <p class="text-gray-500 dark:text-gray-400">تاریخ سررسید: <span class="font-mono">{{ jdate($invoice->due_date)->format('Y/m/d') }}</span></p>
                </div>
            </div>

            <div class="overflow-x-auto -mx-8">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-8 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">شرح</th>
                            <th class="px-8 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">تعداد</th>
                            <th class="px-8 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">واحد</th>
                            <th class="px-8 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">قیمت واحد</th>
                            <th class="px-8 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">مبلغ کل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($invoice->items as $item)
                        <tr class="text-gray-700 dark:text-gray-300 font-sans">
                            <td class="px-8 py-4 whitespace-nowrap">{{ $item->description }}</td>
                            <td class="px-8 py-4 whitespace-nowrap text-center font-mono">{{ (float)$item->quantity }} <span class="text-xs text-gray-500 font-sans">{{ $item->unit_type }}</span></td>
                            <td class="px-8 py-4 whitespace-nowrap text-left font-mono">{{ number_format($item->unit_price) }} <span class="font-sans">{{ $currency }}</span></td>
                            <td class="px-8 py-4 whitespace-nowrap text-left font-mono font-semibold">{{ number_format($item->total) }} <span class="font-sans">{{ $currency }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-start mt-8">
                {{-- Payment History --}}
                <div class="w-1/2 pr-8">
                    @if($payments->isNotEmpty())
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">تاریخچه پرداخت‌ها</h3>
                        <div class="space-y-3">
                            @foreach($payments as $payment)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <div>
                                        <p class="font-semibold text-gray-700 dark:text-gray-300">{{ $payment->fundAccount->name ?? 'نامشخص' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ jdate($payment->transaction_date)->format('Y/m/d') }}</p>
                                    </div>
                                    <div class="text-left">
                                        <p class="font-mono font-bold text-green-600 dark:text-green-400">{{ number_format($payment->credit) }} <span class="font-sans text-xs">{{ $currency }}</span></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs" title="{{ $payment->description }}">{{ $payment->description }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Financial Summary --}}
                <div class="w-1/2 max-w-sm space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">جمع جزء:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200 font-mono">{{ number_format($invoice->subtotal) }} <span class="font-sans">{{ $currency }}</span></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">تخفیف:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200 font-mono">{{ number_format($invoice->discount) }} <span class="font-sans">{{ $currency }}</span></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">مالیات:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200 font-mono">{{ number_format($invoice->tax) }} <span class="font-sans">{{ $currency }}</span></span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">مبلغ کل فاکتور:</span>
                        <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ number_format($invoice->total) }} <span class="font-sans">{{ $currency }}</span></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">جمع پرداختی:</span>
                        <span class="font-semibold text-green-600 dark:text-green-400 font-mono">{{ number_format($totalPaid) }} <span class="font-sans">{{ $currency }}</span></span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                    <div class="flex justify-between items-center p-4 rounded-xl @if($balanceDue <= 0) bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 @else bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 @endif">
                        <span class="text-lg font-bold @if($balanceDue <= 0) text-green-800 dark:text-green-300 @else text-red-800 dark:text-red-300 @endif">
                            @if($balanceDue <= 0)
                                تسویه شده
                            @else
                                مانده بدهی:
                            @endif
                        </span>
                        @if($balanceDue > 0)
                            <span class="text-2xl font-black text-red-600 dark:text-red-400 font-mono">{{ number_format($balanceDue) }} <span class="font-sans text-base">{{ $currency }}</span></span>
                        @endif
                    </div>
                </div>
            </div>

            @if($invoice->document)
            <div class="border-t border-gray-200 dark:border-gray-700 mt-12 pt-6 text-center text-sm text-gray-500 dark:text-gray-400 print:hidden">
                <p>این {{ $invoice->title }} منجر به صدور <a href="{{ route('admin.accounting.documents.show', $invoice->document->id) }}" class="text-indigo-600 hover:underline font-semibold" target="_blank">سند حسابداری شماره {{ $invoice->document->document_number }}</a> شده است.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
