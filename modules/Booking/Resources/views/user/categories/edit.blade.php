@extends('layouts.user')

@section('content')
    <div class="max-w-3xl space-y-5">
        <div
            class="flex items-center justify-between gap-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ویرایش دسته‌بندی</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $category->name }}</p>
            </div>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
               href="{{ route('user.booking.categories.index') }}">
                بازگشت
            </a>
        </div>

        <form method="POST" action="{{ route('user.booking.categories.update', $category) }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            @csrf
            @method('PUT')
            @include('booking::user.categories._form', ['category' => $category])
        </form>
    </div>
@endsection
