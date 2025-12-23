@extends('layouts.user')

@section('content')
    @php
        /** @var \Modules\Booking\Entities\Appointment $appointment */
        $tz = config('booking.timezones.display_default', 'Asia/Tehran');
        $startLocal = $appointment->start_at_utc?->copy()->timezone($tz);
        $endLocal = $appointment->end_at_utc?->copy()->timezone($tz);
        $entryLocal = $appointment->entry_at_utc?->copy()->timezone($tz);
        $exitLocal = $appointment->exit_at_utc?->copy()->timezone($tz);

        $dateJalali = $startLocal
            ? \Morilog\Jalali\Jalalian::fromDateTime($startLocal)->format('Y/m/d')
            : '—';
        $startTime = $startLocal ? $startLocal->format('H:i') : '—';
        $endTime = $endLocal ? $endLocal->format('H:i') : '—';

        $statusMap = [
            \Modules\Booking\Entities\Appointment::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
            \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => ['label' => 'در انتظار پرداخت', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200'],
            \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => ['label' => 'تایید شده', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'],
            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => ['label' => 'لغو شده (ادمین)', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => ['label' => 'لغو شده (مشتری)', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
            \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => ['label' => 'عدم حضور', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
            \Modules\Booking\Entities\Appointment::STATUS_DONE => ['label' => 'انجام شده', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'],
            \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => ['label' => 'جابجا شده', 'class' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-200'],
        ];
        $statusMeta = $statusMap[$appointment->status] ?? ['label' => $appointment->status, 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'];

        $entryValue = $entryLocal ? \Morilog\Jalali\Jalalian::fromDateTime($entryLocal)->format('Y/m/d H:i') : '—';
        $exitValue = $exitLocal ? \Morilog\Jalali\Jalalian::fromDateTime($exitLocal)->format('Y/m/d H:i') : '—';

        $formResponses = $appointment->appointment_form_response_json ?? [];
    @endphp

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
                    <div class="text-xs text-gray-500 dark:text-gray-400">ارائه‌دهنده</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ optional($appointment->provider)->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">مشتری</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ optional($appointment->client)->full_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">وضعیت</div>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusMeta['class'] }}">
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
                        <div class="text-xs text-gray-500 dark:text-gray-400">زمان ورود (شمسی)</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100 font-mono">{{ $entryValue }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">زمان خروج (شمسی)</div>
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

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">فرم اطلاعات نوبت</h2>
            </div>
            @if(!empty($formResponses))
                <div class="space-y-2">
                    @foreach($formResponses as $key => $value)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400 w-40">{{ $key }}</div>
                            <div class="text-sm text-gray-900 dark:text-gray-100">
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
