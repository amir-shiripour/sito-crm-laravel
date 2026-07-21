@extends('layouts.user')
@section('title', 'جزئیات سرویس: ' . ($service->name ?? ''))

@php
    $currency      = $currency ?? 'toman';
    $currencyLabel = $currency === 'rial' ? 'ریال' : 'تومان';
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $periodLabels = [
        'monthly'     => 'ماهانه',
        'quarterly'   => 'فصلی',
        'semi_annual' => 'شش‌ماهه',
        'annual'      => 'سالانه',
    ];

    $fieldTypeLabels = [
        'text'        => 'متن کوتاه',
        'textarea'    => 'متن بلند',
        'number'      => 'عدد',
        'date'        => 'تاریخ',
        'datetime'    => 'تاریخ و زمان',
        'select'      => 'لیست کشویی',
        'multiselect' => 'چندانتخابی',
        'checkbox'    => 'چک‌باکس',
        'radio'       => 'تک‌انتخابی',
        'email'       => 'ایمیل',
        'url'         => 'لینک',
        'phone'       => 'تلفن',
        'file'        => 'فایل',
    ];

    // حذف h-full برای جلوگیری از کشیدگی عمودی نامناسب و کمی گردتر کردن گوشه ها
    $cardClass  = "bg-white dark:bg-gray-800/80 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden backdrop-blur-xl";
@endphp

