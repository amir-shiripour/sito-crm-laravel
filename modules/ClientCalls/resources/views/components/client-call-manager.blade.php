{{-- modules/ClientCalls/resources/views/components/client-call-manager.blade.php --}}

@props(['client'])

@php
    /** @var \Modules\Clients\Entities\Client $client */

    $calls      = $client->calls;
    $callsCount = $calls->count();

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

<div x-data="clientCallManager()">
    {{-- دکمه باز کردن مودال --}}
    <button type="button"
            @click="openCallsModal = true"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-[11px]
                   bg-white border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300
                   dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-600
                   transition-colors">
        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full
                     bg-indigo-100 text-indigo-700 text-[10px]
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
        {{-- بک‌دراپ برای بستن --}}
        <div class="absolute inset-0" @click="openCallsModal = false"></div>

        {{-- کارت مودال --}}
        <div class="relative z-50 w-full max-w-4xl mx-auto
                    bg-white rounded-2xl shadow-2xl border border-gray-200
                    dark:bg-gray-900 dark:border-gray-700">

            {{-- هدر --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-9 h-9 rounded-2xl
                                bg-indigo-50 text-indigo-600
                                dark:bg-indigo-900/40 dark:text-indigo-300">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                  d="M3 5h2l3 10h11l2-6H9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                  d="M10 19a1 1 0 11-2 0 1 1 0 012 0zm9 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                            مدیریت تماس‌های {{ $client->full_name ?: $client->username }}
                        </h2>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                            مجموع تماس‌های ثبت‌شده: <span class="font-semibold">{{ $callsCount }}</span>
                        </p>
                    </div>
                </div>

                <button type="button"
                        @click="openCallsModal = false"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-xl
                               text-gray-400 hover:text-gray-700 hover:bg-gray-100
                               dark:text-gray-500 dark:hover:text-gray-200 dark:hover:bg-gray-800
                               transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- بدنه: فرم سمت راست + تاریخچه بزرگ‌تر سمت چپ (در دسکتاپ) --}}
            <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- ستون فرم ثبت تماس جدید (سمت راست در RTL، کوچکتر) --}}
                <div class="md:col-span-4 space-y-3">
                    <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                        ثبت تماس جدید
                    </h3>

                    @can('client-calls.create')
                        <form method="POST"
                              action="{{ route('user.clients.calls.store', $client) }}"
                              class="space-y-3 text-[11px]"
                              @submit.prevent="handleSubmit($event)">
                            @csrf

                            <div class="grid grid-cols-1 gap-3">
                                {{-- تاریخ شمسی --}}
                                <div>
                                    <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                        تاریخ تماس <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="call_date_jalali"
                                           data-jdp-only-date
                                           value="{{ old('call_date_jalali', $defaultJalaliToday) }}"
                                           required
                                           class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                                  placeholder-gray-400
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
                                           data-jdp-only-time
                                           placeholder="مثلاً 14:30"
                                           value="{{ old('call_time') }}"
                                           required
                                           class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900 dir-ltr
                                                  placeholder-gray-400
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
                                            <option
                                                value="{{ $key }}" @selected(old('status', 'done') === $key)>{{ $info['label'] }}</option>
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
                                                  placeholder-gray-400
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
                                                     placeholder-gray-400
                                                     focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                                     dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                                              placeholder="نتیجه تماس را وارد کنید...">{{ old('result') }}</textarea>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <button type="button"
                                        @click="openCallsModal = false"
                                        class="px-3 py-1.5 rounded-xl border border-gray-300 text-[11px] text-gray-700 bg-white hover:bg-gray-50
                                           dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800
                                           transition-colors">
                                    بستن
                                </button>
                                <button type="submit"
                                        :disabled="submitting"
                                        class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white text-[11px] font-medium
                       hover:bg-indigo-700 shadow-md shadow-indigo-500/20
                       transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                                    <span x-show="!submitting">ثبت تماس</span>
                                    <span x-show="submitting" class="flex items-center gap-1">
                                        <svg class="w-3 h-3 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2
                                                     5.291A7.962 7.962 0 014 12H0c0
                                                     3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>در حال ثبت...</span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            شما مجوز ثبت تماس برای این مشتری را ندارید.
                        </div>
                    @endcan
                </div>

                {{-- ستون تاریخچه (بزرگ‌تر، سمت چپ در دسکتاپ) --}}
                <div class="md:col-span-8 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                            تاریخچه اخیر تماس‌ها
                        </h3>
                        @can('client-calls.view')
                            <a href="{{ route('user.clients.calls.index', $client) }}"
                               class="inline-flex items-center gap-1 text-[11px] text-indigo-600 hover:text-indigo-700
                                      dark:text-indigo-400 dark:hover:text-indigo-300"
                               target="_blank">
                                <span>مشاهده در صفحه کامل</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                          d="M13 5h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @endcan
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50/60 max-h-72 overflow-auto
                                dark:border-gray-800 dark:bg-gray-900/40">
                        @if($recentCalls->isEmpty())
                            <div
                                class="flex flex-col items-center justify-center py-8 text-[11px] text-gray-500 dark:text-gray-400">
                                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M18 8a6 6 0 10-12 0v4a6 6 0 0012 0V8z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M13.73 21a2 2 0 01-3.46 0"/>
                                </svg>
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
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full border {{ $statusInfo['class'] }}">
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
                                                <div class="text-gray-700 dark:text-gray-200 truncate"
                                                     title="{{ $call->reason }}">
                                                    علت: {{ $call->reason }}
                                                </div>
                                            @endif
                                            @if($call->result)
                                                <div class="text-gray-500 dark:text-gray-300 line-clamp-2"
                                                     title="{{ $call->result }}">
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

            </div>

            {{-- پیشنهاد ثبت پیگیری بعد از ثبت موفق تماس --}}
            <div
                x-show="showFollowupPrompt"
                x-transition.opacity
                x-cloak
                class="absolute inset-x-5 bottom-4">
                <div
                    class="rounded-2xl border border-amber-100 bg-amber-50/95 shadow-xl
               dark:border-amber-800 dark:bg-amber-900/95 text-xs text-amber-900 dark:text-amber-50">
                    <div class="flex items-start gap-3 p-3">
                        <div class="mt-0.5">
                            <svg class="w-5 h-5 text-amber-500 dark:text-amber-300" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                      d="M13 16h-1v-4h-1m1-4h.01M4.93 4.93a10.5 10.5 0 0114.84 0 10.5 10.5 0 010 14.84A10.5 10.5 0 014.93 4.93z" />
                            </svg>
                        </div>
                        <div class="flex-1 space-y-1">
                            <p class="font-semibold">
                                تماس برای
                                <span x-text="clientLabel" class="font-bold"></span>
                                با موفقیت ثبت شد.
                            </p>
                            <p class="text-[11px] text-amber-800/80 dark:text-amber-100/80">
                                اگر لازم است بعداً اقدامی انجام شود (مثلاً ارسال پیش‌فاکتور، پیگیری پرداخت و ...)،
                                می‌توانید همین حالا یک <span class="font-semibold">پیگیری</span> برای این مشتری ثبت کنید.
                            </p>
                            <div class="flex items-center justify-end gap-2 pt-1">
                                <button type="button"
                                        @click="dismissFollowupPrompt()"
                                        class="px-2.5 py-1 rounded-xl border border-amber-200 text-[11px] text-amber-800 bg-amber-50 hover:bg-amber-100
                                   dark:border-amber-700 dark:bg-amber-900/40 dark:text-amber-100 dark:hover:bg-amber-800/80 transition-colors">
                                    بعداً انجام می‌دم
                                </button>
                                <button type="button"
                                        @click="openFollowup()"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-amber-500 text-white text-[11px] font-semibold
                                   hover:bg-amber-600 shadow-md shadow-amber-500/30 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>ثبت پیگیری برای این مشتری</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
    function clientCallManager() {
        return {
            openCallsModal: false,
            submitting: false,

            showFollowupPrompt: false,
            followupUrl: null,
            clientLabel: @json($client->full_name ?: $client->username),

            notify(type, text) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, text }
                }));
            },

            openFollowup() {
                if (this.followupUrl) {
                    window.open(this.followupUrl, '_blank');
                }
                this.showFollowupPrompt = false;
            },

            dismissFollowupPrompt() {
                this.showFollowupPrompt = false;
            },

            async handleSubmit(event) {
                this.submitting = true;
                this.showFollowupPrompt = false;

                const form = event.target;
                const formData = new FormData(form);

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
                        },
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('خطا در ثبت تماس');
                    }

                    const data = await response.json();

                    this.notify('success', data.message || 'تماس با موفقیت ثبت شد');

                    if (data.followup_url) {
                        this.followupUrl = data.followup_url;
                        this.showFollowupPrompt = true;
                    }

                    // ریست فرم به حالت اولیه
                    form.reset();

                    if (form.call_date_jalali) {
                        form.call_date_jalali.value = @json($defaultJalaliToday);
                    }

                    if (form.status) {
                        form.status.value = 'done';
                    }

                } catch (e) {
                    console.error(e);
                    this.notify('error', 'خطا در ثبت تماس. دوباره تلاش کنید.');
                } finally {
                    this.submitting = false;
                }
            }
        };
    }
</script>
