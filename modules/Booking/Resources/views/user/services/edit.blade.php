@extends('layouts.user')

@section('content')
    <div class="space-y-5">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ویرایش سرویس #{{ $service->id }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $service->name }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                   href="{{ route('user.booking.services.index') }}">
                    بازگشت
                </a>

                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200 text-sm font-medium hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
                   href="{{ route('user.booking.services.availability.edit', $service) }}">
                    برنامه زمانی این سرویس
                </a>
            </div>
        </div>

        @if(session('success'))
            <div
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('user.booking.services.update', $service) }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-4">
            @csrf
            @include('booking::user.services._form', ['service' => $service])
            <div class="pt-2">
                <button
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
@endsection
