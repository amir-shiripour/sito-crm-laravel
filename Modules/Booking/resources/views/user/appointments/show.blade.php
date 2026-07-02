@extends('layouts.user')

@section('content')
    <style>
        .tooth-path {
            cursor: pointer;
            transition: fill .14s ease, stroke .14s ease, filter .14s ease;
            stroke-width: 1.5px;
            vector-effect: non-scaling-stroke;
        }
        .tooth-selected {
            fill: #3b82f6 !important;
            stroke: #2563eb !important;
            stroke-width: 2.5px !important;
            filter: drop-shadow(0 2px 6px rgba(37, 99, 235, 0.45));
        }
        .dark .tooth-selected {
            fill: #1d4ed8 !important;
            stroke: #3b82f6 !important;
        }
        .tooth-unselected {
            fill: #ffffff !important;
            stroke: #cbd5e1;
        }
        .dark .tooth-unselected {
            fill: #334155 !important;
            stroke: #475569;
        }
        .tooth-unselected:hover {
            fill: #f8fafc !important;
            stroke: #3b82f6;
        }
        .dark .tooth-unselected:hover {
            fill: #1e293b !important;
            stroke: #60a5fa;
        }
    </style>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-2 h-full bg-indigo-500 rounded-r-2xl"></div>
            <div class="pr-3">
                <h1 class="text-xl font-black text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    جزئیات نوبت #{{ $appointment->id }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">مشاهده اطلاعات کامل و سوابق نوبت ثبت شده</p>
            </div>
            <div class="flex items-center gap-3 pr-3 sm:pr-0">
                <a class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 dark:bg-gray-700/70 dark:text-gray-200 dark:hover:bg-gray-600 transition-all duration-200"
                   href="{{ route('user.booking.appointments.index') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                    </svg>
                    بازگشت
                </a>
                @can('booking.appointments.edit')
                    <a class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200"
                       href="{{ route('user.booking.appointments.edit', $appointment) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        ویرایش
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-5 py-4 shadow-sm animate-fade-in">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-emerald-500">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 lg:p-8">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">اطلاعات اصلی نوبت</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">سرویس</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ optional($appointment->service)->name ?? '—' }}</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ config('booking.labels.provider', 'ارائه‌دهنده') }}</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ optional($appointment->provider)->name ?? '—' }}</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">مشتری</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ optional($appointment->client)->full_name ?? '—' }}</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">وضعیت</div>
                        <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-bold mt-0.5 tracking-wide {{ $statusMeta['class'] }}">
                            {{ $statusMeta['label'] }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">تاریخ (شمسی)</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm tracking-wider">{{ $dateJalali }}</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">بازه زمانی</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm tracking-wider" dir="ltr">{{ $startTime }} - {{ $endTime }}</div>
                    </div>
                </div>

                @if($settings->allow_appointment_entry_exit_times)
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">زمان ورود</div>
                            <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $entryValue }}</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">زمان خروج</div>
                            <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $exitValue }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-8">
                <div class="flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">یادداشت ثبت شده</span>
                </div>
                <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line bg-gray-50 dark:bg-gray-900/50 border-r-4 border-gray-300 dark:border-gray-600 rounded-l-xl p-4 shadow-inner">
                    {{ $appointment->notes ?: 'یادداشتی برای این نوبت ثبت نشده است.' }}
                </div>
            </div>
        </div>

        @if($payments->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        اطلاعات پرداخت
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-600 bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">مبلغ</th>
                            <th scope="col" class="px-6 py-4 font-semibold">واحد پول</th>
                            <th scope="col" class="px-6 py-4 font-semibold">نوع پرداخت</th>
                            <th scope="col" class="px-6 py-4 font-semibold">وضعیت</th>
                            <th scope="col" class="px-6 py-4 font-semibold">کد پیگیری</th>
                            <th scope="col" class="px-6 py-4 font-semibold">تاریخ پرداخت</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach($payments as $payment)
                            @php
                                $pStatusMeta = $paymentStatusMap[$payment->status] ?? ['label' => $payment->status, 'class' => 'bg-gray-100 text-gray-700'];
                                $pModeLabel = $paymentModeMap[$payment->mode] ?? $payment->mode;
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-gray-100">
                                    {{ number_format($payment->amount) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-md text-xs font-medium text-gray-600 dark:text-gray-300">
                                        {{ $payment->currency_unit === 'toman' ? 'تومان' : ($payment->currency_unit === 'rial' ? 'ریال' : $payment->currency_unit) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-700 dark:text-gray-300">
                                    {{ $pModeLabel }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-bold tracking-wide {{ $pStatusMeta['class'] }}">
                                        {{ $pStatusMeta['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $payment->transaction_ref ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-xs font-medium" dir="ltr">
                                    {{ $payment->updated_at && $payment->status === \Modules\Booking\Entities\BookingPayment::STATUS_PAID ? \Morilog\Jalali\Jalalian::fromDateTime($payment->updated_at)->format('Y/m/d H:i') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 lg:p-8">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6 border-b border-gray-100 dark:border-gray-700 pb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                </svg>
                فرم اطلاعات تکمیلی نوبت
            </h2>

            @if(!empty($formResponses) || !empty($legacyResponses))
                @if(!empty($formResponses))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach($formResponses as $response)
                            @php
                                $value = $response['value'];
                                $isToothNumber = isset($response['type']) && $response['type'] === 'tooth_number';
                            @endphp

                            <div class="flex flex-col gap-2 {{ $isToothNumber ? 'col-span-1 md:col-span-2' : '' }}">
                                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $response['label'] }}</div>

                                @if($isToothNumber)
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mt-1"
                                         x-data="{
                                             selected: @js(is_array($value) ? array_map('intval', $value) : (is_string($value) && $value !== '' ? array_map('intval', explode(',', $value)) : [])),
                                             isReadOnly: true,
                                             getToothLabel(id) {
                                                 const mapping = {
                                                     1:  { num: 7, pos: 'UR' }, 2:  { num: 6, pos: 'UR' }, 3:  { num: 5, pos: 'UR' }, 4:  { num: 4, pos: 'UR' },
                                                     5:  { num: 3, pos: 'UR' }, 6:  { num: 2, pos: 'UR' }, 7:  { num: 1, pos: 'UR' },
                                                     8:  { num: 1, pos: 'UL' }, 9:  { num: 2, pos: 'UL' }, 10: { num: 3, pos: 'UL' }, 11: { num: 4, pos: 'UL' },
                                                     12: { num: 5, pos: 'UL' }, 13: { num: 6, pos: 'UL' }, 14: { num: 7, pos: 'UL' },
                                                     15: { num: 7, pos: 'LR' }, 16: { num: 6, pos: 'LR' }, 17: { num: 5, pos: 'LR' }, 18: { num: 4, pos: 'LR' },
                                                     19: { num: 3, pos: 'LR' }, 20: { num: 2, pos: 'LR' }, 21: { num: 1, pos: 'LR' },
                                                     22: { num: 1, pos: 'LL' }, 23: { num: 2, pos: 'LL' }, 24: { num: 3, pos: 'LL' }, 25: { num: 4, pos: 'LL' },
                                                     26: { num: 5, pos: 'LL' }, 27: { num: 6, pos: 'LL' }, 28: { num: 7, pos: 'LL' }
                                                 };
                                                 return mapping[id] ?? { num: id, pos: 'UR' };
                                             },
                                             getQuadrantClasses(id) {
                                                 const tooth = this.getToothLabel(id);
                                                 switch(tooth.pos) {
                                                     case 'UR': return '!border-r-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                                                     case 'UL': return '!border-l-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                                                     case 'LR': return '!border-r-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                                                     case 'LL': return '!border-l-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                                                     default:   return '';
                                                 }
                                             },
                                             getQuadrantTeeth(teethArray, pos) {
                                                 return (teethArray || []).map(Number).filter(t => this.getToothLabel(t).pos === pos).sort((a,b) => a - b);
                                             },
                                             toggle(id) {},
                                             is(id) {
                                                 return this.selected.includes(id) ? 'tooth-path tooth-selected' : 'tooth-path tooth-unselected';
                                             }
                                         }">
                                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-2.5 h-6 rounded-full bg-rose-500 shrink-0"></span>
                                                <h2 class="font-bold text-gray-800 dark:text-gray-100 text-sm">نقشه دندانی (مشاهده نوبت)</h2>
                                            </div>
                                        </div>
                                        <div class="px-4 pt-5 pb-2 relative">
                                            <div class="absolute top-6 left-6 z-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm
                                                        px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold block mb-0.5">تعداد انتخاب</span>
                                                <span class="text-xl font-black text-indigo-600 dark:text-indigo-400"
                                                      x-text="selected.length">0</span>
                                            </div>
                                            <div class="flex justify-center select-none dental-chart-wrapper max-w-lg mx-auto mb-4">
                                                <x-booking::dental-chart/>
                                            </div>
                                        </div>
                                        <div class="px-5 py-3.5 flex items-center gap-3 min-h-14 border-t border-gray-150 dark:border-gray-700/50 bg-gray-50/60 dark:bg-gray-900/20">
                                            <template x-if="selected.length > 0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-bold shrink-0">دندان‌های انتخابی:</span>
                                                    <div class="inline-grid grid-cols-2 select-none">
                                                        <!-- Row 1: UR | UL -->
                                                        <!-- UR -->
                                                        <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-700 pb-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[36px]">
                                                            <template x-for="t in getQuadrantTeeth(selected, 'UR')" :key="t">
                                                                <div class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black border-0 border-solid rounded-none pointer-events-none"
                                                                     :class="[getQuadrantClasses(t)]"
                                                                     x-text="getToothLabel(t).num">
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <!-- UL -->
                                                        <div class="border-b-2 border-slate-300 dark:border-slate-700 pb-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[36px]">
                                                            <template x-for="t in getQuadrantTeeth(selected, 'UL')" :key="t">
                                                                <div class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black border-0 border-solid rounded-none pointer-events-none"
                                                                     :class="[getQuadrantClasses(t)]"
                                                                     x-text="getToothLabel(t).num">
                                                                </div>
                                                            </template>
                                                        </div>

                                                        <!-- Row 2: LR | LL -->
                                                        <!-- LR -->
                                                        <div class="border-l-2 border-slate-300 dark:border-slate-700 pt-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[36px]">
                                                            <template x-for="t in getQuadrantTeeth(selected, 'LR')" :key="t">
                                                                <div class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black border-0 border-solid rounded-none pointer-events-none"
                                                                     :class="[getQuadrantClasses(t)]"
                                                                     x-text="getToothLabel(t).num">
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <!-- LL -->
                                                        <div class="pt-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[36px]">
                                                            <template x-for="t in getQuadrantTeeth(selected, 'LL')" :key="t">
                                                                <div class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black border-0 border-solid rounded-none pointer-events-none"
                                                                     :class="[getQuadrantClasses(t)]"
                                                                     x-text="getToothLabel(t).num">
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="selected.length === 0">
                                                <span class="text-xs text-gray-400 dark:text-gray-500 self-center flex items-center gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    هیچ دندانی انتخاب نشده است
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-base text-gray-900 dark:text-gray-100 font-semibold p-3.5 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-700/60 inline-block w-full">
                                        @if(is_array($value))
                                            {{ implode('، ', array_filter(array_map('strval', $value))) ?: '—' }}
                                        @else
                                            {{ $value !== null && $value !== '' ? $value : '—' }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(!empty($legacyResponses))
                    <div class="mt-8 border-t border-gray-150 dark:border-gray-700/50 pt-6">
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-amber-500 rounded-full"></span>
                            اطلاعات قدیمی (مربوط به نسخه قبل)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($legacyResponses as $response)
                                @php
                                    $val = $response['value'];
                                @endphp
                                <div class="flex flex-col gap-2">
                                    <div class="text-xs text-gray-400 dark:text-gray-500 font-medium">{{ $response['label'] }}</div>
                                    <div class="text-sm text-gray-900 dark:text-gray-100 font-semibold p-3.5 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-700/60 inline-block w-full">
                                        @if(is_array($val))
                                            {{ implode('، ', array_filter(array_map('strval', $val))) ?: '—' }}
                                        @else
                                            {{ $val !== null && $val !== '' ? $val : '—' }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mb-3 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <p class="text-sm font-medium">فرمی برای این نوبت ثبت نشده است.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
