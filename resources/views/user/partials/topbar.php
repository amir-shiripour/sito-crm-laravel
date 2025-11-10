<header class="h-16 sticky top-0 z-40 bg-white/80 dark:bg-gray-800/80 backdrop-blur border-b border-gray-200 dark:border-gray-700">
    <div class="h-full px-4 flex items-center justify-between gap-3">
        <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" @click="mobileOpen = true" aria-label="Open sidebar">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>

        <div class="hidden md:flex flex-1 items-center">
            <div class="relative w-full max-w-md">
                <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 1 1-4.24 10.24l-2.5 2.5L2.8 15l2.5-2.5A6 6 0 0 1 10 4Z"/></svg>
                <input type="text" placeholder="جستجو..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="relative">
                <button @click="cycleTheme()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" :title="themeTitle()">
                    <svg x-show="themeIcon==='dark'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                    <svg x-show="themeIcon==='light'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 18a6 6 0 1 0-6-6 6 6 0 0 0 6 6ZM12 2v2m0 16v2m10-10h-2M4 12H2m15.07 6.07-1.41-1.41M6.34 6.34 4.93 4.93m12.73 0-1.41 1.41M6.34 17.66l-1.41 1.41"/></svg>
                    <svg x-show="themeIcon==='system'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2H4Zm0 4h16v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/></svg>
                </button>
            </div>

            <div class="relative" x-data="{open:false}">
                <button @click="open=!open" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" aria-haspopup="true" aria-expanded="false">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a6 6 0 0 0-6 6v3.59L4.29 13.3a1 1 0 0 0 .7 1.7H19a1 1 0 0 0 .71-1.71L18 11.59V8a6 6 0 0 0-6-6Zm0 20a3 3 0 0 0 3-3H9a3 3 0 0 0 3 3Z"/></svg>
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
                    <img src="https://i.pravatar.cc/64?img=5" class="w-8 h-8 rounded-full" alt="avatar">
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
