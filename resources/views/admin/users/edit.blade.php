@extends('layouts.app')

@section('content')
    @php
        /** @var \App\Models\User $user */
        $isEdit = $user && $user->exists;
        $action = $isEdit ? route('admin.users.update', $user) : route('admin.users.store');
        $method = $isEdit ? 'PUT' : 'POST';
        $currentRole = old('role', optional($user->roles->first())->name);
    @endphp

    <div class="max-w-3xl mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold">
                {{ $isEdit ? 'ویرایش کاربر' : 'ایجاد کاربر جدید' }}
            </h1>

            <div class="space-x-2 space-x-reverse">
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 border rounded text-gray-700">
                    بازگشت
                </a>

                @if($isEdit)
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('حذف این کاربر؟')">
                        @csrf
                        @method('DELETE')
                        <button class="px-3 py-2 bg-red-600 text-white rounded">حذف</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- پیام موفقیت --}}
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        {{-- خطاها --}}
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc mr-5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $action }}" class="space-y-5">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium mb-1">نام</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">ایمیل</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">شماره موبایل</label>
                <input name="mobile" value="{{ old('mobile', $user->mobile) }}" class="w-full border rounded p-2" placeholder="مثلاً 0912xxxxxxx">
                <p class="text-gray-500 text-xs mt-1">اختیاری (در صورت نیاز می‌تواند یکتا باشد).</p>
            </div>

            <div class="{{ $isEdit ? '' : '' }}">
                <label class="block text-sm font-medium mb-1">
                    رمز عبور {{ $isEdit ? '(برای تغییر پر کنید)' : '' }}
                </label>
                <input type="password" name="password" class="w-full border rounded p-2" {{ $isEdit ? '' : 'required' }}>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">تأیید رمز عبور</label>
                <input type="password" name="password_confirmation" class="w-full border rounded p-2" {{ $isEdit ? '' : 'required' }}>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">نقش</label>
                <select name="role" class="w-full border rounded p-2" required>
                    <option value="" disabled {{ $currentRole ? '' : 'selected' }}>انتخاب نقش...</option>
                    @foreach($roles as $name => $label)
                        <option value="{{ $name }}" {{ $currentRole === $name ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pt-2">
                <button class="px-4 py-2 bg-gray-900 text-white rounded">
                    {{ $isEdit ? 'ذخیره تغییرات' : 'ایجاد کاربر' }}
                </button>
            </div>
        </form>
    </div>
@endsection
