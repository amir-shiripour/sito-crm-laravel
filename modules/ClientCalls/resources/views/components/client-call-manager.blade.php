{{-- resources/views/components/client-call-manager.blade.php --}}

@props(['client'])

@php
    /** @var \Modules\Clients\Entities\Client $client */

    $calls       = $client->calls;
    $callsCount  = $calls->count();

    // آخرین چند تماس (مرتب بر اساس تاریخ/زمان، جدیدترین بالا)
    $recentCalls = $calls
        ->sortByDesc(function ($call) {
            $date = $call->call_date ? $call->call_date->format('Y-m-d') : '0000-00-00';
            $time = $call->call_time ? $call->call_time->format('H:i:s') : '00:00:00';
            return $date.' '.$time;
        })
        ->take(5);

    // تاریخ امروز به شمسی برای مقدار اولیه
    $defaultJalaliToday = \Morilog\Jalali\Jalalian::fromCarbon(now())->format('Y/m/d');

    // مپ وضعیت‌ها برای لیبل و کلاس
    $statusMap = [
        'planned' => [
            'label' => 'برنامه‌ریزی شده',
            'class' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-700',
        ],
        'done' => [
            'label' => 'انجام شده',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-700',
        ],
        'failed' => [
            'label' => 'ناموفق',
            'class' => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/40 dark:text-red-200 dark:border-red-700',
        ],
        'canceled' => [
            'label' => 'لغو شده',
            'class' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600',
        ],
    ];
@endphp

{{-- اسکریپت/استایل JalaliDatePicker (یکبار در کل صفحه) --}}
@includeIf('partials.jalali-date-picker')

