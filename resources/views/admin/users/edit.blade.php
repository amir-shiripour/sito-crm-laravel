@extends('layouts.admin')
@php($title = (isset($user) && $user instanceof \App\Models\User && $user->exists) ? 'ویرایش کاربر' : 'کاربر جدید')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="font-semibold text-gray-900 dark:text-gray-100">
                {{ (isset($user) && $user instanceof \App\Models\User && $user->exists) ? 'ویرایش کاربر' : 'ایجاد کاربر' }}
            </h1>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}"
                   class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                    بازگشت
                </a>

                @if(isset($user) && $user instanceof \App\Models\User && $user->exists)
                    <form method="POST" action="{{ route('admin.users.destroy',$user) }}" class="inline"
                          onsubmit="return confirm('حذف این کاربر؟')">
                        @csrf @method('DELETE')
                        <button class="px-3 py-2 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700">حذف</button>
                    </form>
                @endif
            </div>
        </div>

        <form method="POST"
              action="{{ (isset($user) && $user instanceof \App\Models\User && $user->exists)
                        ? route('admin.users.update',$user)
                        : route('admin.users.store') }}"
              class="p-6 space-y-5">
            @csrf
            @if(isset($user) && $user instanceof \App\Models\User && $user->exists)
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">نام</label>
                <input name="name" value="{{ old('name', $user->name ?? '') }}"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">ایمیل</label>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">شماره موبایل</label>
                <input name="mobile" value="{{ old('mobile', $user->mobile ?? '') }}"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       placeholder="0912xxxxxxx">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">
                    رمز عبور {{ (isset($user) && $user instanceof \App\Models\User && $user->exists) ? '(برای تغییر پر کنید)' : '' }}
                </label>
                <input type="password" name="password"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       @if(!(isset($user) && $user instanceof \App\Models\User && $user->exists)) required @endif>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">تأیید رمز عبور</label>
                <input type="password" name="password_confirmation"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       @if(!(isset($user) && $user instanceof \App\Models\User && $user->exists)) required @endif>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">نقش</label>
                <select name="role"
                        class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                        required>
                    <option value="" disabled {{ old('role', optional(optional($user)->roles->first())->name) ? '' : 'selected' }}>
                        انتخاب نقش...
                    </option>
                    @foreach($roles as $name => $label)
                        <option value="{{ $name }}"
                            {{ old('role', optional(optional($user)->roles->first())->name) === $name ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div class="pt-2">
                <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                    {{ (isset($user) && $user instanceof \App\Models\User && $user->exists) ? 'ذخیره تغییرات' : 'ایجاد کاربر' }}
                </button>
            </div>
        </form>
    </div>
@endsection
