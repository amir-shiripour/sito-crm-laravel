@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 overflow-hidden";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800";
    $checkboxClass = "w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer transition-colors";
@endphp

<div class="space-y-6">

    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-300 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- وضعیت اتصال و راهنما --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">تنظیمات وب‌سرویس جامع Vision ERP (نسخه ۳.۶.۱)</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">اتصال زنده مارکت به نرم‌افزار حسابداری و انبارداری ویژن با احراز هویت سه سطحی</p>
                </div>
            </div>

            <div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.defer="is_active" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    <span class="ms-3 text-xs font-bold text-gray-700 dark:text-gray-300">اتصال فعال</span>
                </label>
            </div>
        </div>

        <div class="p-6 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- آدرس سرور وب‌سرویس --}}
                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">آدرس وب‌سرویس SOAP ویژن (Endpoint URL)</label>
                    <input type="url" dir="ltr" wire:model.defer="server_url" placeholder="http://192.168.1.100:1004/WebService1.asmx" class="{{ $inputClass }}">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
                        آدرس وب‌سرویس ارائه‌شده توسط شرکت ویژن به همراه پورت (معمولاً ۱۰-۰۴ و نام سرویس WebService1.asmx).
                    </p>
                </div>

                {{-- نام کاربری --}}
                <div>
                    <label class="{{ $labelClass }}">نام کاربری وب‌سرویس (user)</label>
                    <input type="text" dir="ltr" wire:model.defer="ws_username" placeholder="مثال: vision_api" class="{{ $inputClass }}">
                </div>

                {{-- کلمه عبور کاربر --}}
                <div>
                    <label class="{{ $labelClass }}">کلمه عبور وب‌سرویس (password)</label>
                    <input type="password" dir="ltr" wire:model.defer="ws_password" placeholder="••••••••" class="{{ $inputClass }}">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">در صورتی که نمی‌خواهید تغییر دهید، خالی بگذارید.</span>
                </div>

                {{-- رمز ادمین تنظیمات شرکت PDA نسخه 3.6.1 --}}
                <div class="md:col-span-2 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20">
                    <label class="{{ $labelClass }} text-amber-900 dark:text-amber-200">
                        رمز ادمین تنظیمات شرکت PDA (adminPass - الزامی در نسخه ۳.۶.۱)
                    </label>
                    <input type="password" dir="ltr" wire:model.defer="admin_pass" placeholder="••••••••" class="{{ $inputClass }} border-amber-300 dark:border-amber-600/40">
                    <p class="text-[11px] text-amber-800/80 dark:text-amber-300/80 mt-1.5 leading-relaxed">
                        طبق آپدیت نسخه ۳.۶.۱ ویژن، متد لاگین علاوه بر نام کاربری و رمز، به رمز عبور ادمین شرکت در نرم‌افزار ویژن نیاز دارد. این فیلد به صورت کاملاً رمزنگاری‌شده در پایگاه داده محافظت می‌شود.
                    </p>
                </div>

                {{-- کد ویزیتور پیش‌فرض --}}
                <div>
                    <label class="{{ $labelClass }}">کد ویزیتور پیش‌فرض سفارشات (VisitorCode)</label>
                    <input type="text" dir="ltr" wire:model.defer="default_visitor_code" placeholder="مثال: 101" class="{{ $inputClass }}">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">کد پرسنل یا ویزیتور ثبت‌کننده فاکتور در ویژن</span>
                </div>

                {{-- کد انبار ویژن --}}
                <div>
                    <label class="{{ $labelClass }}">کد انبار ویژن (WarehouseCode)</label>
                    <input type="text" dir="ltr" wire:model.defer="default_warehouse_code" placeholder="مثال: 1" class="{{ $inputClass }}">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">کد انباری در ویژن که سفارشات سایت از آن حواله می‌شوند</span>
                </div>

                {{-- کد مشتری پیش‌فرض فاکتور --}}
                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">کد مشتری عمومی / اینترنتی پیش‌فرض (Default CustomerCode)</label>
                    <input type="text" dir="ltr" wire:model.defer="default_customer_code" placeholder="مثال: 1000" class="{{ $inputClass }}">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
                        در صورتی که مشتری ثبت‌شده در سایت فاقد کد طرف‌حساب اختصاصی در ویژن باشد، فاکتورها تحت این کد مشتری ثبت خواهند شد.
                    </p>
                </div>
            </div>

            {{-- تنظیمات اتوماسیون --}}
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">تنظیمات اتوماسیون و پس‌زمینه</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/60 transition-colors">
                        <input type="checkbox" wire:model.defer="auto_sync_stock" class="{{ $checkboxClass }} mt-0.5">
                        <div>
                            <span class="text-xs font-bold text-gray-900 dark:text-white block">به‌روزرسانی خودکار موجودی</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5 leading-relaxed">
                                واکشی آخرین موجودی کالاها از انبار ویژن در بازه‌های زمانی منظم و اصلاح موجودی کاتالوگ سایت.
                            </span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/60 transition-colors">
                        <input type="checkbox" wire:model.defer="auto_dispatch_order" class="{{ $checkboxClass }} mt-0.5">
                        <div>
                            <span class="text-xs font-bold text-gray-900 dark:text-white block">صدور خودکار فاکتور در ویژن پس از پرداخت</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5 leading-relaxed">
                                بلافاصله پس از پرداخت موفق سفارش توسط مشتری، اقلام به وب‌سرویس ارسال و شماره فاکتور مالی ذخیره می‌شود.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- وضعیت آخرین هماهنگ‌سازی --}}
            @if($last_sync_at)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-500 dark:text-slate-400">آخرین همگام‌سازی:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $last_sync_at }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $last_sync_status === 'success' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' }}">
                            {{ $last_sync_status === 'success' ? 'موفق' : 'ناموفق' }}
                        </span>
                    </div>

                    @if($last_error_message)
                        <div class="text-rose-600 dark:text-rose-400 text-[11px] w-full">
                            خطا: {{ $last_error_message }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- پیام نتیجه تست اتصال یا هماهنگ‌سازی --}}
            @if($connectionMessage)
                <div class="p-4 rounded-xl text-xs {{ $connectionSuccess ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-300' }}">
                    <div class="font-bold flex items-center gap-2">
                        @if($connectionSuccess)
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            اتصال به وب‌سرویس موفقیت‌آمیز بود
                        @else
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            خطا در برقراری اتصال
                        @endif
                    </div>
                    <p class="mt-1 leading-relaxed">{{ $connectionMessage }}</p>
                </div>
            @endif

            @if($syncMessage)
                <div class="p-4 rounded-xl text-xs {{ $syncSuccess ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-300' }}">
                    <p class="leading-relaxed">{{ $syncMessage }}</p>
                </div>
            @endif

            {{-- دکمه‌های عملیات --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-2">
                    {{-- دکمه ذخیره تنظیمات --}}
                    <button type="button" wire:click="saveSettings" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/25 transition-all">
                        <span wire:loading.remove wire:target="saveSettings">ذخیره تنظیمات</span>
                        <span wire:loading wire:target="saveSettings">در حال ذخیره...</span>
                    </button>

                    {{-- دکمه تست اتصال آنی --}}
                    <button type="button" wire:click="testConnection" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs font-bold transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span wire:loading.remove wire:target="testConnection">تست ارتباط با وب‌سرویس</span>
                        <span wire:loading wire:target="testConnection">در حال بررسی...</span>
                    </button>
                </div>

                {{-- عملیات دستی --}}
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="syncStockNow" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30 text-xs font-bold transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span wire:loading.remove wire:target="syncStockNow">همگام‌سازی دستی موجودی</span>
                        <span wire:loading wire:target="syncStockNow">در حال واکشی...</span>
                    </button>

                    <button type="button" wire:click="syncBaseCatalog(1)" wire:loading.attr="disabled" title="تطبیق کدهای کالا بر اساس کاتالوگ ویژن" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium transition-all">
                        <span>تطبیق کاتالوگ کالا</span>
                    </button>

                    <button type="button" wire:click="syncBaseCatalog(2)" wire:loading.attr="disabled" title="تطبیق مشتریان با شماره تماس یا کد ملی" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium transition-all">
                        <span>تطبیق مشتریان</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
