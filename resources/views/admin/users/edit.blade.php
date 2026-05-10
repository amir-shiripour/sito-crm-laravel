@extends('layouts.user')

@php
    $isEdit = $user && $user->exists;
    $title = $isEdit ? 'ویرایش کاربر: ' . $user->name : 'ایجاد کاربر جدید';

    // 🚨 هشدارهای معماری: این لاجیک‌ها باید در Controller انجام شوند
    $selectedRolesOld = old('roles');
    $selectedRoles = is_array($selectedRolesOld) ? $selectedRolesOld : (isset($selectedRoles) ? (is_array($selectedRoles) ? $selectedRoles : [$selectedRoles]) : (optional($user)->roles ? $user->roles->pluck('name')->toArray() : []));

    $selectedSuperiorsOld = old('superior_ids');
    $selectedSuperiors = is_array($selectedSuperiorsOld) ? $selectedSuperiorsOld : (optional($user)->superiors ? $user->superiors->pluck('id')->toArray() : []);

    $customValues = collect(optional($user)->customValues ?? [])->keyBy('field_name');

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";

    // ساختار گزینه‌ها برای Select سفارشی (مشابه ماژول Task)
    $roleOptionsForSelect = [];
    foreach($roles as $k => $v) {
        $roleOptionsForSelect[] = [
            'value' => is_object($v) ? ($v->name ?? $k) : $k,
            'label' => is_object($v) ? ($v->display_name ?? $v->name ?? $k) : $v
        ];
    }

    $userOptionsForSelect = [];
    if(isset($allUsers)) {
        foreach($allUsers as $u) {
            $userOptionsForSelect[] = [
                'value' => $u->id,
                'label' => $u->name
            ];
        }
    }
@endphp

