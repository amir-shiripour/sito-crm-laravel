{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.user')

@php
    $title = 'ایجاد کاربر جدید';

    // 🚨 هشدار معماری: این منطق گروه‌بندی باید در کنترلر انجام شود و داده آماده به ویو ارسال گردد.
    $safeSelectedRole = old('role', $selectedRole ?? '');
    $grouped = $customFieldsByRole instanceof \Illuminate\Support\Collection
        ? ($customFieldsByRole->first() instanceof \App\Models\CustomUserField
            ? $customFieldsByRole->groupBy('role_name')
            : $customFieldsByRole)
        : collect();

    $inputTypes = ['text','number','email','date','datetime-local','time','month','week','url','tel','password','color','range','hidden'];

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6" x-data="{ role: '{{ $safeSelectedRole }}', isSubmitting: false }">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                </span>
                    ایجاد کاربر جدید
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">اطلاعات کاربری، رمز عبور و نقش سیستم را تنظیم کنید.</p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                بازگشت به لیست
            </a>
        </div>

        {{-- نمایش خطاها --}}
        @if($errors->any())
            <div class="rounded-xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/20 dark:border-red-800/50 animate-in fade-in slide-in-from-top-2">
                <div class="flex items-center gap-2 text-red-800 dark:text-red-400 font-bold text-sm mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    لطفاً خطاهای زیر را برطرف کنید:
                </div>
                <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-300 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" @submit="isSubmitting = true" class="space-y-6 pb-10">
            @csrf

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
                        <input type="text" name="name" value="{{ old('name') }}" class="{{ $inputClass }}" required placeholder="مثلاً: علی احمدی">
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">ایمیل <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="email" name="email" value="{{ old('email') }}" class="{{ $inputClass }} dir-ltr text-left" required placeholder="example@domain.com">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">شماره موبایل</label>
                        <div class="relative">
                            <input type="tel" name="mobile" value="{{ old('mobile') }}" class="{{ $inputClass }} dir-ltr text-left" placeholder="09123456789">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- بخش دوم: امنیت --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        امنیت حساب
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">رمز عبور <span class="text-red-500">*</span></label>
                        <input type="password" name="password" class="{{ $inputClass }} dir-ltr text-left font-mono" required placeholder="••••••••">
                        <p class="text-[10px] text-gray-400 mt-1.5 text-right">حداقل ۸ کاراکتر شامل حروف و اعداد</p>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">تأیید رمز عبور <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" class="{{ $inputClass }} dir-ltr text-left font-mono" required placeholder="••••••••">
                    </div>
                </div>
            </div>

            {{-- بخش سوم: نقش و سطوح دسترسی --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        نقش و دسترسی
                    </h2>
                </div>
                <div class="p-6 space-y-6">

                    {{-- انتخاب نقش --}}
                    <div class="max-w-md">
                        <label class="{{ $labelClass }}">نقش کاربر سیستم <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="role" x-model="role" class="{{ $inputClass }} appearance-none cursor-pointer font-bold text-indigo-700 dark:text-indigo-400" required>
                                <option value="" disabled>انتخاب نقش...</option>
                                @foreach($roles as $k => $v)
                                    @php
                                        $optionValue = is_object($v) ? ($v->name ?? $k) : $k;
                                        $optionLabel = is_object($v) ? ($v->display_name ?? $v->name ?? $k) : $v;
                                    @endphp
                                    <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-indigo-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- فیلدهای سفارشی (داینامیک بر اساس نقش) --}}
                    <div class="space-y-6 mt-6">
                        @foreach($grouped as $roleName => $fields)
                            <div x-show="role === '{{ $roleName }}'" x-collapse x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 dark:bg-gray-900/30 p-5 rounded-xl border border-gray-100 dark:border-gray-700/50">
                                <div class="md:col-span-2">
                                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2">فیلدهای اختصاصی نقش</h3>
                                </div>

                                @foreach($fields as $field)
                                    @php
                                        $type = strtolower($field->field_type ?? 'text');
                                        $valueOld = old('custom.' . $field->field_name);
                                        $isFullWidth = in_array($type, ['textarea']);
                                    @endphp
                                    <div class="{{ $isFullWidth ? 'md:col-span-2' : '' }}">
                                        <label class="{{ $labelClass }}">{{ $field->label }}</label>

                                        @if($type === 'textarea')
                                            <textarea name="custom[{{ $field->field_name }}]" rows="3" class="{{ $inputClass }} resize-none leading-relaxed">{{ $valueOld }}</textarea>

                                        @elseif($type === 'file')
                                            <div class="flex items-center justify-center w-full">
                                                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-white dark:hover:bg-gray-800 dark:bg-gray-900 hover:bg-gray-50 dark:border-gray-600 dark:hover:border-gray-500 transition-colors">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <svg class="w-6 h-6 mb-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">انتخاب فایل</p>
                                                    </div>
                                                    <input type="file" name="custom[{{ $field->field_name }}]" class="hidden" />
                                                </label>
                                            </div>

                                        @elseif(in_array($type, $inputTypes, true))
                                            <input type="{{ $type }}" name="custom[{{ $field->field_name }}]" value="{{ $valueOld }}" class="{{ $inputClass }} {{ in_array($type, ['number', 'email', 'url', 'tel', 'color']) ? 'dir-ltr text-left' : '' }}">

                                        @else
                                            <input type="text" name="custom[{{ $field->field_name }}]" value="{{ $valueOld }}" class="{{ $inputClass }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

            {{-- دکمه ذخیره --}}
            <div class="flex justify-end pt-4">
                <button type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!isSubmitting">ایجاد کاربر جدید</span>
                    <span x-show="isSubmitting">در حال ذخیره...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </button>
            </div>

        </form>
    </div>
@endsection
