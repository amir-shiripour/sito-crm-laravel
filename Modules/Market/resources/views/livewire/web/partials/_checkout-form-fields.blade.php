@if($field['type'] === 'select-province-city')
    @php
        $jsonPath = base_path('Modules/Clients/resources/data/iran-provinces-cities.json');
        $provincesData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
        $allProvinces = array_keys($provincesData);
        $decodedValue = is_string($currentValue) ? json_decode($currentValue, true) : $currentValue;
        $currentProvince = $decodedValue['province'] ?? '';
        $currentCity = $decodedValue['city'] ?? '';
    @endphp
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6"
         wire:key="field-province-city-{{ $field['id'] }}"
         x-data="{
            province: @js($currentProvince),
            city: @js($currentCity),
            provinces: @js($allProvinces),
            cities: [],
            provincesData: @js($provincesData),
            livewireModel: @entangle($modelName),
            init() {
                if (this.province && this.provincesData[this.province]) {
                    this.cities = this.provincesData[this.province];
                }
                this.$watch('province', value => {
                    this.city = '';
                    this.cities = (value && this.provincesData[value]) ? this.provincesData[value] : [];
                    this.updateLivewireModel();
                });
                this.$watch('city', () => {
                    this.updateLivewireModel();
                });
            },
            updateLivewireModel() {
                this.livewireModel = (this.province || this.city)
                    ? JSON.stringify({ province: this.province, city: this.city })
                    : '';
            }
        }">
        {{-- Province Selector --}}
        <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
            <label for="province-{{ $field['id'] }}" class="{{ $labelClass }}">استان</label>
            <div @click="open = !open" class="{{ $inputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-800': open, 'bg-gray-50 dark:bg-gray-800/50': !open}">
                <span x-text="province || 'انتخاب استان...'" class="block truncate" :class="{'text-gray-400': !province}"></span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </div>
            <div x-show="open" x-transition class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 dark:text-gray-200">
                <template x-for="p in provinces.filter(item => item.toLowerCase().includes(search.toLowerCase()))" :key="p">
                    <div @click="province = p; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': province == p, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': province != p}">
                        <span x-text="p"></span>
                        <svg x-show="province == p" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </template>
            </div>
        </div>

        {{-- City Selector --}}
        <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
            <label for="city-{{ $field['id'] }}" class="{{ $labelClass }}">شهر</label>
            <div @click="open = !open" class="{{ $inputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-800': open, 'bg-gray-50 dark:bg-gray-800/50': !open}" :disabled="!province">
                <span x-text="city || 'انتخاب شهر...'" class="block truncate" :class="{'text-gray-400': !city}"></span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </div>
            <div x-show="open" x-transition class="absolute z-50 w-full mt-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 dark:text-gray-200">
                <template x-for="c in cities.filter(item => item.toLowerCase().includes(search.toLowerCase()))" :key="c">
                    <div @click="city = c; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': city == c, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': city != c}">
                        <span x-text="c"></span>
                        <svg x-show="city == c" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </template>
            </div>
        </div>
    </div>
@else
    <div class="{{ $widthClass }}" wire:key="field-{{ $field['id'] }}">
        @if($field['type'] === 'checkbox')
            <label class="flex items-start gap-3 mt-8">
                <div class="flex items-center h-5">
                    <input type="checkbox" id="{{ $htmlId }}" wire:model="{{ $modelName }}" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer transition-colors" {{ $currentValue ? 'checked' : '' }}>
                </div>
                <div class="text-sm">
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $field['label'] }}</span>
                    @if(!empty($field['required']))
                        <span class="text-red-500 font-semibold text-base mr-1">*</span>
                    @endif
                </div>
            </label>
        @else
            <label for="{{ $htmlId }}" class="{{ $labelClass }}">
                {{ $field['label'] }}
                @if(!empty($field['required']))
                    <span class="text-red-500 font-semibold text-base mr-1">*</span>
                @endif
            </label>

            @if($field['type'] === 'textarea')
                <textarea id="{{ $htmlId }}" wire:model="{{ $modelName }}" placeholder="{{ $field['placeholder'] ?? '' }}" rows="4" class="{{ $inputClass }}">{{ $currentValue }}</textarea>
            @elseif($field['type'] === 'select')
                <select id="{{ $htmlId }}" wire:model="{{ $modelName }}" class="{{ $inputClass }}">
                    <option value="">{{ $field['placeholder'] ?? 'انتخاب کنید...' }}</option>
                    @foreach($field['options'] ?? [] as $option)
                        <option value="{{ $option['value'] }}" {{ $currentValue == $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            @elseif($field['type'] === 'jalali-date')
                <input type="text" id="{{ $htmlId }}" wire:model="{{ $modelName }}" value="{{ $currentValue }}" placeholder="{{ $field['placeholder'] ?? 'YYYY/MM/DD' }}" class="{{ $inputClass }} text-left" dir="ltr" data-jdp>
            @else
                <input type="{{ $field['type'] ?? 'text' }}" id="{{ $htmlId }}" wire:model="{{ $modelName }}" value="{{ $currentValue }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="{{ $inputClass }}">
            @endif
        @endif

        @error($modelName) <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
    </div>
@endif
