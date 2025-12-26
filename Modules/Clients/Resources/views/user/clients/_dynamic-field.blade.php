{{-- clients::user.clients._dynamic-field --}}
@php
    // تعیین ID نهایی فیلد
    $fid = $fid ?? ($field['id'] ?? null);

    // نگاشت فیلدهای سیستمی به پراپرتی‌های لایووایر
    $systemModelMap = [
    'full_name' => 'full_name',
    'phone' => 'phone',
    'email' => 'email',
    'national_code' => 'national_code',
    'case_number' => 'case_number',
    'notes' => 'notes',
    'password' => 'password',
    // status_id را عمداً اینجا نمی‌گذاریم؛ خودش جدا کنترل می‌شود
    ];

    // اگر فیلد سیستمی باشد → مستقیم به پراپرتی، در غیر این صورت → meta.*
    $model = $systemModelMap[$fid] ?? "meta.$fid";

    $type = $field['type'] ?? 'text';
    $placeholder = $field['placeholder'] ?? '';

    // options برای select / radio
    $opts = [];
    if (!empty($field['options_json'])) {
    $parsed = json_decode($field['options_json'], true);
    if (is_array($parsed)) {
    $opts = $parsed;
    }
    }

    // کلاس پایه ورودی‌ها (هماهنگ با UI پروژه)
    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400
    focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200
    dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
@endphp
@includeIf('partials.jalali-date-picker')

{{-- textarea --}}
@if ($type === 'textarea')
    <textarea rows="4" wire:model.defer="{{ $model }}" placeholder="{{ $placeholder }}"
              class="{{ $baseInputClass }} resize-y min-h-[100px]"></textarea>

    {{-- text / email / number / date --}}
@elseif (in_array($type, ['text','email','number'], true))
    <input type="{{ $type === 'text' ? 'text' : $type }}" wire:model.defer="{{ $model }}" placeholder="{{ $placeholder }}"
           class="{{ $baseInputClass }}" />

@elseif (in_array($type, ['date'], true))
    <input type="text" wire:model.defer="{{ $model }}" placeholder="{{ $placeholder }}" class="{{ $baseInputClass }}"
           data-jdp-only-date />
    {{-- password --}}
@elseif ($type === 'password')
    <div class="flex gap-2 items-center" x-data="{ show: false }">
        <div class="relative flex-1">
            <input x-bind:type="show ? 'text' : 'password'" wire:model.defer="{{ $model }}"
                   placeholder="{{ $placeholder ?: 'رمز عبور امن وارد کنید...' }}"
                   class="{{ $baseInputClass }} pr-10 font-mono" />
            {{-- آیکون چشم --}}
            <button type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    @click="show = !show" x-tooltip.raw="نمایش / مخفی کردن رمز">
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
                class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-medium border border-indigo-200 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:border-indigo-300 dark:border-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200 dark:hover:bg-indigo-800/70 transition-colors">
            ساخت خودکار
        </button>
    </div>
    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
        حداقل ۸ کاراکتر و شامل حروف و اعداد باشد. در صورت کلیک روی «ساخت خودکار»، رمز امن تولید و در همین فیلد پر
        می‌شود.
    </p>

    {{-- checkbox --}}
@elseif ($type === 'checkbox')
    <div class="flex items-center h-full pt-2">
        <input type="checkbox" id="chk-{{ $fid }}" wire:model.defer="{{ $model }}"
               class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer" />
        <label for="chk-{{ $fid }}" class="mr-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer select-none">
            {{ $placeholder ?: 'فعال / تأیید' }}
        </label>
    </div>

    {{-- radio --}}
@elseif ($type === 'radio')
    <div class="flex flex-wrap gap-4 pt-1">
        @foreach($opts as $ov => $ol)
            @php
                $val = is_string($ov) ? $ov : $ol;
                $lab = is_string($ol) ? $ol : $ov;
            @endphp
            <label class="inline-flex items-center gap-2 cursor-pointer group">
                <div class="relative flex items-center">
                    <input type="radio" class="peer sr-only" value="{{ $val }}" wire:model.defer="{{ $model }}">
                    <div
                        class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all dark:border-gray-600">
                    </div>
                    <div
                        class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity">
                    </div>
                </div>
                <span class="text-sm text-gray-700 group-hover:text-indigo-600 dark:text-gray-300 transition-colors">
            {{ $lab }}
        </span>
            </label>
        @endforeach
    </div>

    {{-- select --}}
