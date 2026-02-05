@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        {{-- Header & Stats --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-gray-500 dark:text-gray-400">کل نوبت‌ها</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($totalCount) }}</span>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-4 border border-emerald-100 dark:border-emerald-800 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-emerald-600 dark:text-emerald-400">تایید شده</span>
                    <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">
                        {{ number_format($statusCounts[\Modules\Booking\Entities\Appointment::STATUS_CONFIRMED] ?? 0) }}
                    </span>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-4 border border-amber-100 dark:border-amber-800 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-amber-600 dark:text-amber-400">در انتظار</span>
                    <span class="text-2xl font-bold text-amber-700 dark:text-amber-300 mt-1">
                        {{ number_format(($statusCounts[\Modules\Booking\Entities\Appointment::STATUS_PENDING] ?? 0) + ($statusCounts[\Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT] ?? 0)) }}
                    </span>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-4 border border-blue-100 dark:border-blue-800 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-blue-600 dark:text-blue-400">انجام شده</span>
                    <span class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-1">
                        {{ number_format($statusCounts[\Modules\Booking\Entities\Appointment::STATUS_DONE] ?? 0) }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col justify-center items-end gap-3">
                <a class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition"
                   href="{{ route('user.booking.appointments.create') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    ثبت نوبت جدید
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @includeIf('partials.jalali-date-picker')

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('user.booking.appointments.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">جستجو</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="نام، موبایل، کد ملی..."
                           class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">وضعیت</label>
                    <select name="status" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">همه وضعیت‌ها</option>
                        @foreach([
                            \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'تایید شده',
                            \Modules\Booking\Entities\Appointment::STATUS_PENDING => 'در انتظار تایید',
                            \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'در انتظار پرداخت',
                            \Modules\Booking\Entities\Appointment::STATUS_DONE => 'انجام شده',
                            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => 'لغو (مشتری)',
                            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => 'لغو (ادمین)',
                            \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => 'عدم حضور',
                            \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => 'جابجا شده',
                            \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'پیش‌نویس',
                        ] as $key => $label)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Service --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">سرویس</label>
                    <select name="service_id" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">همه سرویس‌ها</option>
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}" {{ (int)request('service_id') === $srv->id ? 'selected' : '' }}>{{ $srv->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Provider (Admin Only) --}}
                @if(!empty($providers) && count($providers) > 0)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ config('booking.labels.provider') }}</label>
                        <select name="provider_user_id" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">همه {{ config('booking.labels.providers') }}</option>
                            @foreach($providers as $prov)
                                <option value="{{ $prov->id }}" {{ (int)request('provider_user_id') === $prov->id ? 'selected' : '' }}>{{ $prov->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Date Range --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">از تاریخ</label>
                    <input type="text" name="date_from" value="{{ request('date_from') }}" placeholder="1402/01/01"
                           data-jdp
                           class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 dir-ltr text-right">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">تا تاریخ</label>
                    <input type="text" name="date_to" value="{{ request('date_to') }}" placeholder="1402/12/29"
                           data-jdp
                           class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 dir-ltr text-right">
                </div>

                {{-- Sort --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">مرتب‌سازی</label>
                    <select name="sort" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>جدیدترین زمان نوبت</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>قدیمی‌ترین زمان نوبت</option>
                        <option value="created_desc" {{ request('sort') === 'created_desc' ? 'selected' : '' }}>جدیدترین زمان ثبت</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full h-[42px] rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 font-medium text-sm transition">
                        اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">مشتری</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">سرویس / {{ config('booking.labels.provider') }}</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">تاریخ نوبت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ساعت نوبت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">مدت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">وضعیت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($appointments as $a)
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
                                \Modules\Booking\Entities\Appointment::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
                                \Modules\Booking\Entities\Appointment::STATUS_PENDING => ['label' => 'در انتظار تایید', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200'],
                                \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => ['label' => 'در انتظار پرداخت', 'class' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-200'],
                                \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => ['label' => 'تایید شده', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'],
                                \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => ['label' => 'لغو شده (ادمین)', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
                                \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => ['label' => 'لغو شده (مشتری)', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
                                \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => ['label' => 'عدم حضور', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
                                \Modules\Booking\Entities\Appointment::STATUS_DONE => ['label' => 'انجام شده', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'],
                                \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => ['label' => 'جابجا شده', 'class' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-200'],
                            ];
                            $statusMeta = $statusMap[$a->status] ?? ['label' => $a->status, 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'];
                        @endphp

                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $a->id }}</td>

                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ optional($a->client)->full_name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">{{ optional($a->client)->phone }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="text-gray-800 dark:text-gray-200 text-sm">{{ optional($a->service)->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ optional($a->provider)->name }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <span class="text-gray-900 dark:text-gray-100 font-mono text-sm">{{ $dateJalali }}</span>
                            </td>

                            <td class="px-4 py-3">
                                <span class="text-gray-500 dark:text-gray-400 font-mono text-sm">{{ $startTime }} - {{ $endTime }}</span>
                            </td>

                            <td class="px-4 py-3 font-mono text-gray-800 dark:text-gray-100 text-xs">
                                {{ $durationMinutes !== null ? ($durationMinutes . ' دقیقه') : '-' }}
                            </td>

                            <td class="px-4 py-3 text-left">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusMeta['class'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2 justify-end opacity-60 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('user.booking.appointments.show', $a) }}"
                                       class="px-3 py-1.5 text-xs rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                                        مشاهده
                                    </a>
                                    @can('booking.appointments.edit')
                                        <a href="{{ route('user.booking.appointments.edit', $a) }}"
                                           class="px-3 py-1.5 text-xs rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20 transition">
                                            ویرایش
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span>هیچ نوبتی یافت نشد.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            {{ $appointments->links() }}
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.jalaliDatepicker) {
                window.jalaliDatepicker.startWatch();
            }
        });
    </script>
@endsection
