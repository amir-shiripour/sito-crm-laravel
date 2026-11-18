@extends('layouts.user')

@section('title', 'مرکز مانیتورینگ زنده و ناوبری ناوگان (GIS)')

@push('styles')
    {{-- Leaflet CSS (Local Core Asset) --}}
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <style>
        /* Radar pulse animations for markers */
        @keyframes fleet-ping {
            0% { transform: scale(0.6); opacity: 0.9; }
            70% { transform: scale(2.2); opacity: 0.2; }
            100% { transform: scale(2.8); opacity: 0; }
        }
        .fleet-radar-ring {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: fleet-ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        /* Custom Leaflet Dark/Light Popup Styler */
        .leaflet-popup-content-wrapper {
            background: #0f172a !important;
            color: #f8fafc !important;
            border-radius: 1rem !important;
            border: 1px solid rgba(51, 65, 85, 0.8) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5) !important;
            padding: 2px !important;
            font-family: IRANYekanX, sans-serif !important;
            direction: rtl !important;
        }
        .leaflet-popup-tip {
            background: #0f172a !important;
            border: 1px solid rgba(51, 65, 85, 0.8) !important;
        }
        .leaflet-popup-content {
            margin: 10px 14px !important;
            line-height: 1.5 !important;
        }
        .leaflet-container {
            font-family: IRANYekanX, sans-serif !important;
        }
    </style>
@endpush

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::fleet-live-map')
</div>
@endsection

