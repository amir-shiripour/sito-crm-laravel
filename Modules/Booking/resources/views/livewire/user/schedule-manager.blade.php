<div class="space-y-6" x-data="{
    draggedAppointmentId: null,
    isDragging: false,
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
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
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
    </div>

    {{-- Main Toolbar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        <div class="flex flex-col xl:flex-row items-center justify-between gap-4">

            {{-- Navigation & DatePicker --}}
            <div class="flex items-center gap-3 w-full xl:w-auto flex-wrap sm:flex-nowrap">
                <div class="flex items-center bg-gray-50 dark:bg-gray-900 rounded-xl p-1 border border-gray-200 dark:border-gray-700/80">
                    <button wire:click="previousPeriod" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition" title="بازه قبل">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    <button wire:click="today" class="px-4 py-2 text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:bg-white dark:hover:bg-gray-800 rounded-lg transition">امروز</button>
                    <button wire:click="nextPeriod" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition" title="بازه بعد">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                </div>

                <div class="relative flex-1 sm:w-64 min-w-[200px]">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <input type="text"
                           wire:model.live="selectedDateJalali"
                           data-jdp
                           data-jdp-only-date
                           class="w-full pr-10 pl-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-black text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 cursor-pointer text-center hover:bg-white dark:hover:bg-gray-800 transition-colors"
                           placeholder="انتخاب تاریخ...">
                </div>

                <div class="hidden md:block text-xs font-black text-indigo-700 dark:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-950/50 px-4 py-3 rounded-xl whitespace-nowrap border border-indigo-100 dark:border-indigo-900/60 shadow-sm">
                    @if ($calendarView === 'week')
                        📅 هفته: {{ $weekDays[0]['jalali_date'] ?? '' }} تا {{ $weekDays[6]['jalali_date'] ?? '' }}
                    @elseif ($calendarView === 'month')
                        🗓️ {{ $monthData['year_month_title'] ?? '' }}
                    @else
                        📆 {{ $dayOfWeekJalali }}
                    @endif
                </div>
            </div>

            {{-- Calendar View Switcher & Filters --}}
            <div class="flex items-center gap-3 w-full xl:w-auto flex-wrap sm:flex-nowrap justify-end">
                {{-- Main Calendar View Switcher --}}
                <div class="flex items-center bg-gray-100 dark:bg-gray-900 p-1 rounded-xl border border-gray-200 dark:border-gray-700/80">
                    <button wire:click="setCalendarView('day')" class="px-3.5 py-2 rounded-lg text-xs font-black transition flex items-center gap-1.5 {{ $calendarView === 'day' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                        📅 روزانه
                    </button>
                    <button wire:click="setCalendarView('week')" class="px-3.5 py-2 rounded-lg text-xs font-black transition flex items-center gap-1.5 {{ $calendarView === 'week' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                        📋 هفتگی
                    </button>
                    <button wire:click="setCalendarView('month')" class="px-3.5 py-2 rounded-lg text-xs font-black transition flex items-center gap-1.5 {{ $calendarView === 'month' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                        🗓️ ماهانه
                    </button>
                </div>

                @if ($calendarView === 'day')
                    <div class="flex items-center bg-gray-100 dark:bg-gray-900 p-1 rounded-xl border border-gray-200 dark:border-gray-700/80">
                        <button wire:click="setViewMode('grid')" class="px-3.5 py-2 rounded-lg text-xs font-black transition flex items-center gap-1.5 {{ $viewMode === 'grid' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                            ماتریسی
                        </button>
                        <button wire:click="setViewMode('timeline')" class="px-3.5 py-2 rounded-lg text-xs font-black transition flex items-center gap-1.5 {{ $viewMode === 'timeline' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/50 dark:border-gray-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200/50 dark:hover:bg-gray-800/50' }}">
                            تایم‌لاین
                        </button>
                    </div>
                @endif

                <select wire:model.live="selectedProviderId" class="text-xs font-black bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 hover:bg-white dark:hover:bg-gray-800 transition-colors">
                    <option value="" class="dark:bg-gray-800 dark:text-white">همه پزشکان</option>
                    @foreach ($providers as $prov)
                        <option value="{{ $prov->id }}" class="dark:bg-gray-800 dark:text-white">{{ $prov->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="selectedServiceId" class="text-xs font-black bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 hover:bg-white dark:hover:bg-gray-800 transition-colors">
                    <option value="" class="dark:bg-gray-800 dark:text-white">همه سرویس‌ها</option>
                    @foreach ($services as $svc)
                        <option value="{{ $svc->id }}" class="dark:bg-gray-800 dark:text-white">{{ $svc->name }} {{ $svc->custom_schedule_enabled ? '⚡' : '' }}</option>
                    @endforeach
                </select>

                <select wire:model.live="statusFilter" class="text-xs font-black bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 hover:bg-white dark:hover:bg-gray-800 transition-colors">
                    <option value="" class="dark:bg-gray-800 dark:text-white">همه وضعیت‌ها</option>
                    <option value="CONFIRMED" class="dark:bg-gray-800 dark:text-white">قطعی / تایید شده</option>
                    <option value="PENDING" class="dark:bg-gray-800 dark:text-white">در انتظار تایید</option>
                    <option value="PENDING_PAYMENT" class="dark:bg-gray-800 dark:text-white">در انتظار پرداخت</option>
                    <option value="DONE" class="dark:bg-gray-800 dark:text-white">انجام شده</option>
                    <option value="DRAFT" class="dark:bg-gray-800 dark:text-white">پیش‌نویس</option>
                    <option value="NO_SHOW" class="dark:bg-gray-800 dark:text-white">عدم حضور</option>
                    <option value="RESCHEDULED" class="dark:bg-gray-800 dark:text-white">جابجا شده</option>
                    <option value="CANCELED_BY_ADMIN" class="dark:bg-gray-800 dark:text-white">لغو شده (ادمین)</option>
                    <option value="CANCELED_BY_CLIENT" class="dark:bg-gray-800 dark:text-white">لغو شده (مشتری)</option>
                </select>

                <button wire:click="openCreateModal()" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-black hover:bg-indigo-700 active:scale-95 shadow-md shadow-indigo-500/20 whitespace-nowrap transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    نوبت جدید
                </button>
            </div>
        </div>

        {{-- Step Control & Hint --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-gray-600 dark:text-gray-400">⏱️ گام زمانی تقویم:</span>
                @if ($isStepLocked)
                    <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg text-amber-800 text-xs font-bold dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-200">
                        🔒 ثابت ({{ $timeStepMinutes }} دقیقه)
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <input type="number" min="5" max="480" step="5" wire:model.blur="timeStepMinutes"
                               class="w-20 px-2 py-1.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-center text-xs font-black text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500"
                               title="برای اعمال، عدد مورد نظر را تایپ کرده و کلیک کنید">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">دقیقه</span>
                        <div class="hidden sm:flex gap-1">
                            @foreach([15, 30, 45, 60, 90, 120] as $step)
                                <button wire:click="setStep({{ $step }})"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-black transition-all {{ $timeStepMinutes == $step ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-800' }}">
                                    {{ $step }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-300 bg-indigo-50/80 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 px-3.5 py-1.5 rounded-xl shadow-xs">
                💡 نکته: کلیک روی نوبت = مشاهده جزئیات | درگ نوبت = تغییر ساعت
            </div>
        </div>
    </div>

    {{-- Status Legend (راهنمای وضعیت‌های نوبت) --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-3.5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3 overflow-x-auto text-xs font-bold">
        <span class="text-gray-500 dark:text-gray-400 whitespace-nowrap flex items-center gap-1.5 pl-3 border-l border-gray-200 dark:border-gray-700">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            راهنمای وضعیت‌ها:
        </span>
        <div class="flex items-center gap-4 flex-wrap">
            <span class="inline-flex items-center gap-1.5 text-emerald-700 dark:text-emerald-300">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-emerald-200 dark:ring-emerald-900"></span> قطعی
            </span>
            <span class="inline-flex items-center gap-1.5 text-amber-700 dark:text-amber-300">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-amber-200 dark:ring-amber-900"></span> در انتظار
            </span>
            <span class="inline-flex items-center gap-1.5 text-yellow-700 dark:text-yellow-300">
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-500 ring-2 ring-yellow-200 dark:ring-yellow-900"></span> در انتظار پرداخت
            </span>
            <span class="inline-flex items-center gap-1.5 text-blue-700 dark:text-blue-300">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 ring-2 ring-blue-200 dark:ring-blue-900"></span> انجام شده
            </span>
            <span class="inline-flex items-center gap-1.5 text-purple-700 dark:text-purple-300">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500 ring-2 ring-purple-200 dark:ring-purple-900"></span> جابجا شده
            </span>
            <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-400 border border-dashed border-gray-600"></span> پیش‌نویس
            </span>
            <span class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-500 ring-2 ring-slate-200 dark:ring-slate-800"></span> عدم حضور
            </span>
            <span class="inline-flex items-center gap-1.5 text-rose-700 dark:text-rose-300">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-rose-200 dark:ring-rose-900"></span> لغو شده
            </span>
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
                                                    'SERVICE_PROVIDER' => ['label' => 'برنامه اختصاصی پزشک', 'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800'],
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
                        @if ($pData['policy']['is_closed'])
                            <div class="h-full flex flex-col items-center justify-center text-gray-400 py-10">
                                <span class="text-3xl mb-2">🌙</span>
                                <span class="text-sm font-black">امروز برنامه کاری ندارد</span>
                            </div>
                        @else
                            {{-- Grid of Time Slots: 1 to 3 spacious columns instead of cramped 5 columns --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
                                @forelse ($pData['slots'] as $slot)

                                    {{-- 1. EMPTY SLOT (Neutral Light/Dark Dashed Card, Visually Distinct from Booked Items) --}}
                                    @if(empty($slot['appointments']) && !$slot['in_break'])
                                        <div @dragover.prevent @drop="handleDrop($event, {{ $pData['provider']->id }}, '{{ $slot['start_time'] }}')"
                                             wire:click="openCreateModal({{ $pData['provider']->id }}, '{{ $slot['start_time'] }}')"
                                             :class="{ 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-950/80': isDragging }"
                                             class="group flex flex-col justify-between p-3.5 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50 hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40 cursor-pointer transition-all min-h-[105px] shadow-2xs">

                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 group-hover:bg-indigo-600 group-hover:text-white transition-colors font-black text-xs dir-ltr shadow-xs">
                                                    <span>{{ $slot['start_time'] }}</span>
                                                    <span class="text-[10px] opacity-75 font-normal">تا</span>
                                                    <span>{{ $slot['end_time'] }}</span>
                                                </div>
                                                <span class="text-[10px] font-black text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-950/80 border border-indigo-200 dark:border-indigo-800 px-2 py-0.5 rounded-lg">
                                                    ⚪ اسلات خالی
                                                </span>
                                            </div>

                                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200 dark:border-gray-800 group-hover:border-indigo-200 dark:group-hover:border-indigo-900/60">
                                                <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 group-hover:translate-x-1 transition-transform flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                                    <span>ثبت نوبت</span>
                                                </span>
                                                <span class="text-[10px] font-extrabold text-gray-400 dark:text-gray-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">آماده رزرو</span>
                                            </div>
                                        </div>

                                        {{-- 2. BREAK SLOT (Gray, Disabled) --}}
                                    @elseif($slot['in_break'])
                                        <div class="flex flex-col justify-between p-3.5 rounded-2xl bg-gray-100 dark:bg-gray-900/80 border border-gray-200 dark:border-gray-700/80 opacity-70 cursor-not-allowed min-h-[105px]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-black text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-800 px-2.5 py-1 rounded-xl dir-ltr">
                                                    {{ $slot['start_time'] }} - {{ $slot['end_time'] }}
                                                </span>
                                                <span class="text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-950/80 border border-amber-300 dark:border-amber-800 px-2 py-0.5 rounded-lg">
                                                    ☕ استراحت
                                                </span>
                                            </div>
                                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-2">خارج از ساعات پذیرش نوبت</p>
                                        </div>

                                        {{-- 3. BOOKED SLOT (Solid Colored Card with full details) --}}
                                    @else
                                        @foreach ($slot['appointments'] as $apt)
                                            @php
                                                $cardBg = match($apt['status']) {
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
                                            @endphp
                                            <div draggable="true" @dragstart="handleDragStart($event, {{ $apt['id'] }})" @dragend="handleDragEnd($event)"
                                                 wire:click="openDetailsModal({{ $apt['id'] }})"
                                                 class="flex flex-col justify-between p-3.5 rounded-2xl {{ $cardBg }} shadow-sm cursor-pointer hover:shadow-md transition-all relative group min-h-[105px]">

                                                {{-- سطر اول: نام بیمار و وضعیت --}}
                                                <div class="flex justify-between items-center gap-2">
                                                    <span class="text-sm font-black truncate text-gray-900 dark:text-white">{{ $apt['client_name'] }}</span>
                                                    <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-lg flex-none {{ $statusBadge['class'] }}">
                                                        {{ $statusBadge['label'] }}
                                                    </span>

                                                    {{-- دکمه‌های سریع --}}
                                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute top-2 left-2 bg-white/95 dark:bg-gray-900/95 rounded-xl flex gap-1 p-1 shadow-md border border-gray-200 dark:border-gray-700 z-20">
                                                        @if ($apt['status'] === \Modules\Booking\Entities\Appointment::STATUS_DRAFT)
                                                            <button wire:click.stop="updateStatus({{ $apt['id'] }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED }}')" class="bg-emerald-600 text-white p-1 rounded-lg hover:bg-emerald-700" title="تایید قطعی"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg></button>
                                                        @endif
                                                        <button wire:click.stop="cancelAppointment({{ $apt['id'] }})" class="bg-rose-600 text-white p-1 rounded-lg hover:bg-rose-700" title="لغو نوبت"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                                    </div>
                                                </div>

                                                {{-- سطر دوم: نام سرویس --}}
                                                <div class="text-xs font-bold opacity-85 truncate mt-1">
                                                    {{ $apt['service_name'] }} @if($apt['client_phone']) <span class="opacity-60">• {{ $apt['client_phone'] }}</span> @endif
                                                </div>

                                                {{-- سطر سوم: زمان دقیق با بج مشکی/سفید خالص --}}
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
                                        @endforeach

                                        {{-- Free Time Segments within partially booked slot --}}
                                        @if (!$slot['is_full'] && !empty($slot['free_segments']))
                                            @foreach ($slot['free_segments'] as $seg)
                                                <div wire:click="openCreateModal({{ $pData['provider']->id }}, '{{ $seg['start_time'] }}')"
                                                     class="flex flex-col justify-between p-3.5 rounded-2xl border-2 border-dashed border-indigo-300 dark:border-indigo-800 bg-indigo-50/40 dark:bg-indigo-950/20 hover:border-indigo-500 hover:bg-indigo-100/60 dark:hover:bg-indigo-900/40 cursor-pointer min-h-[105px] group transition-all">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-1.5 text-xs font-black text-indigo-800 dark:text-indigo-200">
                                                            <span>+ ثبت نوبت</span>
                                                        </div>
                                                        <span class="text-[10px] font-black text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/70 border border-indigo-300 dark:border-indigo-700 px-2 py-0.5 rounded-lg">
                                                            ⚡ {{ $seg['formatted_duration'] }} آزاد
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-indigo-200/70 dark:border-indigo-800/70">
                                                        <span class="text-xs font-black text-indigo-700 dark:text-indigo-300 dir-ltr bg-indigo-100 dark:bg-indigo-900/60 px-2.5 py-1 rounded-xl">
                                                            {{ $seg['start_time'] }} - {{ $seg['end_time'] }}
                                                        </span>
                                                    </div>
                                                </div>
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
                        <button wire:click="openCreateModal({{ $pData['provider']->id }})" class="flex items-center gap-1.5 px-3.5 py-2.5 bg-indigo-600 text-white rounded-2xl text-xs font-black hover:bg-indigo-700 transition-all shadow-md shadow-indigo-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            <span>ثبت نوبت جدید</span>
                        </button>
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

                            @if ($pData['policy']['is_closed'])
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 bg-gray-50/95 dark:bg-gray-900/95 z-10">
                                    <svg class="w-16 h-16 mb-4 text-indigo-500 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                                    <span class="text-lg font-black text-gray-800 dark:text-gray-200">پزشک در این تاریخ حضور ندارد</span>
                                </div>
                            @else

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
                            <div class="h-14 px-4 flex items-center font-black text-xs text-gray-800 dark:text-gray-200 bg-gray-100/90 dark:bg-gray-900">پزشکان / منابع</div>
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

                                    @if (!$pData['policy']['is_closed'])
                                        {{-- استراحت‌ها --}}
                                        @foreach ($pData['breaks'] as $brk)
                                            <div style="right: {{ $brk['left_percent'] }}%; width: {{ $brk['width_percent'] }}%;" class="absolute inset-y-1 bg-amber-50/80 dark:bg-amber-950/40 border-x border-amber-300 dark:border-amber-800 flex items-center justify-center overflow-hidden z-0">
                                                <span class="text-[9px] text-amber-800 dark:text-amber-300 font-black">☕ استراحت</span>
                                            </div>
                                        @endforeach

                                        {{-- اسلات‌های خالی جهت درگ یا کلیک --}}
                                        @foreach ($pData['slotDropTargets'] as $sTarget)
                                            <div @dragover.prevent @drop="handleDrop($event, {{ $pData['provider']->id }}, '{{ $sTarget['start_time'] }}')" wire:click="openCreateModal({{ $pData['provider']->id }}, '{{ $sTarget['start_time'] }}')" style="right: {{ $sTarget['left_percent'] }}%; width: {{ $sTarget['width_percent'] }}%;" class="absolute inset-y-1 group hover:bg-indigo-50/60 dark:hover:bg-indigo-950/40 cursor-pointer border-r border-transparent hover:border-indigo-400 flex justify-center items-center z-1 transition-colors"></div>
                                        @endforeach

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
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs font-black bg-gray-50/70 dark:bg-gray-900/70">
                                            🌙 تعطیل
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

                                {{-- Appointments List --}}
                                <div class="p-3.5 flex-1 space-y-3 min-h-[170px]">
                                    @if ($wDay['is_closed'])
                                        <div class="p-6 text-center text-xs font-black text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 rounded-2xl border border-rose-200 dark:border-rose-900/60">
                                            پزشک در این روز حضور ندارد یا تعطیل رسمی است.
                                        </div>
                                    @elseif (empty($wDay['appointments']))
                                        <div class="p-6 text-center text-xs font-black text-gray-400 dark:text-gray-500 bg-white/70 dark:bg-gray-800/60 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                                            هیچ نوبتی برای این روز ثبت نشده است.
                                        </div>
                                    @else
                                        @foreach ($wDay['appointments'] as $mApt)
                                            @php
                                                $wStatusBadge = match($mApt['status']) {
                                                    \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => ['label' => 'قطعی', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700'],
                                                    \Modules\Booking\Entities\Appointment::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'class' => 'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-200 border-slate-300 dark:border-slate-700'],
                                                    \Modules\Booking\Entities\Appointment::STATUS_DONE => ['label' => 'انجام شده', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-300 dark:border-blue-700'],
                                                    default => ['label' => 'در انتظار', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300 dark:border-amber-700'],
                                                };
                                            @endphp
                                            <div wire:click="openDetailsModal({{ $mApt['id'] }})"
                                                 class="p-3.5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-indigo-500 dark:hover:border-indigo-400 hover:shadow-md transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-3 group border-r-4 border-r-indigo-500 shadow-2xs">
                                                
                                                {{-- Patient & Service Details --}}
                                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center flex-none shadow-xs">
                                                        {{ mb_substr($mApt['client_name'], 0, 1) }}
                                                    </div>
                                                    <div class="truncate">
                                                        <h5 class="text-sm font-black text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                            {{ $mApt['client_name'] }}
                                                        </h5>
                                                        <p class="text-xs font-bold text-gray-600 dark:text-gray-300 truncate mt-0.5">
                                                            {{ $mApt['service_name'] }}
                                                        </p>
                                                    </div>
                                                </div>

                                                {{-- Time & Status Badges --}}
                                                <div class="flex items-center justify-between sm:justify-end gap-2 flex-none pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100 dark:border-gray-700/60">
                                                    {{-- Time Badge (Pure Black in Light / Pure White in Dark) --}}
                                                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 font-black text-xs dir-ltr shadow-xs">
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
                                        @endforeach
                                    @endif
                                </div>

                                {{-- Card Footer --}}
                                @if (!$wDay['is_closed'])
                                    <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700/80 flex items-center justify-between gap-2">
                                        <button wire:click="openCreateModal({{ $wpData['provider']->id }}, '09:00')"
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

    {{-- Quick Create Appointment Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-5 animate-in fade-in zoom-in duration-200">

                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        ثبت سریع نوبت
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">✕</button>
                </div>

                @if ($modalError)
                    <div class="p-3 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-lg text-xs font-bold">
                        {{ $modalError }}
                    </div>
                @endif

                <div class="space-y-4 text-xs font-bold">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1.5">پزشک / ارائه‌دهنده</label>
                            <select wire:model.live="modalProviderId" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                                <option value="">انتخاب پزشک...</option>
                                @foreach ($providers as $prov) <option value="{{ $prov->id }}">{{ $prov->name }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1.5">نوع سرویس</label>
                            <select wire:model.live="modalServiceId" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                                <option value="">انتخاب سرویس...</option>
                                @foreach ($services as $svc) <option value="{{ $svc->id }}">{{ $svc->name }}</option> @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 mb-1.5">جستجوی بیمار</label>
                        <input type="text" wire:model.live.debounce.300ms="modalClientSearch" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500" placeholder="نام یا شماره تماس...">
                        @if (!empty($clientsForModal))
                            <div class="mt-2 border border-gray-200 dark:border-gray-700 rounded-xl max-h-32 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/60 shadow-sm bg-white dark:bg-gray-800">
                                @foreach ($clientsForModal as $c)
                                    <div wire:click="$set('modalClientId', {{ $c->id }})" class="p-2 hover:bg-indigo-50 dark:hover:bg-gray-700 cursor-pointer flex justify-between items-center {{ $modalClientId == $c->id ? 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-200' }}">
                                        <span>{{ $c->full_name }}</span>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $c->phone }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1.5">وضعیت اولیه</label>
                            <select wire:model="modalStatus" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                                <option value="{{ \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED }}">قطعی</option>
                                <option value="{{ \Modules\Booking\Entities\Appointment::STATUS_DRAFT }}">پیش‌نویس</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <div class="w-1/2">
                                <label class="block text-gray-700 dark:text-gray-300 mb-1.5">شروع</label>
                                <input type="text" wire:model="modalStartTime" class="w-full text-center bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-2 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="w-1/2">
                                <label class="block text-gray-700 dark:text-gray-300 mb-1.5">پایان</label>
                                <input type="text" wire:model="modalEndTime" class="w-full text-center bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-2 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 mb-1.5">توضیحات</label>
                        <textarea wire:model="modalNotes" rows="2" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/80">
                    <button wire:click="closeModal" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700/70 text-gray-700 dark:text-gray-200 text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition">انصراف</button>
                    <button wire:click="saveNewAppointment" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition shadow-sm">ثبت نوبت</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Quick Details Appointment Modal (نمایش جزئیات سریع نوبت با استایل هماهنگ پنل) --}}
    @if ($showDetailsModal && $detailsAppointment)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-md flex items-center justify-center p-4"
             x-data
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
                                    <span class="text-[11px] font-medium text-gray-400 block mb-0.5">پزشک / ارائه‌دهنده</span>
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
</div>
