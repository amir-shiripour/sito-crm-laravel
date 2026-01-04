@extends('layouts.user')

@section('content')
    <div class="space-y-5">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ویرایش فرم</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $form->name }}</p>
            </div>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
               href="{{ route('user.booking.forms.index') }}">
                بازگشت
            </a>
        </div>

        <form method="POST" action="{{ route('user.booking.forms.update', $form) }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-4">
            @csrf
            @method('PUT')
            @include('booking::user.forms._form', ['form' => $form])
        </form>
    </div>
@endsection
