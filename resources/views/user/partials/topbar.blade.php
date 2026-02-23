<header
    class="h-16 sticky top-0 z-20 bg-white/80 dark:bg-gray-800/80 backdrop-blur border-b border-gray-200 dark:border-gray-700">
    <div class="h-full px-4 flex items-center justify-between gap-3">
        {{-- Hamburger button for mobile --}}
        <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" @click="mobileOpen = true"
                aria-label="Open sidebar">
            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" width="24"
                 height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 6l16 0" />
                <path d="M4 12l16 0" />
                <path d="M4 18l16 0" />
            </svg>
        </button>

        <div class="hidden md:flex flex-1 items-center">
            <div class="relative w-full max-w-md" x-data="{
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
                            if (!response.ok) {
                                throw new Error('Response not ok: ' + response.status);
                            }
                            const data = await response.json();
                            // Handle both array format and object with results property
                            this.clients = Array.isArray(data) ? data : (Array.isArray(data?.results) ? data.results : (Array.isArray(data?.data) ? data.data : []));
                            // Debug: log first client to see matched_field
                            if (this.clients.length > 0) {
                                console.log('First client:', this.clients[0]);
                                console.log('matched_field value:', this.clients[0].matched_field);
                            }
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
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="absolute right-3 top-2.5 w-5 h-5 text-gray-400 icon icon-tabler icons-tabler-outline icon-tabler-search">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                    <path d="M21 21l-6 -6" />
                </svg>
                <input type="text"
                       placeholder="جستجوی {{config('clients.labels.singular')}} (نام، موبایل، کد ملی، شماره پرونده)..."
                       x-model="searchQuery" @input.debounce.300ms="search()" @keydown="handleKeydown($event)"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />

                <!-- Loading indicator -->
                <div x-show="isLoading" class="absolute left-3 top-2.5">
                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>

                <!-- Results dropdown -->
                <div x-cloak x-show="showResults && Array.isArray(clients) && clients.length > 0" x-transition
                     class="absolute top-full mt-2 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-80 overflow-y-auto z-50">
                    <template x-for="(client, index) in (Array.isArray(clients) ? clients : [])"
                              :key="client?.id || index">
                        <div @click="selectClient(client)" @mouseenter="selectedIndex = index" :class="{
                                'bg-indigo-50 dark:bg-indigo-900/20': selectedIndex === index,
                                'bg-white dark:bg-gray-800': selectedIndex !== index
                            }"
                             class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 dark:text-gray-100"
                                         x-text="client.full_name || 'بدون نام'"></div>
                                    <div
                                        class="flex flex-wrap gap-x-3 gap-y-1 text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        <!-- نمایش همه اطلاعات موجود -->
                                        <span x-show="client.phone" x-text="'📞 ' + client.phone"></span>
                                        <span x-show="client.national_code"
                                              x-text="'🆔 ' + client.national_code"></span>
                                        <span x-show="client.case_number" x-text="'📋 ' + client.case_number"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- No results message -->
                <div x-cloak x-show="showResults && clients.length === 0 && searchQuery.length >= 2 && !isLoading"
                     x-transition
                     class="absolute top-full mt-2 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4 text-center text-sm text-gray-500 dark:text-gray-400 z-50">
                    نتیجه‌ای یافت نشد
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="relative">
                <button @click="cycleTheme()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                        :title="themeTitle()">
                    <svg x-show="themeIcon==='dark'" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-moon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
                    </svg>
                    <svg x-show="themeIcon==='light'" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-sun">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                        <path
                            d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
                    </svg>
                    <svg x-show="themeIcon==='system'" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round"
                         class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-device-desktop">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 5a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1v-10z" />
                        <path d="M7 20h10" />
                        <path d="M9 16v4" />
                        <path d="M15 16v4" />
                    </svg>
                </button>
            </div>

            <div class="relative" x-data="{open:false}">
                <button @click="open=!open" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                        aria-haspopup="true" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-bell-ringing">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path
                            d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
                        <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                        <path d="M21 6.727a11.05 11.05 0 0 0 -2.794 -3.727" />
                        <path d="M3 6.727a11.05 11.05 0 0 1 2.792 -3.727" />
                    </svg>
                    <span
                        class="absolute -top-0.5 -left-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 text-[10px] text-white px-1">3</span>
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
                <button @click="open=!open"
                        class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    {{-- <img src="" class="w-8 h-8 rounded-full" alt="avatar">--}}
                    <span class="hidden md:inline text-sm font-medium">{{ auth()->user()->name ?? 'کاربر' }}</span>
                    <svg class="hidden md:inline w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5" />
                    </svg>
                </button>
                <div x-cloak x-show="open" @click.outside="open=false" x-transition
                     class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden z-50">
                    <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">پروفایل</a>
                    <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">تنظیمات</a>
                    <form method="POST" action="{{ route('logout') }}"
                          class="border-t border-gray-200 dark:border-gray-700">
                        @csrf
                        <button
                            class="w-full text-start px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">خروج</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
