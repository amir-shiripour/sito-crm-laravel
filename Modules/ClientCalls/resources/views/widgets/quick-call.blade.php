{{-- modules/ClientCalls/resources/views/widgets/quick-call.blade.php --}}

@php
    use Morilog\Jalali\Jalalian;
    $todayJalali = Jalalian::fromCarbon(now())->format('Y/m/d');
    $currentTime = now()->format('H:i');

    $statusMap = [
    'done' => 'موفق',
    'failed' => 'ناموفق',
    ];

    // استایل‌های مشترک
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-medium text-gray-900
    placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all
    dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

@includeIf('partials.jalali-date-picker')

<div x-data="clientCallsQuickWidget()" class="relative h-full space-y-4 text-sm text-gray-800 dark:text-gray-200">

    {{-- هدر ویجت --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2">
            <div
                class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">ثبت تماس جدید</h2>
        </div>
    </div>

    {{-- فرم --}}
    <form method="POST" action="{{ route('user.client-calls.quick-store') }}" class="space-y-4"
          @submit.prevent="handleSubmit($event)">
        @csrf

        {{-- انتخاب مشتری --}}
        <div class="relative z-20">
            <label class="{{ $labelClass }}">مخاطب <span class="text-red-500">*</span></label>

            {{-- ورودی جستجو --}}
            <div class="relative" x-show="!selectedClient">
                <input type="text" x-model="searchQuery" @input.debounce.400ms="searchClients"
                       placeholder="جستجوی نام، شماره تماس..." class="{{ $inputClass }} pr-9">
                <div class="absolute right-3 top-2.5 text-gray-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- نمایش مشتری انتخاب شده --}}
            <template x-if="selectedClient">
                <div
                    class="flex items-center justify-between px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800 animate-in fade-in slide-in-from-top-1">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center text-xs font-bold dark:bg-emerald-800 dark:text-emerald-200">
                            <span x-text="(selectedClient.full_name || selectedClient.username).charAt(0)"></span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-200"
                                  x-text="selectedClient.full_name || selectedClient.username"></span>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 dir-ltr text-right"
                                  x-text="selectedClient.phone"></span>
                        </div>
                    </div>
                    <button type="button" @click="clearSelected()"
                            class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-100 dark:text-emerald-400 dark:hover:bg-emerald-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>

            {{-- دراپ‌داون نتایج --}}
            <div x-show="showResults" x-transition @click.away="showResults = false"
                 class="absolute top-full left-0 right-0 mt-1 max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800 z-50 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">

                <template x-if="loading">
                    <div class="p-4 text-center text-xs text-gray-500">
                        <svg class="animate-spin h-4 w-4 mx-auto mb-2 text-indigo-500"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        در حال جستجو...
                    </div>
                </template>

                <template x-if="!loading && Array.isArray(results) && results.length === 0">
                    <div class="p-3 text-center text-xs text-gray-500">موردی یافت نشد.</div>
                </template>

                <template x-for="(client, index) in (Array.isArray(results) ? results : [])" :key="client?.id || index">
                    <button type="button" @click="selectClient(client)"
                            class="w-full flex items-center justify-between px-4 py-3 text-right hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                        <span class="text-xs font-medium text-gray-900 dark:text-white"
                              x-text="client.full_name || client.username"></span>
                        <span class="text-[10px] text-gray-500 dir-ltr" x-text="client.phone"></span>
                    </button>
                </template>
            </div>

            <input type="hidden" name="client_id" :value="selectedClient?.id">
            <p class="text-[10px] text-red-500 mt-1" x-show="submitError && !selectedClient" x-transition>لطفاً یک مشتری
                را انتخاب کنید.</p>
        </div>

        {{-- تاریخ و زمان --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="{{ $labelClass }}">تاریخ <span class="text-red-500">*</span></label>
                <input type="text" name="call_date_jalali" data-jdp-only-date value="{{ $todayJalali }}" required
                       class="{{ $inputClass }} text-center">
            </div>
            <div>
                <label class="{{ $labelClass }}">ساعت <span class="text-red-500">*</span></label>
                <input type="text" name="call_time" data-jdp-only-time value="{{ $currentTime }}" placeholder="14:30" required
                       class="{{ $inputClass }} text-center dir-ltr">
            </div>
        </div>

        {{-- وضعیت و علت --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="{{ $labelClass }}">وضعیت <span class="text-red-500">*</span></label>
                <select name="status" required class="{{ $inputClass }} appearance-none">
                    @foreach($statusMap as $key => $label)
                        <option value="{{ $key }}" @selected($key==='done' )>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelClass }}">موضوع <span class="text-red-500">*</span></label>
                <input type="text" name="reason" placeholder="مثلاً: پیگیری فاکتور" required class="{{ $inputClass }}">
            </div>
        </div>

        {{-- نتیجه --}}
        <div>
            <label class="{{ $labelClass }}">نتیجه تماس <span class="text-red-500">*</span></label>
            <textarea name="result" rows="2" required class="{{ $inputClass }} resize-none"
                      placeholder="خلاصه مکالمه..."></textarea>
        </div>

        {{-- دکمه ثبت --}}
        <div class="pt-2">
            <button type="submit" :disabled="submitting"
                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-2.5 text-xs font-bold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all disabled:opacity-70 disabled:cursor-not-allowed">
                <span x-show="!submitting">ثبت تماس</span>
                <span x-show="submitting" class="flex items-center gap-2">
                    <svg class="animate-spin h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    در حال پردازش
                </span>
            </button>
        </div>

    </form>

    {{-- مودال کوچک پیشنهاد ثبت پیگیری بعد از تماس موفق --}}
    <div x-show="showFollowupPrompt" x-transition.opacity x-cloak class="absolute inset-x-0 bottom-0 px-1 pb-1">
        <div class="mx-1 rounded-2xl border border-amber-100 bg-amber-50/95 shadow-xl
                   dark:border-amber-800 dark:bg-amber-900/95 text-xs text-amber-900 dark:text-amber-50">
            <div class="flex items-start gap-3 p-3">
                <div class="mt-0.5">
                    <svg class="w-5 h-5 text-amber-500 dark:text-amber-300" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                              d="M13 16h-1v-4h-1m1-4h.01M4.93 4.93a10.5 10.5 0 0114.84 0 10.5 10.5 0 010 14.84A10.5 10.5 0 014.93 4.93z" />
                    </svg>
                </div>
                <div class="flex-1 space-y-1">
                    <p class="font-semibold">
                        تماس برای
                        <span x-text="followupClientLabel" class="font-bold"></span>
                        با موفقیت ثبت شد.
                    </p>
                    <p class="text-[11px] text-amber-800/80 dark:text-amber-100/80">
                        اگر نیاز است بعداً کاری انجام شود (مثلاً ارسال پیش‌فاکتور، پیگیری پرداخت و ...)،
                        بهتر است همین حالا یک <span class="font-semibold">پیگیری</span> برای این مشتری ثبت کنید.
                    </p>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="dismissFollowupPrompt()"
                                class="px-2.5 py-1 rounded-xl border border-amber-200 text-[11px] text-amber-800 bg-amber-50 hover:bg-amber-100
                                       dark:border-amber-700 dark:bg-amber-900/40 dark:text-amber-100 dark:hover:bg-amber-800/80 transition-colors">
                            بعداً انجام می‌دم
                        </button>
                        <button type="button" @click="openFollowup()" class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-amber-500 text-white text-[11px] font-semibold
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

<script>
    function clientCallsQuickWidget() {
        return {
            searchQuery: '',
            results: [],
            loading: false,
            showResults: false,
            selectedClient: null,
            submitError: false,
            submitting: false,

            showFollowupPrompt: false,
            followupUrl: null,
            followupClientLabel: '',

            async searchClients() {
                if (this.searchQuery.length < 2) {
                    this.results = [];
                    this.showResults = false;
                    return;
                }

                this.loading = true;
                this.showResults = true;

                try {
                    // آدرس فرضی API سرچ - مطمئن شوید روت user.clients.search وجود دارد
                    const response = await fetch(
                        `{{ route('user.clients.search') }}?q=${encodeURIComponent(this.searchQuery)}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                    if (!response.ok) throw new Error('خطا در ارتباط');

                    const data = await response.json();
                    // Handle both array format and object with results property
                    this.results = Array.isArray(data) ? data : (Array.isArray(data?.results) ? data.results : (Array
                        .isArray(data?.data) ? data.data : []));
                } catch (e) {
                    console.error(e);
                    this.results = [];
                } finally {
                    this.loading = false;
                }
            },

            selectClient(client) {
                this.selectedClient = client;
                this.showResults = false;
                this.results = [];
                this.searchQuery = '';
            },

            clearSelected() {
                this.selectedClient = null;
            },

            notify(type, text) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        type,
                        text
                    }
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
                this.submitError = false;

                if (!this.selectedClient) {
                    this.submitError = true;
                    this.notify('error', 'لطفاً مشتری را انتخاب کنید');
                    return;
                }

                this.submitting = true;
                const form = event.target;
                const formData = new FormData(form);
                formData.set('client_id', this.selectedClient.id);

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken ? {
                                'X-CSRF-TOKEN': csrfToken
                            } : {})
                        },
                        body: formData
                    });

                    if (!response.ok) throw new Error('خطا در ثبت');

                    const data = await response.json();
                    this.notify('success', data.message || 'تماس با موفقیت ثبت شد');

                    // 🔔 اگر سرور لینک پیگیری فرستاد، پیام یادآوری + امکان باز کردن صفحه پیگیری
                    if (data.followup_url) {
                        this.followupUrl = data.followup_url;
                        this.followupClientLabel =
                            this.selectedClient?.full_name ||
                            this.selectedClient?.username ||
                            '';
                        this.showFollowupPrompt = true;
                    }

                    // ریست فیلدها
                    form.reset();
                    // بازگرداندن مقادیر پیش‌فرض
                    form.call_date_jalali.value = '{{ $todayJalali }}';
                    form.call_time.value = '{{ $currentTime }}';
                    // مشتری را نگه می‌داریم

                } catch (e) {
                    this.notify('error', 'خطا در عملیات. مجدد تلاش کنید.');
                } finally {
                    this.submitting = false;
                }
            }
        };
    }
</script>
