@php
    $holidayService = app(\App\Services\JalaliHolidayService::class);
    $holidayStats   = $holidayService->getStoredStats();
    
    $googleToken       = \App\Models\GoogleCalendarToken::where('is_active', true)->latest()->first();
    $googleService     = app(\App\Services\GoogleCalendarService::class);
    $isGoogleConfigured= $googleService->isConfigured();

    $importService = app(\App\Services\GoogleCalendarImportService::class);
    $importStats   = $importService->getStats();
@endphp

{{-- تب تنظیمات ویجت‌ها --}}
<div x-show="activeTab === 'widgets'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
    
    {{-- بخش ۱: ویجت تقویم و رویدادهای روز --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-600 dark:text-rose-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">ویجت «تقویم و رویدادهای روز»</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی نحوه نمایش کارت ویجت تقویم در داشبورد کاربران</p>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div>
                <label class="{{ $labelClass }}">نحوه نمایش بخش‌های کارت ویجت تقویم</label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">تعیین کنید کدام بخش‌ها در کارت این ویجت در داشبورد کاربران به نمایش درآیند:</p>

                @php
                    $mode = $settings['widget_calendar_display_mode'] ?? 'both';
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- گزینه ۱: هر دو بخش (پیش‌فرض) --}}
                    <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 hover:shadow-md
                        {{ $mode === 'both' ? 'border-rose-500 bg-rose-50/20 dark:bg-rose-900/10 dark:border-rose-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }}">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-black text-gray-900 dark:text-white">نمایش کامل (هر دو بخش)</span>
                            <input type="radio" name="widget_calendar_display_mode" value="both" {{ $mode === 'both' ? 'checked' : '' }} class="text-rose-600 focus:ring-rose-500 h-4 w-4">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-3">نمایش همزمان شمارنده روز به شمسی (روز، ماه و نوارهای پیشرفت) + تقویم کوچک ۳۱ روزه ماه جاری.</p>
                        <div class="mt-auto pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-[11px] font-bold text-rose-600 dark:text-rose-400">
                            <span>بخش ۱ + بخش ۲</span>
                            <span class="px-2 py-0.5 rounded bg-rose-100 dark:bg-rose-900/30">پیش‌فرض</span>
                        </div>
                    </label>

                    {{-- گزینه ۲: فقط شمارنده روز --}}
                    <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 hover:shadow-md
                        {{ $mode === 'counter_only' ? 'border-rose-500 bg-rose-50/20 dark:bg-rose-900/10 dark:border-rose-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }}">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-black text-gray-900 dark:text-white">فقط شمارنده روز (بخش ۱)</span>
                            <input type="radio" name="widget_calendar_display_mode" value="counter_only" {{ $mode === 'counter_only' ? 'checked' : '' }} class="text-rose-600 focus:ring-rose-500 h-4 w-4">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-3">فقط شمارنده روز به شمسی (شامل روز بزرگ، نام روز، ماه و سال).</p>
                        <div class="mt-auto pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-[11px] font-bold text-gray-500">
                            <span>بخش ۱ (شمارنده)</span>
                        </div>
                    </label>

                    {{-- گزینه ۳: فقط تقویم کوچک --}}
                    <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 hover:shadow-md
                        {{ $mode === 'calendar_only' ? 'border-rose-500 bg-rose-50/20 dark:bg-rose-900/10 dark:border-rose-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }}">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-black text-gray-900 dark:text-white">فقط تقویم کوچک ماه (بخش ۲)</span>
                            <input type="radio" name="widget_calendar_display_mode" value="calendar_only" {{ $mode === 'calendar_only' ? 'checked' : '' }} class="text-rose-600 focus:ring-rose-500 h-4 w-4">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-3">فقط شبکه ۳۱ روزه تقویم ماه جاری با هایلایت روز امروز و روزهای هفته.</p>
                        <div class="mt-auto pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-[11px] font-bold text-gray-500">
                            <span>بخش ۲ (تقویم)</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- بخش ۲: تنظیمات منابع رویداد تقویم --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">منابع رویدادهای تقویم (ماژول‌های متصل)</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تعیین کنید کدام ماژول‌ها و منابع اجازه ارسال رویداد و نمایش در تقویم را داشته باشند</p>
            </div>
        </div>

        @php
            $rawEnabled = $settings['widget_calendar_enabled_sources'] ?? null;
            if ($rawEnabled !== null) {
                $enabledSources = is_string($rawEnabled) ? json_decode($rawEnabled, true) : $rawEnabled;
                $enabledSources = is_array($enabledSources) ? $enabledSources : ['booking', 'tasks', 'reminders', 'jalali_holidays', 'google_calendar'];
            } else {
                $enabledSources = ['booking', 'tasks', 'reminders', 'jalali_holidays', 'google_calendar'];
            }
        @endphp

        <div class="p-6 space-y-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">با فعال یا غیرفعال کردن هر ماژول، رویدادها و نوبت‌های مربوط به آن در تقویم داشبورد و تقویم کامل نمایش داده خواهد شد:</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
                {{-- ۱. ماژول نوبت‌دهی (Booking) --}}
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition-all">
                    <input type="checkbox" name="widget_calendar_enabled_sources[]" value="booking" {{ in_array('booking', $enabledSources) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">نوبت‌دهی (Booking)</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">نوبت‌های رزرو شده مشتریان.</p>
                    </div>
                </label>

                {{-- ۲. ماژول وظایف (Tasks) --}}
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition-all">
                    <input type="checkbox" name="widget_calendar_enabled_sources[]" value="tasks" {{ in_array('tasks', $enabledSources) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-amber-600 focus:ring-amber-500 h-4 w-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">وظایف (Tasks)</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">کارهای سررسیددار کاربران.</p>
                    </div>
                </label>

                {{-- ۳. ماژول یادآوری‌ها (Reminders) --}}
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition-all">
                    <input type="checkbox" name="widget_calendar_enabled_sources[]" value="reminders" {{ in_array('reminders', $enabledSources) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-purple-600 focus:ring-purple-500 h-4 w-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">یادآوری‌ها (Reminders)</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">یادآوری‌های زمانی سیستمی.</p>
                    </div>
                </label>

                {{-- ۴. مناسبت‌ها و تعطیلات ملی (Jalali Holidays) --}}
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition-all">
                    <input type="checkbox" name="widget_calendar_enabled_sources[]" value="jalali_holidays" {{ in_array('jalali_holidays', $enabledSources) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-rose-600 focus:ring-rose-500 h-4 w-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">مناسبت‌ها & تعطیلات</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">تعطیلات رسمی و رویدادهای ملی.</p>
                    </div>
                </label>

                {{-- ۵. گوگل کلندر (Google Calendar) --}}
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition-all">
                    <input type="checkbox" name="widget_calendar_enabled_sources[]" value="google_calendar" {{ in_array('google_calendar', $enabledSources) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-teal-600 focus:ring-teal-500 h-4 w-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">گوگل کلندر (Google Calendar)</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">رویدادهای همگام‌شده یا ایمپورت‌شده گوگل.</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- بخش ۳: سرویس و اتصال آنلاین به Google Calendar (OAuth2) --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }} justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/20 flex items-center justify-center text-teal-600 dark:text-teal-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">روش اول: اتصال آنلاین به Google Calendar</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی کلیدها و مدیریت اتصال مستقیم OAuth2 حساب گوگل به CRM</p>
                </div>
            </div>

            @if($googleToken)
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300">
                    متصل به: {{ $googleToken->email }}
                </span>
            @else
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    اتصالی ثبت نشده
                </span>
            @endif
        </div>

        <div class="p-6 space-y-6">
            {{-- کلیدهای API گوگل (در صورت عدم وجود در .env از دیتابیس خوانده می‌شوند) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelClass }}">Google Client ID</label>
                    <input type="text" name="google_client_id" value="{{ $settings['google_client_id'] ?? config('google_calendar.client_id', '') }}" placeholder="مثال: xxxxx.apps.googleusercontent.com" class="{{ $inputClass }}" dir="ltr">
                    <p class="text-[11px] text-gray-400 mt-1">در صورت وجود در فایل .env، از آن استفاده خواهد شد.</p>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Google Client Secret</label>
                    <input type="password" name="google_client_secret" value="{{ $settings['google_client_secret'] ?? config('google_calendar.client_secret', '') }}" placeholder="کلید مخفی گوگل..." class="{{ $inputClass }}" dir="ltr">
                    <p class="text-[11px] text-gray-400 mt-1">در صورت عدم ثبت در .env، این مقدار از تنظیمات خوانده می‌شود.</p>
                </div>
            </div>

            {{-- سطح دسترسی نمایش رویدادهای گوگل --}}
            <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                <label class="{{ $labelClass }}">سطح دسترسی نمایش رویدادهای Google Calendar در تقویم</label>
                @php
                    $gVisibility = $settings['google_calendar_visibility'] ?? 'all';
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer transition-all {{ $gVisibility === 'all' ? 'bg-teal-50/30 border-teal-500' : 'bg-gray-50/30' }}">
                        <input type="radio" name="google_calendar_visibility" value="all" {{ $gVisibility === 'all' ? 'checked' : '' }} class="text-teal-600 focus:ring-teal-500 h-4 w-4">
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">قابل مشاهده برای تمامی کاربران سیستم</span>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">تمام کاربران پنل رویدادهای گوگل کلندر را در تقویم مشاهده می‌کنند.</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer transition-all {{ $gVisibility === 'admin_only' ? 'bg-teal-50/30 border-teal-500' : 'bg-gray-50/30' }}">
                        <input type="radio" name="google_calendar_visibility" value="admin_only" {{ $gVisibility === 'admin_only' ? 'checked' : '' }} class="text-teal-600 focus:ring-teal-500 h-4 w-4">
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">فقط مدیران ارشد (Admin Only)</span>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">رویدادهای گوگل کلندر فقط در تقویم مدیران به نمایش درمی‌آیند.</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- وضعیت دکمه‌های اتصال / قطع اتصال --}}
            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">وضعیت اتصال حساب گوگل</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        آدرس Redirect URI برای پیکربندی در Google Cloud Console:
                        <code class="bg-gray-100 dark:bg-gray-900 px-2 py-0.5 rounded text-indigo-600 dark:text-indigo-400 font-mono text-[11px]" dir="ltr">{{ $googleService->getRedirectUri() }}</code>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    @if($googleToken)
                        <button type="submit" 
                                formaction="{{ route('settings.google-calendar.disconnect') }}" 
                                formmethod="POST" 
                                onclick="return confirm('آیا از قطع اتصال حساب گوگل اطمینان دارید؟')"
                                class="px-4 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 text-xs font-bold transition-colors">
                            قطع اتصال حساب
                        </button>
                    @endif

                    <a href="{{ route('settings.google-calendar.connect') }}" 
                       class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <span>{{ $googleToken ? 'تغییر و اتصال حساب گوگل جدید' : 'اتصال به Google Calendar' }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- بخش ۴: ایمپورت دستی فایل iCal / ICS گوگل کلندر (جایگزین اتصال آنلاین) --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }} justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-900/20 flex items-center justify-center text-sky-600 dark:text-sky-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">روش دوم: ایمپورت دستی فایل iCal / ICS گوگل کلندر</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">بارگذاری مستقیم خروجی تقویم گوگل (.ics یا .ical یا .zip) بدون نیاز به اتصال آنلاین API</p>
                </div>
            </div>

            <span class="px-3 py-1 text-xs font-bold rounded-full bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300">
                {{ number_format($importStats['total_count']) }} رویداد ایمپورت شده
            </span>
        </div>

        <div class="p-6 space-y-6">
            <div class="p-4 rounded-2xl bg-sky-50/50 dark:bg-sky-900/20 border border-sky-100 dark:border-sky-800/40 space-y-2">
                <h4 class="text-xs sm:text-sm font-bold text-sky-900 dark:text-sky-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    راهنمای ایمپورت خروجی تقویم گوگل:
                </h4>
                <p class="text-xs text-sky-800 dark:text-sky-300 leading-relaxed">
                    می‌توانید در گوگل کلندر به بخش <strong>Settings &rarr; Export</strong> بروید، فایل زیپ یا <code class="font-mono dir-ltr bg-white/70 dark:bg-gray-800 px-1.5 py-0.5 rounded">.ics</code> دانلود شده را در فرم زیر آپلود کنید. تمام رویدادها استخراج شده و در تقویم CRM با رنگ گوگل کلندر نمایش می‌یابند.
                </p>
            </div>

            {{-- آمار فایل‌های ایمپورت شده --}}
            @if($importStats['total_count'] > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">مجموع رویدادهای ذخیره‌شده</span>
                            <span class="text-sm font-black text-gray-900 dark:text-white block mt-1">
                                {{ number_format($importStats['total_count']) }} رویداد
                            </span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">فایل‌های پردازش‌شده</span>
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 block mt-1 truncate max-w-xs" title="{{ implode(', ', $importStats['filenames']) }}">
                                {{ implode(', ', $importStats['filenames']) ?: 'بدون نام' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- آپلود فایل iCal --}}
            <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex-1">
                    <label class="{{ $labelClass }}">انتخاب فایل خروجی تقویم (.ics / .ical / .zip)</label>
                    <input type="file" 
                           name="ical_file" 
                           form="main-settings-form"
                           accept=".ics,.ical,.zip" 
                           class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 dark:file:bg-sky-900/40 dark:file:text-sky-300 dark:file:hover:bg-sky-900/60 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900">
                </div>

                <div class="flex items-center gap-3 self-end sm:self-auto pt-6">
                    @if($importStats['total_count'] > 0)
                        <button type="submit" 
                                formaction="{{ route('settings.google-calendar.clear-imported') }}" 
                                formmethod="POST" 
                                onclick="return confirm('آیا از پاکسازی تمام رویدادهای ایمپورت‌شده گوگل کلندر اطمینان دارید؟')"
                                class="px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 text-xs font-bold transition-colors">
                            پاکسازی رویدادها
                        </button>
                    @endif

                    <button type="submit" 
                            formaction="{{ route('settings.google-calendar.import') }}" 
                            formmethod="POST" 
                            class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md shadow-sky-500/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>شروع ایمپورت فایل</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- بخش ۵: بانک اطلاعاتی مناسبت‌ها و تعطیلات رسمی (ذخیره‌سازی محلی) --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headerClass }} justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">بانک اطلاعاتی مناسبت‌ها و تعطیلات ملی (پایگاه داده محلی)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تمام داده‌ها در پایگاه داده محلی سیستم ذخیره شده و هیچ وابستگی به اینترنت هنگام خواندن تقویم وجود ندارد.</p>
                </div>
            </div>
            <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                محتوای آفلاین (مستقل)
            </span>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- آخرین زمان بروزرسانی --}}
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">آخرین بروزرسانی موفق</span>
                    <span class="text-sm font-black text-gray-900 dark:text-white mt-2" dir="ltr">
                        {{ $holidayStats['last_sync'] ?? 'هنوز بروزرسانی نشده' }}
                    </span>
                </div>

                {{-- مجموع کل مناسبت‌های ذخیره‌شده --}}
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">مجموع کل مناسبت‌های ذخیره‌شده</span>
                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400 mt-2">
                        {{ number_format($holidayStats['total_count']) }} رویداد
                    </span>
                </div>

                {{-- سال‌های موجود در دیتابیس --}}
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">سال‌های موجود در دیتابیس</span>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @forelse($holidayStats['years'] as $yearNum => $count)
                            <span class="px-2 py-0.5 text-xs font-bold rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                سال {{ $yearNum }}: {{ $count }} مورد
                            </span>
                        @empty
                            <span class="text-xs text-gray-400">هنوز سالی ثبت نشده</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-4">
                <div class="text-xs text-gray-500 dark:text-gray-400 max-w-xl leading-relaxed">
                    با کلیک روی دکمه زیر، اطلاعات مناسبت‌ها و تعطیلات سال جاری و سال بعد دریافت شده و در دیتابیس محلی ذخیره می‌شود. 
                    اطلاعات سال‌های گذشته در پایگاه داده باقی خواهند ماند و پاک نخواهند شد.
                </div>
                
                <button type="submit" 
                        formaction="{{ route('settings.sync-holidays') }}" 
                        formmethod="POST" 
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    به‌روزرسانی و دریافت مناسبت‌ها
                </button>
            </div>
        </div>
    </div>
</div>
