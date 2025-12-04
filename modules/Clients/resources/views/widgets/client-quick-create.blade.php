{{-- clients::widgets.client-quick-create --}}

@php
    $labelSingular = config('clients.labels.singular', 'مشتری');

    // استایل اینپوت‌ها مشابه _quick-field
    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                       focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all duration-200
                       dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dark:focus:border-emerald-500/50";
@endphp

<div
    x-data="clientQuickCreateWidget()"
    x-init="init()"
    class="space-y-4"
>
    {{-- هدر ویجت --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                ایجاد سریع {{ $labelSingular }}
            </h3>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                چند فیلد کلیدی را پر کن تا {{ $labelSingular }} جدید به سرعت ساخته شود.
            </p>
        </div>
    </div>

    {{-- فرم --}}
    <form
        method="POST"
        action="{{ route('user.clients.quick-store') }}"
        class="space-y-3"
        @submit.prevent="submitForm"
    >
        @csrf

        {{-- نام کامل --}}
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                نام کامل <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                x-model="form.full_name"
                :class="errorClass('full_name')"
                class="{{ $baseInputClass }}"
                placeholder="مثلاً: علی رضایی"
            >
            <template x-if="errors.full_name">
                <p class="mt-1 text-[11px] text-red-500" x-text="errors.full_name[0]"></p>
            </template>
        </div>

        {{-- ایمیل --}}
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                ایمیل
            </label>
            <input
                type="email"
                x-model="form.email"
                :class="errorClass('email')"
                class="{{ $baseInputClass }} dir-ltr"
                placeholder="example@mail.com"
            >
            <template x-if="errors.email">
                <p class="mt-1 text-[11px] text-red-500" x-text="errors.email[0]"></p>
            </template>
        </div>

        {{-- تلفن --}}
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                تلفن
            </label>
            <input
                type="text"
                x-model="form.phone"
                :class="errorClass('phone')"
                class="{{ $baseInputClass }} dir-ltr"
                placeholder="مثلاً: 0912xxxxxxx"
            >
            <template x-if="errors.phone">
                <p class="mt-1 text-[11px] text-red-500" x-text="errors.phone[0]"></p>
            </template>
        </div>

        {{-- یادداشت --}}
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                یادداشت
            </label>
            <textarea
                rows="2"
                x-model="form.notes"
                :class="errorClass('notes')"
                class="{{ $baseInputClass }} resize-none"
                placeholder="هر توضیح کوتاهی که کمک می‌کند..."
            ></textarea>
            <template x-if="errors.notes">
                <p class="mt-1 text-[11px] text-red-500" x-text="errors.notes[0]"></p>
            </template>
        </div>

        {{-- دکمه‌ها --}}
        <div class="pt-1 flex items-center justify-end">
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-medium
                       hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 transition-all duration-200
                       disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="loading"
            >
                <span x-show="!loading">
                    ثبت {{ $labelSingular }}
                </span>
                <span x-show="loading" class="flex items-center gap-1">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042
                                 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    در حال ثبت...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
    function clientQuickCreateWidget() {
        return {
            form: {
                full_name: '',
                email: '',
                phone: '',
                notes: '',
            },
            errors: {},
            loading: false,

            init() {
                // می‌توانید عملیات خاص را اینجا انجام دهید (مانند اضافه کردن رویدادها)
            },

            errorClass(field) {
                return this.errors[field]
                    ? 'border-red-300 focus:border-red-400 focus:ring-red-300/30'
                    : '';
            },

            notify(type, text) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, text }
                }));
            },

            async submitForm(event) {
                this.loading = true;
                this.errors = {};

                const url = '{{ route('user.clients.quick-store') }}';
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const payload = new URLSearchParams();
                Object.entries(this.form).forEach(([key, value]) => {
                    if (value !== null && value !== undefined) {
                        payload.append(key, value);
                    }
                });

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        },
                        body: payload.toString(),
                    });

                    if (response.status === 422) {
                        const data = await response.json();
                        this.errors = data.errors || {};
                        this.notify('error', 'لطفاً خطاهای فرم را بررسی کنید.');
                        return;
                    }

                    if (!response.ok) {
                        throw new Error('خطا در ایجاد مشتری');
                    }

                    const data = await response.json();

                    this.notify('success', data.message || 'مشتری با موفقیت ایجاد شد.');

                    this.form.full_name = '';
                    this.form.email     = '';
                    this.form.phone     = '';
                    this.form.notes     = '';

                } catch (e) {
                    console.error(e);
                    this.notify('error', 'خطا در ایجاد مشتری. لطفاً دوباره تلاش کنید.');
                } finally {
                    this.loading = false;
                }
            },
        }
    }
</script>
