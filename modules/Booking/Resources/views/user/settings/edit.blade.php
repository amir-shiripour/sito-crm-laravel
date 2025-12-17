@extends('layouts.user')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">تنظیمات نوبت‌دهی</h1>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 rounded text-green-700">{{ session('success') }}</div>
    @endif

{{-- Include Jalali date picker styles and scripts once --}}
@includeIf('partials.jalali-date-picker')

<form method="POST" action="{{ route('user.booking.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">واحد پول</label>
                <select name="currency_unit" class="w-full border rounded p-2">
                    <option value="IRR" @selected(old('currency_unit', $settings->currency_unit)==='IRR')>IRR</option>
                    <option value="IRT" @selected(old('currency_unit', $settings->currency_unit)==='IRT')>IRT</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">فعال بودن رزرو آنلاین</label>
                <select name="global_online_booking_enabled" class="w-full border rounded p-2">
                    <option value="1" @selected((string)old('global_online_booking_enabled', (int)$settings->global_online_booking_enabled)==='1')>بله</option>
                    <option value="0" @selected((string)old('global_online_booking_enabled', (int)$settings->global_online_booking_enabled)==='0')>خیر</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">مرحله اول ثبت نوبت (اپراتور)</label>
                <select name="operator_appointment_flow" class="w-full border rounded p-2">
                    <option value="PROVIDER_FIRST" @selected(old('operator_appointment_flow', $settings->operator_appointment_flow)==='PROVIDER_FIRST')>
                        اول ارائه‌دهنده
                    </option>
                    <option value="SERVICE_FIRST" @selected(old('operator_appointment_flow', $settings->operator_appointment_flow)==='SERVICE_FIRST')>
                        اول سرویس
                    </option>
                </select>
            </div>


            <div>
                <label class="block text-sm mb-1">مدت هر اسلات (دقیقه)</label>
                <input type="number" name="default_slot_duration_minutes" class="w-full border rounded p-2" value="{{ old('default_slot_duration_minutes', $settings->default_slot_duration_minutes) }}" required>
            </div>

            <div>
                <label class="block text-sm mb-1">ظرفیت هر اسلات</label>
                <input type="number" name="default_capacity_per_slot" class="w-full border rounded p-2" value="{{ old('default_capacity_per_slot', $settings->default_capacity_per_slot) }}" required>
            </div>

            <div>
                <label class="block text-sm mb-1">ظرفیت روزانه (اختیاری)</label>
                <input type="number" name="default_capacity_per_day" class="w-full border rounded p-2" value="{{ old('default_capacity_per_day', $settings->default_capacity_per_day) }}">
            </div>

            <div>
                <label class="block text-sm mb-1">اجازه ساخت سرویس توسط نقش‌ها</label>
                <select name="allow_role_service_creation" class="w-full border rounded p-2">
                    <option value="1" @selected((string)old('allow_role_service_creation', (int)$settings->allow_role_service_creation)==='1')>بله</option>
                    <option value="0" @selected((string)old('allow_role_service_creation', (int)$settings->allow_role_service_creation)==='0')>خیر</option>
                </select>
                <div class="text-xs text-gray-500 mt-1">
                    در صورت انتخاب «بله»، نقش‌های تعیین‌شده در بخش زیر علاوه بر این‌که به‌عنوان <strong>ارائه‌دهنده</strong> شناخته می‌شوند،
                    مجوز <strong>مشاهده و ایجاد سرویس</strong> را نیز دریافت می‌کنند.
                </div>
            </div>


            {{-- انتخاب نقش‌ها برای ایجاد سرویس (چندانتخابی) --}}
            <div class="md:col-span-2">
                <label class="block text-sm mb-1">نقش‌های ارائه‌دهنده / مجاز برای ایجاد سرویس</label>
                <select name="allowed_roles[]" multiple class="w-full border rounded p-2">
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
                <div class="text-xs text-gray-500 mt-1">
                    کاربران دارای این نقش‌ها به‌عنوان <strong>ارائه‌دهنده</strong> در سیستم درنظر گرفته می‌شوند.
                    اگر گزینهٔ بالا روی «بله» باشد، همین نقش‌ها مجوز <strong>ایجاد و مشاهده سرویس‌ها</strong> را نیز دریافت می‌کنند.
                </div>
            </div>


            <div>
                <label class="block text-sm mb-1">Scope مدیریت دسته‌ها</label>
                <select name="category_management_scope" class="w-full border rounded p-2">
                    <option value="ALL" @selected(old('category_management_scope', $settings->category_management_scope)==='ALL')>ALL</option>
                    <option value="OWN" @selected(old('category_management_scope', $settings->category_management_scope)==='OWN')>OWN</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">Scope مدیریت فرم‌ها</label>
                <select name="form_management_scope" class="w-full border rounded p-2">
                    <option value="ALL" @selected(old('form_management_scope', $settings->form_management_scope)==='ALL')>ALL</option>
                    <option value="OWN" @selected(old('form_management_scope', $settings->form_management_scope)==='OWN')>OWN</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">Scope انتخاب دسته در سرویس</label>
                <select name="service_category_selection_scope" class="w-full border rounded p-2">
                    <option value="ALL" @selected(old('service_category_selection_scope', $settings->service_category_selection_scope)==='ALL')>ALL</option>
                    <option value="OWN" @selected(old('service_category_selection_scope', $settings->service_category_selection_scope)==='OWN')>OWN</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">Scope انتخاب فرم در سرویس</label>
                <select name="service_form_selection_scope" class="w-full border rounded p-2">
                    <option value="ALL" @selected(old('service_form_selection_scope', $settings->service_form_selection_scope)==='ALL')>ALL</option>
                    <option value="OWN" @selected(old('service_form_selection_scope', $settings->service_form_selection_scope)==='OWN')>OWN</option>
                </select>
            </div>

        </div>

        {{-- دکمه ذخیره در انتهای فرم (بعد از برنامه زمانی) قرار می‌گیرد --}}
        {{--
            برنامه زمانی سراسری (GLOBAL schedule)
            این بخش برای هر روز هفته اجازه می‌دهد زمان‌های باز بودن، بسته بودن، مدت اسلات و ظرفیت‌ها را تنظیم کنید.
            نام فیلدها به صورت rules[weekday][field] هستند تا در کنترلر به راحتی پردازش شوند.
        --}}
        <div class="mt-8">
            <h2 class="text-lg font-bold mb-4">برنامه زمانی سراسری</h2>
            @php
                $dayNames = [
                    0 => 'شنبه',
                    1 => 'یکشنبه',
                    2 => 'دوشنبه',
                    3 => 'سه‌شنبه',
                    4 => 'چهارشنبه',
                    5 => 'پنج‌شنبه',
                    6 => 'جمعه',
                ];
            @endphp
            <div class="space-y-6">
                @for($d = 0; $d <= 6; $d++)
                    @php
                        $r = $rules[$d] ?? null;
                        // fallback values
                        $isClosed = old('rules.'.$d.'.is_closed', ($r?->is_closed ?? false) ? '1' : '0');
                        $start    = old('rules.'.$d.'.work_start_local', $r?->work_start_local);
                        $end      = old('rules.'.$d.'.work_end_local', $r?->work_end_local);
                        $dur      = old('rules.'.$d.'.slot_duration_minutes', $r?->slot_duration_minutes);
                        $capSlot  = old('rules.'.$d.'.capacity_per_slot', $r?->capacity_per_slot);
                        $capDay   = old('rules.'.$d.'.capacity_per_day', $r?->capacity_per_day);
                        // breaks_json در DB به صورت آرایه‌ای از اشیاء {start_local,end_local}
                        $breaksArray = [];
                        if (old('rules.'.$d.'.breaks')) {
                            $breaksArray = old('rules.'.$d.'.breaks');
                        } elseif ($r?->breaks_json) {
                            $breaksArray = is_array($r->breaks_json) ? $r->breaks_json : json_decode($r->breaks_json, true);
                        }
                    @endphp
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <h3 class="font-semibold mb-3 text-gray-800 dark:text-gray-100">{{ $dayNames[$d] ?? ('Day '.$d) }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm mb-1">روز تعطیل؟</label>
                                <select name="rules[{{ $d }}][is_closed]" class="w-full border rounded p-2">
                                    <option value="0" @selected($isClosed == '0')>خیر</option>
                                    <option value="1" @selected($isClosed == '1')>بله</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm mb-1">شروع</label>
                                <input type="text" data-jdp-only-time name="rules[{{ $d }}][work_start_local]" class="w-full border rounded p-2" value="{{ $start }}" placeholder="مثلاً 09:00">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">پایان</label>
                                <input type="text" data-jdp-only-time name="rules[{{ $d }}][work_end_local]" class="w-full border rounded p-2" value="{{ $end }}" placeholder="مثلاً 17:00">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">مدت اسلات (دقیقه)</label>
                                <input type="number" name="rules[{{ $d }}][slot_duration_minutes]" class="w-full border rounded p-2" value="{{ $dur }}" min="5" step="5" placeholder="{{ $settings->default_slot_duration_minutes ?? 30 }}">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">ظرفیت هر اسلات</label>
                                <input type="number" name="rules[{{ $d }}][capacity_per_slot]" class="w-full border rounded p-2" value="{{ $capSlot }}" min="1" placeholder="{{ $settings->default_capacity_per_slot ?? 1 }}">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">ظرفیت روزانه</label>
                                <input type="number" name="rules[{{ $d }}][capacity_per_day]" class="w-full border rounded p-2" value="{{ $capDay }}" min="0" placeholder="{{ $settings->default_capacity_per_day }}">
                            </div>
                        </div>
                        {{-- استراحت‌ها --}}
                        <div class="mt-4" id="breaks-{{ $d }}">
                            <label class="block text-sm mb-1">استراحت‌ها</label>
                            @if(is_array($breaksArray) && count($breaksArray))
                                @foreach($breaksArray as $i => $br)
                                    <div class="flex items-center gap-2 break-row mb-2">
                                        <input type="text" data-jdp-only-time name="rules[{{ $d }}][breaks][{{ $i }}][start_local]" class="w-full border rounded p-2" value="{{ $br['start_local'] ?? '' }}" placeholder="شروع">
                                        <span>تا</span>
                                        <input type="text" data-jdp-only-time name="rules[{{ $d }}][breaks][{{ $i }}][end_local]" class="w-full border rounded p-2" value="{{ $br['end_local'] ?? '' }}" placeholder="پایان">
                                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 text-xl">&times;</button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" onclick="addBreak({{ $d }})" class="mt-2 inline-flex items-center gap-1 px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                            + افزودن استراحت
                        </button>
                    </div>
                @endfor
            </div>
            {{-- Save button after schedule section --}}
            <div class="mt-6">
                <button class="px-4 py-2 bg-blue-600 text-white rounded">ذخیره</button>
            </div>
        </div>
    </form>
</div>
@endsection

{{-- اسکریپت اضافه کردن استراحت‌ها و راه‌اندازی مجدد جلالی دیت پیکر --}}
<script>
    function addBreak(day) {
        const container = document.getElementById('breaks-' + day);
        const index = container.querySelectorAll('.break-row').length;
        const row = document.createElement('div');
        row.classList.add('flex', 'items-center', 'gap-2', 'break-row', 'mb-2');
        row.innerHTML = `
            <input type="text" data-jdp-only-time name="rules[${day}][breaks][${index}][start_local]" class="w-full border rounded p-2" placeholder="شروع">
            <span>تا</span>
            <input type="text" data-jdp-only-time name="rules[${day}][breaks][${index}][end_local]" class="w-full border rounded p-2" placeholder="پایان">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 text-xl">&times;</button>
        `;
        container.appendChild(row);
        // re-init jalali datepicker for new time inputs
        if (window.jalaliDatepicker) {
            // Rewatch all time-only inputs
            jalaliDatepicker.startWatch({ selector: '[data-jdp-only-time]', hasSecond: false });
        }
    }
</script>
