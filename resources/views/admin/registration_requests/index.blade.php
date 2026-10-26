@extends('layouts.user')

@php
    $title = 'مدیریت درخواست‌های ثبت نام';
@endphp

@section('content')
<div class="space-y-6 font-sans">

    {{-- هدر صفحه و کارت‌های آماری --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl font-bold shrink-0">
                        📋
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-black text-slate-900 dark:text-white">درخواست‌های ثبت‌نام و عضویت</h1>
                            <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-100 dark:border-indigo-500/20">
                                تایید دستی مدارک
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                            بررسی مدارک هویتی، حوزه‌های فنی و تخصص‌های انتخابی متقاضیان و تعیین صلاحیت دسترسی به ناوگان
                        </p>
                    </div>
                </div>
            </div>

            {{-- لینک سریع به مدیریت تنظیمات ثبت‌نام --}}
            <div class="flex items-center gap-2.5">
                <a href="{{ route('user.fieldservice.technicians.index') }}"
                   class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-2">
                    <span>🔧</span>
                    <span>ناوگان متخصصین فنی</span>
                </a>
                <a href="{{ url('/settings#registration') }}"
                   class="px-4 py-2.5 rounded-2xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 text-xs font-bold transition flex items-center gap-2 border border-indigo-200 dark:border-indigo-500/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>تنظیمات قوانین ثبت‌نام</span>
                </a>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-slate-700/50">
            <a href="{{ route('admin.registration-requests.index') }}"
               class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between hover:border-indigo-300 dark:hover:border-indigo-500/40 transition">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">کل درخواست‌ها</div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($counts['total']) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    📥
                </div>
            </a>

            <a href="{{ route('admin.registration-requests.index', ['status' => 'pending']) }}"
               class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between hover:border-amber-400 dark:hover:border-amber-500/40 transition {{ request('status') === 'pending' ? 'ring-2 ring-amber-500/50' : '' }}">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">در انتظار بررسی مدارک</div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($counts['pending']) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    ⏳
                </div>
            </a>

            <a href="{{ route('admin.registration-requests.index', ['status' => 'approved']) }}"
               class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between hover:border-emerald-400 dark:hover:border-emerald-500/40 transition {{ request('status') === 'approved' ? 'ring-2 ring-emerald-500/50' : '' }}">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">تایید و فعال شده</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($counts['approved']) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    ✅
                </div>
            </a>

            <a href="{{ route('admin.registration-requests.index', ['status' => 'rejected']) }}"
               class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between hover:border-rose-400 dark:hover:border-rose-500/40 transition {{ request('status') === 'rejected' ? 'ring-2 ring-rose-500/50' : '' }}">
                <div>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">رد شده</div>
                    <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ number_format($counts['rejected']) }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                    ❌
                </div>
            </a>
        </div>
    </div>

    {{-- پیام‌های سیستمی --}}
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center gap-3 shadow-sm animate-in fade-in">
        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold flex flex-col gap-2 shadow-sm animate-in fade-in">
        @foreach ($errors->all() as $error)
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $error }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- نوار ابزار فیلتر و جستجو --}}
    <form method="GET" action="{{ route('admin.registration-requests.index') }}" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        {{-- جستجو --}}
        <div class="relative w-full md:flex-1">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="جستجوی نام، تلفن همراه یا ایمیل متقاضی..."
                   class="w-full h-10 pr-10 pl-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            {{-- فیلتر نقش --}}
            <select name="role_id" onchange="this.form.submit()"
                    class="h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                <option value="">همه نقش‌ها</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->display_name ?? $role->name }}
                    </option>
                @endforeach
            </select>

            {{-- فیلتر وضعیت --}}
            <select name="status" onchange="this.form.submit()"
                    class="h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>فقط در انتظار بررسی ({{ $counts['pending'] }})</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>تایید شده ({{ $counts['approved'] }})</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>رد شده ({{ $counts['rejected'] }})</option>
            </select>

            <button type="submit"
                    class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                <span>اعمال فیلتر</span>
            </button>

            @if(request()->hasAny(['search', 'role_id', 'status']))
            <a href="{{ route('admin.registration-requests.index') }}"
               class="h-10 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 font-bold text-xs transition flex items-center">
                پاکسازی
            </a>
            @endif
        </div>
    </form>

    {{-- لیست کارت‌های درخواست --}}
    <div class="space-y-4">
        @forelse($requests as $req)
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-5 sm:p-6 shadow-sm hover:border-slate-300 dark:hover:border-slate-600 transition-all space-y-4">
            
            {{-- ردیف هدر کارت: آواتار، نام، نقش، وضعیت و اکشن‌ها --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700/50">
                <div class="flex items-start sm:items-center gap-3.5">
                    {{-- آواتار --}}
                    <div class="w-13 h-13 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xl shrink-0 shadow-sm">
                        {{ mb_substr($req->name ?? 'ک', 0, 1) }}
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-black text-slate-900 dark:text-white">
                                {{ $req->name }}
                            </h2>

                            {{-- برچسب نقش --}}
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/20">
                                <span>{{ $req->role && $req->role->name === 'technician' ? '🛠️' : '👤' }}</span>
                                <span>{{ $req->role?->display_name ?? $req->role?->name ?? 'نقش نامشخص' }}</span>
                            </span>

                            {{-- وضعیت بررسی --}}
                            @if($req->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>در انتظار بررسی و تایید</span>
                            </span>
                            @elseif($req->status === 'approved')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                                <span>✓</span>
                                <span>تایید و فعال شده</span>
                            </span>
                            @elseif($req->status === 'rejected')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-500/30">
                                <span>✕</span>
                                <span>رد شده</span>
                            </span>
                            @endif
                        </div>

                        {{-- ارتباطی: موبایل و ایمیل و تاریخ --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-slate-500 dark:text-slate-400">
                            @if($req->mobile)
                            <div class="flex items-center gap-1.5 font-sans font-medium text-slate-700 dark:text-slate-300" dir="ltr">
                                <span>📞</span>
                                <span>{{ $req->mobile }}</span>
                            </div>
                            @endif

                            @if($req->email)
                            <div class="flex items-center gap-1.5 font-sans text-[11px] text-slate-400 dark:text-slate-500" dir="ltr">
                                <span>✉️</span>
                                <span>{{ $req->email }}</span>
                            </div>
                            @endif

                            <div class="flex items-center gap-1 text-[11px] text-slate-400">
                                <span>ثبت:</span>
                                <span>{{ function_exists('jdate') ? jdate($req->created_at)->format('Y/m/d - H:i') : $req->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- دکمه‌های عملیات سریع --}}
                <div class="flex items-center gap-2 self-end lg:self-center shrink-0">
                    @if($req->status === 'pending')
                    <form action="{{ route('admin.registration-requests.approve', $req) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>تایید و فعال‌سازی حساب</span>
                        </button>
                    </form>

                    <button type="button" onclick="openRejectModal({{ $req->id }}, '{{ addslashes($req->name) }}')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 dark:bg-slate-700/70 dark:hover:bg-rose-500/20 text-slate-700 hover:text-rose-600 dark:text-slate-300 dark:hover:text-rose-400 font-bold text-xs border border-slate-200 dark:border-slate-600 hover:border-rose-300 dark:hover:border-rose-500/30 transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>رد درخواست</span>
                    </button>
                    @elseif($req->status === 'approved' && $req->reviewer)
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 text-left">
                        <span>تایید شده توسط <strong class="text-slate-700 dark:text-slate-200">{{ $req->reviewer->name }}</strong></span>
                        @if($req->reviewed_at)
                        <div class="text-[10px] text-slate-400 font-sans mt-0.5">
                            {{ function_exists('jdate') ? jdate($req->reviewed_at)->format('Y/m/d H:i') : $req->reviewed_at }}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- بخش اطلاعات هویتی، مکانی و خدمات انتخابی متقاضی --}}
            @php
                $cf = $req->custom_fields ?? [];
                $province = $cf['province'] ?? null;
                $city = $cf['city'] ?? null;
                $nationalCode = $cf['national_code'] ?? null;
                $shaba = $cf['shaba_number'] ?? null;
                $address = $cf['address'] ?? null;
                $cardImage = $cf['national_card_image'] ?? null;

                // استخراج رسته‌ها و خدمات انتخابی
                $rawCats = $cf['categories'] ?? $cf['specialties'] ?? [];
                if (is_string($rawCats)) {
                    $rawCats = json_decode($rawCats, true) ?: explode(',', $rawCats);
                }
                $selectedCatIds = is_array($rawCats) ? $rawCats : [];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-slate-50 dark:bg-slate-900/50 p-4 sm:p-5 rounded-2xl border border-slate-200/70 dark:border-slate-700/50 text-xs">
                
                {{-- ستون ۱: مشخصات فردی و هویتی --}}
                <div class="space-y-3">
                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>🪪</span>
                        <span>مشخصات شناسایی و بانکی</span>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50">
                            <span class="text-slate-500 dark:text-slate-400">کد ملی:</span>
                            <span class="font-bold text-slate-900 dark:text-white font-sans">{{ $nationalCode ?: 'ثبت نشده' }}</span>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50">
                            <span class="text-slate-500 dark:text-slate-400">شماره شبا:</span>
                            <span class="font-bold text-slate-900 dark:text-white font-sans text-[11px]" dir="ltr">{{ $shaba ?: 'ثبت نشده' }}</span>
                        </div>
                    </div>
                </div>

                {{-- ستون ۲: محدوده و محل فعالیت --}}
                <div class="space-y-3">
                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>📍</span>
                        <span>موقعیت جغرافیایی و کارگاه</span>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50">
                            <span class="text-slate-500 dark:text-slate-400">استان و شهر:</span>
                            <span class="font-bold text-slate-900 dark:text-white">
                                {{ $province ?: 'مازندران' }} / {{ $city ?: 'بابل' }}
                            </span>
                        </div>

                        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50">
                            <span class="text-slate-500 dark:text-slate-400 block mb-0.5">آدرس کارگاه یا سکونت:</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200 leading-relaxed">
                                {{ $address ?: 'آدرس دقیق ثبت نشده است.' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- ستون ۳: مدرک شناسایی / کارت ملی --}}
                <div class="space-y-3">
                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>🖼️</span>
                        <span>مدرک شناسایی / کارت ملی</span>
                    </div>

                    @if($cardImage)
                    <div class="flex items-center gap-3 p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50">
                        <a href="{{ asset('storage/' . $cardImage) }}" target="_blank"
                           class="group relative w-16 h-16 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 block shadow-sm hover:scale-105 transition-all">
                            <img src="{{ asset('storage/' . $cardImage) }}" alt="کارت ملی" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-slate-950/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[10px] font-bold">
                                🔍 مشاهده
                            </div>
                        </a>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">تصویر مدارک هویتی</div>
                            <p class="text-[11px] text-slate-400 mt-0.5">برای مشاهده در سایز اصلی کلیک نمایید</p>
                            <a href="{{ asset('storage/' . $cardImage) }}" target="_blank"
                               class="inline-flex items-center gap-1 text-[11px] text-indigo-600 dark:text-indigo-400 font-bold hover:underline mt-1">
                                <span>دانلود / مشاهده فایل</span>
                                <span>↗</span>
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50 text-center text-slate-400">
                        تصویر مدرک بارگذاری نشده است.
                    </div>
                    @endif
                </div>

                {{-- سطر تمام‌عرض: حوزه‌ها و خدمات تخصصی انتخابی (Categories) --}}
                <div class="col-span-1 md:col-span-2 lg:col-span-3 pt-2 border-t border-slate-200/60 dark:border-slate-700/40">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                        <div class="text-[11px] font-black text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                            <span>حوزه‌ها و خدمات فنی انتخابی متقاضی (تخصص‌های ناوگان):</span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-sans">تعداد تخصص‌ها: {{ count($selectedCatIds) }}</span>
                    </div>

                    @if(!empty($selectedCatIds))
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($selectedCatIds as $catId)
                            @php
                                $cat = isset($categoriesMap[$catId]) ? $categoriesMap[$catId] : null;
                            @endphp
                            @if($cat)
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/30 shadow-sm">
                                <span class="text-base">{{ $cat->icon ?: '🛠️' }}</span>
                                <span class="font-bold text-xs">{{ $cat->name }}</span>
                                @if($cat->base_labor_fee > 0)
                                <span class="text-[10px] text-slate-400 dark:text-slate-400 border-r border-slate-200 dark:border-slate-700 pr-2 mr-1">
                                    پایه: {{ number_format($cat->base_labor_fee / 10) }} تومان
                                </span>
                                @endif
                            </div>
                            @else
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 font-bold text-xs border border-slate-200 dark:border-slate-600">
                                <span>🛠️</span>
                                <span>خدمت فنی #{{ $catId }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @else
                    <div class="text-xs text-slate-400 italic">
                        هیچ خدمت تخصصی انتخاب نشده است.
                    </div>
                    @endif
                </div>

            </div>

            {{-- نمایش دلیل رد درخواست (در صورت رد شدن) --}}
            @if($req->status === 'rejected' && $req->rejection_reason)
            <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-700 dark:text-rose-300 text-xs flex items-start gap-2.5">
                <span class="text-base">⛔</span>
                <div>
                    <strong class="font-bold block">دلیل عدم تایید این درخواست:</strong>
                    <p class="mt-0.5 leading-relaxed">{{ $req->rejection_reason }}</p>
                </div>
            </div>
            @endif

        </div>
        @empty
        <div class="p-12 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 text-center space-y-3">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100 dark:bg-slate-700/50 text-slate-400 flex items-center justify-center text-3xl">
                📂
            </div>
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">هیچ درخواست ثبت‌نامی یافت نشد</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                در حال حاضر درخواستی با فیلترهای انتخابی در صف بررسی وجود ندارد.
            </p>
        </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($requests->hasPages())
    <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60">
        {{ $requests->links() }}
    </div>
    @endif

</div>

{{-- ========================================================================= --}}
{{-- MODAL رد درخواست ثبت‌نام                                                   --}}
{{-- ========================================================================= --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" onclick="closeRejectModal()"></div>

        <div class="relative w-full max-w-md p-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl text-right font-sans my-auto">
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg font-bold shrink-0">
                        ✕
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">رد درخواست عضویت</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" id="rejectApplicantName"></p>
                    </div>
                </div>

                <div class="py-4 space-y-3">
                    <label for="rejection_reason" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        علت عدم تایید یا نقص مدارک <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                              class="w-full p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500"
                              placeholder="مثال: عدم وضوح تصویر کارت ملی / نقص در اطلاعات شبا یا شماره تماس..."></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                        انصراف
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/30 transition">
                        ثبت رد درخواست
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRejectModal(requestId, applicantName) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const nameEl = document.getElementById('rejectApplicantName');
        form.action = `/admin/registration-requests/${requestId}/reject`;
        if (nameEl) {
            nameEl.textContent = 'متقاضی: ' + applicantName;
        }
        modal.classList.remove('hidden');
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.add('hidden');
    }
</script>
@endsection
