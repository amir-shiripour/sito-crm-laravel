<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $platformName ?? 'سامانه هوشمند خدمات فنی' }} | کنسول اعزام و سفیران</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Leaflet CSS & JS (Local Core Assets) --}}
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Custom Radar Animation Rings */
        @keyframes tech-radar-pulse {
            0% {
                transform: scale(0.2);
                opacity: 0.9;
            }
            70% {
                opacity: 0.25;
            }
            100% {
                transform: scale(2.6);
                opacity: 0;
            }
        }
        .tech-radar-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid #10b981;
            background: rgba(16, 185, 129, 0.15);
            animation: tech-radar-pulse 2.4s cubic-bezier(0.2, 0.8, 0.4, 1) infinite;
            pointer-events: none;
        }

        /* Leaflet Controls & Hardware Acceleration */
        .leaflet-control-zoom {
            display: none !important;
        }
        .leaflet-control-attribution {
            background: rgba(15, 23, 42, 0.8) !important;
            color: #94a3b8 !important;
            font-size: 9px !important;
            border-radius: 6px !important;
            margin: 4px !important;
        }

        /* Instant Touch & GPU Hardware Acceleration */
        #technicianMap {
            touch-action: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
            -webkit-tap-highlight-color: transparent;
            will-change: transform;
            transform: translateZ(0);
        }
        .leaflet-container {
            touch-action: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
            cursor: grab;
            background: #090d16 !important;
        }
        .leaflet-container:active {
            cursor: grabbing;
        }
        .leaflet-map-pane {
            will-change: transform;
            transform: translateZ(0);
        }
    </style>
