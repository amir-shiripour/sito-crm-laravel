{{-- Modules/Clients/resources/views/user/clients/dynamic-form.blade.php --}}

@php
    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400
    focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200
    dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5";

    $fields = $schema['fields'] ?? [];
    $grouped = collect($fields)->groupBy(function ($f) {
    return $f['group'] ?? '';
    });
@endphp

<div class="mx-auto">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none">

        {{-- هدر فرم --}}
        <div
            class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800">
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $client?->id ? 'ویرایش پرونده' : 'ثبت پرونده جدید' }}
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $client?->id
                        ? 'ویرایش اطلاعات '.config('clients.labels.singular', 'مشتری')
                        : 'اطلاعات '.config('clients.labels.singular', 'مشتری').' را با دقت وارد کنید' }}
                </p>
            </div>

            <a href="{{ route('user.clients.index') }}"
               class="group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-400 dark:hover:text-gray-300 transition-all dark:bg-gray-700 dark:border-gray-800 dark:text-gray-200 dark:hover:bg-gray-600">
                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-300 transition-colors" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <span>بازگشت</span>
            </a>
        </div>

        <div class="p-6 sm:p-8 space-y-8">
            {{-- پیام موفقیت --}}
            @if (session('success'))
                <div
                    class="flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(empty($fields))
                {{-- اگر هنوز هیچ فیلدی در فرم تعریف نشده --}}
                <div
                    class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-white">
                        هنوز ساختار فرمی برای این ماژول تعریف نشده است
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        از بخش تنظیمات &raquo; فرم‌ساز برای تعریف فیلدها استفاده کنید.
                    </p>
                </div>
            @else
                {{-- رندر گروه‌ها و فیلدهای اسکیمای فرم --}}
                @foreach($grouped as $groupName => $groupFields)
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 mb-4 pb-1 border-b border-gray-100 dark:border-gray-700">
                    <span
                        class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold dark:bg-indigo-900/50 dark:text-indigo-300">
                        {{ $loop->iteration }}
                    </span>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                                {{ $groupName !== '' ? $groupName : 'اطلاعات فرم' }}
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
                            @foreach($groupFields as $i => $field)
                                @php
                                    $fid = $field['id'] ?? "f{$loop->index}";
                                    $width = $field['width'] ?? 'full';
                                    $widthClass = match ($width) {
                                    '1/2' => 'md:col-span-1 lg:col-span-3',
                                    '1/3' => 'md:col-span-1 lg:col-span-2',
                                    default => 'md:col-span-2 lg:col-span-6',
                                    };
                                @endphp

                                <div wire:key="df-{{ $fid }}" class="col-span-1 {{ $widthClass }}">
                                    <label class="{{ $labelClass }}">
                                        {{ $field['label'] ?? $fid }}
                                        @if(($field['required'] ?? false))
                                            <span class="text-red-500">*</span>
                                        @elseif($this->isFieldConditionallyRequired($fid))
                                            <span class="text-amber-500" title="این فیلد به صورت شرطی الزامی است">*</span>
                                        @endif
                                    </label>

                                    @include('clients::user.clients._dynamic-field', [
                                    'field' => $field,
                                    'fid' => $fid,
                                    ])
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @endif

            {{-- دکمه‌ها --}}
            <div class="flex items-center justify-end gap-3 pt-6 mt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('user.clients.index') }}"
                   class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 transition-all text-sm font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                    انصراف
                </a>
                <button wire:click="save" wire:loading.attr="disabled"
                        class="relative px-6 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 focus:ring-4 focus:ring-indigo-500/30 transition-all transform active:scale-95 text-sm font-medium disabled:opacity-70 disabled:cursor-not-allowed">
                    <span wire:loading.remove>ذخیره تغییرات</span>
                    <span wire:loading.flex class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042
                                     1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        در حال پردازش...
                    </span>
                </button>
            </div>
        </div>
    </div>


    {{-- مودال نمایش رمز بعد از ایجاد موفق --}}
    <div x-data="{
        open: false,
        username: '',
        password: '',
    }" x-on:client-password-created.window="
        open = true;
        username = $event.detail.username;
        password = $event.detail.password;
    ">
        <template x-if="open">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 border border-gray-200 dark:border-gray-700">
                    <div
                        class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            اطلاعات ورود کاربر
                        </h3>
                        <button @click="open = false"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            ✕
                        </button>
                    </div>

                    <div class="px-5 py-4 space-y-3 text-sm">
                        <p class="text-gray-600 dark:text-gray-300">
                            این اطلاعات فقط یک‌بار نمایش داده می‌شود. لطفاً برای ارسال به کاربر کپی کنید.
                        </p>

                        <div
                            class="flex items-center justify-between bg-gray-50 dark:bg-gray-900/50 rounded-xl px-3 py-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">نام کاربری</span>
                            <span class="font-mono text-xs text-gray-900 dark:text-gray-100" x-text="username"></span>
                        </div>

                        <div
                            class="flex items-center justify-between bg-gray-50 dark:bg-gray-900/50 rounded-xl px-3 py-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">رمز عبور</span>
                            <span class="font-mono text-xs text-rose-600 dark:text-rose-400" x-text="password"></span>
                        </div>

                        <button type="button" @click="
                                const text = 'user: ' + username + ' | pass: ' + password;
                                if (navigator && navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(text);
                                } else {
                                    const el = document.createElement('textarea');
                                    el.value = text;
                                    document.body.appendChild(el);
                                    el.select();
                                    document.execCommand('copy');
                                    document.body.removeChild(el);
                                }
                            "
                                class="w-full mt-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-gray-900 text-white text-xs font-medium hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">
                            کپی نام کاربری و رمز
                        </button>
                    </div>

                    <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-between gap-2">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 rounded-xl text-xs font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                            متوجه شدم
                        </button>
                        <button type="button" @click="window.location='{{ route('user.clients.index') }}'"
                                class="px-4 py-2 rounded-xl text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-700">
                            رفتن به لیست مشتریان
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
