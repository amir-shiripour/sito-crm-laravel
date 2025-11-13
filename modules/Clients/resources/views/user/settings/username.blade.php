{{-- clients::user.settings.username --}}
@php
    $title = 'استراتژی ساخت یوزرنیم';
    // استایل‌های مشترک
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

<div class="flex justify-center">
    <div class="w-full max-w-2xl">

        {{-- هدر --}}
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">تنظیمات نام کاربری</h1>
            <p class="text-sm text-gray-500 mt-2">نحوه تولید خودکار نام کاربری (Username) برای مشتریان جدید را تعیین کنید.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden">

            {{-- نوار اعلان موفقیت --}}
            @if(session('success'))
                <div class="bg-emerald-50 border-b border-emerald-100 px-4 py-3 flex items-center gap-3 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="p-6 sm:p-8 space-y-6">

                {{-- انتخاب استراتژی --}}
                <div>
                    <label class="{{ $labelClass }}">الگوریتم تولید (Strategy)</label>
                    <div class="relative">
                        <select wire:model="strategy" class="{{ $inputClass }} appearance-none cursor-pointer">
                            <option value="email_local">استفاده از بخش محلی ایمیل (email_local)</option>
                            <option value="mobile">استفاده از شماره موبایل (mobile)</option>
                            <option value="name_rand">ترکیب نام + عدد تصادفی (name_rand)</option>
                            <option value="prefix_incremental">پیشوند ثابت + شمارنده افزایشی (prefix_incremental)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>

                    {{-- توضیحات کمکی بر اساس انتخاب --}}
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/30 p-3 rounded-lg border border-gray-100 dark:border-gray-700/50">
                        @if($strategy === 'email_local')
                            مثال: اگر ایمیل <code>user@example.com</code> باشد، نام کاربری <code>user</code> خواهد بود.
                        @elseif($strategy === 'mobile')
                            نام کاربری دقیقا برابر با شماره موبایل وارد شده خواهد بود.
                        @elseif($strategy === 'name_rand')
                            مثال: برای "علی رضایی"، نام کاربری چیزی شبیه <code>ali_rezaei_482</code> خواهد بود.
                        @elseif($strategy === 'prefix_incremental')
                            یک پیشوند ثابت (مثلاً 'C') با یک عدد یکتا ترکیب می‌شود. مثال: <code>C-1001</code>
                        @endif
                    </div>
                </div>

                {{-- فیلد پیشوند (فقط در حالت خاص) --}}
                @if($strategy === 'prefix_incremental')
                    <div class="animate-in slide-in-from-top-2 duration-300">
                        <label class="{{ $labelClass }}">پیشوند دلخواه (Prefix)</label>
                        <div class="relative">
                            <input type="text" wire:model="prefix" class="{{ $inputClass }} pl-10 dir-ltr" placeholder="e.g: CLIENT">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <span class="text-lg font-mono">#</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">این عبارت قبل از شماره سریال مشتری قرار می‌گیرد.</p>
                    </div>
                @endif

                {{-- دکمه ذخیره --}}
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button wire:click="save"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 focus:ring-4 focus:ring-indigo-500/30 transition-all transform active:scale-95">
                        ذخیره تنظیمات
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
