<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 animate-in fade-in slide-in-from-bottom-4 duration-700">
    @includeIf('partials.jalali-date-picker')

    @php
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-800 dark:focus:border-indigo-500";
        $labelClass = "block text-sm font-bold text-gray-800 dark:text-gray-200 mb-2";
    @endphp

    {{-- Main Content: Checkout Form --}}
    <div class="lg:col-span-7">
        <form wire:submit.prevent="submit" class="space-y-8">
            @foreach($groupedSchema as $group)
                @php
                    $addressSelectorRendered = false;
                @endphp
                <div class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm" wire:key="group-{{ $group['id'] }}">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $group['name'] }}</h2>
                        @if($loop->first)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">اطلاعات خود را برای تکمیل سفارش وارد کنید.</p>
                        @endif
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @foreach($group['fields'] as $field)
                                @php
                                    $isAddressField = ($field['type'] === 'select-province-city' ||
                                                       $field['type'] === 'postal-code' ||
                                                       $field['id'] === 'address' ||
                                                       str_contains($field['id'], 'address') ||
                                                       str_contains($field['id'], 'postal') ||
                                                       str_contains($field['id'], 'postcode'));
                                @endphp

                                @if(Auth::guard('client')->check() && $isAddressField)
                                    @if(!$addressSelectorRendered)
                                        @php $addressSelectorRendered = true; @endphp
                                        {{-- Render the Address Selector in place of the first address field --}}
                                        <div class="sm:col-span-2 space-y-4" wire:key="checkout-address-selector-block-{{ $group['id'] }}">
                                            <div class="bg-gray-50/50 dark:bg-gray-800/25 border border-gray-200 dark:border-gray-700/60 rounded-2xl p-5 space-y-4">
                                                <div class="flex justify-between items-center pb-3 border-b border-gray-150 dark:border-gray-700/60">
                                                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        </svg>
                                                        نشانی تحویل سفارش
                                                    </h3>
                                                    
                                                    <button type="button" wire:click="openNewAddressModal" class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-bold flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        ثبت آدرس جدید
                                                    </button>
                                                </div>

                                                @if(count($addresses) > 0)
                                                    @php
                                                        $selectedAddress = collect($addresses)->firstWhere('id', $selectedAddressId) ?? $addresses[0];
                                                    @endphp
                                                    
                                                    <div class="relative bg-white dark:bg-gray-800/60 border border-indigo-500/20 rounded-xl p-4 shadow-sm">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <div class="flex items-center gap-2">
                                                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $selectedAddress->title }}</span>
                                                                @if($selectedAddress->is_default)
                                                                    <span class="text-[9px] font-bold px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-md">پیش‌فرض</span>
                                                                @endif
                                                            </div>
                                                            
                                                            {{-- Dropdown selector to change address --}}
                                                            <div x-data="{ open: false }" class="relative">
                                                                <button type="button" @click="open = !open" class="text-[11px] text-gray-500 hover:text-indigo-650 dark:text-indigo-400 font-bold flex items-center gap-1 select-none">
                                                                    تغییر آدرس
                                                                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                    </svg>
                                                                </button>
                                                                
                                                                <div x-show="open" @click.away="open = false" x-transition class="absolute left-0 mt-2 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 py-1.5 overflow-hidden" style="display: none;">
                                                                    @foreach($addresses as $addr)
                                                                        <button type="button" @click="open = false" wire:click="selectAddress({{ $addr->id }})" class="w-full text-right px-4 py-2.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-700/30 flex flex-col gap-1 border-b border-gray-100 dark:border-gray-700/60 last:border-0 transition-colors">
                                                                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $addr->title }} {{ $addr->is_default ? '(پیش‌فرض)' : '' }}</span>
                                                                            <span class="text-gray-500 dark:text-gray-400 truncate">{{ $addr->province }}، {{ $addr->city }}، {{ $addr->address }}</span>
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-semibold">
                                                            {{ $selectedAddress->province }}، {{ $selectedAddress->city }}، {{ $selectedAddress->address }}
                                                        </p>
                                                        @if($selectedAddress->postal_code)
                                                            <div class="text-[10px] text-gray-500 mt-2 font-mono">کد پستی: {{ $selectedAddress->postal_code }}</div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="text-center py-6 space-y-3 bg-white dark:bg-gray-800/40 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">هنوز هیچ آدرسی ثبت نکرده‌اید.</p>
                                                        <button type="button" wire:click="openNewAddressModal" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-650 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-95">
                                                            ثبت اولین آدرس روی نقشه
                                                        </button>
                                                    </div>
                                                @endif

                                                {{-- Details Toggle --}}
                                                @if(count($addresses) > 0)
                                                    <div class="flex justify-start">
                                                        <button type="button" wire:click="$toggle('showManualAddress')" class="text-[10px] text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold flex items-center gap-1 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            {{ $showManualAddress ? 'پنهان کردن جزئیات آدرس' : 'مشاهده و ویرایش جزئیات آدرس به‌صورت دستی' }}
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Only render the dynamic fields when toggled manually --}}
                                    @if($showManualAddress)
                                        @php
                                            $modelName = 'formData.' . $field['id'];
                                            $htmlId = 'form-field-' . str_replace('.', '_', $field['id']);
                                            $widthClass = match($field['width'] ?? 'full') {
                                                '1/2' => 'sm:col-span-1',
                                                default => 'sm:col-span-2',
                                            };
                                            $currentValue = $formData[$field['id']] ?? '';
                                        @endphp
                                        @include('market::livewire.web.partials._checkout-form-fields')
                                    @endif
                                @else
                                    {{-- Guest or non-address fields - Render normal dynamic inputs --}}
                                    @php
                                        $modelName = 'formData.' . $field['id'];
                                        $htmlId = 'form-field-' . str_replace('.', '_', $field['id']);
                                        $widthClass = match($field['width'] ?? 'full') {
                                            '1/2' => 'sm:col-span-1',
                                            default => 'sm:col-span-2',
                                        };
                                        $currentValue = $formData[$field['id']] ?? '';
                                    @endphp
                                    @include('market::livewire.web.partials._checkout-form-fields')
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Payment Method Selection Card --}}
            <div class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">روش پرداخت</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">لطفاً روش پرداخت ترجیحی خود را انتخاب کنید.</p>
                </div>

                <div class="space-y-3">
                    @forelse($paymentMethods as $key => $details)
                        <div
                            wire:key="payment-method-page-{{$key}}"
                            wire:click="$set('payment_method', '{{ $key }}')"
                            class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all duration-300
                                {{ $payment_method === $key
                                    ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-600 ring-2 ring-indigo-500/50'
                                    : 'bg-gray-50 dark:bg-gray-800/30 border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                                }}"
                        >
                            <input type="radio" name="payment_method" value="{{ $key }}" class="sr-only" {{ $payment_method === $key ? 'checked' : '' }}>

                            <div class="flex-shrink-0 w-12 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                @if(!empty($details['logo']))
                                    <img src="{{ asset($details['logo']) }}" alt="{{ $details['title'] }}" class="max-h-6 max-w-10 object-contain">
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                @endif
                            </div>

                            <div class="ml-3 mr-3 text-sm flex-grow">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $details['title'] }}</span>
                                @if($details['description'] ?? null)
                                    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">{{ $details['description'] }}</p>
                                @endif
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all {{ $payment_method === $key ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 dark:border-gray-500 bg-white dark:bg-gray-700' }}">
                                <div class="w-2 h-2 rounded-full bg-white dark:bg-gray-700 transition-transform {{ $payment_method === $key ? 'scale-100' : 'scale-0' }}"></div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            در حال حاضر هیچ روش پرداخت فعالی وجود ندارد.
                        </div>
                    @endforelse
                </div>
                @error('payment_method') <span class="text-xs text-red-505 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </form>
    </div>

    {{-- Sidebar: Order Summary --}}
    <div class="lg:col-span-5">
        <div class="sticky top-28">
            <div class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">خلاصه سفارش</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div class="space-y-4 max-h-72 overflow-y-auto pr-2 -ml-2">
                        @foreach($cartItems as $item)
                            <div class="flex items-center justify-between" wire:key="cart-item-{{ $item['variant_id'] }}">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                                        <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $item['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $item['quantity'] }} عدد</p>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <p class="font-bold text-sm text-gray-900 dark:text-white">{{ number_format($item['price'] * $item['quantity']) }} <span class="text-[10px] text-gray-550">تومان</span></p>
                                    @if($item['price'] < $item['base_price'])
                                        <p class="text-xs text-gray-400 line-through">{{ number_format($item['base_price'] * $item['quantity']) }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-5 space-y-3">
                        <div class="flex justify-between text-sm">
                            <p class="text-gray-600 dark:text-gray-400">جمع کل سبد خرید:</p>
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($totals['subtotal']) }} <span class="text-xs">تومان</span></p>
                        </div>
                        <div class="flex justify-between text-sm">
                            <p class="text-gray-600 dark:text-gray-400">تخفیف شما:</p>
                            <p class="font-medium text-red-500">-{{ number_format($totals['discount']) }} <span class="text-xs">تومان</span></p>
                        </div>
                        <div class="flex justify-between text-base font-bold pt-2 border-t border-dashed border-gray-200 dark:border-gray-700 mt-3">
                            <p class="text-gray-900 dark:text-white">مبلغ قابل پرداخت:</p>
                            <p class="text-indigo-600 dark:text-indigo-400">{{ number_format($totals['grand_total']) }} <span class="text-xs">تومان</span></p>
                        </div>
                    </div>

                    <button wire:click="submit" wire:loading.attr="disabled"
                            class="w-full mt-4 px-6 py-4 rounded-xl bg-indigo-600 text-white font-bold text-base shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submit">
                            پرداخت و ثبت نهایی سفارش
                        </span>
                        <span wire:loading wire:target="submit">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>در حال پردازش...</span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="text-xs text-gray-400 dark:text-gray-500">پرداخت امن از طریق درگاه‌های رسمی کشور</p>
            </div>
        </div>
    </div>

    {{-- Modal for New Address Quick Creation --}}
    @if($showNewAddressModal)
        <div wire:key="checkout-new-address-modal" class="fixed inset-0 z-[100] flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none" style="backdrop-filter: blur(8px); background-color: rgba(0, 0, 0, 0.4);">
            <div class="relative w-full max-w-2xl mx-auto my-6 px-4">
                <div class="relative flex flex-col w-full bg-white dark:bg-gray-900 border-0 rounded-3xl shadow-2xl outline-none focus:outline-none overflow-hidden max-h-[95vh] dark:border dark:border-gray-700">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between p-5 border-b border-solid border-gray-150 dark:border-gray-700 rounded-t-3xl">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">ثبت آدرس جدید روی نقشه</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">موقعیت آدرس خود را روی نقشه مشخص کرده و ذخیره کنید.</p>
                        </div>
                        <button type="button" wire:click="closeNewAddressModal" class="p-1.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-indigo-600 hover:border-indigo-500 transition-all">
                                                {{-- Autocomplete search box --}}
                        <div class="space-y-1 relative" x-data="{ showDropdown: true }">
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">جستجوی آدرس / محله</label>
                            <div class="relative">
                                <input type="text" 
                                       wire:model.live.debounce.300ms="searchQuery" 
                                       @focus="showDropdown = true" 
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 placeholder-gray-400 pl-10" 
                                       placeholder="مثال: تهران، ونک، ملاصدرا...">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                            
                            {{-- Suggestions dropdown --}}
                            @if(!empty($searchQuery) && count($searchResults) > 0)
                                <div x-show="showDropdown" @click.away="showDropdown = false" class="absolute z-50 w-full mt-1 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-150 dark:border-gray-700 rounded-2xl shadow-xl max-h-40 overflow-y-auto py-1">
                                    @foreach($searchResults as $res)
                                        <button type="button" 
                                                wire:click="selectSearchResult({{ $res['lat'] }}, {{ $res['lng'] }}, '{{ addslashes($res['title']) }}')"
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

                        {{-- Map Picker --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">انتخاب موقعیت روی نقشه</label>
                            <div class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                                <div 
                                    x-data="{
                                        initNewAddressMap() {
                                            let map = null;
                                            let marker = null;
                                            const lat = $wire.newLat || 35.6892;
                                            const lng = $wire.newLng || 51.3890;
                                            const provider = @js($mapProvider);
                                            const apiKey = @js($mapApiKey);

                                            const setupEvents = (leafletMap) => {
                                                marker = L.marker([lat, lng], { draggable: true }).addTo(leafletMap);
                                                
                                                leafletMap.on('click', (e) => {
                                                    const newLat = e.latlng.lat;
                                                    const newLng = e.latlng.lng;
                                                    marker.setLatLng([newLat, newLng]);
                                                    $wire.fetchNewAddressFromCoordinates(newLat, newLng);
                                                });

                                                marker.on('dragend', (e) => {
                                                    const newLat = marker.getLatLng().lat;
                                                    const newLng = marker.getLatLng().lng;
                                                    $wire.fetchNewAddressFromCoordinates(newLat, newLng);
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

                                            const initDefaultMap = () => {
                                                if (typeof L === 'undefined') return;
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
                                                        headers: { 'x-api-key': apiKey },
                                                        attribution: '&copy; Map.ir'
                                                    }).addTo(map);
                                                } else {
                                                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                        maxZoom: 18,
                                                        attribution: '&copy; OpenStreetMap'
                                                    }).addTo(map);
                                                }
                                                setupEvents(map);
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
                                                    } catch (err) {}
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
                                                if (typeof L !== 'undefined') {
                                                    initDefaultMap();
                                                } else {
                                                    const css = document.createElement('link');
                                                    css.href = '{{ asset("vendor/leaflet/leaflet.css") }}';
                                                    css.rel = 'stylesheet';
                                                    document.head.appendChild(css);
                                                    
                                                    const script = document.createElement('script');
                                                    script.src = '{{ asset("vendor/leaflet/leaflet.js") }}';
                                                    script.onload = initDefaultMap;
                                                    document.head.appendChild(script);
                                                }
                                            }
                                        }
                                    }"
                                    x-init="initNewAddressMap()"
                                    id="new-address-map" 
                                    wire:ignore 
                                    class="w-full h-56 bg-gray-100 dark:bg-gray-900" 
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
                                                $wire.fetchNewAddressFromCoordinates(userLat, userLng);
                                            },
                                            (error) => {
                                                alert('خطا در دریافت موقعیت از GPS: ' + error.message);
                                            },
                                            { enableHighAccuracy: true, timeout: 8000 }
                                        );
                                    } else {
                                        alert('مرورگر شما از GPS پشتیبانی نمی کند.');
                                    }
                                " class="absolute bottom-10 left-2.5 z-[20] bg-white hover:bg-gray-100 text-indigo-650 p-2.5 rounded-xl shadow-md border border-gray-200 transition-colors flex items-center justify-center" title="موقعیت فعلی من (GPS)">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <circle cx="12" cy="12" r="3" stroke-width="2" />
                                        <circle cx="12" cy="12" r="8" stroke-width="2" />
                                        <path d="M12 2v2M12 20v2M2 12h2M20 12h2" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                </button>

                                <div class="absolute bottom-2 right-2 z-[20] bg-white/90 dark:bg-gray-800/90 backdrop-blur-md px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-[9px] text-gray-600 dark:text-gray-300 font-medium select-none">
                                    برای تغییر مکان، نشانگر را بکشید یا روی نقشه کلیک کنید.
                                </div>
                            </div>
                        </div>

                        {{-- Details Inputs --}}
                        <div class="relative">
                            {{-- Modal Geocode Spinner Overlay --}}
                            <div wire:loading wire:target="fetchNewAddressFromCoordinates" class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-[2px] z-[40] flex items-center justify-center rounded-2xl transition-all">
                                <div class="flex flex-col items-center gap-2 bg-white dark:bg-gray-800 px-5 py-3.5 rounded-xl border border-gray-150 dark:border-gray-700 shadow-lg">
                                    <div class="w-5 h-5 border-3 border-indigo-650 border-t-transparent rounded-full animate-spin"></div>
                                    <span class="text-[11px] font-bold text-gray-800 dark:text-gray-200">در حال دریافت نشانی روی نقشه...</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_title" class="block mb-1 text-[11px] font-bold text-gray-800 dark:text-gray-200">عنوان آدرس (خانه، محل کار و...)</label>
                                    <input type="text" id="new_title" wire:model.defer="newTitle" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 placeholder-gray-400" placeholder="مثال: خانه">
                                    @error('newTitle') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="new_postal_code" class="block mb-1 text-[11px] font-bold text-gray-800 dark:text-gray-200">کد پستی</label>
                                    <input type="text" id="new_postal_code" wire:model.defer="newPostalCode" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 font-mono text-center dir-ltr" placeholder="1234567890">
                                    @error('newPostalCode') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="new_province" class="block mb-1 text-[11px] font-bold text-gray-800 dark:text-gray-200">استان</label>
                                    <input type="text" id="new_province" wire:model="newProvince" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                    @error('newProvince') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="new_city" class="block mb-1 text-[11px] font-bold text-gray-800 dark:text-gray-200">شهر</label>
                                    <input type="text" id="new_city" wire:model="newCity" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                    @error('newCity') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="new_address" class="block mb-1 text-[11px] font-bold text-gray-800 dark:text-gray-200">نشانی دقیق پستی</label>
                                    <textarea id="new_address" wire:model="newAddress" rows="3" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 resize-y min-h-[50px]"></textarea>
                                    @error('newAddress') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end p-5 border-t border-solid border-gray-150 dark:border-gray-700 rounded-b-3xl gap-3">
                        <button type="button" wire:click="saveNewAddress" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-95">
                            ثبت و استفاده از آدرس
                        </button>
                        <button type="button" wire:click="closeNewAddressModal" class="px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl transition-colors">
                            انصراف
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