</head>
<body class="h-dvh w-screen overflow-hidden bg-slate-950 text-slate-100 font-sans selection:bg-indigo-600 selection:text-white select-none relative"
      x-data="technicianAppHandler(@js($mapConfig), @js($technician), @js($activeMission), @js($availableMissions), @js($todayStats))"
      x-init="initMap()">

    {{-- ========================================================================= --}}
    {{-- 1. FULL-VIEWPORT MAP LAYER                                                --}}
    {{-- ========================================================================= --}}
    <div id="technicianMap" class="w-full h-full absolute inset-0 z-0 bg-slate-950"></div>

    {{-- ========================================================================= --}}
    {{-- 2. FLOATING TOP HEADER: DRIVER/TECHNICIAN COCKPIT (RTL ORDER)              --}}
    {{-- ========================================================================= --}}
    <header class="fixed top-3 inset-x-3 sm:top-5 sm:inset-x-6 z-20 pointer-events-none">
        <div class="max-w-xl mx-auto flex items-center justify-between gap-2">
            
            {{-- Right: Profile & Rating Pill (Opens Performance Modal) --}}
            @if($technician)
            <button type="button" @click="statsModalOpen = true"
                    class="pointer-events-auto px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-full bg-slate-900/95 backdrop-blur-md shadow-xl border border-slate-800 flex items-center gap-2 text-right transition-all active:scale-95 shrink-0 hover:border-slate-700">
                <div class="w-8 h-8 rounded-full bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="hidden sm:block min-w-0">
                    <div class="text-xs font-black text-white truncate flex items-center gap-1.5">
                        <span>{{ $technician->full_name }}</span>
                        @if($technician->status->value === 'pending_approval')
                        <span class="text-[9px] px-1.5 py-0.5 rounded font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            در انتظار تایید مدارک
                        </span>
                        @else
                        <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-amber-500/15 text-amber-300 border border-amber-500/25">
                            {{ $technician->tier?->label() ?: 'تکنسین برتر' }}
                        </span>
                        @endif
                    </div>
                    <div class="text-[10px] text-amber-400 font-bold flex items-center gap-1 mt-0.5">
                        <svg class="w-3 h-3 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <span>{{ $todayStats['rating'] }}</span>
                        <span class="text-slate-500">•</span>
                        <span class="text-slate-400">{{ $technician->completed_orders_count }} خدمت</span>
                    </div>
                </div>
            </button>
            @else
            <button type="button" @click="$dispatch('open-technician-auth')"
                    class="pointer-events-auto px-3.5 py-2 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-xl flex items-center gap-2 transition-all active:scale-95 shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>ورود به حساب تکنسین</span>
            </button>
            @endif

            {{-- Center: Duty Capsule Switch (Online / Offline) --}}
            @if($technician)
            <div class="pointer-events-auto bg-slate-900/95 backdrop-blur-md shadow-xl rounded-full p-1 border border-slate-800 flex items-center shrink-0">
                @livewire('fieldservice::technician-duty-toggle', ['technicianId' => $technician->id])
            </div>
            @endif

            {{-- Left: Today Earnings & Switcher Menu --}}
            <div class="pointer-events-auto flex items-center gap-1.5">
                @if($technician)
                {{-- Today's Income Pill --}}
                <button type="button" @click="statsModalOpen = true"
                        class="px-3 py-2 rounded-full bg-slate-900/95 backdrop-blur-md shadow-xl border border-slate-800 flex items-center gap-1.5 text-xs transition-all active:scale-95 hover:border-slate-700 shrink-0">
                    <span class="text-[10px] text-slate-400 font-bold hidden sm:inline">کارکرد امروز:</span>
                    <span class="font-black text-emerald-400 text-xs">{{ number_format($todayStats['net_earnings']) }}</span>
                    <span class="text-[9px] text-emerald-500 font-bold">تومان</span>
                </button>
                @endif

                {{-- Fast Portal Switcher --}}
                <div x-data="{ switcherOpen: false }" class="relative">
                    <button type="button" @click="switcherOpen = !switcherOpen"
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-900/95 backdrop-blur-md shadow-xl border border-slate-800 flex items-center justify-center text-slate-300 hover:text-white transition-all active:scale-95"
                            title="سایر پرتال‌ها و خروج">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </button>

                    <div x-show="switcherOpen" @click.outside="switcherOpen = false" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute left-0 top-full mt-2 w-52 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-1.5 space-y-1 z-30 font-sans">
                        <a href="{{ route('fieldservice.customer') }}"
                           class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-800 text-xs text-slate-200 transition">
                            <svg class="w-4 h-4 text-indigo-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span>پرتال مشتریان</span>
                        </a>
                        <a href="{{ route('user.fieldservice.dashboard') }}"
                           class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-800 text-xs text-slate-200 transition">
                            <svg class="w-4 h-4 text-indigo-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <span>پنل مدیریت CRM</span>
                        </a>
                        <a href="{{ route('fieldservice.b2b') }}"
                           class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-800 text-xs text-slate-200 transition">
                            <svg class="w-4 h-4 text-indigo-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path><path d="M16 10h.01"></path><path d="M16 14h.01"></path><path d="M8 10h.01"></path><path d="M8 14h.01"></path></svg>
                            <span>پرتال مدیران ساختمان</span>
                        </a>

                        @if($technician)
                        <div class="border-t border-slate-800 my-1 pt-1">
                            <a href="{{ route('fieldservice.technician.logout') }}"
                               class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-rose-500/20 text-xs text-rose-300 transition font-bold">
                                <svg class="w-4 h-4 text-rose-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>
                                <span>خروج از حساب تکنسین</span>
                            </a>
                        </div>
                        @else
                        <div class="border-t border-slate-800 my-1 pt-1">
                            <button type="button" @click="$dispatch('open-technician-auth'); switcherOpen = false;"
                                    class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-indigo-600 text-xs text-indigo-300 hover:text-white transition font-bold text-right">
                                <svg class="w-4 h-4 text-indigo-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 2-2 2m-1.5 1.5L10 13l-4 4-4-4 4-4 7.5-7.5"></path><path d="m15.5 7.5 3 3"></path></svg>
                                <span>ورود / ثبت‌نام تکنسین</span>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- 3. FLOATING GPS / RE-CENTER BUTTON (BOTTOM-RIGHT)                         --}}
    {{-- ========================================================================= --}}
    <div :class="sheetExpanded ? 'bottom-[330px] sm:bottom-[350px]' : 'bottom-28 sm:bottom-32'"
         class="fixed right-4 sm:right-6 z-20 transition-all duration-300 ease-out">
        <button type="button" @click="centerOnTechnician()"
                title="موقعیت فعلی من"
                class="w-12 h-12 rounded-full bg-slate-900/95 backdrop-blur-md shadow-xl border border-slate-800 flex items-center justify-center text-slate-200 hover:text-emerald-400 transition-all active:scale-95 hover:border-slate-700">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 2v3m0 14v3M2 12h3m14 0h3"></path>
            </svg>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. FLOATING BOTTOM SHEET: MISSION CONSOLE (SWIPEABLE & COLLAPSIBLE)       --}}
    {{-- ========================================================================= --}}
    <section class="fixed inset-x-0 bottom-0 z-30 lg:top-20 lg:bottom-6 lg:left-6 lg:right-auto lg:w-[480px] pointer-events-none transition-all duration-300 ease-out">
        <div class="pointer-events-auto bg-slate-950/95 backdrop-blur-2xl rounded-t-[32px] lg:rounded-3xl border border-slate-800 shadow-2xl flex flex-col transition-all duration-300 ease-out"
             :class="{
                 'h-auto max-h-[85vh] lg:h-auto lg:max-h-[85vh]': sheetExpanded,
                 'h-[82px] max-h-[82px] lg:h-[82px] lg:max-h-[82px]': !sheetExpanded
             }"
             @touchstart="handleTouchStart($event)"
             @touchmove="handleTouchMove($event)"
             @touchend="handleTouchEnd()">

            {{-- Peek Bar: Mobile Drag Handle & Summary Row --}}
            <div @click="toggleSheet()" class="px-4 py-2 cursor-pointer flex flex-col justify-between h-[82px] select-none border-b border-slate-900">
                {{-- Handle Bar --}}
                <div class="flex justify-center -mt-0.5">
                    <span class="w-12 h-1 rounded-full bg-slate-700 hover:bg-slate-600 transition-colors"></span>
                </div>

                {{-- Compact Peek Info (Reactive via Alpine & Livewire State Sync) --}}
                <div class="flex items-center justify-between gap-3 pt-1">
                    <div class="flex items-center gap-2.5 min-w-0">
                        {{-- 1. Active Assigned Mission Case --}}
                        <template x-if="hasActiveMission">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full bg-indigo-500 animate-pulse shrink-0"></span>
                                <div class="min-w-0">
                                    <div class="text-xs font-black text-white truncate" x-text="'ماموریت فعال: ' + activeMissionTitle"></div>
                                    <div class="text-[10px] text-indigo-300 truncate font-bold flex items-center gap-1.5">
                                        <span x-text="activeMissionStatus"></span>
                                        <span x-show="activeMissionEta" class="text-emerald-400 font-bold" x-text="'• ' + activeMissionEta"></span>
                                        <span x-show="activeMissionDist" class="text-slate-400" x-text="'(' + activeMissionDist + ' ک.م)'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- 2. No Active Mission, Technician Logged In --}}
                        <template x-if="!hasActiveMission && technician">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full shrink-0"
                                      :class="(technician && technician.is_online) ? 'bg-emerald-400 animate-ping' : 'bg-slate-600'"></span>
                                <div class="min-w-0">
                                    <div class="text-xs font-black text-white truncate"
                                         x-text="(technician && technician.is_online) ? 'رادار سفارش‌های مازندران فعال است' : 'شما در وضعیت آفلاین هستید'"></div>
                                    <div class="text-[10px] text-slate-400 truncate"
                                         x-text="(technician && technician.is_online) ? (availableMissionsCount + ' سفارش در انتظار بررسی') : 'جهت دریافت کار آنلاین شوید'"></div>
                                </div>
                            </div>
                        </template>

                        {{-- 3. Guest / Unauthenticated --}}
                        <template x-if="!hasActiveMission && !technician">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full bg-amber-400 animate-ping shrink-0"></span>
                                <div class="min-w-0">
                                    <div class="text-xs font-black text-white truncate">نیاز به ورود به حساب کاربری</div>
                                    <div class="text-[10px] text-amber-300 truncate font-bold">برای دریافت سرویس وارد شوید</div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <template x-if="hasActiveMission && activeMissionCustomerPhone">
                            <a :href="'tel:' + activeMissionCustomerPhone" @click.stop
                               class="w-9 h-9 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center text-xs shadow-md shadow-indigo-600/30 transition active:scale-95"
                               title="تماس با مشتری">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </a>
                        </template>
                        <button type="button" @click.stop="toggleSheet()"
                                class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1.5 active:scale-95 border border-slate-700">
                            <span x-text="sheetExpanded ? 'بستن' : 'مشاهده'"></span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="sheetExpanded ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Full Content Area: Livewire Mission Feed --}}
            <div :class="!sheetExpanded ? 'hidden' : 'flex flex-col flex-1 min-h-0'"
                 class="transition-all duration-200">
                <div class="p-4 sm:p-5 custom-scrollbar flex-1 overflow-y-auto max-h-[75vh]">
                    @livewire('fieldservice::mission-feed', ['technicianId' => $technician?->id])
                </div>

                {{-- Status Bar / Footnote --}}
                <div class="px-5 py-2.5 bg-slate-900/90 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400 shrink-0">
                    <div class="flex items-center gap-1.5 font-bold text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>پوشش هوشمند مازندران (بابل، ساری، آمل، قائمشهر)</span>
                    </div>
                    <div class="text-[10px] text-slate-500">
                        {{ $platformName ?? 'سامانه' }} فیلد سرویس
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 5. DAILY STATS & PERFORMANCE MODAL                                        --}}
    {{-- ========================================================================= --}}
    @if($technician)
    <div x-show="statsModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs font-sans select-none">
        <div @click.outside="statsModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 shadow-2xl">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-400 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                    </span>
                    <h3 class="text-sm font-black text-white">کارنامه و عملکرد امروز شما</h3>
                </div>
                <button type="button" @click="statsModalOpen = false" class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white text-xs">✕</button>
            </div>

            {{-- 4-Grid Metrics --}}
            <div class="grid grid-cols-2 gap-2 font-sans">
                <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-1">
                    <div class="text-[10px] text-slate-400 font-bold">درآمد خالص امروز:</div>
                    <div class="text-sm font-black text-emerald-400">{{ number_format($todayStats['net_earnings']) }} <span class="text-[10px]">تومان</span></div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-1">
                    <div class="text-[10px] text-slate-400 font-bold">خدمات انجام‌شده امروز:</div>
                    <div class="text-sm font-black text-white">{{ $todayStats['completed_count'] }} <span class="text-[10px] text-slate-500">سرویس</span></div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-1">
                    <div class="text-[10px] text-slate-400 font-bold">امتیاز رضایت مشتریان:</div>
                    <div class="text-sm font-black text-amber-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 fill-amber-400 text-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <span>{{ $todayStats['rating'] }}</span>
                        <span class="text-[10px] text-slate-500">از ۵</span>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-1">
                    <div class="text-[10px] text-slate-400 font-bold">نرخ پذیرش رادار:</div>
                    <div class="text-sm font-black text-indigo-400">{{ $todayStats['acceptance_rate'] }}%</div>
                </div>
            </div>

            {{-- Lifetime Summary --}}
            <div class="p-3 rounded-2xl bg-slate-800/50 border border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">مجموع خدمات موفق تا کنون:</span>
                <span class="font-black text-white">{{ $technician->completed_orders_count }} خدمت</span>
            </div>

            <div class="space-y-2 pt-1">
                <button type="button" @click="statsModalOpen = false"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs rounded-2xl transition">
                    بازگشت به نقشه
                </button>

                <a href="{{ route('fieldservice.technician.logout') }}"
                   class="w-full py-2.5 bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 font-bold text-xs rounded-2xl transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>
                    <span>خروج از حساب تکنسین</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 6. DEDICATED TECHNICIAN AUTH MODAL (OTP & PASSWORD)                       --}}
    {{-- ========================================================================= --}}
    @livewire('fieldservice::technician-auth-modal')

    {{-- ========================================================================= --}}
    {{-- 7. APPLICATION LOGIC & LEAFLET MAP HANDLER (UNPROXIED 60 FPS)             --}}
    {{-- ========================================================================= --}}
    <script>
        // Global raw Leaflet instances kept outside Alpine Proxy to prevent reactivity overhead
        let rawTechMap = null;
        let rawTechMarker = null;
        let rawCustomerMarker = null;
        let rawRouteGlowPolyline = null;
        let rawRoutePolyline = null;
        let rawPreviewRouteLine = null;
        let rawFocusRingLayer = null;
        let rawMissionsLayer = null;
        let rawMissionMarkersMap = {};

        // Intercept 419 Page Expired globally in Livewire to prevent the browser confirm dialog
        document.addEventListener('livewire:init', () => {
            if (window.Livewire && window.Livewire.hook) {
                window.Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            preventDefault();
                            window.location.reload();
                        }
                    });
                });
            }
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('technicianAppHandler', (config, technician, activeMission, availableMissions, todayStats) => ({
                config: config || {},
                technician: technician || null,
                activeMission: activeMission || null,
                availableMissions: availableMissions || [],
                todayStats: todayStats || {},
                sheetExpanded: true,
                statsModalOpen: false,

                // Reactive Active Mission State (Updated dynamically via Livewire events)
                hasActiveMission: !!activeMission,
                activeMissionTitle: activeMission ? (activeMission.category?.name || activeMission.customer_name || '') : '',
                activeMissionStatus: activeMission ? (activeMission.status?.value || activeMission.status || '') : '',
                activeMissionCity: activeMission ? (activeMission.city || '') : '',
                activeMissionDist: null,
                activeMissionEta: '',
                activeMissionCustomerPhone: '',
                activeMissionCustomerName: '',
                availableMissionsCount: availableMissions ? availableMissions.length : 0,

                // Touch swipe coordinates for peek sheet
                touchStartY: 0,
                touchEndY: 0,
                touchStartX: 0,
                touchEndX: 0,

                toggleSheet() {
                    this.sheetExpanded = !this.sheetExpanded;
                },

                handleTouchStart(e) {
                    if (e.touches && e.touches.length > 0) {
                        this.touchStartY = e.touches[0].clientY;
                        this.touchStartX = e.touches[0].clientX;
                    }
                },

                handleTouchMove(e) {
                    if (e.touches && e.touches.length > 0) {
                        this.touchEndY = e.touches[0].clientY;
                        this.touchEndX = e.touches[0].clientX;
                    }
                },

                handleTouchEnd() {
                    if (!this.touchStartY || !this.touchEndY) return;
                    const deltaY = this.touchStartY - this.touchEndY;
                    const deltaX = Math.abs((this.touchStartX || 0) - (this.touchEndX || 0));
                    
                    if (Math.abs(deltaY) > 35 && Math.abs(deltaY) > deltaX) {
                        if (deltaY > 35) {
                            this.sheetExpanded = true;
                        } else if (deltaY < -35) {
                            this.sheetExpanded = false;
                        }
                    }
                    this.touchStartY = 0;
                    this.touchEndY = 0;
                },

                initMap() {
                    const techLat = (this.technician && this.technician.lat) ? this.technician.lat : 36.5444;
                    const techLng = (this.technician && this.technician.lng) ? this.technician.lng : 52.6780;
                    const zoom = 15;

                    const sw = this.config.bounds?.southWest || [36.35, 52.45];
                    const ne = this.config.bounds?.northEast || [36.75, 52.95];
                    const coveredBounds = L.latLngBounds(
                        L.latLng(sw[0], sw[1]),
                        L.latLng(ne[0], ne[1])
                    );

                    // Initialize unproxied raw Leaflet map for 60 FPS performance
                    rawTechMap = L.map('technicianMap', {
                        center: [techLat, techLng],
                        zoom: zoom,
                        minZoom: this.config.min_zoom || 10,
                        maxZoom: this.config.max_zoom || 19,
                        preferCanvas: true,
                        zoomControl: false,
                        attributionControl: true,
                        fadeAnimation: true,
                        zoomAnimation: true,
                        inertia: true,
                        inertiaDeceleration: 3400,
                        inertiaMaxSpeed: 2000,
                    });

                    // Dark OSM Tiles
                    const subdomains = this.config.subdomains || ['a', 'b', 'c'];
                    L.tileLayer(this.config.tile_url, {
                        maxZoom: this.config.max_zoom || 19,
                        minZoom: this.config.min_zoom || 10,
                        bounds: coveredBounds,
                        subdomains: subdomains,
                        keepBuffer: 6,
                        updateWhenIdle: false,
                        updateWhenZooming: true,
                        attribution: '&copy; ' + @json($platformName ?? 'GIS') + ' | ' + (this.config.name || 'GIS'),
                    }).addTo(rawTechMap);

                    rawMissionsLayer = L.layerGroup().addTo(rawTechMap);

                    // Render technician marker
                    if (this.technician) {
                        this.renderTechnicianMarker(techLat, techLng);
                    }

                    // Render active mission or available radar missions
                    if (this.activeMission && this.activeMission.lat && this.activeMission.lng) {
                        this.renderActiveMissionRoute(
                            this.activeMission.lat,
                            this.activeMission.lng,
                            this.activeMission.customer_name || 'کارفرما',
                            this.activeMission.customer_phone || ''
                        );
                    } else if (this.availableMissions && this.availableMissions.length > 0) {
                        this.renderAvailableMissionsMarkers();
                    }

                    // Window Events from Livewire
                    window.addEventListener('mission-accepted', (e) => {
                        const d = (Array.isArray(e.detail) ? e.detail[0] : (e.detail?.[0] || e.detail)) || {};
                        if (d.lat && d.lng) {
                            this.hasActiveMission = true;
                            this.clearMissionFocus();
                            if (rawMissionsLayer) {
                                rawMissionsLayer.clearLayers();
                                rawMissionMarkersMap = {};
                            }
                            this.renderActiveMissionRoute(parseFloat(d.lat), parseFloat(d.lng), d.customer || 'مشتری', d.phone || '');
                            this.sheetExpanded = true;
                        }
                    });

                    window.addEventListener('mission-route-started', (e) => {
                        const d = (Array.isArray(e.detail) ? e.detail[0] : (e.detail?.[0] || e.detail)) || {};
                        if (d.lat && d.lng) {
                            this.centerOnRoute(parseFloat(d.lat), parseFloat(d.lng));
                        }
                    });

                    window.addEventListener('mission-completed', () => {
                        this.hasActiveMission = false;
                        this.clearActiveMissionRoute();
                        this.clearMissionFocus();
                        this.renderAvailableMissionsMarkers();
                    });

                    window.addEventListener('active-mission-state-changed', (e) => {
                        const d = (Array.isArray(e.detail) ? e.detail[0] : (e.detail?.[0] || e.detail)) || {};
                        const wasActive = this.hasActiveMission;
                        this.hasActiveMission = !!d.hasActiveMission;
                        this.activeMissionTitle = d.title || '';
                        this.activeMissionStatus = d.status || '';
                        this.activeMissionCity = d.city || '';
                        if (d.customerPhone) this.activeMissionCustomerPhone = d.customerPhone;
                        if (d.customerName) this.activeMissionCustomerName = d.customerName;
                        if (d.distanceKm) this.activeMissionDist = d.distanceKm;
                        if (d.etaText) this.activeMissionEta = d.etaText;
                        if (typeof d.availableCount !== 'undefined') {
                            this.availableMissionsCount = d.availableCount;
                        }

                        if (Array.isArray(d.missions)) {
                            this.availableMissions = d.missions;
                        }

                        if (this.hasActiveMission && d.destLat && d.destLng) {
                            if (rawMissionsLayer) {
                                rawMissionsLayer.clearLayers();
                                rawMissionMarkersMap = {};
                            }
                            this.renderActiveMissionRoute(
                                parseFloat(d.destLat),
                                parseFloat(d.destLng),
                                this.activeMissionCustomerName,
                                this.activeMissionCustomerPhone
                            );
                        } else if (!this.hasActiveMission) {
                            if (wasActive) {
                                this.clearActiveMissionRoute();
                                this.clearMissionFocus();
                            }
                            this.renderAvailableMissionsMarkers();
                        }
                    });

                    window.addEventListener('mission-cancelled-by-admin', () => {
                        this.hasActiveMission = false;
                        this.activeMissionTitle = '';
                        this.activeMissionStatus = '';
                        this.activeMissionCity = '';
                        this.clearActiveMissionRoute();
                        this.clearMissionFocus();
                        this.renderAvailableMissionsMarkers();
                    });

                    window.addEventListener('focus-mission-on-map', (e) => {
                        if (e.detail?.lat && e.detail?.lng) {
                            this.focusOnMission(e.detail.orderId, e.detail.lat, e.detail.lng, e.detail.title || 'سفارش');
                        }
                    });

                    window.addEventListener('clear-mission-focus-on-map', () => {
                        this.clearMissionFocus();
                    });

                    window.addEventListener('mission-card-selected', (e) => {
                        this.sheetExpanded = true;
                        setTimeout(() => {
                            const card = document.getElementById('mission-card-' + e.detail?.orderId);
                            if (card) {
                                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, 100);
                    });

                    window.addEventListener('mission-dismissed-from-map', (e) => {
                        const orderId = e.detail?.orderId;
                        if (orderId && rawMissionMarkersMap[orderId]) {
                            rawMissionsLayer.removeLayer(rawMissionMarkersMap[orderId]);
                            delete rawMissionMarkersMap[orderId];
                        }
                        this.clearMissionFocus();
                    });

                    window.addEventListener('mission-removed-from-map', (e) => {
                        const orderId = e.detail?.orderId;
                        if (orderId && rawMissionMarkersMap[orderId]) {
                            rawMissionsLayer.removeLayer(rawMissionMarkersMap[orderId]);
                            delete rawMissionMarkersMap[orderId];
                        }
                        this.clearMissionFocus();
                    });

                    window.addEventListener('new-mission-received-on-map', (e) => {
                        const m = e.detail;
                        if (!m || !m.order_id || !m.lat || !m.lng || !rawMissionsLayer) return;

                        this.addOrUpdateMissionMarker({
                            id: m.order_id,
                            lat: m.lat,
                            lng: m.lng,
                            category_name: m.appliance_label || 'سفارش',
                            order_number: m.order_number,
                            is_emergency: m.is_emergency
                        });

                        if (navigator.vibrate) {
                            try { navigator.vibrate([100, 50, 100]); } catch(err) {}
                        }
                    });

                    // Direct Client-Side Echo Fleet Subscription
                    if (window.Echo) {
                        window.Echo.channel('fieldservice.fleet')
                            .listen('.ServiceOrderCreated', (e) => {
                                if (window.Livewire) {
                                    window.Livewire.dispatch('refresh-mission-feed');
                                }
                            })
                            .listen('.ServiceOrderStatusChanged', (e) => {
                                if (window.Livewire) {
                                    window.Livewire.dispatch('refresh-mission-feed');
                                }
                                if (e.status === 'accepted' || e.status === 'cancelled') {
                                    const orderId = e.order_id;
                                    if (orderId && rawMissionMarkersMap[orderId]) {
                                        rawMissionsLayer.removeLayer(rawMissionMarkersMap[orderId]);
                                        delete rawMissionMarkersMap[orderId];
                                    }
                                }
                            });
                    }

                    window.addEventListener('technician-duty-changed', (e) => {
                        if (typeof e.detail?.isOnline !== 'undefined' && this.technician) {
                            this.technician.is_online = e.detail.isOnline;
                            this.renderTechnicianMarker(this.technician.lat, this.technician.lng);
                            if (!this.technician.is_online) {
                                if (rawMissionsLayer) rawMissionsLayer.clearLayers();
                                this.clearMissionFocus();
                            }
                        }
                    });

                    window.addEventListener('technician-authenticated', () => {
                        setTimeout(() => {
                            window.location.reload();
                        }, 400);
                    });

                    window.addEventListener('technician-logged-out', () => {
                        setTimeout(() => {
                            window.location.reload();
                        }, 400);
                    });
                },

                renderTechnicianMarker(lat, lng) {
                    if (!rawTechMap) return;
                    if (rawTechMarker) {
                        rawTechMap.removeLayer(rawTechMarker);
                        rawTechMarker = null;
                    }

                    const isOnline = this.technician ? !!this.technician.is_online : false;

                    const techIcon = L.divIcon({
                        className: 'technician-radar-pin',
                        html: `
                            <div style="position: relative; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                ${isOnline ? '<div class="tech-radar-ring" style="width: 44px; height: 44px;"></div>' : ''}
                                <div style="background: ${isOnline ? '#4f46e5' : '#475569'}; width: 30px; height: 30px; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 4px 14px rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; color: white; position: relative; z-index: 2;">
                                    <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                            </div>
                        `,
                        iconSize: [44, 44],
                        iconAnchor: [22, 22],
                    });

                    rawTechMarker = L.marker([lat, lng], { icon: techIcon }).addTo(rawTechMap);
                },

                calculateHaversineDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371; // km
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return Math.round(R * c * 1.25 * 10) / 10;
                },

                calculateETA(distanceKm) {
                    if (!distanceKm || distanceKm <= 0) return 3;
                    return Math.max(2, Math.round((distanceKm / 25) * 60));
                },

                currentDestLat: null,
                currentDestLng: null,

                async renderActiveMissionRoute(destLat, destLng, customerName, customerPhone = null) {
                    if (!rawTechMap) return;

                    const techLat = (this.technician && this.technician.lat) ? parseFloat(this.technician.lat) : 36.5444;
                    const techLng = (this.technician && this.technician.lng) ? parseFloat(this.technician.lng) : 52.6780;
                    const parsedDestLat = parseFloat(destLat);
                    const parsedDestLng = parseFloat(destLng);

                    let dist = this.calculateHaversineDistance(techLat, techLng, parsedDestLat, parsedDestLng);
                    let eta = this.calculateETA(dist);
                    this.activeMissionDist = dist;
                    this.activeMissionEta = `حدود ${eta} دقیقه`;
                    if (customerName) this.activeMissionCustomerName = customerName;
                    if (customerPhone) this.activeMissionCustomerPhone = customerPhone;

                    // If route is already rendered with the exact same destination, update info and don't re-create layers or reset view
                    if (rawCustomerMarker && this.currentDestLat === parsedDestLat && this.currentDestLng === parsedDestLng) {
                        return;
                    }

                    this.clearActiveMissionRoute();
                    this.currentDestLat = parsedDestLat;
                    this.currentDestLng = parsedDestLng;

                    // Fetch real street road route from Routing Service
                    let routeCoords = [[techLat, techLng], [parsedDestLat, parsedDestLng]];
                    try {
                        const url = `{{ route('fieldservice.api.route') }}?origin_lat=${techLat}&origin_lng=${techLng}&dest_lat=${parsedDestLat}&dest_lng=${parsedDestLng}`;
                        const response = await fetch(url);
                        if (response.ok) {
                            const data = await response.json();
                            if (data.coordinates && data.coordinates.length > 1) {
                                routeCoords = data.coordinates;
                                if (data.distance_km) {
                                    this.activeMissionDist = data.distance_km;
                                }
                                if (data.eta_minutes) {
                                    this.activeMissionEta = `حدود ${data.eta_minutes} دقیقه`;
                                }
                            }
                        }
                    } catch (err) {
                        console.warn('Technician road route fallback to direct line:', err);
                    }

                    // Customer destination pin (Clean vector SVG)
                    const customerIcon = L.divIcon({
                        className: 'customer-destination-pin',
                        html: `
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center; filter: drop-shadow(0 4px 12px rgba(239,68,68,0.45));">
                                <div style="background: #ef4444; color: white; border-radius: 50%; width: 34px; height: 34px; border: 2.5px solid white; box-shadow: 0 4px 12px rgba(239,68,68,0.4); display: flex; align-items: center; justify-content: center;">
                                    <svg style="width: 17px; height: 17px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div style="background: rgba(15,23,42,0.92); color: white; font-size: 10px; font-weight: 800; font-family: IRANYekanX, sans-serif; padding: 2px 8px; border-radius: 9999px; margin-top: 3px; white-space: nowrap; border: 1px solid rgba(255,255,255,0.15);">
                                    محل مشتری
                                </div>
                            </div>
                        `,
                        iconSize: [34, 56],
                        iconAnchor: [17, 17],
                    });

                    rawCustomerMarker = L.marker([parsedDestLat, parsedDestLng], { icon: customerIcon })
                        .addTo(rawTechMap)
                        .bindPopup(`<b style="font-family: IRANYekanX, sans-serif; font-size: 12px;">محل مشتری: ${customerName || 'کارفرما'}</b>`);

                    // Dual layer high-contrast polyline (glow + vibrant line) along actual roads
                    rawRouteGlowPolyline = L.polyline(routeCoords, {
                        color: '#4f46e5',
                        weight: 8,
                        opacity: 0.35,
                        lineCap: 'round',
                        lineJoin: 'round',
                    }).addTo(rawTechMap);

                    rawRoutePolyline = L.polyline(routeCoords, {
                        color: '#6366f1',
                        weight: 4.5,
                        opacity: 0.95,
                        lineCap: 'round',
                        lineJoin: 'round',
                    }).addTo(rawTechMap);

                    try {
                        const bounds = L.latLngBounds(routeCoords);
                        rawTechMap.fitBounds(bounds, { padding: [80, 80], maxZoom: 16 });
                    } catch (e) {}
                },

                clearActiveMissionRoute() {
                    if (!rawTechMap) return;
                    this.currentDestLat = null;
                    this.currentDestLng = null;
                    if (rawCustomerMarker) {
                        try { rawTechMap.removeLayer(rawCustomerMarker); } catch (e) {}
                        rawCustomerMarker = null;
                    }
                    if (rawRouteGlowPolyline) {
                        try { rawTechMap.removeLayer(rawRouteGlowPolyline); } catch (e) {}
                        rawRouteGlowPolyline = null;
                    }
                    if (rawRoutePolyline) {
                        try { rawTechMap.removeLayer(rawRoutePolyline); } catch (e) {}
                        rawRoutePolyline = null;
                    }
                    this.activeMissionDist = null;
                    this.activeMissionEta = '';
                },

                addOrUpdateMissionMarker(m) {
                    if (!m || !m.id || !m.lat || !m.lng || !rawMissionsLayer) return null;

                    // If already exists, update position and return
                    if (rawMissionMarkersMap[m.id]) {
                        rawMissionMarkersMap[m.id].setLatLng([m.lat, m.lng]);
                        return rawMissionMarkersMap[m.id];
                    }

                    const pinIcon = L.divIcon({
                        className: 'available-job-pin',
                        html: `
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: ${m.is_emergency ? '#d97706' : '#4f46e5'}; width: 30px; height: 30px; border-radius: 50%; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center; color: white; transition: transform 0.2s;" class="hover:scale-110 active:scale-95">
                                    ${m.is_emergency ? `
                                        <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                        </svg>
                                    ` : `
                                        <svg style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                                        </svg>
                                    `}
                                </div>
                            </div>
                        `,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15],
                    });

                    const mark = L.marker([m.lat, m.lng], { icon: pinIcon }).addTo(rawMissionsLayer);
                    const titleText = m.category?.name || m.category_name || m.appliance_label || 'سفارش';
                    const orderNum = m.order_number || m.id;

                    mark.bindTooltip(`<div style="font-family: IRANYekanX, sans-serif; font-size: 11px; padding: 2px 4px; text-align: center;"><b>${titleText}</b><div style="color: #6366f1; font-weight: bold; margin-top: 2px;">#${orderNum}</div></div>`, {
                        direction: 'top',
                        offset: [0, -12]
                    });

                    rawMissionMarkersMap[m.id] = mark;

                    mark.on('click', () => {
                        this.sheetExpanded = true;
                        this.focusOnMission(m.id, m.lat, m.lng, titleText);
                        if (window.Livewire) {
                            window.Livewire.dispatch('order-pin-clicked', { orderId: m.id });
                        }
                        setTimeout(() => {
                            const card = document.getElementById('mission-card-' + m.id);
                            if (card) {
                                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, 100);
                    });

                    return mark;
                },

                renderAvailableMissionsMarkers() {
                    if (!rawMissionsLayer) return;

                    if (!this.technician || !this.technician.is_online || this.hasActiveMission) {
                        rawMissionsLayer.clearLayers();
                        rawMissionMarkersMap = {};
                        return;
                    }

                    const currentMissions = this.availableMissions || [];
                    const currentIds = currentMissions.map(m => Number(m.id));

                    // Remove stale markers smoothly
                    Object.keys(rawMissionMarkersMap).forEach((id) => {
                        if (!currentIds.includes(Number(id))) {
                            rawMissionsLayer.removeLayer(rawMissionMarkersMap[id]);
                            delete rawMissionMarkersMap[id];
                        }
                    });

                    // Add or update all current missions
                    currentMissions.forEach((m) => {
                        this.addOrUpdateMissionMarker(m);
                    });
                },

                focusOnMission(orderId, lat, lng, title) {
                    if (!rawTechMap) return;

                    // Clear previous preview line and focus ring
                    this.clearMissionFocus();

                    // Ensure the mission pin is physically rendered on map before focusing!
                    if (!rawMissionMarkersMap[orderId] && rawMissionsLayer) {
                        this.addOrUpdateMissionMarker({
                            id: orderId,
                            lat: lat,
                            lng: lng,
                            category_name: title,
                            order_number: orderId,
                            is_emergency: false,
                        });
                    }

                    // Smooth fly to order position
                    rawTechMap.flyTo([lat, lng], 16, { duration: 0.8 });

                    // Draw preview dotted route line from technician to job location
                    const techLat = (this.technician && this.technician.lat) ? this.technician.lat : 36.5444;
                    const techLng = (this.technician && this.technician.lng) ? this.technician.lng : 52.6780;
                    rawPreviewRouteLine = L.polyline([[techLat, techLng], [lat, lng]], {
                        color: '#818cf8',
                        weight: 3.5,
                        opacity: 0.85,
                        dashArray: '6, 8',
                    }).addTo(rawTechMap);

                    // Glowing radar pulse marker on the selected job
                    const pulseIcon = L.divIcon({
                        className: 'focus-job-pulse',
                        html: `
                            <div style="position: relative; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; pointer-events: none;">
                                <div class="tech-radar-ring" style="width: 48px; height: 48px; border-color: #6366f1; background: rgba(99, 102, 241, 0.2);"></div>
                            </div>
                        `,
                        iconSize: [48, 48],
                        iconAnchor: [24, 24],
                    });
                    rawFocusRingLayer = L.marker([lat, lng], { icon: pulseIcon, interactive: false }).addTo(rawTechMap);

                    // Open tooltip if marker exists
                    if (rawMissionMarkersMap[orderId]) {
                        rawMissionMarkersMap[orderId].openTooltip();
                    }
                },

                clearMissionFocus() {
                    if (!rawTechMap) return;
                    if (rawPreviewRouteLine) {
                        rawTechMap.removeLayer(rawPreviewRouteLine);
                        rawPreviewRouteLine = null;
                    }
                    if (rawFocusRingLayer) {
                        rawTechMap.removeLayer(rawFocusRingLayer);
                        rawFocusRingLayer = null;
                    }
                },

                centerOnTechnician() {
                    if (!rawTechMap) return;
                    const techLat = (this.technician && this.technician.lat) ? this.technician.lat : 36.5444;
                    const techLng = (this.technician && this.technician.lng) ? this.technician.lng : 52.6780;
                    rawTechMap.setView([techLat, techLng], 16, { animate: true });
                },

                centerOnRoute(destLat, destLng) {
                    if (!rawTechMap) return;
                    const techLat = (this.technician && this.technician.lat) ? this.technician.lat : 36.5444;
                    const techLng = (this.technician && this.technician.lng) ? this.technician.lng : 52.6780;
                    rawTechMap.fitBounds(L.latLngBounds([techLat, techLng], [destLat, destLng]), {
                        padding: [60, 60],
                        animate: true,
                    });
                },
            }));
        });
    </script>

    @livewireScripts
</body>
</html>
