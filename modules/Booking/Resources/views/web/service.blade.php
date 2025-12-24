@extends('layouts.guest')

@section('content')
    <div class="max-w-4xl mx-auto p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $service->name }}</h1>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
               href="{{ route('booking.public.index') }}">بازگشت</a>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-2">
            <div><span class="text-gray-500 text-sm">قیمت:</span> {{ number_format($service->base_price) }}</div>
            <div><span class="text-gray-500 text-sm">پرداخت:</span> {{ $service->payment_mode }}</div>
            <div><span class="text-gray-500 text-sm">رزرو آنلاین:</span> {{ $service->online_booking_mode }}</div>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-100 text-emerald-700 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @includeIf('partials.jalali-date-picker')

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b font-medium bg-gray-50 text-gray-900">رزرو آنلاین</div>
            <div class="p-4 space-y-4">
                @if(!$settings->global_online_booking_enabled)
                    <div class="text-sm text-rose-600">رزرو آنلاین در حال حاضر غیرفعال است.</div>
                @else
                    <form method="POST" action="{{ route('booking.public.book', $service) }}" class="space-y-6" id="online-booking-form">
                        @csrf
                        <input type="hidden" name="start_at_utc" id="start_at_utc" value="{{ old('start_at_utc') }}">
                        <input type="hidden" name="end_at_utc" id="end_at_utc" value="{{ old('end_at_utc') }}">
                        <input type="hidden" name="date_local" id="date_local" value="{{ old('date_local') }}">

                        <div class="space-y-2">
                            <div class="text-sm text-gray-600">مرحله ۱: انتخاب ارائه‌دهنده</div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">ارائه‌دهنده</label>
                                <select name="provider_user_id" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900">
                                    @foreach($service->serviceProviders->where('is_active', true) as $sp)
                                        <option value="{{ $sp->provider_user_id }}" @selected(old('provider_user_id')==$sp->provider_user_id)>
                                            {{ optional($sp->provider)->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('provider_user_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="text-sm text-gray-600">مرحله ۲: انتخاب تاریخ (شمسی)</div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="font-semibold text-sm text-gray-800">تقویم نوبت‌دهی</div>
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                class="px-2 py-1 border rounded border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                                                id="calendar-prev">ماه قبل</button>
                                        <div class="text-sm text-gray-700" id="calendar-label"></div>
                                        <button type="button"
                                                class="px-2 py-1 border rounded border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                                                id="calendar-next">ماه بعد</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-7 gap-2 text-xs" dir="rtl" id="calendar-grid"></div>
                                <div class="text-xs text-rose-600" id="calendar-error"></div>
                            </div>
                            @error('date_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="space-y-2">
                            <div class="text-sm text-gray-600">مرحله ۳: انتخاب اسلات</div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">اسلات‌های موجود</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2" id="slots-container"></div>
                                <div class="text-xs text-gray-500 mt-2" id="slots-empty">ابتدا تاریخ را انتخاب کنید.</div>
                                @error('start_at_utc')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">انتخاب شده</label>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700" id="slot-selected">
                                    اسلاتی انتخاب نشده است.
                                </div>
                            </div>
                        </div>

                        @if($service->appointmentForm && is_array($service->appointmentForm->schema_json))
                            <div class="space-y-2">
                                <div class="text-sm text-gray-600">مرحله ۴: تکمیل فرم</div>
                                <div class="space-y-4">
                                    @foreach($service->appointmentForm->schema_json as $field)
                                        @if(empty($field['name'])) @continue @endif
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-2">
                                                {{ $field['label'] ?? $field['name'] }}
                                                @if(!empty($field['required'])) <span class="text-red-500">*</span> @endif
                                            </label>
                                            @if(($field['type'] ?? 'text') === 'textarea')
                                                <textarea name="form_data[{{ $field['name'] }}]" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900">{{ old('form_data.'.$field['name']) }}</textarea>
                                            @elseif(($field['type'] ?? 'text') === 'select')
                                                <select name="form_data[{{ $field['name'] }}]" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900">
                                                    <option value="">انتخاب کنید...</option>
                                                    @foreach($field['options'] ?? [] as $opt)
                                                        <option value="{{ $opt }}" @selected(old('form_data.'.$field['name']) == $opt)>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="{{ $field['type'] ?? 'text' }}" name="form_data[{{ $field['name'] }}]" value="{{ old('form_data.'.$field['name']) }}"
                                                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900">
                                            @endif
                                            @error('form_data.'.$field['name'])<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @php
                            $clientMode = \Modules\Clients\Entities\ClientSetting::getValue('auth.mode', 'password');
                            $client = auth('client')->user();
                        @endphp

                        <div class="space-y-2">
                            <div class="text-sm text-gray-600">مرحله ۵: اطلاعات مشتری</div>
                            @if($client)
                                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 text-sm text-gray-700">
                                    رزرو برای: <span class="font-semibold">{{ $client->full_name }}</span>
                                    <span class="text-xs text-gray-500">({{ $client->phone ?? 'بدون شماره' }})</span>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-2">نام و نام خانوادگی</label>
                                        <input type="text" name="full_name" value="{{ old('full_name') }}"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900">
                                        @error('full_name')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-2">شماره تماس</label>
                                        <input type="text" name="phone" value="{{ old('phone') }}"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900">
                                        @error('phone')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                @if($clientMode === 'password')
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-2">رمز عبور</label>
                                        <input type="password" name="password"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900">
                                        @error('password')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="flex items-center justify-end">
                            <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                                ثبت نوبت
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('online-booking-form');
            const providerSelect = form?.querySelector('select[name="provider_user_id"]');
            const slotsContainer = document.getElementById('slots-container');
            const slotsEmpty = document.getElementById('slots-empty');
            const startInput = document.getElementById('start_at_utc');
            const endInput = document.getElementById('end_at_utc');
            const slotSelected = document.getElementById('slot-selected');
            const dateInput = document.getElementById('date_local');
            const calendarGrid = document.getElementById('calendar-grid');
            const calendarLabel = document.getElementById('calendar-label');
            const calendarPrev = document.getElementById('calendar-prev');
            const calendarNext = document.getElementById('calendar-next');
            const calendarError = document.getElementById('calendar-error');

            const weekDays = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
            const persianMonths = [
                'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
            ];

            // Initialize with server-provided Jalali date if available, otherwise fallback to JS date
            let calendarYear = {{ $currentJalali['year'] ?? 'new Date().getFullYear()' }};
            let calendarMonth = {{ $currentJalali['month'] ?? 'new Date().getMonth() + 1' }};

            let calendarDays = [];
            const initialDate = dateInput?.value || '';
            if (initialDate) {
                // If there's an old input (e.g. validation error), try to parse it.
                // Note: date_local might be Gregorian if it came from a previous post.
                // But for calendar navigation, we stick to the current view year/month.
            }

            const clearSlots = (message = 'ابتدا تاریخ را انتخاب کنید.') => {
                slotsContainer.innerHTML = '';
                slotsEmpty.textContent = message;
                slotsEmpty.classList.remove('hidden');
                startInput.value = '';
                endInput.value = '';
                slotSelected.textContent = 'اسلاتی انتخاب نشده است.';
            };

            const renderCalendar = () => {
                if (!calendarGrid) return;
                calendarGrid.innerHTML = '';

                // Use manual array for month name to avoid Gregorian conversion issues
                const monthName = persianMonths[calendarMonth - 1] || 'نامشخص';
                calendarLabel.textContent = `${monthName} ${calendarYear}`;

                weekDays.forEach((w) => {
                    const div = document.createElement('div');
                    div.className = 'text-center text-[11px] font-semibold text-gray-500 py-1';
                    div.textContent = w;
                    calendarGrid.appendChild(div);
                });

                if (!calendarDays.length) return;
                const first = calendarDays[0].local_date;
                const firstDate = new Date(first + 'T00:00:00');
                const persianWeekdayIndex = (firstDate.getDay() + 1) % 7;

                for (let i = 0; i < persianWeekdayIndex; i++) {
                    const empty = document.createElement('div');
                    empty.className = 'h-[52px]';
                    calendarGrid.appendChild(empty);
                }

                calendarDays.forEach((day) => {
                    const cell = document.createElement('button');
                    cell.type = 'button';
                    const isSelected = dateInput.value === day.local_date;
                    const isDisabled = day.is_closed || !day.has_available_slots;
                    cell.className = 'w-full h-[52px] border rounded-lg p-2 text-center text-xs';
                    if (isSelected) {
                        cell.classList.add('border-indigo-600', 'bg-indigo-50', 'text-indigo-700');
                    } else if (isDisabled) {
                        cell.classList.add('border-gray-200', 'bg-gray-100', 'text-gray-400');
                    } else {
                        cell.classList.add('border-gray-200', 'bg-white', 'text-gray-700', 'hover:bg-gray-50');
                    }
                    cell.disabled = isDisabled;
                    const num = new Date(day.local_date + 'T00:00:00').toLocaleDateString('fa-IR-u-ca-persian', { day: 'numeric' });
                    cell.innerHTML = `<div class=\"font-semibold\">${num}</div>`;
                    cell.addEventListener('click', () => {
                        dateInput.value = day.local_date;
                        fetchSlots();
                        renderCalendar();
                    });
                    calendarGrid.appendChild(cell);
                });
            };

            const fetchCalendar = async () => {
                if (!providerSelect?.value) return;
                calendarError.textContent = '';
                const params = new URLSearchParams({
                    provider_user_id: providerSelect.value,
                    year: calendarYear,
                    month: calendarMonth,
                });
                try {
                    const res = await fetch(`{{ route('booking.public.calendar', $service) }}?` + params.toString(), {
                        headers: {'Accept': 'application/json'}
                    });
                    const json = await res.json();
                    calendarDays = json.data || [];
                    renderCalendar();
                } catch (e) {
                    calendarError.textContent = 'خطا در دریافت تقویم.';
                }
            };

            const renderSlots = (slots) => {
                slotsContainer.innerHTML = '';
                if (!slots.length) {
                    slotsEmpty.textContent = 'اسلات خالی برای این روز یافت نشد.';
                    slotsEmpty.classList.remove('hidden');
                    return;
                }
                slotsEmpty.classList.add('hidden');
                slots.forEach((slot) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'border rounded px-2 py-2 text-xs text-center hover:bg-indigo-50';
                    // Use start_time if available (formatted by backend), else fallback to parsing ISO
                    const timeLabel = slot.start_time ? slot.start_time : slot.start_at_view.split(' ')[1].substring(0, 5);
                    btn.textContent = timeLabel;

                    btn.addEventListener('click', () => {
                        startInput.value = slot.start_at_utc;
                        endInput.value = slot.end_at_utc;

                        const sTime = slot.start_time || slot.start_at_view.split(' ')[1].substring(0, 5);
                        const eTime = slot.end_time || slot.end_at_view.split(' ')[1].substring(0, 5);

                        slotSelected.textContent = `از ${sTime} تا ${eTime}`;
                        slotsContainer.querySelectorAll('button').forEach((b) => {
                            b.classList.remove('border-indigo-600', 'bg-indigo-50', 'text-indigo-700');
                        });
                        btn.classList.add('border-indigo-600', 'bg-indigo-50', 'text-indigo-700');
                    });
                    slotsContainer.appendChild(btn);
                });
            };

            const fetchSlots = async () => {
                if (!providerSelect?.value || !dateInput?.value) {
                    clearSlots();
                    return;
                }

                clearSlots('در حال دریافت اسلات‌ها...');
                const params = new URLSearchParams({
                    provider_user_id: providerSelect.value,
                    date_local: dateInput.value,
                });
                try {
                    const res = await fetch(`{{ route('booking.public.slots', $service) }}?` + params.toString(), {
                        headers: {'Accept': 'application/json'}
                    });
                    const json = await res.json();
                    renderSlots(json.data || []);
                    if (startInput.value && endInput.value) {
                        // Optional: Restore selection visual state if needed
                    }
                } catch (e) {
                    clearSlots('خطا در دریافت اسلات‌ها.');
                }
            };

            providerSelect?.addEventListener('change', () => {
                dateInput.value = '';
                clearSlots();
                fetchCalendar();
            });
            calendarPrev?.addEventListener('click', () => {
                calendarMonth -= 1;
                if (calendarMonth < 1) {
                    calendarMonth = 12;
                    calendarYear -= 1;
                }
                fetchCalendar();
            });
            calendarNext?.addEventListener('click', () => {
                calendarMonth += 1;
                if (calendarMonth > 12) {
                    calendarMonth = 1;
                    calendarYear += 1;
                }
                fetchCalendar();
            });

            fetchCalendar();
            if (dateInput?.value) {
                fetchSlots();
            }
        });
    </script>
@endsection
