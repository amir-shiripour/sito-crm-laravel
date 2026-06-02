@extends('clients::layouts.client')

@php
    $title = 'جزئیات سفارش #' . $order->id;

    $payStatusColor = match($order->payment_status) {
        'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'pending', 'unpaid' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'refunded' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        'canceled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
    };

    $payStatusLabel = match($order->payment_status) {
        'paid' => 'پرداخت شده',
        'pending', 'unpaid' => 'در انتظار پرداخت',
        'failed' => 'پرداخت ناموفق',
        'refunded' => 'استرداد شده',
        'canceled' => 'لغو شده',
        default => $order->payment_status
    };

    $delStatusMap = [
        'processing' => ['label' => 'در حال پردازش در انبار', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
        'shipped' => ['label' => 'مرسوله تحویل پست شده', 'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400'],
        'delivered' => ['label' => 'مرسوله تحویل خریدار شده', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
        'canceled' => ['label' => 'سفارش لغو شده', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'],
    ];
    $ds = $delStatusMap[strtolower($order->delivery_status)] ?? ['label' => $order->delivery_status ?: 'نامشخص', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'];

    $gatewayName = match(strtolower((string)$order->payment_method)) {
        'zibal' => 'زیبال',
        'zarinpal' => 'زرین‌پال',
        'pos' => 'کارتخوان در محل',
        'transfer' => 'کارت به کارت / واریز فیش',
        'wallet' => 'کیف پول کاربری',
        default => ucfirst($order->payment_method)
    };

    $shipping = $order->shipping_address_json;
@endphp

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-5xl mx-auto">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <a href="{{ route('client.market.orders.index') }}" class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
                جزئیات سفارش #ORD-{{ $order->id }}
            </h1>
        </div>
    </div>

    {{-- استپر وضعیت ارسال --}}
    @if($order->delivery_status !== 'canceled')
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-6">مراحل آماده‌سازی و ارسال سفارش</h3>
            @php
                $delStatus = $order->delivery_status;
                $steps = [
                    ['id' => 'registered', 'label' => 'ثبت سفارش', 'active' => true],
                    ['id' => 'processing', 'label' => 'پردازش در انبار', 'active' => in_array($delStatus, ['processing', 'shipped', 'delivered'])],
                    ['id' => 'shipped', 'label' => 'ارسال مرسوله', 'active' => in_array($delStatus, ['shipped', 'delivered'])],
                    ['id' => 'delivered', 'label' => 'تحویل نهایی', 'active' => $delStatus === 'delivered']
                ];
                $currentStepIndex = 0;
                if ($delStatus === 'processing') $currentStepIndex = 1;
                elseif ($delStatus === 'shipped') $currentStepIndex = 2;
                elseif ($delStatus === 'delivered') $currentStepIndex = 3;
            @endphp
            <div class="relative pt-4">
                <div class="flex mb-2 items-center justify-between">
                    @foreach($steps as $index => $step)
                        <div class="w-1/4 text-center">
                            <div class="relative flex items-center justify-center">
                                @if($step['active'])
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white flex items-center justify-center font-bold text-xs shadow-md z-10">
                                        ✓
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-600 flex items-center justify-center font-bold text-xs z-10">
                                        {{ $index + 1 }}
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2">
                                <span class="text-xs font-bold block {{ $step['active'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">
                                    {{ $step['label'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="absolute top-8 left-0 right-0 w-full h-0.5 bg-gray-200 dark:bg-gray-700 -z-0">
                    <div class="absolute h-full bg-indigo-600 dark:bg-indigo-500" style="width: {{ ($currentStepIndex / (count($steps) - 1)) * 100 }}%;"></div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- بخش راست: اطلاعات سفارش و آدرس --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- اقلام سفارش --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">اقلام خریداری شده</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($order->items as $item)
                        <div class="p-5 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-2xl flex items-center justify-center border border-gray-100 dark:border-gray-700 overflow-hidden shrink-0">
                                    @if($item->vendorProduct && $item->vendorProduct->variant && $item->vendorProduct->variant->masterProduct && $item->vendorProduct->variant->masterProduct->main_image_url)
                                        <img src="{{ $item->vendorProduct->variant->masterProduct->main_image_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ $item->product_title }}</h4>
                                    @if($item->vendorProduct && $item->vendorProduct->variant)
                                        @if($item->vendorProduct->variant->variant_code)
                                            <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">کد تنوع: {{ $item->vendorProduct->variant->variant_code }}</span>
                                        @endif
                                        @if($item->vendorProduct->variant->name)
                                            <span class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5 block font-medium">تنوع: {{ $item->vendorProduct->variant->name }}</span>
                                        @endif
                                    @endif
                                    @if($item->vendor)
                                        <span class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 block">فروشنده: {{ $item->vendor->store_name }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-left shrink-0">
                                <div class="text-sm font-bold text-gray-900 dark:text-white font-mono">{{ number_format($item->total_price) }} تومان</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ number_format($item->unit_price) }} تومان × {{ $item->quantity }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- آدرس تحویل --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-gray-900 dark:text-white text-base pb-2 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    نشانی و اطلاعات تحویل‌گیرنده
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                    <div>
                        <span class="text-gray-400 block text-xs">نام تحویل‌گیرنده</span>
                        <span class="font-bold text-gray-900 dark:text-white mt-1 block">
                            {{ !empty($shipping['recipient_name']) ? $shipping['recipient_name'] : ($order->client ? $order->client->full_name : '---') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-xs">شماره تماس</span>
                        <span class="font-bold text-gray-900 dark:text-white mt-1 block font-mono">
                            {{ !empty($shipping['recipient_mobile']) ? $shipping['recipient_mobile'] : ($order->client ? $order->client->phone : '---') }}
                        </span>
                    </div>
                    @if(!empty($shipping['recipient_national_code']))
                        <div>
                            <span class="text-gray-400 block text-xs">کد ملی گیرنده</span>
                            <span class="font-bold text-gray-900 dark:text-white mt-1 block font-mono">{{ $shipping['recipient_national_code'] }}</span>
                        </div>
                    @endif
                    <div class="sm:col-span-2">
                        <span class="text-gray-400 block text-xs">نشانی پستی</span>
                        <span class="font-bold text-gray-900 dark:text-white mt-1 block leading-relaxed">
                            استان {{ $shipping['province'] ?? '---' }}، شهر {{ $shipping['city'] ?? '---' }}
                            @if(!empty($shipping['address']))
                                ، {{ $shipping['address'] }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>

        </div>

        {{-- بخش چپ: فاکتور مالی و جزئیات تراکنش --}}
        <div class="space-y-6">

            {{-- فاکتور مالی --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-gray-900 dark:text-white text-base pb-2 border-b border-gray-100 dark:border-gray-700">خلاصه صورت‌حساب</h3>
                <div class="space-y-3.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">قیمت اقلام سفارش:</span>
                        <span class="font-medium text-gray-900 dark:text-white font-mono">{{ number_format($order->total_items_price) }} تومان</span>
                    </div>
                    @if($order->total_shipping_cost > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">هزینه ارسال:</span>
                            <span class="font-medium text-gray-900 dark:text-white font-mono">{{ number_format($order->total_shipping_cost) }} تومان</span>
                        </div>
                    @endif
                    @if($order->total_discount > 0)
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                            <span>تخفیف:</span>
                            <span class="font-mono font-bold">{{ number_format($order->total_discount) }}- تومان</span>
                        </div>
                    @endif
                    <div class="pt-3 border-t border-dashed border-gray-100 dark:border-gray-700 flex justify-between text-base font-black text-gray-900 dark:text-white">
                        <span>مبلغ کل پرداختی:</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-mono">{{ number_format($order->grand_total) }} تومان</span>
                    </div>
                </div>
            </div>

            {{-- تراکنش بانکی --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-gray-900 dark:text-white text-base pb-2 border-b border-gray-100 dark:border-gray-700">جزئیات پرداخت</h3>
                <div class="space-y-3.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">روش پرداخت:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $gatewayName }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">وضعیت پرداخت:</span>
                        <span class="font-bold text-sm {{ $payStatusColor }} px-2 py-0.5 rounded-md">{{ $payStatusLabel }}</span>
                    </div>
                    @if($order->payment_ref_id)
                        <div class="space-y-1">
                            <span class="text-gray-500 block text-xs">کد پیگیری تراکنش (ارجاع):</span>
                            <span class="font-bold font-mono text-gray-900 dark:text-white block select-all text-xs bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/50 break-all">{{ $order->payment_ref_id }}</span>
                        </div>
                    @endif
                    @if($order->transaction_id)
                        <div class="space-y-1">
                            <span class="text-gray-500 block text-xs">کد رهگیری تراکنش:</span>
                            <span class="font-bold font-mono text-gray-900 dark:text-white block select-all text-xs bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/50 break-all">{{ $order->transaction_id }}</span>
                        </div>
                    @endif
                    @if($order->paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">تاریخ تراکنش:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ jdate($order->paid_at)->format('Y/m/d H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
