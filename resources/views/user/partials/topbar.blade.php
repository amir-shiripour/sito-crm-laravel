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
        @if(Route::has('user.clients.search'))
        <div class="hidden md:flex flex-1 items-center max-w-2xl">
            <div class="relative w-full"
                 x-data="{
                     searchQuery: '',
                     clients: [],
                     isLoading: false,
                     showResults: false,
                     selectedIndex: -1,
                     async search() {
                         if (this.searchQuery.trim().length < 2) {
                             this.clients = [];
                             this.showResults = false;
                             return;
                         }
                         this.isLoading = true;
                         try {
                             const response = await fetch('{{ route('user.clients.search') }}?q=' + encodeURIComponent(this.searchQuery.trim()), {
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
                        const baseUrl = '{{ Route::has('user.clients.show') ? route('user.clients.show', ['client' => 0]) : '' }}';
                        if (baseUrl) {
                            window.location.href = baseUrl.replace('/0', '/' + client.id);
                        }
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
                       @focus="if(searchQuery.trim().length >= 2) showResults = true"
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
                                            <span x-show="client.case_number" class="flex items-center gap-1">
                                                 <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                                                 <span>پرونده: <span x-text="client.case_number"></span></span>
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
                <div x-cloak x-show="showResults && clients.length === 0 && searchQuery.trim().length >= 2 && !isLoading"
                     x-transition
                     class="absolute top-full right-0 mt-2 w-full bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl p-6 text-center z-50">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">نتیجه‌ای یافت نشد</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مطمئن شوید کلمه را به درستی تایپ کرده‌اید.</p>
                </div>
            </div>
        </div>
        @endif

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

            @if(Route::has('user.notifications.index') && auth()->check() && auth()->user()->can('notifications.view'))
                @php
                    $unreadNotificationsCount = 0;
                    $initialNotifications = collect();
                    $soundEnabled = true;
                    if (class_exists(\Modules\Notifications\Entities\Notification::class)) {
                        $user = auth()->user();
                        $unreadNotificationsCount = \Modules\Notifications\Entities\Notification::where('notifiable_type', get_class($user))
                            ->where('notifiable_id', $user->id)
                            ->whereNull('read_at')
                            ->count();
                        
                        $latestList = \Modules\Notifications\Entities\Notification::where('notifiable_type', get_class($user))
                            ->where('notifiable_id', $user->id)
                            ->orderByDesc('created_at')
                            ->take(6)
                            ->get();

                        $initialNotifications = $latestList->map(function($n) {
                            return [
                                'id'          => (string) $n->id,
                                'title'       => (string) $n->title,
                                'message'     => (string) $n->message,
                                'action_url'  => $n->action_url,
                                'category'    => (string) $n->category,
                                'priority'    => (string) $n->priority,
                                'severity'    => (string) $n->severity,
                                'is_unread'   => is_null($n->read_at),
                                'created_at'  => $n->created_at->diffForHumans(),
                            ];
                        })->values();

                        if (class_exists(\Modules\Notifications\Entities\NotificationUserSetting::class)) {
                            $setting = \Modules\Notifications\Entities\NotificationUserSetting::where('user_id', $user->id)->first();
                            $soundEnabled = $setting ? (bool) $setting->sound_enabled : true;
                        }
                    }
                @endphp

                {{-- اعلانات با به‌روزرسانی آنی و زنده --}}
                <div class="relative" x-data="{
                    open: false,
                    unreadCount: {{ $unreadNotificationsCount }},
                    notifications: {{ Js::from($initialNotifications) }},
                    soundEnabled: {{ $soundEnabled ? 'true' : 'false' }},

                    playSound() {
                        if (!this.soundEnabled) return;
                        try {
                            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                            const osc = audioCtx.createOscillator();
                            const gain = audioCtx.createGain();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(587.33, audioCtx.currentTime);
                            osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1);
                            gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.35);
                            osc.connect(gain);
                            gain.connect(audioCtx.destination);
                            osc.start();
                            osc.stop(audioCtx.currentTime + 0.35);
                        } catch(e) {}
                    },

                    async fetchLatest() {
                        try {
                            const res = await fetch('{{ route('user.notifications.latest') }}');
                            const data = await res.json();
                            if (typeof data.unreadCount === 'number') {
                                this.unreadCount = data.unreadCount;
                            }
                            if (Array.isArray(data.items)) {
                                this.notifications = data.items;
                            }
                        } catch (error) {
                            console.error('Error fetching latest notifications:', error);
                        }
                    },

                    toggleOpen() {
                        this.open = !this.open;
                        if (this.open) {
                            this.fetchLatest();
                        }
                    },

                    init() {
                        // ۱. در صورتی که Laravel Echo یا Reverb/Pusher فعال باشد، اعلان به صورت آنی و لحظه‌ای دریافت می‌شود
                        if (window.Echo && {{ auth()->check() ? auth()->id() : 'null' }}) {
                            try {
                                window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
                                    .notification((notification) => {
                                        this.playSound();
                                        this.fetchLatest();
                                    });
                            } catch(e) {}
                        }

                        // ۲. بررسی دوره‌ای (Polling) برای تضمین همگام‌سازی بلادرنگ شمارنده، آیتم‌ها و صدای هشدار
                        setInterval(() => {
                            fetch('{{ route('user.notifications.unread-count') }}')
                                .then(res => res.json())
                                .then(data => {
                                    if (typeof data.count === 'number') {
                                        const previousCount = this.unreadCount;
                                        this.unreadCount = data.count;
                                        if (data.count > previousCount) {
                                            this.playSound();
                                            this.fetchLatest();
                                        } else if (data.count !== previousCount) {
                                            this.fetchLatest();
                                        }
                                    }
                                })
                                .catch(() => {});
                        }, 15000);
                    },

                    async markAsRead(id, actionUrl, event) {
                        if (event) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        try {
                            const response = await fetch('{{ route('user.notifications.mark-read', ['id' => 'NOTIFICATION_ID']) }}'.replace('NOTIFICATION_ID', id), {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                            const data = await response.json();
                            if (data.success) {
                                const item = this.notifications.find(n => n.id === id);
                                if (item && item.is_unread) {
                                    item.is_unread = false;
                                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                                }
                                if (typeof showToast === 'function') {
                                    showToast('success', data.message);
                                }
                                if (actionUrl) {
                                    window.location.href = actionUrl;
                                }
                            }
                        } catch (error) {
                            console.error('Error marking notification as read:', error);
                        }
                    },

                    async markAllAsRead() {
                        try {
                            const response = await fetch('{{ route('user.notifications.mark-all-read') }}', {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.unreadCount = 0;
                                this.notifications.forEach(n => n.is_unread = false);
                                if (typeof showToast === 'function') {
                                    showToast('success', data.message);
                                }
                            }
                        } catch (error) {
                            console.error('Error marking all notifications as read:', error);
                        }
                    },

                    deleteModal: {
                        open: false,
                        id: null,
                        loading: false
                    },

                    openDeleteModal(id, event) {
                        if (event) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        this.deleteModal.id = id;
                        this.deleteModal.loading = false;
                        this.deleteModal.open = true;
                    },

                    closeDeleteModal() {
                        this.deleteModal.open = false;
                        this.deleteModal.id = null;
                        this.deleteModal.loading = false;
                    },

                    async confirmDelete() {
                        if (!this.deleteModal.id || this.deleteModal.loading) return;
                        const id = this.deleteModal.id;
                        this.deleteModal.loading = true;
                        try {
                            const response = await fetch('{{ route('user.notifications.destroy', ['id' => 'NOTIFICATION_ID']) }}'.replace('NOTIFICATION_ID', id), {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                            const data = await response.json();
                            if (data.success) {
                                const item = this.notifications.find(n => n.id === id);
                                if (item && item.is_unread) {
                                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                                }
                                this.notifications = this.notifications.filter(n => n.id !== id);
                                this.closeDeleteModal();
                                if (typeof showToast === 'function') {
                                    showToast('success', data.message || 'اعلان با موفقیت حذف شد.');
                                }
                            } else {
                                this.closeDeleteModal();
                                if (typeof showToast === 'function') {
                                    showToast('error', data.message || 'خطا در حذف اعلان.');
                                }
                            }
                        } catch (error) {
                            console.error('Error deleting notification:', error);
                            this.closeDeleteModal();
                            if (typeof showToast === 'function') {
                                showToast('error', 'خطایی در ارتباط با سرور رخ داد.');
                            }
                        }
                    }
                }">
                    {{-- دکمه زنگوله --}}
                    <button @click="toggleOpen()"
                            class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-transparent dark:border-gray-700"
                            aria-haspopup="true" :aria-expanded="open">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
                            <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                        </svg>
                        <span x-show="unreadCount > 0" class="absolute top-2 right-2.5 flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 border-2 border-white dark:border-gray-800"></span>
                        </span>
                    </button>

                    {{-- دراپ‌داون لیست اعلان‌ها --}}
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
                            <div class="flex items-center gap-3">
                                <span x-show="unreadCount > 0" x-text="unreadCount + ' جدید'" class="bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 text-[10px] font-bold px-2 py-0.5 rounded-md"></span>
                                <button x-show="unreadCount > 0" @click="markAllAsRead()" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                                    همه خوانده شد
                                </button>
                            </div>
                        </div>

                        <div id="dropdown-notifications-list" class="max-h-[24rem] overflow-y-auto custom-scrollbar divide-y divide-gray-100/60 dark:divide-gray-700/40">
                            <template x-for="n in notifications" :key="n.id">
                                <div :id="'dropdown-notification-' + n.id" 
                                     class="relative flex gap-3 px-4 py-3.5 transition-all duration-200 group"
                                     :class="n.is_unread ? 'bg-indigo-50/30 dark:bg-indigo-950/10' : 'hover:bg-gray-50/50 dark:hover:bg-gray-800/40'">
                                    
                                    <template x-if="n.action_url">
                                        <a href="#" @click="markAsRead(n.id, n.action_url, $event)" class="absolute inset-0 z-0"></a>
                                    </template>
                                    <template x-if="!n.action_url">
                                        <a href="#" @click="markAsRead(n.id, null, $event)" class="absolute inset-0 z-0"></a>
                                    </template>

                                    {{-- آیکون متناسب با وضعیت و اهمیت --}}
                                    <div class="relative z-10 w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                         :class="{
                                            'bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400': n.severity === 'danger' || n.priority === 'urgent',
                                            'bg-amber-100 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400': (n.severity === 'warning' || n.priority === 'high') && n.severity !== 'danger' && n.priority !== 'urgent',
                                            'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400': n.severity === 'success',
                                            'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400': n.severity === 'info' || (!n.severity && n.priority !== 'urgent' && n.priority !== 'high' && n.severity !== 'success')
                                         }">
                                        <template x-if="n.severity === 'danger' || n.priority === 'urgent'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </template>
                                        <template x-if="(n.severity === 'warning' || n.priority === 'high') && n.severity !== 'danger' && n.priority !== 'urgent'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </template>
                                        <template x-if="n.severity === 'success'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </template>
                                        <template x-if="n.severity !== 'danger' && n.priority !== 'urgent' && n.severity !== 'warning' && n.priority !== 'high' && n.severity !== 'success'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </template>
                                    </div>

                                    {{-- محتوای متن اعلان --}}
                                    <div class="relative z-10 flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1">
                                            <p class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="n.title"></p>
                                            <span x-show="n.is_unread" class="unread-dot w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400 shrink-0"></span>
                                        </div>
                                        <p class="text-[11px] text-gray-600 dark:text-gray-400 mt-1 leading-relaxed line-clamp-2" x-text="n.message"></p>
                                        <div class="flex items-center justify-between mt-2 gap-2">
                                            <span class="text-[9px] text-gray-400 dark:text-gray-500 font-medium" x-text="n.created_at"></span>
                                            
                                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                                <template x-if="n.is_unread">
                                                    <button @click.stop.prevent="markAsRead(n.id, null, $event)"
                                                            type="button"
                                                            title="علامت خوانده شده"
                                                            class="mark-read-btn p-1 rounded-md bg-gray-100 dark:bg-gray-800 hover:bg-gray-250 dark:hover:bg-gray-700/60 text-gray-400 hover:text-gray-750 dark:hover:text-gray-200 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                </template>
                                                <button @click.stop.prevent="openDeleteModal(n.id, $event)"
                                                        type="button"
                                                        title="حذف اعلان"
                                                        class="p-1 rounded-md bg-rose-50/50 dark:bg-rose-950/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-gray-400 hover:text-rose-600 dark:hover:text-rose-450 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- وضعیت خالی بودن --}}
                            <div x-show="notifications.length === 0" class="flex flex-col items-center justify-center py-10 px-4 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-900/30 flex items-center justify-center text-gray-300 dark:text-gray-600 mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4.5m16 3H4" />
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-gray-950 dark:text-white">هیچ اعلانی یافت نشد</p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 max-w-xs">
                                    در حال حاضر هیچ پیام یا اعلانی برای نمایش وجود ندارد.
                                </p>
                            </div>
                        </div>

                        <div class="p-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 text-center">
                            <a href="{{ route('user.notifications.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">نمایش همه اعلان‌ها</a>
                        </div>
                    </div>

                    {{-- مودال اختصاصی تایید حذف پنل برای هدر (Panel Delete Alert Modal) --}}
                    <div x-cloak x-show="deleteModal.open"
                         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">

                        <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 text-center space-y-4 transform transition-all"
                             @click.outside="closeDeleteModal()"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="scale-95 opacity-0"
                             x-transition:enter-end="scale-100 opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="scale-100 opacity-100"
                             x-transition:leave-end="scale-95 opacity-0">

                            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 flex items-center justify-center mx-auto border border-rose-100 dark:border-rose-900/30">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">حذف اعلان</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">آیا از حذف این اعلان از سیستم اطمینان دارید؟ این عملیات غیرقابل بازگشت است.</p>
                            </div>

                            <div class="flex items-center justify-center gap-3 pt-2">
                                <button type="button" @click="closeDeleteModal()" :disabled="deleteModal.loading"
                                        class="w-1/2 py-2.5 text-xs font-bold rounded-xl text-gray-700 bg-gray-100 hover:bg-gray-200 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                                    انصراف
                                </button>
                                <button type="button" @click="confirmDelete()" :disabled="deleteModal.loading"
                                        class="w-1/2 py-2.5 text-xs font-bold rounded-xl text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-sm inline-flex items-center justify-center gap-1.5">
                                    <span x-show="!deleteModal.loading">بله، حذف شود</span>
                                    <span x-cloak x-show="deleteModal.loading" class="inline-flex items-center gap-1">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        در حال حذف...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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

                    <a href="{{ route('profile.show') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 rounded-lg transition-colors">
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
