@php
    /** @var \Illuminate\Support\Collection|\Modules\Booking\Entities\Appointment[] $clientAppointments */
    /** @var \Modules\Clients\Entities\Client $client */
    $tz = config('booking.timezones.display_default', 'Asia/Tehran');
    $clientLabel = config('clients.labels.singular', 'مشتری');
    $providerLabel = config('booking.labels.provider', 'ارائه‌دهنده');

    $totalCount = $clientAppointments->count();
    $confirmedCount = $clientAppointments->whereIn('status', [\Modules\Booking\Entities\Appointment::STATUS_CONFIRMED])->count();
    $doneCount = $clientAppointments->whereIn('status', [\Modules\Booking\Entities\Appointment::STATUS_DONE])->count();
    $pendingCount = $clientAppointments->whereIn('status', [\Modules\Booking\Entities\Appointment::STATUS_PENDING, \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT])->count();
    $canceledCount = $clientAppointments->whereIn('status', [\Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN, \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW])->count();

    $statusMap = [
        \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => [
            'label' => 'تایید شده',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60',
            'dot' => 'bg-emerald-500',
            'icon' => '✓',
        ],
        \Modules\Booking\Entities\Appointment::STATUS_DONE => [
            'label' => 'انجام شده',
            'bg' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/60',
            'dot' => 'bg-blue-500',
            'icon' => '✔',
        ],
        \Modules\Booking\Entities\Appointment::STATUS_PENDING => [
            'label' => 'در انتظار تایید',
            'bg' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
            'dot' => 'bg-amber-500',
            'icon' => '⏳',
        ],
        \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => [
            'label' => 'در انتظار پرداخت',
            'bg' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800/60',
            'dot' => 'bg-orange-500',
            'icon' => '💳',
        ],
        \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => [
            'label' => 'لغو شده (ادمین)',
            'bg' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60',
            'dot' => 'bg-rose-500',
            'icon' => '✕',
        ],
        \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => [
            'label' => "لغو شده ($clientLabel)",
            'bg' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60',
            'dot' => 'bg-rose-500',
            'icon' => '✕',
        ],
        \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => [
            'label' => 'عدم حضور',
            'bg' => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
            'dot' => 'bg-gray-400',
            'icon' => '🚫',
        ],
        \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => [
            'label' => 'جابجا شده',
            'bg' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/60',
            'dot' => 'bg-purple-500',
            'icon' => '🔄',
        ],
        \Modules\Booking\Entities\Appointment::STATUS_DRAFT => [
            'label' => 'پیش‌نویس',
            'bg' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700',
            'dot' => 'bg-gray-400',
            'icon' => '📝',
        ],
    ];
@endphp

