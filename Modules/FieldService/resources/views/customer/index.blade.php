<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $platformName ?? 'سامانه هوشمند خدمات فنی' }} | اعزام تکنسین</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Leaflet CSS & JS (Local Core Assets) --}}
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Custom Radar Animation Rings */
        @keyframes radar-pulse {
            0% {
                transform: scale(0.1);
                opacity: 0.9;
            }
            70% {
                opacity: 0.35;
            }
            100% {
                transform: scale(2.4);
                opacity: 0;
            }
        }
        .radar-wave-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid #2563eb;
            background: rgba(37, 99, 235, 0.15);
            animation: radar-pulse 2.2s cubic-bezier(0.2, 0.8, 0.4, 1) infinite;
            pointer-events: none;
        }

        /* Leaflet Controls Styling */
        .leaflet-control-zoom {
            display: none !important;
        }
        .leaflet-control-attribution {
            background: rgba(255, 255, 255, 0.7) !important;
            font-size: 9px !important;
            border-radius: 6px !important;
            margin: 4px !important;
        }
        .dark .leaflet-control-attribution {
            background: rgba(15, 23, 42, 0.8) !important;
            color: #94a3b8 !important;
        }

        /* Instant Touch & GPU Hardware Acceleration for Zero-Latency Panning */
        #customerMap {
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
        }
        .leaflet-container:active {
            cursor: grabbing;
        }
        .leaflet-map-pane {
            will-change: transform;
            transform: translateZ(0);
        }
        .center-pin-wrapper,
        .center-pin-wrapper * {
            pointer-events: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
        }
    </style>
