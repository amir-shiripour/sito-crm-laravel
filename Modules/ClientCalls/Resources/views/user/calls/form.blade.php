{{-- modules/ClientCalls/resources/views/user/calls/form.blade.php --}}
@extends('layouts.user')

@php
    $pageTitle = ($call?->id ? 'ویرایش تماس' : 'ثبت تماس جدید') . ' برای ' . ($client->full_name ?: $client->username);

    // مقدار اولیه تاریخ به صورت شمسی (برای input جلالی)
    $callDateValue = old('call_date_jalali');
    if (! $callDateValue && $call?->call_date) {
        // تبدیل تاریخ میلادی ذخیره‌شده به شمسی برای نمایش
        $callDateValue = jdate($call->call_date)->format('Y/m/d');
    }

    // وضعیت فعلی
    $statuses = [
        'planned'   => 'برنامه‌ریزی شده',
        'done'      => 'انجام شده',
        'failed'    => 'ناموفق',
        'canceled'  => 'لغو شده',
    ];
    $currentStatus = old('status', $call->status ?? 'done');
@endphp

@php($title = $pageTitle)

{{-- اسکریپت‌ها و استایل JalaliDatePicker --}}
@includeIf('partials.jalali-date-picker')

@section('content')
{{--    <div class="max-w-3xl mx-auto space-y-6">--}}
    <div class="max-w-3xl mx-auto space-y-6">

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $call?->id ? 'ویرایش تماس' : 'ثبت تماس جدید' }}
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        برای مشتری:
                        <span class="font-medium text-gray-800 dark:text-gray-200">
                            {{ $client->full_name ?: $client->username }}
                        </span>
                        <span class="text-gray-400 mx-1">|</span>
                        <span class="text-[11px] text-gray-400">شناسه: {{ $client->id }}</span>
                    </p>
                </div>
                <a href="{{ route('user.clients.calls.index', $client) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50
                          dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                    بازگشت به تاریخچه تماس‌ها
                </a>
            </div>

            <form method="POST"
                  action="{{ $call?->id
                        ? route('user.clients.calls.update', [$client, $call])
                        : route('user.clients.calls.store', $client) }}">
                @csrf
                @if($call?->id)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- تاریخ تماس (شمسی در فرانت، میلادی در بک‌اند) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            تاریخ تماس <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="call_date_jalali"
                            id="call_date_jalali"
                            data-jdp-only-date
                            value="{{ $callDateValue }}"
                            placeholder="مثلاً 1403/10/12"
                            required
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                                   focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                   dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                        >
                        @error('call_date_jalali')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- زمان تماس (با JalaliDatePicker در حالت فقط زمان) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            زمان تماس <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="call_time"
                            id="call_time"
                            data-jdp-only-time
                            value="{{ old('call_time', $call?->call_time) }}"
                            placeholder="مثلاً 14:30"
                            required
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                                   focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                   dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                        >
                        @error('call_time')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- علت تماس --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        علت تماس <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="reason"
                        value="{{ old('reason', $call?->reason) }}"
                        required
                        placeholder="مثلاً: پیگیری بدهی، هماهنگی جلسه، پشتیبانی فنی و ..."
                        class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                               focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                               dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                    >
                    @error('reason')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- نتیجه تماس --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        نتیجه تماس <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        name="result"
                        rows="3"
                        required
                        placeholder="نتیجه تماس را با جزئیات وارد کنید..."
                        class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                               focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                               dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                    >{{ old('result', $call?->result) }}</textarea>
                    @error('result')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- وضعیت تماس --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        وضعیت تماس <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="status"
                        required
                        class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900
                               focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                               dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                    >
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected($currentStatus === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('user.clients.calls.index', $client) }}"
                       class="px-5 py-2 rounded-xl border border-gray-300 text-sm text-gray-700 bg-white hover:bg-gray-50
                              dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                        انصراف
                    </a>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 shadow-md shadow-indigo-500/30">
                        ذخیره تماس
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
