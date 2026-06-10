@extends('layouts.user')

@php
    $title = 'جزئیات سفارش ORD-' . $order->id;
@endphp

@section('content')
    <style>
        /* Screen only and print only styles */
        @media screen {
            .invoice-print-section {
                display: none !important;
            }
        }
        @media print {
            /* Reset body styles for print */
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: Tahoma, 'Vazir', 'Inter', sans-serif !important;
                font-size: 11px !important;
                margin: 0 !important;
                padding: 0 !important;
                direction: rtl !important;
            }
            .no-print, header, footer, sidebar, nav, .screen-dashboard-container {
                display: none !important;
            }
            .invoice-print-section {
                display: block !important;
                width: 100% !important;
                padding: 15px !important;
                box-sizing: border-box !important;
            }
            .invoice-print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px !important;
            }
            .invoice-print-table th, .invoice-print-table td {
                border: 1px solid #1e293b !important;
                padding: 8px !important;
                text-align: center !important;
                font-size: 11px !important;
            }
            .invoice-print-table th {
                background-color: #f1f5f9 !important;
                color: #000000 !important;
                font-weight: bold !important;
            }
            .invoice-print-box {
                border: 1px solid #1e293b !important;
                padding: 10px !important;
                border-radius: 8px !important;
                margin-bottom: 12px !important;
            }
            .invoice-title {
                font-size: 16px !important;
                font-weight: 800 !important;
                text-align: center !important;
                border-bottom: 2px solid #1e293b !important;
                padding-bottom: 8px !important;
                margin-bottom: 15px !important;
            }
        }
    </style>

    {{-- ================= SCREEN DASHBOARD VIEW ================= --}}
    <div class="screen-dashboard-container space-y-6 text-right" dir="rtl">

        {{-- Header & Top Action bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3">
                <a href="{{ route('user.market.orders.index') }}" class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all hover:scale-105 active:scale-95" title="بازگشت به لیست">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white">جزئیات سفارش #ORD-{{ $order->id }}</h1>
                        @if($order->payment_status === 'paid')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-extrabold border border-emerald-500/25">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                پرداخت شده
                            </span>
                        @elseif($order->payment_status === 'failed')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-extrabold border border-rose-500/25">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                پرداخت ناموفق
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-extrabold border border-amber-500/25">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                در انتظار پرداخت
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">تاریخ ثبت: {{ \Morilog\Jalali\Jalalian::fromDateTime($order->created_at)->format('Y/m/d H:i') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('user.market.orders.edit', $order) }}" class="inline-flex items-center gap-2 px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl shadow-md shadow-amber-500/10 hover:shadow-amber-500/20 hover:scale-[1.01] active:scale-95 text-xs transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    ویرایش سفارش
                </a>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold rounded-2xl hover:scale-[1.01] text-xs transition-all duration-200 shadow-sm border border-gray-200 dark:border-gray-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    چاپ فاکتور رسمی
                </button>
            </div>
        </div>

        {{-- Pending Approval Alert --}}
        @if(optional($order->status)->system_type === 'pending')
            <div class="bg-amber-500/10 dark:bg-amber-950/20 border border-amber-500/30 p-5 rounded-3xl flex items-center gap-4 text-amber-800 dark:text-amber-300">
                <div class="p-2 bg-amber-500/20 rounded-2xl text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-black text-sm">سفارش در انتظار تایید است</h4>
                    <p class="text-xs mt-1 opacity-90">این سفارش نیاز به بررسی و تایید دستی مدیریت دارد تا وارد مرحله آماده‌سازی شود.</p>
                </div>
            </div>
        @endif

        {{-- Canceled Alert if Canceled --}}
        @if(optional($order->status)->system_type === 'canceled')
            <div class="bg-rose-500/10 dark:bg-rose-950/20 border border-rose-500/30 p-5 rounded-3xl flex items-center gap-4 text-rose-800 dark:text-rose-300">
                <div class="p-2 bg-rose-500/20 rounded-2xl text-rose-600 dark:text-rose-400">
                    <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-black text-sm">سفارش لغو شده است</h4>
                    <p class="text-xs mt-1 opacity-90">این سفارش لغو گردیده و موجودی‌های رزرو شده به انبار عودت داده شده‌اند.</p>
                </div>
            </div>
        @endif

        {{-- Delivery Progress Stepper --}}
        @php
            $isAdmin = auth()->user() && auth()->user()->hasRole(['super-admin', 'admin']);
            $stepperQuery = \Modules\Market\App\Models\MarketOrderStatus::where('is_active', true);
            if ($isAdmin) {
                $stepperQuery->where('show_in_admin_stepper', true);
            } else {
                $stepperQuery->where('show_in_client_stepper', true);
            }
            $steps = $stepperQuery->orderBy('sort_order', 'asc')->get();

            $currentStatus = $order->status ?: \Modules\Market\App\Models\MarketOrderStatus::orderBy('sort_order', 'asc')->first();
            $currentSortOrder = $currentStatus ? $currentStatus->sort_order : 0;

            $currentStepIndex = -1;
            foreach ($steps as $idx => $step) {
                if ($step->sort_order <= $currentSortOrder) {
                    $currentStepIndex = $idx;
                }
            }
        @endphp

        @if($steps->isNotEmpty() && optional($order->status)->system_type !== 'canceled' && optional($order->status)->system_type !== 'returned')
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="font-black text-gray-900 dark:text-white text-base mb-6 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </span>
                    وضعیت و مراحل فرآیند سفارش
                </h3>

                <div class="relative pt-6 pb-2 overflow-hidden md:overflow-visible">
                    <div class="flex mb-2 items-start justify-between relative">
                        @if(count($steps) > 1)
                            @php
                                $halfPct = 100 / count($steps) / 2;
                            @endphp
                            <!-- Background line -->
                            <div class="absolute top-[1.375rem] h-1 bg-gray-200 dark:bg-gray-700 rounded-full" style="left: {{ $halfPct }}%; right: {{ $halfPct }}%;">
                                <!-- Active line progress -->
                                <div class="absolute right-0 h-full bg-indigo-500 dark:bg-indigo-400 rounded-full transition-all duration-500" style="width: {{ $currentStepIndex > 0 ? ($currentStepIndex / (count($steps) - 1)) * 100 : 0 }}%;"></div>
                            </div>
                        @endif

                        @foreach($steps as $index => $step)
                            @php
                                $isCompleted = $index < $currentStepIndex;
                                $isCurrent = $index === $currentStepIndex;
                                $isUpcoming = $index > $currentStepIndex;
                                $label = $isAdmin ? $step->admin_label : $step->client_label;
                            @endphp
                            <div class="w-1/{{ count($steps) }} text-center relative z-10 flex flex-col items-center group">
                                <div class="relative flex items-center justify-center mb-3">
                                    @if($isCompleted)
                                        <div class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 ring-4 ring-white dark:ring-gray-800 transition-transform group-hover:scale-110 duration-300">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @elseif($isCurrent)
                                        <div class="w-12 h-12 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white flex items-center justify-center shadow-lg shadow-indigo-600/30 ring-4 ring-indigo-50 dark:ring-indigo-900/50">
                                            <span class="absolute w-full h-full rounded-full bg-indigo-400 dark:bg-indigo-500 opacity-50 animate-ping"></span>
                                            <svg class="w-6 h-6 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-2 border-gray-200 dark:border-gray-700 flex items-center justify-center font-bold text-sm shadow-sm ring-4 ring-white dark:ring-gray-800 transition-colors group-hover:border-gray-300 dark:group-hover:border-gray-600">
                                            {{ $index + 1 }}
                                        </div>
                                    @endif
                                </div>
                                <h4 class="font-bold text-xs {{ $isCompleted ? 'text-emerald-600 dark:text-emerald-400' : ($isCurrent ? 'text-indigo-600 dark:text-indigo-400 font-black scale-105' : 'text-gray-500 dark:text-gray-400') }} transition-all text-center px-1">
                                    {{ $label }}
                                </h4>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif



        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left Column: Items Table, Details, Shipping Address --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Order Items Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
                    <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="font-black text-gray-900 dark:text-white text-base">محصولات سفارش داده شده</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-right">
                            <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-xs">
                                <tr>
                                    <th class="px-5 py-3.5 font-bold">نام محصول</th>
                                    <th class="px-5 py-3.5 font-bold text-center">قیمت واحد</th>
                                    <th class="px-5 py-3.5 font-bold text-center">تعداد</th>
                                    <th class="px-5 py-3.5 font-bold text-left pl-8">قیمت کل</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($order->items as $item)
                                    <tr class="text-gray-900 dark:text-white hover:bg-gray-50/40 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-900 rounded-xl flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0">
                                                    @if(optional($item->vendorProduct)->variant && optional($item->vendorProduct->variant)->masterProduct && optional($item->vendorProduct->variant->masterProduct)->main_image_url)
                                                        <img src="{{ $item->vendorProduct->variant->masterProduct->main_image_url }}" alt="" class="w-full h-full object-cover">
                                                    @else
                                                        <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="font-extrabold block text-sm">{{ $item->product_title }}</span>
                                                    @if($item->vendorProduct && $item->vendorProduct->variant)
                                                        @if($item->vendorProduct->variant->variant_code)
                                                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">کد تنوع: {{ $item->vendorProduct->variant->variant_code }}</span>
                                                        @endif
                                                        @php
                                                            $displayVariantName = $item->vendorProduct->variant->name;
                                                            if ($displayVariantName && (mb_strpos($displayVariantName, 'هر ') !== false || $displayVariantName === 'هر')) {
                                                                $titleParts = explode(' - ', $item->product_title);
                                                                if (count($titleParts) > 1) {
                                                                    $displayVariantName = end($titleParts);
                                                                }
                                                            }
                                                        @endphp
                                                        @if($displayVariantName)
                                                            <span class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5 block font-bold">تنوع: {{ $displayVariantName }}</span>
                                                        @endif
                                                    @endif
                                                    @if($item->vendor)
                                                        <span class="text-xs text-amber-600 dark:text-amber-400 mt-0.5 block">فروشنده: {{ $item->vendor->store_name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-center text-xs font-semibold">
                                            {{ number_format($item->unit_price) }} ریال
                                        </td>
                                        <td class="px-5 py-4 text-center font-bold">
                                            {{ $item->quantity }} عدد
                                        </td>
                                        <td class="px-5 py-4 text-left pl-8 text-xs font-black text-emerald-600 dark:text-emerald-400">
                                            {{ number_format($item->total_price) }} ریال
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Delivery Info & Shipping Address --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4 hover:shadow-md transition-all duration-300">
                    <h3 class="font-black text-gray-900 dark:text-white text-base pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        مشخصات ارسال و آدرس تحویل گیرنده
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                        <div class="space-y-1">
                            <span class="text-xs text-gray-400 dark:text-gray-500 block">نام تحویل گیرنده:</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ !empty($order->shipping_address_json['recipient_name']) ? $order->shipping_address_json['recipient_name'] : (optional($order->client)->full_name ?: '-') }}
                            </span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-gray-400 dark:text-gray-500 block">موبایل تحویل گیرنده:</span>
                            <span class="font-bold text-gray-900 dark:text-white dir-ltr inline-block">
                                {{ !empty($order->shipping_address_json['recipient_mobile']) ? $order->shipping_address_json['recipient_mobile'] : (optional($order->client)->phone ?: '-') }}
                            </span>
                        </div>
                        @if(!empty($order->shipping_address_json['recipient_national_code']))
                            <div class="space-y-1">
                                <span class="text-xs text-gray-400 dark:text-gray-500 block">کد ملی تحویل گیرنده:</span>
                                <span class="font-bold text-gray-900 dark:text-white">
                                    {{ $order->shipping_address_json['recipient_national_code'] }}
                                </span>
                            </div>
                        @endif
                        <div class="space-y-1">
                            <span class="text-xs text-gray-400 dark:text-gray-500 block">روش ارسال:</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                @php
                                    $shipMethod = \Modules\Market\Entities\ShippingMethod::where('code', $order->shipping_method)->first();
                                @endphp
                                {{ $shipMethod ? $shipMethod->name : ($order->shipping_method ?: 'نامشخص') }}
                            </span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-gray-400 dark:text-gray-500 block">کد رهگیری مرسوله:</span>
                            @if($order->tracking_code)
                                <span class="font-extrabold text-indigo-600 dark:text-indigo-400 select-all font-mono">
                                    {{ $order->tracking_code }}
                                </span>
                            @else
                                <span class="font-bold text-gray-400 dark:text-gray-500">ثبت نشده</span>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-gray-400 dark:text-gray-500 block">هزینه حمل و نقل:</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ number_format($order->total_shipping_cost) }} ریال
                            </span>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <span class="text-xs text-gray-400 dark:text-gray-500 block">آدرس ارسال مرسوله:</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                استان {{ $order->shipping_address_json['province'] ?: '-' }}، شهر {{ $order->shipping_address_json['city'] ?: '-' }}
                                @if(!empty($order->shipping_address_json['address']))
                                    ، {{ $order->shipping_address_json['address'] }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Client Card, Financial Summary, Payment info, Metadata fields --}}
            <div class="space-y-6">

                {{-- Client Profile Card (Full Details) --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4 hover:shadow-md transition-all duration-300">
                    <h3 class="font-black text-gray-900 dark:text-white text-base pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                        مشخصات کاربری خریدار
                    </h3>

                    @if($order->client)
                        <div class="space-y-3.5 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">نام و نام خانوادگی:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $order->client->full_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">شماره تماس:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $order->client->phone ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">پست الکترونیک:</span>
                                <span class="font-bold text-gray-900 dark:text-white text-xs">{{ $order->client->email ?: 'ثبت نشده' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">کد ملی:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $order->client->national_code ?: 'ثبت نشده' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">نام کاربری سیستم:</span>
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold">{{ $order->client->username }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-400 dark:text-gray-500 text-xs">
                            اطلاعات خریدار ثبت نشده یا حذف شده است.
                        </div>
                    @endif
                </div>

                {{-- Financial Invoice Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4 hover:shadow-md transition-all duration-300">
                    <h3 class="font-black text-gray-900 dark:text-white text-base pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </span>
                        خلاصه فاکتور مالی
                    </h3>

                    <div class="space-y-3.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400 dark:text-gray-500">قیمت اقلام سفارش:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($order->total_items_price) }} ریال</span>
                        </div>
                        @if($order->total_discount > 0)
                            <div class="flex justify-between text-rose-600 dark:text-rose-400">
                                <span>تخفیف سفارش:</span>
                                <span class="font-bold">{{ number_format($order->total_discount) }}- ریال</span>
                            </div>
                        @endif
                        @if($order->total_shipping_cost > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">هزینه حمل و نقل:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($order->total_shipping_cost) }} ریال</span>
                            </div>
                        @endif
                        <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-700 text-base font-black text-gray-900 dark:text-white">
                            <span>مبلغ کل پرداختی:</span>
                            <span class="text-indigo-600 dark:text-indigo-400">{{ number_format($order->grand_total) }} ریال</span>
                        </div>
                    </div>
                </div>

                {{-- Payment Details Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4 hover:shadow-md transition-all duration-300">
                    <h3 class="font-black text-gray-900 dark:text-white text-base pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </span>
                        جزئیات تراکنش مالی
                    </h3>

                    <div class="space-y-3.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400 dark:text-gray-500">درگاه/روش پرداخت:</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                @if($order->payment_method === 'pos')
                                    پرداخت در محل (کارتخوان)
                                @elseif($order->payment_method === 'transfer')
                                    کارت به کارت / واریز فیش
                                @elseif($order->payment_method === 'cod')
                                    پرداخت در محل (نقدی)
                                @elseif($order->payment_method === 'zibal')
                                    درگاه آنلاین زیبال
                                @elseif($order->payment_method === 'zarinpal')
                                    درگاه آنلاین زرین‌پال
                                @elseif($order->payment_method === 'behpardakht')
                                    درگاه آنلاین به‌پرداخت ملت
                                @else
                                    درگاه آنلاین ({{ ucfirst($order->payment_method) }})
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 dark:text-gray-500">وضعیت پرداخت:</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                @if($order->payment_status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold">پرداخت شده</span>
                                @elseif($order->payment_status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-bold">پرداخت ناموفق</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-bold">در انتظار پرداخت</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 dark:text-gray-500">مبلغ کل تراکنش:</span>
                            <span class="font-black text-gray-900 dark:text-white">
                                {{ number_format($order->grand_total) }} ریال
                            </span>
                        </div>
                        @if($order->transaction_id)
                            <div class="space-y-1">
                                <span class="text-xs text-gray-400 dark:text-gray-500 block">شناسه تراکنش:</span>
                                <span class="text-xs text-gray-900 dark:text-white font-bold select-all break-all block">{{ $order->transaction_id }}</span>
                            </div>
                        @endif
                        @if($order->payment_ref_id)
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">شماره مرجع پرداخت:</span>
                                <span class="font-bold text-gray-900 dark:text-white select-all text-xs">{{ $order->payment_ref_id }}</span>
                            </div>
                        @endif
                        @if($order->paid_at)
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">تاریخ پرداخت:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ \Morilog\Jalali\Jalalian::fromDateTime($order->paid_at)->format('Y/m/d H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Dynamic Checkout Fields with Persian labels --}}
                @if($order->meta->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4 hover:shadow-md transition-all duration-300">
                        <h3 class="font-black text-gray-900 dark:text-white text-base pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            داده‌های تکمیل‌شده فرم
                        </h3>

                        <div class="space-y-4 text-sm">
                            @foreach($order->meta as $meta)
                                @php
                                    $field = $order->checkoutForm?->field($meta->key);
                                    if (!$field) {
                                        foreach (\Modules\Market\App\Models\CheckoutForm::all() as $form) {
                                            $f = $form->field($meta->key);
                                            if ($f) {
                                                $field = $f;
                                                break;
                                            }
                                        }
                                    }
                                    $fieldName = $field['label'] ?? (\Modules\Market\App\Models\CheckoutForm::getSystemFields()[$meta->key]['label'] ?? ($meta->label ?? $meta->key));
                                    $val = $meta->value;
                                    $decodedVal = json_decode($val, true);
                                    if (is_array($decodedVal) && json_last_error() === JSON_ERROR_NONE) {
                                        if (isset($decodedVal['province'])) {
                                            $val = 'استان ' . $decodedVal['province'] . '، شهر ' . ($decodedVal['city'] ?? '');
                                        } else {
                                            $val = implode(', ', $decodedVal);
                                        }
                                    }
                                @endphp
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">{{ $fieldName }}:</span>
                                    <span class="font-bold text-gray-900 dark:text-white text-xs">
                                        {{ $val }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ================= OFFICIAL PRINTABLE INVOICE ================= --}}
    <div class="invoice-print-section font-sans text-right" dir="rtl">
        <h2 class="invoice-title text-center text-xl font-bold pb-2 border-b-2 border-black mb-4">صورتحساب فروش کالا و خدمات (فاکتور رسمی)</h2>

        <div class="grid grid-cols-2 gap-4 text-xs mb-4">
            <div class="invoice-print-box">
                <h4 class="font-bold text-sm border-b border-black pb-1 mb-2">مشخصات فروشنده</h4>
                <p class="mb-1"><span class="font-semibold">نام فروشگاه:</span> فروشگاه سامانه مدیریت مشتریان</p>
                <p class="mb-1"><span class="font-semibold">تلفن تماس:</span> ۰۲۱-۱۲۳۴۵۶۷۸</p>
                <p class="mb-1"><span class="font-semibold">نشانی:</span> تهران، خیابان اصلی، پلاک ۱</p>
            </div>

            <div class="invoice-print-box">
                <div class="flex justify-between items-center border-b border-black pb-1 mb-2">
                    <h4 class="font-bold text-sm">مشخصات خریدار</h4>
                    <span>سفارش: #ORD-{{ $order->id }}</span>
                </div>
                <p class="mb-1"><span class="font-semibold">نام و نام خانوادگی:</span> {{ optional($order->client)->full_name }}</p>
                <p class="mb-1"><span class="font-semibold">شماره تلفن:</span> {{ optional($order->client)->phone ?: '-' }}</p>
                <p class="mb-1"><span class="font-semibold">کد ملی:</span> {{ optional($order->client)->national_code ?: '-' }}</p>
                <p class="mb-1"><span class="font-semibold">نشانی تحویل:</span>
                    استان {{ $order->shipping_address_json['province'] ?: '-' }}، شهر {{ $order->shipping_address_json['city'] ?: '-' }}
                    @if(!empty($order->shipping_address_json['address']))
                         {{ $order->shipping_address_json['address'] }}
                    @endif
                </p>
            </div>
        </div>

        <table class="invoice-print-table w-full text-center border-collapse">
            <thead>
                <tr>
                    <th class="border border-black p-2 font-bold bg-gray-100">ردیف</th>
                    <th class="border border-black p-2 font-bold bg-gray-100">کد محصول</th>
                    <th class="border border-black p-2 font-bold bg-gray-100">شرح کالا یا خدمات</th>
                    <th class="border border-black p-2 font-bold bg-gray-100">تعداد</th>
                    <th class="border border-black p-2 font-bold bg-gray-100">مبلغ واحد (ریال)</th>
                    <th class="border border-black p-2 font-bold bg-gray-100">مبلغ کل (ریال)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $idx => $item)
                    <tr>
                        <td class="border border-black p-2">{{ $idx + 1 }}</td>
                        <td class="border border-black p-2 text-xs">
                            #PRD-{{ $item->vendor_product_id }}
                            @if($item->vendorProduct && $item->vendorProduct->variant && $item->vendorProduct->variant->variant_code)
                                <br><span class="text-[9px] text-gray-500">کد: {{ $item->vendorProduct->variant->variant_code }}</span>
                            @endif
                        </td>
                        <td class="border border-black p-2 font-bold text-right" style="text-align: right !important;">
                            {{ $item->product_title }}
                             @if($item->vendorProduct && $item->vendorProduct->variant)
                                 @php
                                     $displayVariantName = $item->vendorProduct->variant->name;
                                     if ($displayVariantName && (mb_strpos($displayVariantName, 'هر ') !== false || $displayVariantName === 'هر')) {
                                         $titleParts = explode(' - ', $item->product_title);
                                         if (count($titleParts) > 1) {
                                             $displayVariantName = end($titleParts);
                                         }
                                     }
                                 @endphp
                                 @if($displayVariantName)
                                     <div class="text-[10px] text-indigo-700 font-normal">تنوع: {{ $displayVariantName }}</div>
                                 @endif
                             @endif
                            @if($item->vendor)
                                <div class="text-[10px] text-amber-700 font-normal">فروشنده: {{ $item->vendor->store_name }}</div>
                            @endif
                        </td>
                        <td class="border border-black p-2">{{ $item->quantity }}</td>
                        <td class="border border-black p-2">{{ number_format($item->unit_price) }}</td>
                        <td class="border border-black p-2">{{ number_format($item->total_price) }}</td>
                    </tr>
                @endforeach

                {{-- Totals inside table for print --}}
                <tr>
                    <td colspan="4" class="border border-black p-2 text-left font-bold" style="text-align: left !important;">جمع کل اقلام:</td>
                    <td colspan="2" class="border border-black p-2 font-bold">{{ number_format($order->total_items_price) }} ریال</td>
                </tr>
                @if($order->total_discount > 0)
                    <tr>
                        <td colspan="4" class="border border-black p-2 text-left font-bold" style="text-align: left !important; color: #000000 !important;">تخفیف صورتحساب:</td>
                        <td colspan="2" class="border border-black p-2 font-bold text-rose-600">{{ number_format($order->total_discount) }}- ریال</td>
                    </tr>
                @endif
                @if($order->total_shipping_cost > 0)
                    <tr>
                        <td colspan="4" class="border border-black p-2 text-left font-bold" style="text-align: left !important;">هزینه حمل و نقل:</td>
                        <td colspan="2" class="border border-black p-2 font-bold">{{ number_format($order->total_shipping_cost) }} ریال</td>
                    </tr>
                @endif
                <tr class="bg-gray-50">
                    <td colspan="4" class="border border-black p-2 text-left font-extrabold text-sm" style="text-align: left !important;">مبلغ قابل پرداخت صورتحساب:</td>
                    <td colspan="2" class="border border-black p-2 font-black text-sm">{{ number_format($order->grand_total) }} ریال</td>
                </tr>
            </tbody>
        </table>

        {{-- Metadata / dynamic info printed officially --}}
        @if($order->meta->isNotEmpty())
            <div class="invoice-print-box mt-4">
                <h4 class="font-bold text-xs border-b border-black pb-1 mb-2">اطلاعات تکمیلی خریدار (پاسخ‌های فرم)</h4>
                <div class="grid grid-cols-2 gap-2 text-[10px]">
                    @foreach($order->meta as $meta)
                        @php
                            $field = $order->checkoutForm?->field($meta->key);
                            if (!$field) {
                                foreach (\Modules\Market\App\Models\CheckoutForm::all() as $form) {
                                    $f = $form->field($meta->key);
                                    if ($f) {
                                        $field = $f;
                                        break;
                                    }
                                }
                            }
                            $fieldName = $field['label'] ?? (\Modules\Market\App\Models\CheckoutForm::getSystemFields()[$meta->key]['label'] ?? ($meta->label ?? $meta->key));
                            $val = $meta->value;
                            $decodedVal = json_decode($val, true);
                            if (is_array($decodedVal) && json_last_error() === JSON_ERROR_NONE) {
                                if (isset($decodedVal['province'])) {
                                    $val = 'استان ' . $decodedVal['province'] . '، شهر ' . ($decodedVal['city'] ?? '');
                                } else {
                                    $val = implode(', ', $decodedVal);
                                }
                            }
                        @endphp
                        <div>
                            <span class="font-semibold">{{ $fieldName }}:</span> {{ $val }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Signatures blocks --}}
        <div class="grid grid-cols-2 gap-4 text-xs mt-12 text-center">
            <div>
                <p class="font-bold mb-12">مهر و امضای فروشنده</p>
                <div class="h-8"></div>
            </div>

            <div>
                <p class="font-bold mb-12">مهر و امضای خریدار</p>
                <div class="h-8"></div>
            </div>
        </div>
    </div>
@endsection
