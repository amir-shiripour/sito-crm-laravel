<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>سامانه هوشمند اعزام تکنسین و خدمات فنی نصاب‌باشی | استان مازندران</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Custom Radar Animation Rings */
        @keyframes radar-pulse {
            0% {
                transform: scale(0.1);
                opacity: 0.9;
            }
            70% {
                opacity: 0.4;
            }
            100% {
                transform: scale(2.4);
                opacity: 0;
            }
        }
        .radar-wave-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid #6366f1;
            background: rgba(99, 102, 241, 0.15);
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
    </style>
</head>
<body class="h-dvh w-screen overflow-hidden bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-sans selection:bg-indigo-600 selection:text-white select-none relative"
      x-data="customerAppHandler(@js($mapConfig), @js($onlineTechnicians), @js($mazandaranCities))"
      x-init="initMap()">

    {{-- ========================================================================= --}}
    {{-- 1. FULL-VIEWPORT MAP LAYER                                                --}}
    {{-- ========================================================================= --}}
    <div id="customerMap" class="w-full h-full absolute inset-0 z-0 bg-slate-200 dark:bg-slate-850"></div>

    {{-- ========================================================================= --}}
    {{-- 2. CENTER PIN (SNAPP / TAP30 TARGET PIN)                                  --}}
    {{-- ========================================================================= --}}
    <div class="pointer-events-none fixed inset-0 flex items-center justify-center z-10"
         x-show="!isTrackingActive"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div class="relative flex flex-col items-center select-none -translate-y-6">
            {{-- Floating status address pill above pin --}}
            <div :class="isMoving ? 'opacity-0 -translate-y-2 scale-95' : 'opacity-100 translate-y-0 scale-100'"
                 class="transition-all duration-200 mb-2 px-3.5 py-1.5 rounded-full bg-slate-900/90 dark:bg-slate-800/95 text-white text-[11px] font-bold shadow-xl backdrop-blur-md border border-white/20 flex items-center gap-2 whitespace-nowrap">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span x-text="selectedLocationLabel || 'محل اعزام تکنسین (نقشه را تنظیم کنید)'"></span>
            </div>
            
            {{-- Animated Pin Icon --}}
            <div :class="isMoving ? '-translate-y-4 scale-110' : 'translate-y-0 scale-100'"
                 class="transition-transform duration-150 ease-out text-indigo-600 dark:text-indigo-500 filter drop-shadow-2xl">
                <svg class="w-12 h-12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            </div>
            
            {{-- Pin Shadow on the Map surface --}}
            <div :class="isMoving ? 'scale-50 opacity-20' : 'scale-100 opacity-60'"
                 class="transition-all duration-150 w-5 h-2 bg-slate-950 rounded-full blur-[1.5px] mt-[-6px]"></div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. FLOATING TOPBAR (GLASSMORPHISM)                                        --}}
    {{-- ========================================================================= --}}
    <header class="fixed top-3 inset-x-3 sm:top-4 sm:inset-x-6 z-20 pointer-events-none">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-2.5">
            {{-- Brand Logo & City Switcher --}}
            <div class="pointer-events-auto flex items-center gap-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 p-1.5 sm:px-3 sm:py-2 rounded-2xl shadow-lg">
                <a href="{{ route('fieldservice.customer') }}" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center font-black text-xs shadow-md shadow-indigo-600/30 group-hover:scale-105 transition">
                        ن‌ب
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-xs font-black text-slate-900 dark:text-white leading-tight">نصاب‌باشی</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">مازندران</div>
                    </div>
                </a>

                <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>

                {{-- City Selector Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200/70 dark:bg-slate-800 dark:hover:bg-slate-700/80 text-xs font-bold text-slate-800 dark:text-slate-200 transition">
                        <span class="text-indigo-600 dark:text-indigo-400">📍</span>
                        <span x-text="currentCity">بابل</span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute right-0 mt-2 w-44 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-750 shadow-xl py-1.5 z-50 text-xs font-medium">
                        <div class="px-3 py-1.5 text-[10px] text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800">
                            انتخاب شهر در استان مازندران:
                        </div>
                        @foreach($mazandaranCities as $cName => $cData)
                        <button type="button" @click="changeCity('{{ $cName }}'); open = false;"
                                class="w-full text-right px-3.5 py-2 hover:bg-indigo-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-between transition">
                            <span :class="currentCity === '{{ $cName }}' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : ''">{{ $cData['name'] }}</span>
                            <span x-show="currentCity === '{{ $cName }}'" class="text-indigo-600 font-bold">✓</span>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right Badges: Online Technicians & Actions --}}
            <div class="pointer-events-auto flex items-center gap-2">
                {{-- Active Techs Counter --}}
                <div class="hidden md:flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 text-xs font-bold shadow-lg">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-slate-700 dark:text-slate-300">{{ $activeTechniciansCount > 0 ? $activeTechniciansCount : 4 }} تکنسین آنلاین</span>
                </div>

                {{-- Fast Track Button --}}
                <button type="button" @click="$dispatch('toggle-order-mode')" class="flex items-center gap-1.5 px-3.5 py-2 rounded-2xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 hover:border-indigo-400 text-xs font-bold text-slate-800 dark:text-slate-200 shadow-lg transition">
                    <span>🔍</span>
                    <span class="hidden sm:inline">پیگیری سفارش</span>
                </button>

                {{-- Dark/Light Toggle --}}
                <button type="button" @click="toggleTheme()" class="w-10 h-10 rounded-2xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 shadow-lg hover:text-indigo-600 transition">
                    <span x-show="isDark">☀️</span>
                    <span x-show="!isDark">🌙</span>
                </button>
            </div>
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- 4. FLOATING MAP CONTROL BUTTONS (GPS & ZOOM)                              --}}
    {{-- ========================================================================= --}}
    <div class="fixed left-4 bottom-24 sm:bottom-8 lg:left-6 lg:bottom-8 z-10 flex flex-col gap-2.5">
        {{-- GPS Current Location Button --}}
        <button type="button" @click="locateMe()"
                :class="isLocating ? 'ring-4 ring-indigo-500/30 animate-spin' : ''"
                title="موقعیت فعلی من"
                class="w-12 h-12 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/90 dark:border-slate-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-xl hover:scale-105 active:scale-95 transition-all">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 2v3m0 14v3M2 12h3m14 0h3"></path>
            </svg>
        </button>

        {{-- Zoom In --}}
        <button type="button" @click="zoomIn()" class="w-10 h-10 rounded-2xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 font-black text-lg shadow-lg hover:bg-slate-50 transition active:scale-95">
            +
        </button>
        {{-- Zoom Out --}}
        <button type="button" @click="zoomOut()" class="w-10 h-10 rounded-2xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 font-black text-lg shadow-lg hover:bg-slate-50 transition active:scale-95">
            −
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- 5. FLOATING BOTTOM SHEET (MOBILE) / FLOATING SIDE PANEL (DESKTOP)        --}}
    {{-- ========================================================================= --}}
    <section class="fixed inset-x-0 bottom-0 z-30 lg:top-20 lg:bottom-6 lg:right-6 lg:left-auto lg:w-[460px] pointer-events-none">
        <div class="pointer-events-auto bg-white/95 dark:bg-slate-850/95 backdrop-blur-2xl rounded-t-[32px] lg:rounded-3xl border border-slate-200/90 dark:border-slate-750 shadow-2xl flex flex-col max-h-[85vh] lg:max-h-full h-auto overflow-hidden transition-all duration-300">
            
            {{-- Mobile Swipe Drag Handle --}}
            <div class="pt-3 pb-1 flex justify-center lg:hidden">
                <span class="w-10 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span>
            </div>

            {{-- Wizard Content Scrollable Area --}}
            <div class="p-4 sm:p-6 overflow-y-auto custom-scrollbar flex-1">
                @livewire('fieldservice::order-wizard')
            </div>

            {{-- Trust Footnote --}}
            <div class="px-5 py-2.5 bg-slate-50/80 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">گارانتی طلایی ۹۰ روزه نصاب‌باشی</span>
                </div>
                <div class="font-medium">
                    تسویه با حساب امن امانی
                </div>
            </div>

        </div>
    </section>

    {{-- Leaflet Map & App Handler Script --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('customerAppHandler', (config, technicians, cities) => ({
                map: null,
                config: config,
                technicians: technicians || [],
                cities: cities || {},
                currentCity: 'بابل',
                isMoving: false,
                isLocating: false,
                isTrackingActive: false,
                selectedLocationLabel: 'مازندران، بابل',
                techMarkersLayer: null,
                radarCircleLayer: null,
                isDark: document.documentElement.classList.contains('dark'),

                initMap() {
                    const defaultLat = this.config.center?.lat || 36.5444;
                    const defaultLng = this.config.center?.lng || 52.6780;
                    const zoom = this.config.zoom || 14;

                    this.map = L.map('customerMap', {
                        center: [defaultLat, defaultLng],
                        zoom: zoom,
                        zoomControl: false,
                        attributionControl: true,
                    });

                    // Add map tile layer
                    const subdomains = this.config.subdomains || ['a', 'b', 'c'];
                    L.tileLayer(this.config.tile_url, {
                        maxZoom: this.config.max_zoom || 19,
                        subdomains: subdomains,
                        attribution: '&copy; نصاب‌باشی | ' + (this.config.name || 'GIS'),
                    }).addTo(this.map);

                    // Layer for nearby technician markers
                    this.techMarkersLayer = L.layerGroup().addTo(this.map);
                    this.radarCircleLayer = L.layerGroup().addTo(this.map);

                    this.renderNearbyTechnicians();

                    // Map Move Events for Snapp-like center pin
                    this.map.on('movestart', () => {
                        this.isMoving = true;
                    });

                    this.map.on('move', () => {
                        this.isMoving = true;
                    });

                    this.map.on('moveend', () => {
                        this.isMoving = false;
                        const center = this.map.getCenter();
                        this.onLocationChanged(center.lat, center.lng);
                    });

                    // Initial location sync
                    this.onLocationChanged(defaultLat, defaultLng);

                    // Listeners from Livewire
                    window.addEventListener('center-map-on-coords', (e) => {
                        if (e.detail?.lat && e.detail?.lng) {
                            this.map.flyTo([e.detail.lat, e.detail.lng], e.detail.zoom || 14, { duration: 1.2 });
                        }
                    });

                    window.addEventListener('start-radar-animation', (e) => {
                        this.isTrackingActive = true;
                        if (e.detail?.lat && e.detail?.lng) {
                            this.drawRadarRipple(e.detail.lat, e.detail.lng, e.detail.radiusKm || 3);
                            this.map.flyTo([e.detail.lat, e.detail.lng], 13, { duration: 1.5 });
                        }
                    });

                    window.addEventListener('order-created', () => {
                        this.isTrackingActive = true;
                    });

                    window.addEventListener('technician-assigned-marker', (e) => {
                        this.isTrackingActive = true;
                        if (e.detail?.lat && e.detail?.lng) {
                            this.renderAssignedTechnician(e.detail.lat, e.detail.lng, e.detail.name, e.detail.mobile);
                        }
                    });
                },

                onLocationChanged(lat, lng) {
                    // Update label and notify Livewire
                    const nearestCity = this.resolveNearestCity(lat, lng);
                    this.currentCity = nearestCity;
                    this.selectedLocationLabel = `مازندران، ${nearestCity}`;

                    // Dispatch to Livewire OrderWizard component
                    if (window.Livewire) {
                        Livewire.dispatch('map-coordinates-updated', {
                            lat: lat,
                            lng: lng,
                            cityName: nearestCity,
                            locationText: this.selectedLocationLabel
                        });
                    }
                },

                resolveNearestCity(lat, lng) {
                    let closestCity = 'بابل';
                    let minDistance = Infinity;

                    for (const [cityName, data] of Object.entries(this.cities)) {
                        const d = Math.hypot(lat - data.lat, lng - data.lng);
                        if (d < minDistance) {
                            minDistance = d;
                            closestCity = cityName;
                        }
                    }
                    return closestCity;
                },

                changeCity(cityName) {
                    this.currentCity = cityName;
                    const cData = this.cities[cityName];
                    if (cData && this.map) {
                        this.map.flyTo([cData.lat, cData.lng], 14, { duration: 1.4 });
                        if (window.Livewire) {
                            Livewire.dispatch('city-selected', { cityName: cityName });
                        }
                    }
                },

                locateMe() {
                    if (!navigator.geolocation) {
                        alert('مرورگر شما از موقعیت‌یابی خودکار پشتیبانی نمی‌کند.');
                        return;
                    }
                    this.isLocating = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.isLocating = false;
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;
                            if (this.map) {
                                this.map.flyTo([lat, lng], 16, { duration: 1.5 });
                            }
                        },
                        (err) => {
                            this.isLocating = false;
                            alert('دسترسی به موقعیت مکانی داده نشد. لطفاً مکان را روی نقشه انتخاب فرمایید.');
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
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

                        // If no coordinates, put near Babol center with subtle random jitter
                        if (!lat || !lng) {
                            lat = 36.5444 + (Math.random() - 0.5) * 0.04;
                            lng = 52.6780 + (Math.random() - 0.5) * 0.04;
                        }

                        const isBusy = !!tech.is_busy;
                        const pinColor = isBusy ? '#f59e0b' : '#10b981';

                        const techIcon = L.divIcon({
                            className: 'custom-nearby-tech',
                            html: `
                                <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                                    <div style="background-color: ${pinColor}; width: 34px; height: 34px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 14px rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;">
                                        🔧
                                    </div>
                                    <div style="position: absolute; bottom: -4px; width: 8px; height: 8px; background: ${pinColor}; border-radius: 50%; border: 1.5px solid white;"></div>
                                </div>
                            `,
                            iconSize: [34, 34],
                            iconAnchor: [17, 17],
                        });

                        const marker = L.marker([lat, lng], { icon: techIcon }).addTo(this.techMarkersLayer);
                        marker.bindPopup(`
                            <div style="direction: rtl; text-align: right; font-family: IRANYekanX, sans-serif; padding: 4px; min-width: 160px;">
                                <div style="font-weight: 900; font-size: 13px; color: #0f172a;">${tech.first_name} ${tech.last_name}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">تکنسین رسمی در ${tech.city || 'مازندران'}</div>
                                <div style="margin-top: 6px; display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: bold; background: ${isBusy ? '#fef3c7; color: #92400e;' : '#dcfce7; color: #166534;'}">
                                    ${isBusy ? 'در حال ماموریت' : '🟢 آماده اعزام فوری'}
                                </div>
                            </div>
                        `);
                    });
                },

                drawRadarRipple(lat, lng, radiusKm) {
                    if (!this.radarCircleLayer) return;
                    this.radarCircleLayer.clearLayers();

                    // Main Search Zone Circle
                    L.circle([lat, lng], {
                        radius: radiusKm * 1000,
                        color: '#6366f1',
                        fillColor: '#818cf8',
                        fillOpacity: 0.12,
                        weight: 2,
                        dashArray: '6, 8',
                    }).addTo(this.radarCircleLayer);

                    // Client Location Marker
                    const clientIcon = L.divIcon({
                        className: 'client-radar-origin',
                        html: `
                            <div style="position: relative; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                <div style="width: 18px; height: 18px; background: #4f46e5; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 16px #4f46e5;"></div>
                                <div class="radar-wave-ring" style="width: 80px; height: 80px; top: -28px; left: -28px;"></div>
                            </div>
                        `,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12],
                    });

                    L.marker([lat, lng], { icon: clientIcon }).addTo(this.radarCircleLayer);
                },

                renderAssignedTechnician(lat, lng, name, mobile) {
                    if (!this.radarCircleLayer) return;

                    const techCarIcon = L.divIcon({
                        className: 'assigned-tech-moving',
                        html: `
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center;">
                                <div style="background-color: #4f46e5; width: 42px; height: 42px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 16px rgba(79,70,229,0.5); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                    🚗
                                </div>
                                <div style="background: rgba(15,23,42,0.9); color: white; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: bold; margin-top: 3px; white-space: nowrap; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                                    ${name} (در مسیر)
                                </div>
                            </div>
                        `,
                        iconSize: [42, 60],
                        iconAnchor: [21, 21],
                    });

                    L.marker([lat, lng], { icon: techCarIcon }).addTo(this.radarCircleLayer)
                        .bindPopup(`<div style="direction: rtl; text-align: right; font-family: IRANYekanX, sans-serif;"><b>${name}</b><br>تلفن: ${mobile}<br><span style="color: #4f46e5; font-weight: bold;">در حال حرکت به سمت شما</span></div>`).openPopup();
                }
            }));
        });
    </script>

    @livewireScripts
</body>
</html>
