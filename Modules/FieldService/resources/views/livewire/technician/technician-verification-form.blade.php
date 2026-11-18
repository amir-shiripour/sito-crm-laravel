@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6 font-sans";
    $headerClass = "px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between gap-2 sm:gap-4";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-slate-200 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-500";
    $provincesData = $isInitial ? \App\Helpers\ProvinceCity::getAll() : [];
@endphp

<div>
    <form wire:submit="saveVerification" class="{{ $cardClass }}">
        <div class="{{ $headerClass }}">
            <div class="min-w-0">
                <h2 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 truncate">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                    <span class="truncate">{{ $isInitial ? 'احراز هویت تکنسین' : 'تخصص‌ها و حوزه‌های کاری تکنسین' }}</span>
                </h2>
                @if(!$isInitial)
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1.5">
                        انتخاب و مدیریت تخصص‌ها و حوزه‌های کاری فعال شما در رادار خدمات فنی
                    </p>
                @endif
            </div>

            @if($technician)
                <div class="shrink-0">
                    @if($needsVerification)
                        <span class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold whitespace-nowrap bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                            نیاز به تکمیل احراز هویت
                        </span>
                    @elseif($technician->status->value === 'pending_approval')
                        <span class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold whitespace-nowrap bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                            در انتظار تایید مدیریت
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold whitespace-nowrap {{ $technician->status->badgeClass() }}">
                            {{ $technician->status->label() }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @php
            $isRejected = $isInitial && $latestRegRequest && $latestRegRequest->status === 'rejected';
            $isAwaitingApproval = $isInitial && !$isRejected && $technician && $technician->status->value === 'pending_approval' && !$needsVerification;
        @endphp

        @if($isAwaitingApproval)
            <div class="p-8 sm:p-10 text-center space-y-5">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                    درخواست احراز هویت شما ثبت شد و در انتظار بررسی است
                </h3>

                @if($infoMessage)
                    <div class="max-w-md mx-auto p-3 rounded-xl bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs font-bold">
                        {{ $infoMessage }}
                    </div>
                @endif

                <div class="pt-1 flex items-center justify-center">
                    <button type="button" wire:click="checkApprovalStatus" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition no-underline disabled:opacity-70">
                        <span wire:loading.remove wire:target="checkApprovalStatus">بررسی وضعیت تایید</span>
                        <span wire:loading wire:target="checkApprovalStatus">در حال بررسی...</span>
                    </button>
                </div>
            </div>
        @else
        <div class="p-6 space-y-6">
            @if($isRejected)
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs space-y-1.5">
                    <div class="font-bold">درخواست احراز هویت شما رد شده است.</div>
                    @if($latestRegRequest->rejection_reason)
                        <div>دلیل رد: <span class="font-bold">{{ $latestRegRequest->rejection_reason }}</span></div>
                    @endif
                    <div class="text-[11px] opacity-90">لطفاً موارد اصلاحی را اعمال کرده و درخواست را مجدداً ارسال فرمایید.</div>
                </div>
            @endif
            @if($errorMessage)
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold">
                    {{ $errorMessage }}
                </div>
            @endif

            @if($successMessage)
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold">
                    {{ $successMessage }}
                </div>
            @endif

            {{-- All Profile / Verification Custom Fields (only in initial verification tab) --}}
            @if($isInitial && $profileFields->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($profileFields as $field)
                        @php
                            $fieldName = $field->field_name;
                            $type = strtolower($field->field_type ?? 'text');
                            $isRequired = (bool) $field->is_required;
                            $opts = \Modules\FieldService\App\Support\TechnicianCustomFieldsHelper::extractOptions($field);
                            $isFullWidth = in_array($type, ['textarea', 'file'], true)
                                || ($type === 'checkbox' && !empty($opts))
                                || ($type === 'radio' && !empty($opts));
                            $isLtr = in_array($type, ['number', 'email', 'url', 'tel', 'date', 'password'], true)
                                || in_array($fieldName, ['national_code', 'shaba_number'], true);
                            $inputId = 'init_ver_' . $fieldName;
                            $uploadedFile = $customFiles[$fieldName] ?? null;
                            $existingPath = $existingFiles[$fieldName] ?? null;
                        @endphp

                        <div class="{{ $isFullWidth ? 'md:col-span-2' : 'md:col-span-1' }}">
                            <label for="{{ $inputId }}" class="{{ $labelClass }}">
                                {{ $field->label }}
                                @if($isRequired) <span class="text-rose-500">*</span> @endif
                            </label>

                            @if($fieldName === 'province' && in_array($type, ['select', 'text'], true))
                                <div x-data="{
                                        open: false,
                                        search: '',
                                        province: $wire.entangle('customFields.province'),
                                        provinces: @js(array_keys($provincesData)),
                                        provincesData: @js($provincesData),
                                        get filtered() {
                                            const q = (this.search || '').trim();
                                            return q ? this.provinces.filter(p => p.includes(q)) : this.provinces;
                                        },
                                        selectProvince(p) {
                                            this.province = p;
                                            const validCities = this.provincesData[p] || [];
                                            const currentCity = $wire.get('customFields.city');
                                            if (currentCity && !validCities.includes(currentCity)) {
                                                $wire.set('customFields.city', '');
                                            }
                                            this.open = false;
                                            this.search = '';
                                        }
                                     }"
                                     @click.outside="open = false"
                                     class="relative font-sans">
                                    <button type="button" @click="open = !open"
                                            class="{{ $inputClass }} flex items-center justify-between cursor-pointer text-right">
                                        <span x-text="province || 'انتخاب استان...'"
                                              :class="province ? 'text-gray-900 dark:text-gray-100 font-medium' : 'text-gray-400 dark:text-gray-500'"
                                              class="truncate"></span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="{'rotate-180 text-indigo-500': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition style="display: none;"
                                         class="absolute z-50 mt-1.5 w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800">
                                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                            <input type="text" x-model="search" placeholder="جستجوی استان..." @click.stop
                                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                        </div>
                                        <ul class="max-h-56 overflow-y-auto p-1.5 space-y-0.5">
                                            <template x-for="prov in filtered" :key="prov">
                                                <li @click="selectProvince(prov)"
                                                    class="cursor-pointer select-none py-2 px-3 text-xs rounded-xl transition-colors flex items-center justify-between"
                                                    :class="province === prov ? 'bg-indigo-50 text-indigo-700 font-bold dark:bg-indigo-500/15 dark:text-indigo-300' : 'text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-gray-700/60'">
                                                    <span x-text="prov"></span>
                                                    <svg x-show="province === prov" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </li>
                                            </template>
                                            <li x-show="filtered.length === 0" class="py-3 px-3 text-xs text-gray-500 dark:text-slate-400 text-center">استانی یافت نشد</li>
                                        </ul>
                                    </div>
                                </div>

                            @elseif($fieldName === 'city' && in_array($type, ['select', 'text'], true))
                                <div x-data="{
                                        open: false,
                                        search: '',
                                        province: $wire.entangle('customFields.province'),
                                        city: $wire.entangle('customFields.city'),
                                        provincesData: @js($provincesData),
                                        get cities() {
                                            return (this.province && this.provincesData[this.province]) ? this.provincesData[this.province] : [];
                                        },
                                        get filtered() {
                                            const q = (this.search || '').trim();
                                            return q ? this.cities.filter(c => c.includes(q)) : this.cities;
                                        },
                                        selectCity(c) {
                                            this.city = c;
                                            this.open = false;
                                            this.search = '';
                                        }
                                     }"
                                     @click.outside="open = false"
                                     class="relative font-sans">
                                    <button type="button" @click="province ? (open = !open) : null"
                                            class="{{ $inputClass }} flex items-center justify-between text-right"
                                            :class="province ? 'cursor-pointer' : 'opacity-60 cursor-not-allowed'">
                                        <span x-text="city || (province ? 'انتخاب شهر...' : 'ابتدا استان را انتخاب کنید...')"
                                              :class="city ? 'text-gray-900 dark:text-gray-100 font-medium' : 'text-gray-400 dark:text-gray-500'"
                                              class="truncate"></span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="{'rotate-180 text-indigo-500': open && province}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div x-show="open && province" x-transition style="display: none;"
                                         class="absolute z-50 mt-1.5 w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800">
                                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                            <input type="text" x-model="search" placeholder="جستجوی شهر..." @click.stop
                                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                        </div>
                                        <ul class="max-h-56 overflow-y-auto p-1.5 space-y-0.5">
                                            <template x-for="c in filtered" :key="c">
                                                <li @click="selectCity(c)"
                                                    class="cursor-pointer select-none py-2 px-3 text-xs rounded-xl transition-colors flex items-center justify-between"
                                                    :class="city === c ? 'bg-indigo-50 text-indigo-700 font-bold dark:bg-indigo-500/15 dark:text-indigo-300' : 'text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-gray-700/60'">
                                                    <span x-text="c"></span>
                                                    <svg x-show="city === c" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </li>
                                            </template>
                                            <li x-show="filtered.length === 0" class="py-3 px-3 text-xs text-gray-500 dark:text-slate-400 text-center">شهری یافت نشد</li>
                                        </ul>
                                    </div>
                                </div>

                            @elseif($type === 'file')
                                <div class="relative">
                                    <div wire:loading.flex wire:target="customFiles.{{ $fieldName }}"
                                         class="absolute inset-0 z-20 bg-white/90 dark:bg-gray-900/90 rounded-xl flex items-center justify-center gap-2 border-2 border-indigo-500">
                                        <span class="w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></span>
                                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-300">در حال بارگذاری...</span>
                                    </div>
                                    <label for="{{ $inputId }}"
                                           class="flex items-center justify-between gap-4 p-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50 hover:border-indigo-500 cursor-pointer transition">
                                        @if($uploadedFile)
                                            <div class="flex items-center gap-3">
                                                @if(method_exists($uploadedFile, 'temporaryUrl') && str_starts_with((string) $uploadedFile->getMimeType(), 'image/'))
                                                    <img src="{{ $uploadedFile->temporaryUrl() }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700" alt="Preview">
                                                @endif
                                                <div class="space-y-1">
                                                    <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $uploadedFile->getClientOriginalName() }}</div>
                                                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400">فایل جدید آماده ذخیره است</div>
                                                </div>
                                            </div>
                                        @elseif($existingPath)
                                            <div class="flex items-center gap-3">
                                                <span class="w-10 h-10 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">✓</span>
                                                <div class="space-y-1">
                                                    <div class="text-xs font-bold text-emerald-700 dark:text-emerald-300">فایل از قبل بارگذاری شده است.</div>
                                                    <div class="text-[11px] text-slate-600 dark:text-slate-300">برای جایگزینی کلیک کنید</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-xs text-slate-600 dark:text-slate-300 flex flex-col gap-2">
                                                <span class="block font-bold text-indigo-600 dark:text-indigo-400">برای انتخاب فایل کلیک کنید</span>
                                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">JPG، PNG، WEBP یا PDF (حداکثر ۵ مگابایت)</span>
                                            </div>
                                        @endif
                                    </label>
                                    <input id="{{ $inputId }}" type="file" wire:model="customFiles.{{ $fieldName }}" class="hidden" accept="image/*,.pdf">
                                </div>

                            @elseif($type === 'textarea')
                                <textarea id="{{ $inputId }}" wire:model="customFields.{{ $fieldName }}" rows="3" class="{{ $inputClass }} resize-none leading-relaxed"></textarea>

                            @elseif($type === 'select' && !empty($opts))
                                <select id="{{ $inputId }}" wire:model="customFields.{{ $fieldName }}" class="{{ $inputClass }}">
                                    <option value="">انتخاب کنید...</option>
                                    @foreach($opts as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>

                            @elseif($type === 'radio' && !empty($opts))
                                <div class="flex flex-wrap gap-4 pt-1">
                                    @foreach($opts as $opt)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" wire:model="customFields.{{ $fieldName }}" value="{{ $opt }}" class="text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-700 dark:text-slate-200">{{ $opt }}</span>
                                        </label>
                                    @endforeach
                                </div>

                            @elseif($type === 'checkbox' && !empty($opts))
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    @foreach($opts as $opt)
                                        <label class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer">
                                            <input type="checkbox" wire:model="customFields.{{ $fieldName }}" value="{{ $opt }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-700 dark:text-slate-200">{{ $opt }}</span>
                                        </label>
                                    @endforeach
                                </div>

                            @else
                                <input id="{{ $inputId }}" type="{{ in_array($type, ['number','email','date','tel','url'], true) ? $type : 'text' }}"
                                       wire:model="customFields.{{ $fieldName }}"
                                       class="{{ $inputClass }} {{ $isLtr ? 'dir-ltr text-left' : '' }}" />
                            @endif

                            @error("customFields.{$fieldName}") <span class="text-xs text-rose-500 font-bold mt-1.5 block">{{ $message }}</span> @enderror
                            @error("customFiles.{$fieldName}") <span class="text-xs text-rose-500 font-bold mt-1.5 block">{{ $message }}</span> @enderror
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Specialties / Categories (Section -> Group -> Service) --}}
            @if($categories->isNotEmpty())
                @php
                    $selectedIntIds = array_map('intval', $selectedCategories);
                    $categoriesById = $categories->keyBy('id');
                    $searchTerm = trim($specialtySearch);
                @endphp
                <div class="{{ $isInitial && $profileFields->isNotEmpty() ? 'pt-6 mt-2 border-t border-gray-200 dark:border-gray-700' : '' }} space-y-5">
                    {{-- Header & Selected Counter --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex flex-col gap-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-slate-200">
                                تخصص‌ها و حوزه‌های کاری <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-xs text-slate-600 dark:text-slate-300">
                                ابتدا بخش را انتخاب کنید، سپس دسته و خدمات مورد نظر را تیک بزنید.
                            </p>
                        </div>
                        <div class="self-start sm:self-auto">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/30 text-xs font-bold text-indigo-700 dark:text-indigo-300">
                                {{ number_format(count($selectedIntIds)) }} تخصص انتخاب‌شده
                            </span>
                        </div>
                    </div>

                    {{-- Selected Specialties Summary --}}
                    @if(!empty($selectedIntIds))
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    موارد انتخاب‌شده شما:
                                </span>
                                <button type="button" wire:click="clearCategories"
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-300 text-xs font-bold transition no-underline">
                                    پاک کردن همه
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedIntIds as $selId)
                                    @php $selCat = $categoriesById->get($selId); @endphp
                                    @if($selCat)
                                        @php
                                            $secName = $selCat->group?->section?->name ?? $selCat->section?->name;
                                            $grpName = $selCat->group?->name;
                                        @endphp
                                        <span wire:key="selected-chip-{{ $selCat->id }}"
                                              class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-medium text-slate-800 dark:text-slate-200">
                                            <span>
                                                @if($secName)
                                                    <span class="text-slate-500 dark:text-slate-400">{{ $secName }}</span>
                                                    <span class="text-slate-400 mx-1">›</span>
                                                @endif
                                                @if($grpName)
                                                    <span class="text-indigo-600 dark:text-indigo-300">{{ $grpName }}</span>
                                                    <span class="text-slate-400 mx-1">›</span>
                                                @endif
                                                <span class="font-bold">{{ $selCat->name }}</span>
                                            </span>
                                            <button type="button" wire:click="removeCategory({{ $selCat->id }})"
                                                    class="w-5 h-5 rounded-md bg-gray-100 dark:bg-gray-700 text-slate-500 dark:text-slate-300 hover:bg-rose-500 hover:text-white flex items-center justify-center text-[11px] transition no-underline"
                                                    title="حذف">
                                                ✕
                                            </button>
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Section Tabs --}}
                    @if($sections->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-3">
                            @foreach($sections as $sec)
                                @php
                                    $secServiceIds = $sec->services->pluck('id')
                                        ->merge($sec->groups->flatMap(fn ($g) => $g->services->pluck('id')))
                                        ->map(fn ($id) => (int) $id)
                                        ->unique()
                                        ->toArray();
                                    $secSelectedCount = count(array_intersect($selectedIntIds, $secServiceIds));
                                    $isTabActive = $activeSectionTab === (string) $sec->id;
                                @endphp
                                @if(!empty($secServiceIds))
                                    <button type="button" wire:key="sec-tab-{{ $sec->id }}" wire:click="selectSectionTab('{{ $sec->id }}')"
                                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold border transition no-underline {{ $isTabActive ? 'bg-indigo-600 text-white border-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 dark:border-indigo-500/40' : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/60' }}">
                                        <span>{{ $sec->name }}</span>
                                        @if($secSelectedCount > 0)
                                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $isTabActive ? 'bg-white/20 text-white dark:bg-indigo-500/30 dark:text-indigo-200' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' }}">
                                                {{ $secSelectedCount }}
                                            </span>
                                        @endif
                                    </button>
                                @endif
                            @endforeach

                            @if($ungroupedCategories->isNotEmpty())
                                @php
                                    $ungroupedIds = $ungroupedCategories->pluck('id')->map(fn ($id) => (int) $id)->toArray();
                                    $ungroupedSelectedCount = count(array_intersect($selectedIntIds, $ungroupedIds));
                                    $isUngroupedActive = $activeSectionTab === 'ungrouped';
                                @endphp
                                <button type="button" wire:key="sec-tab-ungrouped" wire:click="selectSectionTab('ungrouped')"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold border transition no-underline {{ $isUngroupedActive ? 'bg-indigo-600 text-white border-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 dark:border-indigo-500/40' : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/60' }}">
                                    <span>سایر خدمات</span>
                                    @if($ungroupedSelectedCount > 0)
                                        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $isUngroupedActive ? 'bg-white/20 text-white dark:bg-indigo-500/30 dark:text-indigo-200' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' }}">
                                            {{ $ungroupedSelectedCount }}
                                        </span>
                                    @endif
                                </button>
                            @endif
                        </div>
                    @endif

                    {{-- Search within Active Section --}}
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.250ms="specialtySearch"
                               placeholder="جستجوی دسته یا خدمت در این بخش..."
                               class="w-full h-10 pr-10 pl-9 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition font-sans">
                        <svg class="w-4 h-4 text-gray-400 absolute right-3.5 top-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        @if($searchTerm !== '')
                            <button type="button" wire:click="clearSpecialtySearch"
                                    class="absolute left-2.5 top-2 w-6 h-6 rounded-lg bg-gray-200/80 dark:bg-gray-700 text-gray-600 dark:text-slate-300 hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center justify-center text-xs transition no-underline"
                                    title="پاک کردن جستجو">
                                ✕
                            </button>
                        @endif
                    </div>

                    {{-- Groups & Services under Active Section --}}
                    <div class="space-y-4">
                        @php $hasRenderedAny = false; @endphp

                        @foreach($sections as $sec)
                            @if($activeSectionTab === (string) $sec->id)
                                <div wire:key="sec-panel-{{ $sec->id }}" class="space-y-4">
                                    {{-- Groups inside this Section --}}
                                    @foreach($sec->groups as $grp)
                                        @php
                                            $groupMatches = $searchTerm !== '' && mb_stripos($grp->name, $searchTerm) !== false;
                                            $visibleServices = $searchTerm === '' || $groupMatches
                                                ? $grp->services
                                                : $grp->services->filter(fn ($s) => mb_stripos($s->name, $searchTerm) !== false);
                                        @endphp
                                        @if($visibleServices->isNotEmpty())
                                            @php
                                                $hasRenderedAny = true;
                                                $grpIds = $grp->services->pluck('id')->map(fn ($id) => (int) $id)->toArray();
                                                $grpSelCount = count(array_intersect($selectedIntIds, $grpIds));
                                                $grpAllSelected = $grpSelCount === count($grpIds);
                                            @endphp
                                            <div wire:key="grp-box-{{ $grp->id }}" class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 space-y-3">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $grp->name }}</span>
                                                        <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300">
                                                            ({{ $grpSelCount }} از {{ count($grpIds) }})
                                                        </span>
                                                    </div>
                                                    <button type="button" wire:click="toggleGroupCategories({{ $grp->id }})"
                                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition no-underline {{ $grpAllSelected ? 'bg-rose-500/10 text-rose-600 dark:text-rose-300 hover:bg-rose-500/20' : 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-500/20' }}">
                                                        {{ $grpAllSelected ? 'لغو انتخاب همه' : 'انتخاب همه' }}
                                                    </button>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                                    @foreach($visibleServices as $cat)
                                                        @php $isSelected = in_array((int) $cat->id, $selectedIntIds, true); @endphp
                                                        <label wire:key="cat-opt-{{ $cat->id }}" class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $isSelected ? 'bg-indigo-50 border-indigo-400 text-indigo-800 dark:bg-indigo-500/15 dark:border-indigo-500/40 dark:text-indigo-200' : 'bg-white border-gray-200 text-gray-700 dark:bg-gray-800/70 dark:border-gray-700 dark:text-slate-300 hover:border-indigo-400' }}">
                                                            <input type="checkbox" wire:model.live="selectedCategories" value="{{ $cat->id }}" class="rounded text-indigo-600 focus:ring-indigo-500 shrink-0">
                                                            <span class="text-xs font-bold leading-snug">{{ $cat->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    {{-- Direct Services inside this Section (no Group) --}}
                                    @php
                                        $visibleDirectServices = $searchTerm === ''
                                            ? $sec->services
                                            : $sec->services->filter(fn ($s) => mb_stripos($s->name, $searchTerm) !== false);
                                    @endphp
                                    @if($visibleDirectServices->isNotEmpty())
                                        @php
                                            $hasRenderedAny = true;
                                            $directIds = $sec->services->pluck('id')->map(fn ($id) => (int) $id)->toArray();
                                            $directSelCount = count(array_intersect($selectedIntIds, $directIds));
                                            $directAllSelected = $directSelCount === count($directIds);
                                        @endphp
                                        <div wire:key="sec-direct-{{ $sec->id }}" class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 space-y-3">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-gray-900 dark:text-white">
                                                        {{ $sec->groups->isNotEmpty() ? 'سایر خدمات ' . $sec->name . ' (بدون دسته)' : 'خدمات ' . $sec->name }}
                                                    </span>
                                                    <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300">
                                                        ({{ $directSelCount }} از {{ count($directIds) }})
                                                    </span>
                                                </div>
                                                <button type="button" wire:click="toggleSectionDirectCategories({{ $sec->id }})"
                                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition no-underline {{ $directAllSelected ? 'bg-rose-500/10 text-rose-600 dark:text-rose-300 hover:bg-rose-500/20' : 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-500/20' }}">
                                                    {{ $directAllSelected ? 'لغو انتخاب همه' : 'انتخاب همه' }}
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                                @foreach($visibleDirectServices as $cat)
                                                    @php $isSelected = in_array((int) $cat->id, $selectedIntIds, true); @endphp
                                                    <label wire:key="cat-opt-{{ $cat->id }}" class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $isSelected ? 'bg-indigo-50 border-indigo-400 text-indigo-800 dark:bg-indigo-500/15 dark:border-indigo-500/40 dark:text-indigo-200' : 'bg-white border-gray-200 text-gray-700 dark:bg-gray-800/70 dark:border-gray-700 dark:text-slate-300 hover:border-indigo-400' }}">
                                                        <input type="checkbox" wire:model.live="selectedCategories" value="{{ $cat->id }}" class="rounded text-indigo-600 focus:ring-indigo-500 shrink-0">
                                                        <span class="text-xs font-bold leading-snug">{{ $cat->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        {{-- Ungrouped Services (no Section and no Group) --}}
                        @if($ungroupedCategories->isNotEmpty() && ($activeSectionTab === 'ungrouped' || $sections->isEmpty()))
                            @php
                                $visibleUngrouped = $searchTerm === ''
                                    ? $ungroupedCategories
                                    : $ungroupedCategories->filter(fn ($s) => mb_stripos($s->name, $searchTerm) !== false);
                            @endphp
                            @if($visibleUngrouped->isNotEmpty())
                                @php $hasRenderedAny = true; @endphp
                                <div wire:key="sec-panel-ungrouped" class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 space-y-3">
                                    <div class="text-xs font-bold text-gray-900 dark:text-white">سایر خدمات فنی</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                        @foreach($visibleUngrouped as $cat)
                                            @php $isSelected = in_array((int) $cat->id, $selectedIntIds, true); @endphp
                                            <label wire:key="cat-opt-{{ $cat->id }}" class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $isSelected ? 'bg-indigo-50 border-indigo-400 text-indigo-800 dark:bg-indigo-500/15 dark:border-indigo-500/40 dark:text-indigo-200' : 'bg-white border-gray-200 text-gray-700 dark:bg-gray-800/70 dark:border-gray-700 dark:text-slate-300 hover:border-indigo-400' }}">
                                                <input type="checkbox" wire:model.live="selectedCategories" value="{{ $cat->id }}" class="rounded text-indigo-600 focus:ring-indigo-500 shrink-0">
                                                <span class="text-xs font-bold leading-snug">{{ $cat->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if(!$hasRenderedAny && $searchTerm !== '')
                            <div class="p-6 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 text-center space-y-2">
                                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                    موردی با عبارت «{{ $searchTerm }}» در این بخش یافت نشد.
                                </p>
                                <button type="button" wire:click="clearSpecialtySearch"
                                        class="px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 text-xs font-bold transition no-underline">
                                    پاک کردن جستجو
                                </button>
                            </div>
                        @endif
                    </div>

                    @error('selectedCategories') <span class="text-xs text-rose-500 font-bold mt-2 block">{{ $message }}</span> @enderror
                </div>
            @endif
        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-end">
            <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 transition active:scale-95 disabled:opacity-70">
                <span wire:loading.remove wire:target="saveVerification">{{ $isInitial ? 'ثبت و ارسال درخواست احراز هویت' : 'ذخیره تخصص‌ها و حوزه‌های کاری' }}</span>
                <span wire:loading wire:target="saveVerification">در حال ذخیره...</span>
            </button>
        </div>
        @endif
    </form>
</div>
