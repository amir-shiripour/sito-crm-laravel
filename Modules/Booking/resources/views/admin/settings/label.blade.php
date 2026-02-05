@extends('layouts.admin')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="font-semibold text-gray-900 dark:text-gray-100">تنظیم برچسب‌های ماژول نوبت‌دهی</h1>
            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-900/40 text-gray-700 dark:text-gray-300 hover:bg-gray-200">
                بازگشت
            </a>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-200 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.booking.settings.label.update') }}" class="space-y-6 max-w-xl">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">برچسب "ارائه‌دهنده" (مفرد)</label>
                    <input type="text" name="label_provider" value="{{ old('label_provider', $labelProvider) }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">مثال: پزشک، مشاور، آرایشگر</p>
                    @error('label_provider')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">برچسب "ارائه‌دهندگان" (جمع)</label>
                    <input type="text" name="label_providers" value="{{ old('label_providers', $labelProviders) }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">مثال: پزشکان، مشاوران، آرایشگران</p>
                    @error('label_providers')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                        ذخیره
                    </button>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                    این برچسب‌ها در کل ماژول نوبت‌دهی جایگزین خواهند شد.
                </span>
                </div>
            </form>
        </div>
    </div>
@endsection
