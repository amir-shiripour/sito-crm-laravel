@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 overflow-hidden";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 flex flex-wrap items-center justify-between gap-4";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-200 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800";
    $checkboxClass = "w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer transition-colors";
@endphp

<div class="space-y-8 font-sans">

    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- بخش ۱: تنظیمات اتصال وب‌سرویس جامع Vision ERP (نسخه ۳.۶.۱) --}}
    {{-- ========================================================================= --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">تنظیمات وب‌سرویس جامع Vision ERP (نسخه ۳.۶.۱)</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                            بخش ۱: اتصال و اتوماسیون
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">اطلاعات احراز هویت سه سطحی، کدهای پیش‌فرض صدور فاکتور و تنظیمات پس‌زمینه</p>
                </div>
            </div>

            <div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.defer="is_active" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    <span class="ms-3 text-xs font-bold text-gray-700 dark:text-gray-200">اتصال فعال</span>
                </label>
            </div>
        </div>

        <div class="p-6 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- آدرس سرور وب‌سرویس --}}
                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">آدرس وب‌سرویس SOAP ویژن (Endpoint URL)</label>
                    <input type="url" dir="ltr" wire:model.defer="server_url" placeholder="http://192.168.1.100:1004/WebService1.asmx" class="{{ $inputClass }}">
                    <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                        آدرس وب‌سرویس ارائه‌شده توسط شرکت ویژن به همراه پورت (معمولاً ۱۰۰۴ و نام سرویس WebService1.asmx).
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
                    <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 block">در صورتی که نمی‌خواهید تغییر دهید، خالی بگذارید.</span>
                </div>

                {{-- رمز ادمین تنظیمات شرکت PDA نسخه 3.6.1 --}}
                <div class="md:col-span-2 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20">
                    <label class="{{ $labelClass }} text-amber-900 dark:text-amber-200">
                        رمز ادمین تنظیمات شرکت PDA (adminPass - الزامی در نسخه ۳.۶.۱)
                    </label>
                    <input type="password" dir="ltr" wire:model.defer="admin_pass" placeholder="••••••••" class="{{ $inputClass }} border-amber-300 dark:border-amber-600/40">
                    <p class="text-[11px] text-amber-900/90 dark:text-amber-200/90 mt-1.5 leading-relaxed">
                        طبق آپدیت نسخه ۳.۶.۱ ویژن، متد لاگین علاوه بر نام کاربری و رمز، به رمز عبور ادمین شرکت در نرم‌افزار ویژن نیاز دارد. این فیلد به صورت کاملاً رمزنگاری‌شده در پایگاه داده محافظت می‌شود.
                    </p>
                </div>

                {{-- کد ویزیتور پیش‌فرض --}}
                <div>
                    <label class="{{ $labelClass }}">کد ویزیتور پیش‌فرض سفارشات (VisitorCode)</label>
                    <input type="text" dir="ltr" wire:model.defer="default_visitor_code" placeholder="مثال: 101" class="{{ $inputClass }}">
                    <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 block">کد پرسنل یا ویزیتور ثبت‌کننده فاکتور در ویژن</span>
                </div>

                {{-- کد انبار ویژن --}}
                <div>
                    <label class="{{ $labelClass }}">کد انبار ویژن (WarehouseCode)</label>
                    <input type="text" dir="ltr" wire:model.defer="default_warehouse_code" placeholder="مثال: 1" class="{{ $inputClass }}">
                    <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 block">کد انباری در ویژن که سفارشات سایت از آن حواله می‌شوند</span>
                </div>

                {{-- کد مشتری پیش‌فرض فاکتور --}}
                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">کد مشتری عمومی / اینترنتی پیش‌فرض (Default CustomerCode)</label>
                    <input type="text" dir="ltr" wire:model.defer="default_customer_code" placeholder="مثال: 1000" class="{{ $inputClass }}">
                    <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                        در صورتی که مشتری ثبت‌شده در سایت فاقد کد طرف‌حساب اختصاصی در ویژن باشد، فاکتورها تحت این کد مشتری ثبت خواهند شد.
                    </p>
                </div>
            </div>

            {{-- تنظیمات اتوماسیون --}}
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">تنظیمات اتوماسیون و پس‌زمینه</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/60 transition-colors">
                        <input type="checkbox" wire:model.defer="auto_sync_stock" class="{{ $checkboxClass }} mt-0.5">
                        <div>
                            <span class="text-xs font-bold text-gray-900 dark:text-white block">به‌روزرسانی خودکار موجودی</span>
                            <span class="text-[11px] text-slate-600 dark:text-slate-300 block mt-0.5 leading-relaxed">
                                واکشی آخرین موجودی کالاها از انبار ویژن در بازه‌های زمانی منظم و اصلاح موجودی کاتالوگ سایت.
                            </span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/60 transition-colors">
                        <input type="checkbox" wire:model.defer="auto_dispatch_order" class="{{ $checkboxClass }} mt-0.5">
                        <div>
                            <span class="text-xs font-bold text-gray-900 dark:text-white block">صدور خودکار فاکتور در ویژن پس از پرداخت</span>
                            <span class="text-[11px] text-slate-600 dark:text-slate-300 block mt-0.5 leading-relaxed">
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
                        <span class="text-slate-600 dark:text-slate-300">آخرین همگام‌سازی:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $last_sync_at }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $last_sync_status === 'success' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' }}">
                            {{ $last_sync_status === 'success' ? 'موفق' : 'ناموفق' }}
                        </span>
                    </div>

                    @if($last_error_message)
                        <div class="text-rose-600 dark:text-rose-300 text-[11px] w-full">
                            خطا: {{ $last_error_message }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- پیام نتیجه تست اتصال یا ذخیره اتصال --}}
            @if($connectionMessage)
                <div class="p-4 rounded-xl text-xs {{ $connectionSuccess ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200' : 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200' }}">
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

            @if($connectionSaveMessage)
                <div class="p-4 rounded-xl text-xs flex items-center gap-2 {{ $connectionSaveSuccess ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200' : 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $connectionSaveMessage }}</span>
                </div>
            @endif

            {{-- دکمه‌های عملیات بخش اول (فقط مربوط به اتصال) --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button type="button" wire:click="testConnection" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs font-bold transition-all">
                    <svg class="w-4 h-4 animate-spin" wire:loading wire:target="testConnection" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <svg class="w-4 h-4" wire:loading.remove wire:target="testConnection" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span wire:loading.remove wire:target="testConnection">تست ارتباط با وب‌سرویس</span>
                    <span wire:loading wire:target="testConnection">در حال بررسی...</span>
                </button>

                <button type="button" wire:click="saveConnectionSettings" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all">
                    <svg class="w-4 h-4 animate-spin" wire:loading wire:target="saveConnectionSettings" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <svg class="w-4 h-4" wire:loading.remove wire:target="saveConnectionSettings" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span wire:loading.remove wire:target="saveConnectionSettings">ذخیره تنظیمات اتصال</span>
                    <span wire:loading wire:target="saveConnectionSettings">در حال ذخیره...</span>
                </button>
            </div>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- بخش ۲: لایه ۱: هاب قرنطینه و محدودهٔ کاتالوگ ویژن (جریان مرحله‌ای ۱ تا ۴) --}}
    {{-- ========================================================================= --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">لایه ۱: هاب قرنطینه و محدودهٔ کاتالوگ ویژن</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                            بخش ۲: مدیریت کاتالوگ و قیمت‌ها
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                        فقط گروه‌ها و زیرگروه‌های انتخابی شما به کاتالوگ سایت متصل و با تنظیمات قیمتی دلخواه همگام‌سازی می‌شوند.
                    </p>
                </div>
            </div>

            @if(!empty($cached_metadata))
                <span class="text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600/50">
                    {{ count($selected_groups) }} گروه فعال از {{ $cached_metadata['groups_count'] ?? 0 }}
                </span>
            @endif
        </div>

        <div class="p-6 space-y-8">

            {{-- پیام نتیجه عملیات همگام‌سازی داخل همین کارت --}}
            @if($syncMessage)
                <div class="p-4 rounded-xl text-xs flex items-start gap-3 {{ $syncSuccess ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200' : 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200' }}">
                    @if($syncSuccess)
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-600 dark:text-emerald-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @else
                        <svg class="w-4 h-4 flex-shrink-0 text-rose-600 dark:text-rose-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                    <p class="leading-relaxed font-medium">{{ $syncMessage }}</p>
                </div>
            @endif

            {{-- ================================================================= --}}
            {{-- گام ۱: کشف و بروزرسانی ساختار از ویژن --}}
            {{-- ================================================================= --}}
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/30 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold flex-shrink-0 mt-0.5">۱</span>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">گام اول: واکشی و بروزرسانی ساختار از ویژن</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">
                                هر زمان گروه جدیدی در ویژن اضافه شد یا کلاس‌های قیمت جدید تعریف کردید، این دکمه را بزنید تا لیست گروه‌ها، زیرگروه‌ها و کلاس‌های قیمت بروز شود (بدون ورود کالا).
                            </p>
                        </div>
                    </div>

                    <button type="button" wire:click="fetchDiscoveryMetadata" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex-shrink-0">
                        <svg class="w-4 h-4 animate-spin" wire:loading wire:target="fetchDiscoveryMetadata" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <svg class="w-4 h-4" wire:loading.remove wire:target="fetchDiscoveryMetadata" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zm0 4h16M9 4v16"/></svg>
                        <span wire:loading.remove wire:target="fetchDiscoveryMetadata">واکشی / بروزرسانی ساختار از ویژن</span>
                        <span wire:loading wire:target="fetchDiscoveryMetadata">در حال دریافت ساختار...</span>
                    </button>
                </div>

                @if(!empty($cached_metadata))
                    @php
                        $allGroups = $cached_metadata['groups'] ?? [];
                        $groupsWithSubsCount = count(array_filter($allGroups, fn($g) => !empty($g['subgroups'])));
                        $activeGroup = $this->activeGroupData;
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <div class="p-4 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                                {{ $cached_metadata['groups_count'] ?? 0 }}
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900 dark:text-white">گروه‌های اصلی ویژن</div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">{{ count($selected_groups) }} گروه فعال‌شده</div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/40 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-sm">
                                {{ $cached_metadata['subgroups_count'] ?? 0 }}
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900 dark:text-white">زیرگروه‌ها در {{ $groupsWithSubsCount }} گروه</div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">{{ count($selected_subgroups) }} زیرگروه فعال‌شده</div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                                {{ $cached_metadata['total_goods_count'] ?? 0 }}
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900 dark:text-white">کل اقلام در سرور ویژن</div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">آماده فیلتر و همگام‌سازی</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ================================================================= --}}
            {{-- گام ۲: انتخاب محدوده قرنطینه (گروه‌ها و زیرگروه‌ها) + همگام‌سازی برندها و دسته‌ها --}}
            {{-- ================================================================= --}}
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/30 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold flex-shrink-0 mt-0.5">۲</span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">گام دوم: انتخاب گروه‌ها/زیرگروه‌های مجاز و ثبت برندها و دسته‌ها</h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    ذخیره خودکار تیک‌ها
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">
                                گروه‌ها (برندها) و زیرگروه‌ها (دسته‌بندی‌ها) را انتخاب کنید. انتخاب تیک‌ها بلافاصله ذخیره می‌شود؛ سپس دکمه مقابل را بزنید تا برندها و دسته‌ها در سایت ساخته شوند.
                            </p>
                        </div>
                    </div>

                    <button type="button" wire:click="syncBrandsAndCategoriesNow" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-500/20 transition-all flex-shrink-0">
                        <svg class="w-4 h-4 animate-spin" wire:loading wire:target="syncBrandsAndCategoriesNow" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <svg class="w-4 h-4" wire:loading.remove wire:target="syncBrandsAndCategoriesNow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span wire:loading.remove wire:target="syncBrandsAndCategoriesNow">ثبت و همگام‌سازی برندها و دسته‌ها</span>
                        <span wire:loading wire:target="syncBrandsAndCategoriesNow">در حال ثبت ساختار...</span>
                    </button>
                </div>

                @if(!empty($cached_metadata))
                    <div class="space-y-4 pt-2">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-xs text-slate-600 dark:text-slate-300">
                                با کلیک روی هر گروه در ستون راست، زیرگروه‌ها و جزئیات آن در ستون چپ باز می‌شود.
                            </p>

                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="selectAllGroups" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 transition-colors">
                                    انتخاب همه گروه‌ها
                                </button>
                                <button type="button" wire:click="deselectAllGroups" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 transition-colors">
                                    لغو همه
                                </button>
                            </div>
                        </div>

                        {{-- کاوشگر دو ستونه --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800/60 overflow-hidden shadow-sm">

                            {{-- ستون راست: لیست گروه‌های اصلی (Width: 6/12) --}}
                            <div class="lg:col-span-6 border-b lg:border-b-0 lg:border-e border-gray-200 dark:border-gray-700/80 flex flex-col h-[500px]">
                                {{-- نوار ابزار جستجو و تب‌های فیلتر --}}
                                <div class="p-3.5 bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700/60 space-y-2.5">
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.200ms="groupSearch" placeholder="جستجو در نام یا کد گروه..." class="{{ $inputClass }} ps-9 text-xs">
                                        <span class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </span>
                                        @if($groupSearch)
                                            <button type="button" wire:click="$set('groupSearch', '')" class="absolute inset-y-0 end-0 flex items-center pe-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- تب‌های دسته‌بندی گروه‌ها --}}
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" wire:click="$set('groupFilterTab', 'all')"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $groupFilterTab === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-gray-200/60 dark:hover:bg-gray-700/60' }}">
                                            همه ({{ $cached_metadata['groups_count'] ?? 0 }})
                                        </button>

                                        <button type="button" wire:click="$set('groupFilterTab', 'selected')"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $groupFilterTab === 'selected' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-gray-200/60 dark:hover:bg-gray-700/60' }}">
                                            فعال‌شده‌ها ({{ count($selected_groups) }})
                                        </button>

                                        <button type="button" wire:click="$set('groupFilterTab', 'with_subs')"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $groupFilterTab === 'with_subs' ? 'bg-purple-600 text-white shadow-sm' : 'text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 dark:hover:bg-purple-900/40' }}">
                                            دارای زیرگروه ({{ $groupsWithSubsCount }})
                                        </button>
                                    </div>
                                </div>

                                {{-- فهرست اسکرول‌شونده گروه‌ها --}}
                                <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/50">
                                    @forelse($this->filteredGroups as $group)
                                        @php
                                            $gCode = (string)($group['CodeG'] ?? '');
                                            $gName = (string)($group['NameG'] ?? '');
                                            $subs = $group['subgroups'] ?? [];
                                            $subsCount = count($subs);
                                            $isSelected = in_array($gCode, $selected_groups, true);
                                            $isActive = ($activeGroup && (string)$activeGroup['CodeG'] === $gCode);
                                        @endphp

                                        <div class="flex items-center justify-between p-3 transition-colors {{ $isActive ? 'bg-indigo-50/80 dark:bg-indigo-500/15 border-s-4 border-indigo-600 dark:border-indigo-400' : 'hover:bg-gray-50 dark:hover:bg-gray-800/60' }}">
                                            <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                                <input type="checkbox" wire:click="toggleGroup('{{ $gCode }}')" {{ $isSelected ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                                <button type="button" wire:click="selectGroupForInspection('{{ $gCode }}')" class="flex-1 text-start truncate">
                                                    <span class="text-xs font-bold block truncate {{ $isActive ? 'text-indigo-900 dark:text-indigo-200' : 'text-gray-900 dark:text-white' }}">
                                                        {{ $gName }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-600 dark:text-slate-300 block mt-0.5">
                                                        کد ویژن: {{ $gCode }}
                                                    </span>
                                                </button>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                @if($subsCount > 0)
                                                    <button type="button" wire:click="selectGroupForInspection('{{ $gCode }}')"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60">
                                                        <span>{{ $subsCount }} زیرگروه</span>
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                                    </button>
                                                @endif

                                                <button type="button" wire:click="selectGroupForInspection('{{ $gCode }}')" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                    <svg class="w-4 h-4 {{ $isActive ? 'text-indigo-600 dark:text-indigo-400 rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-8 text-center text-xs text-slate-600 dark:text-slate-300">
                                            گروهی با مشخصات واردشده یافت نشد.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- ستون چپ: پنل جزئیات و زیرگروه‌های گروه انتخابی (Width: 6/12) --}}
                            <div class="lg:col-span-6 flex flex-col h-[500px] bg-gray-50/30 dark:bg-gray-900/20">
                                @if($activeGroup)
                                    @php
                                        $actCode = (string)($activeGroup['CodeG'] ?? '');
                                        $actName = (string)($activeGroup['NameG'] ?? '');
                                        $actSubs = $activeGroup['subgroups'] ?? [];
                                        $isActSelected = in_array($actCode, $selected_groups, true);
                                        $actSubsCount = count($actSubs);
                                    @endphp

                                    <div class="p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700/80 flex items-center justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full {{ $isActSelected ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                                <h5 class="text-sm font-bold text-gray-900 dark:text-white">{{ $actName }}</h5>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                                    کد: {{ $actCode }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1">
                                                {{ $actSubsCount > 0 ? "دارای {$actSubsCount} زیرگروه تفکیک‌شده در سیستم ویژن" : "فاقد زیرگروه مجزا (کالاها مستقیماً ذیل گروه اصلی هستند)" }}
                                            </p>
                                        </div>

                                        <div>
                                            <button type="button" wire:click="toggleGroup('{{ $actCode }}')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $isActSelected ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40 hover:bg-rose-100' : 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700' }}">
                                                @if($isActSelected)
                                                    <span>غیرفعال کردن گروه</span>
                                                @else
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>فعال‌سازی در سایت</span>
                                                @endif
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                                        @if($actSubsCount > 0)
                                            <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700/60">
                                                <span class="text-xs font-bold text-purple-900 dark:text-purple-200">
                                                    فهرست زیرگروه‌های «{{ $actName }}»:
                                                </span>
                                                <button type="button" wire:click="toggleAllSubgroupsOfGroup('{{ $actCode }}')"
                                                        class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                    تغییر وضعیت همه زیرگروه‌ها
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-1 gap-2.5">
                                                @foreach($actSubs as $sub)
                                                    @php
                                                        $sCode = (string)($sub['CodeG'] ?? '');
                                                        $sName = (string)($sub['NameG'] ?? '');
                                                        $level = (int)($sub['LevelH'] ?? 1);
                                                        $isSubSel = in_array($sCode, $selected_subgroups, true);
                                                        $marginClass = match($level) {
                                                            2 => 'ms-5 border-s-2 border-s-purple-400',
                                                            3 => 'ms-10 border-s-2 border-s-purple-500',
                                                            4 => 'ms-14 border-s-2 border-s-purple-600',
                                                            default => 'ms-0',
                                                        };
                                                    @endphp

                                                    <label class="flex items-center justify-between p-3 rounded-xl border transition-all cursor-pointer {{ $marginClass }} {{ $isSubSel ? 'bg-purple-50/80 dark:bg-purple-950/30 border-purple-300 dark:border-purple-800/70 shadow-sm' : 'bg-white dark:bg-gray-800/70 border-gray-200 dark:border-gray-700 hover:border-gray-300' }}">
                                                        <div class="flex items-center gap-3">
                                                            <input type="checkbox" wire:click="toggleSubgroup('{{ $sCode }}', '{{ $actCode }}')" {{ $isSubSel ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                                            <div>
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-xs font-bold block text-gray-900 dark:text-white">
                                                                        {{ $sName }}
                                                                    </span>
                                                                    @if($level > 1)
                                                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300">
                                                                            سطح {{ $level }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <span class="text-[10px] text-slate-600 dark:text-slate-300 block mt-0.5">
                                                                    کد زیرگروه: {{ $sCode }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isSubSel ? 'bg-purple-200 text-purple-900 dark:bg-purple-900/60 dark:text-purple-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                                            {{ $isSubSel ? 'فعال در همگام‌سازی' : 'غیرفعال' }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="h-full flex flex-col items-center justify-center text-center p-6 space-y-3">
                                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                    </svg>
                                                </div>
                                                <div class="max-w-xs">
                                                    <h6 class="text-xs font-bold text-gray-900 dark:text-white">این گروه فاقد زیرگروه مجزاست</h6>
                                                    <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                                        در سیستم حسابداری ویژن، کالاهای این گروه به‌طور مستقیم ذیل همین عنوان قرار دارند.
                                                    </p>
                                                </div>
                                                <div class="p-3 rounded-xl bg-slate-100/80 dark:bg-slate-800/80 text-[11px] text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                                    وضعیت:
                                                    @if($isActSelected)
                                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">کالاهای این گروه مجاز به ورود به سایت هستند.</span>
                                                    @else
                                                        <span class="font-bold text-slate-600 dark:text-slate-300">غیرفعال است و کالاهای آن وارد کاتالوگ نمی‌شوند.</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="h-full flex items-center justify-center text-xs text-slate-600 dark:text-slate-300 p-8 text-center">
                                        یک گروه را از ستون راست انتخاب کنید تا جزئیات و زیرگروه‌های آن نمایش داده شود.
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                @else
                    <div class="p-8 text-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700/80 space-y-2">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">ساختار کاتالوگ ویژن هنوز استعلام نشده است</h4>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                            ابتدا در گام اول روی دکمه «واکشی / بروزرسانی ساختار از ویژن» کلیک کنید تا فهرست گروه‌ها و زیرگروه‌ها نمایش داده شود.
                        </p>
                    </div>
                @endif
            </div>

            {{-- ================================================================= --}}
            {{-- گام ۳: تنظیم کلاس‌های قیمت، وضعیت انتشار و مدل انبارداری --}}
            {{-- ================================================================= --}}
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/30 space-y-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold flex-shrink-0 mt-0.5">۳</span>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">گام سوم: نگاشت کلاس‌های قیمت، وضعیت انتشار و انبارداری</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">
                                کلاس قیمت فروش، قیمت خط‌خورده و مدل موجودی را انتخاب کنید. <span class="font-bold text-indigo-600 dark:text-indigo-400">نیازی به رفرش صفحه نیست؛</span> هر تغییری با زدن دکمه «ذخیره» یا به محض زدن دکمه‌های همگام‌سازی گام ۴ به‌صورت خودکار ذخیره و اعمال می‌شود.
                            </p>
                        </div>
                    </div>

                    <button type="button" wire:click="saveMappingSettings" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30 text-xs font-bold transition-all flex-shrink-0">
                        <svg class="w-4 h-4 animate-spin" wire:loading wire:target="saveMappingSettings" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <svg class="w-4 h-4" wire:loading.remove wire:target="saveMappingSettings" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span wire:loading.remove wire:target="saveMappingSettings">ذخیره تنظیمات قیمت و انبار</span>
                        <span wire:loading wire:target="saveMappingSettings">در حال ذخیره...</span>
                    </button>
                </div>

                @if($mappingSaveMessage)
                    <div class="p-3.5 rounded-xl text-xs flex items-center gap-2 {{ $mappingSaveSuccess ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200' : 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $mappingSaveMessage }}</span>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- کلاس قیمت فروش اصلی --}}
                    <div>
                        <label class="{{ $labelClass }}">کلاس قیمت فروش اصلی سایت</label>
                        <select wire:model.defer="price_field" class="{{ $inputClass }}">
                            @if(!empty($cached_metadata['price_fields']))
                                @foreach($cached_metadata['price_fields'] as $key => $title)
                                    <option value="{{ $key }}">{{ $title }} ({{ $key }})</option>
                                @endforeach
                            @else
                                <option value="Pricef1">کلاس قیمت ۱ (عمده / پیش‌فرض)</option>
                                <option value="Pricef2">کلاس قیمت ۲ (همکار)</option>
                                <option value="Pricef3">کلاس قیمت ۳</option>
                                <option value="PriceF">قیمت مصرف‌کننده (PriceF)</option>
                            @endif
                        </select>
                        <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 block">ملاک قیمت نهایی و قابل پرداخت توسط خریدار</span>
                    </div>

                    {{-- قیمت خط‌خورده / مصرف‌کننده --}}
                    <div>
                        <label class="{{ $labelClass }}">قیمت خط‌خورده / مصرف‌کننده (اختیاری)</label>
                        <select wire:model.defer="compare_price_field" class="{{ $inputClass }}">
                            <option value="">بدون قیمت خط‌خورده</option>
                            @if(!empty($cached_metadata['price_fields']))
                                @foreach($cached_metadata['price_fields'] as $key => $title)
                                    <option value="{{ $key }}">{{ $title }} ({{ $key }})</option>
                                @endforeach
                            @else
                                <option value="PriceF">قیمت مصرف‌کننده ویژن (PriceF)</option>
                                <option value="Pricef1">کلاس قیمت ۱</option>
                            @endif
                        </select>
                        <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 block">در صورت بیشتر بودن، به عنوان قیمت پایه و قیمت فروش به عنوان قیمت تخفیف‌دار ثبت می‌شود</span>
                    </div>

                    {{-- وضعیت اولیه کالاهای جدید --}}
                    <div>
                        <label class="{{ $labelClass }}">وضعیت کالاهای جدید هنگام ورود</label>
                        <select wire:model.defer="initial_product_status" class="{{ $inputClass }}">
                            <option value="draft">پیش‌نویس (غیرفعال جهت بررسی و تکمیل محتوا)</option>
                            <option value="published">فعال و آماده فروش مستقیم</option>
                        </select>
                        <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 block">جلوگیری از انتشار ناخواسته پیش از افزودن تصاویر و سئو</span>
                    </div>
                </div>

                {{-- بخش تفکیک انبارداری و مدل‌های موجودی --}}
                <div class="pt-4 border-t border-slate-200/80 dark:border-slate-700/80 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <h5 class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            مدیریت انبارداری و مدل‌های موجودی ویژن
                        </h5>

                        @if($isWmsActive)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                سیستم انبارداری (WMS) فعال است
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                سیستم انبارداری ساده (موجودی مستقیم در محصولات)
                            </span>
                        @endif
                    </div>

                    @if($isWmsActive)
                        <div class="p-3.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 text-xs text-emerald-900 dark:text-emerald-200 leading-relaxed">
                            <strong>معماری انبارداری پیشرفته:</strong> موجودی دریافتی از ویژن مستقیماً در انبار اختصاصی انتخابی شما در <a href="{{ route('user.market.vendor.warehouses.index') }}" target="_blank" class="underline font-bold text-emerald-700 dark:text-emerald-300">«انبارهای من» (/user/market/vendor/warehouses)</a> ذخیره شده و تراکنش تعدیل انبار (Warehouse Transaction) به صورت رسمی ثبت می‌گردد.
                        </div>
                    @else
                        <div class="p-3.5 rounded-xl bg-slate-100/70 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-200 leading-relaxed">
                            <strong>معماری موجودی ساده:</strong> چون گزینه «فعال‌سازی سیستم انبارداری (WMS) مجزا» در تنظیمات اصلی مارکت غیرفعال است، موجودی اقلام مستقیماً در فیلد موجودی بخش <a href="{{ route('user.market.vendor.products.index') }}" target="_blank" class="underline font-bold text-indigo-600 dark:text-indigo-400">«محصولات و تنوع‌ها» (/user/market/vendor/products)</a> قرار می‌گیرد.
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        {{-- مدل موجودی ویژن --}}
                        <div>
                            <label class="{{ $labelClass }}">مدل محاسبه موجودی از ویژن</label>
                            <select wire:model.defer="stock_field" class="{{ $inputClass }}">
                                <option value="AllT">مجموع کل موجودی کالا (AllT - استاندارد)</option>
                                <option value="SlaveT">موجودی جزء / بسته فرعی (SlaveT)</option>
                                <option value="MasterT">موجودی عمده / کارتن اصلی (MasterT)</option>
                                <option value="Sahmieh">موجودی سهمیه‌بندی آنلاین (Sahmieh)</option>
                            </select>
                            <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 block">فیلد مبنای دریافت تعداد موجودی از اقلام وب‌سرویس</span>
                        </div>

                        {{-- انبار مقصد در مارکت (WMS) --}}
                        @if($isWmsActive)
                            <div>
                                <label class="{{ $labelClass }}">انبار مقصد در مارکت (/user/market/vendor/warehouses)</label>
                                <select wire:model.defer="crm_warehouse_id" class="{{ $inputClass }}">
                                    <option value="">پیش‌فرض سیستم (اولین انبار فعال فروشگاه)</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                    @endforeach
                                </select>
                                <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 block">انباری که موجودی فیزیکی و آنلاین به آن اضافه می‌شود</span>
                            </div>
                        @else
                            <div>
                                <label class="{{ $labelClass }}">نحوه اعمال موجودی</label>
                                <input type="text" disabled value="مستقیم در موجودی محصولات فروشنده" class="{{ $inputClass }} bg-gray-100 dark:bg-gray-800 opacity-80 cursor-not-allowed">
                                <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 block">در حالت بدون WMS، موجودی مستقیماً روی تنوع اعمال می‌شود</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ================================================================= --}}
            {{-- گام ۴: اجرای همگام‌سازی کالاها، قیمت‌ها و موجودی --}}
            {{-- ================================================================= --}}
            <div class="p-5 rounded-2xl border border-indigo-200/80 dark:border-indigo-500/30 bg-indigo-50/40 dark:bg-indigo-950/20 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold flex-shrink-0 mt-0.5">۴</span>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">گام چهارم: اجرای همگام‌سازی کالاها، قیمت‌ها و موجودی</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">
                            بسته به نیاز خود یکی از دو عملیات زیر را انتخاب کنید. با کلیک روی هر دکمه، تنظیمات کلاس قیمت و انبار گام ۳ به‌صورت خودکار ذخیره و بلافاصله اعمال می‌شوند.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- دکمه بروزرسانی کامل کاتالوگ --}}
                    <div class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex flex-col justify-between gap-4">
                        <div>
                            <div class="text-xs font-bold text-gray-900 dark:text-white">همگام‌سازی کامل کاتالوگ گروه‌های فعال</div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                مناسب برای <strong>ورود کالاهای جدید</strong>، ایجاد دسته‌بندی/برند و بروزرسانی کامل اطلاعات، قیمت‌ها و موجودی کالاهای محدوده قرنطینه (زمان اجرا: حدود ۱ تا ۲ دقیقه).
                            </p>
                        </div>
                        <button type="button" wire:click="syncSelectedCatalog" wire:loading.attr="disabled" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all">
                            <svg class="w-4 h-4 animate-spin" wire:loading wire:target="syncSelectedCatalog" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <svg class="w-4 h-4" wire:loading.remove wire:target="syncSelectedCatalog" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span wire:loading.remove wire:target="syncSelectedCatalog">همگام‌سازی کامل کالاها، قیمت‌ها و موجودی</span>
                            <span wire:loading wire:target="syncSelectedCatalog">در حال پیمایش کاتالوگ ویژن (حدود ۱ تا ۲ دقیقه)...</span>
                        </button>
                    </div>

                    {{-- دکمه بروزرسانی سریع قیمت‌ها و موجودی --}}
                    <div class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex flex-col justify-between gap-4">
                        <div>
                            <div class="text-xs font-bold text-gray-900 dark:text-white">بروزرسانی سریع قیمت‌ها و موجودی انبار</div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                مناسب برای زمانی که <strong>کلاس قیمت جدید انتخاب کرده‌اید</strong> یا فقط می‌خواهید قیمت و موجودی کالاهای ثبت‌شده فعلی در سایت را سریعاً از ویژن بروز کنید.
                            </p>
                        </div>
                        <button type="button" wire:click="syncStockNow" wire:loading.attr="disabled" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30 text-xs font-bold transition-all">
                            <svg class="w-4 h-4 animate-spin" wire:loading wire:target="syncStockNow" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <svg class="w-4 h-4" wire:loading.remove wire:target="syncStockNow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span wire:loading.remove wire:target="syncStockNow">بروزرسانی سریع قیمت‌ها و موجودی</span>
                            <span wire:loading wire:target="syncStockNow">در حال بروزرسانی قیمت و موجودی...</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
