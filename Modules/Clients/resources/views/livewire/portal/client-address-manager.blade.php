<div>
    {{-- Leaflet local assets loaded dynamically --}}
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    @if(!$showForm)
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">آدرس‌های من</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">لیست آدرس‌های ثبت شده جهت ارسال سفارشات</p>
            </div>
            <button wire:click="openAddForm" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-500/20 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                افزودن آدرس جدید
            </button>
        </div>

        @if(count($addresses) === 0)
            <div class="flex flex-col items-center justify-center py-12 px-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-3xl bg-gray-50/35 dark:bg-gray-900/10">
                <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">هنوز هیچ آدرسی ثبت نکرده‌اید</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center max-w-sm">برای ثبت سفارش آسان‌تر در فروشگاه، لطفاً آدرس محل دریافت مرسولات خود را روی نقشه مشخص و ذخیره کنید.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($addresses as $addr)
                    <div class="relative bg-white dark:bg-gray-800/40 border {{ $addr->is_default ? 'border-indigo-500 ring-2 ring-indigo-500/10' : 'border-gray-150 dark:border-gray-700/80' }} rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group overflow-hidden">
                        
                        @if($addr->is_default)
                            <div class="absolute top-0 left-0 bg-indigo-500 text-white text-[10px] font-bold px-3 py-1 rounded-br-xl">
                                آدرس پیش‌فرض
                            </div>
                        @endif

                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $addr->is_default ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $addr->title }}</h3>
                            </div>

                            <p class="text-xs text-gray-600 dark:text-gray-350 leading-relaxed font-medium">
                                {{ $addr->province }}، {{ $addr->city }}، {{ $addr->address }}
                            </p>

                            @if($addr->postal_code)
                                <div class="flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400 font-mono">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>کد پستی: {{ $addr->postal_code }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-700/50 mt-5 pt-4">
                            @if(!$addr->is_default)
                                <button wire:click="makeDefault({{ $addr->id }})" class="text-[11px] text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-bold hover:underline transition-colors">
                                    انتخاب به عنوان پیش‌فرض
                                </button>
                            @else
                                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    پیش‌فرض است
                                </span>
                            @endif

                            <div class="flex items-center gap-2">
                                <button wire:click="editAddress({{ $addr->id }})" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/40 text-gray-500 hover:text-indigo-600 transition-colors" title="ویرایش آدرس">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button onclick="confirm('آیا از حذف این آدرس اطمینان دارید؟') || event.stopImmediatePropagation()" wire:click="deleteAddress({{ $addr->id }})" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/40 text-gray-500 hover:text-red-600 transition-colors" title="حذف آدرس">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="mb-6 flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $addressId ? 'ویرایش آدرس' : 'افزودن آدرس جدید' }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مشخصات آدرس و انتخاب روی نقشه</p>
            </div>
            <button wire:click="closeForm" class="p-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-indigo-600 hover:border-indigo-500 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form wire:submit.prevent="saveAddress" class="space-y-6">
            {{-- جستجوی آدرس --}}
            <div class="space-y-2 mb-4 relative" x-data="{ showDropdown: true }">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">جستجوی آدرس / محله</label>
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchQuery" 
                           @focus="showDropdown = true" 
                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 placeholder-gray-400 pl-10" 
                           placeholder="مثال: تهران، ونک، ملاصدرا...">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                
                {{-- Suggestions dropdown --}}
                @if(!empty($searchQuery) && count($searchResults) > 0)
                    <div x-show="showDropdown" @click.away="showDropdown = false" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-48 overflow-y-auto py-2">
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

            {{-- انتخابگر نقشه --}}
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">انتخاب موقعیت روی نقشه</label>
                <div class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div 
                        x-data="{
                            initMap() {
                                let map = null;
                                let marker = null;
                                const lat = $wire.lat || 35.6892;
                                const lng = $wire.lng || 51.3890;
                                const provider = @js($mapProvider);
                                const apiKey = @js($mapApiKey);

                                const setupEvents = (leafletMap) => {
                                    marker = L.marker([lat, lng], { draggable: true }).addTo(leafletMap);
                                    
                                    leafletMap.on('click', (e) => {
                                        const newLat = e.latlng.lat;
                                        const newLng = e.latlng.lng;
                                        marker.setLatLng([newLat, newLng]);
                                        $wire.fetchAddressFromCoordinates(newLat, newLng);
                                    });

                                    marker.on('dragend', (e) => {
                                        const newLat = marker.getLatLng().lat;
                                        const newLng = marker.getLatLng().lng;
                                        $wire.fetchAddressFromCoordinates(newLat, newLng);
                                    });

                                    window.addEventListener('mapMoveTo', (e) => {
                                        const targetLat = e.detail.lat;
                                        const targetLng = e.detail.lng;
                                        if (map && marker) {
                                            map.setView([targetLat, targetLng], 16);
                                            marker.setLatLng([targetLat, targetLng]);
                                        }
                                    });

                                    setTimeout(() => leafletMap.invalidateSize(), 250);
                                };

                                if (provider === 'neshan' && apiKey) {
                                    const initNeshan = () => {
                                        try {
                                            map = new L.Map(this.$el, {
                                                key: apiKey,
                                                maptype: 'dreamy',
                                                poi: true,
                                                traffic: false,
                                                center: [lat, lng],
                                                zoom: 15
                                            });
                                            setupEvents(map);
                                        } catch (err) {
                                            // Handle error
                                        }
                                    };

                                    if (typeof L !== 'undefined' && L.Map && L.Map.prototype.addGoogleLayer) {
                                        initNeshan();
                                    } else {
                                        const css = document.createElement('link');
                                        css.href = 'https://static.neshan.org/sdk/leaflet/1.4.0/leaflet.css';
                                        css.rel = 'stylesheet';
                                        document.head.appendChild(css);
                                        
                                        const script = document.createElement('script');
                                        script.src = 'https://static.neshan.org/sdk/leaflet/1.4.0/leaflet.js';
                                        script.onload = initNeshan;
                                        document.head.appendChild(script);
                                    }
                                } else {
                                    if (typeof L === 'undefined') {
                                        return;
                                    }

                                    map = L.map(this.$el).setView([lat, lng], 15);

                                    if (provider === 'map_ir' && apiKey) {
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

                                                    fetch(url, {
                                                        method: 'GET',
                                                        headers: this.headers,
                                                        mode: 'cors'
                                                    })
                                                    .then(response => {
                                                        if (!response.ok) throw new Error('Network response was not ok');
                                                        return response.blob();
                                                    })
                                                    .then(blob => {
                                                        const objectURL = URL.createObjectURL(blob);
                                                        img.onload = () => {
                                                            URL.revokeObjectURL(objectURL);
                                                            done(null, img);
                                                        };
                                                        img.onerror = () => {
                                                            done(new Error('Image load error'), img);
                                                        };
                                                        img.src = objectURL;
                                                    })
                                                    .catch(error => {
                                                        done(error, img);
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
                                            transparent: true,
                                            maxZoom: 18,
                                            headers: {
                                                'x-api-key': apiKey
                                            },
                                            attribution: '&copy; Map.ir'
                                        }).addTo(map);
                                    } else {
                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            maxZoom: 18,
                                            attribution: '&copy; OpenStreetMap'
                                        }).addTo(map);
                                    }
                                    setupEvents(map);
                                }
                            }
                        }"
                        x-init="initMap()"
                        id="address-map" 
                        wire:ignore 
                        class="w-full h-80 bg-gray-100 dark:bg-gray-900" 
                        style="z-index: 10;">
                    </div>

                    {{-- GPS locator --}}
                    <button type="button" onclick="
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    const userLat = position.coords.latitude;
                                    const userLng = position.coords.longitude;
                                    window.dispatchEvent(new CustomEvent('mapMoveTo', { detail: { lat: userLat, lng: userLng } }));
                                    $wire.fetchAddressFromCoordinates(userLat, userLng);
                                },
                                (error) => {
                                    alert('خطا در دریافت موقعیت از GPS: ' + error.message);
                                },
                                { enableHighAccuracy: true, timeout: 8000 }
                            );
                        } else {
                            alert('مرورگر شما از GPS پشتیبانی نمی کند.');
                        }
                    " class="absolute bottom-12 left-2.5 z-[20] bg-white hover:bg-gray-100 text-indigo-650 p-2.5 rounded-xl shadow-md border border-gray-200 transition-colors flex items-center justify-center" title="موقعیت فعلی من (GPS)">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="3" stroke-width="2" />
                            <circle cx="12" cy="12" r="8" stroke-width="2" />
                            <path d="M12 2v2M12 20v2M2 12h2M20 12h2" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>

                    <div class="absolute bottom-2 right-2 z-[20] bg-white/90 dark:bg-gray-800/90 backdrop-blur-md px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-[10px] text-gray-600 dark:text-gray-300 font-medium">
                        برای تغییر مکان، نشانگر را بکشید یا روی نقشه کلیک کنید.
                    </div>
                </div>
            </div>

            {{-- فیلدهای جزئیات آدرس --}}
            <div class="relative">
                <div wire:loading wire:target="fetchAddressFromCoordinates" class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-[3px] z-[40] flex items-center justify-center rounded-3xl transition-all">
                    <div class="flex flex-col items-center gap-3 bg-white dark:bg-gray-800 px-6 py-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl">
                        <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">در حال دریافت نشانی روی نقشه...</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="address_title" class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">عنوان آدرس (خانه، محل کار و...)</label>
                        <input type="text" id="address_title" wire:model.defer="title" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900" placeholder="مثال: خانه">
                        @error('title') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="address-postal-input" class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">کد پستی</label>
                        <input type="text" id="address-postal-input" wire:model.defer="postal_code" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 font-mono text-center dir-ltr" placeholder="1234567890">
                        @error('postal_code') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @php
                        $jsonPath = base_path('Modules/Clients/resources/data/iran-provinces-cities.json');
                        $provincesData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
                        $allProvinces = array_keys($provincesData);
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2"
                         x-data="{
                             province: @entangle('province'),
                             city: @entangle('city'),
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
                        
                        {{-- Province Selector --}}
                        <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative" wire:ignore>
                            <label class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">استان <span class="text-red-500">*</span></label>
                            <div @click="open = !open" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 cursor-pointer flex justify-between items-center select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 bg-white dark:bg-gray-800': open, 'bg-gray-50 dark:bg-gray-900/50': !open}">
                                <span x-text="province || 'انتخاب استان...'" class="block truncate" :class="{'text-gray-400 dark:text-gray-500': !province}"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="open" x-transition class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-48 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                                <template x-for="p in provinces.filter(item => item.toLowerCase().includes(search.toLowerCase()))" :key="p">
                                    <div @click="province = p; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': province == p, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': province != p}">
                                        <span x-text="p"></span>
                                        <svg x-show="province == p" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </template>
                            </div>
                            @error('province') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- City Selector --}}
                        <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative" wire:ignore>
                            <label class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">شهر <span class="text-red-500">*</span></label>
                            <div @click="province ? open = !open : null" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 flex justify-between items-center select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 bg-white dark:bg-gray-800 cursor-pointer': open && province, 'bg-gray-50 dark:bg-gray-900/50 cursor-pointer': !open && province, 'opacity-60 cursor-not-allowed bg-gray-100 dark:bg-gray-900/30': !province}">
                                <span x-text="city || 'انتخاب شهر...'" class="block truncate" :class="{'text-gray-400 dark:text-gray-500': !city}"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500': open && province}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="open && province" x-transition class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-48 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                                <template x-for="c in cities.filter(item => item.toLowerCase().includes(search.toLowerCase()))" :key="c">
                                    <div @click="city = c; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': city == c, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': city != c}">
                                        <span x-text="c"></span>
                                        <svg x-show="city == c" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </template>
                            </div>
                            @error('city') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="address-text-input" class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">نشانی دقیق پستی</label>
                        <textarea id="address-text-input" wire:model="address" rows="3" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 resize-y min-h-[80px]"></textarea>
                        @error('address') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center h-full pt-1 cursor-pointer select-none">
                            <input type="checkbox" wire:model.defer="is_default" value="1" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors" />
                            <span class="mr-3 text-sm font-semibold text-gray-700 dark:text-gray-300">قرار دادن به عنوان آدرس پیش‌فرض جهت ارسال سفارشات</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- دکمه‌های عملیاتی --}}
            <div class="flex items-center gap-3 border-t border-gray-100 dark:border-gray-700 pt-5">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95">
                    ذخیره آدرس
                </button>
                <button type="button" wire:click="closeForm" class="px-6 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-300 text-sm font-bold rounded-xl transition-colors">
                    انصراف
                </button>
            </div>
        </form>
    @endif
</div>
