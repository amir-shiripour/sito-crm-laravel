@extends('clients::layouts.client')

@php
    $title = 'پروفایل کاربری';
    // گروه‌ها را برای تب‌ها آماده می‌کنیم
    $groups = array_keys($groupedFields);
    $firstGroup = $groups[0] ?? 'اطلاعات کاربری';

    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400
    focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200
    dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";

    $lockedInputClass = "w-full rounded-xl border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed
    dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400";
@endphp

@section('content')
    @includeIf('partials.jalali-date-picker')

    <div class="mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">

        {{-- هدر صفحه --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-8 rounded-full bg-indigo-500"></span>
                    {{ $title }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مشاهده و ویرایش اطلاعات حساب کاربری</p>
            </div>

            <a href="{{ route('client.dashboard') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-indigo-600 hover:border-indigo-500 transition-all shadow-sm group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>

        {{-- نمایش پیام‌ها --}}
        @if (session('success'))
            <div class="mb-6 flex items-center gap-3 p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 border border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800/30 dark:text-emerald-400" role="alert">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-800/50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                {{ session('success') }}
            </div>
        @endif

        @if($phoneRequiresOtp)
            <div class="mb-6 flex items-start gap-3 p-4 text-sm text-amber-800 rounded-2xl bg-amber-50 border border-amber-100 dark:bg-amber-900/30 dark:border-amber-800/30 dark:text-amber-400" role="alert">
                <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-800/50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h4 class="font-bold mb-1">شماره تماس غیرقابل تغییر</h4>
                    <p class="opacity-90">شماره تماس شما جهت احراز هویت استفاده می‌شود. برای تغییر آن لطفاً با پشتیبانی در ارتباط باشید.</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start" x-data="{ activeTab: '{{ $firstGroup }}' }">

            {{-- سایدبار تب‌ها --}}
            <div class="lg:col-span-1 space-y-2 sticky top-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-3">
                    <nav class="flex flex-col space-y-1" aria-label="Tabs">
                        @foreach($groups as $groupName)
                            <button @click="activeTab = '{{ $groupName }}'"
                                    :class="activeTab === '{{ $groupName }}' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700/50 dark:hover:text-white'"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all outline-none text-right">
                                <svg class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $groupName }}
                            </button>
                        @endforeach

                        <div class="h-px bg-gray-100 dark:bg-gray-700 my-2 mx-4"></div>

                        <button @click="activeTab = 'security'"
                                :class="activeTab === 'security' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700/50 dark:hover:text-white'"
                                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all outline-none text-right">
                            <svg class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            تغییر رمز عبور
                        </button>
                    </nav>
                </div>
            </div>

            {{-- فرم اصلی --}}
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

                    <form action="{{ route('client.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="p-6 sm:p-10 min-h-[400px]">
                            {{-- محتوای تب‌ها بر اساس گروه‌های فرم --}}
                            @foreach($groupedFields as $groupName => $fields)
                                <div x-show="activeTab === '{{ $groupName }}'" x-transition.opacity.duration.300ms x-cloak>
                                    <div class="mb-8">
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $groupName }}</h2>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">اطلاعات مربوط به این بخش را می‌توانید مشاهده یا ویرایش کنید.</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                        @foreach ($fields as $field)
                                            @php
                                                // محاسبه مقدار اولیه برای فیلد
                                                $fid = $field['id'];
                                                $clientCols = \Illuminate\Support\Facades\Schema::getColumnListing('clients');
                                                $val = in_array($fid, $clientCols) ? ($client->{$fid} ?? '') : ($client->meta[$fid] ?? '');
                                                $fieldValue = old($fid, $val);

                                                $widthClass = match($field['width'] ?? 'full') {
                                                    '1/3', '1/2' => 'md:col-span-1',
                                                    'full' => 'md:col-span-2',
                                                    default => 'md:col-span-2',
                                                };

                                                $isLocked = in_array($fid, $lockedFields);
                                                $isUnauth = in_array($fid, $unauthFields ?? []);
                                                $type = $field['type'] ?? 'text';

                                                // بررسی options برای فیلدهای انتخابی
                                                $opts = [];
                                                if (!empty($field['options_json'])) {
                                                    $parsed = json_decode($field['options_json'], true);
                                                    if (is_array($parsed)) $opts = $parsed;
                                                }
                                            @endphp

                                            <div class="{{ $widthClass }}">
                                                <label for="field_{{ $fid }}" class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                                    {{ $field['label'] }}
                                                    @if(($field['required'] ?? false) && !$isLocked)
                                                        <span class="text-red-500 leading-none">*</span>
                                                    @endif
                                                    @if($isLocked)
                                                        <span class="inline-flex" x-tooltip.raw="{{ $isUnauth ? 'فقط جهت نمایش' : 'غیرقابل ویرایش' }}">
                                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                            </svg>
                                                        </span>
                                                    @endif
                                                </label>

                                                @if($isLocked)
                                                     {{-- نمایش فیلد قفل شده --}}
                                                     <div class="relative">
                                                         @if($type === 'select-province-city')
                                                             @php
                                                                 $decodedCity = is_string($fieldValue) ? json_decode($fieldValue, true) : $fieldValue;
                                                                 $cityStr = '';
                                                                 if(is_array($decodedCity)) {
                                                                     $prov = $decodedCity['province'] ?? '';
                                                                     $cit = $decodedCity['city'] ?? '';
                                                                     if($prov) $cityStr .= $prov;
                                                                     if($cit) $cityStr .= ($cityStr ? ' - ' : '') . $cit;
                                                                 } else {
                                                                     $cityStr = $fieldValue;
                                                                 }
                                                             @endphp
                                                             <input type="text" value="{{ $cityStr }}" readonly class="{{ $lockedInputClass }}">
                                                         @else
                                                             @php
                                                                 // پیدا کردن عنوان نمایشی برای فیلدهای سلکت و رادیو قفل شده
                                                                 $displayVal = $fieldValue;
                                                                 if (in_array($type, ['select', 'radio'])) {
                                                                     $displayOptions = [];
                                                                     if (!empty($field['options_json'])) {
                                                                         $parsedOpts = json_decode($field['options_json'], true);
                                                                         if (is_array($parsedOpts)) {
                                                                             $displayOptions = $parsedOpts;
                                                                         } else {
                                                                             $lines = array_filter(array_map('trim', explode("\n", $field['options_json'])));
                                                                             foreach ($lines as $line) {
                                                                                 if (str_contains($line, ':')) {
                                                                                     [$okey, $oval] = array_map('trim', explode(':', $line, 2));
                                                                                     $displayOptions[$okey] = $oval;
                                                                                 } else {
                                                                                     $displayOptions[$line] = $line;
                                                                                 }
                                                                             }
                                                                         }
                                                                     }

                                                                     $getDisplayLabel = function($val) use ($displayOptions) {
                                                                         $valStr = (string)$val;
                                                                         if (isset($displayOptions[$valStr])) {
                                                                             return $displayOptions[$valStr];
                                                                         }
                                                                         $flipped = array_flip($displayOptions);
                                                                         if (isset($flipped[$valStr])) {
                                                                             return $valStr;
                                                                         }
                                                                         return $val;
                                                                     };

                                                                     // بررسی آرایه یا مقدار متنی معمولی
                                                                     $rawVals = $fieldValue;
                                                                     if (is_string($fieldValue) && str_starts_with($fieldValue, '[') && str_ends_with($fieldValue, ']')) {
                                                                         $decodedArr = json_decode($fieldValue, true);
                                                                         if (is_array($decodedArr)) {
                                                                             $rawVals = $decodedArr;
                                                                         }
                                                                     }

                                                                     if (is_array($rawVals)) {
                                                                         $mappedVals = array_map($getDisplayLabel, $rawVals);
                                                                         $displayVal = implode(', ', $mappedVals);
                                                                     } else {
                                                                         $displayVal = $getDisplayLabel($fieldValue);
                                                                     }
                                                                 } else {
                                                                     if (is_array($fieldValue)) {
                                                                         $displayVal = implode(', ', $fieldValue);
                                                                     }
                                                                 }
                                                             @endphp
                                                             <input type="text" value="{{ $displayVal }}" readonly class="{{ $lockedInputClass }}">
                                                         @endif

                                                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                             <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                             </svg>
                                                         </div>
                                                     </div>
                                                @else
                                                    {{-- رندر فیلد بر اساس نوع --}}
                                                    @switch($type)
                                                        @case('textarea')
                                                            <textarea id="field_{{ $fid }}" name="{{ $fid }}" rows="4"
                                                                      class="{{ $baseInputClass }} resize-y min-h-[100px]"
                                                                      placeholder="{{ $field['placeholder'] ?? '' }}">{{ $fieldValue }}</textarea>
                                                            @break

                                                        @case('date')
                                                            <input type="text" id="field_{{ $fid }}" name="{{ $fid }}" value="{{ $fieldValue }}"
                                                                   class="{{ $baseInputClass }} font-mono" placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                   data-jdp-only-date>
                                                            @break

                                                        @case('select-province-city')
                                                            @php
                                                                // بارگذاری داده‌های استان و شهر
                                                                $jsonPath = base_path('Modules/Clients/resources/data/iran-provinces-cities.json');
                                                                $provincesData = [];
                                                                if (file_exists($jsonPath)) {
                                                                    $provincesData = json_decode(file_get_contents($jsonPath), true) ?? [];
                                                                }
                                                                $provinces = array_keys($provincesData);

                                                                // مقدار فعلی
                                                                $currentValue = $fieldValue;
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
                                                                searchProvince: '',
                                                                searchCity: '',
                                                                openProvince: false,
                                                                openCity: false,
                                                                init() {
                                                                    if (this.province && this.provincesData[this.province]) {
                                                                        this.cities = this.provincesData[this.province];
                                                                    }
                                                                },
                                                                updateCities() {
                                                                    if (this.province && this.provincesData[this.province]) {
                                                                        this.cities = this.provincesData[this.province];
                                                                        this.city = '';
                                                                        this.searchCity = '';
                                                                        this.updateValue();
                                                                    } else {
                                                                        this.cities = [];
                                                                        this.city = '';
                                                                        this.searchCity = '';
                                                                        this.updateValue();
                                                                    }
                                                                },
                                                                updateValue() {
                                                                    const value = JSON.stringify({province: this.province || '', city: this.city || ''});
                                                                    $refs.hiddenInput.value = value;
                                                                },
                                                                get filteredProvinces() {
                                                                    if (this.searchProvince === '') return this.provinces;
                                                                    return this.provinces.filter(p => p.includes(this.searchProvince));
                                                                },
                                                                get filteredCities() {
                                                                    if (this.searchCity === '') return this.cities;
                                                                    return this.cities.filter(c => c.includes(this.searchCity));
                                                                }
                                                            }">
                                                                <input type="hidden" name="{{ $fid }}" x-ref="hiddenInput" value="{{ is_string($fieldValue) ? $fieldValue : json_encode($currentValue) }}">

                                                                {{-- سلکتور استان با جستجو --}}
                                                                <div class="relative w-full m-0" @click.outside="openProvince = false">
                                                                    <div @click="openProvince = !openProvince"
                                                                         class="{{ $baseInputClass }} flex items-center justify-between cursor-pointer">
                                                                        <span x-text="province ? province : 'انتخاب استان...'" :class="{'text-gray-400': !province}"></span>
                                                                        <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': openProvince}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                                        </svg>
                                                                    </div>

                                                                    <div x-show="openProvince" x-transition
                                                                         class="absolute z-50 mt-1 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl ring-1 ring-black ring-opacity-5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10"
                                                                         style="display: none;">
                                                                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                                                            <input type="text" x-model="searchProvince" placeholder="جستجو..."
                                                                                   class="w-full rounded-lg border-gray-200 bg-gray-50 px-2 py-1.5 text-xs text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                                   @click.stop>
                                                                        </div>
                                                                        <ul class="max-h-60 overflow-y-auto p-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
                                                                            <template x-for="prov in filteredProvinces" :key="prov">
                                                                                <li @click="province = prov; updateCities(); openProvince = false; searchProvince = ''"
                                                                                    class="cursor-pointer select-none py-2 px-3 text-xs rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-700/50"
                                                                                    :class="{'bg-indigo-50 text-indigo-700 font-medium dark:bg-indigo-900/30 dark:text-indigo-300': province === prov, 'text-gray-700 dark:text-gray-200': province !== prov}">
                                                                                    <span x-text="prov"></span>
                                                                                </li>
                                                                            </template>
                                                                            <li x-show="filteredProvinces.length === 0" class="py-2 px-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                                                                                موردی یافت نشد
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>

                                                                {{-- سلکتور شهر با جستجو --}}
                                                                <div class="relative w-full" @click.outside="openCity = false">
                                                                    <div @click="if(province && cities.length > 0) openCity = !openCity"
                                                                         class="{{ $baseInputClass }} flex items-center justify-between cursor-pointer"
                                                                         :class="{'opacity-50 cursor-not-allowed': !province || cities.length === 0}">
                                                                        <span x-text="city ? city : 'انتخاب شهر...'" :class="{'text-gray-400': !city}"></span>
                                                                        <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': openCity}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                                        </svg>
                                                                    </div>

                                                                    <div x-show="openCity" x-transition
                                                                         class="absolute z-50 mt-1 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl ring-1 ring-black ring-opacity-5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10"
                                                                         style="display: none;">
                                                                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                                                            <input type="text" x-model="searchCity" placeholder="جستجو..."
                                                                                   class="w-full rounded-lg border-gray-200 bg-gray-50 px-2 py-1.5 text-xs text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                                   @click.stop>
                                                                        </div>
                                                                        <ul class="max-h-60 overflow-y-auto p-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
                                                                            <template x-for="(cityItem, index) in filteredCities" :key="index">
                                                                                <li @click="city = cityItem; updateValue(); openCity = false; searchCity = ''"
                                                                                    class="cursor-pointer select-none py-2 px-3 text-xs rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-700/50"
                                                                                    :class="{'bg-indigo-50 text-indigo-700 font-medium dark:bg-indigo-900/30 dark:text-indigo-300': city === cityItem, 'text-gray-700 dark:text-gray-200': city !== cityItem}">
                                                                                    <span x-text="cityItem"></span>
                                                                                </li>
                                                                            </template>
                                                                            <li x-show="filteredCities.length === 0" class="py-2 px-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                                                                                موردی یافت نشد
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @break

                                                        @case('select')
                                                            @if(!empty($field['multiple']))
                                                                @php
                                                                    $decodedVal = is_string($fieldValue) ? json_decode($fieldValue, true) : $fieldValue;
                                                                    if (!is_array($decodedVal)) $decodedVal = $decodedVal ? [$decodedVal] : [];
                                                                @endphp
                                                                <select id="field_{{ $fid }}" name="{{ $fid }}[]" multiple class="{{ $baseInputClass }} min-h-[80px]">
                                                                    @foreach($opts as $ov => $ol)
                                                                        @php
                                                                            $v = is_string($ov) ? $ov : $ol;
                                                                            $l = is_string($ol) ? $ol : $ov;
                                                                        @endphp
                                                                        <option value="{{ $v }}" {{ in_array($v, $decodedVal) ? 'selected' : '' }}>{{ $l }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <p class="mt-1 text-xs text-gray-500">برای انتخاب چند مورد کلید Ctrl یا Cmd را نگه دارید.</p>
                                                            @elseif(!empty($field['searchable']))
                                                                {{-- حالت تک انتخابی با امکان جستجو در پورتال کلاینت --}}
                                                                @php
                                                                    $currentValArray = $fieldValue ? [(string) $fieldValue] : [];
                                                                    $selectOptions = [];
                                                                    foreach ($opts as $ov => $ol) {
                                                                        $value = is_string($ov) ? $ov : $ol;
                                                                        $label = is_string($ol) ? $ol : $ov;
                                                                        $selectOptions[] = ['value' => (string) $value, 'label' => $label];
                                                                    }
                                                                @endphp
                                                                <div x-data="{
                                                                    options: @js($selectOptions),
                                                                    selectedValues: @js($currentValArray),
                                                                    search: '',
                                                                    open: false,
                                                                    placeholder: @js($field['placeholder'] ?? 'انتخاب کنید...'),
                                                                    creatable: @js(!empty($field['creatable'])),
                                                                    init() {
                                                                        // Sync values and handle initial value if it's not in options (creatable fallback)
                                                                        const initialVal = Array.isArray(this.selectedValues) ? this.selectedValues[0] : this.selectedValues;
                                                                        if (initialVal && this.creatable && !this.options.some(o => o.value == initialVal)) {
                                                                            this.options.push({ value: String(initialVal), label: String(initialVal) });
                                                                        }

                                                                        this.$watch('selectedValues', (values) => {
                                                                            const arr = Array.isArray(values) ? values : [];
                                                                            $refs.hiddenInput.value = arr[0] || '';
                                                                        });
                                                                    },
                                                                    toggle(value) {
                                                                        value = String(value);
                                                                        if (this.selectedValues.includes(value)) {
                                                                            this.selectedValues = [];
                                                                        } else {
                                                                            this.selectedValues = [value];
                                                                        }
                                                                        this.open = false;
                                                                        this.search = '';
                                                                    },
                                                                    remove(value) {
                                                                        this.selectedValues = [];
                                                                    },
                                                                    addNewOption() {
                                                                        const val = this.search.trim();
                                                                        if (val && !this.options.some(o => o.value.toLowerCase() === val.toLowerCase())) {
                                                                            const newOpt = { value: val, label: val };
                                                                            this.options.push(newOpt);
                                                                            this.toggle(val);
                                                                        }
                                                                    },
                                                                    get filteredOptions() {
                                                                        if (!this.search) return this.options;
                                                                        return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase()));
                                                                    }
                                                                }">
                                                                    <input type="hidden" name="{{ $fid }}" x-ref="hiddenInput" value="{{ $fieldValue }}">
                                                                    
                                                                    <div class="relative group" @click.outside="open = false">
                                                                        {{-- کانتینر اصلی --}}
                                                                        <div class="flex items-center gap-1.5 min-h-[42px] w-full rounded-xl border px-2 py-1.5 transition-all duration-200 cursor-text shadow-sm"
                                                                             :class="open
                                                                                ? 'border-indigo-500 ring-1 ring-indigo-500 bg-white dark:bg-gray-800'
                                                                                : 'border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500'"
                                                                             @click="if (!open) { open = true; search = ''; $nextTick(() => $refs.searchInput?.focus()); }">
                                                                            
                                                                            {{-- متن راهنما --}}
                                                                            <template x-if="selectedValues.length === 0 && !search && !open">
                                                                                <span class="text-xs text-gray-400 dark:text-gray-500 px-1 pointer-events-none select-none">
                                                                                    <span x-text="placeholder"></span>
                                                                                </span>
                                                                            </template>
                                                                            
                                                                            {{-- نمایش مقدار انتخاب شده --}}
                                                                            <template x-if="selectedValues.length > 0 && !open">
                                                                                <span class="text-xs text-gray-900 dark:text-gray-100 truncate flex-1 px-1"
                                                                                      x-text="options.find(o => o.value == selectedValues[0])?.label ?? selectedValues[0]"></span>
                                                                            </template>
                                                                            
                                                                            {{-- جستجو --}}
                                                                            <input x-show="selectedValues.length === 0 || open" x-ref="searchInput" type="text" x-model="search"
                                                                                   class="flex-1 min-w-[60px] border-0 bg-transparent p-0 text-xs text-gray-900 focus:ring-0 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 leading-relaxed"
                                                                                   placeholder="جستجو...">
                                                                            
                                                                            {{-- آیکون‌ها --}}
                                                                            <div class="ml-auto pl-1 text-gray-400 pointer-events-none flex items-center gap-1">
                                                                                <template x-if="selectedValues.length > 0 && !open">
                                                                                    <button type="button" class="p-0.5 rounded-md hover:bg-gray-200 dark:hover:bg-white/10 text-gray-400 hover:text-gray-600 pointer-events-auto" @click.stop="remove(selectedValues[0])">
                                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                                    </button>
                                                                                </template>
                                                                                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                                </svg>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        {{-- لیست کشویی --}}
                                                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                                                             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                                                             x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 translate-y-0"
                                                                             x-transition:leave-end="opacity-0 translate-y-1" style="display: none;" 
                                                                             class="absolute z-50 mt-1 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl ring-1 ring-black ring-opacity-5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
                                                                            <ul class="max-h-60 overflow-y-auto p-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
                                                                                <template x-for="option in filteredOptions" :key="option.value">
                                                                                    <li @click="toggle(option.value); $refs.searchInput.focus()"
                                                                                        class="relative cursor-pointer select-none py-2 pl-9 pr-3 text-right text-xs rounded-lg transition-colors group"
                                                                                        :class="selectedValues.includes(option.value) ? 'bg-indigo-50 text-indigo-700 font-medium dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700/50'">
                                                                                        <span x-text="option.label"></span>
                                                                                        <span x-show="selectedValues.includes(option.value)" class="absolute inset-y-0 left-2 flex items-center text-indigo-600 dark:text-indigo-400">
                                                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                                                        </span>
                                                                                    </li>
                                                                                </template>

                                                                                {{-- امکان افزودن گزینه جدید در صورتی که creatable فعال باشد و جستجو با هیچ گزینه‌ای مطابق نباشد --}}
                                                                                <template x-if="creatable && search.trim() && !options.some(o => o.label.toLowerCase() === search.trim().toLowerCase())">
                                                                                    <li @click="addNewOption(); $refs.searchInput.focus()"
                                                                                        class="relative cursor-pointer select-none py-2 px-3 text-right text-xs text-indigo-600 bg-indigo-50/50 hover:bg-indigo-50 rounded-lg transition-colors font-medium dark:text-indigo-400 dark:bg-indigo-950/20 dark:hover:bg-indigo-900/30">
                                                                                        <span>« افزودن گزینه جدید: <span class="font-bold" x-text="search.trim()"></span> »</span>
                                                                                    </li>
                                                                                </template>

                                                                                <li x-show="filteredOptions.length === 0 && (!creatable || !search.trim())" class="py-3 text-center text-xs text-gray-500 dark:text-gray-400 italic">
                                                                                    موردی یافت نشد.
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="relative">
                                                                    <select id="field_{{ $fid }}" name="{{ $fid }}" class="{{ $baseInputClass }} appearance-none">
                                                                        <option value="">{{ $field['placeholder'] ?? 'انتخاب کنید...' }}</option>
                                                                        @foreach($opts as $ov => $ol)
                                                                            @php
                                                                                $v = is_string($ov) ? $ov : $ol;
                                                                                $l = is_string($ol) ? $ol : $ov;
                                                                            @endphp
                                                                            <option value="{{ $v }}" {{ $fieldValue == $v ? 'selected' : '' }}>{{ $l }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-4 text-gray-500">
                                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            @break

                                                        @case('checkbox')
                                                            <div class="flex items-center h-full pt-1">
                                                                <input type="checkbox" id="field_{{ $fid }}" name="{{ $fid }}" value="1" {{ $fieldValue ? 'checked' : '' }}
                                                                       class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer" />
                                                                <label for="field_{{ $fid }}" class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                                                    {{ $field['placeholder'] ?: 'تأیید / فعال' }}
                                                                </label>
                                                            </div>
                                                            @break

                                                        @case('radio')
                                                            <div class="flex flex-wrap gap-5 pt-2">
                                                                @foreach($opts as $ov => $ol)
                                                                    @php
                                                                        $v = is_string($ov) ? $ov : $ol;
                                                                        $l = is_string($ol) ? $ol : $ov;
                                                                    @endphp
                                                                    <label class="inline-flex items-center gap-2 cursor-pointer group">
                                                                        <div class="relative flex items-center">
                                                                            <input type="radio" name="{{ $fid }}" class="peer sr-only" value="{{ $v }}" {{ $fieldValue == $v ? 'checked' : '' }}>
                                                                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all dark:border-gray-600"></div>
                                                                            <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                                                        </div>
                                                                        <span class="text-sm text-gray-700 group-hover:text-indigo-600 dark:text-gray-300 transition-colors">{{ $l }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                            @break

                                                        @case('file')
                                                            <input type="file" id="field_{{ $fid }}" name="{{ $fid }}"
                                                                   class="block w-full text-sm text-gray-500 file:ml-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 cursor-pointer transition-all border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                                                            @if($fieldValue && is_string($fieldValue))
                                                                <div class="mt-3 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                                                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                                    <span>فایل فعلی:</span>
                                                                    <a href="{{ Storage::url($fieldValue) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline dir-ltr truncate max-w-[200px]">{{ basename($fieldValue) }}</a>
                                                                </div>
                                                            @endif
                                                            @break

                                                        @default
                                                            <input type="{{ $type === 'text' ? 'text' : ($type === 'number' ? 'number' : ($type === 'email' ? 'email' : 'text')) }}"
                                                                   id="field_{{ $fid }}" name="{{ $fid }}" value="{{ $fieldValue }}"
                                                                   class="{{ $baseInputClass }}"
                                                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                   @if($field['required'] ?? false) required @endif>
                                                    @endswitch
                                                @endif

                                                @error($fid)
                                                    <div class="flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400 mt-2">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            {{-- تب امنیت و رمز عبور --}}
                            <div x-show="activeTab === 'security'" x-transition.opacity.duration.300ms x-cloak>
                                <div class="mb-8">
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">تغییر رمز عبور</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        برای تغییر رمز عبور، رمز فعلی و سپس رمز جدید را وارد کنید. اگر قصد تغییر ندارید، این فیلدها را خالی بگذارید.
                                    </p>
                                </div>

                                <div class="max-w-xl space-y-6">
                                    {{-- رمز عبور فعلی --}}
                                    <div>
                                        <label for="current_password" class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">رمز عبور فعلی</label>
                                        <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                                               class="{{ $baseInputClass }} font-mono">
                                        @error('current_password')
                                            <div class="flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400 mt-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="h-px bg-gray-100 dark:bg-gray-700/50 w-full my-6"></div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        {{-- رمز عبور جدید --}}
                                        <div>
                                            <label for="password" class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">رمز عبور جدید</label>
                                            <input type="password" id="password" name="password" autocomplete="new-password"
                                                   class="{{ $baseInputClass }} font-mono" placeholder="حداقل ۸ کاراکتر">
                                            @error('password')
                                                <div class="flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400 mt-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        {{-- تکرار رمز عبور جدید --}}
                                        <div>
                                            <label for="password_confirmation" class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">تکرار رمز عبور جدید</label>
                                            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                                                   class="{{ $baseInputClass }} font-mono" placeholder="تکرار رمز">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- فوتر فرم و دکمه ذخیره --}}
                        <div class="p-6 sm:px-10 sm:py-6 bg-gray-50/50 dark:bg-gray-800/20 border-t border-gray-100 dark:border-gray-700 mt-auto">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                <p class="text-xs font-medium text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    فیلدهای دارای ستاره (<span class="text-red-500 text-lg leading-none mt-1">*</span>) الزامی هستند.
                                </p>
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-all shadow-md shadow-indigo-500/20 active:scale-[0.98]">
                                    <svg class="w-5 h-5 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    ذخیره اطلاعات
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
