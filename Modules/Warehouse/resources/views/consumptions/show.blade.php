@extends('layouts.user')

@section('title', 'سند خروج و مصرف ' . $consumption->issue_number)

@section('content')
@php
    $fmt = fn($val) => rtrim(rtrim(number_format((float)$val, 2), '0'), '.') ?: '0';
    $issueJalali = $consumption->issue_date
        ? (function_exists('jdate') ? jdate($consumption->issue_date)->format('Y/m/d') : $consumption->issue_date->format('Y-m-d'))
        : '—';
    $createdJalali = $consumption->created_at
        ? (function_exists('jdate') ? jdate($consumption->created_at)->format('Y/m/d H:i') : $consumption->created_at->format('Y-m-d H:i'))
        : '—';
    $cancelledJalali = $consumption->cancelled_at
        ? (function_exists('jdate') ? jdate($consumption->cancelled_at)->format('Y/m/d H:i') : $consumption->cancelled_at->format('Y-m-d H:i'))
        : null;
    $cancelledByName = \Modules\Warehouse\Support\WarehouseUserVisibility::displayUserName($consumption->cancelledByUser, null, 'سیستم');
@endphp

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Top Breadcrumb & Action Bar --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-gray-200 dark:border-gray-800">
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 flex-wrap">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors no-underline">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.consumptions.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors no-underline">خروج و مصرف کالا</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white dir-ltr inline-block">{{ $consumption->issue_number }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    سند خروج و مصرف کالا
                </h1>

                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 dir-ltr">
                    {{ $consumption->issue_number }}
                </span>

                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-700/70 text-slate-700 dark:text-slate-200">
                    {{ $consumption->issue_type_label }}
                </span>

                @if($consumption->status === 'cancelled')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30">
                        ابطال‌شده و بازگشته به پارت‌ها
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        ثبت‌شده و کسرشده
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <a href="{{ route('admin.warehouse.consumptions.show-pdf', $consumption) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm no-underline">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                چاپ PDF رسمی سند
            </a>

            <a href="{{ route('admin.warehouse.consumptions.create', $consumption->source_warehouse_id ? ['warehouse_id' => $consumption->source_warehouse_id] : []) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition-all shadow-xs no-underline">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                ثبت خروج جدید
            </a>

            @if($consumption->status === 'registered')
                <form action="{{ route('admin.warehouse.consumptions.destroy', $consumption) }}"
                      method="POST"
                      onsubmit="return confirm('آیا از ابطال این سند و بازگرداندن دقیق مقادیر کسرشده به پارت‌های مبدأ اطمینان دارید؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/15 hover:bg-rose-100 dark:hover:bg-rose-500/25 text-rose-700 dark:text-rose-300 font-bold text-xs transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        ابطال سند و برگشت موجودی
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.warehouse.consumptions.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-colors no-underline">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                بازگشت به فهرست
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Cancellation Audit Notice --}}
    @if($consumption->status === 'cancelled')
        <div class="rounded-2xl border border-rose-200 dark:border-rose-500/30 bg-rose-50/70 dark:bg-rose-500/10 p-4 flex flex-wrap items-center justify-between gap-2 text-xs text-rose-800 dark:text-rose-200 font-bold">
            <span>این سند خروج ابطال شده و تمامی مقادیر مصرف‌شده به پارت‌ها و انبار مبدأ بازگردانده شده‌اند.</span>
            <span class="text-[11px] font-normal">
                ابطال‌کننده: <strong>{{ $cancelledByName }}</strong>
                @if($cancelledJalali)
                    · زمان ابطال: <span class="dir-ltr inline-block">{{ $cancelledJalali }}</span>
                @endif
            </span>
        </div>
    @endif

    {{-- 5 Key Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">تاریخ مصرف و زمان ثبت</span>
            <div class="mt-2">
                <div class="text-lg font-bold text-gray-900 dark:text-white dir-ltr text-right">
                    {{ $issueJalali }}
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60 dir-ltr text-right">
                    ثبت در سرور: {{ $createdJalali }}
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">انبار / انبارک مبدأ</span>
            <div class="mt-2">
                <div class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white">
                    @if($consumption->sourceWarehouse)
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $consumption->sourceWarehouse->effective_color }}"></span>
                        <span class="truncate">{{ $consumption->sourceWarehouse->name }}</span>
                    @else
                        <span>—</span>
                    @endif
                </div>
                @if($consumption->sourceWarehouse)
                    <div class="text-[11px] mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60">
                        <a href="{{ route('admin.warehouse.stock-levels.index', ['warehouse_id' => $consumption->source_warehouse_id]) }}"
                           class="font-bold text-indigo-600 dark:text-indigo-300 hover:text-indigo-700 no-underline">
                            مشاهده موجودی این انبار ←
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">تعداد اقلام و واحدها</span>
            <div class="mt-2">
                <div class="text-xl font-bold text-indigo-600 dark:text-indigo-300">
                    {{ $fmt($consumption->total_quantity) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">واحد</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60">
                    در قالب <strong class="text-gray-900 dark:text-white">{{ $consumption->items_count }}</strong> ردیف کالا
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">بهای تمام‌شده کل سند</span>
            <div class="mt-2">
                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                    {{ number_format((float)$consumption->total_cost_value) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60">
                    برآورد نرخ تمام‌شده پارت‌های مبدأ
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">ثبت‌کننده و تحویل‌گیرنده</span>
            <div class="mt-2">
                <div class="text-sm font-bold text-gray-900 dark:text-white truncate">
                    {{ $consumption->creator_name }}
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60 truncate">
                    تحویل‌گیرنده: <strong class="text-gray-900 dark:text-white">{{ $consumption->effective_consumer_name ?: 'ثبت‌نشده' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Patient Case File & Notes Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Patient / Recipient Identity Box --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 dark:border-gray-700/60">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                    مشخصات پرونده بیمار / مصرف‌کننده
                </h2>
                @if($consumption->client_id && \Illuminate\Support\Facades\Route::has('user.clients.show'))
                    <a href="{{ route('user.clients.show', $consumption->client_id) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 text-[11px] font-bold transition-colors no-underline">
                        پروفایل بیمار در CRM
                    </a>
                @endif
            </div>

            @if($consumption->effective_patient_name || $consumption->effective_case_number)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <span class="block text-slate-600 dark:text-slate-300 text-[11px] mb-0.5">نام بیمار</span>
                        <strong class="text-gray-900 dark:text-white">{{ $consumption->effective_patient_name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span class="block text-slate-600 dark:text-slate-300 text-[11px] mb-0.5">شماره پرونده</span>
                        <strong class="text-indigo-600 dark:text-indigo-300">{{ $consumption->effective_case_number ?? '—' }}</strong>
                    </div>
                    <div>
                        <span class="block text-slate-600 dark:text-slate-300 text-[11px] mb-0.5">شماره تماس</span>
                        <strong class="text-gray-900 dark:text-white dir-ltr inline-block">{{ $consumption->client?->phone ?: '—' }}</strong>
                    </div>
                    <div>
                        <span class="block text-slate-600 dark:text-slate-300 text-[11px] mb-0.5">کد ملی</span>
                        <strong class="text-gray-900 dark:text-white dir-ltr inline-block">{{ $consumption->client?->national_code ?: '—' }}</strong>
                    </div>
                </div>
            @else
                <div class="text-xs text-slate-600 dark:text-slate-300 py-1">
                    این سند بدون پرونده بیمار و در قالب <strong class="text-gray-900 dark:text-white">{{ $consumption->issue_type_label }}</strong> ثبت شده است.
                </div>
            @endif
        </div>

        {{-- Document Notes --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 shadow-xs space-y-2.5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white pb-2.5 border-b border-gray-100 dark:border-gray-700/60">
                توضیحات و شرح عملیات
            </h2>
            <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed">
                {{ $consumption->notes ?: 'توضیحات اضافه‌ای برای این سند ثبت نشده است.' }}
            </p>
        </div>
    </div>

    {{-- Consumed Items & Origin Batch Deductions --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-between gap-2 bg-gray-50/70 dark:bg-gray-900/40">
            <div>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                    ریز اقلام خروجی و پارت‌های کسرشده
                </h2>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">
                    تفکیک دقیق پارت‌های مبدأ و هزینه تمام‌شده هر ردیف از کالاهای انبار
                </p>
            </div>
            <span class="px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 text-xs font-bold">
                مجموع: {{ $fmt($consumption->total_quantity) }} واحد · {{ number_format((float)$consumption->total_cost_value) }} ریال
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">کد و نام کالا</th>
                        <th class="px-4 py-3">انبار / انبارک مبدأ</th>
                        <th class="px-4 py-3 text-center">تعداد کسرشده</th>
                        <th class="px-4 py-3">جزئیات پارت‌های مبدأ و قیمت خرید</th>
                        <th class="px-4 py-3 text-center">میانگین فی واحد</th>
                        <th class="px-4 py-3 text-center">بهای تمام‌شده (ریال)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs">
                    @foreach($consumption->items as $idx => $line)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/40 transition-colors">
                            <td class="px-4 py-3.5 align-top font-bold text-slate-600 dark:text-slate-300">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5 align-top">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-bold text-[11px] dir-ltr border border-indigo-200/60 dark:border-indigo-500/30">
                                        {{ $line->item?->code }}
                                    </span>
                                    <a href="{{ route('admin.warehouse.items.show', $line->item_id) }}"
                                       class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors no-underline">
                                        {{ $line->item?->name }}
                                    </a>
                                </div>
                                @if($line->notes)
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1">{{ $line->notes }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 align-top">
                                @if($line->warehouse)
                                    <a href="{{ route('admin.warehouse.stock-levels.index', ['warehouse_id' => $line->warehouse_id, 'search' => $line->item?->code]) }}"
                                       class="inline-flex items-center gap-1.5 font-bold text-gray-800 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-300 no-underline">
                                        <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $line->warehouse->effective_color }}"></span>
                                        {{ $line->warehouse->name }}
                                    </a>
                                @else
                                    <span>—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center align-top font-bold text-indigo-600 dark:text-indigo-300">
                                {{ $fmt($line->quantity) }} {{ $line->item?->unit }}
                            </td>
                            <td class="px-4 py-3.5 align-top">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($line->batchDeductions as $bd)
                                        @php
                                            $batchObj = $bd->batch;
                                            $bCode = $batchObj?->batch_code ?? ('#' . $bd->batch_id);
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 text-[11px] text-slate-700 dark:text-slate-200">
                                            @if($batchObj?->purchase_receipt_id)
                                                <a href="{{ route('admin.warehouse.purchases.show', $batchObj->purchase_receipt_id) }}"
                                                   class="font-bold text-indigo-600 dark:text-indigo-300 hover:text-indigo-700 dir-ltr no-underline">
                                                    {{ $bCode }}
                                                </a>
                                            @else
                                                <strong class="dir-ltr">{{ $bCode }}</strong>
                                            @endif
                                            <span class="text-slate-400">·</span>
                                            <strong>{{ $fmt($bd->quantity_deducted) }}</strong>
                                            × {{ number_format((float)$bd->unit_purchase_price) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center align-top font-bold text-gray-800 dark:text-gray-200">
                                {{ number_format((float)$line->unit_cost_avg) }}
                            </td>
                            <td class="px-4 py-3.5 text-center align-top font-bold text-emerald-600 dark:text-emerald-400">
                                {{ number_format((float)$line->total_cost) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-gray-900/70 border-t border-gray-200 dark:border-gray-700 font-bold text-xs">
                        <td colspan="3" class="px-4 py-3 text-slate-700 dark:text-slate-200">جمع کل سند خروج و مصرف</td>
                        <td class="px-4 py-3 text-center text-indigo-600 dark:text-indigo-300">{{ $fmt($consumption->total_quantity) }}</td>
                        <td colspan="2"></td>
                        <td class="px-4 py-3 text-center text-emerald-600 dark:text-emerald-400">{{ number_format((float)$consumption->total_cost_value) }} ریال</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
