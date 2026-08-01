@php use Carbon\Carbon;use Modules\Market\Entities\MarketSetting;use Morilog\Jalali\Jalalian; @endphp
@props(['invoice'])

@if($invoice)
    @php
        $toJalali = function ($date) {
            if (!$date) return '—';
            try {
                $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
                return Jalalian::fromCarbon($carbon)->format('Y/m/d');
            } catch (\Throwable $e) {
                return '—';
            }
        };

        $faNum = function($str) {
            if (is_null($str)) return '';
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return str_replace(range(0, 9), $persian, (string)$str);
        };

        $invStatusColor = $invoice->status?->color ?? '#6b7280';
        $remaining = max(0, (float)$invoice->total - (float)$invoice->paid_amount);
        $invCurrency = MarketSetting::getValue('general.currency', 'toman');
        $currencyLabel = ($invCurrency === 'rial' || $invCurrency === 'IRR') ? 'ریال' : 'تومان';
    @endphp

    <div
        class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4 hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-black text-amber-700 dark:text-amber-400 text-base flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                فاکتور مرتبط سرویس و خدمات
            </h3>
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between items-center">
                <span class="text-gray-400 dark:text-gray-500">شماره فاکتور:</span>
                <span
                    class="font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-1 rounded-xl text-xs">
                    {{ $faNum($invoice->invoice_number ?: $invoice->proforma_invoice_number) }}
                </span>
            </div>

            <div class="flex justify-between items-center">
                <span class="text-gray-400 dark:text-gray-500">وضعیت فاکتور:</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border"
                      style="background: {{ $invStatusColor }}15; color: {{ $invStatusColor }}; border-color: {{ $invStatusColor }}40">
                    <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $invStatusColor }}"></span>
                    {{ $invoice->status?->name ?? 'نامشخص' }}
                </span>
            </div>

            <div class="flex justify-between items-center">
                <span class="text-gray-400 dark:text-gray-500">تاریخ صدور:</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200 dir-ltr text-xs">
                    {{ $faNum($toJalali($invoice->issue_date)) }}
                </span>
            </div>

            <div class="flex justify-between items-center">
                <span class="text-gray-400 dark:text-gray-500">تاریخ سررسید:</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200 dir-ltr text-xs">
                    {{ $faNum($toJalali($invoice->due_date)) }}
                </span>
            </div>

            <div class="flex justify-between items-center pt-2 border-t border-gray-100 dark:border-gray-700/50">
                <span class="text-gray-400 dark:text-gray-500">مبلغ کل فاکتور:</span>
                <span class="font-bold text-gray-900 dark:text-white">
                    {{ $faNum(number_format($invoice->total)) }} <span
                        class="text-[10px] text-gray-400 font-normal">{{ $currencyLabel }}</span>
                </span>
            </div>

            <div class="flex justify-between items-center">
                <span class="text-gray-400 dark:text-gray-500">پرداخت‌شده:</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $faNum(number_format($invoice->paid_amount)) }} <span
                        class="text-[10px] font-normal">{{ $currencyLabel }}</span>
                </span>
            </div>

            <div class="flex justify-between items-center">
                <span class="text-gray-400 dark:text-gray-500">مانده پرداخت:</span>
                <span class="font-black {{ $remaining > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                    @if($remaining > 0)
                        {{ $faNum(number_format($remaining)) }} <span
                            class="text-[10px] font-normal">{{ $currencyLabel }}</span>
                    @else
                        ۰ {{ $currencyLabel }} (تسویه)
                    @endif
                </span>
            </div>
        </div>

        <div class="pt-3 border-t border-gray-100 dark:border-gray-700/50">
            <a href="{{ route('services.invoices.show', [$invoice, 'back_url' => request()->fullUrl()]) }}"
               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm hover:shadow transition-all">
                مشاهده کامل فاکتور سرویس و خدمات
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>
    </div>
@endif