@elseif ($type === 'select')
    @php
        // چک کردن اینکه آیا از لیست clients استفاده می‌شود
        $useClientsList = !empty($field['use_clients_list']);

        // مقدار فعلی
        $currentValue = $meta[$fid] ?? null;
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

        // اگر از clients استفاده می‌شود، options را خالی می‌گذاریم (از API می‌آید)
        $selectOptions = [];
        if (!$useClientsList) {
        // تبدیل options به فرمت مورد نیاز برای inline-multiselect
        foreach ($opts as $ov => $ol) {
        $value = is_string($ov) ? $ov : $ol;
        $label = is_string($ol) ? $ol : $ov;
        $selectOptions[] = ['value' => (string) $value, 'label' => $label];
        }
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
    placeholder: @js($placeholder ?: 'جستجو و انتخاب مشتری...'),
    searchUrl: @js($searchUrl),
    isMultiple: @js(!empty($field['multiple'])),
    init() {
        // بارگذاری اولیه مقادیر انتخاب شده
        this.loadSelectedClients();
        // sync با Livewire
        this.$watch('selectedValues', (values) => {
            const arr = Array.isArray(values) ? values : [];
            if (this.isMultiple) {
                @this.set('{{ $model }}', arr);
            } else {
                @this.set('{{ $model }}', arr[0] || null);
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
                open ? 'border-indigo-500 ring-1 ring-indigo-500 bg-white dark:bg-gray-800' : 'border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500',
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
                            bg-indigo-100 text-indigo-700 border border-transparent
                            dark:bg-indigo-500/20 dark:text-indigo-300 dark:border-indigo-500/10 transition-colors animate-in fade-in zoom-in duration-200">
                            <span x-text="options.find(o => o.value == value)?.label ?? value"></span>
                            <button type="button"
                                    class="p-0.5 rounded-md hover:bg-indigo-200/50 dark:hover:bg-white/20 text-indigo-600 dark:text-indigo-300 transition-colors"
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
                            ? 'bg-indigo-50 text-indigo-700 font-medium dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700/50'">
                                <span x-text="option.label"></span>
                                <span x-show="selectedValues.includes(option.value)"
                                      class="absolute inset-y-0 left-2 flex items-center text-indigo-600 dark:text-indigo-400">
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
    @elseif(!empty($field['multiple']))
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
    placeholder: @js($placeholder ?: 'انتخاب کنید...'),
    init() {
        // sync با Livewire - فقط زمانی که تغییر می‌کند
        this.$watch('selectedValues', (values) => {
            // تبدیل به آرایه و sync با Livewire
            const arr = Array.isArray(values) ? values : [];
            @this.set('{{ $model }}', arr);
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
            <select wire:model.defer="{{ $model }}" class="{{ $baseInputClass }} appearance-none">
                <option value="">{{ $placeholder ?: 'انتخاب کنید...' }}</option>

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

    {{-- status (وضعیت کلاینت) --}}
@elseif ($type === 'status')
    @php
        // لیست وضعیت‌های مجاز برای این فرم / وضعیت فعلی
        $statusList = collect($availableStatuses ?? []);

        if (!empty($field['status_keys'] ?? [])) {
        $statusList = $statusList->whereIn('key', (array) $field['status_keys']);
        }

        $currentStatusId = $status_id ?? optional($client ?? null)->status_id;
        $currentStatus = null;

        if ($currentStatusId) {
        $currentStatus = $statusList->firstWhere('id', $currentStatusId);

        if (!$currentStatus && isset($client) && $client && $client->relationLoaded('status') ? $client->status :
        $client->status ?? null) {
        $currentStatus = $client->status;
        }

        if (!$currentStatus) {
        $currentStatus = (object)[
        'id' => $currentStatusId,
        'label' => 'وضعیت فعلی (خارج از لیست مجاز)',
        ];
        }

        if ($statusList->where('id', $currentStatus->id)->isEmpty()) {
        $statusList->prepend($currentStatus);
        }
        }

        $statusList = $statusList->values();
    @endphp

    <div class="relative">
        <select wire:model.defer="status_id" class="{{ $baseInputClass }} appearance-none">
            <option value="">{{ $placeholder ?: 'انتخاب وضعیت...' }}</option>

            @foreach($statusList as $st)
                @php
                    $isCurrent = isset($currentStatus) && $currentStatus && $st->id === $currentStatus->id;
                @endphp
                <option value="{{ $st->id }}">
                    {{ $st->label }}
                    @if($isCurrent)
                        (وضعیت فعلی)
                    @endif
                </option>
            @endforeach
        </select>
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

    {{-- file --}}
@elseif ($type === 'file')
    <div class="relative">
        <input type="file" wire:model="{{ $model }}" class="block w-full text-sm text-gray-500
                   file:ml-4 file:py-2.5 file:px-4
                   file:rounded-full file:border-0
                   file:text-xs file:font-semibold
                   file:bg-indigo-50 file:text-indigo-700
                   hover:file:bg-indigo-100
                   dark:file:bg-indigo-900/30 dark:file:text-indigo-300
                   cursor-pointer transition-all" />
    </div>

    {{-- select-user-by-role --}}
@elseif ($type === 'select-user-by-role')
    <div class="relative">
        <select wire:model.defer="{{ $model }}"
                class="{{ $baseInputClass }} @if(!empty($field['multiple'])) min-h-[44px] @endif"
                @if(!empty($field['multiple'])) multiple @endif>

            @if (empty($field['multiple']))
                <option value="">{{ $placeholder ?: 'انتخاب کاربر...' }}</option>
            @endif

            @foreach($this->usersForRole($field['role'] ?? null) as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
        </select>
        @if (empty($field['multiple']))
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        @endif
    </div>

    {{-- select-province-city --}}
@elseif ($type === 'select-province-city')
    @php
        // بارگذاری داده‌های استان و شهر
        $jsonPath = base_path('Modules/Clients/Resources/data/iran-provinces-cities.json');
        $provincesData = [];
        if (file_exists($jsonPath)) {
        $provincesData = json_decode(file_get_contents($jsonPath), true) ?? [];
        }
        $provinces = array_keys($provincesData);

        // مقدار فعلی (می‌تواند یک آرایه باشد: ['province' => 'تهران', 'city' => 'تهران'])
        $currentValue = $meta[$fid] ?? [];
        if (is_string($currentValue)) {
        $currentValue = json_decode($currentValue, true) ?? [];
        }
        if (!is_array($currentValue)) {
        $currentValue = [];
        }
        $selectedProvince = $currentValue['province'] ?? '';
        $selectedCity = $currentValue['city'] ?? '';

        // شهرهای استان انتخاب شده
        $cities = [];
        if ($selectedProvince && isset($provincesData[$selectedProvince])) {
        $cities = $provincesData[$selectedProvince];
        }
    @endphp

    <div class="space-y-3 flex items-center gap-3.5" x-data="{
        province: @js($selectedProvince),
        city: @js($selectedCity),
        provinces: @js($provinces),
        cities: @js($cities),
        provincesData: @js($provincesData),
        init() {
            // بارگذاری اولیه شهرها بر اساس استان انتخاب شده
            if (this.province && this.provincesData[this.province]) {
                this.cities = this.provincesData[this.province];
            }
        },
        updateCities() {
            if (this.province && this.provincesData[this.province]) {
                this.cities = this.provincesData[this.province];
                this.city = ''; // ریست شهر هنگام تغییر استان
                this.updateValue();
            } else {
                this.cities = [];
                this.city = '';
                this.updateValue();
            }
        },
        updateValue() {
            const value = JSON.stringify({province: this.province || '', city: this.city || ''});
            @this.set('meta.{{ $fid }}', value);
        }
    }" wire:ignore>
        {{-- سلکتور استان --}}
        <div class="relative w-full m-0">
            {{-- <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">استان</label>--}}
            <select x-model="province" @change="updateCities()" class="{{ $baseInputClass }} appearance-none">
                <option value="">انتخاب استان...</option>
                <template x-for="prov in provinces" :key="prov">
                    <option :value="prov" x-text="prov"></option>
                </template>
            </select>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 ">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>

        {{-- سلکتور شهر --}}
        <div class="relative w-full">
            {{-- <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">شهر</label>--}}
            <select x-model="city" @change="updateValue()" :disabled="!province || cities.length === 0"
                    class="{{ $baseInputClass }} appearance-none disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">انتخاب شهر...</option>
                <template x-for="city in cities" :key="city">
                    <option :value="city" x-text="city"></option>
                </template>
            </select>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- fallback --}}
@else
    <input type="text" wire:model.defer="{{ $model }}" placeholder="{{ $placeholder }}" class="{{ $baseInputClass }}" />
@endif

@php
    // اگر فیلد status باشد، کلید خطا باید status_id باشد، نه meta.status_id
    $errorKey = ($type === 'status') ? 'status_id' : $model;
@endphp

@error($errorKey)
<div class="flex items-center gap-1 text-xs font-medium text-red-600 mt-1.5">
    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    {{ $message }}
</div>
@enderror
