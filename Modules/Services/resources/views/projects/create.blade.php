@extends('layouts.user')

@php
    $inputClass = "w-full rounded-2xl border-gray-200 bg-gray-50/50 px-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-800 dark:focus:border-purple-500";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";

    $project = $project ?? null;
    $isEdit  = $project && $project->exists;
    $action  = $isEdit
        ? route('services.projects.update', $project)
        : route('services.projects.store');
@endphp

@section('title', $isEdit ? 'ویرایش: '.$project?->name : 'پروژه جدید')

@section('content')
    {{-- توسعه عرض کانتینر به سایز 2XL --}}
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl shadow-lg {{ $isEdit ? 'bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-amber-500/30' : 'bg-gradient-to-br from-purple-500 to-purple-700 text-white shadow-purple-500/30' }}">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        @if($isEdit)
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        @endif
                    </svg>
                </span>
                {{ $isEdit ? 'ویرایش پروژه' : 'ثبت پروژه جدید' }}
            </h1>
            <a href="{{ route('services.projects.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست
            </a>
        </div>

        @if($errors->any())
            <div
                class="p-5 text-sm text-red-800 rounded-3xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <div>
                    <p class="font-black text-base mb-2">خطا در ثبت اطلاعات!</p>
                    <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ $action }}" class="space-y-8">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            {{-- Basic Info --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        اطلاعات پایه پروژه
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div class="md:col-span-2 xl:col-span-2">
                        <label class="{{ $labelClass }}">نام پروژه <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $project?->name ?? '') }}" required
                               class="{{ $inputClass }}" placeholder="عنوان پروژه را وارد کنید">
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">مشتری <span class="text-red-500">*</span></label>
                        <select name="customer_id" required class="{{ $inputClass }} cursor-pointer">
                            <option value="">انتخاب مشتری...</option>
                            @foreach($customers as $customer)
                                <option
                                    value="{{ $customer->id }}" @selected(old('customer_id', $project?->customer_id ?? '') == $customer->id)>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">سرویس مرتبط</label>
                        <select name="service_id" class="{{ $inputClass }} cursor-pointer">
                            <option value="">بدون سرویس</option>
                            @foreach($services as $srv)
                                <option
                                    value="{{ $srv->id }}" @selected(old('service_id', $project?->service_id ?? '') == $srv->id)>
                                    {{ $srv->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">مسئول اجرای پروژه</label>
                        <select name="assigned_user_id" class="{{ $inputClass }} cursor-pointer">
                            <option value="">بدون مسئول</option>
                            @foreach($staff as $member)
                                <option
                                    value="{{ $member->id }}" @selected(old('assigned_user_id', $project?->assigned_user_id ?? '') == $member->id)>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">وضعیت پروژه</label>
                        <select name="status_id" class="{{ $inputClass }} cursor-pointer">
                            <option value="">انتخاب وضعیت...</option>
                            @foreach($statuses as $st)
                                <option
                                    value="{{ $st->id }}" @selected(old('status_id', $project?->status_id ?? '') == $st->id)>
                                    {{ $st->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3 xl:col-span-4">
                        <label class="{{ $labelClass }}">توضیحات کلی</label>
                        <textarea name="description" rows="4" class="{{ $inputClass }} resize-none"
                                  placeholder="توضیحات و بریف اولیه پروژه را اینجا وارد کنید...">{{ old('description', $project?->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                {{-- Schedule & Budget --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                        <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            زمان‌بندی و بودجه
                        </h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">تاریخ شروع</label>
                            <input type="text" name="start_date" data-jdp-only-date autocomplete="off"
                                   value="{{ old('start_date', $project?->start_date?->format('Y/m/d') ?? '') }}"
                                   class="{{ $inputClass }} cursor-pointer text-center" placeholder="انتخاب تاریخ" readonly>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">تاریخ پایان (سررسید)</label>
                            <input type="text" name="end_date" data-jdp-only-date autocomplete="off"
                                   value="{{ old('end_date', $project?->end_date?->format('Y/m/d') ?? '') }}"
                                   class="{{ $inputClass }} cursor-pointer text-center" placeholder="انتخاب تاریخ" readonly>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">بودجه تخمینی (تومان / ریال)</label>
                            <input type="number" name="budget" min="0"
                                   value="{{ old('budget', $project?->budget ?? 0) }}"
                                   class="{{ $inputClass }} dir-ltr text-end font-bold text-lg tabular-nums">
                        </div>
                    </div>
                </div>

                {{-- Priority & Progress --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                        <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            اولویت و پیشرفت
                        </h2>
                    </div>
                    <div class="p-6 space-y-8">
                        <div>
                            <label class="{{ $labelClass }}">سطح اولویت <span class="text-red-500">*</span></label>
                            <select name="priority" required class="{{ $inputClass }} cursor-pointer">
                                <option value="low" @selected(old('priority', $project?->priority ?? 'medium') === 'low')>کم (Low)</option>
                                <option value="medium" @selected(old('priority', $project?->priority ?? 'medium') === 'medium')>متوسط (Medium)</option>
                                <option value="high" @selected(old('priority', $project?->priority ?? '') === 'high')>زیاد (High)</option>
                                <option value="urgent" @selected(old('priority', $project?->priority ?? '') === 'urgent')>فوری (Urgent)</option>
                            </select>
                        </div>
                        <div x-data="{ progress: {{ old('progress', $project?->progress ?? 0) }} }">
                            <label class="{{ $labelClass }} flex justify-between items-center">
                                <span>درصد پیشرفت</span>
                                <span class="text-purple-600 dark:text-purple-400 font-mono text-lg font-black tabular-nums"
                                      x-text="progress + '%'"></span>
                            </label>
                            <div class="mt-4 px-2">
                                <input type="range" name="progress" min="0" max="100" x-model="progress"
                                       value="{{ old('progress', $project?->progress ?? 0) }}"
                                       class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full appearance-none cursor-pointer accent-purple-600 hover:accent-purple-700 transition-all shadow-inner">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sticky Submit Bar --}}
            <div class="sticky bottom-4 z-40">
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-3xl border border-gray-200 dark:border-gray-700/50 shadow-[0_10px_40px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 md:flex-none px-10 py-4 rounded-2xl bg-gradient-to-r {{ $isEdit ? 'from-amber-500 to-amber-600 shadow-amber-500/30 hover:shadow-amber-500/50' : 'from-purple-600 to-purple-700 shadow-purple-500/30 hover:shadow-purple-500/50' }} text-white text-base font-black shadow-lg transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $isEdit ? 'ذخیره تغییرات پروژه' : 'ثبت نهایی پروژه' }}
                    </button>
                    <a href="{{ route('services.projects.index') }}"
                       class="px-8 py-4 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-2xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        انصراف
                    </a>
                </div>
            </div>

        </form>
    </div>

    @include('partials.jalali-date-picker')
@endsection
