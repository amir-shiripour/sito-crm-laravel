@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto p-6">
        <h1 class="text-xl font-bold mb-6">ایجاد کاربر جدید</h1>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc mr-5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">نام</label>
                <input name="name" value="{{ old('name') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">ایمیل</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">رمز عبور</label>
                <input type="password" name="password" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">تأیید رمز عبور</label>
                <input type="password" name="password_confirmation" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">نقش‌ها</label>
                <select name="roles[]" multiple class="w-full border rounded p-2">
                    @foreach($roles as $name => $label)
                        <option value="{{ $name }}" @selected(collect(old('roles',[]))->contains($name))>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="text-gray-500 text-xs mt-1">برای انتخاب چند نقش، Ctrl/⌘ را نگه دارید.</p>
            </div>

            <div class="pt-2">
                <button class="px-4 py-2 bg-gray-900 text-white rounded">ایجاد</button>
                <a href="{{ route('admin.users.index') }}" class="ml-2 text-gray-600">بازگشت</a>
            </div>
        </form>
    </div>
@endsection
