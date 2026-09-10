@extends('clients::layouts.client')

@php
    $title = 'جزئیات سفارش #' . \Morilog\Jalali\CalendarUtils::convertNumbers($order->order_number);

    $statusName = $order->status?->name ?? 'نامشخص';
    $isActive = str_contains($statusName, 'فعال') && !str_contains($statusName, 'غیر');
    $isPending = str_contains($statusName, 'انتظار') || str_contains($statusName, 'بررسی');
    $isCanceled = str_contains($statusName, 'لغو') || str_contains($statusName, 'غیر فعال');

    $orderStatusColor = match(true) {
        $isActive => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-800/40 dark:text-emerald-400',
        $isPending => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:border-amber-800/40 dark:text-amber-400',
        $isCanceled => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/30 dark:border-rose-800/40 dark:text-rose-400',
        default => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:border-blue-800/40 dark:text-blue-400',
    };

    $orderStatusDot = match(true) {
        $isActive => 'bg-emerald-500',
        $isPending => 'bg-amber-500',
        $isCanceled => 'bg-rose-500',
        default => 'bg-blue-500',
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

    $orderTotal = $order->total_amount ?: $order->first_payment_amount ?: 0;
@endphp

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <a href="{{ route('client.orders.index') }}"
                   class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors"
                   title="بازگشت به لیست سفارش‌ها">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
                <span>جزئیات سفارش</span>
                <span class="text-lg font-extrabold text-blue-600 dark:text-blue-400"
                      dir="ltr">#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->order_number) }}</span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">شناسه یکتای سفارش در سامانه جهت مدیریت، پیگیری و
                دریافت خدمات</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($order->invoice_id)
                <a href="{{ route('client.invoices.show', $order->invoice_id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all text-xs font-bold shadow-md shadow-blue-600/20 active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>مشاهده صورت‌حساب</span>
                    @if($order->invoice?->invoice_number)
                        <span class="px-1.5 py-0.5 rounded-md bg-white/20 text-[10px]"
                              dir="ltr">#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->invoice->invoice_number) }}</span>
                    @endif
                </a>
            @endif

            <a href="{{ route('client.orders.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-xs font-medium shadow-sm">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>لیست سفارش‌ها</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div
            class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div
            class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 text-red-700 dark:text-red-400 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div
        class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600"></div>
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-gray-700 border-dashed">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-400 mb-1.5">
                    <span>شماره سفارش:</span>
                    <span class="text-gray-600 dark:text-gray-300"
                          dir="ltr">#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->order_number) }}</span>
                </div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                    {{ optional($order->service)->name ?: 'سرویس خدمات' }}
                </h2>
                <div class="mt-3 flex flex-wrap items-center gap-2.5">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold border {{ $orderStatusColor }}">
                        <span class="w-2 h-2 rounded-full {{ $orderStatusDot }} animate-pulse"></span>
                        {{ $statusName }}
                    </span>
                    @if(optional($order->service)->category)
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round"
                                                             stroke-width="2"
                                                             d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            {{ $order->service->category->name }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                <div
                    class="bg-gray-50/80 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                    <span class="text-[11px] font-bold text-gray-400 block mb-1">تاریخ صدور</span>
                    <span
                        class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white">{{ $order->issue_date ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($order->issue_date)->format('Y/m/d')) : '---' }}</span>
                </div>
                <div
                    class="bg-gray-50/80 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                    <span class="text-[11px] font-bold text-gray-400 block mb-1">دوره صورت‌حساب</span>
                    <span class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white">{{ $cycleLabel }}</span>
                </div>
                <div
                    class="bg-gray-50/80 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                    <span class="text-[11px] font-bold text-gray-400 block mb-1">سررسید بعدی</span>
                    <span
                        class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white">{{ $order->renewal_date ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($order->renewal_date)->format('Y/m/d')) : ($order->billing_cycle === 'one_time' ? 'یکباره' : '---') }}</span>
                </div>
                <div
                    class="bg-gray-50/80 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                    <span class="text-[11px] font-bold text-gray-400 block mb-1">مبلغ سفارش</span>
                    <span class="font-bold text-xs sm:text-sm text-blue-600 dark:text-blue-400">{{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($orderTotal)) }} <span
                            class="text-[10px] font-normal text-gray-500">تومان</span></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
            <div class="lg:col-span-2 space-y-6">
                <div
                    class="bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl p-5 sm:p-6 border border-gray-100 dark:border-gray-700/60 space-y-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        مشخصات و شرایط سرویس سفارش داده‌شده
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs text-gray-400 mb-1">عنوان کامل سرویس</span>
                            <span
                                class="font-bold text-sm text-gray-900 dark:text-white">{{ optional($order->service)->name ?: 'سرویس خدمات' }}</span>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs text-gray-400 mb-1">دسته‌بندی خدمت</span>
                            <span
                                class="font-bold text-sm text-gray-900 dark:text-white">{{ optional(optional($order->service)->category)->name ?: 'عمومی' }}</span>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs text-gray-400 mb-1">هزینه سفارش</span>
                            <span class="font-bold text-sm text-gray-900 dark:text-white">{{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($orderTotal)) }} تومان</span>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs text-gray-400 mb-1">هزینه تمدید در دوره‌های بعد</span>
                            <span class="font-bold text-sm text-gray-900 dark:text-white">
                                @if($order->billing_cycle === 'one_time')
                                    فاقد تمدید (سفارش یکباره)
                                @else
                                    {{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($order->renewal_price ?: $orderTotal)) }}
                                    تومان
                                @endif
                            </span>
                        </div>
                    </div>

                    @if(optional($order->service)->description)
                        <div class="pt-3 border-t border-gray-200/60 dark:border-gray-700">
                            <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">توضیحات و ویژگی‌های سرویس</span>
                            <div
                                class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-xs text-gray-700 dark:text-gray-300 leading-relaxed prose dark:prose-invert max-w-none">
                                {!! optional($order->service)->description !!}
                            </div>
                        </div>
                    @endif

                    @if($order->notes)
                        <div class="pt-3 border-t border-gray-200/60 dark:border-gray-700">
                            <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">یادداشت‌های اختصاصی سفارش</span>
                            <div
                                class="p-4 rounded-xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/70 dark:border-amber-900/40 text-xs text-amber-900 dark:text-amber-200 leading-relaxed flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>{!! nl2br(e($order->notes)) !!}</div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <div class="lg:col-span-1 space-y-6">
                @if($order->invoice_id)
                    <div
                        class="p-5 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50/60 dark:from-blue-950/30 dark:to-indigo-950/20 border border-blue-100 dark:border-blue-900/40 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xs font-bold text-gray-900 dark:text-white">صورت‌حساب رسمی سفارش</h3>
                            </div>
                            <span class="text-[11px] font-bold text-blue-700 dark:text-blue-300"
                                  dir="ltr">#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->invoice?->invoice_number ?? $order->invoice_id) }}</span>
                        </div>

                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                            برای مشاهده فاکتور رسمی، مانده بدهی، سررسید پرداخت‌ها و ثبت فیش واریزی به صفحه صورت‌حساب
                            مراجعه نمایید.
                        </p>

                        <a href="{{ route('client.invoices.show', $order->invoice_id) }}"
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition-all active:scale-95">
                            <span>مشاهده و تسویه صورت‌حساب</span>
                            <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                @endif

                <div
                    class="bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 space-y-4">
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        اطلاعات سفارش‌دهنده
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div
                            class="flex items-center justify-between pb-2.5 border-b border-gray-200/50 dark:border-gray-700/50">
                            <span class="text-gray-400">نام و نام خانوادگی:</span>
                            <span
                                class="font-bold text-gray-900 dark:text-white">{{ $order->client_name ?: optional($order->customer)->full_name ?: '---' }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between pb-2.5 border-b border-gray-200/50 dark:border-gray-700/50">
                            <span class="text-gray-400">شماره تماس:</span>
                            <span class="font-bold text-gray-900 dark:text-white"
                                  dir="ltr">{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->client_phone ?: optional($order->customer)->phone ?: optional($order->customer)->mobile ?: '---') }}</span>
                        </div>

                        @if($order->client_email || optional($order->customer)->email)
                            <div
                                class="flex items-center justify-between pb-2.5 border-b border-gray-200/50 dark:border-gray-700/50">
                                <span class="text-gray-400">ایمیل:</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300"
                                      dir="ltr">{{ $order->client_email ?: optional($order->customer)->email }}</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">تاریخ ثبت:</span>
                            <span
                                class="font-medium text-gray-700 dark:text-gray-300">{{ $order->issue_date ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($order->issue_date)->format('Y/m/d (l)')) : '---' }}</span>
                        </div>
                    </div>
                </div>

                <div
                    class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-dashed border-gray-200 dark:border-gray-700 text-center space-y-1.5">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">نیاز به تغییر یا راهنمایی دارید؟</span>
                    <p class="text-[11px] text-gray-400 leading-relaxed">
                        جهت هرگونه درخواست تغییر مشخصات سرویس یا پیگیری با تیم پشتیبانی در ارتباط باشید.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
