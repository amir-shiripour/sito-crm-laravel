@php
    $fieldName = $field->field_name;
    $fieldType = strtolower($field->field_type ?? 'text');
    $isRequired = (bool) $field->is_required;
    $options = \Modules\FieldService\App\Support\TechnicianCustomFieldsHelper::extractOptions($field);
    $isFullWidth = in_array($fieldType, ['textarea', 'file'], true)
        || ($fieldType === 'checkbox' && !empty($options))
        || ($fieldType === 'radio' && !empty($options));
    $isLtr = in_array($fieldType, ['number', 'email', 'date', 'password'], true)
        || in_array($fieldName, ['national_code', 'shaba_number'], true);
    $uploadedFile = $customFiles[$fieldName] ?? null;
    $existingPath = $existingFiles[$fieldName] ?? null;
    $inputId = ($idPrefix ?? 'cf') . '_' . $fieldName;
@endphp

<div class="{{ $isFullWidth ? 'sm:col-span-2' : 'sm:col-span-1' }}">
    @if(!($fieldType === 'checkbox' && empty($options)))
        <label for="{{ $inputId }}" class="block text-xs font-bold text-slate-300 mb-1.5">
            {{ $field->label }}
            @if($isRequired)
                <span class="text-rose-400">*</span>
            @endif
        </label>
    @endif

    {{-- 1. Special Case: Searchable Province Selector --}}
    @if($fieldName === 'province' && in_array($fieldType, ['select', 'text'], true))
        <div x-data="{
                open: false,
                search: '',
                get provinces() {
                    const list = Object.keys(allData || {});
                    if (!this.search.trim()) return list;
                    return list.filter(p => p.includes(this.search.trim()));
                },
                select(prov) {
                    $wire.set('customFields.province', prov);
                    this.open = false;
                    this.search = '';
                }
             }"
             @click.outside="open = false"
             class="relative">
            <button type="button" @click="open = !open"
                    class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white flex items-center justify-between hover:border-indigo-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                <span x-text="selectedProvince || 'انتخاب استان...'" class="truncate font-sans font-medium"></span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-indigo-400' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="absolute z-50 mt-1.5 w-full rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl shadow-black/80 overflow-hidden">
                <div class="p-2 border-b border-slate-800 bg-slate-950/70">
                    <div class="relative">
                        <input type="text" x-model="search" placeholder="جستجوی استان..."
                               @keydown.escape="open = false"
                               class="w-full h-9 pr-8 pl-3 rounded-lg bg-slate-900 border border-slate-800 text-xs text-white placeholder:text-slate-500 outline-none focus:border-indigo-500">
                        <svg class="w-4 h-4 text-slate-500 absolute right-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <div class="max-h-48 overflow-y-auto py-1">
                    <template x-for="p in provinces" :key="p">
                        <button type="button" @click="select(p)"
                                class="w-full px-3.5 py-2 text-right text-xs hover:bg-slate-800 transition flex items-center justify-between"
                                :class="selectedProvince === p ? 'text-indigo-400 font-bold bg-indigo-950/30' : 'text-slate-200'">
                            <span x-text="p"></span>
                            <span x-show="selectedProvince === p" class="text-indigo-400 text-xs">✓</span>
                        </button>
                    </template>
                    <div x-show="provinces.length === 0" class="p-3 text-center text-xs text-slate-400">
                        استانی یافت نشد.
                    </div>
                </div>
            </div>
        </div>

    {{-- 2. Special Case: Searchable City Selector --}}
    @elseif($fieldName === 'city' && in_array($fieldType, ['select', 'text'], true))
        <div x-data="{
                open: false,
                search: '',
                get cities() {
                    const list = (allData && allData[selectedProvince]) ? allData[selectedProvince] : [];
                    if (!this.search.trim()) return list;
                    return list.filter(c => c.includes(this.search.trim()));
                },
                select(city) {
                    $wire.set('customFields.city', city);
                    this.open = false;
                    this.search = '';
                }
             }"
             @click.outside="open = false"
             class="relative">
            <button type="button" @click="open = !open"
                    class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white flex items-center justify-between hover:border-indigo-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                <span x-text="selectedCity || 'انتخاب شهر...'" class="truncate font-sans font-medium"></span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-indigo-400' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="absolute z-50 mt-1.5 w-full rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl shadow-black/80 overflow-hidden">
                <div class="p-2 border-b border-slate-800 bg-slate-950/70">
                    <div class="relative">
                        <input type="text" x-model="search" placeholder="جستجوی شهر..."
                               @keydown.escape="open = false"
                               class="w-full h-9 pr-8 pl-3 rounded-lg bg-slate-900 border border-slate-800 text-xs text-white placeholder:text-slate-500 outline-none focus:border-indigo-500">
                        <svg class="w-4 h-4 text-slate-500 absolute right-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <div class="max-h-48 overflow-y-auto py-1">
                    <template x-for="c in cities" :key="c">
                        <button type="button" @click="select(c)"
                                class="w-full px-3.5 py-2 text-right text-xs hover:bg-slate-800 transition flex items-center justify-between"
                                :class="selectedCity === c ? 'text-indigo-400 font-bold bg-indigo-950/30' : 'text-slate-200'">
                            <span x-text="c"></span>
                            <span x-show="selectedCity === c" class="text-indigo-400 text-xs">✓</span>
                        </button>
                    </template>
                    <div x-show="cities.length === 0" class="p-3 text-center text-xs text-slate-400">
                        شهری یافت نشد.
                    </div>
                </div>
            </div>
        </div>

    {{-- 3. Special Case: SHABA Number with IR prefix --}}
    @elseif($fieldName === 'shaba_number')
        <div class="flex items-center rounded-xl bg-slate-950 border border-slate-700/90 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition overflow-hidden" dir="ltr">
            <span class="px-2.5 py-2.5 bg-slate-900 border-r border-slate-800 text-xs font-bold text-slate-300 select-none font-sans">IR</span>
            <input id="{{ $inputId }}" type="text" wire:model="customFields.{{ $fieldName }}" placeholder="120170000000..." maxlength="26"
                   class="flex-1 h-11 px-3 bg-transparent text-xs text-white font-sans outline-none text-left placeholder:text-slate-600">
        </div>

    {{-- 4. Textarea --}}
    @elseif($fieldType === 'textarea')
        <textarea id="{{ $inputId }}" wire:model="customFields.{{ $fieldName }}" rows="2"
                  placeholder="{{ $field->label }}..."
                  class="w-full p-3 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white font-sans focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition resize-none leading-relaxed"></textarea>

    {{-- 5. File Upload --}}
    @elseif($fieldType === 'file')
        <div class="relative w-full rounded-2xl overflow-hidden">
            <div wire:loading.flex wire:target="customFiles.{{ $fieldName }}"
                 class="absolute inset-0 z-30 bg-slate-950/95 backdrop-blur-sm flex-col items-center justify-center gap-2 border-2 border-indigo-500 rounded-2xl">
                <svg class="animate-spin h-6 w-6 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-xs font-bold text-indigo-300 font-sans">در حال بارگذاری فایل...</span>
            </div>

            <label for="{{ $inputId }}"
                   class="relative flex flex-col items-center justify-center w-full min-h-[88px] py-3 px-4 transition-all bg-slate-950/80 border-2 border-slate-700/80 border-dashed rounded-2xl cursor-pointer hover:border-indigo-500 hover:bg-slate-950 group">
                @if($uploadedFile)
                    <div class="flex items-center gap-3 w-full">
                        <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-700 shrink-0 bg-slate-900 flex items-center justify-center text-indigo-400">
                            @if(method_exists($uploadedFile, 'temporaryUrl') && str_starts_with((string) $uploadedFile->getMimeType(), 'image/'))
                                <img src="{{ $uploadedFile->temporaryUrl() }}" class="object-cover w-full h-full" alt="Preview">
                            @else
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1 text-right">
                            <div class="text-xs font-bold text-white truncate">{{ $uploadedFile->getClientOriginalName() }}</div>
                            <div class="text-[10px] text-emerald-400 font-medium mt-0.5">فایل جدید آماده ذخیره است</div>
                        </div>
                    </div>
                @elseif($existingPath)
                    <div class="flex items-center gap-3 w-full">
                        <div class="w-11 h-11 rounded-xl border border-emerald-500/30 bg-emerald-500/10 flex items-center justify-center text-emerald-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div class="min-w-0 flex-1 text-right">
                            <div class="text-xs font-bold text-emerald-300">فایل قبلاً بارگذاری شده است</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">برای جایگزینی، کلیک و فایل جدید انتخاب کنید</div>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center space-y-1 text-center">
                        <div class="p-2 bg-indigo-900/30 rounded-xl text-indigo-400 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <span class="font-bold text-xs text-indigo-400">برای بارگذاری کلیک کنید</span>
                        <span class="text-[10px] text-slate-400">JPG، PNG، WEBP یا PDF حداکثر ۵ مگابایت</span>
                    </div>
                @endif
            </label>

            <input id="{{ $inputId }}" type="file" wire:model="customFiles.{{ $fieldName }}" class="hidden" accept="image/*,.pdf">

            @if($uploadedFile)
                <button type="button" wire:click="$set('customFiles.{{ $fieldName }}', null)"
                        class="absolute top-2 left-2 p-1.5 bg-rose-900/40 text-rose-300 hover:bg-rose-900/70 rounded-full transition-colors z-20 shadow-sm border border-rose-800" title="حذف فایل">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

    {{-- 6. Select with options (or text fallback) --}}
    @elseif($fieldType === 'select' && !empty($options))
        <select id="{{ $inputId }}" wire:model="customFields.{{ $fieldName }}"
                class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white font-sans focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
            <option value="">انتخاب کنید...</option>
            @foreach($options as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>

    {{-- 7. Radio with options --}}
    @elseif($fieldType === 'radio' && !empty($options))
        <div class="flex flex-wrap gap-3 pt-1">
            @foreach($options as $opt)
                <label class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-indigo-500/60 transition">
                    <input type="radio" wire:model="customFields.{{ $fieldName }}" value="{{ $opt }}"
                           class="w-4 h-4 text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
                    <span class="text-xs font-medium text-slate-200">{{ $opt }}</span>
                </label>
            @endforeach
        </div>

    {{-- 8. Checkbox (Multi-options or single boolean toggle) --}}
    @elseif($fieldType === 'checkbox' && !empty($options))
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
            @foreach($options as $opt)
                <label class="flex items-center gap-2.5 cursor-pointer p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-indigo-500/60 transition">
                    <input type="checkbox" wire:model="customFields.{{ $fieldName }}" value="{{ $opt }}"
                           class="w-4 h-4 rounded text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
                    <span class="text-xs font-medium text-slate-200">{{ $opt }}</span>
                </label>
            @endforeach
        </div>
    @elseif($fieldType === 'checkbox')
        <label for="{{ $inputId }}" class="flex items-center gap-3 cursor-pointer p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-indigo-500/60 transition">
            <input id="{{ $inputId }}" type="checkbox" wire:model="customFields.{{ $fieldName }}"
                   class="w-4 h-4 rounded text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
            <span class="text-xs font-bold text-slate-200">
                {{ $field->label }}
                @if($isRequired)<span class="text-rose-400 mr-1">*</span>@endif
            </span>
        </label>

    {{-- 9. Standard HTML Input (text, number, email, password, date) --}}
    @else
        @php
            $htmlType = in_array($fieldType, ['text', 'number', 'email', 'password', 'date'], true) ? $fieldType : 'text';
        @endphp
        <input id="{{ $inputId }}"
               type="{{ $htmlType }}"
               wire:model="customFields.{{ $fieldName }}"
               dir="{{ $isLtr ? 'ltr' : 'rtl' }}"
               @if($fieldName === 'national_code') maxlength="10" placeholder="2050000000" @else placeholder="{{ $field->label }}" @endif
               class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white font-sans focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition {{ $isLtr ? 'text-left' : 'text-right' }}">
    @endif

    @error("customFields.{$fieldName}")
        <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span>
    @enderror
    @error("customFiles.{$fieldName}")
        <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span>
    @enderror
</div>
