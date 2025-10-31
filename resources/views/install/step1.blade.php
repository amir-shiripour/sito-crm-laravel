<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>نصب‌کننده - مرحله ۱: دیتابیس</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Vazirmatn Font -->
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="w-full max-w-lg bg-white rounded-lg shadow-xl overflow-hidden">

    <!-- هدر فرم -->
    <div class="p-6 bg-gray-50 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 text-center">نصب‌کننده CRM</h2>
        <p class="text-center text-gray-600 mt-2">مرحله ۱ از ۳: تنظیمات دیتابیس</p>
    </div>

    <!-- فرم اصلی -->
    <!--
        اصلاح شد:
        action فرم به route('install.processStep1') تغییر کرد
        تا با فایل routes/web.php جدید هماهنگ باشد.
    -->
    <form method="POST" action="{{ route('install.processStep1') }}" class="p-8 space-y-6">
        @csrf

        <!-- نمایش خطاها -->
        @if ($errors->any())
            <div class="bg-red-50 border-r-4 border-red-400 p-4 rounded-md">
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- فیلدهای فرم -->
        <div>
            <label for="db_host" class="block text-sm font-medium text-gray-700">میزبان دیتابیس (Host)</label>
            <input type="text" name="db_host" id="db_host" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('db_host', '127.0.0.1') }}" required>
        </div>

        <div>
            <label for="db_port" class="block text-sm font-medium text-gray-700">پورت</label>
            <input type="text" name="db_port" id="db_port" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('db_port', '3306') }}" required>
        </div>

        <div>
            <label for="db_database" class="block text-sm font-medium text-gray-700">نام دیتابیس</label>
            <input type="text" name="db_database" id="db_database" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('db_database', 'laravel-crm') }}" required>
        </div>

        <div>
            <label for="db_username" class="block text-sm font-medium text-gray-700">نام کاربری دیتابیس</label>
            <input type="text" name="db_username" id="db_username" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('db_username', 'root') }}" required>
        </div>

        <div>
            <label for="db_password" class="block text-sm font-medium text-gray-700">رمز عبور دیتابیس</label>
            <input type="password" name="db_password" id="db_password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <p class="mt-1 text-xs text-gray-500">اگر رمز عبور ندارید، این فیلد را خالی بگذارید.</p>
        </div>

        <!-- دکمه ارسال -->
        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                تست اتصال و ادامه
                <svg class="w-5 h-5 mr-2 transform -scale-x-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
        </div>
    </form>

</div>
</body>
</html>

