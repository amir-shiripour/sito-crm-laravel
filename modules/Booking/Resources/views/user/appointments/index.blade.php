@extends('layouts.user')

@section('content')
    <div class="space-y-5">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">نوبت‌ها</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">لیست نوبت‌های اخیر</p>
            </div>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition"
               href="{{ route('user.booking.appointments.create') }}">
                ثبت نوبت جدید
            </a>
        </div>

        @if(session('success'))
            <div
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">سرویس</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ارائه‌دهنده</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">مشتری</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">تاریخ (شمسی)</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">شروع</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">پایان</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">مدت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">وضعیت</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @foreach($appointments as $a)
                        @php
                            /** @var \Modules\Booking\Entities\Appointment $a */
                            $tz = config('booking.timezones.display_default', 'Asia/Tehran');

                            $startLocal = $a->start_at_utc ? $a->start_at_utc->copy()->timezone($tz) : null;
                            $endLocal = $a->end_at_utc ? $a->end_at_utc->copy()->timezone($tz) : null;

                            $dateJalali = $startLocal
                            ? \Morilog\Jalali\Jalalian::fromDateTime($startLocal)->format('Y/m/d')
                            : '';
                            $startTime = $startLocal ? $startLocal->format('H:i') : '';
                            $endTime = $endLocal ? $endLocal->format('H:i') : '';
                            $durationMinutes = ($startLocal && $endLocal)
                            ? $startLocal->diffInMinutes($endLocal)
                            : null;

                            $statusMap = [
                            'CONFIRMED' => ['label' => 'تایید شده', 'class' => 'bg-emerald-50 text-emerald-700
                            dark:bg-emerald-900/30 dark:text-emerald-200'],
                            'PENDING' => ['label' => 'در انتظار', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30
                            dark:text-amber-200'],
                            'CANCELED' => ['label' => 'لغو شده', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30
                            dark:text-rose-200'],
                            'NOSHOW' => ['label' => 'عدم حضور', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700
                            dark:text-gray-200'],
                            ];
                            $statusMeta = $statusMap[$a->status] ?? ['label' => $a->status, 'class' => 'bg-gray-100
                            text-gray-700 dark:bg-gray-700 dark:text-gray-200'];
                        @endphp

                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $a->id }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ optional($a->service)->name }}</td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ optional($a->provider)->name }}</td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ optional($a->client)->full_name }}
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">{{ $dateJalali }}</td>
                            <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">{{ $startTime }}</td>
                            <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">{{ $endTime }}</td>
                            <td class="px-4 py-3 font-mono text-gray-800 dark:text-gray-100">
                                {{ $durationMinutes !== null ? ($durationMinutes . ' دقیقه') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-left">
                            <span
                                class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusMeta['class'] }}">
                                {{ $statusMeta['label'] }}
                            </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            {{ $appointments->links() }}
        </div>
    </div>
@endsection
