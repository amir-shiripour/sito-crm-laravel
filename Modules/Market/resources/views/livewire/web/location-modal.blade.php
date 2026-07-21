@php
    $jsonPath = base_path('Modules/Clients/resources/data/iran-provinces-cities.json');
    $provincesData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
    $allProvinces = array_keys($provincesData);
    $baseInputClass = "w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none placeholder-gray-400 dark:placeholder-gray-500";
    $labelClass = "block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1";
@endphp

<div>
    {{-- Leaflet styles/scripts (loaded only when required) --}}
    @if($isOpen)
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        @endpush
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        @endpush
    @endif

    {{-- Main Modal Overlay --}}
    @if($isOpen)
        <div x-data="{ init() { document.body.classList.add('overflow-hidden'); }, destroy() { document.body.classList.remove('overflow-hidden'); } }" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] p-6 w-full max-w-xl shadow-2xl border border-gray-100 dark:border-gray-800 mx-4 max-h-[90vh] overflow-y-auto scrollbar-thin relative">
                
                {{-- Modal Geocode Spinner Overlay --}}
                <div wire:loading wire:target="fetchNewAddressFromCoordinates" class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-[2px] z-[40] flex items-center justify-center rounded-[2.5rem] transition-all">
                    <div class="flex flex-col items-center gap-2 bg-white dark:bg-gray-800 px-5 py-3.5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg">
                        <div class="w-5 h-5 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-[11px] font-bold text-gray-800 dark:text-gray-200">در حال دریافت نشانی روی نقشه...</span>
                    </div>
                </div>

                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">
                            @if(auth()->guard('client')->check())
                                @if($mustAddAddress || $showAddNewAddress)
                                    ثبت آدرس و موقعیت مکانی جدید
                                @else
                                    انتخاب آدرس فعال
                                @endif
                            @else
                                @if($mustSelectLocation)
                                    تعیین موقعیت مکانی
                                @else
                                    انتخاب موقعیت مکانی (استان و شهر)
                                @endif
                            @endif
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            @if(auth()->guard('client')->check())
                                @if($mustAddAddress || $showAddNewAddress)
                                    موقعیت آدرس جدید خود را روی نقشه مشخص کنید.
                                @else
                                    یکی از آدرس‌های خود را برای نمایش سفارشات انتخاب کنید یا آدرس جدید اضافه کنید.
                                @endif
                            @else
                                برای نمایش محصولات متناسب با منطقه شما، تعیین موقعیت الزامی است.
                            @endif
                        </p>
                    </div>

                    {{-- Close Button --}}
                    @if(!$mustAddAddress && !$mustSelectLocation)
                        <button type="button" wire:click="skipLocationSelection" class="p-2 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    @endif
                </div>

                @if(auth()->guard('client')->check() && !$mustAddAddress && !$showAddNewAddress)
                    {{-- Logged-in Client: Switch between addresses --}}
                    <div class="space-y-4 animate-in fade-in duration-300">
                        <div class="space-y-2.5 max-h-[45vh] overflow-y-auto pr-1">
                            @foreach($addresses as $addr)
                                <div wire:click="selectActiveAddress({{ $addr['id'] }})" 
                                     class="p-4 rounded-2xl border transition-all duration-200 cursor-pointer flex items-start gap-3.5 {{ $selectedAddressId == $addr['id'] ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50/30 dark:bg-indigo-950/20 shadow-sm ring-1 ring-indigo-600/20 dark:ring-indigo-500/25' : 'border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 hover:border-indigo-300 dark:hover:border-indigo-800/60' }}">
                                    
                                    <div class="mt-1 flex items-center justify-center shrink-0">
                                        <input type="radio" name="active_address" value="{{ $addr['id'] }}" 
                                               {{ $selectedAddressId == $addr['id'] ? 'checked' : '' }}
                                               class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-all">
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $addr['title'] }}</h4>
                                            @if($addr['is_default'])
                                                <span class="text-[9px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-extrabold px-1.5 py-0.5 rounded-md shrink-0">پیش‌فرض</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ $addr['province'] }}، {{ $addr['city'] }}، {{ $addr['address'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-2">
                            <button type="button" wire:click="toggleAddNewAddress(true)" class="w-full py-3.5 rounded-xl border-2 border-dashed border-indigo-600/35 hover:border-indigo-600 text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold transition-all text-xs flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                افزودن آدرس جدید
                            </button>
                        </div>
                    </div>

                @elseif(auth()->guard('client')->check() && ($mustAddAddress || $showAddNewAddress))
                    {{-- Logged-in Client: Add/Create New Address Form --}}
                    <div class="space-y-4 animate-in fade-in duration-300">
                        {{-- Autocomplete search box --}}
                        <div class="relative" x-data="{ showDropdown: true }">
                            <label class="{{ $labelClass }}">جستجوی آدرس / محله</label>
                            <div class="relative">
                                <input type="text" 
                                       wire:model.live.debounce.300ms="searchQuery" 
                                       @focus="showDropdown = true" 
                                       class="{{ $baseInputClass }} pl-10" 
                                       placeholder="مثال: تهران، ونک، ملاصدرا...">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                            
                            {{-- Search Results Suggestion Dropdown --}}
                            @if(!empty($searchQuery) && count($searchResults) > 0)
                                <div x-show="showDropdown" @click.away="showDropdown = false" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-48 overflow-y-auto py-2">
                                    @foreach($searchResults as $res)
                                        <button type="button" 
                                                wire:click="selectSearchResult({{ $res['lat'] }}, {{ $res['lng'] }}, @js($res['title']))"
                                                @click="showDropdown = false"
                                                class="w-full text-right px-4 py-2 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/30 transition-colors flex flex-col gap-0.5 border-b border-gray-100 last:border-0 dark:border-gray-700/50">
                                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $res['title'] }}</span>
                                            @if(!empty($res['address']))
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ $res['address'] }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Leaflet Map selector --}}
                        <div class="space-y-2">
                            <label class="{{ $labelClass }}">موقعیت دقیق روی نقشه</label>
                            <div class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                                <div wire:ignore class="w-full h-48 relative z-10 bg-gray-100 dark:bg-gray-900"
                                     x-data="{
                                         map: null,
                                         marker: null,
                                         lat: @entangle('newLat'),
                                         lng: @entangle('newLng'),
                                         provider: @js($mapProvider),
                                         apiKey: @js($mapApiKey),
                                         initMap() {
                                             if (typeof L === 'undefined') {
                                                 setTimeout(() => this.initMap(), 100);
                                                 return;
                                             }
                                             
                                             this.map = L.map(this.$el).setView([this.lat, this.lng], 15);
                                             
                                             if (this.provider === 'map_ir' && this.apiKey) {
                                                 if (!L.TileLayer.WMS.Header) {
                                                     L.TileLayer.WMS.Header = L.TileLayer.WMS.extend({
                                                         initialize: function (url, options) {
                                                             const wmsOptions = Object.assign({}, options);
                                                             this.headers = wmsOptions.headers || {};
                                                             delete wmsOptions.headers;
                                                             L.TileLayer.WMS.prototype.initialize.call(this, url, wmsOptions);
                                                         },
                                                         createTile: function (coords, done) {
                                                             const url = this.getTileUrl(coords);
                                                             const img = document.createElement('img');
                                                             fetch(url, { headers: this.headers, mode: 'cors' })
                                                                 .then(res => res.blob())
                                                                 .then(blob => {
                                                                     const objectURL = URL.createObjectURL(blob);
                                                                     img.onload = () => { URL.revokeObjectURL(objectURL); done(null, img); };
                                                                     img.src = objectURL;
                                                                 });
                                                             return img;
                                                         }
                                                     });
                                                     L.tileLayer.wms.header = function (url, options) {
                                                         return new L.TileLayer.WMS.Header(url, options);
                                                     };
                                                 }
                                                 L.tileLayer.wms.header('https://map.ir/shiveh', {
                                                     layers: 'Shiveh:Shiveh',
                                                     format: 'image/png',
                                                     headers: { 'x-api-key': this.apiKey }
                                                 }).addTo(this.map);
                                             } else if (this.provider === 'neshan' && this.apiKey) {
                                                 L.tileLayer(`https://api.neshan.org/v5/maps/raster/standard?key=${this.apiKey}`, {
                                                     attribution: 'Neshan Map'
                                                 }).addTo(this.map);
                                             } else {
                                                 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                     attribution: '© OpenStreetMap contributors'
                                                 }).addTo(this.map);
                                             }

                                             this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);

                                             this.map.on('click', (e) => {
                                                 this.marker.setLatLng(e.latlng);
                                                 this.lat = e.latlng.lat;
                                                 this.lng = e.latlng.lng;
                                                 @this.dispatch('updateCoordinates', { lat: e.latlng.lat, lng: e.latlng.lng });
                                                 @this.fetchNewAddressFromCoordinates(e.latlng.lat, e.latlng.lng);
                                             });

                                             this.marker.on('dragend', () => {
                                                 const pos = this.marker.getLatLng();
                                                 this.lat = pos.lat;
                                                 this.lng = pos.lng;
                                                 @this.dispatch('updateCoordinates', { lat: pos.lat, lng: pos.lng });
                                                 @this.fetchNewAddressFromCoordinates(pos.lat, pos.lng);
                                             });

                                             window.addEventListener('mapMoveTo', (e) => {
                                                 const lat = e.detail.lat;
                                                 const lng = e.detail.lng;
                                                 this.lat = lat;
                                                 this.lng = lng;
                                                 if (this.map && this.marker) {
                                                     this.map.setView([lat, lng], 16);
                                                     this.marker.setLatLng([lat, lng]);
                                                 }
                                             });

                                             setTimeout(() => this.map.invalidateSize(), 300);
                                         }
                                     }"
                                     x-init="initMap()">
                                </div>

                                {{-- GPS Button floating inside map container --}}
                                <button type="button" onclick="
                                    if (navigator.geolocation) {
                                        navigator.geolocation.getCurrentPosition(
                                            (position) => {
                                                const lat = position.coords.latitude;
                                                const lng = position.coords.longitude;
                                                window.dispatchEvent(new CustomEvent('mapMoveTo', { detail: { lat: lat, lng: lng } }));
                                                @this.dispatch('updateCoordinates', { lat: lat, lng: lng });
                                                @this.fetchNewAddressFromCoordinates(lat, lng);
                                            },
                                            (error) => {
                                                alert('خطا در دریافت موقعیت از GPS: ' + error.message);
                                            },
                                            { enableHighAccuracy: true, timeout: 8000 }
                                        );
                                    } else {
                                        alert('مرورگر شما از GPS پشتیبانی نمی کند.');
                                    }
                                " class="absolute bottom-2.5 left-2.5 z-[20] bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-indigo-600 dark:text-indigo-400 p-2.5 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 transition-colors flex items-center justify-center" title="موقعیت فعلی من (GPS)">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <circle cx="12" cy="12" r="3" stroke-width="2" />
                                        <circle cx="12" cy="12" r="8" stroke-width="2" />
                                        <path d="M12 2v2M12 20v2M2 12h2M20 12h2" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4"
                             x-data="{
                                 province: @entangle('newProvince'),
                                 city: @entangle('newCity'),
                                 provinces: @js($allProvinces),
                                 cities: [],
                                 provincesData: @js($provincesData),
                                 init() {
                                     if (this.province && this.provincesData[this.province]) {
                                         this.cities = this.provincesData[this.province];
                                     }
                                     this.$watch('province', value => {
                                         this.cities = (value && this.provincesData[value]) ? this.provincesData[value] : [];
                                         if (value && this.cities && !this.cities.includes(this.city)) {
                                             this.city = '';
                                         }
                                     });
                                 }
                             }">
                            {{-- Province Selector Dropdown --}}
                            <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative" wire:ignore>
                                <label class="{{ $labelClass }}">استان <span class="text-red-500">*</span></label>
                                <div @click="open = !open" class="{{ $baseInputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-800': open, 'bg-gray-50 dark:bg-gray-800': !open}">
                                    <span x-text="province || 'انتخاب استان...'" class="block truncate" :class="{'text-gray-400 dark:text-gray-500': !province}"></span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                                <div x-show="open" x-transition class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                    <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                                    <template x-for="p in provinces.filter(item => item.toLowerCase().includes(search.toLowerCase()))" :key="p">
                                        <div @click="province = p; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': province == p, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50': province != p}">
                                            <span x-text="p"></span>
                                            <svg x-show="province == p" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </template>
                                </div>
                                @error('newProvince') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            {{-- City Selector Dropdown --}}
                            <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative" wire:ignore>
                                <label class="{{ $labelClass }}">شهر <span class="text-red-500">*</span></label>
                                <div @click="province ? open = !open : null" class="{{ $baseInputClass }} flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-800 cursor-pointer': open && province, 'bg-gray-50 dark:bg-gray-800 cursor-pointer': !open && province, 'opacity-60 cursor-not-allowed bg-gray-100 dark:bg-gray-800/30': !province}">
                                    <span x-text="city || 'انتخاب شهر...'" class="block truncate" :class="{'text-gray-400 dark:text-gray-500': !city}"></span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open && province}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                                <div x-show="open && province" x-transition class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                    <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                                    <template x-for="c in cities.filter(item => item.toLowerCase().includes(search.toLowerCase()))" :key="c">
                                        <div @click="city = c; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': city == c, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50': city != c}">
                                            <span x-text="c"></span>
                                            <svg x-show="city == c" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </template>
                                </div>
                                @error('newCity') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">عنوان آدرس <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="newTitle" class="{{ $baseInputClass }}" placeholder="مثال: خانه، محل کار">
                            @error('newTitle') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">آدرس دقیق پستی <span class="text-red-500">*</span></label>
                            <textarea wire:model.defer="newAddress" rows="2" class="{{ $baseInputClass }} resize-y min-h-[50px]" placeholder="نام خیابان، کوچه، پلاک، واحد"></textarea>
                            @error('newAddress') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">کد پستی (۱۰ رقمی)</label>
                            <input type="text" wire:model.defer="newPostalCode" class="{{ $baseInputClass }} font-mono text-center dir-ltr" placeholder="1234567890" maxlength="10">
                            @error('newPostalCode') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-4 flex items-center justify-between gap-3 border-t border-gray-100 dark:border-gray-800">
                            @if(!$mustAddAddress)
                                <button type="button" wire:click="toggleAddNewAddress(false)" class="px-5 py-3 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors text-xs font-bold text-center">
                                    انصراف
                                </button>
                            @endif
                            <button type="button" wire:click="saveClientAddress" class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition-all shadow-lg shadow-indigo-500/20 text-xs">ثبت آدرس و تایید موقعیت</button>
                        </div>
                    </div>

                @else
                    {{-- Guest Mode --}}
                    @if($step === 1)
                        {{-- Step 1: Choice screen --}}
                        <div class="space-y-6 animate-in fade-in duration-300">
                            <div class="grid grid-cols-1 gap-4">
                                {{-- Option A: Select on Map --}}
                                <button type="button" wire:click="$set('step', 2)" class="w-full text-right p-5 bg-indigo-50/30 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/60 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-3xl transition-all flex items-center justify-between group active:scale-[0.99]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">تعیین موقعیت روی نقشه</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">محصولات منطقه خود را پیدا کنید.</p>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-all transform group-hover:translate-x-[-4px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                {{-- Option B: Login --}}
                                <a href="{{ route('client.login') }}" class="w-full text-right p-5 bg-gray-50/50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-800 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-3xl transition-all flex items-center justify-between group active:scale-[0.99]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-2xl flex items-center justify-center shrink-0">
                                            <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h7a3 3 0 013 3v1" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">ورود به حساب کاربری</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">آدرس‌های ثبت شده خود را به کار بگیرید.</p>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-all transform group-hover:translate-x-[-4px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </a>

                                {{-- Option C: Skip / Decline --}}
                                <button type="button" wire:click="skipLocationSelection" class="w-full text-right p-5 bg-gray-50/50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-800 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-3xl transition-all flex items-center justify-between group active:scale-[0.99]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-2xl flex items-center justify-center shrink-0">
                                            <svg class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">گشت‌وگذار بدون تعیین آدرس</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مشاهده تمام محصولات بدون محدودیت موقعیت.</p>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-all transform group-hover:translate-x-[-4px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- Step 2: Map selection (Guest) --}}
                        <div class="space-y-4 animate-in fade-in duration-300">
                            {{-- Autocomplete search box --}}
                            <div class="relative" x-data="{ showDropdown: true }">
                                <label class="{{ $labelClass }}">جستجوی آدرس / محله</label>
                                <div class="relative">
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="searchQuery" 
                                           @focus="showDropdown = true" 
                                           class="{{ $baseInputClass }} pl-10" 
                                           placeholder="مثال: تهران، ونک، ملاصدرا...">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                                
                                {{-- Search Results Suggestion Dropdown --}}
                                @if(!empty($searchQuery) && count($searchResults) > 0)
                                    <div x-show="showDropdown" @click.away="showDropdown = false" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-48 overflow-y-auto py-2">
                                        @foreach($searchResults as $res)
                                            <button type="button" 
                                                    wire:click="selectSearchResult({{ $res['lat'] }}, {{ $res['lng'] }}, @js($res['title']))"
                                                    @click="showDropdown = false"
                                                    class="w-full text-right px-4 py-2 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/30 transition-colors flex flex-col gap-0.5 border-b border-gray-100 last:border-0 dark:border-gray-700/50">
                                                <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $res['title'] }}</span>
                                                @if(!empty($res['address']))
                                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ $res['address'] }}</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Selected location indicator --}}
                            @if($selectedProvince || $selectedCity)
                                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-xs text-emerald-800 dark:text-emerald-300 font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>موقعیت یافت شده: {{ $selectedProvince }}، {{ $selectedCity }}</span>
                                </div>
                            @endif

                            {{-- Leaflet Map selector --}}
                            <div class="space-y-2">
                                <label class="{{ $labelClass }}">موقعیت خود را روی نقشه علامت‌گذاری کنید</label>
                                <div class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                                    <div wire:ignore class="w-full h-64 relative z-10 bg-gray-100 dark:bg-gray-900"
                                         x-data="{
                                             map: null,
                                             marker: null,
                                             lat: @entangle('newLat'),
                                             lng: @entangle('newLng'),
                                             provider: @js($mapProvider),
                                             apiKey: @js($mapApiKey),
                                             initMap() {
                                                 if (typeof L === 'undefined') {
                                                     setTimeout(() => this.initMap(), 100);
                                                     return;
                                                 }
                                                 
                                                 this.map = L.map(this.$el).setView([this.lat, this.lng], 15);
                                                 
                                                 if (this.provider === 'map_ir' && this.apiKey) {
                                                     if (!L.TileLayer.WMS.Header) {
                                                         L.TileLayer.WMS.Header = L.TileLayer.WMS.extend({
                                                             initialize: function (url, options) {
                                                                 const wmsOptions = Object.assign({}, options);
                                                                 this.headers = wmsOptions.headers || {};
                                                                 delete wmsOptions.headers;
                                                                 L.TileLayer.WMS.prototype.initialize.call(this, url, wmsOptions);
                                                             },
                                                             createTile: function (coords, done) {
                                                                 const url = this.getTileUrl(coords);
                                                                 const img = document.createElement('img');
                                                                 fetch(url, { headers: this.headers, mode: 'cors' })
                                                                     .then(res => res.blob())
                                                                     .then(blob => {
                                                                         const objectURL = URL.createObjectURL(blob);
                                                                         img.onload = () => { URL.revokeObjectURL(objectURL); done(null, img); };
                                                                         img.src = objectURL;
                                                                     });
                                                                 return img;
                                                             }
                                                         });
                                                         L.tileLayer.wms.header = function (url, options) {
                                                             return new L.TileLayer.WMS.Header(url, options);
                                                         };
                                                     }
                                                     L.tileLayer.wms.header('https://map.ir/shiveh', {
                                                         layers: 'Shiveh:Shiveh',
                                                         format: 'image/png',
                                                         headers: { 'x-api-key': this.apiKey }
                                                     }).addTo(this.map);
                                                 } else if (this.provider === 'neshan' && this.apiKey) {
                                                     L.tileLayer(`https://api.neshan.org/v5/maps/raster/standard?key=${this.apiKey}`, {
                                                         attribution: 'Neshan Map'
                                                     }).addTo(this.map);
                                                 } else {
                                                     L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                         attribution: '© OpenStreetMap contributors'
                                                     }).addTo(this.map);
                                                 }

                                                 this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);

                                                 this.map.on('click', (e) => {
                                                     this.marker.setLatLng(e.latlng);
                                                     this.lat = e.latlng.lat;
                                                     this.lng = e.latlng.lng;
                                                     @this.fetchNewAddressFromCoordinates(e.latlng.lat, e.latlng.lng);
                                                 });

                                                 this.marker.on('dragend', () => {
                                                     const pos = this.marker.getLatLng();
                                                     this.lat = pos.lat;
                                                     this.lng = pos.lng;
                                                     @this.fetchNewAddressFromCoordinates(pos.lat, pos.lng);
                                                 });

                                                 window.addEventListener('mapMoveTo', (e) => {
                                                     const lat = e.detail.lat;
                                                     const lng = e.detail.lng;
                                                     this.lat = lat;
                                                     this.lng = lng;
                                                     if (this.map && this.marker) {
                                                         this.map.setView([lat, lng], 16);
                                                         this.marker.setLatLng([lat, lng]);
                                                     }
                                                 });

                                                 setTimeout(() => this.map.invalidateSize(), 300);
                                             }
                                         }"
                                         x-init="initMap()">
                                    </div>

                                    {{-- GPS Button floating inside map container --}}
                                    <button type="button" onclick="
                                        if (navigator.geolocation) {
                                            navigator.geolocation.getCurrentPosition(
                                                (position) => {
                                                    const lat = position.coords.latitude;
                                                    const lng = position.coords.longitude;
                                                    window.dispatchEvent(new CustomEvent('mapMoveTo', { detail: { lat: lat, lng: lng } }));
                                                    @this.fetchNewAddressFromCoordinates(lat, lng);
                                                },
                                                (error) => {
                                                    alert('خطا در دریافت موقعیت از GPS: ' + error.message);
                                                },
                                                { enableHighAccuracy: true, timeout: 8000 }
                                            );
                                        } else {
                                            alert('مرورگر شما از GPS پشتیبانی نمی کند.');
                                        }
                                    " class="absolute bottom-2.5 left-2.5 z-[20] bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-indigo-600 dark:text-indigo-400 p-2.5 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 transition-colors flex items-center justify-center" title="موقعیت فعلی من (GPS)">
                                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <circle cx="12" cy="12" r="3" stroke-width="2" />
                                            <circle cx="12" cy="12" r="8" stroke-width="2" />
                                            <path d="M12 2v2M12 20v2M2 12h2M20 12h2" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="pt-4 flex items-center justify-between gap-3">
                                <button type="button" wire:click="$set('step', 1)" class="px-5 py-3 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors text-xs font-bold text-center">
                                    بازگشت
                                </button>
                                <button type="button" 
                                        wire:click="confirmGuestLocation" 
                                        @if(!$selectedProvince || !$selectedCity) disabled @endif
                                        class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-bold transition-all shadow-lg shadow-indigo-500/20 dark:shadow-indigo-500/10 text-xs text-center flex-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                    تایید و اعمال موقعیت
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
