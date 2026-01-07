{{-- clients::user.clients._quick-field --}}
{{-- Cache Buster: {{ time() }} --}}
@php
    $type = $field['type'] ?? 'text';

    // options برای select / radio
    $opts = [];
    if (!empty($field['options_json'])) {
    $parsed = json_decode($field['options_json'], true);
    if (is_array($parsed)) {
    $opts = $parsed;
    }
    }

    // placeholder سفارشی (اگر در اسکیمای فرم ست شده باشد)
    $placeholder = $field['placeholder'] ?? '...';

    // استایل مشترک با فرم اصلی برای یکپارچگی
    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
    focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all duration-200
    dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dark:focus:border-emerald-500/50";

    // آماده‌سازی داده‌ها برای select
    $selectOptions = [];
    $currentValue = null;
    $isMultiple = false;

    if ($type === 'select') {
    // تبدیل options به فرمت مورد نیاز برای inline-multiselect
    foreach ($opts as $ov => $ol) {
    $value = is_string($ov) ? $ov : $ol;
    $label = is_string($ol) ? $ol : $ov;
    $selectOptions[] = ['value' => (string) $value, 'label' => $label];
    }

    // مقدار فعلی از quick
    $currentValue = $quick[$fid] ?? null;
    if (is_string($currentValue)) {
    // اگر JSON string است
    $decoded = json_decode($currentValue, true);
    if (is_array($decoded)) {
    $currentValue = $decoded;
    }
    }
    if (!is_array($currentValue)) {
    $currentValue = $currentValue ? [$currentValue] : [];
    }
    // تبدیل به string برای Alpine.js
    $currentValue = array_map('strval', $currentValue);
    $isMultiple = !empty($field['multiple']);
    }

    // آماده‌سازی داده‌ها برای select-province-city
    $provincesData = [];
    $provinces = [];
    $selectedProvince = '';
    $selectedCity = '';
    $cities = [];

    if ($type === 'select-province-city') {
    // بارگذاری داده‌های استان و شهر
    $jsonPath = base_path('Modules/Clients/resources/data/iran-provinces-cities.json');
    if (file_exists($jsonPath)) {
    $provincesData = json_decode(file_get_contents($jsonPath), true) ?? [];
    }
    $provinces = array_keys($provincesData);

    // مقدار فعلی از quick
    $currentValue = $quick[$fid] ?? [];
    if (is_string($currentValue)) {
    $currentValue = json_decode($currentValue, true) ?? [];
    }
    if (!is_array($currentValue)) {
    $currentValue = [];
    }
    $selectedProvince = $currentValue['province'] ?? '';
    $selectedCity = $currentValue['city'] ?? '';

    // شهرهای استان انتخاب شده
    if ($selectedProvince && isset($provincesData[$selectedProvince])) {
    $cities = $provincesData[$selectedProvince];
    }
    }
@endphp
@includeIf('partials.jalali-date-picker')

{{-- پسورد در ایجاد سریع --}}
@if($type === 'password')
    <div class="flex items-center gap-2" x-data="{ show: false }">
        <div class="relative flex-1">
            <input x-bind:type="show ? 'text' : 'password'" class="{{ $baseInputClass }} pr-9 font-mono"
                   wire:model.defer="password" placeholder="{{ $placeholder !== '...' ? $placeholder : 'رمز عبور امن...' }}"
                   autocomplete="new-password">
            <button type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    @click="show = !show">
                <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                             -1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7
                             .51-1.626 1.48-3.059 2.75-4.155M9.88 9.88a3 3 0 014.24 4.24
                             M6.1 6.1L4 4m0 0l16 16m-2.1-2.1L20 20" />
                </svg>
            </button>
        </div>

        <button type="button" wire:click="generatePassword"
                class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-medium border border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 hover:border-emerald-300 dark:border-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200 dark:hover:bg-emerald-800/70 transition-colors">
            ساخت خودکار
        </button>
    </div>
    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
        حداقل ۸ کاراکتر و شامل حروف و اعداد باشد. در صورت کلیک روی «ساخت خودکار»، رمز امن تولید و در همین فیلد پر می‌شود.
    </p>

    @error('password')
    <div class="text-xs text-red-600 mt-1 mr-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ $message }}
    </div>
    @enderror

@elseif(in_array($type, ['text','email','number'], true))
    {{-- متن ساده: text / email / number / date --}}
    <input type="{{ $type === 'text' ? 'text' : $type }}" class="{{ $baseInputClass }}" wire:model.defer="quick.{{ $fid }}"
           placeholder="{{ $placeholder }}">

