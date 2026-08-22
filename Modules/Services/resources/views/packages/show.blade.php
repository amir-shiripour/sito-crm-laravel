@extends('layouts.user')

@section('title', 'جزئیات پکیج «' . $package->name . '»')

@php
    $currencyLabel = ($currency ?? 'toman') === 'rial' ? 'ریال' : 'تومان';
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl";
    $periodLabels = ['monthly' => 'ماهانه', 'quarterly' => 'فصلی', 'semi_annual' => 'شش ماهه', 'annual' => 'سالانه'];
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $package->name }}</h1>
                        @if($package->status === 'active')
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-xs font-bold border border-emerald-200 dark:border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> فعال
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 text-xs font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> غیرفعال
                            </span>
                        @endif
                    </div>
                    @if($package->code)
                        <p class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 mt-1">کد
                            پکیج: {{ $package->code }}</p>
                    @endif
                </div>
            </div>
            @if($package->description)
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 max-w-3xl leading-relaxed">{{ $package->description }}</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <a href="{{ route('services.packages.edit', $package) }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black shadow-lg shadow-indigo-500/30 transition-all duration-300 active:scale-95">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                ویرایش پکیج
            </a>
            <a href="{{ route('services.packages.index') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست
            </a>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="{{ $cardClass }} p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400">تعداد سرویس‌ها</span>
                <span class="block text-xl font-black text-gray-900 dark:text-white tabular-nums mt-0.5">{{ number_format($package->items->count()) }} <span class="text-xs font-bold text-gray-400">سرویس</span></span>
            </div>
        </div>

        <div class="{{ $cardClass }} p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400">مبلغ پایه سرویس‌ها</span>
                <span class="block text-xl font-black text-gray-900 dark:text-white tabular-nums mt-0.5">{{ number_format($package->total_amount) }} <span class="text-xs font-bold text-gray-400">{{ $currencyLabel }}</span></span>
            </div>
        </div>

        <div class="{{ $cardClass }} p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 12h10m-7 5h7"/>
                </svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400">تخفیف پکیج</span>
                <span class="block text-xl font-black text-rose-600 dark:text-rose-400 tabular-nums mt-0.5">
                    @if($package->discount_value > 0)
                        @if($package->discount_type === 'percent')
                            {{ number_format($package->discount_value) }}٪
                        @else
                            {{ number_format($package->discount_value) }} <span
                                class="text-xs font-bold text-rose-400">{{ $currencyLabel }}</span>
                        @endif
                    @else
                        بدون تخفیف
                    @endif
                </span>
            </div>
        </div>

        <div class="{{ $cardClass }} p-6 flex items-center gap-4 border-indigo-200 dark:border-indigo-500/30">
            <div
                class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-indigo-500/30">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400">قیمت نهایی پکیج</span>
                <span class="block text-xl font-black text-indigo-600 dark:text-indigo-400 tabular-nums mt-0.5">{{ number_format($package->final_price) }} <span
                        class="text-xs font-bold text-indigo-400">{{ $currencyLabel }}</span></span>
            </div>
        </div>
    </div>

    {{-- Items List Table --}}
    <div class="{{ $cardClass }} overflow-hidden">
        <div
            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
            <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                <div class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                لیست سرویس‌ها و خدمات پکیج
            </h2>
            <span class="text-xs font-bold text-gray-400">{{ number_format($package->items->count()) }} آیتم</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-start border-collapse min-w-[800px]">
                <thead
                    class="bg-gray-50/80 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 text-start font-bold">نام سرویس / خدمات</th>
                    <th class="px-6 py-4 text-start font-bold">شرح</th>
                    <th class="px-6 py-4 text-center font-bold">تعداد / واحد</th>
                    <th class="px-6 py-4 text-center font-bold">دوره billing</th>
                    <th class="px-6 py-4 text-center font-bold">مبلغ واحد</th>
                    <th class="px-6 py-4 text-center font-bold">مبلغ کل</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                @foreach($package->items as $item)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4 align-top font-bold text-gray-900 dark:text-white">
                            <div>
                                <span
                                    class="block text-sm font-black">{{ $item->custom_service_name ?: ($item->service ? $item->service->name : 'سرویس سفارشی') }}</span>
                                @if($item->service && $item->service->code)
                                    <span
                                        class="block text-xs font-mono text-gray-400 mt-0.5">کد: {{ $item->service->code }}</span>
                                @endif

                                @if($item->custom_fields && is_array($item->custom_fields) && count(array_filter($item->custom_fields)) > 0)
                                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                                        @foreach($item->custom_fields as $cfId => $cfValue)
                                            @if(!empty($cfValue))
                                                @php
                                                    $cfDef = $item->service ? $item->service->customFields->firstWhere('id', $cfId) : null;
                                                    $cfLabel = $cfDef ? $cfDef->label : 'فیلد سفارشی';
                                                    $valStr = is_array($cfValue) ? implode('، ', $cfValue) : $cfValue;

                                                    $rawCfPrice = $item->custom_fields_prices[$cfId] ?? null;
                                                    $cfPrice = 0;
                                                    if ($cfDef && $cfDef->has_pricing) {
                                                        if ($cfDef->type === 'multiselect' && is_array($cfValue)) {
                                                            $totalPrice = 0;
                                                            foreach ($cfValue as $opt) {
                                                                $optPrice = is_array($rawCfPrice) ? ($rawCfPrice[$opt] ?? null) : null;
                                                                if ($optPrice === null) {
                                                                    $optPrice = $cfDef->getOptionPrice($opt, $item->unit_price);
                                                                }
                                                                $totalPrice += floatval($optPrice);
                                                            }
                                                            $cfPrice = $totalPrice;
                                                        } else {
                                                            if (!empty($rawCfPrice) && !is_array($rawCfPrice)) {
                                                                $pStr = str_replace(['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'], (string)$rawCfPrice);
                                                                $cfPrice = floatval(preg_replace('/[^\d.]/', '', $pStr));
                                                            }
                                                            if ($cfPrice <= 0) {
                                                                if (in_array($cfDef->type, ['select', 'radio'])) {
                                                                    $cfPrice = $cfDef->getOptionPrice($cfValue, $item->unit_price);
                                                                } else {
                                                                    $cfAmount = floatval($cfDef->pricing_amount ?? 0);
                                                                    $cfPrice = ($cfDef->pricing_type === 'percentage') ? ($item->unit_price * ($cfAmount / 100)) : $cfAmount;
                                                                }
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-50/70 dark:bg-indigo-500/10 text-xs font-bold text-indigo-900 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-500/20">
                                                        <span class="text-indigo-500 dark:text-indigo-400">{{ $cfLabel }}:</span>
                                                        <span>{{ $valStr }}</span>
                                                        @if($cfPrice > 0)
                                                        <span
                                                            class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 bg-emerald-100/80 dark:bg-emerald-500/20 px-1.5 py-0.5 rounded me-0.5">
                                                                +{{ number_format($cfPrice) }} {{ $currencyLabel }}
                                                            </span>
                                                    @endif
                                                    </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top text-xs text-gray-500 dark:text-gray-400">
                            {{ $item->description ?: '—' }}
                        </td>
                        <td class="px-6 py-4 text-center align-top font-black tabular-nums text-gray-800 dark:text-gray-200">
                            {{ number_format($item->quantity) }} <span
                                class="text-xs font-normal text-gray-400 ms-0.5">{{ $item->unit ?: 'عدد' }}</span>
                        </td>
                        <td class="px-6 py-4 text-center align-top">
                            @if($item->billing_period && isset($periodLabels[$item->billing_period]))
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-xs font-bold border border-indigo-200/60 dark:border-indigo-500/20">
                                        {{ $periodLabels[$item->billing_period] }}
                                    </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center align-top tabular-nums font-bold text-gray-700 dark:text-gray-300">
                            {{ number_format($item->unit_price) }} <span
                                class="text-[10px] font-normal text-gray-400">{{ $currencyLabel }}</span>
                        </td>
                        <td class="px-6 py-4 text-center align-top tabular-nums font-black text-indigo-600 dark:text-indigo-400 text-base">
                            {{ number_format($item->total_price) }} <span
                                class="text-xs font-normal text-gray-400">{{ $currencyLabel }}</span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals Summary Box --}}
        <div class="bg-gray-50/50 dark:bg-gray-900/20 p-6 border-t border-gray-100 dark:border-gray-700/50">
            <div class="w-full md:w-[28rem] ms-auto">
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400 font-medium">
                        <span>جمع کل مبالغ سرویس‌ها</span>
                        <span class="tabular-nums font-bold text-gray-900 dark:text-white">
                            {{ number_format($package->total_amount) }}
                            <span class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span>
                        </span>
                    </div>

                    <div class="flex justify-between items-center text-red-500 dark:text-red-400 font-medium">
                        <span>تخفیف کل پکیج</span>
                        <span class="tabular-nums font-bold">
                            − {{ number_format($package->total_amount - $package->final_price) }}
                            <span class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span>
                        </span>
                    </div>

                    <div class="border-t-2 border-dashed border-gray-200 dark:border-gray-700 my-4"></div>

                    <div class="flex justify-between items-start">
                        <span class="text-base font-black text-gray-900 dark:text-white block">مبلغ نهایی پکیج</span>
                        <div class="text-end">
                            <span
                                class="tabular-nums text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($package->final_price) }}</span>
                            <span class="text-xs text-gray-400 block">{{ $currencyLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
