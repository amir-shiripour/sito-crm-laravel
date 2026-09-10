@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('clients::layouts.client')

@php
    $title = 'لیست صورت‌حساب‌ها و پرداخت‌ها';
@endphp

@section('content')
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <a href="{{ route('client.dashboard') }}"
                       class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                    تاریخچه پرداخت‌های من
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">در این بخش می‌توانید تمامی صورت‌حساب‌های مالی
                    خود در پنل را مشاهده و مدیریت کنید.</p>
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
                <a href="{{ route('client.payments.index', array_filter(['search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ !request('status') || request('status') === 'all' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-bold shadow-sm' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    همه پرداخت‌ها ({{ CalendarUtils::convertNumbers($totalCount ?? 0) }})
                </a>
                <a href="{{ route('client.payments.index', array_filter(['status' => 'paid', 'search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ request('status') === 'paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-bold shadow-sm' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    پرداخت شده ({{ CalendarUtils::convertNumbers($paidCount ?? 0) }})
                </a>
                <a href="{{ route('client.payments.index', array_filter(['status' => 'pending', 'search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ request('status') === 'pending' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 font-bold shadow-sm' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    در انتظار پرداخت ({{ CalendarUtils::convertNumbers($pendingCount ?? 0) }})
                </a>
                <a href="{{ route('client.payments.index', array_filter(['status' => 'canceled', 'search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ in_array(request('status'), ['canceled', 'failed']) ? 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 font-bold shadow-sm' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    لغو شده / ناموفق ({{ CalendarUtils::convertNumbers($canceledCount ?? 0) }})
                </a>
            </div>

            <form method="GET" action="{{ route('client.payments.index') }}" class="w-full md:w-72">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="شماره پیگیری، عنوان یا نوع..."
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-4 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:bg-white dark:focus:bg-gray-800 transition-all">
                    <button type="submit"
                            class="absolute left-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        @if($allPayments->isEmpty())
            <div
                class="flex flex-col items-center justify-center p-12 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm text-center">
                <div
                    class="w-20 h-20 bg-gray-50 dark:bg-gray-900/50 rounded-full flex items-center justify-center text-gray-400 mb-4">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">هیچ صورت‌حسابی یافت نشد</h3>
                <p class="text-gray-500 dark:text-gray-400">
                    @if(request('search') || (request('status') && request('status') !== 'all'))
                        با فیلترهای انتخابی هیچ پرداختی یافت نشد.
                    @else
                        تا این لحظه صورت‌حساب مالی برای شما در سیستم ثبت نشده است.
                    @endif
                </p>
                @if(request('search') || (request('status') && request('status') !== 'all'))
                    <a href="{{ route('client.payments.index') }}"
                       class="mt-4 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all">
                        پاکسازی فیلترها
                    </a>
                @endif
            </div>
        @else
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right">
                        <thead
                            class="text-xs text-gray-500 bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-medium">شناسه / بابت</th>
                            <th class="px-6 py-4 font-medium">مبلغ کل</th>
                            <th class="px-6 py-4 font-medium">تاریخ</th>
                            <th class="px-6 py-4 font-medium">وضعیت</th>
                            <th class="px-6 py-4 font-medium">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($allPayments as $payment)
                            @php
                                $payStatusColor = match($payment->status) {
                                    'PAID' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'PENDING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    'FAILED' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'REFUNDED' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                    'CANCELED', 'CANCELLED' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                };

                                $payStatusLabel = match($payment->status) {
                                    'PAID' => 'پرداخت شده',
                                    'PENDING' => 'در انتظار پرداخت',
                                    'FAILED' => 'ناموفق',
                                    'REFUNDED' => 'استرداد شده',
                                    'CANCELED', 'CANCELLED' => 'لغو شده',
                                    default => $payment->status
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 {{ $payment->type === 'booking' ? 'bg-blue-50 text-blue-500' : ($payment->type === 'invoice' ? 'bg-indigo-50 text-indigo-600' : 'bg-purple-50 text-purple-500') }}">
                                            @if($payment->type === 'booking')
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            @elseif($payment->type === 'invoice')
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div
                                                class="font-bold text-gray-900 dark:text-white">{{ $payment->title }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $payment->type_label }} |
                                                شناسه: {{ CalendarUtils::convertNumbers($payment->ref_id) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    @php
                                        if ($payment->type === 'booking') {
                                            $displayAmt = ($bookingCurrencyUnit === 'IRT') ? ($payment->amount / 10) : $payment->amount;
                                            $currLabel = $bookingCurrencyLabel;
                                        } else {
                                            $displayAmt = $payment->amount;
                                            $currLabel = 'تومان';
                                        }
                                    @endphp
                                    {{ CalendarUtils::convertNumbers(number_format($displayAmt)) }} <span
                                        class="text-xs font-normal text-gray-500">{{ $currLabel }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    <div class="flex flex-col">
                                        <span>{{ $payment->date ? CalendarUtils::convertNumbers(jdate($payment->date)->format('Y/m/d')) : '---' }}</span>
                                        <span
                                            class="text-xs text-gray-500 mt-0.5">{{ $payment->date ? CalendarUtils::convertNumbers(jdate($payment->date)->format('H:i')) : '---' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $payStatusColor }}">
                                        {{ $payStatusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($payment->type === 'invoice')
                                            <a href="{{ route('client.invoices.show', $payment->invoice_id) }}"
                                               class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors">
                                                مشاهده
                                            </a>
                                            @if($payment->status === 'PENDING')
                                                <a href="{{ route('client.payments.show', ['type' => 'invoice', 'id' => $payment->invoice_id]) }}"
                                                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1">
                                                    <span>پرداخت</span>
                                                </a>
                                            @endif
                                        @elseif($payment->type === 'booking')
                                            <a href="{{ route('client.payments.show', ['type' => 'booking', 'id' => $payment->id]) }}"
                                               class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors">
                                                مشاهده
                                            </a>
                                            @if($payment->status === 'PENDING')
                                                <a href="{{ route('client.payments.show', ['type' => 'booking', 'id' => $payment->id]) }}"
                                                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1">
                                                    <span>پرداخت</span>
                                                </a>
                                            @endif
                                        @elseif($payment->type === 'market')
                                            <a href="{{ route('client.market.orders.show', $payment->id) }}"
                                               class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors">
                                                مشاهده
                                            </a>
                                            @if($payment->status === 'PENDING')
                                                <a href="{{ url('checkout/process/' . $payment->id) }}"
                                                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1">
                                                    <span>پرداخت</span>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if($allPayments->hasPages())
                    <div
                        class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            نمایش {{ CalendarUtils::convertNumbers($allPayments->firstItem()) }}
                            تا {{ CalendarUtils::convertNumbers($allPayments->lastItem()) }} از
                            مجموع {{ CalendarUtils::convertNumbers($allPayments->total()) }} مورد
                        </div>
                        <div>
                            {{ $allPayments->withQueryString()->links('clients::partials.pagination') }}
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
