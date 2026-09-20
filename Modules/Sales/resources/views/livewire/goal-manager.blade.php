@php
    $inputClass = "w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors shadow-xs font-sans";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $stats = $goalStats ?? [];
@endphp

<div class="space-y-5 text-right" dir="rtl">
    {{-- اسکریپت و استایل JalaliDatePicker --}}
    @includeIf('partials.jalali-date-picker')

    <!-- ==========================================
         1. TOP KPI SUMMARY CARDS
         ========================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- کل اهداف فعال -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">کل اهداف فعال</span>
                <span class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white block font-sans">
                    {{ $stats['total'] ?? 0 }}
                </span>
                <span class="text-[10px] text-gray-400 dark:text-gray-500 block">اهداف برنامه‌ریزی‌شده</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100/60 dark:border-indigo-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
            </div>
        </div>

        <!-- اهداف محقق‌شده -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">اهداف محقق‌شده</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 block font-sans">
                    {{ $stats['achieved'] ?? 0 }}
                </span>
                <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 block">۱۰۰٪ موفقیت ثبت‌شده 🎉</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100/60 dark:border-emerald-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
        </div>

        <!-- میانگین پیشرفت کل -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">میانگین پیشرفت کل</span>
                <span class="text-xl sm:text-2xl font-black text-sky-600 dark:text-sky-400 block font-sans">
                    {{ $stats['avg_progress'] ?? 0 }}٪
                </span>
                <span class="text-[10px] text-sky-600/80 dark:text-sky-400/80 block">شاخص کلی عملکرد</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-100/60 dark:border-sky-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>

        <!-- در انتظار تکمیل -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">در انتظار تکمیل</span>
                <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 block font-sans">
                    {{ $stats['in_progress'] ?? 0 }}
                </span>
                <span class="text-[10px] text-amber-600/80 dark:text-amber-400/80 block">فرصت‌های دستیابی پیش‌رو</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-100/60 dark:border-amber-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ==========================================
         2. TOOLBAR & FILTERS
         ========================================== -->
    <div class="bg-white dark:bg-gray-800/90 p-4 rounded-2xl shadow-xs border border-gray-200/80 dark:border-gray-700/80 flex flex-col md:flex-row items-center justify-between gap-3">
        <!-- Period & Status Filters -->
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <!-- دوره زمانی -->
            <div class="inline-flex items-center bg-gray-100 dark:bg-gray-900/60 p-1 rounded-xl border border-gray-200/60 dark:border-gray-700/60 text-xs font-bold">
                <button wire:click="$set('filterPeriod', 'all')" 
                        class="px-3 py-1.5 rounded-lg transition-all {{ $filterPeriod === 'all' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    همه دوره‌ها
                </button>
                <button wire:click="$set('filterPeriod', 'daily')" 
                        class="px-3 py-1.5 rounded-lg transition-all {{ $filterPeriod === 'daily' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    روزانه
                </button>
                <button wire:click="$set('filterPeriod', 'weekly')" 
                        class="px-3 py-1.5 rounded-lg transition-all {{ $filterPeriod === 'weekly' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    هفتگی
                </button>
                <button wire:click="$set('filterPeriod', 'monthly')" 
                        class="px-3 py-1.5 rounded-lg transition-all {{ $filterPeriod === 'monthly' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    ماهانه
                </button>
            </div>

            <!-- وضعیت تحقق -->
            <select wire:model.live="filterStatus" class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-gray-200 font-medium">
                <option value="all">همه وضعیت‌ها</option>
                <option value="active">اهداف فعال</option>
                <option value="achieved">اهداف محقق‌شده (۱۰۰٪)</option>
                <option value="inactive">اهداف غیرفعال</option>
            </select>

            <!-- انتخابگر کارشناس ویژه مدیران -->
            @if($isManager)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 hidden lg:inline">کارشناس:</span>
                    <select wire:model.live="selectedUserId" class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-gray-200 font-medium">
                        @foreach($usersList as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <!-- دکمه تعریف هدف جدید -->
        <button wire:click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors active:scale-95 w-full md:w-auto shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>تعریف هدف جدید</span>
        </button>
    </div>

    <!-- ==========================================
         3. GOAL CARDS LIST (Progressive Cards)
         ========================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($goals as $goal)
            @php
                $isAchieved = $goal->is_achieved;
                $periodLabel = match($goal->period) {
                    'daily' => 'روزانه',
                    'weekly' => 'هفتگی',
                    'monthly' => 'ماهانه',
                    default => $goal->period,
                };
                $metricUnit = match($goal->goal_type) {
                    'conversion_rate' => 'درصد',
                    'talk_time_minutes' => 'دقیقه',
                    default => 'مورد',
                };
            @endphp
            <div class="bg-white dark:bg-gray-800/90 rounded-2xl p-5 shadow-xs border transition-all relative overflow-hidden {{ $goal->is_active ? ($isAchieved ? 'border-emerald-300/80 dark:border-emerald-800/80 ring-1 ring-emerald-500/20' : 'border-gray-200/80 dark:border-gray-700/80 hover:border-indigo-300 dark:hover:border-indigo-700/70') : 'border-gray-200/50 dark:border-gray-800 opacity-60' }}">
                <!-- لبه رنگی راست کارت -->
                <div class="absolute right-0 top-0 bottom-0 w-1.5 {{ $goal->is_active ? ($isAchieved ? 'bg-emerald-500' : 'bg-indigo-500') : 'bg-gray-400' }}"></div>

                <!-- هدر کارت -->
                <div class="flex items-start justify-between gap-3 mb-3 pr-1">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $goalTypes[$goal->goal_type] ?? $goal->goal_type_label }}
                            </h4>
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-700/60 dark:text-gray-300 border border-gray-200/60 dark:border-gray-600/40">
                                {{ $periodLabel }}
                            </span>
                            @if($isAchieved && $goal->is_active)
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 inline-flex items-center gap-1">
                                    <svg class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>محقق شد! 🎯</span>
                                </span>
                            @elseif(!$goal->is_active)
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                    غیرفعال
                                </span>
                            @endif
                        </div>
                        @if($goal->note)
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 pr-0.5">
                                {{ $goal->note }}
                            </p>
                        @endif
                    </div>

                    <!-- دکمه‌های عملیات هدف -->
                    <div class="flex items-center gap-1 shrink-0">
                        <!-- Toggle Active -->
                        <button wire:click="toggleGoalActive({{ $goal->id }})" 
                                class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700/70 rounded-lg transition-colors" 
                                title="{{ $goal->is_active ? 'غیرفعال کردن هدف' : 'فعال کردن هدف' }}">
                            <span class="w-2.5 h-2.5 rounded-full inline-block {{ $goal->is_active ? 'bg-emerald-500 ring-2 ring-emerald-500/20' : 'bg-gray-400' }}"></span>
                        </button>
                        <!-- Edit -->
                        <button wire:click="editGoal({{ $goal->id }})" 
                                class="p-1.5 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg transition-colors" 
                                title="ویرایش هدف">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <!-- Delete -->
                        <button onclick="confirm('آیا از حذف این هدف عملکردی اطمینان دارید؟') || event.stopImmediatePropagation()" 
                                wire:click="deleteGoal({{ $goal->id }})" 
                                class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg transition-colors" 
                                title="حذف هدف">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- نوار پیشرفت گرافیکی زنده -->
                <div class="mt-4 space-y-2 pr-1">
                    <div class="flex items-center justify-between text-xs font-sans">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">
                            ثبت‌شده: <strong class="text-gray-900 dark:text-white font-black">{{ $goal->current }}</strong> از 
                            <span class="font-bold">{{ $goal->target_value }} {{ $metricUnit }}</span>
                        </span>
                        <span class="font-black {{ $isAchieved ? 'text-emerald-600 dark:text-emerald-400' : 'text-indigo-600 dark:text-indigo-400' }}">
                            {{ $goal->percent }}٪
                        </span>
                    </div>

                    <!-- Track & Fill -->
                    <div class="w-full bg-gray-100 dark:bg-gray-700/60 rounded-full h-2.5 overflow-hidden p-0.5">
                        <div class="h-full rounded-full transition-all duration-700 {{ $isAchieved ? 'bg-gradient-to-r from-emerald-500 to-teal-400 shadow-xs shadow-emerald-500/30' : 'bg-gradient-to-r from-indigo-600 to-indigo-500 dark:from-indigo-500 dark:to-indigo-400' }}"
                             style="width: {{ min(100, $goal->percent) }}%"></div>
                    </div>
                </div>

                <!-- تاریخ اعتبار -->
                @if($goal->active_from || $goal->active_until)
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-[10px] text-gray-400 dark:text-gray-500 pr-1">
                        <span>
                            اعتبار: 
                            {{ $goal->active_from ? \Morilog\Jalali\Jalalian::fromDateTime($goal->active_from)->format('Y/m/d') : 'نامحدود' }} 
                            تا 
                            {{ $goal->active_until ? \Morilog\Jalali\Jalalian::fromDateTime($goal->active_until)->format('Y/m/d') : 'نامحدود' }}
                        </span>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full py-12 px-4 text-center bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-200/70 dark:border-gray-700/60">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                </div>
                <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 mb-1">هدفی با فیلترهای انتخابی یافت نشد</h4>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-4">می‌توانید فیلترها را تغییر داده یا هدف عملکردی جدیدی اضافه کنید.</p>
                <button wire:click="openCreateModal" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors">
                    ＋ تعریف هدف جدید
                </button>
            </div>
        @endforelse
    </div>

    <!-- ==========================================
         4. CREATE / EDIT GOAL MODAL
         ========================================== -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal') }"
             x-init="$watch('show', value => { document.body.style.overflow = value ? 'hidden' : '' })">
            <div x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm transition-opacity" x-on:click="show = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white inline-flex items-center gap-2" id="modal-title">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                    </svg>
                                    <span>{{ $editingGoalId ? 'ویرایش هدف عملکردی' : 'تعریف هدف عملکردی جدید' }}</span>
                                </h3>
                                <button x-on:click="show = false" type="button" class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-500/20 p-2 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- الگوهای سریع (Presets) -->
                            @if(!$editingGoalId)
                                <div class="mb-4 bg-indigo-50/50 dark:bg-indigo-950/20 p-3 rounded-xl border border-indigo-100/70 dark:border-indigo-900/40">
                                    <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 block mb-2">انتخاب سریع الگوهای استاندارد:</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" wire:click="setPreset('daily_calls', 15, 'daily')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200/80 dark:border-gray-700 text-[11px] hover:border-indigo-500 transition-colors font-sans">
                                            ۱۵ تماس در روز
                                        </button>
                                        <button type="button" wire:click="setPreset('daily_answered', 10, 'daily')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200/80 dark:border-gray-700 text-[11px] hover:border-indigo-500 transition-colors font-sans">
                                            ۱۰ تماس موفق در روز
                                        </button>
                                        <button type="button" wire:click="setPreset('weekly_followups', 8, 'weekly')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200/80 dark:border-gray-700 text-[11px] hover:border-indigo-500 transition-colors font-sans">
                                            ۸ پیگیری در هفته
                                        </button>
                                        <button type="button" wire:click="setPreset('monthly_clients', 5, 'monthly')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200/80 dark:border-gray-700 text-[11px] hover:border-indigo-500 transition-colors font-sans">
                                            ۵ مشتری جدید ماهانه
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <form wire:submit.prevent="saveGoal" class="space-y-4">
                                <!-- نوع هدف -->
                                <div>
                                    <label class="{{ $labelClass }}">نوع شاخص هدف <span class="text-rose-500">*</span></label>
                                    <select wire:model="goal_type" class="{{ $inputClass }}">
                                        @foreach($goalTypes as $type => $label)
                                            <option value="{{ $type }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('goal_type') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- مقدار هدف و دوره -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="{{ $labelClass }}">مقدار هدف <span class="text-rose-500">*</span></label>
                                        <input type="number" wire:model="target_value" class="{{ $inputClass }}" min="1">
                                        @error('target_value') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">دوره هدف <span class="text-rose-500">*</span></label>
                                        <select wire:model="period" class="{{ $inputClass }}">
                                            <option value="daily">روزانه</option>
                                            <option value="weekly">هفتگی</option>
                                            <option value="monthly">ماهانه</option>
                                        </select>
                                        @error('period') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- بازه تاریخ شمسی -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="{{ $labelClass }}">فعال از تاریخ (شمسی)</label>
                                        <input type="text" 
                                               data-jdp 
                                               wire:model="active_from_jalali" 
                                               class="{{ $inputClass }}" 
                                               placeholder="۱۴۰۳/۰۱/۰۱">
                                        @error('active_from') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">فعال تا تاریخ (شمسی)</label>
                                        <input type="text" 
                                               data-jdp 
                                               wire:model="active_until_jalali" 
                                               class="{{ $inputClass }}" 
                                               placeholder="اختیاری">
                                        @error('active_until') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- توضیح / یادداشت -->
                                <div>
                                    <label class="{{ $labelClass }}">توضیح یا یادداشت هدف</label>
                                    <input type="text" wire:model="note" class="{{ $inputClass }}" placeholder="مثال: هدف افزایش تماس به مناسبت کمپین پاییزه">
                                    @error('note') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- دکمه‌ها -->
                                <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
                                    <button type="button" x-on:click="show = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-colors">
                                        انصراف
                                    </button>
                                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors active:scale-95">
                                        {{ $editingGoalId ? 'ویرایش هدف' : 'ثبت هدف' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
