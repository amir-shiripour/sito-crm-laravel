<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>سامانه هوشمند سفیران و تکنسین‌های نصاب‌باشی | کنسول اعزام</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

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
                <div class="w-8 h-8 rounded-full bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-sm font-black text-indigo-300 shrink-0">
                    👷‍♂️
                </div>
                <div class="hidden sm:block min-w-0">
                    <div class="text-xs font-black text-white truncate flex items-center gap-1.5">
                        <span>{{ $technician->full_name }}</span>
                        <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-amber-500/15 text-amber-300 border border-amber-500/25">
                            {{ $technician->tier?->label() ?: 'تکنسین برتر' }}
                        </span>
                    </div>
                    <div class="text-[10px] text-amber-400 font-bold flex items-center gap-1 mt-0.5">
                        <span>★ {{ $todayStats['rating'] }}</span>
                        <span class="text-slate-500">•</span>
                        <span class="text-slate-400">{{ $technician->completed_orders_count }} خدمت</span>
                    </div>
                </div>
            </button>
            @else
            <button type="button" @click="$dispatch('open-technician-auth')"
                    class="pointer-events-auto px-3.5 py-2 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-xl flex items-center gap-2 transition-all active:scale-95 shrink-0">
                <span>👷‍♂️</span>
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
                           class="flex items-center gap-2 p-2 rounded-xl hover:bg-slate-800 text-xs text-slate-200 transition">
                            <span>👤</span>
                            <span>پرتال مشتریان</span>
                        </a>
                        <a href="{{ route('user.fieldservice.dashboard') }}"
                           class="flex items-center gap-2 p-2 rounded-xl hover:bg-slate-800 text-xs text-slate-200 transition">
                            <span>⚙️</span>
                            <span>پنل مدیریت CRM</span>
                        </a>
                        <a href="{{ route('fieldservice.b2b') }}"
                           class="flex items-center gap-2 p-2 rounded-xl hover:bg-slate-800 text-xs text-slate-200 transition">
                            <span>🏢</span>
                            <span>پرتال مدیران ساختمان</span>
                        </a>

                        @if($technician)
                        <div class="border-t border-slate-800 my-1 pt-1">
                            <a href="{{ route('fieldservice.technician.logout') }}"
                               class="flex items-center gap-2 p-2 rounded-xl hover:bg-rose-500/20 text-xs text-rose-300 transition font-bold">
                                <span>🚪</span>
                                <span>خروج از حساب تکنسین</span>
                            </a>
                        </div>
                        @else
                        <div class="border-t border-slate-800 my-1 pt-1">
                            <button type="button" @click="$dispatch('open-technician-auth'); switcherOpen = false;"
                                    class="w-full flex items-center gap-2 p-2 rounded-xl hover:bg-indigo-600 text-xs text-indigo-300 hover:text-white transition font-bold text-right">
                                <span>🔑</span>
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
                 'h-[82px] max-h-[82px] lg:h-auto lg:max-h-full': !sheetExpanded
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

                {{-- Compact Peek Info --}}
                <div class="flex items-center justify-between gap-3 pt-1">
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if($activeMission)
                        <span class="w-3 h-3 rounded-full bg-indigo-500 animate-pulse shrink-0"></span>
                        <div class="min-w-0">
                            <div class="text-xs font-black text-white truncate">
                                ماموریت فعال: {{ $activeMission->category?->name ?: $activeMission->customer_name }}
                            </div>
                            <div class="text-[10px] text-indigo-300 truncate font-bold">
                                {{ $activeMission->status->label() }} • {{ $activeMission->city }}
                            </div>
                        </div>
                        @elseif($technician)
                        <span class="w-3 h-3 rounded-full {{ $technician->is_online ? 'bg-emerald-400 animate-ping' : 'bg-slate-600' }} shrink-0"></span>
                        <div class="min-w-0">
                            <div class="text-xs font-black text-white truncate">
                                {{ $technician->is_online ? 'رادار سفارش‌های مازندران فعال است' : 'شما در وضعیت آفلاین هستید' }}
                            </div>
                            <div class="text-[10px] text-slate-400 truncate">
                                {{ $technician->is_online ? ($availableMissions->count() . ' سفارش در انتظار بررسی') : 'جهت دریافت کار آنلاین شوید' }}
                            </div>
                        </div>
                        @else
                        <span class="w-3 h-3 rounded-full bg-amber-400 animate-ping shrink-0"></span>
                        <div class="min-w-0">
                            <div class="text-xs font-black text-white truncate">
                                نیاز به ورود به حساب کاربری
                            </div>
                            <div class="text-[10px] text-amber-300 truncate font-bold">
                                برای دریافت سرویس وارد شوید
                            </div>
                        </div>
                        @endif
                    </div>

                    <button type="button" @click.stop="toggleSheet()"
                            class="shrink-0 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1">
                        <span x-text="sheetExpanded ? 'بستن' : 'مشاهده'"></span>
                        <span class="text-xs" x-text="sheetExpanded ? '⌄' : '⌃'"></span>
                    </button>
                </div>
            </div>

            {{-- Full Content Area: Livewire Mission Feed --}}
            <div :class="!sheetExpanded ? 'hidden lg:flex lg:flex-col lg:flex-1 lg:min-h-0' : 'flex flex-col flex-1 min-h-0'"
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
                        نصاب‌باشی فیلد سرویس
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
                        📊
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
                    <div class="text-sm font-black text-amber-400">★ {{ $todayStats['rating'] }} <span class="text-[10px] text-slate-500">از ۵</span></div>
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
                   class="w-full py-2.5 bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 font-bold text-xs rounded-2xl transition flex items-center justify-center gap-1.5">
                    <span>🚪</span>
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
                        attribution: '&copy; نصاب‌باشی | ' + (this.config.name || 'GIS'),
                    }).addTo(rawTechMap);

                    rawMissionsLayer = L.layerGroup().addTo(rawTechMap);

                    // Render technician marker
                    if (this.technician) {
                        this.renderTechnicianMarker(techLat, techLng);
                    }

                    // Render active mission or available radar missions
                    if (this.activeMission && this.activeMission.lat && this.activeMission.lng) {
                        this.renderActiveMissionRoute(this.activeMission.lat, this.activeMission.lng, this.activeMission.customer_name);
                    } else if (this.availableMissions && this.availableMissions.length > 0) {
                        this.renderAvailableMissionsMarkers();
                    }

                    // Window Events from Livewire
                    window.addEventListener('mission-accepted', (e) => {
                        if (e.detail?.lat && e.detail?.lng) {
                            this.clearMissionFocus();
                            this.renderActiveMissionRoute(e.detail.lat, e.detail.lng, e.detail.customer || 'مشتری');
                            this.sheetExpanded = true;
                        }
                    });

                    window.addEventListener('mission-route-started', (e) => {
                        if (e.detail?.lat && e.detail?.lng) {
                            this.centerOnRoute(e.detail.lat, e.detail.lng);
                        }
                    });

                    window.addEventListener('mission-completed', () => {
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
                                <div style="background: ${isOnline ? '#10b981' : '#64748b'}; width: 28px; height: 28px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 14px rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; position: relative; z-index: 2;">
                                    👷‍♂️
                                </div>
                            </div>
                        `,
                        iconSize: [44, 44],
                        iconAnchor: [22, 22],
                    });

                    rawTechMarker = L.marker([lat, lng], { icon: techIcon }).addTo(rawTechMap);
                },

                renderActiveMissionRoute(destLat, destLng, customerName) {
                    if (!rawTechMap) return;
                    this.clearActiveMissionRoute();

                    const techLat = (this.technician && this.technician.lat) ? this.technician.lat : 36.5444;
                    const techLng = (this.technician && this.technician.lng) ? this.technician.lng : 52.6780;

                    // Customer destination pin
                    const customerIcon = L.divIcon({
                        className: 'customer-destination-pin',
                        html: `
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center;">
                                <div style="background: #ef4444; color: white; border-radius: 50%; width: 34px; height: 34px; border: 3px solid white; box-shadow: 0 6px 18px rgba(239,68,68,0.5); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                    📍
                                </div>
                            </div>
                        `,
                        iconSize: [34, 34],
                        iconAnchor: [17, 34],
                    });

                    rawCustomerMarker = L.marker([destLat, destLng], { icon: customerIcon })
                        .addTo(rawTechMap)
                        .bindPopup(`<b style="font-family: IRANYekanX, sans-serif; font-size: 12px;">محل مشتری: ${customerName || 'کارفرما'}</b>`);

                    // Connecting Route Polyline
                    rawRoutePolyline = L.polyline([[techLat, techLng], [destLat, destLng]], {
                        color: '#6366f1',
                        weight: 4,
                        opacity: 0.85,
                        dashArray: '8, 8',
                    }).addTo(rawTechMap);

                    this.centerOnRoute(destLat, destLng);
                },

                clearActiveMissionRoute() {
                    if (!rawTechMap) return;
                    if (rawCustomerMarker) {
                        rawTechMap.removeLayer(rawCustomerMarker);
                        rawCustomerMarker = null;
                    }
                    if (rawRoutePolyline) {
                        rawTechMap.removeLayer(rawRoutePolyline);
                        rawRoutePolyline = null;
                    }
                },

                renderAvailableMissionsMarkers() {
                    if (!rawMissionsLayer) return;
                    rawMissionsLayer.clearLayers();
                    rawMissionMarkersMap = {};

                    if (!this.technician || !this.technician.is_online) return;

                    this.availableMissions.forEach((m) => {
                        if (!m.lat || !m.lng) return;

                        const pinIcon = L.divIcon({
                            className: 'available-job-pin',
                            html: `
                                <div style="position: relative; display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                    <div style="background: #4f46e5; width: 32px; height: 32px; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 4px 14px rgba(79,70,229,0.5); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; transition: transform 0.2s;" class="hover:scale-110 active:scale-95">
                                        ${m.is_emergency ? '⚡' : '🛠️'}
                                    </div>
                                </div>
                            `,
                            iconSize: [32, 32],
                            iconAnchor: [16, 16],
                        });

                        const mark = L.marker([m.lat, m.lng], { icon: pinIcon }).addTo(rawMissionsLayer);
                        mark.bindTooltip(`<div style="font-family: IRANYekanX, sans-serif; font-size: 11px; padding: 2px 4px; text-align: center;"><b>${m.category?.name || 'سفارش'}</b><div style="color: #6366f1; font-weight: bold; margin-top: 2px;">#${m.order_number}</div></div>`, {
                            direction: 'top',
                            offset: [0, -12]
                        });

                        rawMissionMarkersMap[m.id] = mark;

                        mark.on('click', () => {
                            this.sheetExpanded = true;
                            // 1. Focus map and draw preview route line
                            this.focusOnMission(m.id, m.lat, m.lng, m.category?.name || 'سفارش');
                            // 2. Dispatch to Livewire to open order details modal and select card
                            if (window.Livewire) {
                                window.Livewire.dispatch('order-pin-clicked', { orderId: m.id });
                            }
                            // 3. Scroll to card in list
                            setTimeout(() => {
                                const card = document.getElementById('mission-card-' + m.id);
                                if (card) {
                                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            }, 100);
                        });
                    });
                },

                focusOnMission(orderId, lat, lng, title) {
                    if (!rawTechMap) return;

                    // Clear previous preview line and focus ring
                    this.clearMissionFocus();

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
