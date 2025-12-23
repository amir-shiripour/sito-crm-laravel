@extends('layouts.user')

@php
    /** @var \Modules\Booking\Entities\Appointment $appointment */
    $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
    $displayTz = config('booking.timezones.display_default', $scheduleTz);
    $startSchedule = $appointment->start_at_utc?->copy()->timezone($scheduleTz);
    $endSchedule = $appointment->end_at_utc?->copy()->timezone($scheduleTz);
    $dateValue = old('date_local', $startSchedule ? \Morilog\Jalali\Jalalian::fromDateTime($startSchedule)->format('Y/m/d') : '');
    $startTimeValue = old('start_time_local', $startSchedule?->format('H:i'));
    $endTimeValue = old('end_time_local', $endSchedule?->format('H:i'));
    $entryTime = old('entry_time_local', $appointment->entry_at_utc?->copy()->timezone($displayTz)->format('H:i'));
    $exitTime = old('exit_time_local', $appointment->exit_at_utc?->copy()->timezone($displayTz)->format('H:i'));

    $statusOptions = [
        \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'پیش‌نویس',
        \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'در انتظار پرداخت',
        \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'تایید شده',
        \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => 'لغو شده (ادمین)',
        \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => 'لغو شده (مشتری)',
        \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => 'عدم حضور',
        \Modules\Booking\Entities\Appointment::STATUS_DONE => 'انجام شده',
        \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => 'جابجا شده',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ویرایش نوبت #{{ $appointment->id }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ویرایش جزئیات نوبت با اعتبارسنجی ظرفیت</p>
            </div>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
               href="{{ route('user.booking.appointments.show', $appointment) }}">
                بازگشت
            </a>
        </div>

        @includeIf('partials.jalali-date-picker')

        <form method="POST" action="{{ route('user.booking.appointments.update', $appointment) }}"
              class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">سرویس</label>
                    <select name="service_id"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" @selected((string)old('service_id', $appointment->service_id)===(string)$service->id)>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">ارائه‌دهنده</label>
                    <select name="provider_user_id"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" @selected((string)old('provider_user_id', $appointment->provider_user_id)===(string)$provider->id)>
                                {{ $provider->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('provider_user_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">مشتری</label>
                    <select name="client_id"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected((string)old('client_id', $appointment->client_id)===(string)$client->id)>
                                {{ $client->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت</label>
                    <select name="status"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $appointment->status)===$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">تاریخ (شمسی)</label>
                    <input type="text" name="date_local" value="{{ $dateValue }}" data-jdp
                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                    @error('date_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">ساعت شروع</label>
                    <input type="text" name="start_time_local" value="{{ $startTimeValue }}" data-jdp-only-time
                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                    @error('start_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">ساعت پایان</label>
                    <input type="text" name="end_time_local" value="{{ $endTimeValue }}" data-jdp-only-time
                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                    @error('end_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            @if($settings->allow_appointment_entry_exit_times)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">زمان ورود</label>
                        <input type="text" name="entry_time_local" value="{{ $entryTime }}" data-jdp-only-time
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                        @error('entry_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">زمان خروج</label>
                        <input type="text" name="exit_time_local" value="{{ $exitTime }}" data-jdp-only-time
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                        @error('exit_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">یادداشت</label>
                <textarea name="notes" rows="3"
                          class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">{{ old('notes', $appointment->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.jalaliDatepicker) {
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp]',
                    hasSecond: false
                });
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp-only-time]',
                    hasSecond: false
                });
            }
        });
    </script>
@endsection
