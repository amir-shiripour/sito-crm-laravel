@extends('layouts.user')

@section('content')
    <div class="space-y-6"
         x-data="operatorAppointment()"
         x-init="init()">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">ثبت نوبت (اپراتور)</h1>
            <a class="text-blue-600 hover:underline" href="{{ route('user.booking.appointments.index') }}">بازگشت</a>
        </div>

        @if(session('success'))
            <div class="p-3 bg-green-50 border border-green-200 rounded text-green-700">{{ session('success') }}</div>
        @endif

        <form method="POST"
              action="{{ route('user.booking.appointments.store') }}"
              class="bg-white rounded border p-4 space-y-4"
              x-ref="form"
              @submit.prevent="handleSubmit">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- سرویس --}}
                <div>
                    <label class="block text-sm mb-1">سرویس</label>
                    <select name="service_id"
                            class="w-full border rounded p-2"
                            x-model="serviceId"
                            @change="onFilterChange"
                            required>
                        <option value="">انتخاب کنید</option>
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}" @selected(old('service_id')==$srv->id)>{{ $srv->name }}</option>
                        @endforeach
                    </select>
                    @error('service_id')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- ارائه‌دهنده --}}
                <div>
                    <label class="block text-sm mb-1">ارائه‌دهنده</label>
                    <select name="provider_user_id"
                            class="w-full border rounded p-2"
                            x-model="providerId"
                            @change="onFilterChange"
                            required>
                        <option value="">انتخاب کنید</option>
                        @foreach($providers as $p)
                            <option value="{{ $p->id }}" @selected(old('provider_user_id')==$p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('provider_user_id')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- مشتری --}}
                <div>
                    <label class="block text-sm mb-1">مشتری</label>
                    <select name="client_id" class="w-full border rounded p-2" required>
                        <option value="">انتخاب کنید</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->full_name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    <div class="text-xs text-gray-500 mt-1">در آینده می‌توان این بخش را به جستجوی زنده (autocomplete) ارتقا داد.</div>
                </div>

                {{-- تاریخ (شمسی) --}}
                <div>
                    <label class="block text-sm mb-1">تاریخ نوبت (شمسی)</label>
                    <input type="text"
                           name="date_local"
                           class="w-full border rounded p-2 text-sm"
                           placeholder="مثلاً 1403/10/01"
                           data-jdp
                           x-ref="dateInput"
                           @change="onDateInputChange"
                           value="{{ old('date_local') }}">
                    <p class="text-[11px] text-gray-500 mt-1">
                        تاریخ در فرانت شمسی است. مقدار گرگوری (YYYY-MM-DD) در value فیلد ذخیره می‌شود و برای سرویس اسلات استفاده می‌شود.
                    </p>
                </div>

                {{-- فیلدهای مخفی start/end که با انتخاب اسلات پر می‌شوند --}}
                <input type="hidden" name="start_at_utc" x-ref="startUtcInput" value="{{ old('start_at_utc') }}">
                <input type="hidden" name="end_at_utc" x-ref="endUtcInput" value="{{ old('end_at_utc') }}">

                {{-- نمایش نوبت انتخاب‌شده --}}
                <div class="md:col-span-2">
                    <div class="p-3 rounded border bg-gray-50 text-sm flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <span class="font-semibold">نوبت انتخاب‌شده:</span>
                            <template x-if="selectedSlot">
                                <span class="mr-2" x-text="selectedSlotLabel"></span>
                            </template>
                            <template x-if="!selectedSlot">
                                <span class="mr-2 text-gray-400">هنوز نوبتی انتخاب نشده است.</span>
                            </template>
                        </div>
                        <div class="text-xs text-gray-500">
                            ابتدا سرویس، ارائه‌دهنده و تاریخ را انتخاب کنید، سپس یکی از اسلات‌ها را از لیست زیر انتخاب کنید.
                        </div>
                    </div>
                </div>
            </div>

            {{-- لیست اسلات‌های قابل رزرو --}}
            <div class="border rounded p-4 mt-2 space-y-3 bg-white">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold">اسلات‌های قابل رزرو برای این روز</h2>
                    <button type="button"
                            class="text-xs px-3 py-1 rounded border border-gray-300 hover:bg-gray-50"
                            @click="fetchSlots">
                        بروزرسانی اسلات‌ها
                    </button>
                </div>

                <template x-if="!serviceId || !providerId">
                    <p class="text-xs text-gray-500">
                        لطفاً ابتدا سرویس و ارائه‌دهنده را انتخاب کنید.
                    </p>
                </template>

                <template x-if="serviceId && providerId && !date">
                    <p class="text-xs text-gray-500">
                        لطفاً تاریخ نوبت را انتخاب کنید.
                    </p>
                </template>

                <template x-if="loading">
                    <p class="text-xs text-gray-500">در حال دریافت اسلات‌ها...</p>
                </template>

                <template x-if="error">
                    <p class="text-xs text-red-600" x-text="error"></p>
                </template>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2"
                     x-show="slots.length && !loading">
                    <template x-for="slot in slots" :key="slot.start_at_utc">
                        <button type="button"
                                class="border rounded px-2 py-2 text-xs text-center hover:bg-indigo-50"
                                :class="selectedSlotKey === slot.start_at_utc ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-700'"
                                @click="selectSlot(slot)">
                            <div class="font-semibold" x-text="formatTime(slot.start_at_view)"></div>
                            <div class="text-[11px] text-gray-500 mt-0.5">
                                ظرفیت باقیمانده:
                                <span x-text="slot.remaining_capacity"></span>
                            </div>
                        </button>
                    </template>
                </div>

                <template x-if="!loading && serviceId && providerId && date && slots.length === 0 && !error">
                    <p class="text-xs text-amber-600">
                        برای این ترکیب سرویس / ارائه‌دهنده / تاریخ، اسلات خالی یافت نشد.
                    </p>
                </template>
            </div>

            {{-- یادداشت --}}
            <div class="mt-4">
                <label class="block text-sm mb-1">یادداشت</label>
                <textarea name="notes" rows="3" class="w-full border rounded p-2">{{ old('notes') }}</textarea>
                @error('notes')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="pt-2 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">
                    نوبت در دیتابیس بر اساس زمان UTC ذخیره می‌شود. اسلات‌ها با توجه به تنظیمات زمان‌بندی و استثناها محاسبه شده‌اند.
                </p>
                <button class="px-4 py-2 bg-blue-600 text-white rounded">
                    ثبت نوبت
                </button>
            </div>
        </form>
    </div>

    <script>
        function operatorAppointment() {
            return {
                serviceId: @json(old('service_id', '')),
                providerId: @json(old('provider_user_id', '')),
                date: @json(old('date_local', '')),
                slots: [],
                loading: false,
                error: '',
                selectedSlotKey: @json(old('start_at_utc', '')),
                selectedSlot: null,

                get selectedSlotLabel() {
                    if (!this.selectedSlot) return '';
                    const d = new Date(this.selectedSlot.start_at_view);
                    return d.toLocaleString('fa-IR', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                init() {
                    // jalaliDatepicker
                    document.addEventListener('DOMContentLoaded', () => {
                        if (window.jalaliDatepicker) {
                            jalaliDatepicker.startWatch();
                        }
                    });

                    // اگر بعد از ولیدیشن قبلی اسلاتی انتخاب شده بود، مقدار hiddenها را نگه داریم
                    const startVal = this.$refs.startUtcInput?.value;
                    const endVal   = this.$refs.endUtcInput?.value;
                    if (startVal && endVal) {
                        this.selectedSlot = {
                            start_at_utc: startVal,
                            end_at_utc: endVal,
                            start_at_view: startVal,
                        };
                        this.selectedSlotKey = startVal;
                    }

                    // اگر همه فیلترها پر باشد، خودکار اسلات‌ها را بگیر
                    this.autoFetchIfReady();
                },

                onFilterChange() {
                    this.selectedSlot = null;
                    this.selectedSlotKey = '';
                    this.$refs.startUtcInput.value = '';
                    this.$refs.endUtcInput.value = '';
                    this.autoFetchIfReady();
                },

                onDateInputChange(event) {
                    // فرض: jalaliDatepicker مقدار گرگوری Y-m-d در value می‌نویسد
                    this.date = event.target.value;
                    this.onFilterChange();
                },

                autoFetchIfReady() {
                    if (this.serviceId && this.providerId && this.date) {
                        this.fetchSlots();
                    }
                },

                async fetchSlots() {
                    if (!this.serviceId || !this.providerId || !this.date) return;

                    this.loading = true;
                    this.error = '';
                    this.slots = [];

                    const params = new URLSearchParams({
                        service_id: this.serviceId,
                        provider_id: this.providerId,
                        from_local_date: this.date,
                        to_local_date: this.date,
                        viewer_timezone: @json(config('booking.timezones.display_default')),
                    });

                    try {
                        const res = await fetch('/api/booking/availability/slots?' + params.toString(), {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!res.ok) {
                            throw new Error('خطا در دریافت اسلات‌ها (کد ' + res.status + ')');
                        }

                        const json = await res.json();
                        this.slots = json.data || [];
                    } catch (e) {
                        console.error(e);
                        this.error = e.message || 'خطای ناشناخته در دریافت اسلات‌ها';
                    } finally {
                        this.loading = false;
                    }
                },

                selectSlot(slot) {
                    this.selectedSlotKey = slot.start_at_utc;
                    this.selectedSlot = slot;

                    if (this.$refs.startUtcInput) {
                        this.$refs.startUtcInput.value = slot.start_at_utc;
                    }
                    if (this.$refs.endUtcInput) {
                        this.$refs.endUtcInput.value = slot.end_at_utc;
                    }
                },

                formatTime(isoString) {
                    const d = new Date(isoString);
                    return d.toLocaleTimeString('fa-IR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                handleSubmit() {
                    if (!this.$refs.startUtcInput.value || !this.$refs.endUtcInput.value) {
                        alert('لطفاً ابتدا یک اسلات نوبت را انتخاب کنید.');
                        return;
                    }
                    this.$refs.form.submit();
                }
            }
        }
    </script>
@endsection
