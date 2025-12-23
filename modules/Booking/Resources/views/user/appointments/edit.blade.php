@extends('layouts.user')

@php
    /** @var \Modules\Booking\Entities\Appointment $appointment */
    $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
    $displayTz = config('booking.timezones.display_default', $scheduleTz);
    $startSchedule = $appointment->start_at_utc?->copy()->timezone($scheduleTz);
    $endSchedule = $appointment->end_at_utc?->copy()->timezone($scheduleTz);
    $dateValue = old('date_local', $startSchedule ? \Morilog\Jalali\Jalalian::fromDateTime($startSchedule)->format('Y/m/d') : '');
    $startTimeValue = old('start_time_local', $startSchedule?->format('H:i'));
    $endTimeValue = old('end_time_local', $endSchedule?->format('H:i'));
    $entryTime = old('entry_time_local', $appointment->entry_at_utc?->copy()->timezone($displayTz)->format('H:i'));
    $exitTime = old('exit_time_local', $appointment->exit_at_utc?->copy()->timezone($displayTz)->format('H:i'));

    $statusOptions = [
        \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'پیش‌نویس',
        \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'در انتظار پرداخت',
        \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'تایید شده',
        \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => 'لغو شده (ادمین)',
        \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => 'لغو شده (مشتری)',
        \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => 'عدم حضور',
        \Modules\Booking\Entities\Appointment::STATUS_DONE => 'انجام شده',
        \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => 'جابجا شده',
    ];
@endphp

@section('content')
    @php
        $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
        $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $cardClass }} p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ویرایش نوبت #{{ $appointment->id }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ویرایش جزئیات نوبت با اعتبارسنجی ظرفیت</p>
            </div>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
               href="{{ route('user.booking.appointments.show', $appointment) }}">
                بازگشت
            </a>
        </div>

        @includeIf('partials.jalali-date-picker')

        <form method="POST" action="{{ route('user.booking.appointments.update', $appointment) }}"
              class="{{ $cardClass }} p-6 space-y-6"
              data-flow="{{ $settings->operator_appointment_flow ?? 'PROVIDER_FIRST' }}"
              data-selected-provider="{{ $appointment->provider_user_id }}"
              data-selected-service="{{ $appointment->service_id }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="{{ $labelClass }}">سرویس</label>
                    <select name="service_id" id="service_id"
                            class="{{ $inputClass }}">
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" @selected((string)old('service_id', $appointment->service_id)===(string)$service->id)>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="{{ $labelClass }}">ارائه‌دهنده</label>
                    <select name="provider_user_id" id="provider_user_id"
                            class="{{ $inputClass }}">
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" @selected((string)old('provider_user_id', $appointment->provider_user_id)===(string)$provider->id)>
                                {{ $provider->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('provider_user_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">مشتری</label>
                    <select name="client_id"
                            class="{{ $inputClass }}">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected((string)old('client_id', $appointment->client_id)===(string)$client->id)>
                                {{ $client->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="{{ $labelClass }}">وضعیت</label>
                    <select name="status"
                            class="{{ $inputClass }}">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $appointment->status)===$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="{{ $labelClass }}">تاریخ (شمسی)</label>
                    <input type="text" name="date_local" value="{{ $dateValue }}" data-jdp
                           class="{{ $inputClass }}">
                    @error('date_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="{{ $labelClass }}">ساعت شروع</label>
                    <input type="text" name="start_time_local" value="{{ $startTimeValue }}" data-jdp-only-time
                           class="{{ $inputClass }}">
                    @error('start_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="{{ $labelClass }}">ساعت پایان</label>
                    <input type="text" name="end_time_local" value="{{ $endTimeValue }}" data-jdp-only-time
                           class="{{ $inputClass }}">
                    @error('end_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            @if($settings->allow_appointment_entry_exit_times)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">زمان ورود</label>
                        <input type="text" name="entry_time_local" value="{{ $entryTime }}" data-jdp-only-time
                               class="{{ $inputClass }}">
                        @error('entry_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">زمان خروج</label>
                        <input type="text" name="exit_time_local" value="{{ $exitTime }}" data-jdp-only-time
                               class="{{ $inputClass }}">
                        @error('exit_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            @endif

            <div>
                <label class="{{ $labelClass }}">یادداشت</label>
                <textarea name="notes" rows="3"
                          class="{{ $inputClass }}">{{ old('notes', $appointment->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                    ذخیره تغییرات
                </button>
            </div>
        </form>
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

            const form = document.querySelector('form[data-flow]');
            if (!form) return;

            const flow = form.dataset.flow || 'PROVIDER_FIRST';
            const serviceSelect = document.getElementById('service_id');
            const providerSelect = document.getElementById('provider_user_id');

            const selectedService = form.dataset.selectedService || '';
            const selectedProvider = form.dataset.selectedProvider || '';

            const buildOptions = (select, items, selectedId) => {
                if (!select) return;
                select.innerHTML = '';
                items.forEach((item) => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name;
                    if (String(item.id) === String(selectedId)) {
                        opt.selected = true;
                    }
                    select.appendChild(opt);
                });
            };

            const fetchProviders = async (serviceId, keepSelected = true) => {
                if (!providerSelect) return;
                const params = new URLSearchParams({
                    service_id: serviceId || '',
                });
                const res = await fetch(`{{ route('user.booking.appointments.wizard.providers') }}?` + params.toString(), {
                    headers: {'Accept': 'application/json'}
                });
                const json = await res.json();
                const providers = json.data || [];
                const current = keepSelected ? providerSelect.value : (selectedProvider || '');
                buildOptions(providerSelect, providers, current);
            };

            const fetchServices = async (providerId, keepSelected = true) => {
                if (!serviceSelect) return;
                const params = new URLSearchParams({
                    provider_id: providerId || '',
                });
                const res = await fetch(`{{ route('user.booking.appointments.wizard.services') }}?` + params.toString(), {
                    headers: {'Accept': 'application/json'}
                });
                const json = await res.json();
                const services = json.data || [];
                const current = keepSelected ? serviceSelect.value : (selectedService || '');
                buildOptions(serviceSelect, services, current);
            };

            if (flow === 'PROVIDER_FIRST' && serviceSelect && providerSelect) {
                if (providerSelect.value) {
                    fetchServices(providerSelect.value);
                }
                providerSelect.addEventListener('change', () => {
                    fetchServices(providerSelect.value, false);
                });
                serviceSelect.addEventListener('change', () => {
                    fetchProviders(serviceSelect.value, false);
                });
            }

            if (flow === 'SERVICE_FIRST' && serviceSelect && providerSelect) {
                if (serviceSelect.value) {
                    fetchProviders(serviceSelect.value);
                }
                serviceSelect.addEventListener('change', () => {
                    fetchProviders(serviceSelect.value, false);
                });
                providerSelect.addEventListener('change', () => {
                    fetchServices(providerSelect.value, false);
                });
            }
        });
    </script>
@endsection
