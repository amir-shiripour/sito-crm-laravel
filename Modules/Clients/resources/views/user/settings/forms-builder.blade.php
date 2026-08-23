{{-- clients::user.settings.forms-builder --}}
@php
    $title = 'فرم‌ساز ' . config('clients.labels.singular');
    // استایل پایه اینپوت‌ها
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500
    focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50
    dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1";
    $checkboxClass = "w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600
    dark:bg-gray-800 transition-colors cursor-pointer";
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
                      d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            ذخیره تغییرات
        </button>
    </div>

    <div class="grid grid-cols-12 gap-6 items-start">

        {{-- ستون کناری: لیست فرم‌ها --}}
        <div class="col-span-12 lg:col-span-3 space-y-4 sticky top-20">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div
                    class="p-4 bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        لیست فرم‌ها
                    </h2>

                    {{-- فرم جدید --}}
                    <button type="button" wire:click="newForm"
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
                                    <span
                                        class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                فعال
                            </span>
                                @endif
                            </button>

                            {{-- حذف فرم --}}
                            <button type="button" wire:click="deleteForm({{ $f->id }})"
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
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
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
                    <span class="font-mono">username, full_name, email, phone, national_code, case_number</span>
                </p>

                @error('schema')
                <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            @php
                $customFieldTypes = [
                    'text' => [
                        'label' => 'متن تک‌خطی',
                        'desc' => 'نام، عنوان و عبارات کوتاه',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
                    ],
                    'textarea' => [
                        'label' => 'متن چندخطی',
                        'desc' => 'توضیحات، سابقه و یادداشت',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>',
                    ],
                    'number' => [
                        'label' => 'عدد و ارقام',
                        'desc' => 'مبلغ، تعداد، سن و کدها',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>',
                    ],
                    'email' => [
                        'label' => 'پست الکترونیک',
                        'desc' => 'ایمیل با اعتبارسنجی',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>',
                    ],
                    'date' => [
                        'label' => 'تاریخ شمسی',
                        'desc' => 'انتخابگر تقویم جلالی',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                    ],
                    'select' => [
                        'label' => 'منوی کشویی',
                        'desc' => 'تک یا چندانتخابی از لیست',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>',
                    ],
                    'checkbox' => [
                        'label' => 'چک‌باکس',
                        'desc' => 'انتخاب گزینه‌های چندگانه',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    ],
                    'radio' => [
                        'label' => 'دکمه رادیویی',
                        'desc' => 'تک‌انتخابی از گزینه‌ها',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    ],
                    'file' => [
                        'label' => 'آپلود فایل',
                        'desc' => 'PDF، اسناد، مدارک ضمیمه',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>',
                    ],
                    'profile-photo' => [
                        'label' => 'تصویر پروفایل',
                        'desc' => 'عکس پرسنلی با بهینه‌ساز WebP',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    ],
                    'select-province-city' => [
                        'label' => 'استان و شهر',
                        'desc' => 'انتخاب هوشمند استان/شهر ایران',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    ],
                    'select-user-by-role' => [
                        'label' => 'کاربر با نقش',
                        'desc' => 'ارجاع به پرسنل، پزشکان و...',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
                    ],
                ];
            @endphp

            {{-- جعبه ابزار افزودن فیلد --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></span>
                        افزودن فیلد جدید
                    </h3>
                    <span class="text-xs text-gray-400 dark:text-gray-500">برای اضافه شدن روی نوع فیلد کلیک کنید</span>
                </div>

                {{-- فیلدهای سفارشی با عناوین و آیکون‌های فارسی --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5">
                    @foreach($customFieldTypes as $t => $tInfo)
                        <button type="button" wire:click="addField('{{ $t }}')"
                                class="group flex flex-col items-start p-2.5 rounded-xl border border-gray-200/90 bg-gray-50/50 hover:bg-indigo-50/50 hover:border-indigo-300 text-right transition-all shadow-2xs hover:shadow-sm dark:bg-gray-700/50 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-indigo-500">
                            <div class="flex items-center justify-between w-full mb-1">
                                <div class="p-1.5 rounded-lg bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        {!! $tInfo['icon'] !!}
                                    </svg>
                                </div>
                                <span class="text-xs text-indigo-500 font-bold opacity-0 group-hover:opacity-100 transition-opacity">+</span>
                            </div>
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1">
                                {{ $tInfo['label'] }}
                            </span>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 line-clamp-1">
                                {{ $tInfo['desc'] }}
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- فیلدهای سیستمی --}}
                <div class="border-t border-dashed border-gray-200 pt-3 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 mb-2 dark:text-gray-400 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
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

                            <button type="button" @if(!$alreadyInForm) wire:click="addSystemField('{{ $sid }}')" @endif
                            class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all
                           {{ $alreadyInForm
                                ? 'border-emerald-300 bg-emerald-50 text-emerald-700 cursor-default dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-white hover:border-emerald-400 hover:text-emerald-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:border-emerald-500 shadow-2xs' }}">
                                + {{ $sf['label'] ?? $sid }}
                                @if($alreadyInForm)
                                    <span class="text-[10px] mr-1 opacity-70">✓</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            @php
                $systemFieldIds =
                ['full_name','phone','email','national_code','case_number','notes','status_id','password','booking_waitlist'];

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
            <div x-data="{
                searchQuery: '',
                filterType: 'all',
                openFields: {},
                allFieldIds: @js(collect($schema['fields'] ?? [])->map(fn($f, $idx) => $f['id'] ?? "f{$idx}")->toArray()),
                isFieldVisible(label, id, group, type, isSystem, isRequired, isQuick, isAuth, isReg) {
                    const q = this.searchQuery.toLowerCase().trim();
                    const matchesQuery = !q || 
                        (label && String(label).toLowerCase().includes(q)) || 
                        (id && String(id).toLowerCase().includes(q)) || 
                        (group && String(group).toLowerCase().includes(q)) ||
                        (type && String(type).toLowerCase().includes(q));
                    if (!matchesQuery) return false;

                    if (this.filterType === 'all') return true;
                    if (this.filterType === 'system') return isSystem;
                    if (this.filterType === 'custom') return !isSystem;
                    if (this.filterType === 'required') return isRequired;
                    if (this.filterType === 'quick') return isQuick;
                    if (this.filterType === 'auth') return isAuth;
                    if (this.filterType === 'reg') return isReg;
                    return true;
                },
                isOpen(fid) {
                    if (this.searchQuery.trim() !== '') return true;
                    return this.openFields[fid] !== false;
                },
                toggleField(fid) {
                    if (this.openFields[fid] === undefined) {
                        this.openFields[fid] = false;
                    } else {
                        this.openFields[fid] = !this.openFields[fid];
                    }
                },
                expandAll() {
                    this.allFieldIds.forEach(fid => this.openFields[fid] = true);
                },
                collapseAll() {
                    this.allFieldIds.forEach(fid => this.openFields[fid] = false);
                },
                scrollToField(fid) {
                    this.openFields[fid] = true;
                    this.$nextTick(() => {
                        const el = document.getElementById('field-card-' + fid);
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            el.classList.add('ring-2', 'ring-indigo-500', 'ring-offset-2');
                            setTimeout(() => el.classList.remove('ring-2', 'ring-indigo-500', 'ring-offset-2'), 2500);
                        }
                    });
                }
            }" class="space-y-4">

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1">
                    <div class="flex items-center gap-2.5">
                        <h3 class="font-bold text-base text-gray-900 dark:text-white">فیلدهای فعال</h3>
                        <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 dark:bg-indigo-900/40 dark:text-indigo-300 px-2.5 py-0.5 rounded-full border border-indigo-100 dark:border-indigo-800">
                            {{ count($schema['fields'] ?? []) }} فیلد
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(!$reorderMode)
                            <button type="button" wire:click="toggleReorderMode"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-100 text-indigo-700 font-bold hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-200 dark:hover:bg-indigo-800/70 transition-all text-xs shadow-2xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 8h16M4 16h16" />
                                </svg>
                                مرتب‌سازی فیلدها
                            </button>
                        @else
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="confirmReorder"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition-all text-xs shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7" />
                                    </svg>
                                    تایید ترتیب
                                </button>
                                <button type="button" wire:click="toggleReorderMode"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-200 text-gray-700 font-bold hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all text-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    انصراف
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                @if(!$reorderMode && !empty($schema['fields']))
                    {{-- نوار ابزار جستجو، فیلتر و پرش سریع --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-3.5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                            {{-- جعبه جستجوی زنده --}}
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="text" x-model="searchQuery"
                                       placeholder="جستجوی سریع عنوان، شناسه، نوع یا گروه فیلد..."
                                       class="w-full pr-9 pl-8 py-2 rounded-xl text-xs bg-gray-50 dark:bg-gray-900/60 border-gray-200 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 text-gray-900 dark:text-gray-100 transition-all">
                                <button type="button" x-show="searchQuery" @click="searchQuery = ''"
                                        class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- ابزارهای باز/بسته و پرش سریع --}}
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- سلکتور پرش سریع --}}
                                <div class="relative flex-1 sm:flex-initial">
                                    <select @change="if($event.target.value) { scrollToField($event.target.value); $event.target.value = ''; }"
                                            class="w-full sm:w-auto py-2 pr-3 pl-7 rounded-xl text-xs bg-gray-50 dark:bg-gray-900/60 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20">
                                        <option value="">🎯 پرش سریع به فیلد...</option>
                                        @if(!empty($systemFields))
                                            <optgroup label="فیلدهای سیستمی">
                                                @foreach($systemFields as $sfItem)
                                                    <option value="{{ $sfItem['fid'] }}">{{ $sfItem['field']['label'] ?? $sfItem['fid'] }} ({{ $sfItem['fid'] }})</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if(!empty($customFields))
                                            <optgroup label="فیلدهای سفارشی">
                                                @foreach($customFields as $cfItem)
                                                    <option value="{{ $cfItem['fid'] }}">{{ $cfItem['field']['label'] ?? $cfItem['fid'] }} ({{ $cfItem['field']['type'] }})</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>

                                <button type="button" @click="expandAll()"
                                        class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium transition-colors"
                                        title="باز کردن همه فیلدها">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    <span class="hidden sm:inline">باز کردن همه</span>
                                </button>

                                <button type="button" @click="collapseAll()"
                                        class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium transition-colors"
                                        title="جمع کردن همه فیلدها">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    <span class="hidden sm:inline">بستن همه</span>
                                </button>
                            </div>
                        </div>

                        {{-- تب‌های فیلتر سریع --}}
                        <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                            <span class="text-gray-400 dark:text-gray-500 text-[11px] ml-1">فیلتر:</span>
                            <button type="button" @click="filterType = 'all'"
                                    :class="filterType === 'all' ? 'bg-indigo-600 text-white font-bold shadow-2xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-400 dark:hover:bg-gray-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition-all">
                                همه ({{ count($schema['fields'] ?? []) }})
                            </button>
                            <button type="button" @click="filterType = 'system'"
                                    :class="filterType === 'system' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-400 dark:hover:bg-gray-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition-all">
                                سیستمی ({{ count($systemFields) }})
                            </button>
                            <button type="button" @click="filterType = 'custom'"
                                    :class="filterType === 'custom' ? 'bg-indigo-600 text-white font-bold shadow-2xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-400 dark:hover:bg-gray-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition-all">
                                سفارشی ({{ count($customFields) }})
                            </button>
                            <button type="button" @click="filterType = 'required'"
                                    :class="filterType === 'required' ? 'bg-rose-600 text-white font-bold shadow-2xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-400 dark:hover:bg-gray-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition-all">
                                الزامی‌ها
                            </button>
                            <button type="button" @click="filterType = 'quick'"
                                    :class="filterType === 'quick' ? 'bg-indigo-600 text-white font-bold shadow-2xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-400 dark:hover:bg-gray-700'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition-all">
                                ایجاد سریع
                            </button>
                        </div>
                    </div>
                @endif

                @if($reorderMode)
                    {{-- حالت مرتب‌سازی: نمایش ساده فیلدها --}}
                    @php
                        $groupedFields = collect($schema['fields'] ?? [])->groupBy(function($field) {
                        $group = $field['group'] ?? '';
                        return $group === '' ? '__no_group__' : $group;
                        });
                    @endphp

                    {{-- Container برای جابه‌جایی گروه‌ها --}}
                    <div class="space-y-4 sortable-groups-container">
                        @foreach($groupedFields as $groupName => $groupFields)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-indigo-300 dark:border-indigo-700 p-4 shadow-sm sortable-group-item"
                                 data-group-name="{{ $groupName === '__no_group__' ? '' : $groupName }}">
                                <div class="mb-3 flex items-center gap-2 cursor-move group-header">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 8h16M4 16h16" />
                                    </svg>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $groupName === '__no_group__' ? 'بدون گروه' : $groupName }}
                                    </h4>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                ({{ count($groupFields) }} فیلد)
                            </span>
                                </div>
                                <div class="space-y-2 sortable-group"
                                     data-group="{{ $groupName === '__no_group__' ? '' : $groupName }}">
                                    @foreach($groupFields as $field)
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 cursor-move hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors sortable-field-item"
                                             data-field-id="{{ $field['id'] ?? '' }}">
                                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M4 8h16M4 16h16" />
                                            </svg>
                                            <div class="flex-1">
                                                <div class="font-medium text-sm text-gray-900 dark:text-white">
                                                    {{ $field['label'] ?? 'بی‌نام' }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ $field['type'] ?? 'text' }} | ID: {{ $field['id'] ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                @else
                    {{-- حالت عادی: نمایش کامل فیلدها --}}

                    {{-- === فیلدهای سیستمی (ID ثابت) === --}}
                    @foreach($systemFields as $item)
                        @php
                            $i = $item['i'];
                            $fid = $item['fid'];
                            $field = $item['field'];
                        @endphp
                        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-emerald-300/70 dark:border-emerald-700/70 shadow-sm hover:shadow-md transition-all overflow-hidden"
                             id="field-card-{{ $fid }}"
                             data-field-id="{{ $fid }}"
                             wire:key="field-system-{{ $fid }}"
                             x-show="isFieldVisible(@js($field['label'] ?? $fid), @js($fid), @js($field['group'] ?? ''), @js($field['type'] ?? 'system'), true, @js(!empty($field['required'])), @js(!empty($field['quick_create'])), @js(!empty($field['client_auth'])), @js(!empty($field['show_in_registration'])))"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">

                            {{-- نوار رنگی کنار کارت --}}
                            <div
                                class="absolute right-0 top-0 bottom-0 w-1 bg-emerald-500/70 group-hover:bg-emerald-500 transition-colors">
                            </div>

                            {{-- هدر --}}
                            <div
                                class="flex items-center justify-between bg-emerald-50/70 dark:bg-emerald-900/20 px-4 py-3 border-b border-emerald-100/70 dark:border-emerald-800/60 cursor-pointer select-none"
                                @click="toggleField('{{ $fid }}')">
                                <div class="flex items-center gap-2.5 flex-1 min-w-0" @click.stop>
                                    <button type="button" @click="toggleField('{{ $fid }}')"
                                            class="p-1 rounded-lg hover:bg-emerald-200/50 dark:hover:bg-emerald-800/50 text-emerald-700 dark:text-emerald-300 transition-transform duration-200"
                                            :class="isOpen('{{ $fid }}') ? 'rotate-180' : ''"
                                            title="باز/بستن فیلد">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-600 text-white shrink-0">
                                        سیستمی
                                    </span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-gray-900/80 text-emerald-300 shrink-0">
                                        {{ $fid }}
                                    </span>
                                    <input type="text" wire:model="schema.fields.{{ $i }}.label"
                                           class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 dark:text-white placeholder-gray-400 min-w-0 flex-1"
                                           placeholder="عنوان فیلد">
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    @if(!empty($field['group']))
                                        <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            📁 {{ $field['group'] }}
                                        </span>
                                    @endif

                                    @if(!empty($field['required']))
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                                            الزامی
                                        </span>
                                    @endif

                                    @if(!empty($field['quick_create']))
                                        <span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                            ایجاد سریع
                                        </span>
                                    @endif

                                    {{-- فقط حذف از این فرم (ID ثابت می‌ماند) --}}
                                    <button type="button" wire:click="removeField({{ $i }})" @click.stop
                                            class="text-gray-400 hover:text-red-500 transition-colors text-xs p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/30"
                                            title="حذف این فیلد سیستمی از این فرم">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- بدنه تنظیمات فیلد سیستمی --}}
                            <div x-show="isOpen('{{ $fid }}')"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="p-5 space-y-5">

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    {{-- placeholder --}}
                                    <div class="sm:col-span-2">
                                        <label class="{{ $labelClass }}">Placeholder</label>
                                        <input type="text" class="{{ $inputClass }} !py-1.5 !text-xs"
                                               placeholder="متن راهنمای داخل فیلد" wire:model="schema.fields.{{ $i }}.placeholder">
                                    </div>

                                    {{-- عرض فیلد --}}
                                    <div>
                                        <label class="{{ $labelClass }}">عرض فیلد</label>
                                        <select class="{{ $inputClass }} !py-1.5 !text-xs"
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
                                        <input type="text" class="{{ $inputClass }} !py-1.5 !text-xs"
                                               placeholder="مثلاً: اطلاعات هویتی" wire:model="schema.fields.{{ $i }}.group">
                                    </div>

                                    {{-- Required / Quick / Auth --}}
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
                                        <label
                                            class="inline-flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                            <input type="checkbox" class="{{ $checkboxClass }}"
                                                   wire:model="schema.fields.{{ $i }}.client_auth">
                                            <span class="text-xs text-gray-700 dark:text-gray-300">احراز هویت کاربر (ویرایش در پروفایل)</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                            <input type="checkbox" class="{{ $checkboxClass }}"
                                                   wire:model="schema.fields.{{ $i }}.show_in_registration">
                                            <span class="text-xs text-gray-700 dark:text-gray-300">ثبت نام کاربر</span>
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
                                                    <input type="checkbox" class="{{ $checkboxClass }} w-3.5 h-3.5"
                                                           wire:model="schema.fields.{{ $i }}.required_status_keys"
                                                           value="{{ $st['key'] }}">
                                                    <span>{{ $st['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">
                                            اگر وضعیت پرونده روی هر یک از این موارد قرار بگیرد، پر کردن این فیلد به‌طور خودکار
                                            الزامی می‌شود.
                                        </p>
                                    </div>
                                @endif

                                {{-- قوانین شرطی برای الزامی شدن --}}
                                <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                                            الزامی شرطی (اگر فیلد دیگری پر شد)
                                        </p>
                                        <button type="button" wire:click="addConditionalRule({{ $i }})"
                                                class="text-xs px-2 py-1 rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-200 dark:hover:bg-indigo-800/70 transition-colors">
                                            + افزودن قانون
                                        </button>
                                    </div>

                                    @if(!empty($field['conditional_required']))
                                        <div class="space-y-2">
                                            @foreach($field['conditional_required'] as $ruleIdx => $rule)
                                                <div
                                                    class="flex items-center gap-2 p-3 rounded-lg bg-indigo-50/50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-900/30">
                                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                        {{-- فیلد ماشه --}}
                                                        <div>
                                                            <label class="text-[10px] text-gray-600 dark:text-gray-400 mb-1 block">اگر
                                                                فیلد:</label>
                                                            <select class="{{ $inputClass }} !py-1.5 !text-xs"
                                                                    wire:model.live="schema.fields.{{ $i }}.conditional_required.{{ $ruleIdx }}.trigger_field_id">
                                                                <option value="">انتخاب فیلد...</option>
                                                                @foreach($schema['fields'] ?? [] as $otherIdx => $otherField)
                                                                    @if($otherIdx !== $i)
                                                                        <option value="{{ $otherField['id'] ?? '' }}">
                                                                            {{ $otherField['label'] ?? ($otherField['id'] ?? '') }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        {{-- عملگر --}}
                                                        <div>
                                                            <label
                                                                class="text-[10px] text-gray-600 dark:text-gray-400 mb-1 block">شرط:</label>
                                                            <select class="{{ $inputClass }} !py-1.5 !text-xs"
                                                                    wire:model.live="schema.fields.{{ $i }}.conditional_required.{{ $ruleIdx }}.operator">
                                                                <option value="filled">پر شود</option>
                                                                <option value="empty">خالی باشد</option>
                                                                <option value="equals">برابر با</option>
                                                                <option value="not_equals">مخالف با</option>
                                                            </select>
                                                        </div>

                                                        {{-- مقدار (برای equals/not_equals) --}}
                                                        <div>
                                                            <label class="text-[10px] text-gray-600 dark:text-gray-400 mb-1 block">مقدار
                                                                (اختیاری):</label>
                                                            @php
                                                                $triggerFieldId = $rule['trigger_field_id'] ?? '';
                                                                $triggerField = collect($schema['fields'] ?? [])->firstWhere('id', $triggerFieldId);
                                                                $triggerOptions = [];
                                                                if ($triggerField) {
                                                                    if ($triggerFieldId === 'status_id' && !empty($statuses)) {
                                                                        foreach ($statuses as $st) {
                                                                            $triggerOptions[$st['id']] = $st['label'];
                                                                        }
                                                                    } elseif (in_array($triggerField['type'] ?? '', ['select', 'radio']) && !empty($triggerField['options_json'])) {
                                                                        $parsedOpts = json_decode($triggerField['options_json'], true);
                                                                        if (is_array($parsedOpts)) {
                                                                            $triggerOptions = $parsedOpts;
                                                                        } else {
                                                                            $lines = array_filter(array_map('trim', explode("\n", $triggerField['options_json'])));
                                                                            foreach ($lines as $line) {
                                                                                if (str_contains($line, ':')) {
                                                                                    [$okey, $oval] = array_map('trim', explode(':', $line, 2));
                                                                                    $triggerOptions[$okey] = $oval;
                                                                                } else {
                                                                                    $triggerOptions[$line] = $line;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                $isDisabled = !in_array($rule['operator'] ?? 'filled', ['equals', 'not_equals']);
                                                            @endphp

                                                            @if(!empty($triggerOptions))
                                                                <select class="{{ $inputClass }} !py-1.5 !text-xs"
                                                                        wire:model="schema.fields.{{ $i }}.conditional_required.{{ $ruleIdx }}.value"
                                                                    {{ $isDisabled ? 'disabled' : '' }}>
                                                                    <option value="">انتخاب مقدار...</option>
                                                                    @foreach($triggerOptions as $tkey => $tval)
                                                                        <option value="{{ $tkey }}">{{ $tval }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input type="text" class="{{ $inputClass }} !py-1.5 !text-xs"
                                                                       placeholder="فقط برای 'برابر با'"
                                                                       wire:model="schema.fields.{{ $i }}.conditional_required.{{ $ruleIdx }}.value"
                                                                    {{ $isDisabled ? 'disabled' : '' }}>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- حذف قانون --}}
                                                    <button type="button" wire:click="removeConditionalRule({{ $i }}, {{ $ruleIdx }})"
                                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                                            title="حذف قانون">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 italic">
                                            هنوز قانونی تعریف نشده است. با کلیک روی «افزودن قانون» می‌توانید قانون جدید اضافه کنید.
                                        </p>
                                    @endif

                                    <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500">
                                        مثال: اگر فیلد «نوع مشتری» برابر با «حقوقی» باشد، فیلد «شماره ثبت» الزامی می‌شود.
                                    </p>
                                </div>

                            </div>
                        </div>
                    @endforeach

                    {{-- === فیلدهای سفارشی (غیرسیستمی، مثل قبل) === --}}
                    @foreach($customFields as $item)
                        @php
                            $i = $item['i'];
                            $fid = $item['fid'];
                            $field = $item['field'];
                        @endphp
                        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all overflow-hidden"
                             id="field-card-{{ $fid }}"
                             data-field-id="{{ $fid }}"
                             wire:key="field-custom-{{ $fid }}"
                             x-show="isFieldVisible(@js($field['label'] ?? $fid), @js($fid), @js($field['group'] ?? ''), @js($field['type'] ?? 'text'), false, @js(!empty($field['required'])), @js(!empty($field['quick_create'])), @js(!empty($field['client_auth'])), @js(!empty($field['show_in_registration'])))"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">

                            {{-- نوار رنگی کنار کارت --}}
                            <div
                                class="absolute right-0 top-0 bottom-0 w-1 bg-indigo-500/20 group-hover:bg-indigo-500 transition-colors">
                            </div>

                            {{-- هدر فیلد --}}
                            <div
                                class="flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/30 px-4 py-3 border-b border-gray-100 dark:border-gray-700/50 cursor-pointer select-none"
                                @click="toggleField('{{ $fid }}')">
                                <div class="flex items-center gap-2.5 flex-1 min-w-0" @click.stop>
                                    <button type="button" @click="toggleField('{{ $fid }}')"
                                            class="p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                                            :class="isOpen('{{ $fid }}') ? 'rotate-180' : ''"
                                            title="باز/بستن فیلد">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 uppercase shrink-0">
                                        {{ $field['type'] }}
                                    </span>
                                    <input type="text" wire:model="schema.fields.{{ $i }}.label"
                                           class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 dark:text-white placeholder-gray-400 min-w-0 flex-1"
                                           placeholder="عنوان فیلد (Label)">
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <div class="flex items-center gap-1.5" @click.stop>
                                        <span class="text-[11px] text-gray-400 font-mono">ID:</span>
                                        <input type="text" wire:model="schema.fields.{{ $i }}.id"
                                               class="bg-transparent border-b border-gray-300 text-xs font-mono text-gray-600 focus:border-indigo-500 focus:ring-0 w-24 text-left dir-ltr dark:border-gray-600 dark:text-gray-400 py-0.5"
                                               placeholder="field_id">
                                    </div>

                                    @if(!empty($field['group']))
                                        <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            📁 {{ $field['group'] }}
                                        </span>
                                    @endif

                                    @if(!empty($field['required']))
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                                            الزامی
                                        </span>
                                    @endif

                                    @if(!empty($field['quick_create']))
                                        <span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                            ایجاد سریع
                                        </span>
                                    @endif

                                    {{-- حذف فیلد --}}
                                    <button type="button" wire:click="removeField({{ $i }})" @click.stop
                                            class="text-gray-400 hover:text-red-500 transition-colors p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/30" title="حذف فیلد">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- بدنه فیلد --}}
                            <div x-show="isOpen('{{ $fid }}')"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="p-5 space-y-5">
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
                                    <label
                                        class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                        <input type="checkbox" class="{{ $checkboxClass }}"
                                               wire:model="schema.fields.{{ $i }}.client_auth">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">احراز هویت (پروفایل)</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors dark:border-gray-700 dark:hover:bg-gray-700/30">
                                        <input type="checkbox" class="{{ $checkboxClass }}"
                                               wire:model="schema.fields.{{ $i }}.show_in_registration">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">ثبت نام کاربر</span>
                                    </label>

                                    {{-- عرض فیلد --}}
                                    <div>
                                        <label class="{{ $labelClass }}">عرض فیلد</label>
                                        <select class="{{ $inputClass }} !py-1.5 !text-xs"
                                                wire:model="schema.fields.{{ $i }}.width">
                                            <option value="full">تمام عرض</option>
                                            <option value="1/2">نصف عرض</option>
                                            <option value="1/3">یک‌سوم عرض</option>
                                        </select>
                                    </div>

                                    {{-- گروه --}}
                                    <div>
                                        <label class="{{ $labelClass }}">گروه (بخش)</label>
                                        <input type="text" class="{{ $inputClass }} !py-1.5 !text-xs"
                                               placeholder="مثلاً: اطلاعات تماس" wire:model="schema.fields.{{ $i }}.group">
                                    </div>
                                </div>

                                {{-- placeholder + validation --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="{{ $labelClass }}">Placeholder</label>
                                        <input type="text" class="{{ $inputClass }} !py-1.5 !text-xs"
                                               placeholder="متن راهنمای داخل فیلد" wire:model="schema.fields.{{ $i }}.placeholder">
                                    </div>

                                    @if(in_array($field['type'], ['text','email','number','date','textarea']))
                                        <div>
                                            <label class="{{ $labelClass }}">Validation Rules</label>
                                            <input type="text" class="{{ $inputClass }} !py-1.5 !text-xs"
                                                   placeholder="string|max:255" wire:model="schema.fields.{{ $i }}.validate">
                                        </div>
                                    @endif
                                </div>

                                {{-- تنظیمات اختصاصی بر اساس نوع --}}
                                @if($field['type'] === 'file' || $field['type'] === 'profile-photo')
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-900/30">
                                        <div>
                                            <label class="{{ $labelClass }}">Max Size (MB)</label>
                                            <input type="number" class="{{ $inputClass }}"
                                                   wire:model="schema.fields.{{ $i }}.max_mb">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="{{ $labelClass }}">Allowed Types</label>
                                            <input type="text" class="{{ $inputClass }} dir-ltr"
                                                   placeholder="image/*,application/pdf" wire:model="schema.fields.{{ $i }}.accept">
                                        </div>
                                        <div class="flex items-center gap-2 pt-5">
                                            <input type="checkbox" id="multiple-upload-{{ $i }}"
                                                   wire:model="schema.fields.{{ $i }}.multiple"
                                                   class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer">
                                            <label for="multiple-upload-{{ $i }}" class="text-xs text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                                آپلود چندگانه (Multiple)
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                @if($field['type'] === 'select' || $field['type'] === 'radio' || $field['type'] === 'checkbox')
                                    <div class="space-y-3"
                                         x-data="{
                                             type: '{{ $field['type'] }}',
                                             @if($field['type'] === 'select')
                                             useClientsList: $wire.entangle('schema.fields.{{ $i }}.use_clients_list'),
                                             creatable: $wire.entangle('schema.fields.{{ $i }}.creatable'),
                                             saveGlobally: $wire.entangle('schema.fields.{{ $i }}.save_globally'),
                                             @endif
                                         }">
                                        {{-- گزینه استفاده از لیست clients --}}
                                        @if($field['type'] === 'select')
                                            <div
                                                class="flex items-center gap-2 p-3 rounded-lg bg-indigo-50/50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-900/30">
                                                <input type="checkbox" id="use-clients-{{ $i }}"
                                                       x-model="useClientsList"
                                                       class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer">
                                                <label for="use-clients-{{ $i }}"
                                                       class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none flex-1">
                                                    استفاده از لیست مشتریان (جستجو بر اساس نام، کد ملی، شماره تماس، شماره پرونده)
                                                </label>
                                            </div>
                                        @endif

                                        @if($field['type'] !== 'checkbox')
                                            <div class="space-y-2"
                                                 x-show="type === 'radio' || !useClientsList"
                                                 x-data="{
                                                      optionsJson: $wire.entangle('schema.fields.{{ $i }}.options_json'),
                                                      optionsList: [],
                                                      mode: 'visual',
                                                      init() {
                                                          this.parseJsonToList();
                                                          this.$watch('optionsJson', value => {
                                                              if (this.mode === 'json') {
                                                                  this.parseJsonToList();
                                                              }
                                                          });
                                                      },
                                                      parseJsonToList() {
                                                          try {
                                                              if (!this.optionsJson) {
                                                                  this.optionsList = [];
                                                                  return;
                                                              }
                                                              const parsed = typeof this.optionsJson === 'string' ? JSON.parse(this.optionsJson) : this.optionsJson;
                                                              if (typeof parsed === 'object' && parsed !== null) {
                                                                  this.optionsList = Object.entries(parsed).map(([key, label]) => ({ key, label }));
                                                              } else {
                                                                  this.optionsList = [];
                                                                  const lines = String(this.optionsJson).split('\n');
                                                                  lines.forEach(line => {
                                                                      const parts = line.split(':');
                                                                      if (parts.length >= 2) {
                                                                          this.optionsList.push({ key: parts[0].trim(), label: parts.slice(1).join(':').trim() });
                                                                      } else if (line.trim() !== '') {
                                                                          this.optionsList.push({ key: line.trim(), label: line.trim() });
                                                                      }
                                                                  });
                                                              }
                                                          } catch (e) {
                                                              // Ignore invalid JSON while typing
                                                          }
                                                      },
                                                      updateJsonFromList() {
                                                          const obj = {};
                                                          this.optionsList.forEach(opt => {
                                                              if (opt.key !== '') {
                                                                  obj[opt.key] = opt.label;
                                                              }
                                                          });
                                                          this.optionsJson = JSON.stringify(obj);
                                                      },
                                                      addOption() {
                                                          this.optionsList.push({ key: '', label: '' });
                                                          this.updateJsonFromList();
                                                      },
                                                      removeOption(index) {
                                                          this.optionsList.splice(index, 1);
                                                          this.updateJsonFromList();
                                                      }
                                                  }">

                                                {{-- تب هدر --}}
                                                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                                                    <label class="{{ $labelClass }} !mb-0">
                                                        تنظیمات گزینه‌ها
                                                    </label>
                                                    <div class="flex bg-gray-100 dark:bg-gray-800 p-0.5 rounded-lg text-[10px]">
                                                        <button type="button"
                                                                class="px-2.5 py-1 rounded-md font-medium transition-all"
                                                                :class="mode === 'visual' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                                                @click="mode = 'visual'; parseJsonToList();">
                                                            بصری (ساده)
                                                        </button>
                                                        <button type="button"
                                                                class="px-2.5 py-1 rounded-md font-medium transition-all"
                                                                :class="mode === 'json' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                                                @click="mode = 'json'">
                                                            قالب JSON
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- تب بصری --}}
                                                <div x-show="mode === 'visual'" class="space-y-2">
                                                    <div class="max-h-60 overflow-y-auto pr-1 space-y-2">
                                                        <template x-for="(opt, idx) in optionsList" :key="idx">
                                                            <div class="flex items-center gap-2">
                                                                <div class="flex-1 grid grid-cols-2 gap-2">
                                                                    <div>
                                                                        <input type="text"
                                                                               placeholder="کد/کلید (مثال: male)"
                                                                               class="{{ $inputClass }} !py-1 !text-xs dir-ltr font-mono text-center"
                                                                               x-model="opt.key"
                                                                               @input="updateJsonFromList()">
                                                                    </div>
                                                                    <div>
                                                                        <input type="text"
                                                                               placeholder="برچسب نمایشی (مثال: مرد)"
                                                                               class="{{ $inputClass }} !py-1 !text-xs text-right"
                                                                               x-model="opt.label"
                                                                               @input="updateJsonFromList()">
                                                                    </div>
                                                                </div>
                                                                <button type="button"
                                                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                                                                        @click="removeOption(idx)">
                                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <button type="button"
                                                            class="flex items-center justify-center gap-1.5 w-full py-1.5 px-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/20 transition-all"
                                                            @click="addOption()">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        افزودن گزینه جدید
                                                    </button>
                                                </div>

                                                {{-- تب JSON --}}
                                                <div x-show="mode === 'json'" class="space-y-1">
                                                     <textarea class="{{ $inputClass }} font-mono text-xs dir-ltr" rows="3"
                                                               placeholder='{"m":"مرد", "f":"زن"}'
                                                               x-model="optionsJson"
                                                               @input="(() => { try { JSON.parse(optionsJson) } catch(e) {} })()"></textarea>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- تنظیمات پیشرفته سلکت --}}
                                        @if($field['type'] === 'select')
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div
                                                    class="flex items-center gap-2 p-3 rounded-lg bg-gray-50 border border-gray-100 dark:bg-gray-700/30 dark:border-gray-600">
                                                    <input type="checkbox" id="multiple-{{ $i }}"
                                                           wire:model="schema.fields.{{ $i }}.multiple"
                                                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer">
                                                    <label for="multiple-{{ $i }}"
                                                           class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                                        حالت چند انتخابی (Multiple Selection)
                                                    </label>
                                                </div>

                                                <div
                                                    class="flex items-center gap-2 p-3 rounded-lg bg-gray-50 border border-gray-100 dark:bg-gray-700/30 dark:border-gray-600">
                                                    <input type="checkbox" id="searchable-{{ $i }}"
                                                           wire:model="schema.fields.{{ $i }}.searchable"
                                                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer">
                                                    <label for="searchable-{{ $i }}"
                                                           class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                                        حالت جستجوپذیر (Searchable)
                                                    </label>
                                                </div>

                                                <div
                                                    class="flex items-center gap-2 p-3 rounded-lg bg-gray-50 border border-gray-100 dark:bg-gray-700/30 dark:border-gray-600">
                                                    <input type="checkbox" id="creatable-{{ $i }}"
                                                           x-model="creatable"
                                                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer">
                                                    <label for="creatable-{{ $i }}"
                                                           class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                                        امکان ورود مقدار دلخواه توسط کاربر (Creatable)
                                                    </label>
                                                </div>

                                                <div x-show="creatable"
                                                     class="flex items-center gap-2 p-3 rounded-lg bg-gray-50 border border-gray-100 dark:bg-gray-700/30 dark:border-gray-600 animate-in fade-in duration-200">
                                                    <input type="checkbox" id="save-globally-{{ $i }}"
                                                           x-model="saveGlobally"
                                                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer">
                                                    <label for="save-globally-{{ $i }}"
                                                           class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                                        ذخیره در گزینه‌ها به صورت سراسری (Save Globally)
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($field['type'] === 'select-province-city')
                                    <div
                                        class="flex items-center gap-2 text-sm text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        داده‌های این فیلد به صورت خودکار از لیست استان‌ها و شهرهای ایران بارگذاری می‌شود.
                                    </div>
                                @endif

                                @if($field['type'] === 'select-user-by-role')
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-gray-700/30 dark:border-gray-600">
                                        <div>
                                            <label class="{{ $labelClass }}">نقش</label>
                                            <select class="{{ $inputClass }} !py-1.5 !text-xs dir-ltr"
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
                                                <span class="text-xs text-gray-600 dark:text-gray-400">انتخاب چندگانه
                                        (Multiple)</span>
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
                                                    <input type="checkbox" class="{{ $checkboxClass }} w-3.5 h-3.5"
                                                           wire:model="schema.fields.{{ $i }}.required_status_keys"
                                                           value="{{ $st['key'] }}">
                                                    <span>{{ $st['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">
                                            مثال: برای فیلد «علت لغو» می‌توانید وضعیت «لغو شده» را تیک بزنید تا فقط در آن حالت، پر
                                            کردن فیلد اجباری شود.
                                        </p>
                                    </div>
                                @endif

                                {{-- قوانین شرطی برای الزامی شدن --}}
                                <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                                            الزامی شرطی (اگر فیلد دیگری پر شد)
                                        </p>
                                        <button type="button" wire:click="addConditionalRule({{ $i }})"
                                                class="text-xs px-2 py-1 rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-200 dark:hover:bg-indigo-800/70 transition-colors">
                                            + افزودن قانون
                                        </button>
                                    </div>

                                    @if(!empty($field['conditional_required']))
                                        <div class="space-y-2">
                                            @foreach($field['conditional_required'] as $ruleIdx => $rule)
                                                <div
                                                    class="flex items-center gap-2 p-3 rounded-lg bg-indigo-50/50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-900/30">
                                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                        {{-- فیلد ماشه --}}
                                                        <div>
                                                            <label class="text-[10px] text-gray-600 dark:text-gray-400 mb-1 block">اگر
                                                                فیلد:</label>
                                                            <select class="{{ $inputClass }} !py-1.5 !text-xs"
                                                                    wire:model.live="schema.fields.{{ $i }}.conditional_required.{{ $ruleIdx }}.trigger_field_id">
                                                                <option value="">انتخاب فیلد...</option>
                                                                @foreach($schema['fields'] ?? [] as $otherIdx => $otherField)
                                                                    @if($otherIdx !== $i)
                                                                        <option value="{{ $otherField['id'] ?? '' }}">
                                                                            {{ $otherField['label'] ?? ($otherField['id'] ?? '') }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        {{-- عملگر --}}
                                                        <div>
                                                            <label
                                                                class="text-[10px] text-gray-600 dark:text-gray-400 mb-1 block">شرط:</label>
                                                            <select class="{{ $inputClass }} !py-1.5 !text-xs"
                                                                    wire:model.live="schema.fields.{{ $i }}.conditional_required.{{ $ruleIdx }}.operator">
                                                                <option value="filled">پر شود</option>
                                                                @if(isset($triggerField['required']) && $triggerField['required'])
                                                                    {{-- Do not show empty option if field is required --}}
                                                                @else
                                                                    <option value="empty">خالی باشد</option>
                                                                @endif
                                                                <option value="equals">برابر با</option>
                                                                <option value="not_equals">مخالف با</option>
                                                            </select>
                                                        </div>

                                                        {{-- مقدار (برای equals/not_equals) --}}
                                                        <div>
                                                            <label class="text-[10px] text-gray-600 dark:text-gray-400 mb-1 block">مقدار
                                                                (اختیاری):</label>
                                                            @php
                                                                $triggerFieldId = $rule['trigger_field_id'] ?? '';
                                                                $triggerField = collect($schema['fields'] ?? [])->firstWhere('id', $triggerFieldId);
                                                                $triggerOptions = [];
                                                                if ($triggerField) {
                                                                    if ($triggerFieldId === 'status_id' && !empty($statuses)) {
                                                                        foreach ($statuses as $st) {
                                                                            $triggerOptions[$st['id']] = $st['label'];
                                                                        }
                                                                    } elseif (in_array($triggerField['type'] ?? '', ['select', 'radio']) && !empty($triggerField['options_json'])) {
                                                                        $parsedOpts = json_decode($triggerField['options_json'], true);
                                                                        if (is_array($parsedOpts)) {
                                                                            $triggerOptions = $parsedOpts;
                                                                        } else {
                                                                            $lines = array_filter(array_map('trim', explode("\n", $triggerField['options_json'])));
                                                                            foreach ($lines as $line) {
                                                                                if (str_contains($line, ':')) {
                                                                                    [$okey, $oval] = array_map('trim', explode(':', $line, 2));
                                                                                    $triggerOptions[$okey] = $oval;
                                                                                } else {
                                                                                    $triggerOptions[$line] = $line;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                $isDisabled = !in_array($rule['operator'] ?? 'filled', ['equals', 'not_equals']);
                                                            @endphp

                                                            @if(!empty($triggerOptions))
                                                                <select class="{{ $inputClass }} !py-1.5 !text-xs"
                                                                        wire:model="schema.fields.{{ $i }}.conditional_required.{{ $ruleIdx }}.value"
                                                                    {{ $isDisabled ? 'disabled' : '' }}>
                                                                    <option value="">انتخاب مقدار...</option>
                                                                    @foreach($triggerOptions as $tkey => $tval)
                                                                        <option value="{{ $tkey }}">{{ $tval }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input type="text" class="{{ $inputClass }} !py-1.5 !text-xs"
                                                                       placeholder="فقط برای 'برابر با'"
                                                                       wire:model="schema.fields.{{ $i }}.conditional_required.{{ $ruleIdx }}.value"
                                                                    {{ $isDisabled ? 'disabled' : '' }}>
                                                            @endif
                                                        </div>
                                                    </div>{{-- حذف قانون --}}
                                                    <button type="button" wire:click="removeConditionalRule({{ $i }}, {{ $ruleIdx }})"
                                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                                            title="حذف قانون">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 italic">
                                            هنوز قانونی تعریف نشده است. با کلیک روی «افزودن قانون» می‌توانید قانون جدید اضافه کنید.
                                        </p>
                                    @endif

                                    <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500">
                                        مثال: اگر فیلد «نوع مشتری» برابر با «حقوقی» باشد، فیلد «شماره ثبت» الزامی می‌شود.
                                    </p>
                                </div>

                            </div>
                        </div>
                    @endforeach

                    @if(empty($schema['fields']))
                        <div
                            class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">هنوز فیلدی اضافه نشده است</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                از جعبه ابزار بالا برای افزودن اولین فیلد استفاده کنید.
                            </p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- SortableJS Library --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <style>
        .sortable-chosen {
            background-color: rgb(224 231 255) !important;
        }

        .dark .sortable-chosen {
            background-color: rgb(30 58 138 / 0.3) !important;
        }

        .sortable-chosen-group {
            background-color: rgb(199 210 254) !important;
            border-color: rgb(99 102 241) !important;
        }

        .dark .sortable-chosen-group {
            background-color: rgb(30 58 138 / 0.5) !important;
            border-color: rgb(99 102 241) !important;
        }

        .group-header {
            user-select: none;
        }

        .group-header:hover {
            background-color: rgb(243 244 246);
        }

        .dark .group-header:hover {
            background-color: rgb(55 65 81);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let sortableGroupInstances = []; // برای گروه‌ها
            let sortableFieldInstances = []; // برای فیلدهای داخل گروه‌ها

            // تابع برای راه‌اندازی Sortable
            function initSortable() {
                // حذف نمونه‌های قبلی
                sortableGroupInstances.forEach(instance => {
                    if (instance && instance.destroy) {
                        instance.destroy();
                    }
                });
                sortableGroupInstances = [];

                sortableFieldInstances.forEach(instance => {
                    if (instance && instance.destroy) {
                        instance.destroy();
                    }
                });
                sortableFieldInstances = [];

                // 1. راه‌اندازی Sortable برای گروه‌ها
                const groupsContainer = document.querySelector('.sortable-groups-container');
                if (groupsContainer) {
                    const groupsSortable = Sortable.create(groupsContainer, {
                        animation: 200,
                        handle: '.group-header',
                        ghostClass: 'opacity-50',
                        chosenClass: 'sortable-chosen-group',
                        filter: '.sortable-group, .sortable-field-item', // جلوگیری از drag شدن گروه وقتی فیلد drag می‌شود
                        preventOnFilter: false, // اجازه می‌دهد فیلدها drag شوند
                        onEnd: function(evt) {
                            // فقط اگر header گروه drag شده باشد
                            if (evt.item.classList.contains('sortable-group-item')) {
                                // گرفتن ترتیب جدید گروه‌ها
                                const groupNames = Array.from(groupsContainer.querySelectorAll(
                                    '.sortable-group-item'))
                                    .map(el => el.getAttribute('data-group-name'))
                                    .filter(name => name !== null);

                                // ارسال به Livewire
                                @this.call('reorderGroups', groupNames);
                            }
                        }
                    });
                    sortableGroupInstances.push(groupsSortable);
                }

                // 2. راه‌اندازی Sortable برای فیلدهای داخل هر گروه
                const fieldGroups = document.querySelectorAll('.sortable-group');
                fieldGroups.forEach(group => {
                    const groupName = group.getAttribute('data-group');

                    const fieldSortable = Sortable.create(group, {
                        animation: 150,
                        handle: '.sortable-field-item',
                        ghostClass: 'opacity-50',
                        chosenClass: 'sortable-chosen',
                        group: {
                            name: 'fields-' + (groupName || 'no-group'),
                            pull: false, // فیلدها نمی‌توانند از گروه خارج شوند
                            put: false // فیلدها نمی‌توانند به گروه دیگر منتقل شوند
                        },
                        // جلوگیری از bubble شدن event به والد (گروه)
                        bubbleScroll: false,
                        onStart: function(evt) {
                            // غیرفعال کردن drag گروه وقتی فیلد drag می‌شود
                            if (sortableGroupInstances.length > 0) {
                                sortableGroupInstances.forEach(instance => {
                                    if (instance && instance.option) {
                                        instance.option('disabled', true);
                                    }
                                });
                            }
                        },
                        onEnd: function(evt) {
                            // فعال کردن دوباره drag گروه
                            if (sortableGroupInstances.length > 0) {
                                sortableGroupInstances.forEach(instance => {
                                    if (instance && instance.option) {
                                        instance.option('disabled', false);
                                    }
                                });
                            }

                            // گرفتن ترتیب جدید فیلدها
                            const fieldIds = Array.from(group.querySelectorAll(
                                '[data-field-id]'))
                                .map(el => el.getAttribute('data-field-id'))
                                .filter(id => id !== '');

                            // ارسال به Livewire
                            @this.call('reorderFields', groupName, fieldIds);
                        }
                    });

                    sortableFieldInstances.push(fieldSortable);
                });
            }

            // راه‌اندازی اولیه
            initSortable();

            // گوش دادن به تغییرات Livewire
            let reinitTimeout = null;
            Livewire.hook('morph.updated', ({
                                                component
                                            }) => {
                // اگر در حالت مرتب‌سازی هستیم، دوباره راه‌اندازی کن
                // استفاده از debounce برای جلوگیری از multiple calls
                if (reinitTimeout) {
                    clearTimeout(reinitTimeout);
                }
                reinitTimeout = setTimeout(() => {
                    // بررسی وجود عناصر مرتب‌سازی در DOM
                    const hasSortableGroups = document.querySelectorAll('.sortable-group').length >
                        0;
                    const hasGroupsContainer = document.querySelector(
                        '.sortable-groups-container') !== null;

                    if (hasSortableGroups && hasGroupsContainer) {
                        // همیشه دوباره راه‌اندازی کن چون DOM ممکن است تغییر کرده باشد
                        initSortable();
                    }
                }, 250);
            });

            // گوش دادن به تغییر حالت مرتب‌سازی
            Livewire.on('reorderModeChanged', () => {
                setTimeout(() => {
                    // بررسی وجود عناصر مرتب‌سازی در DOM
                    const hasSortableGroups = document.querySelectorAll('.sortable-group').length >
                        0;

                    if (hasSortableGroups) {
                        // اگر عناصر مرتب‌سازی وجود دارند، راه‌اندازی کن
                        initSortable();
                    } else {
                        // حذف نمونه‌ها هنگام خروج از حالت مرتب‌سازی
                        sortableGroupInstances.forEach(instance => {
                            if (instance && instance.destroy) {
                                instance.destroy();
                            }
                        });
                        sortableGroupInstances = [];

                        sortableFieldInstances.forEach(instance => {
                            if (instance && instance.destroy) {
                                instance.destroy();
                            }
                        });
                        sortableFieldInstances = [];
                    }
                }, 150);
            });
        });
    </script>
</div>
