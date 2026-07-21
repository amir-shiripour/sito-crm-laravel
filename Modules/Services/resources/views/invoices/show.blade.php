@php
    use Modules\Services\App\Http\Models\Status;
    use Morilog\Jalali\Jalalian;
    $isProforma = !$invoice->invoice_number;
@endphp
@extends('layouts.user')
@section('title', ($isProforma ? 'پیش فاکتور: ' : 'فاکتور: ') . ($invoice->invoice_number ?: $invoice->proforma_invoice_number))

@php
    $currency      = $currency ?? 'toman';
    $currencyLabel = $currency === 'rial' ? 'ریال' : 'تومان';

    $toJalali = function ($date) {
        if (!$date) return null;
        if ($date instanceof \Carbon\Carbon && $date->year < 1900) {
            return new Jalalian($date->year, $date->month, $date->day, $date->hour, $date->minute, $date->second);
        }
        if ($date instanceof \Carbon\Carbon) {
            return Jalalian::fromCarbon($date);
        }
        return $date;
    };

    $buyerExtraFieldIds = json_decode($settings['services_invoice_client_fields'] ?? '[]', true) ?: [];
    $buyerExtraFields = ($invoice->customer && !empty($buyerExtraFieldIds))
        ? $invoice->customer->getFormFieldValues($buyerExtraFieldIds)
        : [];

    $remainingAmount = $invoice->remainingAmount();
    $isCanceled = str_contains($invoice->status?->name ?? '', 'لغو');

    // Just use the real status from the database, driven purely by Workflows!
    $statusName = $invoice->status?->name ?? '—';

    // Fetch the color for all statuses from the database
    $allStatuses = Status::whereIn('type', ['payment', 'invoice'])->get()->keyBy('name');
    $statusColor = $invoice->status?->color ?? '#6b7280';
    $paidColor = $allStatuses['پرداخت شده']->color ?? '#00ff40';
    $overdueColor = $allStatuses['معوقه']->color ?? '#ff8000';


    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";

    $getPaymentMethodName = function($method) use ($settings) {
        $posDevices = json_decode($settings['pos_devices'] ?? '[]', true);
        $bankAccounts = json_decode($settings['bank_transfer_accounts'] ?? '[]', true);

        if (str_starts_with($method, 'pos-')) {
            $id = substr($method, 4);
            foreach ($posDevices as $device) {
                if ($device['id'] === $id) {
                    return 'کارتخوان ' . $device['name'];
                }
            }
            return 'کارتخوان';
        }
        if (str_starts_with($method, 'cash-')) {
            return 'نقد';
        }
        if ($method === 'cod') {
            return 'پرداخت در محل';
        }
        if (str_starts_with($method, 'transfer-')) {
             $id = substr($method, 9);
             foreach ($bankAccounts as $account) {
                if ($account['id'] === $id) {
                    return 'انتقال به ' . $account['account_number'];
                }
            }
            return 'انتقال بانکی';
        }
        return $method;
    };

