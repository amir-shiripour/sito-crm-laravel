@extends('layouts.user')

@section('content')
    @php
        /** @var \Modules\Booking\Entities\Appointment $appointment */
        $tz = config('booking.timezones.display_default', 'Asia/Tehran');
        $startLocal = $appointment->start_at_utc?->copy()->timezone($tz);
        $endLocal = $appointment->end_at_utc?->copy()->timezone($tz);

        $startJalali = $startLocal ? \Morilog\Jalali\Jalalian::fromDateTime($startLocal)->format('Y/m/d H:i') : '';
        $endJalali = $endLocal ? \Morilog\Jalali\Jalalian::fromDateTime($endLocal)->format('Y/m/d H:i') : '';

        $entryLocal = $appointment->entry_at_utc?->copy()->timezone($tz);
        $exitLocal = $appointment->exit_at_utc?->copy()->timezone($tz);

        $entryValue = old('entry_at_local', $entryLocal ? \Morilog\Jalali\Jalalian::fromDateTime($entryLocal)->format('Y/m/d H:i') : '');
        $exitValue = old('exit_at_local', $exitLocal ? \Morilog\Jalali\Jalalian::fromDateTime($exitLocal)->format('Y/m/d H:i') : '');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ویرایش نوبت #{{ $appointment->id }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ویرایش یادداشت و زمان ورود/خروج</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 dark:bg-gray-700/70 dark:text-gray-200 dark:hover:bg-gray-700 transition"
                   href="{{ route('user.booking.appointments.show', $appointment) }}">
                    بازگشت
                </a>
            </div>
        </div>

        @includeIf('partials.jalali-date-picker')

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
                    <div class="text-xs text-gray-500 dark:text-gray-400">بازه زمانی (شمسی)</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100 font-mono">{{ $startJalali }} تا {{ $endJalali }}</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('user.booking.appointments.update', $appointment) }}" class="space-y-6">
            @csrf

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">یادداشت</label>
                    <textarea name="notes" rows="4"
                              class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">{{ old('notes', $appointment->notes) }}</textarea>
                    @error('notes')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>

                @if($settings->allow_appointment_entry_exit_times)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">زمان ورود (شمسی)</label>
                            <input type="text" name="entry_at_local" value="{{ $entryValue }}"
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800" data-jdp data-jdp-time="true" placeholder="1403/01/01 09:00">
                            <p class="text-[11px] text-gray-400 mt-2">می‌توانید برای پاک کردن مقدار، فیلد را خالی ذخیره کنید.</p>
                            @error('entry_at_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">زمان خروج (شمسی)</label>
                            <input type="text" name="exit_at_local" value="{{ $exitValue }}"
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800" data-jdp data-jdp-time="true" placeholder="1403/01/01 10:00">
                            @error('exit_at_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50 text-amber-800 text-sm px-4 py-3">
                        امکان ثبت زمان ورود/خروج برای نوبت در تنظیمات غیرفعال است.
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.jalaliDatepicker) {
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp]',
                    hasSecond: false,
                });
            }
        });
    </script>
@endsection
