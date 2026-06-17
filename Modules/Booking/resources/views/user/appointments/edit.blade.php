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

    $client = $appointment->client;
    $clientDetails = [];
    if ($client) {
        if ($client->phone) $clientDetails[] = $client->phone;
        if ($client->national_code) $clientDetails[] = 'کد ملی: ' . $client->national_code;
        if ($client->case_number) $clientDetails[] = 'پرونده: ' . $client->case_number;
    }
    $clientDetailsStr = implode(' | ', $clientDetails);
@endphp

@section('content')
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
        .tooth-selected g text{
            color: #1d4ed8 !important;
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
                <!-- Service Selector -->
                <div>
                    <label class="{{ $labelClass }}">سرویس</label>
                    <div class="relative" @click.outside="closeServiceResults()">
                        <select name="service_id" id="service_id" class="hidden">
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected((string)old('service_id', $appointment->service_id)===(string)$service->id)>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                        <div @click="showServiceResults = !showServiceResults; if(showServiceResults) { $nextTick(() => $refs.serviceSearchInput.focus()); }"
                             class="{{ $inputClass }} flex items-center justify-between cursor-pointer select-none">
                            <span x-text="selectedServiceName || 'انتخاب سرویس...'" :class="!serviceId ? 'text-gray-400 font-normal' : 'text-gray-900 dark:text-gray-100 font-medium'"></span>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 shrink-0" :class="showServiceResults ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div x-show="showServiceResults"
                             x-transition
                             class="absolute right-0 top-full z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-hidden flex flex-col">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <input type="text"
                                       x-ref="serviceSearchInput"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                       placeholder="جستجو سرویس..."
                                       x-model="serviceSearch"
                                       autocomplete="off">
                            </div>
                            <div class="overflow-y-auto flex-1 max-h-48">
                                <template x-for="service in filteredServicesList" :key="service.id">
                                    <div @click="selectService(service)"
                                         :class="String(serviceId) === String(service.id) ? 'bg-indigo-50 dark:bg-indigo-900/30 font-semibold text-indigo-650 dark:text-indigo-400' : 'text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                         class="cursor-pointer select-none py-2.5 px-4 text-xs transition-colors border-b border-gray-100 dark:border-gray-700/50 last:border-0 flex items-center justify-between">
                                        <span x-text="service.name"></span>
                                        <span x-show="String(serviceId) === String(service.id)">
                                              <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                          </span>
                                    </div>
                                </template>
                                <template x-if="filteredServicesList.length === 0">
                                    <div class="py-3 text-center text-xs text-gray-500 dark:text-gray-400">سرویسی یافت نشد</div>
                                </template>
                            </div>
                        </div>
                    </div>
                    @error('service_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>

                <!-- Provider Selector -->
                <div>
                    <label class="{{ $labelClass }}">{{ config('booking.labels.provider') }}</label>
                    <div class="relative" @click.outside="closeProviderResults()">
                        <select name="provider_user_id" id="provider_user_id" class="hidden">
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}" @selected((string)old('provider_user_id', $appointment->provider_user_id)===(string)$provider->id)>
                                    {{ $provider->name }}
                                </option>
                            @endforeach
                        </select>
                        <div @click="showProviderResults = !showProviderResults; if(showProviderResults) { $nextTick(() => $refs.providerSearchInput.focus()); }"
                             class="{{ $inputClass }} flex items-center justify-between cursor-pointer select-none">
                            <span x-text="selectedProviderName || 'انتخاب ارائه‌دهنده...'" :class="!providerId ? 'text-gray-400 font-normal' : 'text-gray-900 dark:text-gray-100 font-medium'"></span>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 shrink-0" :class="showProviderResults ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div x-show="showProviderResults"
                             x-transition
                             class="absolute right-0 top-full z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-hidden flex flex-col">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <input type="text"
                                       x-ref="providerSearchInput"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                       placeholder="جستجو ارائه‌دهنده..."
                                       x-model="providerSearch"
                                       autocomplete="off">
                            </div>
                            <div class="overflow-y-auto flex-1 max-h-48">
                                <template x-for="provider in filteredProvidersList" :key="provider.id">
                                    <div @click="selectProvider(provider)"
                                         :class="String(providerId) === String(provider.id) ? 'bg-indigo-50 dark:bg-indigo-900/30 font-semibold text-indigo-650 dark:text-indigo-400' : 'text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                         class="cursor-pointer select-none py-2.5 px-4 text-xs transition-colors border-b border-gray-100 dark:border-gray-700/50 last:border-0 flex items-center justify-between">
                                        <span x-text="provider.name"></span>
                                        <span x-show="String(providerId) === String(provider.id)">
                                              <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                          </span>
                                    </div>
                                </template>
                                <template x-if="filteredProvidersList.length === 0">
                                    <div class="py-3 text-center text-xs text-gray-500 dark:text-gray-400">موردی یافت نشد</div>
                                </template>
                            </div>
                        </div>
                    </div>
                    @error('provider_user_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>

                <!-- Client Selector -->
                <div class="md:col-span-2 relative" @click.outside="closeResults()">
                    <label class="{{ $labelClass }}">مشتری</label>

                    <!-- Search Input (Visible when no client is selected) -->
                    <div x-show="!clientId" class="relative">
                        <input type="text"
                               class="{{ $inputClass }} pl-10"
                               placeholder="جستجو نام، شماره تماس، کد ملی، شماره پرونده..."
                               x-model="clientSearch"
                               @input.debounce.300ms="fetchClients()"
                               @focus="showResults = true"
                               autocomplete="off">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Selected Client Display Card (Visible when client is selected) -->
                    <div x-show="clientId" class="mt-1 p-3 bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800 rounded-xl flex items-center justify-between hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all shadow-sm">
                        <div class="space-y-1">
                            <div class="text-sm font-semibold text-indigo-900 dark:text-indigo-200" x-text="selectedClientName"></div>
                            <div class="text-xs text-indigo-650/80 dark:text-indigo-400/80 mt-0.5" x-text="selectedClientDetail"></div>
                        </div>
                        <button type="button"
                                @click="clearClient()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-100/60 dark:bg-indigo-900/40 text-indigo-650 dark:text-indigo-300 text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-all">
                            <span>تغییر</span>
                        </button>
                    </div>

                    <input type="hidden" name="client_id" x-model="clientId">

                    <!-- Search Results Dropdown -->
                    <div x-show="showResults && !clientId"
                         x-transition
                         class="absolute right-0 top-full z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto py-1">
                        <template x-if="loading">
                            <div class="py-3 text-center text-xs text-gray-500 dark:text-gray-400">در حال جستجو...</div>
                        </template>
                        <template x-for="client in clients" :key="client.id">
                            <div @click="selectClient(client)"
                                 class="cursor-pointer select-none py-2.5 px-4 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-900 dark:text-gray-100 transition-colors border-b border-gray-100 dark:border-gray-700/50 last:border-0 flex items-center justify-between">
                                <div>
                                    <div class="font-medium" x-text="client.full_name"></div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        <span x-show="client.phone" x-text="client.phone"></span>
                                        <span x-show="client.national_code" x-text="' | کد ملی: ' + client.national_code"></span>
                                        <span x-show="client.case_number" x-text="' | پرونده: ' + client.case_number"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!loading && clients.length === 0">
                            <div class="py-3 text-center text-xs text-gray-500 dark:text-gray-400" x-text="clientSearch ? 'کلاینتی یافت نشد' : 'برای جستجو تایپ کنید...'"></div>
                        </template>
                    </div>

                    @error('client_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>

                <!-- Status Selector -->
                <div>
                    <label class="{{ $labelClass }}">وضعیت</label>
                    <div class="relative" @click.outside="closeStatusResults()">
                        <select name="status" class="hidden" x-model="status">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div @click="showStatusResults = !showStatusResults"
                             class="{{ $inputClass }} flex items-center justify-between cursor-pointer select-none">
                            <span x-text="statusOptions[status] || 'انتخاب وضعیت...'" class="text-gray-900 dark:text-gray-100 font-medium"></span>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 shrink-0" :class="showStatusResults ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div x-show="showStatusResults"
                             x-transition
                             class="absolute right-0 top-full z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto py-1">
                            <template x-for="(label, key) in statusOptions" :key="key">
                                <div @click="selectStatus(key)"
                                     :class="String(status) === String(key) ? 'bg-indigo-50 dark:bg-indigo-900/30 font-semibold text-indigo-650 dark:text-indigo-400' : 'text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                     class="cursor-pointer select-none py-2.5 px-4 text-xs transition-colors border-b border-gray-100 dark:border-gray-700/50 last:border-0 flex items-center justify-between">
                                    <span x-text="label"></span>
                                    <span x-show="String(status) === String(key)">
                                          <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                      </span>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('status')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="{{ $labelClass }}">تاریخ (شمسی)</label>
                    <input type="text" name="date_local" value="{{ $dateValue }}" data-jdp-only-date
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
                <input type="hidden" name="appointment_form_response_json" id="appointment_form_response_json" value="{{ json_encode($appointment->appointment_form_response_json ?? new \stdClass) }}">
                <div id="appointment-form-container" class="space-y-4"></div>
                <div id="dental-chart-editor-container" class="hidden bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mx-auto"
                     x-data="{
                          selectedTeeth: [],
                          preset: 'none',
                          upperJawIds: [1,2,3,4,5,6,7,8,9,10,11,12,13,14],
                          lowerJawIds: [15,16,17,18,19,20,21,22,23,24,25,26,27,28],
                          clientToothPlans: {},
                          get clientId() {
                              if (this.$parent) return this.$parent.clientId;
                              const parentEl = document.getElementById('appointment-form-container')?.closest('[x-data]');
                              if (parentEl && window.Alpine) {
                                  try {
                                      return Alpine.$data(parentEl).clientId;
                                  } catch (e) {}
                              }
                              return '';
                          },
                          init() {
                              const el = document.getElementById('appointment-form-response-json') || document.getElementById('appointment_form_response_json');
                              const raw = el ? el.value : '{}';
                              try {
                                  const parsed = JSON.parse(raw || '{}');
                                  let teeth = parsed.tooth_numbers;
                                  if (!teeth) {
                                      for (const key in parsed) {
                                          if (Array.isArray(parsed[key])) {
                                              teeth = parsed[key];
                                              break;
                                          } else if (typeof parsed[key] === 'string' && parsed[key].includes(',')) {
                                              teeth = parsed[key].split(',').map(Number);
                                              break;
                                          }
                                      }
                                  }
                                  if (Array.isArray(teeth)) {
                                      this.selectedTeeth = teeth.map(Number);
                                  } else if (typeof teeth === 'string' && teeth.trim() !== '') {
                                      this.selectedTeeth = teeth.split(',').map(Number);
                                  }
                              } catch(e) {}

                              this.$watch(() => this.clientId, () => this.fetchClientPlans());
                              this.$nextTick(() => {
                                  if (this.clientId) {
                                      this.fetchClientPlans();
                                  }
                              });
                          },
                          async fetchClientPlans() {
                              if (!this.clientId) {
                                  this.clientToothPlans = {};
                                  return;
                              }
                              try {
                                  const res = await fetch(`/user/booking/cure/client-plans/${this.clientId}`, {
                                      headers: { 'Accept': 'application/json' }
                                  });
                                  const json = await res.json();
                                  if (json.success) {
                                      this.clientToothPlans = json.tooth_treatments || {};
                                  } else {
                                      this.clientToothPlans = {};
                                  }
                              } catch(e) {
                                  console.error('Failed to fetch client plans:', e);
                                  this.clientToothPlans = {};
                              }
                          },
                          selectJaw(type) {
                              if (this.preset === type) { this.resetTeeth(); return; }
                              this.preset = type;
                              this.selectedTeeth = type === 'upper' ? [...this.upperJawIds] : [...this.lowerJawIds];
                          },
                          selectAllTeeth() {
                              if (this.preset === 'all') { this.resetTeeth(); return; }
                              this.preset = 'all';
                              this.selectedTeeth = [...this.upperJawIds, ...this.lowerJawIds];
                          },
                          resetTeeth() {
                              this.selectedTeeth = [];
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
                          get groupedTeeth() {
                              const sorted = [...this.selectedTeeth].sort((a, b) => {
                                  const posOrder = { 'UR': 1, 'UL': 2, 'LR': 3, 'LL': 4 };
                                  return posOrder[this.getToothLabel(a).pos] - posOrder[this.getToothLabel(b).pos];
                              });
                              const groups = { 'UR': [], 'UL': [], 'LR': [], 'LL': [] };
                              sorted.forEach(t => groups[this.getToothLabel(t).pos].push(t));
                              return Object.entries(groups).filter(([key, val]) => val.length > 0);
                          },
                          toggle(id) {
                              const idx = this.selectedTeeth.indexOf(id);
                              if (idx > -1) {
                                  this.selectedTeeth.splice(idx, 1);
                              } else {
                                  this.selectedTeeth.push(id);
                              }
                              this.preset = 'none';
                          },
                          is(id) {
                              let cls = this.selectedTeeth.includes(id) ? 'tooth-path tooth-selected' : 'tooth-path tooth-unselected';
                              if (this.clientToothPlans && this.clientToothPlans[id]) {
                                  cls += ' !stroke-emerald-500 !stroke-[2.5px]';
                              }
                              return cls;
                          }
                      }">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex-wrap gap-3 bg-gray-50/50 dark:bg-gray-900/10">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 rounded-full bg-rose-500 shrink-0"></span>
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-sm">نقشه دندانی</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectJaw('upper')"
                                    :class="preset==='upper' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک بالا</button>
                            <button type="button" @click="selectJaw('lower')"
                                    :class="preset==='lower' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک پایین</button>
                            <button type="button" @click="selectAllTeeth()"
                                    :class="preset==='all' ? 'bg-violet-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">همه</button>
                            <button type="button" @click="resetTeeth()"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-600
                                             hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-400 transition-all">
                                پاک‌سازی
                            </button>
                        </div>
                    </div>
                    <div class="px-4 pt-4 pb-1 relative">
                        <div class="absolute top-6 left-6 z-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur
                                      px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                            <span class="text-[10px] text-gray-400 uppercase font-bold block">انتخاب</span>
                            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400"
                                  x-text="selectedTeeth.length"></span>
                        </div>
                        <x-booking::dental-chart/>
                    </div>
                    <div class="px-5 py-3 flex flex-wrap gap-1.5 min-h-12 border-t border-gray-50
                                  dark:border-gray-700/50 bg-gray-50/60 dark:bg-gray-900/20">
                        <div class="flex flex-wrap items-center gap-2">
                            <template x-for="([pos, teeth], idx) in groupedTeeth" :key="pos">
                                <div class="flex items-center" :class="idx !== groupedTeeth.length - 1 ? 'border-l-2 border-gray-400 dark:border-gray-500 pl-2 ml-1' : ''">
                                    <template x-for="t in teeth" :key="t">
                                        <div role="button"
                                             @click="toggle(t)"
                                             class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-solid cursor-pointer"
                                             :class="getQuadrantClasses(t)"
                                             x-text="getToothLabel(t).num">
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <template x-if="selectedTeeth.length === 0">
                              <span class="text-xs text-gray-400 dark:text-gray-500 self-center">
                                  روی دندان کلیک کنید تا انتخاب شود
                              </span>
                        </template>
                    </div>
                    {{-- Client Treatment Plan Helper --}}
                    <template x-if="Object.keys(clientToothPlans || {}).length > 0">
                        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-indigo-50/30 dark:bg-indigo-950/10 space-y-3">
                            <div class="flex items-center gap-2 text-indigo-850 dark:text-indigo-300">
                                <span class="text-sm">💡</span>
                                <span class="text-xs font-bold">طرح درمان‌های فعال کلاینت (برای انتخاب سریع کلیک کنید):</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(treatments, toothId) in clientToothPlans" :key="toothId">
                                    <button type="button"
                                            @click="toggle(Number(toothId))"
                                            :class="(selectedTeeth || []).includes(Number(toothId)) ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-300 dark:ring-indigo-800' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                            class="inline-flex flex-col items-start p-2.5 rounded-xl text-right transition-all hover:scale-[1.02] active:scale-[0.98] min-w-[140px] border border-transparent shadow-sm">
                                        <div class="flex items-center justify-between w-full">
                                            <span class="text-xs font-black" x-text="`دندان ${getToothLabel(Number(toothId)).num} (${getToothLabel(Number(toothId)).pos})`"></span>
                                            <span class="w-2 h-2 rounded-full" :class="treatments[0].status === 'confirmed' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                        </div>
                                        <div class="mt-1 text-[10px] opacity-90 line-clamp-1 text-gray-500 dark:text-gray-400" :class="(selectedTeeth || []).includes(Number(toothId)) ? '!text-indigo-100' : ''" x-text="treatments.map(t => t.service_name).join('، ')"></div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                    <input type="hidden" data-field-name="tooth_numbers" :value="selectedTeeth.join(',')">
                </div>
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
                selectedClientDetail: '{{ $clientDetailsStr }}',

                // Services properties
                serviceId: '{{ old('service_id', $appointment->service_id) }}',
                selectedServiceName: '{{ optional($appointment->service)->name }}',
                serviceSearch: '',
                services: @json($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name])),
                showServiceResults: false,
                serviceLoading: false,

                // Providers properties
                providerId: '{{ old('provider_user_id', $appointment->provider_user_id) }}',
                selectedProviderName: '{{ optional($appointment->provider)->name }}',
                providerSearch: '',
                providers: @json($providers->map(fn($p) => ['id' => $p->id, 'name' => $p->name])),
                showProviderResults: false,
                providerLoading: false,

                // Status properties
                status: '{{ old('status', $appointment->status) }}',
                statusOptions: @json($statusOptions),
                showStatusResults: false,

                init() {
                    const serviceSelect = document.getElementById('service_id');
                    if (serviceSelect) {
                        const opts = Array.from(serviceSelect.options).map(o => ({ id: o.value, name: o.textContent.trim() }));
                        if (opts.length > 0) this.services = opts;
                    }
                    const providerSelect = document.getElementById('provider_user_id');
                    if (providerSelect) {
                        const opts = Array.from(providerSelect.options).map(o => ({ id: o.value, name: o.textContent.trim() }));
                        if (opts.length > 0) this.providers = opts;
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
                    this.selectedClientDetail = [
                        client.phone,
                        client.national_code ? 'کد ملی: ' + client.national_code : '',
                        client.case_number ? 'پرونده: ' + client.case_number : ''
                    ].filter(Boolean).join(' | ');
                    this.clientSearch = '';
                    this.showResults = false;
                },

                clearClient() {
                    this.clientId = '';
                    this.selectedClientName = '';
                    this.selectedClientDetail = '';
                    this.clientSearch = '';
                },

                closeResults() {
                    this.showResults = false;
                },

                // Service helper methods
                get filteredServicesList() {
                    const q = this.serviceSearch.toLowerCase().trim();
                    if (!q) return this.services;
                    return this.services.filter(s => s.name.toLowerCase().includes(q));
                },
                selectService(service) {
                    this.serviceId = service.id;
                    this.selectedServiceName = service.name;
                    this.showServiceResults = false;
                    this.serviceSearch = '';

                    const serviceSelect = document.getElementById('service_id');
                    if (serviceSelect) {
                        serviceSelect.value = service.id;
                        serviceSelect.dispatchEvent(new Event('change'));
                    }
                },
                closeServiceResults() {
                    this.showServiceResults = false;
                },
                updateServicesList(list) {
                    this.services = list;
                    if (this.serviceId && !list.find(s => String(s.id) === String(this.serviceId))) {
                        this.serviceId = '';
                        this.selectedServiceName = '';
                    }
                },

                // Provider helper methods
                get filteredProvidersList() {
                    const q = this.providerSearch.toLowerCase().trim();
                    if (!q) return this.providers;
                    return this.providers.filter(p => p.name.toLowerCase().includes(q));
                },
                selectProvider(provider) {
                    this.providerId = provider.id;
                    this.selectedProviderName = provider.name;
                    this.showProviderResults = false;
                    this.providerSearch = '';

                    const providerSelect = document.getElementById('provider_user_id');
                    if (providerSelect) {
                        providerSelect.value = provider.id;
                        providerSelect.dispatchEvent(new Event('change'));
                    }
                },
                closeProviderResults() {
                    this.showProviderResults = false;
                },
                updateProvidersList(list) {
                    this.providers = list;
                    if (this.providerId && !list.find(p => String(p.id) === String(this.providerId))) {
                        this.providerId = '';
                        this.selectedProviderName = '';
                    }
                },

                // Status helper methods
                selectStatus(key) {
                    this.status = key;
                    this.showStatusResults = false;
                },
                closeStatusResults() {
                    this.showStatusResults = false;
                },

                handleSubmit() {
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

                // Sync with Alpine
                const alpineEl = document.querySelector('[x-data="editAppointment()"]');
                if (alpineEl && window.Alpine) {
                    const data = Alpine.$data(alpineEl);
                    data.updateProvidersList(providers.map(p => ({ id: p.id, name: p.name })));
                    const matched = providers.find(p => String(p.id) === String(current));
                    if (matched) {
                        data.providerId = matched.id;
                        data.selectedProviderName = matched.name;
                    } else if (providers.length > 0 && !keepSelected) {
                        data.providerId = providers[0].id;
                        data.selectedProviderName = providers[0].name;
                        providerSelect.value = providers[0].id;
                        providerSelect.dispatchEvent(new Event('change'));
                    } else {
                        data.providerId = '';
                        data.selectedProviderName = '';
                    }
                }
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

                // Sync with Alpine
                const alpineEl = document.querySelector('[x-data="editAppointment()"]');
                if (alpineEl && window.Alpine) {
                    const data = Alpine.$data(alpineEl);
                    data.updateServicesList(services.map(s => ({ id: s.id, name: s.name })));
                    const matched = services.find(s => String(s.id) === String(current));
                    if (matched) {
                        data.serviceId = matched.id;
                        data.selectedServiceName = matched.name;
                    } else if (services.length > 0 && !keepSelected) {
                        data.serviceId = services[0].id;
                        data.selectedServiceName = services[0].name;
                        serviceSelect.value = services[0].id;
                        serviceSelect.dispatchEvent(new Event('change'));
                    } else {
                        data.serviceId = '';
                        data.selectedServiceName = '';
                    }
                }
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
                const elements = [
                    ...formContainer.querySelectorAll('[data-field-name]'),
                    ...document.querySelectorAll('#dental-chart-editor-container [data-field-name]')
                ];
                elements.forEach((el) => {
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
                    if (name === 'tooth_numbers') {
                        values[name] = el.value ? el.value.split(',').map(Number) : [];
                        return;
                    }
                    values[name] = el.value;
                });
                return values;
            };

            const loadAppointmentForm = async (serviceId) => {
                const formId = serviceFormMap?.[serviceId] || null;
                const dentalChartContainer = document.getElementById('dental-chart-editor-container');
                if (!formId) {
                    formContainer.innerHTML = '';
                    dentalChartContainer?.classList.add('hidden');
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

                // Show/hide dental chart based on form type / fields schema
                const hasToothNumber = schema && Array.isArray(schema.fields) && schema.fields.some(f => f.type === 'tooth_number');
                if (hasToothNumber) {
                    dentalChartContainer?.classList.remove('hidden');
                    if (window.Alpine) {
                        const alpineEl = Alpine.$data(dentalChartContainer);
                        if (alpineEl && typeof alpineEl.init === 'function') {
                            alpineEl.init();
                        }
                    }
                } else {
                    dentalChartContainer?.classList.add('hidden');
                }

                if (!schema || !Array.isArray(schema.fields) || schema.fields.length === 0) {
                    formEmpty?.classList.remove('hidden');
                    return;
                }

                const fieldsToRender = schema.fields.filter(f => f.type !== 'tooth_number');
                if (fieldsToRender.length === 0 && hasToothNumber) {
                    formEmpty?.classList.add('hidden');
                    return;
                }

                if (fieldsToRender.length === 0) {
                    formEmpty?.classList.remove('hidden');
                    return;
                }

                formEmpty?.classList.add('hidden');
                fieldsToRender.forEach((field) => {
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
