@extends('layouts.user')

@section('content')
    <div class="space-y-5">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ایجاد سرویس</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">اطلاعات پایه و سیاست‌های رزرو</p>
            </div>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
               href="{{ route('user.booking.services.index') }}">
                بازگشت
            </a>
        </div>

        <form method="POST" action="{{ route('user.booking.services.store') }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-4">
            @csrf
            @include('booking::user.services._form', ['service' => new \Modules\Booking\Entities\BookingService()])
            <div class="pt-2">
                <button
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                    ثبت سرویس
                </button>
            </div>
        </form>
    </div>
@endsection
