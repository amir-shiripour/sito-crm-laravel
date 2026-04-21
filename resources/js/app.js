import './bootstrap';
import './sortable.min';
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js';
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css';

// Leaflet Setup
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Leaflet Geosearch
import { GeoSearchControl, OpenStreetMapProvider } from 'leaflet-geosearch';
import 'leaflet-geosearch/dist/geosearch.css';

// Fix Leaflet's default icon path issues with Webpack/Vite
delete L.Icon.Default.prototype._getIconUrl;

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

// Make Leaflet and Geosearch available globally
window.L = L;
window.GeoSearchControl = GeoSearchControl;
window.OpenStreetMapProvider = OpenStreetMapProvider;

// import Alpine from 'alpinejs'
// window.Alpine = Alpine
// Alpine.start()
