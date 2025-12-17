@extends('layouts.user')

@section('content')
    <div class="space-y-6"
         x-data="operatorWizard()"
         x-init="init()">

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">ثبت نوبت (مرحله‌ای)</h1>
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

            <input type="hidden" name="service_id" x-model="serviceId">
            <input type="hidden" name="provider_user_id" x-model="providerId">
            <input type="hidden" name="client_id" x-model="clientId">
            <input type="hidden" name="start_at_utc" x-ref="startUtcInput">
            <input type="hidden" name="end_at_utc" x-ref="endUtcInput">
            <input type="hidden" name="appointment_form_response_json" x-ref="formJsonInput">

            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    مرحله:
                    <span class="font-semibold" x-text="step"></span>
                    از
                    <span class="font-semibold">6</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" class="px-3 py-1 rounded border" @click="prev()" :disabled="step===1">قبلی</button>
                    <button type="button" class="px-3 py-1 rounded bg-indigo-600 text-white" @click="next()" x-show="step<6">بعدی</button>
                </div>
            </div>

            {{-- STEP 1 --}}
            <div x-show="step===1" class="space-y-3">
                <div class="text-sm text-gray-600">
                    @php $flowValue = $flow ?? 'PROVIDER_FIRST'; @endphp
                    حالت انتخاب: <span class="font-semibold">{{ $flowValue === 'SERVICE_FIRST' ? 'اول سرویس' : 'اول ارائه‌دهنده' }}</span>
                </div>

                <template x-if="flow==='PROVIDER_FIRST'">
                    <div>
                        <label class="block text-sm mb-1">انتخاب ارائه‌دهنده</label>
                        <input type="text" class="w-full border rounded p-2 mb-2" placeholder="جستجو..." x-model="providerSearch" @input.debounce.300ms="fetchProviders()">
                        <select class="w-full border rounded p-2" x-model="providerId" @change="onProviderSelected()">
                            <option value="">انتخاب کنید</option>
                            <template x-for="p in providers" :key="p.id">
                                <option :value="p.id" x-text="p.name"></option>
                            </template>
                        </select>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST'">
                    <div>
                        <label class="block text-sm mb-1">انتخاب سرویس</label>
                        <input type="text" class="w-full border rounded p-2 mb-2" placeholder="جستجو..." x-model="serviceSearch" @input.debounce.300ms="fetchServicesForServiceFirst()">
                        <select class="w-full border rounded p-2" x-model="serviceId" @change="onServiceSelected()">
                            <option value="">انتخاب کنید</option>
                            <template x-for="s in services" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                </template>
            </div>

            {{-- STEP 2 --}}
            <div x-show="step===2" class="space-y-3">
                <template x-if="flow==='PROVIDER_FIRST'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm mb-1">دسته‌بندی</label>
                            <select class="w-full border rounded p-2" x-model="categoryId" @change="fetchServicesForProvider()">
                                <option value="">همه</option>
                                <template x-for="c in categories" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">انتخاب سرویس</label>
                            <input type="text" class="w-full border rounded p-2 mb-2" placeholder="جستجو..." x-model="serviceSearch" @input.debounce.300ms="fetchServicesForProvider()">
                            <select class="w-full border rounded p-2" x-model="serviceId" @change="onServiceSelected()">
                                <option value="">انتخاب کنید</option>
                                <template x-for="s in services" :key="s.id">
                                    <option :value="s.id" x-text="s.name"></option>
                                </template>
                            </select>
                            <div class="text-xs text-gray-500 mt-1">فقط سرویس‌هایی که برای این ارائه‌دهنده فعال هستند نمایش داده می‌شود.</div>
                        </div>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST'">
                    <div>
                        <label class="block text-sm mb-1">انتخاب ارائه‌دهنده (برای سرویس انتخابی)</label>
                        <input type="text" class="w-full border rounded p-2 mb-2" placeholder="جستجو..." x-model="providerSearch" @input.debounce.300ms="fetchProviders()">
                        <select class="w-full border rounded p-2" x-model="providerId" @change="onProviderSelected()">
                            <option value="">انتخاب کنید</option>
                            <template x-for="p in providers" :key="p.id">
                                <option :value="p.id" x-text="p.name"></option>
                            </template>
                        </select>
                    </div>
                </template>
            </div>

            {{-- STEP 3: Calendar month --}}
            <div x-show="step===3" class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="font-semibold text-sm">انتخاب روز</div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="px-2 py-1 border rounded" @click="prevMonth()">ماه قبل</button>
                        <div class="text-sm" x-text="monthLabel"></div>
                        <button type="button" class="px-2 py-1 border rounded" @click="nextMonth()">ماه بعد</button>
                    </div>
                </div>

                <template x-if="calendarLoading">
                    <div class="text-xs text-gray-500">در حال بارگذاری تقویم...</div>
                </template>

                <div class="grid grid-cols-7 gap-2 text-xs" dir="rtl">
                    <template x-for="w in weekDays" :key="w">
                        <div class="text-center text-[11px] font-semibold text-gray-500 py-1">
                            <span x-text="w"></span>
                        </div>
                    </template>

                    <template x-for="cell in calendarCells" :key="cell.key">
                        <div>
                            <template x-if="cell.is_placeholder">
                                <div class="h-[52px] border border-transparent"></div>
                            </template>

                            <template x-if="!cell.is_placeholder">
                                <button type="button"
                                        class="w-full h-[52px] border rounded-lg p-2 text-center"
                                        :class="dayBtnClass(cell.day)"
                                        @click="selectDay(cell.day)"
                                        :disabled="cell.day.is_closed || !cell.day.has_available_slots">
                                    <div class="font-semibold" x-text="toPersianDayNumber(cell.day.local_date)"></div>
                                    <div class="text-[10px] mt-1" x-show="cell.day.is_closed">تعطیل</div>
                                    <div class="text-[10px] mt-1" x-show="!cell.day.is_closed && !cell.day.has_available_slots">پر</div>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="text-xs text-gray-500">
                    بعد از انتخاب روز، در مرحله بعد اسلات‌ها نمایش داده می‌شوند.
                </div>
            </div>

            {{-- STEP 4: Slots --}}
            <div x-show="step===4" class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="font-semibold text-sm">انتخاب اسلات زمانی</div>
                    <button type="button" class="text-xs px-3 py-1 rounded border" @click="fetchSlots()">بروزرسانی</button>
                </div>

                <template x-if="slotsLoading">
                    <div class="text-xs text-gray-500">در حال دریافت اسلات‌ها...</div>
                </template>

                <template x-if="slotsError">
                    <div class="text-xs text-red-600" x-text="slotsError"></div>
                </template>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2" x-show="slots.length && !slotsLoading">
                    <template x-for="slot in slots" :key="slot.start_at_utc">
                        <button type="button"
                                class="border rounded px-2 py-2 text-xs text-center hover:bg-indigo-50"
                                :class="selectedSlotKey === slot.start_at_utc ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-700'"
                                @click="selectSlot(slot)">
                            <div class="font-semibold" x-text="formatTime(slot.start_at_view)"></div>
                            <div class="text-[11px] text-gray-500 mt-0.5">
                                ظرفیت: <span x-text="slot.remaining_capacity"></span>
                            </div>
                        </button>
                    </template>
                </div>

                <template x-if="!slotsLoading && dateLocal && slots.length === 0 && !slotsError">
                    <div class="text-xs text-amber-600">اسلات خالی برای این روز یافت نشد.</div>
                </template>
            </div>

            {{-- STEP 5: Appointment Form --}}
            <div x-show="step===5" class="space-y-3">
                <div class="font-semibold text-sm">فرم اطلاعات نوبت</div>

                <template x-if="selectedService && selectedService.appointment_form_id">
                    <div class="space-y-2">
                        <div class="text-xs text-gray-500">
                            سرویس فرم دارد. (فعلاً پاسخ را به صورت JSON ذخیره می‌کنیم؛ اگر کامپوننت فرم‌ساز دارید، همینجا جایگزین می‌شود.)
                        </div>
                        <textarea class="w-full border rounded p-2 text-xs" rows="6"
                                  placeholder='مثلاً: {"field1":"value"}'
                                  x-model="appointmentFormJson"></textarea>
                    </div>
                </template>

                <template x-if="!selectedService || !selectedService.appointment_form_id">
                    <div class="text-xs text-gray-500">برای این سرویس فرم اختصاصی تعریف نشده است.</div>
                </template>
            </div>

            {{-- STEP 6: Client --}}
            <div x-show="step===6" class="space-y-3">
                <div class="font-semibold text-sm">انتخاب مشتری</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <input type="text" class="w-full border rounded p-2" placeholder="جستجو مشتری (نام/موبایل/ایمیل)"
                               x-model="clientSearch" @input.debounce.300ms="fetchClients()">
                        <select class="w-full border rounded p-2 mt-2" x-model="clientId">
                            <option value="">انتخاب کنید</option>
                            <template x-for="c in clients" :key="c.id">
                                <option :value="c.id" x-text="`${c.full_name} (${c.phone || '-'})`"></option>
                            </template>
                        </select>
                    </div>

                    <div class="border rounded p-3 bg-gray-50">
                        <div class="text-sm font-semibold mb-2">ایجاد سریع مشتری</div>
                        <div class="space-y-2">
                            <input class="w-full border rounded p-2 text-sm" placeholder="نام کامل" x-model="quickClient.full_name">
                            <input class="w-full border rounded p-2 text-sm" placeholder="موبایل" x-model="quickClient.phone">
                            <input class="w-full border rounded p-2 text-sm" placeholder="ایمیل" x-model="quickClient.email">
                            <button type="button" class="px-3 py-1 rounded bg-emerald-600 text-white text-sm" @click="quickCreateClient()">
                                ثبت مشتری
                            </button>
                            <div class="text-xs text-red-600" x-show="quickClientError" x-text="quickClientError"></div>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="block text-sm mb-1">یادداشت</label>
                    <textarea name="notes" rows="3" class="w-full border rounded p-2"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-between">
                    <div class="text-[11px] text-gray-500">
                        ثبت نهایی فقط وقتی ممکن است که سرویس/ارائه‌دهنده/روز/اسلات/مشتری کامل باشند.
                    </div>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">
                        ثبت نوبت
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function operatorWizard() {
            return {
                flow: @json($flow ?? 'PROVIDER_FIRST'),
                step: 1,

                providerId: '',
                serviceId: '',
                categoryId: '',
                dateLocal: '',

                providers: [],
                services: [],
                categories: [],

                providerSearch: '',
                serviceSearch: '',

                calendarYear: null,
                calendarMonth: null,
                calendarDays: [],
                calendarLoading: false,

                slots: [],
                slotsLoading: false,
                slotsError: '',
                selectedSlotKey: '',
                selectedService: null,

                appointmentFormJson: '',
                clients: [],
                clientSearch: '',
                clientId: '',

                quickClient: { full_name: '', phone: '', email: '' },
                quickClientError: '',
                weekDays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],

                init() {
                    const now = new Date();
                    this.calendarYear = now.getFullYear();
                    this.calendarMonth = now.getMonth() + 1;

                    // شروع
                    if (this.flow === 'PROVIDER_FIRST') {
                        this.fetchProviders();
                    } else {
                        // service first: اول لیست سرویس‌ها از روی provider_id نداریم؛ پس از API services با provider لازم است.
                        // در این حالت سرویس‌ها را با یک provider_id نمی‌گیریم؛
                        // برای سادگی، اینجا لیست سرویس‌ها را از providers فعال در سیستم نمی‌گیریم و فقط بعد از انتخاب سرویس،
                        // providers را fetch می‌کنیم.
                        // اگر خواستی، endpoint جدا برای "all active services" هم اضافه می‌کنیم.
                        this.fetchAllActiveServices();
                    }

                    this.fetchClients();
                },

                // ---------------- providers/services/categories ----------------

                async fetchProviders() {
                    const params = new URLSearchParams({
                        q: this.providerSearch || '',
                    });
                    if (this.flow === 'SERVICE_FIRST' && this.serviceId) {
                        params.set('service_id', this.serviceId);
                    }

                    const res = await fetch(`{{ route('user.booking.appointments.wizard.providers') }}?` + params.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    this.providers = json.data || [];
                },

                async fetchCategories() {
                    if (!this.providerId) return;
                    const params = new URLSearchParams({ provider_id: this.providerId });
                    const res = await fetch(`{{ route('user.booking.appointments.wizard.categories') }}?` + params.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    this.categories = json.data || [];
                },

                async fetchServicesForProvider() {
                    if (!this.providerId) return;
                    const params = new URLSearchParams({
                        provider_id: this.providerId,
                        q: this.serviceSearch || '',
                    });
                    if (this.categoryId) params.set('category_id', this.categoryId);

                    const res = await fetch(`{{ route('user.booking.appointments.wizard.services') }}?` + params.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    this.services = json.data || [];
                },

                async fetchAllActiveServices() {
                    const params = new URLSearchParams({ q: this.serviceSearch || '' });

                    const res = await fetch(`{{ route('user.booking.appointments.wizard.all-services') }}?` + params.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });

                    const json = await res.json();
                    this.services = json.data || [];
                },

                async fetchServicesForServiceFirst() {
                    // همان fetchAllActiveServices (محدودیت بالا)
                    await this.fetchAllActiveServices();
                },

                onProviderSelected() {
                    this.categoryId = '';

                    // نکته مهم:
                    // در SERVICE_FIRST نباید serviceId را reset کنیم
                    if (this.flow === 'PROVIDER_FIRST') {
                        this.serviceId = '';
                        this.services = [];
                        this.categories = [];
                        this.resetCalendarAndSlots();

                        this.fetchCategories();
                        this.fetchServicesForProvider();
                        return;
                    }

                    // SERVICE_FIRST
                    this.resetCalendarAndSlots();
                },


                onServiceSelected() {
                    this.selectedService = this.services.find(s => String(s.id) === String(this.serviceId)) || null;
                    this.resetCalendarAndSlots();

                    if (this.flow === 'SERVICE_FIRST') {
                        this.fetchProviders(); // حالا providers برای این service
                    }
                },

                resetCalendarAndSlots() {
                    this.dateLocal = '';
                    this.calendarDays = [];
                    this.slots = [];
                    this.selectedSlotKey = '';
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                },

                // ---------------- calendar ----------------

                get monthLabel() {
                    const d = new Date(this.calendarYear, this.calendarMonth - 1, 1);
                    return d.toLocaleDateString('fa-IR-u-ca-persian', { year: 'numeric', month: 'long' });
                },

                get calendarCells() {
                    // خروجی: placeholder + روزهای ماه
                    const days = this.calendarDays || [];
                    if (!days.length) return [];

                    const first = days[0].local_date; // YYYY-MM-DD
                    const firstDate = new Date(first + 'T00:00:00');

                    // JS getDay(): 0=Sun..6=Sat -> تبدیل به 0=Sat..6=Fri
                    const persianWeekdayIndex = (firstDate.getDay() + 1) % 7;

                    const cells = [];
                    for (let i = 0; i < persianWeekdayIndex; i++) {
                        cells.push({ key: `ph-${i}`, is_placeholder: true });
                    }

                    for (const d of days) {
                        cells.push({ key: d.local_date, is_placeholder: false, day: d });
                    }

                    // کامل شدن ردیف آخر
                    while (cells.length % 7 !== 0) {
                        cells.push({ key: `ph-end-${cells.length}`, is_placeholder: true });
                    }

                    return cells;
                },
                async loadCalendar() {
                    if (!this.serviceId || !this.providerId) return;

                    this.calendarLoading = true;
                    const params = new URLSearchParams({
                        service_id: this.serviceId,
                        provider_id: this.providerId,
                        year: this.calendarYear,
                        month: this.calendarMonth
                    });

                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.calendar') }}?` + params.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });
                        const json = await res.json();
                        this.calendarDays = json.data || [];
                    } finally {
                        this.calendarLoading = false;
                    }
                },

                prevMonth() {
                    this.calendarMonth--;
                    if (this.calendarMonth < 1) { this.calendarMonth = 12; this.calendarYear--; }
                    this.loadCalendar();
                },

                nextMonth() {
                    this.calendarMonth++;
                    if (this.calendarMonth > 12) { this.calendarMonth = 1; this.calendarYear++; }
                    this.loadCalendar();
                },

                selectDay(day) {
                    if (day.is_closed || !day.has_available_slots) return;
                    this.dateLocal = day.local_date;
                    this.slots = [];
                    this.selectedSlotKey = '';
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                },

                dayBtnClass(d) {
                    const isSelected = this.dateLocal && this.dateLocal === d.local_date;
                    if (!d.is_closed && !d.has_available_slots) return 'bg-amber-50 text-amber-700 border-amber-200';
                    if (d.is_closed) return 'bg-gray-100 text-gray-400 border-gray-200';
                    if (isSelected) return 'bg-indigo-50 text-indigo-700 border-indigo-500';
                    if (d.capacity_per_day !== null && d.remaining_day_capacity === 0) return 'bg-amber-50 text-amber-700 border-amber-200';
                    return 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50';
                },

                toPersianDayNumber(localDate) {
                    const dd = new Date(localDate + 'T00:00:00');
                    return dd.toLocaleDateString('fa-IR-u-ca-persian', { day: 'numeric' });
                },

                // ---------------- slots ----------------

                async fetchSlots() {
                    if (!this.serviceId || !this.providerId || !this.dateLocal) return;

                    this.slotsLoading = true;
                    this.slotsError = '';
                    this.slots = [];

                    const params = new URLSearchParams({
                        service_id: this.serviceId,
                        provider_id: this.providerId,
                        from_local_date: this.dateLocal,
                        to_local_date: this.dateLocal,
                        viewer_timezone: @json(config('booking.timezones.display_default')),
                    });

                    try {
                        const res = await fetch('/api/booking/availability/slots?' + params.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });

                        if (!res.ok) throw new Error('خطا در دریافت اسلات‌ها (کد ' + res.status + ')');

                        const json = await res.json();
                        this.slots = json.data || [];
                    } catch (e) {
                        this.slotsError = e.message || 'خطای ناشناخته در دریافت اسلات‌ها';
                    } finally {
                        this.slotsLoading = false;
                    }
                },

                selectSlot(slot) {
                    this.selectedSlotKey = slot.start_at_utc;
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = slot.start_at_utc;
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = slot.end_at_utc;
                },

                formatTime(isoString) {
                    const d = new Date(isoString);
                    return d.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
                },

                // ---------------- clients ----------------

                async fetchClients() {
                    const params = new URLSearchParams({ q: this.clientSearch || '' });
                    const res = await fetch(`{{ route('user.booking.appointments.wizard.clients') }}?` + params.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    this.clients = json.data || [];
                },

                async quickCreateClient() {
                    this.quickClientError = '';
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        const res = await fetch(`{{ route('user.clients.quick-store') }}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token || ''
                            },
                            body: JSON.stringify(this.quickClient)
                        });

                        if (!res.ok) {
                            const j = await res.json().catch(() => null);
                            throw new Error(j?.message || 'خطا در ایجاد مشتری');
                        }

                        const j = await res.json();
                        const c = j.client;
                        this.clients.unshift(c);
                        this.clientId = String(c.id);

                        this.quickClient = { full_name: '', phone: '', email: '' };
                    } catch (e) {
                        this.quickClientError = e.message || 'خطای ناشناخته';
                    }
                },

                // ---------------- wizard navigation ----------------

                async next() {
                    // STEP 1 -> 2
                    if (this.step === 1) {
                        if (this.flow === 'PROVIDER_FIRST' && !this.providerId) return alert('لطفاً ارائه‌دهنده را انتخاب کنید.');
                        if (this.flow === 'SERVICE_FIRST' && !this.serviceId) return alert('لطفاً سرویس را انتخاب کنید.');
                    }

                    // STEP 2 -> 3 (قبل از ورود به تقویم، خود تقویم را لود کن)
                    if (this.step === 2) {
                        if (!this.serviceId) return alert('لطفاً سرویس را انتخاب کنید.');
                        if (!this.providerId) return alert('لطفاً ارائه‌دهنده را انتخاب کنید.');

                        await this.loadCalendar();

                        // انتخاب خودکار امروز (اگر موجود و قابل رزرو باشد)
                        if (!this.dateLocal) {
                            const today = new Date();
                            const iso = today.toISOString().slice(0, 10);

                            const found = this.calendarDays.find(x =>
                                x.local_date === iso &&
                                !x.is_closed &&
                                x.has_available_slots
                            );

                            if (found) this.selectDay(found);
                        }
                    }

                    // STEP 3 -> 4 (روز باید انتخاب شود، بعد slots را بگیر)
                    if (this.step === 3) {
                        if (!this.dateLocal) return alert('لطفاً یک روز قابل رزرو انتخاب کنید.');
                        await this.fetchSlots();
                    }

                    // STEP 4 -> 5 (اسلات باید انتخاب شود)
                    if (this.step === 4) {
                        if (!this.$refs.startUtcInput.value || !this.$refs.endUtcInput.value) {
                            return alert('لطفاً یک اسلات زمانی را انتخاب کنید.');
                        }
                    }

                    // STEP 5 -> 6 (فرم json را آماده کن)
                    if (this.step === 5) {
                        if (this.selectedService && this.selectedService.appointment_form_id) {
                            this.$refs.formJsonInput.value = this.appointmentFormJson || '';
                        } else {
                            this.$refs.formJsonInput.value = '';
                        }
                    }

                    this.step++;
                },

                prev() {
                    if (this.step > 1) this.step--;
                },

                async initCalendarIfReady() {
                    if (this.serviceId && this.providerId) {
                        await this.loadCalendar();
                    }
                },

                handleSubmit() {
                    if (!this.serviceId || !this.providerId) return alert('سرویس/ارائه‌دهنده ناقص است.');
                    if (!this.dateLocal) return alert('روز انتخاب نشده است.');
                    if (!this.$refs.startUtcInput.value || !this.$refs.endUtcInput.value) return alert('لطفاً یک اسلات انتخاب کنید.');
                    if (!this.clientId) return alert('لطفاً مشتری را انتخاب کنید.');

                    // فرم JSON
                    if (this.selectedService && this.selectedService.appointment_form_id) {
                        this.$refs.formJsonInput.value = this.appointmentFormJson || '';
                    }

                    this.$refs.form.submit();
                }
            }
        }
    </script>
@endsection
