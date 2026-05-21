@php
    $userRoles = auth()->user()->roles->pluck('name')->toArray();
    $userRolesDisplay = auth()->user()->roles->pluck('display_name')->toArray();
    $customFields = \App\Models\CustomUserField::whereIn('role_name', $userRoles)->orderBy('id')->get()->unique('field_name');
    $customValues = \App\Models\UserCustomValue::where('user_id', auth()->id())->get()->keyBy('field_name');
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
                {{ __('اطلاعات پروفایل، آدرس ایمیل و اطلاعات تکمیلی حساب کاربری خود را به‌روزرسانی کنید.') }}
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

        <!-- Email -->
        <div class="md:col-span-1">
            <label for="email" class="{{ $labelClass }}">{{ __('ایمیل') }} <span class="text-red-500">*</span></label>
            <div class="relative">
                <input id="email" type="email" class="{{ $inputClass }} dir-ltr text-left !pl-11" wire:model="state.email" required autocomplete="username" placeholder="example@domain.com" />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
            </div>
            <x-input-error for="email" class="mt-2 text-xs text-red-500 font-bold" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <div class="p-3 mt-3 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800 text-sm">
                    <p class="text-amber-800 dark:text-amber-300 font-medium">
                        {{ __('آدرس ایمیل شما تأیید نشده است.') }}
                        <button type="button" class="underline font-bold hover:text-amber-900 dark:hover:text-amber-100 focus:outline-none" wire:click.prevent="sendEmailVerification">
                            {{ __('ارسال مجدد ایمیل تأیید.') }}
                        </button>
                    </p>
                    @if ($this->verificationLinkSent)
                        <p class="mt-2 font-bold text-emerald-600 dark:text-emerald-400">
                            {{ __('یک لینک تأیید جدید به آدرس ایمیل شما ارسال شد.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Mobile -->
        <div class="md:col-span-1">
            <label for="mobile" class="{{ $labelClass }}">{{ __('شماره موبایل') }}</label>
            <div class="relative">
                <input id="mobile" type="tel" class="{{ $inputClass }} dir-ltr text-left !pl-11" wire:model="state.mobile" autocomplete="tel" placeholder="09123456789" />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                </div>
            </div>
            <x-input-error for="mobile" class="mt-2 text-xs text-red-500 font-bold" />
        </div>

        <!-- Role (Readonly) -->
        <div class="md:col-span-1">
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
            <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500">{{ __('نقش کاربری تنها توسط مدیر سیستم قابل تغییر است.') }}</p>
        </div>

        <!-- Custom Fields Divider -->
        @if($customFields->count() > 0)
            <div class="md:col-span-2 mt-2 pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="{{ $titleClass }}"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ __('اطلاعات تکمیلی') }}</h3>
                <p class="{{ $descClass }}">{{ __('این فیلدها بر اساس نقش کاربری شما در سیستم تعیین شده‌اند.') }}</p>
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

                    $isFullWidth = in_array($type, ['textarea']);
                    $opts = is_array($field->meta) ? ($field->meta['options'] ?? null) : (is_string($field->meta) ? (json_decode($field->meta, true)['options'] ?? null) : null);
                    $isLtrField = in_array($type, ['number', 'email', 'url', 'tel', 'color']);
                @endphp

                <div class="{{ $isFullWidth ? 'md:col-span-2' : 'md:col-span-1' }}">
                    <label for="custom_{{ $field->field_name }}" class="{{ $labelClass }}">
                        {{ $field->label }}
                        @if($field->is_required) <span class="text-red-500">*</span> @endif
                    </label>

                    @if($type === 'textarea')
                        <textarea id="custom_{{ $field->field_name }}" wire:model="state.custom.{{ $field->field_name }}" rows="3" class="{{ $inputClass }} resize-none leading-relaxed"></textarea>

                    @elseif($type === 'file')
                        <div class="flex items-center justify-center w-full p-4 border border-dashed rounded-xl border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50">
                            <div class="text-sm text-center text-gray-500 dark:text-gray-400">
                                {{ __('امکان تغییر فایل برای این فیلد از طریق این فرم در دسترس نیست.') }}
                                @if($existing && is_string($existing))
                                    <div class="mt-2 text-indigo-600 dark:text-indigo-400 font-bold">{{ __('فایل از قبل بارگذاری شده است.') }}</div>
                                @endif
                            </div>
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
                        <input id="custom_{{ $field->field_name }}" type="{{ in_array($type, $inputTypes) ? $type : 'text' }}" class="{{ $inputClass }} {{ $isLtrField ? 'dir-ltr text-left font-mono' : '' }}" wire:model="state.custom.{{ $field->field_name }}" />
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
