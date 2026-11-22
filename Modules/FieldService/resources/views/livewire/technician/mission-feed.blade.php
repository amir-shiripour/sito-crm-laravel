<div class="space-y-4 font-sans text-xs select-none" wire:poll.3s="heartbeat">

    {{-- ========================================================================= --}}
    {{-- 0. UNAUTHENTICATED NOTICE (WHEN NO TECHNICIAN IS LOGGED IN)               --}}
    {{-- ========================================================================= --}}
    @if(!$tech)
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 text-center space-y-2.5 shadow-xs">
        <div class="w-10 h-10 mx-auto rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <div>
            <h3 class="text-xs font-bold text-slate-800 dark:text-white">ورود به کنسول تکنسین</h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 max-w-xs mx-auto">
                جهت دسترسی به رادار ماموریت‌ها، لطفاً وارد حساب خود شوید.
            </p>
        </div>
        <div class="pt-1">
            <button type="button" @click="$dispatch('open-technician-auth')"
                    class="w-full max-w-xs mx-auto py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-xs transition active:scale-95 flex items-center justify-center gap-1.5">
                <span>ورود یا ثبت‌نام تکنسین</span>
                <svg class="w-3.5 h-3.5 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. PENDING APPROVAL NOTICE                                                --}}
    {{-- ========================================================================= --}}
    @if($tech && $tech->status->value === 'pending_approval')
    <div class="p-4 rounded-2xl bg-amber-50/80 dark:bg-slate-900/95 border border-amber-300 dark:border-amber-500/35 text-center space-y-2.5 shadow-xs">
        <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-amber-200/60 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 text-xs font-bold border border-amber-300 dark:border-amber-500/30">
            <span>در انتظار تایید مدارک و صلاحیت فنی</span>
        </div>
        <p class="text-xs text-slate-600 dark:text-slate-300 max-w-sm mx-auto leading-relaxed">
            پرونده شما در حال بررسی ناظران ناوگان است؛ پس از تایید، رادار به صورت خودکار فعال می‌شود.
        </p>
        <div class="pt-1 text-[11px] text-slate-500 dark:text-slate-400 border-t border-amber-200 dark:border-slate-800 flex items-center justify-center gap-2">
            <span>شماره پرونده:</span>
            <span class="font-bold text-slate-800 dark:text-white font-sans" dir="ltr">{{ $tech->mobile }}</span>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. OFFLINE NOTICE (WHEN TECHNICIAN IS NOT READY FOR MISSIONS)             --}}
    {{-- ========================================================================= --}}
    @elseif($tech && !$tech->is_online && !$activeMission)
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 text-center space-y-2.5 shadow-xs">
        <div class="flex items-center justify-center gap-2">
            <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></span>
            <h3 class="text-xs font-bold text-slate-800 dark:text-white">وضعیت شما: استراحت (آفلاین)</h3>
        </div>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
            برای دریافت و مشاهده سفارش‌های جدید، وضعیت خود را آنلاین کنید.
        </p>
        <div class="pt-0.5 flex justify-center">
            @livewire('fieldservice::technician-duty-toggle', ['technicianId' => $tech->id])
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 2. ACTIVE MISSION CARD (IF ASSIGNED TO AN ORDER)                          --}}
    {{-- ========================================================================= --}}
    @if($tech && $activeMission)
    <div class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-slate-900 border border-indigo-200 dark:border-indigo-500/40 shadow-md dark:shadow-2xl space-y-4">
        
        {{-- Header & Stepped Lifecycle Bar --}}
        <div class="space-y-3 pb-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span class="text-xs font-bold text-slate-800 dark:text-white">ماموریت فعال اختصاص‌یافته</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $activeMission->status->badgeClass() }}">
                    {{ $activeMission->status->label() }}
                </span>
            </div>

            {{-- 5-Step Visual Stepper --}}
            <div class="grid grid-cols-5 gap-1.5 pt-1 text-center font-sans">
                @php
                    $statusSteps = [
                        'accepted'        => 1,
                        'en_route'        => 2,
                        'arrived'         => 3,
                        'in_progress'     => 4,
                        'invoice_pending' => 4,
                        'completed'       => 5,
                    ];
                    $currentStepNum = $statusSteps[$activeMission->status->value] ?? 1;
                @endphp
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 1 ? 'bg-indigo-600 dark:bg-indigo-500' : 'bg-slate-200 dark:bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 1 ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-400 dark:text-slate-600' }}">پذیرش</span>
                </div>
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 2 ? 'bg-indigo-600 dark:bg-indigo-500' : 'bg-slate-200 dark:bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 2 ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-400 dark:text-slate-600' }}">در مسیر</span>
                </div>
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 3 ? 'bg-indigo-600 dark:bg-indigo-500' : 'bg-slate-200 dark:bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 3 ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-400 dark:text-slate-600' }}">حضور</span>
                </div>
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 4 ? 'bg-indigo-600 dark:bg-indigo-500' : 'bg-slate-200 dark:bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 4 ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-400 dark:text-slate-600' }}">فاکتور</span>
                </div>
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 5 ? 'bg-emerald-600 dark:bg-emerald-500' : 'bg-slate-200 dark:bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 5 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-600' }}">تسویه</span>
                </div>
            </div>
        </div>

        {{-- Order & Appliance Details --}}
        <div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white">
                            {{ $activeMission->category?->name ?: ($activeMission->appliance_brand_model ?: 'سرویس فنی') }}
                        </div>
                        <div class="text-[10px] text-indigo-600 dark:text-indigo-300 font-bold mt-0.5">
                            سفارش #{{ $activeMission->order_number }}
                        </div>
                    </div>
                </div>

                @if($activeMission->priority === 'emergency')
                <span class="px-2 py-1 rounded-xl text-[10px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-500/30 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    <span>{{ ($platformName ?? '') . ' پلاس (فوری)' }}</span>
                </span>
                @endif
            </div>

            <p class="text-[11px] text-slate-700 dark:text-slate-300 mt-2 bg-slate-50 dark:bg-slate-900/60 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800/80 leading-relaxed">
                <span class="text-slate-500 dark:text-slate-400 font-bold">شرح مشکل:</span> {{ $activeMission->problem_description ?: 'بدون توضیحات تکمیلی' }}
            </p>
        </div>

        {{-- Customer & Address Card --}}
        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </span>
                    <div>
                        <div class="text-xs font-bold text-slate-800 dark:text-white">{{ $activeMission->customer_name }}</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400">کارفرمای خدمت</div>
                    </div>
                </div>

                {{-- Quick Call Button --}}
                <a href="tel:{{ $activeMission->customer_phone }}"
                   class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-600/20 hover:bg-emerald-100 dark:hover:bg-emerald-600/30 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/30 font-bold text-xs flex items-center gap-1.5 transition active:scale-95">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <span>تماس مستقیم</span>
                </a>
            </div>

            <div class="pt-1.5 border-t border-slate-200 dark:border-slate-800/60 flex items-start gap-1.5 text-[11px] text-slate-600 dark:text-slate-300">
                <svg class="w-3.5 h-3.5 text-rose-500 dark:text-rose-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <span class="leading-relaxed font-sans">{{ $activeMission->address ?: "مازندران، {$activeMission->city}" }}</span>
            </div>
        </div>

        {{-- Live Route ETA & Distance Card --}}
        @if(isset($activeMission->distance_km) || isset($activeMission->eta_minutes))
        <div class="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/25 flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300 font-bold">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                <span>زمان تقریبی رسیدن:</span>
                <span class="text-slate-900 dark:text-white font-bold">{{ $activeMission->eta_minutes ? "حدود {$activeMission->eta_minutes} دقیقه" : 'در مسیر' }}</span>
            </div>
            @if($activeMission->distance_km)
            <div class="px-2.5 py-1 rounded-xl bg-white dark:bg-slate-900/80 border border-indigo-200 dark:border-indigo-500/20 text-slate-700 dark:text-slate-200 font-bold text-[11px] shrink-0">
                <span>فاصله: </span>
                <span class="text-indigo-600 dark:text-indigo-300 font-bold">{{ $activeMission->distance_km }}</span>
                <span>کیلومتر</span>
            </div>
            @endif
        </div>
        @endif

        {{-- Lifecycle Action Buttons --}}
        <div class="space-y-2.5">
            @if($activeMission->status->value === 'accepted')
            <button type="button" wire:click="startRoute({{ $activeMission->id }})" 
                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 active:scale-98 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-md transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                <span>حرکت به سمت مشتری (آغاز اعزام)</span>
                <svg class="w-4 h-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            @elseif($activeMission->status->value === 'en_route')
            <button type="button" wire:click="markArrived({{ $activeMission->id }})" 
                    class="w-full py-3.5 bg-teal-600 hover:bg-teal-500 active:scale-98 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-md transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <span>رسیدم به محل مشتری (اعلام حضور)</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
            @elseif(in_array($activeMission->status->value, ['arrived', 'in_progress', 'invoice_pending'], true))
            <button type="button" wire:click="completeOrder({{ $activeMission->id }})" 
                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-98 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-md transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>اتمام موفقیت‌آمیز کار و تسویه نهایی</span>
            </button>
            @endif

            {{-- Secondary Actions: External Navigation & Backup Tech --}}
            <div class="grid grid-cols-2 gap-2">
                <button type="button" wire:click="openBackupModal({{ $activeMission->id }})" 
                        class="py-2.5 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-500/30 text-xs font-bold transition flex items-center justify-center gap-2 active:scale-95">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>درخواست نصاب کمکی</span>
                </button>

                @php
                    $lat = $activeMission->lat ?: 36.5444;
                    $lng = $activeMission->lng ?: 52.6780;
                @endphp
                <div x-data="{ navOpen: false }" class="relative">
                    <button type="button" @click="navOpen = !navOpen"
                            class="w-full py-2.5 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 text-xs font-bold transition flex items-center justify-center gap-2 active:scale-95">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/></svg>
                        <span>مسیریابی هوشمند</span>
                        <svg class="w-3 h-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div x-show="navOpen" @click.outside="navOpen = false" x-cloak
                         class="absolute bottom-full mb-1.5 inset-x-0 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl p-1.5 space-y-1 z-30 font-sans">
                        <a href="https://neshan.org/maps/route?destination={{ $lat }},{{ $lng }}" target="_blank"
                           class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-xs text-slate-700 dark:text-slate-200 transition">
                            <span class="font-bold">مسیریابی در نشان</span>
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                        </a>
                        <a href="https://balad.ir/location?latitude={{ $lat }}&longitude={{ $lng }}" target="_blank"
                           class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-xs text-slate-700 dark:text-slate-200 transition">
                            <span class="font-bold">مسیریابی در بلد</span>
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </a>
                        <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" target="_blank"
                           class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-xs text-slate-700 dark:text-slate-200 transition">
                            <span class="font-bold">گوگل مپ</span>
                            <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" x2="22" y1="12" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1 4-10z"></path></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Technician Cancellation / Unassign Option --}}
            @if(in_array($activeMission->status->value, ['accepted', 'en_route'], true))
            <button type="button" wire:click="openCancelModal" 
                    class="w-full py-2.5 px-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 hover:bg-rose-100 dark:hover:bg-rose-500/20 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 text-xs font-bold transition flex items-center justify-center gap-1.5 active:scale-95">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"></line><line x1="6" x2="18" y1="6" y2="18"></line></svg>
                <span>انصراف یا بازگرداندن ماموریت به رادار</span>
            </button>
            @endif
        </div>

        {{-- Embedded Invoice Builder Drawer --}}
        <div class="pt-3 border-t border-slate-200 dark:border-slate-800" x-data="{ showInvoice: false }">
            <button type="button" @click="showInvoice = !showInvoice"
                    class="w-full py-2.5 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center justify-between transition">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6"/><path d="M16 12h-6"/><path d="M14 16h-4"/></svg>
                    <span>صدور فاکتور و مشاهده اقلام اجرت</span>
                </span>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold" x-text="showInvoice ? 'بستن فاکتور ▲' : 'مشاهده و ثبت فاکتور ▼'"></span>
            </button>

            <div x-show="showInvoice" x-cloak class="mt-3">
                @livewire('fieldservice::invoice-builder', ['orderId' => $activeMission->id], key('inv-'.$activeMission->id))
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 3. INCOMING DISPATCH FEED (AVAILABLE JOBS WHEN NO ACTIVE MISSION)         --}}
    {{-- ========================================================================= --}}
    @if($tech && !$activeMission && $tech->is_online)
    <div class="space-y-3">
        <div class="space-y-2.5">
            @forelse($availableMissions as $m)
            <div id="mission-card-{{ $m->id }}"
                 wire:key="mission-card-item-{{ $m->id }}"
                 wire:click="focusMission({{ $m->id }})"
                 class="p-4 rounded-3xl cursor-pointer transition-all duration-200 shadow-xs space-y-3 border {{ $previewOrderId === $m->id ? 'bg-indigo-50/70 dark:bg-slate-800 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-white dark:bg-slate-900/90 border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-500/60' }}">
                
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/25 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                                </svg>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-white text-xs sm:text-sm truncate">
                                {{ $m->category?->name ?: ($m->appliance_brand_model ?: 'سرویس فنی') }}
                            </span>
                            @if($m->priority === 'emergency' || $m->is_emergency)
                            <span class="px-2 py-0.5 rounded-full text-[9px] bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 font-bold shrink-0">
                                فوری
                            </span>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 truncate">
                            <svg class="w-3.5 h-3.5 text-rose-500 dark:text-rose-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span>{{ $m->city }}، {{ $m->district ?: 'مرکز شهر' }}</span>
                            @if($m->distance_km !== null)
                            <span class="text-slate-400">•</span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">فاصله: {{ $m->distance_km }} کیلومتر</span>
                            @endif
                        </div>
                    </div>

                    {{-- Order ID Badge --}}
                    <div class="px-2.5 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold shrink-0">
                        #{{ $m->order_number }}
                    </div>
                </div>

                @if($m->problem_description)
                <p class="text-[11px] text-slate-700 dark:text-slate-300 leading-relaxed truncate bg-slate-50 dark:bg-slate-950/60 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800">
                    <span class="text-slate-500 dark:text-slate-400 font-bold">شرح:</span> {{ $m->problem_description }}
                </p>
                @endif

                {{-- Action Row: Details & Location Focus, Dismiss, and Accept --}}
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between gap-2" @click.stop>
                    <div class="flex items-center gap-1.5">
                        <button type="button" wire:click="focusMission({{ $m->id }})"
                                class="py-2 px-3 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-100 dark:hover:bg-indigo-600/35 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 text-[11px] font-bold transition flex items-center gap-1.5 active:scale-95">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/></svg>
                            <span>موقعیت و جزئیات</span>
                        </button>
                        <button type="button" wire:click="dismissOrder({{ $m->id }})"
                                class="py-2 px-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/60 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-300 border border-slate-200 dark:border-slate-700/60 text-[11px] font-bold transition">
                            رد کردن
                        </button>
                    </div>

                    <button type="button" wire:click="acceptMission({{ $m->id }})" 
                            class="py-2 px-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>قبول ماموریت</span>
                    </button>
                </div>
            </div>
            @empty
            <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-center space-y-2">
                <div class="w-10 h-10 mx-auto rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center animate-pulse">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/></svg>
                </div>
                <div class="text-xs font-bold text-slate-800 dark:text-white">در حال رصد سفارش‌های جدید در محدوده شما...</div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
                    سفارش‌ها بر اساس موقعیت فعلی و شعاع کاری شما نمایش داده می‌شوند.
                </p>
            </div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 4. ORDER DETAILS PREVIEW MODAL (بررسی کامل سفارش قبل از قبول)            --}}
    {{-- ========================================================================= --}}
    @if($previewOrder)
    <div class="fixed inset-0 z-50 bg-slate-950/70 dark:bg-slate-950/85 backdrop-blur-xs flex items-center justify-center p-4 font-sans select-none">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/25 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 dark:text-white">بررسی درخواست #{{ $previewOrder->order_number }}</h3>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $previewOrder->category?->name }} • {{ $previewOrder->city }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($previewOrder->lat && $previewOrder->lng)
                    <button type="button" @click="$dispatch('focus-mission-on-map', { orderId: {{ $previewOrder->id }}, lat: {{ (float)$previewOrder->lat }}, lng: {{ (float)$previewOrder->lng }}, title: '{{ $previewOrder->category?->name ?: 'سفارش' }}' })"
                            class="py-1 px-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-100 dark:hover:bg-indigo-600/35 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 text-[10px] font-bold flex items-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span>زوم روی نقشه</span>
                    </button>
                    @endif
                    <button type="button" wire:click="closeOrderDetails" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"></line><line x1="6" x2="18" y1="6" y2="18"></line></svg>
                    </button>
                </div>
            </div>

            <div class="space-y-2.5 text-[11px]">
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1.5">
                    <div class="text-slate-500 dark:text-slate-400 font-bold">شرح عیب یا تقاضای کارفرما:</div>
                    <div class="text-slate-800 dark:text-white leading-relaxed">{{ $previewOrder->problem_description ?: 'توضیحات ثبت نشده است.' }}</div>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-start gap-2">
                    <svg class="w-4 h-4 text-rose-500 dark:text-rose-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <div class="min-w-0 flex-1">
                        <div class="text-slate-500 dark:text-slate-400 font-bold">نشانی تقریبی خدمت:</div>
                        <div class="text-slate-800 dark:text-white mt-0.5">{{ $previewOrder->address ?: "استان مازندران، {$previewOrder->city}" }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    @if($previewOrder->preferred_time)
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">زمان ترجیحی:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-300">{{ $previewOrder->preferred_time }}</span>
                    </div>
                    @endif

                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">ایاب و ذهاب:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">۶۰,۰۰۰ تومان</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                <button type="button" wire:click="dismissOrder({{ $previewOrder->id }})" class="w-1/3 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-300 font-bold text-xs rounded-xl transition">
                    رد سفارش
                </button>
                <button type="button" wire:click="acceptMission({{ $previewOrder->id }})" class="w-2/3 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>قبول و حرکت به سمت آدرس</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 5. ASSISTANT TECHNICIAN MODAL (درخواست نصاب کمکی)                         --}}
    {{-- ========================================================================= --}}
    @if($showBackupModal)
    <div class="fixed inset-0 z-50 bg-slate-950/70 dark:bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-sans select-none">
        <div class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="text-xs font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    <span>درخواست نصاب کمکی و پشتیبان</span>
                </h3>
                <button type="button" @click="$wire.showBackupModal = false" class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"></line><line x1="6" x2="18" y1="6" y2="18"></line></svg>
                </button>
            </div>

            <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                در صورت نیاز به نیروی کمکی جهت کارهای دونفره، حمل وزن سنگین یا تجهیزات خاص، درخواست شما برای نزدیک‌ترین همکار ارسال می‌شود.
            </p>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">علت نیاز به همکار</label>
                <select wire:model="backupReason" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-white font-sans focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <option value="heavy_lifting">جابجایی دستگاه و وزن سنگین</option>
                    <option value="height_work">نصب در ارتفاع و نردبان بلند</option>
                    <option value="specialized_tools">نیاز به ابزار خاص (پمپ وکیوم، پرس)</option>
                    <option value="double_manpower">نیاز به نیروی کار همزمان</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">توضیحات تکمیلی برای همکار</label>
                <textarea wire:model="backupNotes" rows="2" placeholder="توضیح موقعیت یا نوع ابزار مورد نیاز..." class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 font-sans focus:outline-none focus:ring-1 focus:ring-amber-500"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="button" @click="$wire.showBackupModal = false" class="w-1/3 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                    انصراف
                </button>
                <button type="button" wire:click="submitBackupRequest" class="w-2/3 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md transition">
                    ارسال فراخوان کمکی
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 6. TECHNICIAN CANCELLATION / REDISPATCH MODAL                            --}}
    {{-- ========================================================================= --}}
    @if($showCancelModal)
    <div class="fixed inset-0 z-50 bg-slate-950/70 dark:bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-sans select-none">
        <div class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="text-xs font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"></line><line x1="6" x2="18" y1="6" y2="18"></line></svg>
                    </span>
                    <span>انصراف یا لغو ماموریت</span>
                </h3>
                <button type="button" wire:click="closeCancelModal"
                        class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs transition">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"></line><line x1="6" x2="18" y1="6" y2="18"></line></svg>
                </button>
            </div>

            {{-- Action Mode Selector --}}
            <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">نحوه برخورد با سفارش</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="$set('cancelAction', 'redispatch')"
                            class="p-2.5 rounded-2xl border text-right transition flex flex-col justify-between {{ $cancelAction === 'redispatch' ? 'bg-indigo-50 dark:bg-indigo-500/15 border-indigo-400 dark:border-indigo-500/50 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-400/30' : 'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700/60 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <div class="font-bold text-[11px] flex items-center gap-1.5 text-slate-900 dark:text-white">
                            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/></svg>
                            <span>ارجاع به رادار</span>
                        </div>
                        <div class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">واگذاری به سایر همکاران (توصیه‌شده)</div>
                    </button>

                    <button type="button" wire:click="$set('cancelAction', 'cancel')"
                            class="p-2.5 rounded-2xl border text-right transition flex flex-col justify-between {{ $cancelAction === 'cancel' ? 'bg-rose-50 dark:bg-rose-500/15 border-rose-300 dark:border-rose-500/50 text-rose-700 dark:text-rose-300 ring-1 ring-rose-400/30' : 'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700/60 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <div class="font-bold text-[11px] flex items-center gap-1.5 text-slate-900 dark:text-white">
                            <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>
                            <span>لغو کامل سفارش</span>
                        </div>
                        <div class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">مشتری پاسخگو نیست یا منصرف شد</div>
                    </button>
                </div>
            </div>

            {{-- Reasons Radio Group --}}
            <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">علت انصراف / لغو</label>
                <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                    @foreach($technicianCancellationReasons as $reasonKey => $reasonLabel)
                    <label class="flex items-center gap-2.5 p-2 rounded-xl border transition cursor-pointer select-none text-[11px] {{ $cancelReason === $reasonKey ? 'bg-indigo-50 dark:bg-indigo-500/15 border-indigo-400 dark:border-indigo-500/40 text-indigo-800 dark:text-indigo-200 font-bold' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/50 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <input type="radio" wire:model.live="cancelReason" value="{{ $reasonKey }}" class="sr-only">
                        <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0 {{ $cancelReason === $reasonKey ? 'border-indigo-600 dark:border-indigo-400 bg-indigo-600 dark:bg-indigo-500' : 'border-slate-400 dark:border-slate-600' }}">
                            @if($cancelReason === $reasonKey)
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            @endif
                        </span>
                        <span class="leading-tight">{{ $reasonLabel }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">توضیحات تکمیلی</label>
                <textarea wire:model="cancelNote" rows="2" placeholder="توضیح بیشتر در صورت نیاز..."
                          class="w-full p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 font-sans focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1 border-t border-slate-200 dark:border-slate-800">
                <button type="button" wire:click="closeCancelModal"
                        class="w-1/3 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                    بازگشت
                </button>
                <button type="button" wire:click="confirmTechnicianCancel"
                        class="w-2/3 py-2.5 {{ $cancelAction === 'redispatch' ? 'bg-indigo-600 hover:bg-indigo-500' : 'bg-rose-600 hover:bg-rose-500' }} active:scale-95 text-white font-bold text-xs rounded-xl shadow-md transition">
                    {{ $cancelAction === 'redispatch' ? 'تایید و ارجاع به رادار' : 'تایید و لغو نهایی' }}
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
