@extends('layouts.user')

@php
    $title = 'پروفایل و اطلاعات مالی میزبان';
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600 font-sans";
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </span>
                پروفایل میزبانی و اطلاعات بانکی
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-10">مدیریت مشخصات نمایشی و اطلاعات واریز درآمد</p>
        </div>

        <a href="{{ route('user.properties.hosts.dashboard') }}" class="text-xs font-bold text-gray-500 hover:text-indigo-600 transition flex items-center gap-1">
            بازگشت به میزکار
            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </a>
    </div>

    <form action="{{ route('user.properties.hosts.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- مشخصات هویتی و عمومی --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                مشخصات عمومی
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="{{ $labelClass }}">نام نمایشی میزبان / مجموعه</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $host->display_name) }}" required class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">شماره تماس هماهنگی</label>
                    <input type="text" name="phone" value="{{ old('phone', $host->phone) }}" required class="{{ $inputClass }} text-left dir-ltr">
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">معرفی میزبان</label>
                    <textarea name="about" rows="3" class="{{ $inputClass }} resize-none">{{ old('about', $host->about) }}</textarea>
                </div>

                <div>
                    <label class="{{ $labelClass }}">تصویر پروفایل جدید</label>
                    <input type="file" name="avatar" accept="image/*" class="{{ $inputClass }}">
                </div>

                @if($host->avatar)
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('storage/' . $host->avatar) }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200">
                        <span class="text-xs text-gray-500">تصویر فعلی</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- مشخصات بانکی --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                اطلاعات بانکی جهت واریز وجه
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">شماره شبا (بدون IR)</label>
                    <div class="relative">
                        <input type="text" name="shaba_number" value="{{ old('shaba_number', $host->shaba_number) }}" class="{{ $inputClass }} text-left dir-ltr pl-12">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs font-bold text-gray-400">IR</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">نام بانک</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $host->bank_name) }}" class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">نام صاحب حساب</label>
                    <input type="text" name="account_owner_name" value="{{ old('account_owner_name', $host->account_owner_name) }}" class="{{ $inputClass }}">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-500/25 transition">
                ذخیره تغییرات
            </button>
        </div>
    </form>
</div>
@endsection
