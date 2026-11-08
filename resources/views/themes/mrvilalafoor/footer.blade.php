@php
    $globalSettings = [];
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $globalSettings = \Modules\Settings\Entities\Setting::pluck('value', 'key')->toArray();
        }
    } catch (\Throwable $e) {}

    // اطلاعات هویتی و برند از تنظیمات عمومی
    $appName = $globalSettings['app_name'] ?? $globalSettings['site_title'] ?? config('app.name', 'مستر ویلا لفور');
    $appLogo = isset($globalSettings['app_logo']) ? asset($globalSettings['app_logo']) : null;
    $siteDescription = $globalSettings['description'] ?? null;
    $footerText = $globalSettings['footer_text'] ?? null;

    // اطلاعات ارتباطی واقعی از تنظیمات سیستم (/settings#contact)
    $contactPhone = $globalSettings['contact_phone'] ?? $globalSettings['company_phone'] ?? $globalSettings['phone'] ?? null;
    $contactEmail = $globalSettings['contact_email'] ?? null;
    $address = $globalSettings['address'] ?? $globalSettings['identity_full_address'] ?? null;

    // تنظیمات چندمیزبانی
    $rentalMultiHostEnabled = false;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('property_settings')) {
            $rentalMultiHostEnabled = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_multi_host_enabled', 0);
        }
    } catch (\Throwable $e) {}

    // شهرهای دارای اقامتگاه فعال در سامانه
    $realDestinations = [];
    try {
        if (class_exists(\Modules\Properties\Entities\Property::class)) {
            $realDestinations = \Modules\Properties\Entities\Property::query()
                ->where('publication_status', 'published')
                ->where('approval_status', 'approved')
                ->where('listing_type', 'daily_rental')
                ->whereNotNull('city')
                ->select('city')
                ->distinct()
                ->pluck('city')
                ->filter()
                ->values()
                ->all();
        }
    } catch (\Throwable $e) {}

    // لیست مقاصد محبوب (اولویت با شهرهای فعال دیتابیس)
    $footerZones = !empty($realDestinations) ? $realDestinations : ['لفور', 'سوادکوه', 'شیرگاه', 'بابل'];
    $footerZones = array_slice(array_unique(array_merge($footerZones, ['لفور', 'سوادکوه', 'شیرگاه'])), 0, 4);

    // وضعیت ثبت‌نام میزبان جاری
    $isHostRegistered = false;
    if (auth()->check()) {
        try {
            if (class_exists(\Modules\Properties\Entities\PropertyHost::class)) {
                $isHostRegistered = \Modules\Properties\Entities\PropertyHost::where('user_id', auth()->id())->exists();
            }
        } catch (\Throwable $e) {}
    }
@endphp

<!-- ============================================================= -->
<!-- MR. VILA LAFOOR REAL DYNAMIC DESKTOP & MOBILE FOOTER          -->
<!-- ============================================================= -->
<footer class="border-t border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9]/50 dark:bg-zinc-950 mt-12 sm:mt-16 lg:mt-20 pt-12 pb-16 text-xs text-zinc-500 dark:text-zinc-400 transition-colors font-sans">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-8 space-y-10">
        
        {{-- Top Info Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 pb-10 border-b border-[#ECECEE] dark:border-zinc-800">
            
            {{-- Col 1: Brand & About --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-[#18181B] dark:text-zinc-100 font-black text-sm">
                    @if(!empty($appLogo))
                        <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-6 w-auto object-contain">
                    @else
                        <span class="w-2.5 h-2.5 rounded-full bg-[#007A5E] dark:bg-emerald-500"></span>
                    @endif
                    <span>{{ $appName }}</span>
                </div>
                <p class="text-[11px] leading-relaxed text-zinc-500 dark:text-zinc-400 font-sans">
                    {{ $siteDescription ?: 'سامانه رزرواسیون و اجاره آنلاین انواع اقامتگاه، کلبه‌های جنگلی و ویلا در شمال کشور با تضمین تطابق، پشتیبانی آنلاین و تحویل حضوری.' }}
                </p>
                @if(!empty($address))
                    <div class="flex items-start gap-1.5 text-[11px] text-zinc-400 leading-relaxed font-sans pt-1">
                        <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ $address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Popular Real Zones --}}
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-[#18181B] dark:text-zinc-200">محبوب‌ترین مقاصد</h4>
                <ul class="space-y-2 text-[11px] font-sans">
                    @foreach($footerZones as $zone)
                        <li>
                            <a href="{{ route('properties.index', ['search' => $zone]) }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">
                                اقامتگاه‌های {{ $zone }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('properties.index') }}" class="font-bold text-[#007A5E] dark:text-emerald-400 hover:underline">
                            همه اقامتگاه‌ها ←
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Multi-Host Marketplace & Services --}}
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-[#18181B] dark:text-zinc-200">میزبانی و خدمات</h4>
                <ul class="space-y-2 text-[11px] font-sans">
                    @if($rentalMultiHostEnabled && Route::has('user.properties.hosts.register'))
                        <li>
                            <a href="{{ $isHostRegistered ? route('user.properties.hosts.dashboard') : route('user.properties.hosts.register') }}" class="font-bold text-[#007A5E] dark:text-emerald-400 hover:underline">
                                {{ $isHostRegistered ? 'داشبورد مدیریت میزبانی' : 'ثبت اقامتگاه (میزبان شوید)' }}
                            </a>
                        </li>
                    @endif
                    <li><a href="{{ route('properties.index') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">کاوش و اجاره اقامتگاه</a></li>
                    @auth
                        <li><a href="{{ route('user.dashboard') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">پنل کاربری من</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">ورود / ثبت‌نام</a></li>
                    @endauth
                    <li><a href="#trust-guarantees-section" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">ضمانت تحویل و رویه تسویه</a></li>
                </ul>
            </div>

            {{-- Col 4: Support & Real Contact (/settings#contact) --}}
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-[#18181B] dark:text-zinc-200">پشتیبانی و ارتباط</h4>
                <p class="text-[11px] text-zinc-400 leading-relaxed font-sans">
                    پاسخگویی آنلاین و تلفنی در تمام روزهای هفته جهت راهنمایی رزرو و ورود مسافر.
                </p>

                @if(!empty($contactPhone))
                    <div>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 text-xs font-bold text-[#18181B] dark:text-zinc-200 shadow-2xs hover:border-[#007A5E] hover:text-[#007A5E] dark:hover:text-emerald-400 transition-all font-sans">
                            <svg class="w-4 h-4 text-[#007A5E] dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="font-sans dir-ltr">{{ $contactPhone }}</span>
                        </a>
                    </div>
                @endif

                @if(!empty($contactEmail))
                    <div>
                        <a href="mailto:{{ $contactEmail }}" class="inline-flex items-center gap-2 text-[11px] text-zinc-500 hover:text-[#007A5E] dark:text-zinc-400 dark:hover:text-emerald-400 transition-colors font-sans">
                            <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="dir-ltr">{{ $contactEmail }}</span>
                        </a>
                    </div>
                @endif
            </div>

        </div>

        {{-- Bottom Copyright --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-zinc-400 dark:text-zinc-400">
            <div>
                © {{ date('Y') }} {{ $appName }} — تمامی حقوق محفوظ است.
            </div>
            @if(!empty($footerText))
                <div class="flex items-center gap-2">
                    <span>{{ $footerText }}</span>
                </div>
            @endif
        </div>

    </div>
</footer>
