{{-- modules/Reminders/resources/views/widgets/today-reminders.blade.php --}}

@php
    use Modules\Reminders\Entities\Reminder;
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;

    $user = auth()->user();
    $now  = now();
    $endOfDay = $now->copy()->endOfDay();

    $reminders = Reminder::query()
        ->where('user_id', $user->id)
        ->open()
        ->where('remind_at', '<=', $endOfDay)
        ->with('task')
        ->get()
        ->sortByDesc(fn(Reminder $r) => $r->relatedPriorityWeight())
        ->values();

    // Grouping
    $overdueReminders = $reminders->filter(fn($r) => $r->remind_at && $r->remind_at->isBefore($now->copy()->startOfDay()))->values();
    $upcomingReminders = $reminders->filter(fn($r) => !$r->remind_at || $r->remind_at->isBetween($now->copy()->startOfDay(), $endOfDay))->values();

    $statusBadges = [
        Reminder::STATUS_OPEN     => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700/60',
        Reminder::STATUS_DONE     => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700/60',
        Reminder::STATUS_CANCELED => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600',
    ];
@endphp

<div id="today-reminders-widget"
     class="flex flex-col"
     x-data="todayRemindersWidget()">

    {{-- هدر ویجت --}}
    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800/60">
        <div class="flex items-center gap-3">
            <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 21h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 4h3"/>
                </svg>
                @if($reminders->count() > 0)
                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border-2 border-white dark:border-gray-800"></span>
                    </span>
                @endif
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white tracking-tight">
                    یادآوری‌های امروز
                </h2>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                    @if($reminders->count() > 0)
                        {{ $reminders->count() }} یادآوری در انتظار بررسی
                    @else
                        روز آرامی در پیش دارید
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button @click="refreshWidget()" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400" title="بروزرسانی">
                <svg class="w-4 h-4" :class="{'animate-spin': isRefreshing}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
            <a href="{{ route('user.reminders.index') }}" class="text-[12px] font-medium text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40">
                همه یادآوری‌ها
            </a>
        </div>
    </div>

    {{-- محتوا --}}
    <div class="flex-1 pt-4 overflow-hidden flex flex-col gap-4">

        {{-- Tabs --}}
        @if($reminders->isNotEmpty())
            <div class="flex items-center gap-2 p-1 bg-gray-100/80 dark:bg-gray-900/50 rounded-xl">
                <button @click="activeTab = 'overdue'"
                        :class="activeTab === 'overdue' ? 'bg-white dark:bg-gray-800 shadow-sm text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        class="flex-1 text-[12px] py-1.5 rounded-lg transition-all relative flex items-center justify-center gap-1.5">
                    گذشته
                    @if($overdueReminders->count() > 0)
                        <span class="px-1.5 py-0.5 rounded-md bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 text-[10px]">{{ $overdueReminders->count() }}</span>
                    @endif
                </button>
                <button @click="activeTab = 'upcoming'"
                        :class="activeTab === 'upcoming' ? 'bg-white dark:bg-gray-800 shadow-sm text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        class="flex-1 text-[12px] py-1.5 rounded-lg transition-all relative flex items-center justify-center gap-1.5">
                    امروز
                    @if($upcomingReminders->count() > 0)
                        <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px]">{{ $upcomingReminders->count() }}</span>
                    @endif
                </button>
            </div>
        @endif

        <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700 pr-1 max-h-[668px]">
            @if($reminders->isEmpty())
                <div class="h-full flex flex-col items-center justify-center text-center space-y-3 opacity-80">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900/40 rounded-full flex items-center justify-center border border-gray-100 dark:border-gray-800/60 mb-2">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">همه کارهای امروز انجام شده!</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">می‌توانید استراحت کنید یا به سراغ کارهای فردا بروید.</p>
                    </div>
                </div>
            @else

                {{-- Overdue List --}}
                <div x-show="activeTab === 'overdue'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3">
                    @forelse($overdueReminders as $reminder)
                        @include('reminders::widgets.partials._reminder_card', ['reminder' => $reminder, 'isOverdue' => true])
                    @empty
                        <div class="text-center py-8 text-xs text-gray-500 dark:text-gray-400">
                            یادآوری گذشته‌ای ندارید. عالی است! 👏
                        </div>
                    @endforelse
                </div>

                {{-- Upcoming List --}}
                <div x-show="activeTab === 'upcoming'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3">
                    @forelse($upcomingReminders as $reminder)
                        @include('reminders::widgets.partials._reminder_card', ['reminder' => $reminder, 'isOverdue' => false])
                    @empty
                        <div class="text-center py-8 text-xs text-gray-500 dark:text-gray-400">
                            برای ادامه امروز یادآوری ندارید.
                        </div>
                    @endforelse
                </div>

            @endif
        </div>
    </div>

    {{-- مودال تاریخچه تعویق یادآوری --}}
    <div x-show="showHistoryModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-950/40 dark:bg-gray-950/60 backdrop-blur-sm" @click="showHistoryModal = false"></div>

        {{-- Content Card --}}
        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-2xl p-6 transition-all duration-300 transform scale-100"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-95 translate-y-4"
             x-transition:enter-end="scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="scale-100 translate-y-0"
             x-transition:leave-end="scale-95 translate-y-4">

            {{-- Header --}}
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="text-orange-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">تاریخچه تعویق</h3>
                </div>
                <button @click="showHistoryModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Title of Reminder --}}
            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 font-semibold truncate bg-gray-50 dark:bg-gray-900/40 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800/80">
                 مورد: <span class="text-gray-800 dark:text-gray-200" x-text="historyReminderTitle"></span>
            </div>

            {{-- Logs Timeline --}}
            <div class="mt-4 max-h-60 overflow-y-auto space-y-4 pr-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-850">
                <template x-if="isLoadingHistory">
                    <div class="flex justify-center items-center py-8">
                        <svg class="animate-spin h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </template>

                <template x-if="!isLoadingHistory && historyLogs.length === 0">
                    <div class="text-center py-6 text-xs text-gray-400 dark:text-gray-500">
                        تاریخچه‌ای برای این یادآوری وجود ندارد.
                    </div>
                </template>

                <template x-if="!isLoadingHistory && historyLogs.length > 0">
                    <div class="relative border-r-2 border-gray-100 dark:border-gray-700 mr-2 space-y-4">
                        <template x-for="(log, idx) in historyLogs" :key="log.id">
                            <div class="relative pr-6">
                                {{-- Timeline Dot --}}
                                <div class="absolute -right-[6px] top-1.5 w-2.5 h-2.5 rounded-full bg-indigo-500 border-2 border-white dark:border-gray-800"></div>

                                <div class="space-y-1">
                                    <div class="flex justify-between items-center gap-2">
                                        <span class="text-[11px] font-bold text-gray-900 dark:text-white">
                                            تعویق #<span x-text="log.sequence"></span>
                                        </span>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono" x-text="log.created_at"></span>
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                         توسط: <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="log.user_name"></span>
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                         زمان جدید: <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="log.snoozed_to"></span>
                                    </div>
                                    <template x-if="log.reason">
                                        <div class="text-[10px] text-gray-600 dark:text-gray-400 bg-gray-50/60 dark:bg-gray-900/20 p-2 rounded-lg border border-gray-100 dark:border-gray-800 mt-1.5" x-text="'دلیل: ' + log.reason"></div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <button @click="showHistoryModal = false" class="px-4 py-2 text-xs font-bold rounded-xl text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-750 transition-all">
                    بستن
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Alpine Component Logic --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('todayRemindersWidget', () => ({
            activeTab: '{{ $overdueReminders->count() > 0 ? "overdue" : "upcoming" }}',
            isRefreshing: false,

            // تاریخچه تعویق
            showHistoryModal: false,
            isLoadingHistory: false,
            historyLogs: [],
            historyReminderTitle: '',

            init() {
                // Auto refresh every 5 minutes (300000 ms)
                setInterval(() => {
                    this.refreshWidget();
                }, 300000);
            },
            async refreshWidget() {
                if (this.isRefreshing) return;
                this.isRefreshing = true;
                try {
                    let res = await fetch(window.location.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    let html = await res.text();
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let newWidget = doc.querySelector('#today-reminders-widget');

                    if (newWidget) {
                        let currentTab = this.activeTab;
                        this.$root.innerHTML = newWidget.innerHTML;

                        // If we were on overdue tab but there are no overdue reminders anymore, switch to upcoming
                        let hasOverdue = doc.querySelectorAll('#today-reminders-widget [x-show="activeTab === \'overdue\'"] .reminder-card').length > 0;
                        if (currentTab === 'overdue' && !hasOverdue) {
                            this.activeTab = 'upcoming';
                        } else {
                            this.activeTab = currentTab;
                        }
                    }
                } catch (error) {
                    console.error("Failed to refresh reminders widget", error);
                } finally {
                    setTimeout(() => { this.isRefreshing = false; }, 500);
                }
            },
            async fetchHistory(url, title) {
                this.historyReminderTitle = title;
                this.historyLogs = [];
                this.isLoadingHistory = true;
                this.showHistoryModal = true;

                try {
                    let res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    let data = await res.json();
                    if (data.success) {
                        this.historyLogs = data.logs;
                    }
                } catch (error) {
                    console.error("Failed to fetch snooze history", error);
                } finally {
                    this.isLoadingHistory = false;
                }
            },
            async sendAction(url, method, data = {}, el) {
                let card = el.closest('.reminder-card');
                if(card) { card.style.opacity = '0.5'; card.style.pointerEvents = 'none'; }

                try {
                    let res = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    if (res.ok) {
                        // Hide the card
                        if(card) {
                            card.style.display = 'none';
                        }
                        // Refresh to update counts
                        this.refreshWidget();
                    } else {
                        // Parse and show validation error if available
                        let errData = await res.json();
                        alert(errData.message || 'خطایی رخ داد. لطفا دوباره تلاش کنید.');
                        if(card) { card.style.opacity = '1'; card.style.pointerEvents = 'auto'; }
                    }
                } catch (e) {
                    alert('خطایی در ارتباط با سرور رخ داد.');
                    if(card) { card.style.opacity = '1'; card.style.pointerEvents = 'auto'; }
                }
            }
        }));
    });
</script>
