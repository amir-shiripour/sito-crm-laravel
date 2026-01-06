{{-- modules/Clients/resources/views/widgets/client-quick-form.blade.php --}}
{{-- فرم inline برای ویجت ایجاد سریع کلاینت --}}

@php
    // مثل quick-widget: فیلتر فیلدهای quick_create
    $fields = collect($quickFields ?? ($schema['fields'] ?? []))
    ->where('quick_create', true)
    ->values();
@endphp

<div class="mt-4">
    @if($fields->isEmpty())
        <div class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
            هیچ فیلدی برای ایجاد سریع تنظیم نشده است.
        </div>
    @else
        <form wire:submit.prevent="saveQuick" class="space-y-4">
            {{-- گرید فیلدها، هماهنگ با استایل کلی فرم‌ها --}}
            <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                @foreach($fields as $i => $field)
                    @php($fid = $field['id'] ?? "qf{$i}")

                    <div wire:key="widget-qc-{{ $fid }}" class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $field['label'] ?? $fid }}
                            @if(($field['required'] ?? false))
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        {{-- استفاده مجدد از partial اصلی برای یکپارچگی کامل --}}
                        @include('clients::user.clients._quick-field', [
                        'field' => $field,
                        'fid' => $fid,
                        ])
                    </div>
                @endforeach
            </div>

            {{-- فوتر فرم ویجت: دکمه ذخیره سریع --}}
            <div class="flex justify-end pt-2">
                <button type="submit" wire:loading.attr="disabled" @if($fields->isEmpty()) disabled @endif
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold
                text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed
                transition-colors"
                >
                <span wire:loading.remove wire:target="saveQuick">
                    ذخیره سریع
                </span>

                    <span wire:loading wire:target="saveQuick" class="flex items-center gap-1">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </span>
                </button>
            </div>
        </form>
    @endif

    {{-- 🔹 مودال نمایش اطلاعات ورود بعد از ایجاد سریع (کپی شده از quick-widget) --}}
    <div
        x-data="{
            open: false,
            username: '',
            password: '',
        }"
        x-on:client-password-created.window="
            open = true;
            username = $event.detail.username;
            password = $event.detail.password;
        "
    >
        <template x-if="open">
            <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 border border-gray-200 dark:border-gray-700">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            اطلاعات ورود کاربر
                        </h3>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            ✕
                        </button>
                    </div>

                    <div class="px-5 py-4 space-y-3 text-sm">
                        <p class="text-gray-600 dark:text-gray-300">
                            این اطلاعات فقط یک‌بار نمایش داده می‌شود. لطفاً برای ارسال به کاربر کپی کنید.
                        </p>

                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-900/50 rounded-xl px-3 py-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">نام کاربری</span>
                            <span class="font-mono text-xs text-gray-900 dark:text-gray-100" x-text="username"></span>
                        </div>

                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-900/50 rounded-xl px-3 py-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">رمز عبور</span>
                            <span class="font-mono text-xs text-rose-600 dark:text-rose-400" x-text="password"></span>
                        </div>

                        <button
                            type="button"
                            @click="
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
                            class="w-full mt-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-gray-900 text-white text-xs font-medium hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600"
                        >
                            کپی نام کاربری و رمز
                        </button>
                    </div>

                    <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-between gap-2">
                        <button
                            type="button"
                            @click="open = false"
                            class="px-4 py-2 rounded-xl text-xs font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            متوجه شدم
                        </button>
                        <button
                            type="button"
                            @click="window.location='{{ route('user.clients.index') }}'"
                            class="px-4 py-2 rounded-xl text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-700"
                        >
                            رفتن به لیست مشتریان
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
