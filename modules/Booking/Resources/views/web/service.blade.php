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
                    <form method="POST" action="{{ route('booking.public.book', $service) }}" class="space-y-4" id="online-booking-form">
                        @csrf
                        <input type="hidden" name="start_at_utc" id="start_at_utc">
                        <input type="hidden" name="end_at_utc" id="end_at_utc">

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

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">تاریخ (شمسی)</label>
                                <input type="text" name="date_local" value="{{ old('date_local') }}" data-jdp id="date_local"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900">
                                @error('date_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                            </div>
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

                        @php
                            $clientMode = \Modules\Clients\Entities\ClientSetting::getValue('auth.mode', 'password');
                            $client = auth('client')->user();
                        @endphp

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
            if (window.jalaliDatepicker) {
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp]',
                    hasSecond: false
                });
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp-only-time]',
                    hasSecond: false
                });
            }

            const form = document.getElementById('online-booking-form');
            const providerSelect = form?.querySelector('select[name="provider_user_id"]');
            const dateInput = document.getElementById('date_local');
            const slotsContainer = document.getElementById('slots-container');
            const slotsEmpty = document.getElementById('slots-empty');
            const startInput = document.getElementById('start_at_utc');
            const endInput = document.getElementById('end_at_utc');
            const slotSelected = document.getElementById('slot-selected');

            const clearSlots = (message = 'ابتدا تاریخ را انتخاب کنید.') => {
                slotsContainer.innerHTML = '';
                slotsEmpty.textContent = message;
                slotsEmpty.classList.remove('hidden');
                startInput.value = '';
                endInput.value = '';
                slotSelected.textContent = 'اسلاتی انتخاب نشده است.';
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
                    btn.textContent = slot.start_at_view ? slot.start_at_view.split(' ')[1] : slot.start_at_utc;
                    btn.addEventListener('click', () => {
                        startInput.value = slot.start_at_utc;
                        endInput.value = slot.end_at_utc;
                        slotSelected.textContent = `از ${slot.start_at_view} تا ${slot.end_at_view}`;
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
                } catch (e) {
                    clearSlots('خطا در دریافت اسلات‌ها.');
                }
            };

            providerSelect?.addEventListener('change', fetchSlots);
            dateInput?.addEventListener('change', fetchSlots);

            if (dateInput?.value) {
                fetchSlots();
            }
        });
    </script>
@endsection
