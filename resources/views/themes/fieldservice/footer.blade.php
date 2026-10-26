<footer class="bg-slate-50 dark:bg-slate-950 border-t border-slate-200/80 dark:border-slate-800 transition-colors duration-300 font-sans relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 pb-4 sm:pt-10 sm:pb-6 lg:pt-14 lg:pb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10">

            {{-- ستون ۱: درباره نصاب‌باشی --}}
            <div class="space-y-3.5">
                <div class="flex items-center gap-2.5">
                    @if(!empty($appLogo))
                        <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-9 w-auto rounded-xl">
                    @else
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-800 flex items-center justify-center text-white shadow-md shadow-indigo-600/25 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    @endif
                    <span class="font-black text-base text-slate-900 dark:text-white font-sans">{{ $appName }}</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                    سامانه هوشمند اعزام تکنسین، عیب‌یابی و سرویس تاسیسات ساختمانی و صنعتی. پوشش شبکه‌ای سراسر استان مازندران با اعزام راداری سریع، نرخ مصوب و ضمانت کتبی ۹۰ روزه خدمات و قطعات.
                </p>
                <div class="pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 font-sans">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        پرداخت امن امانی (Escrow)
                    </span>
                </div>
            </div>

            {{-- ستون ۲: خدمات و دسته‌بندی‌ها --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-3.5 rounded-full bg-indigo-600 dark:bg-indigo-400 shrink-0"></span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white font-sans">
                        خدمات تخصصی
                    </h3>
                </div>
                <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300 font-sans">
                    <li><a href="{{ route('fieldservice.customer') }}?type=package" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">تعمیر و سرویس پکیج شوفاژ دیواری</a></li>
                    <li><a href="{{ route('fieldservice.customer') }}?type=split_ac" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">نصب و شارژ گاز کولر گازی و اسپلیت</a></li>
                    <li><a href="{{ route('fieldservice.customer') }}?type=chiller" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">تعمیرات چیلر تراکمی و جذبی ساختمانی</a></li>
                    <li><a href="{{ route('fieldservice.customer') }}?type=boiler_room" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">نگهداری و تنظیم مشعل و موتورخانه مرکزی</a></li>
                    <li><a href="{{ route('fieldservice.customer') }}?type=cold_storage" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">سرویس سردخانه صنعتی و تجاری</a></li>
                    <li><a href="{{ route('fieldservice.customer') }}?type=ducted_split" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">تعمیر و عیب‌یابی داکت اسپلیت اینورتر</a></li>
                </ul>
            </div>

            {{-- ستون ۳: دسترسی سریع و پرتال‌ها (استایل یکدست و بدون لینک ناهماهنگ) --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-3.5 rounded-full bg-indigo-600 dark:bg-indigo-400 shrink-0"></span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white font-sans">
                        پرتال‌ها و دسترسی سریع
                    </h3>
                </div>
                <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300 font-sans">
                    <li><a href="{{ route('fieldservice.customer') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پرتال مشتریان (ثبت آنلاین سفارش)</a></li>
                    <li><a href="{{ route('fieldservice.technician') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پرتال متخصصان (ورود تکنسین‌ها)</a></li>
                    <li><a href="{{ route('fieldservice.b2b') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پرتال سازمانی و مدیران مجتمع (B2B)</a></li>
                    <li><a href="{{ route('fieldservice.customer') }}#lookup" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیگیری سفارش و فاکتور دیجیتال</a></li>
                    <li><a href="{{ route('register', ['role' => 'technician']) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">ثبت‌نام و همکاری تکنسین‌های جدید</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">ورود همکاران فروشگاهی و تامین قطعات</a></li>
                </ul>
            </div>

            {{-- ستون ۴: ارتباط و پشتیبانی ۲۴ ساعته --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-3.5 rounded-full bg-indigo-600 dark:bg-indigo-400 shrink-0"></span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white font-sans">
                        پشتیبانی و دفتر مرکزی
                    </h3>
                </div>
                <div class="space-y-3 text-xs text-slate-600 dark:text-slate-300 font-sans">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>مازندران، بابل، میدان باغ فردوس، ساختمان مرکزی نصاب‌باشی</span>
                    </div>

                    @if(!empty($supportPhone))
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <a href="tel:{{ $supportPhone }}" dir="ltr" class="font-bold text-slate-900 dark:text-white font-sans hover:text-indigo-600 transition-colors">{{ $supportPhone }}</a>
                    </div>
                    @endif

                    <div class="p-3 rounded-xl bg-indigo-50/70 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 text-[11px] leading-relaxed text-indigo-950 dark:text-indigo-300">
                        <span class="font-bold">تعرفه مصوب ایاب و ذهاب:</span> هزینه کارشناسی و حضور تکنسین در کلیه نقاط حوزه شهری فقط {{ number_format($baseTravelFee ?? 60000) }} تومان می‌باشد.
                    </div>
                </div>
            </div>

        </div>

        {{-- نوار کپی‌رایت --}}
        <div class="mt-8 pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400 font-sans">
            <p>{{ $footerText ?? 'تمامی حقوق مادی و معنوی برای سامانه نصاب‌باشی محفوظ است.' }}</p>
            <div class="flex items-center gap-3 text-[11px]">
                <span>گارانتی رسمی ۹۰ روزه</span>
                <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                <span>پرداخت امن و تضمین کیفیت</span>
                <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                <span>پشتیبانی شبانه‌روزی</span>
            </div>
        </div>
    </div>
</footer>