<div class="space-y-6">
    {{-- Header with Stats & Actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-gray-100 dark:border-gray-700/80">
        <div>
            <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                <span>سوابق و مدیریت نوبت‌های {{ $clientLabel }}</span>
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-9">
                نمایش تمام نوبت‌های رزرو شده، زمان‌بندی‌ها، وضعیت‌ها و دسترسی سریع به رزرو و تقویم
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            @if(isset($isBookingQueueEnabled) && $isBookingQueueEnabled)
                <button type="button"
                        @click="$dispatch('open-waitlist-modal', { clientId: {{ $client->id }} })"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 text-xs font-bold transition-all shadow-2xs">
                    <span>⏳</span>
                    <span>افزودن به صف انتظار</span>
                </button>
            @endif

            <a href="{{ route('user.booking.appointments.index', ['q' => $client->id]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 text-xs font-bold transition-all">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>لیست جامع نوبت‌ها</span>
            </a>

            <a href="{{ route('user.booking.schedule.index', ['client_id' => $client->id]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/30 transition-all transform active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>ثبت نوبت در تقویم</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards (KPIs) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        {{-- Total --}}
        <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/70">
            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 block">کل نوبت‌ها</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-black text-gray-900 dark:text-white">{{ $faNum($totalCount) }}</span>
                <span class="text-[10px] font-medium text-gray-400">مورد</span>
            </div>
        </div>

        {{-- Confirmed --}}
        <div class="p-3.5 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/40">
            <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 block">تایید شده / فعال</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-black text-emerald-700 dark:text-emerald-300">{{ $faNum($confirmedCount) }}</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
        </div>

        {{-- Done --}}
        <div class="p-3.5 rounded-2xl bg-blue-50/60 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-800/40">
            <span class="text-[11px] font-bold text-blue-700 dark:text-blue-400 block">انجام شده</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-black text-blue-700 dark:text-blue-300">{{ $faNum($doneCount) }}</span>
                <span class="text-[10px] text-blue-500 font-bold">موفق</span>
            </div>
        </div>

        {{-- Pending --}}
        <div class="p-3.5 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40">
            <span class="text-[11px] font-bold text-amber-700 dark:text-amber-400 block">در انتظار تایید/پرداخت</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-black text-amber-700 dark:text-amber-300">{{ $faNum($pendingCount) }}</span>
                <span class="text-[10px] text-amber-600">نیاز به بررسی</span>
            </div>
        </div>

        {{-- Canceled / No Show --}}
        <div class="p-3.5 rounded-2xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200/60 dark:border-rose-800/40 col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold text-rose-700 dark:text-rose-400 block">لغو شده / عدم حضور</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-xl font-black text-rose-700 dark:text-rose-300">{{ $faNum($canceledCount) }}</span>
                <span class="text-[10px] text-rose-500 font-bold">بسته شده</span>
            </div>
        </div>
    </div>

    {{-- Appointments List / Table --}}
    @if($clientAppointments->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-xs text-right">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3.5 font-bold text-gray-700 dark:text-gray-300"># شناسه</th>
                        <th class="px-4 py-3.5 font-bold text-gray-700 dark:text-gray-300">سرویس نوبت</th>
                        <th class="px-4 py-3.5 font-bold text-gray-700 dark:text-gray-300">{{ $providerLabel }}</th>
                        <th class="px-4 py-3.5 font-bold text-gray-700 dark:text-gray-300">تاریخ نوبت</th>
                        <th class="px-4 py-3.5 font-bold text-gray-700 dark:text-gray-300">ساعت و مدت</th>
                        <th class="px-4 py-3.5 font-bold text-gray-700 dark:text-gray-300">وضعیت</th>
                        <th class="px-4 py-3.5 font-bold text-gray-700 dark:text-gray-300">یادداشت / توضیحات</th>
                        <th class="px-4 py-3.5 font-bold text-gray-700 dark:text-gray-300 text-left pl-5">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @foreach($clientAppointments as $a)
                        @php
                            $startLocal = $a->start_at_utc ? $a->start_at_utc->copy()->timezone($tz) : null;
                            $endLocal = $a->end_at_utc ? $a->end_at_utc->copy()->timezone($tz) : null;

                            $dateJalali = $startLocal
                                ? $faNum(\Morilog\Jalali\Jalalian::fromDateTime($startLocal)->format('Y/m/d'))
                                : '-';
                            $dayName = $startLocal
                                ? \Morilog\Jalali\Jalalian::fromDateTime($startLocal)->format('%A')
                                : '';
                            $startTime = $startLocal ? $faNum($startLocal->format('H:i')) : '-';
                            $endTime = $endLocal ? $faNum($endLocal->format('H:i')) : '-';
                            $durationMinutes = ($startLocal && $endLocal)
                                ? $startLocal->diffInMinutes($endLocal)
                                : null;

                            $status = $statusMap[$a->status] ?? [
                                'label' => $a->status,
                                'bg' => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                'dot' => 'bg-gray-400',
                                'icon' => '•',
                            ];
                        @endphp

                        <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors duration-150">
                            {{-- ID --}}
                            <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 font-mono font-bold">
                                #{{ $faNum($a->id) }}
                            </td>

                            {{-- Service --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $status['dot'] }}"></span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100 text-xs">
                                        {{ optional($a->service)->name ?: 'سرویس عمومی' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Provider --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px] font-black">
                                        {{ mb_substr(optional($a->provider)->name ?: 'پ', 0, 1) }}
                                    </div>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">
                                        {{ optional($a->provider)->name ?: 'بدون تخصیص' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Date --}}
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ $dateJalali }}</span>
                                    @if($dayName)
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $dayName }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Time & Duration --}}
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-right">
                                        {{ $startTime }} - {{ $endTime }}
                                    </span>
                                    @if($durationMinutes)
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500">
                                            {{ $faNum($durationMinutes) }} دقیقه
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold {{ $status['bg'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                                    <span>{{ $status['label'] }}</span>
                                </span>
                            </td>

                            {{-- Notes / Cancel reason --}}
                            <td class="px-4 py-3.5 max-w-[200px] truncate text-gray-500 dark:text-gray-400">
                                @if($a->cancel_reason)
                                    <span class="text-rose-600 dark:text-rose-400 text-[11px]" title="{{ $a->cancel_reason }}">
                                        علت لغو: {{ Str::limit($a->cancel_reason, 25) }}
                                    </span>
                                @elseif($a->notes)
                                    <span title="{{ $a->notes }}" class="text-[11px]">
                                        {{ Str::limit($a->notes, 30) }}
                                    </span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>

                            {{-- Operations --}}
                            <td class="px-4 py-3.5 text-left pl-5">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- View Details --}}
                                    <a href="{{ route('user.booking.appointments.show', $a->id) }}"
                                       class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition"
                                       title="مشاهده جزئیات کامل نوبت">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('user.booking.appointments.edit', $a->id) }}"
                                       class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition"
                                       title="ویرایش نوبت">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    {{-- View in Calendar --}}
                                    @if($startLocal)
                                        <a href="{{ route('user.booking.schedule.index') }}"
                                           class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition"
                                           title="مشاهده در تقویم کاری">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center py-16 px-4 bg-gray-50/70 dark:bg-gray-900/30 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 text-center">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">
                هنوز هیچ نوبتی برای این {{ $clientLabel }} ثبت نشده است
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mb-5">
                می‌توانید به راحتی از طریق تقویم کاری یا صف انتظار، اولین نوبت را برای این مراجع ثبت نمایید.
            </p>
            <div class="flex items-center gap-3">
                <a href="{{ route('user.booking.schedule.index', ['client_id' => $client->id]) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ثبت نوبت در تقویم</span>
                </a>
                @if(isset($isBookingQueueEnabled) && $isBookingQueueEnabled)
                    <button type="button"
                            @click="$dispatch('open-waitlist-modal', { clientId: {{ $client->id }} })"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-xs font-bold transition">
                        <span>⏳</span>
                        <span>افزودن به صف انتظار</span>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
