@props([
    'formSchema' => null,
    'formType' => null,
    'formName' => null,
    'formResponses' => [],
    'modelPrefix' => 'formResponses',
    'wire' => true,
])

@if ($formSchema && !empty($formSchema['fields']))
    <style>
        .tooth-path {
            cursor: pointer;
            transition: fill .14s ease, stroke .14s ease, filter .14s ease;
            stroke-width: 1.5px;
            vector-effect: non-scaling-stroke;
        }
        .tooth-selected {
            fill: #3b82f6 !important;
            stroke: #2563eb !important;
            stroke-width: 2.5px !important;
            filter: drop-shadow(0 2px 6px rgba(37, 99, 235, 0.45));
        }
        .dark .tooth-selected {
            fill: #1d4ed8 !important;
            stroke: #3b82f6 !important;
        }
        .tooth-unselected {
            fill: #ffffff !important;
            stroke: #cbd5e1;
        }
        .dark .tooth-unselected {
            fill: #334155 !important;
            stroke: #475569;
        }
        .tooth-unselected:hover {
            fill: #f8fafc !important;
            stroke: #3b82f6;
        }
        .dark .tooth-unselected:hover {
            fill: #1e293b !important;
            stroke: #60a5fa;
        }
    </style>

    <div class="p-5 sm:p-6 rounded-3xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/60 space-y-5 shadow-2xs">
        <div class="flex items-center justify-between border-b border-indigo-100 dark:border-indigo-800/40 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-black text-gray-900 dark:text-gray-100">{{ $formName ?: 'فرم اطلاعات اختصاصی سرویس' }}</h4>
                    <span class="text-xs text-gray-500 dark:text-gray-400">اطلاعات تکمیلی این خدمت برای استفاده در پرونده و نوبت</span>
                </div>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold">
                {{ count($formSchema['fields']) }} فیلد
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($formSchema['fields'] as $field)
                @php
                    $fName = $field['name'] ?? '';
                    $fLabel = $field['label'] ?? $fName;
                    $fType = $field['type'] ?? 'text';
                    $fPlaceholder = $field['placeholder'] ?? '';
                    $fRequired = !empty($field['required']);
                    $fOptions = $field['options'] ?? [];
                    $userOptions = $field['user_options'] ?? [];
                    $wireModel = "{$modelPrefix}.{$fName}";
                    $val = $formResponses[$fName] ?? null;
                    $isFullWidth = in_array($fType, ['textarea', 'tooth_number', 'checkbox', 'radio']) || count($formSchema['fields']) === 1;
                @endphp

                <div class="space-y-1.5 {{ $isFullWidth ? 'sm:col-span-2' : '' }}" wire:key="field-{{ $fName }}">
                    <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200">
                        <span>{{ $fLabel }}</span>
                        @if($fRequired)
                            <span class="text-rose-500 font-black mr-0.5">*</span>
                        @endif
                    </label>

                    {{-- Textarea --}}
                    @if($fType === 'textarea')
                        <textarea wire:model.defer="{{ $wireModel }}"
                                  placeholder="{{ $fPlaceholder }}"
                                  rows="2"
                                  class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition shadow-2xs"></textarea>

                    {{-- Select --}}
                    @elseif($fType === 'select')
                        <select wire:model.defer="{{ $wireModel }}"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition shadow-2xs">
                            <option value="">{{ $fPlaceholder ?: 'انتخاب کنید...' }}</option>
                            @foreach($fOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>

                    {{-- Radio --}}
                    @elseif($fType === 'radio')
                        <div class="flex flex-wrap gap-3.5 pt-1">
                            @foreach($fOptions as $opt)
                                <label class="inline-flex items-center gap-2 text-xs sm:text-sm text-gray-800 dark:text-gray-200 cursor-pointer font-medium">
                                    <input type="radio" value="{{ $opt }}" wire:model.defer="{{ $wireModel }}"
                                           class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800">
                                    <span>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>

                    {{-- Checkbox --}}
                    @elseif($fType === 'checkbox')
                        <div class="flex flex-wrap gap-3.5 pt-1">
                            @foreach($fOptions as $opt)
                                <label class="inline-flex items-center gap-2 text-xs sm:text-sm text-gray-800 dark:text-gray-200 cursor-pointer font-medium">
                                    <input type="checkbox" value="{{ $opt }}" wire:model.defer="{{ $wireModel }}"
                                           class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800">
                                    <span>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>

                    {{-- Select User by Role --}}
                    @elseif($fType === 'select-user-by-role')
                        <select wire:model.defer="{{ $wireModel }}"
                                @if(!empty($field['multiple'])) multiple @endif
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition shadow-2xs">
                            @if(empty($field['multiple']))
                                <option value="">{{ $fPlaceholder ?: 'انتخاب کنید...' }}</option>
                            @endif
                            @foreach($userOptions as $u)
                                <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                            @endforeach
                        </select>

                    {{-- Tooth Chart (Dental) --}}
                    @elseif($fType === 'tooth_number')
                        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mx-auto"
                             x-data="{
                                teeth: @entangle($wireModel).live,
                                preset: 'none',
                                upperJawIds: [1,2,3,4,5,6,7,8,9,10,11,12,13,14],
                                lowerJawIds: [15,16,17,18,19,20,21,22,23,24,25,26,27,28],

                                selectJaw(type) {
                                    if (this.preset === type) { this.resetTeeth(); return; }
                                    this.preset = type;
                                    this.teeth = type === 'upper' ? [...this.upperJawIds] : [...this.lowerJawIds];
                                },

                                selectAllTeeth() {
                                    if (this.preset === 'all') { this.resetTeeth(); return; }
                                    this.preset = 'all';
                                    this.teeth = [...this.upperJawIds, ...this.lowerJawIds];
                                },

                                resetTeeth() {
                                    this.teeth = [];
                                    this.preset = 'none';
                                },

                                getToothLabel(id) {
                                    const mapping = {
                                        1:  { num: 7, pos: 'UR' }, 2:  { num: 6, pos: 'UR' }, 3:  { num: 5, pos: 'UR' }, 4:  { num: 4, pos: 'UR' },
                                        5:  { num: 3, pos: 'UR' }, 6:  { num: 2, pos: 'UR' }, 7:  { num: 1, pos: 'UR' },
                                        8:  { num: 1, pos: 'UL' }, 9:  { num: 2, pos: 'UL' }, 10: { num: 3, pos: 'UL' }, 11: { num: 4, pos: 'UL' },
                                        12: { num: 5, pos: 'UL' }, 13: { num: 6, pos: 'UL' }, 14: { num: 7, pos: 'UL' },
                                        15: { num: 7, pos: 'LR' }, 16: { num: 6, pos: 'LR' }, 17: { num: 5, pos: 'LR' }, 18: { num: 4, pos: 'LR' },
                                        19: { num: 3, pos: 'LR' }, 20: { num: 2, pos: 'LR' }, 21: { num: 1, pos: 'LR' },
                                        22: { num: 1, pos: 'LL' }, 23: { num: 2, pos: 'LL' }, 24: { num: 3, pos: 'LL' }, 25: { num: 4, pos: 'LL' },
                                        26: { num: 5, pos: 'LL' }, 27: { num: 6, pos: 'LL' }, 28: { num: 7, pos: 'LL' }
                                    };
                                    return mapping[id] ?? { num: id, pos: 'UR' };
                                },

                                getQuadrantClasses(id) {
                                    const tooth = this.getToothLabel(id);
                                    switch(tooth.pos) {
                                        case 'UR': return '!border-r-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                                        case 'UL': return '!border-l-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                                        case 'LR': return '!border-r-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                                        case 'LL': return '!border-l-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                                        default:   return '';
                                    }
                                },

                                getQuadrantTeeth(teethArray, pos) {
                                    return (teethArray || []).map(Number).filter(t => this.getToothLabel(t).pos === pos).sort((a,b) => a - b);
                                },

                                toggle(id) {
                                    id = Number(id);
                                    if (!Array.isArray(this.teeth)) this.teeth = [];
                                    const idx = this.teeth.map(Number).indexOf(id);
                                    if (idx > -1) {
                                        this.teeth.splice(idx, 1);
                                    } else {
                                        this.teeth.push(id);
                                    }
                                    this.preset = 'none';
                                },

                                is(id) {
                                    id = Number(id);
                                    return (Array.isArray(this.teeth) && this.teeth.map(Number).includes(id)) ? 'tooth-path tooth-selected' : 'tooth-path tooth-unselected';
                                }
                             }">
                            {{-- Header Actions --}}
                            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex-wrap gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-6 rounded-full bg-rose-500 shrink-0"></span>
                                    <h2 class="font-black text-gray-900 dark:text-gray-100 text-sm sm:text-base">نقشه دندانی</h2>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <button type="button" @click="selectJaw('upper')"
                                            :class="preset==='upper' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200'"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">فک بالا</button>
                                    <button type="button" @click="selectJaw('lower')"
                                            :class="preset==='lower' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200'"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">فک پایین</button>
                                    <button type="button" @click="selectAllTeeth()"
                                            :class="preset==='all' ? 'bg-violet-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200'"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">همه</button>
                                    <button type="button" @click="resetTeeth()"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 transition-all cursor-pointer">
                                        پاک‌سازی
                                    </button>
                                </div>
                            </div>

                            {{-- Chart Container with floating counter --}}
                            <div class="px-5 pt-5 pb-2 relative">
                                <div class="absolute top-6 left-6 z-10 bg-white/95 dark:bg-gray-800/95 backdrop-blur px-3.5 py-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                                    <span class="text-[10px] text-gray-400 uppercase font-black block">انتخاب</span>
                                    <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400"
                                          x-text="Array.isArray(teeth) ? teeth.length : 0"></span>
                                </div>
                                <div class="max-w-2xl mx-auto">
                                    <x-booking::dental-chart />
                                </div>
                            </div>

                            {{-- Selected Teeth Quadrant Display Panel --}}
                            <div class="px-5 sm:px-6 py-4 flex items-center gap-3 min-h-16 border-t border-gray-150 dark:border-gray-700/50 bg-gray-50/70 dark:bg-gray-900/30">
                                <template x-if="Array.isArray(teeth) && teeth.length > 0">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-bold shrink-0">دندان‌های انتخابی:</span>
                                        <div class="inline-grid grid-cols-2 select-none">
                                            <!-- Row 1: UR | UL -->
                                            <!-- UR -->
                                            <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-700 pb-1.5 pl-2.5 flex items-center justify-end gap-1.5 min-w-[40px] min-h-[40px]">
                                                <template x-for="t in getQuadrantTeeth(teeth, 'UR')" :key="t">
                                                    <div role="button" @click="toggle(t)"
                                                         class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer hover:bg-rose-50 hover:text-rose-600"
                                                         :class="[getQuadrantClasses(t)]"
                                                         x-text="getToothLabel(t).num">
                                                    </div>
                                                </template>
                                            </div>
                                            <!-- UL -->
                                            <div class="border-b-2 border-slate-300 dark:border-slate-700 pb-1.5 pr-2.5 flex items-center justify-start gap-1.5 min-w-[40px] min-h-[40px]">
                                                <template x-for="t in getQuadrantTeeth(teeth, 'UL')" :key="t">
                                                    <div role="button" @click="toggle(t)"
                                                         class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer hover:bg-rose-50 hover:text-rose-600"
                                                         :class="[getQuadrantClasses(t)]"
                                                         x-text="getToothLabel(t).num">
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Row 2: LR | LL -->
                                            <!-- LR -->
                                            <div class="border-l-2 border-slate-300 dark:border-slate-700 pt-1.5 pl-2.5 flex items-center justify-end gap-1.5 min-w-[40px] min-h-[40px]">
                                                <template x-for="t in getQuadrantTeeth(teeth, 'LR')" :key="t">
                                                    <div role="button" @click="toggle(t)"
                                                         class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer hover:bg-rose-50 hover:text-rose-600"
                                                         :class="[getQuadrantClasses(t)]"
                                                         x-text="getToothLabel(t).num">
                                                    </div>
                                                </template>
                                            </div>
                                            <!-- LL -->
                                            <div class="pt-1.5 pr-2.5 flex items-center justify-start gap-1.5 min-w-[40px] min-h-[40px]">
                                                <template x-for="t in getQuadrantTeeth(teeth, 'LL')" :key="t">
                                                    <div role="button" @click="toggle(t)"
                                                         class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer hover:bg-rose-50 hover:text-rose-600"
                                                         :class="[getQuadrantClasses(t)]"
                                                         x-text="getToothLabel(t).num">
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!Array.isArray(teeth) || teeth.length === 0">
                                    <span class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 self-center">
                                        روی دندان‌ها در نقشه کلیک کنید تا انتخاب شوند
                                    </span>
                                </template>
                            </div>
                        </div>

                    {{-- Default inputs (text, number, date, etc.) --}}
                    @else
                        <input type="{{ in_array($fType, ['number', 'email', 'date']) ? $fType : 'text' }}"
                               wire:model.defer="{{ $wireModel }}"
                               placeholder="{{ $fPlaceholder }}"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition shadow-2xs">
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
