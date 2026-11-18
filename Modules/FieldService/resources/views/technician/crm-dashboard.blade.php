@extends('layouts.user')

@section('title', 'پنل کاری تکنسین')

@section('content')
<div class="space-y-6 font-sans">
    {{-- Welcome Banner (Technician Experience) --}}
    <div class="bg-gradient-to-l from-indigo-900 via-indigo-950 to-slate-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-indigo-500/20">
        {{-- Ambient decorative glow --}}
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    @if($technician)
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            {{ $technician->status->label() }}
                        </span>
                        <span class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg {{ $technician->is_online ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800/80 text-slate-300 border border-slate-700' }}">
                            <span class="w-2 h-2 rounded-full {{ $technician->is_online ? 'bg-emerald-400 animate-ping' : 'bg-slate-400' }}"></span>
                            {{ $technician->is_online ? 'آنلاین و آماده دریافت سرویس' : 'آفلاین' }}
                        </span>
                    @endif
                </div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-black tracking-tight leading-tight text-white">
                    خوش آمدید، {{ $technician?->full_name ?: auth()->user()->name }}
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    خلاصه وضعیت ماموریت‌ها، درآمد خالص و عملکرد شما در سامانه خدمات فنی
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('fieldservice.technician') }}"
                   class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2 no-underline">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    ورود به کنسول نقشه و رادار
                </a>
                <a href="{{ url('/user/profile') }}"
                   class="px-4 py-2.5 bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-200 hover:text-white font-bold text-xs sm:text-sm rounded-xl border border-indigo-500/30 transition-all flex items-center gap-2 no-underline">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    پروفایل و احراز هویت
                </a>
            </div>
        </div>
    </div>

    {{-- Personal KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 mb-2">
                <span class="text-xs font-semibold">ماموریت‌های فعال من</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">⚡</span>
            </div>
            <div class="text-2xl font-black text-gray-900 dark:text-white">{{ number_format($techStats['active_orders']) }}</div>
            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-2">در حال اجرا یا پیگیری</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 mb-2">
                <span class="text-xs font-semibold">سرویس‌های تکمیل‌شده</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">✓</span>
            </div>
            <div class="text-2xl font-black text-gray-900 dark:text-white">{{ number_format($techStats['completed_orders']) }}</div>
            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-2">مجموع خدمات موفق شما</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 mb-2">
                <span class="text-xs font-semibold">مجموع درآمد خالص من</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">💰</span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-300">{{ number_format($techStats['net_earnings'] / 10) }}</span>
                <span class="text-xs text-slate-600 dark:text-slate-300">تومان</span>
            </div>
            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-2">در انتظار تسویه: {{ number_format($techStats['pending_settlement'] / 10) }} تومان</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 mb-2">
                <span class="text-xs font-semibold">امتیاز رضایت مشتریان</span>
                <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-300 flex items-center justify-center">★</span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-amber-500 dark:text-amber-300">{{ number_format($techStats['rating'], 1) }}</span>
                <span class="text-xs text-slate-600 dark:text-slate-300">از ۵</span>
            </div>
            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-2">نرخ پذیرش: {{ $techStats['acceptance_rate'] }}%</p>
        </div>
    </div>

    {{-- My Recent Missions --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white">آخرین ماموریت‌های من</h2>
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">لیست سرویس‌های اختصاص‌یافته به شما</p>
            </div>
            <a href="{{ route('user.fieldservice.orders.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 transition-colors no-underline">
                <span>مشاهده همه ماموریت‌ها</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        </div>

        {{-- Desktop / Tablet Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm text-right">
                <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs text-slate-600 dark:text-slate-300 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 font-bold">شماره سفارش</th>
                        <th class="px-6 py-3 font-bold">دسته خدمت</th>
                        <th class="px-6 py-3 font-bold">شهر</th>
                        <th class="px-6 py-3 font-bold">وضعیت</th>
                        <th class="px-6 py-3 font-bold">سهم خالص شما</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($myOrders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-6 py-3.5 font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</td>
                            <td class="px-6 py-3.5 text-slate-600 dark:text-slate-300">{{ $order->category?->name ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-slate-600 dark:text-slate-300">{{ $order->city ?? '—' }}</td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $order->status->badgeClass() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $order->invoice ? number_format($order->invoice->technician_net_amount / 10) . ' تومان' : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-xs text-slate-600 dark:text-slate-300">
                                هنوز ماموریتی برای شما ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Responsive Card View --}}
        <div class="block md:hidden divide-y divide-gray-100 dark:divide-gray-700/50 p-4 space-y-3">
            @forelse($myOrders as $order)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200/60 dark:border-gray-700/60 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold text-xs text-gray-900 dark:text-white font-sans">
                            {{ $order->order_number }}
                        </span>
                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold {{ $order->status->badgeClass() }}">
                            {{ $order->status->label() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 dark:text-slate-300">
                        <div>
                            <span class="text-[10px] text-gray-400 block mb-0.5">دسته خدمت:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $order->category?->name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 block mb-0.5">شهر:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $order->city ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between text-xs">
                        <span class="text-slate-600 dark:text-slate-300">سهم خالص شما:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $order->invoice ? number_format($order->invoice->technician_net_amount / 10) . ' تومان' : '—' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-slate-600 dark:text-slate-300">
                    هنوز ماموریتی برای شما ثبت نشده است.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