@section('content')
    <script>
        function setupRoleMultiSelect(config) {
            return {
                name: config.name,
                options: config.options || [],
                selectedValues: [],
                search: '',
                open: false,
                position: 'bottom',

                init() {
                    const oldValues = Array.isArray(config.oldValues) ? config.oldValues : [];
                    this.selectedValues = oldValues.map(String);
                },

                toggleDropdown() {
                    if (this.open) {
                        this.open = false;
                        return;
                    }
                    this.open = true;
                    this.$nextTick(() => {
                        const rect = this.$el.getBoundingClientRect();
                        const dropdownHeight = this.$refs.dropdown.offsetHeight;
                        if (window.innerHeight - rect.bottom < dropdownHeight && rect.top > dropdownHeight) {
                            this.position = 'top';
                        } else {
                            this.position = 'bottom';
                        }
                        this.$refs.roleSearchInput.focus();
                    });
                },

                isSelected(value) {
                    value = String(value);
                    return this.selectedValues.includes(value);
                },

                toggle(value) {
                    value = String(value);
                    if (this.isSelected(value)) {
                        this.selectedValues = this.selectedValues.filter(v => v !== value);
                    } else {
                        this.selectedValues.push(value);
                    }
                },

                clearValue(value) {
                    value = String(value);
                    this.selectedValues = this.selectedValues.filter(v => v !== value);
                },

                filteredOptions() {
                    const term = (this.search || '').toLowerCase();
                    return (this.options || []).filter(o => (o.label || '').toLowerCase().includes(term));
                }
            };
        }
    </script>

    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6" x-data="{ isSubmitting: false }">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg {{ $isEdit ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300' }}">
                    @if($isEdit)
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                    @endif
                </span>
                    {{ $isEdit ? 'ویرایش کاربر' : 'ایجاد کاربر جدید' }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    {{ $isEdit ? 'به‌روزرسانی اطلاعات حساب، رمز عبور و نقش کاربر.' : 'اطلاعات کاربری، رمز عبور و نقش سیستم را تنظیم کنید.' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    بازگشت
                </a>

                @if($isEdit)
                    @can('users.delete')
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-block" onsubmit="return confirm('آیا از حذف این کاربر اطمینان کامل دارید؟ این عملیات غیرقابل بازگشت است.')">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors border border-transparent dark:border-red-800">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                حذف
                            </button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>

        {{-- نمایش خطاها --}}
        @if($errors->any())
            <div class="rounded-xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/20 dark:border-red-800/50 animate-in fade-in slide-in-from-top-2">
                <div class="flex items-center gap-2 text-red-800 dark:text-red-400 font-bold text-sm mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    لطفاً خطاهای زیر را بررسی و برطرف کنید:
                </div>
                <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-300 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" enctype="multipart/form-data" @submit="isSubmitting = true" class="space-y-6 pb-10">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            {{-- بخش اول: اطلاعات فردی --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        اطلاعات فردی و تماس
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">نام و نام خانوادگی <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="{{ $inputClass }}" required placeholder="مثلاً: علی احمدی">
                    </div>

                    <div x-data="{
                        email: '{{ old('email', $user->email ?? '') }}',
                        init() {
                            @if(!$isEdit && !old('email'))
                                this.generateEmail();
                            @endif
                        },
                        generateEmail() {
                            let host = window.location.hostname;
                            let domain = host.startsWith('panel.') ? host.substring(6) : host;
                            if (domain === 'localhost' || domain === '127.0.0.1' || !domain) {
                                domain = 'domain.com';
                            }
                            let randomString = Math.random().toString(36).substring(2, 8);
                            this.email = 'user_' + randomString + '@' + domain;
                        }
                    }">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">ایمیل <span class="text-red-500">*</span></label>
                            <button type="button" @click="generateEmail()" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center gap-1 transition-colors" title="تولید خودکار ایمیل">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                تولید خودکار
                            </button>
                        </div>
                        <div class="relative">
                            <input type="email" name="email" x-model="email" class="{{ $inputClass }} dir-ltr text-left !pl-11" required placeholder="example@domain.com">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">شماره موبایل</label>
                        <div class="relative">
                            <input type="tel" name="mobile" value="{{ old('mobile', $user->mobile ?? '') }}" class="{{ $inputClass }} dir-ltr text-left !pl-11" placeholder="09123456789">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- بخش دوم: امنیت --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        تنظیمات امنیتی
                    </h2>
                    @if($isEdit)
                        <span class="text-[10px] bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800 px-2.5 py-1 rounded-md font-bold">
                        فقط در صورت نیاز به تغییر رمز عبور، فیلدها را پر کنید
                    </span>
                    @endif
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{
                    show: false,
                    password: '',
                    passwordConfirmation: '',
                    generatePassword() {
                        const length = 12;
                        const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
                        let retVal = '';
                        for (let i = 0, n = charset.length; i < length; ++i) {
                            retVal += charset.charAt(Math.floor(Math.random() * n));
                        }
                        this.password = retVal;
                        this.passwordConfirmation = retVal;
                        this.show = true;
                    }
                }">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">رمز عبور @if(!$isEdit) <span class="text-red-500">*</span> @endif</label>
                            <button type="button" @click="generatePassword()" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center gap-1 transition-colors" title="تولید رمز عبور تصادفی">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                تولید رمز
                            </button>
                        </div>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" x-model="password" class="{{ $inputClass }} dir-ltr text-left font-mono !pr-11" @if(!$isEdit) required @endif placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none transition-colors" tabindex="-1">
                                {{-- آیکون چشم باز (وقتی پنهان است نشان بده) --}}
                                <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                {{-- آیکون چشم بسته (وقتی در حال نمایش است نشان بده) --}}
                                <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0l1.414 1.414m12.022-1.254A9.97 9.97 0 0021.543 12c-1.274 4.057-5.064 7-9.542 7m-1.724-1.724l-3.29-3.29" /></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">تأیید رمز عبور @if(!$isEdit) <span class="text-red-500">*</span> @endif</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" x-model="passwordConfirmation" class="{{ $inputClass }} dir-ltr text-left font-mono !pr-11" @if(!$isEdit) required @endif placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none transition-colors" tabindex="-1">
                                <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0l1.414 1.414m12.022-1.254A9.97 9.97 0 0021.543 12c-1.274 4.057-5.064 7-9.542 7m-1.724-1.724l-3.29-3.29" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- بخش سوم: اتصالات و دسترسی‌ها --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        نقش‌ها و دسترسی‌ها
                    </h2>
                </div>
                <div class="p-6 space-y-6">

                    {{-- انتخاب نقش (کامپوننت سفارشی) --}}
                    <div class="max-w-full" x-data="setupRoleMultiSelect({
                        name: 'roles',
                        options: {{ json_encode($roleOptionsForSelect) }},
                        oldValues: {{ json_encode($selectedRoles) }}
                    })" @click.away="open = false">
                        <label class="{{ $labelClass }}">نقش‌های کاربر در سیستم <span class="text-red-500">*</span></label>

                        <div class="relative group mt-2">
                            {{-- کانتینر اصلی شبیه اینپوت --}}
                            <div
                                class="flex flex-wrap gap-1.5 min-h-[42px] items-center rounded-xl border border-gray-300 bg-white px-2 py-1.5 transition-all duration-200
                                       focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500
                                       dark:bg-gray-800 dark:border-gray-600 dark:focus-within:border-indigo-500 cursor-text shadow-sm"
                                @click="toggleDropdown()"
                            >
                                {{-- نمایش متن راهنما وقتی خالی است --}}
                                <template x-if="selectedValues.length === 0">
                                    <span class="text-sm text-gray-400 dark:text-gray-500 px-1 select-none pointer-events-none">
                                        انتخاب نقش‌ها...
                                    </span>
                                </template>

                                {{-- تگ‌های انتخاب شده --}}
                                <template x-for="value in selectedValues" :key="value">
                                    <div
                                        class="inline-flex items-center gap-1 pl-2 pr-1.5 py-0.5 text-xs font-medium rounded-lg
                                               bg-indigo-50 text-indigo-700 border border-indigo-100
                                               dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20 transition-colors"
                                    >
                                        <span x-text="options.find(o => String(o.value) === String(value))?.label ?? value"></span>
                                        <button type="button"
                                                class="p-0.5 rounded-full hover:bg-indigo-200/50 dark:hover:bg-indigo-500/30 text-indigo-600 dark:text-indigo-400 transition-colors"
                                                @click.stop="clearValue(value)">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </template>

                                {{-- اینپوت جستجو --}}
                                <input
                                    x-ref="roleSearchInput"
                                    type="text"
                                    x-model="search"
                                    class="flex-1 min-w-[80px] border-0 bg-transparent p-0 text-sm text-gray-900 focus:ring-0 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600"
                                    placeholder=""
                                    @keydown.backspace="if(search === '' && selectedValues.length > 0) selectedValues.pop()"
                                >

                                {{-- آیکون فلش کوچک --}}
                                <div class="absolute left-2 text-gray-400 pointer-events-none">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- لیست کشویی --}}
                            <div
                                x-ref="dropdown"
                                x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                x-cloak
                                class="absolute z-50 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg ring-1 ring-black ring-opacity-5
                                       dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10"
                                :class="{ 'bottom-full mb-2': position === 'top', 'mt-1': position === 'bottom' }"
                            >
                                <div class="max-h-60 overflow-y-auto py-1 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">

                                    {{-- گزینه‌ها --}}
                                    <template x-for="opt in filteredOptions()" :key="opt.value">
                                        <button
                                            type="button"
                                            class="relative w-full cursor-pointer select-none py-2 pl-3 pr-9 text-right text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            :class="isSelected(opt.value) ? 'font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700/50' : 'text-gray-700 dark:text-gray-200'"
                                            @click.prevent="toggle(opt.value); $refs.roleSearchInput.focus()"
                                        >
                                            <span x-text="opt.label"></span>
                                            <span x-show="isSelected(opt.value)" class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600 dark:text-indigo-400">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>
                                    </template>

                                    {{-- حالت خالی --}}
                                    <template x-if="filteredOptions().length === 0">
                                        <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center italic">
                                            موردی یافت نشد.
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- هیدن اینپوت‌ها برای ارسال به سرور --}}
                            <template x-for="value in selectedValues" :key="'hidden-roles-' + value">
                                <input type="hidden" name="roles[]" :value="value">
                            </template>
                            {{-- برای جلوگیری از خطای ولیدیشن در صورت خالی بودن --}}
                            <template x-if="selectedValues.length === 0">
                                <input type="hidden" name="roles[]" value="">
                            </template>

                        </div>

                        {{-- فیلدهای سفارشی (داینامیک بر اساس نقش) --}}
                        <div class="space-y-6 mt-6">
                            @isset($customFieldsByRole)
                                @foreach($customFieldsByRole as $roleName => $fields)
                                    <div x-show="selectedValues.includes('{{ $roleName }}')" x-collapse x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 dark:bg-gray-900/30 p-5 sm:p-6 rounded-xl border border-gray-100 dark:border-gray-700/50 shadow-inner">
                                        <div class="md:col-span-2">
                                            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2">فیلدهای اختصاصی نقش: {{ $roles[$roleName]->display_name ?? $roles[$roleName] ?? $roleName }}</h3>
                                        </div>

                                        @foreach($fields as $field)
                                            @php
                                                $type = strtolower($field->field_type ?? 'text');
                                                $cv = $customValues->get($field->field_name);
                                                $existing = $cv ? $cv->value : '';
                                                $value = old('custom.' . $field->field_name, $existing);
                                                $isFullWidth = in_array($type, ['textarea']);

                                                // 🚨 هشدار معماری: json_decode کردن در فایل Blade اشتباه است. باید در Model انجام شود.
                                                $checkedVals = [];
                                                if ($type === 'checkbox') {
                                                    $oldArr = old('custom.' . $field->field_name);
                                                    if (is_array($oldArr)) {
                                                        $checkedVals = $oldArr;
                                                    } else {
                                                        $checkedVals = is_string($existing) ? (json_decode($existing, true) ?: []) : (is_array($existing) ? $existing : []);
                                                    }
                                                }

                                                $opts = $field->meta['options'] ?? null;
                                                $isLtrField = in_array($type, ['number', 'email', 'url', 'tel', 'color']);
                                            @endphp

                                            <div class="{{ $isFullWidth ? 'md:col-span-2' : '' }}">
                                                <label class="{{ $labelClass }}">{{ $field->label }}</label>

                                                @if($type === 'textarea')
                                                    <textarea name="custom[{{ $field->field_name }}]" rows="3" class="{{ $inputClass }} resize-none leading-relaxed">{{ $value }}</textarea>

                                                @elseif($type === 'file')
                                                    <div class="flex items-center justify-center w-full">
                                                        <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-white dark:hover:bg-gray-800 dark:bg-gray-900 hover:bg-gray-50 dark:border-gray-600 dark:hover:border-gray-500 transition-colors">
                                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                                <svg class="w-6 h-6 mb-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">آپلود فایل جدید</p>
                                                            </div>
                                                            <input type="file" name="custom[{{ $field->field_name }}]" class="hidden" />
                                                        </label>
                                                    </div>
                                                    @if($existing && is_string($existing))
                                                        <p class="text-[10px] text-gray-500 mt-1">فایل قبلی بارگذاری شده است.</p>
                                                    @endif

                                                @elseif($type === 'select' && is_array($opts))
                                                    <div class="relative">
                                                        <select name="custom[{{ $field->field_name }}]" class="{{ $inputClass }} appearance-none cursor-pointer !pl-11">
                                                            @foreach($opts as $opt)
                                                                <option value="{{ $opt }}" {{ (string)$value === (string)$opt ? 'selected' : '' }}>{{ $opt }}</option>
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
                                                                    <input type="radio" name="custom[{{ $field->field_name }}]" value="{{ $opt }}" {{ (string)$value === (string)$opt ? 'checked' : '' }} class="peer sr-only">
                                                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all dark:border-gray-600"></div>
                                                                    <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                                                </div>
                                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $opt }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>

                                                @elseif($type === 'checkbox' && is_array($opts))
                                                    <div class="grid grid-cols-2 gap-3 pt-2">
                                                        @foreach($opts as $opt)
                                                            <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 transition-colors">
                                                                <input type="checkbox" name="custom[{{ $field->field_name }}][]" value="{{ $opt }}" {{ in_array($opt, $checkedVals, true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $opt }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>

                                                @else
                                                    <input type="{{ $type }}" name="custom[{{ $field->field_name }}]" value="{{ $value }}" class="{{ $inputClass }} {{ $isLtrField ? 'dir-ltr text-left font-mono' : '' }}">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endisset
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>

                    {{-- اتصال به سایر کاربران --}}
                    <div class="max-w-full" x-data="setupRoleMultiSelect({
                        name: 'superiors',
                        options: {{ json_encode($userOptionsForSelect) }},
                        oldValues: {{ json_encode($selectedSuperiors) }}
                    })" @click.away="open = false">
                        <label class="{{ $labelClass }}">اتصال به سایر کاربران (پزشک / سرپرست)</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">شما می‌توانید این کاربر (مثلاً پذیرش) را به یک یا چند کاربر دیگر (مثلاً پزشک) متصل کنید.</p>

                        <div class="relative group mt-2">
                            {{-- کانتینر اصلی شبیه اینپوت --}}
                            <div
                                class="flex flex-wrap gap-1.5 min-h-[42px] items-center rounded-xl border border-gray-300 bg-white px-2 py-1.5 transition-all duration-200
                                       focus-within:border-cyan-500 focus-within:ring-1 focus-within:ring-cyan-500
                                       dark:bg-gray-800 dark:border-gray-600 dark:focus-within:border-cyan-500 cursor-text shadow-sm"
                                @click="toggleDropdown()"
                            >
                                {{-- نمایش متن راهنما وقتی خالی است --}}
                                <template x-if="selectedValues.length === 0">
                                    <span class="text-sm text-gray-400 dark:text-gray-500 px-1 select-none pointer-events-none">
                                        انتخاب کاربران...
                                    </span>
                                </template>

                                {{-- تگ‌های انتخاب شده --}}
                                <template x-for="value in selectedValues" :key="value">
                                    <div
                                        class="inline-flex items-center gap-1 pl-2 pr-1.5 py-0.5 text-xs font-medium rounded-lg
                                               bg-cyan-50 text-cyan-700 border border-cyan-100
                                               dark:bg-cyan-500/10 dark:text-cyan-400 dark:border-cyan-500/20 transition-colors"
                                    >
                                        <span x-text="options.find(o => String(o.value) === String(value))?.label ?? value"></span>
                                        <button type="button"
                                                class="p-0.5 rounded-full hover:bg-cyan-200/50 dark:hover:bg-cyan-500/30 text-cyan-600 dark:text-cyan-400 transition-colors"
                                                @click.stop="clearValue(value)">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </template>

                                {{-- اینپوت جستجو --}}
                                <input
                                    x-ref="roleSearchInput"
                                    type="text"
                                    x-model="search"
                                    class="flex-1 min-w-[80px] border-0 bg-transparent p-0 text-sm text-gray-900 focus:ring-0 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600"
                                    placeholder=""
                                    @keydown.backspace="if(search === '' && selectedValues.length > 0) selectedValues.pop()"
                                >

                                {{-- آیکون فلش کوچک --}}
                                <div class="absolute left-2 text-gray-400 pointer-events-none">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- لیست کشویی --}}
                            <div
                                x-ref="dropdown"
                                x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                x-cloak
                                class="absolute z-50 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg ring-1 ring-black ring-opacity-5
                                       dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10"
                                :class="{ 'bottom-full mb-2': position === 'top', 'mt-1': position === 'bottom' }"
                            >
                                <div class="max-h-60 overflow-y-auto py-1 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">

                                    {{-- گزینه‌ها --}}
                                    <template x-for="opt in filteredOptions()" :key="opt.value">
                                        <button
                                            type="button"
                                            class="relative w-full cursor-pointer select-none py-2 pl-3 pr-9 text-right text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            :class="isSelected(opt.value) ? 'font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700/50' : 'text-gray-700 dark:text-gray-200'"
                                            @click.prevent="toggle(opt.value); $refs.roleSearchInput.focus()"
                                        >
                                            <span x-text="opt.label"></span>
                                            <span x-show="isSelected(opt.value)" class="absolute inset-y-0 left-0 flex items-center pl-3 text-cyan-600 dark:text-cyan-400">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>
                                    </template>

                                    {{-- حالت خالی --}}
                                    <template x-if="filteredOptions().length === 0">
                                        <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center italic">
                                            موردی یافت نشد.
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- هیدن اینپوت‌ها برای ارسال به سرور --}}
                            <template x-for="value in selectedValues" :key="'hidden-superiors-' + value">
                                <input type="hidden" name="superior_ids[]" :value="value">
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            {{-- دکمه ذخیره --}}
            <div class="flex justify-end pt-4">
                <button type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!isSubmitting">{{ $isEdit ? 'ذخیره تغییرات' : 'ایجاد کاربر جدید' }}</span>
                    <span x-show="isSubmitting">در حال پردازش...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </button>
            </div>

        </form>
    </div>
@endsection
