@extends('layouts.user')

@php
    $title = 'جزئیات سفارش #' . $order->id;
@endphp

@section('content')
    <div class="space-y-6">
        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('user.market.orders.index') }}" class="p-1 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" title="بازگشت">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">جزئیات سفارش #ORD-{{ $order->id }}</h1>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-8">ثبت شده در تاریخ: {{ \Morilog\Jalali\Jalalian::fromDateTime($order->created_at)->format('Y/m/d H:i') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($order->payment_status === 'paid')
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400 text-sm font-medium">پرداخت شده</span>
                @else
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400 text-sm font-medium">در انتظار پرداخت</span>
                @endif

                <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 text-sm font-medium">وضعیت ارسال: {{ $order->delivery_status }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- ستون اطلاعات فاکتور و اقلام --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- اقلام سفارش --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="font-bold text-gray-900 dark:text-gray-100">محصولات سفارش داده شده</h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @foreach($order->items as $item)
                            <div class="p-4 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-900 rounded-xl flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700">
                                        @if(optional($item->vendorProduct)->variant && optional($item->vendorProduct->variant)->masterProduct && optional($item->vendorProduct->variant->masterProduct)->main_image_url)
                                            <img src="{{ $item->vendorProduct->variant->masterProduct->main_image_url }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $item->product_title }}</h4>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">تعداد: {{ $item->quantity }} عدد</span>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ number_format($item->unit_price) }} تومان</div>
                                    <div class="text-xs text-gray-500">جمع: {{ number_format($item->total_price) }} تومان</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- اطلاعات ارسال و خریدار --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-4">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 pb-2 border-b border-gray-100 dark:border-gray-700">اطلاعات تحویل و آدرس گیرنده</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-300">
                        <div>
                            <span class="font-medium text-gray-400 block mb-1">نام گیرنده:</span>
                            <span class="text-gray-950 dark:text-gray-50 font-medium">{{ $order->shipping_address_json['recipient_name'] ?? optional($order->client)->name ?? 'مشخص نشده' }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-400 block mb-1">موبایل گیرنده:</span>
                            <span class="text-gray-950 dark:text-gray-50 font-medium dir-ltr inline-block">{{ $order->shipping_address_json['recipient_mobile'] ?? optional($order->client)->mobile ?? 'مشخص نشده' }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium text-gray-400 block mb-1">آدرس ارسال:</span>
                            <span class="text-gray-950 dark:text-gray-50 font-medium">
                                استان {{ $order->shipping_address_json['province'] ?? '-' }}، شهر {{ $order->shipping_address_json['city'] ?? '-' }}
                                @if(!empty($order->shipping_address_json['address']))
                                    ، {{ $order->shipping_address_json['address'] }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ستون اطلاعات مالی و کاستوم متادیتا --}}
            <div class="space-y-6">
                {{-- خلاصه فاکتور مالی --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-4">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 pb-2 border-b border-gray-100 dark:border-gray-700">خلاصه مالی</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">قیمت کل اقلام:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($order->total_items_price) }} تومان</span>
                        </div>
                        @if($order->total_discount > 0)
                            <div class="flex justify-between text-rose-600">
                                <span>تخفیف سفارش:</span>
                                <span>{{ number_format($order->total_discount) }}- تومان</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">هزینه ارسال:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($order->total_shipping_cost) }} تومان</span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-gray-100 dark:border-gray-700 text-base font-bold text-gray-900 dark:text-gray-100">
                            <span>مبلغ نهایی پرداخت:</span>
                            <span class="text-indigo-600 dark:text-indigo-400">{{ number_format($order->grand_total) }} تومان</span>
                        </div>
                    </div>
                </div>

                {{-- اطلاعات پرداخت --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-4">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 pb-2 border-b border-gray-100 dark:border-gray-700">اطلاعات پرداخت</h3>
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div>
                            <span class="text-gray-400 block mb-1">روش پرداخت:</span>
                            <span class="text-gray-950 dark:text-gray-50 font-medium">
                                @if($order->payment_method === 'pos')
                                    پرداخت در محل (کارتخوان)
                                @elseif($order->payment_method === 'transfer')
                                    کارت به کارت / واریز بانکی
                                @else
                                    پرداخت آنلاین ({{ $order->payment_method }})
                                @endif
                            </span>
                        </div>
                        @if($order->transaction_id)
                            <div>
                                <span class="text-gray-400 block mb-1">شناسه تراکنش:</span>
                                <span class="font-mono text-gray-900 dark:text-gray-100 break-all select-all font-bold text-xs">{{ $order->transaction_id }}</span>
                            </div>
                        @endif
                        @if($order->payment_ref_id)
                            <div>
                                <span class="text-gray-400 block mb-1">شماره مرجع پرداخت:</span>
                                <span class="font-mono text-gray-900 dark:text-gray-100 font-bold text-xs">{{ $order->payment_ref_id }}</span>
                            </div>
                        @endif
                        @if($order->paid_at)
                            <div>
                                <span class="text-gray-400 block mb-1">زمان پرداخت:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ \Morilog\Jalali\Jalalian::fromDateTime($order->paid_at)->format('Y/m/d H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- فیلدهای کاستوم فرم تسویه حساب --}}
                @if($order->meta->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-4">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 pb-2 border-b border-gray-100 dark:border-gray-700">داده‌های فرم تسویه حساب</h3>
                        <div class="space-y-3 text-sm">
                            @foreach($order->meta as $meta)
                                @php
                                    $fieldName = $meta->key;
                                    if ($order->checkoutForm) {
                                        $fields = $order->checkoutForm->getSchema()['fields'] ?? [];
                                        foreach ($fields as $field) {
                                            if (($field['id'] ?? '') === $meta->key) {
                                                $fieldName = $field['label'] ?? $field['name'] ?? $meta->key;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <div>
                                    <span class="text-gray-400 block mb-1">{{ $fieldName }}:</span>
                                    <span class="text-gray-950 dark:text-gray-50 font-medium">{{ $meta->value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
