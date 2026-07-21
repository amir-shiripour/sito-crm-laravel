@php
    use Modules\Services\App\Http\Models\Order;
    use Morilog\Jalali\Jalalian;
    use Carbon\Carbon;
    use Modules\Services\App\Http\Models\Status;
@endphp
@extends('layouts.user')
@section('title', 'مدیریت سفارشات')

@php
    $currency      = $currency ?? 'toman';
    $currencyLabel = $currency === 'rial' ? 'ریال' : 'تومان';
    $toJalali = function ($date) {
        if (!$date) return null;
        $carbon = Carbon::parse($date);
        if ($carbon->year < 1900) {
            return new Jalalian($carbon->year, $carbon->month, $carbon->day, $carbon->hour, $carbon->minute, $carbon->second);
        }
        return Jalalian::fromCarbon($carbon);
    };

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $billingCycleLabels = [
        'monthly'     => 'ماهانه',
        'quarterly'   => 'فصلی',
        'semi_annual' => 'شش ماهه',
        'annual'      => 'سالانه',
    ];

    $statuses = Status::where('type', 'order')->get();
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                مدیریت سفارشات
            </h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('services.invoices.create', ['type' => 'invoice']) }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/30 hover:bg-indigo-700 transition-all duration-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    سفارش جدید
                </a>
            </div>
        </div>

        {{-- Filter bar --}}
        <form method="GET"
              class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-5">
                <div class="relative xl:col-span-4">
                    <div class="absolute inset-y-0 start-0 ps-5 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو: نام مشتری، شماره سفارش، نام سرویس..."
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-12 pe-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
                <div class="xl:col-span-2">
                    <select name="status_id"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه وضعیت‌ها</option>
                        @foreach($statuses as $st)
                            <option
                                value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="xl:col-span-2">
                    <select name="service_id"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه سرویس‌ها</option>
                        @foreach($services as $srv)
                            <option
                                value="{{ $srv->id }}" @selected(request('service_id') == $srv->id)>{{ $srv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="xl:col-span-2">
                    <select name="customer_id"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه مشتریان</option>
                        @foreach($customers as $cus)
                            <option
                                value="{{ $cus->id }}" @selected(request('customer_id') == $cus->id)>{{ $cus->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="xl:col-span-2 flex gap-2">
                    <button type="submit"
                            class="flex-1 px-6 py-3.5 rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'status_id', 'service_id', 'customer_id']))
                        <a href="{{ route('services.orders.index') }}" title="پاک کردن فیلترها"
                           class="px-5 py-3.5 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table Container --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-base text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            سرویس و مشتری
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            شماره فاکتور
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            مبلغ پایه
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            دوره و مبلغ تمدید
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            مبلغ نهایی سرویس (فاکتور)
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            تاریخ صدور
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            تاریخ تمدید
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            وضعیت
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            جزئیات
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($orders as $order)
                        @php
                            $invoice    = $order->invoice;
                            $customerName = $order->customer->full_name ?? $order->client_name ?? 'مشتری نامشخص';
                            $issueDate  = $invoice ? $invoice->issue_date : $order->issue_date;

                            $statusColor = $order->status->color ?? '#6b7280';
                            $statusName  = $order->status->name ?? 'نامشخص';

                            $renewalDate = $order->renewal_date ? Carbon::parse($order->renewal_date) : null;
                            if (!$renewalDate && $order->billing_cycle && $issueDate) {
                                $renewalDate = clone Carbon::parse($issueDate);
                                switch ($order->billing_cycle) {
                                    case 'monthly':     $renewalDate->addMonth(); break;
                                    case 'quarterly':   $renewalDate->addMonths(3); break;
                                    case 'semi_annual': $renewalDate->addMonths(6); break;
                                    case 'annual':      $renewalDate->addYear(); break;
                                }
                            }

                            // مبلغ پایه - همیشه از سرویس گرفته می‌شود
                            $basePrice = $order->service->base_price ?? 0;

                            // مبلغ نهایی سرویس (فاکتور) - همیشه ثابت
                            $finalServicePrice = $invoice ? ($invoice->total ?? 0) : ($order->total_amount ?? 0);

                            // مبلغ تمدید - خودکار/دستی
                            $isRenewalManual = ($order->renewal_price_type ?? 'auto') === 'manual';
                            $calculatedRenewalPrice = $isRenewalManual
                                ? ($order->renewal_price ?? 0)
                                : (($order->service && $order->billing_cycle && isset($order->service->renewal_prices[$order->billing_cycle]))
                                    ? $order->service->renewal_prices[$order->billing_cycle]
                                    : ($order->renewal_price ?? 0));
                        @endphp

                        <tr class="group bg-white dark:bg-gray-800 hover:bg-indigo-50/50 dark:hover:bg-indigo-500/10 transition-colors duration-300">

                            {{-- سرویس و مشتری --}}
                            <td class="px-6 py-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex-shrink-0 w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-base border border-indigo-200 dark:border-indigo-500/30">
                                        {{ mb_substr($order->service->name ?? 'د', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div
                                            class="font-black text-gray-900 dark:text-white text-base truncate">{{ $order->service->name ?? $order->notes ?? 'ردیف دستی' }}</div>
                                        <div
                                            class="mt-1 flex items-center gap-1.5 text-sm font-bold text-gray-500 dark:text-gray-400">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span class="truncate max-w-[160px]">{{ $customerName }}</span>
                                        </div>
                                        <div
                                            class="text-xs font-bold text-gray-400 mt-1 uppercase tabular-nums">{{ $order->order_number }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- شماره فاکتور --}}
                            <td class="px-6 py-4 text-center align-middle">
                                @if($invoice)
                                    <a href="{{ route('services.invoices.show', $invoice) }}"
                                       onclick="event.stopPropagation()"
                                       class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-indigo-100 dark:bg-gray-700/50 dark:hover:bg-indigo-500/20 text-sm font-bold text-gray-600 hover:text-indigo-700 dark:text-gray-300 dark:hover:text-indigo-300 px-3 py-1.5 rounded-md tabular-nums transition-colors">
                                        <svg class="w-4 h-4 text-gray-400 hover:text-indigo-500" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ $faNum($invoice->invoice_number) }}
                                    </a>
                                @else
                                    <span class="text-sm font-bold text-gray-400">بدون فاکتور</span>
                                @endif
                            </td>

                            {{-- مبلغ پایه --}}
                            <td class="px-6 py-4 text-center align-middle">
                                <div
                                    class="font-black text-gray-900 dark:text-gray-100 text-base tabular-nums">{{ $faNum(number_format($basePrice)) }}</div>
                                <div class="text-[10px] font-bold text-gray-400 mt-0.5">{{ $currencyLabel }}</div>
                            </td>

                            {{-- دوره و مبلغ تمدید --}}
                            <td class="px-6 py-4 align-middle text-center">
                                @if($order->billing_cycle && $calculatedRenewalPrice > 0)
                                    <div>
                                        <span
                                            class="font-black text-slate-800 dark:text-slate-200 text-sm tabular-nums">{{ $faNum(number_format($calculatedRenewalPrice)) }}</span>
                                        <span class="text-xs text-gray-500">{{ $currencyLabel }}</span>
                                    </div>
                                    <span
                                        class="inline-block mt-1.5 text-xs font-black text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-500/20 px-2.5 py-1 rounded-md">
                                        {{ $billingCycleLabels[$order->billing_cycle] ?? 'نامشخص' }}
                                    </span>
                                    <div
                                        class="text-[10px] {{ $isRenewalManual ? 'text-amber-500' : 'text-indigo-500' }} mt-1 cursor-help"
                                        title="قیمت دوره‌ای بر اساس سرویس. در صورت تغییر قیمت سرویس، این مبلغ خودکار آپدیت می‌شود، مگر دستی ویرایش شده باشد.">
                                        {{ $isRenewalManual ? '(دستی)' : '(خودکار)' }}
                                    </div>
                                @else
                                    <span
                                        class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-md">بدون تمدید</span>
                                @endif
                            </td>

                            {{-- مبلغ نهایی سرویس (فاکتور) --}}
                            <td class="px-6 py-4 text-center align-middle">
                                <div
                                    class="font-black text-gray-900 dark:text-gray-100 text-base tabular-nums">{{ $faNum(number_format($finalServicePrice)) }}</div>
                                <div class="text-[10px] font-bold text-gray-400 mt-0.5">{{ $currencyLabel }}</div>
                            </td>

                            {{-- تاریخ صدور --}}
                            <td class="px-6 py-4 text-center align-middle">
                                <span
                                    class="font-bold text-gray-700 dark:text-gray-300 text-sm tabular-nums dir-ltr block">
                                    {{ $issueDate ? $faNum($toJalali($issueDate)->format('Y/m/d')) : '—' }}
                                </span>
                            </td>

                            {{-- تاریخ تمدید --}}
                            <td class="px-6 py-4 align-middle text-center">
                                @if($renewalDate)
                                    <div
                                        class="font-black text-gray-700 dark:text-gray-300 text-sm tabular-nums dir-ltr whitespace-nowrap">
                                        {{ $faNum($toJalali($renewalDate)->format('Y/m/d')) }}
                                    </div>

                                @else
                                    <span class="text-sm text-gray-400 font-bold">—</span>
                                @endif
                            </td>

                            {{-- وضعیت --}}
                            <td class="px-6 py-4 align-middle text-center">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black border"
                                    style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}40">
                                    <span class="w-2 h-2 rounded-full animate-pulse"
                                          style="background: {{ $statusColor }}"></span>
                                    {{ $statusName }}
                                </span>
                            </td>

                            {{-- جزئیات --}}
                            <td class="px-6 py-4 align-middle text-end">
                                <a href="{{ route('services.orders.show', $order) }}"
                                   class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-400 hover:bg-indigo-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-500/20 transition-all shadow-sm active:scale-95"
                                   title="جزئیات سفارش">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-24 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <span
                                        class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 mb-4">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </span>
                                    <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">سفارشی یافت
                                        نشد</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">هنوز سفارشی ثبت نشده است.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div
                    class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
