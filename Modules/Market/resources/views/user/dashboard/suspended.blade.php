@extends('layouts.user')
@php($title = 'فروشگاه مسدود شده')
@section('content')
    <div class="bg-red-50 dark:bg-red-900/20 rounded-3xl p-8 border border-red-100 dark:border-red-800 text-center">
        <h1 class="text-2xl font-bold text-red-600 dark:text-red-400 mb-4">فروشگاه شما مسدود شده است</h1>
        <p class="text-red-500 dark:text-red-300">متاسفانه فعالیت فروشگاه شما به حالت تعلیق درآمده است. لطفاً با پشتیبانی تماس بگیرید.</p>
    </div>
@endsection
