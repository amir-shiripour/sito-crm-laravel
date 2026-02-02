import './bootstrap';
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

L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

// Make Leaflet and Geosearch available globally
window.L = L;
window.GeoSearchControl = GeoSearchControl;
window.OpenStreetMapProvider = OpenStreetMapProvider;

// import Alpine from 'alpinejs'
// window.Alpine = Alpine
// Alpine.start()
