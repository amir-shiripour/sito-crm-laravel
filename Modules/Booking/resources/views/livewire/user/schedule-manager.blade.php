<div class="space-y-6" x-data="{
    draggedAppointmentId: null,
    isDragging: false,
    showModal: @entangle('showModal'),
    showDetailsModal: @entangle('showDetailsModal'),
    init() {
        this.$watch('showModal', value => this.updateBodyScroll());
        this.$watch('showDetailsModal', value => this.updateBodyScroll());
        this.updateBodyScroll();
    },
    updateBodyScroll() {
        if (this.showModal || this.showDetailsModal) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    },
    handleDragStart(e, id) {
        this.draggedAppointmentId = id;
        this.isDragging = true;
        e.dataTransfer.setData('text/plain', id);
        e.dataTransfer.effectAllowed = 'move';
    },
    handleDragEnd(e) {
        this.isDragging = false;
        this.draggedAppointmentId = null;
    },
    handleDrop(e, providerId, startTime) {
        e.preventDefault();
        this.isDragging = false;
        const id = this.draggedAppointmentId || e.dataTransfer.getData('text/plain');
        if (id) {
            $wire.rescheduleAppointment(parseInt(id), parseInt(providerId), startTime);
        }
    }
}">

    {{-- Toast Notifications --}}
    @if ($toastSuccess)
        <div class="flex items-center justify-between gap-3 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-sm dark:bg-emerald-900/40 dark:text-emerald-200 transition-all animate-in fade-in z-50">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="text-sm font-bold">{{ $toastSuccess }}</span>
            </div>
            <button wire:click="$set('toastSuccess', null)" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-100 font-bold">✕</button>
        </div>
    @endif

    @if ($toastError)
        <div class="flex items-center justify-between gap-3 rounded-xl border-l-4 border-rose-500 bg-rose-50 px-5 py-4 text-rose-800 shadow-sm dark:bg-rose-900/40 dark:text-rose-200 transition-all animate-in fade-in z-50">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm font-bold">{{ $toastError }}</span>
            </div>
            <button wire:click="$set('toastError', null)" class="text-rose-600 hover:text-rose-900 dark:text-rose-400 dark:hover:text-rose-100 font-bold">✕</button>
        </div>
    @endif

    {{-- Executive Dashboard KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 {{ ($isQueueEnabled && $waitlistCount > 0) ? 'lg:grid-cols-5' : '' }} gap-4">
        {{-- Card 1: Total --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between transition-shadow hover:shadow-md">
            <div>
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">کل نوبت‌های امروز</span>
                <span class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5 block">{{ $totalAppointmentsCount }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl">
                📅
            </div>
        </div>

        {{-- Card 2: Confirmed --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between transition-shadow hover:shadow-md">
            <div>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 block">نوبت‌های قطعی</span>
                <span class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1.5 block">{{ $confirmedCount }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl">
                🟢
            </div>
        </div>

        {{-- Card 3: Drafts --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between transition-shadow hover:shadow-md">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">پیش‌نویس‌ها</span>
                    @if ($draftCount > 0)
                        <button wire:click="confirmAllDrafts" class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/50 dark:text-emerald-300 transition" title="تایید قطعی همه">تایید همه</button>
                    @endif
                </div>
                <span class="text-2xl font-black text-gray-700 dark:text-gray-300 mt-1.5 block">{{ $draftCount }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 flex items-center justify-center font-bold text-xl">
                📝
            </div>
        </div>

        {{-- Card 4: Capacity --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between transition-shadow hover:shadow-md">
            <div>
                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 block">ظرفیت خالی پذیرش</span>
                <span class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-1.5 block">{{ $totalRemainingCapacitySum }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xl">
                ⏳
            </div>
        </div>

        {{-- Card 5: Smart Waitlist (When Queue is Enabled and has waiting clients) --}}
        @if ($isQueueEnabled && $waitlistCount > 0)
            <div wire:click="toggleWaitlistDrawer"
                 class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-orange-500/10 dark:from-amber-950/40 dark:to-orange-950/30 rounded-2xl p-5 border border-amber-300/80 dark:border-amber-700/80 shadow-sm flex items-center justify-between transition-all hover:shadow-md hover:border-amber-400 cursor-pointer group">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-black text-amber-800 dark:text-amber-300 block">در صف انتظار</span>
                        <span class="text-[9px] font-extrabold bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-200 px-1.5 py-0.5 rounded-full animate-pulse">مشاهده صف</span>
                    </div>
                    <span class="text-2xl font-black text-amber-900 dark:text-amber-100 mt-1.5 block">{{ $waitlistCount }} نفر</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center font-black text-lg shadow-md shadow-amber-500/25 group-hover:scale-108 transition-transform">
                    📋
                </div>
            </div>
        @endif
    </div>

    {{-- Main Toolbar & Advanced Searchable Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        {{-- Row 1: Date Navigation & Calendar View Switchers --}}
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">

            {{-- Date Navigation & Jalali DatePicker --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap sm:flex-nowrap">
                <div class="flex items-center bg-gray-50 dark:bg-gray-900 rounded-xl p-1 border border-gray-200 dark:border-gray-700/80 shrink-0">
                    <button wire:click="previousPeriod" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition" title="بازه قبل">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    <button wire:click="today" class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:bg-white dark:hover:bg-gray-800 rounded-lg transition">امروز</button>
                    <button wire:click="nextPeriod" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition" title="بازه بعد">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                </div>

                <div class="relative flex-1 min-w-[160px] sm:min-w-[200px]">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <input type="text"
                           wire:model.live="selectedDateJalali"
                           data-jdp
                           data-jdp-only-date
                           class="w-full pr-10 pl-3 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm font-black text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 cursor-pointer text-center hover:bg-white dark:hover:bg-gray-800 transition-colors"
                           placeholder="انتخاب تاریخ...">
                </div>

                <div class="hidden sm:block text-xs font-black text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-950/50 px-3.5 py-2.5 rounded-xl whitespace-nowrap border border-indigo-100 dark:border-indigo-900/60 shadow-xs">
                    @if ($calendarView === 'week')
                        📅 هفته: {{ $weekDays[0]['jalali_date'] ?? '' }} تا {{ $weekDays[6]['jalali_date'] ?? '' }}
                    @elseif ($calendarView === 'month')
                        🗓️ {{ $monthData['year_month_title'] ?? '' }}
                    @else
                        📆 {{ $dayOfWeekJalali }}
                    @endif
                </div>
            </div>

            {{-- View Switchers & Waitlist Drawer Trigger --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap sm:flex-nowrap justify-between lg:justify-end">
                {{-- Smart Waitlist Drawer Trigger Button --}}
                @if ($isQueueEnabled)
                    <button type="button"
                            wire:click="toggleWaitlistDrawer"
                            class="px-3.5 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 border shadow-2xs {{ $showWaitlistDrawer ? 'bg-amber-500 text-white border-amber-600 ring-2 ring-amber-300 dark:ring-amber-800' : ($waitlistCount > 0 ? 'bg-amber-50 dark:bg-amber-950/60 border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200 hover:bg-amber-100 dark:hover:bg-amber-900/60' : 'bg-gray-100 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-800') }}"
                            title="نمایش یا پنهان‌سازی دستیار هوشمند صف انتظار">
                        <svg class="w-4 h-4 {{ $showWaitlistDrawer ? 'text-white' : 'text-amber-600 dark:text-amber-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>صف انتظار</span>
                        @if ($waitlistCount > 0)
                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-black rounded-full {{ $showWaitlistDrawer ? 'bg-white text-amber-700' : 'bg-amber-500 text-white' }}">
                                {{ $waitlistCount }}
                            </span>
                        @endif
                    </button>
                @endif

                {{-- Show Empty Slots Toggle (Visible in Weekly view) --}}
                @if ($calendarView === 'week')
                    <button type="button"
                            wire:click="toggleShowEmptySlots"
                            class="px-3.5 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 border shadow-2xs {{ $showEmptySlots ? 'bg-emerald-50 dark:bg-emerald-950/60 border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200' : 'bg-gray-100 dark:bg-gray-900 border-gray-200 dark:border-gray-700/80 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800' }}"
                            title="نمایش یا عدم نمایش اسلات‌های خالی در تقویم هفتگی">
                        <span class="w-2.5 h-2.5 rounded-full {{ $showEmptySlots ? 'bg-emerald-500 ring-2 ring-emerald-300 dark:ring-emerald-800' : 'bg-gray-400' }}"></span>
                        <span>نمایش اسلات‌های خالی:</span>
                        <span class="font-extrabold">{{ $showEmptySlots ? 'فعال' : 'غیرفعال' }}</span>
                    </button>
                @endif

                <div class="flex items-center bg-gray-100 dark:bg-gray-900 p-1 rounded-xl border border-gray-200 dark:border-gray-700/80">
                    <button wire:click="setCalendarView('day')" class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-lg text-xs font-black transition flex items-center gap-1 {{ $calendarView === 'day' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                        📅 روزانه
                    </button>
                    <button wire:click="setCalendarView('week')" class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-lg text-xs font-black transition flex items-center gap-1 {{ $calendarView === 'week' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                        📋 هفتگی
                    </button>
                    <button wire:click="setCalendarView('month')" class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-lg text-xs font-black transition flex items-center gap-1 {{ $calendarView === 'month' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                        🗓️ ماهانه
                    </button>
                </div>

                @if ($calendarView === 'day')
                    <div class="flex items-center bg-gray-100 dark:bg-gray-900 p-1 rounded-xl border border-gray-200 dark:border-gray-700/80">
                        <button wire:click="setViewMode('grid')" class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-lg text-xs font-black transition flex items-center gap-1 {{ $viewMode === 'grid' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                            ماتریسی
                        </button>
                        <button wire:click="setViewMode('timeline')" class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-lg text-xs font-black transition flex items-center gap-1 {{ $viewMode === 'timeline' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                            تایم‌لاین
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Row 2: Advanced Searchable Filters Grid --}}
        <div class="pt-4 border-t border-gray-100 dark:border-gray-700/80 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- 1. Provider Searchable Dropdown --}}
            <div x-data="{
                open: false,
                search: '',
                selectedId: @entangle('selectedProviderId').live,
                get selectedName() {
                    if (!this.selectedId) return 'همه {{ config("booking.labels.providers") }}';
                    const el = document.getElementById('prov-opt-' + this.selectedId);
                    return el ? el.dataset.name : 'همه {{ config("booking.labels.providers") }}';
                }
            }" @click.outside="open = false" class="relative">
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">👨‍⚕️ {{ config('booking.labels.provider') }}</label>
                <button @click="open = !open" type="button" class="w-full flex items-center justify-between gap-2 px-3 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-black text-gray-800 dark:text-gray-100 hover:bg-white dark:hover:bg-gray-800 transition-all text-right shadow-xs">
                    <div class="flex items-center gap-2 truncate">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                        <span class="truncate" x-text="selectedName"></span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div x-show="open" x-transition.opacity.duration.150ms class="absolute z-50 mt-1 w-full min-w-[220px] bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-2 space-y-1 text-xs" style="display: none;">
                    <div class="relative mb-1">
                        <input type="text" x-model="search" placeholder="جستجوی {{ config('booking.labels.provider') }}..." class="w-full pl-3 pr-8 py-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <div class="max-h-48 overflow-y-auto space-y-0.5 custom-scrollbar">
                        <button type="button" @click="selectedId = null; $wire.set('selectedProviderId', null); open = false;" class="w-full text-right px-3 py-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/50 flex items-center justify-between font-bold" :class="!selectedId ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/60 dark:bg-indigo-950/40' : 'text-gray-700 dark:text-gray-300'">
                            <span>همه {{ config('booking.labels.providers') }}</span>
                            <template x-if="!selectedId">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </template>
                        </button>
                        @foreach ($filterProviders as $prov)
                            <div id="prov-opt-{{ $prov->id }}" data-name="{{ $prov->name }}" class="hidden"></div>
                            <button type="button"
                                    x-show="search === '' || '{{ addslashes($prov->name) }}'.toLowerCase().includes(search.toLowerCase())"
                                    @click="selectedId = {{ $prov->id }}; $wire.set('selectedProviderId', {{ $prov->id }}); open = false;"
                                    class="w-full text-right px-3 py-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/50 flex items-center justify-between font-bold transition-colors"
                                    :class="selectedId == {{ $prov->id }} ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/60 dark:bg-indigo-950/40' : 'text-gray-700 dark:text-gray-300'">
                                <div class="flex items-center gap-2 truncate">
                                    <div class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 text-[10px] font-black flex items-center justify-center shrink-0">
                                        {{ mb_substr($prov->name, 0, 1) }}
                                    </div>
                                    <span class="truncate">{{ $prov->name }}</span>
                                </div>
                                <template x-if="selectedId == {{ $prov->id }}">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 2. Service Searchable Dropdown --}}
            <div x-data="{
                open: false,
                search: '',
                selectedId: @entangle('selectedServiceId').live,
                get selectedName() {
                    if (!this.selectedId) return 'همه سرویس‌ها';
                    const el = document.getElementById('svc-opt-' + this.selectedId);
                    return el ? el.dataset.name : 'همه سرویس‌ها';
                }
            }" @click.outside="open = false" class="relative">
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">🛠️ سرویس نوبت‌دهی</label>
                <button @click="open = !open" type="button" class="w-full flex items-center justify-between gap-2 px-3 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-black text-gray-800 dark:text-gray-100 hover:bg-white dark:hover:bg-gray-800 transition-all text-right shadow-xs">
                    <div class="flex items-center gap-2 truncate">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="truncate" x-text="selectedName"></span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div x-show="open" x-transition.opacity.duration.150ms class="absolute z-50 mt-1 w-full min-w-[220px] bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-2 space-y-1 text-xs" style="display: none;">
                    <div class="relative mb-1">
                        <input type="text" x-model="search" placeholder="جستجوی سرویس..." class="w-full pl-3 pr-8 py-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <div class="max-h-48 overflow-y-auto space-y-0.5 custom-scrollbar">
                        <button type="button" @click="selectedId = null; $wire.set('selectedServiceId', null); open = false;" class="w-full text-right px-3 py-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/50 flex items-center justify-between font-bold" :class="!selectedId ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50/60 dark:bg-emerald-950/40' : 'text-gray-700 dark:text-gray-300'">
                            <span>همه سرویس‌ها</span>
                            <template x-if="!selectedId">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </template>
                        </button>
                        @foreach ($services as $svc)
                            <div id="svc-opt-{{ $svc->id }}" data-name="{{ $svc->name }}{{ $svc->custom_schedule_enabled ? ' ⚡' : '' }}" class="hidden"></div>
                            <button type="button"
                                    x-show="search === '' || '{{ addslashes($svc->name) }}'.toLowerCase().includes(search.toLowerCase())"
                                    @click="selectedId = {{ $svc->id }}; $wire.set('selectedServiceId', {{ $svc->id }}); open = false;"
                                    class="w-full text-right px-3 py-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/50 flex items-center justify-between font-bold transition-colors"
                                    :class="selectedId == {{ $svc->id }} ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50/60 dark:bg-emerald-950/40' : 'text-gray-700 dark:text-gray-300'">
                                <span class="truncate">{{ $svc->name }} {{ $svc->custom_schedule_enabled ? '⚡' : '' }}</span>
                                <template x-if="selectedId == {{ $svc->id }}">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. Status Searchable Dropdown --}}
            <div x-data="{
                open: false,
                search: '',
                selectedVal: @entangle('statusFilter').live,
                statuses: [
                    { val: '', label: 'همه وضعیت‌ها', color: 'bg-gray-400' },
                    { val: 'CONFIRMED', label: 'قطعی / تایید شده', color: 'bg-emerald-500' },
                    { val: 'PENDING', label: 'در انتظار تایید', color: 'bg-amber-500' },
                    { val: 'PENDING_PAYMENT', label: 'در انتظار پرداخت', color: 'bg-yellow-500' },
                    { val: 'DONE', label: 'انجام شده', color: 'bg-blue-500' },
                    { val: 'DRAFT', label: 'پیش‌نویس', color: 'bg-gray-400' },
                    { val: 'NO_SHOW', label: 'عدم حضور', color: 'bg-rose-400' },
                    { val: 'RESCHEDULED', label: 'جابجا شده', color: 'bg-purple-500' },
                    { val: 'CANCELED_BY_ADMIN', label: 'لغو شده (ادمین)', color: 'bg-rose-600' },
                    { val: 'CANCELED_BY_CLIENT', label: 'لغو شده (مشتری)', color: 'bg-rose-600' }
                ],
                get selectedStatus() {
                    return this.statuses.find(s => s.val === (this.selectedVal || '')) || this.statuses[0];
                }
            }" @click.outside="open = false" class="relative">
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">🏷️ وضعیت نوبت</label>
                <button @click="open = !open" type="button" class="w-full flex items-center justify-between gap-2 px-3 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-black text-gray-800 dark:text-gray-100 hover:bg-white dark:hover:bg-gray-800 transition-all text-right shadow-xs">
                    <div class="flex items-center gap-2 truncate">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="selectedStatus.color"></span>
                        <span class="truncate" x-text="selectedStatus.label"></span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div x-show="open" x-transition.opacity.duration.150ms class="absolute z-50 mt-1 w-full min-w-[220px] bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-2 space-y-1 text-xs" style="display: none;">
                    <div class="relative mb-1">
                        <input type="text" x-model="search" placeholder="جستجوی وضعیت..." class="w-full pl-3 pr-8 py-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <div class="max-h-48 overflow-y-auto space-y-0.5 custom-scrollbar">
                        <template x-for="st in statuses" :key="st.val">
                            <button type="button"
                                    x-show="search === '' || st.label.toLowerCase().includes(search.toLowerCase())"
                                    @click="selectedVal = st.val; $wire.set('statusFilter', st.val); open = false;"
                                    class="w-full text-right px-3 py-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/50 flex items-center justify-between font-bold transition-colors"
                                    :class="selectedVal === st.val ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/60 dark:bg-indigo-950/40' : 'text-gray-700 dark:text-gray-300'">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="st.color"></span>
                                    <span class="truncate" x-text="st.label"></span>
                                </div>
                                <template x-if="selectedVal === st.val">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- 4. Step Control & Reset Action Column --}}
            <div>
                <div class="flex items-center justify-between gap-1 mb-1">
                    <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400">⏱️ گام زمانی تقویم</label>
                    @if ($selectedProviderId || $selectedServiceId || $statusFilter)
                        <button wire:click="resetFilters" class="text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-0.5">
                            ✕ پاکسازی فیلترها
                        </button>
                    @endif
                </div>
                @if ($isStepLocked)
                    <div class="flex items-center gap-2 h-[42px] px-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-bold dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-200">
                        🔒 ثابت ({{ $timeStepMinutes }} دقیقه)
                    </div>
                @else
                    <div class="flex items-center gap-2 h-[42px] px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <input type="number" min="5" max="480" step="5" wire:model.blur="timeStepMinutes"
                               class="w-16 px-1 py-0.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-center text-xs font-black text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500"
                               title="گام زمانی (دقیقه)">
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 shrink-0">دقیقه</span>
                        <div class="hidden xl:flex gap-1 pr-1">
                            @foreach([15, 30, 60] as $step)
                                <button wire:click="setStep({{ $step }})"
                                        class="px-2 py-0.5 rounded text-[10px] font-black transition-all {{ $timeStepMinutes == $step ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-700' }}">
                                    {{ $step }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        </div>

        {{-- Hint Banner --}}
        <div class="pt-2 flex justify-end">
            <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-300 bg-indigo-50/80 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 px-3.5 py-1.5 rounded-xl shadow-xs inline-flex items-center gap-1.5">
                <span>💡 ثبت نوبت فقط با کلیک روی تایم‌های خالی جدول انجام می‌شود. (کلیک روی نوبت = جزئیات | درگ = تغییر ساعت)</span>
            </div>
        </div>
    </div>

    {{-- Status & Slot Type Legend (راهنمای تفکیک رنگی زمان‌ها و وضعیت‌های نوبت) --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-3.5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between gap-4 overflow-x-auto text-xs font-bold">
        <div class="flex items-center gap-3 shrink-0">
            <span class="text-gray-500 dark:text-gray-400 whitespace-nowrap flex items-center gap-1.5 pl-3 border-l border-gray-200 dark:border-gray-700">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                راهنمای انواع زمان‌ها:
            </span>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span> ⚪ زمان کاملاً آزاد
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-slate-500"></span> 👥 گروه چندظرفیتی
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-dotted border-gray-400 dark:border-gray-600 text-gray-600 dark:text-gray-400 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span> 🔹 تایم آزاد بین نوبت‌ها
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-200/60 dark:bg-slate-900/80 border border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span> ☕ استراحت
                </span>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0 border-r border-gray-200 dark:border-gray-700 pr-3">
            <span class="text-gray-400 dark:text-gray-500">کارت‌های وضعیت نوبت:</span>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-300"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> قطعی</span>
                <span class="inline-flex items-center gap-1 text-amber-700 dark:text-amber-300"><span class="w-2 h-2 rounded-full bg-amber-500"></span> در انتظار</span>
                <span class="inline-flex items-center gap-1 text-blue-700 dark:text-blue-300"><span class="w-2 h-2 rounded-full bg-blue-500"></span> انجام شده</span>
                <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400"><span class="w-2 h-2 rounded-full bg-gray-400"></span> پیش‌نویس</span>
                <span class="inline-flex items-center gap-1 text-rose-700 dark:text-rose-300"><span class="w-2 h-2 rounded-full bg-rose-500"></span> لغو</span>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- VIEW MODE 1: GRID VIEW (Vertical Columns)  --}}
    {{-- ========================================== --}}
    {{-- ========================================== --}}
    {{-- نمای ماتریس بلوک‌های زمانی (Time-Block Grid) با اطلاعات کامل --}}
    {{-- ========================================== --}}
    @if ($calendarView === 'day')
        @if ($viewMode === 'grid')
        <div class="flex flex-col gap-6">
            @forelse ($providerSchedules as $pData)
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col md:flex-row">

                    {{-- Doctor Info (Left Sidebar) --}}
                    <div class="w-full md:w-64 p-5 bg-gray-50/80 dark:bg-gray-900/80 border-b md:border-b-0 md:border-l border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-base flex items-center justify-center shadow-md shadow-indigo-500/20">
                                    {{ mb_substr($pData['provider']->name, 0, 2) }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 dark:text-white">{{ $pData['provider']->name }}</h3>
                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                        <p class="text-xs font-bold {{ $pData['policy']['is_closed'] ? 'text-rose-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            {{ $pData['policy']['is_closed'] ? '🌙 تعطیل' : '🟢 حضور دارد' }}
                                        </p>
                                        @if (!$pData['policy']['is_closed'] && !empty($pData['policy']['rule_source']))
                                            @php
                                                $sourceBadge = match($pData['policy']['rule_source']) {
                                                    'SERVICE_PROVIDER' => ['label' => 'برنامه اختصاصی ' . config('booking.labels.provider'), 'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800'],
                                                    'SERVICE' => ['label' => 'برنامه سرویس', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/70 dark:text-purple-300 border border-purple-200 dark:border-purple-800'],
                                                    'EXCEPTION' => ['label' => 'استثنای تاریخی', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200 dark:border-amber-800'],
                                                    default => ['label' => 'برنامه عمومی سیستم', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600'],
                                                };
                                            @endphp
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-lg {{ $sourceBadge['class'] }}">
                                                {{ $sourceBadge['label'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if (!$pData['policy']['is_closed'])
                                <div class="text-[11px] text-gray-600 dark:text-gray-300 space-y-1.5 font-bold border-t border-gray-200 dark:border-gray-700/80 pt-3.5 mt-3 bg-white/60 dark:bg-gray-800/60 p-3 rounded-2xl border">
                                    <div class="flex items-center justify-between">
                                        <span>⏱️ مدت اسلات:</span>
                                        <span class="font-black text-gray-900 dark:text-gray-100">{{ $pData['effective_slot_duration'] }} دقیقه</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>👥 ظرفیت اسلات:</span>
                                        <span class="font-black text-gray-900 dark:text-gray-100">{{ $pData['policy']['capacity_per_slot'] ?: 'نامحدود' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>📅 ظرفیت کل روز:</span>
                                        <span class="font-black {{ $pData['daily_remaining'] === 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-gray-100' }}">
                                            {{ $pData['capacity_per_day'] !== null ? $pData['capacity_per_day'] . ' نوبت' : 'نامحدود' }}
                                            @if ($pData['capacity_per_day'] !== null)
                                                <span class="text-[10px] text-gray-400">({{ $pData['daily_remaining'] }} مانده)</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Time Slots Matrix (Right Content) --}}
<div class="flex-1 p-5">
                        @if ($pData['policy']['is_closed'] && empty($pData['slots']))
                            <div class="h-full flex flex-col items-center justify-center text-gray-400 py-10">
                                <span class="text-3xl mb-2">🌙</span>
                                <span class="text-sm font-black">امروز برنامه کاری ندارد</span>
                            </div>
                        @else
                            @if ($pData['policy']['is_closed'])
                                <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300 rounded-2xl text-xs font-black flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span>🌙 این تاریخ تعطیل است (نوبت‌دهی جدید غیرفعال است)</span>
                                    </span>
                                    <span class="text-[11px] font-bold opacity-80">{{ count($pData['slots']) }} نوبت ثبت‌شده</span>
                                </div>
                            @endif
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
                                @forelse ($pData['slots'] as $slot)
                                    @php
                                        $isMulti = ($slot['capacity'] ?? 1) > 1;
                                    @endphp

                                    {{-- 1. BREAK SLOT --}}
                                    @if ($slot['in_break'])
                                        <div class="flex flex-col justify-between p-3.5 rounded-2xl bg-slate-100/90 dark:bg-slate-900/90 border border-slate-300 dark:border-slate-800 opacity-65 cursor-not-allowed min-h-[105px]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-black text-slate-800 dark:text-slate-200 bg-slate-200 dark:bg-slate-800 px-2.5 py-1 rounded-xl dir-ltr">
                                                    {{ $slot['start_time'] }} - {{ $slot['end_time'] }}
                                                </span>
                                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2 py-0.5 rounded-lg">
                                                    ☕ استراحت
                                                </span>
                                            </div>
                                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-2">خارج از ساعات پذیرش نوبت</p>
                                        </div>

                                    {{-- 2. MULTI-CAPACITY GROUP CONTAINER (Renders 0 or 1+ booked appointments + next booking button) --}}
                                    @elseif ($isMulti)
                                        <div class="p-3.5 rounded-2xl border-2 border-slate-300 dark:border-slate-700 bg-slate-100/60 dark:bg-slate-900/50 shadow-xs flex flex-col justify-between min-h-[110px] space-y-2.5 relative">
                                            {{-- Group Header --}}
                                            <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
                                                <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-900 font-black text-xs dir-ltr shadow-xs">
                                                    <span>{{ $slot['start_time'] }}</span>
                                                    <span class="text-[10px] opacity-75 font-normal">تا</span>
                                                    <span>{{ $slot['end_time'] }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5 text-[10px] font-black text-slate-700 dark:text-slate-300 bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-xl shadow-2xs">
                                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20H2v-2a3 3 0 015.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                    <span>ظرفیت {{ $slot['capacity'] }} نفر</span>
                                                </div>
                                            </div>

                                            {{-- Existing Booked Appointments inside Group --}}
                                            @if (!empty($slot['appointments']))
                                                <div class="space-y-2">
                                                    @foreach ($slot['appointments'] as $apt)
                                                        @php
                                                            $cardBg = match($apt['status']) {
                                                                \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'border-r-4 border-r-dashed border-r-indigo-500 bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-950 dark:text-indigo-100 border border-indigo-300 dark:border-indigo-700 border-dashed shadow-2xs',
                                                                \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'border-r-4 border-r-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-100 border border-emerald-300 dark:border-emerald-700',
                                                                \Modules\Booking\Entities\Appointment::STATUS_PENDING => 'border-r-4 border-r-amber-500 bg-amber-50/90 dark:bg-amber-950/60 text-amber-950 dark:text-amber-100 border border-amber-300 dark:border-amber-700',
                                                                \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'border-r-4 border-r-yellow-500 bg-yellow-50/90 dark:bg-yellow-950/60 text-yellow-950 dark:text-yellow-100 border border-yellow-300 dark:border-yellow-700',
                                                                \Modules\Booking\Entities\Appointment::STATUS_DONE => 'border-r-4 border-r-blue-500 bg-blue-50/90 dark:bg-blue-950/60 text-blue-950 dark:text-blue-100 border border-blue-300 dark:border-blue-700',
                                                                \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => 'border-r-4 border-r-slate-500 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700',
                                                                \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => 'border-r-4 border-r-rose-500 bg-rose-50/90 dark:bg-rose-950/60 text-rose-950 dark:text-rose-100 border border-rose-300 dark:border-rose-700',
                                                                \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => 'border-r-4 border-r-purple-500 bg-purple-50/90 dark:bg-purple-950/60 text-purple-950 dark:text-purple-100 border border-purple-300 dark:border-purple-700',
                                                                default => 'border-r-4 border-r-indigo-500 bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-950 dark:text-indigo-100 border border-indigo-300 dark:border-indigo-700',
                                                            };
                                                            $statusBadge = match($apt['status']) {
                                                                \Modules\Booking\Entities\Appointment::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'class' => 'bg-indigo-200/90 text-indigo-900 dark:bg-indigo-900/90 dark:text-indigo-200 border border-indigo-300 dark:border-indigo-700 font-extrabold'],
                                                                \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => ['label' => 'قطعی', 'class' => 'bg-emerald-200/80 text-emerald-900 dark:bg-emerald-900/80 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700'],
                                                                \Modules\Booking\Entities\Appointment::STATUS_PENDING => ['label' => 'در انتظار', 'class' => 'bg-amber-200/80 text-amber-900 dark:bg-amber-900/80 dark:text-amber-200 border border-amber-300 dark:border-amber-700'],
                                                                \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => ['label' => 'در انتظار پرداخت', 'class' => 'bg-yellow-200/80 text-yellow-900 dark:bg-yellow-900/80 dark:text-yellow-200 border border-yellow-300 dark:border-yellow-700'],
                                                                \Modules\Booking\Entities\Appointment::STATUS_DONE => ['label' => 'انجام شده', 'class' => 'bg-blue-200/80 text-blue-900 dark:bg-blue-900/80 dark:text-blue-200 border border-blue-300 dark:border-blue-700'],
                                                                \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => ['label' => 'عدم حضور', 'class' => 'bg-slate-200/80 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600'],
                                                                \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => ['label' => 'لغو ادمین', 'class' => 'bg-rose-200/80 text-rose-900 dark:bg-rose-900/80 dark:text-rose-200 border border-rose-300 dark:border-rose-700'],
                                                                \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => ['label' => 'لغو بیمار', 'class' => 'bg-rose-200/80 text-rose-900 dark:bg-rose-900/80 dark:text-rose-200 border border-rose-300 dark:border-rose-700'],
                                                                \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => ['label' => 'جابجا شده', 'class' => 'bg-purple-200/80 text-purple-900 dark:bg-purple-900/80 dark:text-purple-200 border border-purple-300 dark:border-purple-700'],
                                                                default => ['label' => $apt['status'], 'class' => 'bg-gray-100 text-gray-700'],
                                                            };
                                                        @endphp
                                                        <div draggable="true" @dragstart="handleDragStart($event, {{ $apt['id'] }})" @dragend="handleDragEnd($event)"
                                                             wire:click="openDetailsModal({{ $apt['id'] }})"
                                                             class="flex flex-col justify-between p-3 rounded-xl {{ $cardBg }} shadow-2xs cursor-pointer hover:shadow-xs transition-all relative group">
                                                            <div class="flex justify-between items-center gap-2">
                                                                <span class="text-xs font-black truncate text-gray-900 dark:text-white">{{ $apt['client_name'] }}</span>
                                                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-lg flex-none {{ $statusBadge['class'] }}">
                                                                    {{ $statusBadge['label'] }}
                                                                </span>
                                                                <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute top-1.5 left-1.5 bg-white/95 dark:bg-gray-900/95 rounded-xl flex gap-1 p-1 shadow-md border border-gray-200 dark:border-gray-700 z-20">
                                                                    @if ($apt['status'] === \Modules\Booking\Entities\Appointment::STATUS_DRAFT)
                                                                        <button wire:click.stop="updateStatus({{ $apt['id'] }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED }}')" class="bg-emerald-600 text-white p-1 rounded-lg hover:bg-emerald-700" title="تایید قطعی"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg></button>
                                                                    @endif
                                                                    <button wire:click.stop="cancelAppointment({{ $apt['id'] }})" class="bg-rose-600 text-white p-1 rounded-lg hover:bg-rose-700" title="لغو نوبت"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                                                </div>
                                                            </div>
                                                            <div class="text-[11px] font-bold opacity-85 truncate mt-1">
                                                                {{ $apt['service_name'] }} @if($apt['client_phone']) <span class="opacity-60">• {{ $apt['client_phone'] }}</span> @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Next Booking Button or Full Capacity Badge --}}
                                            @php
                                                $isSyncBlocked = !empty($slot['sync_blocked']);
                                                $remCap = $isSyncBlocked ? 0 : ($slot['remaining_capacity'] ?? ($slot['capacity'] - count($slot['appointments'] ?? [])));
                                            @endphp
                                            @if ($remCap > 0 && !$isSyncBlocked)
                                                <div wire:click="openCreateModal({{ $pData['provider']->id }}, '{{ $slot['start_time'] }}')"
                                                     class="group flex items-center justify-between p-2.5 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 hover:bg-slate-200/60 dark:hover:bg-slate-700/60 cursor-pointer transition-all">
                                                    <span class="text-xs font-black text-slate-800 dark:text-slate-200 group-hover:translate-x-1 transition-transform flex items-center gap-1.5">
                                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                                        <span>ثبت نوبت</span>
                                                    </span>
                                                    <span class="text-[10px] font-black text-slate-700 dark:text-slate-300 bg-slate-200 dark:bg-slate-700 px-2 py-0.5 rounded-md border border-slate-300 dark:border-slate-600">
                                                        {{ $remCap }} ظرفیت آزاد
                                                    </span>
                                                </div>
                                            @else
                                                <div class="p-2 rounded-xl bg-rose-50/70 dark:bg-rose-950/40 text-center text-[11px] font-extrabold text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-900/40">
                                                    @if($isSyncBlocked)
                                                        تکمیل ظرفیت (هماهنگ‌شده)
                                                    @else
                                                        تکمیل ظرفیت ({{ count($slot['appointments'] ?? []) }}/{{ $slot['capacity'] }})
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                    {{-- 3. SINGLE CAPACITY SLOT ($slot['capacity'] <= 1) --}}
                                    @else
                                        @if (!empty($slot['items']))
                                            @foreach ($slot['items'] as $item)

                                                {{-- 3A. EMPTY SLOT --}}
                                                @if ($item['type'] === 'empty_slot')
                                                    <div @dragover.prevent @drop="handleDrop($event, {{ $pData['provider']->id }}, '{{ $item['start_time'] }}')"
                                                         wire:click="openCreateModal({{ $pData['provider']->id }}, '{{ $item['start_time'] }}')"
                                                         :class="{ 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-950/50': isDragging }"
                                                         class="group flex flex-col justify-between p-3.5 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-900/40 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 cursor-pointer transition-all min-h-[105px] shadow-2xs">

                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-200/80 dark:bg-slate-800 text-slate-800 dark:text-slate-200 group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-500 dark:group-hover:text-white transition-colors font-black text-xs dir-ltr shadow-xs">
                                                                <span>{{ $item['start_time'] }}</span>
                                                                <span class="text-[10px] opacity-75 font-normal">تا</span>
                                                                <span>{{ $item['end_time'] }}</span>
                                                            </div>
                                                            <span class="text-[10px] font-black text-slate-600 dark:text-slate-400 bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2 py-0.5 rounded-lg">
                                                                زمان کاملاً آزاد
                                                            </span>
                                                        </div>

                                                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-200 dark:border-slate-800 group-hover:border-indigo-200 dark:group-hover:border-indigo-900">
                                                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:translate-x-1 transition-all flex items-center gap-1">
                                                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                                                <span>ثبت نوبت</span>
                                                            </span>
                                                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">آزاد پذیرش</span>
                                                        </div>
                                                    </div>

                                                {{-- 3B. BOOKED APPOINTMENT --}}
                                                @elseif ($item['type'] === 'appointment')
                                                    @php
                                                        $apt = $item['data'];
                                                        $cardBg = match($apt['status']) {
                                                            \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'border-r-4 border-r-dashed border-r-indigo-500 bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-950 dark:text-indigo-100 border border-indigo-300 dark:border-indigo-700 border-dashed shadow-2xs',
                                                            \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'border-r-4 border-r-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-100 border border-emerald-300 dark:border-emerald-700',
                                                            \Modules\Booking\Entities\Appointment::STATUS_PENDING => 'border-r-4 border-r-amber-500 bg-amber-50/90 dark:bg-amber-950/60 text-amber-950 dark:text-amber-100 border border-amber-300 dark:border-amber-700',
                                                            \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'border-r-4 border-r-yellow-500 bg-yellow-50/90 dark:bg-yellow-950/60 text-yellow-950 dark:text-yellow-100 border border-yellow-300 dark:border-yellow-700',
                                                            \Modules\Booking\Entities\Appointment::STATUS_DONE => 'border-r-4 border-r-blue-500 bg-blue-50/90 dark:bg-blue-950/60 text-blue-950 dark:text-blue-100 border border-blue-300 dark:border-blue-700',
                                                            \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => 'border-r-4 border-r-slate-500 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700',
                                                            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => 'border-r-4 border-r-rose-500 bg-rose-50/90 dark:bg-rose-950/60 text-rose-950 dark:text-rose-100 border border-rose-300 dark:border-rose-700',
                                                            \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => 'border-r-4 border-r-purple-500 bg-purple-50/90 dark:bg-purple-950/60 text-purple-950 dark:text-purple-100 border border-purple-300 dark:border-purple-700',
                                                            default => 'border-r-4 border-r-indigo-500 bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-950 dark:text-indigo-100 border border-indigo-300 dark:border-indigo-700',
                                                        };
                                                        $statusBadge = match($apt['status']) {
                                                            \Modules\Booking\Entities\Appointment::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'class' => 'bg-indigo-200/90 text-indigo-900 dark:bg-indigo-900/90 dark:text-indigo-200 border border-indigo-300 dark:border-indigo-700 font-extrabold'],
                                                            \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => ['label' => 'قطعی', 'class' => 'bg-emerald-200/80 text-emerald-900 dark:bg-emerald-900/80 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700'],
                                                            \Modules\Booking\Entities\Appointment::STATUS_PENDING => ['label' => 'در انتظار', 'class' => 'bg-amber-200/80 text-amber-900 dark:bg-amber-900/80 dark:text-amber-200 border border-amber-300 dark:border-amber-700'],
                                                            \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => ['label' => 'در انتظار پرداخت', 'class' => 'bg-yellow-200/80 text-yellow-900 dark:bg-yellow-900/80 dark:text-yellow-200 border border-yellow-300 dark:border-yellow-700'],
                                                            \Modules\Booking\Entities\Appointment::STATUS_DONE => ['label' => 'انجام شده', 'class' => 'bg-blue-200/80 text-blue-900 dark:bg-blue-900/80 dark:text-blue-200 border border-blue-300 dark:border-blue-700'],
                                                            \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => ['label' => 'عدم حضور', 'class' => 'bg-slate-200/80 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600'],
                                                            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => ['label' => 'لغو ادمین', 'class' => 'bg-rose-200/80 text-rose-900 dark:bg-rose-900/80 dark:text-rose-200 border border-rose-300 dark:border-rose-700'],
                                                            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => ['label' => 'لغو بیمار', 'class' => 'bg-rose-200/80 text-rose-900 dark:bg-rose-900/80 dark:text-rose-200 border border-rose-300 dark:border-rose-700'],
                                                            \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => ['label' => 'جابجا شده', 'class' => 'bg-purple-200/80 text-purple-900 dark:bg-purple-900/80 dark:text-purple-200 border border-purple-300 dark:border-purple-700'],
                                                            default => ['label' => $apt['status'], 'class' => 'bg-gray-100 text-gray-700'],
                                                        };
                                                    @endphp
                                                    <div draggable="true" @dragstart="handleDragStart($event, {{ $apt['id'] }})" @dragend="handleDragEnd($event)"
                                                         wire:click="openDetailsModal({{ $apt['id'] }})"
                                                         class="flex flex-col justify-between p-3.5 rounded-2xl {{ $cardBg }} shadow-sm cursor-pointer hover:shadow-md transition-all relative group min-h-[105px]">
                                                        <div class="flex justify-between items-center gap-2">
                                                            <span class="text-sm font-black truncate text-gray-900 dark:text-white">{{ $apt['client_name'] }}</span>
                                                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-lg flex-none {{ $statusBadge['class'] }}">
                                                                {{ $statusBadge['label'] }}
                                                            </span>
                                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute top-2 left-2 bg-white/95 dark:bg-gray-900/95 rounded-xl flex gap-1 p-1 shadow-md border border-gray-200 dark:border-gray-700 z-20">
                                                                @if ($apt['status'] === \Modules\Booking\Entities\Appointment::STATUS_DRAFT)
                                                                    <button wire:click.stop="updateStatus({{ $apt['id'] }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED }}')" class="bg-emerald-600 text-white p-1 rounded-lg hover:bg-emerald-700" title="تایید قطعی"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg></button>
                                                                @endif
                                                                <button wire:click.stop="cancelAppointment({{ $apt['id'] }})" class="bg-rose-600 text-white p-1 rounded-lg hover:bg-rose-700" title="لغو نوبت"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                                            </div>
                                                        </div>
                                                        <div class="text-xs font-bold opacity-85 truncate mt-1">
                                                            {{ $apt['service_name'] }} @if($apt['client_phone']) <span class="opacity-60">• {{ $apt['client_phone'] }}</span> @endif
                                                        </div>
                                                        <div class="flex items-center justify-between mt-3 pt-2 border-t border-current/15">
                                                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 font-black text-xs dir-ltr shadow-xs">
                                                                <svg class="w-3.5 h-3.5 text-indigo-400 dark:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <span>{{ $apt['start_time'] }}</span>
                                                                <span class="text-[10px] opacity-70 font-normal">تا</span>
                                                                <span>{{ $apt['end_time'] }}</span>
                                                            </div>
                                                            <span class="text-[10px] font-bold opacity-75">نوبت ثبت‌شده</span>
                                                        </div>
                                                    </div>

                                                {{-- 3C. FREE SEGMENT BETWEEN APPOINTMENTS --}}
                                                @elseif ($item['type'] === 'free_segment')
                                                    @php $seg = $item['data']; @endphp
                                                    <div wire:click="openCreateModal({{ $pData['provider']->id }}, '{{ $seg['start_time'] }}')"
                                                         class="flex flex-col justify-between p-3 rounded-2xl border-2 border-dashed border-sky-400/80 dark:border-sky-700/80 bg-sky-50/70 dark:bg-sky-950/40 hover:border-sky-500 dark:hover:border-sky-500 hover:bg-sky-100/80 dark:hover:bg-sky-900/60 cursor-pointer min-h-[90px] group transition-all shadow-2xs">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="flex items-center gap-1.5 text-xs font-black text-sky-950 dark:text-sky-100 group-hover:text-sky-700 dark:group-hover:text-sky-300">
                                                                <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                                                <span>ثبت در بازه آزاد</span>
                                                            </div>
                                                            <span class="text-[10px] font-black text-sky-900 dark:text-sky-200 bg-sky-100 dark:bg-sky-900/90 border border-sky-300 dark:border-sky-700 px-2.5 py-0.5 rounded-lg shadow-2xs">
                                                                {{ $seg['formatted_duration'] }} زمان آزاد
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center justify-between mt-2 pt-1.5 border-t border-sky-200/80 dark:border-sky-800/80">
                                                            <span class="text-xs font-black text-sky-900 dark:text-sky-200 dir-ltr bg-sky-200/70 dark:bg-sky-900/90 border border-sky-300 dark:border-sky-700 px-2.5 py-0.5 rounded-lg">
                                                                {{ $seg['start_time'] }} تا {{ $seg['end_time'] }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                {{-- 3D. CLOSED / NON-BOOKABLE SLOT --}}
                                                @elseif ($item['type'] === 'closed_slot')
                                                    <div class="flex items-center justify-between p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-100/60 dark:bg-slate-900/30 text-slate-400 dark:text-slate-500 min-h-[60px] opacity-75 select-none">
                                                        <div class="flex items-center gap-2 text-xs font-bold dir-ltr">
                                                            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                            <span>{{ $item['start_time'] }} - {{ $item['end_time'] }}</span>
                                                        </div>
                                                        <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-lg bg-slate-200/80 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/60 dark:border-slate-700/60">
                                                            {{ $item['label'] ?? 'غیرقابل رزرو' }}
                                                        </span>
                                                    </div>
                                                @endif

                                            @endforeach
                                        @endif
                                    @endif
                                @empty
                                    <div class="col-span-full p-4 text-center text-xs font-bold text-gray-400">بازه زمانی تعریف نشده است.</div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center text-gray-500 font-bold bg-gray-50 dark:bg-gray-800/40 rounded-3xl border border-gray-200 dark:border-gray-700">هیچ ارائه‌دهنده‌ای یافت نشد.</div>
            @endforelse
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- VIEW MODE 2: SMART TIMELINE (هوشمند بر اساس تعداد پزشک) --}}
    {{-- ========================================== --}}
    @if ($viewMode === 'timeline')

        @php
            $isSingleDoctor = count($providerSchedules) === 1;
        @endphp

        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">

            {{-- حالت اول: نمای عمودی اختصاصی (مخصوص زمانی که یک پزشک فیلتر شده) --}}
            @if ($isSingleDoctor)
                @foreach ($providerSchedules as $pData)

                    {{-- هدر اختصاصی پزشک --}}
                    <div class="p-4 sm:p-5 border-b border-gray-200 dark:border-gray-700/80 bg-gray-50/90 dark:bg-gray-900/90 flex items-center justify-between sticky top-0 z-30 backdrop-blur">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-indigo-600 text-white font-black text-base sm:text-lg flex items-center justify-center shadow-md shadow-indigo-500/20 flex-none">
                                {{ mb_substr($pData['provider']->name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">برنامه کاری: {{ $pData['provider']->name }}</h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="bg-indigo-100 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 px-2.5 py-0.5 rounded-lg text-[10px] sm:text-[11px] font-bold">نمای تایم‌لاین اختصاصی</span>
                                    <span class="text-[10px] sm:text-[11px] font-bold text-gray-600 dark:text-gray-300">گام زمانی: {{ $pData['effective_slot_duration'] }} دقیقه</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-200 dark:border-indigo-800 px-3 py-1.5 rounded-xl shadow-xs">
                            💡 ثبت نوبت فقط با کلیک روی تایم‌های خالی جدول
                        </div>
                    </div>

                    {{-- بدنه اسکرول‌پذیر تایم‌لاین عمودی (با ارتفاع داخلی ۲۲۰۰ پیکسل و اسکرول داخلی نرم) --}}
                    <div class="max-h-[680px] overflow-y-auto relative custom-scrollbar">
                        <div class="flex relative min-h-[2200px] w-full bg-white dark:bg-gray-800">

                            {{-- ستون محور زمان (سمت راست) --}}
                            <div class="w-20 sm:w-24 flex-none border-l border-gray-200 dark:border-gray-700 bg-gray-50/90 dark:bg-gray-900/90 relative z-20">
                                @foreach ($timelineHeaders as $th)
                                    <div style="top: {{ $th['left_percent'] }}%;" class="absolute w-full flex items-center justify-center {{ $loop->first ? 'top-1' : '-mt-3.5' }}">
                                        <span class="text-xs font-black text-gray-800 dark:text-gray-100 bg-gray-200 dark:bg-gray-800 px-2.5 py-1 rounded-xl border border-gray-300 dark:border-gray-700 dir-ltr shadow-2xs">
                                            {{ $th['time_str'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- محوطه قرارگیری نوبت‌ها (تمام عرض) --}}
                            <div class="flex-1 relative">

                                {{-- خطوط افقی راهنما (پرکنتراست در تم تاریک) --}}
                                <div class="absolute inset-0 pointer-events-none">
                                    @foreach ($timelineHeaders as $th)
                                        <div style="top: {{ $th['left_percent'] }}%;" class="absolute w-full border-t border-gray-200 dark:border-gray-700/80"></div>
                                    @endforeach
                                </div>

                            @if ($pData['policy']['is_closed'] && empty($pData['appointments']))
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 bg-gray-50/95 dark:bg-gray-900/95 z-10">
                                    <svg class="w-16 h-16 mb-4 text-indigo-500 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                                    <span class="text-lg font-black text-gray-800 dark:text-gray-200">{{ config('booking.labels.provider') }} در این تاریخ حضور ندارد</span>
                                </div>
                            @else
                                @if ($pData['policy']['is_closed'])
                                    <div class="sticky top-2 z-30 mx-4 p-2.5 bg-amber-500/90 text-white rounded-xl text-xs font-black backdrop-blur flex items-center justify-between shadow-md">
                                        <span>🌙 این تاریخ تعطیل است (نوبت‌دهی جدید غیرفعال است)</span>
                                        <span class="text-[11px] font-bold">{{ count($pData['appointments']) }} نوبت ثبت‌شده</span>
                                    </div>
                                @endif

                                {{-- زمان‌های استراحت --}}
                                @foreach ($pData['breaks'] as $brk)
                                    <div style="top: {{ $brk['left_percent'] }}%; height: {{ $brk['width_percent'] }}%;" class="absolute inset-x-0 bg-amber-50/80 dark:bg-amber-950/40 border-y border-amber-300/80 dark:border-amber-800/80 z-0 flex items-center justify-center pointer-events-none">
                                        <span class="bg-amber-100 dark:bg-amber-900/80 px-4 py-1.5 rounded-2xl text-xs font-black text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-700 shadow-xs">☕ زمان استراحت</span>
                                    </div>
                                @endforeach

                                {{-- مناطق خالی (برای کلیک و درگ) --}}
                                @foreach ($pData['slotDropTargets'] as $sTarget)
                                    <div @dragover.prevent @drop="handleDrop($event, {{ $pData['provider']->id }}, '{{ $sTarget['start_time'] }}')"
                                         wire:click="openCreateModal({{ $pData['provider']->id }}, '{{ $sTarget['start_time'] }}')"
                                         style="top: {{ $sTarget['left_percent'] }}%; height: {{ $sTarget['width_percent'] }}%;"
                                         :class="{ 'bg-indigo-50 dark:bg-indigo-950/60 border-indigo-400': isDragging }"
                                         class="absolute inset-x-1 sm:inset-x-3 border border-transparent hover:border-indigo-400 dark:hover:border-indigo-600 rounded-2xl cursor-pointer group flex items-center justify-center transition-colors z-0">
                                        <span class="opacity-0 group-hover:opacity-100 text-xs font-black text-indigo-700 dark:text-indigo-300 bg-white dark:bg-gray-800 px-3.5 py-1.5 rounded-xl shadow-md border border-indigo-200 dark:border-indigo-800 flex items-center gap-1">
                                            <span>+</span>
                                            <span>ثبت نوبت در {{ $sTarget['start_time'] }}</span>
                                        </span>
                                    </div>
                                @endforeach

                                {{-- کارت‌های نوبت (با محاسبه دقیق ستون‌های عدم تداخل) --}}
                                @foreach ($pData['appointments'] as $apt)
                                    @php
                                        $vCard = match($apt['status']) {
                                            \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'border-r-4 border-r-slate-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 border-dashed',
                                            \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'border-r-4 border-r-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-100 border border-emerald-300 dark:border-emerald-700',
                                            \Modules\Booking\Entities\Appointment::STATUS_PENDING => 'border-r-4 border-r-amber-500 bg-amber-50/90 dark:bg-amber-950/60 text-amber-950 dark:text-amber-100 border border-amber-300 dark:border-amber-700',
                                            \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'border-r-4 border-r-yellow-500 bg-yellow-50/90 dark:bg-yellow-950/60 text-yellow-950 dark:text-yellow-100 border border-yellow-300 dark:border-yellow-700',
                                            \Modules\Booking\Entities\Appointment::STATUS_DONE => 'border-r-4 border-r-blue-500 bg-blue-50/90 dark:bg-blue-950/60 text-blue-950 dark:text-blue-100 border border-blue-300 dark:border-blue-700',
                                            \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => 'border-r-4 border-r-slate-500 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700',
                                            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => 'border-r-4 border-r-rose-500 bg-rose-50/90 dark:bg-rose-950/60 text-rose-950 dark:text-rose-100 border border-rose-300 dark:border-rose-700',
                                            \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => 'border-r-4 border-r-purple-500 bg-purple-50/90 dark:bg-purple-950/60 text-purple-950 dark:text-purple-100 border border-purple-300 dark:border-purple-700',
                                            default => 'border-r-4 border-r-indigo-500 bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-950 dark:text-indigo-100 border border-indigo-300 dark:border-indigo-700',
                                        };

                                        $statusBadge = match($apt['status']) {
                                            \Modules\Booking\Entities\Appointment::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600'],
                                            \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => ['label' => 'قطعی', 'class' => 'bg-emerald-200/80 text-emerald-900 dark:bg-emerald-900/80 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700'],
                                            \Modules\Booking\Entities\Appointment::STATUS_PENDING => ['label' => 'در انتظار', 'class' => 'bg-amber-200/80 text-amber-900 dark:bg-amber-900/80 dark:text-amber-200 border border-amber-300 dark:border-amber-700'],
                                            \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => ['label' => 'در انتظار پرداخت', 'class' => 'bg-yellow-200/80 text-yellow-900 dark:bg-yellow-900/80 dark:text-yellow-200 border border-yellow-300 dark:border-yellow-700'],
                                            \Modules\Booking\Entities\Appointment::STATUS_DONE => ['label' => 'انجام شده', 'class' => 'bg-blue-200/80 text-blue-900 dark:bg-blue-900/80 dark:text-blue-200 border border-blue-300 dark:border-blue-700'],
                                            \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => ['label' => 'عدم حضور', 'class' => 'bg-slate-200/80 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600'],
                                            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => ['label' => 'لغو ادمین', 'class' => 'bg-rose-200/80 text-rose-900 dark:bg-rose-900/80 dark:text-rose-200 border border-rose-300 dark:border-rose-700'],
                                            \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => ['label' => 'لغو بیمار', 'class' => 'bg-rose-200/80 text-rose-900 dark:bg-rose-900/80 dark:text-rose-200 border border-rose-300 dark:border-rose-700'],
                                            \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => ['label' => 'جابجا شده', 'class' => 'bg-purple-200/80 text-purple-900 dark:bg-purple-900/80 dark:text-purple-200 border border-purple-300 dark:border-purple-700'],
                                            default => ['label' => $apt['status'], 'class' => 'bg-gray-100 text-gray-700'],
                                        };

                                        $cIdx = $apt['col_index'] ?? 0;
                                        $mCols = max(1, $apt['max_cols'] ?? 1);
                                        $colWidthPct = (100 / $mCols) - 1.5;
                                        $colRightPct = ($cIdx / $mCols) * 100;
                                    @endphp
                                    <div draggable="true" @dragstart="handleDragStart($event, {{ $apt['id'] }})" @dragend="handleDragEnd($event)"
                                         wire:click="openDetailsModal({{ $apt['id'] }})"
                                         title="{{ $apt['client_name'] }} - {{ $apt['service_name'] }} ({{ $apt['start_time'] }} تا {{ $apt['end_time'] }})"
                                         style="top: {{ $apt['left_percent'] }}%; height: calc({{ max(4.0, $apt['width_percent']) }}% - 2px); min-height: 60px; width: calc({{ $colWidthPct }}% - 0.25rem); right: calc({{ $colRightPct }}% + 0.25rem);"
                                         class="absolute rounded-2xl p-3 sm:p-3.5 shadow-md hover:shadow-lg transition-all cursor-pointer flex items-center justify-between z-10 overflow-hidden group {{ $vCard }}">

                                        <div class="flex items-center gap-3 sm:gap-4 min-w-0 flex-1">

                                            {{-- بج زمان: مشکی کامل در روشن / سفید کامل در تاریک جهت خوانایی ۱۰۰٪ --}}
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 font-black text-xs dir-ltr shadow-xs flex-none">
                                                <svg class="w-3.5 h-3.5 text-indigo-400 dark:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ $apt['start_time'] }}</span>
                                                <span class="text-[10px] opacity-70 font-normal">تا</span>
                                                <span>{{ $apt['end_time'] }}</span>
                                            </div>

                                            {{-- جزئیات بیمار و خدمات --}}
                                            <div class="truncate flex-1">
                                                <div class="flex items-center gap-2 truncate">
                                                    <h4 class="text-xs sm:text-sm font-black truncate leading-tight">{{ $apt['client_name'] }}</h4>
                                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-md flex-none {{ $statusBadge['class'] }}">
                                                        {{ $statusBadge['label'] }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] sm:text-xs font-bold opacity-80 truncate mt-0.5">
                                                    {{ $apt['service_name'] }} @if($apt['client_phone']) <span class="opacity-60">• {{ $apt['client_phone'] }}</span> @endif
                                                </p>
                                            </div>
                                        </div>

                                        {{-- اکشن‌های سریع --}}
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity bg-white/95 dark:bg-gray-900/95 p-1 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 flex-none mr-2">
                                            @if ($apt['status'] === \Modules\Booking\Entities\Appointment::STATUS_DRAFT)
                                                <button wire:click.stop="updateStatus({{ $apt['id'] }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED }}')" class="bg-emerald-600 text-white text-[10px] font-black px-2.5 py-1 rounded-lg hover:bg-emerald-700">تایید</button>
                                            @endif
                                            <button wire:click.stop="cancelAppointment({{ $apt['id'] }})" class="bg-rose-600 text-white text-[10px] font-black px-2.5 py-1 rounded-lg hover:bg-rose-700">لغو</button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

                {{-- حالت دوم: نمای افقی اسکرول‌پذیر (مخصوص زمانی که چند پزشک وجود دارد) --}}
            @else
                <div class="w-full overflow-x-auto">
                    <div class="min-w-[950px] sm:min-w-[1150px] flex relative">

                        {{-- ستون ثابت راست برای اسامی پزشکان --}}
                        <div class="w-44 sm:w-52 flex-none sticky right-0 border-l border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700/80 z-30 shadow-sm">
                            <div class="h-14 px-4 flex items-center font-black text-xs text-gray-800 dark:text-gray-200 bg-gray-100/90 dark:bg-gray-900">{{ config('booking.labels.providers') }} / منابع</div>
                            @foreach ($providerSchedules as $pData)
                                <div class="h-32 px-3 flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center flex-none shadow-sm">
                                        {{ mb_substr($pData['provider']->name, 0, 2) }}
                                    </div>
                                    <div class="truncate">
                                        <h4 class="text-xs font-black text-gray-900 dark:text-gray-100 truncate">{{ $pData['provider']->name }}</h4>
                                        <p class="text-[10px] font-bold mt-0.5 text-gray-500 dark:text-gray-400">{{ $pData['policy']['is_closed'] ? '🌙 تعطیل' : 'گام: '.$pData['effective_slot_duration'].'m' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- مسیر افقی زمان --}}
                        <div class="flex-1 flex flex-col relative divide-y divide-gray-100 dark:divide-gray-800">

                            {{-- هدر محور زمان --}}
                            <div class="h-14 relative border-b border-gray-200 dark:border-gray-700/80 bg-gray-50 dark:bg-gray-900">
                                @foreach ($timelineHeaders as $th)
                                    <div style="right: {{ $th['left_percent'] }}%; width: {{ $th['width_percent'] }}%;" class="absolute inset-y-0 border-r border-gray-200 dark:border-gray-700/80 flex items-center pr-2">
                                        <span class="text-xs font-black text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-200 dark:border-indigo-800 px-2 py-0.5 rounded-lg dir-ltr">
                                            {{ $th['time_str'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- ردیف‌های پزشکان (ارتفاع ۱۲۸ پیکسل h-32 برای فضای عریض نوبت‌ها) --}}
                            @foreach ($providerSchedules as $pData)
                                <div class="h-32 relative bg-white dark:bg-gray-800">

                                    {{-- خطوط راهنمای پس‌زمینه --}}
                                    <div class="absolute inset-0 pointer-events-none">
                                        @foreach ($timelineHeaders as $th)
                                            <div style="right: {{ $th['left_percent'] }}%; width: {{ $th['width_percent'] }}%;" class="absolute inset-y-0 border-r border-gray-100 dark:border-gray-700/40"></div>
                                        @endforeach
                                    </div>

                                    @if ($pData['policy']['is_closed'] && empty($pData['appointments']))
                                        <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs font-black bg-gray-50/70 dark:bg-gray-900/70">
                                            🌙 تعطیل
                                        </div>
                                    @else
                                        @if ($pData['policy']['is_closed'])
                                            <div class="absolute inset-0 bg-amber-500/10 pointer-events-none z-0 border-y border-amber-500/30"></div>
                                        @endif

                                        {{-- استراحت‌ها --}}
                                        @foreach ($pData['breaks'] as $brk)
                                            <div style="right: {{ $brk['left_percent'] }}%; width: {{ $brk['width_percent'] }}%;" class="absolute inset-y-1 bg-amber-50/80 dark:bg-amber-950/40 border-x border-amber-300 dark:border-amber-800 flex items-center justify-center overflow-hidden z-0">
                                                <span class="text-[9px] text-amber-800 dark:text-amber-300 font-black">☕ استراحت</span>
                                            </div>
                                        @endforeach

                                        {{-- اسلات‌های خالی جهت درگ یا کلیک --}}
                                        @if (!$pData['policy']['is_closed'])
                                            @foreach ($pData['slotDropTargets'] as $sTarget)
                                                <div @dragover.prevent @drop="handleDrop($event, {{ $pData['provider']->id }}, '{{ $sTarget['start_time'] }}')" wire:click="openCreateModal({{ $pData['provider']->id }}, '{{ $sTarget['start_time'] }}')" style="right: {{ $sTarget['left_percent'] }}%; width: {{ $sTarget['width_percent'] }}%;" class="absolute inset-y-1 group hover:bg-indigo-50/60 dark:hover:bg-indigo-950/40 cursor-pointer border-r border-transparent hover:border-indigo-400 flex justify-center items-center z-1 transition-colors"></div>
                                            @endforeach
                                        @endif

                                        {{-- کارت‌های نوبت افقی --}}
                                        <div class="absolute inset-x-0 inset-y-2 pointer-events-none z-10">
                                            @foreach ($pData['appointments'] as $apt)
                                                @php
                                                    $hCard = match($apt['status']) {
                                                        \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'border-r-4 border-r-slate-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 border-dashed',
                                                        \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'border-r-4 border-r-emerald-500 bg-emerald-50 dark:bg-emerald-950/90 text-emerald-950 dark:text-emerald-100 border border-emerald-300 dark:border-emerald-700',
                                                        \Modules\Booking\Entities\Appointment::STATUS_PENDING => 'border-r-4 border-r-amber-500 bg-amber-50 dark:bg-amber-950/90 text-amber-950 dark:text-amber-100 border border-amber-300 dark:border-amber-700',
                                                        \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'border-r-4 border-r-yellow-500 bg-yellow-50 dark:bg-yellow-950/90 text-yellow-950 dark:text-yellow-100 border border-yellow-300 dark:border-yellow-700',
                                                        \Modules\Booking\Entities\Appointment::STATUS_DONE => 'border-r-4 border-r-blue-500 bg-blue-50 dark:bg-blue-950/90 text-blue-950 dark:text-blue-100 border border-blue-300 dark:border-blue-700',
                                                        \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => 'border-r-4 border-r-slate-500 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700',
                                                        \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => 'border-r-4 border-r-rose-500 bg-rose-50 dark:bg-rose-950/90 text-rose-950 dark:text-rose-100 border border-rose-300 dark:border-rose-700',
                                                        \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => 'border-r-4 border-r-purple-500 bg-purple-50 dark:bg-purple-950/90 text-purple-950 dark:text-purple-100 border border-purple-300 dark:border-purple-700',
                                                        default => 'border-r-4 border-r-indigo-500 bg-indigo-50 dark:bg-indigo-950/90 text-indigo-950 dark:text-indigo-100 border border-indigo-300 dark:border-indigo-700',
                                                    };

                                                    $statusLabel = match($apt['status']) {
                                                        \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'پیش‌نویس',
                                                        \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'قطعی',
                                                        \Modules\Booking\Entities\Appointment::STATUS_PENDING => 'در انتظار',
                                                        \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'در انتظار پرداخت',
                                                        \Modules\Booking\Entities\Appointment::STATUS_DONE => 'انجام شده',
                                                        \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => 'عدم حضور',
                                                        \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => 'لغو ادمین',
                                                        \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => 'لغو بیمار',
                                                        \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => 'جابجا شده',
                                                        default => $apt['status'],
                                                    };
                                                @endphp
                                                <div draggable="true" @dragstart="handleDragStart($event, {{ $apt['id'] }})" @dragend="handleDragEnd($event)"
                                                     wire:click="openDetailsModal({{ $apt['id'] }})"
                                                     title="{{ $apt['client_name'] }} - {{ $apt['service_name'] }} ({{ $apt['start_time'] }} تا {{ $apt['end_time'] }})"
                                                     style="right: {{ $apt['left_percent'] }}%; width: calc({{ max(2.5, $apt['width_percent']) }}% - 2px); min-width: 75px;"
                                                     class="absolute inset-y-0 rounded-2xl p-2 sm:p-2.5 pointer-events-auto cursor-pointer flex flex-col justify-between overflow-hidden shadow-sm hover:shadow-md transition-all z-20 group {{ $hCard }}">

                                                    <div class="flex items-center justify-between gap-1">
                                                        <span class="text-xs font-black truncate leading-tight">{{ $apt['client_name'] }}</span>
                                                        <span class="text-[8px] font-black px-1.5 py-0.2 rounded bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 flex-none">
                                                            {{ $statusLabel }}
                                                        </span>
                                                    </div>

                                                    <div class="flex items-center justify-between gap-1 text-[9px] font-extrabold mt-auto pt-1 border-t border-current/15">
                                                        <span class="truncate">{{ $apt['service_name'] }}</span>
                                                        <span class="flex-none font-black dir-ltr bg-black/10 dark:bg-white/10 px-1 py-0.2 rounded">{{ $apt['start_time'] }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
    @elseif ($calendarView === 'week')
        <div class="flex flex-col gap-6">
            @forelse ($weekProviderSchedules as $wpData)
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 sm:p-6 space-y-6">

                    {{-- Provider Info Header & Summary Bar --}}
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-gray-200 dark:border-gray-700/80">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-md shadow-indigo-500/20">
                                {{ mb_substr($wpData['provider']->name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    {{ $wpData['provider']->name }}
                                </h3>
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-0.5">برنامه نوبت‌دهی هفته جاری شمسی (شنبه تا جمعه)</p>
                            </div>
                        </div>

                        {{-- Quick 7-Day Navigation Strip --}}
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 max-w-full">
                            @foreach ($wpData['days'] as $wDayNav)
                                <button wire:click="goToDay('{{ $wDayNav['jalali_date'] }}')"
                                        class="px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all flex flex-col items-center flex-none border shadow-2xs
                                        {{ $wDayNav['is_today'] ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 font-black' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 hover:border-indigo-400 dark:hover:border-indigo-600' }}">
                                    <span class="text-[10px] font-bold opacity-75">{{ $wDayNav['day_name'] }}</span>
                                    <span class="font-black text-sm mt-0.5">{{ $wDayNav['day_num'] }}</span>
                                    <span class="text-[9px] font-extrabold mt-1 px-2 py-0.5 rounded-lg {{ $wDayNav['is_closed'] ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300' : ($wDayNav['daily_booked'] > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300' : 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300') }}">
                                        {{ $wDayNav['is_closed'] ? 'تعطیل' : $wDayNav['daily_booked'].' نوبت' }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- 7 Days Grid (Spacious 3-Column Responsive Grid) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        @foreach ($wpData['days'] as $wDay)
                            <div class="bg-gray-50/80 dark:bg-gray-900/60 rounded-3xl border-2 transition-all overflow-hidden flex flex-col justify-between
                                 {{ $wDay['is_today'] ? 'border-indigo-500 shadow-md shadow-indigo-500/10' : 'border-gray-200 dark:border-gray-700/80 hover:border-indigo-400 dark:hover:border-indigo-600' }}">

                                {{-- Day Header --}}
                                <div class="p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700/80 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm {{ $wDay['is_today'] ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
                                            {{ $wDay['day_num'] }}
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                                {{ $wDay['day_name'] }}
                                                @if ($wDay['is_today'])
                                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-lg bg-indigo-600 text-white shadow-2xs">امروز</span>
                                                @endif
                                            </h4>
                                            <span class="text-[11px] font-extrabold text-gray-500 dark:text-gray-400">{{ $wDay['jalali_date'] }}</span>
                                        </div>
                                    </div>

                                    <div class="text-left">
                                        @if ($wDay['is_closed'])
                                            <span class="inline-flex items-center gap-1 text-xs font-black text-rose-700 dark:text-rose-300 bg-rose-100 dark:bg-rose-950/80 px-2.5 py-1 rounded-xl border border-rose-200 dark:border-rose-800">
                                                🌙 تعطیل
                                            </span>
                                        @else
                                            <div class="flex flex-col items-end">
                                                <span class="text-xs font-black text-emerald-800 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/80 px-2.5 py-1 rounded-xl border border-emerald-300 dark:border-emerald-700">
                                                    {{ $wDay['daily_booked'] }} نوبت ثبت‌شده
                                                </span>
                                                @if ($wDay['capacity_per_day'] !== null)
                                                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $wDay['daily_remaining'] }} ظرفیت مانده
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                 {{-- Appointments & Empty Slots List --}}
                                <div class="p-3.5 flex-1 space-y-2.5 min-h-[170px]">
                                    @if ($wDay['is_closed'] && empty($wDay['appointments']))
                                        <div class="p-6 text-center text-xs font-black text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 rounded-2xl border border-rose-200 dark:border-rose-900/60">
                                            {{ config('booking.labels.provider') }} در این روز حضور ندارد یا تعطیل رسمی است.
                                        </div>
                                    @elseif ($showEmptySlots && empty($wDay['items']))
                                        <div class="p-6 text-center text-xs font-black text-gray-400 dark:text-gray-500 bg-white/70 dark:bg-gray-800/60 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                                            هیچ نوبت یا اسلات کاری فعالی برای این روز وجود ندارد.
                                        </div>
                                    @elseif (!$showEmptySlots && empty($wDay['appointments']))
                                        <div class="p-6 text-center text-xs font-black text-gray-400 dark:text-gray-500 bg-white/70 dark:bg-gray-800/60 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                                            هیچ نوبتی برای این روز ثبت نشده است.
                                        </div>
                                    @else
                                        @if ($wDay['is_closed'])
                                            <div class="mb-2 p-2 bg-amber-100 text-amber-900 dark:bg-amber-950/70 dark:text-amber-200 text-[11px] font-black rounded-xl text-center border border-amber-300 dark:border-amber-800">
                                                🌙 این روز تعطیل است (نوبت‌های ثبت‌شده قبلی)
                                            </div>
                                        @endif

                                        @php
                                            $displayList = $showEmptySlots ? ($wDay['items'] ?? []) : array_map(fn($a) => ['type' => 'appointment', 'data' => $a], $wDay['appointments'] ?? []);
                                        @endphp

                                        @foreach ($displayList as $item)
                                            @if ($item['type'] === 'appointment')
                                                @php
                                                    $mApt = $item['data'];
                                                    $wStatusBadge = match($mApt['status']) {
                                                        \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => ['label' => 'قطعی', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700'],
                                                        \Modules\Booking\Entities\Appointment::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'class' => 'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-200 border-slate-300 dark:border-slate-700'],
                                                        \Modules\Booking\Entities\Appointment::STATUS_DONE => ['label' => 'انجام شده', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-300 dark:border-blue-700'],
                                                        default => ['label' => 'در انتظار', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300 dark:border-amber-700'],
                                                    };
                                                @endphp
                                                <div wire:click="openDetailsModal({{ $mApt['id'] }})"
                                                     class="p-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-indigo-500 dark:hover:border-indigo-400 hover:shadow-md transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-3 group border-r-4 border-r-indigo-500 shadow-2xs">
                                                    
                                                    {{-- Patient & Service Details --}}
                                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center flex-none shadow-xs">
                                                            {{ mb_substr($mApt['client_name'], 0, 1) }}
                                                        </div>
                                                        <div class="truncate">
                                                            <h5 class="text-sm font-black text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                                {{ $mApt['client_name'] }}
                                                            </h5>
                                                            <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                                                {{ $mApt['service_name'] }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    {{-- Time & Status Badges --}}
                                                    <div class="flex items-center justify-between sm:justify-end gap-2 flex-none pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100 dark:border-gray-700/60">
                                                        {{-- Time Badge --}}
                                                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 font-black text-xs dir-ltr shadow-xs">
                                                            <svg class="w-3.5 h-3.5 text-indigo-400 dark:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <span>{{ $mApt['start_time'] }}</span>
                                                            <span class="text-[10px] opacity-70 font-normal">تا</span>
                                                            <span>{{ $mApt['end_time'] }}</span>
                                                        </div>

                                                        {{-- Status Badge --}}
                                                        <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-lg border {{ $wStatusBadge['class'] }}">
                                                            {{ $wStatusBadge['label'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @elseif ($item['type'] === 'empty_slot')
                                                @php $eSlot = $item['data']; @endphp
                                                <div wire:click="openCreateModal({{ $wpData['provider']->id }}, '{{ $eSlot['start_time'] }}', '{{ $wDay['jalali_date'] }}')"
                                                     class="p-2.5 rounded-2xl border border-dashed border-emerald-300 dark:border-emerald-700/70 bg-emerald-50/40 dark:bg-emerald-950/20 hover:bg-emerald-100/70 dark:hover:bg-emerald-900/40 hover:border-emerald-500 transition-all cursor-pointer flex items-center justify-between gap-2 group shadow-2xs">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-black text-xs flex items-center justify-center group-hover:scale-110 transition-transform">
                                                            +
                                                        </div>
                                                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">
                                                            اسلات خالی
                                                        </span>
                                                        @if (($eSlot['capacity'] ?? 1) > 1)
                                                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-200/70 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                                                                {{ $eSlot['remaining_capacity'] }} از {{ $eSlot['capacity'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-0.5 rounded-lg bg-white/90 dark:bg-gray-800 text-[11px] font-black text-gray-800 dark:text-gray-200 dir-ltr border border-emerald-200 dark:border-emerald-800/80 shadow-2xs">
                                                            {{ $eSlot['start_time'] }} - {{ $eSlot['end_time'] }}
                                                        </span>
                                                        <span class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 group-hover:underline">
                                                            ثبت ↵
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>

                                {{-- Card Footer --}}
                                @if (!$wDay['is_closed'] && empty($wDay['is_past']))
                                    <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700/80 flex items-center justify-between gap-2">
                                        <button wire:click="openCreateModal({{ $wpData['provider']->id }}, '09:00', '{{ $wDay['jalali_date'] }}')"
                                                class="flex-1 py-2.5 px-3 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-700 transition-all text-center flex items-center justify-center gap-1.5 shadow-sm shadow-indigo-500/20">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                            ثبت نوبت جدید
                                        </button>
                                        <button wire:click="goToDay('{{ $wDay['jalali_date'] }}')"
                                                class="px-3.5 py-2.5 rounded-xl text-xs font-extrabold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/80 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all flex items-center gap-1 border border-gray-200 dark:border-gray-600"
                                                title="ورود به تقویم کامل روز">
                                            روزانه ↵
                                        </button>
                                    </div>
                                @elseif (!$wDay['is_closed'])
                                    <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700/80 flex items-center justify-between gap-2">
                                        <div class="flex-1 py-2 text-center text-xs font-extrabold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900 rounded-xl">
                                            ⏳ زمان این روز سپری شده است
                                        </div>
                                        <button wire:click="goToDay('{{ $wDay['jalali_date'] }}')"
                                                class="px-3.5 py-2.5 rounded-xl text-xs font-extrabold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/80 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all flex items-center gap-1 border border-gray-200 dark:border-gray-600"
                                                title="مشاهده جزئیات روز">
                                            روزانه ↵
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-gray-500 font-bold bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    هیچ ارائه‌دهنده‌ای یافت نشد.
                </div>
            @endforelse
        </div>

    @elseif ($calendarView === 'month')
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden p-5 sm:p-6 space-y-5">
            
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-200 dark:border-gray-700/80">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                        🗓️ تقویم ماه {{ $monthData['year_month_title'] ?? '' }}
                    </h3>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-0.5">نمای کلی وضعیت پذیرش و نوبت‌های ثبت‌شده ماه</p>
                </div>
                <span class="text-xs font-extrabold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-200 dark:border-indigo-800 px-3 py-1.5 rounded-2xl flex items-center gap-1.5 self-start sm:self-auto">
                    <span>💡 جهت ورود به روز، روی خانه کلیک کنید</span>
                </span>
            </div>

            {{-- 7 Columns Header --}}
            <div class="grid grid-cols-7 gap-2 sm:gap-3 text-center text-xs font-black text-gray-700 dark:text-gray-300 pb-2">
                <div class="bg-gray-100 dark:bg-gray-900/80 py-2 rounded-xl border border-gray-200 dark:border-gray-700/80">شنبه</div>
                <div class="bg-gray-100 dark:bg-gray-900/80 py-2 rounded-xl border border-gray-200 dark:border-gray-700/80">یکشنبه</div>
                <div class="bg-gray-100 dark:bg-gray-900/80 py-2 rounded-xl border border-gray-200 dark:border-gray-700/80">دوشنبه</div>
                <div class="bg-gray-100 dark:bg-gray-900/80 py-2 rounded-xl border border-gray-200 dark:border-gray-700/80">سه‌شنبه</div>
                <div class="bg-gray-100 dark:bg-gray-900/80 py-2 rounded-xl border border-gray-200 dark:border-gray-700/80">چهارشنبه</div>
                <div class="bg-gray-100 dark:bg-gray-900/80 py-2 rounded-xl border border-gray-200 dark:border-gray-700/80 text-indigo-600 dark:text-indigo-400 font-black">پنجشنبه</div>
                <div class="bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 py-2 rounded-xl border border-rose-200 dark:border-rose-800 font-black">جمعه</div>
            </div>

            {{-- Month Grid Rows --}}
            <div class="space-y-2.5">
                @foreach ($monthData['grid'] as $weekRow)
                    <div class="grid grid-cols-7 gap-2 sm:gap-3">
                        @foreach ($weekRow as $cell)
                            @if ($cell === null)
                                <div class="min-h-[105px] rounded-2xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800/40 opacity-40"></div>
                            @else
                                <div wire:click="goToDay('{{ $cell['jalali_date'] }}')"
                                     class="min-h-[105px] p-3 rounded-2xl border-2 transition-all cursor-pointer flex flex-col justify-between group hover:shadow-md relative overflow-hidden
                                     {{ $cell['is_today'] ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 shadow-sm shadow-indigo-500/10' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 hover:border-indigo-400 dark:hover:border-indigo-600' }}">

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm sm:text-base font-black {{ $cell['is_today'] ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-900 dark:text-gray-100' }}">
                                            {{ $cell['day_num'] }}
                                        </span>
                                        @if ($cell['is_today'])
                                            <span class="text-[9px] font-black px-2 py-0.5 rounded-lg bg-indigo-600 text-white shadow-2xs">امروز</span>
                                        @endif
                                    </div>

                                    <div class="mt-2 space-y-1">
                                        @if ($cell['total_appts'] > 0)
                                            <div class="flex items-center justify-between text-[11px] font-black px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                                <span>{{ $cell['total_appts'] }} نوبت</span>
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            </div>
                                        @else
                                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 block text-center opacity-70">بدون نوبت</span>
                                        @endif
                                    </div>

                                    <div class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity text-center mt-1 bg-indigo-50 dark:bg-indigo-950/60 py-0.5 rounded-lg border border-indigo-100 dark:border-indigo-900/50">
                                        ورود به روز ↵
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Quick Create Appointment Modal (طراحی ۲ مرحله‌ای هوشمند و بسیار خوانا) --}}
    @if ($showModal)
        @php
            $hasCustomForm = !empty($modalFormSchema) && !empty($modalFormSchema['fields']);
            $modalSelectedService = !empty($modalServiceId) ? collect($services)->firstWhere('id', $modalServiceId) : null;
            $modalSelectedProvider = !empty($modalProviderId) ? collect($providers)->firstWhere('id', $modalProviderId) : null;
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/65 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
             @keydown.escape.window="$wire.closeModal()">
            
            <div class="bg-white dark:bg-gray-800 rounded-3xl {{ $hasCustomForm && $modalStep === 2 ? 'max-w-4xl lg:max-w-5xl' : 'max-w-2xl sm:max-w-3xl' }} w-full shadow-2xl border border-gray-200/80 dark:border-gray-700/80 relative flex flex-col min-h-[580px] max-h-[95vh] sm:max-h-[92vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200"
                 @click.outside="$wire.closeModal()">

                {{-- Top Gradient Accent Line --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 shrink-0"></div>

                {{-- Modal Header (Fixed) --}}
                <div class="px-6 sm:px-7 py-4 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between shrink-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 border border-indigo-200/60 dark:border-indigo-800/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 shadow-2xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                                ثبت سریع نوبت
                            </h3>
                            <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                                ثبت و زمان‌بندی برای روز <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedDateJalali }}</span>
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Stepper Progress Bar (اگر سرویس فرم اختصاصی دارد) --}}
                @if ($hasCustomForm)
                    <div class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700/70 px-6 sm:px-7 py-2.5 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2 sm:gap-3 w-full">
                            {{-- Step 1 Button/Indicator --}}
                            <button type="button"
                                    wire:click="goToModalStep(1)"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-black transition cursor-pointer
                                    {{ $modalStep === 1 ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200/60 dark:hover:bg-gray-800' }}">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] {{ $modalStep === 1 ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">۱</span>
                                <span>مشخصات نوبت و مراجع</span>
                            </button>

                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>

                            {{-- Step 2 Button/Indicator --}}
                            <button type="button"
                                    wire:click="goToModalStep(2)"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-black transition cursor-pointer
                                    {{ $modalStep === 2 ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200/60 dark:hover:bg-gray-800' }}">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] {{ $modalStep === 2 ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">۲</span>
                                <span>فرم اختصاصی سرویس</span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Scrollable Content Body --}}
                <div class="overflow-y-auto px-6 sm:px-7 py-6 space-y-6 flex-1">
                    @if ($modalError)
                        <div class="p-4 bg-rose-50 dark:bg-rose-900/30 border-r-4 border-rose-500 text-rose-800 dark:text-rose-200 rounded-2xl text-xs sm:text-sm font-bold flex items-center gap-3">
                            <svg class="w-5 h-5 text-rose-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $modalError }}</span>
                        </div>
                    @endif

                    {{-- STEP 1: Basic Information & Client Selection --}}
                    @if (!$hasCustomForm || $modalStep === 1)
                        <div class="space-y-5.5">
                            {{-- Provider & Service Row --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">{{ config('booking.labels.provider') }}</label>
                                    <select wire:model.live="modalProviderId" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-gray-900 dark:text-gray-100 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-500 shadow-2xs transition-all">
                                        <option value="">انتخاب {{ config('booking.labels.provider') }}...</option>
                                        @foreach ($providers as $prov) <option value="{{ $prov->id }}">{{ $prov->name }}</option> @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">نوع سرویس</label>
                                    <select wire:model.live="modalServiceId" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-gray-900 dark:text-gray-100 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-500 shadow-2xs transition-all">
                                        <option value="">انتخاب سرویس...</option>
                                        @foreach ($services as $svc) <option value="{{ $svc->id }}">{{ $svc->name }}</option> @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Client Selector Component --}}
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200">
                                        انتخاب {{ $clientLabel }}
                                    </label>
                                    @if ($modalClientId)
                                        <span class="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            {{ $clientLabel }} انتخاب شد
                                        </span>
                                    @endif
                                </div>

                                @if (!empty($selectedModalClient))
                                    {{-- Selected Client Card --}}
                                    <div class="bg-emerald-50/90 dark:bg-emerald-950/40 border-2 border-emerald-300 dark:border-emerald-700/70 rounded-2xl p-4 flex items-center justify-between gap-3 shadow-2xs">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-black text-lg flex-none shadow-xs">
                                                {{ mb_substr($selectedModalClient->full_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-black text-sm sm:text-base text-emerald-950 dark:text-emerald-100 flex items-center flex-wrap gap-2">
                                                    <span>{{ $selectedModalClient->full_name }}</span>
                                                    @if ($modalWaitlistId)
                                                        <span class="inline-flex items-center gap-1 text-[10px] px-2.5 py-0.5 rounded-md font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300 dark:border-amber-700/70">
                                                            ⚡ تخصیص از صف انتظار @if($selectedWaitlistEntry?->global_queue_rank) (نفر {{ $selectedWaitlistEntry->global_queue_rank }} کلینیک) @endif
                                                            @if($selectedWaitlistEntry?->duration_minutes)
                                                                <span>• {{ $selectedWaitlistEntry->duration_minutes }} دقیقه</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 mt-1.5 flex items-center flex-wrap gap-x-4 gap-y-1">
                                                    @if ($selectedModalClient->phone) <span class="dir-ltr text-right">📞 {{ $selectedModalClient->phone }}</span> @endif
                                                    @if ($selectedModalClient->national_code) <span>🆔 کد ملی: {{ $selectedModalClient->national_code }}</span> @endif
                                                    @if ($selectedModalClient->case_number) <span>📋 پرونده: {{ $selectedModalClient->case_number }}</span> @endif
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="clearModalClient" class="px-3.5 py-2 rounded-xl bg-emerald-200/80 dark:bg-emerald-900/80 text-emerald-900 dark:text-emerald-100 text-xs font-bold hover:bg-emerald-300 dark:hover:bg-emerald-800 transition cursor-pointer shrink-0">
                                            تغییر {{ $clientLabel }}
                                        </button>
                                    </div>
                                @else
                                    {{-- 2 or 3-Way Mode Switcher --}}
                                    <div class="grid {{ $isQueueEnabled ? 'grid-cols-3' : 'grid-cols-2' }} gap-1.5 p-1.5 bg-gray-100 dark:bg-gray-900/80 rounded-2xl border border-gray-200 dark:border-gray-700/80 text-xs font-bold">
                                        <button type="button"
                                                wire:click="setModalClientTab('search')"
                                                class="py-2.5 px-3 rounded-xl transition-all flex items-center justify-center gap-1.5 cursor-pointer {{ $modalClientTab === 'search' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            <span>جستجوی {{ $clientLabel }}</span>
                                        </button>

                                        @if($isQueueEnabled)
                                            <button type="button"
                                                    wire:click="setModalClientTab('waitlist')"
                                                    class="py-2.5 px-3 rounded-xl transition-all flex items-center justify-center gap-1.5 cursor-pointer {{ $modalClientTab === 'waitlist' ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span>صف انتظار</span>
                                                @if($waitlistCount > 0)
                                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $modalClientTab === 'waitlist' ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                                        {{ $waitlistCount }}
                                                    </span>
                                                @endif
                                            </button>
                                        @endif

                                        <button type="button"
                                                wire:click="setModalClientTab('new')"
                                                class="py-2.5 px-3 rounded-xl transition-all flex items-center justify-center gap-1.5 cursor-pointer {{ $modalClientTab === 'new' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            <span>{{ $clientLabel }} جدید</span>
                                        </button>
                                    </div>

                                    {{-- Tab 1: Search Existing Client --}}
                                    @if ($modalClientTab === 'search')
                                        <div class="space-y-2.5">
                                            <div class="relative">
                                                <input type="text"
                                                       wire:model.live.debounce.300ms="modalClientSearch"
                                                       class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl pr-10 pl-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-500 shadow-2xs transition-all"
                                                       placeholder="جستجوی نام، شماره تماس، کد ملی یا شماره پرونده {{ $clientLabel }}...">
                                                <svg class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>

                                            {{-- Search Results List --}}
                                            @if (!empty($clientsForModal) && count($clientsForModal) > 0)
                                                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl max-h-56 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/60 shadow-lg bg-gray-50/50 dark:bg-gray-900/40 p-2 space-y-1.5">
                                                    <div class="px-2.5 py-1 text-xs font-bold text-gray-500 dark:text-gray-400 flex justify-between items-center">
                                                        <span>{{ empty($modalClientSearch) ? '۳ ' . $clientLabel . ' اخیر' : 'نتایج جستجو (' . count($clientsForModal) . ' مورد)' }}</span>
                                                        <span class="text-[10px] text-indigo-500 dark:text-indigo-400 font-semibold">برای انتخاب کلیک کنید</span>
                                                    </div>
                                                    @foreach ($clientsForModal as $c)
                                                        <div wire:click="selectModalClient({{ $c->id }})"
                                                             class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/40 cursor-pointer flex items-center justify-between transition-all group shadow-2xs">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-black text-xs flex items-center justify-center shrink-0">
                                                                    {{ mb_substr($c->full_name, 0, 1) }}
                                                                </div>
                                                                <div>
                                                                    <span class="font-black text-xs sm:text-sm block text-gray-900 dark:text-white">{{ $c->full_name }}</span>
                                                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2 mt-0.5 flex-wrap">
                                                                        @if($c->phone) <span class="dir-ltr text-right">📞 {{ $c->phone }}</span> @endif
                                                                        @if($c->national_code) <span>• کد ملی: {{ $c->national_code }}</span> @endif
                                                                        @if($c->case_number) <span>• پرونده: {{ $c->case_number }}</span> @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-indigo-600 group-hover:bg-indigo-700 text-white transition shadow-2xs shrink-0">
                                                                انتخاب
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif ($modalClientSearch && empty($clientsForModal))
                                                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-2xl text-center text-amber-800 dark:text-amber-200 text-xs sm:text-sm font-bold">
                                                    {{ $clientLabel }} مورد نظر یافت نشد
                                                </div>
                                            @endif
                                        </div>

                                    {{-- Tab 2: Select from Waitlist Queue --}}
                                    @elseif ($modalClientTab === 'waitlist' && $isQueueEnabled)
                                        <div class="space-y-2.5">
                                            @if($waitlistCount > 0)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between px-1">
                                                    <span>لیست مراجعین در انتظار ({{ $waitlistCount }} مورد)</span>
                                                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">مرتب‌سازی بر اساس زمان و تطابق</span>
                                                </div>

                                                <div class="space-y-2.5 max-h-64 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700/80 rounded-2xl bg-gray-50/50 dark:bg-gray-900/30">
                                                    @foreach($waitlistForModal as $wEntry)
                                                        @php
                                                            $isMatchBoth = ($modalServiceId && $wEntry->service_id == $modalServiceId && $modalProviderId && $wEntry->provider_user_id == $modalProviderId);
                                                            $isMatchService = ($modalServiceId && $wEntry->service_id == $modalServiceId);
                                                            $isMatchProvider = ($modalProviderId && $wEntry->provider_user_id == $modalProviderId);
                                                            $isGeneral = (!$wEntry->service_id && !$wEntry->provider_user_id);
                                                        @endphp
                                                        <div wire:click="selectWaitlistEntry({{ $wEntry->id }})"
                                                             class="p-3.5 rounded-2xl border transition-all duration-150 cursor-pointer flex flex-col gap-2.5 hover:shadow-md
                                                             {{ $isMatchBoth ? 'bg-indigo-50/90 dark:bg-indigo-950/50 border-indigo-300 dark:border-indigo-800 shadow-xs' : ($isMatchService ? 'bg-blue-50/80 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800/70' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-amber-400 dark:hover:border-amber-600') }}">
                                                            
                                                            <div class="flex items-center justify-between gap-2">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    {{-- Queue Rank Badge --}}
                                                                    @if($wEntry->global_queue_rank === 1)
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-black text-xs shadow-2xs border border-emerald-300 dark:border-emerald-800">
                                                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                                                            نفر ۱ کلینیک
                                                                        </span>
                                                                    @else
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold text-xs border border-gray-200 dark:border-gray-600">
                                                                            نفر {{ $wEntry->global_queue_rank }} کلینیک
                                                                        </span>
                                                                    @endif
                                                                    @if($wEntry->service_id)
                                                                        <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-black">
                                                                            (نفر {{ $wEntry->service_queue_rank }} در سرویس)
                                                                        </span>
                                                                    @endif

                                                                    <span class="font-black text-xs sm:text-sm text-gray-900 dark:text-white">{{ $wEntry->client?->full_name ?? 'بدون نام' }}</span>
                                                                    @if($wEntry->client?->phone)
                                                                        <span class="text-xs text-gray-500 dark:text-gray-400 dir-ltr">({{ $wEntry->client->phone }})</span>
                                                                    @endif
                                                                </div>

                                                                {{-- Priority Match Tag --}}
                                                                <div>
                                                                    @if($isMatchBoth)
                                                                        <span class="text-[10px] font-black px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                                            🎯 تطابق کامل
                                                                        </span>
                                                                    @elseif($isMatchService)
                                                                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                                                            🛠️ سرویس مرتبط
                                                                        </span>
                                                                    @elseif($isMatchProvider)
                                                                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300">
                                                                            👤 ارائه‌دهنده
                                                                        </span>
                                                                    @elseif($isGeneral)
                                                                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                                            🌐 صف عمومی
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300 pt-2 border-t border-gray-100 dark:border-gray-700/60 flex-wrap gap-y-1">
                                                                <div class="flex items-center gap-2.5 flex-wrap">
                                                                    <span>سرویس: <strong class="text-gray-900 dark:text-gray-100">{{ $wEntry->service?->name ?? 'عمومی' }}</strong></span>
                                                                    @if($wEntry->provider)
                                                                        <span>• {{ config('booking.labels.provider') }}: <strong class="text-gray-900 dark:text-gray-100">{{ $wEntry->provider->name }}</strong></span>
                                                                    @endif
                                                                    @if($wEntry->preferred_date)
                                                                        <span>• تاریخ ترجیحی: <strong class="text-gray-900 dark:text-gray-100">{{ \Morilog\Jalali\Jalalian::fromDateTime($wEntry->preferred_date)->format('Y/m/d') }}</strong></span>
                                                                    @endif
                                                                    @if($wEntry->duration_minutes)
                                                                        <span>• مدت زمان: <strong class="text-indigo-600 dark:text-indigo-400">{{ $wEntry->duration_minutes }} دقیقه</strong></span>
                                                                    @endif
                                                                    @if(!empty($wEntry->appointment_form_response_json))
                                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200 dark:border-indigo-800/60">
                                                                            📝 فرم اختصاصی
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <span class="text-indigo-600 dark:text-indigo-400 font-black hover:underline shrink-0 text-xs">
                                                                    تخصیص نوبت ↵
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="p-6 bg-white dark:bg-gray-800 border border-dashed border-gray-200 dark:border-gray-700 rounded-2xl text-center space-y-1.5">
                                                    <div class="text-3xl">⏳</div>
                                                    <div class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">صف انتظار خالی است</div>
                                                    <div class="text-xs text-gray-400">در حال حاضر مراجعی در صف انتظار فعال ثبت نشده است.</div>
                                                </div>
                                            @endif
                                        </div>

                                    {{-- Tab 3: Quick Create New Client (هماهنگ با تم دارک و لایت) --}}
                                    @elseif ($modalClientTab === 'new')
                                        <div class="p-4 bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-2xs">
                                            @includeIf('clients::widgets.client-quick-create')
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- Status & Time Row --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Dynamic Status Selector from Settings --}}
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">وضعیت نوبت</label>
                                    @php
                                        $availableStatuses = $this->modalAvailableStatuses;
                                        $currentSelectedStatus = collect($availableStatuses)->firstWhere('id', $modalStatus);
                                        $currentColor = $currentSelectedStatus['color'] ?? '#6366f1';
                                    @endphp
                                    <div class="relative">
                                        <select wire:model.live="modalStatus"
                                                class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl pr-8 pl-3.5 py-2.5 text-gray-900 dark:text-gray-100 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-500 shadow-2xs transition-all cursor-pointer">
                                            @forelse ($availableStatuses as $st)
                                                <option value="{{ $st['id'] }}">{{ $st['name'] }}</option>
                                            @empty
                                                <option value="{{ \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED }}">تایید شده</option>
                                            @endforelse
                                        </select>
                                        <div class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center">
                                            <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs" style="background-color: {{ $currentColor }};"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Time Input --}}
                                <div>
                                    <div class="flex gap-2.5">
                                        <div class="w-1/2">
                                            <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">ساعت شروع</label>
                                            <input type="text" wire:model="modalStartTime" class="w-full text-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 shadow-2xs transition-all dir-ltr" placeholder="09:00">
                                        </div>
                                        <div class="w-1/2">
                                            <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">ساعت پایان</label>
                                            <input type="text" wire:model="modalEndTime" class="w-full text-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 shadow-2xs transition-all dir-ltr" placeholder="09:30">
                                        </div>
                                    </div>

                                    {{-- Quick Available Free Slot Chips (Minimal, clean, and horizontal-scrollable) --}}
                                    @php
                                        $freeSlots = $this->availableModalSlots;
                                    @endphp
                                    @if (!empty($freeSlots) && count($freeSlots) > 0)
                                        <div class="mt-2.5 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    <span>اسلات‌های خالی پیشنهادی امروز:</span>
                                                </span>
                                                @if (!empty($modalStartTime) && in_array($modalStartTime, $freeSlots))
                                                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800/60">
                                                        زمان مجاز و آزاد انتخاب شده ✓
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar text-xs">
                                                @foreach (array_slice($freeSlots, 0, 12) as $fSlot)
                                                    <button type="button"
                                                            wire:click="setModalTimeSlot('{{ $fSlot }}')"
                                                            class="px-2.5 py-1 rounded-lg text-xs font-black transition-all cursor-pointer shrink-0 border {{ $modalStartTime === $fSlot ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/70 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600' }}">
                                                        {{ $fSlot }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">توضیحات / یادداشت</label>
                                <textarea wire:model="modalNotes" rows="2" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 shadow-2xs transition-all" placeholder="توضیحات اختیاری نوبت..."></textarea>
                            </div>
                        </div>

                    {{-- STEP 2: Custom Service Dynamic Form & Dental Chart (اختصاصی تمام‌عرض با فضای کافی) --}}
                    @elseif ($hasCustomForm && $modalStep === 2)
                        <div class="space-y-6">
                            {{-- Step 1 Summary Banner --}}
                            <div class="p-4 bg-indigo-50/80 dark:bg-indigo-950/40 rounded-2xl border border-indigo-200 dark:border-indigo-800 flex items-center justify-between gap-3 flex-wrap shadow-2xs">
                                <div class="flex items-center gap-3 flex-wrap text-xs sm:text-sm font-bold text-indigo-950 dark:text-indigo-200">
                                    <span>👤 مراجع: <strong class="text-indigo-600 dark:text-indigo-400">{{ $selectedModalClient?->full_name }}</strong></span>
                                    <span>• 🛠️ سرویس: <strong class="text-indigo-600 dark:text-indigo-400">{{ $modalSelectedService?->name }}</strong></span>
                                    @if($modalSelectedProvider)
                                        <span>• {{ config('booking.labels.provider') }}: <strong class="text-indigo-600 dark:text-indigo-400">{{ $modalSelectedProvider->name }}</strong></span>
                                    @endif
                                    <span>• ⏰ ساعت: <strong class="text-indigo-600 dark:text-indigo-400 dir-ltr">{{ $modalStartTime }} تا {{ $modalEndTime }}</strong></span>
                                </div>
                                <button type="button" wire:click="goToModalStep(1)" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                    ویرایش مشخصات ↵
                                </button>
                            </div>

                            {{-- Full-width Dynamic Form --}}
                            <div class="bg-white dark:bg-gray-800/80 rounded-2xl">
                                <x-booking::service-dynamic-form
                                    :formSchema="$modalFormSchema"
                                    :formType="$modalFormType"
                                    :formName="$modalFormName"
                                    :formResponses="$modalFormResponses"
                                    modelPrefix="modalFormResponses" />
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer (Fixed & Sticky) --}}
                <div class="px-6 sm:px-7 py-4.5 bg-gray-50/90 dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between shrink-0">
                    {{-- Left Button (Cancel or Back) --}}
                    @if ($hasCustomForm && $modalStep === 2)
                        <button type="button" wire:click="goToModalStep(1)" class="px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold hover:bg-gray-100 dark:hover:bg-gray-700 transition shadow-2xs cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            <span>بازگشت به مشخصات نوبت</span>
                        </button>
                    @else
                        <button type="button" wire:click="closeModal" class="px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold hover:bg-gray-100 dark:hover:bg-gray-700 transition shadow-2xs cursor-pointer">
                            انصراف
                        </button>
                    @endif

                    {{-- Right Action Button (Next Step or Submit) --}}
                    @if ($hasCustomForm && $modalStep === 1)
                        <button type="button" wire:click="goToModalStep(2)"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-600/25 transition active:scale-98 cursor-pointer">
                            <span>ادامه و تکمیل فرم اختصاصی</span>
                            <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    @else
                        <button type="button" wire:click="saveNewAppointment" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-600/25 transition active:scale-98 disabled:opacity-50 cursor-pointer">
                            <span wire:loading.remove wire:target="saveNewAppointment" class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>{{ $hasCustomForm ? 'ثبت نهایی نوبت' : 'ثبت نوبت' }}</span>
                            </span>
                            <span wire:loading wire:target="saveNewAppointment" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                در حال ثبت...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Quick Details Appointment Modal (نمایش جزئیات سریع نوبت با استایل هماهنگ پنل) --}}
    @if ($showDetailsModal && $detailsAppointment)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-md flex items-center justify-center p-4"
             @keydown.escape.window="$wire.closeDetailsModal()">

            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full shadow-2xl border border-gray-100 dark:border-gray-700/80 relative overflow-hidden animate-in fade-in zoom-in-95 duration-200">

                {{-- Top Gradient Accent Line --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

                <div class="p-6 space-y-6">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/70 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    جزئیات نوبت #{{ $detailsAppointment['id'] }}
                                </h3>
                                <p class="text-xs font-bold text-gray-400 mt-0.5">{{ $detailsAppointment['date_jalali'] }}</p>
                            </div>
                        </div>
                        <button wire:click="closeDetailsModal" class="p-1.5 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Main Details Grid --}}
                    <div class="space-y-4 text-xs font-bold">
                        {{-- Status & Time Row --}}
                        <div class="grid grid-cols-2 gap-3 p-4 bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                            <div>
                                <span class="text-[11px] text-gray-400 block mb-1.5 font-medium">وضعیت فعلی</span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black border {{ $detailsAppointment['status_badge'] }}">
                                    <span class="w-2 h-2 rounded-full bg-current"></span>
                                    {{ $detailsAppointment['status_label'] }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[11px] text-gray-400 block mb-1.5 font-medium">ساعت نوبت</span>
                                <div class="flex items-center gap-1.5 text-sm font-black text-gray-900 dark:text-gray-100">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $detailsAppointment['start_time'] }} <span class="text-xs font-normal text-gray-400">تا</span> {{ $detailsAppointment['end_time'] }}
                                </div>
                            </div>
                        </div>

                        {{-- Client & Provider & Service Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- Client Block --}}
                            <div class="p-3.5 bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl border border-gray-100 dark:border-gray-700/60 flex items-start gap-3">
                                <div class="p-2 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"></path></svg>
                                </div>
                                <div class="truncate">
                                    <span class="text-[11px] font-medium text-gray-400 block mb-0.5">بیمار / مشتری</span>
                                    <span class="text-xs font-black text-gray-900 dark:text-gray-100 block truncate">{{ $detailsAppointment['client_name'] }}</span>
                                    <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block mt-1 dir-ltr text-right">📞 {{ $detailsAppointment['client_phone'] }}</span>
                                </div>
                            </div>

                            {{-- Provider Block --}}
                            <div class="p-3.5 bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl border border-gray-100 dark:border-gray-700/60 flex items-start gap-3">
                                <div class="p-2 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path></svg>
                                </div>
                                <div class="truncate">
                                    <span class="text-[11px] font-medium text-gray-400 block mb-0.5">{{ config('booking.labels.provider') }}</span>
                                    <span class="text-xs font-black text-gray-900 dark:text-gray-100 block truncate">{{ $detailsAppointment['provider_name'] }}</span>
                                    <span class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 block mt-1 truncate">{{ $detailsAppointment['service_name'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Entry/Exit times --}}
                        @if ($detailsAppointment['entry_time'] || $detailsAppointment['exit_time'])
                            <div class="grid grid-cols-2 gap-3 p-3 bg-indigo-50/50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-900/40 text-center">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 text-[11px] font-medium">زمان ورود:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-black mr-1.5">{{ $detailsAppointment['entry_time'] ?? '—' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 text-[11px] font-medium">زمان خروج:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-black mr-1.5">{{ $detailsAppointment['exit_time'] ?? '—' }}</span>
                                </div>
                            </div>
                        @endif

                        {{-- Notes --}}
                        @if (!empty($detailsAppointment['notes']))
                            <div class="p-3.5 bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                                <span class="text-[11px] font-medium text-gray-400 block mb-1">یادداشت / توضیحات</span>
                                <p class="text-gray-700 dark:text-gray-300 font-medium leading-relaxed">{{ $detailsAppointment['notes'] }}</p>
                            </div>
                        @endif

                        {{-- Cancel Reason --}}
                        @if (!empty($detailsAppointment['cancel_reason']))
                            <div class="p-3.5 bg-rose-50 dark:bg-rose-900/30 rounded-2xl border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200">
                                <span class="text-[11px] font-bold block mb-1 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    دلیل لغو نوبت:
                                </span>
                                <p class="font-medium text-xs">{{ $detailsAppointment['cancel_reason'] }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/70">
                        <button wire:click="closeDetailsModal" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/70 text-gray-700 dark:text-gray-200 text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                            بستن
                        </button>
                        <div class="flex items-center gap-2">
                            @if ($detailsAppointment['status'] !== \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN && $detailsAppointment['status'] !== \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT)
                                <button wire:click="cancelAppointment({{ $detailsAppointment['id'] }})" class="px-3.5 py-2.5 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-all flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    لغو نوبت
                                </button>
                            @endif
                            <a href="{{ $detailsAppointment['show_url'] }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                مشاهده کامل و ویرایش
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- SMART WAITLIST DRAWER (کشوی هوشمند مدیریت و فیلتر صف انتظار) --}}
    {{-- ============================================================ --}}
    @if ($isQueueEnabled && $showWaitlistDrawer)
        <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="waitlist-drawer-title" role="dialog" aria-modal="true">
            {{-- Backdrop with blur --}}
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity animate-in fade-in duration-200"
                 wire:click="closeWaitlistDrawer"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-4 sm:pl-12">
                <div class="w-screen max-w-md sm:max-w-lg bg-white dark:bg-gray-800 shadow-2xl border-l border-gray-200 dark:border-gray-700 flex flex-col transform transition ease-in-out duration-300 animate-in slide-in-from-right relative z-10 overflow-hidden">
                    
                    {{-- Top Accent Gradient Bar --}}
                    <div class="h-1.5 w-full bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 shrink-0"></div>

                    {{-- Drawer Header --}}
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-white dark:bg-gray-800 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/70 border border-amber-200 dark:border-amber-800/80 flex items-center justify-center text-amber-600 dark:text-amber-400 font-bold text-lg shrink-0 shadow-2xs">
                                📋
                            </div>
                            <div>
                                <h3 id="waitlist-drawer-title" class="text-sm sm:text-base font-black text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    <span>دستیار هوشمند صف انتظار</span>
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/80 text-amber-800 dark:text-amber-200 text-[11px] font-black border border-amber-300 dark:border-amber-700">
                                        {{ $waitlistForDrawer->count() }} نفر
                                    </span>
                                </h3>
                                <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                                    مشاهده، فیلتر و تخصیص مستقیم اسلات به مراجعین صف
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('user.booking.waitlist.index') }}" target="_blank"
                               class="px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700/80 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 text-xs font-bold transition shadow-2xs flex items-center gap-1"
                               title="ورود به صفحه مدیریت کامل صف">
                                <span>مدیریت صف</span>
                                <span class="text-xs">↗</span>
                            </a>
                            <button type="button" wire:click="closeWaitlistDrawer"
                                    class="p-2 rounded-xl bg-gray-100 dark:bg-gray-700/80 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/60 dark:hover:text-rose-300 text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition shadow-2xs cursor-pointer"
                                    title="بستن کشو">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Search & Filters Inside Drawer --}}
                    <div class="p-4 bg-gray-50/70 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700 space-y-3 shrink-0 shadow-2xs">
                        {{-- Search Input --}}
                        <div class="relative">
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text"
                                   wire:model.live.debounce.300ms="drawerSearch"
                                   placeholder="جستجوی نام، تلفن، کدملی یا توضیحات مراجع..."
                                   class="w-full pr-9 pl-3 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>

                        {{-- Service & Provider Filters --}}
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">فیلتر سرویس صف</label>
                                <select wire:model.live="drawerFilterServiceId"
                                        class="w-full py-2 px-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-500">
                                    <option value="">همه سرویس‌ها</option>
                                    @foreach($services as $svc)
                                        <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">فیلتر {{ config('booking.labels.provider') }} صف</label>
                                <select wire:model.live="drawerFilterProviderId"
                                        class="w-full py-2 px-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-500">
                                    <option value="">همه {{ config('booking.labels.providers') }}</option>
                                    @foreach($filterProviders as $prv)
                                        <option value="{{ $prv->id }}">{{ $prv->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if ($drawerFilterServiceId || $drawerFilterProviderId || !empty($drawerSearch))
                            <div class="flex items-center justify-between pt-1">
                                <span class="text-[10px] text-amber-700 dark:text-amber-300 font-bold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>فیلترهای صف فعال است</span>
                                </span>
                                <button type="button" wire:click="resetDrawerFilters" class="text-[10px] font-black text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">
                                    ✕ پاکسازی فیلترها
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Drawer Body: List of Waiting Clients --}}
                    <div class="flex-1 overflow-y-auto p-4 space-y-3.5 custom-scrollbar bg-gray-50/80 dark:bg-gray-900/80">
                        @forelse($waitlistForDrawer as $wItem)
                            @php
                                $statusColors = \Modules\Booking\Entities\BookingWaitlist::statusColors()[$wItem->status] ?? 'amber';
                                $isMatchingCalendarService = $selectedServiceId && $wItem->service_id == $selectedServiceId;
                                $isMatchingCalendarProvider = $selectedProviderId && $wItem->provider_user_id == $selectedProviderId;
                                $clientDisplayName = $wItem->client?->full_name ?? $wItem->client?->username ?? 'مشتری نامشخص';
                                $clientDisplayPhone = $wItem->client?->phone ?? ($wItem->client?->national_code ?? '—');
                            @endphp
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border {{ ($isMatchingCalendarService && $isMatchingCalendarProvider) ? 'border-amber-400 dark:border-amber-500 ring-2 ring-amber-400/40 dark:ring-amber-500/30' : 'border-gray-200 dark:border-gray-700' }} p-4 shadow-sm hover:shadow-md transition-all space-y-3">
                                
                                {{-- Card Top Header: Rank, Client Info, Status --}}
                                <div class="flex items-start justify-between gap-2 border-b border-gray-100 dark:border-gray-700/80 pb-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        {{-- Position Badge --}}
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-sm">
                                            #{{ $wItem->global_queue_rank }}
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-gray-100 truncate">
                                                {{ $clientDisplayName }}
                                            </h4>
                                            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 dir-ltr inline-block">
                                                {{ $clientDisplayPhone }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Status Badge --}}
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-amber-50 dark:bg-amber-950/80 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800/80 shrink-0">
                                        {{ $wItem->status_label }}
                                    </span>
                                </div>

                                {{-- Details Grid --}}
                                <div class="grid grid-cols-2 gap-2.5 text-[11px] font-bold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-100 dark:border-gray-700/60">
                                    <div>
                                        <span class="text-gray-400 dark:text-gray-500 block text-[10px]">🛠️ سرویس مورد نیاز:</span>
                                        <span class="text-gray-900 dark:text-gray-100 font-extrabold truncate block">
                                            {{ $wItem->service?->name ?? 'صف عمومی' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 dark:text-gray-500 block text-[10px]">👨‍⚕️ {{ config('booking.labels.provider') }} انتخابی:</span>
                                        <span class="text-gray-900 dark:text-gray-100 font-extrabold truncate block">
                                            {{ $wItem->provider?->name ?? 'هر ' . config('booking.labels.provider') . 'ی' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 dark:text-gray-500 block text-[10px]">⏱️ مدت زمان خدمت:</span>
                                        <span class="text-indigo-600 dark:text-indigo-400 font-black">
                                            {{ $wItem->duration_minutes ?: 30 }} دقیقه
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 dark:text-gray-500 block text-[10px]">📅 تاریخ ترجیحی:</span>
                                        <span class="text-gray-800 dark:text-gray-200 font-extrabold">
                                            @if ($wItem->preferred_date)
                                                {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($wItem->preferred_date))->format('Y/m/d') }}
                                            @else
                                                اولین فرصت
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                {{-- Notes / Custom Form responses preview --}}
                                @if (!empty($wItem->notes))
                                    <div class="text-[11px] text-gray-700 dark:text-gray-300 bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-900/60 p-2.5 rounded-xl">
                                        <span class="font-bold text-amber-800 dark:text-amber-300">یادداشت:</span>
                                        <span>{{ \Illuminate\Support\Str::limit($wItem->notes, 90) }}</span>
                                    </div>
                                @endif

                                @if (!empty($wItem->appointment_form_response_json))
                                    <div class="flex items-center gap-1.5 text-[10px] font-black text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 px-2.5 py-1 rounded-lg">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>دارای فرم اختصاصی و جزئیات بالینی تکمیل‌شده</span>
                                    </div>
                                @endif

                                {{-- Action Buttons --}}
                                <div class="grid grid-cols-2 gap-2.5 pt-1.5 border-t border-gray-100 dark:border-gray-700/80">
                                    {{-- Action 1: Filter Calendar & Sync Step --}}
                                    <button type="button"
                                            wire:click="applyWaitlistFilter({{ $wItem->id }})"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/70 dark:hover:bg-indigo-900/90 text-indigo-700 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-800 text-[11px] font-black transition-all active:scale-98 shadow-2xs cursor-pointer"
                                            title="تنظیم فیلتر تقویم، گام زمانی {{ $wItem->duration_minutes ?: 30 }} دقیقه‌ای و بستن کشو">
                                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        <span>تنظیم فیلتر تقویم</span>
                                    </button>

                                    {{-- Action 2: Direct Book / Quick Modal --}}
                                    <button type="button"
                                            wire:click="bookFromWaitlist({{ $wItem->id }})"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-[11px] font-black transition-all shadow-xs active:scale-98 cursor-pointer"
                                            title="باز کردن مودال ثبت نوبت با تمام اطلاعات از پیش تکمیل‌شده">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        <span>ثبت نوبت فوری</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 px-4 bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-300 dark:border-gray-700">
                                <div class="w-14 h-14 mx-auto rounded-3xl bg-amber-50 dark:bg-amber-950/50 text-amber-500 flex items-center justify-center text-2xl mb-3 shadow-inner">
                                    ⏳
                                </div>
                                <h4 class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200">
                                    هیچ مراجعی در این بخش از صف یافت نشد
                                </h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto">
                                    @if ($drawerFilterServiceId || $drawerFilterProviderId || !empty($drawerSearch))
                                        برای مشاهده مراجعین بیشتر، فیلترهای بالا را پاکسازی کنید.
                                    @else
                                        در حال حاضر مراجع فعالی در صف انتظار نوبت‌دهی وجود ندارد.
                                    @endif
                                </p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Drawer Footer Note --}}
                    <div class="p-3.5 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-[11px] font-bold text-gray-600 dark:text-gray-300 shrink-0">
                        <span class="flex items-center gap-1 text-gray-600 dark:text-gray-300">
                            💡 کلیک روی دکمه‌ها کشوی صف را می‌بندد تا تقویم یا فرم ثبت را ببینید.
                        </span>
                        <a href="{{ route('user.booking.waitlist.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:underline font-black shrink-0">
                            مدیریت صف ↵
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