@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ route('services.invoices.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">لیست
                فاکتورها</a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span
                class="text-gray-900 dark:text-white font-bold truncate max-w-xs tabular-nums">{{ $faNum($invoice->invoice_number ?: $invoice->proforma_invoice_number) }}</span>
        </nav>

        {{-- Hero Header --}}
        <div class="{{ $cardClass }}">
            <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start gap-5">
                    <div
                        class="shrink-0 flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                             <span
                                 class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $isProforma ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20' : 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20' }}">
                                {{ $isProforma ? 'پیش فاکتور' : 'فاکتور رسمی' }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border"
                                style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}33;">
                                <span class="w-2 h-2 rounded-full" style="background: {{ $statusColor }}"></span>{{ $statusName }}
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight tabular-nums">{{ $faNum($invoice->invoice_number ?: $invoice->proforma_invoice_number) }}</h1>
                        <div
                            class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-3 text-sm text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1.5"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                                         stroke="currentColor" stroke-width="1.5"><path
                                        stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> تاریخ صدور: {{ $faNum($toJalali($invoice->issue_date)->format('Y/m/d')) }}</span>
                            @if($invoice->due_date)
                                <span class="flex items-center gap-1.5"><svg class="w-5 h-5" fill="none"
                                                                             viewBox="0 0 24 24" stroke="currentColor"
                                                                             stroke-width="1.5"><path
                                            stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> تاریخ سررسید: {{ $faNum($toJalali($invoice->due_date)->format('Y/m/d')) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    @if($isProforma)
                        @can('convertToInvoice', $invoice)
                            <form action="{{ route('services.invoices.convertToInvoice', $invoice) }}" method="POST"
                                  onsubmit="return handleConvert(this)">
                                @csrf
                                <input type="hidden" name="invoice_number" id="convert_invoice_number">
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold shadow-lg transition-all active:scale-95 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-400 hover:to-green-500 shadow-green-500/30 hover:shadow-green-500/50">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    تبدیل به فاکتور
                                </button>
                            </form>
                        @endcan
                    @else
                        @can('update', $invoice)
                            @if (!str_contains($invoice->status?->name ?? '', 'لغو') && !str_contains($invoice->status?->name ?? '', 'cancel') && $remainingAmount > 0)
                                <a href="{{ route('services.invoices.payment', $invoice) }}"
                                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold shadow-lg transition-all active:scale-95 @if(!$invoice->isPaid()) bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-emerald-500/30 hover:shadow-emerald-500/50 @else bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-400 hover:to-sky-500 shadow-sky-500/30 hover:shadow-sky-500/50 @endif">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    @if($invoice->paid_amount == 0)
                                        ثبت پرداختی
                                    @else
                                        ثبت پرداخت جدید
                                    @endif
                                </a>
                            @endif
                        @endcan
                    @endif

                    <a href="{{ route('services.invoices.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        بازگشت</a>
                    <a href="{{ route('services.invoices.print', $invoice) }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 dark:bg-sky-500/10 dark:text-sky-400 dark:hover:bg-sky-500/20 text-sm font-bold transition-all active:scale-95"
                       id="pdf-download-btn">
                        <svg id="pdf-icon-default" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <svg id="pdf-icon-spinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span id="pdf-btn-text">دانلود PDF</span>
                    </a>
                    @can('update', $invoice)
                        @if(!$invoice->status?->locksInvoice() && !str_contains($invoice->status?->name ?? '', 'لغو') && !str_contains($invoice->status?->name ?? '', 'cancel'))
                            <a href="{{ route('services.invoices.edit', $invoice) }}"
                               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                ویرایش</a>
                        @endif
                    @endcan
                    @if ($isProforma)
                        @can('delete', $invoice)
                            <form action="{{ route('services.invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('آیا از حذف این پیش فاکتور اطمینان دارید؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 text-sm font-bold transition-all active:scale-95">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    حذف
                                </button>
                            </form>
                        @endcan
                    @else
                        @can('update', $invoice)
                            @if (!str_contains($invoice->status?->name ?? '', 'لغو') && !str_contains($invoice->status?->name ?? '', 'cancel') && $remainingAmount > 0)
                                <form action="{{ route('services.invoices.cancel', $invoice) }}" method="POST"
                                      onsubmit="return confirm('آیا از لغو این فاکتور اطمینان دارید؟ این عمل غیرقابل بازگشت است.');">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 text-sm font-bold transition-all active:scale-95">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        لغو فاکتور
                                    </button>
                                </form>
                            @endif
                        @endcan
                    @endif
                </div>
            </div>
        </div>

        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0"><svg
                        class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path
                            stroke-linecap="round" stroke-linejoin="round"
                            d="M5 13l4 4L19 7"/></svg></span>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div
                class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-500/10 dark:border-red-500/20 text-red-800 dark:text-red-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-1.5 rounded-full shrink-0"><svg
                        class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path
                            stroke-linecap="round" stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"/></svg></span>{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- SIDEBAR --}}
            <div class="lg:col-span-4 xl:col-span-3 space-y-8 order-2 lg:order-1">
                <div class="{{ $cardClass }}">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div class="p-2 bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            اطلاعات مشتری
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 gap-4 text-sm">
                        @foreach($buyerExtraFields as $field)
                            <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700"><span
                                    class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">{{ $field['label'] }}</span><span
                                    class="text-gray-800 dark:text-gray-200 text-base font-medium break-all">{{ $faNum($field['value']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="{{ $cardClass }}">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-indigo-50 to-transparent dark:from-indigo-500/10">
                        <h3 class="text-lg font-black text-indigo-700 dark:text-indigo-400 flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                </svg>
                            </div>
                            خلاصه وضعیت فاکتور
                        </h3>
                    </div>
                    <div class="p-6 space-y-4 text-base">

                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-400 mb-4 pb-4 border-b border-gray-100 dark:border-gray-700/50">
                            <span class="font-medium text-sm">وضعیت فعلی:</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border"
                                  style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}33;">
                                <span class="w-2 h-2 rounded-full" style="background: {{ $statusColor }}"></span>{{ $statusName }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                            <span class="font-medium">مبلغ کل:</span>
                            <span class="tabular-nums font-bold text-gray-800 dark:text-gray-200">{{ $faNum(number_format($invoice->total)) }} <span class="text-xs">{{ $currencyLabel }}</span></span>
                        </div>
                        <div class="flex justify-between items-center text-emerald-600 dark:text-emerald-400">
                            <span class="font-medium">پرداخت شده:</span>
                            <span class="tabular-nums font-bold">{{ $faNum(number_format($invoice->paid_amount)) }} <span class="text-xs">{{ $currencyLabel }}</span></span>
                        </div>
                        <div class="pt-4 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <span class="font-black text-gray-900 dark:text-white">مانده بدهی:</span>
                            <span class="font-black text-rose-600 dark:text-rose-400 text-xl tabular-nums">{{ $faNum(number_format($remainingAmount)) }} <span class="text-sm font-bold">{{ $currencyLabel }}</span></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MAIN CONTENT --}}
            <div class="lg:col-span-8 xl:col-span-9 space-y-8 order-1 lg:order-2">
                <div class="{{ $cardClass }}">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div class="p-2 bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </div>
                            اقلام و خدمات فاکتور
                        </h3>
                        <span class="text-sm font-bold text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10 px-3 py-1.5 rounded-lg border border-violet-100 dark:border-violet-500/20">{{ $faNum($invoice->items->count()) }} ردیف</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-start border-collapse min-w-[1200px]">
                            <thead class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 w-[22%] min-w-[220px] font-bold text-start">سرویس</th>
                                <th class="px-4 py-3 w-[25%] min-w-[250px] font-bold text-start">شرح</th>
                                <th class="px-4 py-3 w-[12%] min-w-[140px] font-bold text-center">تعداد / واحد</th>
                                <th class="px-4 py-3 w-[18%] min-w-[220px] font-bold text-center">مبلغ واحد</th>
                                <th class="px-4 py-3 w-[13%] min-w-[150px] font-bold text-center">تخفیف</th>
                                @if(($settings['services_tax_mode'] ?? 'invoice') === 'item')
                                    <th class="px-4 py-3 w-[12%] min-w-[140px] font-bold text-center">مالیات ردیف</th>
                                @endif
                                <th class="px-4 py-3 w-[10%] min-w-[150px] font-bold text-center">جمع کل</th>
                            </tr>
                            </thead>
                            @foreach($invoice->items as $item)
                                @php
                                    $customFieldsCollection = $item->service ? $item->service->customFields : collect([]);
                                    $savedCustomFields = $item->meta['custom_fields'] ?? [];
                                    $displayQty = fmod($item->quantity, 1.0) === 0.0 ? (int)$item->quantity : $item->quantity;
                                @endphp
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 transition-all">
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                                    <td class="px-4 py-4 align-top font-bold text-gray-800 dark:text-gray-100 text-start">
                                        {{ $item->custom_service_name ?: ($item->service->name ?? 'ردیف دستی') }}
                                    </td>
                                    <td class="px-4 py-4 align-top text-gray-600 dark:text-gray-400 text-start">
                                        {{ $item->description }}
                                    </td>
                                    <td class="px-4 py-4 align-top text-center">
                                        <div class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 overflow-hidden shadow-sm px-3 py-2">
                                            <span class="font-black text-gray-900 dark:text-white tabular-nums">{{ $faNum($displayQty) }}</span>
                                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500">{{ $item->unit ?? 'عدد' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 align-top text-center tabular-nums font-medium text-gray-700 dark:text-gray-300">
                                        {{ $faNum(number_format($item->unit_price)) }}
                                        <span class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                    </td>
                                    <td class="px-4 py-4 align-top text-center tabular-nums font-medium text-red-500 dark:text-red-400">
                                        @if($item->discount > 0)
                                            {{ $faNum(number_format($item->discount)) }}
                                            <span class="text-[10px] text-red-400/80 ms-1">{{ $currencyLabel }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    @if(($settings['services_tax_mode'] ?? 'invoice') === 'item')
                                        <td class="px-4 py-4 align-top text-center tabular-nums font-medium text-amber-600 dark:text-amber-400">
                                            @if($item->tax_amount > 0)
                                                {{ $faNum(number_format($item->tax_amount)) }}
                                                <span class="text-[10px] text-amber-400/80 ms-1">{{ $currencyLabel }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-4 py-4 align-top tabular-nums font-bold text-gray-800 dark:text-gray-100 text-center whitespace-nowrap">
                                        @php
                                            $rowGrandTotal = $item->total;
                                            if (($settings['services_tax_mode'] ?? 'invoice') === 'item') {
                                                $rowGrandTotal += $item->tax_amount;
                                            }
                                        @endphp
                                        {{ $faNum(number_format($rowGrandTotal)) }}
                                        <span class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                    </td>
                                </tr>

                                @if(!empty($savedCustomFields))
                                    @foreach($savedCustomFields as $field_id => $value)
                                        @php
                                            $fieldDef = $customFieldsCollection->firstWhere('id', $field_id);
                                            if (!$fieldDef || !$fieldDef->has_pricing) continue;

                                            if (is_array($value)) { $displayValue = implode('، ', $value); }
                                            elseif ($fieldDef->type === 'checkbox') { $displayValue = $value ? 'انتخاب شده' : null; }
                                            else { $displayValue = $value ?: null; }

                                            if (!$displayValue) continue;

                                            $fieldPrice = $item->meta['custom_fields_prices'][$field_id] ?? null;
                                            $fieldDiscount = $item->meta['custom_fields_discounts'][$field_id] ?? 0;

                                            if ($fieldPrice === null) {
                                                $fieldPrice = $fieldDef->pricing_type === 'percentage'
                                                                ? ($item->unit_price * ((float)$fieldDef->pricing_amount / 100))
                                                                : (float)$fieldDef->pricing_amount;
                                            }
                                        @endphp
                                        <tr class="bg-indigo-50/20 dark:bg-indigo-500/5 border-y border-dashed border-indigo-100/70 dark:border-indigo-500/10">
                                            <td class="px-4 py-2.5 relative text-start">
                                                <div class="absolute top-0 bottom-0 right-5 w-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                                <div class="absolute top-1/2 right-5 w-3 h-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                                <div class="pe-4 ps-6 flex items-center gap-2">
                                                        <span class="flex items-center justify-center w-5 h-5 rounded-md bg-indigo-100/80 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 shrink-0 shadow-sm">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                        </span>
                                                    <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 truncate">{{ $fieldDef->label }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 text-start">
                                                <span class="inline-block text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100/70 dark:bg-gray-800/60 px-2.5 py-1 rounded-lg border border-gray-200/40 dark:border-gray-700/40">{{ $displayValue }}</span>
                                            </td>
                                            <td class="px-4 py-2.5 text-center text-xs text-gray-400">
                                                {{ $faNum($displayQty) }}
                                            </td>
                                            <td class="px-4 py-2.5 text-center tabular-nums text-xs font-medium text-gray-700 dark:text-gray-300">
                                                {{ $faNum(number_format($fieldPrice)) }}
                                                <span class="text-[9px] text-gray-400 ms-0.5">{{ $currencyLabel }}</span>
                                            </td>
                                            <td class="px-4 py-2.5 text-center tabular-nums text-xs font-medium text-red-500 dark:text-red-400">
                                                @if($fieldDiscount > 0)
                                                    {{ $faNum(number_format($fieldDiscount)) }}
                                                    <span class="text-[9px] text-red-400/80 ms-0.5">{{ $currencyLabel }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            @php
                                                $cfTaxAmount = 0;
                                                $cfTaxPercent = 0;
                                                if (($settings['services_tax_mode'] ?? 'invoice') === 'item' && !empty($settings['services_tax_apply_custom_fields'])) {
                                                    $cfTaxPercent = $item->meta['custom_fields_taxes'][$field_id] ?? 0;
                                                    $cfTaxable = max(0, ($fieldPrice * $item->quantity) - $fieldDiscount);
                                                    $cfTaxAmount = $cfTaxable * ($cfTaxPercent / 100);
                                                }
                                                $cfRowTotal = max(0, ($fieldPrice * $item->quantity) - $fieldDiscount) + $cfTaxAmount;
                                            @endphp
                                            @if(($settings['services_tax_mode'] ?? 'invoice') === 'item')
                                                <td class="px-4 py-2.5 text-center text-xs tabular-nums text-amber-600 dark:text-amber-400">
                                                    @if($cfTaxAmount > 0)
                                                        {{ $faNum(number_format($cfTaxAmount)) }}
                                                        <span class="text-[9px] text-amber-400/80 ms-0.5">{{ $currencyLabel }} ({{ $faNum((float)$cfTaxPercent) }}٪)</span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="px-4 py-2.5 tabular-nums font-black text-indigo-600 dark:text-indigo-400 text-center whitespace-nowrap text-xs">
                                                {{ $faNum(number_format($cfRowTotal)) }}
                                                <span class="text-[9px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            @endforeach
                        </table>
                    </div>

                    <div class="bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/50 p-6 flex justify-end">
                        <div class="w-full sm:w-96 space-y-4 text-base">
                            <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                <span class="font-medium">جمع مبالغ:</span>
                                <span class="tabular-nums font-bold">{{ $faNum(number_format($invoice->subtotal)) }} <span class="text-xs">{{ $currencyLabel }}</span></span>
                            </div>
                            @if($invoice->discount_amount > 0)
                                <div class="flex justify-between items-center text-rose-500 dark:text-rose-400">
                                    <span class="font-medium">مجموع تخفیف‌ها:</span>
                                    <span class="tabular-nums font-bold">− {{ $faNum(number_format($invoice->discount_amount)) }} <span class="text-xs">{{ $currencyLabel }}</span></span>
                                </div>
                            @endif
                            @if($invoice->tax_amount > 0)
                                <div class="flex justify-between items-center text-amber-600 dark:text-amber-400">
                                    <span class="font-medium">مالیات
                                        @if((float)$invoice->tax_percent > 0)
                                            ({{ $faNum((float)$invoice->tax_percent) }}٪):
                                        @else
                                            (تفکیکی هر ردیف):
                                        @endif
                                    </span>
                                    <span class="tabular-nums font-bold">+ {{ $faNum(number_format($invoice->tax_amount)) }} <span class="text-xs">{{ $currencyLabel }}</span></span>
                                </div>
                            @endif
                            <div class="pt-4 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <span class="font-black text-gray-900 dark:text-white text-lg">مبلغ نهایی:</span>
                                <span class="font-black text-indigo-600 dark:text-indigo-400 text-2xl tabular-nums">{{ $faNum(number_format($invoice->total)) }} <span class="text-sm font-bold text-indigo-400/80">{{ $currencyLabel }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($invoice->payments->isNotEmpty())
                    <div class="{{ $cardClass }}">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-emerald-50 to-transparent dark:from-emerald-500/10">
                            <h3 class="text-lg font-black text-emerald-700 dark:text-emerald-400 flex items-center gap-3">
                                <div class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg shadow-sm">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                اطلاعات پرداخت
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-start border-collapse">
                                <thead class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-start">تاریخ</th>
                                    <th class="px-4 py-3 font-bold text-start">مبلغ</th>
                                    <th class="px-4 py-3 font-bold text-start">وضعیت</th>
                                    <th class="px-4 py-3 font-bold text-start">روش پرداخت</th>
                                    <th class="px-4 py-3 font-bold text-start">کد رهگیری</th>
                                    <th class="px-4 py-3 font-bold text-start">یادداشت</th>
                                    <th class="px-4 py-3 font-bold text-start"></th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                @foreach ($invoice->payments as $payment)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                        <td class="px-4 py-4 tabular-nums text-gray-600 dark:text-gray-400 text-start">{{ $faNum($toJalali($payment->paid_at)->format('Y/m/d')) }}</td>
                                        <td class="px-4 py-4 font-bold text-gray-800 dark:text-gray-200 tabular-nums text-start">{{ $faNum(number_format($payment->amount)) }} {{ $currencyLabel }}</td>
                                        <td class="px-4 py-4 text-start">
                                            @if($payment->status === 'canceled')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                                                    لغو شده
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold" style="background-color: {{ $paidColor }}15; color: {{ $paidColor }}; border-color: {{ $paidColor }}33;">
                                                    پرداخت شده
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300 text-start">{{ $getPaymentMethodName($payment->method) }}</td>
                                        <td class="px-4 py-4 text-gray-500 dark:text-gray-400 tabular-nums text-start">{{ $payment->transaction_id ?: '—' }}</td>
                                        <td class="px-4 py-4 text-gray-500 dark:text-gray-400 text-start">{{ $payment->notes ?: '—' }}</td>
                                        <td class="px-4 py-4 text-start">
                                            @if($payment->status !== 'canceled' && !str_contains($invoice->status?->name ?? '', 'لغو'))
                                                <form action="{{ route('services.invoices.cancelPayment', [$invoice, $payment]) }}" method="POST" onsubmit="return confirm('آیا از لغو این پرداخت اطمینان دارید؟');">
                                                    @csrf
                                                    <button type="submit" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-500 text-xs font-bold">لغو</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif


                @if($invoice->notes)
                    <div class="{{ $cardClass }} p-6 sm:p-8 border-l-4 border-l-amber-500 bg-amber-50/50 dark:bg-amber-900/10">
                        <div class="flex items-start gap-4 text-amber-800 dark:text-amber-300">
                            <svg class="w-8 h-8 shrink-0 mt-0.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <div>
                                <strong class="block mb-2 font-black text-lg tracking-tight">یادداشت فاکتور</strong>
                                <p class="font-medium text-base leading-loose">{{ $invoice->notes }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($invoice->activities->isNotEmpty())
                    <div class="{{ $cardClass }}">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                            <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                                <div class="p-2 bg-gray-100 text-gray-600 dark:bg-gray-700/40 dark:text-gray-300 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                تاریخچه فعالیت و لاگ‌ها
                            </h3>
                        </div>
                        <div class="p-8">
                            <ol class="relative border-e-2 border-gray-100 dark:border-gray-700/50 pe-6 space-y-8">
                                @foreach($invoice->activities->take(10) as $log)
                                    <li class="relative">
                                        <span class="absolute top-1.5 -end-7.75 w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-white dark:ring-gray-800"></span>
                                        <span class="text-base text-gray-800 dark:text-gray-200 font-bold block">{{ $log->description ?? $log->action }}</span>
                                        <div class="flex items-center gap-3 mt-2">
                                            @if($log->user)
                                                <span class="text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700 px-2.5 py-1 rounded-md flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    {{ $log->user->name }}
                                                </span>
                                            @endif
                                            <span class="text-xs font-medium text-gray-400 dir-ltr tabular-nums">{{ $faNum($toJalali($log->created_at)->format('Y-m-d H:i')) }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function handleConvert(form) {
                @if(empty($settings['services_invoice_auto_numbering']) && empty($settings['services_invoice_auto']))
                let num = prompt('سیستم روی شماره‌گذاری دستی تنظیم شده است. لطفاً شماره فاکتور جدید را وارد کنید:');
                if (!num || num.trim() === '') {
                    alert('وارد کردن شماره فاکتور الزامی است.');
                    return false;
                }
                form.querySelector('#convert_invoice_number').value = num;
                @endif
                    return true;
            }

            document.getElementById('pdf-download-btn')?.addEventListener('click', function () {
                const btn = this;
                const iconDef = document.getElementById('pdf-icon-default');
                const iconSpin = document.getElementById('pdf-icon-spinner');
                const btnText = document.getElementById('pdf-btn-text');
                iconDef.classList.add('hidden');
                iconSpin.classList.remove('hidden');
                btnText.textContent = 'در حال ساخت PDF...';
                btn.classList.add('opacity-75', 'pointer-events-none');
                setTimeout(() => {
                    iconDef.classList.remove('hidden');
                    iconSpin.classList.add('hidden');
                    btnText.textContent = 'دانلود PDF';
                    btn.classList.remove('opacity-75', 'pointer-events-none');
                }, 30000);
            });
        </script>
    @endpush
@endsection