@elseif(in_array($type, ['date'], true))
    <input type="text" class="{{ $baseInputClass }}" data-jdp-only-date wire:model.defer="quick.{{ $fid }}"
           placeholder="{{ $placeholder }}">

@elseif($type === 'textarea')
    {{-- textarea --}}
    <textarea rows="2" class="{{ $baseInputClass }} resize-none" wire:model.defer="quick.{{ $fid }}"
              placeholder="{{ $placeholder }}"></textarea>

@elseif($type === 'checkbox')
    {{-- checkbox --}}
    <div class="flex items-center h-full pt-1.5">
        <input type="checkbox" id="qc-chk-{{ $fid }}" value="1" wire:model.defer="quick.{{ $fid }}"
               class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 cursor-pointer transition-colors" />
        <label for="qc-chk-{{ $fid }}" class="mr-2 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">
            {{ $placeholder !== '...' ? $placeholder : 'فعال / انتخاب' }}
        </label>
    </div>

@elseif($type === 'radio')
    {{-- radio --}}
    <div class="flex flex-wrap gap-3 pt-1">
        @foreach($opts as $ov => $ol)
            @php
                $val = is_string($ov) ? $ov : $ol;
                $lab = is_string($ol) ? $ol : $ov;
            @endphp
            <label class="inline-flex items-center gap-2 cursor-pointer group">
                <input type="radio"
                       class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800"
                       wire:model.defer="quick.{{ $fid }}" value="{{ $val }}">
                <span class="text-xs text-gray-700 group-hover:text-emerald-600 dark:text-gray-300 transition-colors">
            {{ $lab }}
        </span>
            </label>
        @endforeach
    </div>

