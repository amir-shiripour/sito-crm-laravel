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
    $footerZones = array_slice(array_unique(array_merge($footerZones, ['لفور', 'سوادکوه', 'شیرگاه', 'بابل'])), 0, 5);

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
<!-- MR. VILA LAFOOR MODERN RESPONSIVE FOOTER (DESKTOP & MOBILE)   -->
<!-- ============================================================= -->
<style>
    footer.mrvila-footer {
        position: relative;
        z-index: 1 !important;
    }
    @media (max-width: 1023px) {
        footer.mrvila-footer {
            padding-bottom: 6.5rem !important;
        }
    }
</style>
<footer class="mrvila-footer border-t border-[#ECECEE] dark:border-zinc-800 bg-white/70 dark:bg-zinc-950 mt-8 sm:mt-12 lg:mt-16 pt-8 sm:pt-12 pb-24 md:pb-12 text-zinc-600 dark:text-zinc-300 transition-colors font-sans relative overflow-hidden">
    
    {{-- Decorative Ambient Glow --}}
    <div class="absolute -top-32 right-1/4 w-96 h-96 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-10 sm:space-y-12">
        
        {{-- Top Bar: Brand, Tagline & Back To Top Action --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-8 border-b border-[#ECECEE] dark:border-zinc-800">
            <div class="flex items-center gap-3">
                @if(!empty($appLogo))
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-8 w-auto object-contain">
                @else
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center text-[#007A5E] dark:text-emerald-400 font-black text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </span>
                @endif
                <div>
                    <h3 class="font-black text-base sm:text-lg text-[#18181B] dark:text-zinc-100 font-sans tracking-tight">
                        {{ $appName }}
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 font-sans mt-0.5">
                        سامانه جامع رزرو آنلاین اقامتگاه، کلبه‌های جنگلی و ویلا در شمال کشور
                    </p>
                </div>
            </div>

            {{-- Smooth Back To Top Button --}}
            <button type="button" 
                    onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                    class="group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-[#007A5E] dark:hover:border-emerald-500 text-xs font-bold text-zinc-700 dark:text-zinc-200 transition-all shadow-2xs hover:shadow-sm">
                <span>بازگشت به بالای صفحه</span>
                <svg class="w-3.5 h-3.5 text-zinc-400 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 -translate-y-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
            </button>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-10">
            
            {{-- Col 1: About & Trust Badges (Span 4) --}}
            <div class="md:col-span-4 space-y-4">
                <p class="text-xs sm:text-[13px] leading-relaxed text-zinc-600 dark:text-zinc-300 font-sans">
                    {{ $siteDescription ?: 'مستر ویلا با ایجاد بستری امن و شفاف، امکان رزرو سریع و بی‌دغدغه بهترین کلبه‌های جنگلی، بوم‌گردی‌ها و ویلاهای اختصاصی منطقه لفور و مازندران را با تضمین تمیزی و پشتیبانی مستقیم فراهم ساخته است.' }}
                </p>

                {{-- Micro Trust Badges --}}
                <div class="grid grid-cols-2 gap-2 pt-2">
                    <div class="flex items-center gap-2 p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-100 dark:border-zinc-800/80">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </span>
                        <span class="text-[11px] font-bold text-zinc-800 dark:text-zinc-200 font-sans">ضمانت تحویل و پاکیزگی</span>
                    </div>

                    <div class="flex items-center gap-2 p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-100 dark:border-zinc-800/80">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="text-[11px] font-bold text-zinc-800 dark:text-zinc-200 font-sans">رزرو آنی ۲۴ ساعته</span>
                    </div>
                </div>

                @if(!empty($address))
                    <div class="flex items-start gap-2 pt-2 text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed font-sans">
                        <svg class="w-4 h-4 text-[#007A5E] dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ $address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Popular Real Destinations as Chip Pills (Span 3) --}}
            <div class="md:col-span-3 space-y-3">
                <h4 class="text-xs sm:text-sm font-bold text-[#18181B] dark:text-zinc-100 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#007A5E] dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    <span>محبوب‌ترین مقاصد شمال</span>
                </h4>

                <div class="flex flex-wrap gap-2 pt-1">
                    @foreach($footerZones as $zone)
                        <a href="{{ route('properties.index', ['search' => $zone]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-xs font-medium text-zinc-700 dark:text-zinc-200 hover:border-[#007A5E] hover:text-[#007A5E] dark:hover:border-emerald-500 dark:hover:text-emerald-400 hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20 transition-all font-sans shadow-2xs">
                            <span class="text-zinc-400 dark:text-zinc-500 text-[10px]">📍</span>
                            <span>اقامتگاه‌های {{ $zone }}</span>
                        </a>
                    @endforeach
                    <a href="{{ route('properties.index') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs font-bold text-[#007A5E] dark:text-emerald-400 hover:bg-[#007A5E] hover:text-white dark:hover:bg-emerald-600 transition-all font-sans shadow-2xs">
                        <span>مشاهده همه اقامتگاه‌ها</span>
                        <span>←</span>
                    </a>
                </div>
            </div>

            {{-- Col 3: Services & Hosting (Span 2) --}}
            <div class="md:col-span-2 space-y-3">
                <h4 class="text-xs sm:text-sm font-bold text-[#18181B] dark:text-zinc-100 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#007A5E] dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>میزبانی و خدمات</span>
                </h4>

                <ul class="space-y-2.5 text-xs font-sans">
                    @if($rentalMultiHostEnabled)
                        <li>
                            <a href="{{ $isHostRegistered ? route('user.properties.hosts.dashboard') : url('/register') }}" 
                               class="inline-flex items-center gap-2 font-bold text-[#007A5E] dark:text-emerald-400 hover:underline">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007A5E] dark:bg-emerald-400"></span>
                                <span>{{ $isHostRegistered ? 'داشبورد مدیریت میزبانی' : 'ثبت اقامتگاه (میزبان شوید)' }}</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('properties.index') }}" class="group inline-flex items-center gap-1.5 text-zinc-600 dark:text-zinc-300 hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">
                            <span class="text-zinc-300 dark:text-zinc-600 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors text-[10px]">‹</span>
                            <span>اقامتگاه‌ها</span>
                        </a>
                    </li>
                    @if(auth()->check())
                        @php
                            $u = auth()->user();
                            $isSuperAdmin = ($u->is_super_admin ?? false) || (method_exists($u, 'hasRole') && ($u->hasRole('super-admin') || $u->hasRole('Super Admin')));
                            $uDash = $isSuperAdmin ? url('/admin') : url('/user');
                        @endphp
                        <li>
                            <a href="{{ $uDash }}" class="group inline-flex items-center gap-1.5 text-zinc-600 dark:text-zinc-300 hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">
                                <span class="text-zinc-300 dark:text-zinc-600 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors text-[10px]">‹</span>
                                <span>{{ $isSuperAdmin ? 'پنل مدیریت کل' : 'پنل کاربری من' }}</span>
                            </a>
                        </li>
                    @elseif(auth('client')->check())
                        <li>
                            <a href="{{ url('/clients/dashboard') }}" class="group inline-flex items-center gap-1.5 text-zinc-600 dark:text-zinc-300 hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">
                                <span class="text-zinc-300 dark:text-zinc-600 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors text-[10px]">‹</span>
                                <span>داشبورد کاربری من</span>
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ url('/clients/dashboard') }}" class="group inline-flex items-center gap-1.5 text-zinc-600 dark:text-zinc-300 hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">
                                <span class="text-zinc-300 dark:text-zinc-600 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors text-[10px]">‹</span>
                                <span>ورود / ثبت‌نام</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="#trust-guarantees-section" class="group inline-flex items-center gap-1.5 text-zinc-600 dark:text-zinc-300 hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">
                            <span class="text-zinc-300 dark:text-zinc-600 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors text-[10px]">‹</span>
                            <span>ضمانت تحویل و تسویه</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Col 4: Modern Support & Contact Card (Span 3) --}}
            <div class="md:col-span-3">
                <div class="p-4 sm:p-5 rounded-2xl bg-zinc-50 dark:bg-zinc-900/90 border border-zinc-200/80 dark:border-zinc-800 space-y-3.5 shadow-2xs">
                    
                    {{-- Status Indicator --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 font-sans">پشتیبانی آنلاین و فعال</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-[#007A5E] dark:text-emerald-300 font-bold font-sans">
                            ۲۴/۷
                        </span>
                    </div>

                    <p class="text-[11px] leading-relaxed text-zinc-500 dark:text-zinc-400 font-sans">
                        در تمام مراحل رزرو و سفر، تیم پشتیبانی مستر ویلا همراه و پاسخگوی شماست.
                    </p>

                    @if(!empty($contactPhone))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" 
                           class="w-full flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl bg-[#007A5E] hover:bg-[#00664e] text-white font-bold text-xs sm:text-sm shadow-sm hover:shadow transition-all font-sans">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="font-sans dir-ltr tracking-wide">{{ $contactPhone }}</span>
                        </a>
                    @endif

                    @if(!empty($contactEmail))
                        <div class="pt-1 text-center">
                            <a href="mailto:{{ $contactEmail }}" class="inline-flex items-center gap-1.5 text-[11px] text-zinc-500 hover:text-[#007A5E] dark:text-zinc-400 dark:hover:text-emerald-400 transition-colors font-sans">
                                <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="dir-ltr">{{ $contactEmail }}</span>
                            </a>
                        </div>
                    @endif

                </div>
            </div>

        </div>

        {{-- Bottom Copyright & Legal --}}
        <div class="pt-6 border-t border-[#ECECEE] dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-zinc-500 dark:text-zinc-400 font-sans">
            <div class="text-center sm:text-right">
                © {{ date('Y') }} {{ $appName }} — تمامی حقوق مادی و معنوی محفوظ است.
            </div>
            @if(!empty($footerText))
                <div class="flex items-center gap-1 text-[11px] text-zinc-400 dark:text-zinc-500">
                    <span>{{ $footerText }}</span>
                </div>
            @else
                <div class="flex items-center gap-1 text-[11px] text-zinc-400 dark:text-zinc-500">
                    <span>طراحی و توسعه با بالاترین استانداردهای وب</span>
                </div>
            @endif
        </div>

    </div>
</footer>
