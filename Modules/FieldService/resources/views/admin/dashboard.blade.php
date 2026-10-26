@extends('layouts.user')

@section('title', 'داشبورد فرماندهی خدمات فنی')

@section('content')
<div class="space-y-6 font-sans">
    {{-- Header Banner --}}
    <div class="bg-gradient-to-l from-indigo-600 via-indigo-700 to-indigo-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white/20 backdrop-blur-md text-white border border-white/20">
                        پایش هوشمند استان مازندران
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-emerald-300 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        رادار فعال
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight">
                    مرکز عملیات و فرماندهی {{ $platformName ?? 'خدمات فنی' }}
                </h1>
                <p class="text-indigo-100 text-xs sm:text-sm mt-1.5 max-w-2xl leading-relaxed">
                    مدیریت یکپارچه ناوگان تکنسین‌ها، گردش مالی امانی (Escrow)، تسویه‌های شبانه پایا و درخواست‌های اعزام اضطراری
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('user.fieldservice.fleet') }}" class="px-4 py-2.5 bg-white text-indigo-700 hover:bg-indigo-50 font-bold text-xs sm:text-sm rounded-xl shadow-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    مانیتورینگ زنده ناوگان (GIS)
                </a>
                <a href="{{ route('user.fieldservice.orders.index') }}" class="px-4 py-2.5 bg-indigo-500/30 hover:bg-indigo-500/40 text-white font-bold text-xs sm:text-sm rounded-xl border border-white/20 transition-all flex items-center gap-2">
                    سفارش‌ها و ماموریت‌ها
                </a>
                <a href="{{ route('user.fieldservice.categories.index') }}" class="px-4 py-2.5 bg-indigo-500/30 hover:bg-indigo-500/40 text-white font-bold text-xs sm:text-sm rounded-xl border border-white/20 transition-all flex items-center gap-2">
                    مدیریت خدمات
                </a>
            </div>
        </div>

        {{-- Background glow --}}
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Online Techs --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between text-gray-400 dark:text-gray-400 mb-2">
                <span class="text-xs font-semibold">تکنسین‌های آنلاین</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    🟢
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 dark:text-white">
                    {{ number_format($stats['online_technicians']) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">از {{ number_format($stats['total_technicians']) }} نفر</span>
            </div>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-2 font-medium">آماده اعزام در بابل و شهرهای استان</p>
        </div>

        {{-- Active Orders --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between text-gray-400 dark:text-gray-400 mb-2">
                <span class="text-xs font-semibold">ماموریت‌های فعال</span>
                <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    ⚡
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 dark:text-white">
                    {{ number_format($stats['active_orders']) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">امروز: {{ number_format($stats['today_orders']) }}</span>
            </div>
            <p class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-2 font-medium">در مراحل رادار، اعزام و تعمیر</p>
        </div>

        {{-- Escrow Pool --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between text-gray-400 dark:text-gray-400 mb-2">
                <span class="text-xs font-semibold">موجودی حساب امانی (Escrow)</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    🔒
                </span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400">
                    {{ number_format($stats['escrow_pool'] / 10) }}
                </span>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">تومان</span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2 font-medium">وجوه قفل‌شده تا رضایت نهایی مشتریان</p>
        </div>

        {{-- Platform Commission --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between text-gray-400 dark:text-gray-400 mb-2">
                <span class="text-xs font-semibold">درآمد ناخالص کمیسیون (۱۵٪)</span>
                <span class="w-8 h-8 rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    💰
                </span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-black text-purple-600 dark:text-purple-400">
                    {{ number_format($stats['platform_revenue'] / 10) }}
                </span>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">تومان</span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2 font-medium">گردش کل GMV: {{ number_format($stats['total_gmv'] / 10) }} تومان</p>
        </div>
    </div>

    {{-- Main Sections: Recent Orders & Quick Navigation --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Orders Table --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-black text-gray-900 dark:text-white">آخرین ماموریت‌های در جریان</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">وضعیت زنده پذیرش و اعزام تکنسین‌ها به محل کارفرما</p>
                </div>
                <a href="{{ route('user.fieldservice.orders.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-bold">
                    مشاهده همه سفارش‌ها ←
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/60 text-gray-400 font-bold">
                            <th class="py-3 px-2">شماره سفارش</th>
                            <th class="py-3 px-2">مشتری (کلاینت CRM)</th>
                            <th class="py-3 px-2">دستگاه</th>
                            <th class="py-3 px-2">تکنسین</th>
                            <th class="py-3 px-2">وضعیت</th>
                            <th class="py-3 px-2">شهر</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                        @forelse($recentOrders as $ord)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition-colors">
                            <td class="py-3.5 px-2 font-bold text-gray-900 dark:text-gray-100">
                                {{ $ord->order_number }}
                                @if($ord->is_emergency)
                                <span class="mr-1 px-1.5 py-0.5 text-[10px] bg-rose-500/15 text-rose-600 dark:text-rose-400 rounded font-black">فوری</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-2 text-gray-700 dark:text-gray-300">
                                {{ $ord->customer_name }}
                            </td>
                            <td class="py-3.5 px-2 text-gray-600 dark:text-gray-400">
                                {{ $ord->appliance_type?->label() ?? 'سرویس' }}
                            </td>
                            <td class="py-3.5 px-2 font-medium">
                                @if($ord->technician)
                                <span class="text-indigo-600 dark:text-indigo-400">{{ $ord->technician->full_name }}</span>
                                @else
                                <span class="text-amber-600 dark:text-amber-400">در حال رادار</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-2">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold {{ $ord->status->badgeClass() }}">
                                    {{ $ord->status->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-2 text-gray-500 dark:text-gray-400">
                                {{ $ord->city }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">
                                تاکنون سفارشی ثبت نشده است.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Active Technicians Sidebar & Quick Portals --}}
        <div class="space-y-6">
            {{-- Quick Portal Links --}}
            <div class="bg-gradient-to-br from-indigo-50/80 to-purple-50/50 dark:from-gray-800 dark:to-gray-800/80 p-5 rounded-2xl border border-indigo-100 dark:border-gray-700 shadow-sm">
                <h3 class="text-sm font-black text-gray-900 dark:text-white mb-3">پرتال‌ها و وب‌اپ‌های ناوگان {{ $platformName ?? '' }}</h3>
                <div class="space-y-2.5">
                    <a href="{{ route('fieldservice.customer') }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-gray-700/80 border border-gray-200/70 dark:border-gray-600 hover:border-emerald-500 transition-all group">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">👤</span>
                            <div>
                                <div class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 transition">وب‌اپ آن‌دیمند مشتریان</div>
                                <div class="text-[10px] text-gray-400">ثبت خدمت، رادار و پرداخت امن</div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 group-hover:translate-x-0.5 transition">↗</span>
                    </a>

                    <a href="{{ route('fieldservice.technician') }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-gray-700/80 border border-gray-200/70 dark:border-gray-600 hover:border-amber-500 transition-all group">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">🔧</span>
                            <div>
                                <div class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-amber-600 transition">وب‌اپ اختصاصی تکنسین‌ها</div>
                                <div class="text-[10px] text-gray-400">دریافت کار، فاکتور و نصاب کمکی</div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 group-hover:translate-x-0.5 transition">↗</span>
                    </a>

                    <a href="{{ route('fieldservice.b2b') }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-gray-700/80 border border-gray-200/70 dark:border-gray-600 hover:border-sky-500 transition-all group">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-sm">🏢</span>
                            <div>
                                <div class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-sky-600 transition">پرتال سازمانی مدیران ساختمان</div>
                                <div class="text-[10px] text-gray-400">شناسنامه تاسیسات و فوریت SLA</div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 group-hover:translate-x-0.5 transition">↗</span>
                    </a>
                </div>
            </div>

            {{-- Online Fleet Spotlight --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-black text-gray-900 dark:text-white">متخصصین آنلاین در استان</h3>
                    <a href="{{ route('user.fieldservice.technicians.index') }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">لیست ناوگان</a>
                </div>
                <div class="space-y-2">
                    @forelse($activeTechnicians as $tech)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50 dark:bg-gray-750 text-xs">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full {{ $tech->is_busy ? 'bg-amber-500' : 'bg-emerald-500 animate-pulse' }}"></div>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $tech->full_name }}</span>
                            <span class="text-[10px] text-gray-400">({{ $tech->city }})</span>
                        </div>
                        <span class="px-1.5 py-0.5 text-[10px] rounded {{ $tech->tier->badgeClass() }}">
                            {{ $tech->tier->label() }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 text-center py-2">هیچ تکنسینی آنلاین نیست.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
