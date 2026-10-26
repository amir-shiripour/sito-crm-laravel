<header class="sticky top-0 z-50 transition-all duration-300 bg-white/95 dark:bg-slate-950/95 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800/80 shadow-sm font-sans">
    {{-- نوار اطلاع‌رسانی فوقانی --}}
    @if(!empty($landingAnnouncement))
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-cyan-600 text-white text-[11px] sm:text-xs py-1.5 px-4 font-sans">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="inline-flex w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="line-clamp-1">{{ $landingAnnouncement }}</span>
            </div>
            @if(!empty($supportPhone))
            <div class="hidden sm:flex items-center gap-2 shrink-0" dir="ltr">
                <span class="text-indigo-200 text-[11px]">پشتیبانی ۲۴/۷:</span>
                <a href="tel:{{ $supportPhone }}" class="font-bold hover:underline font-sans">{{ $supportPhone }}</a>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-4">

        {{-- بخش راست: لوگو و عنوان پلتفرم --}}
        <div class="flex items-center gap-4 lg:gap-8 shrink-0">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 sm:gap-3 hover:opacity-90 transition-opacity">
                @if(!empty($appLogo))
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-9 sm:h-10 w-auto rounded-xl">
                @else
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-800 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                @endif
                <span class="font-black text-base sm:text-lg tracking-tight text-slate-900 dark:text-white font-sans">{{ $appName }}</span>
            </a>

            {{-- منوی دسکتاپ با فواصل استاندارد و ریسپانسیو --}}
            <nav class="hidden xl:flex items-center gap-5 text-xs lg:text-sm font-bold text-slate-600 dark:text-slate-300 font-sans">
                <a href="#services" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">تجهیزات و خدمات</a>
                <a href="#roles" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">بخش کاربران</a>
                <a href="#guarantee" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">ضمانت امانی</a>
                <a href="#articles" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">دانشنامه فنی</a>
                <a href="{{ route('fieldservice.technician') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>پرتال تکنسین‌ها</span>
                </a>
            </nav>
        </div>

        {{-- بخش چپ: اکشن‌ها و دکمه‌های ورود --}}
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">

            {{-- سوئیچر تم --}}
            <div class="hidden md:flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <button onclick="setAppThemeMode('light')" data-theme="light" class="theme-switcher-btn p-1.5 rounded-lg transition-all focus:outline-none" title="روشن">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </button>
                <button onclick="setAppThemeMode('system')" data-theme="system" class="theme-switcher-btn p-1.5 rounded-lg transition-all focus:outline-none" title="سیستم">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </button>
                <button onclick="setAppThemeMode('dark')" data-theme="dark" class="theme-switcher-btn p-1.5 rounded-lg transition-all focus:outline-none" title="تاریک">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                </button>
            </div>

            {{-- حساب کاربری (فقط در دسکتاپ/تبلت نمایش داده می‌شود تا هدر موبایل منفجر نشود) --}}
            @auth
                <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}"
                   class="hidden md:flex px-3 sm:px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/25 transition-all items-center gap-1.5 font-sans">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <span>داشبورد</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="hidden md:block text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-2.5 transition-colors font-sans">
                    ورود به پنل
                </a>
            @endauth

            {{-- دکمه کال تو اکشن ثبت سفارش (در دسکتاپ و تبلت فعال، در موبایل به نوار چسبان پایین واگذار می‌شود) --}}
            <a href="{{ route('fieldservice.customer') }}"
               class="hidden sm:flex px-4 sm:px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-600/25 transition-all items-center gap-2 font-sans cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>درخواست فوری تکنسین</span>
            </a>

            {{-- دکمه منوی همبرگری موبایل (همیشه شیک و تمیز در دسترس) --}}
            <button onclick="toggleMobileMenu()" class="xl:hidden p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors focus:outline-none" aria-label="منوی سایت">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
        </div>
    </div>
</header>

{{-- نوار کناری موبایل (Mobile Sidebar) --}}
<div id="mobile-menu-overlay" class="fixed inset-0 bg-gray-900/50 dark:bg-black/60 backdrop-blur-sm z-[60] hidden opacity-0 transition-opacity duration-300" onclick="toggleMobileMenu()"></div>

<aside id="mobile-menu" class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-gray-950 z-[70] transform translate-x-full transition-transform duration-300 shadow-2xl flex flex-col border-l border-indigo-100 dark:border-indigo-900/30 overflow-y-auto font-sans">
    <div class="h-20 px-6 flex items-center justify-between border-b border-gray-100 dark:border-gray-800/50">
        <div class="flex items-center gap-3">
            <span class="font-black text-lg text-gray-900 dark:text-white font-sans">{{ $appName }}</span>
        </div>
        <button onclick="toggleMobileMenu()" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors focus:outline-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <div class="p-6 flex-1 space-y-6">
        <nav class="flex flex-col gap-3 text-sm font-bold text-gray-700 dark:text-gray-300 font-sans">
            <a href="#services" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">تجهیزات و خدمات</a>
            <a href="#how-it-works" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">مراحل اعزام</a>
            <a href="#roles" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">بخش کاربران و پرتال‌ها</a>
            <a href="#guarantee" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">ضمانت و پرداخت امانی</a>
            <a href="#articles" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">دانشنامه فنی</a>
            <a href="{{ route('fieldservice.technician') }}" class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 transition-colors flex items-center justify-between">
                <span>پرتال متخصصان (تکنسین‌ها)</span>
                <span class="text-xs bg-indigo-600 text-white px-2 py-0.5 rounded-lg">ورود</span>
            </a>
            <a href="{{ route('fieldservice.customer') }}#lookup" class="p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">پیگیری سفارش</a>
        </nav>

        {{-- انتخاب حالت تم --}}
        <div class="space-y-2 pt-4 border-t border-gray-100 dark:border-gray-800">
            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider font-sans">ظاهر سایت</p>
            <div class="flex items-center gap-2 p-1 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <button onclick="setAppThemeMode('light')" data-theme="light" class="theme-switcher-btn flex-1 flex justify-center py-2 rounded-lg transition-all focus:outline-none" title="روشن">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </button>
                <button onclick="setAppThemeMode('system')" data-theme="system" class="theme-switcher-btn flex-1 flex justify-center py-2 rounded-lg transition-all focus:outline-none" title="سیستم">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </button>
                <button onclick="setAppThemeMode('dark')" data-theme="dark" class="theme-switcher-btn flex-1 flex justify-center py-2 rounded-lg transition-all focus:outline-none" title="تاریک">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                </button>
            </div>
        </div>

        {{-- ورود به پنل و سفارش --}}
        <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ route('fieldservice.customer') }}" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm text-center flex items-center justify-center gap-2 shadow-md shadow-indigo-600/30">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                درخواست فوری تکنسین
            </a>

            @auth
                <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 font-bold text-sm text-center flex items-center justify-center gap-2">
                    ورود به داشبورد
                </a>
            @else
                <a href="{{ route('login') }}" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 font-bold text-sm text-center flex items-center justify-center gap-2">
                    ورود به پنل کاربری
                </a>
            @endauth
        </div>
    </div>
</aside>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const overlay = document.getElementById('mobile-menu-overlay');
        if (menu.classList.contains('translate-x-full')) {
            menu.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            document.body.style.overflow = 'hidden';
        } else {
            menu.classList.add('translate-x-full');
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
            document.body.style.overflow = '';
        }
    }
</script>
