@extends('layouts.user')

@php
    // کلاس‌های استایل مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </span>
                    تنظیمات نوبت‌دهی
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    پیکربندی واحد پول، قوانین رزرو، نقش‌ها و برنامه زمانی هفتگی
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-2 animate-in fade-in slide-in-from-top-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                {{ session('success') }}
            </div>
        @endif

        @includeIf('partials.jalali-date-picker')

        <form method="POST" action="{{ route('user.booking.settings.update') }}" class="space-y-8 pb-20">
            @csrf

            {{-- کارت ۱: تنظیمات عمومی --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></span>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">تنظیمات پایه و مالی</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">واحد پول</label>
                        <div class="relative">
                            <select name="currency_unit" class="{{ $selectClass }}">
                                <option value="IRR" @selected(old('currency_unit', $settings->currency_unit)==='IRR')>ریال (IRR)</option>
                                <option value="IRT" @selected(old('currency_unit', $settings->currency_unit)==='IRT')>تومان (IRT)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">وضعیت رزرو آنلاین</label>
                        <div class="relative">
                            <select name="global_online_booking_enabled" class="{{ $selectClass }}">
                                <option value="1" @selected((string)old('global_online_booking_enabled', (int)$settings->global_online_booking_enabled)==='1')>فعال (مجاز)</option>
                                <option value="0" @selected((string)old('global_online_booking_enabled', (int)$settings->global_online_booking_enabled)==='0')>غیرفعال (بسته)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">جریان ثبت نوبت (اپراتور)</label>
                        <div class="relative">
                            <select name="operator_appointment_flow" class="{{ $selectClass }}">
                                <option value="PROVIDER_FIRST" @selected(old('operator_appointment_flow', $settings->operator_appointment_flow)==='PROVIDER_FIRST')>
                                    ابتدا انتخاب ارائه‌دهنده
                                </option>
                                <option value="SERVICE_FIRST" @selected(old('operator_appointment_flow', $settings->operator_appointment_flow)==='SERVICE_FIRST')>
                                    ابتدا انتخاب سرویس
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- کارت ۲: تنظیمات ظرفیت و زمان --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">پیش‌فرض‌های زمان و ظرفیت</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">مدت هر اسلات (دقیقه)</label>
                        <input type="number" name="default_slot_duration_minutes" class="{{ $inputClass }} text-center dir-ltr"
                               value="{{ old('default_slot_duration_minutes', $settings->default_slot_duration_minutes) }}" required>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">ظرفیت هر اسلات</label>
                        <input type="number" name="default_capacity_per_slot" class="{{ $inputClass }} text-center dir-ltr"
                               value="{{ old('default_capacity_per_slot', $settings->default_capacity_per_slot) }}" required>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">ظرفیت کل روز (اختیاری)</label>
                        <input type="number" name="default_capacity_per_day" class="{{ $inputClass }} text-center dir-ltr"
                               value="{{ old('default_capacity_per_day', $settings->default_capacity_per_day) }}">
                    </div>
                </div>
            </div>

            {{-- کارت ۳: دسترسی‌ها و نقش‌ها --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">دسترسی‌ها و نقش‌ها</h2>
                </div>
                <div class="p-6 space-y-6">

                    <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 rounded-xl p-4 flex flex-col md:flex-row gap-4 items-start md:items-center">
                        <div class="flex-1">
                            <label class="text-sm font-bold text-gray-900 dark:text-white">اجازه ساخت سرویس توسط نقش‌ها</label>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                در صورت فعال‌سازی، نقش‌های انتخاب شده در پایین، علاوه بر <span class="font-bold text-gray-700 dark:text-gray-300">ارائه‌دهنده بودن</span>، امکان <span class="font-bold text-gray-700 dark:text-gray-300">تعریف سرویس جدید</span> را نیز خواهند داشت.
                            </p>
                        </div>
                        <div class="w-full md:w-48">
                            <div class="relative">
                                <select name="allow_role_service_creation" class="{{ $selectClass }}">
                                    <option value="1" @selected((string)old('allow_role_service_creation', (int)$settings->allow_role_service_creation)==='1')>بله (مجاز)</option>
                                    <option value="0" @selected((string)old('allow_role_service_creation', (int)$settings->allow_role_service_creation)==='0')>خیر (محدود)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">نقش‌های ارائه‌دهنده / مجاز</label>
                        <select name="allowed_roles[]" multiple class="{{ $inputClass }} h-32">
                            @php
                                $selectedRoles = old('allowed_roles', $settings->allowed_roles ?? []);
                                if (is_string($selectedRoles)) {
                                    $decoded = json_decode($selectedRoles, true);
                                    $selectedRoles = is_array($decoded) ? $decoded : [];
                                }
                            @endphp
                            @foreach($roles ?? [] as $role)
                                <option value="{{ $role->id }}" {{ in_array($role->id, $selectedRoles) ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-2">
                            برای انتخاب چند مورد، کلید Ctrl (ویندوز) یا Command (مک) را نگه دارید.
                        </p>
                    </div>
                </div>
            </div>

            {{-- کارت ۴: تنظیمات Scope --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <span class="w-2 h-2 rounded-full bg-purple-500 shadow-[0_0_8px_rgba(168,85,247,0.6)]"></span>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">محدوده دسترسی (Scopes)</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach([
                        'category_management_scope' => 'مدیریت دسته‌ها',
                        'form_management_scope' => 'مدیریت فرم‌ها',
                        'service_category_selection_scope' => 'انتخاب دسته در سرویس',
                        'service_form_selection_scope' => 'انتخاب فرم در سرویس'
                    ] as $field => $label)
                        <div>
                            <label class="{{ $labelClass }}">{{ $label }}</label>
                            <div class="relative">
                                <select name="{{ $field }}" class="{{ $selectClass }}">
                                    <option value="ALL" @selected(old($field, $settings->$field)==='ALL')>همه (Global)</option>
                                    <option value="OWN" @selected(old($field, $settings->$field)==='OWN')>شخصی (Own)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- کارت ۵: برنامه زمانی --}}
            <div class="space-y-4">
                <div class="flex items-center gap-2 mb-2">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">برنامه زمانی سراسری</h2>
                    <span class="px-2 py-0.5 rounded text-[10px] bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">Global Schedule</span>
                </div>

                @php
                    $dayNames = [
                        0 => 'شنبه', 1 => 'یکشنبه', 2 => 'دوشنبه',
                        3 => 'سه‌شنبه', 4 => 'چهارشنبه', 5 => 'پنج‌شنبه', 6 => 'جمعه',
                    ];
                @endphp

                <div class="grid grid-cols-1 gap-4">
                    @for($d = 0; $d <= 6; $d++)
                        @php
                            $r = $rules[$d] ?? null;
                            $isClosed = old('rules.'.$d.'.is_closed', ($r?->is_closed ?? false) ? '1' : '0');
                            $start    = old('rules.'.$d.'.work_start_local', $r?->work_start_local);
                            $end      = old('rules.'.$d.'.work_end_local', $r?->work_end_local);
                            $dur      = old('rules.'.$d.'.slot_duration_minutes', $r?->slot_duration_minutes);
                            $capSlot  = old('rules.'.$d.'.capacity_per_slot', $r?->capacity_per_slot);
                            $capDay   = old('rules.'.$d.'.capacity_per_day', $r?->capacity_per_day);

                            $breaksArray = [];
                            if (old('rules.'.$d.'.breaks')) {
                                $breaksArray = old('rules.'.$d.'.breaks');
                            } elseif ($r?->breaks_json) {
                                $breaksArray = is_array($r->breaks_json) ? $r->breaks_json : json_decode($r->breaks_json, true);
                            }
                        @endphp

                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden transition-all hover:border-indigo-300 dark:hover:border-indigo-700 shadow-sm"
                             x-data="{ isOpen: '{{ $isClosed }}' === '0' }">

                            {{-- هدر روز --}}
                            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 flex items-center justify-center rounded-lg font-bold text-sm {{ $isClosed == '1' ? 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400' }}">
                                        {{ $d + 1 }}
                                    </span>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $dayNames[$d] ?? ('Day '.$d) }}</h3>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium" :class="isOpen ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500'">
                                        <span x-text="isOpen ? 'باز است' : 'تعطیل'"></span>
                                    </span>
                                    {{-- سوئیچ تعطیلی --}}
                                    <select name="rules[{{ $d }}][is_closed]"
                                            x-model="isOpen"
                                            x-on:change="isOpen = $event.target.value === '0'"
                                            class="h-8 rounded-lg border-gray-300 bg-white text-xs focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 cursor-pointer">
                                        <option value="0">باز</option>
                                        <option value="1">تعطیل</option>
                                    </select>
                                </div>
                            </div>

                            {{-- محتوای روز --}}
                            <div x-show="isOpen" x-transition class="p-4 space-y-4">
                                {{-- ردیف اول: زمان و تنظیمات --}}
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-1">شروع کار</label>
                                        <input type="text" data-jdp-only-time name="rules[{{ $d }}][work_start_local]"
                                               class="{{ $inputClass }} text-center dir-ltr" value="{{ $start }}" placeholder="09:00">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-1">پایان کار</label>
                                        <input type="text" data-jdp-only-time name="rules[{{ $d }}][work_end_local]"
                                               class="{{ $inputClass }} text-center dir-ltr" value="{{ $end }}" placeholder="17:00">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-1">مدت اسلات (دقیقه)</label>
                                        <input type="number" name="rules[{{ $d }}][slot_duration_minutes]"
                                               class="{{ $inputClass }} text-center" value="{{ $dur }}" placeholder="{{ $settings->default_slot_duration_minutes }}">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-1">ظرفیت اسلات</label>
                                        <input type="number" name="rules[{{ $d }}][capacity_per_slot]"
                                               class="{{ $inputClass }} text-center" value="{{ $capSlot }}" placeholder="{{ $settings->default_capacity_per_slot }}">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-1">ظرفیت روز</label>
                                        <input type="number" name="rules[{{ $d }}][capacity_per_day]"
                                               class="{{ $inputClass }} text-center" value="{{ $capDay }}" placeholder="{{ $settings->default_capacity_per_day }}">
                                    </div>
                                </div>

                                {{-- ردیف دوم: استراحت‌ها --}}
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-xs font-bold text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            زمان‌های استراحت (Breaks)
                                        </label>
                                        <button type="button" onclick="addBreak({{ $d }})"
                                                class="text-[10px] px-2 py-1 rounded bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 transition-colors">
                                            + افزودن استراحت
                                        </button>
                                    </div>

                                    <div id="breaks-{{ $d }}" class="space-y-2">
                                        @if(is_array($breaksArray) && count($breaksArray))
                                            @foreach($breaksArray as $i => $br)
                                                <div class="flex items-center gap-2 break-row bg-gray-50 dark:bg-gray-900/30 p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                                                    <span class="text-xs text-gray-400">از</span>
                                                    <input type="text" data-jdp-only-time name="rules[{{ $d }}][breaks][{{ $i }}][start_local]"
                                                           class="w-20 h-8 rounded-lg border-gray-200 bg-white text-center text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                                                           value="{{ $br['start_local'] ?? '' }}" placeholder="شروع">
                                                    <span class="text-xs text-gray-400">تا</span>
                                                    <input type="text" data-jdp-only-time name="rules[{{ $d }}][breaks][{{ $i }}][end_local]"
                                                           class="w-20 h-8 rounded-lg border-gray-200 bg-white text-center text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                                                           value="{{ $br['end_local'] ?? '' }}" placeholder="پایان">
                                                    <button type="button" onclick="this.closest('.break-row').remove()"
                                                            class="mr-auto p-1.5 text-red-500 hover:bg-red-50 rounded-lg dark:hover:bg-red-900/20 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-[10px] text-gray-400 italic py-1 px-2">بدون زمان استراحت</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- دکمه ذخیره شناور یا ثابت پایین --}}
            <div class="sticky bottom-4 z-40 flex justify-end">
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <button type="submit"
                            class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95">
                        ذخیره تنظیمات
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- اسکریپت‌ها --}}
    <script>
        function addBreak(day) {
            const container = document.getElementById('breaks-' + day);
            // حذف پیام "بدون زمان استراحت" اگر وجود دارد
            const emptyMsg = container.querySelector('.italic');
            if(emptyMsg) emptyMsg.remove();

            const index = container.querySelectorAll('.break-row').length + Date.now(); // برای یونیک بودن ایندکس در جاوااسکریپت
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 break-row bg-gray-50 dark:bg-gray-900/30 p-2 rounded-lg border border-gray-100 dark:border-gray-700 animate-in fade-in slide-in-from-right-2';

            row.innerHTML = `
                <span class="text-xs text-gray-400">از</span>
                <input type="text" data-jdp-only-time name="rules[${day}][breaks][${index}][start_local]"
                       class="w-20 h-8 rounded-lg border-gray-200 bg-white text-center text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                       placeholder="شروع">
                <span class="text-xs text-gray-400">تا</span>
                <input type="text" data-jdp-only-time name="rules[${day}][breaks][${index}][end_local]"
                       class="w-20 h-8 rounded-lg border-gray-200 bg-white text-center text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                       placeholder="پایان">
                <button type="button" onclick="this.closest('.break-row').remove()"
                        class="mr-auto p-1.5 text-red-500 hover:bg-red-50 rounded-lg dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            `;
            container.appendChild(row);

            // Re-init Jalali Datepicker if available
            if (window.jalaliDatepicker) {
                jalaliDatepicker.startWatch({ selector: '[data-jdp-only-time]', hasSecond: false });
            }
        }
    </script>
@endsection