<div x-data="{ openCallsModal: false }">
    {{-- دکمه باز کردن مودال --}}
    <button type="button"
            @click="openCallsModal = true"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-[11px]
                   bg-white border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300
                   dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-[10px]
                     dark:bg-indigo-900/40 dark:text-indigo-300">
            {{ $callsCount }}
        </span>
        <span>مدیریت تماس‌ها</span>
    </button>

    {{-- مودال مدیریت تماس‌ها --}}
    <div
        x-show="openCallsModal"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-40 flex items-center justify-center px-4 py-6
               bg-black/40 backdrop-blur-sm"
    >
        {{-- کلیک روی بک‌دراپ = بستن --}}
        <div class="absolute inset-0" @click="openCallsModal = false"></div>

        {{-- کارت مودال --}}
        <div class="relative z-50 w-full max-w-3xl mx-auto
                    bg-white rounded-2xl shadow-2xl border border-gray-200
                    dark:bg-gray-900 dark:border-gray-700">

            {{-- هدر --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                        مدیریت تماس‌های {{ $client->full_name ?: $client->username }}
                    </h2>
                    <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                        مجموع تماس‌های ثبت‌شده: {{ $callsCount }}
                    </p>
                </div>
                <button type="button"
                        @click="openCallsModal = false"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-xl
                               text-gray-400 hover:text-gray-700 hover:bg-gray-100
                               dark:text-gray-500 dark:hover:text-gray-200 dark:hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- بدنه: دو ستون --}}
            <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- ستون ۱: تاریخچه --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                            تاریخچه اخیر تماس‌ها
                        </h3>
                        @can('client-calls.view')
                            <a href="{{ route('user.clients.calls.index', $client) }}"
                               class="text-[11px] text-indigo-600 hover:text-indigo-700
                                      dark:text-indigo-400 dark:hover:text-indigo-300"
                               target="_blank">
                                مشاهده در صفحه کامل
                            </a>
                        @endcan
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50/60 max-h-72 overflow-auto
                                dark:border-gray-800 dark:bg-gray-900/40">
                        @if($recentCalls->isEmpty())
                            <div class="flex flex-col items-center justify-center py-8 text-[11px] text-gray-500 dark:text-gray-400">
                                <p>هنوز تماسی برای این مشتری ثبت نشده است.</p>
                            </div>
                        @else
                            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($recentCalls as $call)
                                    @php
                                        $statusKey   = $call->status ?? 'unknown';
                                        $statusInfo  = $statusMap[$statusKey] ?? [
                                            'label' => 'نامشخص',
                                            'class' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600',
                                        ];
                                        $dateText = $call->call_date
                                            ? \Morilog\Jalali\Jalalian::fromCarbon($call->call_date)->format('Y/m/d')
                                            : '—';
                                        $timeText = $call->call_time
                                            ? \Carbon\Carbon::parse($call->call_time)->format('H:i')
                                            : '—';
                                    @endphp

                                    <li class="px-3 py-2.5 text-[11px] flex items-start justify-between gap-2">
                                        <div class="flex-1 space-y-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full border {{ $statusInfo['class'] }}">
                                                    {{ $statusInfo['label'] }}
                                                </span>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 text-left">
                                                    <div class="dir-ltr">
                                                        {{ $dateText }}
                                                    </div>
                                                    <div class="dir-ltr">
                                                        {{ $timeText }}
                                                    </div>
                                                </div>
                                            </div>

                                            @if($call->reason)
                                                <div class="text-gray-700 dark:text-gray-200 truncate" title="{{ $call->reason }}">
                                                    علت: {{ $call->reason }}
                                                </div>
                                            @endif
                                            @if($call->result)
                                                <div class="text-gray-500 dark:text-gray-300 line-clamp-2" title="{{ $call->result }}">
                                                    نتیجه: {{ $call->result }}
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                {{-- ستون ۲: فرم ثبت تماس جدید --}}
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                        ثبت تماس جدید
                    </h3>

                    @can('client-calls.create')
                        <form method="POST" action="{{ route('user.clients.calls.store', $client) }}"
                              class="space-y-3 text-[11px]">
                            @csrf

                            <div class="grid grid-cols-1 gap-3">
                                {{-- تاریخ شمسی --}}
                                <div>
                                    <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                        تاریخ تماس <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="call_date_jalali"
                                           data-jdp
                                           value="{{ old('call_date_jalali', $defaultJalaliToday) }}"
                                           required
                                           class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                                  focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                                  dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                                </div>

                                {{-- زمان (only-time) --}}
                                <div>
                                    <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                        زمان تماس <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="call_time"
                                           data-jdp
                                           data-jdp-only-time="true"
                                           placeholder="مثلاً 14:30"
                                           value="{{ old('call_time') }}"
                                           required
                                           class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900 dir-ltr
                                                  focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                                  dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                                </div>

                                {{-- وضعیت --}}
                                <div>
                                    <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                        وضعیت تماس <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status"
                                            required
                                            class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                                   focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                                   dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                                        @foreach($statusMap as $key => $info)
                                            <option value="{{ $key }}" @selected(old('status', 'done') === $key)>{{ $info['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- علت --}}
                                <div>
                                    <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                        علت تماس <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="reason"
                                           value="{{ old('reason') }}"
                                           required
                                           class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                                  focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                                  dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                                </div>

                                {{-- نتیجه --}}
                                <div>
                                    <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                        نتیجه تماس <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="result"
                                              rows="3"
                                              required
                                              class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                                     focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                                     dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                                              placeholder="نتیجه تماس را وارد کنید...">{{ old('result') }}</textarea>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <button type="button"
                                        @click="openCallsModal = false"
                                        class="px-3 py-1.5 rounded-xl border border-gray-300 text-[11px] text-gray-700 bg-white hover:bg-gray-50
                                               dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800">
                                    بستن
                                </button>
                                <button type="submit"
                                        class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white text-[11px] font-medium
                                               hover:bg-indigo-700 shadow-md shadow-indigo-500/20">
                                    ثبت تماس
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            شما مجوز ثبت تماس برای این مشتری را ندارید.
                        </div>
                    @endcan
                </div>

            </div>
        </div>
    </div>
</div>
