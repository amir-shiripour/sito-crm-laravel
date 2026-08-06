{{-- clients::user.settings.dashboard --}}
@php
    $title = 'تنظیمات داشبورد '.config('clients.labels.plural');

    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 outline-none";
    $labelClass = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

{{-- افزودن استایل و فونت Quill در صورت عدم بارگذاری قبلی --}}
@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .custom-quill-wrapper .ql-toolbar {
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            border-color: #e5e7eb;
            background-color: #f9fafb;
        }
        .dark .custom-quill-wrapper .ql-toolbar {
            border-color: #374151;
            background-color: #111827;
        }
        .custom-quill-wrapper .ql-container {
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            border-color: #e5e7eb;
            min-height: 220px;
            font-family: inherit;
        }
        .dark .custom-quill-wrapper .ql-container {
            border-color: #374151;
            color: #f3f4f6;
        }
        .dark .ql-snow .ql-stroke { stroke: #9ca3af; }
        .dark .ql-snow .ql-fill { fill: #9ca3af; }
        .dark .ql-snow .ql-picker { color: #9ca3af; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
@endpush

<div class="flex justify-center">
    <div class="w-full max-w-4xl">

        {{-- هدر --}}
        <div class="mb-6 text-center sm:text-right flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    مدیریت بخش‌ها و ماژول‌های فعال در پرتال کلاینت‌ها و تنظیم قوانین ورود.
                </p>
            </div>

            {{-- تب‌ها جهت توسعه در آینده --}}
            <div class="flex items-center gap-2 bg-gray-100 dark:bg-gray-800 p-1.5 rounded-2xl border border-gray-200 dark:border-gray-700">
                <button type="button" wire:click="$set('activeTab', 'terms')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'terms' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        مودال قوانین و مقررات
                    </span>
                </button>

                <!-- قابل افزودن برای بخش‌های جدید در آینده -->
            </div>
        </div>

        {{-- نوار اعلان موفقیت --}}
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-100 px-4 py-3 rounded-2xl flex items-center gap-3 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300 animate-in fade-in">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- تب ۱: مودال قوانین و مقررات --}}
        @if($activeTab === 'terms')
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none overflow-hidden">
                <form wire:submit.prevent="saveTerms">
                    <div class="p-6 sm:p-8 space-y-6">

                        {{-- سوئیچ فعال سازی قوانین --}}
                        <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">فعال‌سازی مودال قوانین و مقررات</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    در صورت فعال بودن، هر کلاینتی که وارد اکانت خود در پرتال شود این مودال به او نمایش داده خواهد شد.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="termsEnabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        @if($termsEnabled)
                            <div class="space-y-6 animate-in fade-in slide-in-from-top-2 duration-300">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- عنوان قوانین --}}
                                    <div>
                                        <label class="{{ $labelClass }}">عنوان مودال <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="termsTitle" class="{{ $inputClass }}" placeholder="مثلاً: قوانین و شرایط استفاده از خدمات">
                                        @error('termsTitle') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- نسخه قوانین --}}
                                    <div>
                                        <label class="{{ $labelClass }}">شماره نسخه قوانین <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="termsVersion" class="{{ $inputClass }} font-mono dir-ltr" placeholder="1.0">
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                            با تغییر این نسخه (مثلاً به 1.1 یا 2.0)، مودال قوانین مجدداً برای تمام کاربران نمایش داده می‌شود.
                                        </p>
                                        @error('termsVersion') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- متن قوانین (ویرایشگر Quill JS) --}}
                                <div class="relative">
                                    <label class="{{ $labelClass }}">متن قوانین و مقررات (معرفی و شرح کامل)</label>
                                    <div
                                        wire:ignore
                                        x-data="{
                                            content: @entangle('termsContent'),
                                            initQuill() {
                                                if (typeof Quill === 'undefined') {
                                                    setTimeout(() => this.initQuill(), 200);
                                                    return;
                                                }
                                                let quill = new Quill(this.$refs.editor, {
                                                    theme: 'snow',
                                                    placeholder: 'متن کامل قوانین و مقررات را در اینجا تایپ کنید...',
                                                    modules: {
                                                        toolbar: [
                                                            [{ 'header': [1, 2, 3, false] }],
                                                            ['bold', 'italic', 'underline', 'strike'],
                                                            [{ 'color': [] }, { 'background': [] }],
                                                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                            [{ 'align': [] }, { 'direction': 'rtl' }],
                                                            ['link'],
                                                            ['clean']
                                                        ]
                                                    }
                                                });
                                                quill.on('text-change', () => {
                                                    this.content = quill.root.innerHTML;
                                                });
                                                quill.root.innerHTML = this.content || '';
                                            }
                                        }"
                                        x-init="initQuill()"
                                        class="custom-quill-wrapper border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-900/50 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 shadow-sm"
                                    >
                                        <div x-ref="editor" class="focus:outline-none min-h-[200px]"></div>
                                    </div>
                                    @error('termsContent') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- متن دکمه تأیید --}}
                                    <div>
                                        <label class="{{ $labelClass }}">عنوان دکمه پذیرش <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="termsBtnAccept" class="{{ $inputClass }}">
                                        @error('termsBtnAccept') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- متن دکمه بعداً --}}
                                    <div>
                                        <label class="{{ $labelClass }}">عنوان دکمه انصراف/بعداً <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="termsBtnLater" class="{{ $inputClass }}">
                                        @error('termsBtnLater') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- تنظیمات رفتاری دکمه‌ها --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                                    <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 cursor-pointer group">
                                        <input type="checkbox" wire:model="termsForceScroll" class="mt-1 w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 transition-colors">اجبار کاربر به اسکرول تا انتهای متن</span>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">تا زمانی که کاربر تمام متن قوانین را تا انتهای اسکرول نکند، دکمه قبول کردن غیرفعال است.</span>
                                        </div>
                                    </label>

                                    <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 cursor-pointer group">
                                        <input type="checkbox" wire:model="termsAllowLater" class="mt-1 w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 transition-colors">امکان بستن موقت (دکمه بعداً می‌خوانم)</span>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">در صورت غیرفعال بودن، کاربر تا قوانین را نپذیرد نمی‌تواند مودال را ببندد.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endif

                        {{-- دکمه ذخیره --}}
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 focus:ring-4 focus:ring-indigo-500/30 transition-all transform active:scale-95 disabled:opacity-60">
                                ذخیره تنظیمات قوانین
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        @endif

    </div>
</div>
