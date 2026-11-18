<header class="sticky top-0 z-50 transition-all duration-300 bg-white/95 dark:bg-slate-950/95 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800/80 shadow-xs font-sans">
    {{-- نوار اطلاع‌رسانی فوقانی ویژه جذب تکنسین --}}
    @if(!empty($landingAnnouncement))
    <div class="bg-indigo-600 dark:bg-indigo-700 text-white text-[11px] sm:text-xs py-1.5 px-4 font-sans">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="inline-flex w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="line-clamp-1 font-medium">{{ $landingAnnouncement }}</span>
            </div>
            @if(!empty($supportPhone))
            <div class="hidden sm:flex items-center gap-2 shrink-0" dir="ltr">
                <span class="text-indigo-200 text-[11px]">امور همکاران و جذب:</span>
                <a href="tel:{{ $supportPhone }}" class="font-bold hover:underline font-sans">{{ $supportPhone }}</a>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-4">

        {{-- بخش راست: برند و عنوان مرکز امور تکنسین‌ها --}}
        <div class="flex items-center gap-3 sm:gap-6 shrink-0">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 sm:gap-3 hover:opacity-90 transition-opacity">
                @if(!empty($appLogo))
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-9 sm:h-10 w-auto rounded-xl">
                @else
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center text-white shadow-xs shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                @endif
                <div class="flex flex-col text-right">
                    <span class="font-black text-base sm:text-lg tracking-tight text-slate-900 dark:text-white font-sans">{{ $appName }}</span>
                    <span class="text-[10px] sm:text-[11px] font-bold text-indigo-600 dark:text-indigo-400 font-sans">پرتال جذب و امور تکنسین‌ها</span>
                </div>
            </a>

            {{-- منوی دسکتاپ مینیمال و متمرکز بر تکنسین‌ها --}}
            <nav class="hidden sm:flex items-center gap-5 text-xs lg:text-sm font-bold text-slate-600 dark:text-slate-300 font-sans">
                <a href="#calculator" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">تخمین درآمد</a>
                <a href="#steps" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">مراحل شروع همکاری</a>
                <a href="#faq" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">سوالات متداول</a>
            </nav>
        </div>

        {{-- بخش چپ: اکشن‌ها و دکمه‌های ورود / ثبت‌نام --}}
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">

            {{-- سوئیچر تم --}}
            <div class="hidden sm:flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <button onclick="setAppThemeMode('light')" data-theme="light" class="theme-switcher-btn p-1.5 rounded-lg transition-all focus:outline-none cursor-pointer" title="روشن">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </button>
                <button onclick="setAppThemeMode('system')" data-theme="system" class="theme-switcher-btn p-1.5 rounded-lg transition-all focus:outline-none cursor-pointer" title="سیستم">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </button>
                <button onclick="setAppThemeMode('dark')" data-theme="dark" class="theme-switcher-btn p-1.5 rounded-lg transition-all focus:outline-none cursor-pointer" title="تاریک">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                </button>
            </div>

            {{-- ورود همکاران / کنسول تکنسین --}}
            @auth
                @if(auth()->user()->hasRole('technician'))
                    <a href="{{ route('fieldservice.technician') }}"
                       class="px-3 sm:px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/25 transition-all flex items-center gap-1.5 font-sans">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>کنسول کاربری تکنسین</span>
                    </a>
                @else
                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}"
                       class="px-3 sm:px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center gap-1.5 font-sans">
                        <span>داشبورد مدیریت</span>
                    </a>
                @endif
            @else
                <a href="{{ route('fieldservice.technician') }}"
                   class="px-3 sm:px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 dark:hover:border-indigo-500/40 transition-colors font-sans flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>ورود همکاران</span>
                </a>
            @endauth

            {{-- دکمه کال تو اکشن ثبت‌نام (شروع همکاری) --}}
            <a href="{{ route('fieldservice.technician') }}"
               class="hidden sm:flex px-4 sm:px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white text-xs sm:text-sm font-bold shadow-xs transition-all items-center gap-2 font-sans">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>ثبت‌نام تکنسین</span>
            </a>

            {{-- دکمه منوی همبرگری موبایل --}}
            <button onclick="toggleMobileMenu()" class="lg:hidden p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors focus:outline-none cursor-pointer" aria-label="منوی سایت">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
        </div>
    </div>
</header>

{{-- نوار کناری موبایل (Mobile App Drawer) --}}
<div id="mobile-menu-overlay" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-[60] hidden opacity-0 transition-opacity duration-300" onclick="toggleMobileMenu()"></div>

<aside id="mobile-menu" class="fixed top-0 right-0 w-80 max-w-[85vw] h-full bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 z-[70] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col justify-between font-sans">
    <div class="p-5 space-y-6 overflow-y-auto">

        {{-- سربرگ منوی موبایل --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-black text-sm text-slate-900 dark:text-white">{{ $appName }}</h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">مرکز جذب و امور تکنسین‌ها</p>
                </div>
            </div>
            <button onclick="toggleMobileMenu()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        {{-- منوهای ناوبری --}}
        <nav class="space-y-1 text-sm font-bold text-slate-700 dark:text-slate-200">
            <a href="#calculator" onclick="toggleMobileMenu()" class="block p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">محاسبه‌گر تخمینی درآمد</a>
            <a href="#steps" onclick="toggleMobileMenu()" class="block p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">مراحل شروع همکاری</a>
            <a href="#faq" onclick="toggleMobileMenu()" class="block p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">سوالات پرتکرار همکاران</a>
        </nav>

        {{-- تغییر تم در موبایل --}}
        <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider font-sans">ظاهر سامانه</p>
            <div class="flex items-center gap-2 p-1 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <button onclick="setAppThemeMode('light')" data-theme="light" class="theme-switcher-btn flex-1 flex justify-center py-2 rounded-lg transition-all focus:outline-none cursor-pointer" title="روشن">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </button>
                <button onclick="setAppThemeMode('system')" data-theme="system" class="theme-switcher-btn flex-1 flex justify-center py-2 rounded-lg transition-all focus:outline-none cursor-pointer" title="سیستم">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </button>
                <button onclick="setAppThemeMode('dark')" data-theme="dark" class="theme-switcher-btn flex-1 flex justify-center py-2 rounded-lg transition-all focus:outline-none cursor-pointer" title="تاریک">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                </button>
            </div>
        </div>

        {{-- اکشن‌های ثبت‌نام و تماس --}}
        <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('fieldservice.technician') }}"
               class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm text-center flex items-center justify-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>ثبت‌نام آنلاین تکنسین</span>
            </a>

            @auth
                @if(auth()->user()->hasRole('technician'))
                    <a href="{{ route('fieldservice.technician') }}" class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-sm text-center flex items-center justify-center gap-2">
                        <span>ورود به کنسول تکنسین</span>
                    </a>
                @else
                    <a href="{{ route('user.dashboard') }}" class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-sm text-center flex items-center justify-center gap-2">
                        <span>ورود به داشبورد</span>
                    </a>
                @endif
            @else
                <a href="{{ route('fieldservice.technician') }}"
                   class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-sm text-center flex items-center justify-center gap-2">
                    <span>ورود همکاران عضو</span>
                </a>
            @endauth

            @if(!empty($supportPhone))
            <a href="tel:{{ $supportPhone }}" class="w-full py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-600 dark:text-slate-400 text-center flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>تماس با امور تکنسین‌ها ({{ $supportPhone }})</span>
            </a>
            @endif
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
