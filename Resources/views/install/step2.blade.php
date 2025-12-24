@extends('layouts.install')

@section('content')
    <h2 class="text-xl font-semibold mb-4">مرحله ۲: ایجاد کاربر ادمین کل</h2>
    <p class="text-gray-600 mb-6">اتصال به دیتابیس موفق بود. اکنون کاربر ادمین اصلی را بسازید.</p>
    @if ($errors->any())
        <div style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem;">
            <strong>خطا رخ داده است:</strong>
            <ul style="margin-top: 0.5rem; list-style-position: inside;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('install.processStep2') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">نام کامل</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">ایمیل (نام کاربری)</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-left" dir="ltr">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">رمز عبور</label>
            <input type="password" id="password" name="password" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-left" dir="ltr">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">تکرار رمز عبور</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-left" dir="ltr">
        </div>

        <button type="submit"
                class="w-full bg-green-600 text-white py-2 px-4 rounded-md font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200">
            نصب و ایجاد ادمین
        </button>
    </form>
@endsection

