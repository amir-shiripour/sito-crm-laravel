@extends('layouts.user')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">تنظیمات ماژول قرارداد ساز</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">پیکربندی فرمت شماره‌گذاری قراردادها و مقادیر اولیه شمارشگر</p>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <form action="{{ route('user.contracts.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-4">
                    <h2 class="text-base font-bold text-gray-900 dark:text-gray-100 border-b pb-2 mb-4">شماره‌گذاری اسناد قرارداد (گزینه C)</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">فرمت شماره‌گذاری قرارداد</label>
                            <input type="text" name="number_format" value="{{ $settings['number_format'] }}" required class="w-full font-mono rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm">
                            <p class="text-[10px] text-gray-400 mt-1.5 leading-relaxed">
                                توکن‌های مجاز: <br>
                                <code class="bg-gray-100 dark:bg-gray-900 px-1 py-0.5 rounded">{PREFIX}</code> پیشوند قرارداد<br>
                                <code class="bg-gray-100 dark:bg-gray-900 px-1 py-0.5 rounded">{YEAR}</code> سال جلالی جاری<br>
                                <code class="bg-gray-100 dark:bg-gray-900 px-1 py-0.5 rounded">{MONTH}</code> ماه جلالی جاری<br>
                                <code class="bg-gray-100 dark:bg-gray-900 px-1 py-0.5 rounded">{DAY}</code> روز جلالی جاری<br>
                                <code class="bg-gray-100 dark:bg-gray-900 px-1 py-0.5 rounded">{COUNTER}</code> شمارشگر خودکار افزایش‌یابنده
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">شمارشگر بعدی</label>
                                <input type="number" name="number_counter" value="{{ $settings['number_counter'] }}" required min="1" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">طول شمارشگر (تعداد کاراکتر)</label>
                                <input type="number" name="number_counter_length" value="{{ $settings['number_counter_length'] }}" required min="1" max="10" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm">
                                <p class="text-[10px] text-gray-400 mt-1">تعداد صفرهای پیش از عدد (مثال: ۵ کاراکتر = 00001)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t pt-4">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                        ذخیره تنظیمات
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