</head>
<body class="h-dvh w-screen overflow-hidden bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-sans selection:bg-indigo-600 selection:text-white select-none relative"
      x-data="customerAppHandler(@js($mapConfig), @js($onlineTechnicians), @js($mazandaranCities), @js(Auth::guard('client')->user() ? Auth::guard('client')->user()->addresses()->latest()->take(6)->get() : []), @js($activeOrder))"
      x-init="initMap()">

    {{-- ========================================================================= --}}
    {{-- 1. FULL-VIEWPORT MAP LAYER                                                --}}
    {{-- ========================================================================= --}}
    <div id="customerMap" class="w-full h-full absolute inset-0 z-0 bg-slate-200 dark:bg-slate-850"></div>

    {{-- ========================================================================= --}}
    {{-- 2. CENTER PIN: SNAPP-NATIVE BLUE TARGET BULLSEYE PIN                      --}}
    {{-- ========================================================================= --}}
    <div class="pointer-events-none fixed inset-0 flex items-center justify-center z-10"
         x-show="!isTrackingActive && !isOriginConfirmed"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div class="center-pin-wrapper relative flex flex-col items-center select-none -translate-y-4 pointer-events-none">
            {{-- Floating status address pill above pin --}}
            <div :class="isMoving ? 'opacity-0 -translate-y-2 scale-95' : 'opacity-100 translate-y-0 scale-100'"
                 class="transition-transform transition-opacity duration-150 ease-out mb-2 px-3.5 py-1.5 rounded-full bg-slate-900 dark:bg-slate-850 text-white text-[11px] font-bold shadow-lg border border-slate-700/80 flex items-center gap-2 whitespace-nowrap">
                <span class="w-2 h-2 rounded-full" :class="isGeocoding ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'"></span>
                <span x-text="selectedLocationLabel || 'محل اعزام تکنسین (نقشه را تنظیم کنید)'"></span>
            </div>
            
            {{-- Snapp Blue Concentric Target Dot with Animated Wave --}}
            <div class="relative flex items-center justify-center transition-transform duration-150 ease-out"
                 :class="isMoving ? '-translate-y-3 scale-110' : 'translate-y-0 scale-100'">
                {{-- Outer Pulse Ring --}}
                <span class="absolute w-10 h-10 rounded-full bg-blue-500/20 dark:bg-blue-400/20 ring-2 ring-blue-500/30 pointer-events-none"></span>
                
                {{-- Middle White Circular Ring --}}
                <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-900 shadow-2xl border-2 border-slate-100 dark:border-slate-700 flex items-center justify-center z-10">
                    {{-- Inner Vivid Blue Core Dot --}}
                    <span class="w-4 h-4 rounded-full bg-blue-600 dark:bg-blue-500 shadow-sm"></span>
                </div>
            </div>

            {{-- Pin Connector Line & Road Shadow --}}
            <div class="w-0.5 h-3 bg-slate-800 dark:bg-slate-300 transition-opacity duration-150 ease-out"
                 :class="isMoving ? 'opacity-0' : 'opacity-100'"></div>
            <div :class="isMoving ? 'scale-50 opacity-20' : 'scale-100 opacity-60'"
                 class="transition-transform transition-opacity duration-150 ease-out w-4 h-1.5 bg-slate-950 rounded-full blur-[1px]"></div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. FLOATING TOP HEADER: EXACT SNAPP TRIPLE FLOATING CONTROLS (RTL ORDER)   --}}
    {{-- ========================================================================= --}}
    <header class="fixed top-3 inset-x-3 sm:top-5 sm:inset-x-6 z-20 pointer-events-none">
        <div class="max-w-md mx-auto flex items-center justify-between gap-2">
            
            {{-- Element 1 (Right in RTL): Saved Addresses OR Back/Map Center Toggle --}}
            <button type="button" 
                    @click="currentStep === 4 ? toggleSheet() : (currentStep > 1 ? $dispatch('return-to-map') : addressesModalOpen = true)"
                    class="pointer-events-auto w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95 shrink-0"
                    :title="currentStep === 4 ? (sheetExpanded ? 'مشاهده نقشه کامل' : 'مشاهده جزئیات سفارش') : (currentStep > 1 ? 'بازگشت به نقشه' : 'آدرس‌های منتخب')">
                <template x-if="currentStep === 4">
                    <span class="flex items-center justify-center">
                        <template x-if="sheetExpanded">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/></svg>
                        </template>
                        <template x-if="!sheetExpanded">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                        </template>
                    </span>
                </template>
                <template x-if="currentStep > 1 && currentStep < 4">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </template>
                <template x-if="currentStep <= 1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </template>
            </button>

            {{-- Element 2 (Center): Segmented Capsule (Step 1-3) OR Live Tracking ETA Capsule (Step 4) --}}
            <template x-if="currentStep < 4">
                <div class="pointer-events-auto bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl rounded-full p-1 border border-slate-200/80 dark:border-slate-800 flex items-center font-sans shrink-0">
                    <button type="button" @click="orderFor = 'other'; $dispatch('set-order-for', 'other')"
                            :class="orderFor === 'other' ? 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                            class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs transition-all whitespace-nowrap">
                        برای دیگری
                    </button>
                    <button type="button" @click="orderFor = 'self'; $dispatch('set-order-for', 'self')"
                            :class="orderFor === 'self' ? 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                            class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs transition-all whitespace-nowrap">
                        برای خودم
                    </button>
                </div>
            </template>
            <template x-if="currentStep === 4">
                <div @click="toggleSheet()"
                     class="pointer-events-auto bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl rounded-full px-3.5 sm:px-4 py-1.5 sm:py-2 border border-slate-200/80 dark:border-slate-800 flex items-center gap-2 font-sans shrink-0 cursor-pointer hover:border-indigo-400 transition-all active:scale-95">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span class="text-xs font-black text-slate-800 dark:text-slate-100">رهگیری زنده</span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400" x-text="etaText || 'در حال رصد'"></span>
                </div>
            </template>

            {{-- Element 3 (Left in RTL): User Profile Circular Button --}}
            @php
                $loggedClient = Auth::guard('client')->user();
            @endphp
            @if($loggedClient)
            <a href="{{ route('client.dashboard') }}"
               class="pointer-events-auto w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95 shrink-0"
               title="پرتال مشتریان">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </a>
            @else
            <button type="button" @click="$dispatch('open-customer-auth')"
                    class="pointer-events-auto w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95 shrink-0"
                    title="ورود / ثبت‌نام">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </button>
            @endif

        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- 4. FLOATING GPS / LOCATE ME BUTTON (RIGHT-BOTTOM AS REQUESTED)            --}}
    {{-- ========================================================================= --}}
    <div :class="sheetExpanded ? 'bottom-[330px] sm:bottom-[340px]' : (currentStep >= 2 ? 'bottom-24 sm:bottom-26' : 'bottom-44 sm:bottom-48')"
         class="fixed right-4 sm:right-6 z-20 transition-all duration-300 ease-out">
        <button type="button" @click="locateMe()"
                :class="isLocating ? 'ring-4 ring-indigo-500/30 animate-spin' : ''"
                title="موقعیت فعلی من"
                class="w-12 h-12 rounded-full bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 2v3m0 14v3M2 12h3m14 0h3"></path>
            </svg>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- 5. FLOATING BOTTOM SHEET (EXACT SNAPP EXPERIENCE - COLLAPSIBLE ON BOTH)    --}}
    {{-- ========================================================================= --}}
    <section class="fixed inset-x-0 bottom-0 z-30 lg:top-20 lg:bottom-6 lg:right-6 lg:left-auto lg:w-[480px] pointer-events-none transition-all duration-300 ease-out">
        <div class="pointer-events-auto bg-white/95 dark:bg-slate-850/95 backdrop-blur-2xl rounded-t-[32px] lg:rounded-3xl border border-slate-200/90 dark:border-slate-750 shadow-2xl flex flex-col transition-all duration-300 ease-out"
             :class="{
                 'h-auto max-h-[85vh] lg:h-auto lg:max-h-[85vh]': sheetExpanded,
                 'h-[84px] max-h-[84px] lg:h-[84px] lg:max-h-[84px]': !sheetExpanded && currentStep >= 2,
                 'h-auto max-h-[45vh] lg:h-auto lg:max-h-full': !sheetExpanded && currentStep < 2
             }"
             @touchstart="handleTouchStart($event)"
             @touchmove="handleTouchMove($event)"
             @touchend="handleTouchEnd()">
            
            {{-- Peek Bar: Visible when user collapses sheet in Step >= 2 (Both Mobile & Desktop) --}}
            <div x-show="currentStep >= 2 && !sheetExpanded"
                 @click="sheetExpanded = true"
                 class="px-4 py-2.5 cursor-pointer flex flex-col justify-between h-[84px] select-none">
                {{-- Drag Handle --}}
                <div class="flex justify-center -mt-0.5">
                    <span class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-600 animate-pulse"></span>
                </div>
                
                {{-- Case 1: Live Tracking Active with Assigned Technician --}}
                <template x-if="isTrackingActive && assignedTech">
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- Technician Avatar with Emerald Online Ring --}}
                            <div class="relative w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/70 dark:border-indigo-500/30 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-850"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <div class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="assignedTech?.name || 'تکنسین اعزامی'"></div>
                                    <span class="inline-flex items-center gap-0.5 text-amber-500 dark:text-amber-400 text-[11px] font-bold">
                                        <svg class="w-3 h-3 fill-amber-400 text-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        <span x-text="assignedTech?.rating || '۴.۹۵'"></span>
                                    </span>
                                </div>
                                <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-2 mt-0.5 min-w-0 pr-0.5">
                                    <span class="relative flex h-2.5 w-2.5 shrink-0 my-auto">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-indigo-600 dark:bg-indigo-400"></span>
                                    </span>
                                    <span class="truncate" x-text="etaText ? ('در مسیر • ' + etaText) : 'تکنسین در حال حرکت به سمت شما'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a :href="'tel:' + (assignedTech?.mobile || '')" @click.stop
                               class="w-10 h-10 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center shadow-lg shadow-indigo-600/20 transition active:scale-95"
                               title="تماس مستقیم با تکنسین">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </a>
                            <button type="button" @click.stop="sheetExpanded = true"
                                    class="px-3.5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-750 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1 active:scale-95 border border-slate-200/80 dark:border-slate-700/60 shadow-xs">
                                <span>جزئیات</span>
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Case 2: Live Tracking Active but still searching radar (no technician yet) --}}
                <template x-if="isTrackingActive && !assignedTech">
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-7 h-7 rounded-full bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs font-black text-slate-900 dark:text-white truncate">رادار اعزام تکنسین فعال است</div>
                                <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold truncate">در حال جستجو و ارسال سفارش به نزدیک‌ترین متخصصین</div>
                            </div>
                        </div>
                        <button type="button" @click.stop="sheetExpanded = true"
                                class="shrink-0 px-3.5 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md shadow-indigo-600/20 flex items-center gap-1 transition active:scale-95">
                            <span>رهگیری</span>
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                        </button>
                    </div>
                </template>

                {{-- Case 3: Step 2 or 3 (Order creation flow before submission) --}}
                <template x-if="!isTrackingActive">
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <div class="min-w-0">
                                <div class="text-xs font-black text-slate-800 dark:text-slate-100 truncate">
                                    محل اعزام: <span x-text="confirmedOrigin?.locationText || selectedLocationLabel"></span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold truncate">
                                    برای مشاهده و تکمیل درخواست بالا بکشید
                                </div>
                            </div>
                        </div>

                        <button type="button" @click.stop="sheetExpanded = true"
                                class="shrink-0 px-3.5 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md shadow-indigo-600/20 flex items-center gap-1 transition active:scale-95">
                            <span>ادامه</span>
                            <span class="text-xs">⌃</span>
                        </button>
                    </div>
                </template>
            </div>

            {{-- Full Content Area: Wizard and details --}}
            <div :class="(currentStep >= 2 && !sheetExpanded) ? 'hidden' : 'flex flex-col flex-1 min-h-0'"
                 class="transition-all duration-200">
                <div class="p-3 sm:p-5 custom-scrollbar flex-1 overflow-x-hidden"
                     :class="sheetExpanded ? 'overflow-y-auto' : (currentStep >= 2 ? 'overflow-hidden' : 'overflow-visible')">
                    @livewire('fieldservice::order-wizard')
                </div>

                {{-- Trust Footnote --}}
                <div class="px-5 py-2.5 bg-slate-50/80 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 shrink-0"
                     :class="!sheetExpanded ? 'hidden lg:flex' : 'flex'">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">گارانتی رسمی خدمات {{ $platformName ?? '' }}</span>
                    </div>
                    <div class="font-medium">
                        اعزام تکنسین احراز هویت شده
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 6. MODALS & SLIDE-OVERS (PROFILE, ADDRESSES & SEARCH)                     --}}
    {{-- ========================================================================= --}}

    {{-- PROFILE & SETTINGS MODAL --}}
    <div x-show="profileModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs font-sans">
        <div @click.outside="profileModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-5 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-black text-slate-900 dark:text-white">حساب کاربری {{ $platformName ?? '' }}</h3>
                <button type="button" @click="profileModalOpen = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">✕</button>
            </div>

            @if($loggedClient)
            <div class="p-3.5 rounded-2xl bg-indigo-50/80 dark:bg-indigo-500/15 border border-indigo-200/80 dark:border-indigo-500/30 flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-base font-black shadow-md">
                    {{ mb_substr($loggedClient->full_name ?: 'ک', 0, 1) }}
                </span>
                <div class="min-w-0">
                    <div class="font-black text-sm text-slate-900 dark:text-white truncate">{{ $loggedClient->full_name ?: 'کاربر گرامی' }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-sans mt-0.5" dir="ltr">{{ $loggedClient->phone }}</div>
                </div>
            </div>

            <div class="space-y-1 text-xs">
                <a href="{{ route('client.dashboard') }}" target="_blank"
                   class="flex items-center gap-2.5 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition font-bold">
                    <svg class="w-4 h-4 text-indigo-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                    <span>داشبورد پرتال کارفرمایان</span>
                </a>
                <a href="{{ route('client.orders.index') }}" target="_blank"
                   class="flex items-center gap-2.5 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition font-bold">
                    <svg class="w-4 h-4 text-indigo-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
                    <span>سفارش‌های من و سوابق</span>
                </a>
                <button type="button" @click="$dispatch('toggle-order-mode'); profileModalOpen = false;"
                        class="w-full text-right flex items-center gap-2.5 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition font-bold">
                    <svg class="w-4 h-4 text-indigo-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y1="21" y2="16.65"></line></svg>
                    <span>پیگیری سفارش با کد رهگیری</span>
                </button>
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                {{-- Theme Switcher --}}
                <button type="button" @click="toggleTheme()" class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    <template x-if="isDark">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                            <span>تم روز</span>
                        </span>
                    </template>
                    <template x-if="!isDark">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                            <span>تم شب</span>
                        </span>
                    </template>
                </button>

                {{-- Logout --}}
                <form method="POST" action="{{ route('client.logout') }}">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 text-rose-600 dark:text-rose-300 border border-rose-200/80 dark:border-rose-500/30 text-xs font-bold transition active:scale-95 shadow-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>
                        <span>خروج از حساب</span>
                    </button>
                </form>
            </div>
            @else
            <div class="text-center py-3 space-y-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">شما هنوز وارد حساب کاربری خود نشده‌اید.</p>
                <button type="button" @click="profileModalOpen = false; $dispatch('open-customer-auth')"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg transition">
                    ورود / ثبت‌نام سریع با شماره همراه
                </button>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-center">
                <button type="button" @click="toggleTheme()" class="text-xs font-bold text-slate-600 dark:text-slate-400">
                    <template x-if="isDark">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                            <span>تم روز</span>
                        </span>
                    </template>
                    <template x-if="!isDark">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                            <span>تم شب</span>
                        </span>
                    </template>
                </button>
            </div>
            @endif

        </div>
    </div>

    {{-- SAVED ADDRESSES & CITIES MODAL --}}
    <div x-show="addressesModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs font-sans">
        <div @click.outside="addressesModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-5 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-black text-slate-900 dark:text-white">آدرس‌های منتخب و تغییر شهر</h3>
                <button type="button" @click="addressesModalOpen = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">✕</button>
            </div>

            {{-- Saved Client Addresses --}}
            <div>
                <div class="text-[11px] font-bold text-slate-400 mb-2">آدرس‌های ذخیره شده من:</div>
                <div class="space-y-1.5 max-h-40 overflow-y-auto custom-scrollbar">
                    <template x-for="addr in savedAddresses" :key="addr.id">
                        <button type="button" @click="chooseSavedAddress(addr)"
                                class="w-full text-right p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-500/40 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between transition">
                            <div class="min-w-0">
                                <div class="text-xs font-black text-slate-800 dark:text-slate-100 truncate" x-text="addr.title || 'آدرس من'"></div>
                                <div class="text-[10px] text-slate-400 truncate mt-0.5" x-text="addr.address"></div>
                            </div>
                            <span class="text-indigo-600 dark:text-indigo-400 text-xs font-bold shrink-0 mr-2 flex items-center gap-1">
                                <span>انتخاب</span>
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </span>
                        </button>
                    </template>
                    <div x-show="savedAddresses.length === 0" class="text-xs text-slate-400 text-center py-2">
                        هنوز آدرس ذخیره شده‌ای ندارید.
                    </div>
                </div>
            </div>

            {{-- Covered Cities Quick Switch --}}
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                <div class="text-[11px] font-bold text-slate-400 mb-2">تغییر سریع شهر (مناطق فعال تحت پوشش):</div>
                <div class="grid grid-cols-3 gap-1.5">
                    @foreach($mazandaranCities as $cName => $cData)
                    <button type="button" @click="changeCity('{{ $cName }}'); addressesModalOpen = false;"
                            :class="currentCity === '{{ $cName }}' ? 'bg-indigo-600 text-white font-black' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-slate-700 font-bold'"
                            class="py-2 px-2 rounded-xl text-xs text-center transition">
                        {{ $cData['name'] }}
                    </button>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- ADDRESS SEARCH MODAL --}}
    <div x-show="searchModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-16 bg-slate-950/60 backdrop-blur-xs font-sans">
        <div @click.outside="searchModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-5 space-y-4">
            
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-black text-slate-900 dark:text-white">جستجوی آدرس و خیابان</h3>
                <button type="button" @click="searchModalOpen = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">✕</button>
            </div>

            <div>
                <input type="text" x-model="searchQuery" @input="performPlaceSearch()" placeholder="نام خیابان، میدان یا محله در مازندران..."
                       class="w-full h-12 px-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-sans text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="space-y-1.5 max-h-56 overflow-y-auto custom-scrollbar">
                <template x-for="place in searchResults" :key="place.name">
                    <button type="button" @click="selectPlace(place)"
                            class="w-full text-right p-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 flex items-center justify-between text-xs transition">
                        <span class="font-bold" x-text="place.name"></span>
                        <span class="text-slate-400 text-[10px]" x-text="place.city"></span>
                    </button>
                </template>
                <div x-show="searchResults.length === 0" class="text-xs text-slate-400 text-center py-4">
                    نام محله، خیابان یا میدان مورد نظر خود را تایپ کنید.
                </div>
            </div>

        </div>
    </div>

    {{-- Fast Customer Auth Modal (Snapp / Tap30 Style with SMS OTP & Password) --}}
    @livewire('fieldservice::customer-auth-modal')

    {{-- Leaflet Map & App Handler Script --}}
    <script>
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

        window.addEventListener('client-authenticated', () => {
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });

        window.addEventListener('client-logged-out', () => {
            setTimeout(() => {
                window.location.reload();
            }, 300);
        });

        // Non-reactive raw Leaflet objects to prevent Alpine Proxy traversal overhead
        let rawMap = null;
        let rawTechMarkersLayer = null;
        let rawRadarCircleLayer = null;
        let rawOriginMarker = null;
        let rawAssignedTechMarker = null;
        let rawRouteGlowPolyline = null;
        let rawRoutePolyline = null;

        document.addEventListener('alpine:init', () => {
            Alpine.data('customerAppHandler', (config, technicians, cities, savedAddresses, activeOrder = null) => ({
                get map() { return rawMap; },
                set map(val) { rawMap = val; },
                get techMarkersLayer() { return rawTechMarkersLayer; },
                set techMarkersLayer(val) { rawTechMarkersLayer = val; },
                get radarCircleLayer() { return rawRadarCircleLayer; },
                set radarCircleLayer(val) { rawRadarCircleLayer = val; },
                get originMarker() { return rawOriginMarker; },
                set originMarker(val) { rawOriginMarker = val; },
                config: config,
                technicians: technicians || [],
                cities: cities || {},
                savedAddresses: savedAddresses || [],
                activeOrder: activeOrder,
                currentCity: 'بابل',
                isMoving: false,
                isLocating: false,
                isGeocoding: false,
                isTrackingActive: !!activeOrder,
                selectedLocationLabel: activeOrder?.address || 'مازندران، بابل',
                isDark: document.documentElement.classList.contains('dark'),
                
                // Assigned Technician Live State & ETA
                assignedTech: activeOrder && activeOrder.technician ? {
                    name: `${activeOrder.technician.first_name || ''} ${activeOrder.technician.last_name || ''}`.trim() || 'تکنسین',
                    mobile: activeOrder.technician.mobile || '',
                    rating: activeOrder.technician.rating_avg || '۴.۹۵',
                    ordersCount: activeOrder.technician.completed_orders_count || 0,
                    lat: parseFloat(activeOrder.technician.lat),
                    lng: parseFloat(activeOrder.technician.lng),
                } : null,
                distanceKm: null,
                etaMinutes: null,
                etaText: '',

                // Snapp App States
                orderFor: 'self',
                profileModalOpen: false,
                addressesModalOpen: false,
                searchModalOpen: false,
                searchQuery: '',
                searchResults: [],

                // Confirmed Origin on Map
                isOriginConfirmed: !!activeOrder,
                confirmedOrigin: activeOrder ? {
                    lat: parseFloat(activeOrder.lat),
                    lng: parseFloat(activeOrder.lng),
                    locationText: activeOrder.address
                } : null,
                currentStep: activeOrder ? 4 : 1,

                // Mobile & Desktop Sheet State
                sheetExpanded: !!activeOrder,
                touchStartY: 0,
                touchStartX: 0,
                touchEndY: 0,
                touchEndX: 0,

                toggleSheet() {
                    this.sheetExpanded = !this.sheetExpanded;
                },

                openSheet() {
                    this.sheetExpanded = true;
                },

                closeSheet() {
                    this.sheetExpanded = false;
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
                            // Swipe Up -> Expand
                            this.sheetExpanded = true;
                        } else if (deltaY < -35) {
                            // Swipe Down -> Collapse to peek bar
                            this.sheetExpanded = false;
                        }
                    }
                    this.touchStartY = 0;
                    this.touchEndY = 0;
                },

                initMap() {
                    const defaultLat = this.config.center?.lat || 36.5444;
                    const defaultLng = this.config.center?.lng || 52.6780;
                    const zoom = this.config.zoom || 16;

                    // Strict geographic boundaries strictly locked to covered cities and provinces
                    const sw = this.config.bounds?.southWest || [36.35, 52.45];
                    const ne = this.config.bounds?.northEast || [36.75, 52.95];
                    const coveredBounds = L.latLngBounds(
                        L.latLng(sw[0], sw[1]),
                        L.latLng(ne[0], ne[1])
                    );

                    // Initialize raw Leaflet map with completely frictionless drag start
                    rawMap = L.map('customerMap', {
                        center: [defaultLat, defaultLng],
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

                    // Add map tile layer with STRICT covered bounds:
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
                    }).addTo(rawMap);

                    // Layer for nearby technician markers
                    rawTechMarkersLayer = L.layerGroup().addTo(rawMap);
                    rawRadarCircleLayer = L.layerGroup().addTo(rawMap);

                    this.renderNearbyTechnicians();

                    // Map Move Events for Snapp-like center pin
                    rawMap.on('movestart', () => {
                        // Immediately abort pending geocoding requests to completely free up CPU & network
                        if (this._abortController) {
                            this._abortController.abort();
                            this._abortController = null;
                        }
                        if (this._geocodeTimeout) {
                            clearTimeout(this._geocodeTimeout);
                        }
                        if (!this.isMoving) {
                            this.isMoving = true;
                        }
                    });

                    rawMap.on('moveend', () => {
                        this.isMoving = false;
                        const center = rawMap.getCenter();

                        // Smooth boundary protection: gently slide back if pulled outside coverage area
                        if (!coveredBounds.contains(center)) {
                            rawMap.panInsideBounds(coveredBounds, { animate: true, duration: 0.35 });
                            this.selectedLocationLabel = 'موقعیت خارج از محدوده تحت پوشش خدمات';
                            return;
                        }

                        this.onLocationChanged(center.lat, center.lng);
                    });

                    // Initial location sync or restore active order view
                    if (this.activeOrder && this.activeOrder.lat && this.activeOrder.lng) {
                        const oLat = parseFloat(this.activeOrder.lat);
                        const oLng = parseFloat(this.activeOrder.lng);
                        rawMap.setView([oLat, oLng], 16);
                        this.renderConfirmedOrigin(oLat, oLng, this.activeOrder.address || 'محل سفارش');
                        
                        if (this.activeOrder.technician && this.activeOrder.technician.lat && this.activeOrder.technician.lng) {
                            const tLat = parseFloat(this.activeOrder.technician.lat);
                            const tLng = parseFloat(this.activeOrder.technician.lng);
                            const tName = `${this.activeOrder.technician.first_name || ''} ${this.activeOrder.technician.last_name || ''}`.trim() || 'تکنسین';
                            this.renderAssignedTechnician(tLat, tLng, tName, this.activeOrder.technician.mobile || '', this.activeOrder.technician.rating_avg || '۴.۹۵');
                        } else {
                            this.drawRadarRipple(oLat, oLng, this.activeOrder.search_radius_km || 3);
                        }
                    } else {
                        this.onLocationChanged(defaultLat, defaultLng);
                    }

                    // Listeners from Livewire
                    window.addEventListener('order-step-changed', (e) => {
                        if (e.detail?.step) {
                            this.currentStep = e.detail.step;
                            if (e.detail.step > 1) {
                                this.sheetExpanded = true;
                            } else {
                                this.sheetExpanded = false;
                            }
                        }
                    });

                    const handleLocationConfirmed = (e) => {
                        this.isOriginConfirmed = true;
                        this.confirmedOrigin = {
                            lat: e.detail.lat,
                            lng: e.detail.lng,
                            locationText: e.detail.locationText
                        };
                        this.renderConfirmedOrigin(e.detail.lat, e.detail.lng, e.detail.locationText);
                    };

                    const handleLocationCleared = () => {
                        this.isOriginConfirmed = false;
                        this.isTrackingActive = false;
                        this.confirmedOrigin = null;
                        this.clearRoute();
                        if (this.radarCircleLayer) {
                            this.radarCircleLayer.clearLayers();
                        }
                        if (this.originMarker) {
                            this.map.removeLayer(this.originMarker);
                            this.originMarker = null;
                        }
                    };

                    window.addEventListener('origin-confirmed', handleLocationConfirmed);
                    window.addEventListener('location-confirmed', handleLocationConfirmed);
                    window.addEventListener('origin-cleared', handleLocationCleared);
                    window.addEventListener('location-cleared', handleLocationCleared);

                    window.addEventListener('toggle-sheet', () => {
                        this.toggleSheet();
                    });

                    window.addEventListener('expand-sheet', () => {
                        this.sheetExpanded = true;
                    });

                    window.addEventListener('collapse-sheet', () => {
                        this.sheetExpanded = false;
                    });

                    window.addEventListener('open-search-modal', () => {
                        this.searchModalOpen = true;
                    });

                    window.addEventListener('center-map-on-coords', (e) => {
                        if (e.detail?.lat && e.detail?.lng) {
                            this.map.setView([e.detail.lat, e.detail.lng], e.detail.zoom || 16, { animate: true });
                        }
                    });

                    window.addEventListener('start-radar-animation', (e) => {
                        this.isTrackingActive = true;
                        this.drawRadarRipple(e.detail.lat, e.detail.lng, e.detail.radiusKm || 3);
                    });

                    window.addEventListener('technician-assigned', (e) => {
                        if (e.detail?.lat && e.detail?.lng) {
                            this.renderAssignedTechnician(e.detail.lat, e.detail.lng, e.detail.name, e.detail.mobile, e.detail.rating);
                        }
                    });

                    window.addEventListener('technician-location-live-updated', (e) => {
                        if (e.detail?.lat && e.detail?.lng) {
                            const tLat = parseFloat(e.detail.lat);
                            const tLng = parseFloat(e.detail.lng);
                            if (rawAssignedTechMarker) {
                                rawAssignedTechMarker.setLatLng([tLat, tLng]);
                                const destLat = this.confirmedOrigin?.lat || (this.activeOrder ? parseFloat(this.activeOrder.lat) : null);
                                const destLng = this.confirmedOrigin?.lng || (this.activeOrder ? parseFloat(this.activeOrder.lng) : null);
                                if (destLat && destLng) {
                                    this.renderRouteToDestination(tLat, tLng, destLat, destLng);
                                }
                            } else {
                                this.renderAssignedTechnician(tLat, tLng, this.assignedTech?.name || 'تکنسین', this.assignedTech?.mobile || '');
                            }
                        }
                    });

                    window.addEventListener('active-order-resumed', (e) => {
                        const data = e.detail;
                        this.isTrackingActive = true;
                        this.isOriginConfirmed = true;
                        this.currentStep = 4;
                        if (data.lat && data.lng) {
                            this.renderConfirmedOrigin(data.lat, data.lng, 'محل اعزام تکنسین');
                            if (data.technician && data.technician.lat && data.technician.lng) {
                                this.renderAssignedTechnician(data.technician.lat, data.technician.lng, data.technician.name || 'تکنسین', data.technician.mobile || '', data.technician.rating);
                            } else {
                                this.drawRadarRipple(data.lat, data.lng, 3);
                            }
                        }
                    });

                    window.addEventListener('order-cancelled-by-customer', () => {
                        this.clearRoute();
                        if (this.radarCircleLayer) {
                            this.radarCircleLayer.clearLayers();
                        }
                    });
                },

                changeCity(cityName) {
                    if (!this.cities[cityName]) return;
                    this.currentCity = cityName;
                    const c = this.cities[cityName];
                    this.map.setView([c.lat, c.lng], c.zoom || 16, { animate: true });
                    this.selectedLocationLabel = `مازندران، ${c.name}`;
                    this.$dispatch('city-selected', cityName);
                },

                chooseSavedAddress(addr) {
                    if (!addr || !addr.lat || !addr.lng) return;
                    this.map.setView([addr.lat, addr.lng], 16, { animate: true });
                    this.selectedLocationLabel = `مازندران، ${addr.city || this.currentCity} - ${addr.title || addr.address}`;
                    if (addr.city) this.currentCity = addr.city;
                    this.addressesModalOpen = false;
                },

                performPlaceSearch() {
                    const q = this.searchQuery.trim().toLowerCase();
                    if (!q) {
                        this.searchResults = [];
                        return;
                    }

                    const samplePlaces = [
                        { name: 'میدان جهاد (اوقاف)', city: 'بابل', lat: 36.5444, lng: 52.6780 },
                        { name: 'خیابان شریعتی', city: 'بابل', lat: 36.5490, lng: 52.6820 },
                        { name: 'میدان باغ فردوس', city: 'بابل', lat: 36.5410, lng: 52.6740 },
                        { name: 'بلوار جانبازان', city: 'بابل', lat: 36.5560, lng: 52.6680 },
                        { name: 'میدان ساعت', city: 'ساری', lat: 36.5659, lng: 53.0586 },
                        { name: 'بلوار خزر', city: 'ساری', lat: 36.5750, lng: 53.0700 },
                        { name: 'میدان ۱۷ شهریور', city: 'آمل', lat: 36.4676, lng: 52.3507 },
                        { name: 'بلوار امام رضا', city: 'آمل', lat: 36.4750, lng: 52.3600 },
                    ];

                    this.searchResults = samplePlaces.filter(p => p.name.toLowerCase().includes(q) || p.city.toLowerCase().includes(q));
                },

                selectPlace(place) {
                    this.map.setView([place.lat, place.lng], 16, { animate: true });
                    this.selectedLocationLabel = `مازندران، ${place.city} - ${place.name}`;
                    this.currentCity = place.city;
                    this.searchModalOpen = false;
                    this.searchQuery = '';
                },

                async onLocationChanged(lat, lng) {
                    if (this._geocodeTimeout) {
                        clearTimeout(this._geocodeTimeout);
                    }

                    // Cancel previous in-flight fetch immediately
                    if (this._abortController) {
                        this._abortController.abort();
                        this._abortController = null;
                    }

                    // Boundary Guard: Ignore coordinates outside covered region bounds
                    const sw = this.config.bounds?.southWest || [25.0, 44.0];
                    const ne = this.config.bounds?.northEast || [39.8, 63.5];
                    if (lat < sw[0] || lat > ne[0] || lng < sw[1] || lng > ne[1]) {
                        this.selectedLocationLabel = 'موقعیت خارج از محدوده تحت پوشش خدمات';
                        this.isGeocoding = false;
                        return;
                    }

                    // In-memory instant client cache (~11m resolution: 4 decimal places)
                    const cacheKey = `${lat.toFixed(4)},${lng.toFixed(4)}`;
                    if (this._geoCache && this._geoCache[cacheKey]) {
                        const cached = this._geoCache[cacheKey];
                        this.selectedLocationLabel = cached.formatted_address;
                        if (cached.city) this.currentCity = cached.city;
                        this.isGeocoding = false;
                        return;
                    }

                    this.isGeocoding = true;

                    // Fast debounce (250ms) after drag ends: eliminates lagging & queued requests
                    this._geocodeTimeout = setTimeout(async () => {
                        this._abortController = new AbortController();
                        const signal = this._abortController.signal;

                        try {
                            const res = await fetch(`/fieldservice/api/reverse-geocode?lat=${lat}&lng=${lng}`, { signal });
                            if (res.ok) {
                                const data = await res.json();
                                if (data.formatted_address) {
                                    if (!this._geoCache) this._geoCache = {};
                                    this._geoCache[cacheKey] = data;
                                    this.selectedLocationLabel = data.formatted_address;
                                    if (data.city) {
                                        this.currentCity = data.city;
                                    }
                                }
                            }
                        } catch (e) {
                            if (e.name === 'AbortError') {
                                return; // Quietly ignore aborted request
                            }
                            if (!this.selectedLocationLabel || this.selectedLocationLabel.includes('در حال')) {
                                this.selectedLocationLabel = `مازندران، ${this.currentCity}`;
                            }
                        } finally {
                            this.isGeocoding = false;
                        }
                    }, 250);
                },

                locateMe() {
                    if (!navigator.geolocation) {
                        alert('مرورگر شما از قابلیت مکان‌یابی پشتیبانی نمی‌کند.');
                        return;
                    }

                    this.isLocating = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.isLocating = false;
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;
                            this.map.setView([lat, lng], 16, { animate: true });
                        },
                        (err) => {
                            this.isLocating = false;
                            alert('دسترسی به موقعیت مکانی داده نشد یا GPS خاموش است.');
                        },
                        { enableHighAccuracy: true, timeout: 8000 }
                    );
                },

                zoomIn() {
                    if (this.map) this.map.zoomIn();
                },

                zoomOut() {
                    if (this.map) this.map.zoomOut();
                },

                toggleTheme() {
                    this.isDark = !this.isDark;
                    document.documentElement.classList.toggle('dark', this.isDark);
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                },

                renderNearbyTechnicians() {
                    if (!this.techMarkersLayer) return;
                    this.techMarkersLayer.clearLayers();

                    this.technicians.forEach(tech => {
                        let lat = tech.lat;
                        let lng = tech.lng;

                        if (!lat || !lng) {
                            lat = 36.5444 + (Math.random() - 0.5) * 0.04;
                            lng = 52.6780 + (Math.random() - 0.5) * 0.04;
                        }

                        const isBusy = !!tech.is_busy;
                        const pinBg = isBusy ? '#475569' : '#4f46e5';

                        const techIcon = L.divIcon({
                            className: 'custom-nearby-tech',
                            html: `
                                <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                                    <div style="background-color: ${pinBg}; width: 30px; height: 30px; border-radius: 50%; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white;">
                                        <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                                            <circle cx="7" cy="17" r="2"></circle>
                                            <path d="M9 17h6"></path>
                                            <circle cx="17" cy="17" r="2"></circle>
                                        </svg>
                                    </div>
                                    <div style="position: absolute; bottom: -3px; width: 6px; height: 6px; background: ${isBusy ? '#94a3b8' : '#10b981'}; border-radius: 50%; border: 1.5px solid white;"></div>
                                </div>
                            `,
                            iconSize: [30, 30],
                            iconAnchor: [15, 15],
                        });

                        const marker = L.marker([lat, lng], { icon: techIcon }).addTo(this.techMarkersLayer);
                        marker.bindPopup(`
                            <div style="direction: rtl; text-align: right; font-family: IRANYekanX, sans-serif; padding: 4px; min-width: 160px;">
                                <div style="font-weight: 900; font-size: 13px; color: #0f172a;">${tech.first_name} ${tech.last_name}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">تکنسین رسمی در ${tech.city || 'مازندران'}</div>
                                <div style="margin-top: 6px; display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: bold; background: ${isBusy ? '#f1f5f9; color: #475569;' : '#e0e7ff; color: #4338ca;'}">
                                    ${isBusy ? 'در حال ماموریت' : 'آماده اعزام فوری'}
                                </div>
                            </div>
                        `);
                    });
                },

                calculateHaversineDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371; // km
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    // 1.25 urban road detour factor
                    return Math.round(R * c * 1.25 * 10) / 10;
                },

                calculateETA(distanceKm) {
                    if (!distanceKm || distanceKm <= 0) return 3;
                    return Math.max(2, Math.round((distanceKm / 25) * 60));
                },

                _lastRoutedOrigin: null,
                _lastRoutedDest: null,

                async renderRouteToDestination(techLat, techLng, destLat, destLng) {
                    if (!rawMap) return;

                    const pTechLat = parseFloat(techLat);
                    const pTechLng = parseFloat(techLng);
                    const pDestLat = parseFloat(destLat);
                    const pDestLng = parseFloat(destLng);

                    const originKey = `${pTechLat.toFixed(5)},${pTechLng.toFixed(5)}`;
                    const destKey = `${pDestLat.toFixed(5)},${pDestLng.toFixed(5)}`;

                    // If already drawn for these exact coordinates, skip redundant fetch
                    if (rawRoutePolyline && this._lastRoutedOrigin === originKey && this._lastRoutedDest === destKey) {
                        return;
                    }
                    this._lastRoutedOrigin = originKey;
                    this._lastRoutedDest = destKey;

                    let dist = this.calculateHaversineDistance(pTechLat, pTechLng, pDestLat, pDestLng);
                    let eta = this.calculateETA(dist);
                    let routeCoords = [[pTechLat, pTechLng], [pDestLat, pDestLng]];

                    // Request real street/road navigation route from Routing Service
                    try {
                        const url = `{{ route('fieldservice.api.route') }}?origin_lat=${pTechLat}&origin_lng=${pTechLng}&dest_lat=${pDestLat}&dest_lng=${pDestLng}`;
                        const response = await fetch(url);
                        if (response.ok) {
                            const data = await response.json();
                            if (data.coordinates && data.coordinates.length > 1) {
                                routeCoords = data.coordinates;
                                if (data.distance_km) dist = data.distance_km;
                                if (data.eta_minutes) eta = data.eta_minutes;
                            }
                        }
                    } catch (err) {
                        console.warn('Road routing fallback to direct line:', err);
                    }

                    if (rawRouteGlowPolyline) {
                        try { rawMap.removeLayer(rawRouteGlowPolyline); } catch (e) {}
                        rawRouteGlowPolyline = null;
                    }
                    if (rawRoutePolyline) {
                        try { rawMap.removeLayer(rawRoutePolyline); } catch (e) {}
                        rawRoutePolyline = null;
                    }

                    this.distanceKm = dist;
                    this.etaMinutes = eta;
                    this.etaText = `حدود ${eta} دقیقه (${dist} کیلومتر)`;

                    // Glowing underlayer polyline following the road contours
                    rawRouteGlowPolyline = L.polyline(routeCoords, {
                        color: '#4f46e5',
                        weight: 8,
                        opacity: 0.35,
                        lineCap: 'round',
                        lineJoin: 'round',
                    }).addTo(rawMap);

                    // Vibrant high-contrast street route polyline
                    rawRoutePolyline = L.polyline(routeCoords, {
                        color: '#6366f1',
                        weight: 4.5,
                        opacity: 0.95,
                        lineCap: 'round',
                        lineJoin: 'round',
                    }).addTo(rawMap);

                    try {
                        const bounds = L.latLngBounds(routeCoords);
                        rawMap.fitBounds(bounds, { padding: [85, 85], maxZoom: 16 });
                    } catch (e) {}
                },

                clearRoute() {
                    this._lastRoutedOrigin = null;
                    this._lastRoutedDest = null;
                    if (rawRouteGlowPolyline && rawMap) {
                        try { rawMap.removeLayer(rawRouteGlowPolyline); } catch (e) {}
                        rawRouteGlowPolyline = null;
                    }
                    if (rawRoutePolyline && rawMap) {
                        try { rawMap.removeLayer(rawRoutePolyline); } catch (e) {}
                        rawRoutePolyline = null;
                    }
                    if (rawAssignedTechMarker && rawMap) {
                        try { rawMap.removeLayer(rawAssignedTechMarker); } catch (e) {}
                        rawAssignedTechMarker = null;
                    }
                    this.distanceKm = null;
                    this.etaMinutes = null;
                    this.etaText = '';
                    this.assignedTech = null;
                },

                drawRadarRipple(lat, lng, radiusKm) {
                    if (!this.radarCircleLayer) return;
                    this.radarCircleLayer.clearLayers();

                    // Main Search Zone Circle
                    L.circle([lat, lng], {
                        radius: radiusKm * 1000,
                        color: '#2563eb',
                        fillColor: '#3b82f6',
                        fillOpacity: 0.12,
                        weight: 2,
                        dashArray: '6, 8',
                    }).addTo(this.radarCircleLayer);

                    // Client Location Marker
                    const clientIcon = L.divIcon({
                        className: 'client-radar-origin',
                        html: `
                            <div style="position: relative; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                <div style="width: 18px; height: 18px; background: #2563eb; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 16px #2563eb;"></div>
                                <div class="radar-wave-ring" style="width: 80px; height: 80px; top: -28px; left: -28px;"></div>
                            </div>
                        `,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12],
                    });

                    L.marker([lat, lng], { icon: clientIcon }).addTo(this.radarCircleLayer);
                },

                renderAssignedTechnician(lat, lng, name, mobile, rating = null) {
                    if (!rawMap) return;

                    this.assignedTech = {
                        name: name || 'تکنسین',
                        mobile: mobile || '',
                        rating: rating || this.assignedTech?.rating || '۴.۹۵',
                        lat: lat,
                        lng: lng,
                    };

                    if (rawAssignedTechMarker) {
                        try {
                            rawMap.removeLayer(rawAssignedTechMarker);
                        } catch (e) {}
                    }

                    // Clear search radar ripples once technician is assigned
                    if (this.radarCircleLayer) {
                        try { this.radarCircleLayer.clearLayers(); } catch (e) {}
                    }

                    const techCarIcon = L.divIcon({
                        className: 'assigned-tech-moving',
                        html: `
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center; filter: drop-shadow(0 6px 16px rgba(79,70,229,0.45));">
                                <div style="background-color: #4f46e5; width: 40px; height: 40px; border-radius: 50%; border: 2.5px solid white; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 0 14px rgba(79,70,229,0.5);">
                                    <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                                        <circle cx="7" cy="17" r="2"></circle>
                                        <path d="M9 17h6"></path>
                                        <circle cx="17" cy="17" r="2"></circle>
                                    </svg>
                                </div>
                                <div style="background: rgba(15,23,42,0.92); backdrop-filter: blur(6px); color: white; padding: 2.5px 8px; border-radius: 9999px; font-size: 10px; font-weight: 800; font-family: IRANYekanX, sans-serif; margin-top: 4px; white-space: nowrap; border: 1px solid rgba(255,255,255,0.18);">
                                    ${name} • در مسیر
                                </div>
                            </div>
                        `,
                        iconSize: [40, 60],
                        iconAnchor: [20, 20],
                    });

                    rawAssignedTechMarker = L.marker([lat, lng], { icon: techCarIcon, zIndexOffset: 900 }).addTo(rawMap);
                    rawAssignedTechMarker.bindPopup(`
                        <div style="direction: rtl; text-align: right; font-family: IRANYekanX, sans-serif; padding: 4px;">
                            <b style="color: #4f46e5;">${name}</b><br>
                            <span style="font-size: 11px;">تلفن: ${mobile}</span><br>
                            <span style="color: #059669; font-weight: bold; font-size: 11px;">در حال حرکت به سمت شما</span>
                        </div>
                    `);

                    // Connect route if destination exists
                    const destLat = this.confirmedOrigin?.lat || (this.activeOrder ? parseFloat(this.activeOrder.lat) : null);
                    const destLng = this.confirmedOrigin?.lng || (this.activeOrder ? parseFloat(this.activeOrder.lng) : null);
                    if (destLat && destLng) {
                        this.renderRouteToDestination(lat, lng, destLat, destLng);
                    }
                },

                renderConfirmedOrigin(lat, lng, locationText) {
                    if (!this.map) return;
                    if (this.originMarker) {
                        this.map.removeLayer(this.originMarker);
                        this.originMarker = null;
                    }

                    const originIcon = L.divIcon({
                        className: 'confirmed-origin-marker',
                        html: `
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3)); pointer-events: auto; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #00a651 0%, #059669 100%); color: #ffffff; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 900; font-family: IRANYekanX, sans-serif; white-space: nowrap; border: 2px solid #ffffff; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,166,81,0.4);">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #ffffff; display: inline-block; box-shadow: 0 0 6px rgba(255,255,255,0.9);"></span>
                                    <span>محل اعزام</span>
                                </div>
                                <div style="width: 2px; height: 10px; background: #00a651;"></div>
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #00a651; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.35);"></div>
                            </div>
                        `,
                        iconSize: [80, 50],
                        iconAnchor: [40, 50],
                    });

                    this.originMarker = L.marker([lat, lng], { icon: originIcon, zIndexOffset: 1000 }).addTo(this.map);
                    this.originMarker.bindPopup(`
                        <div style="direction: rtl; text-align: right; font-family: IRANYekanX, sans-serif; padding: 4px; min-width: 170px;">
                            <div style="font-weight: 900; font-size: 12px; color: #00a651; margin-bottom: 2px;">محل تأیید شده اعزام</div>
                            <div style="font-size: 11px; color: #1e293b; font-weight: 700;">${locationText || 'موقعیت شما روی نقشه'}</div>
                        </div>
                    `);

                    this.map.setView([lat, lng], Math.max(this.map.getZoom(), 15), { animate: true });
                }
            }));
        });
    </script>

    @livewireScripts
</body>
</html>
