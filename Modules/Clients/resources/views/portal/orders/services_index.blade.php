@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('clients::layouts.client')

@php
    $title = 'سفارش‌های من';
@endphp

@section('content')
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">

        {{-- هدر صفحه --}}
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
                    سفارش‌های من
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مشاهده وضعیت، دوره‌های تمدید و جزئیات سفارش‌های
                    سرویس‌های شما</p>
            </div>
        </div>

        {{-- فیلترها و جستجو --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto">
                <a href="{{ route('client.orders.index') }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ !request('status') || request('status') === 'all' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    همه سفارش‌ها ({{ CalendarUtils::convertNumbers($totalCount ?? 0) }})
                </a>
                <a href="{{ route('client.orders.index', ['status' => 'active']) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ request('status') === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    سفارش‌های فعال ({{ CalendarUtils::convertNumbers($activeCount ?? 0) }})
                </a>
                <a href="{{ route('client.orders.index', ['status' => 'pending']) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ request('status') === 'pending' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    سفارش‌های در انتظار ({{ CalendarUtils::convertNumbers($pendingCount ?? 0) }})
                </a>
            </div>

            <form method="GET" action="{{ route('client.orders.index') }}" class="w-full md:w-72">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="شماره سفارش یا عنوان سرویس..."
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

        @if($orders->isEmpty())
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center justify-center">
                <div
                    class="w-20 h-20 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mb-4 text-gray-300 dark:text-gray-600">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">سفارشی یافت نشد</h3>
                <p class="text-gray-500 dark:text-gray-400">تاکنون سفارشی در این وضعیت ثبت نشده است.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($orders as $order)
                    @php
                        $statusName = $order->status?->name ?? 'نامشخص';
                        $isActive = str_contains($statusName, 'فعال') && !str_contains($statusName, 'غیر');
                        $isPending = str_contains($statusName, 'انتظار') || str_contains($statusName, 'بررسی');
                        $isCanceled = str_contains($statusName, 'لغو') || str_contains($statusName, 'غیر فعال');

                        $statusClass = match(true) {
                            $isActive => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                            $isPending => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                            $isCanceled => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        };

                        $cycleLabel = match($order->billing_cycle) {
                            'monthly' => 'ماهانه',
                            'quarterly' => '۳ ماهه',
                            'semi_annual', 'semi-annual' => '۶ ماهه',
                            'annual' => 'سالانه',
                            'biennial' => '۲ ساله',
                            'triennial' => '۳ ساله',
                            'one_time', 'onetime' => 'یکباره',
                            default => $order->billing_cycle ?: 'یکباره'
                        };
                    @endphp

                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span
                                    class="text-xs font-bold text-gray-400">#{{ CalendarUtils::convertNumbers($order->order_number) }}</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $statusClass }}">
                                {{ $statusName }}
                            </span>
                            </div>

                            <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-1">
                                {{ optional($order->service)->name ?: 'سرویس خدمات' }}
                            </h3>

                            @if($order->notes)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 line-clamp-2">{{ $order->notes }}</p>
                            @else
                                <div class="mb-4"></div>
                            @endif

                            <div
                                class="space-y-2 text-xs text-gray-500 dark:text-gray-400 mb-6 bg-gray-50 dark:bg-gray-900/40 p-3 rounded-2xl">
                                <div class="flex items-center justify-between">
                                    <span>دوره صورت‌حساب:</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ $cycleLabel }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>تاریخ ثبت:</span>
                                    <span
                                        class="font-medium text-gray-700 dark:text-gray-300">{{ $order->issue_date ? CalendarUtils::convertNumbers(jdate($order->issue_date)->format('Y/m/d')) : '---' }}</span>
                                </div>
                                @if($order->renewal_date)
                                    <div class="flex items-center justify-between">
                                        <span>سررسید تمدید:</span>
                                        <span
                                            class="font-medium text-gray-700 dark:text-gray-300">{{ CalendarUtils::convertNumbers(jdate($order->renewal_date)->format('Y/m/d')) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 pt-4 mt-auto">
                            <div class="flex items-baseline justify-between mb-4">
                                <span class="text-xs text-gray-400">مبلغ سفارش:</span>
                                <div class="text-base font-bold text-gray-900 dark:text-white">
                                    {{ CalendarUtils::convertNumbers(number_format($order->total_amount ?: $order->first_payment_amount)) }}
                                    <span class="text-xs font-normal text-gray-400">تومان</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('client.orders.show', $order->id) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-600 dark:bg-gray-700/50 dark:hover:bg-blue-950/40 dark:text-gray-200 dark:hover:text-blue-300 font-medium text-xs transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    مشاهده جزئیات سفارش
                                </a>

                                @if($order->invoice_id)
                                    <a href="{{ route('client.invoices.show', $order->invoice_id) }}"
                                       class="inline-flex items-center justify-center p-2.5 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-500 transition-colors"
                                       title="مشاهده فاکتور">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($orders->hasPages())
                <div
                    class="mt-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        نمایش {{ CalendarUtils::convertNumbers($orders->firstItem()) }}
                        تا {{ CalendarUtils::convertNumbers($orders->lastItem()) }} از
                        مجموع {{ CalendarUtils::convertNumbers($orders->total()) }} مورد
                    </div>
                    <div>
                        {{ $orders->withQueryString()->links('clients::partials.pagination') }}
                    </div>
                </div>
            @endif
        @endif

    </div>
@endsection
