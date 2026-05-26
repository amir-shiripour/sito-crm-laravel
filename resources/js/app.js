import './bootstrap';
import Sortable from 'sortablejs';
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js';
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css';

// Leaflet Setup
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Leaflet Geosearch
import { GeoSearchControl, OpenStreetMapProvider } from 'leaflet-geosearch';
import 'leaflet-geosearch/dist/geosearch.css';

// Quill.js Editor
import Quill from 'quill';
import 'quill/dist/quill.core.css';
import 'quill/dist/quill.snow.css';

// 💡 ثبت افزونه‌های استاندارد Quill برای ذخیره اصولی استایل‌های راست‌چین در HTML خروجی
const DirectionAttribute = Quill.import('attributors/attribute/direction');
Quill.register(DirectionAttribute, true);

const AlignClass = Quill.import('attributors/class/align');
Quill.register(AlignClass, true);

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

// Make libraries available globally
window.L = L;
window.GeoSearchControl = GeoSearchControl;
window.OpenStreetMapProvider = OpenStreetMapProvider;
window.Quill = Quill;
window.Sortable = Sortable;

// import Alpine from 'alpinejs'
// window.Alpine = Alpine
// Alpine.start()