@section('content')
    {{-- تغییر به max-w-7xl برای جلوگیری از پخش شدن بیش از حد محتوا در مانیتورهای واید --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                {{ $service->name }}
            </h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('services.services.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    بازگشت
                </a>
                @can('services.edit')
                    <a href="{{ route('services.services.edit', $service) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 text-sm font-bold transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        ویرایش
                    </a>
                @endcan
            </div>
        </div>

        {{-- Main Content Grid --}}
        {{-- استفاده از نسبت ۲ به ۱ (۳ ستون) برای کنترل بهتر فضای سفید --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

            {{-- Main Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Description & Details --}}
                <div class="{{ $cardClass }}">
                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                            <div class="space-y-4 w-full">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($service->isActive())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> فعال
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-700/50 dark:text-gray-400 dark:border-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> غیرفعال
                                        </span>
                                    @endif

                                    @if($service->category)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold"
                                              style="background: {{ $service->category->color }}1A; color: {{ $service->category->color }}; border: 1px solid {{ $service->category->color }}33;">
                                            {{ $service->category->name }}
                                        </span>
                                    @endif

                                    @if($service->has_unit_pricing)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20">
                                            فروش واحدی ({{ $service->unit_name }})
                                        </span>
                                    @endif
                                </div>

                                <div class="bg-gray-50/50 dark:bg-gray-900/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700/50">
                                    @if($service->description)
                                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                                            {{ $service->description }}
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-400 dark:text-gray-500 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            توضیحاتی برای این سرویس ثبت نشده است.
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if($service->code)
                                <div class="shrink-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700/50 px-3 py-2 rounded-lg border border-gray-100 dark:border-gray-600">
                                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400 mr-2">کد:</span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $service->code }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Custom Fields --}}
                <div class="{{ $cardClass }}">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between bg-gray-50/30 dark:bg-gray-800/30">
                        <h2 class="text-base font-black text-gray-800 dark:text-gray-100 flex items-center gap-2.5">
                            <div class="p-1.5 bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400 rounded-lg">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </div>
                            فیلدهای سفارشی
                        </h2>
                        @can('services.edit')
                            <a href="{{ route('services.services.edit', ['service' => $service, 'tab' => 'custom_fields']) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-lg transition-all dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400 dark:hover:bg-blue-500/20">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                مدیریت
                            </a>
                        @endcan
                    </div>
                    <div class="p-5 sm:p-6">
                        @if($service->customFields->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                @foreach($service->customFields as $field)
                                    <div class="group p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-all hover:border-blue-200 dark:hover:border-blue-500/30">
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-1 truncate" title="{{ $field->label }}">{{ $field->label }}</p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $fieldTypeLabels[$field->type] ?? $field->type }}
                                            </span>
                                            @if($field->has_pricing)
                                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 rounded">
                                                    + {{ $field->pricing_type === 'percentage' ? $faNum($field->pricing_amount).'%' : $faNum(number_format($field->pricing_amount)).' '.$currencyLabel }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-8 text-center bg-gray-50/50 dark:bg-gray-900/20 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">هیچ فیلد سفارشی تعریف نشده است.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div class="space-y-6">
                {{-- Pricing Card --}}
                <div class="{{ $cardClass }}">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-800/30">
                        <h2 class="text-base font-black text-gray-800 dark:text-gray-100 flex items-center gap-2.5">
                            <div class="p-1.5 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            هزینه‌ها
                        </h2>
                    </div>
                    <div class="p-5 space-y-5">
                        <div>
                            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">قیمت پایه</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-black text-gray-900 dark:text-white tabular-nums">{{ $faNum(number_format($service->base_price ?? 0)) }}</span>
                                <span class="text-sm font-medium text-gray-400">{{ $currencyLabel }}</span>
                            </div>
                        </div>

                        @if($service->has_unit_pricing)
                            <div class="pt-4 border-t border-dashed border-gray-200 dark:border-gray-700/60">
                                <div class="flex items-center justify-between bg-indigo-50/50 dark:bg-indigo-500/5 p-3 rounded-xl border border-indigo-100 dark:border-indigo-500/10">
                                    <span class="text-xs font-semibold text-indigo-800 dark:text-indigo-300">هر واحد ({{ $service->unit_name }})</span>
                                    <div class="text-left">
                                        <span class="block text-sm font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">{{ $faNum(number_format($service->unit_price)) }}</span>
                                        <span class="block text-[10px] text-indigo-400 dark:text-indigo-500">{{ $currencyLabel }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($service->billing_type === 'recurring')
                            <div class="pt-4 border-t border-dashed border-gray-200 dark:border-gray-700/60 space-y-3">
                                <h3 class="text-xs font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider">تعرفه‌های تمدید</h3>
                                <div class="space-y-2">
                                    @foreach($periodLabels as $period => $label)
                                        @php
                                            $price = $service->getRenewalPriceFor($period);
                                            $isOverridden = !is_null($service->renewal_prices[$period] ?? null);
                                        @endphp
                                        <div class="flex items-center justify-between text-sm bg-gray-50 dark:bg-gray-800/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                                            <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $label }}</span>
                                            <div class="flex items-center gap-2">
                                                @if(!$isOverridden)
                                                    <span class="text-[10px] font-bold bg-gray-200 text-gray-500 dark:bg-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded">پیش‌فرض</span>
                                                @endif
                                                <span class="font-bold text-gray-800 dark:text-gray-100 tabular-nums">{{ $faNum(number_format($price)) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Billing Info Card --}}
                <div class="{{ $cardClass }}">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-800/30">
                        <h2 class="text-base font-black text-gray-800 dark:text-gray-100 flex items-center gap-2.5">
                            <div class="p-1.5 bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400 rounded-lg">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            صورتحساب
                        </h2>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">نوع چرخه</span>
                            @if($service->billing_type === 'one_time')
                                <span class="text-xs font-bold text-sky-700 dark:text-sky-400 bg-sky-50 dark:bg-sky-500/10 border border-sky-100 dark:border-sky-500/20 px-2.5 py-1 rounded-lg">یک‌باره</span>
                            @elseif($service->billing_type === 'recurring')
                                <span class="text-xs font-bold text-violet-700 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10 border border-violet-100 dark:border-violet-500/20 px-2.5 py-1 rounded-lg">اشتراک دوره‌ای</span>
                            @else
                                <span class="text-xs font-bold text-gray-500">نامشخص</span>
                            @endif
                        </div>

                        @if($service->billing_type === 'recurring')
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">دوره پیش‌فرض</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $periodLabels[$service->recurring_period] ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm pt-1">
                                <span class="text-gray-500 dark:text-gray-400">یادآور تمدید</span>
                                <span class="inline-flex items-center gap-1 font-bold text-gray-800 dark:text-gray-200">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $faNum($service->renewal_reminder_days ?? 0) }} روز قبل
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
