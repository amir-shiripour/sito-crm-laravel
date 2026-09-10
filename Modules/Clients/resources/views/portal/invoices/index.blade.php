@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('clients::layouts.client')

@php
    $title = 'فاکتورهای من';
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
                    فاکتورهای من
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مشاهده، پیگیری وضعیت و پرداخت آنلاین تمامی
                    فاکتورهای صادره برای شما</p>
            </div>
        </div>

        {{-- فیلتر وضعیت و جستجو --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
                <a href="{{ route('client.invoices.index', array_filter(['search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ !$currentStatus || $currentStatus === 'all' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-bold shadow-sm' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    همه فاکتورها ({{ CalendarUtils::convertNumbers($totalCount ?? 0) }})
                </a>
                @foreach($filterStatuses as $statusItem)
                    @php
                        $isActive = ($currentStatus === $statusItem['key'])
                            || ($currentStatus === $statusItem['name'])
                            || (in_array($currentStatus, array_map('strval', $statusItem['ids'])));

                        $activeClass = match(true) {
                            str_contains($statusItem['name'], 'پرداخت شده') => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-bold shadow-sm',
                            str_contains($statusItem['name'], 'انتظار') => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 font-bold shadow-sm',
                            str_contains($statusItem['name'], 'معوقه') => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 font-bold shadow-sm',
                            str_contains($statusItem['name'], 'لغو') => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 font-bold shadow-sm',
                            str_contains($statusItem['name'], 'ادغام') => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 font-bold shadow-sm',
                            default => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-bold shadow-sm',
                        };
                    @endphp
                    <a href="{{ route('client.invoices.index', array_filter(['status' => $statusItem['key'], 'search' => request('search')])) }}"
                       class="px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ $isActive ? $activeClass : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        {{ $statusItem['name'] }} ({{ CalendarUtils::convertNumbers($statusItem['count']) }})
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('client.invoices.index') }}" class="w-full md:w-72">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('status_id'))
                    <input type="hidden" name="status_id" value="{{ request('status_id') }}">
                @endif
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجوی شماره فاکتور یا خدمت..."
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

        @if($invoices->isEmpty())
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center justify-center">
                <div
                    class="w-20 h-20 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mb-4 text-gray-300 dark:text-gray-600">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">فاکتوری یافت نشد</h3>
                <p class="text-gray-500 dark:text-gray-400">در این وضعیت فاکتوری برای شما ثبت نشده است.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($invoices as $invoice)
                    @php
                        $statusName = $invoice->status?->name ?? 'نامشخص';
                        $isPaid = str_contains($statusName, 'پرداخت شده');
                        $isPending = str_contains($statusName, 'انتظار');
                        $isOverdue = str_contains($statusName, 'معوقه');
                        $isMerged = str_contains($statusName, 'ادغام');
                        $isCanceled = str_contains($statusName, 'لغو');

                        $statusClass = match(true) {
                            $isPaid => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                            $isPending => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                            $isOverdue => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                            $isMerged => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                            $isCanceled => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        };

                        $remaining = max(0, $invoice->total - $invoice->paid_amount);
                        $serviceTitle = $invoice->service?->name ?? (optional($invoice->items->first())->item_name ?: 'خدمات ثبتی');
                    @endphp

                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span
                                    class="text-xs font-bold text-gray-400">#{{ CalendarUtils::convertNumbers($invoice->invoice_number) }}</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $statusClass }}">
                                {{ $statusName }}
                            </span>
                            </div>

                            <h3 class="font-bold text-base text-gray-900 dark:text-white mb-3 line-clamp-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ $serviceTitle }}
                            </h3>

                            <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400 mb-6">
                                <div class="flex items-center justify-between">
                                    <span>تاریخ صدور:</span>
                                    <span
                                        class="font-medium text-gray-700 dark:text-gray-300">{{ $invoice->issue_date ? CalendarUtils::convertNumbers(jdate($invoice->issue_date)->format('Y/m/d')) : '---' }}</span>
                                </div>
                                @if($invoice->due_date)
                                    <div class="flex items-center justify-between">
                                        <span>سررسید:</span>
                                        <span
                                            class="font-medium text-gray-700 dark:text-gray-300">{{ CalendarUtils::convertNumbers(jdate($invoice->due_date)->format('Y/m/d')) }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span>تعداد اقلام:</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ CalendarUtils::convertNumbers($invoice->items->count() ?: 1) }} مورد</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 pt-4 mt-auto">
                            <div class="flex items-baseline justify-between mb-4">
                                <span class="text-xs text-gray-400">مبلغ کل:</span>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ CalendarUtils::convertNumbers(number_format($invoice->total)) }} <span
                                        class="text-xs font-normal text-gray-400">تومان</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('client.invoices.show', $invoice->id) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-600 dark:bg-gray-700/50 dark:hover:bg-blue-950/40 dark:text-gray-200 dark:hover:text-blue-300 font-medium text-xs transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    مشاهده و رسید
                                </a>

                                @if(($isPending || $isOverdue) && $remaining > 0)
                                    <a href="{{ route('client.payments.show', ['type' => 'invoice', 'id' => $invoice->id]) }}"
                                       class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                                        پرداخت
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($invoices->hasPages())
                <div
                    class="mt-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        نمایش {{ CalendarUtils::convertNumbers($invoices->firstItem()) }}
                        تا {{ CalendarUtils::convertNumbers($invoices->lastItem()) }} از
                        مجموع {{ CalendarUtils::convertNumbers($invoices->total()) }} مورد
                    </div>
                    <div>
                        {{ $invoices->withQueryString()->links('clients::partials.pagination') }}
                    </div>
                </div>
            @endif
        @endif

    </div>
@endsection