@push('scripts')
    {{-- Leaflet JS (Local Core Asset) --}}
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    {{-- Map Handler for Livewire & Alpine --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fleetMapHandler', (config, technicians, orders) => ({
                map: null,
                markersLayer: null,
                markerDict: {},
                config: config,
                technicians: technicians,
                orders: orders,

                initMap() {
                    const defaultLat = this.config.center.lat || 36.5444;
                    const defaultLng = this.config.center.lng || 52.6780;
                    const zoom = this.config.zoom || 13;

                    // Strict geographic bounds enclosing Iran (25.0 to 39.8 Lat, 44.0 to 63.5 Lng)
                    const iranBounds = L.latLngBounds(
                        L.latLng(24.5, 43.5),
                        L.latLng(40.0, 64.0)
                    );

                    this.map = L.map('fleetMap', {
                        center: [defaultLat, defaultLng],
                        zoom: zoom,
                        minZoom: 5,
                        maxZoom: 19,
                        maxBounds: iranBounds,
                        maxBoundsViscosity: 0.95,
                        zoomControl: false,
                    });

                    L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                    // Add tile layer
                    const subdomains = this.config.subdomains || ['a', 'b', 'c'];
                    L.tileLayer(this.config.tile_url, {
                        maxZoom: this.config.max_zoom || 19,
                        subdomains: subdomains,
                        attribution: '&copy; ' + @json($platformName ?? 'Fleet GIS') + ' | ' + (this.config.name || 'GIS'),
                    }).addTo(this.map);

                    this.markersLayer = L.layerGroup().addTo(this.map);
                    this.renderMarkers();

                    // Listen for Livewire reactive update events
                    window.addEventListener('fleet-markers-updated', (event) => {
                        const data = event.detail ? (event.detail[0] || event.detail) : {};
                        if (data.technicians) this.technicians = data.technicians;
                        if (data.orders) this.orders = data.orders;
                        this.renderMarkers();
                    });

                    window.addEventListener('focus-map-entity', (event) => {
                        const payload = event.detail ? (event.detail[0] || event.detail) : {};
                        this.focusEntity(payload);
                    });
                },

                renderMarkers() {
                    if (!this.markersLayer) return;
                    this.markersLayer.clearLayers();
                    this.markerDict = {};

                    // 1. Render technicians
                    this.technicians.forEach(tech => {
                        if (!tech.lat || !tech.lng) return;

                        const isBusy = tech.is_busy;
                        const isOnline = tech.is_online;
                        
                        let bgColor = '#10b981'; // Green
                        let ringColor = 'rgba(16, 185, 129, 0.4)';
                        let statusText = 'آزاد و آماده اعزام';

                        if (!isOnline) {
                            bgColor = '#64748b'; // Slate gray
                            ringColor = 'transparent';
                            statusText = 'آفلاین و در استراحت';
                        } else if (isBusy) {
                            bgColor = '#f59e0b'; // Amber
                            ringColor = 'rgba(245, 158, 11, 0.4)';
                            statusText = 'در حال انجام ماموریت';
                        }

                        const pinHtml = `
                            <div class="relative flex items-center justify-center" style="width: 32px; height: 32px;">
                                ${isOnline && !isBusy ? `<span class="fleet-radar-ring" style="width: 38px; height: 38px; background: ${ringColor};"></span>` : ''}
                                <div style="background-color: ${bgColor}; width: 30px; height: 30px; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 4px 14px rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 13px;">
                                    🔧
                                </div>
                            </div>
                        `;

                        const techIcon = L.divIcon({
                            className: 'custom-tech-pin',
                            html: pinHtml,
                            iconSize: [32, 32],
                            iconAnchor: [16, 16],
                        });

                        const marker = L.marker([tech.lat, tech.lng], { icon: techIcon });
                        
                        const categoriesHtml = tech.categories && tech.categories.length > 0
                            ? tech.categories.slice(0, 3).map(c => `<span style="display: inline-block; padding: 1px 6px; border-radius: 6px; font-size: 10px; background: rgba(99,102,241,0.2); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); margin: 2px;">${c}</span>`).join(' ')
                            : '<span style="color: #94a3b8; font-size: 10px;">خدمات فنی عمومی</span>';

                        const popupContent = `
                            <div style="font-family: IRANYekanX, sans-serif; text-align: right; direction: rtl; min-width: 190px; padding: 2px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; padding-bottom: 6px; margin-bottom: 6px;">
                                    <div style="font-weight: 900; font-size: 13px; color: #ffffff;">${tech.full_name}</div>
                                    <span style="font-size: 10px; font-weight: bold; padding: 1px 6px; border-radius: 6px; background: ${bgColor}25; color: ${bgColor}; border: 1px solid ${bgColor}40;">
                                        ${statusText}
                                    </span>
                                </div>
                                <div style="font-size: 11px; color: #cbd5e1; margin-bottom: 3px;">
                                    <span>📞 همراه:</span>
                                    <span style="font-family: IRANYekanX, sans-serif; font-weight: bold; direction: ltr; display: inline-block;" dir="ltr">${tech.mobile}</span>
                                </div>
                                <div style="font-size: 11px; color: #94a3b8; margin-bottom: 5px;">
                                    <span>📍 محدوده:</span>
                                    <span style="color: #f1f5f9; font-weight: bold;">${tech.city || 'شهر'} (${tech.province || 'استان'})</span>
                                </div>
                                <div style="margin-top: 6px; padding-top: 5px; border-top: 1px dashed #334155;">
                                    <div style="font-size: 10px; color: #94a3b8; margin-bottom: 4px;">تخصص‌های فنی:</div>
                                    <div>${categoriesHtml}</div>
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupContent);
                        this.markersLayer.addLayer(marker);
                        this.markerDict['tech_' + tech.id] = marker;
                    });

                    // 2. Render active orders
                    this.orders.forEach(ord => {
                        if (!ord.lat || !ord.lng) return;

                        const isEmergency = ord.is_emergency;
                        const pinBg = isEmergency ? '#e11d48' : '#4f46e5';
                        const ringColor = isEmergency ? 'rgba(225, 29, 72, 0.45)' : 'rgba(79, 70, 229, 0.35)';

                        const pinHtml = `
                            <div class="relative flex items-center justify-center" style="width: 32px; height: 32px;">
                                <span class="fleet-radar-ring" style="width: 38px; height: 38px; background: ${ringColor};"></span>
                                <div style="background-color: ${pinBg}; width: 28px; height: 28px; border-radius: 10px; border: 2.5px solid white; box-shadow: 0 4px 14px ${isEmergency ? 'rgba(225,29,72,0.6)' : 'rgba(79,70,229,0.5)'}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                    ${isEmergency ? '🚨' : (ord.category_icon || '📍')}
                                </div>
                            </div>
                        `;

                        const orderIcon = L.divIcon({
                            className: 'custom-order-pin',
                            html: pinHtml,
                            iconSize: [32, 32],
                            iconAnchor: [16, 16],
                        });

                        const marker = L.marker([ord.lat, ord.lng], { icon: orderIcon });

                        const popupContent = `
                            <div style="font-family: IRANYekanX, sans-serif; text-align: right; direction: rtl; min-width: 200px; padding: 2px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; padding-bottom: 6px; margin-bottom: 6px;">
                                    <div style="font-weight: 900; font-size: 13px; color: ${isEmergency ? '#fda4af' : '#a5b4fc'};">سفارش ${ord.order_number}</div>
                                    <span style="font-size: 10px; font-weight: bold; padding: 1px 6px; border-radius: 6px; background: ${isEmergency ? '#e11d4825' : '#4f46e525'}; color: ${isEmergency ? '#fb7185' : '#a5b4fc'}; border: 1px solid ${isEmergency ? '#e11d4840' : '#4f46e540'};">
                                        ${isEmergency ? '🚨 اعزام فوری' : ord.status_label}
                                    </span>
                                </div>
                                <div style="font-size: 12px; font-weight: bold; color: #ffffff; margin-bottom: 3px;">
                                    ${ord.category_icon || '🛠️'} ${ord.category_name}
                                </div>
                                <div style="font-size: 11px; color: #cbd5e1; margin-bottom: 2px;">
                                    <span>مشتری:</span>
                                    <span style="color: #f1f5f9; font-weight: bold;">${ord.customer_name}</span>
                                    ${ord.customer_phone ? `<span style="font-family: IRANYekanX, sans-serif; font-size: 10px; color: #94a3b8; margin-right: 4px;" dir="ltr">(${ord.customer_phone})</span>` : ''}
                                </div>
                                <div style="font-size: 10px; color: #94a3b8; margin-bottom: 4px;">
                                    <span>📍 موقعیت:</span>
                                    <span>${ord.city || ''} ${ord.address ? '• ' + ord.address : ''}</span>
                                </div>
                                ${ord.technician_name ? `
                                <div style="margin-top: 5px; padding-top: 4px; border-top: 1px dashed #334155; font-size: 11px; color: #10b981;">
                                    <span>تکنسین مامور:</span>
                                    <span style="font-weight: bold;">${ord.technician_name}</span>
                                </div>
                                ` : ''}
                            </div>
                        `;

                        marker.bindPopup(popupContent);
                        this.markersLayer.addLayer(marker);
                        this.markerDict['order_' + ord.id] = marker;

                        // Draw search radius circle
                        const radiusMeters = (ord.search_radius_km || 4) * 1000;
                        const circle = L.circle([ord.lat, ord.lng], {
                            radius: radiusMeters,
                            color: isEmergency ? '#e11d48' : '#6366f1',
                            fillColor: isEmergency ? '#f43f5e' : '#818cf8',
                            fillOpacity: 0.08,
                            weight: 1.5,
                            dashArray: '5, 8',
                        });
                        this.markersLayer.addLayer(circle);
                    });
                },

                focusEntity(payload) {
                    if (!this.map || !payload.lat || !payload.lng) return;
                    this.map.flyTo([payload.lat, payload.lng], payload.zoom || 16, {
                        animate: true,
                        duration: 1.0,
                    });

                    const markerKey = payload.type + '_' + payload.id;
                    if (this.markerDict && this.markerDict[markerKey]) {
                        setTimeout(() => {
                            this.markerDict[markerKey].openPopup();
                        }, 500);
                    }
                },

                fitCoverage() {
                    if (this.config.bounds && this.config.bounds.southWest && this.config.bounds.northEast) {
                        this.map.fitBounds([
                            this.config.bounds.southWest,
                            this.config.bounds.northEast,
                        ], { padding: [40, 40], maxZoom: 15 });
                    } else {
                        this.resetView();
                    }
                },

                fitIran() {
                    if (this.map) {
                        this.map.setView([32.4279, 53.6880], 6);
                    }
                },

                resetView() {
                    if (this.map) {
                        this.map.setView([this.config.center.lat || 36.5444, this.config.center.lng || 52.6780], this.config.zoom || 13);
                    }
                }
            }));
        });
    </script>
@endpush
