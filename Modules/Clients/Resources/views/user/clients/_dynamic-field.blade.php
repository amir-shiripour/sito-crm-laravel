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

{{-- textarea --}}
@if ($type === 'textarea')
    <textarea rows="4" wire:model.defer="{{ $model }}" placeholder="{{ $placeholder }}"
              class="{{ $baseInputClass }} resize-y min-h-[100px]"></textarea>

    {{-- text / email / number / date --}}
@elseif (in_array($type, ['text','email','number','date'], true))
    <input type="{{ $type === 'text' ? 'text' : $type }}" wire:model.defer="{{ $model }}" placeholder="{{ $placeholder }}"
           class="{{ $baseInputClass }}" />

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
    <div class="relative">
        <select wire:model.defer="{{ $model }}" class="{{ $baseInputClass }} appearance-none"
                @if(!empty($field['multiple'])) multiple @endif>
            @if (empty($field['multiple']))
                <option value="">{{ $placeholder ?: 'انتخاب کنید...' }}</option>
            @endif

            @foreach($opts as $ov => $ol)
                <option value="{{ is_string($ov) ? $ov : $ol }}">
                    {{ is_string($ol) ? $ol : $ov }}
                </option>
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

    <div class="space-y-3" x-data="{
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
        <div class="relative">
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">استان</label>
            <select x-model="province" @change="updateCities()" class="{{ $baseInputClass }} appearance-none">
                <option value="">انتخاب استان...</option>
                <template x-for="prov in provinces" :key="prov">
                    <option :value="prov" x-text="prov"></option>
                </template>
            </select>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 top-6">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>

        {{-- سلکتور شهر --}}
        <div class="relative">
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">شهر</label>
            <select x-model="city" @change="updateValue()" :disabled="!province || cities.length === 0"
                    class="{{ $baseInputClass }} appearance-none disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">انتخاب شهر...</option>
                <template x-for="city in cities" :key="city">
                    <option :value="city" x-text="city"></option>
                </template>
            </select>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 top-6">
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
