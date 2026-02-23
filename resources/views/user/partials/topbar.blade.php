<header class="h-20 sticky top-0 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 transition-colors">
    <div class="h-full px-4 sm:px-6 flex items-center justify-between gap-4">

        {{-- دکمه منوی همبرگری موبایل --}}
        <button class="lg:hidden p-2 -ml-2 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                @click="mobileOpen = true"
                aria-label="Open sidebar">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 6h16" />
                <path d="M4 12h16" />
                <path d="M4 18h16" />
            </svg>
        </button>

        {{-- سرچ بار --}}
        <div class="hidden md:flex flex-1 items-center max-w-2xl">
            <div class="relative w-full"
                 x-data="{
                     searchQuery: '',
                     clients: [],
                     isLoading: false,
                     showResults: false,
                     selectedIndex: -1,
                     async search() {
                         if (this.searchQuery.length < 2) {
                             this.clients = [];
                             this.showResults = false;
                             return;
                         }
                         this.isLoading = true;
                         try {
                             const response = await fetch('{{ route('user.clients.search') }}?q=' + encodeURIComponent(this.searchQuery), {
                                 headers: {
                                     'X-Requested-With': 'XMLHttpRequest',
                                     'Accept': 'application/json'
                                 }
                             });
                             if (!response.ok) throw new Error('Response not ok: ' + response.status);
                             const data = await response.json();
                             this.clients = Array.isArray(data) ? data : (Array.isArray(data?.results) ? data.results : (Array.isArray(data?.data) ? data.data : []));
                             this.showResults = Array.isArray(this.clients) && this.clients.length > 0;
                             this.selectedIndex = -1;
                         } catch (error) {
                             console.error('Search error:', error);
                             this.clients = [];
                             this.showResults = false;
                         } finally {
                             this.isLoading = false;
                         }
                     },
                     selectClient(client) {
                        const baseUrl = '{{ route('user.clients.show', ['client' => 0]) }}';
                        window.location.href = baseUrl.replace('/0', '/' + client.id);
                     },
                     handleKeydown(event) {
                         if (!this.showResults || !Array.isArray(this.clients) || this.clients.length === 0) return;
                         if (event.key === 'ArrowDown') {
                             event.preventDefault();
                             this.selectedIndex = Math.min(this.selectedIndex + 1, this.clients.length - 1);
                         } else if (event.key === 'ArrowUp') {
                             event.preventDefault();
                             this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                         } else if (event.key === 'Enter' && this.selectedIndex >= 0) {
                             event.preventDefault();
                             this.selectClient(this.clients[this.selectedIndex]);
                         } else if (event.key === 'Escape') {
                             this.showResults = false;
                             this.selectedIndex = -1;
                         }
                     }
                 }" @click.outside="showResults = false">

                {{-- آیکون جستجو --}}
                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                        <path d="M21 21l-6 -6" />
                    </svg>
                </div>

                {{-- فیلد جستجو --}}
                <input type="text"
                       placeholder="جستجوی {{config('clients.labels.singular', 'مشتری')}} (نام، موبایل، کد ملی)..."
                       x-model="searchQuery"
                       @input.debounce.300ms="search()"
                       @keydown="handleKeydown($event)"
                       @focus="if(searchQuery.length >= 2) showResults = true"
                       class="w-full h-11 rounded-xl border-transparent bg-gray-100 dark:bg-gray-800 pr-11 pl-10 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-sm" />

                {{-- لودینگ --}}
                <div x-show="isLoading" class="absolute left-3.5 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                {{-- دراپ‌داون نتایج --}}
                <div x-cloak x-show="showResults && Array.isArray(clients) && clients.length > 0"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute top-full right-0 mt-2 w-full bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-2xl max-h-[22rem] overflow-y-auto z-50 overflow-hidden custom-scrollbar">

                    <ul class="py-2">
                        <template x-for="(client, index) in (Array.isArray(clients) ? clients : [])" :key="client?.id || index">
                            <li>
                                <div @click="selectClient(client)" @mouseenter="selectedIndex = index"
                                     :class="{
                                         'bg-indigo-50/80 dark:bg-indigo-900/20': selectedIndex === index,
                                         'bg-transparent': selectedIndex !== index
                                     }"
                                     class="flex items-center gap-3 px-4 py-3 mx-2 rounded-xl cursor-pointer transition-colors group">

                                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm shrink-0 border border-indigo-200 dark:border-indigo-800">
                                        <span x-text="(client.full_name || '؟').charAt(0)"></span>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-gray-900 dark:text-gray-100 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
                                             x-text="client.full_name || 'بدون نام'"></div>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                            <span x-show="client.phone" class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                                <span x-text="client.phone" class="dir-ltr"></span>
                                            </span>
                                            <span x-show="client.national_code" class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                                                <span x-text="client.national_code"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-gray-300 opacity-0 group-hover:opacity-100 group-hover:text-indigo-500 transition-all transform -translate-x-2 group-hover:translate-x-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- پیام بدون نتیجه --}}
                <div x-cloak x-show="showResults && clients.length === 0 && searchQuery.length >= 2 && !isLoading"
                     x-transition
                     class="absolute top-full right-0 mt-2 w-full bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl p-6 text-center z-50">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">نتیجه‌ای یافت نشد</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مطمئن شوید کلمه را به درستی تایپ کرده‌اید.</p>
                </div>
            </div>
        </div>

        {{-- آیکون‌های اکشن سمت چپ --}}
        <div class="flex items-center gap-1 sm:gap-2 mr-auto">

            {{-- دکمه تغییر تم --}}
            <button @click="cycleTheme()"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-transparent dark:border-gray-700"
                    :title="themeTitle()">
                <svg x-show="themeIcon==='dark'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" /></svg>
                <svg x-show="themeIcon==='light'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>
                <svg x-show="themeIcon==='system'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M3 5a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1v-10z" /><path d="M7 20h10" /><path d="M9 16v4" /><path d="M15 16v4" /></svg>
            </button>

            {{-- اعلانات --}}
            <div class="relative" x-data="{open:false}">
                <button @click="open=!open"
                        class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-transparent dark:border-gray-700"
                        aria-haspopup="true" :aria-expanded="open">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
                        <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                    </svg>
                    <span class="absolute top-2 right-2.5 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 border-2 border-white dark:border-gray-800"></span>
                    </span>
                </button>

                <div x-cloak x-show="open" @click.outside="open=false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute left-0 mt-3 w-80 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-2xl overflow-hidden z-50">

                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/30">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">اعلان‌ها</span>
                        <span class="bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 text-[10px] font-bold px-2 py-0.5 rounded-md">۳ جدید</span>
                    </div>

                    <div class="max-h-80 overflow-y-auto custom-scrollbar">
                        <a href="#" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-50 dark:border-gray-700/50 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">پرداخت جدید ثبت شد.</p>
                                <span class="text-[10px] text-gray-400 mt-0.5">۵ دقیقه پیش</span>
                            </div>
                        </a>
                        <a href="#" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-50 dark:border-gray-700/50 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">کاربر جدید عضو شد.</p>
                                <span class="text-[10px] text-gray-400 mt-0.5">۱ ساعت پیش</span>
                            </div>
                        </a>
                    </div>

                    <div class="p-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 text-center">
                        <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">نمایش همه اعلان‌ها</a>
                    </div>
                </div>
            </div>

            {{-- منوی کاربر --}}
            <div class="relative" x-data="{open:false}">
                <button @click="open=!open"
                        class="flex items-center gap-2 p-1.5 pr-3 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors border border-transparent dark:border-gray-700">
                    <span class="hidden md:inline text-sm font-bold text-gray-700 dark:text-gray-200">{{ auth()->user()->name ?? 'مدیر سیستم' }}</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shadow-md">
                        {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                    </div>
                    <svg class="hidden md:inline w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9l6 6l6-6" />
                    </svg>
                </button>

                <div x-cloak x-show="open" @click.outside="open=false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute left-0 mt-3 w-56 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-2xl overflow-hidden z-50 p-2">

                    <div class="px-4 py-3 mb-2 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ auth()->user()->name ?? 'مدیر سیستم' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email ?? 'admin@example.com' }}</p>
                    </div>

                    <a href="#" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        پروفایل کاربری
                    </a>

                    <a href="#" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        تنظیمات
                    </a>

                    <div class="h-px bg-gray-100 dark:bg-gray-700 my-1 mx-2"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full flex items-center gap-2 px-3 py-2 text-sm font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            خروج از حساب
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
