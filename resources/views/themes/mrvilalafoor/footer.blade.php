@php
    $globalSettings = [];
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $globalSettings = \Modules\Settings\Entities\Setting::pluck('value', 'key')->toArray();
        }
    } catch (\Throwable $e) {}

    $appName = $globalSettings['app_name'] ?? 'مستر ویلا لفور';
    $supportPhone = $globalSettings['company_phone'] ?? '۰۲۱-۹۱۰۱۴۴۵۵';
    $rentalMultiHostEnabled = false;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('property_settings')) {
            $rentalMultiHostEnabled = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_multi_host_enabled', 0);
        }
    } catch (\Throwable $e) {}
@endphp

<!-- ============================================================= -->
<!-- MR. VILA LAFOOR MINIMAL DESKTOP & MOBILE FOOTER               -->
<!-- ============================================================= -->
<footer class="border-t border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9]/50 dark:bg-zinc-950 mt-20 pt-12 pb-16 text-xs text-zinc-500 dark:text-zinc-400 transition-colors">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-8 space-y-10">
        
        {{-- Top Info Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 pb-10 border-b border-[#ECECEE] dark:border-zinc-800">
            
            {{-- Col 1: Brand & About --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-[#18181B] dark:text-zinc-100 font-black text-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#007A5E] dark:bg-emerald-500"></span>
                    <span>{{ $appName }}</span>
                </div>
                <p class="text-[11px] leading-relaxed text-zinc-400 dark:text-zinc-400">
                    پلتفرم تخصصی رزرواسیون آنلاین اقامتگاه‌های بوم‌گردی، کلبه‌های چوبی سوئیسی و ویلاهای اختصاصی در سوادکوه، لفور و شمال کشور با تضمین تمیزی و تحویل حضوری.
                </p>
            </div>

            {{-- Col 2: Popular Zones --}}
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-[#18181B] dark:text-zinc-200">محبوب‌ترین پهنه‌ها</h4>
                <ul class="space-y-2 text-[11px]">
                    <li><a href="{{ route('properties.index', ['search' => 'لفور']) }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">سد و جنگل‌های لفور</a></li>
                    <li><a href="{{ route('properties.index', ['search' => 'سوادکوه']) }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">سوادکوه و شیرگاه</a></li>
                    <li><a href="{{ route('properties.index', ['search' => 'ماسال']) }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">ییلاقات ماسال و اولسبلنگاه</a></li>
                    <li><a href="{{ route('properties.index', ['search' => 'رامسر']) }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">جواهرده و دالخانی رامسر</a></li>
                </ul>
            </div>

            {{-- Col 3: Multi-Host Marketplace & Rules --}}
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-[#18181B] dark:text-zinc-200">میزبانی و قوانین</h4>
                <ul class="space-y-2 text-[11px]">
                    @if($rentalMultiHostEnabled && Route::has('user.properties.hosts.register'))
                        <li>
                            <a href="{{ route('user.properties.hosts.register') }}" class="font-bold text-[#007A5E] dark:text-emerald-400 hover:underline">
                                ثبت اقامتگاه (میزبان شوید)
                            </a>
                        </li>
                    @endif
                    <li><a href="#" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">قوانین و شرایط لغو رزرو</a></li>
                    <li><a href="#" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">ضمانت تحویل و تحویل قطعی</a></li>
                    <li><a href="#" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">راهنمای مسافران</a></li>
                </ul>
            </div>

            {{-- Col 4: Support & Contact --}}
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-[#18181B] dark:text-zinc-200">پشتیبانی ۲۴ ساعته</h4>
                <p class="text-[11px] text-zinc-400 leading-relaxed">
                    کارشناسان ما در تمام طول سفر همراه و پاسخگوی شما هستند.
                </p>
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 text-xs font-bold text-[#18181B] dark:text-zinc-200 shadow-sm">
                    <svg class="w-4 h-4 text-[#007A5E] dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>{{ $supportPhone }}</span>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-zinc-400 dark:text-zinc-400">
            <div>
                © {{ date('Y') }} {{ $appName }} — تمامی حقوق مادی و معنوی محفوظ است.
            </div>
            <div class="flex items-center gap-4">
                <span>توسعه‌یافته با بالاترین استاندارد ارگونومی و وب فارسی</span>
            </div>
        </div>

    </div>
</footer>
