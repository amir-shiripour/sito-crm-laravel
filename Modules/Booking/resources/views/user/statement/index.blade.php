@extends('layouts.user')

@section('content')
    <div class="space-y-5" x-data="statementFilter()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">صورت وضعیت</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">گزارش نوبت‌ها به تفکیک پزشک و سایر نقش‌ها</p>
            </div>

            <div class="flex gap-2">
                @if($appointments !== null && !$appointments->isEmpty())
                    @can('booking.statement.create')
                        <button type="button" @click="openSaveModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-700 dark:text-indigo-100 text-sm font-medium hover:bg-indigo-200 dark:hover:bg-indigo-600 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            ثبت صورت وضعیت
                        </button>
                    @endcan

                    <button type="button" @click="openPrintModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        چاپ PDF
                    </button>
                @endif
            </div>
        </div>

        @includeIf('partials.jalali-date-picker')

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('user.booking.statement.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

                {{-- Provider Search with Alpine.js --}}
                <div class="relative" @click.outside="closeResults('provider')">
                    <label for="provider_search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">پزشک (ارائه‌دهنده)</label>

                    <div class="relative">
                        <input type="text"
                               id="provider_search"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
                               placeholder="جستجو نام یا موبایل..."
                               x-model="searches['provider']"
                               @input.debounce.300ms="fetchProviders()"
                               @focus="showResults['provider'] = true"
                               autocomplete="off">

                        <input type="hidden" name="provider_id" :value="selectedIds['provider']">

                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <button type="button"
                                x-show="selectedIds['provider']"
                                @click="clearSelection('provider')"
                                class="absolute inset-y-0 left-8 pl-2 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Dropdown Results --}}
                    <div x-show="showResults['provider'] && (users['provider'].length > 0 || loading['provider'])"
                         x-transition.opacity
                         class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-200 dark:border-gray-700">

                        <template x-if="loading['provider']">
                            <div class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-center">
                                در حال جستجو...
                            </div>
                        </template>

                        <template x-for="provider in users['provider']" :key="provider.id">
                            <div @click="selectUser('provider', provider)"
                                 class="cursor-pointer select-none relative py-2 pl-3 pr-4 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-900 dark:text-gray-100 transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium block truncate" x-text="provider.name"></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="provider.mobile"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاریخ شروع</label>
                    <input type="text" name="start_date" id="start_date" data-jdp class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ $startDateLocal }}" autocomplete="off">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاریخ پایان</label>
                    <input type="text" name="end_date" id="end_date" data-jdp class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ $endDateLocal }}" autocomplete="off">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                        نمایش گزارش
                    </button>
                </div>
            </form>
        </div>

        {{-- Saved Statements List --}}
        @if(isset($statements) && $statements->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mb-4">
                <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">صورت وضعیت‌های ثبت شده</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap text-sm text-right">
                        <thead class="bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">شناسه</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">پزشک</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">بازه زمانی</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ساعت نوبت‌ها</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ثبت کننده</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">تاریخ ثبت</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($statements as $statement)
                                <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                                    <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">{{ $statement->id }}</td>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $statement->provider->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">
                                        @if($statement->start_date == $statement->end_date)
                                            {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($statement->start_date))->format('Y/m/d') }}
                                        @else
                                            {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($statement->start_date))->format('Y/m/d') }}
                                            تا
                                            {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($statement->end_date))->format('Y/m/d') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">
                                        @if($statement->live_first_time && $statement->live_last_time)
                                            {{ $statement->live_first_time }} - {{ $statement->live_last_time }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                                                'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200',
                                                'completed' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200',
                                            ];
                                            $statusLabels = \Modules\Booking\Entities\BookingStatement::getStatuses();
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusColors[$statement->status] ?? '' }}">
                                            {{ $statusLabels[$statement->status] ?? $statement->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $statement->user->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">{{ \Morilog\Jalali\Jalalian::fromCarbon($statement->created_at)->format('Y/m/d H:i') }}</td>
                                    <td class="px-4 py-3 text-left">
                                        <div class="flex items-center gap-2 justify-end">
                                            {{-- View Details Button --}}
                                            @php
                                                $viewParams = [
                                                    'provider_id' => $statement->provider_id,
                                                    'start_date' => \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($statement->start_date))->format('Y/m/d'),
                                                    'end_date' => \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($statement->end_date))->format('Y/m/d'),
                                                ];
                                                if($statement->roles_data) {
                                                    foreach($statement->roles_data as $rId => $uId) {
                                                        $viewParams['role_' . $rId] = $uId;
                                                    }
                                                }
                                            @endphp
                                            <a href="{{ route('user.booking.statement.index', $viewParams) }}" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 transition" title="نمایش جزئیات">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>

                                            {{-- Print Button --}}
                                            @if($statement->status !== 'draft')
                                                <a href="{{ route('user.booking.statement.print', ['statement_id' => $statement->id]) }}" target="_blank" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="چاپ PDF">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                    </svg>
                                                </a>
                                            @endif

                                            {{-- Status Update Button --}}
                                            @can('booking.statement.edit')
                                                <button type="button"
                                                        @click="openStatusModal('{{ $statement->id }}', '{{ $statement->status }}', '{{ route('user.booking.statement.update-status', $statement->id) }}')"
                                                        class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 transition"
                                                        title="تغییر وضعیت">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                </button>
                                            @endcan

                                            {{-- Delete Button --}}
                                            @can('booking.statement.delete')
                                                <form action="{{ route('user.booking.statement.destroy', $statement->id) }}" method="POST" onsubmit="return confirm('آیا از حذف این صورت وضعیت اطمینان دارید؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-900/30 transition" title="حذف">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $statements->links() }}
                </div>
            </div>
        @endif

        @if($appointments !== null)
            @if($appointments->isEmpty())
                <div class="flex items-center gap-3 rounded-2xl border border-blue-200 dark:border-blue-700/70 bg-blue-50 dark:bg-blue-900/40 text-blue-800 dark:text-blue-100 px-4 py-3 shadow-sm">
                    <span class="text-xl">ℹ️</span>
                    <span class="text-sm">هیچ نوبتی در این بازه زمانی یافت نشد.</span>
                </div>
            @else
                @if($firstAppointmentTime && $lastAppointmentTime)
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800/50 rounded-2xl p-4 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-800/50 flex items-center justify-center text-indigo-600 dark:text-indigo-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-indigo-900 dark:text-indigo-100">بازه زمانی نوبت‌ها</div>
                                <div class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">
                                    از ساعت <span class="font-bold font-mono">{{ $firstAppointmentTime->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') }}</span>
                                    تا ساعت <span class="font-bold font-mono">{{ $lastAppointmentTime->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @foreach($appointments as $categoryName => $categoryAppointments)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mb-4">
                        <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $categoryName }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full whitespace-nowrap text-sm text-right">
                                <thead class="bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ساعت شروع</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">نام بیمار</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">شماره پرونده</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">نوع درمان</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">شرح درمان</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">وضعیت</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">یادداشت</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                    @foreach($categoryAppointments as $appointment)
                                        @php
                                            $statusMap = [
                                                'CONFIRMED' => ['label' => 'تایید شده', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'],
                                                'PENDING_PAYMENT' => ['label' => 'در انتظار پرداخت', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200'],
                                                'CANCELED_BY_CLIENT' => ['label' => 'لغو توسط بیمار', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
                                                'CANCELED_BY_ADMIN' => ['label' => 'لغو توسط ادمین', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
                                                'NO_SHOW' => ['label' => 'عدم حضور', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
                                                'DONE' => ['label' => 'انجام شده', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'],
                                            ];
                                            $statusMeta = $statusMap[$appointment->status] ?? ['label' => $appointment->status, 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'];
                                        @endphp
                                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                                            <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">
                                                {{ $appointment->start_at_utc ? $appointment->start_at_utc->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                                @if($appointment->client)
                                                    <a href="{{ route('clients.show', $appointment->client->id) }}" target="_blank" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                                        {{ $appointment->client->full_name }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">{{ $appointment->client?->case_number ?? '-' }}</td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                                @if($appointment->unit_count)
                                                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">({{ $appointment->unit_count }} واحد)</span>
                                                @endif
                                                {{ $appointment->service?->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                                @if(!empty($appointment->processed_form_response))
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap gap-3 items-center">
                                                        @foreach($appointment->processed_form_response as $item)
                                                            @if(!empty($item['value']))
                                                                <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md">
                                                                    @if(!empty($item['icon']))
                                                                        <span class="text-gray-500 dark:text-gray-400" style="width: 24px; height: 24px;" title="{{ $item['label'] }}">{!! $item['icon'] !!}</span>
                                                                    @else
                                                                        <span class="font-medium">{{ $item['label'] }}:</span>
                                                                    @endif
                                                                    <span>{{ is_array($item['value']) ? implode(' / ', $item['value']) : str_replace(',', ' / ', $item['value']) }}</span>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-left">
                                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusMeta['class'] }}">
                                                    {{ $statusMeta['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200 max-w-[200px] truncate" title="{{ $appointment->notes }}">
                                                {{ $appointment->notes ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left">
                                                <div class="flex items-center gap-2 justify-end">
                                                    <a href="{{ route('user.booking.appointments.edit', $appointment->id) }}" class="px-3 py-1.5 text-xs rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-500/20 dark:text-indigo-200 dark:hover:bg-indigo-500/30 transition" title="ویرایش">
                                                        ویرایش
                                                    </a>

                                                    <form action="{{ route('user.booking.appointments.destroy', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این نوبت اطمینان دارید؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 dark:bg-rose-500/20 dark:text-rose-200 dark:hover:bg-rose-500/30 transition" title="حذف">
                                                            حذف
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif
        @endif

        {{-- Print Modal --}}
        <div x-show="isPrintModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isPrintModalOpen = false"></div>

            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-right shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-right w-full">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">دریافت خروجی PDF</h3>
                                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                    <p class="mb-4">
                                        لطفاً برای نقش‌های زیر کاربر مورد نظر را انتخاب کنید تا در خروجی PDF نمایش داده شود.
                                    </p>

                                    <form id="printForm" action="{{ route('user.booking.statement.print') }}" method="GET" target="_blank">
                                        <input type="hidden" name="provider_id" :value="selectedIds['provider']">
                                        <input type="hidden" name="start_date" value="{{ $startDateLocal }}">
                                        <input type="hidden" name="end_date" value="{{ $endDateLocal }}">

                                        @foreach($statementRoles as $role)
                                            <div class="mb-4 relative" @click.outside="closeResults('{{ $role->id }}')">
                                                <label for="modal_role_{{ $role->id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $role->display_name ?? $role->name }}</label>

                                                <div class="relative">
                                                    <input type="text"
                                                           id="modal_role_{{ $role->id }}"
                                                           class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
                                                           placeholder="جستجو..."
                                                           x-model="searches['{{ $role->id }}']"
                                                           @input.debounce.300ms="fetchUsers('{{ $role->id }}')"
                                                           @focus="showResults['{{ $role->id }}'] = true"
                                                           autocomplete="off">

                                                    <input type="hidden" name="role_{{ $role->id }}" :value="selectedIds['{{ $role->id }}']">

                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </div>

                                                    <button type="button"
                                                            x-show="selectedIds['{{ $role->id }}']"
                                                            @click="clearSelection('{{ $role->id }}')"
                                                            class="absolute inset-y-0 left-8 pl-2 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                {{-- Dropdown Results --}}
                                                <div x-show="showResults['{{ $role->id }}'] && (users['{{ $role->id }}'].length > 0 || loading['{{ $role->id }}'])"
                                                     x-transition.opacity
                                                     class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-40 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-200 dark:border-gray-700">

                                                    <template x-if="loading['{{ $role->id }}']">
                                                        <div class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-center">
                                                            در حال جستجو...
                                                        </div>
                                                    </template>

                                                    <template x-for="user in users['{{ $role->id }}']" :key="user.id">
                                                        <div @click="selectUser('{{ $role->id }}', user)"
                                                             class="cursor-pointer select-none relative py-2 pl-3 pr-4 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-900 dark:text-gray-100 transition-colors">
                                                            <div class="flex items-center justify-between">
                                                                <span class="font-medium block truncate" x-text="user.name"></span>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="user.mobile"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        @endforeach
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitPrint()" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">دریافت PDF</button>
                        <button type="button" @click="isPrintModalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">انصراف</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Modal --}}
        <div x-show="isSaveModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isSaveModalOpen = false"></div>

            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-right shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-right w-full">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">ثبت صورت وضعیت</h3>
                                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                    <p class="mb-4">
                                        لطفاً اطلاعات زیر را تکمیل کنید.
                                    </p>

                                    <form id="saveForm" action="{{ route('user.booking.statement.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="provider_id" :value="selectedIds['provider']">
                                        <input type="hidden" name="start_date" value="{{ $startDateLocal }}">
                                        <input type="hidden" name="end_date" value="{{ $endDateLocal }}">

                                        <div class="mb-4">
                                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">وضعیت</label>
                                            <select name="status" id="status" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                @foreach(\Modules\Booking\Entities\BookingStatement::getStatuses() as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">یادداشت</label>
                                            <textarea name="notes" id="notes" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                        </div>

                                        <hr class="my-4 border-gray-200 dark:border-gray-700">
                                        <p class="mb-2 font-medium text-gray-700 dark:text-gray-300">نقش‌های مجاز در صورت وضعیت:</p>

                                        @foreach($statementRoles as $role)
                                            <div class="mb-4 relative" @click.outside="closeResults('save_{{ $role->id }}')">
                                                <label for="save_role_{{ $role->id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $role->display_name ?? $role->name }}</label>

                                                <div class="relative">
                                                    <input type="text"
                                                           id="save_role_{{ $role->id }}"
                                                           class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
                                                           placeholder="جستجو..."
                                                           x-model="searches['save_{{ $role->id }}']"
                                                           @input.debounce.300ms="fetchUsers('{{ $role->id }}', 'save_')"
                                                           @focus="showResults['save_{{ $role->id }}'] = true"
                                                           autocomplete="off">

                                                    <input type="hidden" name="role_{{ $role->id }}" :value="selectedIds['save_{{ $role->id }}']">

                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </div>

                                                    <button type="button"
                                                            x-show="selectedIds['save_{{ $role->id }}']"
                                                            @click="clearSelection('save_{{ $role->id }}')"
                                                            class="absolute inset-y-0 left-8 pl-2 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                {{-- Dropdown Results --}}
                                                <div x-show="showResults['save_{{ $role->id }}'] && (users['save_{{ $role->id }}'].length > 0 || loading['save_{{ $role->id }}'])"
                                                     x-transition.opacity
                                                     class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-40 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-200 dark:border-gray-700">

                                                    <template x-if="loading['save_{{ $role->id }}']">
                                                        <div class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-center">
                                                            در حال جستجو...
                                                        </div>
                                                    </template>

                                                    <template x-for="user in users['save_{{ $role->id }}']" :key="user.id">
                                                        <div @click="selectUser('save_{{ $role->id }}', user)"
                                                             class="cursor-pointer select-none relative py-2 pl-3 pr-4 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-900 dark:text-gray-100 transition-colors">
                                                            <div class="flex items-center justify-between">
                                                                <span class="font-medium block truncate" x-text="user.name"></span>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="user.mobile"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        @endforeach
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitSave()" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">ثبت</button>
                        <button type="button" @click="isSaveModalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">انصراف</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Modal --}}
        <div x-show="isStatusModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isStatusModalOpen = false"></div>

            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-right shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-right w-full">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">تغییر وضعیت</h3>
                                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                    <form id="statusForm" :action="editingStatementAction" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-4">
                                            <label for="edit_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">وضعیت جدید</label>
                                            <select name="status" id="edit_status" x-model="editingStatementStatus" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                @foreach(\Modules\Booking\Entities\BookingStatement::getStatuses() as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitStatus()" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">ذخیره</button>
                        <button type="button" @click="isStatusModalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">انصراف</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.jalaliDatepicker) {
                window.jalaliDatepicker.startWatch();
            }
        });

        function statementFilter() {
            return {
                searches: {},
                selectedIds: {},
                users: {},
                showResults: {},
                loading: {},
                isPrintModalOpen: false,
                isSaveModalOpen: false,
                isStatusModalOpen: false,
                editingStatementId: null,
                editingStatementStatus: '',
                editingStatementAction: '',

                init() {
                    // Initialize provider search
                    this.searches['provider'] = '';
                    this.selectedIds['provider'] = '';
                    this.users['provider'] = [];
                    this.showResults['provider'] = false;
                    this.loading['provider'] = false;

                    // Pre-fill provider if selected
                    @if($selectedProviderId)
                        @php
                            $selectedProvider = \App\Models\User::find($selectedProviderId);
                        @endphp
                        @if($selectedProvider)
                            this.searches['provider'] = '{{ $selectedProvider->name }}';
                            this.selectedIds['provider'] = '{{ $selectedProvider->id }}';
                        @endif
                    @endif

                    // Initialize data for each role (both for print and save modals)
                    @foreach($statementRoles as $role)
                        // Print Modal
                        this.searches['{{ $role->id }}'] = '';
                        this.selectedIds['{{ $role->id }}'] = '';
                        this.users['{{ $role->id }}'] = [];
                        this.showResults['{{ $role->id }}'] = false;
                        this.loading['{{ $role->id }}'] = false;

                        // Save Modal
                        this.searches['save_{{ $role->id }}'] = '';
                        this.selectedIds['save_{{ $role->id }}'] = '';
                        this.users['save_{{ $role->id }}'] = [];
                        this.showResults['save_{{ $role->id }}'] = false;
                        this.loading['save_{{ $role->id }}'] = false;

                        // Pre-fill if selected (for Print Modal mainly, but we can copy to Save Modal too)
                        @if(isset($selectedUsers[$role->id]))
                            this.searches['{{ $role->id }}'] = '{{ $selectedUsers[$role->id]->name }}';
                            this.selectedIds['{{ $role->id }}'] = '{{ $selectedUsers[$role->id]->id }}';

                            this.searches['save_{{ $role->id }}'] = '{{ $selectedUsers[$role->id]->name }}';
                            this.selectedIds['save_{{ $role->id }}'] = '{{ $selectedUsers[$role->id]->id }}';
                        @endif
                    @endforeach
                },

                async fetchProviders() {
                    if (!this.searches['provider']) {
                        this.users['provider'] = [];
                        return;
                    }

                    this.loading['provider'] = true;
                    try {
                        const response = await fetch(`{{ route('user.booking.statement.search-providers') }}?q=${this.searches['provider']}`);
                        const data = await response.json();
                        this.users['provider'] = data.data;
                        this.showResults['provider'] = true;
                    } catch (error) {
                        console.error('Error fetching providers:', error);
                    } finally {
                        this.loading['provider'] = false;
                    }
                },

                async fetchUsers(roleId, prefix = '') {
                    const key = prefix + roleId;
                    if (!this.searches[key]) {
                        this.users[key] = [];
                        return;
                    }

                    this.loading[key] = true;
                    try {
                        const response = await fetch(`{{ route('user.booking.statement.search-users') }}?q=${this.searches[key]}&role_id=${roleId}`);
                        const data = await response.json();
                        this.users[key] = data.data;
                        this.showResults[key] = true;
                    } catch (error) {
                        console.error('Error fetching users:', error);
                    } finally {
                        this.loading[key] = false;
                    }
                },

                selectUser(key, user) {
                    this.selectedIds[key] = user.id;
                    this.searches[key] = user.name;
                    this.showResults[key] = false;
                },

                clearSelection(key) {
                    this.selectedIds[key] = '';
                    this.searches[key] = '';
                    this.users[key] = [];
                },

                closeResults(key) {
                    this.showResults[key] = false;
                },

                openPrintModal() {
                    this.isPrintModalOpen = true;
                },

                openSaveModal() {
                    this.isSaveModalOpen = true;
                },

                openStatusModal(id, status, action) {
                    this.editingStatementId = id;
                    this.editingStatementStatus = status;
                    this.editingStatementAction = action;
                    this.isStatusModalOpen = true;
                },

                submitPrint() {
                    document.getElementById('printForm').submit();
                    this.isPrintModalOpen = false;
                },

                submitSave() {
                    document.getElementById('saveForm').submit();
                    this.isSaveModalOpen = false;
                },

                submitStatus() {
                    document.getElementById('statusForm').submit();
                    this.isStatusModalOpen = false;
                }
            }
        }
    </script>
@endsection
