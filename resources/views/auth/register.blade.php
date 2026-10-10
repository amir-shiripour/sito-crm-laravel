<x-guest-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 p-6 dark:bg-gray-950 font-sans"
         x-data="registrationWizard({
             initialRole: '{{ $selectedRole ? $selectedRole->name : ($roles->first()->name ?? '') }}',
             csrfToken: '{{ csrf_token() }}',
             sendOtpUrl: '{{ route('register.send-otp') }}',
             verifyOtpUrl: '{{ route('register.verify-otp') }}'
         })">

        {{-- لوگو و عنوان --}}
        <div class="mb-8 text-center mt-8">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white font-sans">ثبت نام حساب کاربری</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-sans">
                <span x-show="role === 'property_host'">عضویت و ایجاد حساب میزبانی اقامتگاه</span>
                <span x-show="role !== 'property_host'">لطفاً اطلاعات خود را برای عضویت وارد کنید</span>
            </p>
        </div>

        {{-- کارت فرم با کنترل وضعیت مراحل (step) در Alpine.js --}}
        <div class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-xl shadow-gray-200/50 dark:bg-gray-900 dark:shadow-none border border-gray-100 dark:border-gray-800 mb-8">

            {{-- نمایش خطاها سیستم --}}
            <x-validation-errors class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-900/30" />

            @if (session('status'))
                <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                    {{ session('status') }}
                </div>
            @endif

            {{-- پیام‌های تعاملی OTP --}}
            <div x-show="otpAlert.message" x-cloak class="mb-6 rounded-xl p-4 text-sm font-medium border transition-all"
                 :class="otpAlert.type === 'error' ? 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 border-red-100 dark:border-red-900/30' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30'">
                <div class="flex items-center gap-2">
                    <svg x-show="otpAlert.type === 'error'" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <svg x-show="otpAlert.type === 'success'" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="otpAlert.message"></span>
                </div>
            </div>

            {{-- نوار پیشرفت (Stepper) --}}
            <div class="mb-8 relative" dir="rtl">
                {{-- خطوط پس زمینه --}}
                <div class="absolute top-1/2 right-0 w-full h-1 bg-gray-100 dark:bg-gray-800 rounded-full -translate-y-1/2 z-0"></div>

                {{-- خط پیشرفت رنگی (محاسبه عرض بر اساس مرحله) --}}
                <div class="absolute top-1/2 right-0 h-1 bg-indigo-600 rounded-full -translate-y-1/2 z-0 transition-all duration-500 ease-out"
                     :style="'width: ' + (role === 'property_host' ? ((step - 1) * 100) : ((step - 1) * 50)) + '%'"></div>

                <div class="relative z-10 flex justify-between items-center w-full">
                    {{-- دکمه مرحله 1 --}}
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold font-sans transition-all duration-300 shadow-sm border-2"
                             :class="step >= 1 ? 'bg-indigo-600 border-indigo-600 text-white shadow-indigo-600/30' : 'bg-white border-gray-200 text-gray-400 dark:bg-gray-900 dark:border-gray-700'">
                            ۱
                        </div>
                        <span class="mt-2 text-xs font-medium font-sans transition-colors" :class="step >= 1 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400'">اطلاعات پایه</span>
                    </div>

                    {{-- دکمه مرحله 2 --}}
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold font-sans transition-all duration-300 shadow-sm border-2"
                             :class="step >= 2 ? 'bg-indigo-600 border-indigo-600 text-white shadow-indigo-600/30' : 'bg-white border-gray-200 text-gray-400 dark:bg-gray-900 dark:border-gray-700'">
                            ۲
                        </div>
                        <span class="mt-2 text-xs font-medium font-sans transition-colors" :class="step >= 2 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400'">
                            <span x-show="role === 'property_host'">تایید شماره و ورود</span>
                            <span x-show="role !== 'property_host'">تکمیلی</span>
                        </span>
                    </div>

                    {{-- دکمه مرحله 3 (تنها برای سایر نقش‌ها) --}}
                    <div class="flex flex-col items-center" x-show="role !== 'property_host'" x-cloak>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold font-sans transition-all duration-300 shadow-sm border-2"
                             :class="step >= 3 ? 'bg-indigo-600 border-indigo-600 text-white shadow-indigo-600/30' : 'bg-white border-gray-200 text-gray-400 dark:bg-gray-900 dark:border-gray-700'">
                            ۳
                        </div>
                        <span class="mt-2 text-xs font-medium font-sans transition-colors" :class="step >= 3 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400'">امنیت و ثبت</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('register') }}" x-ref="registerForm" id="registerForm" class="space-y-6" enctype="multipart/form-data" @submit="onFormSubmit($event)">
                @csrf

                {{-- ================= مرحله 1: اطلاعات پایه ================= --}}
                <div x-show="step === 1" x-ref="step1" x-transition.opacity.duration.300ms class="space-y-6">
                    {{-- نقش کاربری (تنها در صورتی نمایش داده می‌شود که بیش از یک نقش مجاز وجود داشته باشد) --}}
                    @if($roles->count() > 1)
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 font-sans">نقش کاربری</label>
                            <select name="role" id="role"
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 px-4 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer font-sans"
                                    x-model="role" @change="onRoleChange()">
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}" {{ ($selectedRole && $selectedRole->name === $r->name) ? 'selected' : '' }}>
                                        {{ $r->display_name ?? $r->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="role" x-model="role" value="{{ $roles->first()->name }}">
                    @endif

                    {{-- نام --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 font-sans">نام و نام خانوادگی</label>
                        <input id="name" type="text" name="name" x-model="name" required autofocus autocomplete="name"
                               class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 px-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 font-sans"
                               placeholder="نام و نام خانوادگی خود را وارد کنید" />
                    </div>

                    {{-- شماره موبایل --}}
                    <div>
                        <label for="mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 font-sans">
                            شماره موبایل
                            <span x-show="role === 'property_host'" class="text-indigo-600 dark:text-indigo-400 text-xs font-bold font-sans">(جهت دریافت کد تایید)</span>
                        </label>
                        <div class="relative">
                            <input id="mobile" type="text" name="mobile" x-model="mobile" required autocomplete="mobile"
                                   class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr font-sans"
                                   placeholder="09123456789" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- ایمیل (برای نقش میزبان اقامتگاه که اختیاری است نمایش داده نمی‌شود و برای سایر نقش‌ها نمایش داده می‌شود) --}}
                    <div x-show="role !== 'property_host'" x-cloak>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 font-sans">ایمیل</label>
                        </div>
                        <div class="relative">
                            <input id="email" type="email" name="email" x-model="email" :required="role !== 'property_host'" autocomplete="username"
                                   class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr font-sans"
                                   placeholder="user@example.com" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= مرحله 2: تایید پیامکی (میزبان) یا اطلاعات تکمیلی (سایرین) ================= --}}
                <div x-show="step === 2" x-ref="step2" x-transition.opacity.duration.300ms style="display: none;" class="space-y-6">

                    {{-- حالت میزبان: ورود کد تایید پیامکی --}}
                    <template x-if="role === 'property_host'">
                        <div class="space-y-6">
                            <div class="rounded-xl bg-indigo-50 dark:bg-indigo-950/40 p-4 border border-indigo-100 dark:border-indigo-800/40 text-sm text-indigo-900 dark:text-indigo-200">
                                <div class="flex items-center justify-between">
                                    <span>کد تایید به شماره <strong class="dir-ltr inline-block font-sans font-bold" x-text="mobile"></strong> ارسال شد.</span>
                                    <button type="button" @click="step = 1" class="text-xs text-indigo-600 dark:text-indigo-400 underline font-sans">تغییر شماره</button>
                                </div>
                            </div>

                            <div>
                                <label for="otp_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">کد تایید ۵ رقمی</label>
                                <div class="relative">
                                    <input id="otp_code" type="text" maxlength="6" x-model="otpCode"
                                           class="block w-full text-center tracking-[0.5em] text-2xl font-bold rounded-xl border-gray-200 bg-gray-50 py-3 px-4 text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr font-sans"
                                           placeholder="• • • • •"
                                           @keyup.enter.prevent="submitHostRegistration()" />
                                </div>
                            </div>

                            <div class="flex items-center justify-center pt-2">
                                <button type="button" @click="sendOtp()" :disabled="isSendingOtp || otpCooldown > 0"
                                        class="text-xs font-semibold font-sans transition-colors py-1 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800"
                                        :class="otpCooldown > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-indigo-600 dark:text-indigo-400 hover:text-indigo-700'">
                                    <span x-show="otpCooldown > 0">
                                        ارسال مجدد کد پس از <span class="font-bold" x-text="otpCooldown"></span> ثانیه
                                    </span>
                                    <span x-show="otpCooldown <= 0 && !isSendingOtp">ارسال مجدد کد تایید پیامکی</span>
                                    <span x-show="isSendingOtp">در حال ارسال کد...</span>
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- حالت سایر نقش‌ها: فیلدهای سفارشی متداول --}}
                    <template x-if="role !== 'property_host'">
                        <div class="space-y-6">
                            <div class="rounded-xl bg-indigo-50 dark:bg-indigo-900/20 p-4 border border-indigo-100 dark:border-indigo-800/30 text-sm text-indigo-800 dark:text-indigo-300">
                                در این بخش لطفاً اطلاعات تکمیلی مرتبط با نقش انتخابی خود را وارد نمایید.
                            </div>

                            {{-- فیلدهای سفارشی --}}
                            <div>
                                @if(isset($allCustomFields))
                                    @foreach($allCustomFields as $roleName => $fields)
                                        <div x-show="role === '{{ $roleName }}'" class="space-y-5">
                                            @foreach($fields as $field)
                                                <div>
                                                    <label for="custom_{{ $field->field_name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 font-sans">
                                                        {{ $field->label }}{{ $field->is_required ? ' *' : '' }}
                                                    </label>

                                                    @if($field->field_type === 'textarea')
                                                        <textarea id="custom_{{ $field->field_name }}" name="custom_fields[{{ $field->field_name }}]"
                                                                  class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 px-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 font-sans"
                                                                  rows="3" {{ $field->is_required ? 'required' : '' }}>{{ old('custom_fields.'.$field->field_name) }}</textarea>

                                                    @elseif($field->field_type === 'file')
                                                        <div x-data="{
                                                                fileName: '',
                                                                filePreview: null,
                                                                isImage: false,
                                                                handleFile(e) {
                                                                    const file = e.target.files[0];
                                                                    if (!file) {
                                                                        this.fileName = '';
                                                                        this.filePreview = null;
                                                                        return;
                                                                    }
                                                                    this.fileName = file.name;
                                                                    this.isImage = file.type.startsWith('image/');
                                                                    if (this.isImage) {
                                                                        this.filePreview = URL.createObjectURL(file);
                                                                    } else {
                                                                        this.filePreview = null;
                                                                    }
                                                                }
                                                             }" class="relative w-full mt-1">

                                                            <label for="custom_{{ $field->field_name }}" class="relative flex flex-col items-center justify-center w-full py-6 px-4 transition-all bg-white dark:bg-gray-800/50 border-2 border-gray-200 dark:border-gray-700 border-dashed rounded-xl cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-gray-800 group overflow-hidden">
                                                                <div x-show="!fileName" class="flex flex-col items-center space-y-2 text-center transition-all duration-300">
                                                                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/40 rounded-full text-indigo-500 group-hover:scale-110 transition-transform">
                                                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                                        </svg>
                                                                    </div>
                                                                    <div>
                                                                        <span class="font-bold text-sm text-indigo-600 dark:text-indigo-400 font-sans">برای آپلود فایل کلیک کنید</span>
                                                                    </div>
                                                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-sans">یا فایل خود را اینجا رها کنید</p>
                                                                </div>

                                                                <div x-show="fileName" style="display: none;" class="flex flex-col items-center w-full space-y-3 z-10 relative">
                                                                    <template x-if="isImage">
                                                                        <div class="relative w-20 h-20 rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700">
                                                                            <img :src="filePreview" class="object-cover w-full h-full" alt="Preview">
                                                                        </div>
                                                                    </template>
                                                                    <template x-if="!isImage">
                                                                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-full text-emerald-500">
                                                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                            </svg>
                                                                        </div>
                                                                    </template>
                                                                    <div class="flex flex-col items-center w-full px-4">
                                                                        <span x-text="fileName" class="text-sm font-semibold text-gray-700 dark:text-gray-200 truncate w-full text-center dir-ltr font-sans"></span>
                                                                        <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 mt-1 font-sans">فایل آماده ارسال</span>
                                                                    </div>
                                                                </div>
                                                            </label>

                                                            <input id="custom_{{ $field->field_name }}"
                                                                   type="file"
                                                                   name="custom_fields[{{ $field->field_name }}]"
                                                                   class="hidden"
                                                                   @change="handleFile"
                                                                {{ $field->is_required ? 'required' : '' }}>

                                                            <button type="button" x-show="fileName" style="display: none;"
                                                                    @click.prevent="fileName = ''; filePreview = null; document.getElementById('custom_{{ $field->field_name }}').value = '';"
                                                                    class="absolute top-2 left-2 p-1.5 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-full transition-colors z-20 shadow-sm border border-red-100 dark:border-red-800" title="حذف فایل">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <input id="custom_{{ $field->field_name }}" type="{{ $field->field_type }}" name="custom_fields[{{ $field->field_name }}]" value="{{ old('custom_fields.'.$field->field_name) }}" {{ $field->is_required ? 'required' : '' }}
                                                        class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 px-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 font-sans {{ in_array($field->field_type, ['email', 'number']) ? 'dir-ltr' : '' }}" />
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </template>
                </div>

                {{-- ================= مرحله 3: امنیت و ثبت نهایی ================= --}}
                <div x-show="step === 3 && role !== 'property_host'" x-ref="step3" x-transition.opacity.duration.300ms style="display: none;" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        {{-- رمز عبور --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">رمز عبور</label>
                            <div class="relative" x-data="{ show: false }">
                                <input id="password" x-bind:type="show ? 'text' : 'password'" name="password" :required="role !== 'property_host'" :disabled="role === 'property_host'" autocomplete="new-password"
                                       class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-10 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr font-sans"
                                       placeholder="••••••••" />

                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>

                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-indigo-500 focus:outline-none transition-colors">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" x-cloak style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- تکرار رمز عبور --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">تکرار رمز عبور</label>
                            <div class="relative" x-data="{ show: false }">
                                <input id="password_confirmation" x-bind:type="show ? 'text' : 'password'" name="password_confirmation" :required="role !== 'property_host'" :disabled="role === 'property_host'" autocomplete="new-password"
                                       class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-10 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr font-sans"
                                       placeholder="••••••••" />

                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>

                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-indigo-500 focus:outline-none transition-colors">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" x-cloak style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- شرایط و قوانین --}}
                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="mt-6 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <label for="terms" class="flex items-center cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input id="terms" type="checkbox" name="terms" :required="role !== 'property_host'" :disabled="role === 'property_host'" class="peer h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 cursor-pointer" />
                                </div>
                                <span class="mr-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400 transition-colors font-sans">
                                    من با <a target="_blank" href="{{ route('terms.show') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">شرایط استفاده</a> و <a target="_blank" href="{{ route('policy.show') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">سیاست حریم خصوصی</a> موافقم.
                                </span>
                            </label>
                        </div>
                    @endif
                </div>

                {{-- ================= کنترلرهای دکمه‌های فرم ================= --}}
                <div class="pt-6 mt-8 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">

                    {{-- دکمه قبلی --}}
                    <button type="button" x-show="step > 1" @click="step--"
                            class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 font-sans">
                        مرحله قبل
                    </button>
                    <div x-show="step === 1"></div>

                    {{-- دکمه بعدی (مرحله ۱ برای همه، و مرحله ۲ برای سایر نقش‌ها) --}}
                    <button type="button" x-show="step === 1 || (step === 2 && role !== 'property_host')" @click="nextStep()" :disabled="isSendingOtp || isVerifyingOtp"
                            class="group relative flex justify-center items-center rounded-xl bg-indigo-600 py-2.5 px-6 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 transition-all active:scale-[0.98] font-sans disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="step === 1 && !isSendingOtp">مرحله بعد</span>
                        <span x-show="step === 1 && isSendingOtp">در حال ارسال کد...</span>

                        <span x-show="step === 2 && role !== 'property_host'">مرحله بعد</span>

                        <span class="mr-2 flex items-center" x-show="!isSendingOtp && !isVerifyingOtp">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" /></svg>
                        </span>
                    </button>

                    {{-- دکمه تایید و ثبت‌نام مرحله ۲ برای میزبان --}}
                    <button type="button" x-show="step === 2 && role === 'property_host'" @click="submitHostRegistration()" :disabled="isVerifyingOtp"
                            class="group relative flex justify-center items-center rounded-xl bg-emerald-600 py-2.5 px-8 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-500 hover:shadow-emerald-600/40 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 transition-all active:scale-[0.98] font-sans disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="!isVerifyingOtp">تایید کد و تکمیل ثبت‌نام</span>
                        <span x-show="isVerifyingOtp">در حال بررسی و ورود...</span>
                        <span class="mr-2 flex items-center" x-show="!isVerifyingOtp">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </span>
                    </button>

                    {{-- دکمه ثبت نهایی (مرحله ۳ برای سایر نقش‌ها) --}}
                    <button type="submit" x-show="step === 3 && role !== 'property_host'" style="display: none;"
                            class="group relative flex justify-center rounded-xl bg-emerald-600 py-2.5 px-8 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-500 hover:shadow-emerald-600/40 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 transition-all active:scale-[0.98] font-sans">
                        تکمیل ثبت‌نام
                        <span class="mr-2 flex items-center">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </span>
                    </button>

                </div>

                <div class="text-center mt-4 pb-2">
                    <a class="text-sm font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors font-sans" href="{{ route('login') }}">
                        قبلاً ثبت نام کرده‌اید؟ ورود به سیستم
                    </a>
                </div>

            </form>
        </div>

        {{-- فوتر ثبت نام --}}
        <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400 pb-8 font-sans">
            &copy; {{ date('Y') }} تمامی حقوق محفوظ است.
        </p>
    </div>

    <script>
        function registrationWizard(config) {
            return {
                step: 1,
                role: config.initialRole || 'property_host',
                name: '{{ old('name') }}',
                mobile: '{{ old('mobile') }}',
                email: '{{ old('email') }}',
                otpCode: '',
                isMobileVerified: false,
                isSendingOtp: false,
                isVerifyingOtp: false,
                otpCooldown: 0,
                cooldownTimer: null,
                otpAlert: { type: '', message: '' },

                init() {
                    // Start reactive state
                },

                showAlert(message, type = 'error') {
                    this.otpAlert = { type, message };
                    if (type === 'success') {
                        setTimeout(() => {
                            if (this.otpAlert.type === 'success') {
                                this.otpAlert = { type: '', message: '' };
                            }
                        }, 5000);
                    }
                },

                onRoleChange() {
                    this.otpAlert = { type: '', message: '' };
                    this.isMobileVerified = false;
                    this.step = 1;
                },

                startCooldown(seconds) {
                    this.otpCooldown = seconds;
                    if (this.cooldownTimer) clearInterval(this.cooldownTimer);
                    this.cooldownTimer = setInterval(() => {
                        this.otpCooldown--;
                        if (this.otpCooldown <= 0) {
                            clearInterval(this.cooldownTimer);
                            this.cooldownTimer = null;
                        }
                    }, 1000);
                },

                async sendOtp() {
                    if (!this.mobile || this.mobile.trim().length < 10) {
                        this.showAlert('لطفاً شماره موبایل معتبر وارد کنید.');
                        return false;
                    }

                    this.isSendingOtp = true;
                    this.otpAlert = { type: '', message: '' };

                    try {
                        const response = await fetch(config.sendOtpUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ mobile: this.mobile })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.showAlert(data.message || 'کد تایید پیامک شد.', 'success');
                            this.startCooldown(data.resend_in || 60);
                            return true;
                        } else {
                            this.showAlert(data.message || 'خطا در ارسال کد تایید.');
                            if (data.resend_in) {
                                this.startCooldown(data.resend_in);
                            }
                            return false;
                        }
                    } catch (e) {
                        this.showAlert('خطای ارتباط با سرور. لطفاً مجدداً تلاش کنید.');
                        return false;
                    } finally {
                        this.isSendingOtp = false;
                    }
                },

                async verifyOtp() {
                    if (!this.otpCode || this.otpCode.trim().length < 4) {
                        this.showAlert('لطفاً کد تایید دریافتی را وارد کنید.');
                        return false;
                    }

                    this.isVerifyingOtp = true;
                    this.otpAlert = { type: '', message: '' };

                    try {
                        const response = await fetch(config.verifyOtpUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                mobile: this.mobile,
                                code: this.otpCode
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.isMobileVerified = true;
                            this.showAlert('شماره موبایل با موفقیت تایید شد.', 'success');
                            return true;
                        } else {
                            this.showAlert(data.message || 'کد وارد شده معتبر نیست.');
                            return false;
                        }
                    } catch (e) {
                        this.showAlert('خطای بررسی کد تایید. مجدداً تلاش کنید.');
                        return false;
                    } finally {
                        this.isVerifyingOtp = false;
                    }
                },

                async submitHostRegistration() {
                    this.otpAlert = { type: '', message: '' };

                    if (!this.otpCode || this.otpCode.trim().length < 4) {
                        this.showAlert('لطفاً کد تایید ۵ رقمی دریافتی را وارد کنید.');
                        return;
                    }

                    if (!this.isMobileVerified) {
                        const verified = await this.verifyOtp();
                        if (!verified) return;
                    }

                    // Directly submit the registration form
                    if (this.$refs.registerForm) {
                        this.$refs.registerForm.submit();
                    }
                },

                async nextStep() {
                    this.otpAlert = { type: '', message: '' };

                    if (this.step === 1) {
                        const invalid = [...this.$refs.step1.querySelectorAll('input, select, textarea')].find(el => !el.checkValidity());
                        if (invalid) {
                            invalid.reportValidity();
                            return;
                        }

                        if (this.role === 'property_host') {
                            // If host, send OTP and move to step 2
                            const sent = await this.sendOtp();
                            if (sent) {
                                this.step = 2;
                            }
                        } else {
                            // Normal role, move directly to step 2
                            this.step = 2;
                        }
                    } else if (this.step === 2) {
                        if (this.role === 'property_host') {
                            await this.submitHostRegistration();
                        } else {
                            const invalid = [...this.$refs.step2.querySelectorAll('input, select, textarea')].find(el => !el.checkValidity());
                            if (invalid) {
                                invalid.reportValidity();
                                return;
                            }
                            this.step = 3;
                        }
                    }
                },

                onFormSubmit(e) {
                    if (this.role === 'property_host' && !this.isMobileVerified) {
                        e.preventDefault();
                        this.step = 2;
                        this.showAlert('لطفاً ابتدا کد تایید پیامکی را تایید کنید.');
                    }
                }
            };
        }
    </script>
</x-guest-layout>
