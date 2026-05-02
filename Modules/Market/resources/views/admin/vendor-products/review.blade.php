@extends('layouts.user')
@php($title = 'بررسی محصولات فروشندگان')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-black text-gray-800 dark:text-white">بررسی محصولات فروشندگان</h2>
        <p class="text-sm text-gray-500 mt-1">تایید یا رد تنوع‌های ثبت شده توسط فروشندگان جهت نمایش در سایت</p>
    </div>

    @livewire('market::admin.vendor-product-review')
@endsection
