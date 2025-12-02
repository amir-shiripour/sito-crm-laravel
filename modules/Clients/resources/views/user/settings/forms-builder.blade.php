{{-- clients::user.settings.forms-builder --}}
@php
    $title = 'فرم‌ساز ' . config('clients.labels.singular');
    // استایل پایه اینپوت‌ها
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1";
    $checkboxClass = "w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer";
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- هدر صفحه --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">فرم‌ساز هوشمند</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                مدیریت فیلدهای سفارشی و ساختار فرم‌های {{ config('clients.labels.singular') }}
            </p>
        </div>
        <button wire:click="saveForm"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 transition-all active:scale-95">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            ذخیره تغییرات
        </button>
    </div>

    <div class="grid grid-cols-12 gap-6 items-start">

        {{-- ستون کناری: لیست فرم‌ها --}}
        <div class="col-span-12 lg:col-span-3 space-y-4 sticky top-20">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="p-4 bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        لیست فرم‌ها
                    </h2>

                    {{-- فرم جدید --}}
                    <button
                        type="button"
                        wire:click="newForm"
                        class="text-xs px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                        + فرم جدید
                    </button>
                </div>

                <ul class="p-2 space-y-1">
                    @foreach($forms as $f)
                        <li class="flex items-center gap-1">
                            <button wire:click="loadForm({{ $f->id }})"
                                    class="group flex-1 flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all duration-200
                                           {{ $activeFormId === $f->id
                                              ? 'bg-indigo-50 text-indigo-700 font-medium dark:bg-indigo-900/20 dark:text-indigo-300'
                                              : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700/50' }}">
                                <div class="flex flex-col items-start">
                                    <span>{{ $f->name }}</span>
                                    <span class="text-[10px] text-gray-400 dir-ltr">
                                        key: {{ $f->key }}
                                    </span>
                                </div>
                                @if($f->is_active)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        فعال
                                    </span>
                                @endif
                            </button>

                            {{-- حذف فرم --}}
                            <button
                                type="button"
                                wire:click="deleteForm({{ $f->id }})"
                                onclick="return confirm('فرم حذف شود؟');"
                                class="text-xs px-2 py-1 rounded-lg bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">
                                ✕
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- ستون اصلی: ویرایشگر --}}
        <div class="col-span-12 lg:col-span-9 space-y-6">

            {{-- باکس تنظیمات کلی فرم --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    تنظیمات عمومی فرم
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="{{ $labelClass }}">نام نمایشی فرم</label>
                        <input type="text" wire:model="name" class="{{ $inputClass }}" placeholder="مثلاً: فرم حقوقی">
                        @error('name')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">شناسه سیستمی (Key)</label>
                        <input type="text" wire:model="key" class="{{ $inputClass }} dir-ltr font-mono text-xs"
                               placeholder="در صورت خالی‌بودن، خودکار تولید می‌شود">
                        @error('key')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="flex items-end pb-2">
                        <label
                            class="inline-flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer w-full border border-transparent hover:border-gray-200 dark:hover:border-gray-600">
                            <input type="checkbox" wire:model="is_active" class="{{ $checkboxClass }}">
                            <span class="text-sm text-gray-700 dark:text-gray-300">این فرم فعال باشد</span>
                        </label>
                    </div>
                </div>

                <p class="mt-3 text-[11px] text-gray-500 dark:text-gray-400">
                    آیدی‌های زیر برای فیلدهای سیستمی رزرو شده‌اند و نباید در فیلدهای سفارشی به‌کار بروند:
                    <span class="font-mono">username, full_name, email, phone, national_code</span>
                </p>

                @error('schema')
                <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            {{-- جعبه ابزار افزودن فیلد --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    افزودن فیلد جدید
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach(['text','textarea','email','number','date','checkbox','radio','file','select','select-province-city','select-user-by-role','profile-photo'] as $t)
                        <button type="button" wire:click="addField('{{ $t }}')"
                                class="px-3 py-2 rounded-lg text-xs font-medium border border-gray-200 bg-gray-50 text-gray-600 hover:bg-white hover:border-indigo-300 hover:text-indigo-600 hover:shadow-sm transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:border-gray-500">
                            + {{ $t }}
                        </button>
                    @endforeach
                </div>
                <div class="mt-3 border-t border-dashed border-gray-200 pt-3 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 mb-2 dark:text-gray-400">
                        فیلدهای سیستمی (با شناسه ثابت)
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $sysDefaults = \Modules\Clients\Entities\ClientForm::systemFieldDefaults();
                        @endphp

                        @foreach($sysDefaults as $sid => $sf)
                            @php
                                $alreadyInForm = collect($schema['fields'] ?? [])
                                    ->contains(fn($f) => ($f['id'] ?? null) === $sid);
                            @endphp

                            <button
                                type="button"
                                @if(!$alreadyInForm) wire:click="addSystemField('{{ $sid }}')" @endif
                                class="px-3 py-1.5 rounded-lg text-[11px] font-medium border
                           {{ $alreadyInForm
                                ? 'border-emerald-300 bg-emerald-50 text-emerald-700 cursor-default dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-white hover:border-emerald-400 hover:text-emerald-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:border-emerald-500' }}">
                                {{ $sf['label'] ?? $sid }}
                                @if($alreadyInForm)
                                    <span class="text-[10px] ml-1 opacity-70">(در این فرم وجود دارد)</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            @php
                $systemFieldIds = ['full_name','phone','email','national_code','notes','status_id','password'];

                $systemFields = [];
                $customFields = [];

                foreach (($schema['fields'] ?? []) as $i => $field) {
                    $fid = $field['id'] ?? "f{$i}";
                    if (in_array($fid, $systemFieldIds, true)) {
                        $systemFields[] = ['i' => $i, 'field' => $field, 'fid' => $fid];
                    } else {
                        $customFields[] = ['i' => $i, 'field' => $field, 'fid' => $fid];
                    }
                }
            @endphp

            {{-- لیست فیلدها (Schema) --}}

            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="font-medium text-gray-900 dark:text-white">فیلدهای فعال</h3>
                    <span
                        class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-lg dark:bg-gray-700 dark:text-gray-400">
            {{ count($schema['fields'] ?? []) }} مورد
        </span>
                </div>

                {{-- === فیلدهای سیستمی (ID ثابت) === --}}
                @foreach($systemFields as $item)
                    @php($i   = $item['i'])
                    @php($fid = $item['fid'])
                    @php($field = $item['field'])
                    <div
                        class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-emerald-300/70 dark:border-emerald-700/70 shadow-sm hover:shadow-md transition-all overflow-hidden"
                        wire:key="field-system-{{ $fid }}">

                        {{-- نوار رنگی کنار کارت --}}
                        <div class="absolute right-0 top-0 bottom-0 w-1 bg-emerald-500/70 group-hover:bg-emerald-500 transition-colors"></div>

                        {{-- هدر --}}
                        <div class="flex items-center justify-between bg-emerald-50/70 dark:bg-emerald-900/20 px-5 py-3 border-b border-emerald-100/70 dark:border-emerald-800/60">
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-600 text-white">
                                    فیلد سیستمی
                                </span>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-mono font-semibold bg-gray-900/80 text-emerald-300">
                                    {{ $fid }}
                                </span>
                                <input type="text"
                                       wire:model="schema.fields.{{ $i }}.label"
                                       class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 dark:text-white placeholder-gray-300"
                                       placeholder="عنوان فیلد">
                            </div>

                            <div class="flex items-center gap-3">
                                {{-- فقط حذف از این فرم (ID ثابت می‌ماند) --}}
                                <button type="button"
                                        wire:click="removeField({{ $i }})"
                                        class="text-gray-400 hover:text-red-500 transition-colors text-xs flex items-center gap-1"
                                        title="حذف این فیلد سیستمی از این فرم">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>حذف از فرم</span>
                                </button>
                            </div>
                        </div>

                        {{-- بدنه تنظیمات فیلد سیستمی --}}
                        <div class="p-5 space-y-5">

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                {{-- placeholder --}}
                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}">Placeholder</label>
                                    <input type="text"
                                           class="{{ $inputClass }} !py-1.5 !text-xs"
                                           placeholder="متن راهنمای داخل فیلد"
                                           wire:model="schema.fields.{{ $i }}.placeholder">
                                </div>

                                {{-- عرض فیلد --}}
                                <div>
                                    <label class="{{ $labelClass }}">عرض فیلد</label>
                                    <select
                                        class="{{ $inputClass }} !py-1.5 !text-xs"
                                        wire:model="schema.fields.{{ $i }}.width">
                                        <option value="full">تمام عرض</option>
                                        <option value="1/2">نصف عرض</option>
                                        <option value="1/3">یک‌سوم عرض</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                {{-- گروه --}}
                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}">گروه (بخش)</label>
                                    <input type="text"
                                           class="{{ $inputClass }} !py-1.5 !text-xs"
                                           placeholder="مثلاً: اطلاعات هویتی"
                                           wire:model="schema.fields.{{ $i }}.group">
                                </div>

                                {{-- Required / Quick --}}
                                <div class="flex flex-col gap-2 pt-1">
                                    <label
                                        class="inline-flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                        <input type="checkbox" class="{{ $checkboxClass }}"
                                               wire:model="schema.fields.{{ $i }}.required">
                                        <span class="text-xs text-gray-700 dark:text-gray-300">الزامی (Required)</span>
                                    </label>
                                    <label
                                        class="inline-flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                        <input type="checkbox" class="{{ $checkboxClass }}"
                                               wire:model="schema.fields.{{ $i }}.quick_create">
                                        <span class="text-xs text-gray-700 dark:text-gray-300">نمایش در ایجاد سریع</span>
                                    </label>
                                </div>
                            </div>

                            {{-- الزامی بر اساس وضعیت پرونده --}}
                            @if(!empty($statuses))
                                <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                                    <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 mb-2">
                                        الزامی بر اساس وضعیت پرونده
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($statuses as $st)
                                            <label
                                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-gray-50 text-[11px] text-gray-700 border border-gray-200 cursor-pointer hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                                <input type="checkbox"
                                                       class="{{ $checkboxClass }} w-3.5 h-3.5"
                                                       wire:model="schema.fields.{{ $i }}.required_status_keys"
                                                       value="{{ $st['key'] }}">
                                                <span>{{ $st['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">
                                        اگر وضعیت پرونده روی هر یک از این موارد قرار بگیرد، پر کردن این فیلد به‌طور خودکار الزامی می‌شود.
                                    </p>
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach

                {{-- === فیلدهای سفارشی (غیرسیستمی، مثل قبل) === --}}
                @foreach($customFields as $item)
                    @php($i   = $item['i'])
                    @php($fid = $item['fid'])
                    @php($field = $item['field'])
                    <div
                        class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all overflow-hidden"
                        wire:key="field-custom-{{ $fid }}">

                        {{-- نوار رنگی کنار کارت --}}
                        <div
                            class="absolute right-0 top-0 bottom-0 w-1 bg-indigo-500/20 group-hover:bg-indigo-500 transition-colors"></div>

                        {{-- هدر فیلد --}}
                        <div
                            class="flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/30 px-5 py-3 border-b border-gray-100 dark:border-gray-700/50">
                            <div class="flex items-center gap-3">
                    <span
                        class="inline-flex items-center px-2 py-1 rounded text-xs font-mono font-semibold bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 uppercase">
                        {{ $field['type'] }}
                    </span>
                                <input type="text" wire:model="schema.fields.{{ $i }}.label"
                                       class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 dark:text-white placeholder-gray-400"
                                       placeholder="عنوان فیلد (Label)">
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">ID:</span>
                                    <input type="text" wire:model="schema.fields.{{ $i }}.id"
                                           class="bg-transparent border-b border-gray-300 text-xs font-mono text-gray-600 focus:border-indigo-500 focus:ring-0 w-24 text-left dir-ltr dark:border-gray-600 dark:text-gray-400"
                                           placeholder="field_id">
                                </div>

                                {{-- حذف فیلد --}}
                                <button type="button"
                                        wire:click="removeField({{ $i }})"
                                        class="text-gray-400 hover:text-red-500 transition-colors"
                                        title="حذف فیلد">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- بدنه فیلد --}}
                        <div class="p-5 space-y-5">
                            {{-- تنظیمات اصلی --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                <label
                                    class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                    <input type="checkbox" class="{{ $checkboxClass }}"
                                           wire:model="schema.fields.{{ $i }}.required">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">الزامی (Required)</span>
                                </label>
                                <label
                                    class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                    <input type="checkbox" class="{{ $checkboxClass }}"
                                           wire:model="schema.fields.{{ $i }}.quick_create">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">نمایش در ایجاد سریع</span>
                                </label>

                                {{-- عرض فیلد --}}
                                <div>
                                    <label class="{{ $labelClass }}">عرض فیلد</label>
                                    <select
                                        class="{{ $inputClass }} !py-1.5 !text-xs"
                                        wire:model="schema.fields.{{ $i }}.width">
                                        <option value="full">تمام عرض</option>
                                        <option value="1/2">نصف عرض</option>
                                        <option value="1/3">یک‌سوم عرض</option>
                                    </select>
                                </div>

                                {{-- گروه --}}
                                <div>
                                    <label class="{{ $labelClass }}">گروه (بخش)</label>
                                    <input type="text"
                                           class="{{ $inputClass }} !py-1.5 !text-xs"
                                           placeholder="مثلاً: اطلاعات تماس"
                                           wire:model="schema.fields.{{ $i }}.group">
                                </div>
                            </div>

                            {{-- placeholder + validation --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="{{ $labelClass }}">Placeholder</label>
                                    <input type="text"
                                           class="{{ $inputClass }} !py-1.5 !text-xs"
                                           placeholder="متن راهنمای داخل فیلد"
                                           wire:model="schema.fields.{{ $i }}.placeholder">
                                </div>

                                @if(in_array($field['type'], ['text','email','number','date','textarea']))
                                    <div>
                                        <label class="{{ $labelClass }}">Validation Rules</label>
                                        <input type="text"
                                               class="{{ $inputClass }} !py-1.5 !text-xs"
                                               placeholder="string|max:255"
                                               wire:model="schema.fields.{{ $i }}.validate">
                                    </div>
                                @endif
                            </div>

                            {{-- تنظیمات اختصاصی بر اساس نوع --}}
                            @if($field['type'] === 'file')
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-900/30">
                                    <div>
                                        <label class="{{ $labelClass }}">Max Size (MB)</label>
                                        <input type="number" class="{{ $inputClass }}"
                                               wire:model="schema.fields.{{ $i }}.max_mb">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="{{ $labelClass }}">Allowed Types</label>
                                        <input type="text" class="{{ $inputClass }} dir-ltr"
                                               placeholder="image/*,application/pdf"
                                               wire:model="schema.fields.{{ $i }}.accept">
                                    </div>
                                </div>
                            @endif

                            @if($field['type'] === 'select' || $field['type'] === 'radio')
                                <div class="space-y-2">
                                    <label class="{{ $labelClass }}">
                                        گزینه‌ها (JSON Format: <code>{"key":"Label"}</code>)
                                    </label>
                                    <textarea class="{{ $inputClass }} font-mono text-xs dir-ltr" rows="3"
                                              placeholder='{"m":"مرد", "f":"زن"}'
                                              wire:model.lazy="schema.fields.{{ $i }}.options_json"></textarea>
                                </div>
                            @endif

                            @if($field['type'] === 'select-province-city')
                                <div
                                    class="flex items-center gap-2 text-sm text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    داده‌های این فیلد به صورت خودکار از لیست استان‌ها و شهرهای ایران بارگذاری می‌شود.
                                </div>
                            @endif

                            @if($field['type'] === 'select-user-by-role')
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-gray-700/30 dark:border-gray-600">
                                    <div>
                                        <label class="{{ $labelClass }}">نقش</label>
                                        <select
                                            class="{{ $inputClass }} !py-1.5 !text-xs dir-ltr"
                                            wire:model="schema.fields.{{ $i }}.role">
                                            <option value="">انتخاب نقش</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role['name'] }}">{{ $role['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex flex-col justify-end pb-2 space-y-2">
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" class="{{ $checkboxClass }}"
                                                   wire:model="schema.fields.{{ $i }}.multiple">
                                            <span class="text-xs text-gray-600 dark:text-gray-400">انتخاب چندگانه (Multiple)</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" class="{{ $checkboxClass }}"
                                                   wire:model="schema.fields.{{ $i }}.lock_current_if_role">
                                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                    اگر نقش کاربر فعلی همین باشد، روی او قفل شود
                                </span>
                                        </label>
                                    </div>
                                </div>
                            @endif

                            {{-- الزامی بر اساس وضعیت پرونده --}}
                            @if(!empty($statuses))
                                <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                                    <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 mb-2">
                                        الزامی بر اساس وضعیت پرونده
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($statuses as $st)
                                            <label
                                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-gray-50 text-[11px] text-gray-700 border border-gray-200 cursor-pointer hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                                <input type="checkbox"
                                                       class="{{ $checkboxClass }} w-3.5 h-3.5"
                                                       wire:model="schema.fields.{{ $i }}.required_status_keys"
                                                       value="{{ $st['key'] }}">
                                                <span>{{ $st['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">
                                        مثال: برای فیلد «علت لغو» می‌توانید وضعیت «لغو شده» را تیک بزنید تا فقط در آن حالت، پر کردن فیلد اجباری شود.
                                    </p>
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach

                @if(empty($schema['fields']))
                    <div
                        class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">هنوز فیلدی اضافه نشده است</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            از جعبه ابزار بالا برای افزودن اولین فیلد استفاده کنید.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
