<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $platformName ?? 'سامانه هوشمند خدمات فنی' }} | کنسول سفیران و تکنسین‌ها</title>
    
    {{-- Anti-FOUC Early Theme Script --}}
    <script>
        (() => {
            const pref = localStorage.getItem('theme') || 'system';
            const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = pref === 'dark' || (pref === 'system' && sysDark);
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>

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

        /* Demand Radar Pulse Animation for Dynamic Simulated Demands */
        @keyframes demand-radar-pulse {
            0% {
                transform: scale(0.3);
                opacity: 0.95;
            }
            70% {
                opacity: 0.25;
            }
            100% {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        .demand-radar-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid #6366f1;
            background: rgba(99, 102, 241, 0.2);
            animation: demand-radar-pulse 2.5s cubic-bezier(0.2, 0.8, 0.4, 1) infinite;
            pointer-events: none;
        }
        .demand-radar-ring-emergency {
            border-color: #f59e0b !important;
            background: rgba(245, 158, 11, 0.22) !important;
        }

        /* Custom Leaflet Popup Overrides for IRANYekanX */
        .leaflet-popup-content-wrapper {
            background: rgba(255, 255, 255, 0.97) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            padding: 0 !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            font-family: inherit !important;
        }
        .dark .leaflet-popup-content-wrapper {
            background: rgba(15, 23, 42, 0.97) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
            color: #f8fafc !important;
        }
        .leaflet-popup-content {
            margin: 0 !important;
            line-height: inherit !important;
            font-family: inherit !important;
        }
        .leaflet-popup-tip {
            background: white !important;
        }
        .dark .leaflet-popup-tip {
            background: #0f172a !important;
        }

        /* Leaflet Controls & Hardware Acceleration */
        .leaflet-control-zoom {
            display: none !important;
        }
        .leaflet-control-attribution {
            background: rgba(255, 255, 255, 0.85) !important;
            color: #475569 !important;
            font-size: 9px !important;
            border-radius: 6px !important;
            margin: 4px !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
        }
        .dark .leaflet-control-attribution {
            background: rgba(15, 23, 42, 0.85) !important;
            color: #94a3b8 !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        /* Pure CSS Dark Mode Filter for Map Tiles (Zero API Key & 60 FPS) */
        .dark .leaflet-tile {
            filter: brightness(0.65) invert(1) contrast(2.4) hue-rotate(185deg) saturate(0.28);
        }

                /* Force LTR direction on Leaflet internal containers to eliminate RTL coordinate bugs */
        #technicianMap,
        .leaflet-container,
        .leaflet-pane,
        .leaflet-map-pane,
        .leaflet-top,
        .leaflet-bottom,
        .leaflet-control {
            direction: ltr !important;
            text-align: left !important;
        }

        /* Pure RTL Persian text alignment inside Leaflet Popups */
        .leaflet-popup,
        .leaflet-popup-content-wrapper,
        .leaflet-popup-content {
            direction: rtl !important;
            text-align: right !important;
            font-family: inherit !important;
        }

        .offline-demand-popup .leaflet-popup-content-wrapper {
            background: rgba(255, 255, 255, 0.98) !important;
            border-radius: 18px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            padding: 0 !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
        }
        .dark .offline-demand-popup .leaflet-popup-content-wrapper {
            background: rgba(15, 23, 42, 0.98) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.6) !important;
        }
        .offline-demand-popup .leaflet-popup-tip {
            background: white !important;
        }
        .dark .offline-demand-popup .leaflet-popup-tip {
            background: #0f172a !important;
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
            background: #f1f5f9 !important;
        }
        .dark .leaflet-container {
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

    {{-- Leaflet Global Map Variables & Unproxied Instances --}}
    <script>
        let rawTechMap = null;
        let rawTechMarker = null;
        let rawCustomerMarker = null;
        let rawRouteGlowPolyline = null;
        let rawRoutePolyline = null;
        let rawPreviewRouteLine = null;
        let rawFocusRingLayer = null;
        let rawMissionsLayer = null;
        let rawMissionMarkersMap = {};
        let rawOfflineDemandLayer = null;
        let rawOfflineMarkersMap = {};

        // Global trigger for map popups to activate duty online or sign-in
        window.triggerPromptGoOnline = function() {
            const root = document.querySelector('[x-data]');
            if (root && window.Alpine && Alpine.$data(root)) {
                Alpine.$data(root).promptGoOnline();
            } else {
                const dutyBtn = document.querySelector('[wire\\:click="toggleOnline"]');
                if (dutyBtn) dutyBtn.click();
                else window.dispatchEvent(new CustomEvent('open-technician-auth'));
            }
        };

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

        // Core Technician App Controller (Defined globally so Alpine evaluates it immediately in <head>)
        window.technicianAppHandler = function(config, technician, activeMission, availableMissions, todayStats, activeCategories, techSkills) {
            return {
                config: config || {},
                technician: technician || null,
                activeMission: activeMission || null,
                availableMissions: availableMissions || [],
                todayStats: todayStats || {},
                activeCategories: activeCategories || [],
                techSkills: techSkills || [],
                sheetExpanded: (!(!technician || !technician.is_online)),
                statsModalOpen: false,
                isDark: document.documentElement.classList.contains('dark'),
                isLocating: false,
                currentCityName: '',
                currentAreaName: '',
                toast: { show: false, type: 'info', text: '', timer: null },

                showToast(type, text) {
                    if (!text) return;
                    if (this.toast.timer) clearTimeout(this.toast.timer);
                    this.toast.type = type || 'info';
                    this.toast.text = text;
                    this.toast.show = true;
                    this.toast.timer = setTimeout(() => {
                        this.toast.show = false;
                    }, 5000);
                },

                // Offline State & Procedural Dynamic Demand Engine
                isOffline: (!technician || !technician.is_online),
                offlineDemands: [],
                simulatedRefreshTimer: null,

                // Reactive Active Mission State
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

                toggleTheme() {
                    this.isDark = !this.isDark;
                    document.documentElement.classList.toggle('dark', this.isDark);
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                },

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
                    const techLat = (this.technician && this.technician.lat) ? parseFloat(this.technician.lat) : 36.5444;
                    const techLng = (this.technician && this.technician.lng) ? parseFloat(this.technician.lng) : 52.6780;
                    const zoom = 14;

                    // Initialize unproxied raw Leaflet map for 60 FPS performance
                    rawTechMap = L.map('technicianMap', {
                        center: [techLat, techLng],
                        zoom: zoom,
                        minZoom: this.config.min_zoom || 6,
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

                    // Add OpenStreetMap tiles (Nationwide without bounds restriction)
                    const subdomains = this.config.subdomains || ['a', 'b', 'c'];
                    L.tileLayer(this.config.tile_url, {
                        maxZoom: this.config.max_zoom || 19,
                        minZoom: this.config.min_zoom || 6,
                        subdomains: subdomains,
                        keepBuffer: 6,
                        updateWhenIdle: false,
                        updateWhenZooming: true,
                        attribution: '&copy; ' + @json($platformName ?? 'OpenStreetMap'),
                    }).addTo(rawTechMap);

                    rawMissionsLayer = L.layerGroup().addTo(rawTechMap);

                    // Render initial technician marker
                    this.renderTechnicianMarker(techLat, techLng);

                    // Auto-acquire real GPS location of the technician
                    this.acquireGpsLocation(true);

                    // Render active mission, available radar missions, or dynamic offline simulated demands
                    if (this.activeMission && this.activeMission.lat && this.activeMission.lng) {
                        this.renderActiveMissionRoute(
                            this.activeMission.lat,
                            this.activeMission.lng,
                            this.activeMission.customer_name || 'کارفرما',
                            this.activeMission.customer_phone || ''
                        );
                    } else if (this.technician && this.technician.is_online && this.availableMissions && this.availableMissions.length > 0) {
                        this.renderAvailableMissionsMarkers();
                    } else if (this.isOffline && !this.hasActiveMission) {
                        this.renderSimulatedOfflineDemand();
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
                        if (this.isOffline) {
                            this.renderSimulatedOfflineDemand();
                        } else {
                            this.renderAvailableMissionsMarkers();
                        }
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
                            if (this.isOffline) {
                                this.renderSimulatedOfflineDemand();
                            } else {
                                this.renderAvailableMissionsMarkers();
                            }
                        }
                    });

                    window.addEventListener('mission-cancelled-by-admin', () => {
                        this.hasActiveMission = false;
                        this.activeMissionTitle = '';
                        this.activeMissionStatus = '';
                        this.activeMissionCity = '';
                        this.clearActiveMissionRoute();
                        this.clearMissionFocus();
                        if (this.isOffline) {
                            this.renderSimulatedOfflineDemand();
                        } else {
                            this.renderAvailableMissionsMarkers();
                        }
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

                    window.addEventListener('notify', (e) => {
                        const d = (Array.isArray(e.detail) ? e.detail[0] : (e.detail?.[0] || e.detail)) || {};
                        if (d.text || d.message) {
                            this.showToast(d.type || 'info', d.text || d.message);
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
                        if (typeof e.detail?.isOnline !== 'undefined') {
                            const isOnline = !!e.detail.isOnline;
                            if (this.technician) {
                                this.technician.is_online = isOnline;
                            }
                            this.isOffline = !isOnline;
                            const tLat = (this.technician && this.technician.lat) ? this.technician.lat : 36.5444;
                            const tLng = (this.technician && this.technician.lng) ? this.technician.lng : 52.6780;
                            this.renderTechnicianMarker(tLat, tLng);
                            if (isOnline) {
                                this.clearSimulatedOfflineDemand();
                                this.renderAvailableMissionsMarkers();
                            } else {
                                if (rawMissionsLayer) rawMissionsLayer.clearLayers();
                                this.clearMissionFocus();
                                this.renderSimulatedOfflineDemand();
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

                // Acquire Real GPS Location of Technician
                acquireGpsLocation(silent = false) {
                    if (!navigator.geolocation) {
                        if (!silent) alert('مرورگر شما از قابلیت موقعیت‌یابی GPS پشتیبانی نمی‌کند.');
                        return;
                    }

                    this.isLocating = true;
                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            this.isLocating = false;
                            const lat = Number(pos.coords.latitude.toFixed(6));
                            const lng = Number(pos.coords.longitude.toFixed(6));

                            if (this.technician) {
                                this.technician.lat = lat;
                                this.technician.lng = lng;
                            }

                            // Update technician marker position
                            this.renderTechnicianMarker(lat, lng);

                            // Center map if not currently engaged in an active mission route
                            if (!this.hasActiveMission && rawTechMap) {
                                rawTechMap.setView([lat, lng], Math.max(rawTechMap.getZoom(), 14), { animate: true });
                            }

                            // Reverse geocode to resolve neighborhood and city name
                            try {
                                const revRes = await fetch(`/api/fieldservice/customer/reverse-geocode?lat=${lat}&lng=${lng}`);
                                if (revRes.ok) {
                                    const revData = await revRes.json();
                                    if (revData && revData.display_name) {
                                        this.currentAreaName = revData.display_name;
                                    }
                                    if (revData && revData.city) {
                                        this.currentCityName = revData.city;
                                    }
                                }
                            } catch (e) {}

                            // Send GPS heartbeat to backend
                            if (this.technician && this.technician.id) {
                                try {
                                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                    fetch('/api/fieldservice/technician/location', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': token || ''
                                        },
                                        body: JSON.stringify({
                                            technician_id: this.technician.id,
                                            lat: lat,
                                            lng: lng
                                        })
                                    }).catch(() => {});
                                } catch (e) {}
                            }

                            // Re-generate demands dynamically around the real GPS position
                            if (this.isOffline && !this.hasActiveMission) {
                                this.renderSimulatedOfflineDemand();
                            }
                        },
                        (err) => {
                            this.isLocating = false;
                            console.warn('GPS location error:', err.message);
                            if (!silent) {
                                alert('امکان دریافت موقعیت دقیق GPS وجود نداشت. لطفاً دسترسی موقعیت مکانی مرورگر را بررسی نمایید.');
                            }
                        },
                        { enableHighAccuracy: true, timeout: 12000, maximumAge: 30000 }
                    );
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
                                <div style="background: ${isOnline ? '#4f46e5' : '#475569'}; width: 30px; height: 30px; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 4px 14px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; color: white; position: relative; z-index: 2;">
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

                    if (rawCustomerMarker && this.currentDestLat === parsedDestLat && this.currentDestLng === parsedDestLng) {
                        return;
                    }

                    this.clearActiveMissionRoute();
                    this.currentDestLat = parsedDestLat;
                    this.currentDestLng = parsedDestLng;

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

                    if (rawMissionMarkersMap[m.id]) {
                        rawMissionMarkersMap[m.id].setLatLng([m.lat, m.lng]);
                        return rawMissionMarkersMap[m.id];
                    }

                    const pinIcon = L.divIcon({
                        className: 'available-job-pin',
                        html: `
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: ${m.is_emergency ? '#d97706' : '#4f46e5'}; width: 30px; height: 30px; border-radius: 50%; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; transition: transform 0.2s;" class="hover:scale-110 active:scale-95">
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

                    Object.keys(rawMissionMarkersMap).forEach((id) => {
                        if (!currentIds.includes(Number(id))) {
                            rawMissionsLayer.removeLayer(rawMissionMarkersMap[id]);
                            delete rawMissionMarkersMap[id];
                        }
                    });

                    currentMissions.forEach((m) => {
                        this.addOrUpdateMissionMarker(m);
                    });
                },

                focusOnMission(orderId, lat, lng, title) {
                    if (!rawTechMap) return;

                    this.clearMissionFocus();

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

                    rawTechMap.flyTo([lat, lng], 15, { duration: 0.8 });

                    const techLat = (this.technician && this.technician.lat) ? this.technician.lat : 36.5444;
                    const techLng = (this.technician && this.technician.lng) ? this.technician.lng : 52.6780;
                    rawPreviewRouteLine = L.polyline([[techLat, techLng], [lat, lng]], {
                        color: '#6366f1',
                        weight: 3.5,
                        opacity: 0.85,
                        dashArray: '6, 8',
                    }).addTo(rawTechMap);

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
                    if (this.technician && this.technician.lat && this.technician.lng) {
                        rawTechMap.setView([this.technician.lat, this.technician.lng], 14, { animate: true });
                    }
                    // Refresh GPS location accurately
                    this.acquireGpsLocation(false);
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

                promptGoOnline() {
                    if (!this.technician) {
                        window.dispatchEvent(new CustomEvent('open-technician-auth'));
                        return;
                    }
                    const dutyBtn = document.querySelector('[wire\\:click="toggleOnline"]');
                    if (dutyBtn) {
                        dutyBtn.click();
                    } else if (window.Livewire) {
                        window.Livewire.dispatch('toggle-technician-online');
                    }
                },

                // Generate Dynamic Demands Strictly from Real Database Categories
                generateDynamicOfflineDemands(centerLat, centerLng) {
                    const categoriesPool = (Array.isArray(this.activeCategories) && this.activeCategories.length > 0)
                        ? this.activeCategories
                        : [];

                    if (categoriesPool.length === 0) {
                        return [];
                    }

                    // Realistic specialized maintenance tasks mapped to category keywords
                    const categoryTemplates = {
                        'پکیج': [
                            'سرویس دوره‌ای، رسوب‌زدایی و هواگیری پکیج',
                            'عیب‌یابی افت فشار و رفع خطای سنسور پکیج دیواری',
                            'تعمیر پمپ سیرکولاتور و تعویض سنسور NTC',
                            'شستشوی مبدل ثانویه و تست نشتی مدار گرمایش'
                        ],
                        'کولر': [
                            'شستشو، سرویس فصلی و شارژ گاز کولر گازی',
                            'عیب‌یابی خازن و روشن نشدن کمپرسور اسپلیت',
                            'رفع نشتی لوله مسی و اصلاح آب‌ریزی پنل داخلی',
                            'تست آمپر مصرفی و سرویس جامع کندانسور'
                        ],
                        'اسپلیت': [
                            'سرویس تخصصی و تست نشت مبرد اسپلیت',
                            'تعویض کنتاکتور و خازن راه‌انداز موتور اسپلیت',
                            'بررسی ارور فن کندانسور و برد اینورتر'
                        ],
                        'یخچال': [
                            'بررسی فن اواپراتور و رفع مشکل افت سرما',
                            'تعویض سنسور دیفراست و شارژ گاز تخصصی یخچال',
                            'عیب‌یابی رله استارت و کمپرسور اینورتر',
                            'تنظیم دمای دیجیتال و تعویض نوار درب'
                        ],
                        'لباسشویی': [
                            'رفع خطای عدم تخلیه و تعویض پمپ تخلیه لباسشویی',
                            'تعویض کمک‌فنرها و رفع لرزش شدید در دور خشک‌کن',
                            'سرویس هیدروستات و رفع مشکل آبگیری مکرر',
                            'تعویض بلبرینگ و کاسه‌نمد دیگ ماشین لباسشویی'
                        ],
                        'ظرفشویی': [
                            'سرویس جت‌پمپ و جرم‌گیری تخصصی ماشین ظرفشویی',
                            'رفع نشتی سنسور آکوا استاپ و عدم آبگیری',
                            'عیب‌یابی برد فرمان و المنت گرمکن آب'
                        ],
                        'موتورخانه': [
                            'تنظیم رله و سرویس مشعل گازی موتورخانه',
                            'سرویس پمپ سیرکولاتور و عیب‌یابی دیگ آب‌گرم',
                            'هواگیری کلکتورها و تنظیم ترموستات جداره',
                            'رسوب‌زدایی منبع کویل‌دار و رفع نشتی فلنج‌ها'
                        ],
                        'مشعل': [
                            'سرویس و تنظیم الکترود جرقه و یون مشعل',
                            'تعویض نازل سوخت و تست شیر برقی گاز'
                        ],
                        'چیلر': [
                            'چکاپ دوره‌ای فشار روغن و گاز چیلر تراکمی',
                            'سرویس تابلو برق و کنترلرهای فن کویل',
                            'اسیدشویی کندانسور آبی و بازدید برج خنک‌کن'
                        ],
                        'پمپ': [
                            'تنظیم کلید اتوماتیک مکانیکی و شارژ منبع تحت فشار',
                            'تعویض تیوب مخزن تحت فشار و سرویس پروانه پمپ',
                            'عیب‌یابی افت فشار آب طبقات بالا و هواگیری'
                        ],
                        'آسانسور': [
                            'سرویس ماهانه ترمز ایمنی (پاراشوت) و تعلیق',
                            'روغن‌کاری ریل‌ها و تنظیم سنسورهای توقف طبقات',
                            'عیب‌یابی درب اتوماتیک کابین و تابلو فرمان'
                        ],
                        'برق': [
                            'رفع اتصالی مدار روشنایی و تعویض کلید مینیاتوری',
                            'نصب و تنظیم تایمر و فتوسل مشاعات ساختمان',
                            'تست و عیب‌یابی خطوط تابلو برق اصلی'
                        ],
                        'لوله‌کشی': [
                            'نشت‌یابی لوله‌کشی با دستگاه و تعمیر موضعی',
                            'تعویض شیرآلات توکار و اصلاح مسیر لوله‌کشی آب',
                            'رفع انسداد و لوله‌بازکنی مسیر فاضلاب'
                        ]
                    };

                    // Prioritize categories matching technician skills if available
                    let candidateCategories = [];
                    if (Array.isArray(this.techSkills) && this.techSkills.length > 0) {
                        const matched = categoriesPool.filter(c => this.techSkills.includes(Number(c.id)));
                        if (matched.length > 0) {
                            candidateCategories = matched;
                        }
                    }
                    if (candidateCategories.length === 0) {
                        candidateCategories = [...categoriesPool];
                    }

                    // Confidential general area labels to ensure user privacy
                    const areaPrefixes = [
                        'محدوده مسکونی',
                        'محدوده تجاری / اداری',
                        'حوالی خیابان اصلی',
                        'حوالی میدان مرکزی',
                        'محدوده شهرک مسکونی',
                        'حوالی بلوار اصلی',
                        'منطقه غربی',
                        'منطقه شرقی',
                        'حوالی مرکز شهر'
                    ];

                    const currentCity = this.currentCityName || 'محدوده شما';
                    const count = Math.min(candidateCategories.length >= 3 ? 4 + Math.floor(Math.random() * 2) : 3, 6);
                    const demands = [];
                    const nowTime = Date.now();

                    // Shuffle candidates
                    const shuffledCategories = [...candidateCategories].sort(() => 0.5 - Math.random());

                    for (let i = 0; i < count; i++) {
                        const cat = shuffledCategories[i % shuffledCategories.length];
                        
                        let titleOptions = null;
                        for (const [key, templates] of Object.entries(categoryTemplates)) {
                            if (cat.name.includes(key)) {
                                titleOptions = templates;
                                break;
                            }
                        }
                        if (!titleOptions || titleOptions.length === 0) {
                            titleOptions = [
                                `سرویس تخصصی و بررسی دوره‌ای ${cat.name}`,
                                `عیب‌یابی و رفع نقص فنی ${cat.name}`,
                                `تعمیر و تعویض قطعات ${cat.name}`
                            ];
                        }

                        const chosenTitle = titleOptions[Math.floor(Math.random() * titleOptions.length)];
                        const distKm = +(0.6 + Math.random() * 2.6).toFixed(1); // 0.6 to 3.2 km
                        
                        // Scatter around technician GPS with random angle
                        const angle = Math.random() * Math.PI * 2;
                        const latOffset = (distKm / 111) * Math.sin(angle);
                        const lngOffset = (distKm / (111 * Math.cos(centerLat * Math.PI / 180))) * Math.cos(angle);
                        
                        const lat = +(centerLat + latOffset).toFixed(6);
                        const lng = +(centerLng + lngOffset).toFixed(6);

                        const minsAgo = 1 + Math.floor(Math.random() * 9);
                        const timeAgoText = minsAgo <= 1 ? 'لحظاتی پیش' : `${new Intl.NumberFormat('fa-IR').format(minsAgo)} دقیقه قبل`;
                        const isEmergency = (i === 0 || Math.random() < 0.25);

                        const areaLabel = `${areaPrefixes[i % areaPrefixes.length]} (${currentCity})`;

                        demands.push({
                            id: `sim-${nowTime}-${i}-${Math.random().toString(36).substring(2, 6)}`,
                            category_id: cat.id,
                            category: cat.name,
                            title: chosenTitle,
                            location_approx: areaLabel,
                            distance_km: new Intl.NumberFormat('fa-IR').format(distKm),
                            raw_distance: distKm,
                            time_ago: timeAgoText,
                            is_emergency: isEmergency,
                            lat: lat,
                            lng: lng
                        });
                    }

                    return demands;
                },

                renderSimulatedOfflineDemand(overrideLat = null, overrideLng = null) {
                    if (!rawTechMap) return;

                    if (!rawOfflineDemandLayer) {
                        rawOfflineDemandLayer = L.layerGroup().addTo(rawTechMap);
                    }
                    rawOfflineDemandLayer.clearLayers();
                    rawOfflineMarkersMap = {};

                    const techLat = overrideLat !== null ? overrideLat : ((this.technician && this.technician.lat) ? parseFloat(this.technician.lat) : 36.5444);
                    const techLng = overrideLng !== null ? overrideLng : ((this.technician && this.technician.lng) ? parseFloat(this.technician.lng) : 52.6780);

                    this.offlineDemands = this.generateDynamicOfflineDemands(techLat, techLng);

                    this.offlineDemands.forEach((d) => {
                        const isEmergency = d.is_emergency;
                        const demandIcon = L.divIcon({
                            className: 'offline-demand-radar-pin',
                            html: `
                                <div style="position: relative; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                    <div class="demand-radar-ring ${isEmergency ? 'demand-radar-ring-emergency' : ''}" style="width: 44px; height: 44px;"></div>
                                    <div style="background: ${isEmergency ? '#d97706' : '#4f46e5'}; width: 32px; height: 32px; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 4px 14px rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center; color: white; position: relative; z-index: 2; transition: transform 0.2s;" class="hover:scale-110 active:scale-95">
                                        <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            ${isEmergency ? '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>' : '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>'}
                                        </svg>
                                    </div>
                                </div>
                            `,
                            iconSize: [44, 44],
                            iconAnchor: [22, 22]
                        });

                        const mark = L.marker([d.lat, d.lng], { icon: demandIcon }).addTo(rawOfflineDemandLayer);

                        const popupHtml = `
                            <div style="padding: 12px 14px; font-family: IRANYekanX, sans-serif; direction: rtl; text-align: right; min-width: 210px; max-width: 260px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md ${isEmergency ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300' : 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-300'}">
                                        ${isEmergency ? 'تقاضای فوری' : d.category}
                                    </span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">${d.time_ago}</span>
                                </div>
                                <div class="font-extrabold text-xs text-slate-900 dark:text-white mb-1.5 leading-snug">
                                    ${d.title}
                                </div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300 mb-2.5 flex items-center gap-1">
                                    <span>📍</span>
                                    <span>${d.location_approx} (${d.distance_km} کیلومتر)</span>
                                </div>
                                <button type="button" onclick="window.triggerPromptGoOnline()"
                                        class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-[11px] font-bold cursor-pointer flex items-center justify-center gap-1.5 transition">
                                    <span>روشن کردن وضعیت و دریافت</span>
                                    <span class="text-xs">←</span>
                                </button>
                            </div>
                        `;

                        mark.bindPopup(popupHtml, {
                            offset: [0, -20],
                            closeButton: false,
                            autoPan: true,
                            autoPanPadding: [30, 30],
                            className: 'offline-demand-popup'
                        });

                        rawOfflineMarkersMap[d.id] = mark;
                    });

                    // Setup calm periodic refresh (every 150 seconds / 2.5 minutes to stay stable without jumping)
                    if (this.simulatedRefreshTimer) {
                        clearInterval(this.simulatedRefreshTimer);
                    }
                    this.simulatedRefreshTimer = setInterval(() => {
                        if (this.isOffline && !this.hasActiveMission) {
                            this.renderSimulatedOfflineDemand();
                        }
                    }, 150000);
                },

                clearSimulatedOfflineDemand() {
                    if (this.simulatedRefreshTimer) {
                        clearInterval(this.simulatedRefreshTimer);
                        this.simulatedRefreshTimer = null;
                    }
                    if (rawOfflineDemandLayer) {
                        rawOfflineDemandLayer.clearLayers();
                    }
                    rawOfflineMarkersMap = {};
                    this.offlineDemands = [];
                },

                focusOnOfflineDemand(d) {
                    if (!rawTechMap || !d) return;
                    rawTechMap.flyTo([d.lat, d.lng], 15, { duration: 0.8 });
                    const mark = rawOfflineMarkersMap[d.id];
                    if (mark) {
                        setTimeout(() => {
                            mark.openPopup();
                        }, 500);
                    }
                },
            };
        };

        // Register with Alpine when available or on alpine:init
        if (window.Alpine) {
            Alpine.data('technicianAppHandler', window.technicianAppHandler);
        }
        document.addEventListener('alpine:init', () => {
            Alpine.data('technicianAppHandler', window.technicianAppHandler);
        });
    </script>
</head>
<body class="h-dvh w-screen overflow-hidden bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans selection:bg-indigo-600 selection:text-white select-none relative"
      x-data="technicianAppHandler(@js($mapConfig), @js($technician), @js($activeMission), @js($availableMissions), @js($todayStats), @js($activeCategories ?? []), @js($techSkills ?? []))"
      x-init="initMap()">

    {{-- ========================================================================= --}}
    {{-- 1. FULL-VIEWPORT MAP LAYER                                                --}}
    {{-- ========================================================================= --}}
    <div id="technicianMap" class="w-full h-full absolute inset-0 z-0 bg-slate-200 dark:bg-slate-950"></div>

    {{-- ========================================================================= --}}
    {{-- 2. MODERN FLAT APP HEADER (MINIMAL, BALANCED & RESPONSIVE)               --}}
    {{-- ========================================================================= --}}
    <header class="fixed top-2 inset-x-2 sm:top-4 sm:inset-x-6 z-40 pointer-events-none">
        <div class="max-w-xl mx-auto flex items-center justify-between gap-2">
            
            {{-- Right: Technician Name & Rating (Tap to open Today's Stats) --}}
            <div class="pointer-events-auto flex items-center gap-1.5 shrink-0">
                @if($technician)
                <button type="button" @click="statsModalOpen = true"
                        class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-sm dark:shadow-xl border border-slate-200/90 dark:border-slate-800 flex items-center gap-2 text-right transition-all active:scale-95 hover:border-slate-300 dark:hover:border-slate-700">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800 dark:text-white truncate flex items-center gap-1.5">
                            <span>{{ $technician->full_name }}</span>
                            @if($technician->status->value === 'pending_approval')
                            <span class="text-[9px] px-1.5 py-0.5 rounded-md font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-500/30">
                                در بررسی
                            </span>
                            @endif
                        </div>
                        <div class="text-[10px] text-amber-600 dark:text-amber-400 font-bold flex items-center gap-1 mt-0.5">
                            <svg class="w-3 h-3 fill-amber-500 text-amber-500 shrink-0" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span>{{ $todayStats['rating'] }}</span>
                            <span class="text-slate-400 dark:text-slate-500">•</span>
                            <span class="text-slate-500 dark:text-slate-400">{{ $technician->completed_orders_count }} خدمت</span>
                        </div>
                    </div>
                </button>
                @else
                <button type="button" @click="$dispatch('open-technician-auth')"
                        class="px-3.5 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md flex items-center gap-2 transition-all active:scale-95 shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>ورود تکنسین</span>
                </button>
                @endif
            </div>

            {{-- Center: Duty Capsule Switch (Online / Offline) --}}
            @if($technician)
            <div class="pointer-events-auto bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-sm dark:shadow-xl rounded-full p-1 border border-slate-200/90 dark:border-slate-800 flex items-center shrink-0">
                @livewire('fieldservice::technician-duty-toggle', ['technicianId' => $technician->id])
            </div>
            @endif

            {{-- Left: Today Earnings + Dark/Light Theme Switcher + Extra Menu --}}
            <div class="pointer-events-auto flex items-center gap-1.5 shrink-0">
                @if($technician)
                {{-- Today's Income Pill --}}
                <button type="button" @click="statsModalOpen = true"
                        class="hidden sm:flex px-3 py-2 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-sm dark:shadow-xl border border-slate-200/90 dark:border-slate-800 items-center gap-1.5 text-xs transition-all active:scale-95 hover:border-slate-300 dark:hover:border-slate-700 shrink-0">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold">کارکرد امروز:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 text-xs">{{ number_format($todayStats['net_earnings']) }}</span>
                    <span class="text-[9px] text-emerald-600 dark:text-emerald-500 font-bold">تومان</span>
                </button>
                @endif

                {{-- Dark / Light Theme Toggle Button --}}
                <button type="button" @click="toggleTheme()"
                        class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-sm dark:shadow-xl border border-slate-200/90 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95"
                        :title="isDark ? 'حالت روز (روشن)' : 'حالت شب (تاریک)'">
                    <svg x-show="isDark" class="w-5 h-5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
                    </svg>
                    <svg x-show="!isDark" class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                    </svg>
                </button>

                {{-- Extra Menu / CRM & Logout --}}
                <div x-data="{ menuOpen: false }" class="relative">
                    <button type="button" @click="menuOpen = !menuOpen"
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-sm dark:shadow-xl border border-slate-200/90 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-all active:scale-95"
                            title="گزینه‌ها">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </button>

                    <div x-show="menuOpen" @click.outside="menuOpen = false" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute left-0 top-full mt-2 w-56 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl p-1.5 space-y-1 z-50 font-sans">
                        
                        @if($technician && ($isPendingApproval ?? false))
                        <a href="{{ url('/user/profile') }}"
                           class="flex items-center gap-2.5 p-2 rounded-xl bg-amber-50 dark:bg-amber-500/15 hover:bg-amber-100 dark:hover:bg-amber-500/25 text-xs text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 transition font-bold">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                            <span>{{ ($verificationSubmitted ?? false) ? 'پیگیری تایید احراز هویت' : 'تکمیل احراز هویت و مدارک' }}</span>
                        </a>
                        @elseif($technician && ($hasPendingContract ?? false))
                        <button type="button" @click="$dispatch('open-pending-contract-modal'); menuOpen = false;"
                                class="w-full flex items-center gap-2.5 p-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-xs text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 transition text-right font-bold">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <span>مطالعه و امضای قرارداد</span>
                        </button>
                        @endif

                        <a href="{{ route('user.fieldservice.dashboard') }}"
                           class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-xs text-slate-700 dark:text-slate-200 transition font-bold">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <span>{{ ($technician && \Modules\FieldService\App\Support\TechnicianCustomFieldsHelper::isTechnicianOnly(auth()->user())) ? 'پنل کاری من' : 'پنل مدیریت CRM' }}</span>
                        </a>

                        @if($technician)
                        <button type="button" @click="statsModalOpen = true; menuOpen = false;"
                                class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-xs text-slate-700 dark:text-slate-200 transition text-right font-bold">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                            <span>کارنامه و آمار امروز</span>
                        </button>
                        <div class="border-t border-slate-100 dark:border-slate-800 my-1 pt-1">
                            <a href="{{ route('fieldservice.technician.logout') }}"
                               class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-500/20 text-xs text-rose-600 dark:text-rose-400 transition font-bold">
                                <svg class="w-4 h-4 text-rose-500 dark:text-rose-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>
                                <span>خروج از حساب تکنسین</span>
                            </a>
                        </div>
                        @else
                        <div class="border-t border-slate-100 dark:border-slate-800 my-1 pt-1">
                            <button type="button" @click="$dispatch('open-technician-auth'); menuOpen = false;"
                                    class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-indigo-600 text-xs text-indigo-600 hover:text-white dark:text-indigo-400 dark:hover:text-white transition font-bold text-right">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 2-2 2m-1.5 1.5L10 13l-4 4-4-4 4-4 7.5-7.5"></path><path d="m15.5 7.5 3 3"></path></svg>
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
    {{-- 2.1 FLOATING ACTIVE DEMAND BANNER (DISPLAYED WHEN OFFLINE TO MOTIVATE)     --}}
    {{-- ========================================================================= --}}
    <div x-show="isOffline && !hasActiveMission" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-3"
         class="fixed top-18 sm:top-20 inset-x-3 sm:inset-x-0 z-20 pointer-events-none flex justify-center">
        <div class="pointer-events-auto max-w-md w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-md text-slate-800 dark:text-white rounded-2xl p-2 sm:p-2.5 shadow-md dark:shadow-xl border border-slate-200/90 dark:border-slate-800 flex items-center justify-between gap-2.5 font-sans">
            <div class="flex items-center gap-2 min-w-0 pr-1">
                <span class="relative flex h-3 w-3 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <div class="min-w-0 text-right">
                    <div class="text-xs font-bold truncate flex items-center gap-1.5">
                        <span>تقاضای فعال در منطقه شما:</span>
                        <span class="text-amber-600 dark:text-amber-400 font-extrabold" x-text="offlineDemands.length + ' سفارش'"></span>
                    </div>
                    <div class="text-[10px] text-slate-600 dark:text-slate-300 truncate">برای دریافت و تخصیص مستقیم، آنلاین شوید</div>
                </div>
            </div>
            @if($technician && ($isPendingApproval ?? false))
            <a href="{{ url('/user/profile') }}"
               class="shrink-0 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 active:scale-95 text-slate-950 font-bold text-xs transition shadow-sm flex items-center gap-1">
                <span>{{ ($verificationSubmitted ?? false) ? 'پیگیری احراز هویت' : 'تکمیل احراز هویت' }}</span>
                <svg class="w-3.5 h-3.5 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            @elseif($technician && ($hasPendingContract ?? false))
            <button type="button" @click="$dispatch('open-pending-contract-modal')"
                    class="shrink-0 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold text-xs transition shadow-sm flex items-center gap-1">
                <span>امضای قرارداد</span>
                <svg class="w-3.5 h-3.5 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            @else
            <button type="button" @click="promptGoOnline()"
                    class="shrink-0 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold text-xs transition shadow-sm flex items-center gap-1">
                <span>شروع به کار</span>
                <svg class="w-3.5 h-3.5 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2.2 LIVEWIRE / APP TOAST NOTIFICATION                                     --}}
    {{-- ========================================================================= --}}
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="fixed top-20 sm:top-24 inset-x-4 z-50 pointer-events-none flex justify-center font-sans">
        <div class="pointer-events-auto max-w-md w-full rounded-2xl px-4 py-3 shadow-2xl border backdrop-blur-md flex items-center justify-between gap-3 text-xs font-bold"
             :class="{
                 'bg-amber-50/95 dark:bg-amber-950/95 border-amber-300 dark:border-amber-600/50 text-amber-900 dark:text-amber-200': toast.type === 'warning',
                 'bg-emerald-50/95 dark:bg-emerald-950/95 border-emerald-300 dark:border-emerald-600/50 text-emerald-900 dark:text-emerald-200': toast.type === 'success',
                 'bg-rose-50/95 dark:bg-rose-950/95 border-rose-300 dark:border-rose-600/50 text-rose-900 dark:text-rose-200': toast.type === 'error',
                 'bg-white/95 dark:bg-slate-900/95 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white': toast.type === 'info'
             }">
            <span x-text="toast.text" class="leading-relaxed"></span>
            <button type="button" @click="toast.show = false" class="shrink-0 opacity-70 hover:opacity-100 text-xs px-1">✕</button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. FLOATING GPS / RE-CENTER BUTTON (BOTTOM-RIGHT)                         --}}
    {{-- ========================================================================= --}}
    <div :class="sheetExpanded ? 'bottom-[330px] sm:bottom-[350px]' : 'bottom-20 sm:bottom-20'"
         class="fixed right-4 sm:right-6 z-20 transition-all duration-300 ease-out">
        <button type="button" @click="centerOnTechnician()" title="موقعیت فعلی من (GPS)" :class="isLocating ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 animate-pulse' : 'text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400'" class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-md dark:shadow-xl border border-slate-200/90 dark:border-slate-800 flex items-center justify-center transition-all active:scale-95">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" :class="isLocating ? 'animate-spin' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 2v3m0 14v3M2 12h3m14 0h3"></path>
            </svg>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. FLOATING BOTTOM SHEET: MISSION CONSOLE (SWIPEABLE & COLLAPSIBLE)       --}}
    {{-- ========================================================================= --}}
    <section class="fixed inset-x-0 bottom-0 z-30 lg:top-20 lg:bottom-6 lg:left-6 lg:right-auto lg:w-[480px] pointer-events-none transition-all duration-300 ease-out">
        <div class="pointer-events-auto bg-white/95 dark:bg-slate-950/95 backdrop-blur-xl rounded-t-[32px] lg:rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xl dark:shadow-2xl flex flex-col transition-all duration-300 ease-out"
             :class="{
                 'h-auto max-h-[82vh] lg:h-auto lg:max-h-[85vh]': sheetExpanded,
                 'h-14 max-h-14 lg:h-14 lg:max-h-14': !sheetExpanded
             }"
             @touchstart="handleTouchStart($event)"
             @touchmove="handleTouchMove($event)"
             @touchend="handleTouchEnd()">

            {{-- Peek Bar: Mobile Drag Handle & Summary Row --}}
            <div @click="toggleSheet()" class="px-4 py-2 cursor-pointer flex flex-col justify-between h-14 select-none border-b border-slate-100 dark:border-slate-900">
                {{-- Handle Bar --}}
                <div class="flex justify-center -mt-0.5">
                    <span class="w-10 h-1 rounded-full bg-slate-300 dark:bg-slate-700 hover:bg-slate-400 dark:hover:bg-slate-600 transition-colors"></span>
                </div>

                {{-- Compact Peek Bar Content --}}
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        {{-- 1. Active Assigned Mission Case --}}
                        <template x-if="hasActiveMission">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-500 animate-pulse shrink-0"></span>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-800 dark:text-white truncate" x-text="'ماموریت فعال: ' + activeMissionTitle"></div>
                                    <div class="text-[10px] text-indigo-600 dark:text-indigo-400 truncate font-bold flex items-center gap-1.5">
                                        <span x-text="activeMissionStatus"></span>
                                        <span x-show="activeMissionEta" class="text-emerald-600 dark:text-emerald-400 font-bold" x-text="'• ' + activeMissionEta"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- 2. No Active Mission --}}
                        <template x-if="!hasActiveMission">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="isOffline ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'"></span>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-800 dark:text-white truncate"
                                         x-text="isOffline ? 'استراحت (آفلاین)' : 'پیشنهادها و سفارش‌های رادار'">
                                    </div>
                                    <div x-show="isOffline" class="text-[10px] text-amber-600 dark:text-amber-400 font-bold truncate">
                                        جهت دریافت سفارش، وضعیت را آنلاین کنید
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <template x-if="hasActiveMission && activeMissionCustomerPhone">
                            <a :href="'tel:' + activeMissionCustomerPhone" @click.stop
                               class="w-8 h-8 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center text-xs shadow-md transition active:scale-95"
                               title="تماس با مشتری">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </a>
                        </template>
                        <button type="button" @click.stop="toggleSheet()"
                                class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1 active:scale-95 border border-slate-200 dark:border-slate-700">
                            <span x-text="sheetExpanded ? 'بستن' : 'مشاهده'"></span>
                            <svg class="w-3 h-3 transition-transform duration-200" :class="sheetExpanded ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Full Content Area: Livewire Mission Feed or Offline Demands --}}
            <div :class="!sheetExpanded ? 'hidden' : 'flex flex-col flex-1 min-h-0'"
                 class="transition-all duration-200">
                <div class="p-4 sm:p-5 custom-scrollbar flex-1 overflow-y-auto max-h-[72vh] lg:max-h-[75vh] space-y-3">
                    @if($technician)
                        @livewire('fieldservice::technician-contracts-panel', ['compactPortalMode' => true])
                    @endif

                    {{-- Offline Simple Status Notice (Clean, Minimal & Informative) --}}
                    <div x-show="isOffline && !hasActiveMission" x-cloak class="p-4 sm:p-5 rounded-3xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center space-y-3 font-sans">
                        @if($technician && ($isPendingApproval ?? false))
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-100 dark:bg-amber-500/15 border border-amber-300 dark:border-amber-500/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-white">
                                    {{ ($verificationSubmitted ?? false) ? 'مدارک شما در حال بررسی ناظران است' : 'تکمیل احراز هویت و مدارک الزامی است' }}
                                </h4>
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed max-w-sm mx-auto">
                                    تا زمان تکمیل و تایید احراز هویت، وضعیت شما آفلاین می‌ماند. برای مشاهده یا تکمیل مدارک به صفحه پروفایل بروید.
                                </p>
                            </div>
                            <div class="pt-1">
                                <a href="{{ url('/user/profile') }}"
                                   class="w-full py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold text-xs transition shadow-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span>{{ ($verificationSubmitted ?? false) ? 'مشاهده وضعیت احراز هویت' : 'انتقال به صفحه احراز هویت و مدارک' }}</span>
                                </a>
                            </div>
                        @elseif($technician && ($hasPendingContract ?? false))
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-100 dark:bg-indigo-500/15 border border-indigo-300 dark:border-indigo-500/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-white">قرارداد همکاری در انتظار امضا</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed max-w-sm mx-auto">
                                    برای امکان آنلاین شدن و شروع دریافت سفارش‌ها، ابتدا قرارداد همکاری خود را مطالعه و تایید کنید.
                                </p>
                            </div>
                            <div class="pt-1 flex flex-col gap-2">
                                <button type="button" @click="$dispatch('open-pending-contract-modal')"
                                        class="w-full py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold text-xs transition shadow-sm flex items-center justify-center gap-2">
                                    <span>مطالعه و امضای قرارداد</span>
                                </button>
                                <a href="{{ url('/user/profile') }}"
                                   class="w-full py-2 rounded-2xl bg-slate-200/80 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition flex items-center justify-center">
                                    <span>مشاهده پروفایل و قراردادها</span>
                                </a>
                            </div>
                        @else
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-100 dark:bg-amber-500/15 border border-amber-300 dark:border-amber-500/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-white">وضعیت شما: استراحت (آفلاین)</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed max-w-sm mx-auto">
                                    برای دریافت و مشاهده سفارش‌های جدید، وضعیت خود را آنلاین کنید.
                                </p>
                            </div>
                            <div class="pt-1">
                                <button type="button" @click="promptGoOnline()"
                                        class="w-full py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold text-xs transition shadow-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                        <line x1="12" y1="2" x2="12" y2="12"></line>
                                    </svg>
                                    <span>روشن کردن وضعیت (آنلاین)</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div x-show="!isOffline || hasActiveMission">
                        @livewire('fieldservice::mission-feed', ['technicianId' => $technician?->id])
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
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 dark:bg-slate-950/80 backdrop-blur-xs font-sans select-none">
        <div @click.outside="statsModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 space-y-4 shadow-2xl">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                    </span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">کارنامه و عملکرد امروز شما</h3>
                </div>
                <button type="button" @click="statsModalOpen = false" class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-800 dark:hover:text-white text-xs">✕</button>
            </div>

            {{-- 4-Grid Metrics --}}
            <div class="grid grid-cols-2 gap-2 font-sans">
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-1">
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold">درآمد خالص امروز:</div>
                    <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($todayStats['net_earnings']) }} <span class="text-[10px]">تومان</span></div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-1">
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold">خدمات انجام‌شده امروز:</div>
                    <div class="text-sm font-bold text-slate-800 dark:text-white">{{ $todayStats['completed_count'] }} <span class="text-[10px] text-slate-400 dark:text-slate-500">سرویس</span></div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-1">
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold">امتیاز رضایت مشتریان:</div>
                    <div class="text-sm font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 fill-amber-500 text-amber-500" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <span>{{ $todayStats['rating'] }}</span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500">از ۵</span>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-1">
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold">نرخ پذیرش رادار:</div>
                    <div class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $todayStats['acceptance_rate'] }}%</div>
                </div>
            </div>

            {{-- Lifetime Summary --}}
            <div class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">مجموع خدمات موفق تا کنون:</span>
                <span class="font-bold text-slate-800 dark:text-white">{{ $technician->completed_orders_count }} خدمت</span>
            </div>

            <div class="space-y-2 pt-1">
                @if($isPendingApproval ?? false)
                <a href="{{ url('/user/profile') }}"
                   class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-2xl transition flex items-center justify-center gap-2">
                    <span>{{ ($verificationSubmitted ?? false) ? 'مشاهده وضعیت احراز هویت' : 'تکمیل احراز هویت و مدارک' }}</span>
                </a>
                @elseif($hasPendingContract ?? false)
                <button type="button" @click="statsModalOpen = false; $dispatch('open-pending-contract-modal');"
                        class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-2xl transition flex items-center justify-center gap-2">
                    <span>مطالعه و امضای قرارداد همکاری</span>
                </button>
                @else
                <a href="{{ url('/user/profile') }}"
                   class="w-full py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-2xl transition flex items-center justify-center gap-2">
                    <span>پروفایل و مدارک من</span>
                </a>
                @endif

                <button type="button" @click="statsModalOpen = false"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-2xl transition">
                    بازگشت به نقشه
                </button>

                <a href="{{ route('fieldservice.technician.logout') }}"
                   class="w-full py-2.5 bg-rose-50 dark:bg-rose-500/15 hover:bg-rose-100 dark:hover:bg-rose-500/25 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 font-bold text-xs rounded-2xl transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-rose-500 dark:text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>
                    <span>خروج از حساب تکنسین</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 6. DEDICATED TECHNICIAN AUTH MODAL                                        --}}
    {{-- ========================================================================= --}}
    @if(!$technician)
        @livewire('fieldservice::technician-auth-modal', ['autoOpen' => true, 'allowClose' => false])
    @else
        @livewire('fieldservice::technician-auth-modal', ['autoOpen' => false, 'allowClose' => true])
    @endif
    @livewireScripts
</body>
</html>
