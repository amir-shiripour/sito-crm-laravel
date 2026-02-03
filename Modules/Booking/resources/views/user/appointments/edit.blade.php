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
        \Modules\Booking\Entities\Appointment::STATUS_PENDING => 'در انتظار تایید',
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

    <div class="space-y-6" x-data="editAppointment()">
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
              data-selected-service="{{ $appointment->service_id }}"
              @submit="handleSubmit">
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

                {{-- Client Selector with Search --}}
                <div class="md:col-span-2 relative" @click.outside="closeResults()">
                    <label class="{{ $labelClass }}">مشتری</label>
                    <div class="relative">
                        <input type="text"
                               class="{{ $inputClass }} pl-10"
                               placeholder="جستجو نام، شماره تماس، کد ملی..."
                               x-model="clientSearch"
                               @input.debounce.300ms="fetchClients()"
                               @focus="showResults = true"
                               autocomplete="off">

                        <input type="hidden" name="client_id" x-model="clientId">

                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <button type="button"
                                x-show="clientId"
                                @click="clearClient()"
                                class="absolute inset-y-0 left-8 pl-2 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Selected Client Display --}}
                    <div x-show="clientId && !showResults" class="mt-2 p-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-xl flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-indigo-900 dark:text-indigo-100" x-text="selectedClientName"></div>
                            <div class="text-xs text-indigo-600 dark:text-indigo-300 mt-0.5" x-text="selectedClientDetail"></div>
                        </div>
                        <button type="button" @click="clearClient()" class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-200">
                            <span class="text-xs">تغییر</span>
                        </button>
                    </div>

                    {{-- Dropdown Results --}}
                    <div x-show="showResults && (clients.length > 0 || loading)"
                         x-transition.opacity
                         class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-200 dark:border-gray-700">

                        <template x-if="loading">
                            <div class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-center">
                                در حال جستجو...
                            </div>
                        </template>

                        <template x-for="client in clients" :key="client.id">
                            <div @click="selectClient(client)"
                                 class="cursor-pointer select-none relative py-2 pl-3 pr-4 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-900 dark:text-gray-100 transition-colors border-b border-gray-100 dark:border-gray-700 last:border-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-medium block truncate" x-text="client.full_name"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">
                                            <span x-show="client.phone" x-text="client.phone"></span>
                                            <span x-show="client.national_code" x-text="' | ' + client.national_code"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="!loading && clients.length === 0">
                             <div class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-center">
                                موردی یافت نشد
                            </div>
                        </template>
                    </div>
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

            <div class="border-t border-gray-100 dark:border-gray-700 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">فرم اطلاعات نوبت</h2>
                </div>
                <input type="hidden" name="appointment_form_response_json" id="appointment_form_response_json">
                <div id="appointment-form-container" class="space-y-4"></div>
                <div id="appointment-form-empty" class="text-xs text-gray-500 dark:text-gray-400 hidden">
                    برای این سرویس فرم اختصاصی تعریف نشده است.
                </div>
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
        function editAppointment() {
            return {
                clientSearch: '',
                clientId: '{{ old('client_id', $appointment->client_id) }}',
                clients: [],
                loading: false,
                showResults: false,
                selectedClientName: '{{ optional($appointment->client)->full_name }}',
                selectedClientDetail: '{{ optional($appointment->client)->phone }}',

                init() {
                    // If we have an initial client but no name (e.g. validation error with new ID), fetch it
                    if (this.clientId && !this.selectedClientName) {
                        // Ideally we would fetch single client, but search works too
                        // Or we rely on server-side rendering for initial state which is safer
                    }
                },

                async fetchClients() {
                    if (!this.clientSearch) {
                        this.clients = [];
                        return;
                    }

                    this.loading = true;
                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.clients') }}?q=${this.clientSearch}`, {
                            headers: {'Accept': 'application/json'}
                        });
                        const json = await res.json();
                        this.clients = json.data || [];
                        this.showResults = true;
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },

                selectClient(client) {
                    this.clientId = client.id;
                    this.selectedClientName = client.full_name;
                    this.selectedClientDetail = [client.phone, client.national_code].filter(Boolean).join(' | ');
                    this.clientSearch = '';
                    this.showResults = false;
                },

                clearClient() {
                    this.clientId = '';
                    this.selectedClientName = '';
                    this.selectedClientDetail = '';
                    this.clientSearch = '';
                    // Focus search input next tick
                    setTimeout(() => {
                        this.$el.querySelector('input[placeholder*="جستجو"]').focus();
                    }, 50);
                },

                closeResults() {
                    this.showResults = false;
                },

                handleSubmit() {
                    // Form submission logic handled by standard form submit,
                    // but we can add extra validation here if needed
                    const formJsonInput = document.getElementById('appointment_form_response_json');
                    if (formJsonInput && typeof collectFormValues === 'function') {
                         formJsonInput.value = JSON.stringify(collectFormValues());
                    }
                }
            }
        }

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
            const formContainer = document.getElementById('appointment-form-container');
            const formEmpty = document.getElementById('appointment-form-empty');
            const formJsonInput = document.getElementById('appointment_form_response_json');

            const selectedService = form.dataset.selectedService || '';
            const selectedProvider = form.dataset.selectedProvider || '';
            const initialFormValues = @json($appointment->appointment_form_response_json ?? []);
            const serviceFormMap = @json($services->mapWithKeys(fn ($s) => [$s->id => $s->appointment_form_id])->all());

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

            const renderFormField = (field, value) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'space-y-1';
                const label = document.createElement('label');
                label.className = 'block text-xs text-gray-600 dark:text-gray-300';
                label.textContent = field.label || field.name;
                wrapper.appendChild(label);

                const type = field.type || 'text';
                if (type === 'textarea') {
                    const textarea = document.createElement('textarea');
                    textarea.className = 'w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400';
                    textarea.rows = 3;
                    textarea.placeholder = field.placeholder || '';
                    textarea.value = value ?? '';
                    if (field.required) textarea.required = true;
                    textarea.dataset.fieldName = field.name;
                    wrapper.appendChild(textarea);
                    return wrapper;
                }

                if (type === 'select') {
                    const select = document.createElement('select');
                    select.className = 'w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100';
                    if (field.required) select.required = true;
                    select.dataset.fieldName = field.name;
                    const emptyOpt = document.createElement('option');
                    emptyOpt.value = '';
                    emptyOpt.textContent = 'انتخاب کنید';
                    select.appendChild(emptyOpt);
                    (field.options || []).forEach((opt) => {
                        const option = document.createElement('option');
                        option.value = opt;
                        option.textContent = opt;
                        if (value === opt) option.selected = true;
                        select.appendChild(option);
                    });
                    wrapper.appendChild(select);
                    return wrapper;
                }

                if (type === 'radio') {
                    const container = document.createElement('div');
                    container.className = 'flex flex-wrap gap-3';
                    (field.options || []).forEach((opt) => {
                        const labelWrap = document.createElement('label');
                        labelWrap.className = 'inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200';
                        const input = document.createElement('input');
                        input.type = 'radio';
                        input.name = `form_${field.name}`;
                        input.value = opt;
                        if (field.required) input.required = true;
                        if (value === opt) input.checked = true;
                        input.dataset.fieldName = field.name;
                        labelWrap.appendChild(input);
                        const span = document.createElement('span');
                        span.textContent = opt;
                        labelWrap.appendChild(span);
                        container.appendChild(labelWrap);
                    });
                    wrapper.appendChild(container);
                    return wrapper;
                }

                if (type === 'checkbox') {
                    const container = document.createElement('div');
                    container.className = 'flex flex-wrap gap-3';
                    const currentValues = Array.isArray(value) ? value : [];
                    (field.options || []).forEach((opt) => {
                        const labelWrap = document.createElement('label');
                        labelWrap.className = 'inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200';
                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.value = opt;
                        input.dataset.fieldName = field.name;
                        if (currentValues.includes(opt)) input.checked = true;
                        labelWrap.appendChild(input);
                        const span = document.createElement('span');
                        span.textContent = opt;
                        labelWrap.appendChild(span);
                        container.appendChild(labelWrap);
                    });
                    wrapper.appendChild(container);
                    return wrapper;
                }

                const input = document.createElement('input');
                input.type = type;
                input.className = 'w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400';
                input.placeholder = field.placeholder || '';
                input.value = value ?? '';
                if (field.required) input.required = true;
                input.dataset.fieldName = field.name;
                wrapper.appendChild(input);
                return wrapper;
            };

            // Expose globally for Alpine to call
            window.collectFormValues = () => {
                const values = {};
                formContainer.querySelectorAll('[data-field-name]').forEach((el) => {
                    const name = el.dataset.fieldName;
                    if (!name) return;
                    if (el.type === 'checkbox') {
                        if (!Array.isArray(values[name])) values[name] = [];
                        if (el.checked) values[name].push(el.value);
                        return;
                    }
                    if (el.type === 'radio') {
                        if (el.checked) values[name] = el.value;
                        return;
                    }
                    values[name] = el.value;
                });
                return values;
            };

            const loadAppointmentForm = async (serviceId) => {
                const formId = serviceFormMap?.[serviceId] || null;
                if (!formId) {
                    formContainer.innerHTML = '';
                    formEmpty?.classList.remove('hidden');
                    return;
                }
                const params = new URLSearchParams({ form_id: formId });
                const res = await fetch(`{{ route('user.booking.appointments.wizard.form') }}?` + params.toString(), {
                    headers: {'Accept': 'application/json'}
                });
                const json = await res.json();
                const schema = json.data?.schema_json || null;
                formContainer.innerHTML = '';
                if (!schema || !Array.isArray(schema.fields) || schema.fields.length === 0) {
                    formEmpty?.classList.remove('hidden');
                    return;
                }
                formEmpty?.classList.add('hidden');
                schema.fields.forEach((field) => {
                    const value = initialFormValues[field.name];
                    formContainer.appendChild(renderFormField(field, value));
                });
            };

            // Initial load logic
            if (flow === 'PROVIDER_FIRST' && serviceSelect && providerSelect) {
                providerSelect.addEventListener('change', () => {
                    fetchServices(providerSelect.value, false);
                });
                serviceSelect.addEventListener('change', () => {
                    loadAppointmentForm(serviceSelect.value);
                });
            }

            if (flow === 'SERVICE_FIRST' && serviceSelect && providerSelect) {
                serviceSelect.addEventListener('change', () => {
                    fetchProviders(serviceSelect.value, false);
                    loadAppointmentForm(serviceSelect.value);
                });
            }

            if (serviceSelect && serviceSelect.value) {
                loadAppointmentForm(serviceSelect.value);
            }
        });
    </script>
@endsection
