@php
    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400
    focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200
    dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

<div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/40 dark:shadow-none border border-gray-100 dark:border-gray-700 overflow-hidden">

    {{-- نوار پیشرفت (RTL Fix) --}}
    <div class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700 p-6 sm:px-10">
        <div class="flex items-center justify-between relative dir-rtl">
            <div class="absolute right-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-200 dark:bg-gray-700 rounded-full z-0"></div>
            <div class="absolute right-0 top-1/2 -translate-y-1/2 h-1 bg-indigo-600 rounded-full z-0 transition-all duration-500" style="width: {{ ($currentStep - 1) * 33.33 }}%;"></div>

            @foreach(['اطلاعات پایه', 'مالی', 'آدرس انبار', 'مدارک'] as $index => $stepName)
                @php $stepNum = $index + 1; @endphp
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300 {{ $currentStep >= $stepNum ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/40' : 'bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600 text-gray-400' }}">
                        @if($currentStep > $stepNum)
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $stepNum }}
                        @endif
                    </div>
                    <span class="mt-2 text-xs font-medium {{ $currentStep >= $stepNum ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }} hidden sm:block">{{ $stepName }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="p-6 sm:p-10 min-h-[400px]">

        {{-- مرحله 1 --}}
        @if($currentStep === 1)
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                {{-- 💡 نمایش دلیل رد به صورت کاملا واضح --}}
                @if($vendor?->kyc_status === 'rejected')
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
                        <strong class="block mb-2 text-base">درخواست شما نیاز به اصلاح دارد:</strong>
                        <p>{{ $kyc_rejection_reason ?? 'برخی از اطلاعات یا مدارک شما توسط کارشناسان رد شده است. لطفاً موارد را اصلاح کنید.' }}</p>
                    </div>
                @endif

                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">اطلاعات پایه کسب‌و‌کار</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">نام فروشگاه <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="store_name" class="{{ $baseInputClass }}" placeholder="نامی که مشتریان می‌بینند">
                        @error('store_name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">تلفن پشتیبانی <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="support_phone" class="{{ $baseInputClass }}" placeholder="جهت تماس مشتریان">
                        @error('support_phone') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">نوع شخص <span class="text-red-500">*</span></label>
                        <div class="flex gap-4 mt-2">
                            <label class="flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 rounded-xl flex-1 hover:border-indigo-500 transition-all">
                                <input type="radio" wire:model.live="legal_type" value="real" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium dark:text-gray-200">شخص حقیقی (فرد عادی)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 rounded-xl flex-1 hover:border-indigo-500 transition-all">
                                <input type="radio" wire:model.live="legal_type" value="legal" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium dark:text-gray-200">شخص حقوقی (شرکت)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">{{ $legal_type === 'legal' ? 'شناسه ملی شرکت' : 'کد ملی' }} <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="national_code" class="{{ $baseInputClass }} dir-ltr text-right" @if($vendor?->exists) disabled @endif>
                        @error('national_code') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if($legal_type === 'legal')
                        <div class="animate-in fade-in zoom-in duration-300">
                            <label class="{{ $labelClass }}">کد اقتصادی <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="economic_code" class="{{ $baseInputClass }} dir-ltr text-right" @if($vendor?->exists) disabled @endif>
                            @error('economic_code') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- مرحله 2: مالی --}}
        @if($currentStep === 2)
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">اطلاعات بانکی برای تسویه‌حساب</h2>
                <div class="grid grid-cols-1 gap-6 mt-6">
                    <div>
                        <label class="{{ $labelClass }}">شماره شبا <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" wire:model.defer="shaba_number" class="{{ $baseInputClass }} dir-ltr pl-12 font-mono text-lg tracking-widest" maxlength="24">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 font-bold">IR</div>
                        </div>
                        @error('shaba_number') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">نام و نام خانوادگی صاحب حساب <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="account_owner_name" class="{{ $baseInputClass }}">
                            @error('account_owner_name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">نام بانک</label>
                            <input type="text" wire:model.defer="bank_name" class="{{ $baseInputClass }}">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- مرحله 3: آدرس --}}
        @if($currentStep === 3)
            @push('styles')
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            @endpush
            @push('scripts')
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            @endpush

            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">آدرس دفتر / انبار</h2>
                @php
                    $jsonPath = base_path('Modules/Clients/resources/data/iran-provinces-cities.json');
                    $provincesData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
                    $allProvinces = array_keys($provincesData);
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"
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
                    <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                        <label class="{{ $labelClass }}">استان <span class="text-red-500">*</span></label>
                        <div @click="open = !open" class="{{ $baseInputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-800': open, 'bg-gray-50 dark:bg-gray-900/50': !open}">
                            <span x-text="province || 'انتخاب استان...'" class="block truncate" :class="{'text-gray-400 dark:text-gray-500': !province}"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                        <div x-show="open" x-transition class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-850 backdrop-blur-xl border border-gray-100 dark:border-gray-750 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                            <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 text-gray-900 dark:text-gray-150">
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
                    <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                        <label class="{{ $labelClass }}">شهر <span class="text-red-500">*</span></label>
                        <div @click="province ? open = !open : null" class="{{ $baseInputClass }} flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-800 cursor-pointer': open && province, 'bg-gray-50 dark:bg-gray-900/50 cursor-pointer': !open && province, 'opacity-60 cursor-not-allowed bg-gray-100 dark:bg-gray-900/30': !province}">
                            <span x-text="city || 'انتخاب شهر...'" class="block truncate" :class="{'text-gray-400 dark:text-gray-500': !city}"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open && province}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                        <div x-show="open && province" x-transition class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-850 backdrop-blur-xl border border-gray-100 dark:border-gray-750 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                            <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 text-gray-900 dark:text-gray-150">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">آدرس دقیق <span class="text-red-500">*</span></label>
                        <textarea wire:model.defer="address" rows="2" class="{{ $baseInputClass }} resize-none"></textarea>
                        @error('address') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    {{-- 💡 کد پستی اضافه شد --}}
                    <div>
                        <label class="{{ $labelClass }}">کد پستی انبار/دفتر</label>
                        <input type="text" wire:model.defer="postal_code" class="{{ $baseInputClass }} dir-ltr text-right" placeholder="10 رقمی">
                        @error('postal_code') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Map Pinning for Vendor --}}
                <div class="mt-6 space-y-2">
                    <label class="{{ $labelClass }}">انتخاب موقعیت دقیق انبار/دفتر روی نقشه</label>
                    <div wire:ignore class="w-full h-64 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 relative z-10"
                         x-data="{
                             map: null,
                             marker: null,
                             lat: @entangle('latitude'),
                             lng: @entangle('longitude'),
                             provider: @js($mapProvider),
                             apiKey: @js($mapApiKey),
                             initMap() {
                                 if (typeof L === 'undefined') {
                                     setTimeout(() => this.initMap(), 100);
                                     return;
                                 }
                                 
                                 this.map = L.map(this.$el).setView([this.lat, this.lng], 15);
                                 
                                 if (this.provider === 'map_ir' && this.apiKey) {
                                     // Map.ir Raster setup
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
                                     // Neshan Raster setup
                                     L.tileLayer(`https://api.neshan.org/v5/maps/raster/standard?key=${this.apiKey}`, {
                                         attribution: 'Neshan Map'
                                     }).addTo(this.map);
                                 } else {
                                     // OpenStreetMap fallback
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

                                 setTimeout(() => this.map.invalidateSize(), 300);
                             }
                         }"
                         x-init="initMap()">
                    </div>
                </div>
            </div>
        @endif

        {{-- مرحله 4: مدارک (سیستم قفل هوشمند و نمایش خطا) --}}
        @if($currentStep === 4)
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">آپلود مدارک هویتی</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

                    {{-- کارت ملی --}}
                    <div class="border-2 border-dashed {{ $existingNationalCard?->status === 'approved' ? 'border-emerald-300 bg-emerald-50/50 dark:border-emerald-700 dark:bg-emerald-900/10' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800/50' }} rounded-2xl p-6 text-center relative overflow-hidden transition-colors">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">تصویر روی کارت ملی <span class="text-red-500">*</span></label>

                        @if($existingNationalCard && $existingNationalCard->status === 'approved')
                            <div class="flex flex-col items-center justify-center p-4">
                                <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-800/50 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-emerald-700 dark:text-emerald-400 font-bold">مدرک تایید شده است</span>
                                <span class="text-xs text-gray-500 mt-1">نیازی به آپلود مجدد نیست</span>
                            </div>
                        @else
                            @if($existingNationalCard?->status === 'rejected')
                                <div class="bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 p-2 rounded-lg text-xs mb-4 text-right">
                                    <strong>رد شده:</strong> {{ $existingNationalCard->rejection_reason }}
                                </div>
                            @elseif($existingNationalCard?->status === 'pending')
                                <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 p-2 rounded-lg text-xs mb-4">
                                    مدرک قبلی در حال بررسی است. در صورت نیاز فایل جدید آپلود کنید.
                                </div>
                            @endif

                            @if($nationalCardFile)
                                <img src="{{ $nationalCardFile->temporaryUrl() }}" class="h-32 w-full object-cover rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-3">
                            @elseif($existingNationalCard)
                                <img src="{{ Storage::url($existingNationalCard->file_path) }}" class="h-32 w-full object-cover rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-3 opacity-50 grayscale hover:grayscale-0 transition-all">
                            @else
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            @endif

                            <div class="mt-4 relative">
                                <input type="file" wire:model="nationalCardFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*">
                                <button class="w-full py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl text-sm font-medium hover:bg-indigo-100 dark:hover:bg-indigo-800/50 transition-colors">
                                    {{ $nationalCardFile ? 'تغییر فایل' : 'انتخاب فایل' }}
                                </button>
                            </div>

                            <div wire:loading wire:target="nationalCardFile" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center z-20 rounded-2xl">
                                <span class="text-indigo-600 font-bold flex flex-col items-center">
                                    <svg class="animate-spin h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    در حال بارگذاری...
                                </span>
                            </div>
                        @endif
                        @error('nationalCardFile') <span class="text-xs text-red-500 block mt-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- جواز کسب (هوشمند) --}}
                    @if($legal_type === 'legal' || $existingBusinessLicense || $businessLicenseFile)
                        <div class="border-2 border-dashed {{ $existingBusinessLicense?->status === 'approved' ? 'border-emerald-300 bg-emerald-50/50 dark:border-emerald-700 dark:bg-emerald-900/10' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800/50' }} rounded-2xl p-6 text-center relative overflow-hidden transition-colors animate-in fade-in zoom-in duration-300">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">تصویر روزنامه رسمی / جواز کسب @if($legal_type === 'legal')<span class="text-red-500">*</span>@endif</label>

                            @if($existingBusinessLicense && $existingBusinessLicense->status === 'approved')
                                <div class="flex flex-col items-center justify-center p-4">
                                    <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-800/50 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span class="text-emerald-700 dark:text-emerald-400 font-bold">مدرک تایید شده است</span>
                                    <span class="text-xs text-gray-500 mt-1">نیازی به آپلود مجدد نیست</span>
                                </div>
                            @else
                                @if($existingBusinessLicense?->status === 'rejected')
                                    <div class="bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 p-2 rounded-lg text-xs mb-4 text-right">
                                        <strong>رد شده:</strong> {{ $existingBusinessLicense->rejection_reason }}
                                    </div>
                                @elseif($existingBusinessLicense?->status === 'pending')
                                    <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 p-2 rounded-lg text-xs mb-4">
                                        مدرک قبلی در حال بررسی است. در صورت نیاز فایل جدید آپلود کنید.
                                    </div>
                                @endif

                                @if($businessLicenseFile)
                                    <img src="{{ $businessLicenseFile->temporaryUrl() }}" class="h-32 w-full object-cover rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-3">
                                @elseif($existingBusinessLicense)
                                    <img src="{{ Storage::url($existingBusinessLicense->file_path) }}" class="h-32 w-full object-cover rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-3 opacity-50 grayscale hover:grayscale-0 transition-all">
                                @else
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @endif

                                <div class="mt-4 relative">
                                    <input type="file" wire:model="businessLicenseFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*">
                                    <button class="w-full py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl text-sm font-medium hover:bg-indigo-100 dark:hover:bg-indigo-800/50 transition-colors">
                                        {{ $businessLicenseFile ? 'تغییر فایل' : 'انتخاب فایل' }}
                                    </button>
                                </div>

                                <div wire:loading wire:target="businessLicenseFile" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center z-20 rounded-2xl">
                                    <span class="text-indigo-600 font-bold flex flex-col items-center">
                                        <svg class="animate-spin h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        در حال بارگذاری...
                                    </span>
                                </div>
                            @endif
                            @error('businessLicenseFile') <span class="text-xs text-red-500 block mt-2">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    {{-- فوتر و دکمه‌های کنترل --}}
    <div class="bg-gray-50/50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 p-6 flex items-center justify-between">
        <div>
            @if($currentStep > 1)
                <button wire:click="previousStep" class="px-6 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-sm font-medium">
                    مرحله قبل
                </button>
            @endif
        </div>
        <div>
            @if($currentStep < 4)
                <button wire:click="nextStep" class="px-8 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-all text-sm font-medium flex items-center gap-2">
                    مرحله بعد
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            @else
                <button wire:click="submit" wire:loading.attr="disabled" class="px-8 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition-all text-sm font-bold flex items-center gap-2">
                    <span wire:loading.remove wire:target="submit">ثبت نهایی و ارسال مدارک</span>
                    <span wire:loading wire:target="submit">در حال پردازش...</span>
                </button>
            @endif
        </div>
    </div>
</div>
