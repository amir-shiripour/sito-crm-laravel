<header class="h-16 sticky top-0 z-40 bg-white/80 dark:bg-gray-800/80 backdrop-blur border-b border-gray-200 dark:border-gray-700">
    <div class="h-full px-4 flex items-center justify-between gap-3">
        <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" @click="mobileOpen = true" aria-label="Open sidebar">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>

        <div class="hidden md:flex flex-1 items-center">
            <div class="relative w-full max-w-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute right-3 top-2.5 w-5 h-5 text-gray-400 icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                <input type="text" placeholder="جستجو..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="relative">
                <button @click="cycleTheme()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" :title="themeTitle()">
                    <svg x-show="themeIcon==='dark'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-moon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" /></svg>
                    <svg x-show="themeIcon==='light'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-sun"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>
                    <svg x-show="themeIcon==='system'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-device-desktop"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1v-10z" /><path d="M7 20h10" /><path d="M9 16v4" /><path d="M15 16v4" /></svg>
                </button>
            </div>

            <div class="relative" x-data="{open:false}">
                <button @click="open=!open" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" aria-haspopup="true" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-bell-ringing"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /><path d="M21 6.727a11.05 11.05 0 0 0 -2.794 -3.727" /><path d="M3 6.727a11.05 11.05 0 0 1 2.792 -3.727" /></svg>
                    <span class="absolute -top-0.5 -left-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 text-[10px] text-white px-1">3</span>
                </button>
                <div x-cloak x-show="open" @click.outside="open=false" x-transition
                     class="absolute left-0 mt-2 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden z-50">
                    <div class="p-3 text-sm font-medium border-b border-gray-200 dark:border-gray-700">اعلان‌ها</div>
                    <div class="max-h-64 overflow-y-auto">
                        <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                            پرداخت جدید ثبت شد.
                        </a>
                        <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                            کاربر جدید عضو شد.
                        </a>
                        <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                            تیکت شما پاسخ داده شد.
                        </a>
                    </div>
                    <div class="p-2 text-center border-t border-gray-200 dark:border-gray-700">
                        <a href="#" class="text-xs text-indigo-600 hover:text-indigo-700">مشاهده همه</a>
                    </div>
                </div>
            </div>

            <div class="relative" x-data="{open:false}">
                <button @click="open=!open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
{{--                    <img src="" class="w-8 h-8 rounded-full" alt="avatar">--}}
                    <span class="hidden md:inline text-sm font-medium">کاربر</span>
                    <svg class="hidden md:inline w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5"/></svg>
                </button>
                <div x-cloak x-show="open" @click.outside="open=false" x-transition
                     class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden z-50">
                    <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">پروفایل</a>
                    <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">تنظیمات</a>
                    <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-200 dark:border-gray-700">
                        @csrf
                        <button class="w-full text-start px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">خروج</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