@elseif($type === 'select')
    @php
        // چک کردن اینکه آیا از لیست clients استفاده می‌شود
        $useClientsList = !empty($field['use_clients_list']);

        // اگر از clients استفاده می‌شود، options را خالی می‌گذاریم (از API می‌آید)
        if (!$useClientsList) {
        // تبدیل options به فرمت مورد نیاز برای inline-multiselect
        $selectOptions = [];
        foreach ($opts as $ov => $ol) {
        $value = is_string($ov) ? $ov : $ol;
        $label = is_string($ol) ? $ol : $ov;
        $selectOptions[] = ['value' => (string) $value, 'label' => $label];
        }
        } else {
        $selectOptions = [];
        }
    @endphp

    @if($useClientsList)
        {{-- استفاده از لیست clients با جستجو --}}
        @php
            $searchUrl = route('user.clients.search');
        @endphp
        <div x-data="{
    options: @js($selectOptions),
    selectedValues: @js($currentValue),
    search: '',
    open: false,
    loading: false,
    placeholder: @js($placeholder !== '...' ? $placeholder : 'جستجو و انتخاب مشتری...'),
    searchUrl: @js($searchUrl),
    isMultiple: @js($isMultiple),
    init() {
        // بارگذاری اولیه مقادیر انتخاب شده
        this.loadSelectedClients();
        // sync با Livewire
        this.$watch('selectedValues', (values) => {
            const arr = Array.isArray(values) ? values : [];
            if (this.isMultiple) {
                @this.set('quick.{{ $fid }}', arr);
            } else {
                @this.set('quick.{{ $fid }}', arr[0] || null);
            }
        }, { deep: true });
    },
    async searchClients() {
        if (this.search.length < 2) {
            this.options = [];
            return;
        }
        this.loading = true;
        try {
            const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(this.search)}&limit=20`);
            const data = await response.json();
            this.options = data.results || [];
        } catch (error) {
            console.error('Error searching clients:', error);
            this.options = [];
        } finally {
            this.loading = false;
        }
    },
    async loadSelectedClients() {
        if (this.selectedValues.length === 0) return;
        this.loading = true;
        try {
            const ids = this.selectedValues.join(',');
            const response = await fetch(`${this.searchUrl}?ids=${ids}`);
            const data = await response.json();
            // اضافه کردن به options برای نمایش
            if (data.results) {
                this.options = [...this.options, ...data.results];
            }
        } catch (error) {
            console.error('Error loading selected clients:', error);
        } finally {
            this.loading = false;
        }
    },
    toggle(value) {
        value = String(value);
        if (this.isMultiple) {
            if (this.selectedValues.includes(value)) {
                this.selectedValues = this.selectedValues.filter(v => v !== value);
            } else {
                this.selectedValues.push(value);
            }
        } else {
            // در حالت تک انتخابی، اگر همان مقدار انتخاب شده باشد، حذف کن
            if (this.selectedValues.includes(value)) {
                this.selectedValues = [];
            } else {
                this.selectedValues = [value];
            }
            this.open = false;
            this.search = '';
        }
    },
    remove(value) {
        this.selectedValues = this.selectedValues.filter(v => v !== String(value));
    },
    get filteredOptions() {
        return this.options;
    }
}" wire:ignore>
            <div class="relative group" @click.outside="open = false">
                <div class="flex items-center gap-1.5 min-h-[42px] w-full rounded-xl border px-2 py-1.5 transition-all duration-200 cursor-text shadow-sm"
                     :class="[
                open ? 'border-emerald-500 ring-1 ring-emerald-500 bg-white dark:bg-gray-800' : 'border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500',
                isMultiple ? 'flex-wrap' : ''
            ]"
                     @click="if (!open) { open = true; if (!isMultiple && selectedValues.length > 0) { search = ''; } $nextTick(() => $refs.searchInput?.focus()); }">
                    <template x-if="selectedValues.length === 0 && !search && !open">
                <span class="text-xs text-gray-400 dark:text-gray-500 px-1 pointer-events-none select-none">
                    <span x-text="placeholder"></span>
                </span>
                    </template>

                    {{-- حالت چند انتخابی: نمایش تگ‌ها --}}
                    <template x-for="value in (isMultiple ? selectedValues : [])" :key="value">
                        <div
                            class="inline-flex items-center gap-1 pl-2 pr-1 py-0.5 text-xs font-medium rounded-lg
                            bg-emerald-100 text-emerald-700 border border-transparent
                            dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/10 transition-colors animate-in fade-in zoom-in duration-200">
                            <span x-text="options.find(o => o.value == value)?.label ?? value"></span>
                            <button type="button"
                                    class="p-0.5 rounded-md hover:bg-emerald-200/50 dark:hover:bg-white/20 text-emerald-600 dark:text-emerald-300 transition-colors"
                                    @click.stop="remove(value)">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </template>

                    {{-- حالت تک انتخابی: نمایش مقدار انتخاب شده (فقط وقتی dropdown بسته است) --}}
                    <template x-if="!isMultiple && selectedValues.length > 0 && !open">
                <span class="text-xs text-gray-900 dark:text-gray-100 truncate flex-1"
                      x-text="options.find(o => o.value == selectedValues[0])?.label ?? selectedValues[0]"></span>
                    </template>

                    <input x-show="isMultiple || selectedValues.length === 0 || open" x-ref="searchInput" type="text"
                           x-model="search" @input.debounce.300ms="searchClients()"
                           class="flex-1 min-w-[60px] border-0 bg-transparent p-0 text-xs text-gray-900 focus:ring-0 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 leading-relaxed"
                           @keydown.backspace="if(search === '' && selectedValues.length > 0 && isMultiple) remove(selectedValues[selectedValues.length - 1])"
                           placeholder="جستجو بر اساس نام، کد ملی، تلفن، شماره پرونده...">

                    <div class="ml-auto pl-1 text-gray-400 pointer-events-none">
                        <svg x-show="!loading" class="w-4 h-4 transition-transform duration-200"
                             :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </div>

                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1" style="display: none;" class="absolute z-50 mt-1 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl ring-1 ring-black ring-opacity-5
                   dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
                    <ul
                        class="max-h-60 overflow-y-auto p-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
                        <template x-for="option in filteredOptions" :key="option.value">
                            <li @click="toggle(option.value); $refs.searchInput.focus()"
                                class="relative cursor-pointer select-none py-2 pl-9 pr-3 text-right text-xs rounded-lg transition-colors group"
                                :class="selectedValues.includes(option.value)
                            ? 'bg-emerald-50 text-emerald-700 font-medium dark:bg-emerald-900/30 dark:text-emerald-300'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700/50'">
                                <span x-text="option.label"></span>
                                <span x-show="selectedValues.includes(option.value)"
                                      class="absolute inset-y-0 left-2 flex items-center text-emerald-600 dark:text-emerald-400">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                      clip-rule="evenodd" />
                            </svg>
                        </span>
                            </li>
                        </template>
                        <li x-show="filteredOptions.length === 0 && !loading"
                            class="py-3 text-center text-xs text-gray-500 dark:text-gray-400 italic">
                            <span x-show="search.length < 2">حداقل ۲ کاراکتر برای جستجو وارد کنید.</span>
                            <span x-show="search.length >= 2">موردی یافت نشد.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @elseif($isMultiple)
        {{-- حالت چند انتخابی با inline-multiselect (بدون clients) --}}
        @php
            $selectOptions = [];
            foreach ($opts as $ov => $ol) {
            $value = is_string($ov) ? $ov : $ol;
            $label = is_string($ol) ? $ol : $ov;
            $selectOptions[] = ['value' => (string) $value, 'label' => $label];
            }
        @endphp
        <div x-data="{
    options: @js($selectOptions),
    selectedValues: @js($currentValue),
    search: '',
    open: false,
    placeholder: @js($placeholder !== '...' ? $placeholder : 'انتخاب کنید...'),
    init() {
        // sync با Livewire
        this.$watch('selectedValues', (values) => {
            const arr = Array.isArray(values) ? values : [];
            @this.set('quick.{{ $fid }}', arr);
        }, { deep: true });
    },
    toggle(value) {
        value = String(value);
        if (this.selectedValues.includes(value)) {
            this.selectedValues = this.selectedValues.filter(v => v !== value);
        } else {
            this.selectedValues.push(value);
        }
    },
    remove(value) {
        this.selectedValues = this.selectedValues.filter(v => v !== String(value));
    },
    get filteredOptions() {
        if (!this.search) return this.options;
        return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase()));
    }
}" wire:ignore>
            @include('clients::components.inline-multiselect')
        </div>
    @else
        {{-- حالت تک انتخابی (select معمولی) --}}
        <div class="relative">
            <select class="{{ $baseInputClass }} appearance-none" wire:model.defer="quick.{{ $fid }}">
                <option value="">{{ $placeholder !== '...' ? $placeholder : 'انتخاب کنید...' }}</option>
                @foreach($opts as $ov => $ol)
                    <option value="{{ is_string($ov) ? $ov : $ol }}">
                        {{ is_string($ol) ? $ol : $ov }}
                    </option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>
    @endif

@elseif($type === 'select-user-by-role')
    {{-- select-user-by-role --}}
    <div class="relative">
        <select class="{{ $baseInputClass }} appearance-none" wire:model.defer="quick.{{ $fid }}">
            <option value="">{{ $placeholder !== '...' ? $placeholder : 'انتخاب کاربر...' }}</option>
            @foreach($this->usersForRole($field['role'] ?? null) as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
        </select>
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

@elseif($type === 'status')
    {{-- status در ایجاد سریع --}}
    <div class="relative">
        <select class="{{ $baseInputClass }} appearance-none" wire:model.defer="quick.status_id">
            <option value="">{{ $placeholder !== '...' ? $placeholder : 'انتخاب وضعیت...' }}</option>
            @foreach(($availableStatuses ?? []) as $st)
                <option value="{{ is_array($st) ? $st['id'] : $st->id }}">
                    {{ is_array($st) ? ($st['label'] ?? $st['key']) : ($st->label ?? $st->key) }}
                </option>
            @endforeach
        </select>
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

@elseif($type === 'select-province-city')
    {{-- select-province-city --}}
    <div class="space-y-2 flex items-center gap-2" wire:key="quick-field-{{ $fid }}-province-city" x-data="{
        province: @js($selectedProvince),
        city: @js($selectedCity),
        provinces: @js($provinces),
        cities: @js($cities),
        provincesData: @js($provincesData),
        init() {
            if (this.province && this.provincesData[this.province]) {
                this.cities = this.provincesData[this.province];
            }
        },
        updateCities() {
            if (this.province && this.provincesData[this.province]) {
                this.cities = this.provincesData[this.province];
                this.city = '';
                this.updateValue();
            } else {
                this.cities = [];
                this.city = '';
                this.updateValue();
            }
        },
        updateValue() {
            const value = JSON.stringify({province: this.province || '', city: this.city || ''});
            @this.set('quick.{{ $fid }}', value);
        }
    }" wire:ignore>
        {{-- سلکتور استان --}}
        <div class="relative flex-1">
            <select x-model="province" @change="updateCities()" class="{{ $baseInputClass }} appearance-none text-xs">
                <option value="">استان...</option>
                <template x-for="prov in provinces" :key="prov">
                    <option :value="prov" x-text="prov"></option>
                </template>
            </select>
        </div>

        {{-- سلکتور شهر --}}
        <div class="relative flex-1">
            <select x-model="city" @change="updateValue()" :disabled="!province || cities.length === 0"
                    class="{{ $baseInputClass }} appearance-none text-xs disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">شهر...</option>
                <template x-for="(cityItem, index) in cities" :key="index">
                    <option :value="cityItem" x-text="cityItem"></option>
                </template>
            </select>
        </div>
    </div>

@else
    {{-- سایر انواع (fallback) --}}
    <input type="text" class="{{ $baseInputClass }}" wire:model.defer="quick.{{ $fid }}" placeholder="{{ $placeholder }}">
@endif

{{-- ارور عمومی فقط برای فیلدهایی که password نیستند --}}
@if($type !== 'password')
    @error("quick.".$fid)
    <div class="text-xs text-red-600 mt-1 mr-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ $message }}
    </div>
    @enderror
@endif
