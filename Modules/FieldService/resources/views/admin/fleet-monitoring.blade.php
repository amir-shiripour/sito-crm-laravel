@extends('layouts.user')

@section('title', 'مانیتورینگ زنده ناوگان استان مازندران')

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::fleet-live-map')
</div>
@endsection

@push('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    {{-- Map Handler for Livewire & Alpine --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fleetMapHandler', (config, technicians, orders) => ({
                map: null,
                markersLayer: null,
                config: config,
                technicians: technicians,
                orders: orders,

                initMap() {
                    const defaultLat = this.config.center.lat || 36.5444;
                    const defaultLng = this.config.center.lng || 52.6780;
                    const zoom = this.config.zoom || 13;

                    this.map = L.map('fleetMap', {
                        center: [defaultLat, defaultLng],
                        zoom: zoom,
                        zoomControl: true,
                    });

                    // Add tile layer
                    const subdomains = this.config.subdomains || ['a', 'b', 'c'];
                    L.tileLayer(this.config.tile_url, {
                        maxZoom: this.config.max_zoom || 19,
                        subdomains: subdomains,
                        attribution: '&copy; NasabBashi Fleet GIS',
                    }).addTo(this.map);

                    this.markersLayer = L.layerGroup().addTo(this.map);
                    this.renderMarkers();
                },

                renderMarkers() {
                    if (!this.markersLayer) return;
                    this.markersLayer.clearLayers();

                    // Render technicians
                    this.technicians.forEach(tech => {
                        if (!tech.lat || !tech.lng) return;

                        const color = tech.is_busy ? '#f59e0b' : '#10b981';
                        const statusText = tech.is_busy ? 'در حال انجام ماموریت' : 'آزاد و آماده اعزام';

                        const techIcon = L.divIcon({
                            className: 'custom-tech-pin',
                            html: '<div style="background-color: ' + color + '; width: 28px; height: 28px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 13px;">🔧</div>',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                        });

                        const marker = L.marker([tech.lat, tech.lng], { icon: techIcon });
                        marker.bindPopup(
                            '<div style="font-family: IRANYekanX, sans-serif; text-align: right; direction: rtl; padding: 4px; min-width: 170px;">' +
                                '<div style="font-weight: 900; font-size: 13px; color: #1e293b;">' + tech.first_name + ' ' + tech.last_name + '</div>' +
                                '<div style="font-size: 11px; color: #64748b; margin-top: 2px;">تلفن: ' + tech.mobile + '</div>' +
                                '<div style="font-size: 11px; color: #64748b;">شهر: ' + (tech.city || 'بابل') + '</div>' +
                                '<div style="display: inline-block; margin-top: 6px; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: bold; background: ' + (tech.is_busy ? '#fef3c7; color: #92400e;' : '#dcfce7; color: #166534;') + '">' +
                                    statusText +
                                '</div>' +
                            '</div>'
                        );
                        this.markersLayer.addLayer(marker);
                    });

                    // Render active orders
                    this.orders.forEach(ord => {
                        if (!ord.lat || !ord.lng) return;

                        const orderIcon = L.divIcon({
                            className: 'custom-order-pin',
                            html: '<div style="background-color: #6366f1; width: 26px; height: 26px; border-radius: 8px; border: 2px solid white; box-shadow: 0 4px 10px rgba(99,102,241,0.5); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">📍</div>',
                            iconSize: [26, 26],
                            iconAnchor: [13, 13],
                        });

                        const marker = L.marker([ord.lat, ord.lng], { icon: orderIcon });
                        marker.bindPopup(
                            '<div style="font-family: IRANYekanX, sans-serif; text-align: right; direction: rtl; padding: 4px; min-width: 180px;">' +
                                '<div style="font-weight: 900; font-size: 12px; color: #4338ca;">سفارش: ' + ord.order_number + '</div>' +
                                '<div style="font-size: 11px; color: #1e293b; margin-top: 2px;">مشتری: ' + ord.customer_name + '</div>' +
                                '<div style="font-size: 11px; color: #64748b;">تلفن: ' + ord.customer_phone + '</div>' +
                                '<div style="font-size: 10px; color: #64748b; margin-top: 4px; max-width: 190px;">' + (ord.address || '') + '</div>' +
                            '</div>'
                        );
                        this.markersLayer.addLayer(marker);

                        // Draw dynamic search radius circle
                        const radiusMeters = (ord.search_radius_km || 3) * 1000;
                        const circle = L.circle([ord.lat, ord.lng], {
                            radius: radiusMeters,
                            color: '#6366f1',
                            fillColor: '#818cf8',
                            fillOpacity: 0.1,
                            weight: 1.5,
                            dashArray: '4, 6',
                        });
                        this.markersLayer.addLayer(circle);
                    });
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
