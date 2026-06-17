@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all";
    $selectClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 transition-all";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $daysOfWeek = [
        0 => 'یکشنبه',
        1 => 'دوشنبه',
        2 => 'سه‌شنبه',
        3 => 'چهارشنبه',
        4 => 'پنجشنبه',
        5 => 'جمعه',
        6 => 'شنبه'
    ];
@endphp

<div class="space-y-6 pb-24">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 8h2m-2 3h2" /></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">مدیریت حمل و نقل و ارسال</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">تنظیم روش‌های ارسال، زون‌های جغرافیایی، تعرفه وزن و زمان‌بندی ظرفیت‌ها</p>
            </div>
        </div>
        <button wire:click="openForm" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            ثبت مورد جدید
        </button>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-2">
        <button wire:click="setTab('methods')" class="py-2.5 px-4 text-xs sm:text-sm font-bold border-b-2 transition-all {{ $currentTab === 'methods' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            روش‌های ارسال
        </button>
        <button wire:click="setTab('zones')" class="py-2.5 px-4 text-xs sm:text-sm font-bold border-b-2 transition-all {{ $currentTab === 'zones' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            زون‌های جغرافیایی
        </button>
        <button wire:click="setTab('rates')" class="py-2.5 px-4 text-xs sm:text-sm font-bold border-b-2 transition-all {{ $currentTab === 'rates' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            تعرفه‌های وزن و هزینه
        </button>
        <button wire:click="setTab('slots')" class="py-2.5 px-4 text-xs sm:text-sm font-bold border-b-2 transition-all {{ $currentTab === 'slots' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            برنامه‌زمانی و ظرفیت ارسال
        </button>
        <button wire:click="setTab('rules')" class="py-2.5 px-4 text-xs sm:text-sm font-bold border-b-2 transition-all {{ $currentTab === 'rules' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            قوانین تخفیف و ارسال رایگان
        </button>
    </div>

    {{-- Form --}}
    @if($isFormOpen)
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 sm:p-8 rounded-3xl shadow-xl shadow-gray-200/40 dark:shadow-none animate-in fade-in slide-in-from-top-4">
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    @if($currentTab === 'methods')
                        {{ $method_id ? 'ویرایش روش ارسال: ' . $methodName : 'ایجاد روش ارسال جدید' }}
                    @elseif($currentTab === 'zones')
                        {{ $zone_id ? 'ویرایش زون جغرافیایی: ' . $zoneName : 'ایجاد زون جغرافیایی جدید' }}
                    @elseif($currentTab === 'rates')
                        {{ $rate_id ? 'ویرایش تعرفه ارسال' : 'ثبت تعرفه جدید ارسال' }}
                    @elseif($currentTab === 'slots')
                        {{ $slot_id ? 'ویرایش بازه زمانی ارسال' : 'ثبت بازه زمانی ارسال جدید' }}
                    @elseif($currentTab === 'rules')
                        {{ $rule_id ? 'ویرایش قانون تخفیف ارسال: ' . $ruleName : 'ایجاد قانون تخفیف ارسال جدید' }}
                    @endif
                </h2>
                <button wire:click="closeForm" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- 1. SHIPPING METHODS FORM --}}
            @if($currentTab === 'methods')
                <form wire:submit.prevent="saveMethod">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">نام روش ارسال <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="methodName" class="{{ $inputClass }}" placeholder="مثال: پست پیشتاز، تیپاکس، پیک موتوری">
                            @error('methodName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">کد روش ارسال (یکتا) <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="methodCode" class="{{ $inputClass }}" placeholder="مثال: post_pishtaz" @if($method_id) disabled @endif>
                            @error('methodCode') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">درایور محاسبه هزینه <span class="text-red-500">*</span></label>
                            <select wire:model.live="methodDriver" class="{{ $selectClass }}">
                                <option value="flat_rate">هزینه ثابت (تعرفه‌ها)</option>
                                <option value="weight_based">وزن‌محور (تعرفه‌ها)</option>
                                <option value="post_api">اتصال به وب‌سرویس شرکت پست</option>
                                <option value="tipax_api">اتصال به وب‌سرویس تیپاکس</option>
                            </select>
                            @error('methodDriver') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if(in_array($methodDriver, ['post_api', 'tipax_api']))
                        <div class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-6">
                            <h4 class="text-xs font-extrabold text-gray-700 dark:text-gray-300 mb-4">تنظیمات اتصال به وب‌سرویس API</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="{{ $labelClass }}">کلید دسترسی (API Key)</label>
                                    <input type="text" wire:model.defer="methodSettings.api_key" class="{{ $inputClass }}" placeholder="توکن یا API Key دریافت شده">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">کلمه عبور / رمز اتصال (API Secret)</label>
                                    <input type="password" wire:model.defer="methodSettings.api_secret" class="{{ $inputClass }}" placeholder="API Secret یا رمز اتصال">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="{{ $labelClass }}">اولویت مرتب‌سازی</label>
                            <input type="number" wire:model.defer="methodSortOrder" class="{{ $inputClass }}">
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.defer="methodIsActive" class="peer sr-only">
                                <div class="relative w-10 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:bg-indigo-600 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600"></div>
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">روش ارسال فعال است</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" wire:click="closeForm" class="px-6 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-xl text-sm font-bold transition-all">
                            انصراف
                        </button>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-all">
                            ذخیره روش ارسال
                        </button>
                    </div>
                </form>

            {{-- 2. SHIPPING ZONES FORM --}}
            @elseif($currentTab === 'zones')
                <form wire:submit.prevent="saveZone">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">نام زون جغرافیایی <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="zoneName" class="{{ $inputClass }}" placeholder="مثال: تهران، استان‌های مرکزی، نیمه جنوبی کشور">
                            @error('zoneName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- States selection --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">انتخاب استان‌ها</label>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="selectAllProvinces" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">انتخاب همه</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" wire:click="deselectAllProvinces" class="text-[10px] font-bold text-gray-500 hover:text-gray-600 dark:text-gray-400">لغو همه</button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <input type="text" wire:model.live="searchProvince" placeholder="جستجوی استان..." class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                </div>
                                <div class="flex flex-wrap gap-1.5 max-h-48 overflow-y-auto p-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                    @php
                                        $filteredProvinces = $this->getFilteredProvinces();
                                    @endphp
                                    @foreach($filteredProvinces as $pName)
                                        @php
                                            $isSelected = in_array($pName, $selectedStates);
                                        @endphp
                                        <button type="button"
                                                wire:click="toggleState('{{ $pName }}')"
                                                wire:key="province-pill-{{ $pName }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 {{ $isSelected ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                            @if($isSelected)
                                                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                            <span>{{ $pName }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Cities selection --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">انتخاب شهرها (برای فیلتر دقیق‌تر - اختیاری)</label>
                                    @if(!empty($citiesList))
                                        <div class="flex gap-2">
                                            <button type="button" wire:click="selectAllCities" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">انتخاب همه</button>
                                            <span class="text-gray-300">|</span>
                                            <button type="button" wire:click="deselectAllCities" class="text-[10px] font-bold text-gray-500 hover:text-gray-600 dark:text-gray-400">لغو همه</button>
                                        </div>
                                    @endif
                                </div>
                                @if(!empty($citiesList))
                                    <div class="mb-2">
                                        <input type="text" wire:model.live="searchCity" placeholder="جستجوی شهر..." class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                    </div>
                                @endif
                                <div class="flex flex-wrap gap-1.5 max-h-48 overflow-y-auto p-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                    @php
                                        $filteredCities = $this->getFilteredCities();
                                    @endphp
                                    @forelse($filteredCities as $cName)
                                        @php
                                            $isSelected = in_array($cName, $selectedCities);
                                        @endphp
                                        <button type="button"
                                                wire:click="toggleCity('{{ $cName }}')"
                                                wire:key="city-pill-{{ $cName }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 {{ $isSelected ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                            @if($isSelected)
                                                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                            <span>{{ $cName }}</span>
                                        </button>
                                    @empty
                                        <p class="text-xs text-gray-400 text-center w-full py-10">
                                            @if(empty($selectedStates))
                                                ابتدا استان(ها) را انتخاب کنید.
                                            @else
                                                شهری یافت نشد.
                                            @endif
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.defer="zoneIsActive" class="peer sr-only">
                                <div class="relative w-10 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:bg-indigo-600 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600"></div>
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">زون جغرافیایی فعال است</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" wire:click="closeForm" class="px-6 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-xl text-sm font-bold transition-all">
                            انصراف
                        </button>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-all">
                            ذخیره زون جغرافیایی
                        </button>
                    </div>
                </form>

            {{-- 3. SHIPPING RATES FORM --}}
            @elseif($currentTab === 'rates')
                <form wire:submit.prevent="saveRate">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">روش حمل و نقل <span class="text-red-500">*</span></label>
                            <select wire:model.defer="rateMethodId" class="{{ $selectClass }}">
                                @foreach(\Modules\Market\Entities\ShippingMethod::all() as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                            @error('rateMethodId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">زون جغرافیایی مقصد <span class="text-red-500">*</span></label>
                            <select wire:model.defer="rateZoneId" class="{{ $selectClass }}">
                                @foreach(\Modules\Market\Entities\ShippingZone::all() as $z)
                                    <option value="{{ $z->id }}">{{ $z->name }}</option>
                                @endforeach
                            </select>
                            @error('rateZoneId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="{{ $labelClass }}">حداقل وزن (گرم) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.defer="minWeight" class="{{ $inputClass }}">
                            @error('minWeight') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">حداکثر وزن (گرم) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.defer="maxWeight" class="{{ $inputClass }}">
                            @error('maxWeight') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">حداقل مبلغ خرید سبد ({{ $this->getCurrencyLabel() }}) <span class="text-red-500">*</span></label>
                            <div x-data="{ 
                                rawVal: @entangle('minOrderPrice'),
                                get displayVal() {
                                    if (this.rawVal === null || this.rawVal === undefined || this.rawVal === '') return '';
                                    let num = this.rawVal.toString().replace(/,/g, '');
                                    if (isNaN(num)) return '';
                                    return Number(num).toLocaleString('en-US');
                                },
                                set displayVal(val) {
                                    let num = val.replace(/,/g, '');
                                    this.rawVal = isNaN(num) || num === '' ? '' : num;
                                }
                            }">
                                <input type="text" x-model="displayVal" class="{{ $inputClass }}">
                            </div>
                            @error('minOrderPrice') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="{{ $labelClass }}">هزینه ارسال پایه ({{ $this->getCurrencyLabel() }}) <span class="text-red-500">*</span></label>
                            <div x-data="{ 
                                rawVal: @entangle('cost'),
                                get displayVal() {
                                    if (this.rawVal === null || this.rawVal === undefined || this.rawVal === '') return '';
                                    let num = this.rawVal.toString().replace(/,/g, '');
                                    if (isNaN(num)) return '';
                                    return Number(num).toLocaleString('en-US');
                                },
                                set displayVal(val) {
                                    let num = val.replace(/,/g, '');
                                    this.rawVal = isNaN(num) || num === '' ? '' : num;
                                }
                            }">
                                <input type="text" x-model="displayVal" class="{{ $inputClass }}">
                            </div>
                            @error('cost') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">هزینه اضافه به ازای هر کیلوگرم مازاد ({{ $this->getCurrencyLabel() }})</label>
                            <div x-data="{ 
                                rawVal: @entangle('perKgCost'),
                                get displayVal() {
                                    if (this.rawVal === null || this.rawVal === undefined || this.rawVal === '') return '';
                                    let num = this.rawVal.toString().replace(/,/g, '');
                                    if (isNaN(num)) return '';
                                    return Number(num).toLocaleString('en-US');
                                },
                                set displayVal(val) {
                                    let num = val.replace(/,/g, '');
                                    this.rawVal = isNaN(num) || num === '' ? '' : num;
                                }
                            }">
                                <input type="text" x-model="displayVal" class="{{ $inputClass }}">
                            </div>
                            @error('perKgCost') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" wire:click="closeForm" class="px-6 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-xl text-sm font-bold transition-all">
                            انصراف
                        </button>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-all">
                            ذخیره تعرفه
                        </button>
                    </div>
                </form>

            {{-- 4. DELIVERY SLOTS FORM --}}
            @elseif($currentTab === 'slots')
                <form wire:submit.prevent="saveSlot">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">روش حمل و نقل <span class="text-red-500">*</span></label>
                            <select wire:model.defer="slotMethodId" class="{{ $selectClass }}">
                                @foreach(\Modules\Market\Entities\ShippingMethod::all() as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                            @error('slotMethodId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">روزهای هفته <span class="text-red-500">*</span></label>
                            </div>
                            <div class="flex flex-wrap gap-1.5 p-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                @foreach($daysOfWeek as $num => $dayName)
                                    @php $isSelected = in_array($num, $slotDays); @endphp
                                    <button type="button"
                                            wire:click="toggleSlotDay({{ $num }})"
                                            wire:key="slot-day-{{ $num }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 {{ $isSelected ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                        @if($isSelected)
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                        <span>{{ $dayName }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('slotDays') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Geographic limitations for slot --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">استان‌ها (محدود به این استان‌ها - اختیاری)</label>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="selectAllSlotProvinces" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">انتخاب همه</button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" wire:click="deselectAllSlotProvinces" class="text-[10px] font-bold text-gray-500 hover:text-gray-600 dark:text-gray-400">لغو همه</button>
                                </div>
                            </div>
                            <div class="mb-2">
                                <input type="text" wire:model.live="searchSlotProvince" placeholder="جستجوی استان..." class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                            </div>
                            <div class="flex flex-wrap gap-1.5 max-h-48 overflow-y-auto p-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                @php $filteredProvinces = $this->getFilteredSlotProvinces(); @endphp
                                @foreach($filteredProvinces as $pName)
                                    @php $isSelected = in_array($pName, $slotStates); @endphp
                                    <button type="button"
                                            wire:click="toggleSlotState('{{ $pName }}')"
                                            wire:key="slot-province-{{ $pName }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 {{ $isSelected ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                        @if($isSelected)
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                        <span>{{ $pName }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('slotStates') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">شهرها (محدود به این شهرها - اختیاری)</label>
                                @if(!empty($citiesListForSlot))
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="selectAllSlotCities" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">انتخاب همه</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" wire:click="deselectAllSlotCities" class="text-[10px] font-bold text-gray-500 hover:text-gray-600 dark:text-gray-400">لغو همه</button>
                                    </div>
                                @endif
                            </div>
                            @if(!empty($citiesListForSlot))
                                <div class="mb-2">
                                    <input type="text" wire:model.live="searchSlotCity" placeholder="جستجوی شهر..." class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                </div>
                            @endif
                            <div class="flex flex-wrap gap-1.5 max-h-48 overflow-y-auto p-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                @php $filteredCities = $this->getFilteredSlotCities(); @endphp
                                @forelse($filteredCities as $cName)
                                    @php $isSelected = in_array($cName, $slotCities); @endphp
                                    <button type="button"
                                            wire:click="toggleSlotCity('{{ $cName }}')"
                                            wire:key="slot-city-{{ $cName }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 {{ $isSelected ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                        @if($isSelected)
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                        <span>{{ $cName }}</span>
                                    </button>
                                @empty
                                    <p class="text-xs text-gray-400 text-center w-full py-10">
                                        @if(empty($slotStates))
                                            ابتدا استان(ها) را انتخاب کنید.
                                        @else
                                            شهری یافت نشد.
                                        @endif
                                    </p>
                                @endforelse
                            </div>
                            @error('slotCities') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="{{ $labelClass }}">ساعت شروع <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="startTime" class="{{ $inputClass }}" placeholder="مثال: 09:00">
                            @error('startTime') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">ساعت پایان <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="endTime" class="{{ $inputClass }}" placeholder="مثال: 13:00">
                            @error('endTime') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">ظرفیت ارسال (تعداد کل سفارشات قابل پذیرش) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.defer="capacity" class="{{ $inputClass }}">
                            @error('capacity') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" wire:click="closeForm" class="px-6 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-xl text-sm font-bold transition-all">
                            انصراف
                        </button>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-all">
                            ذخیره بازه تحویل
                        </button>
                    </div>
                </form>

            {{-- 5. DISCOUNT RULES FORM --}}
            @elseif($currentTab === 'rules')
                <form wire:submit.prevent="saveRule">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">نام قانون ارسال تخفیف‌دار/رایگان <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="ruleName" class="{{ $inputClass }}" placeholder="مثال: ارسال رایگان خریدهای بالای ۲ میلیون">
                            @error('ruleName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">حداقل مبلغ کل سبد خرید ({{ $this->getCurrencyLabel() }})</label>
                            <div x-data="{ 
                                rawVal: @entangle('minGrandTotal'),
                                get displayVal() {
                                    if (this.rawVal === null || this.rawVal === undefined || this.rawVal === '') return '';
                                    let num = this.rawVal.toString().replace(/,/g, '');
                                    if (isNaN(num)) return '';
                                    return Number(num).toLocaleString('en-US');
                                },
                                set displayVal(val) {
                                    let num = val.replace(/,/g, '');
                                    this.rawVal = isNaN(num) || num === '' ? '' : num;
                                }
                            }">
                                <input type="text" x-model="displayVal" class="{{ $inputClass }}" placeholder="اختیاری">
                            </div>
                            @error('minGrandTotal') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="{{ $labelClass }}">نوع عملیات تخفیف <span class="text-red-500">*</span></label>
                            <select wire:model.live="actionType" class="{{ $selectClass }}">
                                <option value="free_shipping">ارسال کاملاً رایگان</option>
                                <option value="percentage_discount">تخفیف درصدی روی هزینه ارسال</option>
                                <option value="fixed_discount">کاهش مبلغ ثابت از هزینه ارسال</option>
                            </select>
                            @error('actionType') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if($actionType !== 'free_shipping')
                            <div>
                                <label class="{{ $labelClass }}">
                                    @if($actionType === 'percentage_discount')
                                        مقدار تخفیف (درصد) <span class="text-red-500">*</span>
                                    @else
                                        مقدار تخفیف ({{ $this->getCurrencyLabel() }}) <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                @if($actionType === 'percentage_discount')
                                    <input type="number" wire:model.defer="actionValue" class="{{ $inputClass }}">
                                @else
                                    <div x-data="{ 
                                        rawVal: @entangle('actionValue'),
                                        get displayVal() {
                                            if (this.rawVal === null || this.rawVal === undefined || this.rawVal === '') return '';
                                            let num = this.rawVal.toString().replace(/,/g, '');
                                            if (isNaN(num)) return '';
                                            return Number(num).toLocaleString('en-US');
                                        },
                                        set displayVal(val) {
                                            let num = val.replace(/,/g, '');
                                            this.rawVal = isNaN(num) || num === '' ? '' : num;
                                        }
                                    }">
                                        <input type="text" x-model="displayVal" class="{{ $inputClass }}">
                                    </div>
                                @endif
                                @error('actionValue') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-6">
                        <h4 class="text-xs font-extrabold text-gray-700 dark:text-gray-300 mb-4">شرط‌های اختصاصی اعمال قانون (اعمال روی کالاها - در صورت خالی بودن روی کل سبد اعمال می‌شود)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            {{-- 1. Brands selector --}}
                            <div class="flex flex-col">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">محدود به برندهای خاص</label>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="selectAllFilteredBrands" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">انتخاب همه</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" wire:click="deselectAllFilteredBrands" class="text-[10px] font-bold text-gray-500 hover:text-gray-600 dark:text-gray-400">لغو همه</button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <input type="text" wire:model.live="searchBrand" placeholder="جستجوی برند..." class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                </div>
                                <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                    @foreach($brands as $brand)
                                        @php $isSelected = in_array($brand->id, $condBrandIds); @endphp
                                        <button type="button"
                                                wire:click="toggleRuleBrand({{ $brand->id }})"
                                                wire:key="rule-brand-{{ $brand->id }}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-[10px] font-semibold transition-all duration-150 {{ $isSelected ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                            @if($isSelected)
                                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                            <span>{{ $brand->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                @if(!empty($condBrandIds))
                                    <div class="flex flex-wrap gap-1 mt-2 p-1.5 border border-indigo-100 dark:border-indigo-900/30 rounded-lg bg-indigo-50/20 dark:bg-indigo-900/10">
                                        @foreach($brands as $brand)
                                            @if(in_array($brand->id, $condBrandIds))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    {{ $brand->name }}
                                                    <button type="button" wire:click="toggleRuleBrand({{ $brand->id }})" class="text-indigo-500 hover:text-red-500 focus:outline-none">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- 2. Categories selector --}}
                            <div class="flex flex-col">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">محدود به دسته‌بندی‌های خاص</label>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="selectAllFilteredCategories" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">انتخاب همه</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" wire:click="deselectAllFilteredCategories" class="text-[10px] font-bold text-gray-500 hover:text-gray-600 dark:text-gray-400">لغو همه</button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <input type="text" wire:model.live="searchCategory" placeholder="جستجوی دسته‌بندی..." class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                </div>
                                <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                    @foreach($categories as $cat)
                                        @php $isSelected = in_array($cat->id, $condCategoryIds); @endphp
                                        <button type="button"
                                                wire:click="toggleRuleCategory({{ $cat->id }})"
                                                wire:key="rule-cat-{{ $cat->id }}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-[10px] font-semibold transition-all duration-150 {{ $isSelected ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                            @if($isSelected)
                                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                            <span>{{ $cat->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                @if(!empty($condCategoryIds))
                                    <div class="flex flex-wrap gap-1 mt-2 p-1.5 border border-indigo-100 dark:border-indigo-900/30 rounded-lg bg-indigo-50/20 dark:bg-indigo-900/10">
                                        @foreach($categories as $cat)
                                            @if(in_array($cat->id, $condCategoryIds))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    {{ $cat->name }}
                                                    <button type="button" wire:click="toggleRuleCategory({{ $cat->id }})" class="text-indigo-500 hover:text-red-500 focus:outline-none">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- 3. Display Categories selector --}}
                            <div class="flex flex-col">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">محدود به دسته‌بندی‌های مجزا</label>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="selectAllFilteredDisplayCategories" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">انتخاب همه</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" wire:click="deselectAllFilteredDisplayCategories" class="text-[10px] font-bold text-gray-500 hover:text-gray-600 dark:text-gray-400">لغو همه</button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <input type="text" wire:model.live="searchDisplayCategory" placeholder="جستجوی دسته مجزا..." class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                </div>
                                <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                    @foreach($displayCategories as $dcat)
                                        @php $isSelected = in_array($dcat->id, $condDisplayCategoryIds); @endphp
                                        <button type="button"
                                                wire:click="toggleRuleDisplayCategory({{ $dcat->id }})"
                                                wire:key="rule-dcat-{{ $dcat->id }}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-[10px] font-semibold transition-all duration-150 {{ $isSelected ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                            @if($isSelected)
                                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                            <span>{{ $dcat->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                @if(!empty($condDisplayCategoryIds))
                                    <div class="flex flex-wrap gap-1 mt-2 p-1.5 border border-indigo-100 dark:border-indigo-900/30 rounded-lg bg-indigo-50/20 dark:bg-indigo-900/10">
                                        @foreach($displayCategories as $dcat)
                                            @if(in_array($dcat->id, $condDisplayCategoryIds))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    {{ $dcat->name }}
                                                    <button type="button" wire:click="toggleRuleDisplayCategory({{ $dcat->id }})" class="text-indigo-500 hover:text-red-500 focus:outline-none">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- 4. Products selector --}}
                            <div class="flex flex-col">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">محدود به محصولات خاص</label>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="selectAllFilteredProducts" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">انتخاب همه</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" wire:click="deselectAllFilteredProducts" class="text-[10px] font-bold text-gray-500 hover:text-gray-600 dark:text-gray-400">لغو همه</button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <input type="text" wire:model.live="searchProduct" placeholder="جستجوی محصول..." class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                </div>
                                <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                    @foreach($products as $p)
                                        @php $isSelected = in_array($p->id, $condProductIds); @endphp
                                        <button type="button"
                                                wire:click="toggleRuleProduct({{ $p->id }})"
                                                wire:key="rule-prod-{{ $p->id }}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-[10px] font-semibold transition-all duration-150 {{ $isSelected ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                            @if($isSelected)
                                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                            <span>{{ $p->title }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                @if(!empty($condProductIds))
                                    <div class="flex flex-wrap gap-1 mt-2 p-1.5 border border-indigo-100 dark:border-indigo-900/30 rounded-lg bg-indigo-50/20 dark:bg-indigo-900/10">
                                        @foreach($products as $p)
                                            @if(in_array($p->id, $condProductIds))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    {{ $p->title }}
                                                    <button type="button" wire:click="toggleRuleProduct({{ $p->id }})" class="text-indigo-500 hover:text-red-500 focus:outline-none">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- 5. Variants selector --}}
                            <div class="flex flex-col">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">محدود به تنوع‌های خاص</label>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="selectAllFilteredVariants" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">انتخاب همه</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" wire:click="deselectAllFilteredVariants" class="text-[10px] font-bold text-gray-500 hover:text-gray-600 dark:text-gray-400">لغو همه</button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <input type="text" wire:model.live="searchVariant" placeholder="جستجوی تنوع..." class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                </div>
                                <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                    @foreach($variants as $v)
                                        @php 
                                            $isSelected = in_array($v->id, $condVariantIds); 
                                            $pTitle = $v->masterProduct->title ?? 'محصول نامشخص';
                                            $vName = $v->name ? " ({$v->name})" : "";
                                        @endphp
                                        <button type="button"
                                                wire:click="toggleRuleVariant({{ $v->id }})"
                                                wire:key="rule-variant-{{ $v->id }}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-[10px] font-semibold transition-all duration-150 {{ $isSelected ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-500/20' : 'bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                            @if($isSelected)
                                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                            <span>{{ $pTitle }}{{ $vName }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                @if(!empty($condVariantIds))
                                    <div class="flex flex-wrap gap-1 mt-2 p-1.5 border border-indigo-100 dark:border-indigo-900/30 rounded-lg bg-indigo-50/20 dark:bg-indigo-900/10">
                                        @foreach($variants as $v)
                                            @if(in_array($v->id, $condVariantIds))
                                                @php 
                                                    $pTitle = $v->masterProduct->title ?? 'محصول نامشخص';
                                                    $vName = $v->name ? " ({$v->name})" : "";
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    {{ $pTitle }}{{ $vName }}
                                                    <button type="button" wire:click="toggleRuleVariant({{ $v->id }})" class="text-indigo-500 hover:text-red-500 focus:outline-none">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                    <div class="flex items-center mt-6">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model.defer="ruleIsActive" class="peer sr-only">
                            <div class="relative w-10 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:bg-indigo-600 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600"></div>
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">قانون تخفیف فعال است</span>
                        </label>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" wire:click="closeForm" class="px-6 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-xl text-sm font-bold transition-all">
                            انصراف
                        </button>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-all">
                            ذخیره قانون تخفیف
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    {{-- LISTS BASED ON TAB --}}

    {{-- TAB 1: SHIPPING METHODS LIST --}}
    @if($currentTab === 'methods')
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-right">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">کد روش</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">نام روش ارسال</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">درایور محاسبه</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">ترتیب</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">وضعیت</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400 pl-6 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($methods as $m)
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50/30 dark:bg-transparent">{{ $m->code }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $m->name }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    @if($m->driver === 'flat_rate') هزینه ثابت
                                    @elseif($m->driver === 'weight_based') وزن‌محور
                                    @elseif($m->driver === 'post_api') وب‌سرویس شرکت پست
                                    @elseif($m->driver === 'tipax_api') وب‌سرویس تیپاکس
                                    @else {{ $m->driver }} @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $m->sort_order }}</td>
                                <td class="px-6 py-4">
                                    <button wire:click="toggleMethodActive({{ $m->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium border {{ $m->is_active ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 border-gray-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $m->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        {{ $m->is_active ? 'فعال' : 'غیرفعال' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="openForm({{ $m->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="deleteMethod({{ $m->id }})" onclick="confirm('آیا از حذف این روش ارسال مطمئن هستید؟ با حذف این مورد، تمام زمان‌بندی‌ها و تعرفه‌های مرتبط نیز حذف می‌شوند.') || event.stopImmediatePropagation()" class="p-2 text-red-500 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">هیچ روش ارسالی تاکنون تعریف نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($methods->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    {{ $methods->links() }}
                </div>
            @endif
        </div>

    {{-- TAB 2: SHIPPING ZONES LIST --}}
    @elseif($currentTab === 'zones')
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-right">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">نام زون</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">استان‌های تحت پوشش</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">شهرهای تحت پوشش</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">وضعیت</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400 pl-6 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($zones as $z)
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $z->name }}</td>
                                <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-[200px] truncate" title="{{ implode('، ', $z->states ?? []) }}">
                                    {{ empty($z->states) ? 'کل کشور (سراسری)' : implode('، ', $z->states) }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-[200px] truncate" title="{{ implode('، ', $z->cities ?? []) }}">
                                    {{ empty($z->cities) ? 'کل شهرهای استان' : implode('، ', $z->cities) }}
                                </td>
                                <td class="px-6 py-4">
                                    <button wire:click="toggleZoneActive({{ $z->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium border {{ $z->is_active ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 border-gray-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $z->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        {{ $z->is_active ? 'فعال' : 'غیرفعال' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="openForm({{ $z->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="deleteZone({{ $z->id }})" onclick="confirm('آیا از حذف این محدوده جغرافیایی مطمئن هستید؟') || event.stopImmediatePropagation()" class="p-2 text-red-500 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">هیچ زون جغرافیایی تعریف نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($zones->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    {{ $zones->links() }}
                </div>
            @endif
        </div>

    {{-- TAB 3: SHIPPING RATES LIST --}}
    @elseif($currentTab === 'rates')
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-right">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">روش ارسال</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">زون جغرافیایی مقصد</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">محدوده وزن (گرم)</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">حداقل خرید ({{ $this->getCurrencyLabel() }})</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">هزینه ارسال پایه</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">هزینه کیلوگرم اضافه</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400 pl-6 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($rates as $r)
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $r->method->name ?? 'حذف شده' }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $r->zone->name ?? 'سراسری/کل کشور' }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ number_format($r->min_weight) }} الی {{ number_format($r->max_weight) }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ number_format($r->min_order_price) }} {{ $this->getCurrencyLabel() }}</td>
                                <td class="px-6 py-4 font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($r->cost) }} {{ $this->getCurrencyLabel() }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ number_format($r->per_kg_cost) }} {{ $this->getCurrencyLabel() }}</td>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="openForm({{ $r->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="deleteRate({{ $r->id }})" onclick="confirm('آیا مطمئن هستید؟') || event.stopImmediatePropagation()" class="p-2 text-red-500 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">هیچ تعرفه هزینه‌ای تاکنون ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rates->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    {{ $rates->links() }}
                </div>
            @endif
        </div>

    {{-- TAB 4: DELIVERY SLOTS LIST --}}
    @elseif($currentTab === 'slots')
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-right">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">روش ارسال</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">روز هفته</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">محدوده جغرافیایی</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">ساعت شروع</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">ساعت پایان</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">ظرفیت در هر بازه</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400 pl-6 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($slots as $s)
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $s->method->name ?? 'حذف شده' }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    @if(is_array($s->days))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($s->days as $d)
                                                <span class="inline-block bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded px-2 py-0.5 text-xs">{{ $daysOfWeek[$d] ?? $d }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-semibold">
                                    @if(!empty($s->states))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($s->states as $st)
                                                <span class="inline-block bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 rounded px-2 py-0.5 text-xs">
                                                    {{ $st }}
                                                    @if(!empty($s->cities))
                                                        (شهرهای انتخابی)
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">سراسر کشور</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-bold">{{ date('H:i', strtotime($s->start_time)) }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-bold">{{ date('H:i', strtotime($s->end_time)) }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $s->capacity }} سفارش</td>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="openForm({{ $s->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="deleteSlot({{ $s->id }})" onclick="confirm('آیا از حذف این بازه زمانی تحویل مطمئن هستید؟') || event.stopImmediatePropagation()" class="p-2 text-red-500 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">هیچ بازه ظرفیت و زمان‌بندی تحویلی تعریف نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($slots->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    {{ $slots->links() }}
                </div>
            @endif
        </div>

    {{-- TAB 5: SHIPPING RULES LIST --}}
    @elseif($currentTab === 'rules')
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-right">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">نام قانون</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">نوع تخفیف</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">حداقل خرید کل ({{ $this->getCurrencyLabel() }})</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">شرط‌های استثنا</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400">وضعیت</th>
                            <th class="px-6 py-4 font-semibold text-gray-500 dark:text-gray-400 pl-6 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($rules as $r)
                            @php
                                $conds = $r->conditions ?? [];
                                $brandCount = count($conds['brand_ids'] ?? []);
                                $catCount = count($conds['category_ids'] ?? []);
                                $dispCatCount = count($conds['display_category_ids'] ?? []);
                                $prodCount = count($conds['product_ids'] ?? []);
                                $varCount = count($conds['variant_ids'] ?? []);
                            @endphp
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $r->name }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    @if($r->action_type === 'free_shipping') ارسال کاملاً رایگان
                                    @elseif($r->action_type === 'percentage_discount') {{ $r->action_value }}% تخفیف هزینه ارسال
                                    @elseif($r->action_type === 'fixed_discount') {{ number_format($r->action_value) }} تومان کسر هزینه ارسال
                                    @else {{ $r->action_type }} @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ number_format($r->min_grand_total) }} تومان</td>
                                <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
                                    @if($brandCount > 0) <span class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-[10px]">{{ $brandCount }} برند</span> @endif
                                    @if($catCount > 0) <span class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-[10px]">{{ $catCount }} دسته‌بندی</span> @endif
                                    @if($dispCatCount > 0) <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded text-[10px]">{{ $dispCatCount }} دسته مجزا</span> @endif
                                    @if($prodCount > 0) <span class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-[10px]">{{ $prodCount }} محصول</span> @endif
                                    @if($varCount > 0) <span class="bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded text-[10px]">{{ $varCount }} تنوع</span> @endif
                                    @if($brandCount == 0 && $catCount == 0 && $dispCatCount == 0 && $prodCount == 0 && $varCount == 0) بدون محدودیت کالا @endif
                                </td>
                                <td class="px-6 py-4">
                                    <button wire:click="toggleRuleActive({{ $r->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium border {{ $r->is_active ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 border-gray-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $r->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        {{ $r->is_active ? 'فعال' : 'غیرفعال' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="openForm({{ $r->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="deleteRule({{ $r->id }})" onclick="confirm('آیا مطمئن هستید؟') || event.stopImmediatePropagation()" class="p-2 text-red-500 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">هیچ قانون تخفیفی تاکنون تعریف نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rules->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    {{ $rules->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
