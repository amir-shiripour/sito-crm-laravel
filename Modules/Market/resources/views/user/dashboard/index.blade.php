@extends('layouts.user')
@php($title = 'داشبورد فروشگاه')
@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700 shadow-sm">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">خوش آمدید، {{ $vendor->store_name }}</h1>
        <p class="text-gray-500 dark:text-gray-400">فروشگاه شما فعال است. اکنون می‌توانید محصولات خود را اضافه کنید.</p>
        <a href="{{ route('user.market.vendor.products.index') }}" class="inline-block mt-6 px-6 py-2 bg-indigo-600 text-white rounded-xl">مدیریت محصولات</a>
    </div>
@endsection
