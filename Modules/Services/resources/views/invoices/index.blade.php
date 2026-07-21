@php
    use Modules\Services\App\Http\Models\Invoice;
    use Morilog\Jalali\Jalalian;
    use Modules\Services\App\Http\Models\Status;
@endphp
@extends('layouts.user')
@section('title', 'فاکتورها')

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

    $faNum = function($str) {
        if (is_null($str)) return '';
        // Extract only the date part
        $datePart = explode(' ', (string)$str)[0];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, $datePart);
    };

    // Cache all relevant statuses to avoid querying in a loop
    $allStatuses = Status::whereIn('type', ['payment', 'invoice'])->get()->keyBy('name');
@endphp

@section('content')
    {{-- توسعه عرض کانتینر به سایز 2XL --}}
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
                مدیریت فاکتورها
            </h1>
            @can('create', Invoice::class)
                <div class="flex items-center gap-4">
                    <a href="{{ route('services.invoices.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all duration-200 active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        سفارش جدید
                    </a>
                </div>
            @endcan
        </div>

        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path
                            stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary strip --}}
        @php
            $sumTotal = $invoices->sum('total');
            $sumPaid  = $invoices->sum('paid_amount');
            $sumDue   = max(0, $sumTotal - $sumPaid);
            $sumCount = $invoices->total() ?? $invoices->count();
            $statCardClass = "rounded-3xl border p-5 flex items-center gap-5 overflow-hidden";
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">تعداد فاکتور</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ $faNum(number_format($sumCount)) }}</span>
                    </div>
                </div>
            </div>
            <div class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">جمع کل (این صفحه)</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ $faNum(number_format($sumTotal)) }}</span>
                        <span class="text-[11px] font-medium text-gray-400 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
            <div class="{{ $statCardClass }} bg-emerald-50/60 dark:bg-emerald-500/5 border-emerald-100 dark:border-emerald-500/20 shadow-sm">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1 truncate">پرداخت‌شده</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums break-all">{{ $faNum(number_format($sumPaid)) }}</span>
                        <span class="text-[11px] font-medium text-emerald-500/80 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
            <div class="{{ $statCardClass }} bg-amber-50/60 dark:bg-amber-500/5 border-amber-100 dark:border-amber-500/20 shadow-sm">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wide block mb-1 truncate">مانده دریافتی</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black text-amber-700 dark:text-amber-400 tabular-nums break-all">{{ $faNum(number_format($sumDue)) }}</span>
                        <span class="text-[11px] font-medium text-amber-500/80 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter bar (تنظیم برای رزولوشن عریض) --}}
        <form method="GET"
              class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-5">
                <div class="relative xl:col-span-8">
                    <div class="absolute inset-y-0 start-0 ps-5 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو: نام مشتری، شماره فاکتور..."
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
                <div class="xl:col-span-2 flex gap-2">
                    <button type="submit"
                            class="flex-1 px-6 py-3.5 rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'status_id', 'payment_mode', 'customer_id', 'date_from', 'date_to']))
                        <a href="{{ route('services.invoices.index') }}" title="پاک کردن فیلترها"
                           class="px-5 py-3.5 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            شماره فاکتور
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مشتری
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            مبلغ کل
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            وضعیت پرداخت
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            وضعیت فاکتور
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            تاریخ صدور
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            تاریخ سررسید
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($invoices as $invoice)
                        @php
                            $remaining   = $invoice->remainingAmount();
                            $isCanceled  = str_contains($invoice->status?->name ?? '', 'لغو');

                            $statusName = $invoice->status?->name ?? '—';
                            $statusColor = $invoice->status?->color ?? '#6b7280';
                        @endphp
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <a href="{{ route('services.invoices.show', $invoice) }}" class="font-bold text-indigo-600 dark:text-indigo-400 text-base tabular-nums hover:underline">
                                    {{ $faNum($invoice->invoice_number) }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 dark:text-white text-base">{{ $invoice->customer?->full_name ?? $invoice->client_name ?? '—' }}</div>
                                @if($invoice->customer?->phone ?? $invoice->client_phone)
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 dir-ltr mt-1 tabular-nums w-fit">{{ $faNum($invoice->customer?->phone ?? $invoice->client_phone) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-gray-900 dark:text-gray-100 text-base tabular-nums">{{ $faNum(number_format($invoice->total)) }}</span>
                                <span class="text-[11px] font-medium text-gray-400 block">{{ $currencyLabel }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($isCanceled)
                                    <span class="font-black text-rose-500 dark:text-rose-400 text-base tabular-nums">لغو شده</span>
                                    <span class="block text-[11px] font-bold text-gray-400 mt-1">امکان پرداخت ندارد</span>
                                @elseif($remaining <= 0)
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 text-base tabular-nums">{{ $faNum(number_format($invoice->paid_amount)) }} <span class="text-[11px] font-medium">{{ $currencyLabel }}</span></span>
                                    <span class="block text-[11px] font-bold text-emerald-500 mt-1">تسویه کامل</span>
                                @elseif($invoice->paid_amount > 0)
                                    <span class="font-bold text-blue-600 dark:text-blue-400 text-base tabular-nums">پرداختی: {{ $faNum(number_format($invoice->paid_amount)) }} <span class="text-[11px] font-medium">{{ $currencyLabel }}</span></span>
                                    <span class="block text-[11px] font-bold text-amber-600 dark:text-amber-400 mt-1 tabular-nums">مانده: {{ $faNum(number_format($remaining)) }} <span class="text-[11px] font-medium">{{ $currencyLabel }}</span></span>
                                @else
                                    <span class="font-black text-gray-400 dark:text-gray-500 text-base tabular-nums">۰</span>
                                    <span class="block text-[11px] font-bold text-gray-400 mt-1">پرداخت نشده</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border"
                                      style="background: {{ $statusColor }}1a; color: {{ $statusColor }}; border-color: {{ $statusColor }}33">
                                    <span class="w-2 h-2 rounded-full" style="background: {{ $statusColor }}"></span>
                                    {{ $statusName }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400 dir-ltr whitespace-nowrap tabular-nums">
                                {{ $faNum($invoice->issue_date) }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400 dir-ltr whitespace-nowrap tabular-nums">
                                {{ $faNum($invoice->due_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-40 group-hover:opacity-100 transition-opacity duration-200">
                                    @if($remaining > 0 && !$isCanceled)
                                        <a href="{{ route('services.invoices.payment', $invoice) }}"
                                           class="p-2.5 rounded-xl text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-all hover:scale-110"
                                           title="ثبت پرداخت">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('services.invoices.show', $invoice) }}"
                                       class="p-2.5 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all hover:scale-110"
                                       title="مشاهده">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @can('update', $invoice)
                                        <a href="{{ route('services.invoices.edit', $invoice) }}"
                                           class="p-2.5 rounded-xl text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all hover:scale-110"
                                           title="ویرایش">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endcan
                                    @can('delete', $invoice)
                                        <form method="POST" action="{{ route('services.invoices.destroy', $invoice) }}"
                                              onsubmit="return confirm('فاکتور حذف شود؟')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="p-2.5 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all hover:scale-110"
                                                    title="حذف">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                    <a href="{{ route('services.invoices.print', $invoice) }}"
                                       class="p-2.5 rounded-xl text-gray-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-500/10 transition-all hover:scale-110"
                                       title="دانلود PDF">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-24 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-24 h-24 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-6 shadow-inner">
                                        <svg class="w-12 h-12 text-indigo-300 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هیچ فاکتوری یافت نشد</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 text-center leading-relaxed">
                                        شما هنوز هیچ فاکتوری ثبت نکرده‌اید و یا جستجوی شما نتیجه‌ای نداشت.</p>
                                    @can('create', Invoice::class)
                                        <div class="flex items-center gap-4">
                                            <a href="{{ route('services.invoices.create') }}"
                                               class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all hover:-translate-y-1">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                ثبت اولین سفارش
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
