@php
    $userRoles = auth()->user()->roles->pluck('name')->toArray();
    $userRolesDisplay = auth()->user()->roles->pluck('display_name')->toArray();
    $customFields = \App\Models\CustomUserField::whereIn('role_name', $userRoles)->where('show_in_profile', true)->orderBy('id')->get()->unique('field_name');
    $customValues = \App\Models\UserCustomValue::where('user_id', auth()->id())->get()->keyBy('field_name');
    $provincesData = \App\Helpers\ProvinceCity::getAll();
    $inputTypes = ['text','number','email','date','datetime-local','time','month','week','url','tel','password','color','range','hidden'];

    // استایل‌های مشترک (هماهنگ با CRM)
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4";
    $titleClass = "text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2";
    $descClass = "text-xs text-gray-500 dark:text-gray-400 mt-1";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
    $btnPrimaryClass = "inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed";
    $btnDangerClass = "inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-500/30 hover:bg-red-700 hover:shadow-red-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed";
    $btnSecondaryClass = "inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white border border-gray-200 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white";
    $footerClass = "px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-end gap-4";
@endphp

<form wire:submit="updateProfileInformation" class="{{ $cardClass }}">
    {{-- هدر فرم --}}
    <div class="{{ $headerClass }}">
        <div>
            <h2 class="{{ $titleClass }}">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                {{ __('اطلاعات فردی و تماس') }}
            </h2>
            <p class="{{ $descClass }}">
                {{ __('اطلاعات پروفایل، شماره تماس و اطلاعات تکمیلی حساب کاربری خود را به‌روزرسانی کنید.') }}
            </p>
        </div>
    </div>

    {{-- محتوای فرم --}}
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="md:col-span-2">
                <input type="file" id="photo" class="hidden"
                       wire:model.live="photo"
                       x-ref="photo"
                       x-on:change="
                            photoName = $refs.photo.files[0].name;
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                photoPreview = e.target.result;
                            };
                            reader.readAsDataURL($refs.photo.files[0]);
                    " />

                <label class="{{ $labelClass }}">{{ __('عکس پروفایل') }}</label>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mt-2">
                    <!-- Current Profile Photo -->
                    <div x-show="! photoPreview">
                        <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="rounded-full h-20 w-20 object-cover ring-4 ring-gray-50 dark:ring-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm">
                    </div>

                    <!-- New Profile Photo Preview -->
                    <div x-show="photoPreview" style="display: none;">
                        <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center ring-4 ring-gray-50 dark:ring-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm"
                              x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="{{ $btnSecondaryClass }}" x-on:click.prevent="$refs.photo.click()">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01v.01H14V8z" />
                            </svg>
                            {{ __('انتخاب عکس جدید') }}
                        </button>

                        @if ($this->user->profile_photo_path)
                            <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors border border-transparent dark:border-red-800 text-sm font-bold" wire:click="deleteProfilePhoto">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                {{ __('حذف عکس') }}
                            </button>
                        @endif
                    </div>
                </div>
                <x-input-error for="photo" class="mt-2 text-xs text-red-500 font-bold" />
            </div>
        @endif

        <!-- Name -->
        <div class="md:col-span-1">
            <label for="name" class="{{ $labelClass }}">{{ __('نام و نام خانوادگی') }} <span class="text-red-500">*</span></label>
            <input id="name" type="text" class="{{ $inputClass }}" wire:model="state.name" required autocomplete="name" placeholder="مثلاً: علی احمدی" />
            <x-input-error for="name" class="mt-2 text-xs text-red-500 font-bold" />
        </div>

        <!-- Mobile (Locked + Inline OTP Change) -->
        <div class="md:col-span-1"
             x-data="{
                editing: false,
                newMobile: '',
                otpCode: '',
                otpSent: false,
                sending: false,
                verifying: false,
                cooldown: 0,
                timer: null,
                statusMsg: '',
                statusType: '',
                startCooldown(sec = 60) {
                    this.cooldown = sec;
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if (this.cooldown > 0) this.cooldown--;
                        else clearInterval(this.timer);
                    }, 1000);
                },
                async sendOtp() {
                    if (!this.newMobile.trim() || this.sending || this.cooldown > 0) return;
                    this.sending = true;
                    this.statusMsg = '';
                    try {
                        const res = await fetch('{{ route('user.profile.mobile.send-otp') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ mobile: this.newMobile })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.otpSent = true;
                            this.statusType = 'success';
                            this.statusMsg = data.message || 'کد تایید ارسال شد.';
                            this.startCooldown(60);
                        } else {
                            this.statusType = 'error';
                            this.statusMsg = data.message || 'خطا در ارسال کد تایید.';
                            if (data.resend_in) this.startCooldown(data.resend_in);
                        }
                    } catch (e) {
                        this.statusType = 'error';
                        this.statusMsg = 'خطا در ارتباط با سرور.';
                    } finally {
                        this.sending = false;
                    }
                },
                async verifyOtp() {
                    if (!this.otpCode.trim() || this.verifying) return;
                    this.verifying = true;
                    this.statusMsg = '';
                    try {
                        const res = await fetch('{{ route('user.profile.mobile.verify-otp') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ mobile: this.newMobile, code: this.otpCode })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            $wire.set('state.mobile', data.phone);
                            window.dispatchEvent(new CustomEvent('profile-mobile-updated', { detail: { mobile: data.phone } }));
                            this.statusType = 'success';
                            this.statusMsg = data.message || 'شماره موبایل با موفقیت تغییر یافت.';
                            this.editing = false;
                            this.otpSent = false;
                            this.newMobile = '';
                            this.otpCode = '';
                        } else {
                            this.statusType = 'error';
                            this.statusMsg = data.message || 'کد تایید نامعتبر است.';
                        }
                    } catch (e) {
                        this.statusType = 'error';
                        this.statusMsg = 'خطا در ارتباط با سرور.';
                    } finally {
                        this.verifying = false;
                    }
                }
             }">
            <div class="flex items-center justify-between mb-2">
                <label for="mobile" class="{{ $labelClass }} !mb-0">{{ __('شماره موبایل') }}</label>
                <button type="button"
                        @click="editing = !editing; statusMsg = ''; otpSent = false; otpCode = '';"
                        class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                    <span x-text="editing ? 'انصراف' : 'تغییر شماره موبایل'"></span>
                </button>
            </div>
            <div class="relative">
                <input id="mobile" type="tel" readonly
                       class="{{ $inputClass }} dir-ltr text-left !pl-11 opacity-80 cursor-not-allowed"
                       wire:model="state.mobile" />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                </div>
            </div>

            <div x-show="editing" x-transition style="display: none;"
                 class="mt-3 p-4 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50/50 dark:bg-indigo-500/10 space-y-3">
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="tel" x-model="newMobile" placeholder="09123456789"
                           class="{{ $inputClass }} dir-ltr text-left flex-1 !py-2 text-xs"
                           :disabled="otpSent" />
                    <button type="button" @click="sendOtp()"
                            :disabled="sending || cooldown > 0 || !newMobile.trim()"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition disabled:opacity-50 shrink-0">
                        <span x-show="!sending && cooldown === 0" x-text="otpSent ? 'ارسال مجدد' : 'ارسال کد تایید'"></span>
                        <span x-show="sending">در حال ارسال...</span>
                        <span x-show="!sending && cooldown > 0" x-text="cooldown + ' ثانیه'"></span>
                    </button>
                </div>

                <div x-show="otpSent" x-transition class="flex flex-col sm:flex-row gap-2">
                    <input type="text" x-model="otpCode" placeholder="کد تایید پیامکی"
                           class="{{ $inputClass }} dir-ltr text-center flex-1 !py-2 text-xs tracking-widest" />
                    <button type="button" @click="verifyOtp()"
                            :disabled="verifying || !otpCode.trim()"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition disabled:opacity-50 shrink-0">
                        <span x-show="!verifying">تایید و ثبت شماره</span>
                        <span x-show="verifying">در حال بررسی...</span>
                    </button>
                </div>

                <p x-show="statusMsg" x-text="statusMsg"
                   :class="statusType === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                   class="text-xs font-bold"></p>
            </div>

            <p x-show="!editing && statusMsg && statusType === 'success'" x-text="statusMsg"
               class="mt-2 text-xs font-bold text-emerald-600 dark:text-emerald-400"></p>
            <x-input-error for="mobile" class="mt-2 text-xs text-red-500 font-bold" />
        </div>

        <!-- Role (Readonly) -->
        <div class="md:col-span-2">
            <label class="{{ $labelClass }}">{{ __('نقش‌های کاربری در سیستم') }}</label>
            <div class="flex flex-wrap gap-2 mt-1">
                @forelse($userRolesDisplay as $role)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20">
                        {{ $role }}
                    </span>
                @empty
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">بدون نقش مشخص</span>
                @endforelse
            </div>
        </div>

        <!-- Custom Fields Divider -->
        @if($customFields->count() > 0)
            <div class="md:col-span-2 mt-2 pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="{{ $titleClass }}"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ __('اطلاعات تکمیلی و احراز هویت') }}</h3>
                <p class="{{ $descClass }}">{{ __('این فیلدها جهت احراز هویت و بر اساس نقش کاربری شما در سیستم تعیین شده‌اند.') }}</p>
            </div>

            @foreach($customFields as $field)
                @php
                    $type = strtolower($field->field_type ?? 'text');
                    $existing = $customValues->get($field->field_name)->value ?? '';
                    if(!isset($this->state['custom'][$field->field_name])) {
                        $this->state['custom'][$field->field_name] = $existing;
                        if ($type === 'checkbox') {
                           $this->state['custom'][$field->field_name] = is_string($existing) ? (json_decode($existing, true) ?: []) : (is_array($existing) ? $existing : []);
                        }
                    }

                    $isFullWidth = in_array($type, ['textarea', 'file']);
                    $opts = is_array($field->meta) ? ($field->meta['options'] ?? null) : (is_string($field->meta) ? (json_decode($field->meta, true)['options'] ?? null) : null);
                    $isLtrField = in_array($type, ['number', 'email', 'url', 'tel', 'color']);
                @endphp

                <div class="{{ $isFullWidth ? 'md:col-span-2' : 'md:col-span-1' }}">
                    <label for="custom_{{ $field->field_name }}" class="{{ $labelClass }}">
                        {{ $field->label }}
                        @if($field->is_required) <span class="text-red-500">*</span> @endif
                    </label>

                    @if($field->field_name === 'province' && in_array($type, ['select', 'text'], true))
                        <div x-data="{
                                open: false,
                                search: '',
                                province: $wire.entangle('state.custom.province'),
                                provinces: @js(array_keys($provincesData)),
                                provincesData: @js($provincesData),
                                get filtered() {
                                    const q = (this.search || '').trim();
                                    return q ? this.provinces.filter(p => p.includes(q)) : this.provinces;
                                },
                                selectProvince(p) {
                                    this.province = p;
                                    const validCities = this.provincesData[p] || [];
                                    const currentCity = $wire.get('state.custom.city');
                                    if (currentCity && !validCities.includes(currentCity)) {
                                        $wire.set('state.custom.city', '');
                                    }
                                    this.open = false;
                                    this.search = '';
                                }
                             }"
                             @click.outside="open = false"
                             class="relative font-sans">
                            <button type="button" @click="open = !open"
                                    class="{{ $inputClass }} flex items-center justify-between cursor-pointer text-right">
                                <span x-text="province || 'انتخاب استان...'"
                                      :class="province ? 'text-gray-900 dark:text-gray-100 font-medium' : 'text-gray-400 dark:text-gray-500'"
                                      class="truncate"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="{'rotate-180 text-indigo-500': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition style="display: none;"
                                 class="absolute z-50 mt-1.5 w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800">
                                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                    <div class="relative">
                                        <input type="text" x-model="search" placeholder="جستجوی استان..." @click.stop
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 pr-8 pl-3 py-2 text-xs text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                        <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <ul class="max-h-56 overflow-y-auto p-1.5 space-y-0.5">
                                    <template x-for="prov in filtered" :key="prov">
                                        <li @click="selectProvince(prov)"
                                            class="cursor-pointer select-none py-2 px-3 text-xs rounded-xl transition-colors flex items-center justify-between"
                                            :class="province === prov ? 'bg-indigo-50 text-indigo-700 font-bold dark:bg-indigo-500/15 dark:text-indigo-300' : 'text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-gray-700/60'">
                                            <span x-text="prov"></span>
                                            <svg x-show="province === prov" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </li>
                                    </template>
                                    <li x-show="filtered.length === 0" class="py-3 px-3 text-xs text-gray-500 dark:text-slate-400 text-center">
                                        استانی یافت نشد
                                    </li>
                                </ul>
                            </div>
                        </div>

                    @elseif($field->field_name === 'city' && in_array($type, ['select', 'text'], true))
                        <div x-data="{
                                open: false,
                                search: '',
                                province: $wire.entangle('state.custom.province'),
                                city: $wire.entangle('state.custom.city'),
                                provincesData: @js($provincesData),
                                get cities() {
                                    return (this.province && this.provincesData[this.province]) ? this.provincesData[this.province] : [];
                                },
                                get filtered() {
                                    const q = (this.search || '').trim();
                                    return q ? this.cities.filter(c => c.includes(q)) : this.cities;
                                },
                                selectCity(c) {
                                    this.city = c;
                                    this.open = false;
                                    this.search = '';
                                }
                             }"
                             @click.outside="open = false"
                             class="relative font-sans">
                            <button type="button" @click="province ? (open = !open) : null"
                                    class="{{ $inputClass }} flex items-center justify-between text-right"
                                    :class="province ? 'cursor-pointer' : 'opacity-60 cursor-not-allowed'">
                                <span x-text="city || (province ? 'انتخاب شهر...' : 'ابتدا استان را انتخاب کنید...')"
                                      :class="city ? 'text-gray-900 dark:text-gray-100 font-medium' : 'text-gray-400 dark:text-gray-500'"
                                      class="truncate"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="{'rotate-180 text-indigo-500': open && province}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open && province" x-transition style="display: none;"
                                 class="absolute z-50 mt-1.5 w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800">
                                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                    <div class="relative">
                                        <input type="text" x-model="search" placeholder="جستجوی شهر..." @click.stop
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 pr-8 pl-3 py-2 text-xs text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                        <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <ul class="max-h-56 overflow-y-auto p-1.5 space-y-0.5">
                                    <template x-for="c in filtered" :key="c">
                                        <li @click="selectCity(c)"
                                            class="cursor-pointer select-none py-2 px-3 text-xs rounded-xl transition-colors flex items-center justify-between"
                                            :class="city === c ? 'bg-indigo-50 text-indigo-700 font-bold dark:bg-indigo-500/15 dark:text-indigo-300' : 'text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-gray-700/60'">
                                            <span x-text="c"></span>
                                            <svg x-show="city === c" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </li>
                                    </template>
                                    <li x-show="filtered.length === 0" class="py-3 px-3 text-xs text-gray-500 dark:text-slate-400 text-center">
                                        شهری یافت نشد
                                    </li>
                                </ul>
                            </div>
                        </div>

                    @elseif($type === 'textarea')
                        <textarea id="custom_{{ $field->field_name }}" wire:model="state.custom.{{ $field->field_name }}" rows="3" class="{{ $inputClass }} resize-none leading-relaxed"></textarea>

                    @elseif($type === 'file')
                        @php
                            $currentFile = $this->state['custom'][$field->field_name] ?? null;
                            $isUploadedTmp = $currentFile instanceof \Illuminate\Http\UploadedFile;
                        @endphp
                        <div class="relative">
                            <div wire:loading.flex wire:target="state.custom.{{ $field->field_name }}"
                                 class="absolute inset-0 z-20 bg-white/90 dark:bg-gray-900/90 rounded-xl flex items-center justify-center gap-2 border-2 border-indigo-500">
                                <span class="w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></span>
                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-300">در حال بارگذاری...</span>
                            </div>
                            <label for="custom_{{ $field->field_name }}"
                                   class="flex items-center justify-between gap-4 p-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50 hover:border-indigo-500 cursor-pointer transition">
                                @if($isUploadedTmp)
                                    <div class="flex items-center gap-3">
                                        @if(method_exists($currentFile, 'temporaryUrl') && str_starts_with((string) $currentFile->getMimeType(), 'image/'))
                                            <img src="{{ $currentFile->temporaryUrl() }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700" alt="Preview">
                                        @endif
                                        <div>
                                            <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $currentFile->getClientOriginalName() }}</div>
                                            <div class="text-[11px] text-emerald-600 dark:text-emerald-400">فایل جدید آماده ذخیره است</div>
                                        </div>
                                    </div>
                                @elseif($existing && is_string($existing))
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">✓</span>
                                        <div>
                                            <div class="text-xs font-bold text-emerald-700 dark:text-emerald-300">{{ __('فایل از قبل بارگذاری شده است.') }}</div>
                                            <div class="text-[11px] text-slate-600 dark:text-slate-300">{{ __('برای جایگزینی کلیک کنید') }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-xs text-slate-600 dark:text-slate-300">
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ __('برای انتخاب فایل کلیک کنید') }}</span>
                                        <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">JPG، PNG، WEBP یا PDF (حداکثر ۵ مگابایت)</span>
                                    </div>
                                @endif
                            </label>
                            <input id="custom_{{ $field->field_name }}" type="file" wire:model="state.custom.{{ $field->field_name }}" class="hidden" accept="image/*,.pdf" />
                        </div>

                    @elseif($type === 'select' && is_array($opts))
                        <div class="relative">
                            <select id="custom_{{ $field->field_name }}" wire:model="state.custom.{{ $field->field_name }}" class="{{ $inputClass }} appearance-none cursor-pointer !pl-11">
                                <option value="">{{ __('انتخاب کنید...') }}</option>
                                @foreach($opts as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>

                    @elseif($type === 'radio' && is_array($opts))
                        <div class="flex flex-wrap gap-4 pt-2">
                            @foreach($opts as $opt)
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" wire:model="state.custom.{{ $field->field_name }}" value="{{ $opt }}" class="peer sr-only">
                                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all dark:border-gray-600"></div>
                                        <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>

                    @elseif($type === 'checkbox' && is_array($opts))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            @foreach($opts as $opt)
                                <label class="flex items-center gap-3 cursor-pointer p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 transition-colors">
                                    <input type="checkbox" wire:model="state.custom.{{ $field->field_name }}" value="{{ $opt }}" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>

                    @else
                        <input id="custom_{{ $field->field_name }}" type="{{ in_array($type, $inputTypes) ? $type : 'text' }}" class="{{ $inputClass }} {{ $isLtrField ? 'dir-ltr text-left font-sans' : '' }}" wire:model="state.custom.{{ $field->field_name }}" />
                    @endif

                    <x-input-error for="custom.{{ $field->field_name }}" class="mt-2 text-xs text-red-500 font-bold" />
                </div>
            @endforeach
        @endif
    </div>

    {{-- فوتر و ذخیره --}}
    <div class="{{ $footerClass }}">
        <x-action-message class="me-3 text-sm font-bold text-emerald-600 dark:text-emerald-400" on="saved">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ __('اطلاعات با موفقیت ذخیره شد.') }}
            </span>
        </x-action-message>

        <button type="submit" wire:loading.attr="disabled" class="{{ $btnPrimaryClass }}">
            <span wire:loading.remove wire:target="updateProfileInformation" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                {{ __('ذخیره تغییرات') }}
            </span>
            <span wire:loading wire:target="updateProfileInformation" class="flex items-center gap-2">
                <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                {{ __('در حال ذخیره...') }}
            </span>
        </button>
    </div>
</form>
