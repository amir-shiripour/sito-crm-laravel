@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">جزئیات نوبت #{{ $appointment->id }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مشاهده اطلاعات کامل نوبت ثبت شده</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 dark:bg-gray-700/70 dark:text-gray-200 dark:hover:bg-gray-700 transition"
                   href="{{ route('user.booking.appointments.index') }}">
                    بازگشت
                </a>
                @can('booking.appointments.edit')
                    <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition"
                       href="{{ route('user.booking.appointments.edit', $appointment) }}">
                        ویرایش
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">سرویس</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ optional($appointment->service)->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ config('booking.labels.provider', 'ارائه‌دهنده') }}</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ optional($appointment->provider)->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">مشتری</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ optional($appointment->client)->full_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">وضعیت</div>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold mt-1 {{ $statusMeta['class'] }}">
                        {{ $statusMeta['label'] }}
                    </span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">تاریخ (شمسی)</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100 font-mono">{{ $dateJalali }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">بازه زمانی</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100 font-mono">{{ $startTime }} تا {{ $endTime }}</div>
                </div>

                @if($settings->allow_appointment_entry_exit_times)
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">زمان ورود</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100 font-mono">{{ $entryValue }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">زمان خروج</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100 font-mono">{{ $exitValue }}</div>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <div class="text-xs text-gray-500 dark:text-gray-400">یادداشت</div>
                <div class="mt-2 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    {{ $appointment->notes ?: 'یادداشتی ثبت نشده است.' }}
                </div>
            </div>
        </div>

        @if($payments->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">اطلاعات پرداخت</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-3 rounded-r-xl">مبلغ</th>
                            <th scope="col" class="px-6 py-3">واحد پول</th>
                            <th scope="col" class="px-6 py-3">نوع پرداخت</th>
                            <th scope="col" class="px-6 py-3">وضعیت</th>
                            <th scope="col" class="px-6 py-3">کد پیگیری</th>
                            <th scope="col" class="px-6 py-3 rounded-l-xl">تاریخ پرداخت</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($payments as $payment)
                            @php
                                $pStatusMeta = $paymentStatusMap[$payment->status] ?? ['label' => $payment->status, 'class' => 'bg-gray-100 text-gray-700'];
                                $pModeLabel = $paymentModeMap[$payment->mode] ?? $payment->mode;
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-700/60 last:border-0 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                    {{ number_format($payment->amount) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $payment->currency_unit === 'toman' ? 'تومان' : ($payment->currency_unit === 'rial' ? 'ریال' : $payment->currency_unit) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $pModeLabel }}
                                </td>
                                <td class="px-6 py-4">
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $pStatusMeta['class'] }}">
                                            {{ $pStatusMeta['label'] }}
                                        </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">
                                    {{ $payment->transaction_ref ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-xs" dir="ltr">
                                    {{ $payment->updated_at && $payment->status === \Modules\Booking\Entities\BookingPayment::STATUS_PAID ? \Morilog\Jalali\Jalalian::fromDateTime($payment->updated_at)->format('Y/m/d H:i') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">فرم اطلاعات نوبت</h2>
            </div>
            @if(!empty($formResponses))
                <div class="space-y-2">
                    @foreach($formResponses as $response)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400 w-40">{{ $response['label'] }}</div>
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                @php $value = $response['value']; @endphp
                                @if(is_array($value))
                                    {{ implode('، ', array_filter(array_map('strval', $value))) ?: '—' }}
                                @else
                                    {{ $value !== null && $value !== '' ? $value : '—' }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">فرمی برای این نوبت ثبت نشده است.</p>
            @endif
        </div>
    </div>
@endsection
